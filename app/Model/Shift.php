<?php
App::uses('AppModel', 'Model');

class Shift extends AppModel {
	var $displayField="name";

  public function getShiftListForEnterprise($enterpriseId){
    $shifts=$this->find('list',[
      'conditions'=>[
        'Shift.enterprise_id'=>$enterpriseId,
        'Shift.bool_active'=>true,
      ],
      'order'=>'Shift.display_order ASC',
    ]);
    return $shifts;
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
	];
	public $hasMany = [
		'Order' => [
			'className' => 'Order',
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
    'Invoice' => [
			'className' => 'Invoice',
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
    'PaymentReceipt' => [
			'className' => 'PaymentReceipt',
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
    'StockMovement' => [
			'className' => 'StockMovement',
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
	];

}
