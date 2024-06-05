<?php
App::uses('AppModel', 'Model');

class Hose extends AppModel {
	var $displayField="name";

   public function getHoseListForEnterprise($enterpriseId){
    $hoses=$this->find('list',[
      'conditions'=>[
        'Hose.enterprise_id'=>$enterpriseId,
        'Hose.bool_active'=>true,
      ],
      'order'=>'Hose.name ASC',
    ]);
    return $hoses;
  }

  
  function getHoseUtility($hoseId,$startDate,$endDate){
    $hoseUtilityArray=[];
    
    $stockItemModel=ClassRegistry::init('StockItem');
    //pr($hoseUtilityArray);
    return $hoseUtilityArray;
  }
  
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Island' => [
			'className' => 'Island',
			'foreignKey' => 'island_id',
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
  public $hasMany = [
    'HoseCounter' => [
			'className' => 'HoseCounter',
			'foreignKey' => 'hose_id',
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
    'HoseMeasurement' => [
			'className' => 'HoseMeasurement',
			'foreignKey' => 'hose_id',
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
    'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'hose_id',
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
}

