<?php
App::uses('AppModel', 'Model');

class Island extends AppModel {
	var $displayField="name";

  public function getIslandListForEnterprise($enterpriseId){
    $shifts=$this->find('list',[
      'conditions'=>[
        'Island.enterprise_id'=>$enterpriseId,
        'Island.bool_active'=>true,
      ],
      'order'=>'Island.name ASC',
    ]);
    return $shifts;
  }

	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operatiosns
			),
		),
	);

	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
    'Hose' => [
			'className' => 'Hose',
			'foreignKey' => 'island_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

}
