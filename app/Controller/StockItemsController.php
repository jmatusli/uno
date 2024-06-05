<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');


class StockItemsController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 
   
  public function beforeFilter(){
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('getStockItemInfo','saveStockItemInfo');
	} 
   
  public function getStockItemInfo(){
    $stockItemCount=$this->StockItem->find('count');
    $this->Paginator->settings = [
      'fields'=>[ 'StockItem.id','StockItem.name',
                  'StockItem.stockitem_creation_date','StockItem.remaining_quantity'],
      'contain'=>[
        'StockItemLog'=>[
          'fields'=>['StockItemLog.stock_item_date,StockItemLog.product_quantity'],
          'order'=>'StockItemLog.id DESC',
          'limit'=>1,
        ]
      ],
      'limit'=>$stockItemCount,
    ];
    $stockItems = $this->Paginator->paginate('StockItem');;
    $this->set(compact('stockItems'));
  } 
  public function saveStockItemInfo() {
		$exportData=$_SESSION['stockItemInfo'];
		$this->set(compact('exportData'));
	}	
  
	public function inventario() {
		$this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
		
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		$inventoryDate = null;
		//$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $enterpriseId=0;
    
    define('DISPLAY_STOCK','0');
    define('DISPLAY_ALL','1');
    $displayOptions=[
      DISPLAY_STOCK=>"Mostrar solo stock",
      DISPLAY_ALL=>"Mostrar todos",
    ];
    $this->set(compact('displayOptions'));
    $displayOptionId=DISPLAY_STOCK;
		
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
      $displayOptionId=$this->request->data['Report']['display_option_id'];
      
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
		else if (!empty($_SESSION['inventoryDate'])){
			$inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    $_SESSION['inventoryDate']=$inventoryDate;
    $this->set(compact('inventoryDate'));
		$this->set(compact('displayOptionId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    
		$this->ProductCategory->recursive=-1;		
		$productCategories=$this->ProductCategory->find('all',[
			'contain'=>[
				'ProductType',
			],
		]);
		
    for ($pc=0;$pc<count($productCategories);$pc++){
			for ($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
        $productCategories[$pc]['ProductType'][$pt]['products']=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$enterpriseId,false);
			}
    }
		$this->set(compact('productCategories'));
    
    $this->loadModel('Tank');
    $tankData=$this->Tank->getTankGraphData($inventoryDate);
    //pr($tankData);
    $this->set(compact('tankData'));
    if ($enterpriseId > 0){
      $filename=$enterprises[$enterpriseId]."_Hoja_Inventario_".date('d_m_Y'); 
    }
		$this->set(compact('filename'));
	}

	public function guardarReporteInventario() {
		$exportData=$_SESSION['inventoryReport'];
		$this->set(compact('exportData'));
	}	

  public function ajustesInventario($warehouseId=0,$productTypeId=0) {
		$this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
		
		$inventoryDate = null;
		$warehouseId=WAREHOUSE_DEFAULT;
    
    define('DISPLAY_STOCK','0');
    define('DISPLAY_ALL','1');
    //$displayOptions=[
    //  DISPLAY_STOCK=>"Mostrar solo stock",
    //  DISPLAY_ALL=>"Mostrar todos",
    //];
    //$this->set(compact('displayOptions'));
    $displayOptionId=DISPLAY_STOCK;
		
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
			$warehouseId=$this->request->data['Report']['warehouse_id'];
      $productTypeId=$this->request->data['Report']['product_type_id'];
      //$displayOptionId=$this->request->data['Report']['display_option_id'];
		}
		else if (!empty($_SESSION['inventoryDate'])){
			$inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$_SESSION['inventoryDate']=$inventoryDate;
		
		$this->set(compact('inventoryDate'));
		$this->set(compact('warehouseId'));
    $this->set(compact('productTypeId'));
    $this->set(compact('displayOptionId'));
		
		$this->ProductType->recursive=-1;
		
    $productTypeConditions=[
      'ProductType.id !='=>PRODUCT_TYPE_SERVICE,
    ];
    if ($productTypeId>0){
      $productTypeConditions[]=['ProductType.id'=>$productTypeId];
    }
    
		$allInventoryProductTypes=$this->ProductType->find('all',[
      'conditions'=>$productTypeConditions,
      'contain'=>[
        'ProductCategory',
      ],
    ]);
		for ($pt=0;$pt<count($allInventoryProductTypes);$pt++){
      //pr($productCategories[$pc]['ProductType'][$pt]);
      $productTypeId=$allInventoryProductTypes[$pt]['ProductType']['id'];
      switch ($productTypeId){
        case PRODUCT_TYPE_BOTTLE:
          $bottles=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
          break;
        case PRODUCT_TYPE_PREFORMA:
        case PRODUCT_TYPE_CAP:
        default:
          $products=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
          $allInventoryProductTypes[$pt]['products']=$products;
      }
    }
    $this->set(compact('bottles'));
    $this->set(compact('allInventoryProductTypes'));
    //pr($allInventoryProductTypes);
				
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',['order'=>'Warehouse.name',]);
		$this->set(compact('warehouses'));
    
    $productTypes=$this->ProductType->find('list',[
      'fields'=>['ProductType.id','ProductType.name'],
			'conditions'=>['ProductType.id !='=>PRODUCT_TYPE_SERVICE],
      'order'=>'ProductType.name',
		]);
    $this->set(compact('productTypes'));
	}
  
  public function detalleCostoProducto() {
    
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
    
		$inventoryDate = null;
    $finishedProductId=0;
    $rawMaterialId=0;
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
      
      $finishedProductId=$this->request->data['Report']['finished_product_id'];
      $rawMaterialId=$this->request->data['Report']['raw_material_id'];
		}
		if (!isset($inventoryDate)){
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
			$inventoryDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['inventoryDate']=$inventoryDate;
		$this->set(compact('inventoryDate'));
    $this->set(compact('finishedProductId'));
    $this->set(compact('rawMaterialId'));
		
		$this->StockItem->recursive=-1;
		
		if (!empty($finishedProductId) && !empty($rawMaterialId)){
      $stockItems=$this->StockItem->getInventoryFinishedProduct($finishedProductId,$rawMaterialId,$inventoryDate);
    }
    $this->set(compact('stockItems'));
    
    $finishedProductProductTypeList=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_PRODUCED,
      ),
    ));
    $rawMaterialProductTypeList=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_RAW,
      ),
    ));
    
    $finishedProducts=$this->Product->find('list',array(
      'conditions'=>array(
        'Product.product_type_id'=>$finishedProductProductTypeList,
      ),
      'order'=>'Product.name',
    ));
		$this->set(compact('finishedProducts'));
		$rawMaterials=$this->Product->find('list',array(
      'conditions'=>array(
        'Product.product_type_id'=>$rawMaterialProductTypeList,
      ),
      'order'=>'Product.name',
    ));
		$this->set(compact('rawMaterials'));
    
    $productionResultCodes=$this->ProductionResultCode->find('list',array(
      'order'=>'ProductionResultCode.code',
    ));
		$this->set(compact('productionResultCodes'));
    //pr($productionResultCodes);
	}

	public function guardarDetalleCostoProducto() {
		$exportData=$_SESSION['inventoryReport'];
		$this->set(compact('exportData'));
	}	
   
	public function verPdfHojaInventario($inventoryDate=null,$warehouseId=0) {
	
		if ($inventoryDate==null){
			$startDateString=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDateString=$inventoryDate;
		}
		$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
		$this->set(compact('inventoryDate','inventoryDatePlusOne'));
		
    $this->loadModel('ProductCategory');
    $productCategories=$this->ProductCategory->find('all',array(
			'contain'=>array(
				'ProductType',
			),
		));

		for ($pc=0;$pc<count($productCategories);$pc++){
			for ($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
				//pr($productCategories[$pc]['ProductType'][$pt]);
				switch ($productCategories[$pc]['ProductType'][$pt]['id']){
					case PRODUCT_TYPE_PREFORMA:
						//$preformas=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						//$this->set(compact('preformas'));
						break;
					case PRODUCT_TYPE_BOTTLE:
						$productos=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						$this->set(compact('productos'));
						break;
					case PRODUCT_TYPE_CAP:
						$tapones=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						$this->set(compact('tapones'));
						break;
				}
			 }
		}
		
		$filename="Hoja_Inventario_".date('d_m_Y');
		$this->set(compact('filename'));
	}

	
