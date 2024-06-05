<?php
App::uses('AppModel', 'Model');

class PaymentReceipt extends AppModel {

  public function getPendingFuelBonds($clientId){
    $pendingFuelBonds=$this->find('all',[
      'fields'=>[
        'id','payment_date','payment_amount','currency_id','client_id',
      ],
      'conditions'=>[
        'bool_paid'=>false,
        'client_id'=>$clientId,
      ],
      'contain'=>[
        'Currency'=>[
          'fields'=>['id','abbreviation'],
        ],
      ],
      'order'=>'payment_date ASC',
    ]);
    return $pendingFuelBonds;
  }
  
  function getAmountPaidAlreadyCS($id){
		$cashReceiptPaymentReceiptModel=ClassRegistry::init('CashReceiptPaymentReceipt');
		
		$cashReceiptsForPaymentReceipt=$cashReceiptPaymentReceiptModel->find('all',[
			'fields'=>[
				'CashReceiptPaymentReceipt.cash_receipt_id','CashReceiptPaymentReceipt.currency_id',
				'CashReceiptPaymentReceipt.amount','CashReceiptPaymentReceipt.payment','CashReceiptPaymentReceipt.payment_retention',
				'CashReceiptPaymentReceipt.payment_erdiff_CS','CashReceiptPaymentReceipt.payment_increment_CS','CashReceiptPaymentReceipt.payment_discount_CS',
			],
			'conditions'=>[
				'CashReceiptPaymentReceipt.payment_receipt_id'=>$id,
			],
		]);
		$paidAlreadyCS=0;
		//pr($cashReceiptsForPaymentReceipt);
		if (!empty($cashReceiptsForPaymentReceipt)){
			foreach ($cashReceiptsForPaymentReceipt as $cashReceiptForPaymentReceipt){
				//pr($cashReceiptForPaymentReceipt);
				$cashReceiptAmount=$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['payment']+$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['payment_retention'];
				$cashReceiptCurrencyId=$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['currency_id'];
				if ($cashReceiptCurrencyId==CURRENCY_CS){
					$paidAlreadyCS+=$cashReceiptAmount;
				}
				if ($cashReceiptCurrencyId==CURRENCY_USD){
					$cashReceiptModel=ClassRegistry::init('CashReceipt');
					$cashReceiptModel->recursive=-1;
					$cashReceipt=$cashReceiptModel->read(null,$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['cash_receipt_id']);
					
					$exchangeRateModel=ClassRegistry::init('ExchangeRate');
					$exchangeRateModel->recursive=-1;
					$cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceipt['CashReceipt']['receipt_date']);
					$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateCashReceipt;
				}
				//$paidAlreadyCS-=$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				$paidAlreadyCS-=$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['payment_increment_CS'];
				$paidAlreadyCS+=$cashReceiptForPaymentReceipt['CashReceiptPaymentReceipt']['payment_discount_CS'];
			}
		}
		return $paidAlreadyCS;
	}
	
  
	public $validate = [
		'payment_date' => [
      'date' => [
				'rule' => ['date'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'currency_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'operator_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'shift_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'payment_mode_id' => [
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
		'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
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
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'PaymentMode' => [
			'className' => 'PaymentMode',
			'foreignKey' => 'payment_mode_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
  public $hasMany = [
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'payment_receipt_id',
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
