<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$output="";
	$output.="<div class='cheques view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$receiptCode=$cashReceipt['CashReceipt']['receipt_code'];
	if ($cashReceipt['CashReceipt']['bool_annulled']){
		$receiptCode.=" (Anulado)";
	}
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		$output.="<div class='centered big bold'>Recibo de Caja (Factura de Crédito) # ".$receiptCode."</div>";
	}
	else if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
		$output.="<div class='centered big bold'>Recibo de Caja (Otros Ingresos) # ".$receiptCode."</div>";
	}
	
	$receiptDate=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div>A nombre de :<span class='underline'>".$cashReceipt['CashReceipt']['received_from']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$receiptDate->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Monto: C$<span class='underline'>".number_format($cashReceipt['CashReceipt']['amount'],2,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Caja: <span class='underline'>".$cashReceipt['CashboxAccountingCode']['fullname']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		
		
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Cuenta HABER: <span class='underline'>".$cashReceipt['CreditAccountingCode']['fullname']."</span></div>";
				$output.="</td>";
			$output.="</tr>";	
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Recibido de: <span class='underline'>".$cashReceipt['CashReceipt']['received_from']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Cliente: <span class='underline'>".$cashReceipt['Client']['company_name']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Concepto: <span class='underline'>".$cashReceipt['CashReceipt']['concept']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		if (!empty($cashReceipt['CashReceipt']['observation'])){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Observación: <span class='underline'>".$cashReceipt['CashReceipt']['observation']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
	$output.="</table>";
	
	$output.="<div class='related'>";
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		$output.="<h3>Pagos de Factura en este Recibo de Caja</h3>";
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
        $invoiceTableRows.='<td>'.$invoiceForCashReceipt["Invoice"]["invoice_code"].'</td>';
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
    $invoiceTable='<table>'.$invoiceTableHead.$invoiceTableBody.'</table>';
    $output.=$invoiceTable;
	}

	$output.="</div>"; 

	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	