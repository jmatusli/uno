<?php
App::uses('AppModel', 'Model');

class Invoice extends AppModel {

  function getNewInvoiceCode($paymentModeId,$invoiceDateString){
    switch ($paymentModeId){
      case PAYMENT_MODE_CARD_BAC:
        $lastInvoiceForPaymentMode = $this->find('first',[
          'fields'=>['invoice_code'],
          'conditions'=>[
            'Invoice.payment_mode_id'=>$paymentModeId,
          ],
          'order' => ['CAST(SUBSTR(Invoice.invoice_code,13,5) AS DEC)' => 'desc'],
        ]);
        //pr($lastInvoiceForPaymentMode);
        if (!empty($lastInvoiceForPaymentMode)){
          //pr(intval(substr($lastInvoiceForPaymentMode['Invoice']['invoice_code'],13)));
          $newInvoiceCode = intval(substr($lastInvoiceForPaymentMode['Invoice']['invoice_code'],13))+1;
          $newInvoiceCode="BAC_".$invoiceDateString."_".str_pad($newInvoiceCode, 5, "0", STR_PAD_LEFT); 
        }
        else {
          $newInvoiceCode="BAC_".$invoiceDateString."_00001";
        }
        break;
      case PAYMENT_MODE_CARD_BANPRO:
        $lastInvoiceForPaymentMode = $this->find('first',[
          'fields'=>['invoice_code'],
          'conditions'=>[
            'Invoice.payment_mode_id'=>$paymentModeId,
          ],
          'order' => ['CAST(SUBSTR(Invoice.invoice_code,16,5) AS DEC)' => 'desc'],
        ]);
        //pr($lastInvoiceForPaymentMode);
        if (!empty($lastInvoiceForPaymentMode)){
          $newInvoiceCode = intval(substr($lastInvoiceForPaymentMode['Invoice']['invoice_code'],16))+1;
          $newInvoiceCode="BANPRO_".$invoiceDateString."_".str_pad($newInvoiceCode, 5, "0", STR_PAD_LEFT); 
        }
        else {
          $newInvoiceCode="BANPRO_".$invoiceDateString."_00001";
        }
        break;
      case PAYMENT_MODE_CASH :
      case PAYMENT_MODE_CREDIT:
      default:
        $newInvoiceCode="";
    }
    return $newInvoiceCode;
	}
	
  function getPendingInvoices($clientId,$enterpriseId){
    $pendingInvoices=$this->find('all',[
      'fields'=>[
        'id','invoice_date','sub_total_price','currency_id','client_id','payment_receipt_id',
      ],
      'conditions'=>[
        'bool_paid'=>false,
        'client_id'=>$clientId,
        'enterprise_id'=>$enterpriseId,
        'payment_receipt_id >'=>0,
      ],
      'contain'=>[
        'Currency'=>[
          'fields'=>['id','abbreviation'],
        ],
      ],
      'order'=>'invoice_date ASC',
    ]);
    //pr($pendingInvoices);
    return $pendingInvoices;
  }

