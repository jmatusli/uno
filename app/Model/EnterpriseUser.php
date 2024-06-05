<?php
App::uses('AppModel', 'Model');

class EnterpriseUser extends AppModel {

	public function checkAssociationEnterpriseWithUser($enterpriseId,$userId){
		$this->recursive=-1;
		$enterpriseUserAssociation=$this->find('first',[
			'conditions'=>[
				'EnterpriseUser.enterprise_id'=>$enterpriseId,
				'EnterpriseUser.user_id'=>$userId,
			],
			'order'=>'EnterpriseUser.id DESC',
		]);
    //pr($enterpriseUserAssociation);
		if (!empty($enterpriseUserAssociation)){
			return $enterpriseUserAssociation['EnterpriseUser']['bool_assigned'];
		}
		return false;
	}
  
  public function getEnterpriseListForUser($userId){
    
    $user=$this->User->find('first',[
      'conditions'=>['User.id'=>$userId],
      'recursive'=>-1,
    ]);
    if (empty($user)) {return null;}
    
    $enterpriseConditions=['Enterprise.bool_active'=>true];
    
    if ($user['User']['role_id'] != ROLE_ADMIN){
      $enterpriseIds=$this->getAssociatedEnterprisesForUser($userId);
      $enterpriseConditions['Enterprise.id']=$enterpriseIds;
    }
    
    $enterprises=$this->Enterprise->find('list',[
      'fields'=>['Enterprise.id','Enterprise.company_name'],
      'conditions'=>$enterpriseConditions,
      'order'=>'Enterprise.company_name ASC',
    ]);
    return $enterprises;
  }
  
  public function getAssociatedEnterprisesForUser($userId){
    $this->recursive=-1;
		$enterpriseIdsAssociatedWithUserAtOneTime=$this->find('list',[
      'fields'=>['EnterpriseUser.enterprise_id'],
			'conditions'=>['EnterpriseUser.user_id'=>$userId],
			'order'=>'EnterpriseUser.id DESC',
		]);
    $enterpriseIdsAssociatedWithUserAtOneTime=array_unique($enterpriseIdsAssociatedWithUserAtOneTime);
    $this->Enterprise->recursive=-1;
    $uniqueEnterprises=$this->Enterprise->find('all',[
      'conditions'=>['Enterprise.id'=>$enterpriseIdsAssociatedWithUserAtOneTime,],
      'contain'=>[					
        'EnterpriseUser'=>[
          'conditions'=>['EnterpriseUser.user_id'=>$userId,],
          'order'=>'EnterpriseUser.assignment_datetime DESC,EnterpriseUser.id DESC',
        ],
  		],
    ]);
    $enterpriseIdsCurrentlyAssociated=[];
    foreach ($uniqueEnterprises as $enterprise){
      if ($enterprise['EnterpriseUser'][0]['bool_assigned']){
        $enterpriseIdsCurrentlyAssociated[]=$enterprise['Enterprise']['id'];
      }
    }
		return $enterpriseIdsCurrentlyAssociated;
	}

	public $validate = array(
		'enterprise_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
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


	public $belongsTo = [
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
