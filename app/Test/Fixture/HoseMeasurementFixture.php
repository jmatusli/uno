<?php
/**
 * HoseMeasurementFixture
 *
 */
class HoseMeasurementFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'measurement_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'hose_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'measurement_value' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '8,2', 'unsigned' => false),
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
			'measurement_date' => '2019-06-13',
			'hose_id' => 1,
			'measurement_value' => '',
			'created' => '2019-06-13 12:41:19',
			'modified' => '2019-06-13 12:41:19'
		),
	);

}