	function getCreditDays($id){
		$thisInvoice=$this->find('first',array(
			'fields'=>array('Invoice.total_price','Invoice.currency_id','Invoice.bool_credit','Invoice.bool_paid','Invoice.invoice_date','Invoice.due_date'),
			'conditions'=>array('Invoice.id'=>$id),
		));
		$creditDays=0;
		if ($thisInvoice['Invoice']['bool_credit']){
			if (!$thisInvoice['Invoice']['bool_paid']){
				$invoiceDate=new DateTime($thisInvoice['Invoice']['invoice_date']);
        //$dueDate= new DateTime($thisInvoice['Invoice']['due_date']);
				$currentDate= new DateTime(date('Y-m-d'));
				$daysLate=$currentDate->diff($invoiceDate);
        //$daysLate=$dueDate->diff($invoiceDate);
				$creditDays=$daysLate->days;
			}
			else {
				$lastCashReceiptForInvoice=$this->CashReceiptInvoice->find('first',array(
					'fields'=>array('CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id','CashReceipt.receipt_date'),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$id,
					),
					'order'=>'CashReceipt.receipt_date DESC',
				));
				if (!empty($lastCashReceiptForInvoice)){
					$receiptDate=new DateTime($lastCashReceiptForInvoice['CashReceipt']['receipt_date']);
          $invoiceDate=new DateTime($thisInvoice['Invoice']['invoice_date']);
          //$dueDate= new DateTime($thisInvoice['Invoice']['due_date']);
					$currentDate= new DateTime(date('Y-m-d'));
					$daysLate=$invoiceDate->diff($receiptDate);
          //$daysLate=$dueDate->diff($invoiceDate);
					$creditDays=$daysLate->days;
				}
			}
		}
		return $creditDays;
	}
	
	function getHistoricalCreditForClient($client_id){
		$invoices=$this->find('all',array(
			'fields'=>array('Invoice.id'),
			'conditions'=>array('Invoice.client_id'=>$client_id),
		));
		$historicalCredit=0;
		if (count($invoices)>0){
			foreach ($invoices as $invoice){
				$historicalCredit+=$this->getCreditDays($invoice['Invoice']['id']);
			}
			$historicalCredit=$historicalCredit/count($invoices);
		}
		return $historicalCredit;
	}

	function getAmountPaidAlreadyCS($id){
		$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
		
		$cashReceiptsForInvoice=$cashReceiptInvoiceModel->find('all',[
			'fields'=>[
				'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.currency_id',
				'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention',
				'CashReceiptInvoice.payment_erdiff_CS','CashReceiptInvoice.payment_increment_CS','CashReceiptInvoice.payment_discount_CS',
			],
			'conditions'=>[
				'CashReceiptInvoice.invoice_id'=>$id,
			],
		]);
		$paidAlreadyCS=0;
		//pr($cashReceiptsForInvoice);
		if (!empty($cashReceiptsForInvoice)){
			foreach ($cashReceiptsForInvoice as $cashReceiptForInvoice){
				//pr($cashReceiptForInvoice);
				//$cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment']+$cashReceiptForInvoice['CashReceiptInvoice']['payment_retention'];
        $cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment'];
				$cashReceiptCurrencyId=$cashReceiptForInvoice['CashReceiptInvoice']['currency_id'];
				if ($cashReceiptCurrencyId==CURRENCY_CS){
					$paidAlreadyCS+=$cashReceiptAmount;
				}
				if ($cashReceiptCurrencyId==CURRENCY_USD){
					$cashReceiptModel=ClassRegistry::init('CashReceipt');
					$cashReceiptModel->recursive=-1;
					$cashReceipt=$cashReceiptModel->read(null,$cashReceiptForInvoice['CashReceiptInvoice']['cash_receipt_id']);
					
					$exchangeRateModel=ClassRegistry::init('ExchangeRate');
					$exchangeRateModel->recursive=-1;
					$exchangeRateCashReceipt=$exchangeRateModel->getApplicableExchangeRateValue($cashReceipt['CashReceipt']['receipt_date']);
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateCashReceipt;
				}
				//$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_increment_CS'];
				$paidAlreadyCS+=$cashReceiptForInvoice['CashReceiptInvoice']['payment_discount_CS'];
			}
		}
    //echo 'paidAlreadyCS is '.$paidAlreadyCS."<br/>";
		return $paidAlreadyCS;
	}
	
	function getAmountPaidAlreadyWithoutErDiffCS($id){
		$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
		
		$cashReceiptsForInvoice=$cashReceiptInvoiceModel->find('all',array(
			'fields'=>array(
				'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.currency_id',
				'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention',
				'CashReceiptInvoice.payment_erdiff_CS','CashReceiptInvoice.payment_increment_CS','CashReceiptInvoice.payment_discount_CS',
			),
			'conditions'=>array(
				'CashReceiptInvoice.invoice_id'=>$id,
			),
		));
		$paidAlreadyCS=0;
		//pr($cashReceiptsForInvoice);
		if (!empty($cashReceiptsForInvoice)){
			foreach ($cashReceiptsForInvoice as $cashReceiptForInvoice){
				//pr($cashReceiptForInvoice);
				$cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment']+$cashReceiptForInvoice['CashReceiptInvoice']['payment_retention'];
				$cashReceiptCurrencyId=$cashReceiptForInvoice['CashReceiptInvoice']['currency_id'];
				if ($cashReceiptCurrencyId==CURRENCY_CS){
					$paidAlreadyCS+=$cashReceiptAmount;
				}
				if ($cashReceiptCurrencyId==CURRENCY_USD){
					$cashReceiptModel=ClassRegistry::init('CashReceipt');
					$cashReceiptModel->recursive=-1;
					$cashReceipt=$cashReceiptModel->read(null,$cashReceiptForInvoice['CashReceiptInvoice']['cash_receipt_id']);
					
					$exchangeRateModel=ClassRegistry::init('ExchangeRate');
					$exchangeRateModel->recursive=-1;
					$cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceipt['CashReceipt']['receipt_date']);
					$exchangeRateICashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateICashReceipt;
				}
				$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				// DO NOT HANDLE INCREMENT AND DISCOUNT HERE, IT IS USED FOR CALCULUS PAYMENT IN EDIT CASHRECEIPT
				//$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_increment_CS'];
				//$paidAlreadyCS+=$cashReceiptForInvoice['CashReceiptInvoice']['payment_discount_CS'];
			}
		}
		return $paidAlreadyCS;
	}
	
  function getPendingAmountCS($id){
    // 20180510 NEW VERSION
    // taken from invoices ver historial pagos
    
		$thisInvoice=$this->find('first',[
			'fields'=>['Invoice.total_price','Invoice.currency_id','Invoice.invoice_date'],
			'conditions'=>['Invoice.id'=>$id],
		]);
		$totalForInvoice=$thisInvoice['Invoice']['total_price'];
    $totalForInvoiceCS=$totalForInvoice;
		$invoiceCurrencyId=$thisInvoice['Invoice']['currency_id'];
    
    $exchangeRateModel=ClassRegistry::init('ExchangeRate');
		$exchangeRateModel->recursive=-1;
    
    if ($invoiceCurrencyId==CURRENCY_USD){
      $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($thisInvoice['Invoice']['invoice_date']);
      $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
      $totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
    }
		
    $paidForInvoiceCS=round($this->getAmountPaidAlreadyCS($thisInvoice['Invoice']['id']),2);		
    $pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
		if ($invoiceCurrencyId==CURRENCY_USD){
      $cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');  
			$this->loadModel('CashReceiptInvoice');
      $cashReceiptInvoices=$cashReceiptInvoiceModel->find('all',[
        'conditions'=>['CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id']],
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
      $currentExchangeRate=$exchangeRateModel->getApplicableExchangeRate(date('Y-m-d'));
      $exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
      $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
      $differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
      $pendingForInvoiceCS+=$differenciaCambiariaTotal;
      // add the diferencia cambiaria on the cashreceipts
      if (!empty($cashReceiptInvoices)){
        for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
          $cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
          $exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
          $differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
          $differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
          $pendingForInvoiceCS-=$differenciaCambiariaPaid;
        }
      }
    }
    return $pendingForInvoiceCS;
	}

  function setDepositedStatus($id,$depositAmount,$currencyId){
    $depositedStatus=false;
    $thisInvoice=$this->find('first',[
			'fields'=>[
        'Invoice.invoice_date',
        'Invoice.total_price','Invoice.retention_amount','Invoice.currency_id',
      ],
			'conditions'=>['Invoice.id'=>$id],
		]);
    if (!empty($thisInvoice)){
      $invoiceDate=$thisInvoice['Invoice']['invoice_date'];
      $exchangeRateModel=ClassRegistry::init('ExchangeRate');
      $exchangeRateModel->recursive=-1;
      $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($invoiceDate);
      $exchangeRateInvoice=$invoiceExchangeRate['ExchangeRate']['rate'];
      
      //pr($exchangeRate);
      $thisInvoice['Invoice']['exchange_rate']=$exchangeRateInvoice;
      if ($thisInvoice['Invoice']['currency_id']==CURRENCY_CS){
        $thisInvoice['Invoice']['paid_amount_CS']=($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount']);
        $thisInvoice['Invoice']['paid_amount_USD']=round(($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount'])/$thisInvoice['Invoice']['exchange_rate'],2);
      }
      elseif ($thisInvoice['currency_id']==CURRENCY_USD){
        $thisInvoice['Invoice']['paid_amount_CS']=round(($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount'])*$thisInvoice['Invoice']['exchange_rate'],2);
        $thisInvoice['Invoice']['paid_amount_USD']=($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount']);
      }
      if ($currencyId == CURRENCY_CS){
        //echo "deposit  amount is ".$depositAmount." and paid amount is ".$thisInvoice['Invoice']['paid_amount_CS']."<br/>";
        if (($depositAmount-$thisInvoice['Invoice']['paid_amount_CS'])>-0.001){
          $depositedStatus=true;  
        }
      }
      elseif ($currencyId == CURRENCY_USD){
        if (($depositAmount-$thisInvoice['Invoice']['paid_amount_USD'])>-0.001){
          $depositedStatus=true;
        }
      }
    }
    return $depositedStatus;
  }

  function deleteInvoice($invoiceId){
    $invoice=$this->find('first',[
      'conditions'=>['Invoice.id'=>$invoiceId,],
      'contain'=>[
        'CashReceiptInvoice'=>['CashReceipt'],
      ],
    ]);
    
    $boolDeletionAllowed=true;
    $deletionErrorMessage="";
    
    if (!empty($invoice['CashReceiptInvoice'])){
      $boolDeletionAllowed=false;
      $receiptCounter=0;
      $deletionErrorMessage.="No se puede eliminar la factura porque existen cajas de recibo para esta factura con códigos ";
      foreach ($invoice['CashReceiptInvoice'] as $cashReceiptInvoice){
        $deletionErrorMessage.=$cashReceiptInvoice['CashReceipt']['receipt_code'];
        $receiptCounter++;
        if ($receiptCounter < count($invoice['CashReceiptInvoice'])){
          $deletionErrorMessage.=",";
        }
        else{
          $deletionErrorMessage.=".";
        }
      }
    }
    if(!$boolDeletionAllowed){
      return [
        'deletionSuccess'=>false,
        'message'=>$deletionErrorMessage,  
      ];
    }
    
    $this->id = $invoiceId;
    if (!$this->delete()) {
      return [
        'deletionSuccess'=>false,
        'message'=>'No se podía eliminar factura # '.$invoice['Invoice']['invoice_code'],  
      ];
		}     
    
		$invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);		
    return [
      'deletionSuccess'=>true,
      'message'=>'Se eliminó factura # '.$invoice['Invoice']['invoice_code'].' de fecha '.($invoiceDateTime->format('d-m-Y')),  
    ];
  }

	public $validate = [
		'invoice_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
		'invoice_date' => [
			'date' => [
				'rule' => ['date'],
			],
		],
	];

	public $belongsTo = [
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Enterprise' => [
			'className' => 'Enterprise',
			'foreignKey' => 'enterprise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'CreatingUser' => [
			'className' => 'User',
			'foreignKey' => 'creating_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'PaymentReceipt' => [
			'className' => 'PaymentReceipt',
			'foreignKey' => 'payment_receipt_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'PaymentMode' => [
			'className' => 'PaymentMode',
			'foreignKey' => 'payment_mode_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Shift' => [
			'className' => 'Shift',
			'foreignKey' => 'shift_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Operator' => [
			'className' => 'Operator',
			'foreignKey' => 'operator_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
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
		],
		'CashboxAccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'cashbox_accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'AccountingRegisterInvoice' => [
			'className' => 'AccountingRegisterInvoice',
			'foreignKey' => 'invoice_id',
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
		'CashReceiptInvoice' => [
			'className' => 'CashReceiptInvoice',
			'foreignKey' => 'invoice_id',
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
