<?php
App::uses('AppController', 'Controller');

class HosesController extends AppController {

	public $components = ['Paginator'];

	public function resumen() {
		$this->Hose->recursive = -1;
    $hoseCount=$this->Hose->find('count');
		$this->Paginator->settings = [
      'contain'=>['Product','Island','Enterprise',],
			'order'=>'Hose.bool_active DESC, Hose.name ASC',
			'limit'=>($hoseCount!=0?$hoseCount:1)
		];
    $hoses=$this->Paginator->paginate();
		
    $userRole=$this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $startDate= date("2014-09-01");
    $endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		
    $this->set(compact('hoses'));
    
    $aco_name="Hoses/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Hoses/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Hoses/eliminar";		
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
		if (!$this->Hose->exists($id)) {
			throw new NotFoundException(__('Invalid hose'));
		}

    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		$this->loadModel('Operator');
    $this->loadModel('Shift');
    
		$this->Hose->recursive=-1;
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
    
    
		$options = [
			'conditions' => ['Hose.id' => $id],
			'contain'=>[
        'Enterprise',
        'Island',
        'Product',
        /*
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Operator',
					'Shift',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				]
        */
			]
		];
		$hose=$this->Hose->find('first', $options);
		//pr($hose);
		
		$this->set(compact('hose'));
		
		/*
		$soldProductsPerOperator=[];
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
		*/
		//$this->set(compact('soldProductsPerOperator'));
		
		$this->Hose->recursive=-1;
		$otherHoses=$this->Hose->find('all',[
			'fields'=>['Hose.id','Hose.name'],
			'conditions'=>['Hose.id !='=>$id],
		]);
		$this->set(compact('otherHoses'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
     
    $aco_name="Hoses/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Hoses/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Hoses/eliminar";		
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
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
     
		$enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
   
		if ($this->request->is('post')) {
			$datasource=$this->Hose->getDataSource();
			try {
				$datasource->begin();
				$product_id=0;
				
				$this->Hose->create();
				if (!$this->Hose->save($this->request->data)) {
					echo "Problema guardando la máquina";
					pr($this->validateErrors($this->Hose));
					throw new Exception();
				}
				$hose_id=$this->Hose->id;
				
				$datasource->commit();
				$this->recordUserAction($this->Hose->id,null,null);
				$this->Session->setFlash(__('The hose has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The hose could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
    
    $islands=[];
    if ($enterpriseId>0){
      $islandConditions=[];
      $islandConditions['Island.enterprise_id']=$enterpriseId;
      $islands=$this->Hose->Island->find('list',[
        'conditions'=>$islandConditions,
        'order'=>'Island.name ASC',
      ]);
    }
    else {
      $allIslands=$this->Hose->Island->find('all',[
        'contain'=>'Enterprise',
        'order'=>'Island.name ASC',
      ]);
      if (!empty($allIslands)){
        foreach ($allIslands as $island){
          //pr($island);
          //$islands[$island['Island']['id']]=$island['Island']['name']+" ("+$island['Enterprise']['company_name']+")";  
          $islands[$island['Island']['id']]=$island['Island']['name']." (".$island['Enterprise']['company_name'].")";  
        }
      }
    }
    //pr($islands);
    $this->set(compact('islands'));
    
    
    $products=$this->Hose->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('products'));
    
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
		if (!$this->Hose->exists($id)) {
			throw new NotFoundException(__('Invalid hose'));
		}
		
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
		$enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
     
		$enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
		
		if ($this->request->is(['post', 'put'])) {
			$datasource=$this->Hose->getDataSource();
			try {
				$datasource->begin();
				
				$this->Hose->id=$id;
				if (!$this->Hose->save($this->request->data)) {
					echo "Problema guardando la máquina";
					pr($this->validateErrors($this->Hose));
					throw new Exception();
				}
				$hose_id=$this->Hose->id;
				$datasource->commit();
				$this->recordUserAction($this->Hose->id,null,null);
				$this->Session->setFlash(__('The hose has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The hose could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} 
		else {
			$options = ['conditions' => ['Hose.id'=> $id]];
			$this->request->data = $this->Hose->find('first', $options);
		}
    
    $islandConditions=[];
    if ($enterpriseId>0){
      $islandConditions['Island.enterprise_id']=$enterpriseId;
    }
    $islands=$this->Hose->Island->find('list',[
      'conditions'=>$islandConditions,
      'order'=>'Island.name ASC',
    ]);
    $this->set(compact('islands'));
    
    $products=$this->Hose->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('products'));
    
    $aco_name="Hoses/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Hoses/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Hoses/eliminar";		
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
		$this->Hose->id = $id;
		if (!$this->Hose->exists()) {
			throw new NotFoundException(__('Invalid hose'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Hose->delete()) {
			$this->Session->setFlash(__('The hose has been deleted.'));
		} else {
			$this->Session->setFlash(__('The hose could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
