<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class OperatorsController extends AppController {

  public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');
  
  public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('login','logout');		
	}

	public function resumen() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $enterpriseId=0;
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		$this->Operator->recursive = -1;
		$operatorCount=$this->Operator->find('count');
		$this->Paginator->settings = [
      'conditions'=>[
        'Operator.enterprise_id'=>$enterpriseId,
      ],
      'contain'=>['Enterprise'],
			'order'=>'Operator.bool_active DESC, Operator.name ASC',
			'limit'=>($operatorCount!=0?$operatorCount:1)
		];
		$this->set('operators', $this->Paginator->paginate());
    
    $aco_name="Operators/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Operators/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Operators/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
    
    
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

	public function detalle($id = null) {
		if (!$this->Operator->exists($id)) {
			throw new NotFoundException(__('Invalid operator'));
		}
		
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
		$this->loadModel('Island');
    $this->loadModel('Shift');
    
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		
    $this->Island->recursive=-1;
    $this->Operator->recursive=-1;
    $this->Shift->recursive=-1;
    
		$startDate = null;
		$endDate = null;
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
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
    /*
    $productionRunConditions=[
      'ProductionRun.operator_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
    */
		$options = [
			'conditions' => ['Operator.id'  => $id],
			'contain'=>[
        'Enterprise',
			//	'ProductionRun'=>[
			//		'FinishedProduct',
			//		'Island',
			//		'Shift',
			//		'conditions' => $productionRunConditions,
			//		'order'=>'production_run_date DESC',
			//	]
			]
		];
		
		$operator=$this->Operator->find('first', $options);
		//pr($operator);
		
		/*
		$producedProductsPerShift=array();
		$this->Shift->recursive=-1;
		$shifts=$this->Shift->find('all',array('fields'=>array('Shift.id','Shift.name')));
		$shiftCounter=0;
		foreach ($shifts as $shift){
			$rawMaterialCounter=0;
			
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerShift[$shiftCounter]['shift_id']=$shift['Shift']['id'];
					$producedProductsPerShift[$shiftCounter]['shift_name']=$shift['Shift']['name'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=array();
            
            $shiftProductionRunConditions=$productionRunConditions;
            $shiftProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.shift_id']=$shift['Shift']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$shiftProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							$quantityForProductInMonth=$this->ProductionMovement->find('first',array(
								'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
								'conditions'=>array(
									'ProductionMovement.production_run_id'=>$productionRunIds,
                  'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								),
								'group'=>'ProductionMovement.product_id',
							));
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$shiftCounter++;
		}
		
		//pr($producedProductsPerShift);
		*/
		$this->set(compact('operator','startDate','endDate','producedProductsPerShift','visibleArray'));
		
		$this->Operator->recursive=-1;
		$otherOperators=$this->Operator->find('all',[
			'fields'=>['Operator.id','Operator.name'],
			'conditions'=>[
				'Operator.id !='=>$id,
				'Operator.bool_active'=>true,
			],
			'order'=>'Operator.name ASC',
		]);
		$this->set(compact('otherOperators'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Operators/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Operators/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Operators/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

	public function reporteVentasTotales() {
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		elseif (!empty($this->params['named']['sort'])){
			$startDate=$_SESSION['startDateReporteProduccionTotal'];
			$endDate=$_SESSION['endDateReporteProduccionTotal'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}

		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDateReporteProduccionTotal']=$startDate;
		$_SESSION['endDateReporteProduccionTotal']=$endDate;
		$this->set(compact('startDate','endDate'));

		$this->loadModel('Island');
		$this->loadModel('Shift');
		$this->Operator->recursive=-1;
		$this->Island->recursive=-1;
		$this->Shift->recursive=-1;
		
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionRun');
		$this->ProductionMovement->recursive=-1;
		$this->ProductionRun->recursive=-1;
				
		$operators=$this->Operator->find('all',array(
			'fields'=>array('Operator.id','Operator.name'),
			'order'=>'Operator.name',
		));
		
		//pr($operators);
		$this->set(compact('operators'));
		
		$islands=$this->Island->find('all',[
			'fields'=>['Island.id','Island.name'],
			'order'=>'Island.name',
		]);
		
		for ($i=0;$i<count($islands);$i++){
			
		}
		//pr($islands);
		$this->set(compact('islands'));
		
		$shifts=$this->Shift->find('all',[
			'fields'=>['Shift.id','Shift.name'],
			'order'=>'Shift.name',
		]);
		
		for ($i=0;$i<count($shifts);$i++){
		}
		//pr($shifts);
		$this->set(compact('shifts'));
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
	}

	public function crear() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($this->request->is('post')) {
      if  (empty($this->request->data['Operator']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del operador.'), 'default',['class' => 'error-message']);
      }
      else {
        $this->Operator->create();
        if ($this->Operator->save($this->request->data)) {
          $this->recordUserAction($this->Operator->id,null,null);
          $this->Session->setFlash(__('The operator has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        else {
          $this->Session->setFlash(__('The operator could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		}
    
    $enterpriseId=0;
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

	public function editar($id = null) {
		if (!$this->Operator->exists($id)) {
			throw new NotFoundException(__('Operador inválido'));
		}
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($this->request->is(['post', 'put'])) {
      if  (empty($this->request->data['Operator']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del operador.'), 'default',['class' => 'error-message']);
      }
      else {
        if ($this->Operator->save($this->request->data)) {
          $this->recordUserAction();
          $this->Session->setFlash(__('The operator has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        else {
          $this->Session->setFlash(__('The operator could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		} 
		else {
			$options = ['conditions' => ['Operator.id' => $id]];
			$this->request->data = $this->Operator->find('first', $options);
		}
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Operator->id = $id;
		if (!$this->Operator->exists()) {
			throw new NotFoundException(__('Operador inválido'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Operator->delete()) {
			$this->Session->setFlash(__('The operator has been deleted.'));
		} else {
			$this->Session->setFlash(__('The operator could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
}
