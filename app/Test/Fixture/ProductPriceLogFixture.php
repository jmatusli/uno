<?php
/**
 * ProductPriceLogFixture
 *
 */
class ProductPriceLogFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'price_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'price' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '18,8', 'unsigned' => false),
		'price_currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'price_datetime' => '2019-06-30 15:37:51',
			'product_id' => 1,
			'price' => '',
			'price_currency_id' => 1,
			'created' => '2019-06-30 15:37:51',
			'modified' => '2019-06-30 15:37:51'
		),
	);

}
