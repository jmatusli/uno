<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class MovementEstimatesController extends AppController {

	public $components = ['Paginator','RequestHandler']; 
	public $helpers = array('PhpExcel');

  public function reporteEstimacionesCompras(){
		$this->loadModel('StockMovement');
    $this->loadModel('Product');
    $this->loadModel('StockItem');
    $this->loadModel('HoseCounter');
    
		$enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['MovementEstimate']['enterprise_id'];
    }
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    $this->set(compact('inventoryDate'));
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    $this->Product->recursive=-1;
    $fuelArray=[];
		$fuels=$this->Product->getFuelList();
    //pr($fuels);
    foreach ($fuels as $fuelId=>$fuelName){
      $fuelArray[$fuelId]['name']=$fuelName;  
    }
    $fuelInventories=$this->StockItem->getInventoryItems(PRODUCT_TYPE_FUELS,$inventoryDate,$enterpriseId,false);
    //pr($fuelInventories);
    foreach ($fuelInventories as $fuelInventory){
      $fuelId=$fuelInventory['Product']['id'];
      $fuelArray[$fuelId]['existence']=$fuelInventory[0]['Remaining'];
      $fuelArray[$fuelId]['movement']=0;
    }
    //pr($fuelArray);
    
    $latestHoseCounter=$this->HoseCounter->find('first',[
      'order'=>'counter_date DESC',
    ]);
    $latestCounterDate=$latestHoseCounter['HoseCounter']['counter_date'];
    //echo "Latest counter date is ".$latestCounterDate."<br/>";
    $this->set(compact('latestCounterDate'));
    
    $fuelStockMovementConditions=[
      'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
      'StockMovement.product_id'=>$fuelIds,
      'DATE(StockMovement.movement_date)'=>$latestCounterDate,
    ];
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $fuelMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'product_id',
        'SUM(product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$fuelStockMovementConditions,
      'group'=>['product_id'],
    ]);
    //pr($fuelMovements);
    foreach ($fuelMovements as $fuelMovement){
      $fuelId=$fuelMovement['StockMovement']['product_id'];
      $fuelArray[$fuelId]['movement']+=$fuelMovement['StockMovement']['total_product_quantity'];
    }
		
    $calibrationMovementConditions=[
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_CALIBRATION],
      'StockMovement.product_id'=>$fuelIds,
      'DATE(StockMovement.movement_date)'=>$latestCounterDate,
    ];
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $calibrationMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'product_id',
        'SUM(product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$calibrationMovementConditions,
      'group'=>'product_id'
    ]);
    foreach ($calibrationMovements as $calibrationMovement){
      $fuelId=$calibrationMovement['StockMovement']['product_id'];
      $fuelArray[$fuelId]['movement']-=$calibrationMovement['StockMovement']['total_product_quantity'];
    }
		//pr($fuelArray);
    $this->set(compact('fuelArray'));

    $dateArray=[];
    $currentDate=$inventoryDatePlusOne;
    $inventoryDatePlusTwoWeeks=date( "Y-m-d", strtotime($inventoryDate."+14 days"));
    while ($currentDate<=$inventoryDatePlusTwoWeeks){
      $dateArray[]=$currentDate;
      $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
    }
    //pr($dateArray);
    $this->set(compact('dateArray'));
    $movementEstimateConditions=[
      'MovementEstimate.estimate_date >='=>$inventoryDatePlusOne,
      'MovementEstimate.estimate_date <='=>$inventoryDatePlusTwoWeeks,
      'MovementEstimate.enterprise_id'=>$enterpriseId,
    ];
    //pr($movementEstimateConditions);
    $movementEstimates=$this->MovementEstimate->find('all',[
      'fields'=>['MovementEstimate.id','MovementEstimate.estimate_date','MovementEstimate.product_id',
      'MovementEstimate.product_quantity',
      'MovementEstimate.bool_sale',],
      'conditions'=>$movementEstimateConditions,
      'order'=>['estimate_date','bool_sale'],
    ]);
    
    //pr($this->request->data);
		if ($this->request->is('post') && !empty($this->request->data['saveEstimates'])) {
      $datasource=$this->MovementEstimate->getDataSource();
      $datasource->begin();
      try {
        if (!empty($this->request->data['Entry'])){
          if (!empty($movementEstimates)){
            foreach ($movementEstimates as $movementEstimate){
              if (!$this->MovementEstimate->delete($movementEstimate['MovementEstimate']['id'])) {
                echo "problema eliminando las estimaciones viejas";
                pr($this->validateErrors($this->MovementEstimate));
                throw new Exception();
              }
            }
          }
          
          foreach ($this->request->data['Entry'] as $entryEstimateDate=>$entryFuelData){
            foreach ($entryFuelData['Fuel'] as $fuelId=>$fuelQuantity){
              $movementEstimateArray=[
                'MovementEstimate'=>[
                  'enterprise_id'=>$enterpriseId,
                  'estimate_date'=>$entryEstimateDate,
                  'product_id'=>$fuelId,
                  'product_quantity'=>$fuelQuantity,
                  'bool_sale'=>false,
                  'user_id'=>$loggedUserId,
                ],
              ];
              //pr($movementEstimateArray);
              $this->MovementEstimate->create();
              if (!$this->MovementEstimate->save($movementEstimateArray)) {
                echo "problema guardando las estimaciones de entradas";
                pr($this->validateErrors($this->MovementEstimate));
                throw new Exception();
              }
            }
          }
        }
        if (!empty($this->request->data['Sale'])){
          foreach ($this->request->data['Sale'] as $saleEstimateDate=>$saleFuelData){
            foreach ($saleFuelData['Fuel'] as $fuelId=>$fuelQuantity){
              $movementEstimateArray=[
                'MovementEstimate'=>[
                  'enterprise_id'=>$enterpriseId,
                  'estimate_date'=>$saleEstimateDate,
                  'product_id'=>$fuelId,
                  'product_quantity'=>$fuelQuantity,
                  'bool_sale'=>true,
                  'user_id'=>$loggedUserId,
                ],
              ];
              //pr($movementEstimateArray);
              $this->MovementEstimate->create();
              if (!$this->MovementEstimate->save($movementEstimateArray)) {
                echo "problema guardando las estimaciones de ventas";
                pr($this->validateErrors($this->MovementEstimate));
                throw new Exception();
              }
            }
          }
        }
        $datasource->commit();
        $this->recordUserAction($this->MovementEstimate->id,"add",null);
        // SAVE THE USERLOG FOR THE PURCHASE
        $this->recordUserActivity($this->Session->read('User.username'),"Se registraron estimados de entradas y ventas");
        $this->Session->setFlash(__('Se guardaron los etimados.'),'default',['class' => 'success']);
        //return $this->redirect(['action' => 'resumenEntradas']);
      } 
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash(__('No se guardaron los estimados.'), 'default',['class' => 'error-message']);
      }
              
       //$startDateArray=$this->request->data['Report']['startdate'];
      //$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			//$startDate=date( "Y-m-d", strtotime($startDateString));
		
			//$endDateArray=$this->request->data['Report']['enddate'];
			//$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			//$endDate=date("Y-m-d",strtotime($endDateString));
			//$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		/*
    else if (!empty($_SESSION['startDateEstimations']) && !empty($_SESSION['endDateEstimations'])){
			//$startDate=$_SESSION['startDateEstimations'];
			
      //$endDate=$_SESSION['endDateEstimations'];
			//$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			//$firstDayThisMonth = date("Y-m-01");
      //$startDate= date( "Y-m-d", strtotime( date("Y-m-01")."-3 months" ) );
			
      //$endDate=date( "Y-m-d", strtotime( date("Y-m-01")."-1 days" ) );
      //$endDatePlusOne= date("Y-m-01");
		}
		
		$_SESSION['startDateEstimations']=$startDate;
		//$_SESSION['endDateEstimations']=$endDate;
		*/
	
    //pr($movementEstimateConditions);
    $movementEstimates=$this->MovementEstimate->find('all',[
      'fields'=>['MovementEstimate.id','MovementEstimate.estimate_date','MovementEstimate.product_id',
      'MovementEstimate.product_quantity',
      'MovementEstimate.bool_sale',],
      'conditions'=>$movementEstimateConditions,
      'order'=>['estimate_date','bool_sale'],
    ]);
    //pr($movementEstimates);
    $this->set(compact('movementEstimates'));
    $estimates=[];
    if (!empty($movementEstimates)){
      $estimates=[
        'Entry'=>[],
        'Sale'=>[],
      ];
      $initialFuelEstimates=[];
      foreach (array_keys($fuels) as $fuelId){
        $initialFuelEstimates[$fuelId]=0;
      }
      foreach ($movementEstimates as $estimate){
        if (!array_key_exists($estimate['MovementEstimate']['estimate_date'],$estimates['Entry'])){
          $estimates['Entry'][$estimate['MovementEstimate']['estimate_date']]=['Fuel'=>$initialFuelEstimates];
          $estimates['Sale'][$estimate['MovementEstimate']['estimate_date']]=['Fuel'=>$initialFuelEstimates];
        }
        if ($estimate['MovementEstimate']['bool_sale']){
          $estimates['Sale'][$estimate['MovementEstimate']['estimate_date']]['Fuel'][$estimate['MovementEstimate']['product_id']]=$estimate['MovementEstimate']['product_quantity'];
        }
        else {
          $estimates['Entry'][$estimate['MovementEstimate']['estimate_date']]['Fuel'][$estimate['MovementEstimate']['product_id']]=$estimate['MovementEstimate']['product_quantity'];
        }
      }
    }
    //pr($estimates);
    $this->set(compact('estimates'));
  }
	
	public function guardarReporteEstimacionesCompras($fileName){
		$exportData=$_SESSION['reporteEstimacionesCompras'];
		$this->set(compact('exportData','fileName'));
	}
  
  

}
