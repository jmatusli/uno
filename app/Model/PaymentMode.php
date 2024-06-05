<?php
App::uses('AppModel', 'Model');

class PaymentMode extends AppModel {

  var $displayField="name";

  public function getPaymentModeList(){
    $paymentModes=$this->find('list',[
      //'conditions'=>[
        //'PaymentMode.bool_active'=>true,
      //],
      'order'=>'PaymentMode.list_order ASC',
    ]);
    return $paymentModes;
  }
  
  public function getCreditPaymentModeList(){
    $paymentModes=$this->find('list',[
      'conditions'=>[
        'PaymentMode.id !='=>PAYMENT_MODE_CASH,
      ],
      'order'=>'PaymentMode.list_order ASC',
    ]);
    return $paymentModes;
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
  
  public $hasMany = [
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'payment_mode_id',
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
