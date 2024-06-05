<?php
App::uses('AppModel', 'Model');

class PurchaseOrder extends AppModel {
	public $displayField='purchase_order_code';

  public function getNewPurchaseOrderCode(){
    $lastPurchaseOrder = $this->find('first',[
			'fields'=>['purchase_order_code'],
			'order' => ['PurchaseOrder.purchase_order_code' => 'desc'],
		]);
		//pr($lastTransfer);
		if ($lastPurchaseOrder!= null){
			$newPurchaseOrderCode = intval(substr($lastPurchaseOrder['PurchaseOrder']['purchase_order_code'],3))+1;
			$newPurchaseOrderCode="OC_".str_pad($newPurchaseOrderCode,4,'0',STR_PAD_LEFT);
		}
		else {
			$newPurchaseOrderCode="OC_0001";
		}
    return $newPurchaseOrderCode;
  }
  
  /*
  function getAmountPaidAlreadyCS($id){
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
					$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateCashReceipt;
				}
				//$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_increment_CS'];
				$paidAlreadyCS+=$cashReceiptForInvoice['CashReceiptInvoice']['payment_discount_CS'];
			}
		}
		return $paidAlreadyCS;
	}
  */
  /*
  function getHistoricalCreditForProvider($provider_id){
		$purchaseOrders=$this->find('all',[
			'fields'=>['PurchaseOrder.id'],
			'conditions'=>['PurchaseOrder.provider_id'=>$provider_id],
		));
		$historicalCredit=0;
		if (count($purchaseOrders)>0){
			foreach ($purchaseOrders as $purchaseOrder){
				$historicalCredit+=$this->getCreditDays($purchaseOrder['PurchaseOrder']['id']);
			}
			$historicalCredit=$historicalCredit/count($purchaseOrders);
		}
		return $historicalCredit;
	}
  
  function getCreditDays($id){
		$thisPurchaseOrder=$this->find('first',[
			'fields'=>['PurchaseOrder.cost_total','PurchaseOrder.currency_id','PurchaseOrder.bool_credit','PurchaseOrder.bool_paid','PurchaseOrder.purchase_order_date','PurchaseOrder.due_date'],
			'conditions'=>['PurchaseOrder.id'=>$id],
		]);
		$creditDays=0;
		if ($thisPurchaseOrder['PurchaseOrder']['bool_credit']){
			if (!$thisPurchaseOrder['PurchaseOrder']['bool_paid']){
				$purchaseOrderDate=new DateTime($thisPurchaseOrder['PurchaseOrder']['purchase_order_date']);
        $currentDate= new DateTime(date('Y-m-d'));
				$daysLate=$currentDate->diff($purchaseOrderDate);
        $creditDays=$daysLate->days;
			}
			else {
        //
				//$lastCashReceiptForInvoice=$this->CashReceiptInvoice->find('first',array(
					//'fields'=>array('CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id','CashReceipt.receipt_date'),
					//'conditions'=>array(
					//	'CashReceiptInvoice.invoice_id'=>$id,
					//),
					//'order'=>'CashReceipt.receipt_date DESC',
				//));
				//if (!empty($lastCashReceiptForInvoice)){
				//	$receiptDate=new DateTime($lastCashReceiptForInvoice['CashReceipt']['receipt_date']);
        //  $invoiceDate=new DateTime($thisInvoice['Invoice']['invoice_date']);
        //  $currentDate= new DateTime(date('Y-m-d'));
				//	$daysLate=$invoiceDate->diff($receiptDate);
        //  $creditDays=$daysLate->days;
				//}
        //
			}
		}
		return $creditDays;
	}
	*/

	public $validate = array(
		'purchase_order_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'purchase_order_code' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
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
	public $belongsTo = [
		'Provider' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'provider_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
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
	];

	public $hasMany = [
		'PurchaseOrderProduct' => [
			'className' => 'PurchaseOrderProduct',
			'foreignKey' => 'purchase_order_id',
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
