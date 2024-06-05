<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ThirdPartiesController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel');

	/*
	public $helpers = array('Combobox'); 

    function autoComplete() { 
        Configure::write('debug', 0); 
         
        $this->set('values', $this->Stuff->find('all', array( 
                    'conditions' => array( 
                        'location_id LIKE' => $this->data['Stuff']['location_id'].'%' 
                    ), 
                    'fields' => array('location_id'), 
                    'order' => 'location_id' 
        ))); 
        $this->layout = 'ajax'; 
    }
	*/	
  
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('saveclient','saveexistingclient','getclientlist','getclientlistforclientname','getclientinfo','getcreditdays','getprovidercreditdays','getcreditstatus');		
	}
	
  public function saveclient() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$clientid=trim($_POST['clientid']);
		$boolnewclient=($_POST['boolnewclient']=="true");
		
		$clientname=trim($_POST['clientname']);
		$clientruc=trim($_POST['clientruc']);
		$clientaddress=trim($_POST['clientaddress']);
		$clientphone=trim($_POST['clientphone']);
		$clientcell=trim($_POST['clientcell']);
		//20170821 ADDED CHECK ON EXISTING CLIENTS
    $boolClientOK=true;
    if ($boolnewclient){
      $this->Client->recursive=-1;
      $existingClientsWithThisName=$this->Client->find('all',array(
        'fields'=>array('Client.id'),
        'conditions'=>array(
          'Client.name'=>$clientname,
        ),
      ));
      if (!empty($existingClientsWithThisName)){
        $boolClientOK=false;
      }
    }
    if (!$boolClientOK){
      return "Ya existe un cliente con el mismo nombre, seleccione de la lista";
    }
    else {
      $datasource=$this->Client->getDataSource();
      $datasource->begin();
      try {
        $currentDateTime=new DateTime();
        //pr($this->request->data);
        $clientArray=array();
        $clientArray['Client']['name']=$clientname;
        $clientArray['Client']['ruc']=$clientruc;
        $clientArray['Client']['address']=$clientaddress;
        $clientArray['Client']['phone']=$clientphone;
        $clientArray['Client']['cell']=$clientcell;
        $clientArray['Client']['bool_active']=true;
        $clientArray['Client']['creating_user_id']=$this->Auth->User('id');
        if ($boolnewclient){
          $this->Client->create();
        }
        else {
          $this->Client->id=$clientid;
          //pr($clientid);
          if (!$this->Client->exists($clientid)) {
            throw new Exception(__('Cliente inválido'));
          }				
        }
        if (!$this->Client->save($clientArray)) {
          echo "Problema guardando el cliente";
          pr($this->validateErrors($this->Client));
          throw new Exception();
        }
        $client_id=$this->Client->id;

        $this->loadModel('ClientUser');
        $this->ClientUser->create();
        $clientUserData=array();
        $clientUserData['ClientUser']['client_id']=$client_id;
        $clientUserData['ClientUser']['user_id']=$this->Auth->User('id');
        $clientUserData['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
        $clientUserData['ClientUser']['bool_assigned']=true;
        if (!$this->ClientUser->save($clientUserData)) {
          echo "Problema guardando la asociación entre cliente y usuario";
          pr($this->validateErrors($this->ClientUser));
          throw new Exception();
        }
        
        $datasource->commit();
      
        $this->recordUserAction($this->Client->id,"add",null);
        $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$clientname);
        
        $this->Session->setFlash(__('The client has been saved.'),'default',array('class' => 'success'));
        //return $this->redirect(array('action' => 'index'));
        return true;
      } 
      catch(Exception $e){
        $datasource->rollback();
        //pr($e);
        $this->Session->setFlash(__('The client could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
        return false;
      }
    }
  }
	
  public function saveexistingclient() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$clientId=trim($_POST['clientid']);
		
		$clientEmail=trim($_POST['clientemail']);
    $clientPhone=trim($_POST['clientphone']);
		$clientAddress=trim($_POST['clientaddress']);
		$clientRucNumber=trim($_POST['clientrucnumber']);
    
		$datasource=$this->ThirdParty->getDataSource();
    $datasource->begin();
    
    try {
      $currentDateTime=new DateTime();
      //pr($this->request->data);
      $clientArray=[];
      $clientArray['ThirdParty']['email']=$clientEmail;
      $clientArray['ThirdParty']['phone']=$clientPhone;
      $clientArray['ThirdParty']['address']=$clientAddress;
      $clientArray['ThirdParty']['ruc_number']=$clientRucNumber;
      
      $this->ThirdParty->id=$clientId;
        //pr($clientid);
      if (!$this->ThirdParty->exists($clientId)) {
        throw new Exception(__('Cliente inválido'));
      }				
      if (!$this->ThirdParty->save($clientArray)) {
        echo "Problema guardando el cliente";
        pr($this->validateErrors($this->ThirdParty));
        throw new Exception();
      }
      $datasource->commit();
    
      $this->recordUserAction($this->ThirdParty->id,"edit",null);
      $this->recordUserActivity($this->Session->read('User.username'),"Se editó el cliente con id ".$clientId);
      
      $this->Session->setFlash(__('The client has been saved.'),'default',['class' => 'success']);
      //return $this->redirect(array('action' => 'index'));
      return true;
    } 
    catch(Exception $e){
      $datasource->rollback();
      //pr($e);
      $this->Session->setFlash(__('No se podía guardar el cliente.'), 'default',['class' => 'error-message']);
      return false;
    }
  }
  
	public function getclientlist() {
		$this->layout = "ajax";
		
		$this->Client->recursive=-1;
		$clients=$this->Client->find('all',array(
			'fields'=>array('Client.id','Client.name','Client.bool_active'),
			'order'=>'Client.name',
		));
		//pr($clients);
		$this->set(compact('clients'));
	}
	
	public function getclientlistforclientname() {
		$this->layout = "ajax";
		
		$clientval=trim($_POST['clientval']);
		
		$this->Client->recursive=-1;
		$clients=$this->Client->find('all',array(
			'fields'=>array('Client.id','Client.name','Client.bool_active'),
			'conditions'=>array(
				'Client.name LIKE'=> "%$clientval%",
			),
			'order'=>'Client.name',
		));
		//pr($clients);
		$this->set(compact('clients'));
	}
	
	public function getclientinfo() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$clientid=trim($_POST['clientid']);
		
		$this->ThirdParty->recursive=-1;
		$client=$this->ThirdParty->find('first',[
			'fields'=>['ThirdParty.id','ThirdParty.first_name','ThirdParty.last_name','ThirdParty.email','ThirdParty.phone','ThirdParty.address','ThirdParty.ruc_number'],
			'conditions'=>['ThirdParty.id'=> $clientid],
			//'contain'=>array(
			//	'CreatingUser'=>array(
			//		'fields'=>'username',
			//	),
			//	'Quotation'=>array(
			//		'fields'=>array(
			//			'Quotation.quotation_code',
			//		),	
			//		'order'=>'Quotation.quotation_date DESC',
			//		'limit'=>5,
			//	),
			//),
		]);
		return json_encode($client);
	}

	public function getcreditdays(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->autoRender=false;
		
		$clientid=trim($_POST['clientid']);
		
		if (!$clientid){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientid)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$clientid)));
		
		$creditperiod=0;
		if (!empty($client)){
			$creditperiod=$client['ThirdParty']['credit_days'];
		}
		
		return $creditperiod;
	}
  
  public function getprovidercreditdays(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->autoRender=false;
		
		$providerId=trim($_POST['providerid']);
		
		if (!$providerId){
			throw new NotFoundException(__('Proveedor no está presente'));
		}
		if (!$this->ThirdParty->exists($providerId)) {
			throw new NotFoundException(__('Proveedor inválido'));
		}
		
		$provider=$this->ThirdParty->find('first',['conditions'=>['ThirdParty.id'=>$providerId]]);
		$creditperiod=0;
		if (!empty($provider)){
			$creditperiod=$provider['ThirdParty']['credit_days'];
		}
		
		return $creditperiod;
	}
  
  public function getcreditstatus($clientid){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender=false;
		
		if (!$clientid){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientid)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
    $this->recursive=-1;
    $client=$this->ThirdParty->find('first', [
			'conditions'=>['ThirdParty.id'=>$clientid]
		]);
		//pr($client);
		$client['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($clientid);
    $client['ThirdParty']['credit_saldo']=$client['ThirdParty']['credit_amount']-$client['ThirdParty']['pending_payment'];
		
		return json_encode($client);
	}
	
	public function resumenClientes() {
		$this->ThirdParty->recursive = -1;
		$this->loadModel('ClientUser');
		$this->loadModel('ClientEnterprise');
    $this->loadModel('ExchangeRate');
		$this->loadModel('Order');
    
    $userId=$this->Auth->User('id');
		$userrole = $this->Auth->User('role_id');
		
    if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
			$userId=0;
		}
    
    $enterpriseId=0;
    
		$activeDisplayOptions=[
			'0'=>'Mostrar solamente clientes activos',
			'1'=>'Mostrar clientes activos y no activos',
			'2'=>'Mostrar clientes desactivados',
		];
		$this->set(compact('activeDisplayOptions'));
		$aggregateOptions=[
			'0'=>'No mostrar acumulados, ordenar por nombre cliente',
			'1'=>'Mostrar salidas, ordenado por salidas y cliente',
		];
		$this->set(compact('aggregateOptions'));
		
		define('SHOW_CLIENT_ACTIVE_YES','0');
		define('SHOW_CLIENT_ACTIVE_ALL','1');
		define('SHOW_CLIENT_ACTIVE_NO','2');
		
		define('AGGREGATES_NONE','0');
		define('AGGREGATES_ORDERS','1');
		
		$activeDisplayOptionId=SHOW_CLIENT_ACTIVE_YES;
		$searchTerm="";
		
    if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
			$aggregateOptionId=AGGREGATES_ORDERS;
		}
    else {
      $aggregateOptionId=AGGREGATES_NONE;
    }
    
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$userId=$this->request->data['Report']['user_id'];
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
			
			$activeDisplayOptionId=$this->request->data['Report']['active_display_option_id'];
			$aggregateOptionId=$this->request->data['Report']['aggregate_option_id'];
			
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
    $this->set(compact('enterpriseId'));
		$this->set(compact('activeDisplayOptionId','aggregateOptionId'));
		$this->set(compact('searchTerm'));
		
		$clientConditions=['ThirdParty.bool_provider'=> false];
    $clientUserConditions=[];
    
		if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT) { 
      // in this case the user_id is set to the logged in user explicitly
      // the clients are limited to those that have at least at one time been associated with the user
    	$clientUserIds=$this->ClientUser->find('list',[
				'fields'=>['ClientUser.client_id'],
				'conditions'=>[
          'ClientUser.user_id'=>$this->Auth->User('id'),
          'ClientUser.bool_assigned'=>true,
        ],
			]);
		
			$clientConditions['ThirdParty.id']=$clientUserIds;
      $clientUserConditions['ClientUser.user_id']=$this->Auth->User('id');
		}
		else {
      // case of an admin or assistant
			if ($userId>0){
				$clientUserIds=$this->ClientUser->find('list',[
					'fields'=>['ClientUser.client_id'],
					'conditions'=>[
            'ClientUser.user_id'=>$userId,
            'ClientUser.bool_assigned'=>true,
          ],
				]);
			
				$clientConditions['ThirdParty.id']=$clientUserIds;
        $clientUserConditions['ClientUser.user_id']=$userId;
			}
		}
    $clientEnterpriseConditions=[];
    if ($enterpriseId>0){
      $clientEnterpriseConditions['ClientEnterprise.enterprise_id']=$enterpriseId;
    }
		if ($activeDisplayOptionId!=SHOW_CLIENT_ACTIVE_ALL){
			if ($activeDisplayOptionId==SHOW_CLIENT_ACTIVE_YES){
				$clientConditions['ThirdParty.bool_active']=true;
			}
			else {
				$clientConditions['ThirdParty.bool_active']=false;
			}
		}
		
		if (!empty($searchTerm)){
			$clientConditions['ThirdParty.company_name LIKE']='%'.$searchTerm.'%';
		}
    
		$clientCount=	$this->ThirdParty->find('count', [
			'fields'=>['ThirdParty.id'],
			'conditions' => $clientConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $clientConditions,
			'contain'=>[
				'AccountingCode',
        'ClientUser'=>[
          'conditions' => $clientUserConditions,  
					'User',
					'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
          'limit'=>1,
				],
        'ClientEnterprise'=>[
          'conditions' => $clientEnterpriseConditions,  
					'Enterprise',
					'order'=>'ClientEnterprise.assignment_datetime DESC,ClientEnterprise.id DESC',
				],
        'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
            'Order.bool_annulled'=>false,
					],
				],
			],
			'order' => ['ThirdParty.company_name'=>'ASC'],
			'limit'=>($clientCount>0?$clientCount:1),
		];

		$allClients = $this->Paginator->paginate('ThirdParty');
    //pr($allClients);
    $clients=[];	
		for ($c=0;$c<count($allClients);$c++){
			$orderTotal=0;
			//TODO FIX NEEDED 20180603 NO SELECTION ON ASSIGNED YET
      //if (empty($userId)||$allClients[$c]['ClientUser'][0]['bool_assigned']){
        // 20180503 LATER A CHECK SHOULD BE ADDED TO ACCELERATE THE PAGE TO ONLY DO THE CALCULUS FOR THE CLIENTS THAT HAVE A RIGHT TO CREDIT
        $thisClient=$allClients[$c];
        $thisClient['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($thisClient['ThirdParty']['id']);
        
        for ($q=0;$q<count($thisClient['Order']);$q++){
          $orderTotal+=$thisClient['Order'][$q]['total_price'];
        }
        $thisClient['Client']['order_total']=$orderTotal;
        $clients[]=$thisClient;        
      //}  
    }
    
    $linkedEnterprises=[];
    $rejectedEnterprises=[];
    foreach($allClients as $client){
      if (!empty($client['ClientEnterprise'])){
        foreach ($client['ClientEnterprise'] as $clientEnterprise){
          //pr($clientEnterprise);
          if (!array_key_exists($clientEnterprise['enterprise_id'],$linkedEnterprises)&& !array_key_exists($clientEnterprise['enterprise_id'],$rejectedEnterprises)){
            if ($clientEnterprise['bool_assigned']){
              $linkedEnterprises[$clientEnterprise['enterprise_id']]=$clientEnterprise['Enterprise']['company_name'];
            }
            else {
              $rejectedEnterprises[$clientEnterprise['enterprise_id']]=$clientEnterprise['Enterprise']['company_name'];
            }
          }
        }
      }
    }
    //pr($linkedEnterprises);
    //pr($rejectedEnterprises);
    $this->set(compact('linkedEnterprises'));
    switch ($aggregateOptionId){
      case AGGREGATES_NONE:
        usort($clients,array($this,'sortByCompanyName'));
        break;
      case AGGREGATES_ORDERS:
        usort($clients,array($this,'sortByOrderTotalCompanyName'));
        break;
    }
    $this->set(compact('clients'));
    
    $this->loadModel('User');
		$userConditions=[];
		if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT){
			$userConditions=['User.id'=>$this->Auth->User('id')];
		}
		$users=$this->User->find('list',[
      'fields'=>'User.username',
			'conditions'=>$userConditions,
			'order'=>'User.username'
		]);
		$this->set(compact('users'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $enterprises=$this->ThirdParty->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
    
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
  
  public function sortByCompanyName($a,$b ){ 
		if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
			return 0 ; 
		} 
		return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
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
	
  public function guardarResumenClientes() {
		$exportData=$_SESSION['resumenClientes'];
		$this->set(compact('exportData'));
	}
  
	public function resumenProveedores() {
		$this->ThirdParty->recursive = -1;
    
    $userId=$this->Auth->User('id');
		$userrole = $this->Auth->User('role_id');
		if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
			$userId=0;
		}
    
		$activeDisplayOptions=[
			'0'=>'Mostrar solamente proveedores activos',
			'1'=>'Mostrar proveedores activos y no activos',
			'2'=>'Mostrar proveedores desactivados',
		];
		$this->set(compact('activeDisplayOptions'));
		$aggregateOptions=[
			'0'=>'No mostrar acumulados, ordenar por nombre proveedor',
			'1'=>'Mostrar ordenes de compra, ordenado por orden y proveedor',
		];
		$this->set(compact('aggregateOptions'));
		
		define('SHOW_PROVIDER_ACTIVE_YES','0');
		define('SHOW_PROVIDER_ACTIVE_ALL','1');
		define('SHOW_PROVIDE_ACTIVE_NO','2');
		
		define('AGGREGATES_NONE','0');
		define('AGGREGATES_ORDERS','1');
		
		$activeDisplayOptionId=SHOW_PROVIDER_ACTIVE_YES;
		$searchTerm="";
		
    if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
			$aggregateOptionId=AGGREGATES_ORDERS;
		}
    else {
      $aggregateOptionId=AGGREGATES_NONE;
    }
    
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			//$userId=$this->request->data['Report']['user_id'];
			
			$activeDisplayOptionId=$this->request->data['Report']['active_display_option_id'];
			$aggregateOptionId=$this->request->data['Report']['aggregate_option_id'];
			
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
		$this->set(compact('activeDisplayOptionId','aggregateOptionId'));
		$this->set(compact('searchTerm'));
		
		$providerConditions=['ThirdParty.bool_provider'=> true];
    
    if ($activeDisplayOptionId!=SHOW_PROVIDER_ACTIVE_ALL){
			if ($activeDisplayOptionId==SHOW_PROVIDER_ACTIVE_YES){
				$providerConditions['ThirdParty.bool_active']=true;
			}
			else {
				$providerConditions['ThirdParty.bool_active']=false;
			}
		}
		
		if (!empty($searchTerm)){
			$providerConditions['ThirdParty.company_name LIKE']='%'.$searchTerm.'%';
		}
    
		$providerCount=	$this->ThirdParty->find('count', [
			'fields'=>['ThirdParty.id'],
			'conditions' => $providerConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $providerConditions,
			'contain'=>[
				'AccountingCode',
        'Enterprise',
        'PurchaseOrder'=>[
					'conditions'=>[
						'PurchaseOrder.purchase_order_date >='=>$startDate,
						'PurchaseOrder.purchase_order_date <'=>$endDatePlusOne,
            'PurchaseOrder.bool_annulled'=>false,
					],
				],
			],
			'order' => ['ThirdParty.company_name'=>'ASC'],
			'limit'=>($providerCount>0?$providerCount:1),
		];
		$allProviders = $this->Paginator->paginate('ThirdParty');
    
    $providers=[];	
		for ($p=0;$p<count($allProviders);$p++){
			$orderTotal=0;
			//TODO FIX NEEDED 20180603 NO SELECTION ON ASSIGNED YET
      //if (empty($userId)||$allClients[$c]['ClientUser'][0]['bool_assigned']){
        // 20180503 LATER A CHECK SHOULD BE ADDED TO ACCELERATE THE PAGE TO ONLY DO THE CALCULUS FOR THE CLIENTS THAT HAVE A RIGHT TO CREDIT
        $thisProvider=$allProviders[$p];
        //$thisProvider['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($thisProvider['ThirdParty']['id']);
        
        for ($q=0;$q<count($thisProvider['PurchaseOrder']);$q++){
          $orderTotal+=$thisProvider['PurchaseOrder'][$q]['cost_total'];
        }
        $thisProvider['ThirdParty']['purchase_order_total']=$orderTotal;
        $providers[]=$thisProvider;        
      //}  
    }
    
    switch ($aggregateOptionId){
      case AGGREGATES_NONE:
        usort($providers,array($this,'sortByCompanyName'));
        break;
      case AGGREGATES_ORDERS:
        usort($providers,array($this,'sortByPurchaseOrderTotalCompanyName'));
        break;
    }
    
		$this->set(compact('providers'));
		
    $this->loadModel('User');
		$userConditions=[];
		if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT){
			$userConditions=['User.id'=>$this->Auth->User('id')];
		}
		$users=$this->User->find('list',[
      'fields'=>'User.username',
			'conditions'=>$userConditions,
			'order'=>'User.username'
		]);
		$this->set(compact('users'));
    
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}
	
  public function sortByPurchaseOrderTotalCompanyName($a,$b ){ 
		if( $a['ThirdParty']['purchase_order_total'] == $b['ThirdParty']['purchase_order_total'] ){ 			
      if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
        return 0 ; 
      }
      else {
        return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
      }
		} 
		return ($a['ThirdParty']['purchase_order_total'] < $b['ThirdParty']['purchase_order_total']) ? 1 : -1;
	}
	
  public function guardarResumenProveedores() {
		$exportData=$_SESSION['resumenProveedores'];
		$this->set(compact('exportData'));
	}
  
	public function verCliente($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$client=$this->ThirdParty->find('first', [
			'conditions'=>[
				'ThirdParty.id'=>$id,
			],
			'contain'=>[
        'AccountingCode',
        'CreditCurrency',
        'ClientUser'=>[
          'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
					'User',
				],
        'ClientEnterprise'=>[
					'Enterprise',
					'order'=>'ClientEnterprise.assignment_datetime DESC,ClientEnterprise.id DESC',
				],
				'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
					],
					'order'=>'order_date DESC'
				],
			]
		]);
		//pr($client);
		$client['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($id);
		$this->set(compact('client','startDate','endDate'));
		
    $userIdList=[];
    foreach ($client['ClientUser'] as $clientUser){
      if (!in_array($clientUser['user_id'],$userIdList)){
        $userIdList[]=$clientUser['user_id'];
      }
    }
    $this->loadModel('Users');
    $uniqueUsers=$this->User->find('all',[
      'conditions'=>['User.id'=>$userIdList],
      'contain'=>[					
        'ClientUser'=>[
          'conditions'=>['ClientUser.client_id'=>$id],
          'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
        ]
  		],
      'order'=>'User.username'
    ]);
    $this->set(compact('uniqueUsers'));
		
    $linkedEnterprises=[];
    $rejectedEnterprises=[];
    if (!empty($client['ClientEnterprise'])){
      foreach ($client['ClientEnterprise'] as $clientEnterprise){
        //pr($clientEnterprise);
        if (!array_key_exists($clientEnterprise['enterprise_id'],$linkedEnterprises)&& !array_key_exists($clientEnterprise['enterprise_id'],$rejectedEnterprises)){
          if ($clientEnterprise['bool_assigned']){
            $linkedEnterprises[$clientEnterprise['enterprise_id']]=$clientEnterprise['Enterprise']['company_name'];
          }
          else {
            $rejectedEnterprises[$clientEnterprise['enterprise_id']]=$clientEnterprise['Enterprise']['company_name'];
          }
        }
      }
    }
    $this->set(compact('linkedEnterprises'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteClient";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
    
    $aco_name="Users/edit";		
		$bool_user_edit_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_user_edit_permission'));
	}
	public function verProveedor($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$provider=$this->ThirdParty->find('first', [
			'conditions'=>[
				'ThirdParty.id'=>$id,
			],
			'contain'=>[
        'Enterprise',
				'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
					],
					'order'=>'order_date DESC'
				],
				'AccountingCode',
        'PurchaseOrder'=>[
					'conditions'=>[
						'PurchaseOrder.purchase_order_date >='=>$startDate,
						'PurchaseOrder.purchase_order_date <'=>$endDatePlusOne,
					],
					'order'=>'purchase_order_date DESC'
				],
			]
		]);
		//pr($client);
		
		$this->set(compact('provider','startDate','endDate'));
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteProvider";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}
	
	public function crearCliente() {
    $this->loadModel('AccountingCode');
		$this->loadModel('User');
    
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
    
		$this->User->recursive=-1;
		$users = $this->User->find('all',[
			'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'conditions'=>['User.bool_active'=>true],
      'order'=>'User.first_name,User.last_name,User.username',
		]);
		$this->set(compact('users'));
	
    $enterprises=$this->ThirdParty->Enterprise->find('all',[
      'fields'=>['Enterprise.id','Enterprise.company_name'],
      'conditions'=>['Enterprise.bool_active'=>true],
      'order'=>'Enterprise.company_name ASC'
    ]);
    $this->set(compact('enterprises'));
		
		if ($this->request->is('post')) {			
			$this->request->data['ThirdParty']['company_name']=trim(strtoupper($this->request->data['ThirdParty']['company_name']));
			$previousClientsWithThisName=[];
			$previousClientsWithThisName=$this->ThirdParty->find('all',[
				'conditions'=>['TRIM(UPPER(ThirdParty.company_name))'=>$this->request->data['ThirdParty']['company_name']],
			]);
			
			$allPreviousClients=$this->ThirdParty->find('all',[
				'fields'=>['ThirdParty.company_name'],
			]);
			
			$bool_similar=false;
			$similar_string="";
			foreach ($allPreviousClients as $existingClientName){
				similar_text($this->request->data['ThirdParty']['company_name'],$existingClientName['ThirdParty']['company_name'],$percent);
				if ($percent>80){
					$bool_similar=true;
					$similar_string=$existingClientName['ThirdParty']['company_name'];
				}
			}
			
			if (count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			else {	
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
          $this->AccountingCode->create();
          $accountingCodeArray=array();
          $accountingCodeArray['AccountingCode']['code']=$this->request->data['ThirdParty']['accounting_code_id'];
          $accountingCodeArray['AccountingCode']['description']=$this->request->data['ThirdParty']['company_name'];
          $accountingCodeArray['AccountingCode']['parent_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
          $accountingCodeArray['AccountingCode']['bool_main']=false;
          $accountingCodeArray['AccountingCode']['bool_creditor']=false;
          if (!$this->AccountingCode->save($accountingCodeArray)) {
            $this->Session->setFlash(__('No se podía guardar la cuenta contable para el cliente nuevo.'), 'default',array('class' => 'error-message'));
          }
          else {
            $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
            $this->ThirdParty->create();
            $this->request->data['ThirdParty']['bool_provider']=false;
            if (!$this->ThirdParty->save($this->request->data)) {
              echo "Problema guardando el cliente";
              pr($this->validateErrors($this->ThirdParty));
              throw new Exception();
            }
            $client_id=$this->ThirdParty->id;
            
            if (!empty($this->request->data['User'])){
              $currentDateTime=new DateTime();
              for ($u=0;$u<count($this->request->data['User']);$u++){
                $clientUserArray=array();
                $this->ThirdParty->ClientUser->create();
                $clientUserArray['ClientUser']['client_id']=$client_id;
                $clientUserArray['ClientUser']['user_id']=$users[$u]['User']['id'];
                $clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
                $clientUserArray['ClientUser']['bool_assigned']=$this->request->data['User'][$u]['id'];
                if (!$this->ThirdParty->ClientUser->save($clientUserArray)){
                  echo "Problema guardando el vendedor para el cliente";
                  pr($this->validateErrors($this->ThirdParty->ClientUser));
                  throw new Exception();
                }							
              }
            }
            
            if (!empty($this->request->data['Enterprise'])){
              $currentDateTime=new DateTime();
              for ($e=0;$e<count($this->request->data['Enterprise']);$e++){
                $clientEnterpriseArray=[];
                $this->ThirdParty->ClientEnterprise->create();
                $clientEnterpriseArray['ClientEnterprise']['client_id']=$client_id;
                $clientEnterpriseArray['ClientEnterprise']['enterprise_id']=$enterprises[$e]['Enterprise']['id'];
                $clientEnterpriseArray['ClientEnterprise']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
                $clientEnterpriseArray['ClientEnterprise']['bool_assigned']=$this->request->data['Enterprise'][$e]['id'];
                if (!$this->ThirdParty->ClientEnterprise->save($clientEnterpriseArray)){
                  echo "Problema guardando la empresa para el cliente";
                  pr($this->validateErrors($this->ThirdParty->ClientEnterprise));
                  throw new Exception();
                }							
              }
            }
          }
          
          $datasource->commit();
          $this->recordUserAction($this->ThirdParty->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['ThirdParty']['company_name']);
            
          $this->Session->setFlash(__('Se guardó el cliente.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumenClientes']);
        }      
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente. Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}
			}  
		}
		
		$lastClientAccountingCode=$this->AccountingCode->find('first',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
			),
			'order'=>'AccountingCode.code DESC',
		));
		$lastClientCode=$lastClientAccountingCode['AccountingCode']['code'];
		//pr($lastClientCode);
		$positionLastHyphen=strrpos($lastClientCode,"-");
		//echo $positionLastHyphen."<br/>";
		$clientCodeStart=substr($lastClientCode,0,($positionLastHyphen+1));
		$clientCodeEnding=substr($lastClientCode,($positionLastHyphen+1));
		//echo $clientCodeStart."<br/>";
		//echo $clientCodeEnding."<br/>";
		$newClientCodeEnding=str_pad($clientCodeEnding+1,3,'0',STR_PAD_LEFT);
		//echo $newClientCodeEnding."<br/>";
		$newClientCode=$clientCodeStart.$newClientCodeEnding;
		//echo $newClientCode."<br/>";
		$this->set(compact('newClientCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>false,
			),
			'order'=>'AccountingCode.code ASC',
		));
		$accountingCodes[$newClientCode]=$newClientCode;
		//pr($accountingCodes);
		
		$this->set(compact('accountingCodes'));
    
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $this->loadModel('Currency');
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
	public function crearProveedor() {
		$this->loadModel('AccountingCode');
		if ($this->request->is('post')) {
      if  (empty($this->request->data['ThirdParty']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del proveedor.'), 'default',['class' => 'error-message']);
      }
      else {
        $previousProvidersWithThisName=array();
        $previousProvidersWithThisName=$this->ThirdParty->find('all',[
          'conditions'=>[
            'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
          ],
        ]);
        
        $allPreviousProviders=$this->ThirdParty->find('all',[
          'fields'=>['ThirdParty.company_name'],
        ]);
        
        $bool_similar=false;
        $similar_string="";
        foreach ($allPreviousProviders as $existingProviderName){
          similar_text($this->request->data['ThirdParty']['company_name'],$existingProviderName['ThirdParty']['company_name'],$percent);
          if ($percent>80){
            $bool_similar=true;
            $similar_string=$existingProviderName['ThirdParty']['company_name'];
          }
        }
        
        if (count($previousProvidersWithThisName)>0){
          $this->Session->setFlash(__('Ya se introdujo un proveedor con este nombre!  No se guardó el proveedor.'), 'default',['class' => 'error-message']);
        }
        elseif ($bool_similar){
          $this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un proveedor existente: '.$similar_string.'!  No se guardó el proveedor.'), 'default',['class' => 'error-message']);
        }
        else {
          $datasource=$this->ThirdParty->getDataSource();
          $datasource->begin();
          try {
            $this->AccountingCode->create();
            $accountingCodeArray=[];
            $accountingCodeArray['AccountingCode']['code']=$this->request->data['ThirdParty']['accounting_code_id'];
            $accountingCodeArray['AccountingCode']['description']=$this->request->data['ThirdParty']['company_name'];
            $accountingCodeArray['AccountingCode']['parent_id']=ACCOUNTING_CODE_PROVIDERS;
            $accountingCodeArray['AccountingCode']['bool_main']=false;
            $accountingCodeArray['AccountingCode']['bool_creditor']=true;
            if (!$this->AccountingCode->save($accountingCodeArray)) {
              $this->Session->setFlash(__('No se podía guardar la cuenta contable para el proveedor nuevo.'), 'default',['class' => 'error-message']);
            }
            else {
              $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
              $this->ThirdParty->create();
              $this->request->data['ThirdParty']['bool_provider']=true;
              if (!$this->ThirdParty->save($this->request->data)) {
                echo "Problema guardando el proveedor";
                pr($this->validateErrors($this->ThirdParty));
                throw new Exception();
              }
              $provider_id=$this->ThirdParty->id;
            }
            $datasource->commit();  
            $this->recordUserAction($this->ThirdParty->id,"add",null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['ThirdParty']['company_name']);
              
            $this->Session->setFlash(__('The provider has been saved.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'resumenProveedores']);
          }      
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('No se podía guardar el proveedor.'), 'default',['class' => 'error-message']);
          }  
        }
      }
    }
		
		$lastProviderAccountingCode=$this->AccountingCode->find('first',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
			],
			'order'=>'AccountingCode.code DESC',
		]);
		$lastProviderCode=$lastProviderAccountingCode['AccountingCode']['code'];
		$positionLastHyphen=strrpos($lastProviderCode,"-");
		$providerCodeStart=substr($lastProviderCode,0,($positionLastHyphen+1));
		$providerCodeEnding=substr($lastProviderCode,($positionLastHyphen+1));
		$newProviderCodeEnding=str_pad($providerCodeEnding+1,3,'0',STR_PAD_LEFT);
		$newProviderCode=$providerCodeStart.$newProviderCodeEnding;
		$this->set(compact('newProviderCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
				//'AccountingCode.bool_main'=>false,
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$accountingCodes[$newProviderCode]=$newProviderCode;
		$this->set(compact('accountingCodes'));
    
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
		
    $this->loadModel('Currency');
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $enterprises=$this->ThirdParty->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
    
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}

	public function editarCliente($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    $this->loadModel('clientEnterprise');
    
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
  
    //$this->loadModel('ClientUser');
		$this->loadModel('User');
		$this->User->recursive=-1;
		/*
    $users = $this->User->find('all',[
			'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'conditions'=>['User.bool_active'=>true],
			'contain'=>[
				'ClientUser'=>[
					'conditions'=>['ClientUser.client_id'=>$id],
					'order'=>'ClientUser.id DESC',
				],
			],
			'order'=>'User.first_name,User.last_name,User.username',
		]);
		$this->set(compact('users'));
		//pr($users);
    */
    $enterprises=$this->ThirdParty->Enterprise->find('all',[
      'fields'=>['Enterprise.id','Enterprise.company_name'],
      'conditions'=>['Enterprise.bool_active'=>true],
      'contain'=>[
				'ClientEnterprise'=>[
					'conditions'=>['ClientEnterprise.client_id'=>$id],
					'order'=>'ClientEnterprise.id DESC',
				],
			],
      'order'=>'Enterprise.company_name ASC'
    ]);
    $this->set(compact('enterprises'));
		
		if ($this->request->is(array('post', 'put'))) {
			$previousClientsWithThisName=array();
			$previousClient=$this->ThirdParty->read(null,$id);
			
			$bool_similar=false;
			$similar_string="";
			
			if ($previousClient['ThirdParty']['company_name']!=$this->request->data['ThirdParty']['company_name']){
				$previousClientsWithThisName=$this->ThirdParty->find('all',array(
					'conditions'=>array(
						'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
					),
				));
				
				$allPreviousClients=$this->ThirdParty->find('all',array(
					'fields'=>array('ThirdParty.company_name'),
				));
				
				
				foreach ($allPreviousClients as $existingClientName){
					similar_text($this->request->data['ThirdParty']['company_name'],$existingClientName['ThirdParty']['company_name'],$percent);
					if ($percent>80){
						$bool_similar=true;
						$similar_string=$existingClientName['ThirdParty']['company_name'];
					}
				}
			}
			
			if (count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			else {
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
				  $this->ThirdParty->ClientUser->recursive=-1;
          
          $this->request->data['ThirdParty']['id']=$id;
          $this->request->data['ThirdParty']['bool_provider']=false;
          $this->ThirdParty->id=$id;
          if (!$this->ThirdParty->save($this->request->data)) {
						echo "Problema guardando el cliente";
						pr($this->validateErrors($this->ThirdParty));
						throw new Exception();
					}
					$client_id=$this->ThirdParty->id;
					
          /*
          if (!empty($this->request->data['User'])){
						$currentDateTime=new DateTime();					
						for ($u=0;$u<count($this->request->data['User']);$u++){
							//pr($this->request->data['User'][$u]);
							$clientUserArray=array();
							$this->ThirdParty->ClientUser->create();
							
							$clientUserArray['ClientUser']['client_id']=$client_id;
							$clientUserArray['ClientUser']['user_id']=$users[$u]['User']['id'];
							$clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
							$clientUserArray['ClientUser']['bool_assigned']=$this->request->data['User'][$u]['id'];
							if (!$this->ThirdParty->ClientUser->save($clientUserArray)){
								echo "Problema guardando el vendedor para el cliente";
								pr($this->validateErrors($this->ThirdParty->ClientUser));
								throw new Exception();
							}
						}
					}
          */
          //pr($this->request->data);
          if (!empty($this->request->data['Enterprise'])){
            $currentDateTime=new DateTime();
            $this->loadModel('ClientEnterprise');
            for ($e=0;$e<count($this->request->data['Enterprise']);$e++){
              $clientEnterpriseArray=[];
              $this->ClientEnterprise->create();
              $clientEnterpriseArray['ClientEnterprise']['client_id']=$client_id;
              $clientEnterpriseArray['ClientEnterprise']['enterprise_id']=$enterprises[$e]['Enterprise']['id'];
              $clientEnterpriseArray['ClientEnterprise']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
              $clientEnterpriseArray['ClientEnterprise']['bool_assigned']=$this->request->data['Enterprise'][$e]['id'];
              //pr($clientEnterpriseArray);
              if (!$this->ClientEnterprise->save($clientEnterpriseArray)){
                echo "Problema guardando la empresa para el cliente";
                pr($this->validateErrors($this->ClientEnterprise));
                throw new Exception();
              }
            }
          }
					
          $datasource->commit();
					$this->recordUserAction($this->ThirdParty->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó el cliente ".$this->request->data['ThirdParty']['company_name']);
					
					$this->Session->setFlash(__('Se guardó el cliente.'),'default',['class' => 'success']);
					//return $this->redirect(['action' => 'resumenClientes']);
        } 
				catch(Exception $e){
					$datasource->rollback();
					//pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente.'), 'default',['class' => 'error-message']);
				}  
			}
		} 
		else {
			$options = ['conditions' => ['ThirdParty.id'=> $id]];
			$this->request->data = $this->ThirdParty->find('first', $options);
		}
		$this->loadModel('AccountingCode');
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>false,
			),
			'order'=>'AccountingCode.code ASC',
		));
		$this->set(compact('accountingCodes'));
		
    $roleId = $this->Auth->User('role_id');
    //echo "roleId is ".$roleId."<br/>";
    $this->set(compact('roleId'));
    
    $this->loadModel('Currency');
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    /*
    $this->loadModel('User');
    $this->User->recursive=-1;  
    $allUsers=$this->User->find('all',[
      'fields'=>['User.id','User.id','User.username','User.first_name','User.last_name'],
      'order'=>'User.first_name ASC,User.last_name ASC,User.username ASC',
    ]);
    
    $usersAssociatedWithClient=[];
    foreach ($allUsers as $user){
      if ($this->ClientUser->checkAssociationClientWithUser($id,$user['User']['id'])){
        $usersAssociatedWithClient[]=$user;
      }
    }
    $this->set(compact('usersAssociatedWithClient'));
    */
    //$enterprises=$this->ThirdParty->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    //$this->set(compact('enterprises'));
    
    
    $this->ThirdParty->Enterprise->recursive=-1;  
    $allEnterprises=$this->ThirdParty->Enterprise->find('all',[
      'fields'=>['Enterprise.id','Enterprise.company_name'],
      'order'=>'Enterprise.company_name ASC',
    ]);
    
    $enterprisesAssociatedWithClient=[];
    foreach ($allEnterprises as $enterprise){
      if ($this->ThirdParty->ClientEnterprise->checkAssociationClientWithEnterprise($id,$enterprise['Enterprise']['id'])){
        $enterprisesAssociatedWithClient[]=$enterprise;
      }
    }
    $this->set(compact('enterprisesAssociatedWithClient'));
    //pr($enterprisesAssociatedWithClient);
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteClient";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
	public function editarProveedor($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
  
		if ($this->request->is(array('post', 'put'))) {
      if  (empty($this->request->data['ThirdParty']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del proveedor.'), 'default',['class' => 'error-message']);
      }
      else {
        $previousProvidersWithThisName=array();
        $previousProvider=$this->ThirdParty->read(null,$id);
        
        $bool_similar=false;
        $similar_string="";
        
        if ($previousProvider['ThirdParty']['company_name']!=$this->request->data['ThirdParty']['company_name']){
          $previousProvidersWithThisName=$this->ThirdParty->find('all',array(
            'conditions'=>array(
              'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
            ),
          ));
          
          $allPreviousProviders=$this->ThirdParty->find('all',array(
            'fields'=>array('ThirdParty.company_name'),
          ));
          
          foreach ($allPreviousProviders as $existingProviderName){
            similar_text($this->request->data['ThirdParty']['company_name'],$existingProviderName['ThirdParty']['company_name'],$percent);
            if ($percent>80){
              $bool_similar=true;
              $similar_string=$existingProviderName['ThirdParty']['company_name'];
            }
          }				
        }
        
        if (count($previousProvidersWithThisName)>0){
          $this->Session->setFlash(__('Ya se introdujo un proveedor con este nombre!  No se guardó el proveedor.'), 'default',array('class' => 'error-message'));
        }
        elseif ($bool_similar){
          $this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un proveedor existente: '.$similar_string.'!  No se guardó el proveedor.'), 'default',array('class' => 'error-message'));
        }
        else {								
          $datasource=$this->ThirdParty->getDataSource();
          $datasource->begin();
          try {
            $this->ThirdParty->ClientUser->recursive=-1;
            
            $this->request->data['ThirdParty']['id']=$id;
            $this->request->data['ThirdParty']['bool_provider']=true;
            $this->ThirdParty->id=$id;
            if (!$this->ThirdParty->save($this->request->data)) {
              echo "Problema guardando el proveedor";
              pr($this->validateErrors($this->ThirdParty));
              throw new Exception();
            }
            $client_id=$this->ThirdParty->id;
            
            
            $datasource->commit();
            $this->recordUserAction($this->ThirdParty->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se editó el proveedor ".$this->request->data['ThirdParty']['company_name']);
            
            $this->Session->setFlash(__('Se guardó el proveedor.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'resumenProveedores']);
          } 
          catch(Exception $e){
            $datasource->rollback();
            //pr($e);
            $this->Session->setFlash(__('No se podía guardar el proveedor.'), 'default',['class' => 'error-message']);
          }  
        }
      }
    } 
		else {
			$options = ['conditions' => ['ThirdParty.id' => $id]];
			$this->request->data = $this->ThirdParty->find('first', $options);
		}
		$this->loadModel('AccountingCode');
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
				//'AccountingCode.bool_main'=>false,
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$this->set(compact('accountingCodes'));
		
    $roleId = $this->Auth->User('role_id');
    //echo "roleId is ".$roleId."<br/>";
    $this->set(compact('roleId'));
    
    $enterprises=$this->ThirdParty->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
    
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteProvider";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The third party has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	public function deleteClient($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The client has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'resumenClientes'));
	}
	public function deleteProvider($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The provider has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'resumenProveedores'));
	}

  public function asociarClientesUsuarios($selectedClientId=0){
		$this->loadModel('ThirdParty');
		$this->loadModel('ClientUser');
		$this->loadModel('User');
		
		$this->ThirdParty->recursive=-1;
		$this->ClientUser->recursive=-1;
		$this->User->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedClientId=0;
		$selectedUserId=0;
   
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$selectedUserId=$this->request->data['ClientUser']['user_id'];
			$selectedClientId=$this->request->data['ClientUser']['client_id'];
			
			if (!empty($this->request->data['refresh'])){
        //$this->redirect(array('action' => 'asociarClientesUsuarios',$selectedClientId, 'page' => 1));
      }
      else {
				$currentDateTime=new DateTime();
				$datasource=$this->ClientUser->getDataSource();
				$datasource->begin();
				try {
					foreach ($this->request->data['Client'] as $clientId=>$clientValue){
						//pr($clientValue);
						if ($clientValue['bool_changed']){
							foreach ($clientValue['User'] as $userId=>$userValue){
								$clientUserArray=array();
								$clientUserArray['ClientUser']['client_id']=$clientId;
								$clientUserArray['ClientUser']['user_id']=$userId;
								$clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
								$clientUserArray['ClientUser']['bool_assigned']=$userValue['bool_assigned'];
								//pr($clientUserArray);
								$this->ClientUser->create();
								if (!$this->ClientUser->save($clientUserArray)){
									echo "Problema creando la asociación entre cliente y vendedor";
									pr($this->validateErrors($this->ClientUser));
									throw new Exception();
								}								
							}
						}					
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'asociarClientesUsuarios','clients');
					$this->recordUserActivity($this->Session->read('User.username'),"Se asignaron clientes a usuarios");
					$this->Session->setFlash(__('Se asignaron los clientes a los usuarios.'),'default',array('class' => 'success'));
					//return $this->redirect(array('action' => 'asociarClientesUsuarios'));
					//return $this->redirect($this->referer());
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían asignar los clientes a los usuarios.'), 'default',array('class' => 'error-message'));
					$this->recordUserActivity($this->Session->read('User.username')," intentó asignar clientes sin éxito");
				}
			}
		}
		
		$this->set(compact('selectedUserId'));
		$this->set(compact('selectedClientId'));
		
    //echo "selected user id is ".$selectedUserId."<br/>";
    
		$userConditions=['User.bool_active'=>true];
		if (!empty($selectedUserId)){
			$userConditions['User.id']=$selectedUserId;
		}
    //pr($userConditions);
		$selectedUsers=$this->User->find('list',[
			'fields'=>['User.id','User.username'],
			'conditions'=>$userConditions,
			'order'=>'User.username',			
		]);
		$this->set(compact('selectedUsers'));
		//pr($selectedUsers);
		$clientConditions=[
			'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>false,
		];
		if (!empty($selectedClientId)){
			$clientConditions['ThirdParty.id']=$selectedClientId;
		}
		
		$selectedClients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.id',
				'ThirdParty.company_name',
			],
			'conditions'=>$clientConditions,
			'contain'=>[
				'ClientUser'=>[
					'fields'=>[
						'ClientUser.id',
						'ClientUser.user_id',
						'ClientUser.bool_assigned',
						'ClientUser.assignment_datetime',
					],
					'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',
		]);

		for ($c=0;$c<count($selectedClients);$c++){
			$userArray=[];
			if (!empty($selectedClients[$c]['ClientUser'])){
				foreach ($selectedUsers as $userId=>$userValue){
					$userArray[$userId]=0;
					foreach ($selectedClients[$c]['ClientUser'] as $clientUser){
						if ($clientUser['user_id']==$userId){
							$userArray[$userId]=$clientUser['bool_assigned'];
							break;
						}
					}
				}
			}
			$selectedClients[$c]['Users']=$userArray;
		}
		$this->set(compact('selectedClients'));
		//pr($clients);
		
		$users=$this->User->find('list',[
			'fields'=>[
				'User.id',
				'User.username',
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('users'));
		
		$clients=$this->ThirdParty->find('list',[
			'fields'=>[
				'ThirdParty.id',
				'ThirdParty.company_name',
			],
			'conditions'=>[
				'ThirdParty.bool_active'=>true,
        'ThirdParty.bool_provider'=>false,
			],
			'order'=>'ThirdParty.company_name',
		]);
		$this->set(compact('clients'));
	}
	
	public function guardarAsociacionesClientesUsuarios() {
		$exportData=$_SESSION['resumenAsociaciones'];
		$this->set(compact('exportData'));
	}

	public function reasignarClientes() {
		$this->loadModel('User');
		$this->loadModel('ClientUser');
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
		
		$originUserId=0;
		$boolKeepOrigin=true;
		$destinyUserArray=0;
		$clientsAssociatedWithUser=[];
		if ($this->request->is(['post', 'put'])) {
			//pr($this->request->data);
			$originUserId=$this->request->data['Reassign']['origin_user_id'];
			$boolKeepOrigin=$this->request->data['Reassign']['bool_keep_origin'];
			$destinyUserId=$this->request->data['Reassign']['destiny_user_id'];
			if (!empty($this->request->data['Reassign']['origin_user_id'])){
				$clientIdsAssociatedWithUser=$this->ClientUser->find('list',[
					'fields'=>['ClientUser.client_id'],
					'conditions'=>[
						'ClientUser.user_id'=>$originUserId,
            'ClientUser.bool_assigned'=>true,
					],
				]);
				//pr($clientIdsAssociatedWithUser);
				$clientsAssociatedWithUser=$this->ThirdParty->find('all',[
					'conditions'=>[
						'ThirdParty.id'=>$clientIdsAssociatedWithUser,
					],
					'order'=>'ThirdParty.company_name',
				]);	
			}
			if (empty($this->request->data['showclients'])){
				$currentDateTime=new DateTime();
				$datasource=$this->ClientUser->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					$this->ClientUser->recursive=-1;
					foreach ($this->request->data['Reassign']['Client'] as $clientId=>$clientValue){						
						if ($clientValue['selector']){
							if (!$boolKeepOrigin){
								$clientUserArray=array();
								$clientUserArray['ClientUser']['client_id']=$clientId;
								$clientUserArray['ClientUser']['user_id']=$originUserId;
								$clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
								$clientUserArray['ClientUser']['bool_assigned']=false;
								
								$this->ClientUser->create();
								if (!$this->ClientUser->save($clientUserArray)){
									echo "Problema creando la asociación entre cliente y vendedor";
									pr($this->validateErrors($this->ClientUser));
									throw new Exception();
								}
							}
							foreach ($clientValue['target_user_id'] as $targetUserId){
								//if (empty($clientUserId)){
									$clientUserArray=array();
									$clientUserArray['ClientUser']['client_id']=$clientId;
									$clientUserArray['ClientUser']['user_id']=$targetUserId;
									$clientUserArray['ClientUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
									$clientUserArray['ClientUser']['bool_assigned']=true;
									
									$this->ClientUser->create();
									if (!$this->ClientUser->save($clientUserArray)){
										echo "Problema creando la asociación entre cliente y vendedor";
										pr($this->validateErrors($this->ClientUser));
										throw new Exception();
									}
								//}
							}
						}
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'reassignClients','clients');
					$this->recordUserActivity($this->Session->read('User.username'),"Se reasignaron clientes");
					$this->Session->setFlash(__('Se reasignaron los clientes.'),'default',['class' => 'success']);
					
					return $this->redirect(['action' => 'asociarClientesUsuarios']);
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían reasignar los clientes.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó reasignar clientes sin éxito");
				}
			}
		}
		$this->set(compact('originUserId'));
		$this->set(compact('boolKeepOrigin'));
		$this->set(compact('destinyUserArray'));
		$this->set(compact('clientsAssociatedWithUser'));
		
		$targetUsers=$originUsers = $destinyUsers= $users= $this->User->find('list',['fields'=>['User.username'],'order'=>'User.username ASC']);
		$this->set(compact('originUsers','destinyUsers','targetUsers','users'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="Users/index";		
		$bool_user_index_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_user_index_permission'));
		
		//pr($clientsAssociatedWithUser);
		//$clientsCreatedByUser=$this->Client->find('all',array(
		//	'conditions'=>array(
		//		'Client.creating_user_id'=>$id,
		//	),
		//	'order'=>'Client.created',
		//));
		//$this->set(compact('clientsCreatedByUser'));
		//$departments = $this->User->Department->find('list',array('order'=>'Department.name ASC'));
		//$companies = $this->User->Company->find('list',array('order'=>'Company.name ASC'));
		/*
		$this->loadModel('ClientUser');
		$this->loadModel('User');
		$this->User->recursive=-1;
		$users = $this->User->find('all',array(
			'fields'=>array('User.id','User.username','User.first_name','User.last_name'),
			'contain'=>array(
				'ClientUser'=>array(
					'conditions'=>array(
						'ClientUser.client_id'=>$id,
					)
				),
			),
			'order'=>'User.first_name,User.last_name',
		));
		$this->set(compact('users'));
		//pr($users);
		
		$this->Client->Contact->recursive=-1;
		$existingContacts=$this->Client->Contact->find('all',array(
			'conditions'=>array(
				'Contact.client_id'=>$id,
			),
		));
		$this->set(compact('existingContacts'));
		if ($this->request->is(array('post', 'put'))) {
			$this->loadModel('Contact');
			
			$existingContactIds=$this->Contact->find('list',array(
				'fields'=>'Contact.id',
				'conditions'=>array(
					'Contact.client_id'=>$id,
				),
			));
			//pr($existingContactIds);
			
			$boolContactsOK=true;
			$flashMessage="";
			foreach ($this->request->data['Contact'] as $contact){
				if (!empty($contact['first_name'])&&!empty($contact['last_name'])){
					$existingContactsInDatabase=$this->Contact->find('list',array(
						'fields'=>array('Contact.id'),
						'conditions'=>array(
							'Contact.first_name'=>$contact['first_name'],
							'Contact.last_name'=>$contact['last_name'],
							'Contact.client_id'=>$id,
							'Contact.id !='=>$existingContactIds,
						),
					));
					//echo "existing contacts for contact ".$contact['first_name']." y apellido ".$contact['last_name']."<br/>";
					//pr($existingContactsInDatabase);
					if (count($existingContactsInDatabase)>0){
						$flashMessage="Ya existe un contacto con nombre ".$contact['first_name']." y apellido ".$contact['last_name'].".  No se guardó el cliente.";
						$boolContactsOK=false;
					}
				}
			}
			
			if (!$boolContactsOK){
				$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Client->getDataSource();
				$datasource->begin();
				try {
					$this->Client->ClientUser->recursive=-1;
					$previousClientUsers=$this->Client->ClientUser->find('all',array(
						'fields'=>array('ClientUser.id'),
						'conditions'=>array(
							'ClientUser.client_id'=>$id,
						),
					));
					if (!empty($previousClientUsers)){
						foreach ($previousClientUsers as $previousClientUser){
							$this->Client->ClientUser->id=$previousClientUser['ClientUser']['id'];
							$this->Client->ClientUser->delete($previousClientUser['ClientUser']['id']);
						}
					}
				
					//pr($this->request->data);
					$this->Client->id=$id;
					if (!$this->Client->save($this->request->data)) {
						echo "Problema guardando el cliente";
						pr($this->validateErrors($this->Client));
						throw new Exception();
					}
					$client_id=$this->Client->id;
					
					for ($u=0;$u<count($this->request->data['User']);$u++){
						//pr($this->request->data['User'][$u]);
						if ($this->request->data['User'][$u]['id']){
							$clientUserArray=array();
							$this->Client->ClientUser->create();
							
							$clientUserArray['ClientUser']['client_id']=$client_id;
							$clientUserArray['ClientUser']['user_id']=$users[$u]['User']['id'];
							if (!$this->Client->ClientUser->save($clientUserArray)){
								echo "Problema guardando el vendedor para el cliente";
								pr($this->validateErrors($this->Client->ClientUser));
								throw new Exception();
							}
						}
					}
					
					$i=0;
					foreach ($this->request->data['Contact'] as $contact){
						if ($i<count($existingContacts)){
							if (!empty($contact['first_name'])&&!empty($contact['last_name'])){
								
								$contactArray=array();
								$contactArray['Contact']['id']=$existingContacts[$i]['Contact']['id'];
								$contactArray['Contact']['first_name']=$contact['first_name'];
								$contactArray['Contact']['last_name']=$contact['last_name'];
								$contactArray['Contact']['phone']=$contact['phone'];
								$contactArray['Contact']['cell']=$contact['cell'];
								$contactArray['Contact']['email']=$contact['email'];
								$contactArray['Contact']['department']=$contact['department'];
								$contactArray['Contact']['bool_active']=true;
								$contactArray['Contact']['client_id']=$client_id;
								if (!$this->Contact->save($contactArray)) {
									echo "Problema guardando los contactos del cliente";
									pr($this->validateErrors($this->Contact));
									throw new Exception();
								}
							}
						}
						else { 
							if (!empty($contact['first_name'])&&!empty($contact['last_name'])){
								$contactArray=array();
								$contactArray['Contact']['first_name']=$contact['first_name'];
								$contactArray['Contact']['last_name']=$contact['last_name'];
								$contactArray['Contact']['phone']=$contact['phone'];
								$contactArray['Contact']['cell']=$contact['cell'];
								$contactArray['Contact']['email']=$contact['email'];
								$contactArray['Contact']['department']=$contact['department'];
								$contactArray['Contact']['bool_active']=true;
								$contactArray['Contact']['client_id']=$client_id;
								$this->Contact->create();
								if (!$this->Contact->save($contactArray)) {
									echo "Problema guardando los contactos del cliente";
									pr($this->validateErrors($this->Contact));
									throw new Exception();
								}
							}
						}
						$i++;
					}
					$datasource->commit();
					$this->recordUserAction($this->Client->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['Client']['name']);
					
					$this->Session->setFlash(__('Se guardó el cliente.'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				} 
				catch(Exception $e){
					$datasource->rollback();
					//pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente.'), 'default',array('class' => 'error-message'));
				}
			}
		} 
		else {
			$options = array('conditions' => array('Client.id'=> $id));
			$this->request->data = $this->Client->find('first', $options);
		}
		
		$userIdsAssociatedWithClient=$this->ClientUser->find('list',array(
			'fields'=>array('ClientUser.user_id'),
			'conditions'=>array(
				'ClientUser.client_id'=>$id,
			),
		));
		$usersAssociatedWithClient=$this->User->find('all',array(
			'conditions'=>array(
				'User.id'=>$userIdsAssociatedWithClient,
			),
			'order'=>'User.first_name ASC,User.last_name ASC',
		));
		//$this->set(compact('usersAssociatedWithClient'));
		//pr($users);
		*/
	}

  public function asociarClientesEmpresas($selectedClientId=0){
		$this->loadModel('ThirdParty');
		$this->loadModel('ClientEnterprise');
		$this->loadModel('Enterprise');
		
		$this->ThirdParty->recursive=-1;
		$this->ClientEnterprise->recursive=-1;
		$this->Enterprise->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedClientId=0;
		$selectedEnterpriseId=0;
   
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$selectedEnterpriseId=$this->request->data['ClientEnterprise']['enterprise_id'];
			$selectedClientId=$this->request->data['ClientEnterprise']['client_id'];
			
			if (!empty($this->request->data['refresh'])){
        //$this->redirect(array('action' => 'asociarClientesUsuarios',$selectedClientId, 'page' => 1));
      }
      else {
				$currentDateTime=new DateTime();
				$datasource=$this->ClientEnterprise->getDataSource();
				$datasource->begin();
				try {
					foreach ($this->request->data['Client'] as $clientId=>$clientValue){
						//pr($clientValue);
						if ($clientValue['bool_changed']){
							foreach ($clientValue['Enterprise'] as $enterpriseId=>$enterpriseValue){
								$clientEnterpriseArray=[];
								$clientEnterpriseArray['ClientEnterprise']['client_id']=$clientId;
								$clientEnterpriseArray['ClientEnterprise']['enterprise_id']=$enterpriseId;
								$clientEnterpriseArray['ClientEnterprise']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
								$clientEnterpriseArray['ClientEnterprise']['bool_assigned']=$enterpriseValue['bool_assigned'];
								//pr($clientEnterpriseArray);
								$this->ClientEnterprise->create();
								if (!$this->ClientEnterprise->save($clientEnterpriseArray)){
									echo "Problema creando la asociación entre cliente y empresa";
									pr($this->validateErrors($this->ClientEnterprise));
									throw new Exception();
								}								
							}
						}					
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'asociarClientesEmpresas','clients');
					$this->recordUserActivity($this->Session->read('Enterprise.company_name'),"Se asignaron clientes a empresas");
					$this->Session->setFlash(__('Se asignaron los clientes a las empresas.'),'default',['class' => 'success']);
					//return $this->redirect(array('action' => 'asociarClientesUsuarios'));
					return $this->redirect($this->referer());
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían asignar los clientes a las empresas.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó asignar clientes a empresas sin éxito");
				}
			}
		}
		
		$this->set(compact('selectedEnterpriseId'));
		$this->set(compact('selectedClientId'));
		
		$enterpriseConditions=['Enterprise.bool_active'=>true];
		if (!empty($selectedEnterpriseId)){
			$enterpriseConditions['Enterprise.id']=$selectedEnterpriseId;
		}
    //pr($enterpriseConditions);
		$selectedEnterprises=$this->Enterprise->find('list',[
			'fields'=>['Enterprise.id','Enterprise.company_name'],
			'conditions'=>$enterpriseConditions,
			'order'=>'Enterprise.company_name',			
		]);
		$this->set(compact('selectedEnterprises'));
		
		$clientConditions=[
			'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>false,
		];
		if (!empty($selectedClientId)){
			$clientConditions['ThirdParty.id']=$selectedClientId;
		}
		
		$selectedClients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.id',
				'ThirdParty.company_name',
			],
			'conditions'=>$clientConditions,
			'contain'=>[
				'ClientEnterprise'=>[
					'fields'=>[
						'ClientEnterprise.id',
						'ClientEnterprise.enterprise_id',
						'ClientEnterprise.bool_assigned',
						'ClientEnterprise.assignment_datetime',
					],
					'order'=>'ClientEnterprise.assignment_datetime DESC,ClientEnterprise.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',
		]);
		
		//pr($selectedClients);
		for ($c=0;$c<count($selectedClients);$c++){
			$enterpriseArray=[];
			if (!empty($selectedClients[$c]['ClientEnterprise'])){
				foreach ($selectedEnterprises as $enterpriseId=>$enterpriseValue){
					$enterpriseArray[$enterpriseId]=0;
					foreach ($selectedClients[$c]['ClientEnterprise'] as $clientEnterprise){
						if ($clientEnterprise['enterprise_id']==$enterpriseId){
							$enterpriseArray[$enterpriseId]=$clientEnterprise['bool_assigned'];
							break;
						}
					}
				}
			}
			$selectedClients[$c]['Enterprises']=$enterpriseArray;
		}
		$this->set(compact('selectedClients'));
		//pr($clients);
		
		$enterprises=$this->Enterprise->find('list',[
			'order'=>'Enterprise.company_name',			
		]);
		$this->set(compact('enterprises'));
		
		$clients=$this->ThirdParty->find('list',[
			'fields'=>[
				'ThirdParty.id',
				'ThirdParty.company_name',
			],
			'conditions'=>[
				'ThirdParty.bool_active'=>true,
        'ThirdParty.bool_provider'=>false,
			],
			'order'=>'ThirdParty.company_name',
		]);
		$this->set(compact('clients'));
	}
	
	public function guardarAsociacionesClientesEmpresas() {
		$exportData=$_SESSION['resumenAsociacionesClientesEmpresas'];
		$this->set(compact('exportData'));
	}


}
