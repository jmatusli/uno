<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class InvoicesController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getInvoiceCode','saveInvoice','invoiceInputsForPaymentReceipt','deleteInvoiceFromPaymentReceipt','invoicesTableForPaymentReceipt','getPendingInvoicesForClient','changePaidStatus');		
	}
  
  public function getInvoiceCode() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$paymentModeId=($_POST['paymentModeId']);
    $invoiceDateString=($_POST['invoiceDateString']);
		return $this->Invoice->getNewInvoiceCode($paymentModeId,$invoiceDateString);
  }
	
  public function saveInvoice() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$invoiceId=trim($_POST['id']);
    
		$enterpriseId=($_POST['enterpriseId']);
		$orderId=($_POST['orderId']);
    $shiftId=($_POST['shiftId']);
    $operatorId=($_POST['operatorId']);
    $paymentModeId=trim($_POST['paymentModeId']);
		$clientId=trim($_POST['clientId']);
    
    $invoiceCode=trim($_POST['invoiceCode']);
		$invoiceDateDay=trim($_POST['invoiceDateDay']);
    $invoiceDateMonth=trim($_POST['invoiceDateMonth']);
    $invoiceDateYear=trim($_POST['invoiceDateYear']);
		$invoiceDateString=$invoiceDateYear.'-'.$invoiceDateMonth.'-'.$invoiceDateDay;
		$invoiceDate=date( "Y-m-d", strtotime($invoiceDateString));
    
    $dueDateDay=trim($_POST['dueDateDay']);
    $dueDateMonth=trim($_POST['dueDateMonth']);
    $dueDateYear=trim($_POST['dueDateYear']);
    $dueDateString=$dueDateYear.'-'.$dueDateMonth.'-'.$dueDateDay;
		$dueDate=date( "Y-m-d", strtotime($dueDateString));
    
    $invoiceSubTotalPrice=trim($_POST['invoiceSubTotalPrice']);
    
    $invoicesWithThisCode=$this->Invoice->find('list',[
      'conditions'=>[
        'Invoice.invoice_code'=>$invoiceCode,  
        'Invoice.id !='=>$invoiceId,
        'Invoice.payment_receipt_id >'=>0,
      ],
    ]);
    if (!empty($invoicesWithThisCode)){
      throw new Exception(__('Ya existe una factura con este código'));
      return "Ya existe una factura con este código";
    }
    
    $datasource=$this->Invoice->getDataSource();
    $datasource->begin();
    try {
      $currentDateTime=new DateTime();
      //pr($this->request->data);
      $invoiceArray=[];
      $invoiceArray['Invoice']['enterprise_id']=$enterpriseId;
      $invoiceArray['Invoice']['order_id']=$orderId;
      $invoiceArray['Invoice']['shift_id']=$shiftId;
      $invoiceArray['Invoice']['operator_id']=$operatorId;
      $invoiceArray['Invoice']['payment_mode_id']=$paymentModeId;
      $invoiceArray['Invoice']['client_id']=$clientId;
      $invoiceArray['Invoice']['currency_id']=CURRENCY_CS;
      
      $invoiceArray['Invoice']['invoice_code']=$invoiceCode;
      $invoiceArray['Invoice']['invoice_date']=$invoiceDate;
      $invoiceArray['Invoice']['due_date']=$dueDate;
      $invoiceArray['Invoice']['sub_total_price']=$invoiceSubTotalPrice;
      $invoiceArray['Invoice']['iva_price']=0;
      $invoiceArray['Invoice']['total_price']=$invoiceSubTotalPrice;
      $invoiceArray['Invoice']['creating_user_id']=$this->Auth->User('id');
      
      if (empty($invoiceId)){
        $this->Invoice->create();
      }
      else {
        $this->Invoice->id=$invoiceId;
        if (!$this->Invoice->exists($invoiceId)) {
          throw new Exception(__('Factura inválida'));
        }
      }
      
      if (!$this->Invoice->save($invoiceArray)) {
        echo "Problema guardando la factura";
        //pr($invoiceArray);
        pr($this->validateErrors($this->Invoice));
        throw new Exception();
      }
      $invoiceId=$this->Invoice->id;
      
      $datasource->commit();
      
      $this->recordUserAction($this->Invoice->id,"crear",null);
      $this->recordUserActivity($this->Session->read('User.username'),"Se registró la factura ".$invoiceCode);
      
      //$this->Session->setFlash(__('Se guardó la factura.'),'default',['class' => 'success']);
      return "ok";
    } 
    catch(Exception $e){
      $datasource->rollback();
      //pr($e);
      //$this->Session->setFlash(__('No se guardó la factura.'), 'default',['class' => 'error-message']);
      return "No se guardó la factura";
    }
  }
	
  public function invoiceInputsForPaymentReceipt(){
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
    
		$orderId=($_POST['orderId']);
    $shiftId=($_POST['shiftId']);
    $operatorId=($_POST['operatorId']);
    $paymentModeId=($_POST['paymentModeId']);
    $clientId=($_POST['clientId']);
    $rowCounter=($_POST['rowCounter']);
    // invoice date is not needed as a condition because the order is already tied to a date
    $invoiceConditions=[
      'Invoice.order_id'=>$orderId,
      'Invoice.shift_id'=>$shiftId,
      'Invoice.operator_id'=>$operatorId,
      'Invoice.payment_mode_id'=>$paymentModeId,
      'Invoice.client_id'=>$clientId,
    ];
    
    $invoices=$this->Invoice->find('all',[
      'fields'=>['Invoice.id','Invoice.invoice_code','Invoice.sub_total_price'],
      'conditions'=>$invoiceConditions,
      'order'=>'Invoice.invoice_code ASC',
    ]);
    
    $this->set(compact('invoices'));
    $this->set(compact('paymentModeId'));
    $this->set(compact('shiftId','operatorId'));
    $this->set(compact('rowCounter'));
  }
  
  public function deleteInvoiceFromPaymentReceipt() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$invoiceId=trim($_POST['invoiceId']);
    
    $this->Invoice->id = $invoiceId;
		if (!$this->Invoice->exists()) {
			throw new NotFoundException(__('Invalid invoice'));
		}
		
    $deletionResult=$this->Invoice->deleteInvoice($invoiceId);
    if ($deletionResult['deletionSuccess']){
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la factura con id ".$invoiceId);
      return 'ok';
    }
    else {
      return $deletionResult['message']; 
    }
	}
  
  public function invoicesTableForPaymentReceipt(){
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
    
		$orderId=($_POST['orderId']);
    $shiftId=($_POST['shiftId']);
    $operatorId=($_POST['operatorId']);
    $paymentModeId=($_POST['paymentModeId']);
    $clientId=($_POST['clientId']);
    $callingRowId=($_POST['callingRowId']);
    $editingMode=($_POST['editingMode']);
    $this->set(compact('callingRowId','editingMode'));
    // invoice date is not needed as a condition because the order is already tied to a date
    $invoiceConditions=[
      'Invoice.order_id'=>$orderId,
      'Invoice.shift_id'=>$shiftId,
      'Invoice.operator_id'=>$operatorId,
      'Invoice.payment_mode_id'=>$paymentModeId,
      'Invoice.client_id'=>$clientId,
    ];
    //pr($invoiceConditions);
    $invoices=$this->Invoice->find('all',[
      'fields'=>['Invoice.id','Invoice.invoice_date','Invoice.invoice_code','Invoice.sub_total_price'],
      'conditions'=>$invoiceConditions,
      'contain'=>[
        'CashReceiptInvoice',
      ],
      'order'=>'Invoice.invoice_code ASC',
    ]);
    //pr($invoices);
    $this->set(compact('invoices'));
    
  }
  
	public function getPendingInvoicesForClient(){
		$this->layout = "ajax";
    
    $clientId=trim($_POST['clientId']);
    $enterpriseId=trim($_POST['enterpriseId']);
		$receiptDay=trim($_POST['receiptDay']);
		$receiptMonth=trim($_POST['receiptMonth']);
		$receiptYear=trim($_POST['receiptYear']);
		$cashReceiptCurrencyId=trim($_POST['currencyId']);
		//$boolRetention=trim($_POST['boolretention']);
		//if ($boolRetention=="true"){
		//	$boolRetention=1;
		//}
		//else {
		//	$boolRetention=0;
		//}
		
		if (!$clientId){
			throw new NotFoundException(__('Cliente no existe'));
		}
    if (!$enterpriseId){
			throw new NotFoundException(__('Gasolinera no existe'));
		}
		
		$this->loadModel('CashReceiptInvoice');
		$this->loadModel('ExchangeRate');
		
		$receiptDateString=$receiptYear.'-'.$receiptMonth.'-'.$receiptDay;
		$receiptDate=date( "Y-m-d", strtotime($receiptDateString));
		$exchangeRateCashReceipt=$this->ExchangeRate->getApplicableExchangeRateValue($receiptDate);
		
		// $this->InvoiceProduct->virtualFields['total_product_quantity']=0;
		$invoicesForClient=$this->Invoice->find('all',[
			'fields'=>[
				//'SUM(product_quantity) AS InvoiceProduct__total_product_quantity', 
				'Invoice.id','Invoice.order_id','Invoice.invoice_code','Invoice.invoice_date',
				'Invoice.currency_id','Invoice.due_date',
				// when registering the sale, no cashbox accounting code nor retention should have been registered yet
				// 'Invoice.cashbox_accounting_code_id','Invoice.bool_retention','Invoice.retention_amount','Invoice.retention_number',
				'Invoice.sub_total_price',
				//'Invoice.bool_IVA','Invoice.IVA_price',
				'Invoice.total_price',
				'Currency.id','Currency.abbreviation'
			],
			'conditions'=>[
				'Invoice.client_id'=>$clientId,
        'Invoice.enterprise_id'=>$enterpriseId,
				'Invoice.bool_credit'=>true,
				'Invoice.bool_annulled'=>false,
				'Invoice.bool_paid'=>false,
        'Invoice.payment_receipt_id >'=>0,
			],
			'order'=>'Invoice.invoice_date ASC'
		]);
		//pr($invoicesForClient);
		
		for ($i=0;$i<count($invoicesForClient);$i++){
			$totalForInvoice=$invoicesForClient[$i]['Invoice']['total_price'];
			$pendingForInvoice=$totalForInvoice;
			$invoiceCurrencyId=$invoicesForClient[$i]['Invoice']['currency_id'];
			$invoiceDate=$invoicesForClient[$i]['Invoice']['invoice_date'];
			
			// add the retention amount
			//$invoicesForClient[$i]['Invoice']['retention']=round($invoicesForClient[$i]['Invoice']['sub_total_price']*0.02,2);
			
      $difference_exchange_rates=0;
			$exchange_rate_difference=0;
			
			$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($invoiceDate);
			if ($invoiceCurrencyId==CURRENCY_USD){
				if (($exchangeRateCashReceipt-$exchangeRateInvoiceDate)>0.00001){
					$difference_exchange_rates=$exchangeRateCashReceipt-$exchangeRateInvoiceDate;
					$exchange_rate_difference=round($totalForInvoice*$difference_exchange_rates,2);
					if ($exchange_rate_difference<0){
						$exchange_rate_difference=0;
					}
				}
			}
			$invoicesForClient[$i]['Invoice']['invoice_exchange_rate']=$exchangeRateInvoiceDate;
			$invoicesForClient[$i]['Invoice']['difference_exchange_rates']=$difference_exchange_rates;
			$invoicesForClient[$i]['Invoice']['exchange_rate_difference']=$exchange_rate_difference;
			
			// get the amount already paid for this invoice
			// NOTICE THAT WE USE RATE OF CURRENT DATE FOR PENDING CALCULATION
			$invoicesForClient[$i]['Invoice']['paid_already_CS']=round($this->Invoice->getAmountPaidAlreadyCS($invoicesForClient[$i]['Invoice']['id']),2);
			
			$diferenciaCambiariaPagado=0;
			if ($invoicesForClient[$i]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$invoicesForClient[$i]['Invoice']['id'],
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
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($invoicesForClient[$i]['Invoice']['invoice_date']);
				// add the diferencia cambiaria on the total
				$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$exchangeRatePreviousCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$differenceExchangeRateNowCashReceipt=$exchangeRateCashReceipt-$exchangeRatePreviousCashReceiptDate;
						//echo "difference exchange rate between now and cashreceipt is".$differenceExchangeRateNowCashReceipt."<br/>";
						//echo "payment_credit_CS is".$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']."<br/>";
						//echo "exchange rate previous cash receipts is".$exchangeRatePreviousCashReceiptDate."<br/>";
						//echo "diferenciaCambiariaPagado is".$diferenciaCambiariaPagado."<br/>";
						$diferenciaCambiariaPagado+=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRatePreviousCashReceiptDate;
						//echo "diferenciaCambiariaPagado is ".$diferenciaCambiariaPagado."<br/>";
					}
				}
			}
			$invoicesForClient[$i]['Invoice']['diferencia_cambiaria_pagado']=round($diferenciaCambiariaPagado,2);
			
		}
		
		//pr($invoicesForClient);
		//$this->set(compact('invoicesForClient','id','cashReceiptCurrencyId','exchangeRateCashReceipt','boolRetention'));
    $this->set(compact('invoicesForClient','id','cashReceiptCurrencyId','exchangeRateCashReceipt'));
	}

	public function changePaidStatus($id){
		$this->autoRender=false;
	
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Factura no válida'));
		}
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.id'=>$id,
			),
		));
		if (!empty($invoice)){
			$this->Invoice->id=$id;
			$invoiceData['Invoice']['id']=$id;
			$invoiceData['Invoice']['bool_paid']=!$invoice['Invoice']['bool_paid'];
			if ($this->Invoice->save($invoiceData)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('Se cambió el estado de pago de factura '.$invoice['Invoice']['invoice_code'].'.'),'default',array('class' => 'success'));
				return $this->redirect(array('controller'=>'orders','action' => 'verVenta',$invoice['Invoice']['order_id']));
			} 
		}
		
		$this->Session->setFlash(__('No se podía modificar el estado de pagado de la factura.'), 'default',array('class' => 'error-message')); 
		return $this->redirect(Router::url( $this->referer(), true ));		
	}
	
	public function resumen() {
		$this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Shift');
    $this->loadModel('Operator');
  	
    /*
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
    */
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $paymentModeId=PAYMENT_MODE_CREDIT;
    $shiftId=0;
    $operatorId=0;
    
    $startDate = null;
		$endDate = null;
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
      
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      //$displayOptionId=$this->request->data['Report']['display_option_id'];
      
      $paymentModeId=$this->request->data['Report']['payment_mode_id'];
      $shiftId=$this->request->data['Report']['shift_id'];
      $operatorId=$this->request->data['Report']['operator_id'];
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
    $this->set(compact('paymentModeId','shiftId','operatorId'));
    //$this->set(compact('displayOptionId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    
    $this->set(compact('enterprises'));
    
    $invoiceConditions=[
      'Invoice.invoice_date >='=>$startDate,
      'Invoice.invoice_date <'=>$endDatePlusOne,
      'Invoice.payment_receipt_id !='=>0,
    ];
    
    if ($paymentModeId > 0){
      $invoiceConditions['Invoice.payment_mode_id']=$paymentModeId;
    }
    if ($shiftId > 0){
      $invoiceConditions['Invoice.shift_id']=$shiftId;
    }
    if ($operatorId > 0){
      $invoiceConditions['Invoice.operator_id']=$operatorId;
    }
    
    $invoiceCount=$this->Invoice->find('count', [
			'conditions' => $invoiceConditions,
		]);
    
    $this->Paginator->settings = [
      'conditions'=>$invoiceConditions,
      'contain'=>[
        'Client',
        'PaymentMode',
        'Shift',
        'Operator',
      ],
      'order'=>'Invoice.invoice_date DESC',
      'limit'=>($invoiceCount!=0?$invoiceCount:1),
    ];
    
    $invoices = $this->Paginator->paginate('Invoice');
    $this->set(compact('invoices'));
    
    $paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
    
    $shifts=$this->Shift->getShiftListForEnterprise($enterpriseId);
    $this->set(compact('shifts'));
    
    $operators=$this->Operator->getOperatorListForEnterprise($enterpriseId);
    $this->set(compact('operators'));
    
    $this->set(compact('invoices'));
    $aco_name="Invoices/crear";
    $bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Invoices/editar";
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="PaymentModes/view";
		$boolPaymentModeDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolPaymentModeDetailPermission'));
    $aco_name="Shifts/view";
		$boolShiftDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolShiftDetailPermission'));
    $aco_name="Operators/detalle";
		$boolOperatorDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolOperatorDetailPermission'));
    
	}
  
  public function guardarResumenFacturas(){
    $exportData=$_SESSION['resumenFacturas'];
		$this->set(compact('exportData','fileName'));
  }

	public function detalle($id = null) {
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Invalid invoice'));
		}
    $invoiceConditions=['Invoice.id' => $id];
    $invoice=$this->Invoice->find('first', [
      'conditions'=>$invoiceConditions,
      'contain'=>[
        'Enterprise',
        'Client',
        'PaymentMode',
        'Shift',
        'Operator',
        'CreatingUser',
        'CashReceiptInvoice'=>[
          'CashReceipt',
        ],
      ],
    ]);
		$this->set(compact('invoice'));
    
    $aco_name="Invoices/crear";
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    $aco_name="Invoices/editar";
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Enterprises/detalle";
		$boolEnterpriseDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolEnterpriseDetailPermission'));
    $aco_name="PaymentModes/view";
		$boolPaymentModeDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolPaymentModeDetailPermission'));
    $aco_name="Shifts/view";
		$boolShiftDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolShiftDetailPermission'));
    $aco_name="Operators/detalle";
		$boolOperatorDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolOperatorDetailPermission'));
    
    $aco_name="ThirdParties/verCliente";
		$boolClientDetailPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolClientDetailPermission'));
    $aco_name="ThirdParties/resumenClientes";
		$boolClientIndexPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolClientIndexPermission'));
    $aco_name="ThirdParties/crearCliente";
		$boolClientAddPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolClientAddPermission'));
	}
  
  public function detallePdf($id = null) {
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Invalid invoice'));
		}
    $invoiceConditions=['Invoice.id' => $id];
    $invoice=$this->Invoice->find('first', [
      'conditions'=>$invoiceConditions,
      'contain'=>[
        'Enterprise',
        'Client',
        'PaymentMode',
        'Shift',
        'Operator',
        'CreatingUser',
        'CashReceiptInvoice'=>[
          'CashReceipt',
        ],
      ],
    ]);
		$this->set(compact('invoice'));
	}

	public function crear() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Shift');
    $this->loadModel('Operator');
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		if ($this->request->is('post')) {
			$this->Invoice->create();
			if ($this->Invoice->save($this->request->data)) {
				$this->recordUserAction($this->Invoice->id,null,null);
				$this->Session->setFlash(__('The invoice has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			else {
				$this->Session->setFlash(__('The invoice could not be saved. Please, try again.'), 'default',['class' => 'error-message']); 
			}
		}
		$paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
    
    $shifts=$this->Shift->getShiftListForEnterprise($enterpriseId);
    $this->set(compact('shifts'));
    
    $operators=$this->Operator->getOperatorListForEnterprise($enterpriseId);
    $this->set(compact('operators'));
    
    $clients = $this->Invoice->Client->find('list',[
			'conditions'=>[
				'bool_active'=>true,
			],
		]);
		
    $currencies = $this->Invoice->Currency->find('list',['conditions'=>['Currency.id'=>CURRENCY_CS]]);
		$this->set(compact('clients', 'currencies'));
	}

	public function editar($id = null) {
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Invalid invoice'));
		}
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('PaymentMode');
    $this->loadModel('Shift');
    $this->loadModel('Operator');
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    
		if ($this->request->is(['post', 'put'])) {
			if ($this->Invoice->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The invoice has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			else {
				$this->Session->setFlash(__('The invoice could not be saved. Please, try again.'), 'default',['class' => 'error-message']); 
			}
		} 
		else {
			$this->request->data = $this->Invoice->find('first', ['conditions' => ['Invoice.id' => $id]]);
		}
		
		$clients = $this->Invoice->Client->find('list',[
			'conditions'=>[
				'bool_active'=>true,
			],
		]);
		$currencies = $this->Invoice->Currency->find('list',['conditions'=>['Currency.id'=>CURRENCY_CS]]);
		$this->set(compact('orders', 'clients', 'currencies'));
    
    $paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
    
    $shifts=$this->Shift->getShiftListForEnterprise($enterpriseId);
    $this->set(compact('shifts'));
    
    $operators=$this->Operator->getOperatorListForEnterprise($enterpriseId);
    $this->set(compact('operators'));
    
    $clients = $this->Invoice->Client->find('list',[
			'conditions'=>[
				'bool_active'=>true,
			],
		]);
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Invoice->id = $id;
		if (!$this->Invoice->exists()) {
			throw new NotFoundException(__('Invalid invoice'));
		}
		$this->request->allowMethod('post', 'delete');
		
    $deletionResult=$this->Invoice->deleteInvoice($id);
    $this->Session->setFlash($deletionResult['message'],'default',['class' => ($deletionResult['deletionSuccess']?'success':'error-message')]);
    if ($deletionResult['deletionSuccess']){
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la factura con id ".$id);
      return $this->redirect(['action' => 'resumen']);
    }
    else {
      return $this->redirect(['action' => 'detalle',$id]); 
    }
	}

	public function estadoCuentas() {
    $this->loadModel('PaymentMode');
    $this->loadModel('ThirdParty');
    
		$this->loadModel('ExchangeRate');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Invoice->recursive = 0;
		
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $paymentModeId=PAYMENT_MODE_CREDIT;
    
    define('INVOICE_DISPLAY_OPTION_ALL','0'); 
    define('INVOICE_DISPLAY_OPTION_UNPAID','1'); 
    define('INVOICE_DISPLAY_OPTION_PAID','2'); 
    $invoiceDisplayOptions=[
      INVOICE_DISPLAY_OPTION_ALL=>'Mostrar todas facturas',
      INVOICE_DISPLAY_OPTION_UNPAID=>'Mostrar solo facturas pendientes',
      INVOICE_DISPLAY_OPTION_PAID=>'Mostrar solo facturas pagadas',
    ];
    $this->set(compact('invoiceDisplayOptions'));
    $invoiceDisplayOptionId=INVOICE_DISPLAY_OPTION_UNPAID;
    
    define('AMOUNT_DISPLAY_OPTION_INVOICE','0'); 
    define('AMOUNT_DISPLAY_OPTION_PENDING','1'); 
    define('AMOUNT_DISPLAY_OPTION_PAID','2'); 
    $amountDisplayOptions=[
      AMOUNT_DISPLAY_OPTION_INVOICE=>'Mostrar monto factura',
      AMOUNT_DISPLAY_OPTION_PENDING=>'Mostrar monto pendiente',
      AMOUNT_DISPLAY_OPTION_PAID=>'Mostrar monto pagado',
    ];
    $this->set(compact('amountDisplayOptions'));
    $amountDisplayOptionId=AMOUNT_DISPLAY_OPTION_PENDING;
    
    $allSundays=[];
    $sunday=date('2019-09-29');
    do {
      array_unshift($allSundays,$sunday);
      $sunday = date("Y-m-d",strtotime($sunday."+7 days"));
    }
    while ($sunday <= date('Y-m-d'));
    $sundays=[];
    foreach ($allSundays as $listedSunday){
      $sundayDateTime=new DateTime($listedSunday);
      $sundays[]=$sundayDateTime -> format ('d-m-Y');
    }
    //pr($allSundays);
    $lastSunday=$allSundays[0];
    $allSaturdays=[];
    $saturday=date('2019-10-05');
    array_unshift($allSaturdays,$saturday);
    do {
      $saturday = date("Y-m-d",strtotime($saturday."+7 days"));
      array_unshift($allSaturdays,$saturday);
    }
    while ($saturday <= $lastSunday);
    //pr($allSaturdays);
    $saturdays=[];
    foreach ($allSaturdays as $listedSaturday){
      $saturdayDateTime=new DateTime($listedSaturday);
      $saturdays[]=$saturdayDateTime -> format ('d-m-Y');
    }
    $sundayId=4;
    $saturdayId=0;
    //$this->set(compact('allSundays','allSaturdays'));
    $this->set(compact('sundays','saturdays'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
      $sundayId=$this->request->data['Report']['sunday_id'];
      $saturdayId=$this->request->data['Report']['saturday_id'];
      
      $paymentModeId=$this->request->data['Report']['payment_mode_id'];	
      $invoiceDisplayOptionId=$this->request->data['Report']['invoice_display_option_id'];
      $amountDisplayOptionId=$this->request->data['Report']['amount_display_option_id'];
		}
		//else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
		//	$startDate=$_SESSION['startDate'];
		//	$endDate=$_SESSION['endDate'];
		//	$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		//}
		//else {
		//	$startDate = date("Y-m-01");
		//	$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		//	$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		//}
    //$_SESSION['startDate']=$startDate;
		//$_SESSION['endDate']=$endDate;
    
    $this->set(compact('sundayId','saturdayId'));
    $this->set(compact('paymentModeId'));
    $this->set(compact('invoiceDisplayOptionId'));
    $this->set(compact('amountDisplayOptionId'));
    
    //echo 'amountDisplayOptionId is '.$amountDisplayOptionId.'<br/>';
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
		
    $clients=$this->ThirdParty->getActiveClientList();
    $this->Set(compact('clients'));
    
    $weeks=[];
    if ($enterpriseId > 0){
      $generalConditions=[
        'Invoice.enterprise_id'=>$enterpriseId,
        'Invoice.sub_total_price >'=>0,
      ];
      if ($paymentModeId > 0){
        $generalConditions['Invoice.payment_mode_id']=$paymentModeId;
      }
      switch($invoiceDisplayOptionId){
        case INVOICE_DISPLAY_OPTION_UNPAID:
          $generalConditions['Invoice.bool_paid']=false;
          break;
        case INVOICE_DISPLAY_OPTION_PAID:
          $generalConditions['Invoice.bool_paid']=true;
          break;
        case INVOICE_DISPLAY_OPTION_ALL:
        default:
      }
      $currentSundayId=$saturdayId;
      while ($currentSundayId <= $sundayId){
        $invoiceTotalsArray=[];
        $currentDate=$sundayDate=date( "Y-m-d",strtotime($allSundays[$currentSundayId]));
        $saturdayDate=date( "Y-m-d",strtotime($allSaturdays[$currentSundayId]));
        
        $invoiceConditions=$generalConditions;
        $invoiceConditions['invoice_date >=']=$sundayDate;
        $invoiceConditions['invoice_date <=']=$saturdayDate;
        //pr($invoiceConditions);
      
        $invoices=$this->Invoice->find('all',[
          'conditions'=>$invoiceConditions,
          'contain'=>[
            'CashReceiptInvoice'=>['CashReceipt',],
          ],
          'order'=>'Invoice.invoice_date'
        ]);
      
        $clientIdsForWeek=[];
        if (!empty($invoices)){
          foreach ($invoices as $invoice){
            //pr($invoice);
            $clientIdsForWeek[]=$invoice['Invoice']['client_id'];
          }
          $clientIdsForWeek=array_unique($clientIdsForWeek);
        }
        //pr($clientIdsForWeek);
        // initialization
        $invoiceTotalsArray['Total']=0;
        while ($currentDate<=$saturdayDate){
          $invoiceTotalsArray['DayTotal'][$currentDate]=0;
          $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
        }
        if (empty($clientIdsForWeek)){
          $invoiceTotalsArray['Client']=[];
        }
        else {  
          foreach ($clientIdsForWeek as $clientId){
            $invoiceTotalsArray['Client'][$clientId]['Total']=0;
            $currentDate=$sundayDate;
            while ($currentDate<=$saturdayDate){
              $invoiceTotalsArray['Client'][$clientId]['DayTotal'][$currentDate]=0;
              $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
            }
          }
        }
        
        if (!empty($invoices)){
          foreach ($invoices as $invoice){
            $invoiceDate=$invoice['Invoice']['invoice_date'];
            $invoiceClientId=$invoice['Invoice']['client_id'];
            
            $totalAmountInvoiceCS=$totalAmountInvoice=$invoice['Invoice']['sub_total_price'];
            if ($invoice['Invoice']['currency_id'] != CURRENCY_CS){
              $invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($invoiceDate,$invoice['Invoice']['currency_id']);
              $totalAmountInvoiceCS=$totalAmountInvoiice*$invoiceExchangeRate;
            }
            if ($invoice['Invoice']['bool_paid']){
              $totalPaidCS=$totalAmountInvoiceCS;  
            }
            else {
              $totalPaidCS=$totalPaid=0;
              if (!empty($invoice['CashReceiptInvoice'])){
                foreach ($invoice['CashReceiptInvoice'] as $cashReceiptInvoice){
                  if ($cashReceiptInvoice['CashReceipt']['currency_id'] == CURRENCY_CS){
                    $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount'];  
                  }
                  else {
                    $cashReceiptDate=$cashReceiptInvoice['CashReceipt']['receipt_date'];  
                    $cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoice['CashReceipt']['currency_id']);
                    $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount']*$cashReceiptExchangeRate;  
                  }
                }
              }
            }
            
            switch($amountDisplayOptionId){
              case AMOUNT_DISPLAY_OPTION_PENDING:
                $invoiceAmount=$totalAmountInvoiceCS-$totalPaidCS;
                break;
              case AMOUNT_DISPLAY_OPTION_PAID:
                $invoiceAmount=$totalPaidCS;
                break;  
              case AMOUNT_DISPLAY_OPTION_INVOICE:
              default:
                $invoiceAmount=$totalAmountInvoiceCS;
            }
            $invoiceTotalsArray['Total']+=$invoiceAmount;
            $invoiceTotalsArray['DayTotal'][$invoiceDate]+=$invoiceAmount;
            $invoiceTotalsArray['Client'][$invoiceClientId]['DayTotal'][$invoiceDate]+=$invoiceAmount;
            $invoiceTotalsArray['Client'][$invoiceClientId]['Total']+=$invoiceAmount;
          }
        }
        //pr($invoiceTotalsArray);
        
        $weeks['Week'][$currentSundayId]=$invoiceTotalsArray;
        $currentSundayId++;
      }
    }
    //pr($weeks);
    $this->set(compact('weeks'));
    
    $paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
	}
	
  public function guardarEstadoCuentas($fileName){
    $exportData=$_SESSION['estadoCuentas'];
		$this->set(compact('exportData','fileName'));
  }
  
  public function estadoCuentasCliente($clientId,$sundayId=0) {
    $this->loadModel('PaymentMode');
    $this->loadModel('ThirdParty');
    
		$this->loadModel('ExchangeRate');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Invoice->recursive = 0;
		
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $paymentModeId=PAYMENT_MODE_CREDIT;
    
    //define('INVOICE_DISPLAY_OPTION_ALL','0'); 
    //define('INVOICE_DISPLAY_OPTION_UNPAID','1'); 
    //define('INVOICE_DISPLAY_OPTION_PAID','2'); 
    //$invoiceDisplayOptions=[
    //  INVOICE_DISPLAY_OPTION_ALL=>'Mostrar todas facturas',
    //  INVOICE_DISPLAY_OPTION_UNPAID=>'Mostrar solo facturas pendientes',
    //  INVOICE_DISPLAY_OPTION_PAID=>'Mostrar solo facturas pagadas',
    //];
    //$this->set(compact('invoiceDisplayOptions'));
    //$invoiceDisplayOptionId=INVOICE_DISPLAY_OPTION_UNPAID;
    
    //define('AMOUNT_DISPLAY_OPTION_INVOICE','0'); 
    //define('AMOUNT_DISPLAY_OPTION_PENDING','1'); 
    //define('AMOUNT_DISPLAY_OPTION_PAID','2'); 
    //$amountDisplayOptions=[
    //  AMOUNT_DISPLAY_OPTION_INVOICE=>'Mostrar monto factura',
    //  AMOUNT_DISPLAY_OPTION_PENDING=>'Mostrar monto pendiente',
    //  AMOUNT_DISPLAY_OPTION_PAID=>'Mostrar monto pagado',
    //];
    //$this->set(compact('amountDisplayOptions'));
    //$amountDisplayOptionId=AMOUNT_DISPLAY_OPTION_PENDING;
    
    $allSundays=[];
    $sunday=date('2019-09-29');
    do {
      array_unshift($allSundays,$sunday);
      $sunday = date("Y-m-d",strtotime($sunday."+7 days"));
    }
    while ($sunday <= date('Y-m-d'));
    $sundays=[];
    foreach ($allSundays as $listedSunday){
      $sundayDateTime=new DateTime($listedSunday);
      $sundays[]=$sundayDateTime -> format ('d-m-Y');
    }
    //pr($allSundays);
    $lastSunday=$allSundays[0];
    $allSaturdays=[];
    $saturday=date('2019-10-05');
    array_unshift($allSaturdays,$saturday);
    do {
      $saturday = date("Y-m-d",strtotime($saturday."+7 days"));
      array_unshift($allSaturdays,$saturday);
    }
    while ($saturday <= $lastSunday);
    //pr($allSaturdays);
    $saturdays=[];
    foreach ($allSaturdays as $listedSaturday){
      $saturdayDateTime=new DateTime($listedSaturday);
      $saturdays[]=$saturdayDateTime -> format ('d-m-Y');
    }
    $saturdayId=$sundayId;
    //$this->set(compact('allSundays','allSaturdays'));
    $this->set(compact('sundays','saturdays'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
      $sundayId=$this->request->data['Report']['sunday_id'];
      //$saturdayId=$this->request->data['Report']['saturday_id'];
      //$paymentModeId=$this->request->data['Report']['payment_mode_id'];	
      //$invoiceDisplayOptionId=$this->request->data['Report']['invoice_display_option_id'];
      //$amountDisplayOptionId=$this->request->data['Report']['amount_display_option_id'];
		}
		//else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
		//	$startDate=$_SESSION['startDate'];
		//	$endDate=$_SESSION['endDate'];
		//	$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		//}
		//else {
		//	$startDate = date("Y-m-01");
		//	$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		//	$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		//}
    //$_SESSION['startDate']=$startDate;
		//$_SESSION['endDate']=$endDate;
    $this->set(compact('clientId'));
    $this->set(compact('sundayId','saturdayId'));
    //$this->set(compact('paymentModeId'));
    //$this->set(compact('invoiceDisplayOptionId'));
    //$this->set(compact('amountDisplayOptionId'));
    
    //echo 'amountDisplayOptionId is '.$amountDisplayOptionId.'<br/>';
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
		
    $clients=$this->ThirdParty->getActiveClientList();
    $this->Set(compact('clients'));
    
    //$weeks=[];
    if ($enterpriseId > 0){
      $generalConditions=[
        'Invoice.enterprise_id'=>$enterpriseId,
        'Invoice.sub_total_price >'=>0,
        'Invoice.client_id'=>$clientId,
      ];
      if ($paymentModeId > 0){
        $generalConditions['Invoice.payment_mode_id']=$paymentModeId;
      }
      //switch($invoiceDisplayOptionId){
      //  case INVOICE_DISPLAY_OPTION_UNPAID:
      //    $generalConditions['Invoice.bool_paid']=false;
      //    break;
      //  case INVOICE_DISPLAY_OPTION_PAID:
      //    $generalConditions['Invoice.bool_paid']=true;
      //    break;
      // case INVOICE_DISPLAY_OPTION_ALL:
      //  default:
      //}
      $currentSundayId=$saturdayId;
      //while ($currentSundayId <= $sundayId){
      //  $invoiceTotalsArray=[];
        $currentDate=$sundayDate=date( "Y-m-d",strtotime($allSundays[$currentSundayId]));
        $saturdayDate=date( "Y-m-d",strtotime($allSaturdays[$currentSundayId]));
        
        $invoiceConditions=$generalConditions;
        $invoiceConditions['invoice_date >=']=$sundayDate;
        $invoiceConditions['invoice_date <=']=$saturdayDate;
        //pr($invoiceConditions);
      
        $invoices=$this->Invoice->find('all',[
          'conditions'=>$invoiceConditions,
          'contain'=>[
            'CashReceiptInvoice'=>['CashReceipt',],
          ],
          'order'=>'Invoice.invoice_date'
        ]);
      
        //$clientIdsForWeek=[];
        //if (!empty($invoices)){
        //  foreach ($invoices as $invoice){
        //    //pr($invoice);
        //    $clientIdsForWeek[]=$invoice['Invoice']['client_id'];
        //  }
        //  $clientIdsForWeek=array_unique($clientIdsForWeek);
        //}
        //pr($clientIdsForWeek);
        // initialization
      //  $invoiceTotalsArray['Total']=0;
      //  while ($currentDate<=$saturdayDate){
      //    $invoiceTotalsArray['DayTotal'][$currentDate]=0;
          $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
        //}
        //if (empty($clientIdsForWeek)){
        //  $invoiceTotalsArray['Client']=[];
        //}
        //else {  
          //foreach ($clientIdsForWeek as $clientId){
            $invoiceTotalsArray['Client'][$clientId]['Total']['amount_cs']=0;
            $invoiceTotalsArray['Client'][$clientId]['Total']['saldo_cs']=0;
            $currentDate=$sundayDate;
            while ($currentDate<=$saturdayDate){
              $invoiceTotalsArray['Client'][$clientId]['DayTotal'][$currentDate]=0;
              $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
            }
          //}
        //}
        
        if (!empty($invoices)){
          $invoiceCounter=0;
          foreach ($invoices as $invoice){
            $invoiceDate=$invoice['Invoice']['invoice_date'];
            $invoiceClientId=$invoice['Invoice']['client_id'];
            
            $totalAmountInvoiceCS=$totalAmountInvoice=$invoice['Invoice']['sub_total_price'];
            if ($invoice['Invoice']['currency_id'] != CURRENCY_CS){
              $invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($invoiceDate,$invoice['Invoice']['currency_id']);
              $totalAmountInvoiceCS=$totalAmountInvoiice*$invoiceExchangeRate;
            }
            if ($invoice['Invoice']['bool_paid']){
              $totalPaidCS=$totalAmountInvoiceCS;  
            }
            else {
              $totalPaidCS=$totalPaid=0;
              if (!empty($invoice['CashReceiptInvoice'])){
                foreach ($invoice['CashReceiptInvoice'] as $cashReceiptInvoice){
                  if ($cashReceiptInvoice['CashReceipt']['currency_id'] == CURRENCY_CS){
                    $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount'];  
                  }
                  else {
                    $cashReceiptDate=$cashReceiptInvoice['CashReceipt']['receipt_date'];  
                    $cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoice['CashReceipt']['currency_id']);
                    $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount']*$cashReceiptExchangeRate;  
                  }
                }
              }
            }
            
            /*
            switch($amountDisplayOptionId){
              case AMOUNT_DISPLAY_OPTION_PENDING:
                $invoiceAmount=$totalAmountInvoiceCS-$totalPaidCS;
                break;
              case AMOUNT_DISPLAY_OPTION_PAID:
                $invoiceAmount=$totalPaidCS;
                break;  
              case AMOUNT_DISPLAY_OPTION_INVOICE:
              default:
                $invoiceAmount=$totalAmountInvoiceCS;
            }
            */
            
            
            $invoices[$invoiceCounter]['Invoice']['amount_cs']=$totalAmountInvoiceCS;
            $invoices[$invoiceCounter]['Invoice']['saldo_cs']=$totalAmountInvoiceCS-$totalPaidCS;
            
            //$invoiceTotalsArray['Total']+=$invoiceAmount;
            //$invoiceTotalsArray['DayTotal'][$invoiceDate]+=$invoiceAmount;
            //$invoiceTotalsArray['Client'][$invoiceClientId]['DayTotal'][$invoiceDate]['amount_cs']+=$totalAmountInvoiceCS;
            //$invoiceTotalsArray['Client'][$invoiceClientId]['DayTotal'][$invoiceDate]['saldo_cs']+=($totalAmountInvoiceCS-$totalPaidCS);
            $invoiceTotalsArray['Client'][$invoiceClientId]['Total']['amount_cs']+=$totalAmountInvoiceCS;
            $invoiceTotalsArray['Client'][$invoiceClientId]['Total']['saldo_cs']+=($totalAmountInvoiceCS-$totalPaidCS);
            $invoiceCounter++;
          }
        }
        //pr($invoiceTotalsArray);
        
        //$weeks['Week'][$currentSundayId]=$invoiceTotalsArray;
        //$currentSundayId++;
     // }
    //}
    //pr($weeks);
    //$this->set(compact('weeks'));
    }
    $this->set(compact('invoiceTotalsArray'));
    $this->set(compact('invoices'));
    
    $paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
	}
	
  public function guardarEstadoCuentasCliente($fileName){
    $exportData=$_SESSION['estadoCuentasCliente'];
		$this->set(compact('exportData','fileName'));
  }
  
  public function estadoCuentasClientePdf($clientId,$sundayId,$enterpriseId) {
    $this->loadModel('PaymentMode');
    $this->loadModel('ThirdParty');
    
		$this->loadModel('ExchangeRate');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Invoice->recursive = 0;
	    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $paymentModeId=PAYMENT_MODE_CREDIT;
    
    $allSundays=[];
    $sunday=date('2019-09-29');
    do {
      array_unshift($allSundays,$sunday);
      $sunday = date("Y-m-d",strtotime($sunday."+7 days"));
    }
    while ($sunday <= date('Y-m-d'));
    $sundays=[];
    foreach ($allSundays as $listedSunday){
      $sundayDateTime=new DateTime($listedSunday);
      $sundays[]=$sundayDateTime -> format ('d-m-Y');
    }
    //pr($allSundays);
    $lastSunday=$allSundays[0];
    $allSaturdays=[];
    $saturday=date('2019-10-05');
    array_unshift($allSaturdays,$saturday);
    do {
      $saturday = date("Y-m-d",strtotime($saturday."+7 days"));
      array_unshift($allSaturdays,$saturday);
    }
    while ($saturday <= $lastSunday);
    //pr($allSaturdays);
    $saturdays=[];
    foreach ($allSaturdays as $listedSaturday){
      $saturdayDateTime=new DateTime($listedSaturday);
      $saturdays[]=$saturdayDateTime -> format ('d-m-Y');
    }
    $saturdayId=$sundayId;
    $this->set(compact('sundays','saturdays'));
    
    $this->set(compact('clientId'));
    $this->set(compact('sundayId','saturdayId'));
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
		
    $clients=$this->ThirdParty->getActiveClientList();
    $this->Set(compact('clients'));
    
    if ($enterpriseId > 0){
      $generalConditions=[
        'Invoice.enterprise_id'=>$enterpriseId,
        'Invoice.sub_total_price >'=>0,
        'Invoice.client_id'=>$clientId,
      ];
      if ($paymentModeId > 0){
        $generalConditions['Invoice.payment_mode_id']=$paymentModeId;
      }
      
      $currentSundayId=$saturdayId;
      $currentDate=$sundayDate=date( "Y-m-d",strtotime($allSundays[$currentSundayId]));
      $saturdayDate=date( "Y-m-d",strtotime($allSaturdays[$currentSundayId]));
      
      $invoiceConditions=$generalConditions;
      $invoiceConditions['invoice_date >=']=$sundayDate;
      $invoiceConditions['invoice_date <=']=$saturdayDate;
      //pr($invoiceConditions);
    
      $invoices=$this->Invoice->find('all',[
        'conditions'=>$invoiceConditions,
        'contain'=>[
          'CashReceiptInvoice'=>['CashReceipt',],
        ],
        'order'=>'Invoice.invoice_date'
      ]);
      
      $currentDate=date( "Y-m-d", strtotime( $currentDate."+1 days" ) );
      
      $invoiceTotalsArray['Client'][$clientId]['Total']['amount_cs']=0;
      $invoiceTotalsArray['Client'][$clientId]['Total']['saldo_cs']=0;
      $currentDate=$sundayDate;
         
      if (!empty($invoices)){
        $invoiceCounter=0;
        foreach ($invoices as $invoice){
          $invoiceDate=$invoice['Invoice']['invoice_date'];
          $invoiceClientId=$invoice['Invoice']['client_id'];
          
          $totalAmountInvoiceCS=$totalAmountInvoice=$invoice['Invoice']['sub_total_price'];
          if ($invoice['Invoice']['currency_id'] != CURRENCY_CS){
            $invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($invoiceDate,$invoice['Invoice']['currency_id']);
            $totalAmountInvoiceCS=$totalAmountInvoiice*$invoiceExchangeRate;
          }
          if ($invoice['Invoice']['bool_paid']){
            $totalPaidCS=$totalAmountInvoiceCS;  
          }
          else {
            $totalPaidCS=$totalPaid=0;
            if (!empty($invoice['CashReceiptInvoice'])){
              foreach ($invoice['CashReceiptInvoice'] as $cashReceiptInvoice){
                if ($cashReceiptInvoice['CashReceipt']['currency_id'] == CURRENCY_CS){
                  $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount'];  
                }
                else {
                  $cashReceiptDate=$cashReceiptInvoice['CashReceipt']['receipt_date'];  
                  $cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoice['CashReceipt']['currency_id']);
                  $totalPaidCS+=$cashReceiptInvoice['CashReceipt']['amount']*$cashReceiptExchangeRate;  
                }
              }
            }
          }
          $invoices[$invoiceCounter]['Invoice']['amount_cs']=$totalAmountInvoiceCS;
          $invoices[$invoiceCounter]['Invoice']['saldo_cs']=$totalAmountInvoiceCS-$totalPaidCS;
          
          $invoiceTotalsArray['Client'][$invoiceClientId]['Total']['amount_cs']+=$totalAmountInvoiceCS;
          $invoiceTotalsArray['Client'][$invoiceClientId]['Total']['saldo_cs']+=($totalAmountInvoiceCS-$totalPaidCS);
          $invoiceCounter++;
        }
      }
      //pr($invoiceTotalsArray);       
    }
    $this->set(compact('invoiceTotalsArray'));
    $this->set(compact('invoices'));
    
    $paymentModes=$this->PaymentMode->getCreditPaymentModeList();
    $this->set(compact('paymentModes'));
	}
	

  public function verClientesPorCobrar() {
		$this->loadModel('ExchangeRate');
    $this->loadModel('ThirdParty');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Invoice->recursive = 0;
		$this->ThirdParty->recursive=-1;
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $allSundays=[];
    $sunday=date('2019-09-29');
    do {
      array_unshift($allSundays,$sunday);
      $sunday = date("Y-m-d",strtotime($sunday."+7 days"));
    }
    while ($sunday <= date('Y-m-d'));
    $sundays=[];
    foreach ($allSundays as $listedSunday){
      $sundayDateTime=new DateTime($listedSunday);
      $sundays[]=$sundayDateTime -> format ('d-m-Y');
    }
    //pr($allSundays);
    $lastSunday=$allSundays[0];
    $allSaturdays=[];
    $saturday=date('2019-10-05');
    array_unshift($allSaturdays,$saturday);
    do {
      $saturday = date("Y-m-d",strtotime($saturday."+7 days"));
      array_unshift($allSaturdays,$saturday);
    }
    while ($saturday <= $lastSunday);
    //pr($allSaturdays);
    $saturdays=[];
    foreach ($allSaturdays as $listedSaturday){
      $saturdayDateTime=new DateTime($listedSaturday);
      $saturdays[]=$saturdayDateTime -> format ('d-m-Y');
    }
    $sundayId=0;
    $saturdayId=0;
    //$this->set(compact('allSundays','allSaturdays'));
    $this->set(compact('sundays','saturdays'));
    
		if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
    }
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
		
		$clients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.company_name','ThirdParty.id',
        'ThirdParty.first_name','ThirdParty.last_name',
        'ThirdParty.phone',
			],
			'conditions'=>[
				'bool_provider'=>false,
				'bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
		//pr($clients);
		
    for ($c=0;$c<count($clients);$c++){
      $invoiceConditions=[
        'Invoice.bool_annulled'=>false,
        'Invoice.bool_paid'=>false,
        'Invoice.client_id'=>$clients[$c]['ThirdParty']['id'],
        
        'Invoice.enterprise_id'=>$enterpriseId,
        'Invoice.invoice_date <='=>$allSaturdays[1],
      ];
      //pr($invoiceConditions);  
      
			$pendingInvoices=$this->Invoice->find('all',[
				'fields'=>[
					'Invoice.id','Invoice.invoice_code',
					'Invoice.total_price','Invoice.currency_id',
					'Invoice.invoice_date','Invoice.due_date',
					'Invoice.client_id',
					'Currency.abbreviation','Currency.id'
				],
				'conditions'=>$invoiceConditions,
				'order'=>'Invoice.invoice_date ASC',
			]);
			
			$totalPending=0;
			$pending=[
        '1'=>0,
        '2'=>0,
        '3'=>0,
        '4'=>0,
      ];
      for ($i=0;$i<count($pendingInvoices);$i++){
				$totalForInvoice=$pendingInvoices[$i]['Invoice']['total_price'];
				$totalForInvoiceCS=$totalForInvoice;
				if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
					$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$i]['Invoice']['invoice_date']);
					$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
				}
				// get the amount already paid for this invoice
				$invoice_paid_already_CS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$i]['Invoice']['id']),2);
				$pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
				//if ($pendingInvoices[$i]['Invoice']['client_id']==34){
				//	echo "invoice paid already cs for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." is ".$invoice_paid_already_CS."<br/>";
				//	echo "pending for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." without diferencia cambiaria is ".$pendingForInvoice."<br/>";
				//}
				if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
					$this->loadModel('CashReceiptInvoice');
					$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
						'conditions'=>[
							'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$i]['Invoice']['id'],
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
					$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$i]['Invoice']['invoice_date']);
					// add the diferencia cambiaria on the total
					$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
          $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
					$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
					$pendingForInvoice+=$differenciaCambiariaTotal;
					// add the diferencia cambiaria on the cashreceipts
					if (!empty($cashReceiptInvoices)){
						for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
							
							$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
							$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
							$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
							$pendingForInvoice-=$differenciaCambiariaPaid;
						}
					}
				}
				
				//if ($pendingInvoices[$i]['Invoice']['client_id']==34){
					//echo "pending for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." with diferencia cambiaria is ".$pendingForInvoice."<br/>";
				//}
				$totalPending+=$pendingForInvoice;
				//$invoiceDate=new DateTime($pendingInvoices[$i]['Invoice']['invoice_date']);
        $invoiceDate=$pendingInvoices[$i]['Invoice']['invoice_date'];
        //echo 'invoiceDate is '.$invoiceDate.'<br/>';
				//$dueDate= new DateTime($pendingInvoices[$i]['Invoice']['due_date']);
        if ($invoiceDate >= $allSundays[1]){
          $pending[1]+=$pendingForInvoice;
        }
        elseif ($invoiceDate >= $allSundays[2]){
          $pending[2]+=$pendingForInvoice;
        }
        elseif ($invoiceDate >= $allSundays[3]){
          $pending[3]+=$pendingForInvoice;
        }
        else{
          $pending[4]+=$pendingForInvoice;
        }
				//$nowDate= new DateTime();
				//$daysLate=$nowDate->diff($invoiceDate);
				//echo "factura ".$pendingInvoices[$i]['Invoice']['invoice_code']." ".$daysLate->days."<br/>";
				//if ($daysLate->days<31){
				//	$pendingUnder30+=$pendingForInvoice;
				//}
				//else if ($daysLate->days<46){
				//	$pendingUnder45+=$pendingForInvoice;
				//}
				//else if ($daysLate->days<61){
				//	$pendingUnder60+=$pendingForInvoice;
				//}
				//else{
				//	$pendingOver60+=$pendingForInvoice;
				//}
			}
      //pr($pending);
			$clients[$c]['saldo']=$totalPending;
			//$clients[$c]['pendingUnder30']=$pendingUnder30;
			//$clients[$c]['pendingUnder45']=$pendingUnder45;
			//$clients[$c]['pendingUnder60']=$pendingUnder60;
			//$clients[$c]['pendingOver60']=$pendingOver60;
      $clients[$c]['pending']=$pending;
			//$clients[$c]['historicalCredit']=$this->Invoice->getHistoricalCreditForClient($clients[$c]['ThirdParty']['id']);
		}
		
		$this->set(compact('clients'));
	}
	
	public function guardarClientesPorCobrar($fileName) {
		$exportData=$_SESSION['clientesPorCobrar'];
		$this->set(compact('exportData','fileName'));
	}
	
	public function verHistorialPagos() {
    $this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
		$this->loadModel('CashReceiptInvoice');
		
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->Invoice->recursive = -1;
		$this->ThirdParty->recursive=-1;
		$this->CashReceiptInvoice->recursive=-1;
		
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $client_id=0;
		
     if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
      
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$client_id=$this->request->data['Report']['client_id'];
		}
		//else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
		//	$startDate=$_SESSION['startDate'];
		//	$endDate=$_SESSION['endDate'];
		//	$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		//}
		else {
			$startDate = date("Y-m-d",strtotime(date("Y-m-01")."-3 months"));
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		//$_SESSION['startDate']=$startDate;
		//$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate','client_id'));
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
		$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				
		$conditions=[
			'bool_provider'=>false,
			'bool_active'=>true,
		];
		if ($client_id>0){
			$conditions[]=['ThirdParty.id'=>$client_id];
		}
		$selectedClients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.company_name','ThirdParty.id',
			],
			'conditions'=>$conditions,
		]);
		//pr($clients);
		
		for ($c=0;$c<count($selectedClients);$c++){
      $invoiceConditions=[
        'Invoice.bool_annulled'=>false,
        'Invoice.client_id'=>$selectedClients[$c]['ThirdParty']['id'],
        'Invoice.invoice_date >='=>$startDate,
        'Invoice.invoice_date <'=>$endDatePlusOne,
        'Invoice.enterprise_id'=>$enterpriseId,
      ];
			$invoices=$this->Invoice->find('all',[
				'fields'=>[
					'Invoice.id','Invoice.invoice_code',
					'Invoice.order_id',
					'Invoice.total_price','Invoice.currency_id',
					'Invoice.invoice_date','Invoice.due_date',
					'Invoice.bool_credit','Invoice.bool_paid',
					'Invoice.sub_total_price','Invoice.IVA_price','Invoice.total_price',
				],
				'conditions'=>$invoiceConditions,
				'contain'=>[
					'Currency'=>[
						'fields'=>[
							'Currency.abbreviation','Currency.id',
						],
					],
				],
				'order'=>'Invoice.invoice_date ASC',
			]);
			if (!empty($invoices)){
				for ($i=0;$i<count($invoices);$i++){
					if ($invoices[$i]['Invoice']['bool_credit']){
						$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
							'conditions'=>[
								'CashReceiptInvoice.invoice_id'=>$invoices[$i]['Invoice']['id'],
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
						$invoices[$i]['cashreceiptpayments']=$cashReceiptInvoices;
						$totalForInvoice=$invoices[$i]['Invoice']['total_price'];
						$totalForInvoiceCS=$totalForInvoice;
						if ($invoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
							$invoiceExchangeRate=
							$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($invoices[$i]['Invoice']['invoice_date']);
							$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
						}
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "total price in C$ for invoice 968 is ".$totalForInvoiceCS."!<br/>";
						//}
						// get the amount already paid for this invoice
						$invoice_paid_already_CS=round($this->Invoice->getAmountPaidAlreadyCS($invoices[$i]['Invoice']['id']),2);
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "total payment in C$ for invoice 968 is ".$invoice_paid_already_CS."!<br/>";
						//}
						
						// this is the pending amount in C$ without taking into account the diff camb
						$pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
						if ($invoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
							$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($invoices[$i]['Invoice']['invoice_date']);
							// add the diferencia cambiaria on the total
							$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				
              $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
							$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
							$pendingForInvoice+=$differenciaCambiariaTotal;
							// add the diferencia cambiaria on the cashreceipts
							if (!empty($cashReceiptInvoices)){
								for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
									$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
									$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
									//if ($invoices[$i]['Invoice']['id']==159){
									//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
									//	//pr($cashReceiptInvoices);
									//}
									$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
									$pendingForInvoice-=$differenciaCambiariaPaid;
								}
							}
						}
						
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "cashreceipts for invoice 968!<br/>";
						//	pr($cashReceiptInvoices);
						//}
						
						if ($pendingForInvoice<0){
							$pendingForInvoice=0;
						}
						$invoices[$i]['Invoice']['pendingCS']=$pendingForInvoice;
						
					}
					else {
						$invoices[$i]['Invoice']['pendingCS']=0;
					}
				}
			}
			$selectedClients[$c]['invoices']=$invoices;
			
		}
		$this->set(compact('selectedClients'));
		$clients=$this->ThirdParty->find('list',[
			'conditions'=>[
				'bool_provider'=>false,
				'bool_active'=>true,
			],
		]);
		$this->set(compact('clients'));
	}
	
	public function guardarHistorialPagos($fileName) {
		$exportData=$_SESSION['historialPagos'];
		$this->set(compact('exportData','fileName'));
	}
	
	public function verFacturasPorCobrar($client_id=0) {
    $this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
		
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
		$this->Invoice->recursive = 0;
    $this->ThirdParty->recursive=-1;
		
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
    }
		if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
    }
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    if ($client_id==0){
      //pr($this->Auth->User());
      $client_id=$this->Auth->User('client_id');
    }
    
		$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
		
		$client=$this->ThirdParty->read(null,$client_id);
		
    $invoiceConditions=[
      'Invoice.bool_annulled'=>false,
      'Invoice.bool_paid'=>false,
      'Invoice.client_id'=>$client_id,
      
      'Invoice.enterprise_id'=>$enterpriseId,
    ];
    
		$pendingInvoices=$this->Invoice->find('all',[
			'fields'=>[
				'Invoice.id','Invoice.invoice_code',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			],
			'conditions'=>$invoiceConditions,
			'order'=>'Invoice.invoice_date ASC',
		]);
		
		for ($c=0;$c<count($pendingInvoices);$c++){
			$totalForInvoice=$pendingInvoices[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$c]['Invoice']['invoice_date']);
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$c]['Invoice']['id']),2);		
			$pendingInvoices[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id'],
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
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$c]['Invoice']['invoice_date']);
				// add the diferencia cambiaria on the total
				$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoiceCS+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
						//	//pr($cashReceiptInvoices);
						//}
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoiceCS-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoices[$c]['Invoice']['pendingCS']=$pendingForInvoiceCS;
			//echo "total for invoice in C$ is ".$totalForInvoiceCS."<br/>";
			//echo "paid for invoice in C$ is ".$paidForInvoiceCS."<br/>";
			//echo "pending for invoice in C$ is ".($totalForInvoiceCS-$paidForInvoiceCS)."<br/>";
			//echo "pending registered for invoice in C$ is ".$pendingInvoices[$c]['Invoice']['pendingCS']."<br/>";
		}
		
		$this->set(compact('pendingInvoices','client','exchangeRateCurrent'));
	}
	
	public function guardarFacturasPorCobrar($fileName) {
		$exportData=$_SESSION['facturasPorCobrar'];
		$this->set(compact('exportData','fileName'));
	}
	
  public function verCuentasPorPagar($clientId=0) {
		$this->Invoice->recursive = 0;
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    if ($clientId==0){
      //pr($this->Auth->User());
      $clientId=$this->Auth->User('client_id');
    }
    
    if ($this->request->is('post')) {
			$clientId=$this->request->data['Report']['client_id'];
		}
		
    $this->set(compact('clientId'));
    //echo "client id is ".$clientId."<br/>";
		$this->loadModel('ExchangeRate');
		$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
    
		$client=$this->ThirdParty->find('first',[
      'conditions'=>['ThirdParty.id'=>$clientId],
    ]);
		//pr($client);
		$pendingInvoices=$this->Invoice->find('all',[
			'fields'=>[
				'Invoice.id','Invoice.invoice_code',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			],
			'conditions'=>[
				'Invoice.bool_annulled'=>false,
				'Invoice.bool_paid'=>false,
				'Invoice.client_id'=>$clientId,
			],
			'order'=>'Invoice.invoice_date ASC',
		]);
		
		for ($c=0;$c<count($pendingInvoices);$c++){
			$totalForInvoice=$pendingInvoices[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$c]['Invoice']['invoice_date']);
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$c]['Invoice']['id']),2);		
			$pendingInvoices[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id'],
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
				
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoices[$c]['Invoice']['invoice_date']);
				// add the diferencia cambiaria on the total
				$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoiceCS+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$cashReceiptExchangeRate=
						$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
						//	//pr($cashReceiptInvoices);
						//}
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoiceCS-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoices[$c]['Invoice']['pendingCS']=$pendingForInvoiceCS;
			//echo "total for invoice in C$ is ".$totalForInvoiceCS."<br/>";
			//echo "paid for invoice in C$ is ".$paidForInvoiceCS."<br/>";
			//echo "pending for invoice in C$ is ".($totalForInvoiceCS-$paidForInvoiceCS)."<br/>";
			//echo "pending registered for invoice in C$ is ".$pendingInvoices[$c]['Invoice']['pendingCS']."<br/>";
		}
		
		$this->set(compact('pendingInvoices','client','exchangeRateCurrent'));
    
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
	}
	
  public function guardarCuentasPorPagar() {
		$exportData=$_SESSION['cuentasPorPagar'];
		$this->set(compact('exportData'));
	}
  
	public function verCobrosSemana() {
		$this->Invoice->recursive = 0;
		
		$this->loadModel('ExchangeRate');
		$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
		$this->set(compact('exchangeRateNow'));
		
		$finalDateThisWeek=date("Y-m-d",strtotime(date("Y-m-d")."+7 days"));
		
		$pendingInvoicesThisWeek=$this->Invoice->find('all',[
			'fields'=>[
				'Invoice.id','Invoice.invoice_code','Invoice.client_id',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			],
			'conditions'=>[
				'Invoice.bool_annulled'=>false,
				'Invoice.bool_paid'=>false,
				'Invoice.due_date >='=>date("Y-m-d"),
				'Invoice.due_date <='=>$finalDateThisWeek,
				
			],
			'order'=>'Client.company_name ASC, Invoice.due_date ASC',
		]);
		//pr($pendingInvoicesThisWeek);
		
		for ($c=0;$c<count($pendingInvoicesThisWeek);$c++){
			$totalForInvoice=$pendingInvoicesThisWeek[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoicesThisWeek[$c]['Invoice']['currency_id']==CURRENCY_USD){
				
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoicesThisWeek[$c]['Invoice']['invoice_date']);
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoicesThisWeek[$c]['Invoice']['id']),2);		
			$pendingInvoicesThisWeek[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoice=($totalForInvoiceCS-$paidForInvoiceCS);
						
			if ($pendingInvoicesThisWeek[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$pendingInvoicesThisWeek[$c]['Invoice']['id'],
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
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoicesThisWeek[$c]['Invoice']['invoice_date']);
				// add the diferencia cambiaria on the total
				$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoice+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						
						$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoice-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoicesThisWeek[$c]['Invoice']['pendingCS']=$pendingForInvoice;
		}
		$this->set(compact('pendingInvoicesThisWeek'));
		
		$pendingInvoicesEarlier=$this->Invoice->find('all',[
			'fields'=>[
				'Invoice.id','Invoice.invoice_code','Invoice.client_id',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			],
			'conditions'=>[
				'Invoice.bool_annulled'=>false,
				'Invoice.bool_paid'=>false,
				'Invoice.due_date <'=>date("Y-m-d"),
			],
			'order'=>'Client.company_name ASC, Invoice.due_date DESC',
		]);
		//pr($pendingInvoicesEarlier);
		
		for ($c=0;$c<count($pendingInvoicesEarlier);$c++){
			$totalForInvoice=$pendingInvoicesEarlier[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoicesEarlier[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$invoiceExchangeRate=
				//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
				//	pr($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				//}
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
					//echo "exchange rate for invoice 893 on 15 july is ".$exchangeRateInvoiceDate."!<br/>";
				//}
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
				//echo "total price in C$ for invoice 893 is ".$totalForInvoiceCS."!<br/>";
			//}
						
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoicesEarlier[$c]['Invoice']['id']),2);		
			//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
			//	echo "total payment in C$ for invoice 893 is ".$paidForInvoiceCS."!<br/>";
			//}
			$pendingInvoicesEarlier[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoice=($totalForInvoiceCS-$paidForInvoiceCS);
						
			if ($pendingInvoicesEarlier[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$pendingInvoicesEarlier[$c]['Invoice']['id'],
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
				$exchangeRateInvoiceDate=$this->ExchangeRate->getApplicableExchangeRateValue($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				// add the diferencia cambiaria on the total
				$exchangeRateNow=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoice+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						
						$exchangeRateCashReceiptDate=$this->ExchangeRate->getApplicableExchangeRateValue($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoice-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoicesEarlier[$c]['Invoice']['pendingCS']=$pendingForInvoice;
		}
		$this->set(compact('pendingInvoicesEarlier'));
	}
	
}
