<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class EnterprisesController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel');

  
  public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('');		
	}
	
	public function resumen() {
		$this->Enterprise->recursive = -1;
		$this->loadModel('EnterpriseUser');
		//$this->loadModel('ExchangeRate');
		//$this->loadModel('Order');
		
    $userId=$this->Auth->User('id');
		$userrole = $this->Auth->User('role_id');
		if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
			$userId=0;
		}
    
		$activeDisplayOptions=[
			'0'=>'Mostrar solamente empresas activas',
			'1'=>'Mostrar empresas activas y no activas',
			'2'=>'Mostrar empresas desactivadas',
		];
		$this->set(compact('activeDisplayOptions'));
		//$aggregateOptions=[
		//	'0'=>'No mostrar acumulados, ordenar por nombre cliente',
		//	'1'=>'Mostrar salidas, ordenado por salidas y cliente',
		//];
		//$this->set(compact('aggregateOptions'));
		
		define('SHOW_ENTERPRISE_ACTIVE_YES','0');
		define('SHOW_ENTERPRISE_ACTIVE_ALL','1');
		define('SHOW_ENTERPRISE_ACTIVE_NO','2');
		
		//define('AGGREGATES_NONE','0');
		//define('AGGREGATES_ORDERS','1');
		
		$activeDisplayOptionId=SHOW_ENTERPRISE_ACTIVE_YES;
		$searchTerm="";
		
    //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
		//	$aggregateOptionId=AGGREGATES_ORDERS;
		//}
    //else {
    //  $aggregateOptionId=AGGREGATES_NONE;
    //}
    
    if ($this->request->is('post')) {
			//$startDateArray=$this->request->data['Report']['startdate'];
			//$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			//$startDate=date( "Y-m-d", strtotime($startDateString));
		
			//$endDateArray=$this->request->data['Report']['enddate'];
			//$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			//$endDate=date("Y-m-d",strtotime($endDateString));
			//$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$userId=$this->request->data['Report']['user_id'];
			
			$activeDisplayOptionId=$this->request->data['Report']['active_display_option_id'];
			//$aggregateOptionId=$this->request->data['Report']['aggregate_option_id'];
			
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		//else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			//$startDate=$_SESSION['startDateClient'];
			//$endDate=$_SESSION['endDateClient'];
			//$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		//}
		//else {
			//$startDate = date("Y-01-01");
			//$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			//$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		//}
		//$_SESSION['startDateClient']=$startDate;
		//$_SESSION['endDateClient']=$endDate;
		
		//$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
		$this->set(compact('activeDisplayOptionId'));
    //$this->set(compact('aggregateOptionId'));
		$this->set(compact('searchTerm'));
		
    $enterpriseUserConditions=[];
    
		if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT) { 
      // in this case the user_id is set to the logged in user explicitly
      // the enterprises are limited to those that have at least at one time been associated with the user
    	$enterpriseUserIds=$this->EnterpriseUser->find('list',[
				'fields'=>['EnterpriseUser.enterprise_id'],
				'conditions'=>[
          'EnterpriseUser.user_id'=>$this->Auth->User('id'),
          'EnterpriseUser.bool_assigned'=>true,
        ],
			]);
		
			$enterpriseConditions['Enterprise.id']=$enterpriseUserIds;
      $enterpriseUserConditions['EnterpriseUser.user_id']=$this->Auth->User('id');
		}
		else {
      // case of an admin or assistant
			if ($userId>0){
				$enterpriseUserIds=$this->EnterpriseUser->find('list',[
					'fields'=>['EnterpriseUser.enterprise_id'],
					'conditions'=>[
            'EnterpriseUser.user_id'=>$userId,
            'EnterpriseUser.bool_assigned'=>true,
          ],
				]);
			
				$enterpriseConditions['Enterprise.id']=$enterpriseUserIds;
        $enterpriseUserConditions['EnterpriseUser.user_id']=$userId;
			}
		}
		if ($activeDisplayOptionId!=SHOW_ENTERPRISE_ACTIVE_ALL){
			if ($activeDisplayOptionId==SHOW_ENTERPRISE_ACTIVE_YES){
				$enterpriseConditions['Enterprise.bool_active']=true;
			}
			else {
				$enterpriseConditions['Enterprise.bool_active']=false;
			}
		}
		
		if (!empty($searchTerm)){
			$enterpriseConditions['Enterprise.company_name LIKE']='%'.$searchTerm.'%';
		}
    
		$enterpriseCount=	$this->Enterprise->find('count', [
			'fields'=>['Enterprise.id'],
			'conditions' => $enterpriseConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $enterpriseConditions,
			'contain'=>[
				//'AccountingCode',
        'EnterpriseUser'=>[
          'conditions' => $enterpriseUserConditions,  
					'User',
					'order'=>'EnterpriseUser.assignment_datetime DESC,EnterpriseUser.id DESC',
          'limit'=>1,
				],
				//'Order'=>[
				//	'conditions'=>[
				//		'Order.order_date >='=>$startDate,
				//		'Order.order_date <'=>$endDatePlusOne,
        //    'Order.bool_annulled'=>false,
				//	],
				//],
			],
			'order' => ['Enterprise.company_name'=>'ASC'],
			'limit'=>($enterpriseCount>0?$enterpriseCount:1),
		];

		$enterprises = $this->Paginator->paginate('Enterprise');
    //pr($allEnterprises);
    //$enterprises=[];	
		/*
    for ($c=0;$c<count($allEnterprises);$c++){
			$orderTotal=0;
        $thisEnterprise=$allEnterprises[$c];
        $thisEnterprise['Enterprise']['pending_payment']=$this->Enterprise->getCurrentPendingPayment($thisEnterprise['Enterprise']['id']);
        
        for ($q=0;$q<count($thisEnterprise['Order']);$q++){
          $orderTotal+=$thisEnterprise['Order'][$q]['total_price'];
        }
        $thisEnterprise['Enterprise']['order_total']=$orderTotal;
        $enterprises[]=$thisEnterprise;        
    }
    */
    $this->set(compact('enterprises'));
    
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
    
		$aco_name="Enterprises/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Enterprises/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));		
	}
  
  public function guardarResumen() {
		$exportData=$_SESSION['resumenEmpresas'];
		$this->set(compact('exportData'));
	}
  
	public function detalle($id = null) {
		if (!$this->Enterprise->exists($id)) {
			throw new NotFoundException(__('Empresa inválida'));
		}
		/*
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDateEnterprise']) && !empty($_SESSION['endDateEnterprise'])){
			$startDate=$_SESSION['startDateEnterprise'];
			$endDate=$_SESSION['endDateEnterprise'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDateEnterprise']=$startDate;
		$_SESSION['endDateEnterprise']=$endDate;
		*/
		$enterprise=$this->Enterprise->find('first', [
			'conditions'=>[
				'Enterprise.id'=>$id,
			],
			'contain'=>[
        //'AccountingCode',
        'EnterpriseUser'=>[
          'order'=>'EnterpriseUser.assignment_datetime DESC,EnterpriseUser.id DESC',
					'User',
				],
				//'Order'=>[
				//	'conditions'=>[
				//		'Order.order_date >='=>$startDate,
				//		'Order.order_date <'=>$endDatePlusOne,
				//	],
				//	'order'=>'order_date DESC'
				//],
			]
		]);
		//pr($enterprise);
		//$enterprise['Enterprise']['pending_payment']=$this->Enterprise->getCurrentPendingPayment($id);
		$this->set(compact('enterprise'));
    $this->set(compact('startDate','endDate'));
		
    $userIdList=[];
    foreach ($enterprise['EnterpriseUser'] as $enterpriseUser){
      if (!in_array($enterpriseUser['user_id'],$userIdList)){
        $userIdList[]=$enterpriseUser['user_id'];
      }
    }
    $this->loadModel('Users');
    $uniqueUsers=$this->User->find('all',[
      'conditions'=>['User.id'=>$userIdList],
      'contain'=>[					
        'EnterpriseUser'=>[
          'conditions'=>['EnterpriseUser.enterprise_id'=>$id],
          'order'=>'EnterpriseUser.assignment_datetime DESC,EnterpriseUser.id DESC',
        ]
  		],
      'order'=>'User.username'
    ]);
    $this->set(compact('uniqueUsers'));
		
		$aco_name="Enterprises/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Enterprises/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));		
	
    $aco_name="Enterprises/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));		
    
    $aco_name="Users/edit";		
		$bool_user_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_user_edit_permission'));		
	
  }
	
	public function crear() {
    //$this->loadModel('AccountingCode');
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
	
		
		if ($this->request->is('post')) {			
			$this->request->data['Enterprise']['company_name']=trim(strtoupper($this->request->data['Enterprise']['company_name']));
			$previousEnterprisesWithThisName=[];
			$previousEnterprisesWithThisName=$this->Enterprise->find('all',[
				'conditions'=>['TRIM(UPPER(Enterprise.company_name))'=>$this->request->data['Enterprise']['company_name']],
			]);
			
			$allPreviousEnterprises=$this->Enterprise->find('all',[
				'fields'=>['Enterprise.company_name'],
			]);
			
			$bool_similar=false;
			$similar_string="";
			foreach ($allPreviousEnterprises as $existingEnterpriseName){
				similar_text($this->request->data['Enterprise']['company_name'],$existingEnterpriseName['Enterprise']['company_name'],$percent);
				if ($percent>80){
					$bool_similar=true;
					$similar_string=$existingEnterpriseName['Enterprise']['company_name'];
				}
			}
			
			if (count($previousEnterprisesWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo una empresa con este nombre!  No se guardó la empresa.'), 'default',array('class' => 'error-message'));
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de una empresa existente: '.$similar_string.'!  No se guardó la empresa.'), 'default',['class' => 'error-message']);
			}
			else {	
        $datasource=$this->Enterprise->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
          /*
          $this->AccountingCode->create();
          $accountingCodeArray=array();
          $accountingCodeArray['AccountingCode']['code']=$this->request->data['Enterprise']['accounting_code_id'];
          $accountingCodeArray['AccountingCode']['description']=$this->request->data['Enterprise']['company_name'];
          $accountingCodeArray['AccountingCode']['parent_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
          $accountingCodeArray['AccountingCode']['bool_main']=false;
          $accountingCodeArray['AccountingCode']['bool_creditor']=false;
          if (!$this->AccountingCode->save($accountingCodeArray)) {
            $this->Session->setFlash(__('No se podía guardar la cuenta contable para la empresa nueva.'), 'default',[class' => 'error-message']);
          }
          else {
            $this->request->data['Enterprise']['accounting_code_id']=$this->AccountingCode->id;
          */  
            
            $this->Enterprise->create();
            if (!$this->Enterprise->save($this->request->data)) {
              echo "Problema guardando la empresa";
              pr($this->validateErrors($this->Enterprise));
              throw new Exception();
            }
            $enterprise_id=$this->Enterprise->id;
            
            if (!empty($this->request->data['User'])){
              $currentDateTime=new DateTime();
              for ($u=0;$u<count($this->request->data['User']);$u++){
                $enterpriseUserArray=array();
                $this->Enterprise->EnterpriseUser->create();
                $enterpriseUserArray['EnterpriseUser']['enterprise_id']=$enterprise_id;
                $enterpriseUserArray['EnterpriseUser']['user_id']=$users[$u]['User']['id'];
                $enterpriseUserArray['EnterpriseUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
                $enterpriseUserArray['EnterpriseUser']['bool_assigned']=$this->request->data['User'][$u]['id'];
                if (!$this->Enterprise->EnterpriseUser->save($enterpriseUserArray)){
                  echo "Problema guardando el vendedor para la empresa";
                  pr($this->validateErrors($this->Enterprise->EnterpriseUser));
                  throw new Exception();
                }							
              }
            }
          //}
          
          $datasource->commit();
          $this->recordUserAction($this->Enterprise->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró la empresa ".$this->request->data['Enterprise']['company_name']);
            
          $this->Session->setFlash(__('Se guardó la empresa.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        }      
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar la empresa. Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}
			}  
		}
		/*
		$lastEnterpriseAccountingCode=$this->AccountingCode->find('first',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
			),
			'order'=>'AccountingCode.code DESC',
		));
		$lastEnterpriseCode=$lastEnterpriseAccountingCode['AccountingCode']['code'];
		//pr($lastEnterpriseCode);
		$positionLastHyphen=strrpos($lastEnterpriseCode,"-");
		//echo $positionLastHyphen."<br/>";
		$enterpriseCodeStart=substr($lastEnterpriseCode,0,($positionLastHyphen+1));
		$enterpriseCodeEnding=substr($lastEnterpriseCode,($positionLastHyphen+1));
		//echo $enterpriseCodeStart."<br/>";
		//echo $enterpriseCodeEnding."<br/>";
		$newEnterpriseCodeEnding=str_pad($enterpriseCodeEnding+1,3,'0',STR_PAD_LEFT);
		//echo $newEnterpriseCodeEnding."<br/>";
		$newEnterpriseCode=$enterpriseCodeStart.$newEnterpriseCodeEnding;
		//echo $newEnterpriseCode."<br/>";
		$this->set(compact('newEnterpriseCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>false,
			),
			'order'=>'AccountingCode.code ASC',
		));
		$accountingCodes[$newEnterpriseCode]=$newEnterpriseCode;
		//pr($accountingCodes);
		
		$this->set(compact('accountingCodes'));
    */
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $aco_name="Enterprises/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Enterprises/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));	


	}

	public function editar($id = null) {
		if (!$this->Enterprise->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
  
    $this->loadModel('EnterpriseUser');
		$this->loadModel('User');
		$this->User->recursive=-1;
		$users = $this->User->find('all',[
			'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'conditions'=>['User.bool_active'=>true],
			'contain'=>[
				'EnterpriseUser'=>[
					'conditions'=>['EnterpriseUser.enterprise_id'=>$id],
					'order'=>'EnterpriseUser.id DESC',
				],
			],
			'order'=>'User.first_name,User.last_name,User.username',
		]);
		$this->set(compact('users'));
		//pr($users);
		
		if ($this->request->is(['post', 'put'])) {
			$previousEnterprisesWithThisName=[];
			$previousEnterprise=$this->Enterprise->read(null,$id);
			
			$bool_similar=false;
			$similar_string="";
			
			if ($previousEnterprise['Enterprise']['company_name']!=$this->request->data['Enterprise']['company_name']){
				$previousEnterprisesWithThisName=$this->Enterprise->find('all',[
					'conditions'=>[
						'TRIM(LOWER(Enterprise.company_name))'=>trim(strtolower($this->request->data['Enterprise']['company_name'])),
					],
				]);
				
				$allPreviousEnterprises=$this->Enterprise->find('all',[
					'fields'=>['Enterprise.company_name'],
				]);
				
				
				foreach ($allPreviousEnterprises as $existingEnterpriseName){
					similar_text($this->request->data['Enterprise']['company_name'],$existingEnterpriseName['Enterprise']['company_name'],$percent);
					if ($percent>80){
						$bool_similar=true;
						$similar_string=$existingEnterpriseName['Enterprise']['company_name'];
					}
				}
			}
			
			if (count($previousEnterprisesWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un empresa con este nombre!  No se guardó la empresa.'), 'default',['class' => 'error-message']);
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un empresa existente: '.$similar_string.'!  No se guardó la empresa.'), 'default',['class' => 'error-message']);
			}
			else {
        $datasource=$this->Enterprise->getDataSource();
				$datasource->begin();
				try {
				  $this->Enterprise->EnterpriseUser->recursive=-1;
          
          $this->request->data['Enterprise']['id']=$id;
          $this->Enterprise->id=$id;
          if (!$this->Enterprise->save($this->request->data)) {
						echo "Problema guardando la empresa";
						pr($this->validateErrors($this->Enterprise));
						throw new Exception();
					}
					$enterprise_id=$this->Enterprise->id;
					if (!empty($this->request->data['User'])){
						$currentDateTime=new DateTime();					
						for ($u=0;$u<count($this->request->data['User']);$u++){
							//pr($this->request->data['User'][$u]);
							$enterpriseUserArray=array();
							$this->Enterprise->EnterpriseUser->create();
							
							$enterpriseUserArray['EnterpriseUser']['enterprise_id']=$enterprise_id;
							$enterpriseUserArray['EnterpriseUser']['user_id']=$users[$u]['User']['id'];
							$enterpriseUserArray['EnterpriseUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
							$enterpriseUserArray['EnterpriseUser']['bool_assigned']=$this->request->data['User'][$u]['id'];
							if (!$this->Enterprise->EnterpriseUser->save($enterpriseUserArray)){
								echo "Problema guardando el vendedor para la empresa";
								pr($this->validateErrors($this->Enterprise->EnterpriseUser));
								throw new Exception();
							}
						}
					}
					
          $datasource->commit();
					$this->recordUserAction($this->Enterprise->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó la empresa ".$this->request->data['Enterprise']['company_name']);
					
					$this->Session->setFlash(__('Se guardó la empresa.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'resumen']);
        } 
				catch(Exception $e){
					$datasource->rollback();
					//pr($e);
					$this->Session->setFlash(__('No se podía guardar la empresa.'), 'default',['class' => 'error-message']);
				}  
			}
		} 
		else {
			$options = array('conditions' => ['Enterprise.id'=> $id]);
			$this->request->data = $this->Enterprise->find('first', $options);
		}
    /*
		$this->loadModel('AccountingCode');
		$accountingCodes=$this->AccountingCode->find('list',array(
			'conditions'=>array(
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>false,
			),
			'order'=>'AccountingCode.code ASC',
		));
		$this->set(compact('accountingCodes'));
		*/
    $roleId = $this->Auth->User('role_id');
    //echo "roleId is ".$roleId."<br/>";
    $this->set(compact('roleId'));
    
    //$this->loadModel('Currency');
    //$creditCurrencies=$this->Currency->find('list');
    //$this->set(compact('creditCurrencies'));
    
    $this->loadModel('User');
    $this->User->recursive=-1;  
    $allUsers=$this->User->find('all',[
      'fields'=>['User.id','User.id','User.username','User.first_name','User.last_name'],
      'order'=>'User.first_name ASC,User.last_name ASC,User.username ASC',
    ]);
    
    $usersAssociatedWithEnterprise=[];
    foreach ($allUsers as $user){
      if ($this->EnterpriseUser->checkAssociationEnterpriseWithUser($id,$user['User']['id'])){
        $usersAssociatedWithEnterprise[]=$user;
      }
    }
    //pr($usersAssociatedWithEnterprise);
    $this->set(compact('usersAssociatedWithEnterprise'));
    
		$aco_name="Enterprises/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Enterprises/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));		
	}
	
/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Enterprise->id = $id;
		if (!$this->Enterprise->exists()) {
			throw new NotFoundException(__('Empresa inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Enterprise->delete()) {
			$this->Session->setFlash(__('The enterprise has been deleted.'));
		} else {
			$this->Session->setFlash(__('The enterprise could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
	

  public function asociarEmpresasUsuarios($selectedEnterpriseId=0){
		$this->loadModel('Enterprise');
		$this->loadModel('EnterpriseUser');
		$this->loadModel('User');
		
		$this->Enterprise->recursive=-1;
		$this->EnterpriseUser->recursive=-1;
		$this->User->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedEnterpriseId=0;
		$selectedUserId=0;
   
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$selectedUserId=$this->request->data['EnterpriseUser']['user_id'];
			$selectedEnterpriseId=$this->request->data['EnterpriseUser']['enterprise_id'];
			
			if (!empty($this->request->data['refresh'])){
        //$this->redirect(array('action' => 'asociarEnterpriseesUsuarios',$selectedEnterpriseId, 'page' => 1));
      }
      else {
				$currentDateTime=new DateTime();
				$datasource=$this->EnterpriseUser->getDataSource();
				$datasource->begin();
				try {
					foreach ($this->request->data['Enterprise'] as $enterpriseId=>$enterpriseValue){
						//pr($enterpriseValue);
						if ($enterpriseValue['bool_changed']){
							foreach ($enterpriseValue['User'] as $userId=>$userValue){
								$enterpriseUserArray=array();
								$enterpriseUserArray['EnterpriseUser']['enterprise_id']=$enterpriseId;
								$enterpriseUserArray['EnterpriseUser']['user_id']=$userId;
								$enterpriseUserArray['EnterpriseUser']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
								$enterpriseUserArray['EnterpriseUser']['bool_assigned']=$userValue['bool_assigned'];
								//pr($enterpriseUserArray);
								$this->EnterpriseUser->create();
								if (!$this->EnterpriseUser->save($enterpriseUserArray)){
									echo "Problema creando la asociación entre empresa y vendedor";
									pr($this->validateErrors($this->EnterpriseUser));
									throw new Exception();
								}								
							}
						}					
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'asociarEnterpriseesUsuarios','enterprises');
					$this->recordUserActivity($this->Session->read('User.username'),"Se asignaron empresas a usuarios");
					$this->Session->setFlash(__('Se asignaron las empresas a los usuarios.'),'default',['class' => 'success']);
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían asignar los empresas a los usuarios.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó asignar empresas sin éxito");
				}
			}
		}
		
		$this->set(compact('selectedUserId'));
		$this->set(compact('selectedEnterpriseId'));
		
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
		$enterpriseConditions=[
			'Enterprise.bool_active'=>true,     
		];
		if (!empty($selectedEnterpriseId)){
			$enterpriseConditions['Enterprise.id']=$selectedEnterpriseId;
		}
		
		$selectedEnterprises=$this->Enterprise->find('all',[
			'fields'=>[
				'Enterprise.id',
				'Enterprise.company_name',
			],
			'conditions'=>$enterpriseConditions,
			'contain'=>[
				'EnterpriseUser'=>[
					'fields'=>[
						'EnterpriseUser.id',
						'EnterpriseUser.user_id',
						'EnterpriseUser.bool_assigned',
						'EnterpriseUser.assignment_datetime',
					],
					'order'=>'EnterpriseUser.assignment_datetime DESC,EnterpriseUser.id DESC',
				],
			],
			'order'=>'Enterprise.company_name',
		]);
		for ($c=0;$c<count($selectedEnterprises);$c++){
			$userArray=[];
			if (!empty($selectedEnterprises[$c]['EnterpriseUser'])){
				foreach ($selectedUsers as $userId=>$userValue){
					$userArray[$userId]=0;
					foreach ($selectedEnterprises[$c]['EnterpriseUser'] as $enterpriseUser){
						if ($enterpriseUser['user_id']==$userId){
							$userArray[$userId]=$enterpriseUser['bool_assigned'];
							break;
						}
					}
				}
			}
			$selectedEnterprises[$c]['Users']=$userArray;
		}
		$this->set(compact('selectedEnterprises'));
		//pr($enterprises);
		
		$users=$this->User->find('list',[
			'fields'=>[
				'User.id',
				'User.username',
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('users'));
		
		$enterprises=$this->Enterprise->find('list',[
			'fields'=>[
				'Enterprise.id',
				'Enterprise.company_name',
			],
			'conditions'=>[
				'Enterprise.bool_active'=>true,
			],
			'order'=>'Enterprise.company_name',
		]);
		$this->set(compact('enterprises'));
	}
	
	public function guardarAsociacionesEmpresasUsuarios() {
		$exportData=$_SESSION['resumenAsociacionesEmpresasUsuarios'];
		$this->set(compact('exportData'));
	}

}
