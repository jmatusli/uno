<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class UsersController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');

	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('login','logout');		

		// Allow users to register and logout.
		//$this->Auth->allow('add','logout');		
	}
	
	public function login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
        $boolActive=$this->Auth->User('bool_active');
				//pr($this->Auth->User);
        //echo "bool active is ".$boolActive."<br/>";
        if ($boolActive){
          $this->recordUserActivity($this->data['User']['username'],"Login successful");
          $this->Session->write('User.username',$this->data['User']['username']);
          $this->Session->write('User.userid',$this->Auth->User('id'));
          
          //$userid = $this->Auth->User('id');
          //echo "user id ".$userid."!<br/>";
          $role = $this->Auth->User('role_id');
          //echo "role id ".$role."!<br/>";
          return $this->redirect(parent::userhome($role));
        }
			}
			$this->recordUserActivity($this->data['User']['username'],"Invalid username or password");
			$this->Session->setFlash(__('Invalid username or password, try again'));
		}
	}

	public function logout() {
		$this->recordUserActivity($this->Session->read('User.username'),"Logout");
		return $this->redirect($this->Auth->logout());
	}	

	public function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
    
    $this->loadModel('ThirdParty');
		$this->loadModel('Order');
		$this->loadModel('ExchangeRate');
    
		$unassociatedDisplayOptions=array(
			'0'=>'Mostrar solamente clientes asociados',
			'1'=>'Mostrar clientes asociados y no asociados',
		);
    $this->set(compact('unassociatedDisplayOptions'));
    define('SHOW_CLIENT_UNASSOCIATED_NO','0');
    define('SHOW_CLIENT_UNASSOCIATED_YES','1');
    
    $historyDisplayOptions=array(
			'0'=>'No mostrar el historial de asignaciones al cliente',
			'1'=>'Mostrar el historial de asignaciones al cliente',
		);
		$this->set(compact('historyDisplayOptions'));
		define('HISTORY_NONE','0');
		define('HISTORY_FULL','1');
    
    //$currencyId=CURRENCY_CS;
		$searchTerm="";
    // TODO FIX NEEDED 20180603 CHECK ON ASSOCIATIONS REMOVED
    $unassociatedDisplayOptionId=SHOW_CLIENT_UNASSOCIATED_YES;
    $historyDisplayOptionId=HISTORY_NONE;
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      //$currencyId=$this->request->data['Report']['currency_id'];
      
      $unassociatedDisplayOptionId=$this->request->data['Report']['unassociated_display_option_id'];
      $historyDisplayOptionId=$this->request->data['Report']['history_display_option_id'];
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			//if ($this->Session->check('currencyId')){
			//	$currencyId=$_SESSION['currencyId'];
			//}
      
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
		$this->set(compact('startDate','endDate'));
		//$this->set(compact('currencyId'));
    $this->set(compact('unassociatedDisplayOptionId'));
    $this->set(compact('historyDisplayOptionId'));
    $this->set(compact('searchTerm'));
		
    $outgoingOrderIdsForUserAndPeriod=$this->Order->find('list',[
       'fields'=>['Order.id'],
			'conditions'=>[
        'Order.order_date >='=>$startDate,
				'Order.order_date <'=>$endDatePlusOne,
				'Order.user_id'=>$id,
			],
    ]);
    $clientConditions=[];
    if (!empty($searchTerm)){
			$clientConditions['ThirdParty.company_name LIKE ']='%'.$searchTerm.'%';
		}
    $clientIds=$this->ThirdParty->find('list',[
      'fields'=>['ThirdParty.id'],
      'conditions'=>$clientConditions,
    ]);
    
		$user= $this->User->find('first',[ 
			'conditions'=>['User.id'=>$id],
			'contain'=>[
        'ClientUser'=>[
          'conditions'=>['ClientUser.client_id'=>$clientIds],
          'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
        ],
				'Order'=>[
					'conditions'=>[
						'Order.id'=>$outgoingOrderIdsForUserAndPeriod,
            'Order.third_party_id'=>$clientIds,
            'Order.user_id'=>$id,
					],
				],
				'Role',
				'UserLog'=>[
					'conditions'=>[
						'event LIKE '=>'%Log%',
						'created >='=>$startDate,
						'created <'=>$endDatePlusOne,
					],
					'order'=>'created DESC',
				],
			],
		]);
    $orderQuantity=0;
		$orderTotal=0;
    for ($i=0;$i<count($user['Order']);$i++){
      if (!empty($user['Order'][$i]['Order'])){
        $orderQuantity+=1;
        $orderTotal+=$user['Order'][$i]['total_price'];
        /*
        $orderDate=$user['Order'][$i]['order_date'];
        //pr($user['InvoiceSalesOrder'][$i]['Invoice']);
        // set the exchange rate
        $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
        $user['Order'][$i]['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
        if ($currencyId==CURRENCY_CS){
          if ($user['Order'][$i]['Currency']['id']==CURRENCY_CS){
            $orderTotal+=$user['Order'][$i]['total_price'];
          }
          elseif ($invoice['Currency']['id']==CURRENCY_USD){
            //added calculation of totals in CS$
            $orderTotal+=round($user['Order'][$i]['total_price']*$user['Order'][$i]['exchange_rate'],2);
          }
        }
        elseif ($currencyId==CURRENCY_USD){
          if ($user['Order'][$i]['Currency']['id']==CURRENCY_USD){
            $orderTotal+=$user['Order'][$i]['total_price'];
          }
          elseif ($user['Order'][$i]['Currency']['id']==CURRENCY_CS){
            //added calculation of totals in USD
            $orderTotal+=round($user['Order'][$i]['total_price']/$user['Order'][$i]['exchange_rate'],2);
          }
        }
        */
      }
		}
    $user['User']['order_quantity']=$orderQuantity;
		$user['User']['order_total']=$orderTotal;
		$this->set(compact('user'));
    
    $clientIdList=[];
    for ($cu=0;$cu<count($user['ClientUser']);$cu++){
      if (!in_array($user['ClientUser'][$cu]['client_id'],$clientIdList)){
        $clientIdList[]=$user['ClientUser'][$cu]['client_id'];
      }
    }
    //pr($clientIdList);
    $uniqueClients=$this->ThirdParty->find('all',[
      // TODO FIX NEEDED 20180603 CHECK ON ASSOCIATIONS REMOVED
      //'conditions'=>['ThirdParty.id'=>$clientIdList],
      'contain'=>[					
        'Order'=>[
          'conditions'=>[
            'Order.order_date >='=>$startDate,
            'Order.order_date <'=>$endDatePlusOne,
            'Order.user_id'=>$id,
            'Order.third_party_id'=>$clientIds,
          ],
        ],
        'ClientUser'=>[
          'conditions'=>['ClientUser.user_id'=>$id],
          'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
        ]
  		],
      'order'=>'ThirdParty.company_name'
    ]);
    //pr($uniqueClients);
    
		for ($uc=0;$uc<count($uniqueClients);$uc++){
			$orderTotal=0;
			for ($q=0;$q<count($uniqueClients[$uc]['Order']);$q++){
        $orderTotal+=$uniqueClients[$uc]['Order'][$q]['total_price'];
        /*
				// set the exchange rate
				$orderDate=$uniqueClients[$uc]['Order'][$q]['order_date'];
				$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
				$uniqueClients[$uc]['Order'][$q]['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
				if ($currencyId==CURRENCY_CS){
					if ($uniqueClients[$uc]['Order'][$q]['Currency']['id']==CURRENCY_CS){
						$quotationTotal+=$uniqueClients[$uc]['Order'][$q]['total_price'];
					}
					elseif ($uniqueClients[$uc]['Order'][$q]['Currency']['id']==CURRENCY_USD){
						//added calculation of totals in CS$
						$quotationTotal+=round($uniqueClients[$uc]['Order'][$q]['total_price']*$uniqueClients[$uc]['Order'][$q]['exchange_rate'],2);
					}
				}
				elseif ($currencyId==CURRENCY_USD){
					if ($uniqueClients[$uc]['Order'][$q]['Currency']['id']==CURRENCY_USD){
						$quotationTotal+=$uniqueClients[$uc]['Order'][$q]['total_price'];
					}
					elseif ($uniqueClients[$uc]['Order'][$q]['Currency']['id']==CURRENCY_CS){
						//added calculation of totals in USD
						$quotationTotal+=round($uniqueClients[$uc]['Order'][$q]['total_price']/$uniqueClients[$uc]['Order'][$q]['exchange_rate'],2);
					}
				}
        */
			}
			$uniqueClients[$uc]['Client']['order_total']=$orderTotal;
		}
    //switch ($aggregateOptionId){
    //  case AGGREGATES_NONE:
    //    //usort($uniqueClients,array($this,'sortByCompanyName'));
    //    break;
    //  case AGGREGATES_INVOICES_QUOTATIONS:
    //    usort($uniqueClients,array($this,'sortByInvoiceTotalQuotationTotalCompanyName'));
    //    break;
    //  case AGGREGATES_QUOTATIONS_INVOICES:
    //   usort($uniqueClients,array($this,'sortByQuotationTotalInvoiceTotalCompanyName'));
    //    break;
    //}
    usort($uniqueClients,array($this,'sortByOrderTotalCompanyName'));
        
		$this->set(compact('uniqueClients'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_client_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_edit_permission'));
    //echo "bool_client_edit_permission is ".$bool_client_edit_permission."<br/>";
		
		//$this->loadModel('Currency');
		//$currencies=$this->Currency->find('list');
		//$this->set(compact('currencies'));
	}
	
  public function sortByOrderTotalCompanyName($a,$b ){ 
		if( $a['Client']['order_total'] == $b['Client']['order_total'] ){ 			
      if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
        return 0 ; 
      }
      else {
        return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
      }
		} 
		return ($a['Client']['order_total'] < $b['Client']['order_total']) ? 1 : -1;
	}
  
	public function add() {
    $this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
		$clients = $this->ThirdParty->find('all',array(
			'fields'=>array('ThirdParty.id','ThirdParty.company_name'),
			'conditions'=>array(
				'ThirdParty.bool_active'=>true,
        'ThirdParty.bool_provider'=>false,
			),
			'order'=>'ThirdParty.company_name',
		));
		$this->set(compact('clients'));
		
		if ($this->request->is('post')) {
      $datasource=$this->User->getDataSource();
			$datasource->begin();
			try {
				//pr($this->request->data);
				$this->User->create();
				if (!$this->User->save($this->request->data)) {
					echo "Problema guardando el usuario";
					pr($this->validateErrors($this->User));
					throw new Exception();
				}
				$user_id=$this->User->id;
				if (!empty($this->request->data['Client'])){
					for ($c=0;$c<count($this->request->data['Client']);$c++){
						if ($this->request->data['Client'][$c]['id']){
							$clientUserArray=[];
							$this->User->ClientUser->create();
							$clientUserArray['ClientUser']['client_id']=$clients[$c]['ThirdParty']['id'];
							$clientUserArray['ClientUser']['user_id']=$user_id;
							if (!$this->User->ClientUser->save($clientUserArray)){
								echo "Problema guardando el cliente para el usuario";
								pr($this->validateErrors($this->User->ClientUser));
								throw new Exception();
							}
						}
					}
				}
				$datasource->commit();
				$this->recordUserAction($this->User->id,null,null);
				$this->recordUserActivity($this->Session->read('User.username'),"Se registró el usuario ".$this->request->data['User']['username']);
				$this->Session->setFlash(__('The user has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
				$this->recordUserActivity($this->Session->read('User.username'),"Tried to add user unsuccessfully");
			}
		}
		$roles = $this->User->Role->find('list');
		$this->set(compact('roles'));
		
    $roles = $this->User->Role->find('list',['order'=>'list_order ASC']);
		$this->ThirdParty->recursive=-1;
    $this->set(compact('roles'));
	}

	public function edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		
    $this->loadModel('ClientUser');
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
		$clients = $this->ThirdParty->find('all',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>[
				'ThirdParty.bool_provider'=>false,
				'ThirdParty.bool_active'=>true,
			],
			'contain'=>[
				'ClientUser'=>[
					'conditions'=>[
						'ClientUser.user_id'=>$id,
					],
					'order'=>'ClientUser.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',
		]);
		$this->set(compact('clients'));
    
		$this->User->recursive=-1;
		
		if ($this->request->is(array('post', 'put'))) {
			$this->User->id=$id;
			$this->request->data['User']['password']=$this->request->data['User']['pwd'];
			
      $datasource=$this->User->getDataSource();
      $datasource->begin();
      try {
        $this->User->id=$id;
        if (!$this->User->save($this->request->data['User'])) {
          echo "Problema guardando el usuario";
          //pr($this->validateErrors($this->User));
          throw new Exception();
        }
        $user_id=$this->User->id;
        if ($this->request->data['User']['bool_active']){
          if (!empty($this->request->data['Client'])){
            $currentDateTime=new DateTime();
            for ($c=0;$c<count($this->request->data['Client']);$c++){						
              $clientUserArray=[];
              $this->User->ClientUser->create();
              $clientUserArray['ClientUser']['client_id']=$clients[$c]['ThirdParty']['id'];
              $clientUserArray['ClientUser']['user_id']=$user_id;
              $clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
              $clientUserArray['ClientUser']['bool_assigned']=$this->request->data['Client'][$c]['id'];
              if (!$this->User->ClientUser->save($clientUserArray)){
                echo "Problema guardando el cliente para el usuario";
                pr($this->validateErrors($this->User->ClientUser));
                throw new Exception();
              }
            }
          }
        }
        else {
          $this->User->ClientUser->recursive=-1;
          $previousClientUsers=$this->User->ClientUser->find('all',[
            'fields'=>['ClientUser.id'],
            'conditions'=>[
              'ClientUser.user_id'=>$id,
            ],
          ]);
          if (!empty($previousClientUsers)){
            foreach ($previousClientUsers as $previousClientUser){
              $this->User->ClientUser->id=$previousClientUser['ClientUser']['id'];
              $this->User->ClientUser->delete($previousClientUser['ClientUser']['id']);
            }
          }
        }
        $datasource->commit();
          $this->recordUserAction($this->User->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró el usuario ".$this->request->data['User']['username']);
          $this->Session->setFlash(__('The user has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'index']);
      } 
      catch(Exception $e){
        $datasource->rollback();
        //pr($e);
        $this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        $this->recordUserActivity($this->Session->read('User.username'),"Tried to add user unsuccessfully");
      }
		} 
		else {
			$options = ['conditions' => ['User.id'=> $id]];
			$this->request->data = $this->User->find('first', $options);
      $this->request->data['User']['pwd'] = $this->request->data['User']['password'];
		}
		$clientIdsAssociatedWithUser=[];		
		foreach ($clients as $client){
			$boolAssociatedWithUser=$this->ClientUser->checkAssociationClientWithUser($client['ThirdParty']['id'],$id);
			if ($boolAssociatedWithUser){
				$clientIdsAssociatedWithUser[]=$client['ThirdParty']['id'];
			}
		}
		
		//pr($clientIdsAssociatedWithUser);
		$clientsAssociatedWithUser=$this->ThirdParty->find('all',[
			'conditions'=>[
				'ThirdParty.id'=>$clientIdsAssociatedWithUser,
			],
			'order'=>'ThirdParty.company_name',
		]);
		$this->set(compact('clientsAssociatedWithUser'));
		$roles = $this->User->Role->find('list',['order'=>'list_order ASC']);
		$this->set(compact('roles'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('The user has been deleted.'));
		} else {
			$this->Session->setFlash(__('The user could not be deleted. Please, try again.'));
		}
		$this->recordUserActivity($this->Session->read('User.username'),"Deleted user with id ".$id);
		return $this->redirect(array('action' => 'index'));
	}

	public function rolePermissions(){
		$this->loadModel('Role');
		
		$roles=$this->Role->find('all',['conditions'=>['Role.id !='=>[ROLE_ADMIN,ROLE_CLIENT,ROLE_ACCOUNTING]]]);
		//pr($roles);
		$this->set(compact('roles'));
		
		$consideredControllerAliases=[
			'Orders',
      'TankMeasurements',
      'HoseMeasurements',
      'PaymentReceipts',
      'HoseCounters',
			'StockItems',
      'StockMovements',
      'ProductPriceLogs',
      
      'PurchaseOrders',
      
      'ClosingDates',
			
      'CashReceipts',
      'Cheques',
			'Transfers',
			'ExchangeRates',
			'AccountingCodes',
			'AccountingRegisterTypes',
			'AccountingRegisters',
      'Invoices',
			
			//'ProductCategories',
      'ProductTypes',
			'Products',
			'ThirdParties',
      //'Clients',
			//'Contacts',
			
      'Hoses',
			'Islands',
      'Tanks',
			
			'Operators',
			'Shifts',
      
      'Users',
      'Enterprises',
			'Employees',
			'EmployeeHolidays',
			'HolidayTypes',
      
      'Constants',
      'Units',
      'PaymentModes',
			
			//'PurchaseEstimations',
			//'ClientRequests',
      
		];
		
		$selectedControllers=$this->Acl->Aco->find('all',[
			'conditions'=>[
				'Aco.parent_id'=>'1',
				'Aco.alias'=>$consideredControllerAliases,
			],
		]);
		//pr($selectedControllers);
		
		$excludedActions=[
			'controllers',
			'recordUserActivity',
			'userhome',
			'get_date',
			'recordUserAction',
			'uploadFiles',
			'hasPermission',
			'recreateStockItemLogs',
			'saveAccountingRegisterData',
			'normalizeChars',
			
			'create_pdf', // for orders
			'downloadPdf', // for orders
			'sortByTotalForClient', // for orders
			'setduedate', // for orders
      
      'guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones', // for orders
      'guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones', // for orders
      'setduedate', // for orders
      'setduedate', // for orders
			
      'getpendinginvoicesforclient', // for invoices
      'guardarClientesPorCobrar', // for invoices
      'guardarFacturasPorCobrar', // for invoices
      'guardarHistorialPagos', // for invoices
      'changePaidStatus', // for invoices
      
			'getrawmaterialid', // for production runs
			
			'compareArraysByDate', // for products
			'getproductcategoryid', // for products
			'getproductpackagingunit', // for products
			'getmachines', // for products
      'getdefaultcost', // for products
			
			'cuadrarEstadosDeLote',// for stock items
			'recreateStockItemLogsForSquaring',// for stock items
			'recreateAllStockItemLogs',// for stock items
			'recreateAllBottleCosts',// for stock items
			'recreateStockItemPriceForSquaring',// for stock items
			'cuadrarPreciosBotellas',// for stock items
			'recreateProductionMovementPriceForSquaring',// for stock items
			'sortByFinishedProduct',// for stock items
			'sortByRawMaterial',// for stock items
      'getStockItemInfo',// for stock items
      'saveStockItemInfo',// for stock items
      
      'viewStockentry',// for stock movements
			'addStockentry',// for stock movements
			'addStockremoval',// for stock movements
			'guardarReporteCompraVenta',// for stock movements
			'viewStockremoval',// for stock movements
			'guardarReporteVentaProductoPorCliente',// for stock movements
      'guardarResumenAjustesInventario',// for stock movements
      'guardarReporteEstimacionesComprasPorCliente',// for stock movements
      
      'guardarReporteProduccionMeses',// for production movements
      
      'guardarKardex',// for stock movements
      'sortByMovementDate',// for stock movements
      'registeradjustmentmovement',// for stock movements
      'registerbottleadjustmentmovements',// for stock movements
      'guardarEstimacionesComprasPorCliente',// for stock movements
      
			'getcreditdays',// for third parties
      'getcreditstatus',// for third parties
      'sortByCompanyName', // for third parties
      'sortByOrderTotalCompanyName', // for third parties
      
      'sortByOrderTotalCompanyName', // for third parties
      'saveclient', // for third parties
      'saveexistingclient', // for third parties
      'getclientlist', // for third parties
      'getclientlistforclientname', // for third parties
      'getclientinfo', // for third parties
      'getprovidercreditdays', // for third parties
      'sortByPurchaseOrderTotalCompanyName', // for third parties
      'guardarResumenProveedores', // for third parties
      'guardarAsociacionesClientesEmpresas', // for third parties
			
			'getaccountsaldo',// for accounting codes
			'getaccountingcodenature',// for accounting codes
			'indexOriginal',
			'viewOriginal',// for accounting codes
			'loadCodes',// for accounting codes
			'getaccountingcodeforparent',// for accounting codes
			'getaccountingcodename',// for accounting codes
			
			'getaccountingregistercode', // for accounting registers
			'cuadrarAccountingRegisters', // for accounting registers
			'guardarResumenAsientosContablesProblemas', // for accounting registers
			'getaccountingcodedescription', // for accounting registers
			'calculateResultState', // for accounting registers
			
			'getchequenumber', // for cheques
			
			'getexchangerate', // for exchange rates
			
			'login', // for users
			'logout', // for users
      'sortByOrderTotalCompanyName', // for users
			'init_DB_permissions',
			'rolePermissions',
      
      'getnewclientrequestcode',// for client requests
      'getnewpurchaseestimationcode',// for purchase estimations
      
      'verVentasPorCliente', // for orders, because this is not linked to in the menu 20190116
      
      'guardarReporteCierre', // for orders
      'guardarReporteVentasCliente', // for orders
      'guardarResumenVentasRemisiones', // for orders
      'guardarResumenComprasRealizadas', // for orders
      
      'guardarResumenOrdenesDeProduccion', // for production runs
      
      'guardarReporteProductoFabricado', // for products
      'guardarReporteSalidasMateriaPrima', // for products
      
      'guardarReporteProductoMateriaPrima', // for stock items
      'guardarReporteProductos', // for stock items
      'guardarReporteInventario', // for stock items
      'guardarReporteReclasificaciones', // for stock items
      'guardarReporteProduccionDetalle', // for stock items
      'guardarDetalleCostoProducto', // for stock items
      'guardarReporteEstado', // for stock items
      
      'guardarResumenClientes','guardarAsociacionesClientesUsuarios', // for third parties
      
      'guardarReporteCaja', // for accounting codes
      
      'guardarEstadoResultados','guardarBalanceGeneral', // for accounting registers
      
      'guardarResumenCheques', // for cheques
      
      'guardarAsociacionesEmpresasUsuarios', // for enterprises
      
      'guardarResumenRecibos', // for payment receipts
      'getpendingfuelbondsforclient', // for payment receipts
      
      'guardarCuentasPorPagar', // for invoices
      'getInvoiceCode', // for invoices
      'saveInvoice', // for invoices
      'invoiceInputsForPaymentReceipt', // for invoices
      'invoicesTableForPaymentReceipt', // for invoices
      'getPendingInvoicesForClient', // for invoices
      
     
      'guardarResumen', // for incidences
      'guardarReporteIncidencias', // for incidences;
      
      'guardarResumen', // for purchase estimations
      'guardarResumen',  // for purchase orders
      'guardarProveedoresPorPagar',  // for purchase orders
      'guardarFacturasPorPagar',  // for purchase orders
      
      'sortByDateDescending', //for transfers
      
      'verPdfEntrada',
      'verPdfVenta',
      'verPdfRemision',
      'verPdfEntradaSuministros',
      'pdf',
      'verPdfHojaInventario',
      'pdf',
      'verPdfHojaVacaciones',
      'verPdf',
		];
		
		for ($c=0;$c<count($selectedControllers);$c++){
			$selectedActions=[];
      $conditions=[
        'Aco.parent_id'=>$selectedControllers[$c]['Aco']['id'],
        'Aco.alias !='=>$excludedActions,
      ];
      $controllerName=$selectedControllers[$c]['Aco']['alias'];
      //if ($controllerName=='Orders'){
      //  $conditions[]=['Aco.alias !='=>['guardarReporteCierre','guardarReporteVentasCliente','guardarResumenVentasRemisiones','guardarResumenComprasRealizadas']];
      //}
      // elseif ($controllerName=='ProductionRuns'){
      //  $conditions[]=['Aco.alias !='=>['guardarResumenOrdenesDeProduccion']];
      //}
      //elseif ($controllerName=='Products'){
      //  $conditions[]=['Aco.alias !='=>['guardarReporteProductoFabricado','guardarReporteSalidasMateriaPrima']];
      //}
      //if ($controllerName=='StockItems'){
      //  $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete','guardarReporteProductoMateriaPrima','guardarReporteProductos','guardarReporteInventario','guardarReporteReclasificaciones','guardarReporteProduccionDetalle','guardarDetalleCostoProducto',]];
      //}
      if ($controllerName=='StockItems'){
        $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete',]];
      }
      elseif ($controllerName=='StockMovements'){
        $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete',]];
      }
      elseif ($controllerName=='ProductionMovements'){
        $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete',]];
      }
      elseif ($controllerName=='ThirdParties'){
        //$conditions[]=['Aco.alias !='=>['delete','guardarResumenClientes','guardarAsociacionesClientesUsuarios',]];
        $conditions[]=['Aco.alias !='=>['delete',]];
      }
      elseif ($controllerName=='Invoices'){
        $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete']];
      }
      //elseif ($controllerName=='AccountingCodes'){
      //  $conditions[]=['Aco.alias !='=>['guardarReporteCaja',]];
      //}
      //elseif ($controllerName=='AccountingRegisters'){
      //  $conditions[]=['Aco.alias !='=>['guardarEstadoResultados','guardarBalanceGeneral',]];
      //}
      //elseif ($controllerName=='Cheques'){
      // $conditions[]=['Aco.alias !='=>['guardarResumenCheques',]];
      //}
      
      //elseif ($controllerName=='Incidences'){
      //  $conditions[]=['Aco.alias !='=>['guardarResumen','guardarReporteIncidencias']];
      //}
      //elseif ($controllerName=='PurchaseEstimations'){
      //  $conditions[]=['Aco.alias !='=>['guardarResumen']];
      //}
      //elseif ($controllerName=='PurchaseOrders'){
      //  $conditions[]=['Aco.alias !='=>['guardarResumen']];
      //}
				
			$selectedActions=$this->Acl->Aco->find('all',[
				'conditions'=>$conditions,
			]);
			if (!empty($selectedActions)){
				for ($a=0;$a<count($selectedActions);$a++){
					$rolePermissions=[];
					for ($r=0;$r<count($roles);$r++){
						$aco_name=$selectedControllers[$c]['Aco']['alias']."/".$selectedActions[$a]['Aco']['alias'];
						//pr($aco_name);
						$hasPermission=$this->Acl->check(['Role'=>['id'=>$roles[$r]['Role']['id']]],$aco_name);
						//if ($selectedActions[$a]['Aco']['id']==15){
						//	echo "permission for ".$aco_name." is ".$hasPermission."<br/>";
						//}
						if ($hasPermission){
							$rolePermissions[$r]=$hasPermission;
						}
						else {
							$rolePermissions[$r]=0;
						}						
					}
					//if ($selectedActions[$a]['Aco']['id']==15){
					//	pr($rolePermissions);
					//}
					$selectedActions[$a]['rolePermissions']=$rolePermissions;
				}
			}
			//pr($selectedActions);
			
			$selectedControllers[$c]['actions']=$selectedActions;
		}
		$this->set(compact('selectedControllers'));
		//pr($selectedControllers);
		if ($this->request->is('post')) {
      //pr($this->request->data);
			$role = $this->User->Role;
			for ($r=0;$r<count($this->request->data['Role']);$r++){
				$thisRole=$roles[$r];
				
        //pr($role);
				$role_id=$thisRole['Role']['id'];
				
				$role->id=$role_id;
				
				for ($c=0;$c<count($this->request->data['Role'][$r]['Controller']);$c++){
					$controller=$selectedControllers[$c];
					//pr($controller);
					$controller_alias=$controller['Aco']['alias'];
					//if ($controller['Aco']['id']==992){
						//pr($role_id);
					//}
					for ($a=0;$a<count($this->request->data['Role'][$r]['Controller'][$c]['Action']);$a++){
						//if ($controller['Aco']['id']==992){
              //pr($this->request->data['Role'][$r]['Controller'][$c]['Action'][$a]);
						//}
						$action=$selectedControllers[$c]['actions'][$a];
						$action_alias=$action['Aco']['alias'];
            //if ($controller['Aco']['id']==992){
              //pr($action_alias);
            //}
						
						if ($this->request->data['Role'][$r]['Controller'][$c]['Action'][$a]){
              //if ($controller['Aco']['id']==992){
                //echo "allowing action alias ".$action_alias."<br/>";
              //}
							$this->Acl->allow($role, 'controllers/'.$controller_alias."/".$action_alias);
						}
						else {
              //if ($controller['Aco']['id']==992){
                //echo "denying action alias ".$action_alias."<br/>";
              //}
							$this->Acl->deny($role, 'controllers/'.$controller_alias."/".$action_alias);
						}
						$this->Session->setFlash(__('Los permisos se guardaron.'),'default',array('class' => 'success'));
						//$role->id = 5;
						//$this->Acl->allow($role, 'controllers');
						//$this->Acl->deny($role, 'controllers/ProductionResultCodes');
						//$this->Acl->deny($role, 'controllers/StockMovementTypes');
						//$this->Acl->deny($role, 'controllers/Role');			
					}					
				}				
			}
			/*
			$this->Client->create();
			if ($this->Client->save($this->request->data)) {
				$this->Session->setFlash(__('The client has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The client could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
			*/
      for ($c=0;$c<count($selectedControllers);$c++){
        $selectedActions=[];
        $conditions=[
          'Aco.parent_id'=>$selectedControllers[$c]['Aco']['id'],
          'Aco.alias !='=>$excludedActions,
        ];
        $controllerName=$selectedControllers[$c]['Aco']['alias'];
        if ($controllerName=='StockItems'){
          $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete',]];
        }
        elseif ($controllerName=='ThirdParties'){
          //$conditions[]=['Aco.alias !='=>['delete','guardarResumenClientes','guardarAsociacionesClientesUsuarios',]];
          $conditions[]=['Aco.alias !='=>['delete',]];
        }
        elseif ($controllerName=='Invoices'){
          $conditions[]=['Aco.alias !='=>['index','view','add','edit','delete']];
        }
          
        $selectedActions=$this->Acl->Aco->find('all',[
          'conditions'=>$conditions,
        ]);
        if (!empty($selectedActions)){
          for ($a=0;$a<count($selectedActions);$a++){
            $rolePermissions=[];
            for ($r=0;$r<count($roles);$r++){
              $aco_name=$selectedControllers[$c]['Aco']['alias']."/".$selectedActions[$a]['Aco']['alias'];
              //pr($aco_name);
              $hasPermission=$this->Acl->check(['Role'=>['id'=>$roles[$r]['Role']['id']]],$aco_name);
              if ($selectedControllers[$c]['Aco']['id']==992){
                //echo "permission for ".$aco_name." and role id ".$roles[$r]['Role']['id']." is ".$hasPermission."<br/>";
              }
              if ($hasPermission){
                $rolePermissions[$r]=$hasPermission;
              }
              else {
                $rolePermissions[$r]=0;
              }						
            }
            //if ($selectedActions[$a]['Aco']['id']==15){
            //	pr($rolePermissions);
            //}
            $selectedActions[$a]['rolePermissions']=$rolePermissions;
          }
        }
        //pr($selectedActions);
        
        $selectedControllers[$c]['actions']=$selectedActions;
      }
      $this->set(compact('selectedControllers'));
    //pr($selectedControllers);
		}
		
		
	}
	
	public function init_DB_permissions() {
	
		$role = $this->User->Role;
	/*
		// Allow admins to access everything
		$role->id = 4;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		
		// Allow assistants to access everything but leave out editing rights in the views and controllers
		$role->id = 5;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		
		// Allow assistants to access everything but leave out editing rights in the views and controllers
		$role->id = 6;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		*/
		// we add an exit to avoid an ugly "missing views" error message
		echo "all done";
		exit;
	
	}

}
