<?php
App::uses('AppModel', 'Model');

class HoseMeasurement extends AppModel {
/*
  function getHoseMeasurementDifference($hoseId,$hoseMeasurementDate){
    $hoseMeasurementDateMinusOne=date( "Y-m-d", strtotime( $hoseMeasurementDate."-1 days" ) );  
    $hoseMeasurementConditions=[
      'HoseMeasurement.measurement_date >='=>$hoseMeasurementDateMinusOne,
      'HoseMeasurement.measurement_date <='=>$hoseMeasurementDate,
      'HoseMeasurement.hose_id'=>$hoseId,
      
    ];
    $hoseMeasurements=$this->find('all',[
      'fields'=>[
        'measurement_date',
        'hose_id',
        'measurement_value',
      ],
      'conditions'=>$hoseMeasurementConditions,
      'order'=>'measurement_date DESC',
    ]);
    if ($hoseMeasurementDate == '2020-02-01'){
      pr($hoseMeasurements);
    }
    $hoseMeasurementDifference=0;
    if (count($hoseMeasurements) == 2 ){
      $hoseMeasurementDifference+=$hoseMeasurements[0]['HoseMeasurement']['measurement_value']; 
      $hoseMeasurementDifference-=$hoseMeasurements[1]['HoseMeasurement']['measurement_value']; 
    }
    if ($hoseMeasurementDate == '2020-02-01'){
      pr($hoseMeasurementDifference);
    }
    return [
      'hoseId'=>$hoseId,
      'hoseMeasurementDate'=>$hoseMeasurementDate,
      'hoseMeasurementDifference'=>$hoseMeasurementDifference,
    ];
  }
*/
	public $validate = array(
		'measurement_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'hose_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
		'Hose' => array(
			'className' => 'Hose',
			'foreignKey' => 'hose_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
