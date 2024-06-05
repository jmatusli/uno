<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class StockMovementsController extends AppController {
	
	//public $components = array('Paginator');
  public $components = ['Paginator','RequestHandler'];

	// throws an error when both are present.  InventoryCountDisplay Also does not seem to be needed for StockMovementsController
	//public $helpers = array('PhpExcel','InventoryCountDisplay'); 
	public $helpers = array('PhpExcel'); 

  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('registeradjustmentmovement','registerbottleadjustmentmovements','sortByMovementDate');		
	}
  
  public function informeDiario($saleDateAsString = '') {
		$this->loadModel('Order');
    $this->loadModel('Product');   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
			
		$this->loadModel('Hose');
    $this->loadModel('HoseCounter');
    $this->loadModel('Island');
     $this->loadModel('Operator');
		$this->loadModel('Shift');
    
    $this->loadModel('Enterprise');
    
    $this->loadModel('Tank');
    $this->loadModel('TankMeasurement');
    
    $this->loadModel('HoseMeasurement');
    
    $this->Order->recursive=-1;
		$this->Product->recursive=-1;
    $this->StockMovement->recursive=-1;
		
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}    
		if ($this->request->is('post')) {
			$saleDateArray=$this->request->data['Report']['order_date'];
			$saleDateAsString=$saleDateArray['year'].'-'.$saleDateArray['month'].'-'.$saleDateArray['day'];
			$saleDate=date( "Y-m-d", strtotime($saleDateAsString));
      
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
    elseif (!empty($saleDateAsString)){
      $saleDate=date( "Y-m-d", strtotime($saleDateAsString));
      //$saleDateAsString=$saleDateAsString;
    }
		else if (!empty($_SESSION['saleDate']) && !empty($_SESSION['endDate'])){
			$saleDateAsString=$saleDate=$_SESSION['saleDate'];
		}
		else {
			$saleDateAsString=$saleDate = date( "Y-m-d", strtotime(date('Y-m-d')."-1 days"));
		}
    $_SESSION['saleDate']=$saleDate;
    $saleDateMinusOne=date( "Y-m-d", strtotime($saleDateAsString."-1 days"));
    $saleDatePlusOne=date( "Y-m-d", strtotime($saleDateAsString."+1 days"));
    $this->set(compact('saleDate'));
    
    
    $enterprises=$this->Enterprise->find('list',[
			'conditions'=>['Enterprise.bool_active'=>true],
      'order'=>'Enterprise.company_name',
		]);
		$this->set(compact('enterprises'));
    
    $this->set(compact('enterpriseId'));
    
    $shifts=$this->Shift->find('list',[
      'conditions'=>['Shift.bool_active'=>true],
			'order'=>'Shift.name',
		]);
		$this->set(compact('shifts'));
    
    //INFORME II
    $initialTankData=$this->Tank->getTankGraphData($saleDateMinusOne,[],$unitId=UNIT_GALLONS);
    //pr($initialTankData['values']);
    $this->set(compact('initialTankData'));
    //pr($initialTankData);
    //INFORME II
    $finalTankData=$this->Tank->getTankGraphData($saleDate,[],$unitId=UNIT_GALLONS);
    //pr($finalTankData);
    $this->set(compact('finalTankData'));
    
    $fuels=$this->Product->find('all',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'contain'=>[
        'DefaultPriceCurrency',
        'ProductPriceLog'=>[
          'conditions'=>[
            'price_datetime <'=>$saleDatePlusOne,
          ],
          'order'=>'price_datetime DESC',
          'limit'=>1,
        ],
        'StockItem',
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.movement_date >='=>$saleDate,
            'StockMovement.movement_date <'=>$saleDatePlusOne,
          ],
          'Shift',
        ],
        'Tank'=>[
          'limit'=>1,
          'TankMeasurement'=>[
            'conditions'=>[
              'TankMeasurement.measurement_date <'=>$saleDate,
            ],
            'order'=>'TankMeasurement.measurement_date DESC',
            'limit'=>1,
          ]
        ],
      ],  
      'order'=>'Product.product_order ASC',
    ]);
    $fuelPrices=[];
    foreach ($fuels as $fuel){
      $fuelId=$fuel['Product']['id'];
      $fuelName=$fuel['Product']['name'];
      $fuelAbbreviation=$fuel['Product']['abbreviation'];
      $fuelPrice=$fuel['ProductPriceLog'][0]['price'];
      $fuelCurrencyId=$fuel['ProductPriceLog'][0]['currency_id'];
      $fuelPrices[$fuelId]['name']=$fuelName;
      $fuelPrices[$fuelId]['abbreviation']=$fuelAbbreviation;
      $fuelPrices[$fuelId]['price']=$fuelPrice;
      $fuelPrices[$fuelId]['currency_id']=$fuelCurrencyId;
    }
    $this->set(compact('fuelPrices'));
    for ($f=0;$f<count($fuels);$f++){
      $fuelId=$fuels[$f]['Product']['id'];
      //echo "fuel id is ".$fuelId."<br/>";
      $tankArrayIndex=0;
      foreach ($initialTankData['fuelIds'] as $graphIndex=>$graphFuelId){
        if ($fuelId == $graphFuelId){
          $tankArrayIndex=$graphIndex;
        }
      }
      //echo "for fuel id ".$fuelId." the graph index is ".$tankArrayIndex."<br/>";  
      
      //$fuels[$f]['Product']['initial_existence']=$initialTankData['values'][$tankArrayIndex];
      $fuels[$f]['Product']['initial_existence']=$fuels[$f]['Tank'][0]['TankMeasurement'][0]['measurement_value'];

      $tankArrayIndex=0;
      foreach ($finalTankData['fuelIds'] as $graphIndex=>$graphFuelId){
        if ($fuelId == $graphFuelId){
          $tankArrayIndex=$graphIndex;
        }
      }
      //echo "for fuel id ".$fuelId." the graph index is ".$tankArrayIndex."<br/>";  
      $fuels[$f]['Product']['final_existence']=$finalTankData['values'][$tankArrayIndex];
      
      $fuels[$f]['Shift']=[];
      foreach ($shifts as $shiftId=>$shiftName){
        $fuels[$f]['Shift'][$shiftId]=0;
      }
      $entered=0;
      $exited=0;
      foreach($fuels[$f]['StockMovement'] as $stockMovement){
        if ($stockMovement['bool_input']){
          $entered+=$stockMovement['product_quantity'];
        }
        else {
          $exited+=$stockMovement['product_quantity'];
          $shiftId=$stockMovement['shift_id'];
          //if (array_key_exists($stockMovement['shift_id'],$fuels[$f]['Shift'])){
            $fuels[$f]['Shift'][$shiftId]+=$stockMovement['product_quantity'];
          //}
          //else {
          //  $fuels[$f]['Shift'][$shiftId]=$stockMovement['product_quantity'];
          //} 
        }
      }
      $fuels[$f]['Product']['entered']=$entered;
      $fuels[$f]['Product']['exited']=$exited;
      $fuels[$f]['Product']['final_existence']=$fuels[$f]['Product']['initial_existence']+$entered-$exited;

    }
    $this->set(compact('fuels'));
    //echo 'sale Date is'.$saleDate.'<br/>';
    //INFORME II
    $tankMeasurements=[];
    //pr($saleDate);
    $tanks=$this->Tank->find('all',[
      'conditions'=>[
        'Tank.enterprise_id'=>$enterpriseId
      ],
      'contain'=>[
        'TankMeasurement'=>[
          'conditions'=>['measurement_date'=>$saleDate]
        ],
      ],    
    ]);
    //pr($tanks);
    foreach ($tanks as $tank){
      if (!empty($tank['TankMeasurement'])){
        $tankMeasurements[$tank['Tank']['product_id']]=$tank['TankMeasurement'][0]['measurement_value'];
      }
      else {
        $tankMeasurements[$tank['Tank']['product_id']]=0;
      }
    }
    $this->set(compact('tankMeasurements'));

    
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC'
    ]);
        
    $operators=$this->Operator->find('list',[
			'conditions'=>['Operator.bool_active'=>true],
      'order'=>'Operator.name',
		]);
		$this->set(compact('operators'));
    
    $enterpriseIslandIds=$this->Island->find('list',[
      'fields'=>['Island.id'],
      'conditions'=>[
        'Island.enterprise_id'=>$enterpriseId,
        'Island.bool_active'=>true,
      ],
    ]);
    $allHoses=$this->Hose->find('all',[
      'conditions'=>[
        'Hose.bool_active'=>true,
        'Hose.island_id'=>$enterpriseIslandIds,
      ],
      'contain'=>[
          'HoseMeasurement'=>[
            'conditions'=>['measurement_date'=>$saleDate]
          ]
        ],
		]);
    $hoses=$this->Hose->find('list',[
      'conditions'=>['Hose.bool_active'=>true],
		]);
    
    //INFORME III
    foreach ($allHoses as $hose){
      if (!empty($hose['HoseMeasurement'])){
        $hoseMeasurements[$hose['Hose']['id']]=$hose['HoseMeasurement'][0]['measurement_value'];
      }
    }
    //pr($hoseMeasurements);
    $this->set(compact('hoseMeasurements'));
    $islands=$this->Island->find('list',[
      'conditions'=>['Island.bool_active'=>true],
		]);
    $islandsWithMeasurements=$this->Island->find('all',[
      'conditions'=>['Island.id'=>$enterpriseIslandIds,],
      'contain'=>[
        'Hose'=>[
          'Product',
          'HoseMeasurement'=>[
            'conditions'=>[
              'HoseMeasurement.measurement_date <'=>$saleDate
            ],
            'order'=>'HoseMeasurement.measurement_date DESC',
            'limit'=>1,
          ],
          'StockMovement'=>[
            'conditions'=>[
              'StockMovement.movement_date >='=>$saleDate,
              'StockMovement.movement_date <'=>$saleDatePlusOne,
              'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
            ]
          ],
        ],
      ],
      'order'=>'Island.name',
		]);
    //pr($islandsWithMeasurements);
    for ($i=0;$i<count($islandsWithMeasurements);$i++){
      $islandFuelTotal=0;
      for ($h=0;$h<count($islandsWithMeasurements[$i]['Hose']);$h++){
        $hoseFuelTotal=0;
        if (!empty($islandsWithMeasurements[$i]['Hose'][$h]['StockMovement'])){
          foreach ($islandsWithMeasurements[$i]['Hose'][$h]['StockMovement'] as $stockMovement){
            $hoseFuelTotal+=$stockMovement['product_quantity'];
          }
        }
        $islandsWithMeasurements[$i]['Hose'][$h]['fuel_total']=$hoseFuelTotal;
        $islandFuelTotal+=$hoseFuelTotal;
      }
      $islandsWithMeasurements[$i]['fuel_total']=$islandFuelTotal;
    }
    //pr($islandsWithMeasurements);
		$this->set(compact('islandsWithMeasurements'));
			
    $fuelStockMovementConditions=[
      'StockMovement.bool_input'=>false,
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.movement_date >='=>$saleDate,
      'StockMovement.movement_date <'=>$saleDatePlusOne,
    ];
    $fuelMovements=$this->StockMovement->find('all',[
      'conditions'=>$fuelStockMovementConditions,
      'order'=>['shift_id','operator_id'],
    ]);
    //pr($fuelMovements);
    $islands=$this->Island->find('list',[
      'conditions'=>['Island.bool_active'=>true],
      'order'=>'Island.name',
    ]);
    // Informe I 
    $fuelTotals=[];
    foreach ($fuelIds as $fuelId=>$fuelName){
      $fuelTotals[$fuelId]=0;
    }
    $saleShifts=[];
    foreach ($shifts as $shiftId=>$shiftName){
      $shiftArray=[];  
      
      $fuelStockMovementConditions=[
        'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
        'StockMovement.product_id'=>$fuelIds,
        'StockMovement.shift_id'=>$shiftId,
        'StockMovement.movement_date >='=>$saleDate,
        'StockMovement.movement_date <'=>$saleDatePlusOne,
      ];
      $fuelMovements=$this->StockMovement->find('all',[
        //'fields'=>['operator_id', 'hose_id'],
        'conditions'=>$fuelStockMovementConditions,
        'contain'=>[
          'Hose'=>[
            'HoseCounter'=>[
              'conditions'=>[
                'HoseCounter.counter_date >='=>$saleDate,
                'HoseCounter.counter_date <'=>$saleDatePlusOne,
                'HoseCounter.shift_id'=>$shiftId,
              ],
              'order'=>'HoseCounter.id DESC',
              'limit'=>1,
            ],
          ]
        ],
        'order'=>['hose_id'],
      ]);
      //pr($fuelMovements);
      $operatorArray=[];
      $shiftFuelTotals=$fuelTotals;
      foreach ($fuelMovements as $fuelMovement){
        $operatorId=$fuelMovement['StockMovement']['operator_id'];
        $hoseId=$fuelMovement['StockMovement']['hose_id']; 
        $islandId=$fuelMovement['Hose']['island_id']; 
        $fuelId=$fuelMovement['Hose']['product_id'];
        
        $shiftFuelTotals[$fuelId]+=$fuelMovement['StockMovement']['product_quantity'];
        
        if (!array_key_exists($operatorId,$operatorArray)){
          $operatorArray[$operatorId]=[];
          $operatorArray[$operatorId]['Operator']['name']=$operators[$operatorId];
          $operatorArray[$operatorId]['Operator']['total_gallons']=0;
          $operatorArray[$operatorId]['Operator']['total_price']=0;
        }
        
        $operatorArray[$operatorId]['Operator']['total_gallons']+=$fuelMovement['StockMovement']['product_quantity'];
        $operatorArray[$operatorId]['Operator']['total_price']+=$fuelMovement['StockMovement']['product_total_price'];
        
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['name']=$fuelMovement['Hose']['name'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['island_id']=$islandId;
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['island_name']=$islands[$islandId];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_id']=$fuelId;
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_name']=$fuelPrices[$fuelId]['name'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_abbreviation']=$fuelPrices[$fuelId]['abbreviation'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['final']=$fuelMovement['Hose']['HoseCounter'][0]['counter_value'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['initial']=round($fuelMovement['Hose']['HoseCounter'][0]['counter_value']-$fuelMovement['StockMovement']['product_quantity'],2);
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['quantity']=$fuelMovement['StockMovement']['product_quantity'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['unit_price']=$fuelPrices[$fuelId]['price'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['total_price']=$fuelMovement['StockMovement']['product_total_price'];
      }
      
      // check if all hoses are present
      $hoseCountForShift=0;
      $operatorCountForShift=0;
      $operatorIdsForShift=[];
      foreach ($operatorArray as $operatorId=>$operatorData){
        $hoseCountForShift+=count($operatorData['Hoses']);
        $operatorCountForShift++;
        array_push($operatorIdsForShift,$operatorId);
      }
      //pr($operatorArray);
      if (!empty($fuelStockMovements) && $hoseCountForShift < count($hoses)){
        //echo "hoseCountForShift is ".$hoseCountForShift."<br/>";  
        //echo "operatorCountForShift is ".$operatorCountForShift."<br/>";  
        //pr($operatorIdsForShift);
        foreach ($hoses as $hoseId=>$hoseName){
          $operatorId=0;
          if ($operatorCountForShift == 1){
            $operatorId=$operatorIdsForShift[0];
          }
          elseif ($operatorCountForShift == 2){
            //PENDING FOR DIFFERENT GASOLINERAS THIS SHOULD BE REVISED E.G. BY ADDING A HOSE ORDER FROM 1 TO X TO TABLE HOSES AND THEN SWITCHING ON HOSE ORDER
            switch ($hoseId){
              case '7':
              case '8':
              case '9':
              case '13':
              case '14':
              case '15':
              case '16':
              case '17':
              case '18':
                $operatorId=$operatorIdsForShift[1];
                break;
              case '1':
              case '2':
              case '3':
              case '4':
              case '5':
              case '6':
              case '10':
              case '11':
              case '12':
              default:
                $operatorId=$operatorIdsForShift[0];
            }
          }  
          //echo "operatorId is ".$operatorId."<br/>";  
          if (!array_key_exists($hoseId,$operatorArray[$operatorId]['Hoses'])){
            $hoseArray=[];
            
            $hose=$this->Hose->find('first',[
              'conditions'=>['Hose.id'=>$hoseId],
              'contain'=>[
                'HoseCounter'=>[
                  'conditions'=>[
                    'HoseCounter.counter_date >='=>$saleDate,
                    'HoseCounter.counter_date <'=>$saleDatePlusOne,
                    'HoseCounter.shift_id'=>$shiftId,
                  ],
                ]
              ],
            ]);
            if (!empty($hose)){
              $islandId=$hose['Hose']['island_id'];
              $fuelId=$hose['Hose']['product_id'];
              $hoseArray['name']=$hoseName;
              $hoseArray['island_id']=$islandId;
              $hoseArray['island_name']=$islands[$islandId];
              $hoseArray['fuel_id']=$fuelId;
              $hoseArray['fuel_name']=$fuelPrices[$fuelId]['name'];
              $hoseArray['fuel_abbreviation']=$fuelPrices[$fuelId]['abbreviation'];
              $hoseArray['final']=$hose['HoseCounter'][0]['counter_value'];
              $hoseArray['initial']=$hose['HoseCounter'][0]['counter_value'];
              $hoseArray['quantity']=0;
              $hoseArray['unit_price']=$fuelPrices[$fuelId]['price'];
              $hoseArray['total_price']=0;
          
              $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']=$hoseArray;              
            }
          }
        }
      }
      foreach ($operatorArray as $operatorId=>$operatorData){
        ksort($operatorArray[$operatorId]['Hoses']);
      }
      
      $shiftArray['Operators']=$operatorArray;
      
      
      $this->StockMovement->virtualFields['CalibrationQuantity']=0;
      $calibrationMovementConditions=[
        'StockMovement.stock_movement_type_id'=>MOVEMENT_ADJUSTMENT_CALIBRATION,
        //'StockMovement.product_id'=>$fuelIds,
        'StockMovement.movement_date >='=>$saleDate,
        'StockMovement.movement_date <'=>$saleDatePlusOne,
        'StockMovement.shift_id'=>$shiftId,
      ];
      $calibrationMovements=$this->StockMovement->find('all',[
        'fields'=>['StockMovement.shift_id','StockMovement.product_id','SUM(StockMovement.product_quantity) AS StockMovement__CalibrationQuantity'],
        'conditions'=>$calibrationMovementConditions,
        'group'=>['product_id'],
      ]);
      $calibrationArray=[];
      //pr($shiftFuelTotals);
      foreach ($calibrationMovements as $calibrationMovement){
        if ($calibrationMovement['StockMovement']['shift_id'] == $shiftId){
          $fuelId=$calibrationMovement['StockMovement']['product_id'];
          $fuelQuantity=round($shiftFuelTotals[$fuelId]*GALLONS_TO_LITERS,2);
          $calibrationQuantity=round($calibrationMovement['StockMovement']['CalibrationQuantity']*GALLONS_TO_LITERS,2);
          $fuelArray=[];
          $fuelArray['fuel_liters']=$fuelQuantity;
          $fuelArray['calibration_liters']=$calibrationQuantity;
          $fuelArray['net_liters']=($fuelQuantity-$calibrationQuantity);
          
          $calibrationArray[$fuelId]['Fuel']=$fuelArray;
        }
      }
      $shiftArray['Calibration']=$calibrationArray;
      /*
      foreach ($islands as $island){
        $islandId=$island['Island']['id'];
        $islandArray=[];
        $islandArray['operator_id']=0;
        $islandArray['operator_name']="";
        foreach ($island['Hose'] as $hose){
          $hoseId=$hose['id'];  
          $hoseArray=[];
          $hoseArray['quantity']=0;
          $hoseArray['price']=0;
          foreach ($fuelMovements as $fuelMovement){
            if ($fuelMovement['StockMovement']['shift_id'] == $shiftId && $fuelMovement['StockMovement']['hose_id'] == $hoseId){
              $hoseArray['quantity']=$fuelMovement['StockMovement']['product_quantity'];
              $hoseArray['price']=$fuelMovement['StockMovement']['product_total_price'];
              if ($islandArray['operator_id'] == 0 && $fuelMovement['StockMovement']['operator_id'] > 0){
                $islandArray['operator_id']=$fuelMovement['StockMovement']['operator_id'];
                $islandArray['operator_name']=$operators[$fuelMovement['StockMovement']['operator_id']];
                //pr($islandArray);
              }
            }
          }
         
          $islandArray['Hose'][$hoseId]=$hoseArray;
        }
        $shiftArray['Island'][$islandId]=$islandArray;
      }
      */
      $saleShifts['Shift'][$shiftId]=$shiftArray;
    }
    //pr($saleShifts);
    
    $lubricantStockMovementConditions=[
      'StockMovement.bool_input'=>false,
      'StockMovement.product_id !='=>$fuelIds,
      'StockMovement.movement_date >='=>$saleDate,
      'StockMovement.movement_date <'=>$saleDatePlusOne,
    ];
    $lubricantMovements=$this->StockMovement->find('all',[
      'conditions'=>$lubricantStockMovementConditions,
      'contain'=>['Product'],
    ]);
    $saleLubricants=[];
    foreach ($lubricantMovements as $lubricantMovement){
      $saleLubricantMovement=[];
      $saleLubricantMovement['lubricant_id']=$lubricantMovement['StockMovement'] ['product_id'];
      $saleLubricantMovement['lubricant_name']=$lubricantMovement['Product'] ['name'];
      $saleLubricantMovement['lubricant_quantity']=$lubricantMovement['StockMovement'] ['product_quantity'];
      $saleLubricantMovement['lubricant_unit_price']=$lubricantMovement['StockMovement'] ['product_unit_price'];
      $saleLubricantMovement['lubricant_total_price']=$lubricantMovement['StockMovement'] ['product_total_price'];
      $saleLubricants['Lubricant'][]=$saleLubricantMovement;
    }
    //pr ($saleLubricants);
     
    $this->set(compact('saleDate'));
		$this->set(compact('enterpriseId'));
		$this->set(compact('saleShifts'));
    $this->set(compact('saleLubricants'));
		
		$islands=$this->Island->find('all',[
      'conditions'=>['Island.bool_active'=>true],
      'contain'=>[
        'Hose'=>[
          'HoseCounter'=>[
            'conditions'=>['counter_date <' => $saleDate],
            'order'=>'HoseCounter.id DESC',
            'limit'=>1,
          ],
          'Product',
        ],
      ],
      'order'=>'Island.name',
		]);
		$this->set(compact('islands'));

		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','enterprise_id'],
          'conditions'=>['StockItem.bool_active'=>true,],
				],
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		$products = [];
		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockItem){
					if ($stockItem['remaining_quantity']>0){
						if (!empty($enterpriseId)){
							if ($stockItem['enterprise_id']==$enterpriseId){
								$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
							}
						}
						else {
							$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
						}		
					}
				}
			}
		}
		$this->Product->recursive=-1;
		
		$this->loadModel('ExchangeRate');
		$saleExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDate);
		$exchangeRateOrder=$saleExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    
    $lubricantPrices=$this->Product->find('list',[
      'fields'=>['Product.id','Product.default_price'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
    ]);
    $this->set(compact('lubricantPrices'));
    
    $lubricants=$this->Product->find('list',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.name ASC',
      'contain'=>['DefaultPriceCurrency'],  
    ]);
    $this->set(compact('allLubricants','lubricants'));
    
  	/*
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		*/
    
	}
		
  public function pdfInformeDiario($saleDateAsString = '') {
		$this->loadModel('Order');
    $this->loadModel('Product');   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
			
		$this->loadModel('Hose');
    $this->loadModel('HoseCounter');
    $this->loadModel('Island');
     $this->loadModel('Operator');
		$this->loadModel('Shift');
    
    $this->loadModel('Enterprise');
    
    $this->loadModel('Tank');
    $this->loadModel('TankMeasurement');
    
    $this->loadModel('HoseMeasurement');
    
    $this->Order->recursive=-1;
		$this->Product->recursive=-1;
    $this->StockMovement->recursive=-1;
		
    $enterpriseId=ENTERPRISE_LAS_PALMAS;
        
		if (!empty($saleDateAsString)){
      $saleDate=date( "Y-m-d", strtotime($saleDateAsString));
      //$saleDateAsString=$saleDateAsString;
    }
		else if (!empty($_SESSION['saleDate']) && !empty($_SESSION['endDate'])){
			$saleDateAsString=$saleDate=$_SESSION['saleDate'];
		}
		else {
			$saleDateAsString=$saleDate = date("Y-m-d");
		}
    $_SESSION['saleDate']=$saleDate;
    $saleDateMinusOne=date( "Y-m-d", strtotime($saleDateAsString."-1 days"));
    $saleDatePlusOne=date( "Y-m-d", strtotime($saleDateAsString."+1 days"));
    $this->set(compact('saleDate'));
    $this->set(compact('enterpriseId'));
    
    $shifts=$this->Shift->find('list',[
      'conditions'=>['Shift.bool_active'=>true],
			'order'=>'Shift.name',
		]);
		$this->set(compact('shifts'));
    
    //INFORME II
    $initialTankData=$this->Tank->getTankGraphData($saleDateMinusOne,[],$unitId=UNIT_GALLONS);
    //pr($initialTankData['values']);
    $this->set(compact('initialTankData'));
    //pr($initialTankData);
    //INFORME II
    $finalTankData=$this->Tank->getTankGraphData($saleDate,[],$unitId=UNIT_GALLONS);
    //pr($finalTankData);
    $this->set(compact('finalTankData'));
    
    $fuels=$this->Product->find('all',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'contain'=>[
        'DefaultPriceCurrency',
        'ProductPriceLog'=>[
          'conditions'=>[
            'price_datetime <'=>$saleDatePlusOne,
          ],
          'order'=>'price_datetime DESC',
          'limit'=>1,
        ],
        'StockItem',
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.movement_date >='=>$saleDate,
            'StockMovement.movement_date <'=>$saleDatePlusOne,
          ],
          'Shift',
        ],
        'Tank'=>[
          'limit'=>1,
          'TankMeasurement'=>[
            'conditions'=>[
              'TankMeasurement.measurement_date <'=>$saleDate,
            ],
            'order'=>'TankMeasurement.measurement_date DESC',
            'limit'=>1,
          ]
        ],
      ],  
      'order'=>'Product.product_order ASC',
    ]);
    $fuelPrices=[];
    foreach ($fuels as $fuel){
      $fuelId=$fuel['Product']['id'];
      $fuelName=$fuel['Product']['name'];
      $fuelAbbreviation=$fuel['Product']['abbreviation'];
      $fuelPrice=$fuel['ProductPriceLog'][0]['price'];
      $fuelCurrencyId=$fuel['ProductPriceLog'][0]['currency_id'];
      $fuelPrices[$fuelId]['name']=$fuelName;
      $fuelPrices[$fuelId]['abbreviation']=$fuelAbbreviation;
      $fuelPrices[$fuelId]['price']=$fuelPrice;
      $fuelPrices[$fuelId]['currency_id']=$fuelCurrencyId;
    }
    $this->set(compact('fuelPrices'));
    for ($f=0;$f<count($fuels);$f++){
      $fuelId=$fuels[$f]['Product']['id'];
      //echo "fuel id is ".$fuelId."<br/>";
      $tankArrayIndex=0;
      foreach ($initialTankData['fuelIds'] as $graphIndex=>$graphFuelId){
        if ($fuelId == $graphFuelId){
          $tankArrayIndex=$graphIndex;
        }
      }
      //echo "for fuel id ".$fuelId." the graph index is ".$tankArrayIndex."<br/>";  
      
      //$fuels[$f]['Product']['initial_existence']=$initialTankData['values'][$tankArrayIndex];
      $fuels[$f]['Product']['initial_existence']=$fuels[$f]['Tank'][0]['TankMeasurement'][0]['measurement_value'];

      $tankArrayIndex=0;
      foreach ($finalTankData['fuelIds'] as $graphIndex=>$graphFuelId){
        if ($fuelId == $graphFuelId){
          $tankArrayIndex=$graphIndex;
        }
      }
      //echo "for fuel id ".$fuelId." the graph index is ".$tankArrayIndex."<br/>";  
      $fuels[$f]['Product']['final_existence']=$finalTankData['values'][$tankArrayIndex];
      
      $fuels[$f]['Shift']=[];
      foreach ($shifts as $shiftId=>$shiftName){
        $fuels[$f]['Shift'][$shiftId]=0;
      }
      $entered=0;
      $exited=0;
      foreach($fuels[$f]['StockMovement'] as $stockMovement){
        if ($stockMovement['bool_input']){
          $entered+=$stockMovement['product_quantity'];
        }
        else {
          $exited+=$stockMovement['product_quantity'];
          $shiftId=$stockMovement['shift_id'];
          //if (array_key_exists($stockMovement['shift_id'],$fuels[$f]['Shift'])){
            $fuels[$f]['Shift'][$shiftId]+=$stockMovement['product_quantity'];
          //}
          //else {
          //  $fuels[$f]['Shift'][$shiftId]=$stockMovement['product_quantity'];
          //} 
        }
      }
      $fuels[$f]['Product']['entered']=$entered;
      $fuels[$f]['Product']['exited']=$exited;
      $fuels[$f]['Product']['final_existence']=$fuels[$f]['Product']['initial_existence']+$entered-$exited;

    }
    $this->set(compact('fuels'));
    
    //INFORME II
    $tankMeasurements=[];
    //pr($saleDate);
    $tanks=$this->Tank->find('all',[
      'conditions'=>[
        'Tank.enterprise_id'=>$enterpriseId
      ],
      'contain'=>[
        'TankMeasurement'=>[
          'conditions'=>['measurement_date'=>$saleDate]
        ],
      ],    
    ]);
    //pr($tanks);
    foreach ($tanks as $tank){
      if (!empty($tank['TankMeasurement'])){
        $tankMeasurements[$tank['Tank']['product_id']]=$tank['TankMeasurement'][0]['measurement_value'];
      }
      else {
        $tankMeasurements[$tank['Tank']['product_id']]=0;
      }
    }
    $this->set(compact('tankMeasurements'));

    
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC'
    ]);
        
    $operators=$this->Operator->find('list',[
			'conditions'=>['Operator.bool_active'=>true],
      'order'=>'Operator.name',
		]);
		$this->set(compact('operators'));
    
    $enterpriseIslandIds=$this->Island->find('list',[
      'fields'=>['Island.id'],
      'conditions'=>[
        'Island.enterprise_id'=>$enterpriseId,
        'Island.bool_active'=>true,
      ],
    ]);
    $allHoses=$this->Hose->find('all',[
      'conditions'=>[
        'Hose.bool_active'=>true,
        'Hose.island_id'=>$enterpriseIslandIds,
      ],
      'contain'=>[
          'HoseMeasurement'=>[
            'conditions'=>['measurement_date'=>$saleDate]
          ]
        ],
		]);
    $hoses=$this->Hose->find('list',[
      'conditions'=>['Hose.bool_active'=>true],
		]);
    
    //INFORME III
    foreach ($allHoses as $hose){
      if (!empty($hose['HoseMeasurement'])){
        $hoseMeasurements[$hose['Hose']['id']]=$hose['HoseMeasurement'][0]['measurement_value'];
      }
    }
    //pr($hoseMeasurements);
    $this->set(compact('hoseMeasurements'));
    $islands=$this->Island->find('list',[
      'conditions'=>['Island.bool_active'=>true],
		]);
    $islandsWithMeasurements=$this->Island->find('all',[
      'conditions'=>['Island.id'=>$enterpriseIslandIds,],
      'contain'=>[
        'Hose'=>[
          'Product',
          'HoseMeasurement'=>[
            'conditions'=>[
              'HoseMeasurement.measurement_date <'=>$saleDate
            ],
            'order'=>'HoseMeasurement.measurement_date DESC',
            'limit'=>1,
          ],
          'StockMovement'=>[
            'conditions'=>[
              'StockMovement.movement_date >='=>$saleDate,
              'StockMovement.movement_date <'=>$saleDatePlusOne,
              'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
            ]
          ],
        ],
      ],
      'order'=>'Island.name',
		]);
    //pr($islandsWithMeasurements);
    for ($i=0;$i<count($islandsWithMeasurements);$i++){
      $islandFuelTotal=0;
      for ($h=0;$h<count($islandsWithMeasurements[$i]['Hose']);$h++){
        $hoseFuelTotal=0;
        if (!empty($islandsWithMeasurements[$i]['Hose'][$h]['StockMovement'])){
          foreach ($islandsWithMeasurements[$i]['Hose'][$h]['StockMovement'] as $stockMovement){
            $hoseFuelTotal+=$stockMovement['product_quantity'];
          }
        }
        $islandsWithMeasurements[$i]['Hose'][$h]['fuel_total']=$hoseFuelTotal;
        $islandFuelTotal+=$hoseFuelTotal;
      }
      $islandsWithMeasurements[$i]['fuel_total']=$islandFuelTotal;
    }
    //pr($islandsWithMeasurements);
		$this->set(compact('islandsWithMeasurements'));
    
    
		$enterprises=$this->Enterprise->find('list',[
			'conditions'=>['Enterprise.bool_active'=>true],
      'order'=>'Enterprise.company_name',
		]);
		$this->set(compact('enterprises'));
			
    $fuelStockMovementConditions=[
      'StockMovement.bool_input'=>false,
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.movement_date >='=>$saleDate,
      'StockMovement.movement_date <'=>$saleDatePlusOne,
    ];
    $fuelMovements=$this->StockMovement->find('all',[
      'conditions'=>$fuelStockMovementConditions,
      'order'=>['shift_id','operator_id'],
    ]);
    //pr($fuelMovements);
    $islands=$this->Island->find('list',[
      'conditions'=>['Island.bool_active'=>true],
      'order'=>'Island.name',
    ]);
    // Informe I 
    $fuelTotals=[];
    foreach ($fuelIds as $fuelId=>$fuelName){
      $fuelTotals[$fuelId]=0;
    }
    $saleShifts=[];
    foreach ($shifts as $shiftId=>$shiftName){
      $shiftArray=[];  
      
      $fuelStockMovementConditions=[
        'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
        'StockMovement.product_id'=>$fuelIds,
        'StockMovement.shift_id'=>$shiftId,
        'StockMovement.movement_date >='=>$saleDate,
        'StockMovement.movement_date <'=>$saleDatePlusOne,
      ];
      $fuelMovements=$this->StockMovement->find('all',[
        //'fields'=>['operator_id', 'hose_id'],
        'conditions'=>$fuelStockMovementConditions,
        'contain'=>[
          'Hose'=>[
            'HoseCounter'=>[
              'conditions'=>[
                'HoseCounter.counter_date >='=>$saleDate,
                'HoseCounter.counter_date <'=>$saleDatePlusOne,
                'HoseCounter.shift_id'=>$shiftId,
              ],
              'order'=>'HoseCounter.id DESC',
              'limit'=>1,
            ],
          ]
        ],
        'order'=>['hose_id'],
      ]);
      //pr($fuelMovements);
      $operatorArray=[];
      $shiftFuelTotals=$fuelTotals;
      foreach ($fuelMovements as $fuelMovement){
        $operatorId=$fuelMovement['StockMovement']['operator_id'];
        $hoseId=$fuelMovement['StockMovement']['hose_id']; 
        $islandId=$fuelMovement['Hose']['island_id']; 
        $fuelId=$fuelMovement['Hose']['product_id'];
        
        $shiftFuelTotals[$fuelId]+=$fuelMovement['StockMovement']['product_quantity'];
        
        if (!array_key_exists($operatorId,$operatorArray)){
          $operatorArray[$operatorId]=[];
          $operatorArray[$operatorId]['Operator']['name']=$operators[$operatorId];
          $operatorArray[$operatorId]['Operator']['total_gallons']=0;
          $operatorArray[$operatorId]['Operator']['total_price']=0;
        }
        
        $operatorArray[$operatorId]['Operator']['total_gallons']+=$fuelMovement['StockMovement']['product_quantity'];
        $operatorArray[$operatorId]['Operator']['total_price']+=$fuelMovement['StockMovement']['product_total_price'];
        
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['name']=$fuelMovement['Hose']['name'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['island_id']=$islandId;
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['island_name']=$islands[$islandId];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_id']=$fuelId;
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_name']=$fuelPrices[$fuelId]['name'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['fuel_abbreviation']=$fuelPrices[$fuelId]['abbreviation'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['final']=$fuelMovement['Hose']['HoseCounter'][0]['counter_value'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['initial']=round($fuelMovement['Hose']['HoseCounter'][0]['counter_value']-$fuelMovement['StockMovement']['product_quantity'],2);
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['quantity']=$fuelMovement['StockMovement']['product_quantity'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['unit_price']=$fuelPrices[$fuelId]['price'];
        $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']['total_price']=$fuelMovement['StockMovement']['product_total_price'];
      }
      
      // check if all hoses are present
      $hoseCountForShift=0;
      $operatorCountForShift=0;
      $operatorIdsForShift=[];
      foreach ($operatorArray as $operatorId=>$operatorData){
        $hoseCountForShift+=count($operatorData['Hoses']);
        $operatorCountForShift++;
        array_push($operatorIdsForShift,$operatorId);
      }
      //pr($operatorArray);
      if (!empty($fuelStockMovements) && $hoseCountForShift < count($hoses)){
        //echo "hoseCountForShift is ".$hoseCountForShift."<br/>";  
        //echo "operatorCountForShift is ".$operatorCountForShift."<br/>";  
        //pr($operatorIdsForShift);
        foreach ($hoses as $hoseId=>$hoseName){
          $operatorId=0;
          if ($operatorCountForShift == 1){
            $operatorId=$operatorIdsForShift[0];
          }
          elseif ($operatorCountForShift == 2){
            //PENDING FOR DIFFERENT GASOLINERAS THIS SHOULD BE REVISED E.G. BY ADDING A HOSE ORDER FROM 1 TO X TO TABLE HOSES AND THEN SWITCHING ON HOSE ORDER
            switch ($hoseId){
              case '7':
              case '8':
              case '9':
              case '13':
              case '14':
              case '15':
              case '16':
              case '17':
              case '18':
                $operatorId=$operatorIdsForShift[1];
                break;
              case '1':
              case '2':
              case '3':
              case '4':
              case '5':
              case '6':
              case '10':
              case '11':
              case '12':
              default:
                $operatorId=$operatorIdsForShift[0];
            }
          }  
          //echo "operatorId is ".$operatorId."<br/>";  
          if (!array_key_exists($hoseId,$operatorArray[$operatorId]['Hoses'])){
            $hoseArray=[];
            
            $hose=$this->Hose->find('first',[
              'conditions'=>['Hose.id'=>$hoseId],
              'contain'=>[
                'HoseCounter'=>[
                  'conditions'=>[
                    'HoseCounter.counter_date >='=>$saleDate,
                    'HoseCounter.counter_date <'=>$saleDatePlusOne,
                    'HoseCounter.shift_id'=>$shiftId,
                  ],
                ]
              ],
            ]);
            if (!empty($hose)){
              $islandId=$hose['Hose']['island_id'];
              $fuelId=$hose['Hose']['product_id'];
              $hoseArray['name']=$hoseName;
              $hoseArray['island_id']=$islandId;
              $hoseArray['island_name']=$islands[$islandId];
              $hoseArray['fuel_id']=$fuelId;
              $hoseArray['fuel_name']=$fuelPrices[$fuelId]['name'];
              $hoseArray['fuel_abbreviation']=$fuelPrices[$fuelId]['abbreviation'];
              $hoseArray['final']=$hose['HoseCounter'][0]['counter_value'];
              $hoseArray['initial']=$hose['HoseCounter'][0]['counter_value'];
              $hoseArray['quantity']=0;
              $hoseArray['unit_price']=$fuelPrices[$fuelId]['price'];
              $hoseArray['total_price']=0;
          
              $operatorArray[$operatorId]['Hoses'][$hoseId]['Hose']=$hoseArray;              
            }
          }
        }
      }
      foreach ($operatorArray as $operatorId=>$operatorData){
        ksort($operatorArray[$operatorId]['Hoses']);
      }
      
      $shiftArray['Operators']=$operatorArray;
      
      
      $this->StockMovement->virtualFields['CalibrationQuantity']=0;
      $calibrationMovementConditions=[
        'StockMovement.stock_movement_type_id'=>MOVEMENT_ADJUSTMENT_CALIBRATION,
        //'StockMovement.product_id'=>$fuelIds,
        'StockMovement.movement_date >='=>$saleDate,
        'StockMovement.movement_date <'=>$saleDatePlusOne,
        'StockMovement.shift_id'=>$shiftId,
      ];
      $calibrationMovements=$this->StockMovement->find('all',[
        'fields'=>['StockMovement.shift_id','StockMovement.product_id','SUM(StockMovement.product_quantity) AS StockMovement__CalibrationQuantity'],
        'conditions'=>$calibrationMovementConditions,
        'group'=>['product_id'],
      ]);
      $calibrationArray=[];
      //pr($shiftFuelTotals);
      foreach ($calibrationMovements as $calibrationMovement){
        if ($calibrationMovement['StockMovement']['shift_id'] == $shiftId){
          $fuelId=$calibrationMovement['StockMovement']['product_id'];
          $fuelQuantity=round($shiftFuelTotals[$fuelId]*GALLONS_TO_LITERS,2);
          $calibrationQuantity=round($calibrationMovement['StockMovement']['CalibrationQuantity']*GALLONS_TO_LITERS,2);
          $fuelArray=[];
          $fuelArray['fuel_liters']=$fuelQuantity;
          $fuelArray['calibration_liters']=$calibrationQuantity;
          $fuelArray['net_liters']=($fuelQuantity-$calibrationQuantity);
          
          $calibrationArray[$fuelId]['Fuel']=$fuelArray;
        }
      }
      $shiftArray['Calibration']=$calibrationArray;

      $saleShifts['Shift'][$shiftId]=$shiftArray;
    }
    //pr($saleShifts);
    
    $lubricantStockMovementConditions=[
      'StockMovement.bool_input'=>false,
      'StockMovement.product_id !='=>$fuelIds,
      'StockMovement.movement_date >='=>$saleDate,
      'StockMovement.movement_date <'=>$saleDatePlusOne,
    ];
    $lubricantMovements=$this->StockMovement->find('all',[
      'conditions'=>$lubricantStockMovementConditions,
      'contain'=>['Product'],
    ]);
    $saleLubricants=[];
    foreach ($lubricantMovements as $lubricantMovement){
      $saleLubricantMovement=[];
      $saleLubricantMovement['lubricant_id']=$lubricantMovement['StockMovement'] ['product_id'];
      $saleLubricantMovement['lubricant_name']=$lubricantMovement['Product'] ['name'];
      $saleLubricantMovement['lubricant_quantity']=$lubricantMovement['StockMovement'] ['product_quantity'];
      $saleLubricantMovement['lubricant_unit_price']=$lubricantMovement['StockMovement'] ['product_unit_price'];
      $saleLubricantMovement['lubricant_total_price']=$lubricantMovement['StockMovement'] ['product_total_price'];
      $saleLubricants['Lubricant'][]=$saleLubricantMovement;
    }
    //pr ($saleLubricants);
     
    $this->set(compact('saleDate'));
		$this->set(compact('enterpriseId'));
		$this->set(compact('saleShifts'));
    $this->set(compact('saleLubricants'));
		
		$islands=$this->Island->find('all',[
      'conditions'=>['Island.bool_active'=>true],
      'contain'=>[
        'Hose'=>[
          'HoseCounter'=>[
            'conditions'=>['counter_date <' => $saleDate],
            'order'=>'HoseCounter.id DESC',
            'limit'=>1,
          ],
          'Product',
        ],
      ],
      'order'=>'Island.name',
		]);
		$this->set(compact('islands'));

		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','enterprise_id'],
          'conditions'=>['StockItem.bool_active'=>true,],
				],
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		$products = [];
		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockItem){
					if ($stockItem['remaining_quantity']>0){
						if (!empty($enterpriseId)){
							if ($stockItem['enterprise_id']==$enterpriseId){
								$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
							}
						}
						else {
							$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
						}		
					}
				}
			}
		}
		$this->Product->recursive=-1;
		
		$this->loadModel('ExchangeRate');
		$saleExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDate);
		$exchangeRateOrder=$saleExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    
    $lubricantPrices=$this->Product->find('list',[
      'fields'=>['Product.id','Product.default_price'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
    ]);
    $this->set(compact('lubricantPrices'));
    
    $lubricants=$this->Product->find('list',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.name ASC',
      'contain'=>['DefaultPriceCurrency'],  
    ]);
    $this->set(compact('allLubricants','lubricants'));
    
	}
	
  public function resumenAjustesInventario(){
    $this->StockMovement->recursive = -1;
    $stockMovementTypeId=0;
		
		//$currencyId=CURRENCY_USD;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $stockMovementTypeId=$this->request->data['Report']['stock_movement_type_id'];
			//$userId=$this->request->data['Report']['user_id'];
			//$currencyId=$this->request->data['Report']['currency_id'];
		}
		
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			//if ($this->Session->check('currencyId')){
			//	$currencyId=$_SESSION['currencyId'];
			//}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		//$_SESSION['currencyId']=$currencyId;
		//$_SESSION['userId']=$userId;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('stockMovementTypeId'));
		
    $stockMovementTypes=$this->StockMovement->StockMovementType->find('list',[
      'conditions'=>['id'=>[MOVEMENT_ADJUSTMENT_GENERAL,MOVEMENT_ADJUSTMENT_CALIBRATION,MOVEMENT_ADJUSTMENT_MEASURE]],
    ]);
    $this->set(compact('stockMovementTypes'));
		
    $conditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
		];
    
    
    $adjustments=[];
    foreach ($stockMovementTypes as $adjustmentStockMovementTypeId => $stockMovementTypeName){
      if($adjustmentStockMovementTypeId === $stockMovementTypeId || $stockMovementTypeId === 0){
        $adjustmentConditions=$conditions;
        $adjustmentConditions['StockMovement.stock_movement_type_id']=$adjustmentStockMovementTypeId;
        $adjustmentCount=	$this->StockMovement->find('count', [
          'fields'=>['StockMovement.id'],
          'conditions' => $adjustmentConditions,
        ]);
        $adjustmentMovements = $this->StockMovement->find('all',[
          'conditions' => $adjustmentConditions,
          'contain'=>[				
            'Product',
            'StockItem',
          ],
          'order'=>'movement_date DESC,adjustment_code DESC',
          'limit'=>($adjustmentCount!=0?$adjustmentCount:1),
        ]);
        //pr($adjustmentMovements);
        $adjustments[$adjustmentStockMovementTypeId]['Adjustments']=$adjustmentMovements;
      }
    }
    $this->set(compact('adjustments'));
    
		//$this->loadModel('Currency');
		//$currencies=$this->Currency->find('list');
		//$this->set(compact('currencies'));
		
    //$aco_name="PurchaseOrders/editar";		
		//$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		//$this->set(compact('bool_edit_permission'));
  }
  
  public function guardarResumenAjustesInventario(){
    $exportData=$_SESSION['ajustesInventario'];
		$this->set(compact('exportData'));
  }
  
  public function registrarAjusteTanque() {
    $this->loadModel('TankMeasurement');
    $this->loadModel('StockItem');
    $this->loadModel('Enterprise');
    $this->loadModel('Product');
    
    //$currentDate= new DateTime(date('Y-m-d'));
    //$currentDateString=$currentDate->format('Y-m-d');
    
    $enterpriseId=ENTERPRISE_LAS_PALMAS;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
		
    $enterprises=$this->Enterprise->find('list',[
			'order'=>'Enterprise.company_name',
		]);
		$this->set(compact('enterprises'));
    
    $products=$this->Product->find('list',[
      'fields'=>['Product.id','Product.abbreviation',],
      'conditions'=>[
        'product_type_id'=>PRODUCT_TYPE_FUELS,
        'Product.bool_active'=>true,
      ],
			'order'=>'Product.product_order ASC',
		]);
		$this->set(compact('products'));
    
    if ($this->request->is('post')) {
      //pr($this->request->data);
      if (!empty($this->request->data['submitEnterprise'])){
        $enterpriseId=$this->request->data['Adjustment']['enterprise_id'];
      }
      else {
        $adjustmentDateArray=$this->request->data['Adjustment']['adjustment_date'];
        $adjustmentDateAsString=$adjustmentDateArray['year'].'-'.$adjustmentDateArray['month'].'-'.$adjustmentDateArray['day'];
        $adjustmentDate=date( "Y-m-d", strtotime($adjustmentDateAsString));
        $adjustmentDateTime=new DateTime($adjustmentDateAsString);
        $comment=  $this->request->data['Adjustment']['comment'];
        
        $allFuels=$this->Product->find('all',[
          'conditions'=>[  
            'Product.product_type_id'=>PRODUCT_TYPE_FUELS,
            'Product.bool_active'=>true,
          ],
          'contain'=>[
            'StockItem',
          ],  
          'order'=>'Product.product_order ASC',
        ]);
        $fuelStockItems=[];
        foreach ($allFuels as $fuel){
          $fuelStockItems[$fuel['Product']['id']]=$fuel['StockItem'][0]['id'];
        }
        //pr($fuelStockItems);
      
        $adjustmentCodes=[];
        $offSet=0;
        
        $datasource=$this->StockMovement->getDataSource();
        $datasource->begin();
        try {
          foreach ($this->request->data['StockMovement'] as $stockMovement){
            $userName=$this->Session->read('User.username');
            $adjustmentCode=$this->StockMovement->getAdjustmentCode($userName,$offSet);
            $offSet++;
            array_push($adjustmentCodes,$adjustmentCode);
            $message="Ajuste de inventario en base a medida de vara de día ".$adjustmentDateTime->format('d-m-Y')." en gasolinera ".$enterprises[$enterpriseId].": se ".($stockMovement['bool_input']?"aumentó":"diminuyó")." el inventario de combustible ".$products[$stockMovement['product_id']]." con una cantidad ".$stockMovement['product_quantity']." desde ".$stockMovement['inventory_value']." galones hasta ".$stockMovement['measurement_value']." galones";
        
            $adjustmentStockMovementData=[];
            // only the IN movement; out movements are part of the movements per house that were registered
            $adjustmentStockMovementData['movement_date']=$adjustmentDateTime->format('Y-m-d H:i:s');
            $adjustmentStockMovementData['bool_input']=$stockMovement['bool_input'];
            $adjustmentStockMovementData['stock_movement_type_id']=MOVEMENT_ADJUSTMENT_MEASURE;
            $adjustmentStockMovementData['name']="Ajuste medida ".$products[$stockMovement['product_id']]." del día ".$adjustmentDateTime->format('d-m-Y');
            $adjustmentStockMovementData['description']=$message;
            //$adjustmentStockMovementData['hose_id']=0;
            //$adjustmentStockMovementData['operator_id']=0;
            //$adjustmentStockMovementData['shift_id']=$shiftId;
            //$adjustmentStockMovementData['order_id']=$orderId;
            $adjustmentStockMovementData['stock_item_id']=$fuelStockItems[$stockMovement['product_id']];
            $adjustmentStockMovementData['product_id']=$stockMovement['product_id'];
            $adjustmentStockMovementData['product_quantity']=$stockMovement['product_quantity'];
            $adjustmentStockMovementData['product_unit_price']=0;
            $adjustmentStockMovementData['product_total_price']=0;
            $adjustmentStockMovementData['adjustment_code']=$adjustmentCode;
            $adjustmentStockMovementData['comment']=$comment;
            //pr($adjustmentStockMovementData);
            $this->StockMovement->create();
            if (!$this->StockMovement->save($adjustmentStockMovementData)) {
              echo "problema al guardar el ajuste para ".$products[$stockMovement['product_id']];
              pr($this->validateErrors($this->StockMovement));
              throw new Exception();
            }
            
            $stockItem=$this->StockItem->find('first',[
              'conditions'=>[
                'StockItem.id'=>$fuelStockItems[$stockMovement['product_id']]
              ],
            ]);
            if (!empty($stockItem)){
              $quantityRemaining=$stockItem['StockItem']['remaining_quantity'];
              if ($stockMovement['bool_input']){
                $quantityRemaining+=$stockMovement['product_quantity'];
              }
              else {
                $quantityRemaining-=$stockMovement['product_quantity'];
              }
              $stockItemData=[];
              $stockItemData['id']=$fuelStockItems[$stockMovement['product_id']];
              $stockItemData['remaining_quantity']=$quantityRemaining;
              $this->StockItem->id=$fuelStockItems[$stockMovement['product_id']];
              //pr($stockItemData);
              if (!$this->StockItem->save($stockItemData)) {
                echo "problema al guardar el lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              } 
            }
            
            $this->recreateStockItemLogs($fuelStockItems[$stockMovement['product_id']],$adjustmentDateAsString);
            $this->recordUserActivity($this->Session->read('User.username'),$message);
          }
          $datasource->commit();
        
          // SAVE THE USERLOGS
          $this->recordUserAction($this->StockMovement->id,"registrarAjusteTanque",null);
          // PENDING ADJUSTMENTCODES
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron ajustes de tanque con códigos ".implode(',',$adjustmentCodes));
          $this->Session->setFlash('Se guardaron los ajustes  de inventario de combustibles en base a la medida de vara del '.($adjustmentDateTime->format('d-m-Y')),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumenAjustesInventario']);      
        } 
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);
          $this->Session->setFlash(__('No se podía registrar el ajuste de tanque en el inventario.'), 'default',['class' => 'error-message']);
          
        }
      }
    }  
  
    $this->set(compact('enterpriseId'));
    
    $inventoryMeasurementStatus=$this->TankMeasurement->getCurrentInventoryTankMeasurementStatus($enterpriseId);
    //pr($inventoryMeasurementStatus);   
        
    $inventoryFuels=$this->StockItem->getInventoryItems(PRODUCT_TYPE_FUELS,$inventoryMeasurementStatus['sunday_measurement_date'],$enterpriseId,false);
    foreach ($inventoryFuels as $fuel){
      //pr($fuel);
      $fuelId=$fuel['Product']['id'];
      $inventoryMeasurementStatus['fuel_values'][$fuelId]['inventory_value']=round($fuel[0]['Remaining'],3);
    }
    //pr($inventoryFuels);

    $this->set(compact('inventoryFuels'));
    
    //pr($inventoryMeasurementStatus);   
    $this->set(compact('inventoryMeasurementStatus'));

    
    
    $movementDirections=[
      0 => 'Diminuir Inventario',
      1 => 'Aumentar Inventario',
    ];
    $this->set(compact('movementDirections'));
  }
  
  public function registrarAjuste($enterpriseId=0) {
    $this->loadModel('TankMeasurement');
    $this->loadModel('StockItem');
    $this->loadModel('Enterprise');
    $this->loadModel('Product');
    
    //$currentDate= new DateTime(date('Y-m-d'));
    //$currentDateString=$currentDate->format('Y-m-d');
    
    if($enterpriseId == 0) {
      $enterpriseId=ENTERPRISE_LAS_PALMAS;  
    }
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
		
    $enterprises=$this->Enterprise->find('list',[
			'order'=>'Enterprise.company_name',
		]);
		$this->set(compact('enterprises'));
    
    $products=$this->Product->find('list',[
      'fields'=>['Product.id','Product.abbreviation',],
      'conditions'=>[
        'product_type_id'=>PRODUCT_TYPE_FUELS,
        'Product.bool_active'=>true,
      ],
			'order'=>'Product.product_order ASC',
		]);
		$this->set(compact('products'));
    
    if ($this->request->is('post')) {
			$adjustmentDateArray=$this->request->data['Adjustment']['adjustment_date'];
			$adjustmentDateAsString=$adjustmentDateArray['year'].'-'.$adjustmentDateArray['month'].'-'.$adjustmentDateArray['day'];
			$adjustmentDate=date( "Y-m-d", strtotime($adjustmentDateAsString));
		}
		else if (!empty($_SESSION['adjustmentDate'])){
			$adjustmentDateAsString=$adjustmentDate=$_SESSION['adjustmentDate'];
		}
		else {
			$adjustmentDateAsString=$adjustmentDate = date("Y-m-d");
		}
		$adjustmentDatePlusOne=date("Y-m-d",strtotime($adjustmentDate."+1 days"));
		$_SESSION['adjustmentDate']=$adjustmentDate;
    $this->set(compact('adjustmentDate'));
    
    $requestMovements=[];
    if ($this->request->is('post')) {
      //pr($this->request->data);
      if (!empty($this->request->data['submitEnterprise'])){
        $enterpriseId=$this->request->data['Adjustment']['enterprise_id'];
      }
      else {
        $adjustmentDateTime=new DateTime($adjustmentDateAsString);
        $comment=  $this->request->data['Adjustment']['comment'];
        
        $allFuels=$this->Product->find('all',[
          'conditions'=>[  
            'Product.product_type_id'=>PRODUCT_TYPE_FUELS,
            'Product.bool_active'=>true,
          ],
          'contain'=>[
            'StockItem',
          ],  
          'order'=>'Product.product_order ASC',
        ]);
        $fuelStockItems=[];
        foreach ($allFuels as $fuel){
          $fuelStockItems[$fuel['Product']['id']]=$fuel['StockItem'][0]['id'];
        }
        //pr($fuelStockItems);
      
        $adjustmentCodes=[];
        $offSet=0;
        
        $datasource=$this->StockMovement->getDataSource();
        $datasource->begin();
        try {
          foreach ($this->request->data['StockMovement'] as $stockMovement){
            if ($stockMovement['product_id']>0 && $stockMovement['product_quantity']!=0){
              $userName=$this->Session->read('User.username');
              $adjustmentCode=$this->StockMovement->getAdjustmentCode($userName,$offSet);
              $offSet++;
              array_push($adjustmentCodes,$adjustmentCode);
              $message="Ajuste manual de inventario de día ".$adjustmentDateTime->format('d-m-Y')." en gasolinera ".$enterprises[$enterpriseId].": se ".($stockMovement['bool_input']?"aumentó":"diminuyó")." el inventario de ".$products[$stockMovement['product_id']]." con una cantidad ".$stockMovement['product_quantity']." desde ".$stockMovement['inventory_value']." galones hasta ".$stockMovement['resulting_inventory_value']." galones";
          
              $adjustmentStockMovementData=[];
              // only the IN movement; out movements are part of the movements per house that were registered
              $adjustmentStockMovementData['movement_date']=$adjustmentDateTime->format('Y-m-d H:i:s');
              $adjustmentStockMovementData['bool_input']=$stockMovement['bool_input'];
              $adjustmentStockMovementData['stock_movement_type_id']=MOVEMENT_ADJUSTMENT_GENERAL;
              $adjustmentStockMovementData['name']="Ajuste ".$products[$stockMovement['product_id']]." del día ".$adjustmentDateTime->format('d-m-Y');
              $adjustmentStockMovementData['description']=$message;
              //$adjustmentStockMovementData['hose_id']=0;
              //$adjustmentStockMovementData['operator_id']=0;
              //$adjustmentStockMovementData['shift_id']=$shiftId;
              //$adjustmentStockMovementData['order_id']=$orderId;
              $adjustmentStockMovementData['stock_item_id']=$fuelStockItems[$stockMovement['product_id']];
              $adjustmentStockMovementData['product_id']=$stockMovement['product_id'];
              $adjustmentStockMovementData['product_quantity']=$stockMovement['product_quantity'];
              $adjustmentStockMovementData['product_unit_price']=0;
              $adjustmentStockMovementData['product_total_price']=0;
              $adjustmentStockMovementData['adjustment_code']=$adjustmentCode;
              $adjustmentStockMovementData['comment']=$comment;
              //pr($adjustmentStockMovementData);
              $this->StockMovement->create();
              if (!$this->StockMovement->save($adjustmentStockMovementData)) {
                echo "problema al guardar el ajuste para ".$products[$stockMovement['product_id']];
                pr($this->validateErrors($this->StockMovement));
                throw new Exception();
              }
              
              $stockItem=$this->StockItem->find('first',[
                'conditions'=>[
                  'StockItem.id'=>$fuelStockItems[$stockMovement['product_id']]
                ],
              ]);
              if (!empty($stockItem)){
                $quantityRemaining=$stockItem['StockItem']['remaining_quantity'];
                if ($stockMovement['bool_input']){
                  $quantityRemaining+=$stockMovement['product_quantity'];
                }
                else {
                  $quantityRemaining-=$stockMovement['product_quantity'];
                }
                $stockItemData=[];
                $stockItemData['id']=$fuelStockItems[$stockMovement['product_id']];
                $stockItemData['remaining_quantity']=$quantityRemaining;
                $this->StockItem->id=$fuelStockItems[$stockMovement['product_id']];
                //pr($stockItemData);
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema al guardar el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                } 
              }
              
              $this->recreateStockItemLogs($fuelStockItems[$stockMovement['product_id']],$adjustmentDateAsString);
              $this->recordUserActivity($this->Session->read('User.username'),$message);
            }
          }
          $datasource->commit();
        
          // SAVE THE USERLOGS
          $this->recordUserAction($this->StockMovement->id,"registrarAjuste",null);
          // PENDING ADJUSTMENTCODES
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron ajustes manuales con códigos ".implode(',',$adjustmentCodes));
          $this->Session->setFlash('Se guardaron los ajustes manuales de inventario de combustibles el '.($adjustmentDateTime->format('d-m-Y')),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumenAjustesInventario']);      
        } 
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);
          $this->Session->setFlash(__('No se podía registrar los ajustes manuales del inventario.'), 'default',['class' => 'error-message']);
          
        }
      }
    }  
    $this->set(compact('requestMovements'));  
    $this->set(compact('enterpriseId'));
    
    $inventoryFuels=$this->StockItem->getInventoryItems(PRODUCT_TYPE_FUELS,$adjustmentDate,$enterpriseId,false);
    $inventoryQuantities=[];
    foreach ($inventoryFuels as $fuel){
      //pr($fuel);
      $fuelId=$fuel['Product']['id'];
      $inventoryQuantities[$fuelId]=round($fuel[0]['Remaining'],3);
    }
    //pr($inventoryQuantities);
    $this->set(compact('inventoryQuantities'));
    
    $movementDirections=[
      0 => 'Diminuir Inventario',
      1 => 'Aumentar Inventario',
    ];
    $this->set(compact('movementDirections'));
  }
  
  public function eliminarAjuste($adjustmentCode = null) {
		if (empty($adjustmentCode)) {
			throw new NotFoundException(__('No hay código de ajuste'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		
		$adjustmentMovements=$this->StockMovement->find('all', [
			'conditions' => [
				'StockMovement.adjustment_code' => $adjustmentCode,
			],
			'contain'=>[
        'StockItem'
      ],
		]);
		$flashMessage="";
		
    $datasource=$this->StockMovement->getDataSource();
    $datasource->begin();
    try {
      //delete all stockMovements, stockItems and stockItemLogs
      foreach ($adjustmentMovements as $stockMovement){
        //pr($stockMovement);
        if (!empty($stockMovement['StockItem'])){
          $stockItem['StockItem']=$stockMovement['StockItem'];
          if ($stockMovement['StockMovement']['bool_input']){
            $stockItem['StockItem']['remaining_quantity']-=$stockMovement['StockMovement']['product_quantity'];
            $stockItem['StockItem']['original_quantity']=0;
          }
          else {
            $stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
          }
          $stockItem['StockItem']['description'].="|ajuste eliminado ".$adjustmentCode;
          if (!$this->StockItem->save($stockItem)) {
            echo "problema eliminando el estado de lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
        }
        if (!$this->StockMovement->delete($stockMovement['StockMovement']['id'])) {
          echo "Problema al eliminar el movimiento de ajuste";
          pr($this->validateErrors($this->StockMovement));
          throw new Exception();
        }
      }
      foreach ($adjustmentMovements as $stockMovement){
        if (!empty($stockMovement['StockItem']['id'])){
           $this->recreateStockItemLogs($stockMovement['StockItem']['id']);
        }				
			}
			
      $datasource->commit();
    /*
      $this->loadModel('Deletion');
      $this->Deletion->create();
      $deletionArray=array();
      $deletionArray['Deletion']['user_id']=$this->Auth->User('id');
      $deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
      $deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
      $deletionArray['Deletion']['type']='Order';
      $this->Deletion->save($deletionArray);
    */			
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el ajuste número ".$adjustmentCode);
          
      $this->Session->setFlash('Se eliminó el ajuste #'.$adjustmentCode,'default',['class' => 'success']);				
    }
    catch(Exception $e){
      $datasource->rollback();
      pr($e);
      $this->Session->setFlash('No se podía eliminar el ajuste #'.$adjustmentCode, 'default',['class' => 'error-message']);
    }
    return $this->redirect(['action' => 'resumenAjustesInventario']);
	}

	public function verReporteCompraVenta($id=0){
		if (!$this->StockMovement->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
	
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->StockMovement->Product->recursive=1;
		$allOtherMaterials=$this->StockMovement->Product->find('all',array('conditions'=>array('ProductType.product_category_id'=>CATEGORY_OTHER,'Product.id'=>$id)));
		$materialPosition=array();
		$positionCounter=0;
		
		foreach ($allOtherMaterials as $tapon){
			$materialPosition[$tapon['Product']['id']]['Entry']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Exit']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Reclassified']=$positionCounter;
			$positionCounter++;
			$materialPosition[$tapon['Product']['id']]['Saldo']=$positionCounter;
			$positionCounter++;
		}
		//pr($materialPosition);
		$originalInventory=array();
		for ($i=0;$i<4*count($allOtherMaterials);$i++){
			$originalInventory[$i]=0;
		}
		$this->StockMovement->StockItem->StockItemLog->recursive=0;
		foreach ($allOtherMaterials as $tapon){
			$this->StockMovement->StockItem->recursive=-1;
			$allStockItemsForProduct = $this->StockMovement->StockItem->find('all', array(
				'conditions' => array(
					'StockItem.product_id'=> $tapon['Product']['id'],
				),
			));
			//pr($allStockItemsForProduct);
			$productInitialStock=0;
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				$this->StockMovement->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockMovement->StockItem->StockItemLog->find('first',array(
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
				if (!empty($initialStockItemLogForStockItem)){
					if ($initialStockItemLogForStockItem['StockItemLog']['product_quantity']>0){
						//pr($initialStockItemLogForStockItem);
					}
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
				}
			}
			$originalInventory[$materialPosition[$tapon['Product']['id']]['Saldo']]=$productInitialStock;
		}
		//pr($originalInventory);
		
		$otherMaterialIds=array();
		foreach ($allOtherMaterials as $tapon){
			$otherMaterials[]=$tapon['Product']['id'];
		}
		$this->StockMovement->recursive=2;
		$allStockMovements=$this->StockMovement->find('all',
			array(
				'conditions'=>array(
					'Product.id'=>$otherMaterials, // IN statement
					'StockMovement.product_quantity >'=>0,
					'movement_date >='=>$startDate,
					'movement_date <'=>$endDatePlusOne,
				),
				'order'=>'movement_date ASC'
			)
		);
		//pr($allStockMovements);
		$resultMatrix=array();
		$currentInventory=$originalInventory;
		$rowCounter=0;
		foreach($allStockMovements as $otherStockMovement){
			$resultMatrix[$rowCounter]['date']=$otherStockMovement['Order']['order_date'];
			if (!empty($otherStockMovement['Order']['ThirdParty']['company_name'])){
				$resultMatrix[$rowCounter]['providerclient']=$otherStockMovement['Order']['ThirdParty']['company_name'];
				$resultMatrix[$rowCounter]['providerid']=$otherStockMovement['Order']['ThirdParty']['id'];
				$resultMatrix[$rowCounter]['providerbool']=$otherStockMovement['Order']['ThirdParty']['bool_provider'];
        $resultMatrix[$rowCounter]['issale']=!empty($otherStockMovement['Order']['Invoice']['id']);
			}
			else {
				$resultMatrix[$rowCounter]['providerclient']="-";
				$resultMatrix[$rowCounter]['providerid']=0;
				$resultMatrix[$rowCounter]['providerbool']=0;
        $resultMatrix[$rowCounter]['issale']=0;
			}
			if (!empty($otherStockMovement['Order']['order_code'])){
				$resultMatrix[$rowCounter]['invoicecode']=$otherStockMovement['Order']['order_code'];
				$resultMatrix[$rowCounter]['invoiceid']=$otherStockMovement['Order']['id'];
				if ($otherStockMovement['Order']['stock_movement_type_id']==MOVEMENT_PURCHASE){
					$resultMatrix[$rowCounter]['entrybool']=1;
				}
				else {
					$resultMatrix[$rowCounter]['entrybool']=0;
				}
			}
			else {
				$resultMatrix[$rowCounter]['invoicecode']="-";
				$resultMatrix[$rowCounter]['invoiceid']=0;
				$resultMatrix[$rowCounter]['entrybool']=0;
			}
			$productid=$otherStockMovement['StockMovement']['product_id'];
			$boolinput=$otherStockMovement['StockMovement']['bool_input'];
			$saldoRef=0;
			if ($boolinput){
        for ($i=0;$i<4*count($allOtherMaterials);$i++){					
          if ($i==$materialPosition[$productid]['Entry']){
            $resultMatrix[$rowCounter][$i]=$otherStockMovement['StockMovement']['product_quantity'];
            $saldoRef=$i+3;
          }
          else {
            $resultMatrix[$rowCounter][$i]="-";
          }
        }
        $currentInventory[$saldoRef-3]+=$otherStockMovement['StockMovement']['product_quantity'];
        $currentInventory[$saldoRef]+=$otherStockMovement['StockMovement']['product_quantity'];
        $resultMatrix[$rowCounter][$saldoRef]=$currentInventory[$saldoRef];
      }
				
			$rowCounter++;
		}
		//pr($resultMatrix);
		//pr($currentInventory);
			
		$this->set(compact('originalInventory','resultMatrix','currentInventory','startDate','endDate','allOtherMaterials','id'));

	}
	
	public function guardarReporteCompraVenta(){
		$exportData=$_SESSION['reporteCompraVenta'];
		$this->set(compact('exportData'));
	}
  
  public function verKardex($productId=0){
		if (!$this->StockMovement->Product->exists($productId)) {
			throw new NotFoundException(__('Invalid product'));
		}
	
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->StockMovement->Product->recursive=1;
		$product=$this->StockMovement->Product->find('first',['conditions'=>['Product.id'=>$productId]]);
		$originalInventory=[
      'total_entries'=>0,
      'total_exits'=>0,
      'total_saldo'=>0,
    ];
		
    $this->StockMovement->StockItem->StockItemLog->recursive=0;
		$this->StockMovement->StockItem->recursive=-1;
    $allStockItemsForProduct = $this->StockMovement->StockItem->find('all', [
      'conditions' => ['StockItem.product_id'=> $productId],
    ]);
    //pr($allStockItemsForProduct);
    $productInitialStock=0;
    foreach ($allStockItemsForProduct as $stockItemForProduct){
      $stockItemId=$stockItemForProduct['StockItem']['id'];
      $this->StockMovement->StockItem->StockItemLog->recursive=-1;
      $initialStockItemLogForStockItem=$this->StockMovement->StockItem->StockItemLog->find('first',[
        'conditions' => [
          'StockItemLog.stock_item_id'=> $stockItemId,
          'StockItemLog.stock_item_date <'=>$startDate
        ],
        'order'=>'StockItemLog.id DESC'
      ]);
      if (!empty($initialStockItemLogForStockItem)){
        $productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
      }
    }
    $originalInventory['total_saldo']=$productInitialStock;
		//pr($originalInventory);
		$this->StockMovement->recursive=2;
		$allPurchaseStockMovements=$this->StockMovement->find('all',[
      'conditions'=>[
        'Product.id'=>$productId,
        'StockMovement.product_quantity >'=>0,
        'StockMovement.stock_movement_type_id'=>[MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
        'movement_date >='=>$startDate,
        'movement_date <'=>$endDatePlusOne,
      ],
      'order'=>'movement_date ASC'
    ]);
		//pr($allStockMovements);
		$resultMatrix=[];
		$currentInventory=$originalInventory;
		
    $rowCounter=0;
    
    foreach($allPurchaseStockMovements as $stockMovement){
			$currentInventory['total_entries']+=$stockMovement['StockMovement']['product_quantity'];
      $currentInventory['total_saldo']+=$stockMovement['StockMovement']['product_quantity'];
      
      $resultMatrix[$rowCounter]['date']=$stockMovement['Order']['order_date'];
			$resultMatrix[$rowCounter]['providerclient']=$stockMovement['Order']['ThirdParty']['company_name'];
      $resultMatrix[$rowCounter]['providerid']=$stockMovement['Order']['ThirdParty']['id'];
      $resultMatrix[$rowCounter]['ordercode']=$stockMovement['Order']['order_code'];
			$resultMatrix[$rowCounter]['orderid']=$stockMovement['Order']['id'];
			$resultMatrix[$rowCounter]['type']="Compra";
			$resultMatrix[$rowCounter]['total_entries']=$stockMovement['StockMovement']['product_quantity'];
      $resultMatrix[$rowCounter]['total_exits']=0;
      $resultMatrix[$rowCounter]['total_saldo']=$currentInventory['total_saldo'];
      
      $rowCounter++;
		}
    
    $this->StockMovement->virtualFields['dailyQuantity']=0;
    $allSaleMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'SUM(StockMovement.product_quantity) AS StockMovement__dailyQuantity',
        'Order.id','Order.order_date','Order.order_code',
        //'ThirdParty.id','ThirdParty.company_name',
      ],
      'conditions'=>[
        'Product.id'=>$productId,
        'StockMovement.product_quantity >'=>0,
        'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
        'movement_date >='=>$startDate,
        'movement_date <'=>$endDatePlusOne,
      ],
      'order'=>'movement_date ASC',
      'group'=>'movement_date',
    ]);
		//pr($$allSaleMovements);
		
    foreach($allSaleMovements as $stockMovement){
			$currentInventory['total_exits']+=$stockMovement['StockMovement']['dailyQuantity'];
      $currentInventory['total_saldo']-=$stockMovement['StockMovement']['dailyQuantity'];
      
      $resultMatrix[$rowCounter]['date']=$stockMovement['Order']['order_date'];
			$resultMatrix[$rowCounter]['providerclient']='-';
      $resultMatrix[$rowCounter]['providerid']='-';
      //$resultMatrix[$rowCounter]['code']=$stockMovement['Order']['order_code'];
      $resultMatrix[$rowCounter]['code']='Gasolinera';
			$resultMatrix[$rowCounter]['id']=$stockMovement['Order']['id'];
			$resultMatrix[$rowCounter]['type']="Venta";
			$resultMatrix[$rowCounter]['total_entries']=0;
      $resultMatrix[$rowCounter]['total_exits']=$stockMovement['StockMovement']['dailyQuantity'];
      $resultMatrix[$rowCounter]['total_saldo']=$currentInventory['total_saldo'];
      
      $rowCounter++;
		}
    
    $this->StockMovement->virtualFields['adjustmentQuantity']=0;
    $allAdjustmentMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'StockMovement.bool_input','StockMovement.movement_date','SUM(StockMovement.product_quantity) AS StockMovement__adjustmentQuantity',  'StockMovementType.name',
      ],
      'conditions'=>[
        'Product.id'=>$productId,
        'StockMovement.product_quantity >'=>0,
        'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_GENERAL,MOVEMENT_ADJUSTMENT_CALIBRATION,MOVEMENT_ADJUSTMENT_MEASURE],
        'movement_date >='=>$startDate,
        'movement_date <'=>$endDatePlusOne,
      ],
      'recursive'=>0,
      'order'=>'StockMovement.movement_date ASC',
      'group'=>'StockMovement.movement_date,StockMovement.bool_input',
    ]);
		//pr($allAdjustmentMovements);
		
    foreach($allAdjustmentMovements as $stockMovement){
      if ($stockMovement['StockMovement']['bool_input']){
        $currentInventory['total_entries']+=$stockMovement['StockMovement']['adjustmentQuantity'];
        $currentInventory['total_saldo']+=$stockMovement['StockMovement']['adjustmentQuantity'];
      }
      else {
        $currentInventory['total_exits']+=$stockMovement['StockMovement']['adjustmentQuantity'];
        $currentInventory['total_saldo']-=$stockMovement['StockMovement']['adjustmentQuantity'];
      }
			
      $resultMatrix[$rowCounter]['date']=$stockMovement['StockMovement']['movement_date'];
			$resultMatrix[$rowCounter]['providerclient']='-';
      $resultMatrix[$rowCounter]['providerid']='-';
      $resultMatrix[$rowCounter]['code']='Ajuste';
			$resultMatrix[$rowCounter]['id']='';
			$resultMatrix[$rowCounter]['type']=$stockMovement['StockMovementType']['name'];
      
      if ($stockMovement['StockMovement']['bool_input']){
        $resultMatrix[$rowCounter]['total_entries']=$stockMovement['StockMovement']['adjustmentQuantity'];
        $resultMatrix[$rowCounter]['total_exits']=0;
      }
      else {
        $resultMatrix[$rowCounter]['total_entries']=0;
        $resultMatrix[$rowCounter]['total_exits']=$stockMovement['StockMovement']['adjustmentQuantity'];
      }
			$resultMatrix[$rowCounter]['total_saldo']=$currentInventory['total_saldo'];
      
      $rowCounter++;
		}
    
		//pr($resultMatrix);	
    usort($resultMatrix,[$this,'sortByMovementDate']);
		$this->set(compact('originalInventory','resultMatrix','currentInventory','startDate','endDate','product','productId'));
	}
	
	public function guardarKardex($productName=""){
		$exportData=$_SESSION['kardex'];
		$this->set(compact('exportData','productName'));
	}
	
  public function sortByMovementDate($rowA,$rowB){
    if($rowA['date'] != $rowB['date']){ 		
			return ($rowA['date'] < $rowB['date']) ? -1 : 1;
		}
	}
	
	public function verReporteVentaProductoPorCliente(){
		$this->loadModel('Order');
		$this->loadModel('Product');
		$this->loadModel('StockItem');
		$this->loadModel('ThirdParty');
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$movementConditions=[
			'StockMovement.movement_date >='=>$startDate,
			'StockMovement.movement_date <'=>$endDatePlusOne,
			'StockMovement.product_quantity >'=>0,
			'StockMovement.bool_input'=>false,
			'StockMovement.bool_adjustment'=>false,
		];
		$movementsForPeriod=$this->StockMovement->find('all',[
			'fields'=>[
				'StockMovement.product_id',
				'StockMovement.order_id',
				'StockMovement.stock_item_id',
			],
			'conditions'=>$movementConditions,
			'contain'=>[
				'Order'=>[
					'fields'=>['Order.id','Order.third_party_id'],
				],
			],
		]);
		$soldProductIds=[];
    $orderIds=[];
		$buyingClientIds=[];
		$stockItemIds=[];
		
		foreach($movementsForPeriod as $movement){
			//pr($movement);
			$soldProductIds[]=$movement['StockMovement']['product_id'];
			$orderIds[]=$movement['Order']['id'];
      $buyingClientIds[]=$movement['Order']['third_party_id'];
			$stockItemIds[]=$movement['StockMovement']['stock_item_id'];
		}
		
		$soldProductIds=array_unique($soldProductIds);
		$this->Product->recursive=-1;
		$soldProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>[
				'Product.id'=>$soldProductIds,
			],
			'order'=>'Product.name',
		]);
		
    $orderIds=array_unique($orderIds);
    //pr($orderIds);
		$buyingClientIds=array_unique($buyingClientIds);
    /*
		$buyingClientList=$this->ThirdParty->find('list',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
			'order'=>'ThirdParty.company_name',
		]);
    */
    $buyingClients=$this->ThirdParty->find('all',[
			'conditions'=>[
				'ThirdParty.id'=>$buyingClientIds,
			],
      'contain'=>[
        'Order'=>[
          'fields'=>'Order.id',
          'conditions'=>[
            'Order.id'=>$orderIds,
          ],
        ]
      ],
			'order'=>'ThirdParty.company_name',
		]);
    for ($c=0;$c<count($buyingClients);$c++){
      $clientOrderIds=[];
      foreach ($buyingClients[$c]['Order'] as $order){
        $clientOrderIds[]=$order['id'];
      }
      $buyingClients[$c]['orderIds']=array_unique( $clientOrderIds);
    }
		//pr($buyingClients);
		$stockItemIds=array_unique($stockItemIds);
		$stockItemList=$this->StockItem->find('list',[
			'conditions'=>[
				'StockItem.id'=>$stockItemIds,
			],
		]);
		
		//pr($soldProducts);
		//pr($buyingClientList);
		/*
		for ($p=0;$p<count($soldProducts);$p++){
			//echo "soldProduct is ".$soldProducts[$p]['Product']['name']."<br/>";
			$movementConditionsForPeriodAndProduct=$movementConditions;
			$movementConditionsForPeriodAndProduct[]=array('StockMovement.product_id'=>$soldProducts[$p]['Product']['id']);
			$movementsForPeriodAndProduct=$this->StockMovement->find('all',array(
				'fields'=>array(
					'StockMovement.stock_item_id',				
				),
				'conditions'=>$movementConditionsForPeriodAndProduct,
				'contain'=>array(
					'StockItem'=>array(
						'fields'=>array('StockItem.raw_material_id'),
					),
				),
			));
			//pr($movementsForPeriodAndProduct);
			$rawMaterialIds=array();
			foreach($movementsForPeriodAndProduct as $movement){
				//pr($movement);
				$rawMaterialIds[]=$movement['StockItem']['raw_material_id'];
			}
			$rawMaterialIds=array_unique($rawMaterialIds);	
			// now retrieve what we need for the rawmaterials
			$rawMaterials=$this->Product->find('all',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array(
					'Product.id'=>$rawMaterialIds,
				),
				'order'=>'Product.name',
			));
			//pr($rawMaterials);
			//echo "count of raw materials is ".count($rawMaterials)."<br/>";
			if ($soldProducts[$p]['Product']['id']==12){
				//echo "product material is ".$soldProducts[$p]['Product']['id']."<br/>";
			}
			for ($r=0;$r<count($rawMaterials);$r++){
				if ($soldProducts[$p]['Product']['id']==12){
					//echo "raw material is ".$rawMaterials[$r]['Product']['id']."<br/>";
				}
				//echo "r is ".$r."<br/>";
				//pr($rawMaterials[$r]);
				$rawMaterialStockItemIds=$this->StockItem->find('list',array(
					'fields'=>array('StockItem.id'),
					'conditions'=>array(
						'StockItem.id'=>$stockItemIds,
						'StockItem.raw_material_id'=>$rawMaterials[$r]['Product']['id'],
						'StockItem.product_id'=>$soldProducts[$p]['Product']['id'],
					),
				));
				$movementConditionsForPeriodAndProductAndRawMaterial=$movementConditionsForPeriodAndProduct;
				$movementConditionsForPeriodAndProductAndRawMaterial[]=array('StockMovement.stock_item_id'=>$rawMaterialStockItemIds);
				$stockItemIdsForRawMaterialAndFinishedProduct=$this->StockMovement->find('list',array(
					'fields'=>array(
						'StockMovement.stock_item_id',		
					),
					'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterial,
				));
				if ($soldProducts[$p]['Product']['id']==12){
					//echo "movement conditions for CL 365ML and preforma 21 <br/>";
					//pr($movementConditionsForPeriodAndProductAndRawMaterial);
					//echo "stock item ids for CL 365ML and preforma 21 <br/>";
					//pr($stockItemIdsForRawMaterialAndFinishedProduct);
				}
				$clientProductArray=array();
				
				foreach ($buyingClientList as $clientId=>$clientName){
					$orderConditionsForClient=array(
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
						'Order.bool_annulled'=>false,
						'Order.third_party_id'=>$clientId,
						'Order.stock_movement_type_id'=>MOVEMENT_SALE,
					);
					$orderIdsForClient=$this->Order->find('list',array(
						'fields'=>array('Order.id'),
						'conditions'=>$orderConditionsForClient,
					));
					
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient=$movementConditionsForPeriodAndProductAndRawMaterial;
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient[]=array('StockMovement.order_id'=>$orderIdsForClient);
					//echo "showing the ocnditions for period and product and raw material and client";
					//pr($movementConditionsForPeriodAndProductAndRawMaterialAndClient);
					$this->StockMovement->virtualFields['product_total'] = 0;
					$quantityPurchasedForClient = $this->StockMovement->find('all', array(
						'fields' => array('SUM(StockMovement.product_quantity) AS StockMovement__product_total'),
						'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterialAndClient,
					));
					//pr($quantityPurchasedForClient);
					
					$clientData=array();
					$clientData['client_id']=$clientId;
					$clientData['client_name']=$clientName;
					if (!empty($quantityPurchasedForClient['0']['StockMovement']['product_total'])){
						$clientData['product_quantity']=$quantityPurchasedForClient['0']['StockMovement']['product_total'];
					}
					else {
						$clientData['product_quantity']=0;
					}
					
					$clientProductArray[]=$clientData;
				}
				$rawMaterials[$r]['Clients']=$clientProductArray;
			}
			//pr($rawMaterials);
			$soldProducts[$p]['RawMaterials']=$rawMaterials;
		}
		*/
    //pr($soldProducts);
    
    for ($p=0;$p<count($soldProducts);$p++){
			$movementConditionsForPeriodAndProduct=$movementConditions;
			$movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$soldProducts[$p]['Product']['id']];
			$movementsForPeriodAndProduct=$this->StockMovement->find('all',[
				'fields'=>['StockMovement.stock_item_id',				],
				'conditions'=>$movementConditionsForPeriodAndProduct,
				'contain'=>[
					'StockItem'=>['fields'=>['StockItem.raw_material_id'],],
				],
			]);
			$rawMaterialIds=[];
			foreach($movementsForPeriodAndProduct as $movement){
				$rawMaterialIds[]=$movement['StockItem']['raw_material_id'];
			}
			$rawMaterialIds=array_unique($rawMaterialIds);	
			$rawMaterials=$this->Product->find('all',[
				'fields'=>['Product.id','Product.name'],
				'conditions'=>['Product.id'=>$rawMaterialIds,],
				'order'=>'Product.name',
			]);
			
			$soldProducts[$p]['RawMaterials']=$rawMaterials;
    }
    //pr($soldProducts);
    //pr ($buyingClients);
    
    for ($c=0;$c<count($buyingClients);$c++){
      $orderIdsForClient=$buyingClients[$c]['orderIds'];
      
      foreach ($soldProducts as $product){
        $movementConditionsForPeriodAndProduct=$movementConditions;
        $movementConditionsForPeriodAndProduct[]=['StockMovement.product_id'=>$product['Product']['id']];
        
        foreach ($product['RawMaterials'] as $rawMaterial){
          $rawMaterialStockItemIds=$this->StockItem->find('list',[
            'fields'=>['StockItem.id'],
            'conditions'=>[
              'StockItem.id'=>$stockItemIds,
              'StockItem.raw_material_id'=>$rawMaterial['Product']['id'],
              'StockItem.product_id'=>$product['Product']['id'],
            ],
          ]);
          $movementConditionsForPeriodAndProductAndRawMaterial=$movementConditionsForPeriodAndProduct;
          $movementConditionsForPeriodAndProductAndRawMaterial[]=['StockMovement.stock_item_id'=>$rawMaterialStockItemIds];
          $movementConditionsForPeriodAndProductAndRawMaterialAndClient=$movementConditionsForPeriodAndProductAndRawMaterial;
					$movementConditionsForPeriodAndProductAndRawMaterialAndClient[]=['StockMovement.order_id'=>$orderIdsForClient];
          $this->StockMovement->virtualFields['product_total'] = 0;
          $quantityPurchasedForClient = $this->StockMovement->find('all', [
            'fields' => ['SUM(StockMovement.product_quantity) AS StockMovement__product_total'],
            'conditions'=>$movementConditionsForPeriodAndProductAndRawMaterialAndClient,
          ]);
          if (!empty($quantityPurchasedForClient['0']['StockMovement']['product_total'])){
            $buyingClients[$c]['quantities'][]=['product_quantity'=>$quantityPurchasedForClient['0']['StockMovement']['product_total']];
          }
          else {
            $buyingClients[$c]['quantities'][]=['product_quantity'=>0];
          }
        }
      }
    }
		//pr($buyingClients);
		$this->set(compact('soldProducts','buyingClients','startDate','endDate'));

	}
	
	public function guardarReporteVentaProductoPorCliente(){
		$exportData=$_SESSION['reporteVentaProductoPorCliente'];
		$this->set(compact('exportData'));
	}
  
}
