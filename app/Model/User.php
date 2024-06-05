<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Role $Role
 * @property UserLog $UserLog
 */
class User extends AppModel {

	public $actsAs = array('Acl' => array('type' => 'requester', 'enabled' => false));

	public function bindNode($user) {
		return array('model' => 'Role', 'foreign_key' => $user['User']['role_id']);
	}
	
  public function parentNode() {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        if (isset($this->data['User']['role_id'])) {
            $roleId = $this->data['User']['role_id'];
        } 
		else {
            $roleId = $this->field('role_id');
        }
        if (!$roleId) {
            return null;
        } 
		else {
            return array('Role' => array('id' => $roleId));
        }
    }

	public function beforeSave($options = array()) {
		//echo "executing beforesave<br/>";
		//pr($this->data[$this->alias]);
		
		if (array_key_exists('pwd',$this->data[$this->alias])){
			if (!empty($this->data[$this->alias]['pwd'])){
				//echo "now I am encrypting the password with the AuthComponent for the pwd";
				$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['pwd']);
				//pr($this->request->data[$this->alias]);
			}
			else {
				//if password is not set, auth data is taken into account
        //echo "getting the password from the user id";
				unset($this->data[$this->alias]['password']);
				if(!empty($this->data[$this->alias]['id'])){
					$currentUser=$this->find('first',array(
						'conditions'=>array(
							'User.id'=>$this->data[$this->alias]['id'],
						),
					));
          //pr($currentUser);
					if (!empty($currentUser)){
						$this->data[$this->alias]['password'] = $currentUser[$this->alias]['password'];
					}
          //pr($this->request->data[$this->alias]);
				}
			}
		}
		elseif (array_key_exists('password',$this->data[$this->alias])){
			if (!empty($this->data[$this->alias]['password'])){
				//echo "now I am encrypting the password with the AuthComponent for the pwd";
				$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
				//pr($this->request->data[$this->alias]);
			}
		}
		
		//echo "printing the request data<br/>";
		//pr($this->data[$this->alias]);
        return true;
	}
	
	public $validate = array(
		'username' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		//'password' => array(
		//	'notEmpty' => array(
		//		'rule' => array('notEmpty'),
		//		//'message' => 'Your custom message here',
		//		//'allowEmpty' => false,
		//		//'required' => false,
		//		//'last' => false, // Stop validation after this rule
		//		//'on' => 'create', // Limit validation to 'create' or 'update' operations
		//	),
		//),
		'role_id' => array(
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
    'Client' => array(
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Role' => array(
			'className' => 'Role',
			'foreignKey' => 'role_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
    'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		//Client User association is the relation between user and client created manually
		'ClientUser' => array(
			'className' => 'ClientUser',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
    //Client User association is the relation between user and client created manually
		'EnterpriseUser' => array(
			'className' => 'EnterpriseUser',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'UserLog' => array(
			'className' => 'UserLog',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
