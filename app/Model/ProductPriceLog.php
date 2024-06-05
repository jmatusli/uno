<?php
App::uses('AppModel', 'Model');

class ProductPriceLog extends AppModel {

  function getLatestFuelProductPriceLog($enterpriseId){
    $productModel=ClassRegistry::init('Product');
		$fuelProductIds=$productModel->find('list',[
			'fields'=>['Product.id'],
			'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],    
		]);
    
		$latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price_datetime','ProductPriceLog.enterprise_id'],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$fuelProductIds,
        'ProductPriceLog.enterprise_id'=>$enterpriseId,
        'ProductPriceLog.price_datetime <'=>date('Y-m-d 23:59:59'),
      ],
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		$duration=1000;
		if (!empty($latestProductPriceLog)){
      //pr($latestProductPriceLog);
			$productPriceDateTime=new DateTime(date('Y-m-d',strtotime($latestProductPriceLog['ProductPriceLog']['price_datetime'])));
			//pr($productPriceDateTime);
			$currentDate= new DateTime(date('Y-m-d'));
			$daysPassed=$currentDate->diff($productPriceDateTime);
			//pr($daysPassed);
			$duration=abs($daysPassed->format('%r%a'));
		}
    else {
      $latestProductPriceLog['ProductPriceLog']['price_datetime']=null;  
      $latestProductPriceLog['ProductPriceLog']['enterprise_id']=$enterpriseId;
    }
    $latestProductPriceLog['ProductPriceLog']['duration']=$duration;
		return $latestProductPriceLog;
	}

  function getLatestPrice($productId,$priceDate,$enterpriseId){
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price_datetime','ProductPriceLog.price'],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.enterprise_id'=>$enterpriseId,
        'DATE(ProductPriceLog.price_datetime) <='=>$priceDate,
      ],
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		return empty($latestProductPriceLog)?0:$latestProductPriceLog['ProductPriceLog']['price'];
  }

	public $validate = [
		'price_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'currency_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];
}
