<?php
App::uses('HoseCounter', 'Model');

/**
 * HoseCounter Test Case
 *
 */
class HoseCounterTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.hose_counter',
		'app.shift',
		'app.enterprise',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.third_party',
		'app.client_enterprise',
		'app.client_user',
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
		'app.hose_measurement',
		'app.product_price_log',
		'app.stock_item',
		'app.stock_item_log',
		'app.tank',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.enterprise_user',
		'app.user_log',
		'app.employee',
		'app.employee_holiday',
		'app.holiday_type',
		'app.operator',
		'app.payment_receipt',
		'app.payment_mode'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->HoseCounter = ClassRegistry::init('HoseCounter');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->HoseCounter);

		parent::tearDown();
	}

}
