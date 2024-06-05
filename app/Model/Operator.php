<?php
App::uses('AppModel', 'Model');
class Operator extends AppModel {
	var $displayField="name";
  
  public function getOperatorListForEnterprise($enterpriseId){
    $operators=$this->find('list',[
      'conditions'=>[
        'Operator.enterprise_id'=>$enterpriseId,
        'Operator.bool_active'=>true,
      ],
      'order'=>'Operator.name ASC',
    ]);
    return $operators;
  }

	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'Se debe especificar el nombre del operador',
				'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
    'enterprise_id' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'Se debe especificar la empresa del operador',
				'allowEmpty' => false,
				'required' => true,
				'last' => true, // Stop validation after this rule
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
	];
	public $hasMany = [
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'operator_id',
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
			'foreignKey' => 'operator_id',
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
