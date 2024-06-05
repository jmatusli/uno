<div class="cashReceipts view">
<?php 
	echo '<h2>'.$cashReceipt['CashReceiptType']['description'].'</h2>';
	
  $receiptDateTime=new DateTime($cashReceipt["CashReceipt"]["receipt_date"]);
	$receiptCode=$cashReceipt["CashReceipt"]["receipt_code"];
	if ($cashReceipt["CashReceipt"]["bool_annulled"]){
    $receiptCode.=' (Anulado)';
  }
  
  $cashReceiptData='';
	$cashReceiptData.='<dl>';
		$cashReceiptData.='<dt>'.__("Receipt Date").'</dt>';
		$cashReceiptData.='<dd>'.$receiptDateTime->format("d-m-Y").'</dd>';
		$cashReceiptData.='<dt>'.__("Receipt Code").'</dt>';
		$cashReceiptData.='<dd>'.$receiptCode.'</dd>';
		$cashReceiptData.='<dt>'.__("Amount").'</dt>';
		$cashReceiptData.='<dd>'.$cashReceipt["Currency"]["abbreviation"].' '.$cashReceipt["CashReceipt"]["amount"].'</dd>';
		$cashReceiptData.='<dt>'.__("Cashbox Accounting Code").'</dt>';
		$cashReceiptData.='<dd>'.(empty($cashReceipt["CashboxAccountingCode"]["AccountingRegister"]["id"])?"-":$this->Html->link($cashReceipt["CashboxAccountingCode"]["fullname"], ["controller" => "accounting_codes", "action" => "view", $cashReceipt["CashboxAccountingCode"]["id"]])).'</dd>';
		$cashReceiptData.='<dt>'.__("Comprobante").'</dt>';
		$cashReceiptData.='<dd>'.(empty($cashReceipt["AccountingRegisterCashReceipt"][0]["AccountingRegister"]["id"])?"-":$this->Html->link($cashReceipt["AccountingRegisterCashReceipt"][0]["AccountingRegister"]["concept"], ["controller" => "accounting_registers", "action" => "view", $cashReceipt["AccountingRegisterCashReceipt"][0]["AccountingRegister"]["id"]])).'</dd>';
		if ($cashReceipt["CashReceiptType"]["id"]==CASH_RECEIPT_TYPE_OTHER){
			$cashReceiptData.='<dt>'.__("Credit Accounting Code").'</dt>';
			if (!empty($cashReceipt["CreditAccountingCode"]["id"])){
				$cashReceiptData.='<dd>'.$this->Html->link($cashReceipt["CreditAccountingCode"]["fullname"], ["controller" => "accounting_codes", "action" => "view", $cashReceipt["CreditAccountingCode"]["id"]]).'</dd>';
			}
			else {
				$cashReceiptData.='<dd>-</dd>';
			}
			$cashReceiptData.='<dt>'.__("Recibido de ").'</dt>';
			if (!empty($cashReceipt["CashReceipt"]["received_from"])){
				$cashReceiptData.='<dd>'.$cashReceipt["CashReceipt"]["received_from"].'</dd>';
			}
			else {
				$cashReceiptData.='<dd>-</dd>';
			}
		}
		if ($cashReceipt["CashReceiptType"]["id"]==CASH_RECEIPT_TYPE_CREDIT){
			$cashReceiptData.='<dt>'.__("Client").'</dt>';
			$cashReceiptData.='<dd>'.$this->Html->link($cashReceipt["Client"]["company_name"], ["controller" => "third_parties", "action" => "verCliente", $cashReceipt["Client"]["id"]]).'</dd>';
		}
		
		$cashReceiptData.='<dt>'.__("Concept").'</dt>';
		$cashReceiptData.='<dd>'.h($cashReceipt["CashReceipt"]["concept"]).'</dd>';
		if (!empty($cashReceipt["CashReceipt"]["observation"])){
			$cashReceiptData.='<dt>'.__("Observation").'</dt>';
			$cashReceiptData.='<dd>'.$cashReceipt["CashReceipt"]["observation"].'</dd>';
		}
		/*
		$cashReceiptData.='<dt>'.__("Bool Cash").'</dt>';
		$cashReceiptData.='<dd>'.h($cashReceipt["CashReceipt"]["bool_cash"]).'</dd>';
		$cashReceiptData.='<dt>'.__("Cheque Number").'</dt>';
		$cashReceiptData.='<dd>'.h($cashReceipt["CashReceipt"]["cheque_number"]).'</dd>';
		$cashReceiptData.='<dt>'.__("Cheque Bank").'</dt>';
		$cashReceiptData.='<dd>'.h($cashReceipt["CashReceipt"]["cheque_bank"]).'</dd>';
		*/
	$cashReceiptData.='</dl>';
  echo $cashReceiptData;
	echo '<div class="related">';
	if ($cashReceipt["CashReceiptType"]["id"]==CASH_RECEIPT_TYPE_CREDIT){
		echo '<h3>Pagos de Factura en este Recibo de Caja</h3>';
		//pr($cashReceipt);
		
    $invoiceTableHead='';
    $invoiceTableHead.='<thead>';
      $invoiceTableHead.='<tr>';
        $invoiceTableHead.='<th>Fecha</th>';
        $invoiceTableHead.='<th>Factura</th>';
        $invoiceTableHead.='<th class="centered">Monto Factura</th>';
        $invoiceTableHead.='<th class="centered">Monto Recibo</th>';
        //$invoiceTableHead.='<th>Monto Pagado en Retención</th>';
        $invoiceTableHead.='<th class="centered">Monto Total Abonado</th>';
        $invoiceTableHead.='<th class="centered">Saldo</th>';
      $invoiceTableHead.='</tr>';
    $invoiceTableHead.='</thead>';
    
    $totalInvoice=0;
    $totalPaymentReceipt=0;
    //$totalPaymentRetention=0;
    $totalPaymentCredit=0;
    
    $invoiceTableRows='';
    foreach ($invoicesForCashReceipt as $invoiceForCashReceipt){
      $invoiceDateTime= new DateTime($invoiceForCashReceipt["Invoice"]["invoice_date"]);
      
      $totalInvoice+=$invoiceForCashReceipt["Invoice"]["sub_total_price"];
      $totalPaymentReceipt+=$invoiceForCashReceipt["CashReceiptInvoice"]["payment"];
      //$totalPaymentRetention+=$invoiceForCashReceipt["CashReceiptInvoice"]["payment_retention"];
      $totalPaymentCredit+=$invoiceForCashReceipt["Invoice"]["paid_already_CS"];
        
      $invoiceTableRows.='<tr>';
        $invoiceTableRows.='<td>'.$invoiceDateTime->format('d-m-Y').'</td>';
        $invoiceTableRows.='<td>'.$this->Html->Link($invoiceForCashReceipt["Invoice"]["invoice_code"],["controller"=>"orders","action"=>"verVenta",$invoiceForCashReceipt["Invoice"]["id"]]).'</td>';
        $invoiceTableRows.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($invoiceForCashReceipt["Invoice"]["sub_total_price"],2,'.',',').'</span></td>';
        
        $invoiceTableRows.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($invoiceForCashReceipt["CashReceiptInvoice"]["payment"],2,'.',',').'</span></td>';
        //$invoiceTableRows.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($invoiceForCashReceipt["CashReceiptInvoice"]["payment_retention"],2,'.',',').'</span></td>';
        $invoiceTableRows.='<td><span class="currency">C$</span><span class="amountright">'.number_format($invoiceForCashReceipt["Invoice"]["paid_already_CS"],2,'.',',').'</span></td>';
        $invoiceTableRows.='<td><span class="currency">C$</span><span class="amountright">'.number_format($invoiceForCashReceipt["Invoice"]["sub_total_price"]-$invoiceForCashReceipt["Invoice"]["paid_already_CS"],2,'.',',').'</span></td>';
      $invoiceTableRows.='</tr>';
    }
    $invoiceTotalRow='';  
    $invoiceTotalRow.='<tr class="totalrow">';
      $invoiceTotalRow.='<td>Total</td>';
      $invoiceTotalRow.='<td></td>';
      $invoiceTotalRow.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($totalInvoice,2,'.',',').'</span></td>';
      $invoiceTotalRow.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($totalPaymentReceipt,2,'.',',').'</span></td>';
      //$invoiceTotalRow.='<td>'.$invoiceForCashReceipt["Currency"]["abbreviation"].' <span class="amountright">'.number_format($totalPaymentRetention,2,'.',',').'</span></td>';
      $invoiceTotalRow.='<td><span class="currency">C$</span><span class="amountright">'.number_format($totalPaymentCredit,2,'.',',').'</span></td>';
      $invoiceTotalRow.='<td><span class="currency">C$</span><span class="amountright">'.number_format($totalInvoice-$totalPaymentCredit,2,'.',',').'</span></td>';
    $invoiceTotalRow.='</tr>';
    $invoiceTableBody='<tbody>'.$invoiceTotalRow.$invoiceTableRows.$invoiceTotalRow.'</tbody>';
    $invoiceTable='<table id="facturas">'.$invoiceTableHead.$invoiceTableBody.'</table>';
    echo $invoiceTable;
	}
	echo '</div>';
