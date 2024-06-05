<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PaymentReceiptsController extends AppController {
	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel']; 

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getpendingfuelbondsforclient','changePaidStatus');		
	}
  
  public function getpendingfuelbondsforclient(){
		$clientId=trim($_POST['clientid']);
		$receiptday=trim($_POST['receiptday']);
		$receiptmonth=trim($_POST['receiptmonth']);
		$receiptyear=trim($_POST['receiptyear']);
		$cashReceiptCurrencyId=trim($_POST['currencyid']);
		
    //$boolRetention=trim($_POST['boolretention']);
		//if ($boolRetention=="true"){
		//	$boolRetention=1;
		//}
		//else {
		//	$boolRetention=0;
		//}
		
		if (!$clientId){
			throw new NotFoundException(__('Identificación de cliente no es presente'));
		}
		
		$this->layout = "ajax";
		
		$this->loadModel('CashReceiptPaymentReceipt');
		$this->loadModel('ExchangeRate');
		
		$receiptDateString=$receiptyear.'-'.$receiptmonth.'-'.$receiptday;
		$receiptDate=date( "Y-m-d", strtotime($receiptDateString));
		$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($receiptDate);
		$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
		
		$fuelBondsForClient=$this->PaymentReceipt->getPendingFuelBonds($clientId);
		
		for ($i=0;$i<count($fuelBondsForClient);$i++){
			$totalForFuelBond=$fuelBondsForClient[$i]['PaymentReceipt']['payment_amount'];
			$pendingForFuelBond=$totalForFuelBond;
			$fuelBondCurrencyId=$fuelBondsForClient[$i]['PaymentReceipt']['currency_id'];
			$fuelBondDate=$fuelBondsForClient[$i]['PaymentReceipt']['payment_date'];
			
			// add the retention amount
			// IF STATEMENT ELIMINATED AS WE WANT THE RETENTION INTHE ORIGINAL CURRENCY
			$fuelBondsForClient[$i]['PaymentReceipt']['retention']=round($fuelBondsForClient[$i]['PaymentReceipt']['payment_amount']*0.02,2);
			// look up the exchange rate difference
			$difference_exchange_rates=0;
			$exchange_rate_difference=0;
			$fuelBondExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($fuelBondDate);
			$exchangeRateFuelBondDate=$fuelBondExchangeRate['ExchangeRate']['rate'];
			if ($fuelBondCurrencyId==CURRENCY_USD){
				if (($exchangeRateCashReceipt-$exchangeRateFuelBondDate)>0.00001){
					$difference_exchange_rates=$exchangeRateCashReceipt-$exchangeRateFuelBondDate;
					$exchange_rate_difference=round($totalForFuelBond*$difference_exchange_rates,2);
					if ($exchange_rate_difference<0){
						$exchange_rate_difference=0;
					}
				}
			}
			$fuelBondsForClient[$i]['PaymentReceipt']['payment_receipt_exchange_rate']=$exchangeRateFuelBondDate;
			$fuelBondsForClient[$i]['PaymentReceipt']['difference_exchange_rates']=$difference_exchange_rates;
			$fuelBondsForClient[$i]['PaymentReceipt']['exchange_rate_difference']=$exchange_rate_difference;
			
			// get the amount already paid for this payment receipt
			// NOTICE THAT WE USE RATE OF CURRENT DATE FOR PENDING CALCULATION
			$fuelBondsForClient[$i]['PaymentReceipt']['paid_already_CS']=round($this->PaymentReceipt->getAmountPaidAlreadyCS($fuelBondsForClient[$i]['PaymentReceipt']['id']),2);
			
			$diferenciaCambiariaPagado=0;
			if ($fuelBondsForClient[$i]['PaymentReceipt']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptPaymentReceipt');
				$cashReceiptPaymentReceipts=$this->CashReceiptPaymentReceipt->find('all',[
					'conditions'=>[
						'CashReceiptPaymentReceipt.payment_receipt_id'=>$fuelBondsForClient[$i]['PaymentReceipt']['id'],
					],
					'contain'=>[
						'CashReceipt'=>[
							'fields'=>[
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							],
						],
						'Currency'=>[
							'fields'=>[
								'Currency.abbreviation','Currency.id',
							],
						],
					],
				]);
				$fuelBondExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($fuelBondsForClient[$i]['PaymentReceipt']['payment_date']);
				$exchangeRateFuelBondDate=$fuelBondExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptPaymentReceipts)){
					for ($cri=0;$cri<count($cashReceiptPaymentReceipts);$cri++){
						$previousCashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptPaymentReceipts[$cri]['CashReceipt']['receipt_date']);
						$exchangeRatePreviousCashReceiptDate=$previousCashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateCashReceipt-$exchangeRatePreviousCashReceiptDate;
						//echo "difference exchange rate between now and cashreceipt is".$differenceExchangeRateNowCashReceipt."<br/>";
						//echo "payment_credit_CS is".$cashReceiptPaymentReceipts[$cri]['CashReceiptPaymentReceipt']['payment_credit_CS']."<br/>";
						//echo "exchange rate previous cash receipts is".$exchangeRatePreviousCashReceiptDate."<br/>";
						//echo "diferenciaCambiariaPagado is".$diferenciaCambiariaPagado."<br/>";
						$diferenciaCambiariaPagado+=$differenceExchangeRateNowCashReceipt*$cashReceiptPaymentReceipts[$cri]['CashReceiptPaymentReceipt']['payment_credit_CS']/$exchangeRatePreviousCashReceiptDate;
						//echo "diferenciaCambiariaPagado is ".$diferenciaCambiariaPagado."<br/>";
					}
				}
			}
			$fuelBondsForClient[$i]['PaymentReceipt']['diferencia_cambiaria_pagado']=round($diferenciaCambiariaPagado,2);
		}
		
		//pr($fuelBondsForClient);
		$this->set(compact('fuelBondsForClient','id','cashReceiptCurrencyId','exchangeRateCashReceipt','boolRetention'));
	}

	public function changePaidStatus($paymentReceiptId){
		$this->autoRender=false;
	
		if (!$this->PaymentReceipt->exists($paymentReceiptId)) {
			throw new NotFoundException(__('Bono de combustible no válida'));
		}
		
		$paymentReceipt=$this->PaymentReceipt->find('first',[
			'conditions'=>[
				'PaymentReceipt.id'=>$paymentReceiptId,
			],
      'contain'=>['ThirdParty'],
		]);
		if (!empty($paymentReceipt)){
			$this->PaymentReceipt->id=$paymentReceiptId;
			$paymentReceiptData['PaymentReceipt']['id']=$paymentReceiptId;
			$paymentReceiptData['PaymentReceipt']['bool_paid']=!$paymentReceipt['PaymentReceipt']['bool_paid'];
			if ($this->PaymentReceipt->save($paymentReceiptData)) {
        $paymentDateTime=$paymentReceipt['PaymentReceipt']['payment_date'];
				$this->recordUserAction();
				$this->Session->setFlash(__('Se cambió el estado de pago de bono de combustible '.$paymentDateTime->format('d-m-Y').' y cliente '.$paymentReceipt['Client']['company_name'].'.'),'default',['class' => 'success']);
				return $this->redirect(['controller'=>'paymentReceipts','action' => 'registrarRecibos',$paymentDateTime->format('Y-m-d')]);
			} 
		}
		
		$this->Session->setFlash(__('No se podía modificar el estado de pagado del bono de combustible.'), 'default',['class' => 'error-message']); 
		return $this->redirect(Router::url( $this->referer(), true ));		
	}
  
  public function registrarRecibos($saleDateAsString = '',$filterPaymentModeId=0,$filterCurrencyId=0) {
    $this->loadModel('ClosingDate');
    $this->loadModel('Shift');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Product');
    $this->loadModel('StockMovement');
    
    $this->loadModel('TankMeasurement');
    $this->loadModel('HoseMeasurement');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('Order');
    $this->loadModel('Invoice');
    
    $this->loadModel('ThirdParty');
    $this->loadModel('Currency');
    $this->loadModel('Operator');
    
    $this->Shift->recursive=-1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $paymentDate = null;
    if ($this->request->is('post')) {
      $paymentDateArray=$this->request->data['PaymentReceipt']['payment_date'];
      $paymentDateAsString=$paymentDateArray['year'].'-'.$paymentDateArray['month'].'-'.$paymentDateArray['day'];
      $paymentDate=date( "Y-m-d", strtotime($paymentDateAsString));
      $_SESSION['paymentDate']=$paymentDate;
        
      $enterpriseId=$this->request->data['PaymentReceipt']['enterprise_id'];
      $filterPaymentModeId=$this->request->data['Filter']['payment_mode_id'];
      $filterCurrencyId=$this->request->data['Filter']['currency_id'];
		}
    elseif (!empty($saleDateAsString)){
      $paymentDate=date( "Y-m-d", strtotime($saleDateAsString));
    }
    elseif (!empty($_SESSION['paymentDate'])){
			$paymentDate=$_SESSION['paymentDate'];      
		}
		else {
			$paymentDate = date( "Y-m-d", strtotime(date('Y-m-d')."-1 days"));
		}
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId=$_SESSION['enterpriseId'];  
    }
		$_SESSION['paymentDate']=$paymentDate;
		$paymentDateAsString=$paymentDate;
    $paymentDatePlusOne= date( "Y-m-d", strtotime( $paymentDate."+1 days" ) );
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    
    // LOOK UP THE ORDER ID TO LINK DIRECTLY INT HE INVOICES
    
    $orderId=0;
    if($enterpriseId > 0){
      $orderId=$this->Order->getOrderIdForEnterpriseAndDate($enterpriseId,$paymentDate);
    }
    //echo "orderId is ".$orderId."<br/>";
    $this->set(compact('orderId','orders'));
    $enterpriseShiftIds=$this->Shift->find('list',[
      'fields'=>['Shift.id'],
      'conditions'=>[
        'Shift.enterprise_id'=>$enterpriseId,
        'Shift.bool_active'=>true,
      ],
    ]);
    
    $paymentModes=$this->PaymentMode->find('list');
    $this->set(compact('paymentModes'));
    
    $clients=$this->ThirdParty->getActiveClientListForEnterprise($enterpriseId,20);
    $this->set(compact('clients'));
    $fullClients=$this->ThirdParty->getActiveClientListForEnterprise($enterpriseId);
    $this->set(compact('fullClients'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    /*
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    */
    $fuels=$this->Product->find('list',[
      'fields'=>['Product.id','Product.name'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    $fuelIds=array_keys($fuels);
    //pr($fuels);
    //pr($fuelIds);
    $stockMovementConditions=[
      'StockMovement.movement_date >='=>$paymentDate,
      'StockMovement.movement_date <'=>$paymentDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE],
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.enterprise_id'=>$enterpriseId,
    ];
    $calibrationMovementConditions=[
      'StockMovement.movement_date >='=>$paymentDate,
      'StockMovement.movement_date <'=>$paymentDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_CALIBRATION],
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.enterprise_id'=>$enterpriseId,
    ];
    
    $this->StockMovement->virtualFields['total_product_price']=0;
    $stockMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'shift_id',
        'operator_id',
        'SUM(product_total_price) AS StockMovement__total_product_price',
      ],
      'conditions'=>$stockMovementConditions,
      'order'=>'shift_id, operator_id',
      'group'=>'StockMovement.shift_id, StockMovement.operator_id'
    ]);
    $this->StockMovement->virtualFields['total_product_price']=0;
    $calibrationMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'shift_id',
        'SUM(product_total_price) AS StockMovement__total_product_price'
      ],
      'conditions'=>$calibrationMovementConditions,
      'order'=>'shift_id',
      'group'=>'StockMovement.shift_id'
    ]);
    //pr($calibrationMovements);
    
    $this->StockMovement->virtualFields['total_product_price']=0;
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $fuelMovementTotals=$this->StockMovement->find('all',[
      'fields'=>[
        'StockMovement.product_id',
        'Product.name',
        'Product.product_order',
        'StockMovement.product_unit_price',
        'SUM(StockMovement.product_total_price) AS StockMovement__total_product_price',
        'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$stockMovementConditions,
      'order'=>'Product.product_order',
      'group'=>'StockMovement.product_id'
    ]);
    $this->StockMovement->virtualFields['total_product_price']=0;
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $calibrationMovementTotals=$this->StockMovement->find('all',[
      'fields'=>[
        'StockMovement.product_id',
        'StockMovement.product_unit_price',
        'SUM(StockMovement.product_total_price) AS StockMovement__total_product_price',
        'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$calibrationMovementConditions,
      'order'=>'Product.product_order',
      'group'=>'StockMovement.product_id'
    ]);
    
    $fuelTotals=[];
    foreach ($fuels as $fuelId=>$fuelName){
      $fuelTotals[$fuelId]['name']=$fuelName;
      $boolFuelUsed=false;
      foreach ($fuelMovementTotals as $fuelMovementTotal){
        if ($fuelMovementTotal['StockMovement']['product_id'] == $fuelId){
          $fuelTotals[$fuelId]['total_gallons']=$fuelMovementTotal['StockMovement']['total_product_quantity'];
          $fuelTotals[$fuelId]['unit_price']=$fuelMovementTotal['StockMovement']['product_unit_price'];
          $fuelTotals[$fuelId]['total_price']=$fuelMovementTotal['StockMovement']['total_product_price'];
          $boolFuelUsed=true;
        }
      }
      if (!$boolFuelUsed){
        $fuelTotals[$fuelId]['total_gallons']=0;
        $fuelTotals[$fuelId]['unit_price']=$this->Product->ProductPriceLog->getLatestPrice($fuelId,$paymentDate,$enterpriseId);
        $fuelTotals[$fuelId]['total_price']=0;
      }
      foreach ($calibrationMovementTotals as $calibrationMovementTotal){
        if ($calibrationMovementTotal['StockMovement']['product_id'] == $fuelId){
          $fuelTotals[$fuelId]['total_gallons']-=$calibrationMovementTotal['StockMovement']['total_product_quantity'];
          $fuelTotals[$fuelId]['total_price']-=$calibrationMovementTotal['StockMovement']['total_product_price'];
        }
      }
    }
    //pr($fuelTotals);
    $this->set(compact('fuelTotals'));
    $requestShifts=[];
    $boolEditingMode=false;
    $boolEditingToggleVisible=false;
    
		if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	
      //pr($this->request->data);
      $boolEditingMode=true;
      foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
        if (!empty($shiftData['Operator'])){
          //pr($shiftData);
          foreach ($shiftData['Operator'] as $operatorId=>$operatorData){
            $paymentModeData=[];  
            foreach ($operatorData['PaymentReceipt'] as $paymentReceiptData){
              $paymentAmount=$paymentReceiptData['payment_amount'];
              if ($paymentAmount > 0){
                $paymentModeId=$paymentReceiptData['payment_mode_id'];  
                $currencyId=$paymentReceiptData['currency_id'];  
                //$paymentModeData[$paymentModeId]['Currency'][$currencyId]=['payment_amount'=>$paymentAmount,];
                $paymentModeData[$paymentModeId]['Currency'][$currencyId]=$paymentAmount;
                $paymentModeData[$paymentModeId]['InvoiceData']['invoice_list']='';
                $paymentModeData[$paymentModeId]['InvoiceData']['invoice_total_amount']=0;
                $paymentModeData[$paymentModeId]['InvoiceData']['Invoice']=[];
                if (!empty($paymentReceiptData['InvoiceData']['Invoice'])){
                  $paymentModeData[$paymentModeId]['InvoiceData']['invoice_list']=$paymentReceiptData['InvoiceData']['invoice_list'];
                  $paymentModeData[$paymentModeId]['InvoiceData']['invoice_total_amount']=$paymentReceiptData['InvoiceData']['invoice_total_amount'];
                  $iCounter=0;
                  foreach ($paymentReceiptData['InvoiceData']['Invoice'] as $invoice){
                    $paymentModeData[$paymentModeId]['InvoiceData']['Invoice'][$iCounter]['id']=$invoice['id'];
                    $iCounter++;
                  }
                }  
              }
              /*
              if (!array_key_exists($paymentModeId,$paymentModeData)){
                $paymentModeData[$paymentModeId]=[
                  'InvoiceData'=>[
                    'invoice_list'=>'',
                    'invoice_total_amount'=>0,
                    'Invoice'=>[],
                  ]
                ];
              }
              */
              //$paymentModeData[$paymentModeId]['InvoiceData']['invoice_list']='';
              //$paymentModeData[$paymentModeId]['InvoiceData']['invoice_total_amount']=0;
              //$paymentModeData[$paymentModeId]['InvoiceData']['Invoice']=[];
              
            }
            foreach ($operatorData['Credit'] as $creditData){
              $paymentAmount=$creditData['payment_amount'];
              if ($paymentAmount > 0){
                $clientId=$creditData['client_id'];
                $paymentModeId=$creditData['payment_mode_id'];  
                if (!empty($creditData['InvoiceData']['Invoice'])){
                  $invoiceData=[];
                  $invoiceData['invoice_list']=$creditData['InvoiceData']['invoice_list'];
                  $invoiceData['invoice_total_amount']=$creditData['InvoiceData']['invoice_total_amount'];
                  $iCounter=0;
                  foreach ($creditData['InvoiceData']['Invoice'] as $invoice){
                    $invoiceData['Invoice'][$iCounter]['id']=$invoice['id'];
                    $iCounter++;
                  }
                }
                $paymentModeData[$paymentModeId][]=[
                  'payment_amount'=>$paymentAmount,
                  'client_id'=>$clientId,
                  'InvoiceData'=>$invoiceData,
                ];
              }
              
            }  
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']=$paymentModeData;  
          }
          //$requestShifts[][$shiftId]=$shiftData;
        }  
      }
      //pr($requestShifts);      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
      
      $clientNameProblem=false;
      $clientNameErrorText="";
      foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
        foreach ($shiftData['Operator'] as $operatorId=>$operatorData){
          foreach ($operatorData['Credit'] as $creditIndex => $creditPaymentReceipt){
            if ($creditPaymentReceipt['payment_amount']>0 && $creditPaymentReceipt['client_id']==0){
              $clientNameProblem=true;
              $clientNameErrorText.="El bono crédito #".($creditIndex+1)." tiene un monto positivo ".$creditPaymentReceipt['payment_amount']." pero no tiene cliente indicado.  ";
            }
          }
        }
      }  
      
      // check if previous data are present
      $previousDataPresent=true;
      $previousDataWarning="";
      // for informe I: no problemo, nothing to save if there is no informe I
      // for informe II: check if tankmeasurements are present
      $tankMeasurements=$this->TankMeasurement->find('list',[
        'conditions'=>[
          'TankMeasurement.measurement_date >='=>$paymentDate,
          'TankMeasurement.measurement_date <'=>$paymentDatePlusOne,
          'TankMeasurement.enterprise_id'=>$enterpriseId,
        ],
      ]);
      if (empty($tankMeasurements)){
        $previousDataPresent=false;
        $previousDataWarning.="Se debe registrar informe II Medidas de Tanque antes de registrar informe IV.  ";
      }
      // for informe III: check if hosemeasurements are present
      $hoseMeasurements=$this->HoseMeasurement->find('list',[
        'conditions'=>[
          'HoseMeasurement.measurement_date >='=>$paymentDate,
          'HoseMeasurement.measurement_date <'=>$paymentDatePlusOne,
          'HoseMeasurement.enterprise_id'=>$enterpriseId,
        ],
      ]);
      if (empty($hoseMeasurements)){
        $previousDataPresent=false;
        $previousDataWarning.="Se debe registrar informe III Medidas de Manguera antes de registrar informe IV.  ";
      }
      
      if ($paymentDateAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de los recibos no puede estar en el futuro!  No se guardaron los recibos.'), 'default',['class' => 'error-message']);
      }
      elseif ($paymentDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los recibos.'), 'default',['class' => 'error-message']);
      }
      elseif ($clientNameProblem){
        $this->Session->setFlash($clientNameErrorText."Indique el cliente o ponga el monto en cero, después vuelva a guardar.", 'default',['class' => 'error-message']);
      }
      elseif (!$previousDataPresent){
        $this->Session->setFlash($previousDataWarning."No se guardaron los recibos.", 'default',['class' => 'error-message']);
      }
      else {
        //pr($this->request->data);
        $datasource=$this->PaymentReceipt->getDataSource();
        $datasource->begin();
        
        try {
          $shifts=$this->Shift->find('all',[
            'conditions'=>['Shift.id'=>$enterpriseShiftIds],
            'contain'=>[
              'PaymentReceipt'=>[
                'conditions'=>[
                  'PaymentReceipt.payment_date'=>$paymentDate
                ],
              ],
            ],
            'order'=>'Shift.display_order',
          ]);
          //pr($shifts);
          foreach ($shifts as $shift){
            if (!empty($shift['PaymentReceipt'])){
              foreach ($shift['PaymentReceipt'] as $paymentReceipt){
                if (!$this->PaymentReceipt->delete($paymentReceipt['id'])) {
                  echo "Problema eliminando el recibo obsoleto";
                  pr($this->validateErrors($this->PaymentReceipt));
                  throw new Exception();
                }
              }
            }
          }
          foreach ($this->request->data['Shift'] as $shiftId=>$shiftData){
            foreach ($shiftData['Operator'] as $operatorId=>$operatorData){
              foreach ($operatorData['PaymentReceipt'] as $paymentReceipt){
                $paymentReceiptData=$paymentReceipt;
                $paymentReceiptData['enterprise_id']=$enterpriseId;
                $paymentReceiptData['shift_id']=$shiftId;
                $paymentReceiptData['payment_date']=$paymentDateAsString;
                //pr($paymentReceiptData);
                
                $this->PaymentReceipt->create();
                if (!$this->PaymentReceipt->save($paymentReceiptData)) {
                  echo "Problema guardando el recibo";
                  pr($this->validateErrors($this->PaymentReceipt));
                  throw new Exception();
                }
                
                $paymentReceiptId=$this->PaymentReceipt->id;
                if (!empty($paymentReceipt['InvoiceData']) &&!empty($paymentReceipt['InvoiceData']['Invoice'])){
                  //pr($paymentReceipt['InvoiceData']);
                  foreach ($paymentReceipt['InvoiceData']['Invoice'] as $invoice){
                    $invoiceArray=[];
                    $invoiceArray['Invoice']['id']=$invoice['id'];
                    $invoiceArray['Invoice']['payment_receipt_id']=$paymentReceiptId;
                    $this->Invoice->id=$invoice['id'];
                    //pr($invoiceArray);
                    if (!$this->Invoice->save($invoiceArray)) {
                      echo "Problema guardando el recibo de la factura";
                      pr($this->validateErrors($this->Invoice));
                      throw new Exception();
                    }
                  }
                }
              }
              foreach ($operatorData['Credit'] as $creditPaymentReceipt){
                if ($creditPaymentReceipt['payment_amount']>0){
                  $paymentReceiptData=$creditPaymentReceipt;
                  $paymentReceiptData['enterprise_id']=$enterpriseId;
                  $paymentReceiptData['shift_id']=$shiftId;
                  $paymentReceiptData['payment_date']=$paymentDateAsString;
                  //pr($paymentReceiptData);
                  
                  $this->PaymentReceipt->create();
                  if (!$this->PaymentReceipt->save($paymentReceiptData)) {
                    echo "Problema guardando el recibo";
                    pr($this->validateErrors($this->PaymentReceipt));
                    throw new Exception();
                  }
                  
                  
                $paymentReceiptId=$this->PaymentReceipt->id;
                
                if (!empty($creditPaymentReceipt['InvoiceData']['Invoice'])){
                  //pr($creditPaymentReceipt['Invoice']);
                  foreach ($creditPaymentReceipt['InvoiceData']['Invoice'] as $invoice){
                    $invoiceArray=[];
                    $invoiceArray['Invoice']['id']=$invoice['id'];
                    $invoiceArray['Invoice']['payment_receipt_id']=$paymentReceiptId;
                    $this->Invoice->id=$invoice['id'];
                    if (!$this->Invoice->save($invoiceArray)) {
                      echo "Problema guardando el recibo de la factura";
                      pr($this->validateErrors($this->Invoice));
                      throw new Exception();
                    }
                  }
                }
                }
              }
            }              
          }
         
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los recibos de fecha ".$paymentDateAsString);
          $this->Session->setFlash("Se registraron los recibos de fecha ".$paymentDateAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolEditingMode=false;
          $boolEditingToggleVisible=true;
          
          return $this->redirect(['controller'=>'stockMovements','action' => 'informeDiario',$paymentDateAsString]);

        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar los recibos de fecha ".$paymentDateAsString, 'default',['class' => 'error-message']);
        }
      }	
    }
    else {
      //echo "initial post<br/>";
      $shifts=$this->Shift->find('all',[
        'conditions'=>['Shift.id'=>$enterpriseShiftIds],
        'contain'=>[
          'PaymentReceipt'=>[
            'conditions'=>[
              'PaymentReceipt.payment_date'=>$paymentDate,
              'PaymentReceipt.enterprise_id'=>$enterpriseId,
            ],
            'Invoice',
            'order'=>'operator_id ASC, payment_mode_id ASC',
          ],
          'StockMovement'=>[
            'fields'=>['operator_id'],
            'conditions'=>[
              'StockMovement.movement_date >='=>$paymentDate,
              'StockMovement.movement_date <'=>$paymentDatePlusOne,
              'StockMovement.operator_id !='=>0,
              'StockMovement.enterprise_id'=>$enterpriseId,
            ],
          ]          
        ],
        'order'=>'Shift.display_order',
      ]);
      //pr($shifts);
      $dataPresent=false;
      foreach ($shifts as $shift){
        $shiftId=$shift['Shift']['id'];
        $requestShifts[$shiftId]=[];
        $requestShifts[$shiftId]['Operator']=[];
        if (!empty($shift['PaymentReceipt'])){ 
          $dataPresent=true;
          foreach ($shift['PaymentReceipt'] as $paymentReceipt){
            $operatorId=$paymentReceipt['operator_id'];
            $paymentModeId=$paymentReceipt['payment_mode_id'];
            $currencyId=$paymentReceipt['currency_id'];
            $paymentAmount=$paymentReceipt['payment_amount'];
            $clientId=$paymentReceipt['client_id'];
            if (!in_array($operatorId,array_keys($requestShifts[$shiftId]['Operator']))){
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']=[];
            }
            if (!in_array($paymentModeId,array_keys($requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']))){
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]=[];
            }
            if ($paymentModeId !== PAYMENT_MODE_CREDIT){
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=$paymentAmount;
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_list']='';
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_total_amount']=0;
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['Invoice']=[];
              $iCounter=0;
              if (!empty($paymentReceipt['Invoice'])){
                $invoiceList='';
                $invoiceTotalAmount=0;
                foreach ($paymentReceipt['Invoice'] as $invoice){
                  //pr($invoice);
                  $invoiceList.=$invoice['invoice_code'];
                  $invoiceTotalAmount=$invoice['sub_total_price'];
                  $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['Invoice'][$iCounter]['id']=$invoice['id'];
                  $iCounter++;
                  if ($iCounter<count($paymentReceipt['Invoice'])){
                    $invoiceList.=', ';
                  }
                }
                $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_list']=$invoiceList;
                $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_total_amount']=$invoiceTotalAmount;
              }
            }
            else {
              $creditCurrencyArray=[
                'payment_amount'=>$paymentAmount,
                'client_id'=>$clientId,
                'InvoiceData'=>[
                  'invoice_list'=>'',
                  'invoice_total_amount'=>0,
                  'Invoice'=>[],
                ],
              ];
              $iCounter=0;
              if (!empty($paymentReceipt['Invoice'])){
                //pr($paymentReceipt['Invoice']);
                $invoiceList='';
                $invoiceTotalAmount=0;
                foreach ($paymentReceipt['Invoice'] as $invoice){
                  //pr($invoice);
                  $invoiceList.=$invoice['invoice_code'];
                  $invoiceTotalAmount=$invoice['sub_total_price'];
                  $creditCurrencyArray['InvoiceData']['Invoice'][$iCounter]['id']=$invoice['id'];
                  $iCounter++;
                  if ($iCounter<count($paymentReceipt['Invoice'])){
                    $invoiceList.=', ';
                  }
                }
                $creditCurrencyArray['InvoiceData']['invoice_list']=$invoiceList;
                $creditCurrencyArray['InvoiceData']['invoice_total_amount']=$invoiceTotalAmount;
              }
              //pr($creditCurrencyArray);
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId][]=$creditCurrencyArray;  
            }
          }  
        }
        else{
          if (!empty($shift['StockMovement'])){
            foreach ($shift['StockMovement'] as $stockMovement){
              $operatorId=$stockMovement['operator_id'];
              if (!in_array($operatorId,array_keys($requestShifts[$shiftId]['Operator']))){
                $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']=[];
              }
            }    
          }
        }
      }
      //pr($requestShifts);
      if (!$dataPresent){
        $boolEditingMode=true;
      }
      else {
        $boolEditingToggleVisible=true;
      }
    }
    $requestShiftsEmpty=true;
    foreach ($requestShifts as $shiftId=>$shiftData){
      if (!empty($shiftData['Operator'])){
        $requestShiftsEmpty=false;  
      }
    }
    if (!$requestShiftsEmpty){
      foreach($requestShifts as $shiftId=>$shiftData){
        $shiftTotal=0;
        foreach ($shiftData['Operator'] as $operatorId=>$operatorData){
          $requestShifts[$shiftId]['Operator'][$operatorId]['total_income']=0;
          if (!empty($stockMovements)){
            foreach ($stockMovements as $stockMovement){
              if ($stockMovement['StockMovement']['shift_id'] == $shiftId && $stockMovement['StockMovement']['operator_id'] == $operatorId){
                $requestShifts[$shiftId]['Operator'][$operatorId]['total_income']=round($stockMovement['StockMovement']['total_product_price'],2);
                $shiftTotal+=$stockMovement['StockMovement']['total_product_price'];
              }
            }
          }
        }
        $requestShifts[$shiftId]['total_income']=round($shiftTotal,2);
        $requestShifts[$shiftId]['total_calibration']=0;
        if (!empty($calibrationMovements)){
          foreach ($calibrationMovements as $calibrationMovement){
            if ($calibrationMovement['StockMovement']['shift_id'] == $shiftId){
              $requestShifts[$shiftId]['total_calibration']=round($calibrationMovement['StockMovement']['total_product_price'],2);
            }
          }
        }
      }
    }
    //pr($requestShifts);
    //echo 'payment date is '.$paymentDate.'<br/>';
		$exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($paymentDate);
    //pr($exchangeRate);
    $this->set(compact('exchangeRate'));
    
    $clientPaymentTotals=[
      'Client'=>[],
      'Shift'=>[],
    ];
    foreach ($requestShifts as $receiptShiftId =>$receiptShiftData){
      $clientPaymentTotals['Shift'][$receiptShiftId]=[
        'Client'=>[],
      ];
      foreach ($receiptShiftData['Operator'] as $receiptOperator){
        if (array_key_exists(PAYMENT_MODE_CREDIT,$receiptOperator['PaymentMode']) && !empty($receiptOperator['PaymentMode'][PAYMENT_MODE_CREDIT])){
          foreach ($receiptOperator['PaymentMode'][PAYMENT_MODE_CREDIT] as $receiptData){            
            $clientId=$receiptData['client_id'];
            if (!array_key_exists($clientId,$clientPaymentTotals['Client'])){
              $clientPaymentTotals['Client'][$clientId]['total']=0;
              $clientPaymentTotals['Client'][$clientId]['name']=$clients[$clientId];
            }
            if (!array_key_exists($clientId,$clientPaymentTotals['Shift'][$receiptShiftId]['Client'])){
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']=0;
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['name']=$clients[$clientId];
            }
            //if ($currencyId == CURRENCY_CS){
              $clientPaymentTotals['Client'][$clientId]['total']+=$receiptData['payment_amount'];  
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']+=$receiptData['payment_amount'];  
            //}
            /*
            elseif ($currencyId == CURRENCY_USD){
              $clientPaymentTotals['Client'][$clientId]['total']+=$receiptData['payment_amount']*$exchangeRate;  
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']+=$receiptData['payment_amount']*$exchangeRate;    
            }
            */
          }  
        }
      }
    }
    //pr($clientPaymentTotals);
    uasort($clientPaymentTotals['Client'],[$this->ThirdParty,'sortByCompanyName']);
    //pr($clientPaymentTotals);
    $this->set(compact('clientPaymentTotals'));
    
    $paymentModeTotals=[
      'PaymentMode'=>[],
      'Shift'=>[],
    ];
    foreach (array_keys($paymentModes) as $paymentModeId){
      if ($paymentModeId != PAYMENT_MODE_CREDIT){
        $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency']=[];
        foreach (array_keys($currencies) as $currencyId){
          $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=['total'=>0];
        }  
      }      
    };
    foreach ($requestShifts as $receiptShiftId =>$receiptShiftData){
      $paymentModeTotals['Shift'][$receiptShiftId]=[
        'PaymentMode'=>[],
      ];
      foreach (array_keys($paymentModes) as $paymentModeId){
        if ($paymentModeId != PAYMENT_MODE_CREDIT){
          $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency']=[];
          foreach (array_keys($currencies) as $currencyId){
              $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=['total'=>0];
          }
        }
      };
      
      foreach ($receiptShiftData['Operator'] as $receiptOperator){
        foreach (array_keys($paymentModes) as $paymentModeId){  
          if ($paymentModeId != PAYMENT_MODE_CREDIT && 
            array_key_exists($paymentModeId,$receiptOperator['PaymentMode']) && 
            !empty($receiptOperator['PaymentMode'][$paymentModeId])){
            foreach ($receiptOperator['PaymentMode'][$paymentModeId]['Currency'] as $currencyId=>$currencyTotal){
              //if ($currencyId == CURRENCY_CS){
                $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal;  
                $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal;  
              //}
              /*
              elseif ($currencyId == CURRENCY_USD){
                $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal*$exchangeRate;  
                $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal*$exchangeRate;      
              }
              */
            }
          }
        }
      }
    }
    //pr($paymentModeTotals);
    $this->set(compact('paymentModeTotals'));

		$this->set(compact('boolEditingMode'));
    $this->set(compact('boolEditingToggleVisible'));
    $this->set(compact('paymentDate'));
    $this->set(compact('filterPaymentModeId'));
    $this->set(compact('filterCurrencyId'));
    $_SESSION['enterpriseId']=$enterpriseId;
		$this->set(compact('enterpriseId'));
		$this->set(compact('requestShifts'));
    
    $this->Shift->recursive=-1;
    $shifts=$this->Shift->find('all',[
      'conditions'=>['Shift.id'=>$enterpriseShiftIds],
      'order'=>'Shift.display_order',
    ]);
    //pr($shifts);
    $this->loadModel('StockMovement');
    for ($s=0;$s<count($shifts);$s++){
      $operatorIds=$this->StockMovement->find('list',[
        'fields'=>'StockMovement.operator_id',
        'conditions'=>[
          'date(StockMovement.movement_date)'=>$paymentDate,
          'StockMovement.product_quantity >'=>0,
          'StockMovement.shift_id'=>$shifts[$s]['Shift']['id'],
          'StockMovement.enterprise_id'=>$enterpriseId,
        ],
      ]);
      $operatorIds=array_unique($operatorIds);
      //temporal data
      /*
      switch ($shifts[$s]['Shift']['id']){
        case '1':
          $operatorIds=['3','4'];
          break;
        case '2':
          $operatorIds=['5'];
          break;
        case '3':
          $operatorIds=['6'];
          break;
      }
      */
      if(!empty( $operatorIds)){
        foreach ($operatorIds as $operatorId){
          $shifts[$s]['Operator'][]=$operatorId;  
        }
      }
    }
    //pr($shifts);
    $this->set(compact('shifts'));

    $shiftList=$this->Shift->find('list',[
      'conditions'=>[
        'Shift.id'=>$enterpriseShiftIds,
        'Shift.bool_active'=>true
      ],
    ]);
    $this->set(compact('shiftList'));
    
    $operators=$this->Operator->find('list',[
      'conditions'=>[
        'Operator.enterprise_id'=>$enterpriseId,
        'Operator.bool_active'=>true
      ],
    ]);
    $this->set(compact('operators'));
    //pr($operators);
  }

  public function pdfVentasCombustibleRecibosTurno($paymentDate = '',$enterpriseId=0) {
    $this->loadModel('Shift');
    $this->loadModel('ThirdParty');
    $this->loadModel('Currency');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Product');
    $this->loadModel('StockMovement');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('Order');
    $this->loadModel('Invoice');
    
    $this->Shift->recursive=-1;
    
    //echo "enterpriseId is ".$enterpriseId."<br/>";
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $this->set(compact('paymentDate','enterpriseId'));
    
    $paymentDatePlusOne= date( "Y-m-d", strtotime( $paymentDate."+1 days" ) );
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterprises'));
    // LOOK UP THE ORDER ID TO LINK DIRECTLY INT HE INVOICES
    $orderId=0;
    if($enterpriseId > 0){
      $orderId=$this->Order->getOrderIdForEnterpriseAndDate($enterpriseId,$paymentDate);
    }
    //echo "orderId is ".$orderId."<br/>";
    $this->set(compact('orderId','orders'));
    $enterpriseShiftIds=$this->Shift->find('list',[
      'fields'=>['Shift.id'],
      'conditions'=>[
        'Shift.enterprise_id'=>$enterpriseId,
        'Shift.bool_active'=>true,
      ],
    ]);
    
    $paymentModes=$this->PaymentMode->find('list');
    $this->set(compact('paymentModes'));
    
    $clients=$this->ThirdParty->getActiveClientListForEnterprise($enterpriseId,15);
    $this->set(compact('clients'));
    $fullClients=$this->ThirdParty->getActiveClientListForEnterprise($enterpriseId);
    $this->set(compact('fullClients'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    $fuels=$this->Product->find('list',[
      'fields'=>['Product.id','Product.name'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    $fuelIds=array_keys($fuels);
    //pr($fuels);
    //pr($fuelIds);
    $stockMovementConditions=[
      'StockMovement.movement_date >='=>$paymentDate,
      'StockMovement.movement_date <'=>$paymentDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE],
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.enterprise_id'=>$enterpriseId,
    ];
    $calibrationMovementConditions=[
      'StockMovement.movement_date >='=>$paymentDate,
      'StockMovement.movement_date <'=>$paymentDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_CALIBRATION],
      'StockMovement.product_id'=>$fuelIds,
      'StockMovement.enterprise_id'=>$enterpriseId,
    ];
    
    $this->StockMovement->virtualFields['total_product_price']=0;
    $stockMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'shift_id',
        'operator_id',
        'SUM(product_total_price) AS StockMovement__total_product_price',
      ],
      'conditions'=>$stockMovementConditions,
      'order'=>'shift_id, operator_id',
      'group'=>'StockMovement.shift_id, StockMovement.operator_id'
    ]);
    $this->StockMovement->virtualFields['total_product_price']=0;
    $calibrationMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'shift_id',
        'SUM(product_total_price) AS StockMovement__total_product_price'
      ],
      'conditions'=>$calibrationMovementConditions,
      'order'=>'shift_id',
      'group'=>'StockMovement.shift_id'
    ]);
    //pr($calibrationMovements);
    
    $this->StockMovement->virtualFields['total_product_price']=0;
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $fuelMovementTotals=$this->StockMovement->find('all',[
      'fields'=>[
        'StockMovement.product_id',
        'Product.name',
        'Product.product_order',
        'StockMovement.product_unit_price',
        'SUM(StockMovement.product_total_price) AS StockMovement__total_product_price',
        'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$stockMovementConditions,
      'order'=>'Product.product_order',
      'group'=>'StockMovement.product_id'
    ]);
    $this->StockMovement->virtualFields['total_product_price']=0;
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $calibrationMovementTotals=$this->StockMovement->find('all',[
      'fields'=>[
        'StockMovement.product_id',
        'StockMovement.product_unit_price',
        'SUM(StockMovement.product_total_price) AS StockMovement__total_product_price',
        'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity',
      ],
      'conditions'=>$calibrationMovementConditions,
      'order'=>'Product.product_order',
      'group'=>'StockMovement.product_id'
    ]);
    
    $fuelTotals=[];
    foreach ($fuels as $fuelId=>$fuelName){
      $fuelTotals[$fuelId]['name']=$fuelName;
      $boolFuelUsed=false;
      foreach ($fuelMovementTotals as $fuelMovementTotal){
        if ($fuelMovementTotal['StockMovement']['product_id'] == $fuelId){
          $fuelTotals[$fuelId]['total_gallons']=$fuelMovementTotal['StockMovement']['total_product_quantity'];
          $fuelTotals[$fuelId]['unit_price']=$fuelMovementTotal['StockMovement']['product_unit_price'];
          $fuelTotals[$fuelId]['total_price']=$fuelMovementTotal['StockMovement']['total_product_price'];
          $boolFuelUsed=true;
        }
      }
      if (!$boolFuelUsed){
        $fuelTotals[$fuelId]['total_gallons']=0;
        $fuelTotals[$fuelId]['unit_price']=$this->Product->ProductPriceLog->getLatestPrice($fuelId,$paymentDate,$enterpriseId);
        $fuelTotals[$fuelId]['total_price']=0;
      }
      foreach ($calibrationMovementTotals as $calibrationMovementTotal){
        if ($calibrationMovementTotal['StockMovement']['product_id'] == $fuelId){
          $fuelTotals[$fuelId]['total_gallons']-=$calibrationMovementTotal['StockMovement']['total_product_quantity'];
          $fuelTotals[$fuelId]['total_price']-=$calibrationMovementTotal['StockMovement']['total_product_price'];
        }
      }
    }
    //pr($fuelTotals);
    $this->set(compact('fuelTotals'));
    $requestShifts=[];
    
    $shiftList=$this->Shift->find('list',[
      'conditions'=>[
        'Shift.id'=>$enterpriseShiftIds,
        'Shift.bool_active'=>true
      ],
    ]);
    $this->set(compact('shiftList'));
    
    //echo "initial post<br/>";
    $allShifts=$this->Shift->find('all',[
      'conditions'=>['Shift.id'=>$enterpriseShiftIds],
      'contain'=>[
        'PaymentReceipt'=>[
          'conditions'=>[
            'PaymentReceipt.payment_date'=>$paymentDate,
            'PaymentReceipt.enterprise_id'=>$enterpriseId,
          ],
          'Invoice',
          'order'=>'operator_id ASC, payment_mode_id ASC',
        ],
        'StockMovement'=>[
          'fields'=>['operator_id'],
          'conditions'=>[
            'StockMovement.movement_date >='=>$paymentDate,
            'StockMovement.movement_date <'=>$paymentDatePlusOne,
            'StockMovement.operator_id !='=>0,
            'StockMovement.enterprise_id'=>$enterpriseId,
          ],
        ]          
      ],
      'order'=>'Shift.display_order',
    ]);
    //pr($shifts);
    $dataPresent=false;
    foreach ($allShifts as $shift){
      $shiftId=$shift['Shift']['id'];
      $requestShifts[$shiftId]=[];
      $requestShifts[$shiftId]['Operator']=[];
      if (!empty($shift['PaymentReceipt'])){ 
        $dataPresent=true;
        foreach ($shift['PaymentReceipt'] as $paymentReceipt){
          $operatorId=$paymentReceipt['operator_id'];
          $paymentModeId=$paymentReceipt['payment_mode_id'];
          $currencyId=$paymentReceipt['currency_id'];
          $paymentAmount=$paymentReceipt['payment_amount'];
          $clientId=$paymentReceipt['client_id'];
          if (!in_array($operatorId,array_keys($requestShifts[$shiftId]['Operator']))){
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']=[];
          }
          if (!in_array($paymentModeId,array_keys($requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']))){
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]=[];
          }
          if ($paymentModeId !== PAYMENT_MODE_CREDIT){
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=$paymentAmount;
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_list']='';
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_total_amount']=0;
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['Invoice']=[];
            $iCounter=0;
            if (!empty($paymentReceipt['Invoice'])){
              $invoiceList='';
              $invoiceTotalAmount=0;
              foreach ($paymentReceipt['Invoice'] as $invoice){
                //pr($invoice);
                $invoiceList.=$invoice['invoice_code'];
                $invoiceTotalAmount=$invoice['sub_total_price'];
                $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['Invoice'][$iCounter]['id']=$invoice['id'];
                $iCounter++;
                if ($iCounter<count($paymentReceipt['Invoice'])){
                  $invoiceList.=', ';
                }
              }
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_list']=$invoiceList;
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId]['InvoiceData']['invoice_total_amount']=$invoiceTotalAmount;
            }
          }
          else {
            $creditCurrencyArray=[
              'payment_amount'=>$paymentAmount,
              'client_id'=>$clientId,
              'InvoiceData'=>[
                'invoice_list'=>'',
                'invoice_total_amount'=>0,
                'Invoice'=>[],
              ],
            ];
            $iCounter=0;
            if (!empty($paymentReceipt['Invoice'])){
              //pr($paymentReceipt['Invoice']);
              $invoiceList='';
              $invoiceTotalAmount=0;
              foreach ($paymentReceipt['Invoice'] as $invoice){
                //pr($invoice);
                $invoiceList.=$invoice['invoice_code'];
                $invoiceTotalAmount=$invoice['sub_total_price'];
                $creditCurrencyArray['InvoiceData']['Invoice'][$iCounter]['id']=$invoice['id'];
                $iCounter++;
                if ($iCounter<count($paymentReceipt['Invoice'])){
                  $invoiceList.=', ';
                }
              }
              $creditCurrencyArray['InvoiceData']['invoice_list']=$invoiceList;
              $creditCurrencyArray['InvoiceData']['invoice_total_amount']=$invoiceTotalAmount;
            }
            //pr($creditCurrencyArray);
            $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode'][$paymentModeId][]=$creditCurrencyArray;  
          }
        }  
      }
      else{
        if (!empty($shift['StockMovement'])){
          foreach ($shift['StockMovement'] as $stockMovement){
            $operatorId=$stockMovement['operator_id'];
            if (!in_array($operatorId,array_keys($requestShifts[$shiftId]['Operator']))){
              $requestShifts[$shiftId]['Operator'][$operatorId]['PaymentMode']=[];
            }
          }    
        }
      }
    }
    foreach ($requestShifts as $shiftId=>$shiftData){
      if (!empty($shiftData['Operator'])){
        $requestShiftsEmpty=false;  
      }
    }
    if (!$requestShiftsEmpty){
      foreach($requestShifts as $shiftId=>$shiftData){
        $shiftTotal=0;
        foreach ($shiftData['Operator'] as $operatorId=>$operatorData){
          $requestShifts[$shiftId]['Operator'][$operatorId]['total_income']=0;
          if (!empty($stockMovements)){
            foreach ($stockMovements as $stockMovement){
              if ($stockMovement['StockMovement']['shift_id'] == $shiftId && $stockMovement['StockMovement']['operator_id'] == $operatorId){
                $requestShifts[$shiftId]['Operator'][$operatorId]['total_income']=round($stockMovement['StockMovement']['total_product_price'],2);
                $shiftTotal+=$stockMovement['StockMovement']['total_product_price'];
              }
            }
          }
        }
        $requestShifts[$shiftId]['total_income']=round($shiftTotal,2);
        $requestShifts[$shiftId]['total_calibration']=0;
        if (!empty($calibrationMovements)){
          foreach ($calibrationMovements as $calibrationMovement){
            if ($calibrationMovement['StockMovement']['shift_id'] == $shiftId){
              $requestShifts[$shiftId]['total_calibration']=round($calibrationMovement['StockMovement']['total_product_price'],2);
            }
          }
        }
      }
    }
    //pr($requestShifts);
    
		$exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($paymentDate);
    $this->set(compact('exchangeRate'));
    
    $clientPaymentTotals=[
      'Client'=>[],
      'Shift'=>[],
    ];
    foreach ($requestShifts as $receiptShiftId =>$receiptShiftData){
      $clientPaymentTotals['Shift'][$receiptShiftId]=[
        'Client'=>[],
      ];
      foreach ($receiptShiftData['Operator'] as $receiptOperator){
        if (array_key_exists(PAYMENT_MODE_CREDIT,$receiptOperator['PaymentMode']) && !empty($receiptOperator['PaymentMode'][PAYMENT_MODE_CREDIT])){
          foreach ($receiptOperator['PaymentMode'][PAYMENT_MODE_CREDIT] as $receiptData){            
            $clientId=$receiptData['client_id'];
            if (!array_key_exists($clientId,$clientPaymentTotals['Client'])){
              $clientPaymentTotals['Client'][$clientId]['total']=0;
              $clientPaymentTotals['Client'][$clientId]['name']=$clients[$clientId];
            }
            if (!array_key_exists($clientId,$clientPaymentTotals['Shift'][$receiptShiftId]['Client'])){
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']=0;
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['name']=$clients[$clientId];
            }
            //if ($currencyId == CURRENCY_CS){
              $clientPaymentTotals['Client'][$clientId]['total']+=$receiptData['payment_amount'];  
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']+=$receiptData['payment_amount'];  
            //}
            /*
            elseif ($currencyId == CURRENCY_USD){
              $clientPaymentTotals['Client'][$clientId]['total']+=$receiptData['payment_amount']*$exchangeRate;  
              $clientPaymentTotals['Shift'][$receiptShiftId]['Client'][$clientId]['total']+=$receiptData['payment_amount']*$exchangeRate;    
            }
            */
          }  
        }
      }
    }
    //pr($clientPaymentTotals);
    uasort($clientPaymentTotals['Client'],[$this->ThirdParty,'sortByCompanyName']);
    //pr($clientPaymentTotals);
    $this->set(compact('clientPaymentTotals'));
    
    $paymentModeTotals=[
      'PaymentMode'=>[],
      'Shift'=>[],
    ];
    foreach (array_keys($paymentModes) as $paymentModeId){
      if ($paymentModeId != PAYMENT_MODE_CREDIT){
        $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency']=[];
        foreach (array_keys($currencies) as $currencyId){
          $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=['total'=>0];
        }  
      }      
    };
    foreach ($requestShifts as $receiptShiftId =>$receiptShiftData){
      $paymentModeTotals['Shift'][$receiptShiftId]=[
        'PaymentMode'=>[],
      ];
      foreach (array_keys($paymentModes) as $paymentModeId){
        if ($paymentModeId != PAYMENT_MODE_CREDIT){
          $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency']=[];
          foreach (array_keys($currencies) as $currencyId){
              $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]=['total'=>0];
          }
        }
      };
      
      foreach ($receiptShiftData['Operator'] as $receiptOperator){
        foreach (array_keys($paymentModes) as $paymentModeId){  
          if ($paymentModeId != PAYMENT_MODE_CREDIT && 
            array_key_exists($paymentModeId,$receiptOperator['PaymentMode']) && 
            !empty($receiptOperator['PaymentMode'][$paymentModeId])){
            foreach ($receiptOperator['PaymentMode'][$paymentModeId]['Currency'] as $currencyId=>$currencyTotal){
              //if ($currencyId == CURRENCY_CS){
                $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal;  
                $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal;  
              //}
              /*
              elseif ($currencyId == CURRENCY_USD){
                $paymentModeTotals['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal*$exchangeRate;  
                $paymentModeTotals['Shift'][$receiptShiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']+=$currencyTotal*$exchangeRate;      
              }
              */
            }
          }
        }
      }
    }
    //pr($paymentModeTotals);
    $this->set(compact('paymentModeTotals'));
  }  
  
  public function reporteRecibos($saleDateAsString = '') {
    $this->loadModel('Shift');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Product');
    $this->loadModel('StockMovement');
    
    $this->loadModel('Operator');
    $this->loadModel('ThirdParty');
    $this->loadModel('Enterprise');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $startDate = null;
		$endDate = null;

    define('PAYMENT_TOTALS','0');
    define('PAYMENT_SHIFT_TOTALS','1');
    define('PAYMENT_OPERATOR_TOTALS','2');
    
    $displayOptions=[
			PAYMENT_TOTALS=>'Mostrar ingresos totales',
			PAYMENT_SHIFT_TOTALS=>'Mostrar ingresos por turno',
      PAYMENT_OPERATOR_TOTALS=>'Mostrar ingresos por operador',
		];
		$this->set(compact('displayOptions'));
		$displayOptionId=0;
    
    //$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $shiftId=0;
    $operatorId=0;
    
    $enterpriseId=0;
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
      
      //$displayOptionId=$this->request->data['Report']['display_option_id'];
      $enterpriseId=$this->request->data['Report']['enterprise_id'];
      //$shiftId=$this->request->data['Report']['shift_id'];
      //$operatorId=$this->request->data['Report']['operator_id'];
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
    $this->set(compact('shiftId'));
    $this->set(compact('operatorId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $enterpriseShiftIds=$this->Shift->find('list',[
      'fields'=>['Shift.id'],
      'conditions'=>[
        'Shift.enterprise_id'=>$enterpriseId,
        'Shift.bool_active'=>true,
      ],
    ]);
    $paymentModes=$this->PaymentMode->find('list');
    $this->set(compact('paymentModes'));
    $fuelIds=$this->Product->find('list',[
      'fields'=>['Product.id'],
      'conditions'=>[  
        ['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
        ['Product.bool_active'=>true],
      ],
      'order'=>'Product.product_order ASC',
    ]);
    
    $paymentsArray=[];
    $currentDate=$endDate;
    $startDateMinusOne=date( "Y-m-d", strtotime($startDate."-1 days"));
    while ($currentDate>$startDateMinusOne){
      $paymentsArray[$currentDate]['total_price']=0;
      $paymentsArray[$currentDate]['total_calibration']=0;
      $paymentsArray[$currentDate]['net_price']=0;
      $paymentsArray[$currentDate]['total_received']=0;
      $paymentsArray[$currentDate]['total_cash']=0;
      $paymentsArray[$currentDate]['total_card_bac']=0;
      $paymentsArray[$currentDate]['total_card_banpro']=0;
      $paymentsArray[$currentDate]['total_credit']=0;
      $paymentsArray[$currentDate]['Shift']=[];
      $currentDate=date( "Y-m-d", strtotime( $currentDate."-1 days" ) );
    }
    //pr($paymentsArray);
    
    $stockMovementConditions=[
      'StockMovement.movement_date >='=>$startDate,
      'StockMovement.movement_date <'=>$endDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_SALE],
      'StockMovement.product_id'=>$fuelIds,
    ];
    //pr($stockMovementConditions);
    $this->StockMovement->virtualFields['sale_date']="";
    $this->StockMovement->virtualFields['total_product_price']=0;
    $stockMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'date(movement_date) AS StockMovement__sale_date',
        'shift_id',
        'operator_id',
        'SUM(product_total_price) AS StockMovement__total_product_price'
      ],
      'conditions'=>$stockMovementConditions,
      'order'=>'movement_date DESC,shift_id, operator_id',
      'group'=>'StockMovement.movement_date, StockMovement.shift_id, StockMovement.operator_id'
    ]);
    //pr($stockMovements);
   
   if (!empty($stockMovements)){
      foreach ($stockMovements as $stockMovement){
        $saleDate=$stockMovement['StockMovement']['sale_date'];
        $shiftId=$stockMovement['StockMovement']['shift_id'];
        $operatorId=$stockMovement['StockMovement']['operator_id'];
        //$salesArray[$saleDate]['Fuel'][$productId]['quantity_liters']=$stockMovement['StockMovement']['total_product_quantity']*GALLONS_TO_LITERS;
        $paymentsArray[$saleDate]['total_price']+=$stockMovement['StockMovement']['total_product_price'];
        if (array_key_exists($shiftId,$paymentsArray[$saleDate]['Shift'])){
          $paymentsArray[$saleDate]['Shift'][$shiftId]['total_price']+=$stockMovement['StockMovement']['total_product_price'];  
        }
        else {
          $paymentsArray[$saleDate]['Shift'][$shiftId]['total_price']=$stockMovement['StockMovement']['total_product_price'];  
        }
        
        $paymentsArray[$saleDate]['Shift'][$shiftId]['Operator'][$operatorId]=$stockMovement['StockMovement']['total_product_price'];
      }
    }
    //pr($paymentsArray);
    $calibrationMovementConditions=[
      'StockMovement.movement_date >='=>$startDate,
      'StockMovement.movement_date <'=>$endDatePlusOne,
      'StockMovement.stock_movement_type_id'=>[MOVEMENT_ADJUSTMENT_CALIBRATION],
      'StockMovement.product_id'=>$fuelIds,
    ];
    $this->StockMovement->virtualFields['sale_date']="";
    $this->StockMovement->virtualFields['total_product_price']=0;
    $calibrationMovements=$this->StockMovement->find('all',[
      'fields'=>[
        'date(movement_date) AS StockMovement__sale_date',
        'shift_id',
        'SUM(product_total_price) AS StockMovement__total_product_price'
      ],
      'conditions'=>$calibrationMovementConditions,
      'order'=>'movement_date DESC, shift_id',
      'group'=>'StockMovement.movement_date, StockMovement.shift_id'
    ]);
    //pr($calibrationMovements);
    if (!empty($calibrationMovements)){
      foreach ($calibrationMovements as $calibrationMovement){
        $saleDate=$calibrationMovement['StockMovement']['sale_date'];
        $shiftId=$calibrationMovement['StockMovement']['shift_id'];
        $paymentsArray[$saleDate]['total_calibration']+=$calibrationMovement['StockMovement']['total_product_price'];
        if (array_key_exists($shiftId,$paymentsArray[$saleDate]['Shift']) && array_key_exists('total_calibration',$paymentsArray[$saleDate]['Shift'][$shiftId])){
          $paymentsArray[$saleDate]['Shift'][$shiftId]['total_calibration']+=$calibrationMovement['StockMovement']['total_product_price'];  
        }
        else {
          $paymentsArray[$saleDate]['Shift'][$shiftId]['total_calibration']=$calibrationMovement['StockMovement']['total_product_price'];  
        }
        
      }
    }
    foreach ($paymentsArray as $paymentDate=> $paymentInfo){
      $paymentsArray[$paymentDate]['net_price']=$paymentInfo['total_price']-$paymentInfo['total_calibration'];
      foreach ($paymentInfo['Shift'] as $shiftId =>$shiftInfo){
        if (!array_key_exists('total_price',$shiftInfo)){
          //echo "payment_date is ".$paymentDate."<br/>";
          //pr($paymentsArray[$paymentDate]);
          $shiftInfo['total_price']=0;
        }
        if (array_key_exists('total_calibration',$shiftInfo))
          $paymentsArray[$paymentDate]['Shift'][$shiftId]['net_price']=$shiftInfo['total_price']-$shiftInfo['total_calibration'];
        else {
          $paymentsArray[$paymentDate]['Shift'][$shiftId]['net_price']=$shiftInfo['total_price'];  
          $paymentsArray[$paymentDate]['Shift'][$shiftId]['total_calibration']=0;  
        }
      }  
    }
    //pr($paymentsArray);
    
    $paymentReceiptConditions=[
      'PaymentReceipt.payment_date >='=>$startDate,
      'PaymentReceipt.payment_date <'=>$endDatePlusOne,
    ];
    //pr($paymentReceiptConditions);
    $this->PaymentReceipt->virtualFields['total_payment_amount']=0;
    $paymentReceipts=$this->PaymentReceipt->find('all',[
      'fields'=>[
        'payment_date',
        'payment_mode_id',
        'shift_id',
        'operator_id',
        'currency_id',
        'SUM(payment_amount) AS PaymentReceipt__total_payment_amount'
      ],
      'conditions'=>$paymentReceiptConditions,
      'order'=>'payment_date DESC, payment_mode_id, shift_id, operator_id, currency_id',
      'group'=>'PaymentReceipt.payment_date, PaymentReceipt.payment_mode_id, PaymentReceipt.shift_id, PaymentReceipt.operator_id, PaymentReceipt.currency_id'
    ]);
    //pr($paymentReceipts);
   
   if (!empty($paymentReceipts)){
      foreach ($paymentReceipts as $paymentReceipt){
        $paymentDate=$paymentReceipt['PaymentReceipt']['payment_date'];
        $shiftId=$paymentReceipt['PaymentReceipt']['shift_id'];
        $operatorId=$paymentReceipt['PaymentReceipt']['operator_id'];
        if ($paymentReceipt['PaymentReceipt']['currency_id'] == CURRENCY_CS){
          $paymentsArray[$paymentDate]['total_received']+=$paymentReceipt['PaymentReceipt']['total_payment_amount'];
        }
        else {
          $exchangeRatePaymentDate=$this->ExchangeRate->getApplicableExchangeRate($paymentDate);
          $exchangeRate=$exchangeRatePaymentDate['ExchangeRate']['rate'];
          $paymentsArray[$paymentDate]['total_received']+=($exchangeRate*$paymentReceipt['PaymentReceipt']['total_payment_amount']);
        }          
        
        switch($paymentReceipt['PaymentReceipt']['payment_mode_id']){
          case PAYMENT_MODE_CASH:
            if ($paymentReceipt['PaymentReceipt']['currency_id'] == CURRENCY_CS){
              $paymentsArray[$paymentDate]['total_cash']+=$paymentReceipt['PaymentReceipt']['total_payment_amount'];
            }
            else {
              $exchangeRatePaymentDate=$this->ExchangeRate->getApplicableExchangeRate($paymentDate);
              $exchangeRate=$exchangeRatePaymentDate['ExchangeRate']['rate'];
              $paymentsArray[$paymentDate]['total_cash']+=($exchangeRate*$paymentReceipt['PaymentReceipt']['total_payment_amount']);
            }    
            break;
          case PAYMENT_MODE_CARD_BAC:
            $paymentsArray[$paymentDate]['total_card_bac']+=$paymentReceipt['PaymentReceipt']['total_payment_amount'];
            break;
          case PAYMENT_MODE_CARD_BANPRO:
            $paymentsArray[$paymentDate]['total_card_bac']+=$paymentReceipt['PaymentReceipt']['total_payment_amount'];
            break;         
          case PAYMENT_MODE_CREDIT:
            if ($paymentReceipt['PaymentReceipt']['currency_id'] == CURRENCY_CS){
              $paymentsArray[$paymentDate]['total_credit']+=$paymentReceipt['PaymentReceipt']['total_payment_amount'];
            }
            else {
              $exchangeRatePaymentDate=$this->ExchangeRate->getApplicableExchangeRate($paymentDate);
              $exchangeRate=$exchangeRatePaymentDate['ExchangeRate']['rate'];
              $paymentsArray[$paymentDate]['total_credit']+=($exchangeRate*$paymentReceipt['PaymentReceipt']['total_payment_amount']);
            } 
            break;
        }
        /*
        if (array_key_exists($shiftId,$paymentsArray[$paymentDate]['Shift'])){
          $paymentsArray[$paymentDate]['Shift'][$shiftId]['total_price']+=$stockMovement['StockMovement']['total_product_price'];  
        }
        else {
          $paymentsArray[$paymentDate]['Shift'][$shiftId]['total_price']=$stockMovement['StockMovement']['total_product_price'];  
        }
        
        $paymentsArray[$paymentDate]['Shift'][$shiftId]['Operator'][$operatorId]=$stockMovement['StockMovement']['total_product_price'];
        */
      }
    }
    //pr($paymentsArray);

		$this->set(compact('paymentsArray'));
    
    $this->Shift->recursive=-1;
    $shifts=$this->Shift->getShiftListForEnterprise($enterpriseId);
    $this->set(compact('shifts'));

    $operators=$this->Operator->getOperatorListForEnterprise($enterpriseId);
    $this->set(compact('operators'));
    //pr($operators);
    
    $clients=$this->ThirdParty->getActiveClientListForEnterprise($enterpriseId);
    $this->set(compact('clients'));
  }
  
  public function guardarResumenRecibos() {
		$exportData=$_SESSION['resumenRecibos'];
		$this->set(compact('exportData'));
	}	
  
	public function index() {
		$this->PaymentReceipt->recursive = -1;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		
		$paymentReceiptCount=	$this->PaymentReceipt->find('count', array(
			'fields'=>array('PaymentReceipt.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($paymentReceiptCount!=0?$paymentReceiptCount:1),
		);

		$paymentReceipts = $this->Paginator->paginate('PaymentReceipt');
		$this->set(compact('paymentReceipts'));
	}

	public function view($id = null) {
		if (!$this->PaymentReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid payment receipt'));
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
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$options = array('conditions' => array('PaymentReceipt.' . $this->PaymentReceipt->primaryKey => $id));
		$this->set('paymentReceipt', $this->PaymentReceipt->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->PaymentReceipt->create();
			if ($this->PaymentReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The payment receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The payment receipt could not be saved. Please, try again.'));
			}
		}
		$currencies = $this->PaymentReceipt->Currency->find('list');
		$operators = $this->PaymentReceipt->Operator->find('list');
		$shifts = $this->PaymentReceipt->Shift->find('list');
		$paymentModes = $this->PaymentReceipt->PaymentMode->find('list');
		$this->set(compact('currencies', 'operators', 'shifts', 'paymentModes'));
	}

	public function edit($id = null) {
		if (!$this->PaymentReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid payment receipt'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PaymentReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The payment receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The payment receipt could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('PaymentReceipt.' . $this->PaymentReceipt->primaryKey => $id));
			$this->request->data = $this->PaymentReceipt->find('first', $options);
		}
		$currencies = $this->PaymentReceipt->Currency->find('list');
		$operators = $this->PaymentReceipt->Operator->find('list');
		$shifts = $this->PaymentReceipt->Shift->find('list');
		$paymentModes = $this->PaymentReceipt->PaymentMode->find('list');
		$this->set(compact('currencies', 'operators', 'shifts', 'paymentModes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PaymentReceipt->id = $id;
		if (!$this->PaymentReceipt->exists()) {
			throw new NotFoundException(__('Invalid payment receipt'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PaymentReceipt->delete()) {
			$this->Session->setFlash(__('The payment receipt has been deleted.'));
		} else {
			$this->Session->setFlash(__('The payment receipt could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
