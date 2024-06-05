<?php
App::uses('AppModel', 'Model');

class StockItemLog extends AppModel {

	public function getProductCost($productId,$inventoryDate,$enterpriseId=0){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    $stockItemModel=ClassRegistry::init('StockItem');

    $productPrice=0; 
    
		$conditions=[
			'StockItem.product_id'=> $productId,
		];
		if (!empty($enterpriseId)){
			$conditions['StockItem.enterprise_id']=$enterpriseId;
		}
    $stockItemModel->recursive=-1;
    $stockItem=$stockItemModel->find('first',[
			'fields'=>'StockItem.id',
			'conditions' => $conditions,
		]);
    //pr($stockItem);
		
    if (!empty($stockItem)){
			$stockItemId=$stockItem['StockItem']['id'];
      
      $stockItemLogConditions=[
        'StockItemLog.stock_item_id'=>$stockItemId,
        'StockItemLog.stock_item_date <'=>$inventoryDatePlusOne,
      ];
      $stockItemLog=$this->find('first',[
        'fields'=>'StockItemLog.product_unit_cost',
        'conditions'=>$stockItemLogConditions,
        'order'=>'StockItemLog.id DESC',
      ]);
      if (!empty($stockItemLog)){
        $productPrice=$stockItemLog['StockItemLog']['product_unit_cost'];
      }
		};
    
		return $productPrice;
	}


  
  public function getStockQuantityAtDateForProduct($productId,$inventoryDate,$enterpriseId=0,$boolReturnQuantityOnCurrentDate=false){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$quantityInStock=0;
		$stockItemConditions=[
			'StockItem.product_id'=> $productId,
		];
		if (!empty($enterpriseId)){
			$stockItemConditions['StockItem.enterprise_id']=$enterpriseId;
		}
    //echo "bool current date is ".$boolReturnQuantityOnCurrentDate."<br/>";
    if ($boolReturnQuantityOnCurrentDate){
      $stockItemConditions['StockItem.remaining_quantity >']= 0;
    }
		$stockItemIds=$this->StockItem->find('list',[
			'fields'=>'StockItem.id',
			'conditions' => $stockItemConditions,
		]);
		if (!empty($stockItemIds)){
			foreach($stockItemIds as $id=>$stockItemId){
        $stockItemLogConditions=['StockItemLog.stock_item_id'=>$stockItemId];
        if (!$boolReturnQuantityOnCurrentDate){
          $stockItemLogConditions['StockItemLog.stock_item_date <=']=$inventoryDate;
        }
        //pr($stockItemLogConditions);
				$stockItemLog=$this->find('first',[
					'fields'=>'StockItemLog.product_quantity',
					'conditions'=>$stockItemLogConditions,
					'order'=>'StockItemLog.id DESC',
				]);
				if (!empty($stockItemLog)){
					$quantityInStock+=$stockItemLog['StockItemLog']['product_quantity'];
				}
			}
		};								
		return $quantityInStock;
	}

  public function getStockQuantityAtDateForFuel($fuelId,$unitId,$inventoryDate,$enterpriseId=0,$boolReturnQuantityOnCurrentDate=false){
		$quantityInStock=$this->getStockQuantityAtDateForProduct($fuelId,$inventoryDate,$enterpriseId,$boolReturnQuantityOnCurrentDate);
		if ($unitId == UNIT_LITERS){
      $quantityInStock *= GALLONS_TO_LITERS;
    }
		return $quantityInStock;
	}
	public $validate = [
		'stock_item_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'stock_item_date' => [
			'datetime' => [
				'rule' => ['datetime'],
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
		'product_quantity' => [
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
			],
		],
		*/
	];

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'stock_item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'stock_movement_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		
	];
}
