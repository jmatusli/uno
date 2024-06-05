<?php
App::uses('AppModel', 'Model');
/**
 * AccountingCode Model
 *
 * @property AccountingCode $ParentAccountingCode
 * @property AccountingCode $ChildAccountingCode
 */
class AccountingCode extends AppModel {
	public $displayField="fullname";
	
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['fullname'] = sprintf(
			'CONCAT(%s.code, " (", %s.description,")")', $this->alias, $this->alias
		);
		$this->virtualFields['shortfullname'] = sprintf(
			'SUBSTR(CONCAT(%s.code, " (", %s.description,")"),1,50)', $this->alias, $this->alias
		);
	}
	//public $virtualFields = array('fullname' => 'CONCAT(ChildAccountingCode.code, " (", ChildAccountingCode.description,")")');
/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Tree',
	);
	
	function getSaldo($accountingcodeid,$registerdate){	
		$accountingCode= $this->find('first', array(
			'conditions'=>array('id'=>$accountingcodeid),
			'contain' => array(
				'AccountingMovement'=>array(
					'fields' => array(
						'AccountingMovement.amount',
						'AccountingMovement.currency_id',
						'AccountingMovement.bool_debit',
					),
					'conditions' => array(
						'AccountingMovement.accounting_code_id'=>$accountingcodeid,
						'AccountingMovement.amount >'=>'0',
					),
					'AccountingRegister'=>array(
						'fields'=>array('AccountingRegister.id','AccountingRegister.register_date'),
						'conditions'=>array(
							'AccountingRegister.register_date <'=>$registerdate,
						),
					)
				)
			),			
		));
		$saldo=0;
		foreach ($accountingCode['AccountingMovement'] as $accountingMovement){
			if (!empty($accountingMovement['AccountingRegister'])){
				if ($accountingMovement['bool_debit']){
					$saldo+=$accountingMovement['amount'];
				}
				else {
					$saldo-=$accountingMovement['amount'];
				}
			}
			//echo "saldo is ".$saldo."<br/>";
		}
		
		if ($accountingCode['AccountingCode']['bool_creditor']){
			$saldo=-$saldo;
		}
		//echo "saldo after conversion is ".$saldo."<br/>";
		return $saldo;
	}
	
	function getTotalSaldo($accountingcodeid,$registerdate){
		$accountingCode=$this->read(null,$accountingcodeid);
		$bool_parent_credit=$accountingCode['AccountingCode']['bool_creditor'];
		$descendentcodeids=$this->find('list',array(
			'fields' => array('AccountingCode.id'),
			'conditions' => array(
				'AccountingCode.lft BETWEEN ? AND ?' => array($accountingCode['AccountingCode']['lft']+1, $accountingCode['AccountingCode']['rght']-1),
			)
		));
		//pr($descendentcodeids);
		$totalSaldo=$this->getSaldo($accountingcodeid,$registerdate);
		//echo "total saldo is ".$totalSaldo."<br/>";
		if (!empty($descendentcodeids)){
			foreach ($descendentcodeids as $descendentcodeid){
				//pr($registerdate);
				//$totalchildren=$this->getTotalSaldo($descendentcodeid,$registerdate);
				$saldochildren=$this->getSaldo($descendentcodeid,$registerdate);
				//pr($saldochildren);
				$childAccountingCode=$this->read(null,$descendentcodeid);
				$bool_child_credit=$childAccountingCode['AccountingCode']['bool_creditor'];
				if ($bool_parent_credit==$bool_child_credit){
					$totalSaldo+=$saldochildren;
				}
				else {
					$totalSaldo-=$saldochildren;
				}
			}
		}
		return $totalSaldo;
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'code' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*'bool_creditor' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),*/
		/*
		'lft' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'rght' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'ParentAccountingCode' => array(
			'className' => 'AccountingCode',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'ChildAccountingCode' => array(
			'className' => 'AccountingCode',
			'foreignKey' => 'parent_id',
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
		'AccountingMovement' => array(
			'className' => 'AccountingMovement',
			'foreignKey' => 'accounting_code_id',
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
