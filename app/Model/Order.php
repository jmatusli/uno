<?php
App::uses('AppModel', 'Model');
class Order extends AppModel {
	public $displayField='order_code';

  public function getOrderIdForEnterpriseAndDate($enterpriseId,$paymentDate){
    $order=$this->getOrderForEnterpriseAndDate($enterpriseId,$paymentDate);
    if (empty($order)){
      return 0;
    }
    //pr($order);
    return $order['Order']['id'];
  }
  public function getOrderForEnterpriseAndDate($enterpriseId,$paymentDate){
    $order=$this->find('first',[
      'fields'=>['Order.id','Order.order_code',],
      'conditions'=>[
        'Order.enterprise_id'=>$enterpriseId,
        'DATE(Order.order_date)'=>$paymentDate,
        'Order.stock_movement_type_id'=>MOVEMENT_SALE,
      ],
      'recursive'=>-1,
    ]);
    //pr($order);
    return $order;
  }
  public function getOrdersForEnterprise($enterpriseId,$orderIds=[]){
    $orders=$this->find('list',[
      'fields'=>['Order.id','Order.order_code',],
      'conditions'=>[
        'Order.enterprise_id'=>$enterpriseId,
      ],
    ]);
    return $orders;
  }
  
	public $validate = [];

	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'ThirdParty' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'third_party_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'StockMovementType' => array(
			'className' => 'StockMovementType',
			'foreignKey' => 'stock_movement_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
    'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
	
	public $hasMany = array(
		'StockMovement' => array(
			'className' => 'StockMovement',
			'foreignKey' => 'order_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Invoice' => array(
			'className' => 'Invoice',
			'foreignKey' => 'order_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CashReceipt' => array(
			'className' => 'CashReceipt',
			'foreignKey' => 'order_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);
}