/******************** REPORTES PRODUCTOS Y PRODUCTO *******************/	
	
	public function verReporteProductos($startDate = null,$endDate=null) {
    $model=$this;
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
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		//echo $endDatePlusOne;
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		//echo "session startDate is ".$_SESSION['startDate']."<br/>";
		//echo "session endDate is ".$_SESSION['endDate']."<br/>";
		
		$this->loadModel('Product');
		$this->loadModel('ProductionMovement');
		
    $model->loadModel('Product');
    
    $allRawMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_RAW.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->Product->find('all',[
    
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id ='=> CATEGORY_RAW,
        ],
        'contain'=>[
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
          'ProductionRunInput'=>[
          // CONDITIONS ADDED 20160202
            'conditions'=>[
              'ProductionRunInput.production_run_date >='=> $startDate,
              'ProductionRunInput.production_run_date <' => $endDatePlusOne,
            ],
          ],
          'ProductType',
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');  
    /*
		$allRawMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_RAW,
			),
			'contain'=>array(
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
				'ProductionRunInput'=>array(
				// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'ProductionRunInput.production_run_date >='=> $startDate,
						'ProductionRunInput.production_run_date <' => $endDatePlusOne,
					),
				),
				'ProductType',
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allRawMaterials as $rawMaterial){
			$productId=$rawMaterial['Product']['id'];
			$productName=$rawMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productPurchasedQuantity=0;
			$productPurchasedValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productConsumedQuantity=0;
			$productConsumedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			foreach ($rawMaterial['StockMovement'] as $stockMovement){			
				if ($stockMovement['bool_input']){
					//echo "recognized as input";
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] < $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							//echo "this is a relevant one";
							$productPurchasedQuantity+=$stockMovement['product_quantity'];
							//echo "summed to purchasedquantity quantity".$productPurchasedQuantity;
							$productPurchasedValue+=$stockMovement['product_total_price'];
						}
						else {
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							//echo "summed to purchasedquantity quantity".$productPurchasedQuantity;
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			foreach ($rawMaterial['ProductionRunInput'] as $productionRun){
				// 20160202 CONDITIONS REMOVED
				//if ($productionRun['production_run_date'] >= $startDate && $productionRun['production_run_date'] <= $endDatePlusOne){
					$productionRunId=$productionRun['id'];
					// RECURSIVE ADDED 20151201
					$model->ProductionMovement->recursive=-1;
          $allProductionMovementsForProductionRun = Cache::remember('stockitem_reporteproductos_productionmovements_input_'.$productionRunId, function() use ($model,$productionRunId){
            return $model->ProductionMovement->find('all',[
              'fields'=>[
                  'ProductionMovement.product_quantity','ProductionMovement.product_unit_cost',
                ],
                'conditions' => [
                  'ProductionMovement.production_run_id'=> $productionRunId,
                  'ProductionMovement.bool_input'=> true,
                ]
            ]);
           }, 'long'); 
          /*
					$allProductionMovementsForProductionRun = $this->ProductionMovement->find('all', array(
						//FIELDS ADDED 20151201
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_cost',
						),
						'conditions' => array(
							'ProductionMovement.production_run_id'=> $productionRunId,
							'ProductionMovement.bool_input'=> true,
						)
					));
					*/
					foreach ($allProductionMovementsForProductionRun as $productionMovement){
						//pr($productionMovement);					
						$productConsumedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productConsumedValue+=$productionMovement['ProductionMovement']['product_unit_cost']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			$this->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
      // RECURSIVE ADDED 20151201
      /*
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
          'StockItem.product_id'=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				//pr($stockItemForProduct);
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				
				//get the last stockitem log before the startdate to determine the initial stock
        $model->StockItem->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id ='=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$this->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions' => array(
						'StockItemLog.stock_item_id ='=> $stockitemId,
						'StockItemLog.stock_item_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
				
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id ='=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions' => array(
						'StockItemLog.stock_item_id ='=> $stockitemId,
						'StockItemLog.stock_item_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($finalStockItemLogForStockItem)){
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}
				
				
				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$rawMaterials[$i]['id']=$productId;
			$rawMaterials[$i]['name']=$productName;
			$rawMaterials[$i]['unit_price']=$productUnitPrice;
			$rawMaterials[$i]['initial_quantity']=$productInitialStock;
			$rawMaterials[$i]['initial_value']=$productInitialValue;
			$rawMaterials[$i]['purchased_quantity']=$productPurchasedQuantity;
			$rawMaterials[$i]['purchased_value']=$productPurchasedValue;
			$rawMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$rawMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$rawMaterials[$i]['used_quantity']=$productConsumedQuantity;
			$rawMaterials[$i]['used_value']=$productConsumedValue;
			$rawMaterials[$i]['final_quantity']=$productFinalStock;
			$rawMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}
		
		/*********************************************************
		PRODUCED MATERIALS
		*********************************************************/
		$allProducedMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_PRODUCED.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->Product->find('all',[
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id ='=> CATEGORY_PRODUCED,
        ],
        'contain'=>[
          'ProductType',
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
          'ProductionRunOutput'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'ProductionRunOutput.production_run_date >='=> $startDate,
              'ProductionRunOutput.production_run_date <' => $endDatePlusOne,
            ],
          ],
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');
    /*
		$allProducedMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_PRODUCED,
			),
			'contain'=>array(
				'ProductType',
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
				'ProductionRunOutput'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'ProductionRunOutput.production_run_date >='=> $startDate,
						'ProductionRunOutput.production_run_date <' => $endDatePlusOne,
					),
				),
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allProducedMaterials as $producedMaterial){
			//pr($producedMaterial);
			$productId=$producedMaterial['Product']['id'];
			$productName=$producedMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productProducedQuantity=0;
			$productProducedValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productSoldQuantity=0;
			$productSoldValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			
			foreach ($producedMaterial['StockMovement'] as $stockMovement){
				//pr($stockMovement);
				if (!$stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$productSoldQuantity+=$stockMovement['product_quantity'];
							$linkedStockItem=$this->StockItem->find('first',[
                'fields'=>'StockItem.product_unit_cost',
                'conditions'=>[
                  'StockItem.id'=>$stockMovement['stock_item_id']
                ]
              ]);
							$productSoldValue+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_cost'];
						}
						else {
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							//if ($productId==13){
							//	echo "summed to product reclassified quantity".$stockMovement['product_quantity'];
							//	echo "resulting reclassified quantity is ".$productReclassifiedQuantity."<br/>";
							//}
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
				else {
					if ($stockMovement['bool_reclassification']){
						$productReclassifiedQuantity-=$stockMovement['product_quantity'];
						//if ($productId==13){
						//	echo "rested from product reclassified quantity".$stockMovement['product_quantity'];
						//	echo "resulting reclassified quantity is ".$productReclassifiedQuantity."<br/>";
						//}
						$productReclassifiedValue-=$stockMovement['product_total_price'];
					}
				}
			}
			
			
			foreach ($producedMaterial['ProductionRunOutput'] as $productionRun){
				// 20160202 CONDITIONS REMOVED
				//if ($productionRun['production_run_date'] >= $startDate && $productionRun['production_run_date'] <= $endDatePlusOne){
					$productionRunId=$productionRun['id'];
					// RECURSIVE ADDED 20151201
					$model->ProductionMovement->recursive=-1;
          $allProductionMovementsForProductionRun = Cache::remember('stockitem_reporteproductos_productionmovements_'.$productionRunId, function() use ($model,$productionRunId){
            return $model->ProductionMovement->find('all',[
              'fields'=>[
                  'ProductionMovement.product_quantity','ProductionMovement.product_unit_cost',
                ],
                'conditions' => [
                  'ProductionMovement.production_run_id ='=> $productionRunId,
                  'ProductionMovement.bool_input ='=> false,
                ]
            ]);
           }, 'long'); 
          /*
					$allProductionMovementsForProductionRun = $this->ProductionMovement->find('all', array(
						//FIELDS ADDED 20151201
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_cost',
						),
						'conditions' => array(
							'ProductionMovement.production_run_id ='=> $productionRunId,
							'ProductionMovement.bool_input ='=> false,
						)
					));
					*/
					foreach ($allProductionMovementsForProductionRun as $productionMovement){					
						$productProducedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productProducedValue+=$productionMovement['ProductionMovement']['product_unit_cost']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			
			// RECURSIVE ADDED 20151201
			$model->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
       /*
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
					'StockItem.product_id ='=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				$model->StockItem->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id ='=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions' => array(
						'StockItemLog.stock_item_id ='=> $stockitemId,
						'StockItemLog.stock_item_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id ='=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions' => array(
						'StockItemLog.stock_item_id ='=> $stockitemId,
						'StockItemLog.stock_item_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($finalStockItemLogForStockItem)){
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}
				//pr($stockItemForProduct);
				
				
				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$producedMaterials[$i]['id']=$productId;
			$producedMaterials[$i]['name']=$productName;
			$producedMaterials[$i]['unit_price']=$productUnitPrice;
			$producedMaterials[$i]['initial_quantity']=$productInitialStock;
			$producedMaterials[$i]['initial_value']=$productInitialValue;
			$producedMaterials[$i]['produced_quantity']=$productProducedQuantity;
			$producedMaterials[$i]['produced_value']=$productProducedValue;
			$producedMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$producedMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$producedMaterials[$i]['sold_quantity']=$productSoldQuantity;
			$producedMaterials[$i]['sold_value']=$productSoldValue;
			$producedMaterials[$i]['final_quantity']=$productFinalStock;
			$producedMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}
		
		/*********************************************************
		TAPONES
		*********************************************************/
		$allOtherMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_OTHER.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->Product->find('all',[
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id ='=> CATEGORY_OTHER,
        ],
        'contain'=>[
          'ProductType',
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');
    /*
		$allOtherMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_OTHER,
			),
			'contain'=>array(
				'ProductType',
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allOtherMaterials as $otherMaterial){
			$productId=$otherMaterial['Product']['id'];
			$productName=$otherMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productPurchasedQuantity=0;
			$productPurchasedValue=0;
			$productSoldQuantity=0;
			$productSoldValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
				if ($stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$productPurchasedQuantity+=$stockMovement['product_quantity'];
							$productPurchasedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
				if (!$stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						$productSoldQuantity+=$stockMovement['product_quantity'];
						$productSoldValue+=$stockMovement['product_total_price'];
					//}
				} 
				
				if ($stockMovement['bool_reclassification']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if ($stockMovement['bool_input']){
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
						else {
							$productReclassifiedQuantity-=$stockMovement['product_quantity'];
							$productReclassifiedValue-=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			// RECURSIVE ADDED 20151201
			$model->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
      /*    
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
					'StockItem.product_id ='=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			$this->loadModel('StockItemLog');
			foreach ($allStockItemsForProduct as $stockItem){
        $stockitemId=$stockItem['StockItem']['id'];
        
				$model->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id'=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$initialStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions'=>array(
						'StockItemLog.stock_item_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stock_item_date <'=>$startDate,
					),
					'order'=>'StockItemLog.id DESC'
				));
				*/
				if (!empty($initialStockItemLogForStockItem)){
					//pr ($initialStockItemLogForStockItem);
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}
				$this->StockItemLog->recursive=-1;
        
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions' => [
              'StockItemLog.stock_item_id ='=> $stockitemId,
              'StockItemLog.stock_item_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				
				$finalStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_cost'),
					'conditions'=>array(
						'StockItemLog.stock_item_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stock_item_date <'=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC'
				));
				*/

				if (!empty($finalStockItemLogForStockItem)){
					//pr ($finalStockItemLogForStockItem);
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_cost'];
				}

				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$otherMaterials[$i]['id']=$productId;
			$otherMaterials[$i]['name']=$productName;
			$otherMaterials[$i]['unit_price']=$productUnitPrice;
			$otherMaterials[$i]['initial_quantity']=$productInitialStock;
			$otherMaterials[$i]['initial_value']=$productInitialValue;
			$otherMaterials[$i]['purchased_quantity']=$productPurchasedQuantity;
			$otherMaterials[$i]['purchased_value']=$productPurchasedValue;
			$otherMaterials[$i]['sold_quantity']=$productSoldQuantity;
			$otherMaterials[$i]['sold_value']=$productSoldValue;
			$otherMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$otherMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$otherMaterials[$i]['final_quantity']=$productFinalStock;
			$otherMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}		
		$this->set(compact('rawMaterials','producedMaterials','otherMaterials','startDate','endDate'));
	}
		
	public function estadoResultados($startDate = null,$endDate=null) {
		$this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('Product');
    $this->loadModel('StockItemLog');
    $this->loadModel('Enterprise');
    
    $this->loadModel('ThirdParty');
		$this->loadModel('PaymentReceipt');
		$this->loadModel('StockMovement');
    
    $startDate = null;
		$endDate = null;
    
    //$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
    
    
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $allFuels=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => ['Product.product_type_id'=> PRODUCT_TYPE_FUELS,],
			'contain'=>[
				'StockMovement'=>[
          'conditions'=>[
            'StockMovement.movement_date >=' =>$startDate,
            'StockMovement.movement_date <' => $endDatePlusOne,
            'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE,MOVEMENT_ADJUSTMENT_CALIBRATION],
            'StockMovement.product_quantity > '=>0,
            'StockMovement.enterprise_id' =>$enterpriseId,
          ],
          'StockItem'=>[
            'conditions'=>['enterprise_id'=>$enterpriseId],
          ],
        ]
			],
		]);
		
    $fuels=[];
		$i=0;
		foreach ($allFuels as $fuel){
			$productId=$fuel['Product']['id'];
			$productName=$fuel['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
      
			
			foreach ($fuel['StockMovement'] as $stockMovement){
        if ($stockMovement['bool_input']){
          $productTotalQuantity+=$stockMovement['product_quantity'];
          $productTotalValueCost+=$stockMovement['product_quantity']*$this->StockItemLog->getProductCost($stockMovement['product_id'],$stockMovement['movement_date'],$enterpriseId);
          $productTotalValuePrice+=$stockMovement['product_total_price'];
        }
        else {
          $productTotalQuantity+=$stockMovement['product_quantity'];
          $productTotalValueCost+=$stockMovement['product_quantity']*$this->StockItemLog->getProductCost($stockMovement['product_id'],$stockMovement['movement_date'],$enterpriseId);
          $productTotalValuePrice+=$stockMovement['product_total_price']; 
        }
			}
			
			$fuels[$i]['id']=$productId;
			$fuels[$i]['name']=$productName;
			$fuels[$i]['total_quantity']=$productTotalQuantity;
			$fuels[$i]['total_price']=$productTotalValuePrice;
			$fuels[$i]['total_cost']=$productTotalValueCost;
			$fuels[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}
    $this->set(compact('fuels'));
		
		$this->ThirdParty->recursive=0;
		$clients=$this->ThirdParty->find('list',[
      'fields'=>['ThirdParty.id','ThirdParty.company_name', ],
			'conditions'=>[
				'bool_provider'=>false,
				'ThirdParty.bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
		//pr($clients);
    $this->set(compact('clients'));

    $this->PaymentReceipt->virtualFields['total_client']=0;
    $salesClientPeriod=$this->PaymentReceipt->find('all',[
      'fields'=>['client_id', 'SUM(PaymentReceipt.payment_amount) AS PaymentReceipt__total_client'],
      'conditions'=>[
        'PaymentReceipt.client_id'=>array_keys($clients),
        'PaymentReceipt.payment_date >='=>$startDate,
        'PaymentReceipt.payment_date <'=>$endDatePlusOne,
      ],
      'group'=>'client_id',
      'order'=>'PaymentReceipt__total_client DESC',
    ]);
    //pr($salesClientPeriod);
		$this->set(compact('salesClientPeriod'));
    
	}
	
	public function guardarReporteEstado() {
		$exportData=$_SESSION['statusReport'];
		$this->set(compact('exportData'));
	}
	
	public function verReporteProducto($id) {
		$this->loadModel('Product');
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItemLog');
		
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
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
		
		$productData=$this->Product->find('first',
			array(
				'contain'=>array(
					'ProductType'=>array('fields'=>'product_category_id')
				),
				'conditions' => array('Product.id'=> $id)
			)
		);
		
		$this->Product->recursive=0;
		$allFinishedProducts=$this->Product->find('all',
			array(
				'fields'=>array('id','name'),
				'conditions'=>array(
					'ProductType.product_category_id'=>CATEGORY_PRODUCED
				),
				'order'=>'name ASC'
			)
		);
		$this->ProductionResultCode->recursive=0;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		
		$initialStock=0;
		
		$this->StockItem->recursive=0;
		$initialStockItems=$this->StockItem->find('all',array(
			'fields'=>'StockItem.id',
			'conditions'=>array(
				'product_id'=>$id,
        // CONDITIONS ADDED 20180314
        'StockItem.stockitem_creation_date <'=> $startDate,        
        'StockItem.stockitem_depletion_date >='=> $startDate,
			)
		));
		//pr($initialStockItems);
		$this->StockItemLog->recursive=0;
		foreach ($initialStockItems as $initialStockItem){
			$initialStockItemLogs=$this->StockItemLog->find('first',array(
				'conditions'=>array(
					'StockItemLog.stock_item_id'=>$initialStockItem['StockItem']['id'],
					'StockItemLog.stock_item_date <'=>$startDate,
				),
				'order'=>'StockItemLog.id DESC'
			));
			
			if (!empty($initialStockItemLogs)){
				//pr ($initialStockItemLogs);
				$initialStock+=$initialStockItemLogs['StockItemLog']['product_quantity'];
			}
		}
		
		$reclassified=0;
		$this->loadModel('StockMovement');
		$this->StockMovement->virtualFields['total_reclassified']=0;
		$reclassificationStockMovements=$this->StockMovement->find('first',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
			'conditions'=>array(
				'StockMovement.bool_reclassification'=>true,
				'StockMovement.bool_input'=>true,
				'StockMovement.product_id'=>$id,
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
			),
		));
		$rawreclassified=$reclassificationStockMovements['StockMovement']['total_reclassified'];
			
		$finishedreclassified=array();
		foreach ($allFinishedProducts as $finishedProduct){
			foreach ($allProductionResultCodes as $productionResultCode){								
				$reclassificationIncomingStockMovements=$this->StockMovement->find('first',array(
					'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
					'conditions'=>array(
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>true,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					),
				));
				//pr($reclassificationIncomingStockMovements);
				
				$reclassificationOutgoingStockMovements=$this->StockMovement->find('first',array(
					'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
					'conditions'=>array(
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>false,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					),
				));
				//pr($reclassificationOutgoingStockMovements);
			
				if (!empty($reclassificationIncomingStockMovements)){
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedreclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified']-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedreclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified'];
					}
				}
				else {
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedreclassified[]=0-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedreclassified[]=0;
					}
				}
			}
		}
		
		//pr($finishedreclassified);
		
		
		$stockItemsForPeriodWithProductionRuns=$this->ProductionMovement->find('list', array(
			'fields'=>array('ProductionMovement.stock_item_id'),
			'conditions'=>array(
				'ProductionMovement.product_id'=> $id,
				'ProductionMovement.bool_input'=> true,
				'ProductionMovement.movement_date >='=> $startDate,
				'ProductionMovement.movement_date <'=> $endDatePlusOne,
			),
		));
		
		$stockItemsWithoutProductionRun=$this->StockItem->find('list', array(
			'fields'=>array('id'),
			'conditions'=>array(
				'StockItem.product_id'=> $id,
				'StockItem.stockitem_creation_date >='=> $startDate,
				'StockItem.stockitem_creation_date <'=> $endDatePlusOne,
			),
		));
		
		$stockItemsForPeriod=array_merge($stockItemsForPeriodWithProductionRuns,$stockItemsWithoutProductionRun);
		
		//pr($stockItemsForPeriod);
		
		$thisProductOrders=$this->Order->find('all',
			array(
				'fields'=>array('id','order_date','order_code'),
				'contain'=>array(
					'ThirdParty'=>array('fields'=>'company_name'),
					'StockMovement'=>array(
						'fields'=>array('id','movement_date','order_id','stock_item_id','product_quantity','product_unit_cost','product_total_price'),
						'conditions' => array(
							'StockMovement.product_id'=> $id,
							'StockMovement.bool_input'=> true,
							'StockMovement.stock_item_id'=> $stockItemsForPeriod,
						),
						'order'=>'movement_date ASC',
						'Product'=>array(
							'fields'=>array('id','packaging_unit'),	
							'ProductType'=>array('fields'=>'product_category_id'),
						),
						
					)
				),
				'order'=>'order_date ASC'
			)
		);
		$productOrderCount=0;
		
		foreach ($thisProductOrders as $productOrder){
			$purchaseMovementCount=0;
			foreach ($productOrder['StockMovement'] as $purchaseMovement){
				$stockitemid=$purchaseMovement['stock_item_id'];
				$productionMovementsForPurchaseMovement=$this->ProductionMovement->find('all',
					array(
						'fields'=>array('id','stock_item_id','product_id','product_quantity','product_unit_cost','production_run_id'),
						'conditions' => array('stock_item_id'=> $stockitemid),	
						'contain'=>array(
							'ProductionRun'=>array(
								'fields'=>array('id','production_run_code','production_run_date'),
								'ProductionMovement'=>array(
									'fields'=>array('id','stock_item_id','product_id','product_quantity','product_unit_cost','production_run_id'),
									'conditions'=>array(
										'bool_input'=>false,
										'ProductionMovement.movement_date >='=> $startDate,
										'ProductionMovement.movement_date <'=> $endDatePlusOne,
									),
									'StockItem'=>array(
										'fields'=>array('production_result_code_id','product_id')
									)
								)
							)
						)
					)
				);
				
				$productionRunCount=0;
				
				foreach ($productionMovementsForPurchaseMovement as $productionRunMovement){
					$productionRunId=$productionRunMovement['ProductionRun']['id'];
					$produced=array();
					$producedRun=null;
					foreach($productionRunMovement['ProductionRun']['ProductionMovement'] as $movementForRun){
						$producedRun=array();
						
						$productidForMovement=$movementForRun['product_id'];
						$productionresultcodeForMovement=$movementForRun['StockItem']['production_result_code_id'];
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($allProductionResultCodes as $productionResultCode){								
								if ($productidForMovement==$finishedProduct['Product']['id'] && $productionresultcodeForMovement==$productionResultCode['ProductionResultCode']['id']){
									$producedRun[]=$movementForRun['product_quantity'];
								}
								else{
									$producedRun[]=0;
								}
							}
						}
									
						foreach (array_keys($producedRun + $produced) as $key) {
							$produced[$key] = (isset($producedRun[$key]) ? $producedRun[$key] : 0) + (isset($produced[$key]) ? $produced[$key] : 0);
						}
					}
					
					array_push($productionMovementsForPurchaseMovement[$productionRunCount]['ProductionRun'],$produced);
					$productionRunCount++;
				}
				array_push($thisProductOrders[$productOrderCount]['StockMovement'][$purchaseMovementCount],$productionMovementsForPurchaseMovement);
				$purchaseMovementCount++;
			}
			$productOrderCount++;
		}
		
		
		$this->set(compact('productData','thisProductOrders','startDate','endDate','endDatePlusOne','allFinishedProducts','allProductionResultCodes','finalStock','initialStock','rawreclassified','finishedreclassified'));

	}
	
	public function guardarReporteProductos() {
		$exportData=$_SESSION['productsReport'];
		$this->set(compact('exportData'));
	}
	
/******************** CUADRAR LOTES *******************/
	
	public function cuadrarEstadosDeLote(){
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('ProductType');
		$this->loadModel('ProductCategory');
		$this->loadModel('Product');

    $startDate = date("2019-05-01");  
    
		if ($this->request->is('post')) {
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
      
			//$productCategoryId=$this->request->data['Report']['product_category_id'];      
		}
		else if ($this->Session->check('productCategoryId')){
			//$productCategoryId=$_SESSION['productCategoryId'];
		}
		
    $this->set(compact('startDate'));
    
		//$_SESSION['productCategoryId']=$productCategoryId;
		//$this->set(compact('productCategoryId'));
    		
		$productCategories=$this->ProductCategory->find('all',[
      'fields'=>['ProductCategory.id', 'ProductCategory.name'],
      'contain'=>[
        'ProductType'=>[
          'fields'=>['ProductType.id', 'ProductType.name'],
          'Product'=>[
            'fields'=>['Product.id', 'Product.name'],
            'StockItem'=>[
              'fields'=>['StockItem.id', 'StockItem.remaining_quantity'],
              'conditions'=>['StockItem.bool_active'=>true,],
            ],   
            'StockMovement'=>[
              'fields'=>['StockMovement.product_quantity', 'StockMovement.bool_input'],
            ],  
            'order'=>'Product.product_order',
          ],
        ],
      ],
    ]);
		
    for ($pc=0;$pc<count($productCategories);$pc++){
      for($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
        for ($p=0;$p<count($productCategories[$pc]['ProductType'][$pt]['Product']);$p++){
          $totalInMovements=0;
          $totalOutMovements=0;
          foreach ($productCategories[$pc]['ProductType'][$pt]['Product'][$p]['StockMovement'] as $stockMovement){
            if ($stockMovement['bool_input']){
              $totalInMovements+=$stockMovement['product_quantity'];
            }
            else {
              $totalOutMovements+=$stockMovement['product_quantity'];
            }
          }
          $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['total_in_movements']=$totalInMovements;
          $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['total_out_movements']=$totalOutMovements;
          $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['total_saldo_stock_item']=$productCategories[$pc]['ProductType'][$pt]['Product'][$p]['StockItem'][0]['remaining_quantity'];
          $lastStockItemLog=$this->StockItemLog->find('first',[
            'fields'=>['StockItemLog.id, StockItemLog.product_quantity'],
            'conditions'=>[
              'StockItemLog.stock_item_id'=>$productCategories[$pc]['ProductType'][$pt]['Product'][$p]['StockItem'][0]['id'],
            ],
            'order'=>['StockItemLog.id DESC,StockItemLog.stock_item_date DESC'],
          ]);
          //echo "stockitemlog for stockitem ".$allStockItems[$i]['StockItem']['id']."<br/>";
          //pr($lastStockItemLog);
          if (!empty($lastStockItemLog['StockItemLog'])){
            $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['total_saldo_stock_item_log']=$lastStockItemLog['StockItemLog']['product_quantity'];
            $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['stock_item_log_id']=$lastStockItemLog['StockItemLog']['id'];
          }
          else {
            $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['total_saldo_stock_item_log']=0;
            $productCategories[$pc]['ProductType'][$pt]['Product'][$p]['stock_item_log_id']=0;
          }
        }        
      }
    }
    $this->set(compact('productCategories'));
    //pr($productCategories);
	}
	
	public function recreateStockItemLogsForSquaring($id = null) {
		$this->StockItem->id = $id;
		if (!$this->StockItem->exists()) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		$success=$this->recreateStockItemLogs($id,'2019-01-01');
		if ($success){
			$this->Session->setFlash(__('Los estados de lote han estado recreados para el lote '.$id),'default',['class' => 'success']);
		}
		else {
			$this->Session->setFlash(__('No se podían recrear los estados de lote para el lote '.$id), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'cuadrarEstadosDeLote']);
	}
	
	public function recreateAllStockItemLogs() {
		$allStockItems=$this->StockItem->find('list');
		//pr($allStockItems);
		foreach (array_keys($allStockItems) as $StockItemId){
			$success=$this->recreateStockItemLogs($StockItemId,'2019-01-01');
			if (!$success){
				$this->Session->setFlash(__('No se podían recrear los estados de lote para el lote '.$StockItemID), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'cuadrarEstadosDeLote']);
			}
		}
		$this->Session->setFlash(__('Los estados de lote han estado recreados'),'default',['class' => 'success']);
		return $this->redirect(['action' => 'cuadrarEstadosDeLote']);
	}

	public function recreateStockItemPriceForSquaring($stockitemid,$rightprice){
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try {
			$stockItemData['id']=$stockitemid;
			$stockItemData['product_unit_cost']=$rightprice;
			$logsuccess=$this->StockItem->save($stockItemData);
			if (!$logsuccess){
				echo "Error al guardar el movimiento de producción.  No se guardó<br/>";
				pr($this->validateErrors($this->StockItem));
				throw new Exception();
			}
			$datasource->commit();
			$this->Session->setFlash(__('The stock item has been saved.'), 'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'cuadrarPreciosBotellas'));
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
		}
	}
	
  public function index() {
		$this->StockItem->recursive = 0;
		$this->Paginator->settings = array(
			'conditions' => array('StockItem.remaining_quantity > '=> '0')
		);
		$stockItems = $this->Paginator->paginate('StockItem');
		$this->set(compact('stockItems'));
	}

	public function view($id = null) {
		if (!$this->StockItem->exists($id)) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		$options = array(
			'contain'=>array(
				'ProductionMovement'=>array(
					'Product'=>array(
						'fields'=> array('name')
					),
					'ProductionRun'=>array(
						'fields'=>array('id','production_run_code')
					),
				),
				'StockMovement'=>array(
					'Product'=>array(
						'fields'=> array('name')
					),
					'Order'=>array(
						'fields'=>array('order_code')
					),
				),
				'ProductionResultCode',
				'Product'=>array(
					'ProductType'=>array(
						'fields'=> array('product_category_id')
					),
				),
			),
			'conditions' => array('StockItem.' . $this->StockItem->primaryKey => $id)
		);
		$this->set('stockItem', $this->StockItem->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->StockItem->create();
			if ($this->StockItem->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$productTypes = $this->StockItem->ProductType->find('list');
		$productionResultCodes = $this->StockItem->ProductionResultCode->find('list');
		$this->set(compact('productTypes', 'productionResultCodes'));
	}

	public function edit($id = null) {
		if (!$this->StockItem->exists($id)) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StockItem->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('StockItem.' . $this->StockItem->primaryKey => $id));
			$this->request->data = $this->StockItem->find('first', $options);
		}
		$productTypes = $this->StockItem->ProductType->find('list');
		$productionResultCodes = $this->StockItem->ProductionResultCode->find('list');
		$this->set(compact('productTypes', 'productionResultCodes'));
	}
	
}


