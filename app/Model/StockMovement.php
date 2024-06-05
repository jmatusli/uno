<?php
App::uses('AppModel', 'Model');

class StockMovement extends AppModel {
  
  function getAdjustmentCode($userName,$offSet=0){
    $lastAdjustment=$this->find('first',[
			'fields'=>['StockMovement.adjustment_code'],
			'conditions'=>['StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_GENERAL,MOVEMENT_ADJUSTMENT_CALIBRATION,MOVEMENT_ADJUSTMENT_MEASURE],],
			'order'=>['StockMovement.adjustment_code' => 'desc'],
		]);
    if (!empty($lastAdjustment)){
      $adjustmentNumber=substr($lastAdjustment['StockMovement']['adjustment_code'],4,6)+1+$offSet;
      $adjustmentCode="AJU_".str_pad($adjustmentNumber,6,"0",STR_PAD_LEFT)."_".$userName;
    }
    else  {
      $adjustmentCode="AJU_000001_".$userName;
    }
		return $adjustmentCode;
  }

	function getTotalMovement($product_category_id = 1, $startdate=null, $enddate = null){
		//$this->recursive=2;
		return $this->find('all', array(
			'fields' => array(
				'StockMovement.product_id',
				'Product.name',
				'StockMovement.product_quantity',
				'SUM(StockMovement.product_quantity*StockMovement.product_unit_price) AS total_value',
			),
			
			'conditions' => array(
				//'Product.product_category_id'=>$product_category_id,
				'StockMovement.movement_date >'=>$startDate,
				'StockMovement.movement_date <='=>$endDate,
			),
			'group' => 'StockMovement.product_id', 
			
		));
	}

	public $validate = array(
		'movement_date' => array(
			'date' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*
		'product_quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		/*
		'product_unit_price' => array(
			'numeric' => array(
				'rule' => array('decimal',8),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'product_total_price' => array(
			'numeric' => array(
				'rule' => array('decimal',8),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		
	);


	public $belongsTo = [
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
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
    'Hose' => [
			'className' => 'Hose',
			'foreignKey' => 'hose_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Operator' => [
			'className' => 'Operator',
			'foreignKey' => 'operator_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Shift' => [
			'className' => 'Shift',
			'foreignKey' => 'shift_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'stock_item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
    'StockMovementType' => [
			'className' => 'StockMovementType',
			'foreignKey' => 'stock_movement_type_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

	public $hasMany = [
		
	];

}
