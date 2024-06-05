<div class="cashReceiptPaymentReceipts index">
<?php 
	echo "<h2>".__('Cash Receipt Payment Receipts')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Cash Receipt Payment Receipt'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Payment Receipts'), array('controller' => 'payment_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Receipt'), array('controller' => 'payment_receipts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
<div>
<?php
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('cash_receipt_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_receipt_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('amount')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('increment')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('discount')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('erdiff')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_retention')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_credit_CS')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_increment_CS')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_discount_CS')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_erdiff_CS')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('cash_receipt_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_receipt_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('amount')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('increment')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('discount')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('erdiff')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_retention')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_credit_CS')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_increment_CS')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_discount_CS')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_erdiff_CS')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	foreach ($cashReceiptPaymentReceipts as $cashReceiptPaymentReceipt){ 
		$pageRow=""		$pageRow=""		$pageRow.="<td>".$this->Html->link($cashReceiptPaymentReceipt['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $cashReceiptPaymentReceipt['CashReceipt']['id']))."</td>";
		$pageRow=""		$pageRow.="<td>".$this->Html->link($cashReceiptPaymentReceipt['PaymentReceipt']['id'], array('controller' => 'payment_receipts', 'action' => 'view', $cashReceiptPaymentReceipt['PaymentReceipt']['id']))."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['amount'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['increment'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['discount'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['erdiff'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_retention'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_credit_CS'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_increment_CS'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_discount_CS'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_erdiff_CS'])."</td>";
		$pageRow=""		$pageRow.="<td>".$this->Html->link($cashReceiptPaymentReceipt['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $cashReceiptPaymentReceipt['Currency']['id']))."</td>";
		$pageRow=""		$pageRow=""
			$excelBody.="<tr>".$pageRow."</tr>";

			$pageRow.="<td class='actions'>";
				$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']));
				$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']));
				//$pageRow.=->postLink(__('Delete'), array('action' => 'delete', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']));
			$pageRow.="</td>";

		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageTotalRow="";
	$pageTotalRow.="<tr class=\'totalrow\'>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
	$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});

	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#ReportStartdateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(previousMonth);
		$('#ReportStartdateYear').val(previousYear);
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#ReportEnddateDay').val(daysInPreviousMonth);
		$('#ReportEnddateMonth').val(previousMonth);
		$('#ReportEnddateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#ReportStartdateYear').val());
		if (nextMonth==0){
			nextMonth=12;
		}
		if (nextMonth==1){
			nextYear+=1;
		}
		if (nextMonth<10){
			nextMonth="0"+nextMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(nextMonth);
		$('#ReportStartdateYear').val(nextYear);
		var daysInNextMonth=daysInMonth(nextMonth,nextYear);
		$('#ReportEnddateDay').val(daysInNextMonth);
		$('#ReportEnddateMonth').val(nextMonth);
		$('#ReportEnddateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
</script>