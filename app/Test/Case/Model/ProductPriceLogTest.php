<?php
App::uses('ProductPriceLog', 'Model');

/**
 * ProductPriceLog Test Case
 *
 */
class ProductPriceLogTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.product_price_log',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.third_party',
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
		'app.stock_item',
		'app.unit',
		'app.stock_item_log',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.client_user',
		'app.user_log',
		'app.island',
		'app.hose',
		'app.hose_measurement',
		'app.operator',
		'app.shift',
		'app.payment_receipt',
		'app.payment_mode',
		'app.tank',
		'app.price_currency'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductPriceLog = ClassRegistry::init('ProductPriceLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductPriceLog);

		parent::tearDown();
	}

}
