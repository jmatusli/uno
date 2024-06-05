<?php
App::uses('Tank', 'Model');

/**
 * Tank Test Case
 *
 */
class TankTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.tank',
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
		'app.stock_item',
		'app.production_result_code',
		'app.warehouse',
		'app.stock_item_log',
		'app.production_movement',
		'app.island_product',
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
		'app.shift'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Tank = ClassRegistry::init('Tank');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Tank);

		parent::tearDown();
	}

}
