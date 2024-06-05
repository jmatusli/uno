<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');


class HoseMeasurementsController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('');		
	}

  public function registrarMedidas($saleDateAsString = '') {
    $this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
    $this->loadModel('HoseCounter');
    $this->loadModel('TankMeasurement');
    
    $this->loadModel('Hose');
    $this->loadModel('Island');
		
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		$enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$measurementDateArray=$this->request->data['HoseMeasurement']['measurement_date'];
			$measurementDateAsString=$measurementDateArray['year'].'-'.$measurementDateArray['month'].'-'.$measurementDateArray['day'];
			$measurementDate=date( "Y-m-d", strtotime($measurementDateAsString));
      
      $enterpriseId=$this->request->data['HoseMeasurement']['enterprise_id'];
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
    
    $enterpriseIslandIds=array_keys($this->Island->getIslandListForEnterprise($enterpriseId));
    
    $requestHoseMeasurements=[];
    $boolEditingMode=false;
    $boolEditingToggleVisible=false;
    
    
    if ($this->request->is('post') && empty($this->request->data['changeDate'])) {
      $enterpriseId=$this->request->data['HoseMeasurement']['enterprise_id'];
      foreach ($this->request->data['Hose'] as $hoseId=>$hoseData){
        $requestHoseMeasurements[$hoseId]=$hoseData['HoseMeasurement']['measurement_value'];
      }
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
       
      $latestHoseMeasurementAfterSelectedDay=$this->HoseMeasurement->find('first',[
        'fields'=>['measurement_date'],
        'conditions'=>[
          'measurement_date >'=>$measurementDate,
          'HoseMeasurement.enterprise_id'=>$enterpriseId,
        ],
        'limit'=>1,
        'order'=>'measurement_date DESC',
      ]);
      //echo "measurement date string is ".$measurementDateAsString."<br/>";
      //pr($latestHoseMeasurementAfterSelectedDay);
      
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
      // for informe II: check if tankmeasurements are present
      $tankMeasurements=$this->TankMeasurement->find('list',[
        'conditions'=>[
          'TankMeasurement.measurement_date >='=>$measurementDate,
          'TankMeasurement.measurement_date <'=>$measurementDatePlusOne,
          'TankMeasurement.enterprise_id'=>$enterpriseId,
        ],
      ]);
      if (empty($tankMeasurements)){
        $previousDataPresent=false;
        $previousDataWarning.="Se debe registrar informe II Medidas de Tanque antes de registrar informe IV.  ";
      }
      //echo "measurement date string is ".$measurementDateAsString."<br/>";
      if ($measurementDateAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de las medidas no puede estar en el futuro!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      elseif ($measurementDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      elseif (!empty($latestHoseMeasurementAfterSelectedDay)){
        $this->Session->setFlash("Ya existen medidas electrónicas de una fecha posterior a la fecha seleccionada.  Hay que remover estas medidas primeras, comenzando con las medidas de ".$latestHoseMeasurementAfterSelectedDay['HoseMeasurement']['measurement_date'], 'default',['class' => 'error-message']);
      }
      elseif (!$previousDataPresent){
        $this->Session->setFlash($previousDataWarning."No se guardaron las medidas.", 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->HoseMeasurement->getDataSource();
        $datasource->begin();
        try {
          // FIRST REMOVE PREVIOUS VALUES OF TANKMEASUREMENT FOR EDITING
          $hoses=$this->Hose->find('all',[
            'conditions'=>['Hose.island_id'=>$enterpriseIslandIds,],
            'contain'=>[
              'HoseMeasurement'=>[
                'conditions'=>[
                  'measurement_date'=>$measurementDate,
                  'HoseMeasurement.enterprise_id'=>$enterpriseId,
                ]
              ]
            ],
          ]);
          //pr($hoses);
          foreach ($hoses as $hose){
            if (!empty($hose['HoseMeasurement'])){
              foreach ($hose['HoseMeasurement'] as $hoseMeasurement){
                if (!$this->HoseMeasurement->delete($hoseMeasurement['id'])) {
                  echo "Problema eliminando la medida electrónica de manguera obsoleta";
                  pr($this->validateErrors($this->HoseMeasurement));
                  throw new Exception();
                }
              }
            }
          }
          // THEN SAVE THE NEW DATA
          foreach ($this->request->data['Hose'] as $hoseId=>$hoseData){
            $hoseMeasurementData=$hoseData['HoseMeasurement'];
            $hoseMeasurementData['hose_id']=$hoseId;
            $hoseMeasurementData['measurement_date']=$measurementDateAsString;
            $hoseMeasurementData['enterprise_id']=$enterpriseId;
            //pr($hoseMeasurementData);
            
            $this->HoseMeasurement->create();
            if (!$this->HoseMeasurement->save($hoseMeasurementData)) {
              echo "Problema guardando la medida electrónica de manguera";
              pr($this->validateErrors($this->HoseMeasurement));
              throw new Exception();
            }
          }
         
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron las medidas electrónicas de fecha ".$measurementDateAsString);
          $this->Session->setFlash("Se registraron las medidas electrónicas de fecha ".$measurementDateAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolEditingMode=false;
          $boolEditingToggleVisible=true;
          
          return $this->redirect(['controller'=>'paymentReceipts','action' => 'registrarRecibos',$measurementDateAsString]);
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar las medidas electrónicas de fecha ".$measurementDateAsString, 'default',['class' => 'error-message']);
        }
      }	
    }      
   
		else {
      $hoses=$this->Hose->find('all',[
        'conditions'=>[
          'Hose.bool_active'=>true,
          'Hose.island_id'=>$enterpriseIslandIds,
        ],
        'contain'=>[
          'HoseMeasurement'=>[
            'conditions'=>[
              'measurement_date'=>$measurementDate,
              'HoseMeasurement.enterprise_id'=>$enterpriseId,
            ]
          ]
        ],
      ]);
      //pr($hoses);
      foreach ($hoses as $hose){
        if (!empty($hose['HoseMeasurement'])){
          $requestHoseMeasurements[$hose['Hose']['id']]=$hose['HoseMeasurement'][0]['measurement_value'];
        }
      }
      //pr($requestHoseMeasurements);
      if (empty($requestHoseMeasurements)){
        $boolEditingMode=true;
      }
      else {
        $boolEditingToggleVisible=true;
      }
    }
    
    //pr($requestHoseMeasurements);
    //pr($measurementDate);
    $this->set(compact('boolEditingMode'));
    $this->set(compact('boolEditingToggleVisible'));
    $this->set(compact('measurementDate'));
		$this->set(compact('requestHoseMeasurements'));
		/*		
    $shifts[0]['FuelTotals']=[];
    $shifts[0]['FuelTotals'][0]=2440;
    $shifts[0]['FuelTotals'][1]=229.5;
    $shifts[0]['FuelTotals'][2]=476.5;
    $shifts[0]['FuelTotals'][3]=983.7;
    $shifts[0]['FuelTotals'][4]=750.3;
    $shifts[1]['FuelTotals']=[];
    $shifts[1]['FuelTotals'][0]=0;
    $shifts[1]['FuelTotals'][1]=0;
    $shifts[1]['FuelTotals'][2]=0;
    $shifts[1]['FuelTotals'][3]=0;
    $shifts[1]['FuelTotals'][4]=0;
    $shifts[2]['FuelTotals']=[];
    $shifts[2]['FuelTotals'][0]=751.4;
    $shifts[2]['FuelTotals'][1]=24.9;
    $shifts[2]['FuelTotals'][2]=178.7;
    $shifts[2]['FuelTotals'][3]=251.4;
    $shifts[2]['FuelTotals'][4]=296.4;
    //pr($shifts);
		$this->set(compact('shifts'));
    */
    //pr($measurementDate);
		$islands=$this->Island->find('all',[
      'conditions'=>['Island.id'=>$enterpriseIslandIds,],
      'contain'=>[
        'Hose'=>[
          'Product',
          'HoseMeasurement'=>[
            'conditions'=>[
              'HoseMeasurement.measurement_date <'=>$measurementDate,
              'HoseMeasurement.enterprise_id'=>$enterpriseId,
            ],
            'order'=>'HoseMeasurement.measurement_date DESC',
            'limit'=>1,
          ],
          'StockMovement'=>[
            'conditions'=>[
              'DATE(StockMovement.movement_date)'=>$measurementDate,
              'enterprise_id'=>$enterpriseId,
            ]
          ],
        ],
      ],
      'order'=>'Island.name',
		]);
    //pr($islands);
    for ($i=0;$i<count($islands);$i++){
      $islandFuelTotal=0;
      for ($h=0;$h<count($islands[$i]['Hose']);$h++){
        $hoseFuelTotal=0;
        if (!empty($islands[$i]['Hose'][$h]['StockMovement'])){
          foreach ($islands[$i]['Hose'][$h]['StockMovement'] as $stockMovement){
            $hoseFuelTotal+=$stockMovement['product_quantity']*GALLONS_TO_LITERS;
          }
        }
        // temporary filling up
        //echo "hose id is".$islands[$i]['Hose'][$h]['id']."<br/>";
        /*
        switch($islands[$i]['Hose'][$h]['id']){
          case 1:
            $hoseFuelTotal=134.4;
            break;
          case 2:
            $hoseFuelTotal=62.9;
            break;
          case 3:
            $hoseFuelTotal=29.8;
            break;
          case 4:
            $hoseFuelTotal=120;
            break;  
          case 5:
            $hoseFuelTotal=25.8;
            break;
          case 6:
            $hoseFuelTotal=49.1;
            break;
          case 7:
            $hoseFuelTotal=93.5;
            break;
          case 8:
            $hoseFuelTotal=129.6;
            break;    
          case 9:
            $hoseFuelTotal=163.8;
            break;
          case 10:
            $hoseFuelTotal=578.5;
            break;
          case 11:
            $hoseFuelTotal=247.4;
            break;
          case 12:
            $hoseFuelTotal=562.3;
            break;
          case 13:
            $hoseFuelTotal=127.5;
            break;
          case 14:
            $hoseFuelTotal=37.6;
            break;
          case 15:
            $hoseFuelTotal=56.6;
            break;
          case 16:
            $hoseFuelTotal=247.2;
            break;
          case 17:
            $hoseFuelTotal=151.9;
            break;
          case 18:
            $hoseFuelTotal=373.5;
            break;            
        }
        */
        $islands[$i]['Hose'][$h]['fuel_total']=round($hoseFuelTotal,2);
        $islandFuelTotal+=$hoseFuelTotal;
      }
      $islands[$i]['fuel_total']=$islandFuelTotal;
    }
    //pr($islands);
		$this->set(compact('islands'));
  }

  public function reporteMedidasMangueras(){
     $this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('HoseCounter');
    $this->loadModel('Hose');
    $this->loadModel('Island');
		
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $enterpriseId=0;
    $hoseId=0;
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
      $hoseId=$this->request->data['Report']['hose_id'];
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
    $this->set(compact('hoseId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    //echo 'enterpriseId is '.$enterpriseId.'<br/>';
    
    $hoses=$this->Hose->find('all',[
      'conditions'=>[
        'Hose.enterprise_id'=>$enterpriseId,
        'Hose.bool_active'=>true,
      ],
      'contain'=>[
        'Island',
        'Product',
      ],
      'order'=>'Hose.name ASC',
    ]);
    $this->set(compact('hoses'));
    
    $measurementsArray=[
      'GrandTotal'=>[
        'counter_value'=>0,
        'measurement_value'=>0,
      ],
    ];
    $emptyTotals=[
      'counter_value'=>0,
      'measurement_value'=>0,
    ];
    foreach ($hoses as $hoseData){
      $hoseId=$hoseData['Hose']['id'];
      $measurementsArray['Hose'][$hoseId]=[
        'hose_name'=>$hoseData['Hose']['name'],
        'product_id'=>$hoseData['Product']['id'],
        'product_name'=>$hoseData['Product']['name'],
        'island_id'=>$hoseData['Island']['id'],
        'island_name'=>$hoseData['Island']['name'],
      ];
    }
    $currentDate=$endDate;
    $startDateMinusOne=date( "Y-m-d", strtotime($startDate."-1 days"));
    
    while ($currentDate>$startDateMinusOne){
      foreach ($hoses as $hoseData){
        $hoseId=$hoseData['Hose']['id'];
        $measurementsArray['Measurement'][$currentDate]['Hose'][$hoseId]=$emptyTotals;
      }  
      $measurementsArray['Measurement'][$currentDate]['DayTotal']=$emptyTotals;
      $currentDate=date( "Y-m-d", strtotime( $currentDate."-1 days" ) );
    }
    //pr($measurementsArray);
    
    $hoseMeasurementConditions=[
      'HoseMeasurement.measurement_date >='=>$startDate,
      'HoseMeasurement.measurement_date <'=>$endDatePlusOne,
    ];
    $hoseMeasurements=$this->HoseMeasurement->find('all',[
      'fields'=>[
        'measurement_date',
        'hose_id',
        'measurement_value',
      ],
      'conditions'=>$hoseMeasurementConditions,
      'order'=>'measurement_date DESC',
      'group'=>'HoseMeasurement.measurement_date, HoseMeasurement.hose_id'
    ]);
    //pr($hoseMeasurements);
   
    if (!empty($hoseMeasurements)){
      foreach ($hoseMeasurements as $hoseMeasurement){
        $hoseId=$hoseMeasurement['HoseMeasurement']['hose_id'];
        $measurementDate=$hoseMeasurement['HoseMeasurement']['measurement_date'];
        
        $measurementsArray['GrandTotal']['measurement_value']+=$hoseMeasurement['HoseMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['DayTotal']['measurement_value']+=$hoseMeasurement['HoseMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['Hose'][$hoseId]['measurement_value']=$hoseMeasurement['HoseMeasurement']['measurement_value'];
      }
    }
    
    $hoseMeasurementDayBeforeConditions=[
      'HoseMeasurement.measurement_date >='=>$startDateMinusOne,
      'HoseMeasurement.measurement_date <'=>$endDate,
    ];
    $hoseMeasurementsDayBefore=$this->HoseMeasurement->find('all',[
      'fields'=>[
        'measurement_date',
        'hose_id',
        'measurement_value',
      ],
      'conditions'=>$hoseMeasurementDayBeforeConditions,
      'order'=>'measurement_date DESC',
      'group'=>'HoseMeasurement.measurement_date, HoseMeasurement.hose_id'
    ]);
    //pr($hoseMeasurements);
   
    if (!empty($hoseMeasurementsDayBefore)){
      foreach ($hoseMeasurementsDayBefore as $hoseMeasurementDayBefore){
        $hoseId=$hoseMeasurementDayBefore['HoseMeasurement']['hose_id'];
        $measurementDateDayBefore=$hoseMeasurementDayBefore['HoseMeasurement']['measurement_date'];
        $measurementDate=date( "Y-m-d", strtotime($measurementDateDayBefore."+1 days" ) );
        
        $measurementsArray['GrandTotal']['measurement_value']-=$hoseMeasurementDayBefore['HoseMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['DayTotal']['measurement_value']-=$hoseMeasurementDayBefore['HoseMeasurement']['measurement_value'];
        $measurementsArray['Measurement'][$measurementDate]['Hose'][$hoseId]['measurement_value']-=$hoseMeasurementDayBefore['HoseMeasurement']['measurement_value'];
      }
    }
    
    $hoseCounterConditions=[
      'HoseCounter.counter_date >='=>$startDate,
      'HoseCounter.counter_date <'=>$endDatePlusOne,
    ];
    $hoseCounters=$this->HoseCounter->find('all',[
      'fields'=>[
        'counter_date',
        'hose_id',
        'counter_value',
      ],
      'conditions'=>$hoseCounterConditions,
      'order'=>'counter_date DESC',
      'group'=>'HoseCounter.counter_date, HoseCounter.hose_id'
    ]);
    //pr($hoseCounters);
   
    if (!empty($hoseCounters)){
      foreach ($hoseCounters as $hoseCounter){
        $hoseId=$hoseCounter['HoseCounter']['hose_id'];
        $counterDate=$hoseCounter['HoseCounter']['counter_date'];
        
        $measurementsArray['GrandTotal']['counter_value']+=$hoseCounter['HoseCounter']['counter_value'];
        $measurementsArray['Measurement'][$counterDate]['DayTotal']['counter_value']+=$hoseCounter['HoseCounter']['counter_value'];
        $measurementsArray['Measurement'][$counterDate]['Hose'][$hoseId]['counter_value']=$hoseCounter['HoseCounter']['counter_value'];
      }
    }
    
    $hoseCounterDayBeforeConditions=[
      'HoseCounter.counter_date >='=>$startDateMinusOne,
      'HoseCounter.counter_date <'=>$endDate,
    ];
    $hoseCountersDayBefore=$this->HoseCounter->find('all',[
      'fields'=>[
        'counter_date',
        'hose_id',
        'counter_value',
      ],
      'conditions'=>$hoseCounterDayBeforeConditions,
      'order'=>'counter_date DESC',
      'group'=>'HoseCounter.counter_date, HoseCounter.hose_id'
    ]);
    //pr($hoseCounters);
   
    if (!empty($hoseCountersDayBefore)){
      foreach ($hoseCountersDayBefore as $hoseCounterDayBefore){
        $hoseId=$hoseCounterDayBefore['HoseCounter']['hose_id'];
        $counterDateDayBefore=$hoseCounterDayBefore['HoseCounter']['counter_date'];
        $counterDate=date( "Y-m-d", strtotime($counterDateDayBefore."+1 days" ) );
        
        $measurementsArray['GrandTotal']['counter_value']-=$hoseCounterDayBefore['HoseCounter']['counter_value'];
        $measurementsArray['Measurement'][$counterDate]['DayTotal']['counter_value']-=$hoseCounterDayBefore['HoseCounter']['counter_value'];
        $measurementsArray['Measurement'][$counterDate]['Hose'][$hoseId]['counter_value']-=$hoseCounterDayBefore['HoseCounter']['counter_value'];
      }
    }
    
    $this->set(compact('measurementsArray'));
    
    $currentDate=$endDate;
    /*
    while ($currentDate>$startDate){
      foreach ($hoses as $hoseData){
        $hoseId=$hoseData['Hose']['id'];
        $hoseMeasurementDifference=$this->HoseMeasurement->getHoseMeasurementDifference($hoseId,$currentDate);
        if ($currentDate == $startDate){
          //pr($hoseMeasurementDifference);
        }
        $measurementsArray['GrandTotal']['measurement_value']+=$hoseMeasurementDifference['hoseMeasurementDifference'];
        $measurementsArray['Measurement'][$currentDate]['DayTotal']['measurement_value']+=$hoseMeasurementDifference['hoseMeasurementDifference'];
        $measurementsArray['Measurement'][$currentDate]['Hose'][$hoseId]['measurement_value']=$hoseMeasurementDifference['hoseMeasurementDifference'];
        
        $currentDate=date( "Y-m-d", strtotime( $currentDate."-1 days" ) );
        
      }  
    }
    */
  }
  
  public function guardarReporteMedidasMangueras($fileName){
    $exportData=$_SESSION['reporteMedidasMangueras'];
    $this->set(compact('exportData','fileName'));
  }
}
