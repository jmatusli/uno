<script>
	function formatNumbers(){
		$("td.number span.amountcenter").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			else {
				var percentageValue=parseFloat($(this).text());
				$(this).text(100*percentageValue);
			}
			$(this).number(true,2,'.',',');
			$(this).append(" %");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>

<div class='invoices view fullwidth'>
<?php 
  $invoiceDateTime=new DateTime($invoice["Invoice"]["invoice_date"]);
  $dueDateTime=new DateTime($invoice["Invoice"]["due_date"]);
	echo '<h2>'.__("Invoice").' '.$invoice['Invoice']['invoice_code'].($invoice['Invoice']['bool_annulled']?" (Anulada)":"").'</h2>';
  
    
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-8">';
      //pr($invoice['Invoice']);
        echo '<dl class="dl50">';
          echo '<dt>'.__("Invoice Date").'</dt>';
          echo '<dd>'.$invoiceDateTime->format('d-m-Y').'</dd>';
          echo '<dt>'.__("Invoice Code").'</dt>';
          echo '<dd>'.h($invoice["Invoice"]["invoice_code"]).'</dd>';
          echo '<dt>'.__("Enterprise").'</dt>';
          echo '<dd>'.($boolEnterpriseDetailPermission?$this->Html->link($invoice["Enterprise"]["company_name"],["controller"=>"enterprises", "action"=>"detalle",$invoice["Enterprise"]["company_name"]]):$invoice["Enterprise"]["company_name"]).'</dd>';
          
          //echo '<dt>'.__("Order").'</dt>';
          //echo '<dd>'.($boolEnterpriseDetailPermission?$this->Html->link($invoice["Order"]["order_code"], ["controller" => "orders", "action" => "view", $invoice["Order"]["id"]]).'</dd>';
          echo '<dt>'.__("Shift").'</dt>';
          echo '<dd>'.($boolShiftDetailPermission?$this->Html->link($invoice["Shift"]["name"], ["controller" => "shifts", "action" => "view", $invoice["Shift"]["id"]]):$invoice["Shift"]["name"]).'</dd>';
          echo '<dt>'.__("Operator").'</dt>';
          echo '<dd>'.($boolOperatorDetailPermission?$this->Html->link($invoice["Operator"]["name"], ["controller" => "operators", "action" => "view", $invoice["Operator"]["id"]]):$invoice["Operator"]["name"]).'</dd>';
          //echo '<dt>'.__("Payment Receipt").'</dt>';
          //echo '<dd>'.$this->Html->link($invoice["PaymentReceipt"]["id"], ["controller" => "payment_receipts", "action" => "view", $invoice["PaymentReceipt"]["id"]]).'</dd>';
          echo '<dt>'.__("Payment Mode").'</dt>';
          echo '<dd>'.($boolPaymentModeDetailPermission?$this->Html->link($invoice["PaymentMode"]["name"], ["controller" => "payment_modes", "action" => "view", $invoice["PaymentMode"]["id"]]):$invoice["PaymentMode"]["name"]).'</dd>';
          echo '<dt>'.__("Client").'</dt>';
          echo '<dd>'.($boolClientDetailPermission?$this->Html->link($invoice["Client"]["company_name"], ["controller" => "third_parties", "action" => "view", $invoice["Client"]["id"]]):$invoice["Client"]["company_name"]).'</dd>';
          //echo '<dt>'.__("Currency").'</dt>';
          //echo '<dd>'.$this->Html->link($invoice["Currency"]["abbreviation"], ["controller" => "currencies", "action" => "view", $invoice["Currency"]["id"]]).'</dd>';
          echo '<dt>'.__("Registrado por").'</dt>';
          echo '<dd>'.h($invoice["CreatingUser"]["username"]).'</dd>';
          echo '<dt>'.__("Due Date").'</dt>';
          echo '<dd>'.$dueDateTime->format('d-m-Y').'</dd>';
          //echo '<dt>'.__("Bool Annulled").'</dt>';
          //echo '<dd>'.h($invoice["Invoice"]["bool_annulled"]).'</dd>';
          //echo '<dt>'.__("Bool Credit").'</dt>';
          //echo '<dd>'.h($invoice["Invoice"]["bool_credit"]).'</dd>';
          //echo '<dt>'.__("Cashbox Accounting Code").'</dt>';
          //echo '<dd>'.$this->Html->link($invoice["CashboxAccountingCode"]["fullname"], ["controller" => "accounting_codes", "action" => "view", $invoice["CashboxAccountingCode"]["id"])).'</dd>';
          //echo '<dt>'.__("Bool Retention").'</dt>';
          //echo '<dd>'.h($invoice["Invoice"]["bool_retention"]).'</dd>';
          //echo '<dt>'.__("Retention Amount").'</dt>';
          //echo '<dd>'.h($invoice["Invoice"]["retention_amount"]).'</dd>';
          //echo '<dt>'.__("Retention Number").'</dt>';
          //echo '<dd>'.h($invoice["Invoice"]["retention_number"]).'</dd>';
          echo '<dt>'.__("Bool Iva").'</dt>';
          echo '<dd>'.h($invoice["Invoice"]["bool_iva"]?"Sí":"No").'</dd>';
          echo '<dt>'.__("Precio Subtotal").'</dt>';
          echo '<dd><span class="currency">C$</span><span class="amountright">'.h($invoice["Invoice"]["sub_total_price"]).'</span></dd>';
          echo '<dt>'.__("Iva Price").'</dt>';
          echo '<dd><span class="currency">C$</span><span class="amountright">'.h($invoice["Invoice"]["iva_price"]).'</span></dd>';
          echo '<dt>'.__("Precio Total").'</dt>';
          echo '<dd><span class="currency">C$</span><span class="amountright">'.h($invoice["Invoice"]["total_price"]).'</span></dd>';
          echo '<dt>'.__("Estado Pago").'</dt>';
          echo '<dd>'.h($invoice["Invoice"]["bool_paid"]?"Pagado":"Pago pendiente").'</dd>';
          echo '<dt>'.__("Estado Depósito").'</dt>';
          echo '<dd>'.h($invoice["Invoice"]["bool_deposited"]?"Depositado":"Depósito pendiente").'</dd>';
        echo '</dl>';
      echo '</div>';  
      echo '<div class="col-sm-4">';
 
        echo '<h3>'.__("Actions").'</h3>';
        echo '<ul style="list-style:none;">';
          $fileName=$invoice['Enterprise']['company_name']."_Factura_".$invoiceDateTime->format('dmY');
          echo $this->Html->link('Pdf factura',['action'=>'detallePdf','ext'=>'pdf',$invoice['Invoice']['id'],$fileName],['class'=>'btn btn-primary','target'=>'_blank']);
          echo '<br/>';
          echo '<br/>';
          if($bool_edit_permission){
            echo '<li>'.$this->Html->link(__("Edit Invoice"), ["action" => "editar", $invoice["Invoice"]["id"]]).'</li>';
            echo '<br/>';
          }
          //if($bool_delete_permission){
          //  echo '<li>'.$this->Form->postLink('Eliminar Factura', ["action" => "delete", $invoice["Invoice"]["id"]], [], __("Está seguro que quiere eliminar completamente factura # %s?", $invoice["Invoice"]["invoice_code"])).'</li>';
            //echo '<br/>';
          //}
          echo '<li>'.$this->Html->link(__("List Invoices"), ["action" => "resumen"]).'</li>';
          if($bool_add_permission){
            echo '<li>'.$this->Html->link(__("New Invoice"), ["action" => "crear"]).'</li>';
          }
          echo '<br/>';
          
          if($boolClientIndexPermission){
            echo '<li>'.$this->Html->link(__("List Clients"), ["controller" => "third_parties", "action" => "resumenClientes"]).'</li>';
          }
          if($boolClientAddPermission){
            echo '<li>'.$this->Html->link(__("New Client"), ["controller" => "third_parties", "action" => "crearCliente"]).'</li>';
          }
        echo '</ul>';
      echo '</div>';
    echo '</div>';
  echo '</div>';
?> 
</div>
<div class='related'>
<?php 
	if (!empty($invoice["CashReceiptInvoice"])){
		
    $tableHead='';
    $tableHead.='<thead>';
      $tableHead.='<tr>';
        $tableHead.='<th>Fecha</th>';
        $tableHead.='<th># Recibo</th>';
        $tableHead.='<th>Monto</th>';
        $tableHead.='<th>Concepto</th>';
        $tableHead.='<th class="actions"></th>';
      $tableHead.='</tr>';
    $tableHead.='</thead>';
    
    $receiptTotal=0;
    $tableRows='';
		foreach ($invoice["CashReceiptInvoice"] as $cashReceiptInvoice){ 
      $cashReceipt=$cashReceiptInvoice['CashReceipt'];
      //pr($cashReceipt);
      $cashReceiptDateTime= new DateTime($cashReceipt['receipt_date']);
      $receiptTotal+=$cashReceipt['amount'];
			
      $tableRows.='<tr>';
				$tableRows.='<td>'.$cashReceiptDateTime->Format('d-m-Y').'</td>';
        $tableRows.='<td>'.$this->Html->Link($cashReceipt["receipt_code"],['controller'=>'cashReceipts','action'=>'detalle',$cashReceipt["id"]]).'</td>';
				$tableRows.='<td class="'.($cashReceipt['currency_id']  == CURRENCY_USD?'USDcurrency':'CScurrency').'"><span class="currency"></span><span class="amountright">'.$cashReceipt["amount"].'</span></td>';
				$tableRows.='<td>'.(empty($cashReceipt["concept"])?"-":$cashReceipt["concept"]).'</td>';
				//$tableRows.='<td class="actions">';
				//	$tableRows.=$this->Html->link(__("Edit"), ["controller" => "cash_receipt_invoices", "action" => "edit", $cashReceiptInvoice["id"]]);
				//$tableRows.='</td>';
			$tableRows.='</tr>';
		}
    $totalRow='';
    $totalRow.='<tr class="totalrow">';
      $totalRow.='<td>Total</td>';
      $totalRow.='<td></td>';
      $totalRow.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$receiptTotal.'</span></td>';
      $totalRow.='<td></td>';
    $totalRow.='</tr>';
    
    $cashReceiptTableBody='<tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
		$cashReceiptTable='<table id="recibos_caja">'.$tableHead.$cashReceiptTableBody.'</table>';
    
    echo '<h3>'.__("Recibos de Caja para esta Factura").'</h3>';
		echo $cashReceiptTable;
	}
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Factura'), ['action' => 'delete', $invoice['Invoice']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la factura # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $invoice["Invoice"]["invoice_code"]));
  }
?>
</div>