<?php
App::uses('CashReceiptPaymentReceipt', 'Model');

/**
 * CashReceiptPaymentReceipt Test Case
 *
 */
class CashReceiptPaymentReceiptTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cash_receipt_payment_receipt',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.enterprise',
		'app.client_enterprise',
		'app.employee',
		'app.employee_holiday',
		'app.holiday_type',
		'app.enterprise_user',
		'app.user',
		'app.role',
		'app.order',
		'app.stock_movement_type',
		'app.stock_movement',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.unit',
		'app.hose',
		'app.island',
		'app.hose_counter',
		'app.shift',
		'app.payment_receipt',
		'app.operator',
		'app.payment_mode',
		'app.hose_measurement',
		'app.product_price_log',
		'app.stock_item',
		'app.stock_item_log',
		'app.tank',
		'app.tank_measurement',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.client_user',
		'app.user_log',
		'app.accounting_register_cash_receipt'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CashReceiptPaymentReceipt = ClassRegistry::init('CashReceiptPaymentReceipt');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CashReceiptPaymentReceipt);

		parent::tearDown();
	}

}
