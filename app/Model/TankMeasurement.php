<?php
App::uses('AppModel', 'Model');

class TankMeasurement extends AppModel {

  function getCurrentInventoryTankMeasurementStatus($enterpriseId){
    $productModel=ClassRegistry::init('Product');
    $stockMovementModel=ClassRegistry::init('StockMovement');
    //echo "enterprise id ".$enterpriseId."<br/>";
		$fuelProducts=$productModel->find('all',[
			'fields'=>['Product.id','Product.name',],
			'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],    
      'contain'=>[
        'StockItem'=>[
          'fields'=>['StockItem.id'],
          'conditions'=>[
            //'StockItem.enterprise_id'=>$enterpriseId,
          ],
        ],
        'Tank'=>[
          'fields'=>['Tank.id','Tank.enterprise_id'],
          'conditions'=>[
            //'Tank.enterprise_id'=>$enterpriseId,
          ],
        ],
      ],
      'order'=>['Product.product_order ASC'],
		]);
    //pr($fuelProducts);
    $currentDate= new DateTime(date('Y-m-d'));
    //get the day of the week
    $currentWeekDay=date('w',strtotime($currentDate->format('Y-m-d')));
    //pr($currentWeekDay);
    $latestSunday=new DateTime(date("Y-m-d",strtotime($currentDate->format('Y-m-d')."-".$currentWeekDay." days")));
    //pr($latestSunday);
    
    $fuelValues=[];
    $measurementsPresent=true;
    $adjustmentsPresent=true;
    //pr($fuelProducts);
    
    foreach ($fuelProducts as $fuel){
      // check if tanks and stockitems are already present
      if (!empty($fuel['Tank'])){
        $fuelId=$fuel['Product']['id'];
        $fuelValues[$fuelId]['name']=$fuel['Product']['name'];
        $tankMeasurement=$this->find('first',[
          'conditions'=>[
            'DATE(TankMeasurement.measurement_date)'=>($latestSunday->format('Y-m-d')),
            'TankMeasurement.tank_id'=>$fuel['Tank'][0]['id'],
          ],
          'order'=>['TankMeasurement.measurement_date DESC','TankMeasurement.id DESC'],
        ]);
        if (!empty($tankMeasurement)){
          $fuelValues[$fuelId]['sunday_measurement_value']=round($tankMeasurement['TankMeasurement']['measurement_value'],3);
        }
        else {
          $fuelValues[$fuelId]['sunday_measurement_value']=0;
          $measurementsPresent=false;
        } 
        
        $tankAdjustmentMovement=$stockMovementModel->find('first',[
          'conditions'=>[
            'DATE(StockMovement.movement_date)'=>($latestSunday->format('Y-m-d')),
            'StockMovement.product_id'=>$fuelId,
            'StockMovement.stock_item_id'=>$fuel['StockItem'][0]['id'],
            'StockMovement.stock_movement_type_id'=>MOVEMENT_ADJUSTMENT_MEASURE,
          ],
          'order'=>['StockMovement.movement_date DESC','StockMovement.id DESC'],
        ]);
        if (!empty($tankAdjustmentMovement)){
          $fuelValues[$fuelId]['adjustment_value']=round($tankAdjustmentMovement['StockMovement']['product_quantity'],3);
        }
        else {
          $fuelValues[$fuelId]['adjustment_value']=0;
          $adjustmentsPresent=false;
        }
      }
      else {
        $fuelValues[$fuelId]['sunday_measurement_value']=0;
        $measurementsPresent=false;
        
         $fuelValues[$fuelId]['adjustment_value']=0;
        $adjustmentsPresent=false;
      }
      
      
      
    }
    //pr($latestSundayTankMeasurementValues);
    $inventoryTankMeasurementStatus =[
      'sunday_measurement_date'=>($latestSunday->format('Y-m-d')),
      'fuel_values'=>$fuelValues,
      'measurements_present'=>$measurementsPresent,
      'adjustments_present'=>$adjustmentsPresent,
      'week_day'=>$currentWeekDay,
    ];
    
    
    
    return $inventoryTankMeasurementStatus;
	}

	public $validate = [
		'measurement_date' => [
			'date' => [
				'rule' => ['date'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'tank_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'measurement_value' => [
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
		'Tank' => [
			'className' => 'Tank',
			'foreignKey' => 'tank_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];
}
