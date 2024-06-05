<script>
	$('body').on('change','#CashReceiptReceiptDateDay',function(){	
		updateExchangeRate();
		calculateAllRows();
	});	
	$('body').on('change','#CashReceiptReceiptDateMonth',function(){	
		updateExchangeRate();
		calculateAllRows();
	});		
	$('body').on('change','#CashReceiptReceiptDateYear',function(){	
		updateExchangeRate();
		calculateAllRows();
	});			
	function updateExchangeRate(){
		var receiptday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#CashReceiptExchangeRate').val(exchangerate);
				updateExchangeRateDifferences();
				updatePending();
				//updateRetention();
			},
			error: function(e){
				$('#invoicesForClient').html(e.responseText);
				console.log(e);
			}
		});
	}
	function updateExchangeRateDifferences(){
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('td.differenceexchangerates input').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoiceexchangerate=invoicerow.find('td.invoiceexchangerate input').val();
			var exchangeratedifference=roundToFour(cashreceiptexchangerate-invoiceexchangerate);
			$(this).val(exchangeratedifference);
			var invoicecurrency=invoicerow.find('td.invoicecurrency input').val();
			if (invoicecurrency==2){
				var invoiceamount=invoicerow.find('td.totalprice span.amount').text();
				invoicerow.find('td.exchangeratedifference input').val(roundToTwo(invoiceamount*exchangeratedifference));
			}
		});
	}
	function updatePending(){
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('tr:not(.totalrow) td.pending span.amountright').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoicecurrencyid=invoicerow.find('td.invoicecurrency input').val();
			var pendingcashreceiptcurrency=invoicerow.find('td.totalprice span').text();
			if (cashreceiptcurrencyid!=invoicecurrencyid){
				if (invoicecurrencyid==1){
					pendingcashreceiptcurrency/=$cashreceiptexchangerate;
				}
				else if (invoicecurrencyid==2){
					pendingcashreceiptcurrency*=$cashreceiptexchangerate;
				}
			}
			var paidalreadyCS=invoicerow.find('td.paidalready span').text();
			var diferenciacambiariapagado=invoicerow.find('td.diferenciacambiariapagado span').text();
			if (cashreceiptcurrencyid==1){
				//pendingcashreceiptcurrency-=paidalreadyCS;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-paidalreadyCS-diferenciacambiariapagado;
			}
			else if (cashreceiptcurrencyid==2){
				//pendingcashreceiptcurrency-=paidalreadyCS/cashreceiptexchangerate;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-(paidalreadyCS+diferenciacambiariapagado)/cashreceiptexchangerate;
			}
			pendingcashreceiptcurrency=roundToTwo(pendingcashreceiptcurrency,2);
			$(this).text(pendingcashreceiptcurrency);		
		});
	}/*
	function updateRetention(){
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('td.retention div input').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoicecurrencyid=invoicerow.find('td.invoicecurrency input').val();
			var retentioncashreceiptcurrency=invoicerow.find('td.retentioninvoicecurrency div input').val();
			if (invoicecurrencyid!=cashreceiptcurrencyid){
				if (invoicecurrencyid==1){
					retentioncashreceiptcurrency/=cashreceiptexchangerate;
				}
				else if (invoicecurrencyid==2){
					retentioncashreceiptcurrency*=cashreceiptexchangerate;
				}
			}
			retentioncashreceiptcurrency=roundToTwo(retentioncashreceiptcurrency,2);
			$(this).text(retentioncashreceiptcurrency);		
		});			
	}
	*/
	
	function calculateAllRows(){
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	}

	$('body').on('change','#CashReceiptBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#CashReceiptAmount').parent().addClass('hidden');
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().addClass('hidden');
			$('#invoicesForClient').addClass('hidden');
			//$('#CashReceiptBoolRetention').parent().addClass('hidden');
			//$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			//$('#CashReceiptRetentionNumber').parent().addClass('hidden');
		}
		else {
			$('#CashReceiptAmount').parent().removeClass('hidden');
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().removeClass('hidden');
			//$('#CashReceiptBoolRetention').parent().removeClass('hidden');
			//$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			//if ($('#CashReceiptBoolRetention').is(':checked')){
			//	$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
			//}
			//else {
			//	$('#CashReceiptRetentionNumber').parent().addClass('hidden');
			//}
			$('#invoicesForClient').removeClass('hidden');
		}
	});	
	
	$('body').on('change','#CashReceiptCurrencyId',function(){	
		var currencyid=$(this).children("option").filter(":selected").val();
		var exchangerate=parseFloat($('#CashReceiptExchangeRate').val());
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			//$('td.amount div').prepend('C$ ');
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
			var value_in_USD=0;
			$('tr:not(.totalrow) td.pending span.amount').each(function(){
				value_in_USD=parseFloat($(this).text());
				$(this).text(roundToTwo(value_in_USD*exchangerate));
			});
			var value_in_USD=0;
			//$('tr:not(.totalrow) td.retention div input').each(function(){
			//	value_in_USD=parseFloat($(this).text());
			//	$(this).val(roundToTwo(value_in_USD*exchangerate));
			//});
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			//$('td.amount div').prepend('US$ ');
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
			var value_in_CS=0;
			$('tr:not(.totalrow) td.pending span.amount').each(function(){
				value_in_CS=parseFloat($(this).text());
				$(this).text(roundToTwo(value_in_CS/exchangerate));
			});
			//var value_in_CS=0;
			//$('tr:not(.totalrow) td.retention div input').each(function(){
			//	value_in_CS=parseFloat($(this).val());
			//	$(this).val(roundToTwo(value_in_CS/exchangerate));
			//});
		}
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	});	
	
<?php
	if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
