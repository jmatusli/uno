<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class OrdersController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setduedate','imprimirVenta','guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones','guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones','guardarResumenComprasRealizadas','verPdfEntrada','verPdfEntradaSuministros','verPdfVenta','verPdfRemision','sortByTotalForClient','guardarReporteCierre','guardarReporteVentasCliente','reporteVentas','guardarReporteVentas');		
	}
  
  public function reporteVentas() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('StockMovement');
    $this->loadModel('Product');   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		$this->loadModel('Currency');
		
		$this->loadModel('Hose');
    $this->loadModel('HoseCounter');
    $this->loadModel('Island');
    $this->loadModel('Operator');
		$this->loadModel('Shift');
    
    $this->loadModel('PaymentReceipt');
    
    $this->Order->recursive=-1;
		$this->Product->recursive=-1;
    $this->StockMovement->recursive=-1;
		
    define('SALES_ALL','0');
    define('SALES_ISLAND','1');
    define('SALES_HOSE','2');
    define('SALES_OPERATOR','3');
    
    $displayOptions=[
			SALES_ALL=>'Mostrar todas ventas',
			SALES_ISLAND=>'Mostrar ventas por isla',
      SALES_HOSE=>'Mostrar ventas por manguera',
      SALES_OPERATOR=>'Mostrar ventas por operador',
		];
		$this->set(compact('displayOptions'));
		$displayOptionId=0;
    
    //$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $startDate = null;
		$endDate = null;
    
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
      
      $displayOptionId=$this->request->data['Report']['display_option_id'];
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
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
    $this->set(compact('displayOptionId'));
    
    $startDateTime=new DateTime($startDate);
    $endDateTime=new DateTime($endDate);
    $daysInMonth=0;
    if (date_format($startDateTime,"n") == date_format($endDateTime,"n") && date_format($startDateTime,"Y") == date_format($endDateTime,"Y")){
      $daysInMonth = cal_days_in_month(CAL_GREGORIAN, date_format($startDateTime,"n"), date_format($startDateTime,"Y"));   
    }
    //echo "days in month is ".$daysInMonth."<br/>";
    $this->set(compact('daysInMonth'));
		
    /*
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
          'Currency',
        ],
        'StockItem',
      ],  
      'order'=>'Product.product_order ASC',
    ]);
    $this->set(compact('fuels'));
    //pr($fuels);
    */
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    
    $this->set(compact('enterprises'));
    
    $fuels=$this->Product->find('list',[
      'fields'=>['Product.id','Product.name'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    $this->set(compact('fuels'));
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    
    $fuelArray=[];
    $fuelArray[0]=[
      'quantity_gallons'=>0,
      'price'=>0,
    ];
    foreach ($fuelIds as $fuelId){
      $fuelArray[$fuelId]=[
        'quantity_gallons'=>0,
        'price'=>0,
      ];
    }
    //pr($fuelArray);
    
    $classifiers=[
      'type'=>'',
      'enum'=>[],
    ];
    switch ($displayOptionId){
      case SALES_ISLAND:
        $classifiers['type']='Island';
        
        $enumerator=$this->Island->find('list',[
          'fields'=>['Island.id','Island.name'],
          'conditions'=>[
            'Island.enterprise_id'=>$enterpriseId,
            'Island.bool_active'=>true,
          ],
          'order'=>'Island.name ASC',
        ]);
        break;
      case SALES_HOSE:
        $classifiers['type']='Hose';
        
        $islandIds=$this->Island->find('list',[
          'fields'=>['Island.id'],
          'conditions'=>[
            'Island.enterprise_id'=>$enterpriseId,
          ],
        ]);
        $enumerator=$this->Hose->find('list',[
          'fields'=>['Hose.id','Hose.name'],
          'conditions'=>[
            'Hose.island_id'=>$islandIds,
            'Hose.bool_active'=>true,
          ],
          'order'=>'Hose.name ASC',
        ]);
        break;
      case SALES_OPERATOR:
        $classifiers['type']='Operator';
        $enumerator=$this->Operator->find('list',[
          'fields'=>['Operator.id','Operator.name'],
          'conditions'=>[
            'Operator.enterprise_id'=>$enterpriseId,
            'Operator.bool_active'=>true,
          ],
          'order'=>'Operator.name ASC',
        ]);
        break;
      case SALES_ALL:
      default:
        $classifiers['type']='All';
        $enumerator=['0'=>'All'];
    }    
    foreach ($enumerator as $enumId=>$enumName){
      $classifiers['enum'][$enumId]=['title_name'=>$enumName];
    }
    
    //pr($classifiers);
    $originalSalesArray=[];
    /*
    $currentDate=$startDate;
    while ($currentDate<$endDatePlusOne){
      $salesArray[$currentDate]['Fuel']=$fuelArray;
      $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
    }
    //pr($salesArray);
    */
    $currentDate=$endDate;
    $startDateMinusOne=date( "Y-m-d", strtotime($startDate."-1 days"));
    while ($currentDate>$startDateMinusOne){
      $originalSalesArray[$currentDate]['Fuel']=$fuelArray;
      $currentDate=date( "Y-m-d", strtotime( $currentDate."-1 days" ) );
    }
    //pr($salesArray);
    
    foreach ($classifiers['enum'] as $enumId => $enumName){
      $salesArray=$originalSalesArray;  
      $stockMovementConditions=[
        'StockMovement.movement_date >='=>$startDate,
        'StockMovement.movement_date <'=>$endDatePlusOne,
        'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE],
        'StockMovement.product_id'=>$fuelIds,
        'StockMovement.enterprise_id'=>$enterpriseId,
      ];
      switch ($displayOptionId){
        case SALES_ISLAND:
          $hoseIds=$this->Hose->find('list',[
            'fields'=>['Hose.id'],
            'conditions'=>['Hose.island_id'=>$enumId]
          ]);
          $stockMovementConditions['StockMovement.hose_id']=$hoseIds;
          break;
        case SALES_HOSE:
          $stockMovementConditions['StockMovement.hose_id']=$enumId;
          break;
        case SALES_OPERATOR:
          $stockMovementConditions['StockMovement.operator_id']=$enumId;
          break;
        case SALES_ALL:
        default:
          $break;
      }
      
      $this->StockMovement->virtualFields['sale_date']="";
      $this->StockMovement->virtualFields['total_product_quantity']=0;
      $this->StockMovement->virtualFields['total_product_price']=0;
      $stockMovements=$this->StockMovement->find('all',[
        'fields'=>[
          'date(movement_date) AS StockMovement__sale_date',
          'product_id',
          'SUM(product_quantity) AS StockMovement__total_product_quantity',
          'SUM(product_total_price) AS StockMovement__total_product_price'
        ],
        'conditions'=>$stockMovementConditions,
        'order'=>'movement_date DESC',
        'group'=>'movement_date, product_id'
      ]);
      
      //pr($stockMovements);
      if (!empty($stockMovements)){
        foreach ($stockMovements as $stockMovement){
          $saleDate=$stockMovement['StockMovement']['sale_date'];
          $productId=$stockMovement['StockMovement']['product_id'];
          //$salesArray[$saleDate]['Fuel'][$productId]['quantity_liters']=$stockMovement['StockMovement']['total_product_quantity']*GALLONS_TO_LITERS;
          $salesArray[$saleDate]['Fuel'][$productId]['quantity_gallons']=$stockMovement['StockMovement']['total_product_quantity'];
          $salesArray[$saleDate]['Fuel'][$productId]['price']=$stockMovement['StockMovement']['total_product_price'];
          $salesArray[$saleDate]['Fuel'][0]['quantity_gallons']+=$stockMovement['StockMovement']['total_product_quantity'];
          $salesArray[$saleDate]['Fuel'][0]['price']+=$stockMovement['StockMovement']['total_product_price'];
        }
      }
      //pr($salesArray);
      
      $calibrationMovementConditions=[
        'StockMovement.movement_date >='=>$startDate,
        'StockMovement.movement_date <'=>$endDatePlusOne,
        'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_CALIBRATION],
        'StockMovement.product_id'=>$fuelIds,
        'StockMovement.enterprise_id'=>$enterpriseId,
      ];
      $this->StockMovement->virtualFields['sale_date']="";
      $this->StockMovement->virtualFields['total_product_quantity']=0;
      $this->StockMovement->virtualFields['total_product_price']=0;
      $calibrationMovements=$this->StockMovement->find('all',[
        'fields'=>[
          'date(movement_date) AS StockMovement__sale_date',
          'product_id',
          'SUM(product_quantity) AS StockMovement__total_product_quantity',
          'SUM(product_total_price) AS StockMovement__total_product_price'
        ],
        'conditions'=>$calibrationMovementConditions,
        'order'=>'movement_date DESC',
        'group'=>'movement_date, product_id'
      ]);
      
      if ($classifiers['type'] == 'All'){
        //echo 'calibrations taken into account';
        if (!empty($calibrationMovements)){
          foreach ($calibrationMovements as $calibrationMovement){
            $saleDate=$calibrationMovement['StockMovement']['sale_date'];
            $productId=$calibrationMovement['StockMovement']['product_id'];
            //$salesArray[$saleDate]['Fuel'][$productId]['quantity_liters']=$stockMovement['StockMovement']['total_product_quantity']*GALLONS_TO_LITERS;
            $salesArray[$saleDate]['Fuel'][$productId]['quantity_gallons']-=$calibrationMovement['StockMovement']['total_product_quantity'];
            $salesArray[$saleDate]['Fuel'][$productId]['price']-=$calibrationMovement['StockMovement']['total_product_price'];
            $salesArray[$saleDate]['Fuel'][0]['quantity_gallons']-=$calibrationMovement['StockMovement']['total_product_quantity'];
            $salesArray[$saleDate]['Fuel'][0]['price']-=$calibrationMovement['StockMovement']['total_product_price'];
          }
        }
      }
      
      $paymentReceiptConditions=[
        'PaymentReceipt.payment_date >='=>$startDate,
        'PaymentReceipt.payment_date <'=>$endDatePlusOne,
        'PaymentReceipt.enterprise_id'=>$enterpriseId,
        // 20200119 PROCESS ALL PAYMENT MODES
        //'PaymentReceipt.payment_mode_id'=>PAYMENT_MODE_CREDIT,      
        
      ];
      
      if ($classifiers['type'] == 'Operator'){
        $paymentReceiptConditions['PaymentReceipt.operator_id']=$enumId;
      }
      //pr($paymentReceiptConditions);
      if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
        $this->PaymentReceipt->virtualFields['sale_date']="";
        $this->PaymentReceipt->virtualFields['total_payment']=0;
        $paymentReceipts=$this->PaymentReceipt->find('all',[
          'fields'=>[
            'date(payment_date) AS PaymentReceipt__sale_date',
            // 20200119 ADDED PAYMENT MODE AND CURRENCY
            'payment_mode_id',
            'currency_id',
            'SUM(payment_amount) AS PaymentReceipt__total_payment',
          ],
          'conditions'=>$paymentReceiptConditions,
          'order'=>'payment_date DESC',
          'group'=>['payment_date','payment_mode_id','currency_id'],
        ]);
        //pr($paymentReceipts);
      }
      
      
      if (!empty($paymentReceipts)){
        foreach (array_keys($salesArray) as $saleDate){
          //pr($saleDate);
          $totalCashCs=0;
          $totalCashUsd=0;
          $totalCredit=0;
          $totalCardBac=0;
          $totalCardBanpro=0;
          
          foreach ($paymentReceipts as $paymentReceipt){
            if ($paymentReceipt['PaymentReceipt']['sale_date'] == $saleDate){
              switch ($paymentReceipt['PaymentReceipt']['payment_mode_id']){
                case PAYMENT_MODE_CASH:
                  if ($paymentReceipt['PaymentReceipt']['currency_id'] == CURRENCY_CS){
                    $totalCashCs+=$paymentReceipt['PaymentReceipt']['total_payment'];
                  }
                  else {
                    $totalCashUsd+=$paymentReceipt['PaymentReceipt']['total_payment'];
                  }
                  break;
                case  PAYMENT_MODE_CREDIT:
                  $totalCredit+=$paymentReceipt['PaymentReceipt']['total_payment'];
                  break;
                case PAYMENT_MODE_CARD_BAC:
                  $totalCardBac+=$paymentReceipt['PaymentReceipt']['total_payment'];
                  break;
                case PAYMENT_MODE_CARD_BANPRO:
                  $totalCardBanpro+=$paymentReceipt['PaymentReceipt']['total_payment'];
                  break;
                default:
                  break;  
                
              }
              $salesArray[$saleDate]['Credit']['total_credit']=$paymentReceipt['PaymentReceipt']['total_payment'];
            }
          }
          $salesArray[$saleDate]['exchange_rate_usd']=$this->ExchangeRate->getApplicableExchangeRateValue($saleDate,CURRENCY_USD);
          $salesArray[$saleDate]['Cash']['total_cash_cs']=$totalCashCs;
          $salesArray[$saleDate]['Cash']['total_cash_usd']=$totalCashUsd;
          $salesArray[$saleDate]['Credit']['total_credit']=$totalCredit;
          $salesArray[$saleDate]['Card']['total_bac']=$totalCardBac;
          $salesArray[$saleDate]['Card']['total_banpro']=$totalCardBanpro;
        }
        
      }
      //pr($salesArray);
      foreach (array_keys($salesArray) as $saleDate){
        $totalCost=0;
        foreach ($salesArray[$saleDate]['Fuel'] as $fuelId=>$fuelData){
          if ($fuelId!=0){
            $salesArray[$saleDate]['Fuel'][$fuelId]['cost']=$fuelData['quantity_gallons']*$this->StockItemLog->getProductCost($fuelId,$saleDate,$enterpriseId);  
          }
        }  
      }  
      //$this->set(compact('salesArray'));
      
      $classifiers['enum'][$enumId]['sales']=$salesArray;
    }
    $this->set(compact('classifiers'));  

    $fuelUtilities=[];
    foreach ($fuels as $fuelId=>$fuelName){
      $fuelUtilities[$fuelId]=[
        'name'=>$fuelName,
        'price'=>0,
        'cost'=>0,
      ];
    }
    foreach ($classifiers['enum'] as $enumId=>$enumData){
      foreach ($enumData['sales'] as $saleDate=>$saleData){
        foreach ($saleData['Fuel'] as $fuelId=>$fuelData){
          if ($fuelId!=0){
            //pr($fuelData);
            $fuelUtilities[$fuelId]['price']+=$fuelData['price'];
            $fuelUtilities[$fuelId]['cost']+=$fuelData['cost'];
          }
        }
      }
    }
    //pr($fuelUtilities);
    $this->set(compact('fuelUtilities'));
    
    //$this->set(compact('paymentReceipts'));
    /*    
    $operators=$this->Operator->find('list',[
			'conditions'=>['Operator.bool_active'=>true],
      'order'=>'Operator.name',
		]);
		$this->set(compact('operators'));
    
    $shifts=$this->Shift->find('list',[
			'order'=>'Shift.name',
		]);
		$this->set(compact('shifts'));
    
    $allHoses=$this->Hose->find('all',[
      'conditions'=>['Hose.bool_active'=>true],
      'contain'=>[
        'HoseCounter'=>[
          'conditions'=>['DATE(counter_date) < '=>$saleDate],
          'order'=>'shift_id DESC, counter_date DESC'
        ],
      ],
		]);
    $hoses=$this->Hose->find('list',[
      'conditions'=>['Hose.bool_active'=>true],
		]);
    $islands=$this->Island->find('list',[
      'conditions'=>['Island.bool_active'=>true],
		]);
    */
    /*
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
    */
	}

  public function guardarReporteVentas($fileName){
    $exportData=$_SESSION['reporteVentas'];
    $this->set(compact('exportData','fileName'));
  }

  public function registrarVentas() {
		$this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('Product');   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		$this->loadModel('Currency');
		
    $this->loadModel('Hose');
    $this->loadModel('HoseCounter');
    $this->loadModel('Island');
     $this->loadModel('Operator');
		$this->loadModel('Shift');
    
    $this->Order->recursive=-1;
		$this->Product->recursive=-1;
    $this->StockMovement->recursive=-1;
		
    //$inventoryDisplayOptions=array(
		//	'0'=>'No mostrar inventario',
		//	'1'=>'Mostrar inventario',
		//);
		//$this->set(compact('inventoryDisplayOptions'));
		//$inventoryDisplayOptionId=0;
    
		$saleDate = null;
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$saleDateArray=$this->request->data['Order']['order_date'];
			$saleDateAsString=$saleDateArray['year'].'-'.$saleDateArray['month'].'-'.$saleDateArray['day'];
			$saleDate=date( "Y-m-d", strtotime($saleDateAsString));
      
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
		}
    elseif (!empty($_SESSION['saleDate'])){
      //pr($_SESSION['saleDate']);
			$saleDate = date( "Y-m-d", strtotime( $_SESSION['saleDate']) );
      $saleDateAsString=$_SESSION['saleDate'];
      //echo "from session";
		}
		else {
			$saleDate = date("Y-m-d");
      $saleDate= date( "Y-m-d", strtotime( $saleDate."-1 days" ) );
      //echo "from default";
		}
		
    
    $_SESSION['saleDate']=$saleDate;
    
    $saleDateAsString=$saleDate;
    $saleDatePlusOne= date( "Y-m-d", strtotime( $saleDate."+1 days" ) );
    //pr($saleDatePlusOne);
    $saleDateTimePlusOne=new DateTime($saleDatePlusOne);
    //pr($saleDateTimePlusOne);
    
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    //echo 'enterprise id is '.$enterpriseId.'<br/>';
    $productPriceLogConditions=[
      'ProductPriceLog.price_datetime <'=>$saleDatePlusOne,
      'ProductPriceLog.enterprise_id'=>$enterpriseId,
    ];
    //pr($productPriceLogConditions);
    $fuels=$this->Product->find('all',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'contain'=>[
        'DefaultPriceCurrency',
        'ProductPriceLog'=>[
          'conditions'=>$productPriceLogConditions,
          'order'=>'price_datetime DESC',
          'limit'=>1,
          'Currency',
        ],
        'StockItem',
      ],  
      'order'=>'Product.product_order ASC',
    ]);
    $this->set(compact('fuels'));
    //pr($fuels);
    
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    
    $operators=$this->Operator->find('list',[
			'conditions'=>[
        'Operator.bool_active'=>true,
        'Operator.enterprise_id'=>$enterpriseId,
      ],
      'order'=>'Operator.name',
		]);
		$this->set(compact('operators'));
    
    $shifts=$this->Shift->find('list',[
      'conditions'=>[
        'Shift.bool_active'=>true,
        'Shift.enterprise_id'=>$enterpriseId,
      ],
			'order'=>'Shift.name',
		]);
		$this->set(compact('shifts'));
    	
    $hoseConditions=[
      'Hose.bool_active'=>true,
      'Hose.enterprise_id'=>$enterpriseId,
    ];  
    $allHoses=$this->Hose->find('all',[
      'conditions'=>$hoseConditions,
      'contain'=>[
        'HoseCounter'=>[
          'conditions'=>['DATE(counter_date) < '=>$saleDate],
          'order'=>'shift_id DESC, counter_date DESC'
        ],
      ],
		]);  
    $hoses=$this->Hose->find('list',[
      'conditions'=>$hoseConditions,
		]);  
      
    $islands=$this->Island->find('list',[
      'conditions'=>[
        'Island.bool_active'=>true,
        'Island.enterprise_id'=>$enterpriseId,
      ],
		]);  
      
		$requestShifts=[];
    $requestLubricants=[];
    $boolEditingMode=false;
    $boolEditingToggleVisible=false;
    
    $operatorConfigurationOptions=[
      1=>2,
      2=>1,
      3=>1,
    ];
    $shiftOperatorConfigs=[];
		if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	
      //pr($this->request->data);
			$saleDateArray=$this->request->data['Order']['order_date'];
      //pr($saleDateArray);
      $saleDateAsString=$saleDateArray['year'].'-'.$saleDateArray['month'].'-'.$saleDateArray['day'];
      $saleDate=date( "Y-m-d", strtotime($saleDateAsString));
      $saleDatePlusOne=date("Y-m-d",strtotime($saleDateAsString."+1 days"));
     
      foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
        $requestShifts['Shift'][$shiftId]=$shiftData;
      }
      
      foreach ($this->request->data['Lubricant'] as $lubricant){
        if (!empty($lubricant['lubricant_id'])){
          $requestLubricants['Lubricant'][]=$lubricant;
        }
      }
      
      foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
        //pr($shiftData);
        if (!empty($shiftData['OperatorConfiguration'])){
          $operatorConfigurationOptions[$shiftId]=$shiftData['OperatorConfiguration'];
        }
      }
      //pr($operatorConfigurationOptions);
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDateTime=new DateTime($latestClosingDate);
      
      $pricesOk=true;
      $latestFuelProductPriceLog=$this->ProductPriceLog->getLatestFuelProductPriceLog($enterpriseId);
      $productPriceDateTime=new DateTime(date('Y-m-d',strtotime($latestFuelProductPriceLog['ProductPriceLog']['price_datetime'])));
      $saleDateTime= new DateTime(date('Y-m-d', strtotime($saleDate)));
      $daysPassed=$productPriceDateTime->diff($saleDateTime);
      $daysSinceLastFuelPriceUpdate=$daysPassed->format('%r%a');
      $weekDay=date('w',strtotime($saleDate));
      if ($weekDay != 0 && $daysSinceLastFuelPriceUpdate>5){
        $pricesOk=false;
      }
                
      //$orderCode=$this->request->data['Order']['order_code'];
      /*
      $namedSales=$this->Order->find('all',array(
        'conditions'=>array(
          'order_code'=>$orderCode,
          'stock_movement_type_id'=>MOVEMENT_SALE,
        )
      ));
      if (count($namedSales)>0){
        $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
      }
      else
      */
      if ($saleDateAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de las ventas no puede estar en el futuro!  No se guardaron las ventas.'), 'default',['class' => 'error-message']);
      }
      elseif ($saleDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardaron las ventas.'), 'default',['class' => 'error-message']);
      }
      
      elseif ($this->request->data['Order']['total_price']==0){
        $this->Session->setFlash(__('El precio total no puede ser cero para una venta que no está anulada!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif (!$pricesOk){
        $this->Session->setFlash(__('No se pueden grabar las ventas con precios viejos, registra nuevos precios primero.  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else {
        // before moving into the selling part, perform inventory check
        // test 1 check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
        //pr($this->request->data);
        
        $fuelsOK=true;
        $exceedingFuels="";
        
        $operatorsOK=true;
        $operatorsWarning="";
        
        $counterDifferenceOk=true;
        $counterDifferenceWarning="";
        
        $fuelMultiplicationOk=true;
        $fuelMultiplicationWarning="";
        
        $fuelTotalSumBasedOnFuelTotals=0;
        
        $calibrationsOk=true;
        $calibrationsWarning='';
        
        $netFuelsOk=true;
        $netFuelsWarning='';
        
        $netFuelPricesOk=true;
        $netFuelPricesWarning='';
        
        $fuelTotals=[];
        $calibrationTotals=[];
        $netFuelTotals=[];
        $netFuelPriceTotals=[];
        foreach ($fuels as $fuel){
          $fuelTotals[$fuel['Product']['id']]=0;
          $calibrationTotals[$fuel['Product']['id']]=0;
          $netFuelTotals[$fuel['Product']['id']]=0;
          $netFuelPriceTotals[$fuel['Product']['id']]=0;
        }
        $emptyFuelTotals=$fuelTotals;
        // first sum the fuel totals
        foreach ($this->request->data['Shift'] as $shift){
          //pr($shift);
          foreach ($shift['Island'] as $islandId => $islandData){
            foreach ($islandData['Hose'] as $hose) {
              //pr($hose);
              if ($hose['quantity']>0 && $hose['fuel_id']>0){
                //echo "fuel quantity is ".$hose['quantity']."<br/>";
                $fuelTotals[$hose['fuel_id']]+=$hose['quantity'];
              }
            }
          }
          foreach ($shift['Calibration'] as $fuelId =>$fuelCalibration){
            $calibrationTotals[$fuelId]+=$fuelCalibration;
          }   
          foreach ($shift['NetFuelTotal'] as $fuelId =>$netFuel){
            $netFuelTotals[$fuelId]+=$netFuel;
          }   
          foreach ($shift['NetFuelTotalPrice'] as $fuelId =>$netFuelPrice){
            $netFuelPriceTotals[$fuelId]+=$netFuelPrice;
          }                
        }
        //pr($fuelTotals);        
        $sumNetFuelPrices=0;
        foreach ($fuelTotals as $fuelId=>$fuelTotal){
          $this->Product->recursive=-1;
          $relatedProduct=$this->Product->find('first',[
            'conditions'=>['Product.id'=>$fuelId,],
          ]);
          $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFuel($fuelId,UNIT_LITERS,$saleDateAsString,$enterpriseId,true);
          //echo "fuel total is ".$fuelTotal."<br/>";
          //echo "quantity in stock is ".$quantityInStock."<br>";
          if ($fuelTotal>$quantityInStock){
            //echo "fuelsOK is false <br/>";
            $fuelsOK=false;
            $exceedingFuels.=__("Para combustible ".$relatedProduct['Product']['name']." la cantidad requerida (".$fuelTotal.") excede la cantidad disponible en bodega (".$quantityInStock.")!")."<br/>";
          }
        
          if ($calibrationTotals[$fuelId]>$fuelTotal){
            $calibrationsOk=false;
            $calibrationsWarning.="Para combustible ".$relatedProduct['Product']['name']." se registró un total de ventas de ".$fuelTotal." pero un total de calibración de ".$calibrationTotals[$fuelId]."!  ";
          }
          
          if (abs($fuelTotal-$calibrationTotals[$fuelId]-$netFuelTotals[$fuelId])>0.02){
            $netFuelsOk=false;
            $netFuelsWarning.="Para combustible ".$relatedProduct['Product']['name']." se registró un total de ventas de ".$fuelTotal." y una calibración de ".$calibrationTotals[$fuelId].", pero la diferencia da ".$netFuelTotals[$fuelId]."!  ";
          }
          $sumNetFuelPrices+=$netFuelPriceTotals[$fuelId];            
        }
        if (abs($sumNetFuelPrices - $this->request->data['Order']['total_net_price_fuels'])>0.02){
          $netFuelPricesOk=false;
          $netFuelPricesWarning='La suma de los precios netos es '.$sumNetFuelPrices.' pero el precio total neto está marcado como '.$this->request->data['Order']['total_net_price_fuels'].'!  No se guardaron las ventas';
        }
        
        //echo "final fuelsOK is ".$fuelsOK." <br/>";
        if (!$fuelsOK){ 
          $exceedingFuels.=__("Please correct and try again!");
        }
        else {
          // additional controls.
          // test 1 check if all operators are present when there are sales registered
          // test 2 for all fuels check if counter final - counter initial = quantity sold
          // test 3 for all fuels check if unit price * quantity sold = total price per row
          // test 4 for all fuels check if sum of all total prices = grand total
          
          foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
            //pr($shift);
            $salesPresent=false;
            $operatorMissing=false;
            foreach ($shiftData['Island'] as $islandId => $islandData){
              foreach ($islandData['Hose'] as $hoseId=>$hose) {
                //pr($hose);
                //if ($hose['quantity']>0 && $hose['fuel_id']>0){
                if ($hose['final']>0 && $hose['fuel_id']>0){
                  if (empty($hose['operator_id'])){
                    $operatorMissing=true;
                  }
                  
                  $counterDifference=$hose['final']-$hose['initial'];
                  if ($counterDifference > 0.001){
                    $salesPresent=true;
                  }
                  if (abs($counterDifference - $hose['quantity']) >0.001){
                    $counterDifferenceOk=false;
                    $counterDifferenceWarning.="Combustible ".$relatedProduct['Product']['name']." en manguera ".$hoses[$hoseId]." tiene un contador de cierre de ".$hose['final']." y un cantidad inicial de ".$hose['initial'].", así que la diferencia ".$counterDifference." no corresponde con la cantidad de litros ".$hose['quantity'].";";  
                  }
                  //echo "fuel quantity is ".$hose['quantity']."<br/>";
                  $fuelMultiplicationBasedOnUnitPriceAndQuantity=$hose['quantity']*$hose['product_unit_price'];
                  $fuelMultiplicationBasedOnTotalPrice=$hose['price'];
                  if (abs($fuelMultiplicationBasedOnUnitPriceAndQuantity-$fuelMultiplicationBasedOnTotalPrice) > 0.01){
                    $fuelMultiplicationOk=false;
                    $fuelMultiplicationWarning.="Combustible ".$relatedProduct['Product']['name']." en manguera ".$hoses[$hoseId]." tiene una cantidad ".$hose['quantity']." y un precio unitario ".$hose['product_unit_price'].", pero el total calculado ".$hose['price']." no es correcto;";
                  }
                  //echo "product total price is ".$hose['product_total_price']."<br/>";
                  $fuelTotalSumBasedOnFuelTotals+=$hose['price'];
                }
              }
            }
            if ($salesPresent && $operatorMissing){
              $operatorsOK=false;
              $operatorsWarning.="Por favor especifique los operadores para turno ".$shifts[$shiftId]."!";
            }
          }
        }
   
        // test 11 for all lubricants check if unit price * quantity sold = total price per row
        // test 12 for all lubricants check if sum of all total prices = grand total
        $lubricantsOK=true;
        $exceedingLubricants="";
        $lubricantMultiplicationOk=true;
        $lubricantMultiplicationWarning="";
        $lubricantTotalSumBasedOnLubricantTotals=0;
        
        //$lubricantCount=0;
        $lubricantProducts=[];
        foreach ($this->request->data['Lubricant'] as $lubricant){
          //pr($lubricant);
          // keep track of number of rows so that in case of an error jquery displays correct number of rows again
          //if ($lubricant['lubricant_id']>0){
          //  $lubricantCount++;
          //}
          // only process lines where lubricant_quantity and product id have been filled out
          
          if ($lubricant['lubricant_quantity']>0 && $lubricant['lubricant_id']>0){
            //echo "product quantity is ".$lubricant['lubricant_quantity']."<br/>";
            $lubricantProducts[]=$lubricant;
            $quantityEntered=$lubricant['lubricant_quantity'];
            $lubricantId = $lubricant['lubricant_id'];
            
            $this->Product->recursive=-1;
            $relatedProduct=$this->Product->find('first',[
              'conditions'=>['Product.id'=>$lubricantId,],
            ]);
            $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($lubricantId,$saleDateAsString,$enterpriseId,true);
            //echo "quantity in stock is ".$quantityInStock."<br>";
              
            //compare the quantity requested and the quantity in stock
            if ($quantityEntered>$quantityInStock){
              $lubricantsOK=false;
              $exceedingLubricants.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad disponible en bodega (".$quantityInStock.")!")."<br/>";
            }
            
            $lubricantMultiplicationBasedOnUnitPriceAndQuantity=$lubricant['lubricant_quantity']*$lubricant['lubricant_unit_price'];
            $lubricantMultiplicationBasedOnTotalPrice=$lubricant['lubricant_total_price'];
            if (abs($lubricantMultiplicationBasedOnUnitPriceAndQuantity-$lubricantMultiplicationBasedOnTotalPrice) > 0.01){
              $lubricantMultiplicationOk=false;
              $lubricantMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$lubricant['lubricant_quantity']." y un precio unitario ".$lubricant['lubricant_unit_price'].", pero el total calculado ".$lubricant['lubricant_total_price']." no es correcto;";
            }
            //echo "product total price is ".$lubricant['lubricant_total_price']."<br/>";
            $lubricantTotalSumBasedOnLubricantTotals+=$lubricant['lubricant_total_price'];
          }
        }
        if ($exceedingLubricants!=""){ 
          $exceedingLubricants.=__("Please correct and try again!");
        }             
        
        if (!$fuelsOK){
          $this->Session->setFlash(__('La cantidad disponible no es suficiente.')."<br/>".$exceedingFuels, 'default',['class' => 'error-message']);
        }
        if (!$operatorsOK){
          $this->Session->setFlash($operatorsWarning.'  No se guardaron las ventas.', 'default',['class' => 'error-message']);
        }
        elseif (!$counterDifferenceOk){
          $this->Session->setFlash($counterDifferenceWarning.'.', 'default',['class' => 'error-message']);
        }
        elseif (!$fuelMultiplicationOk){
          $this->Session->setFlash($fuelMultiplicationWarning.'.', 'default',['class' => 'error-message']);
        }
        //elseif (abs($fuelTotalSumBasedOnFuelTotals-$this->request->data['Order']['total_price_fuels']) > 0.01){
        //  $this->Session->setFlash('Si se suman los totales de cada combustible se llega a '.$fuelTotalSumBasedOnFuelTotals.' pero el total calculado es '.$this->request->data['Order']['total_price_fuels'].'.Verifique que toda la información está correcta y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        //}
        elseif (!$calibrationsOk){
          $calibrationsWarning.="La calibración no puede ser mayor que la venta.  Por favor corregir esto y vuelve a intentar.  No se guardaron las ventas.";
          $this->Session->setFlash($calibrationsWarning, 'default',['class' => 'error-message']);
        }
        elseif (!$netFuelsOk){
          $netFuelsWarning.="La venta neta debe igual la venta total menos la calibración.  Por favor corregir esto y vuelve a intentar.  No se guardaron las ventas.";
          $this->Session->setFlash($netFuelsWarning, 'default',['class' => 'error-message']);
        }
        elseif (!$netFuelPricesOk){
          $this->Session->setFlash($netFuelPricesWarning, 'default',['class' => 'error-message']);
        }
        elseif (!$lubricantsOK){
          $this->Session->setFlash(__('La cantidad disponible no es suficiente.')."<br/>".$exceedingLubricants, 'default',['class' => 'error-message']);
        }
        elseif (!$lubricantMultiplicationOk){
          $this->Session->setFlash($lubricantMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($lubricantTotalSumBasedOnLubricantTotals-$this->request->data['Order']['total_price_lubricants']) >= 0.02){
          $this->Session->setFlash('Si se suman los totales de cada lubricante se llega a '.$lubricantTotalSumBasedOnLubricantTotals.' pero el total calculado es '.$this->request->data['Order']['total_price_lubricants'].'.  Verifique que ha seleccionado cada lubricante para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        /*
        elseif (abs($this->request->data['Order']['total_price_fuels']-$this->request->data['Order']['total_price_calibrations']-$this->request->data['Order']['total_net_price_fuels'])>=0.03){
          $this->Session->setFlash('El precio total de combustibles '.$this->request->data['Order']['total_price_fuels'].' menos el valor de las calibraciones '.$this->request->data['Order']['total_price_calibrations'].' no iguala el neto de combustibles '.$this->request->data['Order']['total_net_price_fuels'], 'default',['class' => 'error-message']);
        }
        */
        elseif (abs($this->request->data['Order']['total_net_price_fuels']+$this->request->data['Order']['total_price_lubricants']-$this->request->data['Order']['total_price'])>=0.02){
          $this->Session->setFlash('El precio neto de combustibles '.$this->request->data['Order']['total_net_price_fuels'].' más el valor de los lubricantes '.$this->request->data['Order']['total_price_lubricants'].' no iguala el precio total '.$this->request->data['Order']['total_price'], 'default',['class' => 'error-message']);
        }
        /*
        elseif (abs($this->request->data['Invoice']['total_price']-$this->request->data['Invoice']['IVA_price']-$this->request->data['Invoice']['sub_total_price'])>0.01){
          $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        */
        else{
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          
          $orderId=0;
          $order=$this->Order->find('first',[
            'conditions'=>[
              'DATE(Order.order_date)'=>$saleDate,
              'Order.stock_movement_type_id'=>MOVEMENT_SALE,
              'Order.enterprise_id'=>$enterpriseId,
            ],  
          ]);
          //pr($order);
          if (!empty($order)){
            $orderId=$order['Order']['id'];
          }
          //echo "order id is ".$orderId."<br/>";
          $obsoleteMovementsRemoved=false;
          try {
            //REMOVE PREVIOUS MOVEMENTS
            $previousMovementsForSaleDate=[];
            if ($orderId>0){
              $previousMovementsForSaleDate=$this->StockMovement->find('all',[
                'conditions'=>[
                  'DATE(StockMovement.movement_date)'=>$saleDate,
                  'StockMovement.order_id'=>$orderId,
                  //'StockMovement.enterprise_id'=>$enterpriseId, // not needed as it is tied to a specific order for a specific enterprise
                  //'StockMovement.bool_input'=>false,
                ],  
                'contain'=>['StockItem',],
              ]);
            }
            $stockItemsArray=[];
            if (!empty($previousMovementsForSaleDate)){
              foreach($previousMovementsForSaleDate as $previousMovement){
                //pr($previousMovement);
                $this->StockMovement->id=$previousMovement['StockMovement']['id'];
                if (!$this->StockMovement->delete($previousMovement['StockMovement']['id'])) {
                  echo "Problema guardando la venta.  No se podían eliminar los movimientos viejos.";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                $stockItemId=$previousMovement['StockItem']['id'];

                if (array_key_exists($stockItemId,$stockItemsArray)){
                  if ($previousMovement['StockMovement']['bool_input']){
                    $stockItemsArray[$stockItemId]['StockItem']['remaining_quantity']-=$previousMovement['StockMovement']['product_quantity'];
                  }
                  else {
                    //REMEMBER THAT WHILE WHEN SELLING PRODUCTS, THE UNIT COST IS NOT AFFECTED, WHEN PLACING THEM BACK THE UNIT COST NEEDS TO BE ADAPTED
                    $stockItemsArray[$stockItemId]['StockItem']['remaining_quantity']+=$previousMovement['StockMovement']['product_quantity'];
                    $stockItemsArray[$stockItemId]['StockItem']['product_unit_cost']=($stockItemsArray[$stockItemId]['StockItem']['remaining_quantity']* $stockItemsArray[$stockItemId]['StockItem']['product_unit_cost']+$previousMovement['StockMovement']['product_total_price'])/($stockItemsArray[$stockItemId]['StockItem']['remaining_quantity']+$previousMovement['StockMovement']['product_quantity']);
                  }
                }
                else {
                  $stockItemData=[];
                  if ($previousMovement['StockMovement']['bool_input']){
                    $stockItemData['StockItem']['remaining_quantity']=($previousMovement['StockItem']['remaining_quantity']-$previousMovement['StockMovement']['product_quantity']);
                    $stockItemData['StockItem']['product_unit_cost']=$previousMovement['StockItem']['product_unit_cost'];
                  }
                  else {
                    //REMEMBER THAT WHILE WHEN SELLING PRODUCTS, THE UNIT COST IS NOT AFFECTED, WHEN PLACING THEM BACK THE UNIT COST NEEDS TO BE ADAPTED
                    $stockItemData['StockItem']['remaining_quantity']=($previousMovement['StockItem']['remaining_quantity']+$previousMovement['StockMovement']['product_quantity']);
                    $stockItemData['StockItem']['product_unit_cost']=($previousMovement['StockItem']['remaining_quantity']*$previousMovement['StockItem']['product_unit_cost']+$previousMovement['StockMovement']['product_total_price'])/($previousMovement['StockItem']['remaining_quantity']+$previousMovement['StockMovement']['product_quantity']);
                  }
                  $stockItemsArray[$stockItemId]= $stockItemData;
                }
              }
              foreach ($stockItemsArray as $stockItemId=>$stockItemData){
                //pr($stockItemData);
                $this->StockItem->id=$stockItemId;
                if (!$this->StockItem->save($stockItemData)) {
                  echo "Problema al actualizar el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
              }
            }
          
            $previousHoseCountersForSaleDate=$this->HoseCounter->find('all',[
              'conditions'=>[
                'HoseCounter.counter_date'=>$saleDate,
                'HoseCounter.enterprise_id'=>$enterpriseId,
              ],  
            ]);
            if (!empty($previousHoseCountersForSaleDate)){
              foreach($previousHoseCountersForSaleDate as $previousHoseCounter){
                $this->HoseCounter->id=$previousHoseCounter['HoseCounter']['id'];
                if (!$this->HoseCounter->delete($previousHoseCounter['HoseCounter']['id'])) {
                  echo "Problema guardando la venta.  No se podían eliminar los contadores viejos.";
                  pr($this->validateErrors($this->HoseCounter));
                  throw new Exception();
                }
              }
            }
          
            $datasource->commit();
            $obsoleteMovementsRemoved=true;
          }
          catch(Exception $e){
            $this->Session->setFlash(__('Las ventas no se podían editar.'),'default',['class' => 'error-message']);
            pr($e);
            $datasource->rollback();
          }
          if ($obsoleteMovementsRemoved){
            try {
              $currencyId=$this->request->data['Order']['currency_id'];
            
              // if all products are in stock, proceed with the sale 
              $this->request->data['Order']['enterprise_id']=$enterpriseId;
              $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
              // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
              // PENDING SUPOOSED THAT ALL PRICES ARE IN C$
              //if ($currencyId==CURRENCY_USD){
              //  $this->request->data['Order']['total_price']*=$this->request->data['Order']['exchange_rate'];
              //}
              //pr($this->request->data);
              if ($orderId == 0){
                $this->Order->create();  
              }
              else {
                $this->Order->id=$orderId;  
              }
              
              if (!$this->Order->save($this->request->data)) {
                echo "Problema guardando la venta ";
                pr($this->validateErrors($this->Order));
                throw new Exception();
              }
            
              $orderId=$this->Order->id;            
              /*
              // now prepare the accounting registers
              // if the invoice is with credit, save one accounting register; 
              // debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
              // if the invoice is paid with cash, save two or three accounting register; 
              // debit=caja selected by client, credit = ingresos por venta 401, amount = total
              // debit=?, credit = ?, amount = iva
              // if bool_retention is true
              // debit=?, credit = ?, amount = retention

              if ($currencyId==CURRENCY_USD){
                $this->loadModel('ExchangeRate');
                $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
                //pr($applicableExchangeRate);
                $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $IVA_CS=round($IVA_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $total_CS=round($total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              }
              else {
                $retention_CS=$retention_invoice;
                $sub_total_CS=$sub_total_invoice;
                $IVA_CS=$IVA_invoice;
                $total_CS=$total_invoice;
              }
              $this->AccountingCode->recursive=-1;
              if ($this->request->data['Invoice']['bool_credit']){
                $client_id=$this->request->data['Order']['third_party_id'];
                $this->loadModel('ThirdParty');
                $this->ThirdParty->recursive=-1;
                $thisClient=$this->ThirdParty->find('first',array(
                  'conditions'=>array(
                    'ThirdParty.id'=>$client_id,
                  ),
                ));
              
                $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
                $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
                $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
                $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                
                if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
                    ),
                  ));
                }
                else {								
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id'],
                    ),
                  ));
                }
                $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
                
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                
                if ($this->request->data['Invoice']['bool_IVA']){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
                }
                
                //pr($accountingRegisterData);
                $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                $this->recordUserAction($this->AccountingRegister->id,"add",null);
            
                $AccountingRegisterInvoiceData=array();
                $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                $this->AccountingRegisterInvoice->create();
                if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                  pr($this->validateErrors($this->AccountingRegisterInvoice));
                  echo "problema al guardar el lazo entre asiento contable y factura";
                  throw new Exception();
                }
                //echo "link accounting register sale saved<br/>";					
              }
              else {
                $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
                $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
                $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
                $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                
                if (!$this->request->data['Invoice']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
                }
                else {
                  // with retention
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS-$retention_CS;
                }
                
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                
                if ($this->request->data['Invoice']['bool_IVA']){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
                }
                if ($this->request->data['Invoice']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR,
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
                }
                
                //pr($accountingRegisterData);
                $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                $this->recordUserAction($this->AccountingRegister->id,"add",null);
                //echo "accounting register saved for cuentas cobrar clientes<br/>";
            
                $AccountingRegisterInvoiceData=array();
                $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                $this->AccountingRegisterInvoice->create();
                if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                  pr($this->validateErrors($this->AccountingRegisterInvoice));
                  echo "problema al guardar el lazo entre asiento contable y factura";
                  throw new Exception();
                }
                //echo "link accounting register sale saved<br/>";	
              }
              */
              /*
              $hoseCounterArray=[];
              foreach ($allHoses as $hose){
                $hoseId=$hose['Hose']['id'];
                $hoseCounter=$hose['HoseCounter'][0]['counter_value'];
                $hoseCounterArray[$hoseId]=[0=>$hoseCounter];
                foreach (array_keys($shifts) as $shiftId){
                  $hoseCounterArray[$hoseId][$shiftId]=$hoseCounter;
                }
              }
              //echo "houseCounterArray";
              //pr($hoseCounterArray[1]);
              */
              $totalPriceFuels=0;
              //pr($fuelTotals);
              //echo "fuels";
              //pr($fuels);
              //foreach ($fuelTotals as $fuelId=>$fuelTotalQuantity){
              foreach ($netFuelTotals as $fuelId=>$fuelTotalQuantity){
                if ($fuelTotalQuantity>0){
                  $this->Product->recursive=-1;
                  $relatedProduct=$this->Product->find('first',[
                    'conditions'=>['Product.id'=>$fuelId,],
                  ]);
                  $fuelName=$relatedProduct['Product']['name'];
                  // PENDING THIS SHOULD BE REPLACED BY THE  CORRECT VALUE OF THE PRICE LOG
                  $fuelUnitPrice=$relatedProduct['Product']['default_price'];
                  $fuelQuantity=$fuelTotalQuantity/GALLONS_TO_LITERS;
                  $usedFuels= $this->StockItem->getMaterialsForSale($fuelId,$fuelQuantity,$saleDateAsString,$enterpriseId);		
                  //pr($usedFuels);
              
                  for ($k=0;$k<count($usedFuels);$k++){
                    $materialUsed=$usedFuels[$k];
                    $stockItemId=$materialUsed['id'];
                    //$quantityPresent=$materialUsed['quantity_present'];
                    //$quantityUsed=$materialUsed['quantity_used'];
                    $quantityRemaining=$materialUsed['quantity_remaining'];
                    if (!$this->StockItem->exists($stockItemId)) {
                      throw new NotFoundException(__('Invalid StockItem'));
                    }
                    
                    $stockItemData=[];
                    $stockItemData['id']=$stockItemId;
                    $stockItemData['remaining_quantity']=$quantityRemaining;
                    if (!$this->StockItem->save($stockItemData)) {
                      echo "problema al guardar el lote";
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                  }
                }
              }
              
              foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
                foreach ($shiftData['Island'] as $islandId => $islandData){
                  //pr($islandData);
                  
                    foreach ($islandData['Hose'] as $hoseId =>$hoseData) {
                      if ($hoseData['final']>0){                            
                        $hoseCounterData=[];
                        $hoseCounterData['counter_date']=$saleDate;
                        $hoseCounterData['enterprise_id']=$enterpriseId;
                        $hoseCounterData['shift_id']=$shiftId;
                        $hoseCounterData['hose_id']=$hoseId;
                        $hoseCounterData['operator_id']=$hoseData['operator_id'];
                        $hoseCounterData['counter_value']=$hoseData['final'];
                        $this->HoseCounter->create();
                        if (!$this->HoseCounter->save($hoseCounterData)) {
                          echo "problema al guardar el contador de manguera";
                          pr($this->validateErrors($this->HoseCounter));
                          throw new Exception();
                        }
                        
                        //pr($hoseData);
                        $fuelId=$hoseData['fuel_id'];
                        $fuelQuantity=$hoseData['quantity'];
                        foreach ($fuels as $fuel){
                          if ($fuel['Product']['id'] == $fuelId){
                            //$fuelUnitPrice=$fuel['Product']['default_price'];
                            $fuelUnitPrice=$fuel['ProductPriceLog'][0]['price'];
                            $fuelName=$fuel['Product']['name'];
                            $stockItemId=$fuel['StockItem'][0]['id'];
                          }
                        }
                        
                        $message="El día ".$saleDateAsString." en gasolinera ".$enterprises[$enterpriseId]." el operador ".($hoseData['operator_id']>0?$operators[$hoseData['operator_id']]:"NO OPERADOR")." vendió en isla ".$islands[$islandId]." y manguera ".$hoses[$hoseId]." ".$fuelTotalQuantity." litros de ".$fuelName." en el turno ".$shifts[$shiftId].".";
                          
                        if ($hoseData['quantity']>0){
                          $stockMovementData=[];
                          $stockMovementData['movement_date']=$saleDateArray;
                          $stockMovementData['bool_input']=false;
                          $stockMovementData['stock_movement_type_id']=MOVEMENT_SALE;
                          $stockMovementData['name']=$saleDateArray['day'].$saleDateArray['month'].$saleDateArray['year']."_".$fuelName;
                          $stockMovementData['description']=$message;
                          $stockMovementData['enterprise_id']=$enterpriseId;
                          $stockMovementData['hose_id']=$hoseId;
                          $stockMovementData['operator_id']=$hoseData['operator_id'];
                          $stockMovementData['shift_id']=$shiftId;
                          $stockMovementData['order_id']=$orderId;
                          $stockMovementData['stock_item_id']=$stockItemId;
                          $stockMovementData['product_id']=$fuelId;
                          $stockMovementData['product_quantity']=$fuelQuantity/GALLONS_TO_LITERS;
                          $stockMovementData['product_unit_price']=$fuelUnitPrice*GALLONS_TO_LITERS;
                          $stockMovementData['product_total_price']=$hoseData['price'];
                          
                          $totalPriceFuels+=$hoseData['price'];
                        
                          $this->StockMovement->create();
                          if (!$this->StockMovement->save($stockMovementData)) {
                            echo "problema al guardar el movimiento de lote";
                            pr($this->validateErrors($this->StockMovement));
                            throw new Exception();
                          }
                        }
                        $this->recordUserActivity($this->Session->read('User.username'),$message);
                        /*
                        foreach ($hoseCounterArray as $counterHoseId => $counterHoseData){
                          if ($counterHoseId===$hoseId){
                            //pr($hoseData);  
                            //pr($counterHoseData);  
                            foreach ($counterHoseData as $hoseShiftId=>$shiftCounterValue){
                              //echo "hoseId is ".$hoseId."<br/>";
                              //echo "shiftId is ".$shiftId."<br/>";
                              //echo "hoseShiftId is ".$hoseShiftId."<br/>";
                              //echo "fuelQuantity is ".$fuelQuantity."<br/>";
                              if ($shiftId<=$hoseShiftId){
                                //echo "changing fuelquantity for hoseId ".$counterHoseId." shiftId ".$shiftId." hoseShiftId".$hoseShiftId." with fuel quantity ".$fuelQuantity."<br/>";
                                $hoseCounterArray[$counterHoseId][$hoseShiftId]+=$fuelQuantity;
                                //echo "fuel quantity is ".$hoseCounterArray[$counterHoseId][$hoseShiftId]."<br/>";  
                              }
                            }
                          }
                        }
                        */                            
                      }
                      else {
                        $hoseCounterData=[];
                        $hoseCounterData['counter_date']=$saleDate;
                        $hoseCounterData['enterprise_id']=$enterpriseId;
                        $hoseCounterData['shift_id']=$shiftId;
                        $hoseCounterData['hose_id']=$hoseId;
                        $hoseCounterData['operator_id']=$hoseData['operator_id'];
                        $hoseCounterData['counter_value']=$hoseData['initial'];
                        $this->HoseCounter->create();
                        if (!$this->HoseCounter->save($hoseCounterData)) {
                          echo "problema al guardar el contador de manguera";
                          pr($this->validateErrors($this->HoseCounter));
                          throw new Exception();
                        }
                      }
                    }
                }
              
                foreach ($shiftData['Calibration'] as $fuelId=>$fuelCalibrationLiters){
                  //pr($fuelCalibrationLiters);   

                  foreach ($fuels as $fuel){
                    if ($fuel['Product']['id'] == $fuelId){
                      $fuelUnitPrice=$fuel['ProductPriceLog'][0]['price'];
                      $fuelName=$fuel['Product']['name'];
                      $stockItemId=$fuel['StockItem'][0]['id'];
                    }
                  }
                  
                  $message="El día ".$saleDateAsString." en gasolinera ".$enterprises[$enterpriseId]." durante el turno ".$shifts[$shiftId]." se sacaron ".$fuelCalibrationLiters." litros de ".$fuelName." en concepto de calibración.";
                  
                  $userName=$this->Session->read('User.username');
                  $adjustmentCode=$this->StockMovement->getAdjustmentCode($userName);
      
                  $calibrationStockMovementData=[];
                  // only the IN movement; out movements are part of the movements per house that were registered
                  $calibrationStockMovementData['movement_date']=$saleDateArray;
                  $calibrationStockMovementData['bool_input']=true;
                  $calibrationStockMovementData['stock_movement_type_id']=MOVEMENT_ADJUSTMENT_CALIBRATION;
                  $calibrationStockMovementData['name']=$saleDateArray['day'].$saleDateArray['month'].$saleDateArray['year']."_".$fuelName;
                  $calibrationStockMovementData['description']=$message;
                  $calibrationStockMovementData['enterprise_id']=$enterpriseId;
                  $calibrationStockMovementData['hose_id']=0;
                  $calibrationStockMovementData['operator_id']=0;
                  $calibrationStockMovementData['shift_id']=$shiftId;
                  $calibrationStockMovementData['order_id']=$orderId;
                  $calibrationStockMovementData['stock_item_id']=$stockItemId;
                  $calibrationStockMovementData['product_id']=$fuelId;
                  $calibrationStockMovementData['product_quantity']=$fuelCalibrationLiters/GALLONS_TO_LITERS;
                  $calibrationStockMovementData['product_unit_price']=$fuelUnitPrice*GALLONS_TO_LITERS;
                  $calibrationStockMovementData['product_total_price']=$fuelCalibrationLiters*$fuelUnitPrice;
                  $calibrationStockMovementData['adjustment_code']=$adjustmentCode;
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($calibrationStockMovementData)) {
                    echo "problema al guardar la calibración para turno ".$shifts[$shiftId].' y combustible '.$fuelName;
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                }
              }
              //pr($fuelTotals);
              foreach ($fuelTotals as $fuelId=>$fuelTotalQuantity){
                if ($fuelTotalQuantity >0){
                  foreach ($fuels as $fuel){
                    if ($fuel['Product']['id'] == $fuelId){
                      $stockItemId=$fuel['StockItem'][0]['id'];
                      $this->recreateStockItemLogs($stockItemId,$saleDateAsString);
                    }
                  }
                }              
              }
              /*
              if (abs($this->request->data['Order']['total_price_fuels']-$totalPriceFuels)>0.01){
                echo "el total de los combustibles ".$this->request->data['Order']['total_price_fuels']." no iguala los precios sumados de los combustibles ".$totalPriceFuels;
                throw new Exception();
              }
              */
              /*
              //pr($hoseCounterArray[1]);
              foreach ($hoseCounterArray as $hoseId=>$hoseData){
                foreach ($hoseData as $hoseShiftId=>$shiftCounterValue){
                  if ($hoseShiftId>0){
                    $hoseCounterData=[];
                    $hoseCounterData['counter_date']=$saleDate;
                    $hoseCounterData['shift_id']=$hoseShiftId;
                    $hoseCounterData['hose_id']=$hoseId;
                    $hoseCounterData['counter_value']=$shiftCounterValue;
                    $this->HoseCounter->create();
                    if (!$this->HoseCounter->save($hoseCounterData)) {
                      echo "problema al guardar el contador de manguera";
                      pr($this->validateErrors($this->HoseCounter));
                      throw new Exception();
                    }
                  }
                }
              }
              */
              $totalPriceLubricants=0;
              foreach ($this->request->data['Lubricant'] as $lubricant){
                if ($lubricant['lubricant_quantity']>0){
                  //pr($lubricant);
                  $lubricantId=$lubricant['lubricant_id'];
                  $lubricantUnitPrice=$lubricant['lubricant_unit_price'];
                  $lubricantQuantity = $lubricant['lubricant_quantity'];
                  $lubricantTotalPrice=$lubricant['lubricant_total_price'];
                  
                  $this->Product->recursive=-1;
                  $linkedProduct=$this->Product->find('first',[
                    'conditions'=>['Product.id'=>$lubricantId,],
                  ]);
                  $lubricantName=(!empty($linkedProduct)?$linkedProduct['Product']['name']:"");
                  
                  $usedMaterials= $this->StockItem->getMaterialsForSale($lubricantId,$lubricantQuantity,$saleDateAsString,$enterpriseId);		
                   //pr($usedMaterials);
                  
                  for ($k=0;$k<count($usedMaterials);$k++){
                    $materialUsed=$usedMaterials[$k];
                    $stockItemId=$materialUsed['id'];
                    $quantityPresent=$materialUsed['quantity_present'];
                    $quantityUsed=$materialUsed['quantity_used'];
                    $quantityRemaining=$materialUsed['quantity_remaining'];
                    if (!$this->StockItem->exists($stockItemId)) {
                      throw new NotFoundException(__('Invalid StockItem'));
                    }
                    //$linkedStockItem=$this->StockItem->read(null,$stockItemId);
                    $this->StockItem->recursive=-1;
                    $linkedStockItem=$this->StockItem->find('first',[
                      'conditions'=>['StockItem.id'=>$stockItemId,],
                    ]);
                    
                    $stockItemData=[];
                    $stockItemData['id']=$stockItemId;
                    $stockItemData['remaining_quantity']=$quantityRemaining;
                    if (!$this->StockItem->save($stockItemData)) {
                      echo "problema al guardar el lote";
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                                    
                    $message="El día ".$saleDateAsString." en gasolinera ".$enterprises[$enterpriseId]." se vendió ".$quantityUsed." unidades de ".$lubricantName.".";
                    $stockMovementData=[];
                    $stockMovementData['movement_date']=$saleDateArray;
                    $stockMovementData['bool_input']=false;
                    $stockMovementData['stock_movement_type_id']=MOVEMENT_SALE;
                    $stockMovementData['name']=$saleDateArray['day'].$saleDateArray['month'].$saleDateArray['year']."_".$fuelName;
                    $stockMovementData['description']=$message;
                    $stockMovementData['enterprise_id']=$enterpriseId;
                    $stockMovementData['hose_id']=0;
                    $stockMovementData['operator_id']=0;
                    $stockMovementData['shift_id']=0;
                    $stockMovementData['order_id']=$orderId;
                    $stockMovementData['stock_item_id']=$stockItemId;
                    $stockMovementData['product_id']=$lubricantId;
                    $stockMovementData['product_quantity']=$quantityUsed;
                    $stockMovementData['product_unit_price']=$lubricantUnitPrice;
                    $stockMovementData['product_total_price']=$lubricantTotalPrice;
                    
                    $totalPriceLubricants+=$stockMovementData['product_total_price'];
                    
                    $this->StockMovement->create();
                    if (!$this->StockMovement->save($stockMovementData)) {
                      echo "problema al guardar el movimiento de lote";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                    $this->recreateStockItemLogs($stockItemId,$saleDateAsString);
                     
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                  }
                }
              }
              
              if (abs($this->request->data['Order']['total_price_lubricants']-$totalPriceLubricants)>0.01){
                echo "el total de los lubricantes no iguala los precios sumados de los lubricantes";
                throw new Exception();
              }
                      
              $datasource->commit();
              $this->recordUserAction($this->Order->id,"add",null);
              // SAVE THE USERLOG FOR THE SALE
              $this->recordUserActivity($this->Session->read('User.username'),"Se registraron las ventas del día ".$saleDateAsString." para gasolinera ".$enterpriseId);
              $this->Session->setFlash('Se guardaron las ventas de '.$saleDateAsString.'.','default',['class' => 'success']);
              
              $boolEditingMode=false;
              $boolEditingToggleVisible=true;
              //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
              //return $this->redirect(['action' => 'verInformeDiario',$saleDateAsString]);
              
              return $this->redirect(['controller'=>'tankMeasurements','action' => 'registrarMedidas',$saleDateAsString]);
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);
              $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
            }
          }
        }
      }
    }
		else {
      if (!empty($this->request->data['changeDate'])){
        $saleDateArray=$this->request->data['Order']['order_date'];
        //pr($saleDateArray);
        $saleDateAsString=$saleDateArray['year'].'-'.$saleDateArray['month'].'-'.$saleDateArray['day'];
        $saleDate=date( "Y-m-d", strtotime($saleDateAsString));
        $saleDatePlusOne=date("Y-m-d",strtotime($saleDateAsString."+1 days"));
        //$_SESSION['saleDate']=$saleDate;
      }
      
      if ($enterpriseId > 0){
        $hoseCounterConditions=[
          'HoseCounter.counter_date >='=>$saleDate,
          'HoseCounter.counter_date <'=>$saleDatePlusOne,
          'HoseCounter.enterprise_id'=>$enterpriseId,
        ];
        $hoseCounters=$this->HoseCounter->find('all',[
          'conditions'=>$hoseCounterConditions,
          'order'=>['shift_id','hose_id','operator_id'],
        ]);  
        
        $islands=$this->Island->find('all',[
          'conditions'=>[
            'Island.bool_active'=>true,
            'Island.enterprise_id'=>$enterpriseId,
          ],
          'contain'=>[
            'Hose'=>[
            ],
          ],
          'order'=>'Island.name',
        ]);
        
        $this->StockMovement->virtualFields['CalibrationQuantity']=0;
        $calibrationMovementConditions=[
          'StockMovement.stock_movement_type_id'=>MOVEMENT_ADJUSTMENT_CALIBRATION,
          //'StockMovement.product_id'=>$fuelIds,
          'StockMovement.movement_date >='=>$saleDate,
          'StockMovement.movement_date <'=>$saleDatePlusOne,
          'StockMovement.enterprise_id'=>$enterpriseId,
        ];
        $calibrationMovements=$this->StockMovement->find('all',[
          'fields'=>['StockMovement.shift_id','StockMovement.product_id','SUM(StockMovement.product_quantity) AS StockMovement__CalibrationQuantity'],
          'conditions'=>$calibrationMovementConditions,
          'group'=>['shift_id','product_id'],
          'order'=>['shift_id'],
        ]);
        //pr($calibrationMovements);
        
        $requestShifts=[];
        foreach ($shifts as $shiftId=>$shiftName){
          $shiftArray=[];  
          
          foreach ($islands as $island){
            $islandId=$island['Island']['id'];
            $islandArray=[];
            //$islandArray['operator_id']=0;
            foreach ($island['Hose'] as $hose){
              $hoseId=$hose['id'];  
              $hoseArray=[];
              $hoseArray['operator_id']=0;
              $hoseArray['final']=0;
              foreach ($hoseCounters as $hoseCounter){
                if ($hoseCounter['HoseCounter']['shift_id'] == $shiftId && $hoseCounter['HoseCounter']['hose_id'] == $hoseId){
                  $hoseArray['operator_id']=$hoseCounter['HoseCounter']['operator_id'];
                  $hoseArray['final']=$hoseCounter['HoseCounter']['counter_value'];
                }
              }
              /*
              $hoseArray['quantity']=0;
              $hoseArray['price']=0;
              foreach ($fuelMovements as $fuelMovement){
                if ($fuelMovement['StockMovement']['shift_id'] == $shiftId && $fuelMovement['StockMovement']['hose_id'] == $hoseId){
                  $hoseArray['quantity']=$fuelMovement['StockMovement']['product_quantity']*GALLONS_TO_LITERS;
                  $hoseArray['price']=$fuelMovement['StockMovement']['product_total_price'];
                  if ($islandArray['operator_id'] == 0 && $fuelMovement['StockMovement']['operator_id'] > 0){
                    $islandArray['operator_id']=$fuelMovement['StockMovement']['operator_id'];
                    //pr($islandArray);
                  }
                  
                }
              }
              */
              $islandArray['Hose'][$hoseId]=$hoseArray;
            }
            $shiftArray['Island'][$islandId]=$islandArray;
          }
          $calibrationArray=[];
          foreach ($calibrationMovements as $calibrationMovement){
            if ($calibrationMovement['StockMovement']['shift_id'] == $shiftId){
              $fuelId=$calibrationMovement['StockMovement']['product_id'];
              $calibrationQuantity=round($calibrationMovement['StockMovement']['CalibrationQuantity']*GALLONS_TO_LITERS,2);
              $calibrationArray[$fuelId]=$calibrationQuantity;
            }
          }
          $shiftArray['Calibration']=$calibrationArray;
          $requestShifts['Shift'][$shiftId]=$shiftArray;
        }
        //pr($requestShifts);
        $lubricantStockMovementConditions=[
          'StockMovement.bool_input'=>false,
          'StockMovement.product_id !='=>$fuelIds,
          'StockMovement.movement_date >='=>$saleDate,
          'StockMovement.movement_date <'=>$saleDatePlusOne,
          'StockMovement.enterprise_id'=>$enterpriseId,
        ];
        $lubricantMovements=$this->StockMovement->find('all',[
          'conditions'=>$lubricantStockMovementConditions,
        ]);
        foreach ($lubricantMovements as $lubricantMovement){
          $requestLubricantMovement=[];
          $requestLubricantMovement['lubricant_id']=$lubricantMovement['StockMovement'] ['product_id'];
          $requestLubricantMovement['lubricant_quantity']=$lubricantMovement['StockMovement'] ['product_quantity'];
          $requestLubricantMovement['lubricant_unit_price']=$lubricantMovement['StockMovement'] ['product_unit_price'];
          $requestLubricantMovement['lubricant_total_price']=$lubricantMovement['StockMovement'] ['product_total_price'];
          $requestLubricants['Lubricant'][]=$requestLubricantMovement;
        }
        //pr ($requestLubricants);
        
        $fuelStockMovementConditions=[
          'StockMovement.stock_movement_type_id'=>MOVEMENT_SALE,
          'StockMovement.product_id'=>$fuelIds,
          'StockMovement.movement_date >='=>$saleDate,
          'StockMovement.movement_date <'=>$saleDatePlusOne,
          'StockMovement.enterprise_id'=>$enterpriseId,
        ];
        $fuelMovements=$this->StockMovement->find('all',[
          'conditions'=>$fuelStockMovementConditions,
          'order'=>['shift_id','operator_id'],
        ]);
        
        if (empty($fuelMovements) && empty($lubricantMovements)){
          $boolEditingMode=true;
        }
        else {
          $boolEditingToggleVisible=true;
        }
        
        foreach ($requestShifts['Shift'] as $shiftId=>$shiftData){
          //pr($shiftData);
          $operatorFirstIsland=0;
          $operatorSecondIsland=0;
          $operatorThirdIsland=0;
          foreach ($shiftData['Island'] as $islandId=>$islandData){
            
            if (!empty($islandData['Hose'][1])){
              $operatorFirstIsland= $islandData['Hose'][1]['operator_id'];
            }
            if (!empty($islandData['Hose'][7])){
              $operatorSecondIsland= $islandData['Hose'][7]['operator_id'];
            }  
            if (!empty($islandData['Hose'][13])){
              $operatorThirdIsland= $islandData['Hose'][13]['operator_id'];
            }  
          }
          //echo "operator first island is ".$operatorFirstIsland."<br/>";
          //echo "operator second island is ".$operatorSecondIsland."<br/>";
          //echo "operator third island is ".$operatorThirdIsland."<br/>";
          
          if ($operatorFirstIsland != 0){
            if ($operatorSecondIsland !== $operatorFirstIsland){
              if($operatorThirdIsland !== $operatorSecondIsland){
                $shiftOperatorConfigs[$shiftId]=3;                  
              }
              else {
                $shiftOperatorConfigs[$shiftId]=2;
              }
            }
            else {
              $shiftOperatorConfigs[$shiftId]=1;  
            }            
          }
          else {
            $shiftOperatorConfigs[$shiftId]=0;
          }
        }
      }
      //pr($shiftOperatorConfigs);
    }
    
    $this->set(compact('boolEditingMode'));
    $this->set(compact('boolEditingToggleVisible'));
    $this->set(compact('operatorConfigurationOptions'));
    $saleDate= date( "Y-m-d H:i:s", strtotime( $saleDate) );
    $this->set(compact('saleDate'));
    //$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestShifts'));
    $this->set(compact('requestLubricants'));
    $this->set(compact('shiftOperatorConfigs'));
    
    //echo "bool editing toggle visible is ".$boolEditingToggleVisible."<br/>";
    //echo "bool editing mode is ".$boolEditingMode."<br/>";
		
    //echo "saledateplusone is ".$saleDatePlusOne."<br/>";
		$islands=$this->Island->find('all',[
      'conditions'=>[
        'Island.bool_active'=>true,
        'Island.enterprise_id'=>$enterpriseId,
      ],
      'contain'=>[
        'Hose'=>[
          'HoseCounter'=>[
            'conditions'=>['DATE(counter_date) <' => $saleDate],
            'order'=>'counter_date DESC,shift_id DESC, id DESC',
            'limit'=>1,
          ],
          'Product'=>[
            'ProductPriceLog'=>[
              'conditions'=>[
                'price_datetime <'=>$saleDatePlusOne,
                'enterprise_id'=>$enterpriseId,
              ],
              'order'=>'price_datetime DESC',
              'limit'=>1,
              'Currency',
            ],
          ],
        ],
      ],
      'order'=>'Island.name',
		]);
		$this->set(compact('islands'));
    //pr($islands);

		//$stockMovementTypes = $this->Order->StockMovementType->find('list');
		//$this->set(compact('stockMovementTypes'));

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
		/*
		if (!empty($inventoryDisplayOptionId)){
      //pr($finishedMaterialsInventory);
			$productcategoryid=CATEGORY_OTHER;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
      //pr($otherMaterialsInventory);
		}
    
    $this->set(compact('otherMaterialsInventory'));
    
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
    
    $this->set(compact('accountingCodes'));
		*/
    
    $currencies = $this->Currency->find('list');
		$this->set(compact('currencies'));
		
		$this->loadModel('ExchangeRate');
		$saleExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDate);
		$exchangeRateOrder=$saleExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    
    $lubricantsWithPrices=$this->Product->find('all',[
      'fields'=>['Product.id','Product.default_price',],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
      'contain'=>[
        'ProductPriceLog'=>[
          'conditions'=>[
            'price_datetime <'=>$saleDatePlusOne,
            'enterprise_id'=>$enterpriseId,
          ],
          'order'=>'price_datetime DESC',
          'limit'=>1,
          'Currency',
        ],
      ],
    ]);
    $lubricantPrices=[];
    if (!empty($lubricantsWithPrices)){
      foreach ($lubricantsWithPrices as $lubricantWithPrice){
        $lubricantId=$lubricantWithPrice['Product']['id'];
        $lubricantPrice=(empty($lubricantWithPrice['ProductPriceLog'])?$lubricantWithPrice['Product']['default_price']:$lubricantWithPrice['ProductPriceLog'][0]['price']);        
        $lubricantPrices[$lubricantId]=$lubricantPrice;
      }
    }
    //$otherProducts=array_values($otherProducts);
    $this->set(compact('lubricantPrices'));
    //pr($lubricantPrices);
    //$allLubricants=$this->Product->find('all',[
    //  'conditions'=>[  
    //    ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
    //    ['Product.bool_active'=>true],
    //  ],
    //  'contain'=>['DefaultPriceCurrency'],  
    //]);
    $lubricants=$this->Product->find('list',[
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_LUBRICANTS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.name ASC',
      'contain'=>['DefaultPriceCurrency'],  
    ]);
    $this->set(compact('allLubricants','lubricants'));
    
    $latestFuelProductPriceLog=$this->ProductPriceLog->getLatestFuelProductPriceLog($enterpriseId);
    if ($enterpriseId > 0){
      $productPriceDateTime=new DateTime(date('Y-m-d',strtotime($latestFuelProductPriceLog['ProductPriceLog']['price_datetime'])));
      //pr($productPriceDateTime);
      $saleDateTime= new DateTime(date('Y-m-d', strtotime($saleDate)));
      //$daysPassed=$saleDateTime->diff($productPriceDateTime);
      $daysPassed=$productPriceDateTime->diff($saleDateTime);
      $daysSinceLastFuelPriceUpdate=$daysPassed->format('%r%a');
      //echo "daysSinceLastFuelPriceUpdate is ".$daysSinceLastFuelPriceUpdate."<br/>";
    }
    else {
      $daysSinceLastFuelPriceUpdate=1000;      
    }
    $buttonDisabled=($boolEditingMode?false:true);
    if ($daysSinceLastFuelPriceUpdate > 6){
      $buttonDisabled=true; 
    }
    
    $this->set(compact('daysSinceLastFuelPriceUpdate','buttonDisabled'));  
		/*
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
    */
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
		
	public function setduedate(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$clientid=trim($_POST['clientid']);
		$emissionday=trim($_POST['emissionday']);
		$emissionmonth=trim($_POST['emissionmonth']);
		$emissionyear=trim($_POST['emissionyear']);
	
		$this->loadModel('ThirdParty');
		if (!$clientid){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientid)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$clientid)));
		
		$creditperiod=0;
		if (!empty($client)){
			$creditperiod=$client['ThirdParty']['credit_days'];
		}
		$emissionDateString=$emissionyear.'-'.$emissionmonth.'-'.$emissionday;
		$emissionDate=date( "Y-m-d", strtotime($emissionDateString));
		
		$dueDate=$emissionDate;
		if($creditperiod>0){
			$dueDate=date("Y-m-d",strtotime($emissionDate."+".$creditperiod." days"));
		}
		
		$this->set(compact('dueDate'));
	}
	
	public function resumenEntradas($lastMonth=0) {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Order->recursive = -1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		$startDate = null;
		$endDate = null;
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
	
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
  	
		$purchaseConditions=[
			'Order.stock_movement_type_id'=> MOVEMENT_PURCHASE,
			'Order.order_date >='=> $startDate,
			'Order.order_date <'=> $endDatePlusOne,
      'Order.enterprise_id'=> $enterpriseId,
		];
		
		$purchaseCount=$this->Order->find('count', [
			'conditions' => $purchaseConditions,
		]);
		
		$purchases=[];
		$this->Paginator->settings = [
			'conditions' => $purchaseConditions,
			'contain'=>[
				'StockMovement'=>[
					'fields'=>['product_id','product_quantity','product_unit_price'],
          'conditions'=>[
            'product_quantity >'=> 0,
            'enterprise_id'=> $enterpriseId,
          ],
					'Product'=>[
						'fields'=>['product_type_id','name'],
						'ProductType'=>[
							'fields'=>['product_category_id'],
						],
					],
				],
				'ThirdParty'=>[
					'fields'=>['id','company_name'],
				],
			],
			'order'=>'order_date DESC,Order.id DESC',
			'limit'=>($purchaseCount!=0?$purchaseCount:1)
		];
		$purchases = $this->Paginator->paginate('Order');
    //pr($purchases);
		$this->set(compact('purchases', 'startDate','endDate'));
		
		$orderIdsForPeriod=$this->Order->find('list',[
			'fields'=>['Order.id'],
			'conditions' => $purchaseConditions,
		]);
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

  public function resumenVentasRemisiones() {
		$startDate = null;
		$endDate = null;
    
    define('INVOICES_ALL','0');
    define('INVOICES_CASH','1');
    define('INVOICES_CREDIT','2');
    
    $paymentOptions=[
      INVOICES_ALL=>'Todas Facturas',
      INVOICES_CASH=>'Solo Facturas de Contado',
      INVOICES_CREDIT=>'Solo Facturas de Crédito',
    ];
    $this->set(compact('paymentOptions'));
    
    $paymentOptionId=0;
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $paymentOptionId=$this->request->data['Report']['payment_option_id'];
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
    
    $this->set(compact('paymentOptionId'));
	
		$this->Order->recursive = 0;
		
		$salesForPeriod=$this->Order->find('all',array(
			'fields'=>array(),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('id','company_name')),
				'StockMovement'=>array(
					'fields'=>array('StockMovement.product_quantity','StockMovement.production_result_code_id'),
					'StockItem'=>array(
						'fields'=>array('product_unit_price'),
					),
					'Product'=>array(
						'ProductType'=>array(
							'fields'=>array('product_category_id')
						)
					)
				),
				'Invoice'=>array(
					'fields'=>array(
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            'Invoice.bool_credit',
						'Invoice.currency_id','Invoice.total_price',
					),
					'Currency'
				),
				'CashReceipt'=>array(
					'fields'=>array(
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						'CashReceipt.currency_id','CashReceipt.amount',
					),
					'Currency'
				),
			),
			'conditions' => array(
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			),
			'order'=>'order_date DESC,order_code DESC',
		));
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		//pr($salesForPeriod);
		$sales=array();
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
      if ($sale['Order']['id']==4391){
        //pr($sale);  
      }
			$quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
          $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        }
				
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        if ($sale['ThirdParty']['id'] != CLIENTS_VARIOUS){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['extra_client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']=false;
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']=false;
          $sales[$rowCounter]['Invoice']['bool_credit']=true;
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
          $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        }
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions','startDate','endDate'));
		//pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

  public function resumenDescuadresSubtotalesSumaProductosVentasRemisiones() {
		$startDate = null;
		$endDate = null;
    
    define('ORDERS_ERROR','0');
    define('ORDERS_ALL','1');
    
    $displayOptions=[
      ORDERS_ERROR=>'Solo Ventas y Remisiones donde hay descuadre de subtotal vs suma de precios productos',
      ORDERS_ALL=>'Todas Ventas y Remisiones',
    ];
    $this->set(compact('displayOptions'));
    
    $displayOptionId=0;
   
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $displayOptionId=$this->request->data['Report']['display_option_id'];
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
    
    $this->set(compact('displayOptionId'));
    
    $this->Order->recursive = -1;
		
		$salesForPeriod=$this->Order->find('all',[
			'fields'=>[],
			'contain'=>[
				'ThirdParty'=>['fields'=>['id','company_name']],
				'StockMovement'=>[
					'fields'=>['StockMovement.product_unit_price','StockMovement.product_quantity','StockMovement.product_total_price','StockMovement.production_result_code_id'],
					'Product'=>[
						'ProductType'=>[
							'fields'=>['product_category_id']
						]
					]
				],
				'Invoice'=>[
					'fields'=>[
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            //'Invoice.bool_credit',
						//'Invoice.currency_id','Invoice.total_price',
					],
					//'Currency'
				],
				'CashReceipt'=>[
					'fields'=>[
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						//'CashReceipt.currency_id','CashReceipt.amount',
					],
					//'Currency'
				],
			],
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			],
			'order'=>'order_date DESC,order_code DESC',
		]);
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = ['limit'=>($quantitySales!=0?$quantitySales:1)];
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		//$totalQuantityProduced=0;
		//$totalQuantityOther=0;
		//pr($salesForPeriod);
		$sales=[];
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
			$quantityProduced=0;
			$quantityOther=0;
			//$totalCost=0;
      
      $priceProductsCSBasedOnUnitPriceQuantity=0;
      $priceProductsCSBasedOnTotals=0;
    
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
				
        if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					//$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					//$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				//$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        
        $priceProductsCSBasedOnUnitPriceQuantity+=($stockMovement['product_unit_price']*$stockMovement['product_quantity']);
        $priceProductsCSBasedOnTotals+=$stockMovement['product_total_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        if ($sale['ThirdParty']['id'] != CLIENTS_VARIOUS){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['extra_client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $sales[$rowCounter]['Order']['price_products_unit_quantity']=$priceProductsCSBasedOnUnitPriceQuantity;
        $sales[$rowCounter]['Order']['price_products_total']=$priceProductsCSBasedOnTotals;
        // yes that is right, the total_price field in the order corresponds with the sub_total_price  of the invoice
				$sales[$rowCounter]['Order']['sub_total_price']=$sale['Order']['total_price'];
				//$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				//$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				//$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				//$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				//$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']=false;
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']=false;
          //$sales[$rowCounter]['Invoice']['bool_credit']=true;
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = [
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		];
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=[];
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			//$totalCost=0;
			
      $priceProductsCSBasedOnUnitPriceQuantity=0;
      $priceProductsCSBasedOnTotals=0;

      foreach ($sale['StockMovement'] as $stockMovement){
        
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					//$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					//$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
        
        $priceProductsCSBasedOnUnitPriceQuantity+=($stockMovement['product_unit_price']*$stockMovement['product_quantity']);
        $priceProductsCSBasedOnTotals+=$stockMovement['product_total_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $remissions[$rowCounter]['Order']['price_products_unit_quantity']=$priceProductsCSBasedOnUnitPriceQuantity;
        $remissions[$rowCounter]['Order']['price_products_total']=$priceProductsCSBasedOnTotals;
        $remissions[$rowCounter]['Order']['sub_total_price']=$sale['Order']['total_price'];
				//$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				//$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				//$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				//$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				//$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions','startDate','endDate'));
		//pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

  public function resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones() {
		$startDate = null;
		$endDate = null;
    
    define('ORDERS_ERROR','0');
    define('ORDERS_ALL','1');
    
    $displayOptions=[
      ORDERS_ERROR=>'Solo Ventas donde hay descuadre de total vs suma subtotal+iva',
      ORDERS_ALL=>'Todas Ventas',
    ];
    $this->set(compact('displayOptions'));
    
    $displayOptionId=0;
    
    $paymentOptionId=0;
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $displayOptionId=$this->request->data['Report']['display_option_id'];
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
    
    $this->set(compact('displayOptionId'));
	
		$this->Order->recursive = -1;
		
		$salesForPeriod=$this->Order->find('all',[
			'fields'=>[],
			'contain'=>[
				'ThirdParty'=>['fields'=>['id','company_name']],
				'StockMovement'=>[
					'fields'=>['StockMovement.product_unit_price','StockMovement.product_quantity','StockMovement.product_total_price','StockMovement.production_result_code_id'],
					'Product'=>[
						'ProductType'=>[
							'fields'=>['product_category_id']
						]
					]
				],
				'Invoice'=>[
					'fields'=>[
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            'Invoice.sub_total_price','Invoice.IVA_price','Invoice.total_price','Invoice.currency_id',
					],
				],
				'CashReceipt'=>[
					'fields'=>[
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
					],
				],
			],
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			],
			'order'=>'order_date DESC,order_code DESC',
		]);
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		//$totalQuantityProduced=0;
		//$totalQuantityOther=0;
		//pr($salesForPeriod);
		$sales=[];
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
      $quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					//$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					//$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				//$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
      //if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['StockMovement']))){  
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        if ($sale['ThirdParty']['id'] != CLIENTS_VARIOUS){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['extra_client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $sales[$rowCounter]['Invoice']=$sale['Invoice'];
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']=false;
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
    /*
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
		*/
		$this->set(compact('sales','remissions','startDate','endDate'));
		//pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function guardarResumenVentasRemisiones() {
		$exportData=$_SESSION['resumenVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones() {
		$exportData=$_SESSION['resumenDescuadresSubtotalesSumaProductosVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones() {
		$exportData=$_SESSION['resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function resumenComprasRealizadas($clientId=0) {
		$startDate = null;
		$endDate = null;
    
    define('INVOICES_ALL','0');
    define('INVOICES_CASH','1');
    define('INVOICES_CREDIT','2');
    
    $paymentOptions=[
      INVOICES_ALL=>'Todas Facturas',
      INVOICES_CASH=>'Solo Facturas de Contado',
      INVOICES_CREDIT=>'Solo Facturas de Crédito',
    ];
    $this->set(compact('paymentOptions'));
    
    $paymentOptionId=0;
    
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    if ($clientId==0){
      //pr($this->Auth->User());
      $clientId=$this->Auth->User('client_id');
    }
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $clientId=$this->request->data['Report']['client_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date( "Y-m-d", strtotime( date("Y-m-d")." - 1 months"));
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
    $this->set(compact('paymentOptionId'));
    $this->set(compact('clientId'));
    
		$this->Order->recursive = 0;
		
		$salesForPeriod=$this->Order->find('all',array(
			'fields'=>array(),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('id','company_name')),
				'StockMovement'=>array(
					'fields'=>array('StockMovement.product_quantity','StockMovement.production_result_code_id'),
					'StockItem'=>array(
						'fields'=>array('product_unit_price'),
					),
					'Product'=>array(
						'ProductType'=>array(
							'fields'=>array('product_category_id')
						)
					)
				),
				'Invoice'=>array(
					'fields'=>array(
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            'Invoice.bool_credit',
						'Invoice.currency_id','Invoice.total_price',
					),
					'Currency'
				),
				'CashReceipt'=>array(
					'fields'=>array(
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						'CashReceipt.currency_id','CashReceipt.amount',
					),
					'Currency'
				),
			),
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
        'Order.third_party_id'=>$clientId,
        'Order.bool_annulled'=>false
			],
			'order'=>'order_date DESC,order_code DESC',
		));
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			//pr($sale);
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		
		$sales=array();
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
			$quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				//pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&$sale['Invoice'][0]['bool_annulled'])){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']=false;
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions'));
    
    $this->loadModel('ThirdParty');
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
    
    $this->loadModel('PurchaseEstimation');
    $purchaseEstimation=$this->PurchaseEstimation->getPurchaseEstimation($clientId);
    //pr($purchaseEstimation);
    $this->set(compact('purchaseEstimation'));
	}

	public function guardarResumenComprasRealizadas() {
		$exportData=$_SESSION['resumenComprasRealizadas'];
		$this->set(compact('exportData'));
	}	
  
	public function verEntrada($id = null) {
		$this->Order->recursive=-1;
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
		$options = [
      'conditions' => ['Order.id' => $id],
      'contain'=>[
        'ThirdParty',
        'StockMovement'=>['Product'],
      ],
    ];
		$this->set('order', $this->Order->find('first', $options));
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
  public function verPdfEntrada($id = null) {
		$this->Order->recursive=2;
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
		$options = ['conditions' => ['Order.id' => $id]];
		$this->set('order', $this->Order->find('first', $options));
	}
	
	public function verVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		$this->Product->recursive=0;
		$this->Invoice->recursive=0;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
			'contain'=>array(
				'AccountingRegisterInvoice'=>array(
					'AccountingRegister'=>array(
						'AccountingMovement'=>array(
							'AccountingCode',
						),
					),
				),
				'CashboxAccountingCode',
				'Currency'=>array('fields'=>array('Currency.id, Currency.abbreviation')),
			)
		));
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',[
			'fields'=>['SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'],
			'conditions'=>['StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				$this->Product->recursive=-1;
				$linkedRawMaterial=$this->Product->find('first',array(
					'conditions'=>array(
						'Product.id'=>$summedMovements[$i]['StockItem']['raw_material_id'],
					),
				));
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=array();
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
		
		//if (!empty($invoice)){
		//	$creditDays=$this->Invoice->getCreditDays($invoice['Invoice']['id']);
		//}
		//$this->set(compact('creditDays'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	 
	public function verPdfVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		
		$this->Product->recursive=0;
		$this->Invoice->recursive=0;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name, ThirdParty.phone')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
			'contain'=>array(
				'CashboxAccountingCode',
				'Currency'=>array('fields'=>array('Currency.id, Currency.abbreviation')),
			)
		));
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',[
			'fields'=>['SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'],
			'conditions'=>['StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				//$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				$this->Product->recursive=-1;
				$linkedRawMaterial=$this->Product->find('first',array(
					'conditions'=>array(
						'Product.id'=>$summedMovements[$i]['StockItem']['raw_material_id'],
					),
				));
				$this->Product->recursive=-1;
				$linkedRawMaterial=$this->Product->find('first',array(
					'conditions'=>array(
						'Product.id'=>$summedMovements[$i]['StockItem']['raw_material_id'],
					),
				));
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=array();
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
		
		//if (!empty($invoice)){
		//	$creditDays=$this->Invoice->getCreditDays($invoice['Invoice']['id']);
		//}
		//$this->set(compact('creditDays'));
	}
	
	public function crearEntrada() {
		$this->loadModel('Product');
		$this->loadModel('Tank');
    
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
    
    $this->loadModel('StockMovement');
    $this->loadModel('StockItem');
    $this->loadModel('StockItemLog');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Product->recursive=-1;
    $this->Tank->recursive=-1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
		}
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId=$_SESSION['enterpriseId'];  
    }
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $requestProducts=[];
		if ($this->request->is('post')) {
      foreach ($this->request->data['Product'] as $product){
				if ($product['product_id']>0 && $product['product_quantity']>0 && $product['product_unit_price']>0){
					$requestProducts[]['Product']=$product;
				}
			}
      
			$purchaseDate=$this->request->data['Order']['order_date'];
			$purchaseDateString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
      $purchaseDatePlusOne=date('Y-m-d', strtotime($purchaseDateString."+1 days"));
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDateTime=new DateTime($latestClosingDate);
			
			$previousPurchasesWithThisCode=[];
			$previousPurchasesWithThisCode=$this->Order->find('all',[
				'conditions'=>[
					'Order.order_code'=>$this->request->data['Order']['order_code'],
					'Order.stock_movement_type_id'=>[MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
					'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
          'Order.enterprise_id'=>$enterpriseId,
				],
			]);
			
      if ($purchaseDateString>date('Y-m-d H:i')){
				$this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			elseif ($purchaseDateString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($previousPurchasesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
      elseif (empty($this->request->data['Order']['third_party_id'])){
				$this->Session->setFlash(__('Se debe seleccionar el proveedor.  No se guardó la entrada.'), 'default',['class' => 'error-message']);
			}
			else {
        $productTotalSumBasedOnProductTotals=0;  
        
        $boolMultiplicationOK=true;
        $productMultiplicationWarning="";
        
        $productOutsideCostRange=false;
        $costRangeError="";
        
        $fuelTankCapacityExceeded=false;
        $capacityExceededError="";
        
        foreach ($this->request->data['Product'] as $product){
          $productId = $product['product_id'];
          $productQuantity = $product['product_quantity'];
          $productTotalCost = $product['product_total_price'];
          
          if ($productQuantity>0 && $productId>0){
            $multiplicationDifference=abs($product['product_total_price']-$productQuantity*$product['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK=false;
              $this->Product->recursive=-1;
              $relatedProduct=$this->Product->find('first',[
                'conditions'=>['Product.id'=>$productId,],
              ]);
               $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$productQuantity." y un precio unitario ".$product['product_unit_cost'].", pero el total calculado ".$product['product_total_cost']." no es correcto;";
            };
            $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
            
            $productData=$this->Product->find('first',[
              'conditions'=>['Product.id'=>$productId],
            ]);
            if (!empty($productData)){
              $productMaxCost=$productData['Product']['max_cost'];
              $productMinCost=$productData['Product']['min_cost'];
              if ($productData['Product']['product_type_id'] == PRODUCT_TYPE_FUELS){
                if ($productData['Product']['default_cost_unit_id']==UNIT_LITERS){
                  $productMaxCost*=GALLONS_TO_LITERS;
                  $productMinCost*=GALLONS_TO_LITERS;
                }
                //IF NEEDED ADD ANOTHER CHECK IN CASE ENTRIES WOULD BE IN DOLLARS OR DEFAULT PRODUCT CURRENCIES WOULD BE IN COLLARS
              }
              if ($productMaxCost >0 && $productTotalCost/$productQuantity>$productMaxCost){
                $productOutsideCostRange=true;
                $costRangeError.="Para el producto ".$productData['Product']['name']." el costo unitario registrado ".($productTotalCost/$productQuantity)." C$/galón supera el costo máximo configurado ".$productMaxCost." C$.  ";
              } 
              elseif ($productTotalCost/$productQuantity<$productMinCost){
                $productOutsideCostRange=true;
                $costRangeError.="Para el producto ".$productData['Product']['name']." el costo registrado ".($productTotalCost/$productQuantity)." C$/galón está debajo del costo mínimo configurado ".$productMinCost." C$/galón.  ";
              }
            }
          
            if ($productData['Product']['product_type_id'] == PRODUCT_TYPE_FUELS){
              $tankData=$this->Tank->find('first',[
                'conditions'=>[
                  'Tank.product_id'=>$productId,
                  'Tank.enterprise_id'=>$enterpriseId
                ],
                'contain'=>[
                  'Product'=>['StockItem'],
                ]
              ]);
              if (!empty($tankData)){
                $totalCapacityTankGallons=$tankData['Tank']['total_capacity'];
                if ($tankData['Tank']['unit_id'] == UNIT_LITERS){
                  $totalCapacityTankGallons=$totalCapacityTankGallons/GALLONS_TO_LITERS;
                }
                //ONE STOCKITEM PER PRODUCT IS PRESUPPOSED
                $totalCurrentVolumeTankGallons=$tankData['Product']['StockItem'][0]['remaining_quantity'];
                if ($tankData['Product']['StockItem'][0]['unit_id'] == UNIT_LITERS){
                  $totalCurrentVolumeTankGallons=$totalCurrentVolumeTankGallons/GALLONS_TO_LITERS;
                }
                //if (($totalCurrentVolumeTankGallons + $productQuantity) > $totalCapacityTankGallons){
                //  $fuelTankCapacityExceeded=true;
                //  $capacityExceededError.="No se puede exceder la capacidad máxima (".$totalCapacityTankGallons." galones) del tanque ".$tankData['Tank']['name'].".  El contenido actual del tanque es ".$totalCurrentVolumeTankGallons." galones y se intentó añadir ".$productQuantity." galones.";
                //}
              }
            }
          }
        }
        if (abs($productTotalSumBasedOnProductTotals-$this->request->data['Order']['subtotal_price']) > 0.01){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Order']['subtotal_price'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif ($productOutsideCostRange){
          $this->Session->setFlash($costRangeError.'No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        //elseif ($fuelTankCapacityExceeded){
        //  $this->Session->setFlash($capacityExceededError.'No se guardó la entrada.', 'default',['class' => 'error-message']);
        //}
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['total_price']-$this->request->data['Order']['iva_price']-$this->request->data['Order']['rent_price']-$this->request->data['Order']['adjustment_price']-$this->request->data['Order']['subtotal_price'])>0.01){
          $this->Session->setFlash('La suma del subtotal, el IVA y la renta no iguala el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        else {
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            $this->Order->create();
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
            $this->request->data['Order']['currency_id']=CURRENCY_CS;
            $this->request->data['Order']['enterprise_id']=$enterpriseId;
            if (!$this->Order->save($this->request->data)) {
              echo "problema guardando la entrada";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $purchaseId=$this->Order->id;
            $orderCode=$this->request->data['Order']['order_code'];
            $providerId=$this->request->data['Order']['third_party_id'];
            $this->ThirdParty->recursive=-1;
            $linkedProvider=$this->ThirdParty->find('first',[
              'conditions'=>['ThirdParty.id'=>$providerId,],
            ]);
            $providerName=$linkedProvider['ThirdParty']['company_name'];
              
            //$productMultiplicationOk=true;
            //$productMultiplicationWarning="";
            
            //$productTotalSumBasedOnProductTotals=0;  
              
            foreach ($this->request->data['Product'] as $product){
              // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
              
              // load the product request data into variables
              $productId = $product['product_id'];
              $productQuantity = $product['product_quantity'];
              $productUnitPrice = $product['product_unit_price'];
              $productTotalPrice = $product['product_total_price'];
              
              if ($productQuantity>0 && $productId>0){
                $productUnitCost=$productUnitPrice;
                // get the related product data
                $linkedProduct=$this->Product->find('first',[
                  'conditions'=>['Product.id'=>$productId,],
                  'contain'=>[
                    'StockItem'=>[
                      'conditions'=>[
                        'StockItem.enterprise_id'=>$enterpriseId,
                      ],
                      'StockItemLog'=>[
                        'conditions'=>['StockItemLog.stock_item_date <' => $purchaseDatePlusOne],
                        'order'=>'StockItemLog.stock_item_date DESC, StockItemLog.id DESC',
                      ],
                    ],
                  ],
                ]);
                //pr($linkedProduct);
                $productName=$linkedProduct['Product']['name'];
                
                $originalRemainingQuantity=0;
                $originalUnitCost=0;
                if (!empty($linkedProduct['StockItem'][0]['StockItemLog'])){
                  $originalRemainingQuantity=$linkedProduct['StockItem'][0]['StockItemLog'][0]['product_quantity'];
                  $originalUnitCost=$linkedProduct['StockItem'][0]['StockItemLog'][0]['product_unit_cost'];
                }
                //pr($linkedProduct['StockItem'][0]);
                if ($linkedProduct['Product']['product_type_id'] == PRODUCT_TYPE_FUELS && $linkedProduct['StockItem'][0]['unit_id'] == UNIT_LITERS){
                  $productQuantity=$productQuantity*GALLONS_TO_LITERS;
                }
                $newRemainingQuantity = $originalRemainingQuantity + $productQuantity;
                //PRICE IS ALWAYS SUPPOSED TO BE IN C$
                $newUnitCost=($originalRemainingQuantity*$originalUnitCost + $productTotalPrice)/$newRemainingQuantity;
                // STEP 1: SAVE THE STOCK ITEM
                $stockItemData=[];
                $stockItemData['product_unit_cost']=$newUnitCost;
                $stockItemData['remaining_quantity']=$newRemainingQuantity;
                
                //$stockItemData['name']=$itemMovementName;
                //$stockItemData['description']=$description;
                //$stockItemData['product_id']=$productId;
                
                //$stockItemData['stockitem_creation_date']=$purchaseDate;
                //$stockItemData['original_quantity']=$productQuantity;
                //$stockItemData['product_unit_price']=$productUnitPrice;
                
                $this->StockItem->id=$linkedProduct['StockItem'][0]['id'];
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema guardando el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                $stockItemId=$this->StockItem->id;
                
                // STEP 2: SAVE THE STOCK MOVEMENT
                
                $itemMovementName=$purchaseDate['day']."_".$purchaseDate['month']."_".$purchaseDate['year']."_".$providerName."_".$orderCode."_".$productName;
                $description="Nueva entrada ".$productName." (Quantity:".$productQuantity.",Costo total:".$productTotalCost.") de ".$providerName."_".$orderCode;
                
                $stockMovementData=[];
                $stockMovementData['movement_date']=$purchaseDate;
                $stockMovementData['bool_input']=true;
                $stockMovementData['stock_movement_type_id']=MOVEMENT_PURCHASE;
                $stockMovementData['name']=$itemMovementName;
                $stockMovementData['description']=$description;
                $stockMovementData['order_id']=$purchaseId;
                $stockMovementData['stock_item_id']=$stockItemId;
                $stockMovementData['product_id']=$productId;
                $stockMovementData['product_quantity']=$productQuantity;
                $stockMovementData['product_unit_price']=$productUnitPrice;
                $stockMovementData['product_total_price']=$productTotalPrice;
                $stockMovementData['enterprise_id']=$enterpriseId;
                
                $this->StockMovement->create();
                if (!$this->StockMovement->save($stockMovementData)) {
                  echo "problema guardando el movimiento de inventario";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                $stockMovementId=$this->Order->StockMovement->id;
                
                $this->recreateStockItemLogs($stockItemId,$purchaseDateString);
              }
            }
            
            $datasource->commit();
            $this->recordUserAction($this->Order->id,"add",null);
            // SAVE THE USERLOG FOR THE PURCHASE
            $this->recordUserActivity($this->Session->read('User.username'),"Purchase registered with invoice code ".$this->request->data['Order']['order_code']);
            $this->Session->setFlash(__('The purchase has been saved.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'resumenEntradas']);
          //	return $this->redirect(array('action' => 'verEntrada',$purchaseid));
          } 
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('The purchase could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
			}
		}
    $this->set(compact('requestProducts'));
    
		$thirdParties = $this->Order->ThirdParty->find('list',[
			'conditions' => ['ThirdParty.bool_provider'=> true],
			'order'=>'ThirdParty.company_name ASC',
		]);
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=0;
    
    $productsFuel = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=>PRODUCT_TYPE_FUELS,
				'Product.bool_active'=> true
			],
      'order'=>'Product.product_order'
		]);
    
		$productsOther = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id !='=>PRODUCT_TYPE_FUELS,
				'Product.bool_active'=> true
			],
      'order'=>'Product.name'
		]);
		$products = [];
    foreach ($productsFuel as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		//foreach ($productsOther as $product){
		//	$products[$product['Product']['id']]=$product['Product']['name'];
		//}
		$this->set(compact('thirdParties', 'stockMovementTypes','products'));
    
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function crearVenta() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
   
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		
		$this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		//$this->loadModel('Invoice');
    
		$this->Order->recursive=-1;
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Order->ThirdParty->recursive=-1;
		$this->Order->StockMovementType->recursive=-1;
		$this->AccountingCode->recursive=-1;
		$this->Invoice->recursive=-1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
		}
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId=$_SESSION['enterpriseId'];  
    }
		
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
		
		$inventoryDisplayOptions=array(
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		);
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=0;
			
		$requestProducts=[];
		if ($this->request->is('post')) {	
      //pr($this->request->data);
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
      if ($this->request->data['Order']['third_party_id']==0){
        $this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['Order']['save_allowed']){
        //echo "the value for order save_allowed is ".$this->request->data['Order']['save_allowed']."<br/>";
        $this->Session->setFlash(__('No se puede emitir una factura de crédito!  No se guardaron las medidas.'), 'default',['class' => 'error-message']);
      }
      else {
        $inventoryDisplayOptionId=$this->request->data['Order']['inventory_display_option_id'];
        if (empty($this->request->data['refresh'])&&empty($this->request->data['showinventory'])){
          $saleDate=$this->request->data['Order']['order_date'];
          $saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
          $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
          $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
          $closingDateTime=new DateTime($latestClosingDate);
                
          $saleDateArray=[];
          $saleDateArray['year']=$saleDate['year'];
          $saleDateArray['month']=$saleDate['month'];
          $saleDateArray['day']=$saleDate['day'];
              
          $orderCode=$this->request->data['Order']['order_code'];
          $namedSales=$this->Order->find('all',array(
            'conditions'=>array(
              'order_code'=>$orderCode,
              'stock_movement_type_id'=>MOVEMENT_SALE,
            )
          ));
          if (count($namedSales)>0){
            $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }
          elseif ($saleDateAsString>date('Y-m-d 23:59:59')){
            $this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }
          elseif ($saleDateAsString<$latestClosingDatePlusOne){
            $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }
          elseif ($this->request->data['Invoice']['bool_annulled']){
            $datasource=$this->Order->getDataSource();
            $datasource->begin();
            try {
              //pr($this->request->data);					
              $this->Order->create();
              $OrderData=array();
              $OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
              $OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
              $OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
              $OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
              $OrderData['Order']['bool_annulled']=true;
              $OrderData['Order']['total_price']=0;
          
              if (!$this->Order->save($OrderData)) {
                echo "Problema guardando el orden de salida";
                pr($this->validateErrors($this->Order));
                throw new Exception();
              }
              
              $orderId=$this->Order->id;
              
              $this->Invoice->create();
              $InvoiceData=array();
              $InvoiceData['Invoice']['order_id']=$orderId;
              $InvoiceData['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
              $InvoiceData['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
              $InvoiceData['Invoice']['bool_annulled']=true;
              $InvoiceData['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
              $InvoiceData['Invoice']['sub_total_price']=0;
              $InvoiceData['Invoice']['IVA_price']=0;
              $InvoiceData['Invoice']['total_price']=0;
              $InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
          
              if (!$this->Invoice->save($InvoiceData)) {
                echo "Problema guardando la factura";
                pr($this->validateErrors($this->Invoice));
                throw new Exception();
              }
              
              $datasource->commit();
              $this->recordUserAction();
              // SAVE THE USERLOG 
              $this->recordUserActivity($this->Session->read('User.username'),"Se registró una venta anulada con número ".$this->request->data['Order']['order_code']);
              $this->Session->setFlash(__('Se guardó la venta '.$this->request->data['Order']['order_code'].' anulada.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
              //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
              return $this->redirect(array('action' => 'imprimirVenta',$orderId));
              
              //return $this->redirect(array('action' => 'verVenta',$orderId));
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);
              $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
            }
          }					
          elseif ($this->request->data['Invoice']['total_price']==0){
            $this->Session->setFlash(__('El precio total no puede ser cero para una venta que no está anulada!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }
          else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
            $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }
          else if ($this->request->data['Invoice']['bool_retention']&&strlen($this->request->data['Invoice']['retention_number'])==0){
            $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardaron las medidas.'), 'default',array('class' => 'error-message'));
          }	
          else {
            // before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
            $saleItemsOK=true;
            $exceedingItems="";
            
            $productMultiplicationOk=true;
            $productMultiplicationWarning="";
            
            $productTotalSumBasedOnProductTotals=0;
            
            $productCount=0;
            $products=[];
            foreach ($this->request->data['Product'] as $product){
              //pr($product);
              // keep track of number of rows so that in case of an error jquery displays correct number of rows again
              if ($product['product_id']>0){
                $productCount++;
              }
              // only process lines where product_quantity and product id have been filled out
              
              if ($product['product_quantity']>0 && $product['product_id']>0){
                //echo "product quantity is ".$product['product_quantity']."<br/>";
                $products[]=$product;
                $quantityEntered=$product['product_quantity'];
                $productid = $product['product_id'];
                $productionresultcodeid = $product['production_result_code_id'];
                $rawmaterialid = $product['raw_material_id'];
                
                $this->Product->recursive=-1;
                $relatedProduct=$this->Product->find('first',[
                  'conditions'=>['Product.id'=>$productid,],
                ]);
                if ($relatedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){  
                  if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
                    $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productid,$rawmaterialid,$productionresultcodeid,$saleDateAsString,$enterpriseId,true);
                  }
                  else {
                    $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productid,$saleDateAsString,$enterpriseId,true);
                  }
                  //echo "quantity in stock is ".$quantityInStock."<br>";
                  
                  //compare the quantity requested and the quantity in stock
                  if ($quantityEntered>$quantityInStock){
                    $saleItemsOK=false;
                    $exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
                  }
                }
                
                $productMultiplicationBasedOnUnitPriceAndQuantity=$product['product_quantity']*$product['product_unit_price'];
                $productMultiplicationBasedOnTotalPrice=$product['product_total_price'];
                if (abs($productMultiplicationBasedOnUnitPriceAndQuantity-$productMultiplicationBasedOnTotalPrice) > 0.01){
                  $productMultiplicationOk=false;
                  $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$product['product_quantity']." y un precio unitario ".$product['product_unit_price'].", pero el total calculado ".$product['product_total_price']." no es correcto;";
                }
                //echo "product total price is ".$product['product_total_price']."<br/>";
                $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
              }
            }
            
            if ($exceedingItems!=""){
              $exceedingItems.=__("Please correct and try again!");
            }					
            if (!$productMultiplicationOk){
              $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['Invoice']['sub_total_price']) > 0.01){
              $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Invoice']['sub_total_price'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (abs($this->request->data['Invoice']['total_price']-$this->request->data['Invoice']['IVA_price']-$this->request->data['Invoice']['sub_total_price'])>0.01){
              $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (!$saleItemsOK){
              $_SESSION['saleRequestData']=$this->request->data;
                
              $aco_name="Orders/manipularVenta";		
              $bool_order_manipularventa_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
              
              if ($bool_order_manipularventa_permission){
                return $this->redirect(array('action' => 'manipularVenta'));
              }
            
              $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
            }
            else{
              $totalPriceProducts=0;
              
              $datasource=$this->Order->getDataSource();
              $datasource->begin();
              try {
                $currencyId=$this->request->data['Invoice']['currency_id'];
              
                $retention_invoice=$this->request->data['Invoice']['retention_amount'];
                $sub_total_invoice=$this->request->data['Invoice']['sub_total_price'];
                $IVA_invoice=$this->request->data['Invoice']['IVA_price'];
                $total_invoice=$this->request->data['Invoice']['total_price'];
            
                // if all products are in stock, proceed with the sale 
                $this->Order->create();
                $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
                // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
                if ($currencyId==CURRENCY_USD){
                  $this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
                }
                else {
                  $this->request->data['Order']['total_price']=$sub_total_invoice;
                }
                //pr($this->request->data);
                if (!$this->Order->save($this->request->data)) {
                  echo "Problema guardando la salida";
                  pr($this->validateErrors($this->Order));
                  throw new Exception();
                }
              
                $orderId=$this->Order->id;
                $orderCode=$this->request->data['Order']['order_code'];
              
                $this->Invoice->create();
                $this->request->data['Invoice']['order_id']=$orderId;
                $this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
                $this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
                $this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
                if ($this->request->data['Invoice']['bool_credit']){
                  $this->request->data['Invoice']['bool_retention']=false;
                  $this->request->data['Invoice']['retention_amount']=0;
                  $this->request->data['Invoice']['retention_number']="";
                }
                else {
                  $this->request->data['Invoice']['bool_paid']=true;
                }
            
                if (!$this->Invoice->save($this->request->data)) {
                  echo "Problema guardando la factura";
                  pr($this->validateErrors($this->Invoice));
                  throw new Exception();
                }
                
                $invoice_id=$this->Invoice->id;
                
                // now prepare the accounting registers
                
                // if the invoice is with credit, save one accounting register; 
                // debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
                
                // if the invoice is paid with cash, save two or three accounting register; 
                // debit=caja selected by client, credit = ingresos por venta 401, amount = total
                // debit=?, credit = ?, amount = iva
                // if bool_retention is true
                // debit=?, credit = ?, amount = retention
                
                if ($currencyId==CURRENCY_USD){
                  $this->loadModel('ExchangeRate');
                  $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
                  //pr($applicableExchangeRate);
                  $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $IVA_CS=round($IVA_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $total_CS=round($total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                }
                else {
                  $retention_CS=$retention_invoice;
                  $sub_total_CS=$sub_total_invoice;
                  $IVA_CS=$IVA_invoice;
                  $total_CS=$total_invoice;
                }
                $this->AccountingCode->recursive=-1;
                if ($this->request->data['Invoice']['bool_credit']){
                  $client_id=$this->request->data['Order']['third_party_id'];
                  $this->loadModel('ThirdParty');
                  $this->ThirdParty->recursive=-1;
                  $thisClient=$this->ThirdParty->find('first',array(
                    'conditions'=>array(
                      'ThirdParty.id'=>$client_id,
                    ),
                  ));
                
                  $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                  $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
                  $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
                  $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                  $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                  $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
                  $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                  
                  if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
                      ),
                    ));
                  }
                  else {								
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id'],
                      ),
                    ));
                  }
                  $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
                  
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                  
                  if ($this->request->data['Invoice']['bool_IVA']){
                    $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
                  }
                  
                  //pr($accountingRegisterData);
                  $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                  $this->recordUserAction($this->AccountingRegister->id,"add",null);
              
                  $AccountingRegisterInvoiceData=array();
                  $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                  $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                  $this->AccountingRegisterInvoice->create();
                  if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                    pr($this->validateErrors($this->AccountingRegisterInvoice));
                    echo "problema al guardar el lazo entre asiento contable y factura";
                    throw new Exception();
                  }
                  //echo "link accounting register sale saved<br/>";					
                }
                else {
                  $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                  $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
                  $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
                  $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                  $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                  $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
                  $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                  
                  if (!$this->request->data['Invoice']['bool_retention']){
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
                  }
                  else {
                    // with retention
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS-$retention_CS;
                  }
                  
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                  
                  if ($this->request->data['Invoice']['bool_IVA']){
                    $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
                  }
                  if ($this->request->data['Invoice']['bool_retention']){
                    $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR,
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
                  }
                  
                  //pr($accountingRegisterData);
                  $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                  $this->recordUserAction($this->AccountingRegister->id,"add",null);
                  //echo "accounting register saved for cuentas cobrar clientes<br/>";
              
                  $AccountingRegisterInvoiceData=array();
                  $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                  $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                  $this->AccountingRegisterInvoice->create();
                  if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                    pr($this->validateErrors($this->AccountingRegisterInvoice));
                    echo "problema al guardar el lazo entre asiento contable y factura";
                    throw new Exception();
                  }
                  //echo "link accounting register sale saved<br/>";	
                }
              
                foreach ($products as $product){
                  // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
                  //pr($product);
                  
                  // load the product request data into variables
                  $productId = $product['product_id'];
                  $product_category_id = $this->Product->getProductCategoryId($productId);
                  $production_result_code_id =0;
                  $raw_material_id=0;
                  
                  if ($product_category_id==CATEGORY_PRODUCED){
                    $production_result_code_id = $product['production_result_code_id'];
                    $raw_material_id = $product['raw_material_id'];
                  }
                  
                  $productUnitPrice=$product['product_unit_price'];
                  $productQuantity = $product['product_quantity'];
                  
                  if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                    $productUnitPrice*=$this->request->data['Order']['exchange_rate'];
                  }
                  
                  // get the related product data
                  $this->Product->recursive=-1;
                  $linkedProduct=$this->Product->find('first',[
                    'conditions'=>['Product.id'=>$productId,],
                  ]);
                  $productName=$linkedProduct['Product']['name'];
                  
                  if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                    // STEP 1: SAVE THE STOCK ITEM(S)
                    // first prepare the materials that will be taken out of stock
                    if ($product_category_id==CATEGORY_PRODUCED){
                      $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$production_result_code_id,$productQuantity,$raw_material_id,$saleDateAsString,$enterpriseId);		
                    }
                    else {
                      $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateAsString,$enterpriseId);		
                    }
                    //pr($usedMaterials);
                  
                    for ($k=0;$k<count($usedMaterials);$k++){
                      $materialUsed=$usedMaterials[$k];
                      $stockItemId=$materialUsed['id'];
                      $quantityPresent=$materialUsed['quantity_present'];
                      $quantityUsed=$materialUsed['quantity_used'];
                      $quantityRemaining=$materialUsed['quantity_remaining'];
                      if (!$this->StockItem->exists($stockItemId)) {
                        throw new NotFoundException(__('Invalid StockItem'));
                      }
                      //$linkedStockItem=$this->StockItem->read(null,$stockItemId);
                      $this->StockItem->recursive=-1;
                      $linkedStockItem=$this->StockItem->find('first',[
                        'conditions'=>['StockItem.id'=>$stockItemId,],
                      ]);
                      $message="Se vendió lote ".$productName." (Cantidad:".$quantityUsed.") para Venta ".$orderCode;
                      
                      $stockItemData=array();
                      $stockItemData['id']=$stockItemId;
                      //$stockItemData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                      $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
                      $stockItemData['remaining_quantity']=$quantityRemaining;
                      // notice that no new stockitem is created because we are taking from an already existing one
                      if (!$this->StockItem->save($stockItemData)) {
                        echo "problema al guardar el lote";
                        pr($this->validateErrors($this->StockItem));
                        throw new Exception();
                      }
                      
                      // STEP 2: SAVE THE STOCK MOVEMENT
                      $message="Se vendió ".$productName." (Cantidad:".$quantityUsed.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                      $stockMovementData=array();
                      $stockMovementData['movement_date']=$saleDate;
                      $stockMovementData['bool_input']=false;
                      $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                      $stockMovementData['description']=$message;
                      $stockMovementData['order_id']=$orderId;
                      $stockMovementData['stock_item_id']=$stockItemId;
                      $stockMovementData['product_id']=$productId;
                      $stockMovementData['product_quantity']=$quantityUsed;
                      $stockMovementData['product_unit_price']=$productUnitPrice;
                      $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
                      $stockMovementData['production_result_code_id']=$production_result_code_id;
                      
                      $totalPriceProducts+=$stockMovementData['product_total_price'];
                      
                      $this->StockMovement->create();
                      if (!$this->StockMovement->save($stockMovementData)) {
                        echo "problema al guardar el movimiento de lote";
                        pr($this->validateErrors($this->StockMovement));
                        throw new Exception();
                      }
                    
                      // STEP 3: SAVE THE STOCK ITEM LOG
                      $this->recreateStockItemLogs($stockItemId);
                          
                      // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                      $this->recordUserActivity($this->Session->read('User.username'),$message);
                    }
                  
                  }
                  else {
                    $message="Se vendió ".$productName." (Cantidad:".$productQuantity.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                    $stockMovementData=[];
                    $stockMovementData['movement_date']=$saleDate;
                    $stockMovementData['bool_input']=false;
                    $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                    $stockMovementData['description']=$message;
                    $stockMovementData['order_id']=$orderId;
                    $stockMovementData['stock_item_id']=0;
                    $stockMovementData['product_id']=$productId;
                    $stockMovementData['product_quantity']=$productQuantity;
                    $stockMovementData['product_unit_price']=$productUnitPrice;
                    $stockMovementData['product_total_price']=$productUnitPrice*$productQuantity;
                    $stockMovementData['production_result_code_id']=$production_result_code_id;
                    
                    $totalPriceProducts+=$stockMovementData['product_total_price'];
                    
                    $this->StockMovement->create();
                    if (!$this->StockMovement->save($stockMovementData)) {
                      echo "problema al guardar el movimiento de lote";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                  
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                  }
                  
                }
                
                if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts)>0.01){
                  echo "el subtotal no iguala el precio sumado de los productos";
                  throw new Exception();
                }
                        
                $datasource->commit();
                $this->recordUserAction($this->Order->id,"add",null);
                // SAVE THE USERLOG FOR THE PURCHASE
                $this->recordUserActivity($this->Session->read('User.username'),"Sale registered with invoice code ".$this->request->data['Order']['order_code']);
                $this->Session->setFlash(__('Se guardó la venta.'),'default',['class' => 'success']);
                //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
                return $this->redirect(['action' => 'imprimirVenta',$orderId]);
                // on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
                //return $this->redirect(array('action' => 'verVenta',$orderId));
              }
              catch(Exception $e){
                $datasource->rollback();
                pr($e);
                $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
              }
            }
          }
        }
        
      }
    }
		
		$this->set(compact('enterpriseId'));
		$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestProducts'));
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array(
				'ThirdParty.bool_provider'=> false,
				'ThirdParty.bool_active'=>true,
			),
			'order'=>'ThirdParty.company_name',			
		));
		
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
		if (!empty($this->request->data)){
			// 	20170422 THE ONLY WAY THE REQUEST DATA WOULD BE SET IF THERE WAS A SUBMISSION OF A KIND ALREADY
			// 20170422 THIS WOULD IMPLY THAT THE ORDER DATE COMES IN FORM OF AN ARRAY
			//if (is_array($this->request->data['Order']['order_date'])){
				$orderDateArray=$this->request->data['Order']['order_date'];
				$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
				$orderDate=date("Y-m-d",strtotime($orderDateString));
				$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
			//}
		}
		else {
			// 20170422 CREATION BY DEFAULT HAS CURRENT DATE
			$orderDate=date("Y-m-d",strtotime(date('Y-m-d')));
			$orderDatePlusOne=date("Y-m-d",strtotime(date('Y-m-d')."+1 days"));
		}
		$this->set(compact('orderDate'));
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
		//pr($productsAll);
		$products = [];
		$rawmaterialids=[];
		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					if ($stockitem['remaining_quantity']>0){
						if (!empty($enterpriseId)){
							if ($stockitem['enterprise_id']==$enterpriseId){
								$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
                if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
                  $rawmaterialids[]=$stockitem['raw_material_id'];
                }
							}
						}
						else {
							$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
              if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
                $rawmaterialids[]=$stockitem['raw_material_id'];
              }
						}		
					}
         
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
		}
		//pr($rawmaterialids);
    
		$productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    
    //$rawProductTypeIds=$this->ProductType->find('list',array(
    //  'fields'=>'ProductType.id',
    //  'conditions'=>array(
    //    'ProductType.product_category_id'=> CATEGORY_RAW
    //  ),
    //));
    
    $this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',array(
      'fields'=>array('Product.id','Product.name'),
      'conditions' => [
        //'Product.product_type_id'=> $rawProductTypeIds,
        'Product.id'=>$rawmaterialids,
        'Product.bool_active'=>true
      ],
      //'contain'=>array(
      //	'ProductType',
      //	//'StockItem'=>array(
      //	//	'fields'=> array('remaining_quantity'),
      //  //  'conditions'=>array(
      //  //    'StockItem.bool_active'=>true,
      //  //  ),
      //	//),
      //),
      'order'=>'Product.name',
		));
    //pr($preformasAll);
    
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=substr($preforma['Product']['name'],0,18).(strlen($preforma['Product']['name'])>18?"...":"");
		}
    
		if (!empty($inventoryDisplayOptionId)){
      //echo "inventory display option id is ".$inventoryDisplayOptionId."<br/>";
			$productcategoryid=CATEGORY_PRODUCED;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
      
			$finishedMaterialsInventory =array();
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
			
			//pr($finishedMaterialsInventory);
			$productcategoryid=CATEGORY_OTHER;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
      //pr($otherMaterialsInventory);
      
			
			$productcategoryid=CATEGORY_RAW;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
			
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
			//$rawMaterialsInventory = $this->StockItem->getInventoryTotals($categoryids,$producttypeids);
			//$finishedMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED);
			//$otherMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER);
      
		}
    //echo "inventarios hallados<br/>";
		$currencies = $this->Currency->find('list');
    //echo "currencies found<br/>";
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
		// calculate the code for the new service sheet
		$newInvoiceCode="";
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','currencies','accountingCodes','newInvoiceCode'));
		
		$this->loadModel('ExchangeRate');
		$orderDate=date( "Y-m-d");
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$lastInvoice = $this->Invoice->find('first',array(
			'fields'=>array('Invoice.invoice_code'),
			'order' => array('Invoice.invoice_code' => 'desc'),
		));
		//pr($lastInvoice);
		if ($lastInvoice!= null){
			$newInvoiceCode = $lastInvoice['Invoice']['invoice_code']+1;
		}
		else {
			$newInvoiceCode="1";
		}
		$this->set(compact('newInvoiceCode'));
		
		
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    
    
		/*
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
    */
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
    
	}
	
  public function editarEntrada($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Entrada no válida'));
		}
		
		$this->loadModel('Product');
		$this->loadModel('Tank');
    
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
    
    $this->loadModel('StockMovement');
    $this->loadModel('StockItem');
    $this->loadModel('StockItemLog');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Product->recursive=-1;
    $this->Tank->recursive=-1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
		}
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId=$_SESSION['enterpriseId'];  
    }
		$_SESSION['enterpriseId']=$enterpriseId;
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		$requestProducts=[];
		if ($this->request->is(['post', 'put'])) {
      foreach ($this->request->data['Product'] as $product){
        if ($product['product_id']>0 && $product['product_quantity'] > 0){
          $requestProducts[]=$product;
        }  
      }  
      
			$purchaseDate=$this->request->data['Order']['order_date'];
			$purchaseDateString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
      $purchaseDatePlusOne=date('Y-m-d', strtotime($purchaseDateString."+1 days"));

			$latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDateTime=new DateTime($latestClosingDate);
			
			$previousPurchasesWithThisCode=[];
      $previousPurchase=$this->Order->find('first',['conditions'=>['Order.id'=>$id]]);
			if ($previousPurchase['Order']['order_code']!=$this->request->data['Order']['order_code']){
        $previousPurchasesWithThisCode=$this->Order->find('all',[
          'conditions'=>[
            'Order.order_code'=>$this->request->data['Order']['order_code'],
            'Order.stock_movement_type_id'=>[MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
            'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
            'Order.enterprise_id'=>$enterpriseId,
          ],
        ]);
      }
			
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
      
      $previousStockMovements=$this->Order->StockMovement->find('all',[
        'conditions'=>[
          'StockMovement.order_id'=>$this->request->data['Order']['id']
        ],
        'contain'=>['StockItem'],
      ]);
      
			if ($purchaseDateString>date('Y-m-d H:i')){
				$this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			elseif ($purchaseDateString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($previousPurchasesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			else {
        $productTotalSumBasedOnProductTotals=0;  
        
        $boolMultiplicationOK=true;
        $productMultiplicationWarning="";
        
        $productOutsideCostRange=false;
        $costRangeError="";
        
        $fuelTankCapacityExceeded=false;
        $capacityExceededError="";
   
        foreach ($this->request->data['Product'] as $product){
          $productId = $product['product_id'];
          $productQuantity = $product['product_quantity'];
          $productTotalCost = $product['product_total_price'];
          
          if ($productQuantity>0 && $productId>0){
            $multiplicationDifference=abs($product['product_total_price']-$productQuantity*$product['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK=false;
              $this->Product->recursive=-1;
              $relatedProduct=$this->Product->find('first',[
                'conditions'=>['Product.id'=>$productId,],
              ]);
               $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$productQuantity." y un precio unitario ".$product['product_unit_cost'].", pero el total calculado ".$product['product_total_cost']." no es correcto;";
            };
            $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
            
            $productData=$this->Product->find('first',[
              'conditions'=>['Product.id'=>$productId],
            ]);
            if (!empty($productData)){
              $productMaxCost=$productData['Product']['max_cost'];
              $productMinCost=$productData['Product']['min_cost'];
              if ($productData['Product']['product_type_id'] == PRODUCT_TYPE_FUELS){
                if ($productData['Product']['default_cost_unit_id']==UNIT_LITERS){
                  $productMaxCost*=GALLONS_TO_LITERS;
                  $productMinCost*=GALLONS_TO_LITERS;
                }
                //IF NEEDED ADD ANOTHER CHECK IN CASE ENTRIES WOULD BE IN DOLLARS OR DEFAULT PRODUCT CURRENCIES WOULD BE IN COLLARS
              }
              if ($productMaxCost >0 && $productTotalCost/$productQuantity>$productMaxCost){
                $productOutsideCostRange=true;
                $costRangeError.="Para el producto ".$productData['Product']['name']." el costo unitario registrado ".($productTotalCost/$productQuantity)." C$/galón supera el costo máximo configurado ".$productMaxCost." C$.  ";
              } 
              elseif ($productTotalCost/$productQuantity<$productMinCost){
                $productOutsideCostRange=true;
                $costRangeError.="Para el producto ".$productData['Product']['name']." el costo registrado ".($productTotalCost/$productQuantity)." C$/galón está debajo del costo mínimo configurado ".$productMinCost." C$/galón.  ";
              }
            }
          
            if ($productData['Product']['product_type_id']==PRODUCT_TYPE_FUELS){
              $tankData=$this->Tank->find('first',[
                'conditions'=>[
                  'Tank.product_id'=>$productId,
                  'Tank.enterprise_id'=>$enterpriseId
                ],
                'contain'=>[
                  'Product'=>['StockItem'],
                ]
              ]);
              if (!empty($tankData)){
                $totalCapacityTankGallons=$tankData['Tank']['total_capacity'];
                if ($tankData['Tank']['unit_id'] == UNIT_LITERS){
                  $totalCapacityTankGallons=$totalCapacityTankGallons/GALLONS_TO_LITERS;
                }
                //ONE STOCKITEM PER PRODUCT IS PRESUPPOSED
                $totalCurrentVolumeTankGallons=$tankData['Product']['StockItem'][0]['remaining_quantity'];
                
                if (!empty($previousStockMovements)){
                  foreach ($previousStockMovements as $previousStockMovement){
                    if ($previousStockMovement['StockMovement']['product_id'] == $productId){
                      $totalCurrentVolumeTankGallons-=$previousStockMovement['StockMovement']['product_quantity'];
                    }
                  }
                }  
                if ($tankData['Product']['StockItem'][0]['unit_id'] == UNIT_LITERS){
                  $totalCurrentVolumeTankGallons=$totalCurrentVolumeTankGallons/GALLONS_TO_LITERS;
                }
                //if (($totalCurrentVolumeTankGallons + $productQuantity) > $totalCapacityTankGallons){
                //  $fuelTankCapacityExceeded=true;
                //  $capacityExceededError.="No se puede exceder la capacidad máxima (".$totalCapacityTankGallons." galones) del tanque ".$tankData['Tank']['name'].".  El contenido actual del tanque sin tomar en cuenta esta entrada es ".$totalCurrentVolumeTankGallons." galones y se intentó añadir ".$productQuantity." galones.";
                //}
              }
            }
          }
        }
        if (abs($productTotalSumBasedOnProductTotals-$this->request->data['Order']['subtotal_price']) > 0.01){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Order']['subtotal_price'].'.  Verifique que ha indicado cada producto para que se registró un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif ($productOutsideCostRange){
          $this->Session->setFlash($costRangeError.'No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        //elseif ($fuelTankCapacityExceeded){
        //  $this->Session->setFlash($capacityExceededError.'No se guardó la entrada.', 'default',['class' => 'error-message']);
        //}
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['total_price']-$this->request->data['Order']['iva_price']-$this->request->data['Order']['rent_price']-$this->request->data['Order']['adjustment_price']-$this->request->data['Order']['subtotal_price'])>0.01){
          $this->Session->setFlash('La suma del subtotal, el IVA y la renta no iguala el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        else{
          $obsoleteMovementsRemoved=false;
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            
            if (!empty($previousStockMovements)){
              foreach ($previousStockMovements as $previousStockMovement){
                if (!$this->Order->StockMovement->delete($previousStockMovement['StockMovement']['id'])) {
                  echo "problema al eliminar el movimiento obsoleto de entrada";
                  pr($this->validateErrors($this->Order->StockMovement));
                  throw new Exception();
                }
                $stockItemData=[];
                $stockItemData['StockItem']['remaining_quantity']=($previousStockMovement['StockItem']['remaining_quantity']-$previousStockMovement['StockMovement']['product_quantity']);
                if ($previousStockMovement['StockItem']['remaining_quantity'] == $previousStockMovement['StockMovement']['product_quantity']){
                  $stockItemData['StockItem']['product_unit_cost']=0;  
                }
                else {
                  $stockItemData['StockItem']['product_unit_cost']=($previousStockMovement['StockItem']['remaining_quantity']*$previousStockMovement['StockItem']['product_unit_cost']-$previousStockMovement['StockMovement']['product_total_price'])/($previousStockMovement['StockItem']['remaining_quantity']-$previousStockMovement['StockMovement']['product_quantity']);
                }
                $this->StockItem->id=$previousStockMovement['StockItem']['id'];
                if (!$this->StockItem->save($stockItemData)) {
                  echo "Problema al actualizar el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                
                $this->recreateStockItemLogs($previousStockMovement['StockItem']['id'],$purchaseDateString);
              }
            }
            $datasource->commit();
            $obsoleteMovementsRemoved=true;
          }
          catch(Exception $e){
            $this->Session->setFlash(__('La entrada no se podía editar.'),'default',['class' => 'error-message']);
            $datasource->rollback();
          }
          if ($obsoleteMovementsRemoved){
            try {
              $this->Order->id=$this->request->data['Order']['id'];
            
              $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
              $this->request->data['Order']['currency_id']=CURRENCY_CS;
              $this->request->data['Order']['enterprise_id']=$enterpriseId;
              if (!$this->Order->save($this->request->data)) {
                echo "problema al guardar la entrada";
                pr($this->validateErrors($this->Order));
                throw new Exception();
              }
              
              // get the relevant information of the purchase that was just saved
              $purchaseId=$this->Order->id;
              $orderCode=$this->request->data['Order']['order_code'];
              $providerId=$this->request->data['Order']['third_party_id'];
              // get the related provider data
              $this->ThirdParty->recursive=-1;
              $linkedProvider=$this->ThirdParty->find('first',[
                'conditions'=>['ThirdParty.id'=>$providerId,],
              ]);
              $providerName=$linkedProvider['ThirdParty']['company_name'];
                
              foreach ($this->request->data['Product'] as $product){
                // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
                
                // load the product request data into variables
                $productId = $product['product_id'];
                $productQuantity = $product['product_quantity'];
                $productUnitPrice = $product['product_unit_price'];
                $productTotalPrice = $product['product_total_price'];
                
                if ($productQuantity>0 && $productId>0){
                  $productUnitCost=$productUnitPrice;
                  // get the related product data
                  $linkedProduct=$this->Product->find('first',[
                    'conditions'=>['Product.id'=>$productId,],
                    'contain'=>[
                      'StockItem'=>[
                        'conditions'=>[
                          'StockItem.enterprise_id'=>$enterpriseId,
                        ],
                        //'StockItemLog'=>[
                        //'conditions'=>['StockItemLog.stock_item_date <' => $purchaseDatePlusOne],
                        //  'order'=>'StockItemLog.stock_item_date DESC, StockItemLog.id DESC',
                        //],
                      ],
                    ],
                  ]);
                  //pr($linkedProduct);
                  $productName=$linkedProduct['Product']['name'];
                  
                  $originalRemainingQuantity=0;
                  $originalUnitCost=0;
                  if (!empty($linkedProduct['StockItem'])){
                    $originalRemainingQuantity=$linkedProduct['StockItem'][0]['remaining_quantity'];
                    $originalUnitCost=$linkedProduct['StockItem'][0]['product_unit_cost'];
                  }
                  //pr($linkedProduct['StockItem'][0]);
                  if ($linkedProduct['Product']['product_type_id'] == PRODUCT_TYPE_FUELS && $linkedProduct['StockItem'][0]['unit_id'] == UNIT_LITERS){
                    $productQuantity=$productQuantity*GALLONS_TO_LITERS;
                  }
                  $newRemainingQuantity = $originalRemainingQuantity + $productQuantity;
                  //PRICE IS ALWAYS SUPPOSED TO BE IN C$
                  $newUnitCost=($originalRemainingQuantity*$originalUnitCost + $productTotalPrice)/$newRemainingQuantity;
                  // STEP 1: SAVE THE STOCK ITEM
                  $stockItemData=[];
                  $stockItemData['product_unit_cost']=$newUnitCost;
                  $stockItemData['remaining_quantity']=$newRemainingQuantity;
                 
                  $this->StockItem->id=$linkedProduct['StockItem'][0]['id'];
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema guardando el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $stockItemId=$this->StockItem->id;
                  
                  // STEP 2: SAVE THE STOCK MOVEMENT
                  
                  $itemMovementName=$purchaseDate['day']."_".$purchaseDate['month']."_".$purchaseDate['year']."_".$providerName."_".$orderCode."_".$productName;
                  $description="Nueva entrada ".$productName." (Quantity:".$productQuantity.",Precio total:".$productTotalPrice.") de ".$providerName."_".$orderCode;
                  
                  $stockMovementData=[
                    'movement_date'=>$purchaseDate,
                    'bool_input'=>true,
                    'stock_movement_type_id'=>MOVEMENT_PURCHASE,
                    'name'=>$itemMovementName,
                    'description'=>$description,
                    'order_id'=>$purchaseId,
                    'stock_item_id'=>$stockItemId,
                    'product_id'=>$productId,
                    'product_quantity'=>$productQuantity,
                    'product_unit_price'=>$productUnitPrice,
                    'product_total_price'=>$productTotalPrice,
                    'enterprise_id'=>$enterpriseId,
                  ];
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema guardando el movimiento de inventario";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                  $stockMovementId=$this->Order->StockMovement->id;
                  
                  $this->recreateStockItemLogs($stockItemId,$purchaseDateString);
                }
              }
              
              $datasource->commit();
              $this->recordUserAction($this->Order->id,"edit",null);	
              $this->recordUserActivity($this->Session->read('User.username'),"Se editó entrada número ".$this->request->data['Order']['order_code']);
              
              $this->Session->setFlash(__('Se editó la entrada.'),'default',['class' => 'success']);
              return $this->redirect(['action' => 'resumenEntradas']);
            }
            catch(Exception $e){
              $this->Session->setFlash(__('La entrada no se podía editar.'),'default',['class' => 'error-message']);
              $datasource->rollback();
            }
          }
				}
      }	
		}
		else {
			$this->request->data = $this->Order->find('first', [
				'conditions' => ['Order.id' => $id,],
				'contain'=>[
					'StockMovement'=>[
            'conditions'=>['StockMovement.product_quantity >'=>0,],
						'StockItem'=>[
							//'StockMovement'=>[
							//	'conditions'=>[
              //    'StockMovement.product_quantity >'=>0,
							//		'StockMovement.order_id !='=>$id,
							//	],
							//	'Order',
							//],
						],
					],
				],
			]);
      foreach ($this->request->data['StockMovement'] as $product){
        if ($product['product_id']>0 && $product['product_quantity'] > 0){
          $requestProducts[]=$product;
        }  
      }
      //pr($requestProducts);
    }
    $this->set(compact('requestProducts'));
      
		$thirdParties = $this->Order->ThirdParty->find('list',[
			'conditions' => ['ThirdParty.bool_provider'=> true],
			'order'=>'ThirdParty.company_name ASC',
		]);
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=0;
    
    $productsFuel = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=>PRODUCT_TYPE_FUELS,
				'Product.bool_active'=> true
			],
      'order'=>'Product.product_order'
		]);
    
		$productsOther = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id !='=>PRODUCT_TYPE_FUELS,
				'Product.bool_active'=> true
			],
      'order'=>'Product.name'
		]);
		$products = [];
    foreach ($productsFuel as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		//foreach ($productsOther as $product){
		//	$products[$product['Product']['id']]=$product['Product']['name'];
		//}
		$this->set(compact('thirdParties', 'stockMovementTypes','products'));
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function editarVenta($id = null) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
				
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('AccountingMovement');
		
    $this->Order->recursive=-1;
    $this->StockItem->recursive=-1;
    $this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Order->ThirdParty->recursive=-1;
    $this->Order->StockMovementType->recursive=-1;
    $this->AccountingCode->recursive=-1;
		$this->Invoice->recursive=-1;
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['Order']['enterprise_id'];
		}
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId=$_SESSION['enterpriseId'];  
    }
		$_SESSION['enterpriseId']=$enterpriseId;
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		$inventoryDisplayOptions=array(
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		);
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=0;
		
		$requestProducts=array();
		
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		//pr($this->request->data);
		if ($this->request->is(array('post', 'put'))) {
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
			$inventoryDisplayOptionId=$this->request->data['Order']['inventory_display_option_id'];
			if (empty($this->request->data['refresh'])&&empty($this->request->data['showinventory'])){
				$saleDate=$this->request->data['Order']['order_date'];
				$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDateTime=new DateTime($latestClosingDate);
				
				$saleDateArray=array();
				$saleDateArray['year']=$saleDate['year'];
				$saleDateArray['month']=$saleDate['month'];
				$saleDateArray['day']=$saleDate['day'];
				
				$orderCode=$this->request->data['Order']['order_code'];
        
				$namedSales=$this->Order->find('all',array(
					'conditions'=>array(
						'Order.order_code'=>$orderCode,
						'Order.stock_movement_type_id'=>MOVEMENT_SALE,
						'Order.id !='=>$id,
					)
				));
				
				if (count($namedSales)>0){
					$this->Session->setFlash(__('Ya existe una salida con el mismo código!  No se guardó la salida.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se puede realizar cambios.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['Invoice']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					// first remove existing data
					$stockMovementsOriginalSale=$this->Order->StockMovement->find('all',array(
						'fields'=>array(
							'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stock_item_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
						),
						'conditions' => array(
							'StockMovement.order_id'=> $id
						),
						'contain'=>array(
							'StockItem'=>array(
								'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
								'StockItemLog'=>array(
									'fields'=>array('StockItemLog.id,StockItemLog.stock_item_date'),
								)
							),
							'Product'=>array(
								'fields'=> array('id','name','product_type_id'),
							)
						),						
					));						
          
					$originalInvoice=$this->Invoice->find('first',array(
						'conditions'=>array(
							'Invoice.order_id'=>$id,
						),
						'contain'=>array(
							'AccountingRegisterInvoice'=>array(
								'AccountingRegister'=>array(
									'AccountingMovement'
								),
								'Invoice',
							),
						),
					));						
					try {
						if (!empty($stockMovementsOriginalSale)){
							foreach ($stockMovementsOriginalSale as $originalStockMovement){						
								// set all stockmovements to 0
								$annulledStockMovementData=array();
								$annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
								$annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$annulledStockMovementData['product_quantity']=0;
								$annulledStockMovementData['product_total_price']=0;								
								if (!$this->StockMovement->save($annulledStockMovementData)) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								if ($originalStockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                  // restore the stockitems to their previous level
                  $annulledStockItemData=[];
                  $annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
                  $annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
                  $annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
                  if (!$this->StockItem->save($annulledStockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
                }
								
							}
						}					
						if (!empty($originalInvoice)){				
							if (!empty($originalInvoice['AccountingRegisterInvoice'])){
								foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
									if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
										foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
											$this->AccountingMovement->delete($originalAccountingMovement['id']);
										}
									}
									$this->AccountingRegister->delete($originalAccountingRegisterInvoice['AccountingRegister']['id']);
									$this->AccountingRegisterInvoice->delete($originalAccountingRegisterInvoice['id']);
								}
							}
							$this->Invoice->delete($originalInvoice['Invoice']['id']);
						}						
						$datasource->commit();
						$this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para la anulación de venta ".$this->request->data['Order']['order_code']);
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Problema al eliminar los datos viejos en la anulación.'), 'default',array('class' => 'error-message'));
					}
					// then save the minimum data for the annulled invoice/order				
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						//pr($this->request->data);
						$OrderData=array();
						$OrderData['Order']['id']=$id;
						$OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
            $OrderData['Order']['bool_annulled']=true;
						$OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$OrderData['Order']['total_price']=0;
						$this->Order->id=$id;
						if (!$this->Order->save($OrderData)) {
							echo "Problema guardando la venta";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						$orderId=$this->Order->id;
						
						$InvoiceData=array();
						$InvoiceData['Invoice']['id']=$this->request->data['Invoice']['id'];
						$InvoiceData['Invoice']['order_id']=$orderId;
						$InvoiceData['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
						$InvoiceData['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
						$InvoiceData['Invoice']['bool_annulled']=true;
						$InvoiceData['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
						$InvoiceData['Invoice']['total_price']=0;
						$InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
						$this->Invoice->id=$this->request->data['Invoice']['id'];
						if (!$this->Invoice->save($InvoiceData)) {
							echo "Problema guardando la factura";
							pr($this->validateErrors($this->Invoice));
							throw new Exception();
						}
						
						$datasource->commit();
							
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la venta con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se anuló la venta '.$this->request->data['Order']['order_code'].'.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
						//return $this->redirect(array('action' => 'resumenVentasRemisiones'));
            return $this->redirect(array('action' => 'imprimirVenta',$this->Order->id));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
					}
				}
				else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				else if ($this->request->data['Invoice']['bool_retention']&&strlen($this->request->data['Invoice']['retention_number'])==0){
					$this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}			
				else {
					// 20170420 REMOVE EXISTING DATA
					$stockMovementsOriginalSale=$this->Order->StockMovement->find('all',array(
						'fields'=>array(
							'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stock_item_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
						),
						'conditions' => array(
							'StockMovement.order_id'=> $id
						),
						'contain'=>array(
							'StockItem'=>array(
								'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
								'StockItemLog'=>array(
									'fields'=>array('StockItemLog.id,StockItemLog.stock_item_date'),
								)
							),
							'Product'=>[
								'fields'=> ['id','name','product_type_id'],
							]
						),
					));						
					$originalInvoice=$this->Invoice->find('first',array(
						'conditions'=>array(
							'Invoice.order_id'=>$id,
						),
						'contain'=>array(
							'AccountingRegisterInvoice'=>array(
								'AccountingRegister'=>array(
									'AccountingMovement'
								),
								'Invoice',
							),
						),
					));	
          //pr($stockMovementsOriginalSale);
					//pr($originalInvoice);
					$oldDataRemoved=false;
          $datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						if (!empty($stockMovementsOriginalSale)){
							foreach ($stockMovementsOriginalSale as $originalStockMovement){						
								// set all stockmovements to 0
								$annulledStockMovementData=array();
								$annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
								$annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$annulledStockMovementData['product_quantity']=0;
								$annulledStockMovementData['product_total_price']=0;								
								if (!$this->StockMovement->save($annulledStockMovementData)) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								if ($originalStockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                  // restore the stockitems to their previous level
                  $annulledStockItemData=[];
                  $annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
                  $annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
                  $annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
                  //if ($originalStockMovement['StockItem']['id']==8907){
                  //  pr($annulledStockItemData);
                  //}
                  if (!$this->StockItem->save($annulledStockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
                }
								
							}
						}
            
						if (!empty($originalInvoice)){				
							if (!empty($originalInvoice['AccountingRegisterInvoice'])){
								foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
									//pr($originalAccountingRegisterInvoice);
									if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
										foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
											$this->AccountingMovement->delete($originalAccountingMovement['id']);
										}
									}
									
									$this->AccountingRegister->delete($originalAccountingRegisterInvoice['AccountingRegister']['id']);
									$this->AccountingRegisterInvoice->delete($originalAccountingRegisterInvoice['id']);
								}
							}
							$this->Invoice->delete($originalInvoice['Invoice']['id']);
						}						
						$datasource->commit();
						$this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para venta ".$this->request->data['Order']['order_code']);
						$oldDataRemoved=true;
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
					}
					
          
          //$restoredStockItemForTaponVerde=$this->StockItem->find('first',array(
          //  'conditions'=>array('id'=>8907),
          //  'contain'=>'StockItemLog',
          //));
          //pr($restoredStockItemForTaponVerde);
          
          if ($oldDataRemoved){
						$this->request->data['Order']['id']=$id;
						$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						// get the relevant information of the purchase that was just saved
						$orderId=$id;
						$saleDate=$this->request->data['Order']['order_date'];
						$orderCode=$this->request->data['Order']['order_code'];		
						
						$newDataSaved=false;
						
						$saleItemsOK=true;
						$exceedingItems="";
            
            $productMultiplicationOk=true;
            $productMultiplicationWarning="";
            
            $productTotalSumBasedOnProductTotals=0;
            
						$productCount=0;
						$products=[];
						foreach ($this->request->data['Product'] as $product){
							//pr($product);
							// keep track of number of rows so that in case of an error jquery displays correct number of rows again
							if ($product['product_id']>0){
								$productCount++;
							}
							// only process lines where product_quantity and product id have been filled out
							if ($product['product_quantity']>0 && $product['product_id']>0){
								$products[]=$product;
								$quantityEntered=$product['product_quantity'];
								$productid = $product['product_id'];
								$productionresultcodeid = $product['production_result_code_id'];
								$rawmaterialid = $product['raw_material_id'];
								
								$relatedProduct=$this->Product->find('first',array(
									'conditions'=>array(
										'Product.id'=>$productid,
									),
								));
								if ($relatedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){  
                  if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
                    $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productid,$rawmaterialid,$productionresultcodeid,$saleDateAsString,$enterpriseId,true);
                  }
                  else {
                    $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productid,$saleDateAsString,$enterpriseId,true);
                  }
                  //compare the quantity requested and the quantity in stock
                  if ($quantityEntered>$quantityInStock){
                    $saleItemsOK=false;
                    $exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
                  }
                }
                $productMultiplicationBasedOnUnitPriceAndQuantity=$product['product_quantity']*$product['product_unit_price'];
                $productMultiplicationBasedOnTotalPrice=$product['product_total_price'];
                if (abs($productMultiplicationBasedOnUnitPriceAndQuantity-$productMultiplicationBasedOnTotalPrice) > 0.01){
                  $productMultiplicationOk=false;
                  $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$product['product_quantity']." y un precio unitario ".$product['product_unit_price'].", pero el total calculado ".$product['product_total_price']." no es correcto;";
                }
                //echo "product total price is ".$product['product_total_price']."<br/>";
                $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
							}
						}
						if ($exceedingItems!=""){
							$exceedingItems.=__("Please correct and try again!");
						}					
						
            if (!$productMultiplicationOk){
              $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['Invoice']['sub_total_price']) > 0.01){
              $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Invoice']['sub_total_price'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (abs($this->request->data['Invoice']['total_price']-$this->request->data['Invoice']['IVA_price']-$this->request->data['Invoice']['sub_total_price'])>0.01){
              $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precioi total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (!$saleItemsOK){
							$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
						}
						else{
							$totalPriceProducts=0;
              
              $datasource=$this->Order->getDataSource();
							$datasource->begin();
							try {
								$currencyId=$this->request->data['Invoice']['currency_id'];
							
								$retention_invoice=$this->request->data['Invoice']['retention_amount'];
								$sub_total_invoice=$this->request->data['Invoice']['sub_total_price'];
								$IVA_invoice=$this->request->data['Invoice']['IVA_price'];
								$total_invoice=$this->request->data['Invoice']['total_price'];
						
								// if all products are in stock, proceed with the sale 
								$this->Order->create();
								$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
                $this->request->data['Order']['bool_annulled']=false;
								// ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
								if ($currencyId==CURRENCY_USD){
									$this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
								}
								else {
									$this->request->data['Order']['total_price']=$sub_total_invoice;
								}
							
								if (!$this->Order->save($this->request->data)) {
									echo "Problema guardando la salida";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
							
								$orderId=$this->Order->id;
								$orderCode=$this->request->data['Order']['order_code'];
							
								$this->Invoice->create();
								$this->request->data['Invoice']['order_id']=$orderId;
								$this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
								$this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
								$this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
								if ($this->request->data['Invoice']['bool_credit']){
									$this->request->data['Invoice']['bool_retention']=false;
									$this->request->data['Invoice']['retention_amount']=0;
									$this->request->data['Invoice']['retention_number']="";
								}
								else {
									$this->request->data['Invoice']['bool_paid']=true;
								}
						
								if (!$this->Invoice->save($this->request->data)) {
									echo "Problema guardando la factura";
									pr($this->validateErrors($this->Invoice));
									throw new Exception();
								}
								
								$invoice_id=$this->Invoice->id;
								
								// now prepare the accounting registers
								
								// if the invoice is with credit, save one accounting register; 
								// debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
								
								// if the invoice is paid with cash, save two or three accounting register; 
								// debit=caja selected by client, credit = ingresos por venta 401, amount = total
								// debit=?, credit = ?, amount = iva
								// if bool_retention is true
								// debit=?, credit = ?, amount = retention
								
								if ($currencyId==CURRENCY_USD){
									$this->loadModel('ExchangeRate');
									$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
									//pr($applicableExchangeRate);
									$retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$IVA_CS=round($IVA_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$total_CS=round($total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
								}
								else {
									$retention_CS=$retention_invoice;
									$sub_total_CS=$sub_total_invoice;
									$IVA_CS=$IVA_invoice;
									$total_CS=$total_invoice;
								}
								$this->AccountingCode->recursive=-1;
								if ($this->request->data['Invoice']['bool_credit']){
									$client_id=$this->request->data['Order']['third_party_id'];
									$this->loadModel('ThirdParty');
									$this->ThirdParty->recursive=-1;
									$thisClient=$this->ThirdParty->find('first',array(
										'conditions'=>array(
											'ThirdParty.id'=>$client_id,
										),
									));
								
									$accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
									$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
									$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
									$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
									$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
									$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
									$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
									
									if (empty($thisClient['ThirdParty']['accounting_code_id'])){
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
										//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES
											),
										));
									}
									else {								
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
										//$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id']
											),
										));
									}
									$accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
									$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
									
									$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
									//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
									$accountingCode=$this->AccountingCode->find('first',array(
										'conditions'=>array(
											'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA
										),
									));
									$accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
									$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
									
									if ($this->request->data['Invoice']['bool_IVA']){
										$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
										//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR
											),
										));
										
										$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
										$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
									}
									
									//pr($accountingRegisterData);
									$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
									$this->recordUserAction($this->AccountingRegister->id,"add",null);
							
									$AccountingRegisterInvoiceData=array();
									$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
									$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
									$this->AccountingRegisterInvoice->create();
									if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
										pr($this->validateErrors($this->AccountingRegisterInvoice));
										echo "problema al guardar el lazo entre asiento contable y factura";
										throw new Exception();
									}
									//echo "link accounting register sale saved<br/>";					
								}
								else {
									$accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
									$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
									$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
									$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
									$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
									$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
									$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
									
									if (!$this->request->data['Invoice']['bool_retention']){
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
										//$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
											),
										));
										$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
										$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
									}
									else {
										// with retention
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
										//$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
											),
										));
										$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
										$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS-$retention_CS;
									}
									
									$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
									//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
									$accountingCode=$this->AccountingCode->find('first',array(
										'conditions'=>array(
											'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA
										),
									));
									$accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
									$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
									
									if ($this->request->data['Invoice']['bool_IVA']){
										$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
										//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR
											),
										));
										$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
										$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
									}
									if ($this->request->data['Invoice']['bool_retention']){
										$accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
										//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
										$accountingCode=$this->AccountingCode->find('first',array(
											'conditions'=>array(
												'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR
											),
										));
										$accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
										$accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
									}
									
									//pr($accountingRegisterData);
									$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
									$this->recordUserAction($this->AccountingRegister->id,"add",null);
									//echo "accounting register saved for cuentas cobrar clientes<br/>";
							
									$AccountingRegisterInvoiceData=array();
									$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
									$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
									$this->AccountingRegisterInvoice->create();
									if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
										pr($this->validateErrors($this->AccountingRegisterInvoice));
										echo "problema al guardar el lazo entre asiento contable y factura";
										throw new Exception();
									}
									//echo "link accounting register sale saved<br/>";	
								}
							
								foreach ($products as $product){
									// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
									//pr($product);
									
									// load the product request data into variables
									$productId = $product['product_id'];
									$product_category_id = $this->Product->getProductCategoryId($productId);
									$production_result_code_id =0;
									$raw_material_id=0;
									
									if ($product_category_id==CATEGORY_PRODUCED){
										$production_result_code_id = $product['production_result_code_id'];
										$raw_material_id = $product['raw_material_id'];
									}
									
									$productUnitPrice=$product['product_unit_price'];
									$productQuantity = $product['product_quantity'];
									
									if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
										$productUnitPrice*=$this->request->data['Order']['exchange_rate'];
									}
									
									// get the related product data
									//$linkedProduct=$this->Product->read(null,$productId);
									$this->Product->recursive=-1;
									$linkedProduct=$this->Product->find('first',array(
										'conditions'=>array(
											'Product.id'=>$productId,
										),
									));
									$productName=$linkedProduct['Product']['name'];
									if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                    // STEP 1: SAVE THE STOCK ITEM(S)
                    // first prepare the materials that will be taken out of stock
                    
                    if ($product_category_id==CATEGORY_PRODUCED){
                      $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$production_result_code_id,$productQuantity,$raw_material_id,$saleDateAsString,$enterpriseId);		
                    }
                    else {
                      $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateAsString,$enterpriseId);		
                    }
                    //pr($usedMaterials);

                    for ($k=0;$k<count($usedMaterials);$k++){
                      $materialUsed=$usedMaterials[$k];
                      $stockItemId=$materialUsed['id'];
                      $quantityPresent=$materialUsed['quantity_present'];
                      $quantityUsed=$materialUsed['quantity_used'];
                      $quantityRemaining=$materialUsed['quantity_remaining'];
                      if (!$this->StockItem->exists($stockItemId)) {
                        throw new NotFoundException(__('Invalid StockItem'));
                      }
                      $this->StockItem->recursive=-1;
                      $linkedStockItem=$this->StockItem->find('first',array(
                        'conditions'=>array(
                          'StockItem.id'=>$stockItemId,
                        )
                      ));
                      $message="Se vendió lote ".$productName." (Cantidad:".$quantityUsed.") para Venta ".$orderCode;
                      
                      $stockItemData=array();
                      $stockItemData['id']=$stockItemId;
                      //$stockItemData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                      $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
                      $stockItemData['remaining_quantity']=$quantityRemaining;
                      // notice that no new stockitem is created because we are taking from an already existing one
                      if (!$this->StockItem->save($stockItemData)) {
                        echo "problema al guardar el lote";
                        pr($this->validateErrors($this->StockItem));
                        throw new Exception();
                      }
                      
                      // STEP 2: SAVE THE STOCK MOVEMENT
                      $message="Se vendió ".$productName." (Cantidad:".$quantityUsed.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                      $stockMovementData=array();
                      $stockMovementData['movement_date']=$saleDate;
                      $stockMovementData['bool_input']=false;
                      $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                      $stockMovementData['description']=$message;
                      $stockMovementData['order_id']=$orderId;
                      $stockMovementData['stock_item_id']=$stockItemId;
                      $stockMovementData['product_id']=$productId;
                      $stockMovementData['product_quantity']=$quantityUsed;
                      $stockMovementData['product_unit_price']=$productUnitPrice;
                      $stockMovementData['product_total_price']=$productUnitPrice*$quantityUsed;
                      $stockMovementData['production_result_code_id']=$production_result_code_id;
                      
                      $totalPriceProducts+=$stockMovementData['product_total_price'];
                      
                      $this->StockMovement->create();
                      if (!$this->StockMovement->save($stockMovementData)) {
                        echo "problema al guardar el movimiento de lote";
                        pr($this->validateErrors($this->StockMovement));
                        throw new Exception();
                      }
                    
                      // STEP 3: SAVE THE STOCK ITEM LOG
                      $this->recreateStockItemLogs($stockItemId);
                          
                      // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                      $this->recordUserActivity($this->Session->read('User.username'),$message);
                    }
                  
                  }
                  else {
                    $message="Se vendió ".$productName." (Cantidad:".$productQuantity.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                    $stockMovementData=[];
                    $stockMovementData['movement_date']=$saleDate;
                    $stockMovementData['bool_input']=false;
                    $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                    $stockMovementData['description']=$message;
                    $stockMovementData['order_id']=$orderId;
                    $stockMovementData['stock_item_id']=0;
                    $stockMovementData['product_id']=$productId;
                    $stockMovementData['product_quantity']=$productQuantity;
                    $stockMovementData['product_unit_price']=$productUnitPrice;
                    $stockMovementData['product_total_price']=$productUnitPrice*$productQuantity;
                    $stockMovementData['production_result_code_id']=$production_result_code_id;
                    
                    $totalPriceProducts+=$stockMovementData['product_total_price'];
                    
                    $this->StockMovement->create();
                    if (!$this->StockMovement->save($stockMovementData)) {
                      echo "problema al guardar el movimiento de lote";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                  
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                  }
                  
                }
                
                if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts)>0.01){
                  echo "el subtotal no iguala el precio sumado de los productos";
                  throw new Exception();
                }
												
								$datasource->commit();
								$this->recordUserAction($this->Order->id,"editarVenta",null);
								$newDataSaved=true;
								// SAVE THE USERLOG FOR THE PURCHASE
								$this->recordUserActivity($this->Session->read('User.username'),"Se registró la venta con factura número ".$this->request->data['Order']['order_code']);
								$this->Session->setFlash(__('Se guardó la venta.'),'default',['class' => 'success'],'default',['class' => 'success']);
								//return $this->redirect(array('action' => 'resumenVentasRemisiones'));
                return $this->redirect(['action' => 'imprimirVenta',$this->Order->id]);
								// on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
								//return $this->redirect(array('action' => 'verVenta',$orderId));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
							}
						}
						if (!$newDataSaved){
							$datasource=$this->Order->getDataSource();
							$datasource->begin();	
							try {
								if (!empty($stockMovementsOriginalSale)){
									foreach ($stockMovementsOriginalSale as $originalStockMovement){						
										// set all stockmovements to 0
										$restoredStockMovementData=array();
										$restoredStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
										$restoredStockMovementData['description']=$originalStockMovement['StockMovement']['description'];
										$restoredStockMovementData['product_quantity']=$originalStockMovement['StockMovement']['product_quantity'];
										$restoredStockMovementData['product_total_price']=$originalStockMovement['StockMovement']['product_total_price'];								
										if (!$this->StockMovement->save($restoredStockMovementData)) {
											echo "problema al guardar el movimiento de salida";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										
										// restore the stockitems to their previous level
										$restoredStockItemData=array();
										$restoredStockItemData['id']=$originalStockMovement['StockItem']['id'];
										$restoredStockItemData['description']=$originalStockMovement['StockItem']['description'];
										$restoredStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity'];
										if (!$this->StockItem->save($restoredStockItemData)) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										$this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
									}
								}					
								if (!empty($originalInvoice)){				
									if (!empty($originalInvoice['AccountingRegisterInvoice'])){
										foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
											if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
												foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
													$accountingMovementArray=$originalAccountingMovement;
													$this->AccountingMovement->create();
													if (!$this->AccountingMovement->save($accountingMovementArray)) {
														echo "problema al guardar el movimeinto contable";
														pr($this->validateErrors($this->AccountingMovement));
														throw new Exception();
													}
												}
											}
											$accountingRegisterArray=$originalAccountingRegisterInvoice['AccountingRegister'];
											$this->AccountingRegister->create();
											if (!$this->AccountingRegister->save($restoredStockItemData)) {
												echo "problema al guardar el asiento contable";
												pr($this->validateErrors($this->AccountingRegister));
												throw new Exception();
											}
											$accountingRegisterInvoiceArray=$originalAccountingRegisterInvoice;
											$this->AccountingRegisterInvoice->create();
											if (!$this->AccountingRegisterInvoice->save($accountingRegisterInvoiceArray)) {
												echo "problema al guardar el vínculo entre asiento contable y factura";
												pr($this->validateErrors($this->AccountingRegisterInvoice));
												throw new Exception();
											}
										}
									}
									$invoiceArray=$originalInvoice['Invoice'];
									$this->Invoice->create();
									if (!$this->Invoice->save($invoiceArray)) {
										echo "problema al guardar la factura";
										pr($this->validateErrors($this->Invoice));
										throw new Exception();
									}
									$this->Invoice->delete($originalInvoice['Invoice']['id']);
								}						
								$datasource->commit();
								$this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para venta ".$this->request->data['Order']['order_code']);
								$oldDateRemoved=true;
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
							}
						}
					}
				}
			}
		} 
		else {
			$options = [
				'conditions' => [
					'Order.id' => $id,
				],
				'contain'=>[
					'Invoice'=>[
						'AccountingRegisterInvoice'=>[
							'AccountingRegister'=>[
								'AccountingMovement'
							],
							'Invoice',
						],
					],
				],
			];
			$this->request->data = $this->Order->find('first', $options);
			
			$this->StockMovement->recursive=0;
			$this->StockMovement->virtualFields['total_product_quantity']=0;
			$this->StockMovement->virtualFields['total_product_price']=0;
	
			$stockMovements=$this->StockMovement->find('all',array(
				'fields'=>array(
					//'StockItem.warehouse_id',
					'StockMovement.product_id',
					'StockMovement.production_result_code_id',
					'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity', 
					'SUM(product_total_price) AS StockMovement__total_product_price', 
				),
				'conditions'=>array(
					'StockMovement.product_quantity >'=>0,
					'StockMovement.order_id'=>$id,
				),
				'group'=>'StockMovement.product_id,StockItem.raw_material_id,StockMovement.production_result_code_id',
			));
			if (!empty($stockMovements)){
				$enterpriseId=$stockMovements[0]['StockItem']['warehouse_id'];
				foreach ($stockMovements as $stockMovement){
					//pr($stockMovement);
					$productArray=array();
					$productArray['product_id']=$stockMovement['StockMovement']['product_id'];
					$productArray['raw_material_id']=$stockMovement['StockItem']['raw_material_id'];
					$productArray['production_result_code_id']=$stockMovement['StockMovement']['production_result_code_id'];
					$productArray['product_quantity']=$stockMovement['StockMovement']['total_product_quantity'];
					//$productArray['product_unit_price']=round($stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'],4);
          $productArray['product_unit_price']=$stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'];
					$productArray['product_total_price']=$stockMovement['StockMovement']['total_product_price'];
					$requestProducts[]['Product']=$productArray;
				}
			}
			$this->StockMovement->recursive=-1;
      
		}
    if (array_key_exists(0,$this->request->data['Invoice'])){
      $this->request->data['Invoice']=$this->request->data['Invoice'][0];
    }
		$this->set(compact('enterpriseId'));
		$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestProducts'));
		
		$subtotalNoInvoice=0;
		//pr($this->request->data);
		$bool_invoicetype_editable=true;
		if (!empty($this->request->data['Invoice'])){
			if ($this->request->data['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('list',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$this->request->data['Invoice']['id'],
						'CashReceiptInvoice.amount >'=>0,
					),
				));
				if (count($cashReceiptsForInvoice)>0){
					$bool_invoicetype_editable=false;
				}
			}
		}
		elseif (!empty($this->request->data['StockMovement'])){			
			foreach($this->request->data['StockMovement'] as $productSold){
				$subtotalNoInvoice+=$productSold['StockMovement']['total_product_price'];
			}
		}
		$this->set(compact('subtotalNoInvoice'));
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions'=>array(
				'OR'=>array(
					array(
						'ThirdParty.bool_provider'=>false,
						'ThirdParty.bool_active'=>true,
					),
					array(
						'ThirdParty.id'=>$this->request->data['Order']['third_party_id'],
					),
				),
			),
			'order'=>'ThirdParty.company_name',			
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
		if (is_array($this->request->data['Order']['order_date'])){
			$orderDateArray=$this->request->data['Order']['order_date'];
			$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
			$orderDate=date("Y-m-d",strtotime($orderDateString));
			$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
		}
		else {
			$orderDate=date("Y-m-d",strtotime($this->request->data['Order']['order_date']));
			$orderDatePlusOne=date("Y-m-d",strtotime($this->request->data['Order']['order_date']."+1 days"));
		}
		$this->set(compact('orderDate'));
		
    //pr($requestProducts);
    $finishedProductsInSale=[];
    $rawMaterialsInSale=[];
    foreach ($requestProducts as $requestProduct){
      if (!in_array($requestProduct['Product']['product_id'],$finishedProductsInSale)){
        $finishedProductsInSale[]=$requestProduct['Product']['product_id'];
      }
      if (!in_array($requestProduct['Product']['raw_material_id'],$rawMaterialsInSale)){
        $rawMaterialsInSale[]=$requestProduct['Product']['raw_material_id'];
      }
    }
		$productsAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
        'conditions'=>[
          'OR'=>[
            [
              'Product.bool_active'=>true
            ],
            [
              'Product.id'=>$finishedProductsInSale
            ]
          ],
        ],
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('id','remaining_quantity'),
						'conditions'=>[
							'StockItem.stockitem_creation_date <='=>$orderDatePlusOne,
              'StockItem.bool_active'=>true,
						],
					),
				),
				'order'=>'product_type_id DESC, name ASC',
			)
		);
		//pr($productsAll);
		$products = [];
		$rawmaterialids=[];
		$stockItemsOfSoldProducts=$this->StockMovement->find('all',array(
			'fields'=>array(
				'StockMovement.stock_item_id',
			),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity>0',
			),
			'contain'=>array(
				'StockItem'=>array(
					'fields'=> array('id','product_id','remaining_quantity'),
				),
			),
		));
		//pr($stockItemsOfSoldProducts);
		$stockItemRawMaterialIdsForProductId=array();
		foreach ($stockItemsOfSoldProducts as $soldProduct){
			$stockItemRawMaterialIdsForProductId[$soldProduct['StockItem']['product_id']]=$soldProduct['StockItem']['raw_material_id'];
		}
		//pr($stockItemRawMaterialIdsForProductId);
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if (!empty($product['StockItem'])){
				$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
            if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
              $rawmaterialids[]=$stockitem['raw_material_id'];
            }
					}
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
			if (in_array($product['Product']['id'],array_keys($stockItemRawMaterialIdsForProductId))){
				//pr($product['Product']['id']);
				//pr(array_keys($stockItemRawMaterialIdsForProductId));
				$products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
        if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
          $rawmaterialids[]=$stockItemRawMaterialIdsForProductId[$product['Product']['id']];
        }
			}
		}
		
		$productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    //$rawProductTypeIds=$this->ProductType->find('list',array(
    //  'fields'=>'ProductType.id',
    //  'conditions'=>array(
    //    'ProductType.product_category_id'=> CATEGORY_RAW
    //  ),
    //));
    
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
        'conditions' => array(
					//'Product.product_type_id ='=> $rawProductTypeIds,
					'Product.id'=>$rawmaterialids
          
				),
        'conditions'=>[
          'OR'=>[
            [
              'Product.id'=>$rawmaterialids,
              'Product.bool_active'=>true
            ],
            [
              'Product.id'=>$rawmaterialids,
              'Product.id'=>$rawMaterialsInSale
            ]
          ],
        ],
				//'contain'=>array(
				//	'ProductType',
				//	'StockItem'=>array(
				//		'fields'=> array('remaining_quantity')
				//	),
				//),
				'order'=>'Product.name',
			)
		);
    
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=substr($preforma['Product']['name'],0,18).(strlen($preforma['Product']['name'])>18?"...":"");
		}
		
		if (!empty($inventoryDisplayOptionId)){
			//20170419 THIS SHOULD SHOW THE INVENTORY ON THE DAY OF THE ORDER 
			//20170419 THIS SHOULD SHOW THE INVENTORY FOR THE SELECTED WAREHOUSE 
			$productcategoryid=CATEGORY_PRODUCED;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
      
			$finishedMaterialsInventory =array();
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
			
			$productcategoryid=CATEGORY_OTHER;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
			
			$productcategoryid=CATEGORY_RAW;
			$producttypeids=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
			));
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$enterpriseId);
		}
    
		$currencies = $this->Currency->find('list');
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','otherMaterialsInventory','currencies','accountingCodes','bool_invoicetype_editable'));
		
		$this->loadModel('ExchangeRate');
		$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));


    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    
    $users=$this->User->find('list',[
      'fields'=>['User.id','User.username'],
      'conditions'=>[ 
        'User.role_id' => [ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES],
        'User.id !=' => [11,12,14,15,17,27,29,30],
      ],
      'order'=>'User.username'
    ]);
    //pr($users);
    $this->set(compact('users'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function eliminarEntrada($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Entrada inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockMovement');
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		
		$entry=$this->Order->find('first', [
			'conditions' => ['Order.id' => $id,],
			'contain'=>[
				'StockMovement'=>[
          'Product',
          'StockItem'=>[
            'StockItemLog'=>[
              'conditions'=>['StockItemLog.stock_item_date >=' => $entry['Order']['order_date']],
            ],
          ],
        ],
      ],
		]);
		$flashMessage="";
		$boolDeletionAllowed=true;
		if (!empty($entry['StockMovement'])){
			foreach ($entry['StockMovement'] as $stockMovement){
        if ($stockMovement['product_quantity'] > $stockMovement['StockItem']['remaining_quantity']){
          !$boolDeletionAllowed=false;  
          $flashMessage.="No se puede eliminar la entrada porque el producto ". $stockMovement['Product']['name']." tiene una cantidad en inventario de ".$stockMovement['StockItem']['remaining_quantity']." y el movimiento de entrada correspondía con una cantidad ".$stockMovement['product_quantity'].".   Si se elimina la entrada el inventario quedaría en negativo para este producto.";
        }
      }
    }
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la entrada.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'verEntrada',$id]);
		}
		else {
			$datasource=$this->Order->getDataSource();
			$datasource->begin();
			try {
				//delete all stockMovements and stockItemLogs
				foreach ($entry['StockMovement'] as $stockMovement){
					//pr($stockMovement['StockItem']);
				
					if (!$this->StockMovement->delete($stockMovement['id'])) {
						echo "Problema al eliminar el movimiento de entrada en bodega";
						pr($this->validateErrors($this->StockMovement));
						throw new Exception();
					}
					
					// update stock item quantity
					if (!empty($stockMovement['StockItem']['id'])){
            $stockItemData=[];
            $stockItemData['StockItem']['remaining_quantity']=$stockMovement['StockItem']['remaining_quantity']-$stockMovement['product_quantity'];
            if ($stockMovement['StockItem']['remaining_quantity'] == $stockMovement['product_quantity']){
              $stockItemData['StockItem']['product_unit_cost']=0;  
            }
            else {
              $stockItemData['StockItem']['product_unit_cost']=($stockMovement['StockItem']['remaining_quantity']*$stockMovement['StockItem']['product_unit_cost']-$stockMovement['product_total_price'])/($stockMovement['StockItem']['remaining_quantity']-$stockMovement['product_quantity']);
            }
            $this->StockItem->id=$stockMovement['StockItem']['id'];
						if (!$this->StockItem->save($stockItemData)) {
							echo "Problema al actualizar el lote";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
            
            foreach ($stockMovement['StockItem']['StockItemLog'] as $stockItemLog){
              if (!$this->StockItemLog->delete($stockItemLog['id'])) {
                echo "Problema al eliminar el historial del lote";
                pr($this->validateErrors($this->StockItemLog));
                throw new Exception();
              }
            }
					}
				}			
					
				if (!$this->Order->delete($id)) {
					echo "Problema al eliminar la entrada";
					pr($this->validateErrors($this->Order));
					throw new Exception();
				}
						
				$datasource->commit();
        
        foreach ($entry['StockMovement'] as $stockMovement){
          $this->recreateStockItemLogs($stockMovement['StockItem']['id'],$entry['Order']['order_date']);
        }
        
			/*
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=[);
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
				$deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
				$deletionArray['Deletion']['type']='Order';
				$this->Deletion->save($deletionArray);
			*/			
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la entrada número ".$entry['Order']['order_code']);
						
				$this->Session->setFlash(__('Se eliminó la entrada.'),'default',['class' => 'success']);				
				return $this->redirect(['action' => 'resumenEntradas']);
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la entrada.'), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'verEntrada',$id]);
			}
		}
	}
	
