<?php
App::uses('AppModel', 'Model');

class ClientEnterprise extends AppModel {

	public function checkAssociationClientWithEnterprise($clientId,$enterpriseId){
		$this->recursive=-1;
		$clientEnterpriseAssociation=$this->find('first',[
			'conditions'=>[
				'ClientEnterprise.client_id'=>$clientId,
				'ClientEnterprise.enterprise_id'=>$enterpriseId,
			],
			'order'=>'ClientEnterprise.id DESC',
		]);
		if (!empty($clientEnterpriseAssociation)){
      //pr($clientEnterpriseAssociation);
			return $clientEnterpriseAssociation['ClientEnterprise']['bool_assigned'];
		}
		return false;
	}
  
  public function getAssociatedClientsForEnterprise($enterpriseId){
    $this->recursive=-1;
		$clientIdsAssociatedWithEnterpriseAtOneTime=$this->find('list',[
      'fields'=>['ClientEnterprise.client_id'],
			'conditions'=>['ClientEnterprise.enterprise_id'=>$enterpriseId],
			'order'=>'ClientEnterprise.id DESC',
		]);
    $clientIdsAssociatedWithEnterpriseAtOneTime=array_unique($clientIdsAssociatedWithEnterpriseAtOneTime);
    $this->Client->recursive=-1;
    $uniqueClients=$this->Client->find('all',[
      'conditions'=>['Client.id'=>$clientIdsAssociatedWithEnterpriseAtOneTime,],
      'contain'=>[					
        'ClientEnterprise'=>[
          'conditions'=>[
            'ClientEnterprise.enterprise_id'=>$enterpriseId,
          ],
          'order'=>'ClientEnterprise.assignment_datetime DESC,ClientEnterprise.id DESC',
        ]
  		],
    ]);
    $clientIdsCurrentlyAssociated=[];
    foreach ($uniqueClients as $client){
      if ($client['ClientEnterprise'][0]['bool_assigned']){
        $clientIdsCurrentlyAssociated[]=$client['Client']['id'];
      }
    }
		return $clientIdsCurrentlyAssociated;
	}

	public $validate = array(
		'client_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
	);

	public $belongsTo =[
		'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
