<?php
App::uses('AppModel', 'Model');
/**
 * ThirdParty Model
 *
 * @property Order $Order
 */
class ThirdParty extends AppModel {
	var $displayField='company_name';
  
  public function sortByCompanyName($a,$b ){ 
    if( $a['name'] == $b['name'] ){ return 0 ; } 
    return ($a['name'] < $b['name']) ? -1 : 1;
  }
 
  public function getActiveClientList(){
    $clients = $this->find('list',[
			'conditions'=>[
				'bool_provider'=>false,
				'bool_active'=>true,
      ],
			'order'=>'company_name',
		]);
    return $clients;
  }
  public function getActiveClientListForEnterprise($enterpriseId,$maxLengthCompanyName=0){
    $clients = $this->find('list',[
      'conditions'=>[
        'ThirdParty.enterprise_id'=>$enterpriseId,
        'ThirdParty.bool_provider'=>false,
        'ThirdParty.bool_active'=>true
      ],
      'order'=>'ThirdParty.company_name ASC',
    ]);
    if ($maxLengthCompanyName > 0){
      if (!empty($clients)){
        foreach ($clients as $clientId=>$clientCompanyName){
          //pr($clientCompanyName);
          if (strlen($clientCompanyName) > $maxLengthCompanyName){
            $clients[$clientId]=substr($clientCompanyName,0,$maxLengthCompanyName-4)."...";
          }
        }
      }
    }
    return $clients;
  }
 
  public function getCurrentPendingPayment($clientId){
    $totalPending=0;
    $invoiceModel=ClassRegistry::init('Invoice');
    $exchangeRateModel=ClassRegistry::init('ExchangeRate');
    $cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
   	$pendingInvoices=$invoiceModel->find('all',array(
      'fields'=>array(
        'Invoice.id','Invoice.invoice_code',
        'Invoice.total_price','Invoice.currency_id',
        'Invoice.invoice_date','Invoice.due_date',
        'Invoice.client_id',
        'Currency.abbreviation','Currency.id'
      ),
      'conditions'=>array(
        'Invoice.bool_annulled'=>false,
        'Invoice.bool_paid'=>false,
        'Invoice.client_id'=>$clientId,					
      ),
    ));
			
		
    for ($i=0;$i<count($pendingInvoices);$i++){
      $totalForInvoice=$pendingInvoices[$i]['Invoice']['total_price'];
      $totalForInvoiceCS=$totalForInvoice;
      if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
        $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
        $totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
      }
      
      
      // get the amount already paid for this invoice
      $invoice_paid_already_CS=round($invoiceModel->getAmountPaidAlreadyCS($pendingInvoices[$i]['Invoice']['id']),2);
      $pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
      if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        
        $cashReceiptInvoices=$cashReceiptInvoiceModel->find('all',array(
          'conditions'=>array(
            'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$i]['Invoice']['id'],
          ),
          'contain'=>array(
            'CashReceipt'=>array(
              'fields'=>array(
                'CashReceipt.id','CashReceipt.receipt_code',
                'CashReceipt.receipt_date',
                'CashReceipt.bool_annulled',
              ),
            ),
            'Currency'=>array(
              'fields'=>array(
                'Currency.abbreviation','Currency.id',
              ),
            ),
          ),
        ));
        $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
        $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
        // add the diferencia cambiaria on the total
        $currentExchangeRate=$exchangeRateModel->getApplicableExchangeRate(date('Y-m-d'));
        $exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
        $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
        $differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
        $pendingForInvoice+=$differenciaCambiariaTotal;
        // add the diferencia cambiaria on the cashreceipts
        if (!empty($cashReceiptInvoices)){
          for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
            $cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
            $exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
            $differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
            $differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
            $pendingForInvoice-=$differenciaCambiariaPaid;
          }
        }
        
      }
      
      $totalPending+=$pendingForInvoice;
    }
    
    return $totalPending;
 }
	
 
	public $validate = [
		'bool_provider' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'company_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*
		'first_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'last_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		*/
	];
	public $belongsTo = [
		'AccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'CreditCurrency' => [
			'className' => 'Currency',
			'foreignKey' => 'credit_currency_id',
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
	];
	public $hasMany = [
    'PaymentReceipt' => [
			'className' => 'PaymentReceipt',
			'foreignKey' => 'client_id',
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
    'ClientEnterprise' => [
			'className' => 'ClientEnterprise',
			'foreignKey' => 'client_id',
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
		'ClientUser' => [
			'className' => 'ClientUser',
			'foreignKey' => 'client_id',
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
    'Order' => [
			'className' => 'Order',
			'foreignKey' => 'third_party_id',
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
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'client_id',
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
    'PurchaseOrder' => [
			'className' => 'PurchaseOrder',
			'foreignKey' => 'provider_id',
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
