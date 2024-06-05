<?php
App::uses('AppModel', 'Model');

class Product extends AppModel {

var $displayField="name";

  function getFuelList(){
		$fuels= $this->find('list', [
      'fields'=>['Product.id','Product.name'],
			'conditions'=>[
				'Product.product_type_id'=>PRODUCT_TYPE_FUELS,
        'Product.bool_active'=>true,
			],
			'order'=>'Product.product_order',
		]);
    //pr($fuels);
    return $fuels;
	}

  function getTankListForEnterprise($enterpriseId){
		$fuels= $this->find('all', [
      'fields' => ['Product.id','Product.name','Product.product_order'],
      'conditions' => [ 'Product.product_type_id'=>PRODUCT_TYPE_FUELS,],
      'contain'=>[
        'Tank'=>[
          'fields' => ['Tank.id','Tank.name'],  
          'conditions'=>[
            'Tank.enterprise_id'=>$enterpriseId,
            'Tank.bool_active'=>true,
          ],
        ],
      ],
      'order'=>'Product.product_order ASC',
		]);
    //pr($fuels);
    
    $tanks=[];
    if (!empty($fuels)){
      foreach ($fuels as $fuel){
        $tanks[$fuel['Tank'][0]['id']]=$fuel['Tank'][0]['name'];
      }
    }
    return $tanks;
	}

  function getTanksAndFuelsForEnterprise($enterpriseId){
		$fuels= $this->find('all', [
      'fields' => ['Product.id','Product.name','Product.product_order'],
      'conditions' => [ 'Product.product_type_id'=>PRODUCT_TYPE_FUELS,],
      'contain'=>[
        'Tank'=>[
          'fields' => ['Tank.id','Tank.name'],  
          'conditions'=>[
            'Tank.enterprise_id'=>$enterpriseId,
            'Tank.bool_active'=>true,
          ],
        ],
      ],
      'order'=>'Product.product_order ASC',
		]);
    return $fuels;
	}

	function getProductCategoryId($productId){
		$this->recursive=-1;
		$product= $this->find('first', 
			array(
				'fields' => array('Product.product_type_id'),
				'conditions' => array('Product.id'=>$productId)
			)
		);
		$this->ProductType->recursive=-1;
		$productType= $this->ProductType->find('first', 
			array(
				'fields' => array('ProductType.product_category_id'),
				'conditions' => array('ProductType.id'=>$product['Product']['product_type_id'])
			)
		);
		return $productType['ProductType']['product_category_id'];
	}
	
	function getProductPackagingUnit($productid){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		if (!$productid){
			throw new NotFoundException(__('No producto seleccionado'));
		}
		if (!$this->Product->exists($productid)) {
			throw new NotFoundException(__('Producto no existe'));
		}
		$product=$this->Product->read(null,$productid);
		echo $product['Product']['packaging_unit'];
	}
	 
	public function getDefaultCost(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender = false;
		
    $productId=trim($_POST['productId']);
		$currencyId=trim($_POST['currencyId']);
		
    $defaultCost=0;
    
		$product=$this->Product->find('first',[
			'fields'=>['Product.default_cost'],
			'conditions'=>[
				'Product.id'=>$productId,
        'Product.default_cost_currency_id'=>$currencyId,
			],
		]);
		
		if (!empty($product)){
      $defaultCost=$product['Product']['default_cost'];
    }
		return $defaultCost;
	} 
	
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'product_type_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'packaging_unit' => array(
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
		'ProductType' => array(
			'className' => 'ProductType',
			'foreignKey' => 'product_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'AccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'DefaultCostCurrency' => [
			'className' => 'Currency',
			'foreignKey' => 'default_cost_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'DefaultCostUnit' => [
			'className' => 'Unit',
			'foreignKey' => 'default_cost_unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'DefaultPriceCurrency' => [
			'className' => 'Currency',
			'foreignKey' => 'default_price_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'DefaultPriceUnit' => [
			'className' => 'Unit',
			'foreignKey' => 'default_price_unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	);

	public $hasMany = [
		'Hose' => [
			'className' => 'Hose',
			'foreignKey' => 'product_id',
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
    'ProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'product_id',
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
    'PreviousProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'product_id',
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
    'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'product_id',
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
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'product_id',
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
		'Tank' => [
			'className' => 'Tank',
			'foreignKey' => 'product_id',
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
