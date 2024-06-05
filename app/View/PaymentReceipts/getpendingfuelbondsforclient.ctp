<?php
	//echo 'bool retention is '.$boolRetention.'<br/>';
	if (!empty($fuelBondsForClient)){
		$tableHead='';
    $tableHead.='<thead>';
      $tableHead.='<tr>';
        $tableHead.='<th class="hidden">'.__("Payment date").'</th>';
        $tableHead.='<th class="hidden">'.__("Currency").'</th>';
        $tableHead.='<th class="hidden">'.__("Exchange rate").'</th>';
        $tableHead.='<th class="hidden">'.__("difference in rates").'</th>';
        $tableHead.='<th class="hidden">'.__("retention_receipt_currency").'</th>';
        $tableHead.='<th class="hidden">'.__("diferencia_cambiaria_pagado").'</td>';

        $tableHead.='<th>'.__("Emisión").'</th>';
        //$tableHead.='<th>'.__("Bono").'</th>';
        $tableHead.='<th>'.__("Total Bono").'</th>';
        $tableHead.='<th>'.__("Abonado").'</th>';
        $tableHead.='<th>'.__("Saldo").'</th>';

        $tableHead.='<th>'.__("Incr").'</th>';
        $tableHead.='<th>'.__("Desc").'</th>';
        $tableHead.='<th>'.__("Dif Camb").'</th>';
        $tableHead.='<th class="retention'.($boolRetention?'':' hidden').'">'.__("Ret").'</th>';
        $tableHead.='<th>'.__("A pagar").'</th>';

        $tableHead.='<th class="separator"></th>';

        $tableHead.='<th>'.__("Abono Efectivo").'</th>';
        $tableHead.='<th class="retention'.($boolRetention?'':' hidden').'">'.__("Abono Ret").'</th>';
        
        $tableHead.='<th>'.__("Pago Crédito C$").'</th>';
        $tableHead.='<th>'.__("Pago Inc C$").'</th>';
        $tableHead.='<th>'.__("Pago Desc C$").'</th>';
        $tableHead.='<th>'.__("Pago Dif C$").'</th>';
        //$tableHead.='<th>'.__("Vencimiento").'</th>';
      $tableHead.='</tr>';
    $tableHead.='</thead>';
    
    $i=0;
    
    $totalPaymentReceipt=0;
    $totalPaidAlready=0;
    $totalPending=0;
    $totalRetention=0;
    
    $totalIncrement=0;
    $totalDiscount=0;
    $totalRateDifference=0;
    
    $totalSaldo=0;
    if ($cashReceiptCurrencyId == CURRENCY_CS){
      $cashReceiptCurrencyAbbreviation="C$";
    }
    elseif ($cashReceiptCurrencyId == CURRENCY_USD){
      $cashReceiptCurrencyAbbreviation="US$";
    }
    $tableBodyRows='';
    foreach ($fuelBondsForClient as $fuelBondForClient){
      //pr($fuelBondForClient);
      $paymentDateTime=new DateTime($fuelBondForClient['PaymentReceipt']['payment_date']);
      //$dueDate=new DateTime($fuelBondForClient['PaymentReceipt']["due_date"]);
      
      $paymentReceiptCurrencyId=$fuelBondForClient['PaymentReceipt']["currency_id"];
        
      // calculate the pending amount in the cashreceipt currency
      $pendingCashReceiptCurrency=$fuelBondForClient['PaymentReceipt']["payment_amount"];
      if ($paymentReceiptCurrencyId!=$cashReceiptCurrencyId){
        if ($paymentReceiptCurrencyId==CURRENCY_CS){
          $pendingCashReceiptCurrency/=$exchangeRateCashReceipt;
        }
        elseif ($paymentReceiptCurrencyId==CURRENCY_USD){
          $pendingCashReceiptCurrency*=$exchangeRateCashReceipt;
        }
      }
      // right now the pending amount is the totalprice of the currency in the cashreceipt currency
      // now rest the amount already paid
      if ($cashReceiptCurrencyId==CURRENCY_CS){
        //$pendingCashReceiptCurrency-=$fuelBondForClient['PaymentReceipt']["paid_already_CS"];
        $pendingCashReceiptCurrency=$pendingCashReceiptCurrency-$fuelBondForClient['PaymentReceipt']["paid_already_CS"]-$fuelBondForClient['PaymentReceipt']["diferencia_cambiaria_pagado"];
      }
      elseif ($cashReceiptCurrencyId==CURRENCY_USD){
        $pendingCashReceiptCurrency=$pendingCashReceiptCurrency-($fuelBondForClient['PaymentReceipt']["paid_already_CS"]+$fuelBondForClient['PaymentReceipt']["diferencia_cambiaria_pagado"])/$exchangeRateCashReceipt;
      }
      
      if (abs($pendingCashReceiptCurrency)<0.01){
        $pendingCashReceiptCurrency=0;
      }
      else {
        $pendingCashReceiptCurrency=round($pendingCashReceiptCurrency,2);
      }
      
      $retentionCashReceiptCurrency=$fuelBondForClient['PaymentReceipt']["retention"];
      if ($paymentReceiptCurrencyId!=$cashReceiptCurrencyId){
        if ($paymentReceiptCurrencyId==CURRENCY_CS){
          $retentionCashReceiptCurrency/=$exchangeRateCashReceipt;
        }
        elseif ($paymentReceiptCurrencyId==CURRENCY_USD){
          $retentionCashReceiptCurrency*=$exchangeRateCashReceipt;
        }
      }
      
      if ($boolRetention){
        if ($pendingCashReceiptCurrency>=$retentionCashReceiptCurrency){
          $pendingCashReceiptCurrency-=$retentionCashReceiptCurrency;
        }
        else {
          $retentionCashReceiptCurrency-=$pendingCashReceiptCurrency;
        }
      }
      $pendingCashReceiptCurrency=round($pendingCashReceiptCurrency,2);
      $retentionCashReceiptCurrency=round($retentionCashReceiptCurrency,2);
      

      $tableBodyRow='';
      $tableBodyRow.='<tr id="'.$fuelBondForClient['PaymentReceipt']["id"].'">';
        $tableBodyRow.='<td class="paymentreceiptid hidden">'.$this->Form->input("PaymentReceipt.".$i.".payment_receipt_id",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["id"],"type"=>"text"]).'</td>';
        $tableBodyRow.='<td class="paymentreceiptcurrency hidden">'.$this->Form->input("PaymentReceipt.".$i.".currency_id",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["currency_id"],"type"=>"text"]).'</td>';
        $tableBodyRow.='<td class="paymentreceiptexchangerate hidden">'.$this->Form->input("PaymentReceipt.".$i.".receiptexchangerate",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["payment_receipt_exchange_rate"]]).'</td>';
        $tableBodyRow.='<td class="differenceexchangerates hidden">'.$this->Form->input("PaymentReceipt.".$i.".differenceexchangerate",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["difference_exchange_rates"]]).'</td>';
        $tableBodyRow.='<td class="retentionreceiptcurrency hidden">'.$this->Form->input("PaymentReceipt.".$i.".retentionreceiptcurrency",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["retention"]]).'</td>';
        $tableBodyRow.='<td class="diferenciacambiariapagado hidden">'.$this->Form->input("PaymentReceipt.".$i.".diferenciacambiariapagado",["label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["diferencia_cambiaria_pagado"]]).'</td>';
        $tableBodyRow.='<td class="paymentreceiptdate">'.$paymentDateTime->format("d-m-Y").'</td>';
        //$tableBodyRow.='<td class="paymentreceiptcode">'.$this->Html->Link($fuelBondForClient['PaymentReceipt']["payment_receipt_code"],["controller"=>"orders","action"=>"verVenta",$fuelBondForClient['PaymentReceipt']["order_id"]]].'</td>';
        $tableBodyRow.='<td class="totalprice amount"><span class="currencyleft">'.$fuelBondForClient["Currency"]["abbreviation"].' </span><span class="amount right">'.$fuelBondForClient['PaymentReceipt']["payment_amount"].'</span></td>';
        $tableBodyRow.='<td class="paidalready amount"><span class="currencyleft">C$ </span><span class="amount right">'.$fuelBondForClient['PaymentReceipt']["paid_already_CS"].'</span></td>';
        $tableBodyRow.='<td class="pending amount"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="amount right">'.$pendingCashReceiptCurrency.'</span></td>';
        $tableBodyRow.='<td class="increment amount"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".increment",["type"=>"decimal","label"=>false,"default"=>"0"]).'</td>';
        $tableBodyRow.='<td class="discount amount"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".discount",["type"=>"decimal","label"=>false,"default"=>"0"]).'</td>';
        $tableBodyRow.='<td class="exchangeratedifference amount"><span class="currencyleft">C$ </span>'.$this->Form->input("PaymentReceipt.".$i.".exchangeratedifference",["type"=>"decimal","label"=>false,"default"=>$fuelBondForClient['PaymentReceipt']["exchange_rate_difference"],"readonly"=>"readonly","class"=>"nobox"]).'</td>';					
        $tableBodyRow.='<td class="retention amount'.($boolRetention?'':' hidden').'"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".retention",["type"=>"decimal","label"=>false,"default"=>$retentionCashReceiptCurrency]).'</td>';
        $tableBodyRow.='<td class="saldo amount"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".saldo",["type"=>"decimal","label"=>false,"readonly"=>"readonly","default"=>$pendingCashReceiptCurrency,"class"=>"nobox"]).'</td>';
        $tableBodyRow.='<td class="separator"></td>';
        $tableBodyRow.='<td class="payment amount"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".payment",["type"=>"decimal","label"=>false,"default"=>"0"]).'</td>';
        $tableBodyRow.='<td class="retentionpayment amount'.($boolRetention?'':' hidden').'"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span>'.$this->Form->input("PaymentReceipt.".$i.".retentionpayment",["type"=>"decimal","label"=>false,"default"=>"0"]).'</td>';
        $tableBodyRow.='<td class="creditpayment amount"><span class="currencyleft">C$ </span>'.$this->Form->input("PaymentReceipt.".$i.".creditpayment",["type"=>"decimal","label"=>false,"default"=>"0","readonly"=>"readonly","class"=>"nobox"]).'</td>';
        $tableBodyRow.='<td class="incpayment amount"><span class="currencyleft">C$ </span>'.$this->Form->input("PaymentReceipt.".$i.".incpayment",["type"=>"decimal","label"=>false,"default"=>"0","readonly"=>"readonly","class"=>"nobox"]).'</td>';
        $tableBodyRow.='<td  class="descpayment amount"><span class="currencyleft">C$ </span>'.$this->Form->input("PaymentReceipt.".$i.".descpayment",["type"=>"decimal","label"=>false,"default"=>"0","readonly"=>"readonly","class"=>"nobox"]).'</td>';
        $tableBodyRow.='<td  class="difpayment amount"><span class="currencyleft">C$ </span>'.$this->Form->input("PaymentReceipt.".$i.".difpayment",["type"=>"decimal","label"=>false,"default"=>"0","readonly"=>"readonly","class"=>"nobox"]).'</td>';
        //$tableBodyRow.='<td class="duedate">'.$dueDate->format("d-m-Y").'</td>';
      $tableBodyRow.='</tr>';
      $i++;
      
      $tableBodyRows.=$tableBodyRow;
    }
      
    $totalRow='';
    $totalRow.='<tr class="totalrow">';
      $totalRow.='<td class="hidden"></td>';
      $totalRow.='<td class="hidden"></td>';
      $totalRow.='<td class="hidden"></td>';
      $totalRow.='<td class="hidden"></td>';
      $totalRow.='<td class="hidden"></td>';
      $totalRow.='<td>Totales</td>';
      //$totalRow.='<td></td>';
      $totalRow.='<td class="totalprice amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span> <span class="totalamount amountright">'.$totalPaymentReceipt.'</span></td>';
      $totalRow.='<td class="paidalready amount right"><span class="currencyleft">C$ </span><span class="totalamount amountright">'.$totalPaidAlready.'</span></td>';
      $totalRow.='<td class="pending amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.'</span> <span class="totalamount amountright">'.$totalPending.'</span></td>';
      $totalRow.='<td class="increment amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">'.$totalIncrement.'</span></td>';
      $totalRow.='<td class="discount amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">'.$totalDiscount.'</span></td>';
      $totalRow.='<td class="exchangeratedifference amount right">C$ <span class="totalamount amountright">'.$totalRateDifference.'</td>';
      $totalRow.='<td class="retention amount right'.($boolRetention?'':' hidden').'"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">'.$totalRetention.'</span></td>';
      $totalRow.='<td class="saldo amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">'.$totalSaldo.'</span></td>';
      $totalRow.='<td class="separator"></td>';
      $totalRow.='<td class="payment amount right"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">0</span></td>';
      $totalRow.='<td class="retentionpayment amount right'.($boolRetention?'':' hidden').'"><span class="currency">'.$cashReceiptCurrencyAbbreviation.' </span><span class="totalamount amountright">0</span></td>';
      $totalRow.='<td class="creditpayment amount right"><span class="currencyleft">C$ </span><span class="totalamount amountright">0</span></td>';
      $totalRow.='<td class="incpayment amount right"><span class="currencyleft">C$ </span><span class="totalamount amountright">0</span></td>';
      $totalRow.='<td  class="descpayment amount right"><span class="currencyleft">C$ </span><span class="totalamount amountright">0</span></td>';
      $totalRow.='<td  class="difpayment amount right"><span class="currencyleft">C$ </span><span class="totalamount amountright">0</span></td>';
      //$totalRow.='<td></td>';
    $totalRow.='</tr>';			
		$tableBody='tbody'.$totalRow.$tableBodyRows.$totalRow.'</tbody>';
    $table='<table id="pendingFuelBonds">'.$tableHead.$tableBody.'</table>';
    echo $table;
	}
?>
<script>
	$(document).ajaxComplete(function() {
		
	});
</script>