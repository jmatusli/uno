<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductsController extends AppController {


	public $components = array('Paginator');
	public $helpers = array('PhpExcel'); 
  
  public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('');		
	}
 
  public function index() {
		$this->Product->recursive = -1;
		
		$productCount=	$this->Product->find('count', ['fields'=>['Product.id'],]);
		
		$this->Paginator->settings = [
			'contain'=>[
				'ProductType'=>['ProductCategory',],
				//'AccountingCode',
        'DefaultCostCurrency',
        'DefaultPriceCurrency',
			],
			'order' => ['ProductType.name'=>'ASC','Product.product_order'=> 'ASC','Product.name'=> 'ASC'],
			'limit'=>$productCount
		];
		$products = $this->Paginator->paginate('Product');
		$this->set(compact('products'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function view($id = null) {
    $this->loadModel('StockMovement');
    $this->loadModel('Product');
    
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
		
		$this->Set(compact('startDate','endDate'));
    $this->Set(compact('id'));
		
		$product=$this->Product->find('first', [
			'conditions' => ['Product.id'=> $id,],
			'contain'=>[
        'DefaultCostCurrency',
        'DefaultCostUnit',
        'DefaultPriceCurrency',
        'DefaultPriceUnit',
				'ProductType'=>['ProductCategory'],
        'ProductPriceLog'=>[
          'Currency',          
        ],
				//'AccountingCode',
				'StockMovement'=>[
					'conditions'=>[
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
					],
					'order'=>'StockMovement.movement_date DESC',
				],
        'StockItem',
			],
		]);
		
		$this->loadModel('Order');
		$this->Order->recursive=-1;
    
    $orderIds=[];
    for ($i=0;$i<count($product['StockMovement']);$i++){
			$linkedOrder=$this->Order->read(null,$product['StockMovement'][$i]['order_id']);
			$product['StockMovement'][$i]['order_code']=$linkedOrder['Order']['order_code'];
      if (!in_array($product['StockMovement'][$i]['order_id'],$orderIds)){
        $orderIds[]=$product['StockMovement'][$i]['order_id'];
      }
		}
		$this->set(compact('product'));
    
    $priceData=[];
    if (!empty($product['ProductPriceLog']) && count($product['ProductPriceLog'])>1){
      foreach ($product['ProductPriceLog'] as $productPriceLog){
        $priceDateTime=new DateTime($productPriceLog['price_datetime']);
        $priceData['labels'][]=$priceDateTime->format('d/m/Y H:i:s')." (".$productPriceLog['Currency']['abbreviation'].")";
        $priceData['values'][]=$productPriceLog['price'];
        $priceData['backgroundColors'][]='rgba(75, 192, 192, 0.2)';
        $priceData['borderColors'][]='rgba(75, 192, 192, 1)';
      }
    }
    $this->set(compact('priceData'));
    //pr($priceData);
    
    $ordersForProductInPeriod=$this->Order->find('all',[
      'conditions'=>['Order.id'=>$orderIds],
      'contain'=>[
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.product_id'=>$id,
          ]
        ]
      ],
      'order'=>'order_date DESC'
    ]);
    
    $this->set(compact('ordersForProductInPeriod'));
    $this->set(compact('startDate','endDate'));
    
    $aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$previousProductsWithThisName=[];
			$previousProductsWithThisName=$this->Product->find('all',[
				'conditions'=>[
					'TRIM(LOWER(Product.name))'=>trim(strtolower($this->request->data['Product']['name'])),
				],
			]);
			
			if (count($previousProductsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un producto con este nombre!  No se guardó el producto.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Product->getDataSource();
				$datasource->begin();
				try {
					$this->Product->create();
					if (!$this->Product->save($this->request->data)) {
						echo "problema al guardar el producto";
						pr($this->validateErrors($this->Product));
						throw new Exception();
					} 
					$productId=$this->Product->id;
          
          $this->loadModel('StockItem');
          $stockItemData=[];
          $stockItemData['StockItem']['name']=$this->request->data['Product']['name'];
          $stockItemData['StockItem']['product_id']=$productId;
          $stockItemData['StockItem']['unit_id']=$this->request->data['Product']['default_cost_unit_id'];
          $stockItemData['StockItem']['product_unit_cost']=$this->request->data['Product']['default_cost'];
          $stockItemData['StockItem']['remaining_quantity']=0;
          $stockItemData['StockItem']['bool_active']=true;
          $this->StockItem->create();
          if (!$this->StockItem->save($stockItemData)) {
						echo "problema al guardar el lote para el producto";
						pr($this->validateErrors($this->StockItem));
						throw new Exception();
					} 

					$this->loadModel('ProductPriceLog');
          $productPriceLogData=[];
          $productPriceLogData['ProductPriceLog']['price_datetime']=date('Y-m-d H:i:s');
          $productPriceLogData['ProductPriceLog']['product_id']=$productId;
          $productPriceLogData['ProductPriceLog']['price']=$this->request->data['Product']['default_price'];
          $productPriceLogData['ProductPriceLog']['currency_id']=$this->request->data['Product']['default_price_currency_id'];
          $this->ProductPriceLog->create();
          if (!$this->ProductPriceLog->save($productPriceLogData)) {
						echo "problema al guardar el precio histórico para el producto";
						pr($this->validateErrors($this->ProductPriceLog));
						throw new Exception();
					} 
          
					$datasource->commit();
					
					$this->recordUserAction($this->Product->id,null,null);
					$this->Session->setFlash(__('Se guardó el producto '.$this->request->data['Product']['name'].'.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'index']);
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
			}
		}
		$productTypes = $this->Product->ProductType->find('list');
		$this->set(compact('productTypes'));
		/*
		$this->loadModel('AccountingCode');
		$inventoryAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INVENTORY);
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.lft >'=>$inventoryAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$inventoryAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>false,
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$this->set(compact('accountingCodes'));
    */
    $this->loadModel('Currency');
    $defaultPriceCurrencies=$defaultCostCurrencies=$this->Currency->find('list');
    $this->set(compact('defaultCostCurrencies','defaultPriceCurrencies'));
    
    $this->loadModel('Unit');
    $units=$this->Unit->find('list');
    $this->set(compact('units'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		if ($this->request->is(['post', 'put'])) {
			$previousProductsWithThisName=[];
			$previousProduct=$this->Product->read(null,$id);
			
			if ($previousProduct['Product']['name']!=$this->request->data['Product']['name']){
				$previousProductsWithThisName=$this->Product->find('all',[
					'conditions'=>['TRIM(LOWER(Product.name))'=>trim(strtolower($this->request->data['Product']['name']))],
				]);
			}
			
			if (count($previousProductsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un producto con este nombre!  No se guardó el producto.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Product->getDataSource();
				$datasource->begin();
				try {
					$this->Product->id=$id;
					if (!$this->Product->save($this->request->data)) {
						echo "problema al guardar el producto";
						pr($this->validateErrors($this->Product));
						throw new Exception();
					} 
					$productId=$this->Product->id;
					
          $this->loadModel('ProductPriceLog');
          $productPriceLogData=[];
          $productPriceLogData['ProductPriceLog']['price_datetime']=date('Y-m-d H:i:s');
          $productPriceLogData['ProductPriceLog']['product_id']=$productId;
          $productPriceLogData['ProductPriceLog']['price']=$this->request->data['Product']['default_price'];
          $productPriceLogData['ProductPriceLog']['currency_id']=$this->request->data['Product']['default_price_currency_id'];
          $this->ProductPriceLog->create();
          if (!$this->ProductPriceLog->save($productPriceLogData)) {
						echo "problema al guardar el precio histórico para el producto";
						pr($this->validateErrors($this->ProductPriceLog));
						throw new Exception();
					} 
          
					$datasource->commit();
					
					$this->recordUserAction();
					$this->Session->setFlash(__('The product has been saved.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'index']);
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
			}
		} 
		else {
			$this->request->data = $this->Product->find('first',[
				'conditions' => [
					'Product.id' => $id,
				],
				'contain'=>[
				],
			]);
		}
		$productTypes = $this->Product->ProductType->find('list');
		$this->set(compact('productTypes'));
		/*
		$this->loadModel('AccountingCode');
		$inventoryAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INVENTORY);
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.lft >'=>$inventoryAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$inventoryAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>false,
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$this->set(compact('accountingCodes'));
    */
    $this->loadModel('Currency');
    $defaultPriceCurrencies=$defaultCostCurrencies=$this->Currency->find('list');
    $this->set(compact('defaultCostCurrencies','defaultPriceCurrencies'));
    
    $this->loadModel('Unit');
    $units=$this->Unit->find('list');
    $this->set(compact('units'));;
    $this->set(compact('defaultCostCurrencies'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Product->id = $id;
		if (!$this->Product->exists()) {
			throw new NotFoundException(__('Invalid product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Product->delete()) {
			$this->Session->setFlash(__('The product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function compareArraysByDate($a, $b){
		$ad = strtotime($a['order_date']);
		$bd = strtotime($b['order_date']);
		return ($ad-$bd);
	}
	
	public function viewSaleReport($id=null,$startDate = null,$endDate=null) {
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItem');
		
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
		
		$this->Product->recursive=-1;
    $model=$this;
    $allFinishedProducts = Cache::remember('productsalereport_productspercategory_'.CATEGORY_PRODUCED, function() use ($model){
        return $model->Product->find('all',[
          'fields'=>['id','name'],
          'conditions' => ['ProductType.product_category_id'=>CATEGORY_PRODUCED],
          'contain'=>[
            'ProductType',
          ],
          'order'=>'name ASC'
        ]);
    }, 'long');
    /*
		$allFinishedProducts=$this->Product->find('all',
			array(
				'fields'=>array('id','name'),
				'conditions'=>array(
					'ProductType.product_category_id'=>CATEGORY_PRODUCED
				),
				'contain'=>array(
					'ProductType',
				),
				'order'=>'name ASC'
			)
		);
    */
		$this->ProductionResultCode->recursive=-1;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		$model->Product->recursive=-1;
		if ($id==null){
      $allRawMaterials = Cache::remember('productsalereport_productspercategory_'.CATEGORY_RAW, function() use ($model){
        return $model->Product->find('all',[
          'fields'=>['Product.id','Product.name'],
          'conditions' => ['ProductType.product_category_id'=>CATEGORY_RAW],
          'contain'=>[
            'ProductType',
          ],
          'order'=>'Product.name ASC'
        ]);
      }, 'long');
      /*
			$allRawMaterials = $this->Product->find('all',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('ProductType.product_category_id'=>CATEGORY_RAW),
				'contain'=>array(
					'ProductType',
				),
				'order'=>'Product.name',
			));
      */
		}
		else {
			$allRawMaterials=$this->Product->find('first',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('Product.id'=>$id)
			));
		}
    $allSales = Cache::remember('productsalereport_allsales_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->Order->find('all',[
        'fields'=>['id','order_date','order_code'],
				'contain'=>[
					'ThirdParty'=>['fields'=>'company_name'],
					'StockMovement'=>[
						'fields'=>['id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'],
						'Product'=>[
							'fields'=>['id','packaging_unit'],	
							'ProductType'=>['fields'=>'product_category_id'],
						],
						'StockItem'=>[
							'fields'=>['production_result_code_id','product_id','raw_material_id'],
						]
					],
          'Invoice',
          'CashReceipt'
				],
				'conditions'=>[
					'Order.stock_movement_type_id'=>MOVEMENT_SALE,
					'Order.order_date >='=>$startDate,
					'Order.order_date <'=>$endDatePlusOne,
				],
				'order'=>'order_date ASC'
      ]);
    }, 'long');
    /*
      $allSales=$this->Order->find('all',
        array(
          'fields'=>array('id','order_date','order_code'),
          'contain'=>array(
            'ThirdParty'=>array('fields'=>'company_name'),
            'StockMovement'=>array(
              'fields'=>array('id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'),
              'Product'=>array(
                'fields'=>array('id','packaging_unit'),	
                'ProductType'=>array('fields'=>'product_category_id'),
              ),
              'StockItem'=>array(
                'fields'=>array('production_result_code_id','product_id','raw_material_id'),
              )
            ),
            'Invoice',
            'CashReceipt'
          ),
          'conditions'=>array(
            'Order.stock_movement_type_id'=>MOVEMENT_SALE,
            'Order.order_date >='=>$startDate,
            'Order.order_date <'=>$endDatePlusOne,
          ),
          'order'=>'order_date ASC'
        )
      );
		*/
		
		$salesData=[];
		$i=0;
		//pr($allSales);
		
    $allReclassifications = Cache::remember('productsalereport_allreclassifications_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->StockMovement->find('all',[
        'fields'=>['StockMovement.reclassification_code','StockMovement.movement_date'],
        'conditions'=>[
          'StockMovement.bool_reclassification'=>true,
          'StockMovement.movement_date >='=>$startDate,
          'StockMovement.movement_date <='=>$endDatePlusOne,
        ],
        'group'=>'reclassification_code',
      ]);
    }, 'long');
    /*
		$allReclassifications=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.reclassification_code','StockMovement.movement_date'),
			'conditions'=>array(
				'StockMovement.bool_reclassification'=>true,
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <='=>$endDatePlusOne,
			),
			'group'=>'reclassification_code',
		));
		*/
		for ($r=0;$r<count($allReclassifications);$r++){
      $reclassificationCode=$allReclassifications[$r]['StockMovement']['reclassification_code'];
      $reclassificationMovements = Cache::remember('productsalereport_reclassificationmovements_'.$reclassificationCode.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$reclassificationCode,$startDate,$endDatePlusOne){
        return $model->StockMovement->find('all',[
          'fields'=>['StockMovement.movement_date','StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_id','StockMovement.production_result_code_id','StockMovement.bool_input'],
          'conditions'=>[
            'StockMovement.bool_reclassification'=>true,
            'StockMovement.movement_date >='=>$startDate,
            'StockMovement.movement_date <='=>$endDatePlusOne,
            'StockMovement.reclassification_code'=>$reclassificationCode,
          ],
          'contain'=>[
            'Product'=>[
              'fields'=>['id','packaging_unit'],	
              'ProductType'=>['fields'=>'product_category_id'],
            ],
            'StockItem'=>[
              'fields'=>['production_result_code_id','product_id','raw_material_id'],
            ]
          ],
        ]);
      }, 'long');
      /*
			$reclassificationMovements=$this->StockMovement->find('all',array(
				'fields'=>array('StockMovement.movement_date','StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_id','StockMovement.production_result_code_id','StockMovement.bool_input'),
				'conditions'=>array(
					'StockMovement.bool_reclassification'=>true,
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <='=>$endDatePlusOne,
					'StockMovement.reclassification_code'=>$allReclassifications[$r]['StockMovement']['reclassification_code'],
				),
				'contain'=>array(
					'Product'=>array(
						'fields'=>array('id','packaging_unit'),	
						'ProductType'=>array('fields'=>'product_category_id'),
					),
					'StockItem'=>array(
						'fields'=>array('production_result_code_id','product_id','raw_material_id'),
					)
				),
			));
      */
			$allReclassifications[$r]['reclassificationMovements']=$reclassificationMovements;
		}
		
		//pr($allReclassifications);
				
		foreach ($allRawMaterials as $rawMaterial){
			$salesData[$i]['RawMaterial']['id']=$rawMaterial['Product']['id'];
			$salesData[$i]['RawMaterial']['name']=$rawMaterial['Product']['name'];
			
			$saleCount=0;
			// first get the sales data
			$formattedSales=array();
			foreach ($allSales as $sale){
				//pr($sale);
				$sold=array();
				$saleid=$sale['Order']['id'];
				foreach ($sale['StockMovement'] as $saleMovement){
					$soldProducts=array();

					$productidForSale=$saleMovement['StockItem']['product_id'];
					$productionresultcodeForSale=$saleMovement['StockItem']['production_result_code_id'];
					//echo "salemovement loop started";
					if ($saleMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($allProductionResultCodes as $productionResultCode){	
								// retrieve products sold
								if ($productidForSale==$finishedProduct['Product']['id'] && $productionresultcodeForSale==$productionResultCode['ProductionResultCode']['id']){
									$soldProducts[]=$saleMovement['product_quantity'];
								}
								else{
									$soldProducts[]=0;
								}
							}
						}
					}
					foreach (array_keys($soldProducts + $sold) as $key) {
						$sold[$key] = (isset($soldProducts[$key]) ? $soldProducts[$key] : 0) + (isset($sold[$key]) ? $sold[$key] : 0);
					}					
					
				}
				
				//pr($sold);
				/*
				if (array_sum($sold)>0){
					$salesData[$i]['Sale'][$saleCount]['id']=$sale['Order']['id'];
					$salesData[$i]['Sale'][$saleCount]['order_date']=$sale['Order']['order_date'];
					$salesData[$i]['Sale'][$saleCount]['order_code']=$sale['Order']['order_code'];
					$salesData[$i]['Sale'][$saleCount]['client']=$sale['ThirdParty']['company_name'];
					$salesData[$i]['Sale'][$saleCount]['sold_products']=$sold;
				}
				*/
				
				if (array_sum($sold)>0){
          //if ($sale['Order']['id']==2501){
            //pr($sale);
          //}
					$formattedSales[$saleCount]['id']=$sale['Order']['id'];
					$formattedSales[$saleCount]['order_date']=$sale['Order']['order_date'];
					$formattedSales[$saleCount]['order_code']=$sale['Order']['order_code'];
          $formattedSales[$saleCount]['is_sale']=(empty($sale['Invoice'])?0:1);
					$formattedSales[$saleCount]['reclassification_code']=0;
					$formattedSales[$saleCount]['client']=$sale['ThirdParty']['company_name'];
					$formattedSales[$saleCount]['sold_products']=$sold;
				}
				$saleCount++;
			}
			
			$formattedReclassifications=array();
			foreach ($allReclassifications as $reclassification){
				$reclassified=array();
				
				foreach($reclassification['reclassificationMovements'] as $reclassificationMovement){
					$reclassifiedProducts=array();
					
					$productidForReclassification=$reclassificationMovement['StockMovement']['product_id'];
					$productionresultcodeForReclassification=$reclassificationMovement['StockMovement']['production_result_code_id'];
					if ($reclassificationMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
						//echo "production result code is ".$productionresultcodeForReclassification."<br/>";
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($allProductionResultCodes as $productionResultCode){	
								if ($productidForReclassification==$finishedProduct['Product']['id'] && $productionresultcodeForReclassification==$productionResultCode['ProductionResultCode']['id']){
									if ($reclassificationMovement['StockMovement']['bool_input']){
										$reclassifiedProducts[]=$reclassificationMovement['StockMovement']['product_quantity'];
									}
									else {
										$reclassifiedProducts[]=0-$reclassificationMovement['StockMovement']['product_quantity'];
									}
								}
								else{
									$reclassifiedProducts[]=0;
								}
							}
						}
					}
					
					foreach (array_keys($reclassifiedProducts + $reclassified) as $key) {
						$reclassified[$key] = (isset($reclassifiedProducts[$key]) ? $reclassifiedProducts[$key] : 0) + (isset($reclassified[$key]) ? $reclassified[$key] : 0);
					}
					
				}
				if (!empty($reclassified)){
					if (max($reclassified)>0){
						$formattedReclassifications[$saleCount]['id']=0;
						$formattedReclassifications[$saleCount]['order_date']=$reclassification['StockMovement']['movement_date'];
						$formattedReclassifications[$saleCount]['order_code']=0;
						$formattedReclassifications[$saleCount]['reclassification_code']=$reclassification['StockMovement']['reclassification_code'];
						$formattedReclassifications[$saleCount]['client']="-";
						$formattedReclassifications[$saleCount]['sold_products']=$reclassified;
						//pr($reclassified);
					}			
				}
				$saleCount++;
			}
			
			// and now for the conclusion: merge the sales and the reclassifications by date
			$mergedSalesAndReclassifications=array_merge($formattedSales,$formattedReclassifications);
			usort($mergedSalesAndReclassifications,array($this,'compareArraysByDate'));
			$salesData[$i]['Sale']=$mergedSalesAndReclassifications;
			//pr($mergedSalesAndReclassifications);
			
			$initialStock=array();
			$producedStock=array();
			$reclassifiedStock=array();
			$finalStock=array();
			foreach ($allFinishedProducts as $finishedProduct){
				foreach ($allProductionResultCodes as $productionResultCode){	
					// get all the stockitems
					//ADDED RECURSIVE 20151201
          $finishedProductId=$finishedProduct['Product']['id'];
          $productionResultCodeId=$productionResultCode['ProductionResultCode']['id'];
          $rawMaterialId=$rawMaterial['Product']['id'];
          
          $model->StockItem->recursive=-1;
          $allStockItemsForProduct = Cache::remember('productsalereport_allstockitemsforproduct_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate){
            return $model->StockItem->find('all',[
              'fields'=>'StockItem.id',
              'conditions' => [
                'StockItem.product_id'=> $finishedProductId,
                'StockItem.production_result_code_id'=> $productionResultCodeId,
                'StockItem.raw_material_id'=> $rawMaterialId,
                // ADDED CONDITIONS 20180314
                'StockItem.stockitem_creation_date <'=>$startDate,
                'StockItem.stockitem_depletion_date >'=>$startDate,
              ],
            ]);  
          }, 'long');      
          /*
					$allStockItemsForProduct = $this->StockItem->find('all', array(
						//ADDED FIELDS 20151201
						'fields'=>'StockItem.id',
						'conditions' => 
							array(
								'StockItem.product_id ='=> $finishedProduct['Product']['id'],
								'StockItem.production_result_code_id ='=> $productionResultCode['ProductionResultCode']['id'],
								'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
                // ADDED CONDITIONS 20180314
                'StockItem.stockitem_creation_date <'=>$startDate,
                'StockItem.stockitem_depletion_date >'=>$startDate,
							)
						)
					);
          */
					//pr($allStockItemsForProduct);
					$quantityInitialStock=0;
					$quantityProduced=0;
					$quantityReclassified=0;
					$quantityFinalStock=0;					
					
					// retrieve produced quantity
          $producedProducts = Cache::remember('productsalereport_producedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->ProductionMovement->find('all',[
              'fields'=>['Product.id, SUM(ProductionMovement.product_quantity) as total_product_quantity'],
              'conditions' => array(
                'ProductionMovement.product_id ='=> $finishedProductId,
                'ProductionMovement.production_result_code_id'=> $productionResultCodeId,
                'ProductionMovement.movement_date >='=>$startDate,
                'ProductionMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
              ),
              'group'=>array('Product.id')
            ]);  
          }, 'long'); 
          /*
					$producedProducts=$this->ProductionMovement->find('all',array(
						'fields'=>array('Product.id, SUM(ProductionMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'ProductionMovement.product_id ='=> $finishedProduct['Product']['id'],
							'ProductionMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'ProductionMovement.movement_date >='=>$startDate,
							'ProductionMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
						),
						'group'=>array('Product.id')
					));
          */
					//pr($producedProducts);
					if (!empty($producedProducts)){
						$quantityProduced=$producedProducts[0][0]['total_product_quantity'];
					}
					$inputReclassifiedProducts = Cache::remember('productsalereport_inputreclassifiedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->StockMovement->find('all',[
              'fields'=>['Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'],
              'conditions' => [
                'StockMovement.product_id ='=> $finishedProductId,
                'StockMovement.production_result_code_id'=> $productionResultCodeId,
                'StockMovement.movement_date >='=>$startDate,
                'StockMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
                'StockMovement.bool_reclassification'=>true,
                'StockMovement.bool_input'=>true,
              ],
              'group'=>['Product.id']
            ]);  
          }, 'long'); 
          /*
					$inputReclassifiedProducts=$this->StockMovement->find('all',array(
						'fields'=>array('Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'StockMovement.product_id ='=> $finishedProduct['Product']['id'],
							'StockMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'StockMovement.movement_date >='=>$startDate,
							'StockMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
							'StockMovement.bool_reclassification'=>true,
							'StockMovement.bool_input'=>true,
						),
						'group'=>array('Product.id')
					));
          */
          $outputReclassifiedProducts = Cache::remember('productsalereport_outputreclassifiedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->StockMovement->find('all',[
              'fields'=>['Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'],
              'conditions' => [
                'StockMovement.product_id ='=> $finishedProductId,
                'StockMovement.production_result_code_id'=> $productionResultCodeId,
                'StockMovement.movement_date >='=>$startDate,
                'StockMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
                'StockMovement.bool_reclassification'=>true,
                'StockMovement.bool_input'=>true,
              ],
              'group'=>['Product.id']
            ]);  
          }, 'long'); 
					
          /*
          $outputReclassifiedProducts=$this->StockMovement->find('all',array(
						'fields'=>array('Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'StockMovement.product_id ='=> $finishedProduct['Product']['id'],
							'StockMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'StockMovement.movement_date >='=>$startDate,
							'StockMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
							'StockMovement.bool_reclassification'=>true,
							'StockMovement.bool_input'=>false,
						),
						'group'=>array('Product.id')
					));
          */
					//pr($reclassifiedProducts);
					if (!empty($inputReclassifiedProducts)||!empty($outputReclassifiedProducts)){
						if (!empty($inputReclassifiedProducts)){
							// we know there is reclassified input
							if (!empty($outputReclassifiedProducts)){
								// both reclassified input and output
								$quantityReclassified=$inputReclassifiedProducts[0][0]['total_product_quantity']-$outputReclassifiedProducts[0][0]['total_product_quantity'];
							}
							else {
								// only reclassified input
								$quantityReclassified=$inputReclassifiedProducts[0][0]['total_product_quantity'];
							}
						}
						else {
							// only reclassified output
							$quantityReclassified=-$outputReclassifiedProducts[0][0]['total_product_quantity'];
						}
					}
					
					foreach ($allStockItemsForProduct as $stockItemForProduct){
						$stockitemId=$stockItemForProduct['StockItem']['id'];

						// retrieve initial stock value
						//get the last stockitem log before the startdate to determine the initial stock
            $model->StockItem->StockItemLog->recursive=-1;
            $initialStockItemLogForStockItem = Cache::remember('productsalereport_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
              return $model->StockItem->StockItemLog->find('first',[
                'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
                'conditions' => [
                  'StockItemLog.stockitem_id ='=> $stockitemId,
                  'StockItemLog.stockitem_date <'=>$startDate
                ],
                'order'=>'StockItemLog.id DESC'
              ]);
            }, 'long');  
						/*$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
							//ADDED FIELDS 20151201
							'fields'=>'StockItemLog.product_quantity',
							'conditions' => array(
								'StockItemLog.stockitem_id ='=> $stockitemId,
								'StockItemLog.stockitem_date <'=>$startDate
							),
							'order'=>'StockItemLog.id DESC'
						));
            */
						//pr($initialStockItemLogForStockItem);
						if (!empty($initialStockItemLogForStockItem)){
							$quantityInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
						}
							
						// retrieve final stock value
						//get the last stockitem log before the startdate to determine the initial stock
            $finalStockItemLogForStockItem = Cache::remember('productsalereport_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
              return $model->StockItem->StockItemLog->find('first',[
                'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
                'conditions' => [
                  'StockItemLog.stockitem_id ='=> $stockitemId,
                  'StockItemLog.stockitem_date <'=>$endDatePlusOne
                ],
                'order'=>'StockItemLog.id DESC'
              ]);
            }, 'long');  
            /*
						$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
							//ADDED FIELDS 20151201
							'fields'=>'StockItemLog.product_quantity',
							'conditions' => array(
								'StockItemLog.stockitem_id ='=> $stockitemId,
								'StockItemLog.stockitem_date <'=>$endDatePlusOne
							),
							'order'=>'StockItemLog.id DESC'
						));
            */
						if (!empty($finalStockItemLogForStockItem)){
							$quantityFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
						}
					}	
					$initialStock[]=$quantityInitialStock;
					$producedStock[]=$quantityProduced;
					$reclassifiedStock[]=$quantityReclassified;
					$finalStock[]=$quantityFinalStock;					
				}
			}
			$salesData[$i]['initial_stock']=$initialStock;
			$salesData[$i]['produced_stock']=$producedStock;
			$salesData[$i]['reclassified_stock']=$reclassifiedStock;
			$salesData[$i]['final_stock']=$finalStock;
			
			$i++;
		}
		//pr($salesData);
			
		$this->set(compact('salesData','startDate','endDate','allFinishedProducts','allProductionResultCodes'));

	}
	
	/*******************************************************************************************************/
	
	public function verReporteProducto($id=null) {
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItem');
		
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
		
		$this->ProductionResultCode->recursive=0;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		$this->Product->recursive=0;
		$allRawMaterials = $this->Product->find('all',
			array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('ProductType.product_category_id'=>CATEGORY_RAW)
			)
		);
		
		$finishedProduct = $this->Product->find('first',
			array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('Product.id'=>$id)
			)
		);
		
		$productionRuns=$this->ProductionRun->find('all',
			array(
				'fields'=>array('id','production_run_date','raw_material_quantity'),
				'contain'=>array(
					'ProductionMovement'=>array(
						'fields'=>array('id','stockitem_id','product_quantity','product_unit_price'),
						'StockItem'=>array('fields'=>array('production_result_code_id','product_id','raw_material_id','product_unit_price'))
					)
				),
				'conditions'=>array(
					'ProductionRun.finished_product_id'=>$id,
					'ProductionRun.production_run_date >='=>$startDate,
					'ProductionRun.production_run_date <='=>$endDatePlusOne,
				),
				'order'=>'production_run_date ASC'
			)
		);
		$productionData=[];
		$i=0;
		foreach ($allRawMaterials as $rawMaterial){
			$productionData[$i]['RawMaterial']['id']=$rawMaterial['Product']['id'];
			$productionData[$i]['RawMaterial']['name']=$rawMaterial['Product']['name'];
			$productionData[$i]['Product']['id']=$finishedProduct['Product']['id'];
			$productionData[$i]['Product']['productname']=$finishedProduct['Product']['name'];
			
			$productionCount=0;
			foreach ($productionRuns as $productionRun){
				$productionRunId=$productionRun['ProductionRun']['id'];
				$rawMaterialQuantity=$productionRun['ProductionRun']['raw_material_quantity'];
				$quantityA=0;
				$quantityB=0;
				$quantityC=0;
				$valueA=0;
				$valueB=0;
				$valueC=0;
				foreach ($productionRun['ProductionMovement'] as $productionMovement){
					
					if ($productionMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
						switch ($productionMovement['StockItem']['production_result_code_id']){
							case 1:
								$quantityA+=$productionMovement['product_quantity'];
								$valueA=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
							case 2:
								$quantityB+=$productionMovement['product_quantity'];
								$valueB=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
							case 3:
								$quantityC+=$productionMovement['product_quantity'];
								$valueC=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
						}
					}
				}
				if (($quantityA+$quantityB+$quantityC)>0){
					$productionData[$i]['ProductionRun'][$productionCount]['id']=$productionRunId;
					$productionData[$i]['ProductionRun'][$productionCount]['productionrundate']=$productionRun['ProductionRun']['production_run_date'];
					$productionData[$i]['ProductionRun'][$productionCount]['quantityA']=$quantityA;
					$productionData[$i]['ProductionRun'][$productionCount]['quantityB']=$quantityB;
					$productionData[$i]['ProductionRun'][$productionCount]['quantityC']=$quantityC;
					$productionData[$i]['ProductionRun'][$productionCount]['rawUsed']=$rawMaterialQuantity;
					$productionData[$i]['ProductionRun'][$productionCount]['valueA']=$valueA;
					$productionData[$i]['ProductionRun'][$productionCount]['valueB']=$valueB;
					$productionData[$i]['ProductionRun'][$productionCount]['valueC']=$valueC;
					$productionData[$i]['ProductionRun'][$productionCount]['valueTotal']=$valueA+$valueB+$valueC;
				}
				$productionCount++;
			}			
			$i++;
		}
		$this->set(compact('finishedProduct','productionData','startDate','endDate','allProductionResultCodes'));
	}
	
	public function guardarReporteProductoFabricado($productname) {
		$exportData=$_SESSION['fabricatedProductReport'];
		$this->set(compact('exportData','productname'));
	}
	
	public function guardarReporteSalidasMateriaPrima() {
		$exportData=$_SESSION['rawMaterialExitReport'];
		$this->set(compact('exportData'));
	}
}
