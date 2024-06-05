<?php
/**
 * TankMeasurementFixture
 *
 */
class TankMeasurementFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'measurement_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'tank_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'measurement_value' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'measurement_date' => '2019-06-10',
			'tank_id' => 1,
			'measurement_value' => 1,
			'created' => '2019-06-10 14:39:41',
			'modified' => '2019-06-10 14:39:41'
		),
	);

}
