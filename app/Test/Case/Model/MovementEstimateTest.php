<?php
App::uses('MovementEstimate', 'Model');

/**
 * MovementEstimate Test Case
 *
 */
class MovementEstimateTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.movement_estimate',
		'app.enterprise',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.third_party',
		'app.payment_receipt',
		'app.operator',
		'app.order',
		'app.stock_movement_type',
		'app.user',
		'app.role',
		'app.client_user',
		'app.enterprise_user',
		'app.user_log',
		'app.stock_movement',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.unit',
		'app.hose',
		'app.island',
		'app.hose_counter',
		'app.shift',
		'app.invoice',
		'app.payment_mode',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.hose_measurement',
		'app.product_price_log',
		'app.stock_item',
		'app.stock_item_log',
		'app.tank',
		'app.tank_measurement',
		'app.client_enterprise',
		'app.employee',
		'app.employee_holiday',
		'app.holiday_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MovementEstimate = ClassRegistry::init('MovementEstimate');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MovementEstimate);

		parent::tearDown();
	}

}
