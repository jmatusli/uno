<?php
/**
 * HoseCounterFixture
 *
 */
class HoseCounterFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'counter_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'shift_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'hose_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'counter_value' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
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
			'counter_date' => '2019-07-06',
			'shift_id' => 1,
			'hose_id' => 1,
			'counter_value' => '',
			'created' => '2019-07-06 11:24:48',
			'modified' => '2019-07-06 11:24:48'
		),
	);

}