?>
	$('body').on('change','#CashReceiptClientId',function(){	
		var clientId=$(this).children("option").filter(":selected").val();
    var enterpriseId=$('#CashReceiptEnterpriseId').val();
		if (clientId>0){
			//loadInvoicesForClient(clientId);
      loadInvoicesForClient(clientId,enterpriseId);
		}
	});	
	/*	
	function loadInvoicesForClient(clientid){
		var receiptday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		//var boolretention=$('#CashReceiptBoolRetention').is(':checked');
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>invoices/getpendinginvoicesforclient/',
			//data:{"clientid":clientid,"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear,"currencyid":currencyid,"boolretention":boolretention},
      data:{"clientid":clientid,"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear,"currencyid":currencyid},
			cache: false,
			type: 'POST',
			success: function (invoices) {
				$('#invoicesForClient').html(invoices);
				formatCurrencies();
			},
			error: function(e){
				$('#invoicesForClient').html(e.responseText);
				console.log(e);
			}
		});
	}
	*/	
  function loadInvoicesForClient(clientId,enterpriseId){
		var receiptDay=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptMonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptYear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		var currencyId=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		//var boolretention=$('#CashReceiptBoolRetention').is(':checked');
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>invoices/getPendingInvoicesForClient/',
			//data:{"clientid":clientid,"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear,"currencyid":currencyid,"boolretention":boolretention},
      data:{"clientId":clientId,"enterpriseId":enterpriseId,"receiptDay":receiptDay,"receiptMonth":receiptMonth,"receiptYear":receiptYear,"currencyId":currencyId},
			cache: false,
			type: 'POST',
			success: function (invoices) {
				$('#invoicesForClient').html(invoices);
				formatCurrencies();
			},
			error: function(e){
				$('#invoicesForClient').html(e.responseText);
				console.log(e);
			}
		});
	}
<?php	
	}
