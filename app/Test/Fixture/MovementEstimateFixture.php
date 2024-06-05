<?php
/**
 * MovementEstimateFixture
 *
 */
class MovementEstimateFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'enterprise_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'estimate_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'bool_sale' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
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
			'enterprise_id' => 1,
			'estimate_date' => '2020-03-30',
			'product_id' => 1,
			'bool_sale' => 1,
			'user_id' => 1,
			'created' => '2020-03-30 10:10:00',
			'modified' => '2020-03-30 10:10:00'
		),
	);

}
