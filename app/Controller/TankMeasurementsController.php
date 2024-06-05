<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class TankMeasurementsController extends AppController {
	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setduedate','imprimirVenta','guardarResumenVentasRemisiones','guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones','guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones','guardarResumenComprasRealizadas','verPdfEntrada','verPdfEntradaSuministros','verPdfVenta','verPdfRemision','sortByTotalForClient','guardarReporteCierre','guardarReporteVentasCliente');		
	}
  
  public function registrarMedidas($saleDateAsString = '') {
		$this->loadModel('Product');
    $this->loadModel('Tank');
   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
    
    $this->loadModel('HoseCounter');
				
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
        
    $this->Product->recursive=-1;
		
		$enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$measurementDateArray=$this->request->data['TankMeasurement']['measurement_date'];
			$measurementDateAsString=$measurementDateArray['year'].'-'.$measurementDateArray['month'].'-'.$measurementDateArray['day'];
			$measurementDate=date( "Y-m-d", strtotime($measurementDateAsString));
      
      $enterpriseId=$this->request->data['TankMeasurement']['enterprise_id'];
		}
		elseif (!empty($saleDateAsString)){
      $measurementDate=date( "Y-m-d", strtotime($saleDateAsString));
      $measurementDateAsString=$saleDateAsString;
    }
    else if (!empty($_SESSION['measurementDate'])){
			$measurementDateAsString=$measurementDate=$_SESSION['measurementDate'];
		}
		else {
			$measurementDateAsString=$measurementDate = date( "Y-m-d", strtotime(date('Y-m-d')."-1 days"));
		}
    $_SESSION['measurementDate']=$measurementDate;
    $measurementDateMinusOne=date( "Y-m-d", strtotime($measurementDateAsString."-1 days"));
    $measurementDatePlusOne=date( "Y-m-d", strtotime($measurementDateAsString."+1 days"));

    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    

    $requestTankMeasurements=[];
    $boolEditingMode=false;
    $boolEditingToggleVisible=false;
    
		if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	
      //pr($this->request->data);			
      foreach ($this->request->data['Fuel'] as $fuelId => $fuelData){
        $requestTankMeasurements[$fuelId]=$fuelData['TankMeasurement']['measurement_value'];
      }
      
      $enterpriseId=$this->request->data['TankMeasurement']['enterprise_id'];
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
       
      // check if previous data are present
      $previousDataPresent=true;
      $previousDataWarning="";
      // for informe I: no problemo, nothing to save if there is no informe I
      $hoseCounters=$this->HoseCounter->find('list',[
        'conditions'=>[
          'HoseCounter.counter_date >='=>$measurementDate,
          'HoseCounter.counter_date <'=>$measurementDatePlusOne,
          'HoseCounter.enterprise_id'=>$enterpriseId,
        ],
      ]);
      if (empty($hoseCounters)){
        $previousDataPresent=false;
        $previousDataWarning.="Se debe registrar informe I Ventas de Isla antes de registrar informe II.  ";
      }  
       
      if ($measurementDateAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de las medidas no puede estar en el futuro!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      elseif ($measurementDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      elseif (!$previousDataPresent){
        $this->Session->setFlash($previousDataWarning."No se guardaron las medidas.", 'default',['class' => 'error-message']);
      }
      else {
        //pr($this->request->data);
        $tankIdsByFuels=$this->Tank->getTankIdsByFuels();
        //pr($tankIdsByFuels);
        
        $datasource=$this->TankMeasurement->getDataSource();
        $datasource->begin();
        try {
          // FIRST REMOVE PREVIOUS VALUES OF TANKMEASUREMENT FOR EDITING
          $tanks=$this->Tank->find('all',[
            'conditions'=>['Tank.enterprise_id'=>$enterpriseId,],
            'contain'=>[
              'TankMeasurement'=>[
                'conditions'=>['measurement_date'=>$measurementDate]
              ]
            ],
          ]);
          //pr($tanks);
          foreach ($tanks as $tank){
            if (!empty($tank['TankMeasurement'])){
              foreach ($tank['TankMeasurement'] as $tankMeasurement){
                if (!$this->TankMeasurement->delete($tankMeasurement['id'])) {
                  echo "Problema eliminando la medida de vara obsoleta";
                  pr($this->validateErrors($this->TankMeasurement));
                  throw new Exception();
                }
              }
            }
          }
          // THEN SAVE THE NEW DATA
          foreach ($this->request->data['Fuel'] as $fuelId=>$fuelData){
            $tankMeasurementData=$fuelData['TankMeasurement'];
            $tankMeasurementData['tank_id']=$tankIdsByFuels[$fuelId];
            $tankMeasurementData['measurement_date']=$measurementDateAsString;
            $tankMeasurementData['enterprise_id']=$enterpriseId;
            //pr($tankMeasurementData);
            $this->TankMeasurement->create();
            
            if (!$this->TankMeasurement->save($tankMeasurementData)) {
              echo "Problema guardando la medida electrónica de manguera";
              pr($this->validateErrors($this->TankMeasurement));
              throw new Exception();
            }
          }
          
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron las medidas de vara de fecha ".$measurementDateAsString);
          $this->Session->setFlash("Se registraron las medidas de vara de fecha ".$measurementDateAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolEditingMode=false;
          $boolEditingToggleVisible=true;
          
          return $this->redirect(['controller'=>'hoseMeasurements','action' => 'registrarMedidas',$measurementDateAsString]);
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar las medidas de vara de fecha ".$measurementDateAsString, 'default',['class' => 'error-message']);
        }
      }					
    }
		else {
      //pr($measurementDate);
      $tanks=$this->Tank->find('all',[
        'conditions'=>[
          'Tank.enterprise_id'=>$enterpriseId
        ],
        'contain'=>[
          'TankMeasurement'=>[
            'conditions'=>[
              'measurement_date'=>$measurementDate,
              'TankMeasurement.enterprise_id'=>$enterpriseId,
            ]
          ],
        ],    
      ]);
      //pr($tanks);
      foreach ($tanks as $tank){
        if (!empty($tank['TankMeasurement'])){
          $requestTankMeasurements[$tank['Tank']['product_id']]=$tank['TankMeasurement'][0]['measurement_value'];
        }
      }
      //pr($requestTankMeasurements);
      if (empty($requestTankMeasurements)){
        $boolEditingMode=true;
      }
      else {
        $boolEditingToggleVisible=true;
      }
    }
    $this->set(compact('boolEditingMode'));
    $this->set(compact('boolEditingToggleVisible'));
    $this->set(compact('measurementDate'));
		$this->set(compact('enterpriseId'));
		$this->set(compact('requestTankMeasurements'));
				
		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','enterprise_id'],
          'conditions'=>[
            'StockItem.bool_active'=>true,
            'StockItem.enterprise_id'=>$enterpriseId,
          ],
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

    $this->loadModel('Operator');
		$operators=$this->Operator->getOperatorListForEnterprise($enterpriseId);
		$this->set(compact('operators'));
    
    $this->loadModel('Shift');
		$shifts=$this->Shift->getShiftListForEnterprise($enterpriseId);
    //pr($shifts);
		$this->set(compact('shifts'));
    
    $this->loadModel('Island');
		$islands=$this->Island->getIslandListForEnterprise($enterpriseId);
		$this->set(compact('islands'));
    
    $tankMeasurementConditions=[
      'TankMeasurement.measurement_date <'=>$measurementDate,
    ];
    if ($enterpriseId > 0){
      $tankMeasurementConditions['TankMeasurement.enterprise_id']=$enterpriseId;
    }
    
    $fuels=$this->Product->find('all',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'contain'=>[
        'DefaultPriceCurrency',
        'ProductPriceLog'=>[
          'conditions'=>[
            'price_datetime <'=>$measurementDatePlusOne,
            'ProductPriceLog.enterprise_id'=>$enterpriseId,
          ],
          'order'=>'price_datetime DESC',
          'limit'=>1,
          'Currency',
        ],
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.movement_date >='=>$measurementDate,
            'StockMovement.movement_date <'=>$measurementDatePlusOne,
            'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE,MOVEMENT_PURCHASE,MOVEMENT_ADJUSTMENT_CALIBRATION],
            'StockMovement.enterprise_id'=>$enterpriseId,
          ],
          'Shift'=>[
            'conditions'=>['Shift.enterprise_id'=>$enterpriseId,]
          ],
        ],
        'Tank'=>[
          'limit'=>1,
          'TankMeasurement'=>[
            'conditions'=>$tankMeasurementConditions,
            'order'=>'TankMeasurement.measurement_date DESC',
            'limit'=>1,
          ]
        ],
      ],  
      'order'=>'Product.product_order ASC',
    ]);
    //pr($fuels);
    for ($f=0;$f<count($fuels);$f++){
      $fuelId=$fuels[$f]['Product']['id'];
      
      $fuels[$f]['Product']['initial_existence']=$fuels[$f]['Tank'][0]['TankMeasurement'][0]['measurement_value'];
      
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
          $fuels[$f]['Shift'][$shiftId]+=$stockMovement['product_quantity'];
        }
      }
      $fuels[$f]['Product']['entered']=$entered;
      $fuels[$f]['Product']['exited']=$exited;
      $fuels[$f]['Product']['final_existence']=$fuels[$f]['Product']['initial_existence']+$entered-$exited;
    }
    
    $this->set(compact('fuels'));
	}
	
  public function reporteMedidasTanques(){
    $this->loadModel('Product');
    $this->loadModel('Tank');
   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $enterpriseId=0;
    $tankId=0;
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
      $tankId=$this->request->data['Report']['tank_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
    $this->set(compact('tankId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    //echo 'enterpriseId is '.$enterpriseId.'<br/>';
    $fuelsAndTanks=$this->Product->getTanksAndFuelsForEnterprise($enterpriseId);
    $this->set(compact('tanks'));
    
    $measurementsArray=[
      'GrandTotal'=>[
        'inventory_value'=>0,
        'measurement_value'=>0,
        'calibration_value'=>0,
      ],
    ];
    $emptyTotals=[
      'inventory_value'=>0,
      'measurement_value'=>0,
      'calibration_value'=>0,
    ];
    if (!empty($fuelsAndTanks)){
      foreach ($fuelsAndTanks as $fuelTankData){
        $tankId=$fuelTankData['Tank'][0]['id'];
        $measurementsArray['Tank'][$tankId]=[
          'tank_name'=>$fuelTankData['Tank'][0]['name'],
          'product_id'=>$fuelTankData['Product']['id'],
          'product_name'=>$fuelTankData['Product']['name'],
        ];
      }
    }
    $currentDate=$endDate;
    $startDateMinusOne=date( "Y-m-d", strtotime($startDate."-1 days"));
    
    while ($currentDate>$startDateMinusOne){
      foreach ($fuelsAndTanks as $fuelTankData){
        $tankId=$fuelTankData['Tank'][0]['id'];
        $measurementsArray['Measurement'][$currentDate]['Tank'][$tankId]=$emptyTotals;
      }  
      $measurementsArray['Measurement'][$currentDate]['DayTotal']=$emptyTotals;
      $currentDate=date( "Y-m-d", strtotime( $currentDate."-1 days" ) );
    }
    //pr($measurementsArray);
    $tankMeasurementConditions=[
      'TankMeasurement.measurement_date >='=>$startDate,
      'TankMeasurement.measurement_date <'=>$endDatePlusOne,
    ];
    $tankMeasurements=$this->TankMeasurement->find('all',[
      'fields'=>[
        'measurement_date',
        'tank_id',
        'measurement_value',
      ],
      'conditions'=>$tankMeasurementConditions,
      'order'=>'measurement_date DESC',
      'group'=>'TankMeasurement.measurement_date, TankMeasurement.tank_id'
    ]);
    //pr($tankMeasurements);
   
   if (!empty($tankMeasurements)){
      foreach ($tankMeasurements as $tankMeasurement){
        $tankId=$tankMeasurement['TankMeasurement']['tank_id'];
        $measurementDate=$tankMeasurement['TankMeasurement']['measurement_date'];
        $measurementDatePlusOne= date( "Y-m-d", strtotime( $measurementDate."+1 days" ) );
        
        $fuelInventoryValue=$this->StockItemLog->getStockQuantityAtDateForProduct($tankId,$measurementDatePlusOne,$enterpriseId);
        
        $measurementsArray['GrandTotal']['inventory_value']+=$fuelInventoryValue;
        $measurementsArray['Measurement'][$measurementDate]['DayTotal']['inventory_value']+=$fuelInventoryValue;
        $measurementsArray['Measurement'][$measurementDate]['Tank'][$tankId]['inventory_value']=$fuelInventoryValue;
        
        $measurementsArray['GrandTotal']['measurement_value']+=$tankMeasurement['TankMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['DayTotal']['measurement_value']+=$tankMeasurement['TankMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['Tank'][$tankId]['measurement_value']=$tankMeasurement['TankMeasurement']['measurement_value'];
      }
    }
    //pr($measurementsArray);
    
    $this->set(compact('measurementsArray'));
  }
  
  public function guardarReporteMedidasTanques($fileName){
    $exportData=$_SESSION['reporteMedidasTanques'];
    $this->set(compact('exportData','fileName'));
  }
  
}
