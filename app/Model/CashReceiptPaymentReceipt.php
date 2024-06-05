<?php
App::uses('AppModel', 'Model');
/**
 * CashReceiptPaymentReceipt Model
 *
 * @property CashReceipt $CashReceipt
 * @property PaymentReceipt $PaymentReceipt
 * @property Currency $Currency
 */
class CashReceiptPaymentReceipt extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'cash_receipt_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'payment_receipt_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'currency_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CashReceipt' => array(
			'className' => 'CashReceipt',
			'foreignKey' => 'cash_receipt_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'PaymentReceipt' => array(
			'className' => 'PaymentReceipt',
			'foreignKey' => 'payment_receipt_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Currency' => array(
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