/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$this->Order->recursive=-1;
		$linkedSale=$this->Order->find('first',array(
			'conditions'=>array(
				'Order.id'=>$id,
			),
		));
		$orderCode=$linkedSale['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('Invoice');
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			// find stock movements for order
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stock_item_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',['conditions'=>['StockItem.id'=>$stockMovement['StockMovement']['stock_item_id']]]);
        // 20190429 stockitem is empty for service product type
				if (!empty($stockItem)){
          $stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
          $stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
          if (!$this->StockItem->save($stockItem)) {
            echo "problema eliminando el estado de lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
        }
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				if (!$this->Order->StockMovement->delete()) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
			
			$oldInvoice=$this->Invoice->find('first',array(
				'conditions'=>array(
					'Invoice.order_id'=>$id,
				)
			));
			if (!empty($oldInvoice)){
				// first remove existing data: invoice, accounting registers, accounting register invoice				
				$oldAccountingRegisterInvoices=$this->AccountingRegisterInvoice->find('all',array(
					'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
					'conditions'=>array(
						'invoice_id'=>$oldInvoice['Invoice']['id']
					)
				));
				
				if (!empty($oldAccountingRegisterInvoices)){
					foreach ($oldAccountingRegisterInvoices as $oldAccountingRegisterInvoice){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
					}
				}
				// then remove the invoice
				if (!$this->Invoice->delete($oldInvoice['Invoice']['id'])) {
					echo "problema al eliminar la factura";
					pr($this->validateErrors($this->Invoice));
					throw new Exception();
				}			
			}
			
			$oldCashReceipt=$this->CashReceipt->find('first',array(
				'conditions'=>array(
					'CashReceipt.order_id'=>$id,
				)
			));
			if (!empty($oldCashReceipt)){
				// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
				$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
					'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
					'conditions'=>array(
						'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
					)
				));
				
				if (!empty($oldAccountingRegisterCashReceipts)){
					foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
					}
				}
				// then remove the cash receipt
				if (!$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id'])) {
					echo "problema al eliminar el recibo de caja";
					pr($this->validateErrors($this->CashReceipt));
					throw new Exception();
				}			
			}
			
			
			// delete order
			$success=$this->Order->delete();
			if (!$success) {
				echo "problema al eliminar la venta";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			//recreate stockitemlogs
      foreach ($stockMovements as $stockMovement){
        if (!empty($stockMovement['StockMovement']['stock_item_id'])){
           $this->recreateStockItemLogs($stockMovement['StockMovement']['stock_item_id']);
        }				
			}

			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Order removed with code ".$orderCode);			
			$this->Session->setFlash(__('The sale has been deleted.'), 'default',array('class' => 'success'));
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('The sale could not be deleted. Please, try again.'), 'default',array('class' => 'error-message'));
		}
		return $this->redirect(array('action' => 'resumenVentasRemisiones'));
	}
	
	public function anularVenta ($id=null){
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$this->Order->recursive=-1;
		$linkedSale=$this->Order->find('first',array(
			'conditions'=>array(
				'Order.id'=>$id,
			),
		));
		$orderCode=$linkedSale['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		
		$this->request->allowMethod('post', 'delete');
		
		$oldInvoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
		));
		
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stock_item_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stock_item_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
				$success=$this->StockItem->save($stockItem);
				if (!$success) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				$success=$this->Order->StockMovement->delete();
				if (!$success) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
		
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
			$oldAccountingRegisterInvoices=array();
			if (!empty($oldInvoice)){
				$oldAccountingRegisterInvoices=$this->AccountingRegisterInvoice->find('all',array(
					'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
					'conditions'=>array(
						'invoice_id'=>$oldInvoice['Invoice']['id']
					)
				));
			
				if (!empty($oldAccountingRegisterInvoices)){
					foreach ($oldAccountingRegisterInvoices as $oldAccountingRegisterInvoice){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
					}
				}
				// then remove the invoice
				$this->Invoice->delete($oldInvoice['Invoice']['id']);
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stock_item_id']);
			}
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
		}
		// then save the minimum data for the annulled invoice/order				
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			//pr($this->request->data);
			$OrderData=array();
			$OrderData['Order']['id']=$id;
			$OrderData['Order']['bool_annulled']=true;
			$OrderData['Order']['total_price']=0;
			$this->Order->id=$id;
	
			if (!$this->Order->save($OrderData)) {
				echo "Problema anulando la remisión";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			$orderId=$this->Order->id;
			$this->Order->recursive=-1;
			$linkedOrder=$this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
			//pr($linkedOrder);		
			$this->Invoice->create();
			$InvoiceData=array();
			$InvoiceData['Invoice']['order_id']=$orderId;
			$InvoiceData['Invoice']['order_code']=$linkedOrder['Order']['order_code'];
			$InvoiceData['Invoice']['invoice_date']=date( 'Y-m-d', strtotime($linkedOrder['Order']['order_date']));
			$InvoiceData['Invoice']['bool_annulled']=true;
			$InvoiceData['Invoice']['client_id']=$linkedOrder['Order']['third_party_id'];
			$InvoiceData['Invoice']['total_price']=0;
			$InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
	
			if (!$this->Invoice->save($InvoiceData)) {
				echo "Problema guardando la factura";
				pr($this->validateErrors($this->Invoice));
				throw new Exception();
			}
			$datasource->commit();
				
			// SAVE THE USERLOG 
			$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la venta con número ".$linkedOrder['Order']['order_code']);
			$this->Session->setFlash(__('Se anuló la venta.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'resumenVentasRemisiones'));

		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía anular la venta.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
		}
	}
	
	
	public function verReporteCierre($startDate = null,$endDate=null) {
		$bool_bottles=false;
    echo "role id is ".$this->Auth->User('role_id')."<br/>";
    if ($this->Auth->User('role_id')!=ROLE_SALES){
        $bool_bottles=false;
    }
    else {
      $bool_bottles=true;
    }
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$bool_bottles=$this->request->data['Report']['report_type'];
		}
		else{
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$clients=$this->ThirdParty->find('all',
			array(
				'fields'=>array('id','company_name'),
				'conditions'=>array(
					'bool_provider'=>false,
					'bool_active'=>true,
				),
			)
		);
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		//echo $startDateDay."<br/>";
		//echo $startDateMonth."<br/>";
		//echo $startDateYear."<br/>";
		//echo $endDateDay."<br/>";
		//echo $endDateMonth."<br/>";
		//echo $endDateYear."<br/>";
		$monthArray=array();
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		
		$salesArray=array();
		$clientCounter=0;
		$totalSale=0;
		if (!$bool_bottles){
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$salesCounter=0;
				$totalForClient=0;
				$salesArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$salesArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
					$saleForMonthForClient=$this->Order->find('first',
						array(
							'fields'=>array('SUM(total_price) as totalSale'),
							'conditions'=>array(
								'stock_movement_type_id'=>MOVEMENT_SALE,
								'order_date >='=> $saleStartDate,
								'order_date <'=> $saleEndDate,
								'third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['period']=$period;
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['total']=$saleForMonthForClient[0]['totalSale'];
					if (!empty($saleForMonthForClient)){
						$totalForClient+=$saleForMonthForClient[0]['totalSale'];
						$totalSale+=$saleForMonthForClient[0]['totalSale'];
					}
					$salesCounter++;
				}
				$salesArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}		
		//echo "totalSale is ".$totalSale."<br/>";
		usort($salesArray,array($this,'sortByTotalForClient'));
		//pr($salesArray);
		
		$bottlesAArray=array();
		$totalABottles=0;
		$bottlesBCArray=array();
		$totalBCBottles=0;
		if ($bool_bottles){
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesAArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesAArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id'=>1,
								'StockMovement.bool_reclassification'=>false,
								'StockMovement.product_quantity >'=>0,
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					// if ($clients[$clientCounter]['ThirdParty']['id']==19){
						// $bottlesForProcasa=$this->StockMovement->find('all',array(
							// 'fields'=>array(
								// 'StockMovement.product_quantity',
							// ),
							// 'conditions'=>array(
								// 'StockMovement.production_result_code_id'=>1,
								// 'StockMovement.bool_reclassification'=>false,
								// 'StockMovement.product_quantity >'=>0,
								// 'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								// 'Order.order_date >='=> $saleStartDate,
								// 'Order.order_date <'=> $saleEndDate,
								// 'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							// ),
							// 'contain'=>array(
								// 'Order'=>array(
									// 'fields'=>array(
										// 'Order.order_code','Order.order_date',
									// ),
								// ),
							// ),
						// ));
						// //pr($bottlesForProcasa);
					// }
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalABottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesAArray[$clientCounter]['totalForClient']=$totalForClient;
			}
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesBCArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesBCArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id >'=>1,
								'StockMovement.bool_reclassification'=>false,
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalBCBottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesBCArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}
		
		usort($bottlesAArray,array($this,'sortByTotalForClient'));
		usort($bottlesBCArray,array($this,'sortByTotalForClient'));
		
		$this->set(compact('clients','monthArray','salesArray','bottlesAArray','bottlesBCArray','startDate','endDate','totalSale','totalABottles','totalBCBottles','bool_bottles'));
	}
	
	public function sortByTotalForClient($a,$b ){ 
	  if( $a['totalForClient'] == $b['totalForClient'] ){ return 0 ; } 
	  return ($a['totalForClient'] < $b['totalForClient']) ? 1 : -1;
	} 
	
	public function guardarReporteCierre() {
		$exportData=$_SESSION['reporteCierre'];
		$this->set(compact('exportData'));
	}
	
	public function verVentasPorCliente($id=0){
		$this->loadModel('StockItem');
		$startDate = null;
		$endDate = null;
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
		}
		else {
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		$monthArray=array();
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		//pr($monthArray);
		$this->loadModel('ThirdParty');
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$id)));
		
		$salesArray=array();
		$salesCounter=0;
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		$totalSale=0;
		$totalCost=0;
		$totalProfit=0;
		
		for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
			$period=$monthArray[$salePeriod]['period'];
			$start=$monthArray[$salePeriod]['start'];
			$month=$monthArray[$salePeriod]['month'];
			//echo "saleperiod is ".$salePeriod."<br/>";
			//echo "month is ".$month."<br/>";
			$nextmonth=($month==12)?1:($month+1);
			$year=$monthArray[$salePeriod]['year'];
			$nextyear=($month==12)?($year+1):$year;
			$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
			$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			$salesForMonthForClient=$this->Order->find('all',
				array(
					'fields'=>array('total_price','order_date','order_code'),
					'conditions'=>array(
						'stock_movement_type_id'=>MOVEMENT_SALE,
						'order_date >='=> $saleStartDate,
						'order_date <'=> $saleEndDate,
						'third_party_id'=>$client['ThirdParty']['id']
					),
					'contain'=>array(
						'ThirdParty'=>array('fields'=>'company_name'),
						'StockMovement'=>array(
							'fields'=>array('id','movement_date','order_id','stock_item_id','product_quantity','product_unit_price','product_total_price'),
							'conditions'=>array('StockMovement.product_quantity >'=>0),
							'Product'=>array(
								'fields'=>array('id','packaging_unit'),	
								'ProductType'=>array('fields'=>'product_category_id'),
								
							)
						)
					),
					'order'=>'Order.order_date ASC, Order.id ASC'
				)
			);
			//pr($salesForMonthForClient);
			
			$totalQuantityProducedMonth=0;
			$totalQuantityOtherMonth=0;
			$totalSaleMonth=0;
			$totalCostMonth=0;
			$totalProfitMonth=0;
			
			$processedSales=array();
			for ($s=0;$s<count($salesForMonthForClient);$s++){
				//pr($salesForMonthForClient[$s]);
				$processedSales[$s]['order_date']=$salesForMonthForClient[$s]['Order']['order_date'];
				$processedSales[$s]['order_id']=$salesForMonthForClient[$s]['Order']['id'];
				$processedSales[$s]['order_code']=$salesForMonthForClient[$s]['Order']['order_code'];
				$amountBottles=0;
				$amountCaps=0;
				$productTotalPrice=0;
				$productTotalCost=0;
				$productTotalUtility=0;
				
				foreach ($salesForMonthForClient[$s]['StockMovement'] as $stockMovement){
					if ($stockMovement['product_quantity']>0){
						if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
							$amountBottles+=$stockMovement['product_quantity'];
						}
						else {
							$amountCaps+=$stockMovement['product_quantity'];
						}
						$productTotalPrice+=$stockMovement['product_total_price'];
						$this->StockItem->recursive=-1;
						$stockItem=$this->StockItem->find('first',array(
							'conditions'=>array(
								'StockItem.id'=>$stockMovement['stock_item_id']
							),
						));
						$productTotalCost+=$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price'];
						$productTotalUtility+=($stockMovement['product_total_price']-$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price']);
					}
				}
				
				$processedSales[$s]['amount_bottles']=$amountBottles;
				$processedSales[$s]['amount_caps']=$amountCaps;
				$processedSales[$s]['product_total_price']=$productTotalPrice;
				$processedSales[$s]['product_total_cost']=$productTotalCost;
				$processedSales[$s]['product_total_utility']=$productTotalUtility;
			}
			
			foreach ($salesForMonthForClient as $saleForMonth){
				$totalSaleMonth+=$saleForMonth['Order']['total_price'];
				foreach ($saleForMonth['StockMovement'] as $stockMovement){
					//pr($stockMovement);
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
						$totalQuantityProducedMonth+=$stockMovement['product_quantity'];
					}
					else {
						$totalQuantityOtherMonth+=$stockMovement['product_quantity'];
					}
					$relatedStockitem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['stock_item_id'])));
					$unitcost=$relatedStockitem['StockItem']['product_unit_price'];
					$totalCostMonth+=$stockMovement['product_quantity']*$unitcost;
				}
			}
			$totalProfitMonth=$totalSaleMonth-$totalCostMonth;
			
			$totalQuantityProduced+=$totalQuantityProducedMonth;
			$totalQuantityOther+=$totalQuantityOtherMonth;
			$totalSale+=$totalSaleMonth;
			$totalCost+=$totalCostMonth;
			$totalProfit+=$totalProfitMonth;
			
			//$salesArray[$salesCounter]['clientid']=$client['ThirdParty']['id'];
			$salesArray[$salesCounter]['period']=$period;
			
			$salesArray[$salesCounter]['sales']=$processedSales;
			
			$salesArray[$salesCounter]['totalSaleMonth']=$totalSaleMonth;
			$salesArray[$salesCounter]['totalQuantityProducedMonth']=$totalQuantityProducedMonth;
			$salesArray[$salesCounter]['totalQuantityOtherMonth']=$totalQuantityOtherMonth;
			$salesArray[$salesCounter]['totalCostMonth']=$totalCostMonth;
			$salesArray[$salesCounter]['totalProfitMonth']=$totalProfitMonth;
			
			$salesCounter++;
		}
		//echo "totalSale is ".$totalSale."<br/>";
		
		$totals=array();
		$totals['totalQuantityProduced']=$totalQuantityProduced;
		$totals['totalQuantityOther']=$totalQuantityOther;
		$totals['totalSale']=$totalSale;
		$totals['totalCost']=$totalCost;
		$totals['totalProfit']=$totalProfitMonth;
		
		$this->set(compact('client','monthArray','salesArray','startDate','endDate','totals'));
	}
	
	public function guardarReporteVentasCliente($clientname){
		$exportData=$_SESSION['reporteVentasPorCliente'];
		$this->set(compact('exportData','clientname'));
	}

}





