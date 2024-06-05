<?php
	if (!empty($invoicesForClient)){
    echo $this->Form->input('powerselector1',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
		echo "<table id='pendingInvoices'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th class='hidden'>".__('Invoice id')."</th>";
					echo "<th class='hidden'>".__('Invoice currency')."</th>";
					echo "<th class='hidden'>".__('invoice exchange rate')."</th>";
					echo "<th class='hidden'>".__('difference in rates')."</th>";
					echo "<th class='hidden'>".__('retention_invoice_currency')."</th>";
					echo "<th class='hidden'>".__('diferencia_cambiaria_pagado')."</td>";

          echo "<th></th>";
					echo "<th>".__('Emisión')."</th>";
					echo "<th>".__('Factura')."</th>";
					echo "<th>".__('Total Factura')."</th>";
					echo "<th>".__('Abonado')."</th>";
					echo "<th>".__('Saldo')."</th>";

					echo "<th>".__('Incr')."</th>";
					echo "<th>".__('Desc')."</th>";
					echo "<th>".__('Dif Camb')."</th>";
					//if ($boolRetention){
					//	echo "<th class='retention'>".__('Ret')."</th>";
					//}
					//else {
					//	echo "<th class='retention hidden'>".__('Ret')."</th>";
					//}
					echo "<th>".__('A pagar')."</th>";

					echo "<th class='separator'></th>";

					echo "<th>".__('Abono Efectivo')."</th>";
					//if ($boolRetention){
					//	echo "<th class='retention'>".__('Abono Ret')."</th>";
					//}
					//else {
					//	echo "<th class='retention hidden'>".__('Abono Ret')."</th>";
					//}
					echo "<th>".__('Pago Crédito C$')."</th>";
					echo "<th>".__('Pago Inc C$')."</th>";
					echo "<th>".__('Pago Desc C$')."</th>";
					echo "<th>".__('Pago Dif C$')."</th>";
					echo "<th>".__('Vencimiento')."</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i=0;
			
			$totalInvoice=0;
			$totalPaidAlready=0;
			$totalPending=0;
			//$totalRetention=0;
			
			$totalIncrement=0;
			$totalDiscount=0;
			$totalRateDifference=0;
			
			$totalSaldo=0;
			if ($cashReceiptCurrencyId==CURRENCY_CS){
				$cashReceiptCurrencyAbbreviation="C$";
			}
			elseif ($cashReceiptCurrencyId==CURRENCY_USD){
				$cashReceiptCurrencyAbbreviation="US$";
			}
      foreach ($invoicesForClient as $invoiceForClient){
				//pr($invoiceForClient);
				$invoiceDateTime=new DateTime($invoiceForClient['Invoice']['invoice_date']);
				$dueDateTime=new DateTime($invoiceForClient['Invoice']['due_date']);
				
				echo "<tr id='".$invoiceForClient['Invoice']['id']."'>";
					$invoiceCurrencyId=$invoiceForClient['Invoice']['currency_id'];
					
					// calculate the pending amount in the cashreceipt currency
					$pendingCashReceiptCurrency=$invoiceForClient['Invoice']['total_price'];
					if ($invoiceCurrencyId!=$cashReceiptCurrencyId){
						if ($invoiceCurrencyId==CURRENCY_CS){
							$pendingCashReceiptCurrency/=$exchangeRateCashReceipt;
						}
						elseif ($invoiceCurrencyId==CURRENCY_USD){
							$pendingCashReceiptCurrency*=$exchangeRateCashReceipt;
						}
					}
					// right now the pending amount is the totalprice of the currency in the cashreceipt currency
					// now rest the amount already paid
					if ($cashReceiptCurrencyId==CURRENCY_CS){
						//$pendingCashReceiptCurrency-=$invoiceForClient['Invoice']['paid_already_CS'];
						$pendingCashReceiptCurrency=$pendingCashReceiptCurrency-$invoiceForClient['Invoice']['paid_already_CS']-$invoiceForClient['Invoice']['diferencia_cambiaria_pagado'];
					}
					elseif ($cashReceiptCurrencyId==CURRENCY_USD){
						//echo "pending cash receipt currency is ".$pendingCashReceiptCurrency."<br/>";
						//echo "exchange rate cashreceipt is ".$exchangeRateCashReceipt."<br/>";
						//echo "diferencia cambiario en pagado es ".$invoiceForClient['Invoice']['diferencia_cambiaria_pagado']."<br/>";
						//$pendingCashReceiptCurrency-=$invoiceForClient['Invoice']['paid_already_CS']/$exchangeRateCashReceipt;
						$pendingCashReceiptCurrency=$pendingCashReceiptCurrency-($invoiceForClient['Invoice']['paid_already_CS']+$invoiceForClient['Invoice']['diferencia_cambiaria_pagado'])/$exchangeRateCashReceipt;
					}
					
					if (abs($pendingCashReceiptCurrency)<0.01){
						$pendingCashReceiptCurrency=0;
					}
					else {
						$pendingCashReceiptCurrency=round($pendingCashReceiptCurrency,2);
						//$pendingCashReceiptCurrency=ceil(100*$pendingCashReceiptCurrency)/100;
					}
					
					// calculate the retention in the cashreceipt currency
					//$retentionCashReceiptCurrency=$invoiceForClient['Invoice']['retention'];
					//if ($invoiceCurrencyId!=$cashReceiptCurrencyId){
					//	if ($invoiceCurrencyId==CURRENCY_CS){
					//		$retentionCashReceiptCurrency/=$exchangeRateCashReceipt;
					//	}
					//	elseif ($invoiceCurrencyId==CURRENCY_USD){
					//		$retentionCashReceiptCurrency*=$exchangeRateCashReceipt;
					//	}
					//}
					
          
					echo "<td class='invoiceid hidden'>".$this->Form->input('Invoice.'.$i.'.invoice_id',['label'=>false,'default'=>$invoiceForClient['Invoice']['id'],'type'=>'text'])."</td>";
					echo "<td class='invoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.currency_id',['label'=>false,'default'=>$invoiceForClient['Invoice']['currency_id'],'type'=>'text'])."</td>";
					echo "<td class='invoiceexchangerate hidden'>".$this->Form->input('Invoice.'.$i.'.invoiceexchangerate',['label'=>false,'default'=>$invoiceForClient['Invoice']['invoice_exchange_rate']])."</td>";
					echo "<td class='differenceexchangerates hidden'>".$this->Form->input('Invoice.'.$i.'.differenceexchangerate',['label'=>false,'default'=>$invoiceForClient['Invoice']['difference_exchange_rates']])."</td>";
					//echo "<td class='retentioninvoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.retentioninvoicecurrency',['label'=>false,'default'=>$invoiceForClient['Invoice']['retention']])."</td>";
					echo "<td class='diferenciacambiariapagado hidden'>".$this->Form->input('Invoice.'.$i.'.diferenciacambiariapagado',['label'=>false,'default'=>$invoiceForClient['Invoice']['diferencia_cambiaria_pagado']])."</td>";
					
          echo '<td class="selector">'.$this->Form->input('Invoice.'.$i.'.selector',['checked'=>false,'label'=>false]).'</td>';
          echo "<td class='saledate'>".$invoiceDateTime->format('d-m-Y')."</td>";
					echo "<td class='invoicecode'>".$this->Html->Link($invoiceForClient['Invoice']['invoice_code'],['controller'=>'invoices','action'=>'detalle',$invoiceForClient['Invoice']['id']])."</td>";
					echo "<td class='totalprice amount'><span class='currencyleft'>".$invoiceForClient['Currency']['abbreviation']." </span><span class='amount right'>".$invoiceForClient['Invoice']['total_price']."</span></td>";
					echo "<td class='paidalready amount'><span class='currencyleft'>C$ </span><span class='amount right'>".$invoiceForClient['Invoice']['paid_already_CS']."</span></td>";
					echo "<td class='pending amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='amount right'>".$pendingCashReceiptCurrency."</span></td>";
					
					echo "<td class='increment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.increment',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
					echo "<td class='discount amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.discount',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
					echo "<td class='exchangeratedifference amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.exchangeratedifference',['type'=>'decimal','label'=>false,'default'=>$invoiceForClient['Invoice']['exchange_rate_difference'],'readonly'=>'readonly','class'=>'nobox'])."</td>";					
					
					//if ($boolRetention){
					//	if ($pendingCashReceiptCurrency>=$retentionCashReceiptCurrency){
					//		$pendingCashReceiptCurrency-=$retentionCashReceiptCurrency;
					//	}
					//	else {
					//		$retentionCashReceiptCurrency-=$pendingCashReceiptCurrency;
					//	}
					//}
					$pendingCashReceiptCurrency=round($pendingCashReceiptCurrency,2);
					//$retentionCashReceiptCurrency=round($retentionCashReceiptCurrency,2);
					
					//if ($boolRetention){
					//	echo "<td class='retention amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',['type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
					//}
					//else {
					//	echo "<td class='retention amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',['type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
					//}
					echo "<td class='saldo amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.saldo',['type'=>'decimal','label'=>false,'readonly'=>'readonly','default'=>$pendingCashReceiptCurrency,'class'=>'nobox'])."</td>";
					
					echo "<td class='separator'></td>";

					echo "<td class='payment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.payment',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
					//if ($boolRetention){
					//	echo "<td class='retentionpayment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
					//}
					//else {
					//	echo "<td class='retentionpayment amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
					//}
					echo "<td class='creditpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.creditpayment',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'])."</td>";
					echo "<td class='incpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.incpayment',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'])."</td>";
					echo "<td  class='descpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.descpayment',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'])."</td>";
					echo "<td  class='difpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.difpayment',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'])."</td>";
					echo "<td class='duedate'>".$dueDateTime->format('d-m-Y')."</td>";
				echo "</tr>";
				$i++;
				
				if ($invoiceForClient['Currency']['id'] != $cashReceiptCurrencyId){
					if ($invoiceForClient['Currency']['id'] == CURRENCY_USD){
						$totalInvoice+=round($invoiceForClient['Invoice']['total_price']*$exchangeRateCashReceipt,2);
					}
					else {
						$totalInvoice+=round($invoiceForClient['Invoice']['total_price']/$exchangeRateCashReceipt,2);
					}
				}
				else {
					$totalInvoice+=$invoiceForClient['Invoice']['total_price'];
				}
				//echo "total invoice is ".$totalInvoice."<br/>";
				
				$totalPaidAlready+=$invoiceForClient['Invoice']['paid_already_CS'];
				$totalPending+=$pendingCashReceiptCurrency;
				$totalRateDifference+=$invoiceForClient['Invoice']['exchange_rate_difference'];
				//$totalRetention+=$retentionCashReceiptCurrency;
				$totalSaldo+=$pendingCashReceiptCurrency;
			}
				echo "<tr class='totalrow'>";
					echo "<td class='hidden'></td>";
					echo "<td class='hidden'></td>";
					echo "<td class='hidden'></td>";
					echo "<td class='hidden'></td>";
					//echo "<td class='hidden'></td>";
					echo "<td>Totales</td>";
					echo "<td></td>";
          echo "<td></td>";
					echo "<td class='totalprice amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span> <span class='totalamount amountright'>".$totalInvoice."</span></td>";
					echo "<td class='paidalready amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>".$totalPaidAlready."</span></td>";
					echo "<td class='pending amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation."</span> <span class='totalamount amountright'>".$totalPending."</span></td>";
					
					echo "<td class='increment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalIncrement."</span></td>";
					echo "<td class='discount amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalDiscount."</span></td>";
					echo "<td class='exchangeratedifference amount right'>C$ <span class='totalamount amountright'>".$totalRateDifference."</td>";
					//if ($boolRetention){
					//	echo "<td class='retention amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalRetention."</span></td>";
					//}
					//else {
					//	echo "<td class='retention amount right hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalRetention."</span></td>";
					//}
					echo "<td class='saldo amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalSaldo."</span></td>";
					
					echo "<td class='separator'></td>";
					
					echo "<td class='payment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
					//if ($boolRetention){
					//	echo "<td class='retentionpayment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
					//}
					//else {
					//	echo "<td class='retentionpayment amount right hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
					//}
					
					echo "<td class='creditpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
					echo "<td class='incpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
					echo "<td  class='descpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
					echo "<td  class='difpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
					
					echo "<td></td>";
				echo "</tr>";			
			echo "</tbody>";
		echo "</table>";
    
    echo $this->Form->input('powerselector2',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
	}
?>
<script>
	$(document).ajaxComplete(function() {
		
	});
</script>