<?php
App::uses('PaymentReceipt', 'Model');

/**
 * PaymentReceipt Test Case
 *
 */
class PaymentReceiptTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.payment_receipt',
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
		'app.hose',
		'app.island',
		'app.hose_measurement',
		'app.stock_item',
		'app.unit',
		'app.stock_item_log',
		'app.production_movement',
		'app.warehouse',
		'app.tank',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.client_user',
		'app.user_log',
		'app.operator',
		'app.shift',
		'app.payment_mode'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PaymentReceipt = ClassRegistry::init('PaymentReceipt');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PaymentReceipt);

		parent::tearDown();
	}

}