?>
	/*
	$('body').on('change','#CashReceiptBoolRetention',function(){	
		if ($(this).is(':checked')){
			$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			$('#invoicesForClient th.retention').removeClass('hidden');
			$('#invoicesForClient td.retention').removeClass('hidden');
			$('#invoicesForClient th.retentionpayment').removeClass('hidden');
			$('#invoicesForClient td.retentionpayment').removeClass('hidden');
		}
		else {
			$('#CashReceiptRetentionNumber').parent().addClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			$('#invoicesForClient th.retention').addClass('hidden');
			$('#invoicesForClient td.retention').addClass('hidden');
			$('#invoicesForClient th.retentionpayment').addClass('hidden');
			$('#invoicesForClient td.retentionpayment').addClass('hidden');
		}
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	});	
	*/
	$('body').on('change','td.increment div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
	});	
	$('body').on('change','td.discount div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
	});	
	/*
	$('body').on('change','td.retention div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
	});	
	*/
	
  $('body').on('change','.powerselector',function(e){
		if ($(this).is(':checked')){
			$(this).closest('fieldset').find('td.selector input').prop('checked',true);
      $(this).closest('fieldset').find('input.powerselector').prop('checked',true);
      $(this).closest('fieldset').find('tr').removeClass('noprint');
		}
		else {
			$(this).closest('fieldset').find('td.selector input').prop('checked',false);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',false);
      $(this).closest('fieldset').find('tr').addClass('noprint');
		}
    triggerAllSelectors();
	});
  function triggerAllSelectors(){
    $('#pendingInvoices tr:not(.totalrow)').each(function(){
      $(this).find('td.selector input[type="checkbox"]').each(function(){
        setRowValue($(this).attr('id'));
      });
    });
    calculateTotalRow();
		calculateTotalPayment();
  }
  function setRowValue(selectorId){
    var rowSelector=$('#'+selectorId);
    var closestRow=rowSelector.closest('tr');
    if ($(rowSelector).is(':checked')){
      var amount= closestRow.find('td.saldo div input').val();
      closestRow.find('td.payment div input').val(amount);
      closestRow.removeClass('noprint');
    }
    else {
      closestRow.find('td.payment div input').val(0);
      closestRow.addClass('noprint');
    }
  }
  
  $('body').on('change','.selector input',function(e){
    setRowValue($(this).attr('id'));
    calculateTotalRow();
		calculateTotalPayment();
  });
  
  
  $('body').on('change','td.payment div input',function(){	
		var invoiceId=$(this).closest('tr').attr('id');
		calculateRow(invoiceId);
		calculateTotalRow();
		calculateTotalPayment();
	});	
	/*
	$('body').on('change','td.retentionpayment div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
		calculateTotalPayment();
	});	
	*/
	function calculateRow(invoiceId) {    
		var cashreceiptCurrencyId=$('#CashReceiptCurrencyId').val();
		var exchangeRateCashReceipt=$('#CashReceiptExchangeRate').val();
		if ($.isNumeric(invoiceId)){
			// calculate value saldo
			var currentrow=$('#'+invoiceId);
			var pending=parseFloat(currentrow.find('td.pending span.amount.right').text());
			var increment=parseFloat(currentrow.find('td.increment div input').val());
			var discount=parseFloat(currentrow.find('td.discount div input').val());
			 
			//var bool_retention=$('#CashReceiptBoolRetention').is(':checked');
			/*var retention=parseFloat(currentrow.find('td.retention div input').val());*/
			var saldo=pending+increment-discount;
			/*
      if (bool_retention){
				if (pending>retention){
					saldo=pending+increment-discount-retention;
				}
				else {
					retention=retention-pending;
					saldo=increment-discount;
				}
			}
			*/
			currentrow.find('td.saldo div input').val(roundToTwo(saldo));
			
			var rateDifference=parseFloat(currentrow.find('td.exchangeratedifference div input').val());
			
			var payment=parseFloat(currentrow.find('td.payment div input').val());
			if (!$.isNumeric(payment)){
				payment=0;
				currentrow.find('td.selector input[type="checkbox"]').prop('checked',false);
				currentrow.find('td.payment div input').val('0');
			}
			
			if (payment>0){
				if (payment<saldo){
					currentrow.find('td.payment div input').addClass('yellowbg');
					currentrow.find('td.payment div input').removeClass('redbg');
					currentrow.find('td.payment div input').removeClass('greenbg');
				}
				else {
					if ((payment-saldo)<0.01){
						currentrow.find('td.payment div input').addClass('greenbg');
						currentrow.find('td.payment div input').removeClass('redbg');
						currentrow.find('td.payment div input').removeClass('yellowbg');
					}
					else {
						currentrow.find('td.payment div input').addClass('redbg');
						currentrow.find('td.payment div input').removeClass('yellowbg');
						currentrow.find('td.payment div input').removeClass('greenbg');
					}
				}
			}
			else {
				currentrow.find('td.payment div input').removeClass('yellowbg');
				currentrow.find('td.payment div input').removeClass('greenbg');
				currentrow.find('td.payment div input').removeClass('redbg');
			}
      /*
			var retentionpayment=parseFloat(currentrow.find('td.retentionpayment div input').val());
			if (!$.isNumeric(retentionpayment)){
				retentionpayment=0;
				currentrow.find('td.retentionpayment div input').val('0');
			}
			if (retentionpayment>0){
				if (retentionpayment<retention){
					currentrow.find('td.retentionpayment div input').addClass('yellowbg');
					currentrow.find('td.retentionpayment div input').removeClass('redbg');
					currentrow.find('td.retentionpayment div input').removeClass('greenbg');
				}
				else {
					if (retentionpayment==retention){
						currentrow.find('td.retentionpayment div input').addClass('greenbg');
						currentrow.find('td.retentionpayment div input').removeClass('redbg');
						currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
					}
					else {
						currentrow.find('td.retentionpayment div input').addClass('redbg');
						currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
						currentrow.find('td.retentionpayment div input').removeClass('greenbg');
					}
				}
			}
			else {
				currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
				currentrow.find('td.retentionpayment div input').removeClass('greenbg');
				currentrow.find('td.retentionpayment div input').removeClass('redbg');
				//20160321 MODIFIED TO CORRECT SITUATION WITH BOTH PAYMENTS RESET TO 0
				currentrow.find('td.creditpayment div input').val(roundToTwo(payment+retentionpayment));
			}
			var retentionpaymentCS=parseFloat(currentrow.find('td.retentionpayment div input').val());
      */
			var paymentCS=payment;
			var incrementCS=increment;
			var discountCS=discount;
			if (cashreceiptCurrencyId == <?php echo CURRENCY_USD; ?>){
				paymentCS=roundToTwo(paymentCS*exchangeRateCashReceipt);
				/*retentionpaymentCS=roundToTwo(retentionpaymentCS*exchangeRateCashReceipt);*/
			}
			//var paymentleft=roundToTwo(paymentCS+retentionpaymentCS+discountCS);
      var paymentleft=roundToTwo(paymentCS+discountCS);
			var pendingCS=parseFloat(currentrow.find('td.pending span.amount.right').text());
			if (cashreceiptCurrencyId == <?php echo CURRENCY_USD; ?>){
				pendingCS=roundToTwo(pendingCS*exchangeRateCashReceipt);
			}
			var pendingcreditCS=roundToTwo(pendingCS-rateDifference);
			currentrow.find('td.descpayment div input').val(roundToTwo(discountCS));
			if (paymentleft>0){
				if (paymentleft<pendingcreditCS){
					currentrow.find('td.creditpayment div input').val(roundToTwo(paymentleft));
					if (paymentleft<pendingcreditCS){
						currentrow.find('td.creditpayment div input').addClass('red');
					}
					paymentleft=0;
				}
				else {
					currentrow.find('td.creditpayment div input').val(roundToTwo(pendingcreditCS));
					currentrow.find('td.creditpayment div input').removeClass('red');
					paymentleft-=pendingcreditCS;
				}
				// MODIFIED 20160122
				//if (paymentleft>0){
				//	currentrow.find('td.descpayment div input').val(roundToTwo(discountCS));
					//paymentleft+=discountCS;
				//}
				if (paymentleft<incrementCS){
					currentrow.find('td.incpayment div input').addClass('red');
				}
				else {
					currentrow.find('td.incpayment div input').removeClass('red');
				}
				paymentleft=roundToTwo(paymentleft);
				if (paymentleft>0){
					if (paymentleft<incrementCS){
						currentrow.find('td.incpayment div input').val(roundToTwo(paymentleft));
						paymentleft=0;
					}
					else {
						currentrow.find('td.incpayment div input').val(roundToTwo(incrementCS));
						paymentleft-=incrementCS;
					}
				}
				paymentleft=roundToTwo(paymentleft);
				if (paymentleft<rateDifference){
					currentrow.find('td.difpayment div input').addClass('red');
				}
				else {
					currentrow.find('td.difpayment div input').removeClass('red');
				}
				paymentleft=roundToTwo(paymentleft);
				if (paymentleft>0){
					if (paymentleft<rateDifference){
						currentrow.find('td.difpayment div input').val(roundToTwo(paymentleft));
						paymentleft=0;
					}
					else {
						currentrow.find('td.difpayment div input').val(roundToTwo(rateDifference));
						paymentleft-=rateDifference;
					}
				}
				//ADDED 20160309 TO FIX SITUATION WITH DIFF BEING 0 AFTER FIRST REGISTERING HIGHER AMOUNT
				else {
					currentrow.find('td.difpayment div input').val(0);
				}
			}
		}
	}
	
	function calculateTotalRow() {    
		var invoiceTotal=0;
		var totalPaidAlready=0;
		var totalPending=0;
		
		var totalIncrement=0;
		var totalDiscount=0;
		/*var totalRetention=0;*/
		var totalRateDifference=0;
		var totalSaldo=0;
		
		var totalPayment=0;
		/*var totalRetentionPayment=0;*/
		var totalCreditPayment=0;
		var totalIncPayment=0;
		var totalDescPayment=0;
		var totalDifPayment=0;
		
		var cashCurrencyId=$('#CashReceiptCurrencyId').val();
		
		$('tr:not(.totalrow) td.totalprice span.amount.right').each(function(){
			var invoiceCurrencyId=$(this).closest('tr').find('td.invoiceCurrencyId input').val();
			var invoiceCost=parseFloat($(this).text());
			if (cashCurrencyId==invoiceCurrencyId){
				invoiceTotal+=invoiceCost;
			}
			else {
				if (cashCurrencyId==<?php echo CURRENCY_CS; ?>){
					invoiceTotal+=roundToTwo(invoiceCost*<?php echo $exchangeRateCashReceipt; ?>);
				}
				else {
					invoiceTotal+=roundToTwo(invoiceCost/<?php echo $exchangeRateCashReceipt; ?>);
				}
			}
		});
		$('tr.totalrow td.totalprice span.totalamount').text(roundToTwo(invoiceTotal));
		
		$('tr:not(.totalrow) td.paidalready span.amount.right').each(function(){
			totalPaidAlready+=parseFloat($(this).text());
		});
		$('tr.totalrow td.totalPaidAlready span.totalamount').text(roundToTwo(totalPaidAlready));

		$('tr:not(.totalrow) td.pending span.amount.right').each(function(){
			totalPending+=parseFloat($(this).text());
		});
		//var roundedtotalPending=roundToTwo(totalPending);
		$('tr.totalrow td.pending span.totalamount').text(parseFloat(roundToTwo(totalPending)));
		
		$('tr:not(.totalrow) td.increment div input').each(function(){
			totalIncrement+=parseFloat($(this).val());
		});
		$('tr.totalrow td.increment span.totalamount').text(roundToTwo(totalIncrement));
		
		$('tr:not(.totalrow) td.discount div input').each(function(){
			totalDiscount+=parseFloat($(this).val());
		});
		$('tr.totalrow td.discount span.totalamount').text(roundToTwo(totalDiscount));
		
		$('tr:not(.totalrow) td.exchangeratedifference div input').each(function(){
			totalRateDifference+=parseFloat($(this).val());
		});
		$('tr.totalrow td.exchangeratedifference span.totalamount').text(roundToTwo(totalRateDifference));
		/*
		$('tr:not(.totalrow) td.retention div input').each(function(){
			totalRetention+=parseFloat($(this).val());
		});
		$('tr.totalrow td.retention span.totalamount').text(roundToTwo(totalRetention));
		*/
		$('tr:not(.totalrow) td.saldo div input').each(function(){
			totalSaldo+=parseFloat($(this).val());
		});
		$('tr.totalrow td.saldo span.totalamount').text(roundToTwo(totalSaldo));
		
		$('tr:not(.totalrow) td.payment div input').each(function(){
			if ($.isNumeric($(this).val())){
				totalPayment+=parseFloat($(this).val());
			}
		});
		$('tr.totalrow td.payment span.totalamount').text(roundToTwo(totalPayment));
		/*
		$('tr:not(.totalrow) td.retentionpayment div input').each(function(){
			if ($.isNumeric($(this).val())){
				totalRetentionPayment+=parseFloat($(this).val());
			}
		});
		$('tr.totalrow td.retentionpayment span.totalamount').text(roundToTwo(totalRetentionPayment));
    */
		
		$('tr:not(.totalrow) td.creditpayment div input').each(function(){
			totalCreditPayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.creditpayment span.totalamount').text(roundToTwo(totalCreditPayment));
		
		$('tr:not(.totalrow) td.incpayment div input').each(function(){
			totalIncPayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.incpayment span.totalamount').text(roundToTwo(totalIncPayment));
		
		$('tr:not(.totalrow) td.descpayment div input').each(function(){
			totalDescPayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.descpayment span.totalamount').text(roundToTwo(totalDescPayment));
		
		$('tr:not(.totalrow) td.difpayment div input').each(function(){
			totalDifPayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.difpayment span.totalamount').text(roundToTwo(totalDifPayment));
		var totalrowfinished=true;
	}
  
	function calculateTotalPayment() {    
		var totalcreditpayment=parseFloat($('#pendingInvoices tr.totalrow td.creditpayment span.totalamount').text());
		var totalretentionpayment=parseFloat($('#pendingInvoices tr.totalrow td.retentionpayment span.totalamount').text());
		var totalincrement=parseFloat($('#pendingInvoices tr.totalrow td.incpayment span.totalamount').text());
		var totaldiscount=parseFloat($('#pendingInvoices tr.totalrow td.descpayment span.totalamount').text());
		var totalerdiff=parseFloat($('#pendingInvoices tr.totalrow td.difpayment span.totalamount').text());
		var totalpayment=parseFloat($('#pendingInvoices tr.totalrow td.payment span.totalamount').text());
		
		
		//$('#CashReceiptAmount').val(totalcreditpayment);
		$('#CashReceiptAmountRetentionPaid').val(totalretentionpayment);
		$('#CashReceiptAmountCuentasPorCobrar').val(roundToTwo(totalcreditpayment));
		$('#CashReceiptAmountDiscount').val(totaldiscount);
		$('#CashReceiptAmountIncrement').val(totalincrement);
		$('#CashReceiptAmountDifferenceExchangeRate').val(totalerdiff);
		$('#CashReceiptAmountTotalPayment').val(totalpayment);
	}
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToFour(num) {    
		return +(Math.round(num + "e+4")  + "e-4");
	}
	
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
	
	function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			//$(this).parent().prepend("C$ ");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext);
	});
	
	$(document).ready(function(){
		if ($('#CashReceiptBoolAnnulled').is(':checked')){
			$('#CashReceiptAmount').parent().addClass('hidden');
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().addClass('hidden');
			/*
        $('#CashReceiptBoolRetention').parent().addClass('hidden');
        $('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
        $('#CashReceiptRetentionNumber').parent().addClass('hidden');
      */
		}
		else {
			$('#CashReceiptAmount').parent().removeClass('hidden');
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().removeClass('hidden');	
			$('#CashReceiptBoolRetention').parent().removeClass('hidden');
			/*
      if ($('#CashReceiptBoolRetention').is(':checked')){
      	$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
				$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			}
			else {
				$('#CashReceiptRetentionNumber').parent().addClass('hidden');
				$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			}
      */
		}
		
	<?php 
		if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){ 
	?>
		
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		
		if (cashreceiptcurrencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (cashreceiptcurrencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}	
			
		var clientid=$('#CashReceiptClientId').children("option").filter(":selected").val();
		if (clientid>0){
			//loadInvoicesForClient(clientid);
		<?php	
			echo "$(document).ajaxComplete(function() {\r\n";
			if (!empty($postedInvoiceData)){
				foreach ($postedInvoiceData as $postedInvoice){
					echo "var invoiceRow=$('#'+".$postedInvoice['invoice_id'].");\r\n";
					echo "invoiceRow.find('td.increment div input').val('".$postedInvoice['increment']."');\r\n";
					echo "invoiceRow.find('td.discount div input').val('".$postedInvoice['discount']."');\r\n";
					echo "invoiceRow.find('td.saldo div input').val('".$postedInvoice['saldo']."');\r\n";
					echo "invoiceRow.find('td.payment div input').val('".$postedInvoice['payment']."');\r\n";
					//echo "invoiceRow.find('td.retention div input').val('".$postedInvoice['retention']."');\r\n";
					echo "calculateTotalRow();";
					echo "calculateTotalPayment();";
				}
			}
		?>
    /*
			if (!$('#CashReceiptBoolAnnulled').is(':checked')){
				if ($('#CashReceiptBoolRetention').is(':checked')){				
					$('#invoicesForClient th.retention').removeClass('hidden');
				}
			}
    */
		<?php
			echo "});\r\n";
		?>
		}
	<?php } ?>
		calculateAllRows();
		calculateTotalPayment();
	});
	
</script>
<div class="cashReceipts form fullwidth">
<?php 
	//$boolRetention=$previousCashReceipt['CashReceipt']['bool_retention'];
	$cashReceiptCurrencyId=$previousCashReceipt['CashReceipt']['currency_id'];
  if (count($enterprises) == 0){
    echo '<h2>Su usuario no está asociada con ninguna gasolinera, por favor comuníquese con su administrador</h2>';
  }
  else {
    echo $this->Form->create('CashReceipt'); 
      echo "<fieldset>";
      if ($this->request->data['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_CREDIT){
        echo "<legend>".__('Editar Recibo de Caja (Factura de Crédito)')."</legend>";
      }
      else if ($this->request->data['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_OTHER){
        echo "<legend>".__('Editar Recibo de Caja (Otros Ingresos)')."</legend>";
      }
      echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
      if ($enterpriseId == 0){
        echo '<h2>Seleccione una gasolinera para registrar un recibo de caja</h2>';
      } 
      else {
        echo $this->Form->input('id');
        echo $this->Form->input('receipt_date',array('dateFormat'=>'DMY'));
        echo $this->Form->input('receipt_code',array('class'=>'narrow','readonly'=>'readonly'));
        echo $this->Form->input('exchange_rate',array('default'=>$exchangeRateCashReceipt,'class'=>'narrow','readonly'=>'readonly'));
        echo $this->Form->input('bool_annulled');
        
        echo $this->Form->input('cash_receipt_type_id',array('default'=>$cash_receipt_type_id,'div'=>array('hidden'=>'hidden')));
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
          echo $this->Form->input('received_from');
        }
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          echo $this->Form->input('client_id',array('empty'=>array('0'=>'Seleccione Cliente')));
        }
        echo $this->Form->input('concept',array('class'=>'narrow'));
        echo $this->Form->input('observation',array('type'=>'textarea', 'rows' => 2, 'cols' => 25,'style'=>'width:40%'));
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
          echo $this->Form->input('amount',array('type'=>'decimal','class'=>'narrow','default'=>'0'));
        }
        echo $this->Form->input('currency_id');
        echo $this->Form->input('cashbox_accounting_code_id',array('options'=>$cashboxAccountingCodes,'empty'=>array('0'=>'Seleccione Caja')));
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
          echo $this->Form->input('credit_accounting_code_id',array('label'=>'Cuenta Contable HABER (OTROS INGRESOS)','options'=>$accountingCodes,'empty'=>array('0'=>'Seleccione Cuenta HABER')));
          echo $this->Form->submit(__('Submit'));
        }
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          echo $this->Form->input('bool_retention',array('type'=>'checkbox','label'=>'Retención','default'=>$previousCashReceipt['CashReceipt']['bool_retention']));
          echo $this->Form->input('retention_number',array('label'=>'Número Retención'));
          echo "<div class='righttop'>";
            echo "<h4>Desglose</h4>";
            //echo $this->Form->input('amount',array('label'=>'Abono para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
            echo $this->Form->input('amount_cuentas_por_cobrar',array('label'=>'Total para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
            echo "<br/>";
            echo $this->Form->input('amount_increment',array('label'=>'Monto Incremento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
            echo $this->Form->input('amount_discount',array('label'=>'Monto Descuento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
            echo $this->Form->input('amount_difference_exchange_rate',array('label'=>'Monto Cambiario','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
            echo "<br/>";					
        
            echo $this->Form->input('amount_total_payment',array('label'=>'Monto Total Pagado','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>'));
            //echo $this->Form->input('amount_retention_paid',array('label'=>'Retención para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>'));
            echo $this->Form->submit(__('Submit'));
          echo "</div>";
        }
      
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          echo "<div id='invoicesForClient'>";				
            echo $this->Form->input('powerselector1',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
            echo "<table id='pendingInvoices'>";
              echo "<thead>";
                echo "<tr>";
                  echo "<th class='hidden'>".__('Invoice id')."</th>";
                  echo "<th class='hidden'>".__('Invoice currency')."</th>";
                  echo "<th class='hidden'>".__('invoice exchange rate')."</th>";
                  echo "<th class='hidden'>".__('difference in rates')."</th>";
                  //echo "<th class='hidden'>".__('retention_invoice_currency')."</th>";
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
                /*
                  if ($boolRetention){
                    echo "<th class='retention'>".__('Ret')."</th>";
                  }
                  else {
                    echo "<th class='retention hidden'>".__('Ret')."</th>";
                  }
                */
                  echo "<th>".__('A pagar')."</th>";

                  echo "<th class='separator'></th>";

                  echo "<th>".__('Abono Efectivo')."</th>";
                /*
                  if ($boolRetention){
                    echo "<th class='retention'>".__('Abono Ret')."</th>";
                  }
                  else {
                    echo "<th class='retention hidden'>".__('Abono Ret')."</th>";
                  }
                */
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
              
              if ($cashreceiptcurrencyid==CURRENCY_CS){
                $cashReceiptCurrencyAbbreviation="C$";
              }
              elseif ($cashreceiptcurrencyid==CURRENCY_USD){
                $cashReceiptCurrencyAbbreviation="US$";
              }
              //pr($invoicesForClient);
              //pr($cashReceiptInvoices);
              foreach ($cashReceiptInvoices as $invoiceForClient){
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
                  // sum back the amounts paid when the cash receipt was saved previously
                  //echo $pendingCashReceiptCurrency."<br/>";
                  $pendingCashReceiptCurrency+=$invoiceForClient['CashReceiptInvoice']['payment'];
                  //echo $pendingCashReceiptCurrency."<br/>";
                  //$pendingCashReceiptCurrency+=$invoiceForClient['CashReceiptInvoice']['payment_retention'];
                  // at this time, for invoices in US$, an erdiff might have been applied which would have been incorporated in paid_already_CS, this needs to be subtracted still from the saldo in the correct_currency
                  if ($invoiceCurrencyId==CURRENCY_USD){
                    if ($invoiceForClient['CashReceiptInvoice']['payment_erdiff_CS']>0){
                      if ($cashReceiptCurrencyId==CURRENCY_CS){
                        $pendingCashReceiptCurrency-=$invoiceForClient['CashReceiptInvoice']['payment_erdiff_CS'];
                      }
                      elseif ($invoiceCurrencyId==CURRENCY_USD){
                        $pendingCashReceiptCurrency-=$invoiceForClient['CashReceiptInvoice']['payment_erdiff_CS']/$exchangeRateCashReceipt;
                      }
                    }
                  }
                  
                  //echo $pendingCashReceiptCurrency."<br/>";
                  if (abs($pendingCashReceiptCurrency)<0.01){
                    $pendingCashReceiptCurrency=0;
                  }
                  else {
                    $pendingCashReceiptCurrency=round($pendingCashReceiptCurrency,2);
                  }
                  // calculate the retention in the cashreceipt currency
                  //$retentionCashReceiptCurrency=$invoiceForClient['Invoice']['retention'];
                  //echo $retentionCashReceiptCurrency."<br/>";
                  //echo $invoiceCurrencyId."<br/>";
                  //echo $cashReceiptCurrencyId."<br/>";
                /*  
                  if ($invoiceCurrencyId!=$cashReceiptCurrencyId){
                    if ($invoiceCurrencyId==CURRENCY_CS){
                      $retentionCashReceiptCurrency/=$exchangeRateCashReceipt;
                    }
                    elseif ($invoiceCurrencyId==CURRENCY_USD){
                      $retentionCashReceiptCurrency*=$exchangeRateCashReceipt;
                    }
                  }
                  //echo $retentionCashReceiptCurrency."<br/>";
                */
                  echo "<td class='invoiceid hidden'>".$this->Form->input('Invoice.'.$i.'.invoice_id',array('label'=>false,'default'=>$invoiceForClient['Invoice']['id'],'type'=>'text'))."</td>";
                  echo "<td class='invoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.currency_id',array('label'=>false,'default'=>$invoiceForClient['Invoice']['currency_id'],'type'=>'text'))."</td>";
                  echo "<td class='invoiceexchangerate hidden'>".$this->Form->input('Invoice.'.$i.'.invoiceexchangerate',array('label'=>false,'default'=>$invoiceForClient['Invoice']['invoice_exchange_rate']))."</td>";
                  echo "<td class='differenceexchangerates hidden'>".$this->Form->input('Invoice.'.$i.'.differenceexchangerate',array('label'=>false,'default'=>$invoiceForClient['Invoice']['difference_exchange_rates']))."</td>";
                  //echo "<td class='retentioninvoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.retentioninvoicecurrency',array('label'=>false,'default'=>$invoiceForClient['Invoice']['retention']))."</td>";
                  echo "<td class='diferenciacambiariapagado hidden'>".$this->Form->input('Invoice.'.$i.'.diferenciacambiariapagado',array('label'=>false,'default'=>$invoiceForClient['Invoice']['diferencia_cambiaria_pagado']))."</td>";
                  
                  echo '<td class="selector">'.$this->Form->input('Invoice.'.$i.'.selector',['checked'=>true,'label'=>false]).'</td>';

                  echo "<td class='saledate'>".$invoiceDateTime->format('d-m-Y')."</td>";
                  echo "<td class='invoicecode'>".$this->Html->Link($invoiceForClient['Invoice']['invoice_code'],array('controller'=>'orders','action'=>'verVenta',$invoiceForClient['Invoice']['order_id']))."</td>";
                  echo "<td class='totalprice amount'><span class='currencyleft'>".$invoiceForClient['Invoice']['Currency']['abbreviation']." </span><span class='amount right'>".$invoiceForClient['Invoice']['total_price']."</span></td>";
                  
                  $paidAlreadyForCashReceipt=$invoiceForClient['Invoice']['paid_already_CS']-$invoiceForClient['CashReceiptInvoice']['payment_credit_CS'];
                  if (abs($paidAlreadyForCashReceipt)<0.01){
                    $paidAlreadyForCashReceipt=0;
                  }
                  else {
                    $paidAlreadyForCashReceipt=round($paidAlreadyForCashReceipt,2);
                  }
                  echo "<td class='paidalready amount'><span class='currencyleft'>C$ </span><span class='amount right'>".$paidAlreadyForCashReceipt."</span></td>";
                  echo "<td class='pending amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='amount right'>".$pendingCashReceiptCurrency."</span></td>";
                /*  
                  if ($boolRetention){
                    if ($pendingCashReceiptCurrency>=$retentionCashReceiptCurrency){
                      $pendingCashReceiptCurrency-=$retentionCashReceiptCurrency;
                    }
                    else {
                      $retentionCashReceiptCurrency-=$pendingCashReceiptCurrency;
                    }
                  }
                */
                  $pendingCashReceiptCurrency=ceil($pendingCashReceiptCurrency*100)/100;
                //  $retentionCashReceiptCurrency=ceil($retentionCashReceiptCurrency*100)/100;
                  
                  echo "<td class='increment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.increment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['increment']))."</td>";
                  echo "<td class='discount amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.discount',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['discount']))."</td>";
                  echo "<td class='exchangeratedifference amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.exchangeratedifference',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['erdiff'],'readonly'=>'readonly','class'=>'nobox'))."</td>";	
                /*                  
                  if ($boolRetention){
                    echo "<td class='retention amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',array('type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
                  }
                  else {
                    echo "<td class='retention amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',array('type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
                  }
                */
                  echo "<td class='saldo amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.saldo',array('type'=>'decimal','label'=>false,'readonly'=>'readonly','default'=>$pendingCashReceiptCurrency,'class'=>'nobox'))."</td>";
                  
                  echo "<td class='separator'></td>";

                  echo "<td class='payment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.payment',['type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment']])."</td>";
                  /*
                  if ($boolRetention){
                    echo "<td class='retentionpayment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_retention']))."</td>";
                  }
                  else {
                    echo "<td class='retentionpayment amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_retention']))."</td>";
                  }
                  */
                  echo "<td class='creditpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.creditpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_credit_CS'],'readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td class='incpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.incpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_increment_CS'],'readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td  class='descpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.descpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_discount_CS'],'readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td  class='difpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.difpayment',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['CashReceiptInvoice']['payment_erdiff_CS'],'readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td class='duedate'>".$dueDateTime->format('d-m-Y')."</td>";
                echo "</tr>";
                $i++;
                if ($invoiceForClient['Currency']['id']!=$cashReceiptCurrencyId){
                  if ($invoiceForClient['Currency']['id']==CURRENCY_USD){
                    $totalInvoice+=round($invoiceForClient['Invoice']['total_price']*$exchangeRateCashReceipt,2);
                  }
                  else {
                    $totalInvoice+=round($invoiceForClient['Invoice']['total_price']/$exchangeRateCashReceipt,2);
                  }
                }
                else {
                  $totalInvoice+=$invoiceForClient['Invoice']['total_price'];
                }
                
                $totalPaidAlready+=$paidAlreadyForCashReceipt;
                $totalPending+=$pendingCashReceiptCurrency;
                $totalRateDifference+=$invoiceForClient['CashReceiptInvoice']['erdiff'];
                //$totalRetention+=$retentionCashReceiptCurrency;
                $totalSaldo+=$pendingCashReceiptCurrency;
              }
              
              foreach ($otherPendingInvoicesForClient as $invoiceForClient){
                
                $invoiceDate=new DateTime($invoiceForClient['Invoice']['invoice_date']);
                $dueDate=new DateTime($invoiceForClient['Invoice']['due_date']);
                
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
                  }
                  /*
                  // calculate the retention in the cashreceipt currency
                  $retentionCashReceiptCurrency=$invoiceForClient['Invoice']['retention'];
                  if ($invoiceCurrencyId!=$cashReceiptCurrencyId){
                    if ($invoiceCurrencyId==CURRENCY_CS){
                      $retentionCashReceiptCurrency/=$exchangeRateCashReceipt;
                    }
                    elseif ($invoiceCurrencyId==CURRENCY_USD){
                      $retentionCashReceiptCurrency*=$exchangeRateCashReceipt;
                    }
                  }
                  */
                  echo "<td class='invoiceid hidden'>".$this->Form->input('Invoice.'.$i.'.invoice_id',array('label'=>false,'default'=>$invoiceForClient['Invoice']['id'],'type'=>'text'))."</td>";
                  echo "<td class='invoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.currency_id',array('label'=>false,'default'=>$invoiceForClient['Invoice']['currency_id'],'type'=>'text'))."</td>";
                  echo "<td class='invoiceexchangerate hidden'>".$this->Form->input('Invoice.'.$i.'.invoiceexchangerate',array('label'=>false,'default'=>$invoiceForClient['Invoice']['invoice_exchange_rate']))."</td>";
                  echo "<td class='differenceexchangerates hidden'>".$this->Form->input('Invoice.'.$i.'.differenceexchangerate',array('label'=>false,'default'=>$invoiceForClient['Invoice']['difference_exchange_rates']))."</td>";
                  //echo "<td class='retentioninvoicecurrency hidden'>".$this->Form->input('Invoice.'.$i.'.retentioninvoicecurrency',array('label'=>false,'default'=>$invoiceForClient['Invoice']['retention']))."</td>";
                  echo "<td class='diferenciacambiariapagado hidden'>".$this->Form->input('Invoice.'.$i.'.diferenciacambiariapagado',array('label'=>false,'default'=>$invoiceForClient['Invoice']['diferencia_cambiaria_pagado']))."</td>";
                  
                  echo '<td class="selector">'.$this->Form->input('Invoice.'.$i.'.selector',['checked'=>false,'label'=>false]).'</td>';
                  echo "<td class='saledate'>".$invoiceDate->format('d-m-Y')."</td>";
                  echo "<td class='invoicecode'>".$this->Html->Link($invoiceForClient['Invoice']['invoice_code'],array('controller'=>'orders','action'=>'verVenta',$invoiceForClient['Invoice']['order_id']))."</td>";
                  echo "<td class='totalprice amount'><span class='currencyleft'>".$invoiceForClient['Currency']['abbreviation']." </span><span class='amount right'>".$invoiceForClient['Invoice']['total_price']."</span></td>";
                  echo "<td class='paidalready amount'><span class='currencyleft'>C$ </span><span class='amount right'>".$invoiceForClient['Invoice']['paid_already_CS']."</span></td>";
                  echo "<td class='pending amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='amount right'>".(ceil($pendingCashReceiptCurrency*100)/100)."</span></td>";
                  /*
                  if ($boolRetention){
                    if ($pendingCashReceiptCurrency>=$retentionCashReceiptCurrency){
                      $pendingCashReceiptCurrency-=$retentionCashReceiptCurrency;
                    }
                    else {
                      $retentionCashReceiptCurrency-=$pendingCashReceiptCurrency;
                    }
                  }
                  */
                  $pendingCashReceiptCurrency=ceil($pendingCashReceiptCurrency*100)/100;
                  //$retentionCashReceiptCurrency=ceil($retentionCashReceiptCurrency*100)/100;
                  
                  echo "<td class='increment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.increment',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  echo "<td class='discount amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.discount',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  echo "<td class='exchangeratedifference amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.exchangeratedifference',array('type'=>'decimal','label'=>false,'default'=>$invoiceForClient['Invoice']['exchange_rate_difference'],'readonly'=>'readonly','class'=>'nobox'))."</td>";	
                  /*    
                  if ($boolRetention){
                    echo "<td class='retention amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',array('type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
                  }
                  else {
                    echo "<td class='retention amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retention',array('type'=>'decimal','label'=>false,'default'=>$retentionCashReceiptCurrency))."</td>";
                  }
                  */
                  echo "<td class='saldo amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.saldo',array('type'=>'decimal','label'=>false,'readonly'=>'readonly','default'=>$pendingCashReceiptCurrency,'class'=>'nobox'))."</td>";
                  
                  echo "<td class='separator'></td>";

                  echo "<td class='payment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.payment',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  /*
                  if ($boolRetention){
                    echo "<td class='retentionpayment amount'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  }
                  else {
                    echo "<td class='retentionpayment amount hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span>".$this->Form->input('Invoice.'.$i.'.retentionpayment',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  }
                  */
                  echo "<td class='creditpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.creditpayment',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td class='incpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.incpayment',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td  class='descpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.descpayment',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td  class='difpayment amount'><span class='currencyleft'>C$ </span>".$this->Form->input('Invoice.'.$i.'.difpayment',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','class'=>'nobox'))."</td>";
                  echo "<td class='duedate'>".$dueDate->format('d-m-Y')."</td>";
                echo "</tr>";
                $i++;
                if ($invoiceForClient['Currency']['id']!=$cashReceiptCurrencyId){
                  if ($invoiceForClient['Currency']['id']==CURRENCY_USD){
                    $totalInvoice+=round($invoiceForClient['Invoice']['total_price']*$exchangeRateCashReceipt,2);
                  }
                  else {
                    $totalInvoice+=round($invoiceForClient['Invoice']['total_price']/$exchangeRateCashReceipt,2);
                  }
                }
                else {
                  $totalInvoice+=$invoiceForClient['Invoice']['total_price'];
                }
                
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
                  echo "<td class='hidden'></td>";
                  echo "<td>Totales</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span> <span class='totalamount amountright'>".$totalInvoice."</span></td>";
                  echo "<td class='paidalready amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>".$totalPaidAlready."</span></td>";
                  echo "<td class='pending amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation."</span> <span class='totalamount amountright'>".$totalPending."</span></td>";
                  
                  echo "<td class='increment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalIncrement."</span></td>";
                  echo "<td class='discount amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalDiscount."</span></td>";
                  echo "<td class='exchangeratedifference amount right'>C$ <span class='totalamount amountright'>".$totalRateDifference."</td>";
                  /*
                  if ($boolRetention){
                    echo "<td class='retention amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalRetention."</span></td>";
                  }
                  else {
                    echo "<td class='retention amount right hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalRetention."</span></td>";
                  }
                  */
                  echo "<td class='saldo amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>".$totalSaldo."</span></td>";
                  
                  echo "<td class='separator'></td>";
                  
                  echo "<td class='payment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
                  /*
                  if ($boolRetention){
                    echo "<td class='retentionpayment amount right'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
                  }
                  else {
                    echo "<td class='retentionpayment amount right hidden'><span class='currency'>".$cashReceiptCurrencyAbbreviation." </span><span class='totalamount amountright'>0</span></td>";
                  }
                  */
                  echo "<td class='creditpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
                  echo "<td class='incpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
                  echo "<td  class='descpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
                  echo "<td  class='difpayment amount right'><span class='currencyleft'>C$ </span><span class='totalamount amountright'>0</span></td>";
                  
                  echo "<td></td>";
                echo "</tr>";			
              echo "</tbody>";
            echo "</table>";
            echo $this->Form->input('powerselector2',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
          
          echo "</div>";
        }
      }
    }  
    echo "</fieldset>";  
	echo $this->Form->end(); 
?>
</div>
<!--div class='actions'>
	<h3><?php //echo __('Actions'); ?></h3>
	<ul>
	<?php	
		//if ($bool_delete_permission) { 
		//	echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('CashReceipt.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('CashReceipt.id')))."</li>";
		//}
		//echo "<li>".$this->Html->link(__('List Cash Receipts'), array('action' => 'index'))."</li>";
		//echo "<br/>";
		if ($bool_client_index_permission){
			//echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'indexClients'))."</li>";
		}
		if ($bool_client_add_permission) { 
			//echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'addClient'))."</li>";
		} 
	?>
	</ul>
</div-->