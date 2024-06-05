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
		var cashReceiptExchangeRate=$('#CashReceiptExchangeRate').val();
		$('td.differenceexchangerates input').each(function(){
			var invoiceRow=$(this).closest('tr');
			var invoiceExchangeRate=invoiceRow.find('td.invoiceExchangeRate input').val();
			var exchangeRateDifference=roundToFour(cashReceiptExchangeRate-invoiceExchangeRate);
			$(this).val(exchangeRateDifference);
			var invoiceCurrencyId=invoiceRow.find('td.invoiceCurrencyId input').val();
			if (invoiceCurrencyId==2){
				var invoiceAmount=invoiceRow.find('td.totalprice span.amount').text();
				invoiceRow.find('td.exchangeRateDifference input').val(roundToTwo(invoiceAmount*exchangeRateDifference));
			}
		});
	}
	function updatePending(){
		var cashReceiptCurrencyId=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('tr:not(.totalrow) td.pending span.amountright').each(function(){
			var invoiceRow=$(this).closest('tr');
			var invoiceCurrencyIdid=invoiceRow.find('td.invoiceCurrencyId input').val();
			var pendingcashreceiptcurrency=invoiceRow.find('td.totalprice span').text();
			if (cashReceiptCurrencyId != invoiceCurrencyId){
				if (invoiceCurrencyIdid == <?php echo CURRENCY_CS; ?>){
					pendingcashreceiptcurrency/=$cashreceiptexchangerate;
				}
				else if (invoiceCurrencyIdid == <?php echo CURRENCY_USD; ?>){
					pendingcashreceiptcurrency*=$cashreceiptexchangerate;
				}
			}
			var paidalreadyCS=invoiceRow.find('td.paidalready span').text();
			var diferenciacambiariapagado=invoiceRow.find('td.diferenciacambiariapagado span').text();
			if (cashReceiptCurrencyId ==  <?php echo CURRENCY_CS; ?>){
				//pendingcashreceiptcurrency-=paidalreadyCS;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-paidalreadyCS-diferenciacambiariapagado;
			}
			else if (cashReceiptCurrencyId ==  <?php echo CURRENCY_USD; ?>){
				//pendingcashreceiptcurrency-=paidalreadyCS/cashreceiptexchangerate;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-(paidalreadyCS+diferenciacambiariapagado)/cashreceiptexchangerate;
			}
			pendingcashreceiptcurrency=roundToTwo(pendingcashreceiptcurrency,2);
			$(this).text(pendingcashreceiptcurrency);		
		});
	}
  /*
	function updateRetention(){
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('td.retention div input').each(function(){
			var invoiceRow=$(this).closest('tr');
			var invoiceCurrencyIdid=invoiceRow.find('td.invoiceCurrencyId input').val();
			var retentioncashreceiptcurrency=invoiceRow.find('td.retentioninvoiceCurrencyId div input').val();
			if (invoiceCurrencyIdid!=cashreceiptcurrencyid){
				if (invoiceCurrencyIdid==1){
					retentioncashreceiptcurrency/=cashreceiptexchangerate;
				}
				else if (invoiceCurrencyIdid==2){
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
			//var value_in_USD=0;
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
			//loadinvoicesForClient(clientid);
      loadInvoicesForClient(clientId,enterpriseId);
		}
	});	
	/*	
	function loadinvoicesForClient(clientid){
		var receiptday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		//var boolretention=$('#CashReceiptBoolRetention').is(':checked');
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>paymentReceipts/getpendinginvoicesforclient/',
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
		var invoiceId=$(this).closest('tr').attr('id');
		calculateRow(invoiceId);
		calculateTotalRow();
	});	
	$('body').on('change','td.discount div input',function(){	
		var invoiceId=$(this).closest('tr').attr('id');
		calculateRow(invoiceId);
		calculateTotalRow();
	});	
	/*
	$('body').on('change','td.retention div input',function(){	
		var invoiceId=$(this).closest('tr').attr('id');
		//calculateRow(invoiceId);
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
		var invoiceId=$(this).closest('tr').attr('id');
		calculateRow(invoiceId);
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
        currentrow.find('td.selector input[type="checkbox"]').prop('checked',true);
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
						currentrow.find('td.difpayment div input').val(roundToTwo(ratedifference));
						paymentleft-=ratedifference;
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
		var totalCreditPayment=parseFloat($('#pendingInvoices tr.totalrow td.creditpayment span.totalamount').text());
		/*var totalRetentionPayment=parseFloat($('#pendingInvoices tr.totalrow td.retentionpayment span.totalamount').text());*/
		var totalIncrement=parseFloat($('#pendingInvoices tr.totalrow td.incpayment span.totalamount').text());
		var totalDiscount=parseFloat($('#pendingInvoices tr.totalrow td.descpayment span.totalamount').text());
		var totalErDiff=parseFloat($('#pendingInvoices tr.totalrow td.difpayment span.totalamount').text());
		var totalPayment=parseFloat($('#pendingInvoices tr.totalrow td.payment span.totalamount').text());
		
		
		$('#CashReceiptAmount').val(totalCreditPayment);
		/*$('#CashReceiptAmountRetentionPaid').val(totalRetentionPayment);*/
		$('#CashReceiptAmountCuentasPorCobrar').val(roundToTwo(totalCreditPayment));
		$('#CashReceiptAmountDiscount').val(totalDiscount);
		$('#CashReceiptAmountIncrement').val(totalIncrement);
		$('#CashReceiptAmountDifferenceExchangeRate').val(totalErDiff);
		$('#CashReceiptAmountTotalPayment').val(totalPayment);
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
		$(this).val(uppercasetext)
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
			/*
      $('#CashReceiptBoolRetention').parent().removeClass('hidden');
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
			loadinvoicesForClient(clientid);
		<?php	
			echo "$(document).ajaxComplete(function() {\r\n";
			if (!empty($postedInvoiceData)){
				foreach ($postedInvoiceData as $postedinvoice){
					echo "var invoiceRow=$('#'+".$postedinvoice['payment_receipt_id'].");\r\n";
					echo "invoiceRow.find('td.increment div input').val('".$postedinvoice['increment']."');\r\n";
					echo "invoiceRow.find('td.discount div input').val('".$postedinvoice['discount']."');\r\n";
					echo "invoiceRow.find('td.saldo div input').val('".$postedinvoice['saldo']."');\r\n";
					echo "invoiceRow.find('td.payment div input').val('".$postedinvoice['payment']."');\r\n";
					//echo "invoiceRow.find('td.retention div input').val('".$postedinvoice['retention']."');\r\n";
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
		
	});
	
</script>
<div class="cashReceipts form" style='width:100%;'>
<?php 
  if (count($enterprises) == 0){
    echo '<h2>Su usuario no está asociada con ninguna gasolinera, por favor comuníquese con su administrador</h2>';
  }
  else {
    echo $this->Form->create('CashReceipt'); 
      echo "<fieldset>";
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          echo "<legend>".__('Crear Nuevo Recibo de Caja (Factura)')."</legend>";
        }
        else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
          echo "<legend>".__('Crear Nuevo Recibo de Caja (Otros Ingresos)')."</legend>";
        }
        echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
        if ($enterpriseId == 0){
          echo '<h2>Seleccione una gasolinera para registrar un recibo de caja</h2>';
        } 
        else {
        
          echo $this->Form->input('receipt_date',['dateFormat'=>'DMY']);
          echo $this->Form->input('receipt_code',['default'=>$newCashReceiptCode,'class'=>'narrow','readonly'=>'readonly']);
          echo $this->Form->input('exchange_rate',['default'=>$exchangeRateCashReceipt,'class'=>'narrow','readonly'=>'readonly']);
          echo $this->Form->input('bool_annulled');
          
          echo $this->Form->input('cash_receipt_type_id',['default'=>$cash_receipt_type_id,'div'=>['hidden'=>'hidden']]);
          if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
            echo $this->Form->input('received_from');
          }
          if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
            echo $this->Form->input('client_id',['empty'=>['0'=>'-- Seleccione Cliente --']]);
          }
          echo $this->Form->input('concept',['class'=>'narrow']);
          echo $this->Form->input('observation',['type'=>'textarea', 'rows' => 2, 'cols' => 25,'style'=>'width:40%']);
          if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
            echo $this->Form->input('amount',['type'=>'decimal','class'=>'narrow','default'=>'0']);
          }
          echo $this->Form->input('currency_id');
          echo $this->Form->input('cashbox_accounting_code_id',['options'=>$cashboxAccountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN,'empty'=>['0'=>'-- Seleccione Caja --']]);
          if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
            echo $this->Form->input('credit_accounting_code_id',['label'=>'Cuenta Contable HABER (OTROS INGRESOS)','options'=>$accountingCodes,'empty'=>['0'=>'-- Seleccione Cuenta HABER --']]);
          }
          if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
            //echo $this->Form->input('bool_retention',['type'=>'checkbox','label'=>'Retención']);
            //echo $this->Form->input('retention_number',['label'=>'Número Retención']);
            echo "<div class='righttop'>";
              echo "<h4>Desglose</h4>";
              //echo $this->Form->input('amount',['label'=>'Abono para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$']);
              echo $this->Form->input('amount_cuentas_por_cobrar',['label'=>'Total para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$']);
              //echo "<br/>";
              echo $this->Form->input('amount_increment',['label'=>'Monto Incremento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$']);
              echo $this->Form->input('amount_discount',['label'=>'Monto Descuento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$']);
              echo $this->Form->input('amount_difference_exchange_rate',['label'=>'Monto Cambiario','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$']);
              //echo "<br/>";					
              echo $this->Form->input('amount_total_payment',['label'=>'Monto Total Pagado Efectivo','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>']);
              //echo $this->Form->input('amount_retention_paid',['label'=>'Retención para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>']);
            echo "</div>";
          }
        }  
        echo $this->Form->submit(__('Submit'));
      if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
        echo "<div id='invoicesForClient'>";
        echo "</div>";
      }
      echo "</fieldset>";
    echo $this->Form->end(); 
  }
?>
</div>