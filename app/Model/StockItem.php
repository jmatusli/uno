<?php
App::uses('AppModel', 'Model');

class StockItem extends AppModel {
	
	function getInventoryTotals($productCategoryId,$productTypeIds,$warehouseId=0){
		//echo "warehouse id is ".$warehouseId."<br/>";
		return $this->getInventoryTotalsByDate($productCategoryId,$productTypeIds,date('Y-m-d'),$warehouseId);
	}
	
	function getInventoryTotalsByDate($productCategoryId,$productTypeIds,$inventoryDate,$warehouseId=0){
		//echo "inventory date is ".$inventoryDate."<br/>";
		//echo "warehouse id is ".$warehouseId."<br/>";
		$productsArray=[];
		if ($productCategoryId==CATEGORY_PRODUCED){
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($productsOfProductType);
				if (!empty($productsOfProductType)){
					foreach ($productsOfProductType as $retrievedProduct){
						//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
						//pr($retrievedProduct);
						$thisProductArray=[];
						$thisProductArray['Product']['id']=$retrievedProduct['Product']['id'];
						$thisProductArray['StockItem']['product_id']=$retrievedProduct['Product']['id'];
						//$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['StockItem']['production_result_code_id'];
						$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['ProductionResultCode']['id'];
						//$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['StockItem']['id'];
						$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['name']=$retrievedProduct['RawMaterial']['name'];
						$thisProductArray['Product']['name']=$retrievedProduct['Product']['name'];
						$thisProductArray['ProductionResultCode']['code']=$retrievedProduct['ProductionResultCode']['code'];
						//switch ($retrievedProduct['StockItem']['production_result_code_id']){
						switch ($retrievedProduct['ProductionResultCode']['id']){
							case PRODUCTION_RESULT_CODE_A:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_A'];
								break;
							case PRODUCTION_RESULT_CODE_B:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_B'];
								break;
							case PRODUCTION_RESULT_CODE_C:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_C'];
								break;
						}
						$thisProductArray['0']['Remaining_A']=$retrievedProduct['0']['Remaining_A'];
						$thisProductArray['0']['Remaining_B']=$retrievedProduct['0']['Remaining_B'];
						$thisProductArray['0']['Remaining_C']=$retrievedProduct['0']['Remaining_C'];
						$thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining_A']+$retrievedProduct['0']['Remaining_B']+$retrievedProduct['0']['Remaining_C'];
						$productsArray[]=$thisProductArray;
					}
				}
			}
			return $productsArray;
		}
		else {
			$productsArray=[];
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($productsOfProductType);
				foreach ($productsOfProductType as $retrievedProduct){
					//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
					//pr($retrievedProduct);
					$thisProductArray=[];
					$thisProductArray['Product']['id']=$retrievedProduct['Product']['id'];
					$thisProductArray['StockItem']['product_id']=$retrievedProduct['Product']['id'];
					$thisProductArray['Product']['name']=$retrievedProduct['Product']['name'];
					//$thisProductArray['ProductionResultCode']['code']=$retrievedProduct['ProductionResultCode']['code'];
					$thisProductArray['0']['inventory_total']=$retrievedProduct['0']['Remaining'];
					$thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining'];
					//echo "processed product array<br/>";
					//pr($thisProductArray);
					$productsArray[]=$thisProductArray;
				}
			}
			//pr($productsArray);
			return $productsArray;
		}	
	}
	
	function getInventoryItems($productTypeId,$inventoryDate,$enterpriseId=1,$boolQuantitiesAtCurrentDate=false){
    $model=$this;
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		
		$this->recursive=-1;
		$productModel=ClassRegistry::init('Product');
		$productIds=$productModel->find('list',[
			'fields'=>['Product.id'],
			'conditions'=>['Product.product_type_id'=>$productTypeId],    
		]);
    
		$conditions=[
		  'StockItem.product_id'=> $productIds,
		];
    
		if ($enterpriseId>0){
			$conditions['StockItem.enterprise_id']=$enterpriseId;
		}
    $this->recursive=-1;
    $productCount= $this->find('count',[
      'fields'=>[
        'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_cost) AS Saldo', 
      ],
      'conditions' => $conditions,
      'group'=>'StockItem.product_id',
    ]);
    $this->recursive=-1;
    $products = $this->find('all',[
      'fields'=>[
        'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_cost) AS Saldo', 
      ],
      'conditions' => $conditions,
      'contain'=>[
        'Product'=>[
          'fields'=>['Product.name','Product.id','Product.product_order','Product.packaging_unit','Product.product_type_id'],
        ],
        'Unit'=>['fields'=>['id','name','abbreviation']],
      ],
      'group'=>'StockItem.product_id',
      'limit'=>$productCount,
    ]);
    //usort($products,[$this,'sortByProductName']);
    usort($products,[$this,'sortByProductOrderThenName']);
    for ($i=0;$i<count($products);$i++){
      $stockItemConditions=[
        'StockItem.product_id'=>$products[$i]['Product']['id'],
        
      ];
      if ($enterpriseId>0){
        $stockItemConditions[]=['StockItem.enterprise_id'=>$enterpriseId,];
      }
      $allStockItems=$this->find('all',[
        'fields'=>[
          'StockItem.id',
          'StockItem.remaining_quantity','StockItem.product_unit_cost'
        ],
        'conditions'=>$stockItemConditions,
        'contain'=>['Unit'=>['fields'=>['id','name','abbreviation']],],
      ]);
      
      $totalStockInventoryDate=0;
      $totalValueInventoryDate=0;
      if (count($allStockItems)>0){
        $lastStockItemLog=[];
        foreach ($allStockItems as $stockItem){		
          $this->StockItemLog->recursive=-1;
          $stockItemLogConditions=['StockItemLog.stock_item_id'=>$stockItem['StockItem']['id']];
          if (!$boolQuantitiesAtCurrentDate){
            $stockItemLogConditions[]=['StockItemLog.stock_item_date <='=>$inventoryDatePlusOne];
          }
          // enterprise id already applied to stockitem
          //if ($enterpriseId>0){
          //  $stockItemLogConditions[]=['StockItemLog.enterprise_id'=>$enterpriseId,];
          //}
          $lastStockItemLog=$this->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_cost'],
            'conditions'=>$stockItemLogConditions,
            'order'=>'StockItemLog.id DESC',
          ]);
          
          if (count($lastStockItemLog)>0){
            $totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
            $totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_cost'];
          }    
        }
      }
      $products[$i][0]['Remaining']=$totalStockInventoryDate;
      $products[$i][0]['Unit']=(empty($allStockItems[0]['Unit'])?['Unit']:$allStockItems[0]['Unit']);
      $products[$i][0]['Saldo']=$totalValueInventoryDate;
    }
    
		//echo "products coming out of getInventoryItems<br/>";
		//pr($products);
		return $products;	
	}
  
  public function sortByProductName($firstTerm,$secondTerm){
		return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;
	}
  public function sortByProductOrderThenName($firstTerm,$secondTerm){
    if ($firstTerm['Product']['product_order'] == $secondTerm['Product']['product_order']){
      return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;  
    }
    else {
      return ($firstTerm['Product']['product_order'] < $secondTerm['Product']['product_order']) ? -1 : 1;  
    }
      
	}
	 
  public function getInventoryTotalPerProduct($productId,$inventoryDate,$enterpriseId=0,$boolQuantitiesAtCurrentDate=false){
    //echo "inventory date is ".$inventoryDate."<br/>";
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		//echo "inventoryDatePlusOne is ".$inventoryDatePlusOne."<br/>";

		$this->recursive=-1;
    $productModel=ClassRegistry::init('Product');
    $productModel->recursive=-1;
		$stockItemConditions=[
      'StockItem.product_id'=> $productId,
      'StockItem.enterprise_id'=> $enterpriseId,
		];
    
    $product=$productModel->find('first',['conditions'=>['id'=>$productId]]);
    //pr($product);
    $products=[];
		switch ($product['Product']['product_type_id']){
			default:
				$productCount=	$this->find('count', [
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_cost) AS Saldo'],
					'conditions' => $stockItemConditions,
				]);
				$this->recursive=-1;
				$products = $this->find('all',[
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_cost) AS Saldo'],
					'conditions' => $stockItemConditions,
					'contain'=>[
						'Product'=>['fields'=>['Product.name','Product.id','Product.packaging_unit','Product.product_type_id']],
					],
          'group'=>'StockItem.product_id',
					'limit'=>$productCount,
				]);
        
				for ($i=0;$i<count($products);$i++){
					$allStockItems=$this->find('all',[
						'fields'=>['StockItem.id'],					]);
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
					if (count($allStockItems)>0){
						$lastStockItemLog=[];
						foreach ($allStockItems as $stockitem){				
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=[
								'StockItemLog.stock_item_id'=>$stockitem['StockItem']['id'],
							];
							if (!$boolQuantitiesAtCurrentDate){
								$stockItemLogConditions[]=['StockItemLog.stock_item_date <='=>$inventoryDatePlusOne];	
							}
							//pr($stockItemLogConditions);
							$lastStockItemLog=$this->StockItemLog->find('first',[
								'fields'=>[
									'StockItemLog.product_quantity','StockItemLog.product_unit_cost',
								],
								'conditions'=>$stockItemLogConditions,
								'order'=>'StockItemLog.id DESC',
							]);
							if (count($lastStockItemLog)>0){
								if ($lastStockItemLog['StockItemLog']['product_quantity']>0){
									//pr($lastStockItemLog);
								}
								$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
								$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_cost'];
							}
						}
					}
					$products[$i][0]['Remaining']=$totalStockInventoryDate;
					$products[$i][0]['Saldo']=$totalValueInventoryDate;
				}
			
				break;					
		}
		//echo "products coming out of getInventoryItems<br/>";
		//pr($products);
		return $products;	
	}
  
  function getMaterialsForSale($productId=null,$quantityNeeded=0,$saleDate,$enterpriseId=0,$orderby="ASC"){
		$saleDatePlusOne=date("Y-m-d",strtotime($saleDate."+1 days"));
		$conditions=[
			'StockItem.product_id'=>$productId,
			'StockItem.remaining_quantity >'=>'0',
		];
		if (!empty($enterpriseId)){
			$conditions[]=[
				'StockItem.enterprise_id'=>$enterpriseId,
			];
		}
    //pr($conditions);
		$materialsComplete=false;
		$usedMaterials=[];
    $this->recursive=-1;
		$stockItem = $this->find('first', [
			'fields' => [
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_cost',
				'StockItem.remaining_quantity',
			],
			'conditions' => $conditions,
		]);
    //pr($stockItem);
    $quantityPresent=0;
    if (!empty($stockItem)){
      $quantityPresent=$stockItem['StockItem']['remaining_quantity'];
      if($quantityPresent >= $quantityNeeded){
        $quantityRemaining=$quantityPresent-$quantityNeeded;
      
        $usedMaterials[0]['id']=$stockItem['StockItem']['id'];
        $usedMaterials[0]['name']=$stockItem['StockItem']['name'];
        $usedMaterials[0]['unit_price']=$stockItem['StockItem']['product_unit_cost'];
        
        $usedMaterials[0]['quantity_present']=$quantityPresent;
        $usedMaterials[0]['quantity_used']=$quantityNeeded;
        $usedMaterials[0]['quantity_remaining']=$quantityRemaining;
      }
    }
    else {
      $usedMaterials[0]['id']=0;
      $usedMaterials[0]['name']='No hay producto';
      $usedMaterials[0]['unit_price']=0;
      
      $usedMaterials[0]['quantity_present']=0;
      $usedMaterials[0]['quantity_used']=0;
      $usedMaterials[0]['quantity_remaining']=0;
    }
		return $usedMaterials;
	}

  function getStockMovementDataForUtility($stockItemIds){
    $stockItems=$this->find('all',[
      'fields'=>['StockItem.remaining_quantity','StockItem.product_unit_cost'],
      'conditions'=>['StockItem.id'=>$stockItemIds],
      'contain'=>[
        'StockMovement'=>[
          'fields'=>[
            'StockMovement.product_quantity',
            'StockMovement.product_total_price',
            'StockMovement.bool_reclassification',
            'StockMovement.bool_transfer',
          ],
          'conditions'=>[
            'StockMovement.bool_input'=>false,
            'StockMovement.product_quantity >'=>0,
          ],
        ]
      ]
    ]);
    $quantitySold=0;
    $quantityStock=0;
    $quantityReclassified=0;
    $quantityTransferred=0;
    $valueSold=0;
    $valueStock=0;
    $valueReclassified=0;
    $valueTransferred=0;
    
    foreach ($stockItems as $stockItem){
      $quantityStock+=$stockItem['StockItem']['remaining_quantity'];
      $valueStock+=$stockItem['StockItem']['remaining_quantity']*$stockItem['StockItem']['product_unit_cost'];
      foreach ($stockItem['StockMovement'] as $stockMovement){
        if ($stockMovement['bool_reclassification']){
          $quantityReclassified+=$stockMovement['product_quantity'];
          $valueReclassified+=$stockMovement['product_total_price'];  
        }
        elseif($stockMovement['bool_transfer']){
          $quantityTransferred+=$stockMovement['product_quantity'];
          $valueTransferred+=$stockMovement['product_total_price'];
        }
        else {
          $quantitySold+=$stockMovement['product_quantity'];
          $valueSold+=$stockMovement['product_total_price'];
        }
      }
    }
    
    $stockMovementData=[
      'quantitySold'=>$quantitySold,
      'quantityStock'=>$quantityStock,
      'quantityReclassified'=>$quantityReclassified,
      'quantityTransferred'=>$quantityTransferred,
      'valueSold'=>$valueSold,
      'valueStock'=>$valueStock,
      'valueReclassified'=>$valueReclassified,
      'valueTransferred'=>$valueTransferred,
    ];
    
    return $stockMovementData;
  }
	
	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*
		'product_unit_cost' => [
			'numeric' => [
				'rule' => ['decimal',8),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		'remaining_quantity' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'type'=>'right outer',
		],
		'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'stock_item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'StockItemLog' => [
			'className' => 'StockItemLog',
			'foreignKey' => 'stock_item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

}