?>
</div>
<div class="actions">
	<h3><?php echo __("Actions"); ?></h3>
	<ul>
	<?php
		$receiptCode=str_replace(" ","",$cashReceipt["CashReceipt"]["receipt_code"]);
		$receiptCode=str_replace("/","",$receiptCode);
		$fileName=$enterprises[$cashReceipt["CashReceipt"]["enterprise_id"]]."_Recibo_Caja_".$receiptCode;
		echo '<li>'.$this->Html->link(__("Guardar como pdf"), ["action" => "detallePdf","ext"=>"pdf",$cashReceipt["CashReceipt"]["id"],$fileName],['target'=>'_blank']).'</li>';
		echo '<br/>';
		if ($bool_edit_permission) { 
			echo '<li>'.$this->Html->link(__("Edit Cash Receipt"), ["action" => "editar", $cashReceipt["CashReceipt"]["id"]]).'</li>'; 
		}
		//if ($bool_delete_permission) { 
		//	echo '<li>'.$this->Form->postLink(__("Delete Cash Receipt"), ["action" => "delete", $cashReceipt["CashReceipt"]["id"]], [], __("Estä seguro que quiere eliminar recibo # %s?", $cashReceipt["CashReceipt"]["receipt_code"])).'</li>';
		//} 
		echo '<li>'.$this->Html->link(__("List Cash Receipts"), ["action" => "resumen"]).'</li>';
		echo '<br/>';
		if ($bool_add_permission) { 
			echo '<li>'.$this->Html->link("Nuevo Recibo de Caja (Factura de Crédito)",["action" => "crear",CASH_RECEIPT_TYPE_CREDIT]).'</li>';
			echo '<br/>';
			echo '<li>'.$this->Html->link(__("Nuevo Recibo de Caja (Otros Ingresos)"),["action" => "crear",CASH_RECEIPT_TYPE_OTHER]).'</li>';
			echo '<br/>';
		}
		if ($bool_client_index_permission) { 
			echo '<li>'.$this->Html->link(__("List Clients"), ["controller" => "third_parties", "action" => "indexClients"]).'</li>';
		}
		if ($bool_client_add_permission) { 
			echo '<li>'.$this->Html->link(__("New Client"), ["controller" => "third_parties", "action" => "addClient"]).'</li>';
		} 
	?>
	</ul>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="clear:left">
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Recibo'), ['action' => 'delete', $cashReceipt["CashReceipt"]["id"]], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar el recibo # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $cashReceipt["CashReceipt"]["receipt_code"]));
  }
?>
</div>