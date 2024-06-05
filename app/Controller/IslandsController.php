<?php
App::uses('AppController', 'Controller');

class IslandsController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->Island->recursive = -1;
    $islandCount=$this->Island->find('count');
    $this->Paginator->settings = [
      'contain'=>['Enterprise'],
			'order'=>'Island.bool_active DESC, Island.name ASC',
			'limit'=>($islandCount!=0?$islandCount:1)
		];
    $islands=$this->Paginator->paginate();
    $this->set(compact('islands'));
		
    $startDate= date("2014-09-01");
    $endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Islands/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Islands/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Islands/eliminar";		
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
		if (!$this->Island->exists($id)) {
			throw new NotFoundException(__('Invalid island'));
		}

    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		$this->loadModel('Operator');
    $this->loadModel('Shift');
    
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
		$this->set(compact('startDate','endDate'));
    
    /*
    $productionRunConditions=[
      'ProductionRun.island_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
    
		$options = [
			'conditions' => ['Island.id' => $id],
			'contain'=>[
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Operator',
					'Shift',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				]
			]
		];
    */
    $options = [
			'conditions' => ['Island.id' => $id],
			'contain'=>[
				'Enterprise'
			],
		];
		$island=$this->Island->find('first', $options);
		//pr($island);
		$this->set(compact('island'));
		
    /*
		$producedProductsPerOperator=array();
		$operators=$this->Operator->find('all',array(
			'fields'=>array('Operator.id','Operator.name'),
		));
		$operatorCounter=0;
		foreach ($operators as $operator){
			$rawMaterialCounter=0;
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerOperator[$operatorCounter]['operator_id']=$operator['Operator']['id'];
					$producedProductsPerOperator[$operatorCounter]['operator_name']=$operator['Operator']['name'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=array();
            
            $operatorProductionRunConditions=$productionRunConditions;
            $operatorProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.operator_id']=$operator['Operator']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$operatorProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							$quantityForProductInMonth=$this->ProductionMovement->find('first',array(
								'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
								'conditions'=>[
									'ProductionMovement.production_run_id'=>$productionRunIds,
                  'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								],
								'group'=>'ProductionMovement.product_id',
							));
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$operatorCounter++;
		}
		
		//pr($producedProductsPerOperator);
		
		$producedProductsPerShift=array();
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
		//$this->set(compact('producedProductsPerOperator','producedProductsPerShift'));
		
		$this->Island->recursive=-1;
		$otherIslands=$this->Island->find('all',[
			'fields'=>['Island.id','Island.name'],
			'conditions'=>['Island.id !='=>$id],
		]);
		$this->set(compact('otherIslands'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Islands/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Islands/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Islands/eliminar";		
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

	public function crear() {
		if ($this->request->is('post')) {
			$datasource=$this->Island->getDataSource();
			try {
				$datasource->begin();
				$this->Island->create();
				if (!$this->Island->save($this->request->data)) {
					echo "Problema guardando la isla";
					pr($this->validateErrors($this->Island));
					throw new Exception();
				}
				$island_id=$this->Island->id;
				
				$datasource->commit();
				$this->recordUserAction($this->Island->id,null,null);
				$this->Session->setFlash(__('The island has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The island could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
    
    $enterprises=$this->Island->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
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
		if (!$this->Island->exists($id)) {
			throw new NotFoundException(__('Invalid island'));
		}
		if ($this->request->is(['post', 'put'])) {
			$datasource=$this->Island->getDataSource();
			try {
				$datasource->begin();
				
				$this->Island->id=$id;
				if (!$this->Island->save($this->request->data)) {
					echo "Problema guardando la máquina";
					pr($this->validateErrors($this->Island));
					throw new Exception();
				}
				$island_id=$this->Island->id;
				
				$datasource->commit();
				$this->recordUserAction($this->Island->id,null,null);
				$this->Session->setFlash(__('The island has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The island could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} 
		else {
			$options = ['conditions' => ['Island.id' => $id]];
			$this->request->data = $this->Island->find('first', $options);
		}
		
    $enterprises=$this->Island->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
    
    $aco_name="Islands/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Islands/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Islands/eliminar";		
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

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Island->id = $id;
		if (!$this->Island->exists()) {
			throw new NotFoundException(__('Invalid island'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Island->delete()) {
			$this->Session->setFlash(__('The island has been deleted.'));
		} else {
			$this->Session->setFlash(__('The island could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
}
