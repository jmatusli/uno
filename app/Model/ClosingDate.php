<?php
App::uses('AppModel', 'Model');
/**
 * ClosingDate Model
 *
 */
class ClosingDate extends AppModel {

	public function getLatestClosingDate($enterpriseId){
		$latestClosingDate=$this->find('first',[
      'conditions'=>['ClosingDate.enterprise_id'=>$enterpriseId],
      'order'=>'closing_date DESC',
    ]);
		return $latestClosingDate['ClosingDate']['closing_date'];
	}

	public $validate = [
		'ìd' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
		'closing_date' => [
			'datetime' => [
				'rule' => ['datetime'],
			],
		],
	];
  
  	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
