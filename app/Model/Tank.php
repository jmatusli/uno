<?php
App::uses('AppModel', 'Model');

class Tank extends AppModel {
  
  public function getTankGraphData ($inventoryDate,$providedTankValues=[],$unitId=UNIT_GALLONS){
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    //pending add parameter for date and retrieve quantities from stockitemlog instead of stockitem
    $tanks=$this->find('all',[
      'contain'=>[
        'Product'=>[
          'StockItem'=>[
            'StockItemLog'=>[
              'conditions'=>['StockItemLog.stock_item_date <='=>$inventoryDatePlusOne],
              'order'=>'StockItemLog.id DESC',
              'limit'=>1,
            ],
            'Unit',
          ],
        ],
        'Unit',
      ],
      'order'=>'Product.product_order ASC'
    ]);
    //pr($tanks);
    //$this->set(compact('tanks'));
    
    $tankData=[];
    $tankFuelIds=[];
    $tankLabels=[];
    $tankValues=[];
    $tankBackgroundColors=[];
    $tankBorderColors=[];
    foreach ($tanks as $tank){
      $tankFuelId=$tank['Product']['id'];
      
      $tankName=$tank['Tank']['name']." ".$tank['Tank']['description'];
      if (!empty($tank['Product']['StockItem'][0]['StockItemLog'])){
        //pr($tank['Product']['StockItem'][0]['StockItemLog']);
        $tankVolume=$tank['Product']['StockItem'][0]['StockItemLog'][0]['product_quantity'];
      }
      else {
        $tankVolume=0;  
      }
      
      //echo "tank product id is ".$tank['Product']['id']."<br/>";
      if (count($providedTankValues)>0 && !empty($providedTankValues[$tank['Product']['id']])){
        //pr($providedTankValues);
        //echo "the new tank value is ".$providedTankValues[$tank['Product']['id']]."<br/>";
        $tankVolume=$providedTankValues[$tank['Product']['id']];
      }
      $volumeUnitId=$tank['Product']['StockItem'][0]['unit_id'];
      $tankCapacity=$tank['Tank']['total_capacity'];
      //todo check if units are the same, if not multiply by the gallon factor
      $tankPercentage=100*$tankVolume/$tankCapacity;
      $tankLabel=$tankName." (".(round(100*$tankPercentage)/100)."%)";
      
      if ($tankPercentage>=60){
        $backgroundColor='rgba(75, 192, 192, 0.2)';
        $borderColor='rgba(75, 192, 192, 1)';  
      }
      elseif ($tankPercentage>=30){
        $backgroundColor='rgba(255, 159, 64, 0.2)';
        $borderColor='rgba(255, 159, 64, 1)';  
      }
      else{
        $backgroundColor='rgba(255, 99, 132, 0.2)';
        $borderColor='rgba(255, 99, 132, 1)';  
      }
      $tankFuelIds[]=$tankFuelId;
      $tankLabels[]=$tankLabel;
      $tankValues[]=$tankVolume;
      $tankBackgroundColors[]=$backgroundColor;
      $tankBorderColors[]=$borderColor;
    }
    $tankData['fuelIds']=$tankFuelIds;
    $tankData['labels']=$tankLabels;
    $tankData['values']=$tankValues;
    $tankData['backgroundColors']=$tankBackgroundColors;
    $tankData['borderColors']=$tankBorderColors;
    // todo: make conversion to gallons possible
    $tankData['legend']="Volumen en tanques (galones)";
    
    //pr($tankData);
    //echo implode(",",$tankData['labels']);
    return $tankData;
  }
  
  public function getTankIdsByFuels(){
    $tanks=$this->find('list',['fields'=>['id','product_id']]);
    $tankByFuelArray=[];
    foreach($tanks as $tankId=>$productId){
      $tankByFuelArray[$productId]=$tankId;
    }
    return $tankByFuelArray;
  }
  
	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
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
		'bool_active' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
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
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
  
  public $hasMany = [
		'TankMeasurement' => [
			'className' => 'TankMeasurement',
			'foreignKey' => 'tank_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
