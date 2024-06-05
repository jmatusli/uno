<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  var exchangeRate=<?php echo $exchangeRate; ?>;
  var csCurrencyId=<?php echo CURRENCY_CS; ?>;
  var usdCurrencyId=<?php echo CURRENCY_USD; ?>;

  $('body').on('click','.recibosDolares',function(){	
    var operatorId=$(this).closest('tr').attr('operatorid');
    $(this).closest('tbody').find('tr.cashUSD[operatorid=\''+operatorId+'\']').removeClass('hidden');
	});

	$('body').on('click','.removerRecibosDolares',function(){	
		var thisRow=$(this).closest('tr');
    thisRow.addClass('hidden');
    thisRow.find('td.amount div input').val(0);
    
    var operatorId=$(this).closest('tr').attr('operatorid');
		var tableId=$(this).closest('table').attr('id');
    calculateOperatorTotal(operatorId,tableId);
	});
  
  $('body').on('click','.addClientRow',function(){	
    var operatorId=$(this).closest('tr').attr('operatorid');
    $(this).closest('tbody').find('tr.client[operatorid=\''+operatorId+'\'].hidden:first').removeClass('hidden');
    //$(this).closest('tbody').find('tr.client[operatorid=\''+operatorId+'\'].hidden:first button.addClientRow').removeClass('hidden');
	});

	$('body').on('click','.removeClientRow',function(){	
		var thisRow=$(this).closest('tr');
    thisRow.addClass('hidden');
    thisRow.find('td.amount div input').val(0);
    
    var operatorId=$(this).closest('tr').attr('operatorid');
		var tableId=$(this).closest('table').attr('id');
    calculateOperatorTotal(operatorId,tableId);
	});
  
  $('body').on('change','td.amount',function(){	
    var operatorId=$(this).closest('tr').attr('operatorid');
		var tableId=$(this).closest('table').attr('id');
    calculateOperatorTotal(operatorId,tableId);
	});
  $('body').on('change','td.currency',function(){	
    var operatorId=$(this).closest('tr').attr('operatorid');
		var tableId=$(this).closest('table').attr('id');
    calculateOperatorTotal(operatorId,tableId);
	});
  
  function calculateOperatorTotal(operatorId,tableId){
    var operatorAmount=0;
    var currentAmount=0;
    var currentCurrencyId=0;
    $('table#'+tableId).find('tr:not(.operatortotal)[operatorid=\''+operatorId+'\']').each(function(){
      currentAmount=parseFloat($(this).find('td.amount div input').val());
      currentCurrencyId=parseInt($(this).find('td.currency div select').val());
      if (currentCurrencyId==usdCurrencyId){
        currentAmount*=exchangeRate;
      }
      operatorAmount+=currentAmount;
    });
    $('table#'+tableId).find('tr.operatortotal[operatorid=\''+operatorId+'\'] td.amount span.amountright').text(operatorAmount);
		calculateTotals(tableId);
	}
  
  function calculateTotals(tableId){
    var shiftAmount=0;
    var currentAmount=0;
    $('table#'+tableId).find('tr.operatortotal:even td.amount span.amountright').each(function(){
      currentAmount=parseFloat($(this).text());
      shiftAmount+=currentAmount;
    });
    $('table#'+tableId).find('tr.shifttotal td.amount span.amountright').text(shiftAmount);
	}
  
  $('body').on('change','#chkEditingMode',function(){	
    if ($(this).is(':checked')){
      $('.amount.cash div input').removeAttr('readonly',false)
      $('#savePaymentReceipts').attr('disabled',false)
      $('.addClientRow').removeClass('hidden')
      $('.removeClientRow').removeClass('hidden')
      $('tr.cardCS td.client .openAddInvoiceDialog').removeClass('hidden')
      //$('tr.cardCS td.client .openViewInvoicesDialog').removeClass('hidden');
      
      $('tr.cash').removeClass('hidden')
      $('tr.bac').removeClass('hidden')
      $('tr.banpro').removeClass('hidden')
      $('tr.credit').removeClass('hidden')
    }
    else {
      $('.amount.cash div input').attr('readonly','readonly')
      $('#savePaymentReceipts').attr('disabled',true)
      $('.addClientRow').addClass('hidden')
      $('.removeClientRow').addClass('hidden')
      $('tr.cardCS td.client .openAddInvoiceDialog').addClass('hidden')
      //$('tr.cardCS td.client .openViewInvoicesDialog').addClass('hidden');
    <?php
      if ($filterPaymentModeId != 0){
        switch ($filterPaymentModeId){
          case PAYMENT_MODE_CASH:
            echo "$('tr.cash').removeClass('hidden');";
            echo "$('tr.bac').addClass('hidden');";
            echo "$('tr.banpro').addClass('hidden');";
            echo "$('tr.credit').addClass('hidden');";
            break;
          case PAYMENT_MODE_CARD_BAC:
            echo "$('tr.cash').addClass('hidden');";
            echo "$('tr.bac').removeClass('hidden');";
            echo "$('tr.banpro').addClass('hidden');";
            echo "$('tr.credit').addClass('hidden');";
            break;  
          case PAYMENT_MODE_CARD_BANPRO:
            echo "$('tr.cash').addClass('hidden');";
            echo "$('tr.bac').addClass('hidden');";
            echo "$('tr.banpro').removeClass('hidden');";
            echo "$('tr.credit').addClass('hidden');";
            break;  
          case PAYMENT_MODE_CREDIT:
            echo "$('tr.cash').addClass('hidden');";
            echo "$('tr.bac').addClass('hidden');";
            echo "$('tr.banpro').addClass('hidden');";
            echo "$('tr.credit').removeClass('hidden');";
            break;  
        }
        if ($filterCurrencyId == CURRENCY_CS){
          switch ($filterPaymentModeId){
            case PAYMENT_MODE_CASH:
              echo "$('tr.cashUSD').addClass('hidden');";
              break;
            case PAYMENT_MODE_CREDIT:
              echo "$('tr.creditUSD').addClass('hidden');";
              break;  
          }
        }
        elseif ($filterCurrencyId == CURRENCY_USD){
          switch ($filterPaymentModeId){
            case PAYMENT_MODE_CASH:
              echo "$('tr.cashCS').addClass('hidden');";
              break;
            case PAYMENT_MODE_CREDIT:
              echo "$('tr.creditCS').addClass('hidden');";
              break;  
          }
        }
      }
      elseif ($filterCurrencyId == CURRENCY_CS){
        echo "$('tr.cashCS').removeClass('hidden');";
        echo "$('tr.cashUSD').addClass('hidden');";
        echo "$('tr.bac').removeClass('hidden');";
        echo "$('tr.banpro').removeClass('hidden');";
        echo "$('tr.credit').addClass('hidden');";
        echo "$('tr.creditCS').removeClass('hidden');";
        
        
      }
      elseif ($filterCurrencyId == CURRENCY_USD){
        echo "$('tr.cashCS').addClass('hidden');";
        echo "$('tr.cashUSD').removeClass('hidden');";
        echo "$('tr.bac').addClass('hidden');";
        echo "$('tr.banpro').addClass('hidden');";
        echo "$('tr.credit').addClass('hidden');";
        echo "$('tr.creditUSD').removeClass('hidden');";
      }
    ?>
    }
    $('.client div select.clientid').trigger("change");
	});	

	$('#PaymentReceiptPaymentDateDay').change(function(){
		//updateExchangeRate();
	});	
	$('#PaymentReceiptPaymentDateMonth').change(function(){
		//updateExchangeRate();
	});	
	$('#PaymentReceiptPaymentDateYear').change(function(){
		//updateExchangeRate();
	});	
	function updateExchangeRate(){
		var receiptday=$('#PaymentReceiptPaymentDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#PaymentReceiptPaymentDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#PaymentReceiptPaymentDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear},
			cache: false,
			type: 'POST',
			success: function (result) {
				exchangerate=result;
			},
			error: function(e){
				alert(e.responseText);
				console.log(e);
			}
		});
	}
  
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToThree(num) {    
		return +(Math.round(num + "e+3")  + "e-3");
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
  
  function formatNumbers(){
		$("td.number span.number").each(function(){
      if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCurrencies(){
    $("td.amount span.amount.right").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
      $(this).number(true,2);
      if ($(this).parent().find('span.currency').hasClass('USDCurrency')){
        $(this).parent().find('span.currency').text("US$");
      }
      else {
        $(this).parent().find('span.currency').text(" C$");
      }
		});
	}
  
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
  
  $('body').on('change','.client div select.clientid',function(){	
		var thisTableCell=$(this).closest('td');
    var selectedClientId = $(this).val();
    $(this).closest('tr').attr('clientid',selectedClientId);
    var boolEditingMode= $('#chkEditingMode').is(':checked');
    if (selectedClientId == 0 || !boolEditingMode){
      thisTableCell.find('a.openAddInvoiceDialog').addClass('hidden');
      //thisTableCell.find('a.openViewInvoicesDialog').addClass('hidden');
    }
    else {
      thisTableCell.find('a.openAddInvoiceDialog').removeClass('hidden');
      //thisTableCell.find('a.openViewInvoicesDialog').removeClass('hidden');
    }
	});
  
  $('body').on('click','.openAddInvoiceDialog',function(){	
		var thisTableCell=$(this).closest('td');
    var thisRow=$(this).closest('tr');
    var selectedClientId = 0;
    if (thisRow.hasClass("client")){
      selectedClientId = thisRow.attr('clientid');
      thisTableCell.find('a.openAddInvoiceDialog').attr('data-client-id',selectedClientId);
    }
    $('#InvoiceClientId').val(selectedClientId);
    var selectedPaymentModeId=thisRow.find('td.paymentMode select').val();
    $('#InvoicePaymentModeId').val(selectedPaymentModeId);
    
    var invoiceDateDay = $('#InvoiceInvoiceDateDay').val();
    var invoiceDateMonth = $('#InvoiceInvoiceDateMonth').val();
    var invoiceDateYear = $('#InvoiceInvoiceDateYear').val();
    
    $('#InvoiceInvoiceCode').val('');
    
    $.ajax({
			url: '<?php echo $this->Html->url('/'); ?>invoices/getInvoiceCode/',
			data:{
        "paymentModeId":selectedPaymentModeId,
        "invoiceDateString":invoiceDateDay+invoiceDateMonth+invoiceDateYear,
      },
			cache: false,
			type: 'POST',
			success: function (newInvoiceCode) {
				$('#InvoiceInvoiceCode').val(newInvoiceCode);
        if (newInvoiceCode == ''){
          $('#InvoiceInvoiceCode').prop('readonly',false);  
        }
        else {
          $('#InvoiceInvoiceCode').prop('readonly',true);  
        }
			},
			error: function(e){
				alert(e.responseText);
				console.log(e);
			}
		});
    
    $('#InvoiceSubTotalPrice').val(0);
    $('#SenderCallingRowId').val(thisRow.attr('id'));
    $('#SaveInvoice').removeAttr('disabled');
	});
  
  $('body').on('click','#SaveInvoice',function(){
    var rowId=$('#SenderCallingRowId').val();
    var paymentReceiptRow = $('tr#'+rowId);
    
		var id=$('#InvoiceId').val();
    var enterpriseId=$('#PaymentReceiptEnterpriseId').val();
    var orderId=$('#InvoiceOrderId').val();
    var shiftId=paymentReceiptRow.attr('shiftid');
    var operatorId=paymentReceiptRow.attr('operatorid');
    var paymentModeId=$('#InvoicePaymentModeId').val();
    var clientId=paymentReceiptRow.attr('clientid');
    var invoiceCode=$('#InvoiceInvoiceCode').val();
    
    var invoiceDateDay = $('#InvoiceInvoiceDateDay').val();
    var invoiceDateMonth = $('#InvoiceInvoiceDateMonth').val();
    var invoiceDateYear = $('#InvoiceInvoiceDateYear').val();
    var dueDateDay = $('#InvoiceDueDateDay').val();
    var dueDateMonth = $('#InvoiceDueDateMonth').val();
    var dueDateYear = $('#InvoiceDueDateYear').val();
    var invoiceSubTotalPrice = $('#InvoiceSubTotalPrice').val();
    
    var rowId=$('#SenderCallingRowId').val();
    
    if (invoiceCode == ''){
      alert('Se debe indicar el número de la factura');
    }
    else if (isNaN(invoiceSubTotalPrice)){
      alert('El precio total de la factura tiene que ser un número positivo');
    }
    else if (invoiceSubTotalPrice == 0){
      alert('El precio total de la factura tiene que ser mayor que cero');
    }
    else {
      $('#SaveInvoice').attr('disabled', 'disabled');
      $('#addInvoice').modal('hide');
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>invoices/saveInvoice/',
        data:{
          "id":id,
          "enterpriseId":enterpriseId,
          "orderId":orderId,
          "shiftId":shiftId,
          "operatorId":operatorId,
          "paymentModeId":paymentModeId,
          "clientId":clientId,
          
          "invoiceCode":invoiceCode,
          "invoiceDateDay":invoiceDateDay,
          "invoiceDateMonth":invoiceDateMonth,
          "invoiceDateYear":invoiceDateYear,
          "dueDateDay":dueDateDay,
          "dueDateMonth":dueDateMonth,
          "dueDateYear":dueDateYear,
          
          "invoiceSubTotalPrice":invoiceSubTotalPrice,
        },
        cache: false,
        type: 'POST',
        success: function (result) {
          if (result == "ok"){
            addInvoiceInputs(rowId);
          }
          else {
            alert(result);
          }
        },
        error: function(e){
          console.log(e);
          alert(e.responseText);
        }
      });
    }
	});
  
  function addInvoiceInputs(rowId){
    var paymentReceiptRow = $('tr#'+rowId);
    var rowCounter=0;
    var rowCounterValue = paymentReceiptRow.attr('rowcounter');
    if (typeof rowCounterValue !== typeof undefined && rowCounterValue !== false){
      rowCounter=rowCounterValue;
    }
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>invoices/invoiceInputsForPaymentReceipt/',
      data:{
        "orderId":$('#InvoiceOrderId').val(),
        "shiftId":paymentReceiptRow.attr('shiftid'),
        "operatorId":paymentReceiptRow.attr('operatorid'),
        "paymentModeId":paymentReceiptRow.attr('paymentmodeid'),
        "clientId":$('#InvoiceClientId').val(),
        "rowCounter":rowCounter,
      },
      
      cache: false,
      type: 'POST',
      success: function (invoiceInputs) {
      
        paymentReceiptRow.find('td.client div.invoiceInfo').html(invoiceInputs);
        setAmount(rowId); 
        
      },
      error: function(e){
        console.log(e);
        alert(e.responseText);
      }
    });
  }
  
  function setAmount(rowId){
    var paymentReceiptRow = $('tr#'+rowId);
    var invoiceTotalAmount=paymentReceiptRow.find('td.client div.invoiceInfo input.invoiceTotalAmount').val();
    paymentReceiptRow.find('td.amount div input').val(invoiceTotalAmount);
    paymentReceiptRow.find('td.amount div input').trigger('change');
  }
  
  $('body').on('click','.openViewInvoicesDialog',function(){	
		var thisTableCell=$(this).closest('td');
    var paymentReceiptRow = $(this).closest('tr');
    var editingMode=$('#chkEditingMode').is(':checked')?1:0;
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>invoices/invoicesTableForPaymentReceipt/',
      data:{
        "orderId":$('#InvoiceOrderId').val(),
        "shiftId":paymentReceiptRow.attr('shiftid'),
        "operatorId":paymentReceiptRow.attr('operatorid'),
        "paymentModeId":paymentReceiptRow.attr('paymentmodeid'),
        "clientId":paymentReceiptRow.attr('clientid'),
        "callingRowId":paymentReceiptRow.attr('id'),
        "editingMode":editingMode,
      },
      
      cache: false,
      type: 'POST',
      success: function (invoicesTable) {
        $('#invoiceTable').html(invoicesTable);
      },
      error: function(e){
        console.log(e);
        alert(e.responseText);
      }
    });
	});
  
  $('body').on('click','.eliminarFactura',function(){	
		var callingRowId=$(this).closest('tr').attr('callingRowId');
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>invoices/deleteInvoiceFromPaymentReceipt/',
      data:{
        "invoiceId":$(this).attr('invoiceId'),
      },
      cache: false,
      type: 'POST',
      success: function (result) {
        if (result == 'ok'){
          alert("Se eliminó la factura");
          //$('#viewInvoices').modal('hide');
          addInvoiceInputs(callingRowId);
          $('#'+callingRowId).find('.openViewInvoicesDialog').trigger('click');
        }
        else {
          alert(result);
        }
      },
      error: function(e){
        console.log(e);
        alert(e.responseText);
      }
    });
	});
  
	$(document).ready(function(){
    formatNumbers();
		formatCurrencies();
		$('#PaymentReceiptPaymentDateHour').val('08');
		$('#PaymentReceiptPaymentDateMin').val('00');
		$('#PaymentReceiptPaymentDateMeridian').val('am');
    
    $('.currentMeasurement div input').each(function(){	
      var currentMeasurement=parseFloat($(this).val());
      var hoseId=$(this).closest('td').attr('hoseid');
      var previousMeasurement=parseFloat($(this).closest('table').find('tr.previousMeasurements td[hoseid=\''+hoseId+'\'] div input').val());
      var measurementDifference=roundToThree(currentMeasurement-previousMeasurement);
      $(this).closest('table').find('tr.measurementDifferences td[hoseid=\''+hoseId+'\'] div input').val(measurementDifference);
      var fuelRegistered=parseFloat($(this).closest('table').find('tr.fuelTotals td[hoseid=\''+hoseId+'\'] div input').val());
      var deviation=roundToThree(measurementDifference-fuelRegistered);
      $(this).closest('table').find('tr.deviations td[hoseid=\''+hoseId+'\'] div input').val(deviation);
    });
    $('.islandTable').each(function(){	
      var tableId=$(this).attr('id');
      calculateTotalCurrentMeasurements(tableId);
      calculateTotalMeasurementDifferences(tableId);
      calculateTotalDeviations(tableId);    
    });
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
		$('#saving').addClass('hidden');
    
    $('#savePaymentReceipts').removeAttr('disabled');
    $('#savePaymentReceipts').css("background-color","#62af56");
    $('.client div select.clientid').trigger("change");
    $('#chkEditingMode').trigger("change");
	});
  
  $('body').on('click','#savePaymentReceipts',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#PaymentReceiptRegistrarRecibosForm',function(e){	
    if($("#savePaymentReceipts").data('clicked'))
    {
      $('#savePaymentReceipts').attr('disabled', 'disabled');
      $("#mainform").fadeOut();
      $("#saving").removeClass('hidden');
      $("#saving").fadeIn();
      var opts = {
          lines: 12, // The number of lines to draw
          length: 7, // The length of each line
          width: 4, // The line thickness
          radius: 10, // The radius of the inner circle
          color: '#000', // #rgb or #rrggbb
          speed: 1, // Rounds per second
          trail: 60, // Afterglow percentage
          shadow: false, // Whether to render a shadow
          hwaccel: false // Whether to use hardware acceleration
      };
      var target = document.getElementById('saving');
      var spinner = new Spinner(opts).spin(target);
    }
    
    return true;
  });
  
</script>

<div class="orders form sales fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando los recibos...</p>";
    echo "</div>";
  echo "</div>";
  
  $fuelTableHead='';
  $fuelTableHead.='<thead>';
    $fuelTableHead.='<tr>';
      $fuelTableHead.='<th style="min-width:130px;"></th>';
      foreach ($fuelTotals as $fuelId => $fuelData){
        $fuelTableHead.='<th class="centered">'.$this->Html->link($fuelData['name'],['controller'=>'products','action'=>'view',$fuelId]).'</th>';
      }
      $fuelTableHead.='<th class="centered">Total</th>';
    $fuelTableHead.='</tr>';
  $fuelTableHead.='</thead>';
  
  $totalGallons=0;
  $fuelQuantityRow='';
  $fuelQuantityRow.='<tr>';
    $fuelQuantityRow.='<td>Galones</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $totalGallons+=$fuelData['total_gallons'];
      $fuelQuantityRow.='<td class="centered number"><span class="number">'.$fuelData['total_gallons'].'</span></td>';
    }
    $fuelQuantityRow.='<td class="centered number"><span class="number">'.$totalGallons.'</span></td>';
  $fuelQuantityRow.='</tr>';
  
  $totalPrice=0;
  $fuelTotalPriceRow='';
  $fuelTotalPriceRow.='<tr>';
    $fuelTotalPriceRow.='<td>Precio Total</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $totalPrice+=$fuelData['total_price'];
      $fuelTotalPriceRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$fuelData['total_price'].'</span></td>';
    }
    $fuelTotalPriceRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$totalPrice.'</span></td>';
  $fuelTotalPriceRow.='</tr>';
  
  $fuelUnitPriceRow='';
  $fuelUnitPriceRow.='<tr>';
    $fuelUnitPriceRow.='<td>Precio por Litro</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $fuelUnitPriceRow.='<td class="amount"><span class="currency">C$</span><span class="amount right">'.$fuelData['unit_price'].'</span></td>';
    }
    //$fuelUnitPriceRow.='<td>'.($totalPrice > 0?$totalGallons/$totalPrice:0.'</td>';
    $fuelUnitPriceRow.='<td class="amount"></td>';
  $fuelUnitPriceRow.='</tr>';
  
  $fuelTableRows=$fuelQuantityRow.$fuelUnitPriceRow.$fuelTotalPriceRow;
  $fuelTableBody='<tbody>'.$fuelTableRows.'</tbody>';
  $fuelOverviewTable='<table>'.$fuelTableHead.$fuelTableBody.'</table>';
  
  $paymentModeByShiftTableHead='';
  $paymentModeByShiftTableHead.='<thead>';
    $paymentModeByShiftTableHead.='<tr>';
      $paymentModeByShiftTableHead.='<th style="min-width:250px;width:250px;max-width:250px;"></th>';
      foreach ($shiftList as $shiftId => $shiftName){
        $paymentModeByShiftTableHead.='<th class="centered">'.$this->Html->link($shiftName,['controller'=>'shifts','action'=>'detalle',$shiftId]).'</th>';
      }
      $paymentModeByShiftTableHead.='<th class="centered">Total</th>';
    $paymentModeByShiftTableHead.='</tr>';
  $paymentModeByShiftTableHead.='</thead>';
  
  
  $shiftTotals=[];
  $grandTotal=0;
  foreach (array_keys($shiftList) as $shiftId){
    $shiftTotals[$shiftId]['total']=0;
  }
  $paymentModeByShiftTableRows='';
  foreach($paymentModeTotals['PaymentMode'] as $paymentModeId=>$paymentModeData){
    //pr($paymentModeData);
    foreach ($paymentModeData['Currency'] as $currencyId=>$currencyPaymentData){
      if ($paymentModeId == PAYMENT_MODE_CASH || $currencyId == CURRENCY_CS){      
        $paymentModeRow='';
        $paymentModeRow.='<tr>';
          $grandTotal+=$currencyPaymentData['total'];
          $paymentModeRow.='<td>'.$this->Html->link($paymentModes[$paymentModeId].($paymentModeId == PAYMENT_MODE_CASH?(" ".$currencies[$currencyId]):""),['controller'=>'paymentModes','action'=>'detalle',$paymentModeId]).'</td>';
          foreach (array_keys($shiftList) as $shiftId){
            if ($currencyId == CURRENCY_USD){      
              $shiftTotals[$shiftId]['total']+=$paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']*$exchangeRate;
            }
            else {
              $shiftTotals[$shiftId]['total']+=$paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total'];
            }
            $paymentModeRow.='<td class="centered amount"><span style="display:inline-block;min-width:35px;width:35px;text-align:right;" class="currency '.($currencyId==CURRENCY_USD?" USDCurrency":"CSCurrency").'">'.($currencyId==CURRENCY_USD?"US$":"C$").'</span><span class="amount right">'.$paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total'].'</span></td>';
          }
          $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$currencyPaymentData['total'].'</span></td>';
        $paymentModeRow.='</tr>';
        $paymentModeByShiftTableRows.=$paymentModeRow;
      }
    }
  }
  
  foreach($clientPaymentTotals['Client'] as $clientId=>$clientData){
    //pr($clientData);
    $paymentModeRow='';
    $paymentModeRow.='<tr>';
      $grandTotal+=$clientData['total'];
      $paymentModeRow.='<td>'.$this->Html->link($fullClients[$clientId],['controller'=>'thirdParties','action'=>'verCliente',$clientId]).'</td>';
      foreach (array_keys($shiftList) as $shiftId){
        if (array_key_exists($clientId,$clientPaymentTotals['Shift'][$shiftId]['Client'])){
        $shiftTotals[$shiftId]['total']+=$clientPaymentTotals['Shift'][$shiftId]['Client'][$clientId]['total'];
        $paymentModeRow.='<td class="centered amount"><span  style="display:inline-block;min-width:35px;width:35px;text-align:right;" class="currency">C$</span><span class="amount right">'.$clientPaymentTotals['Shift'][$shiftId]['Client'][$clientId]['total'].'</span></td>';
        }
        else {
          $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">0</span></td>';
        }
      }
      $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$clientData['total'].'</span></td>';
      
    $paymentModeRow.='</tr>';
    $paymentModeByShiftTableRows.=$paymentModeRow;
  }
  
  $totalRow='';
  $totalRow.='<tr>';
    $totalRow.='<td>TOTAL C$</td>';
    foreach (array_keys($shiftList) as $shiftId){
      $totalRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$shiftTotals[$shiftId]['total'].'</span></td>';
    }
    $totalRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$grandTotal.'</span></td>';
  $totalRow.='</tr>';
  
  
  $paymentModeByShiftTableBody='<tbody>'.$paymentModeByShiftTableRows.'</tbody>';
  $paymentModeByShiftOverviewTable='<table>'.$paymentModeByShiftTableHead.$paymentModeByShiftTableBody.'</table>';
  
  
  
  
	echo $this->Form->create('PaymentReceipt');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Recibos')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-12 col-lg-4'>";	
						echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
            echo $this->Form->input('payment_date',['label'=>__('Date'),'type'=>'date','dateFormat'=>'DMY','default'=>$paymentDate,'minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->input('Filter.payment_mode_id',['label'=>__('Filtrar por modo de pago'),'default'=>$filterPaymentModeId,'empty'=>[0=>'-- Todos modos de pago --']]);
            echo $this->Form->input('Filter.currency_id',['label'=>__('Filtrar por moneda'),'default'=>$filterCurrencyId,'empty'=>[0=>'-- Todas monedas --']]);
            echo $this->Form->Submit(__('Establecer fecha y filtros'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
            echo  $this->Form->input('comment',['type'=>'textarea','rows'=>5]);
          echo "</div>";
					echo "<div class='col-sm-12 col-lg-8'>";	
            if ($enterpriseId >0){
              echo '<h2>Resumen ventas por combustible</h2>';
              echo $fuelOverviewTable;
              
              echo '<h2>Resumen pagos por turno</h2>';
              echo $paymentModeByShiftOverviewTable;              
              
              $paymentDateTime=new DateTime($paymentDate);
              $fileName=$enterprises[$enterpriseId]."_ventas_combustible_recibos_turno_".$paymentDateTime->format('dmY');
              echo $this->Html->link('Pdf ventas combustibles y recibos por turno',['action'=>'pdfVentasCombustibleRecibosTurno','ext'=>'pdf',$paymentDate,$enterpriseId,$fileName],['class'=>'btn btn-primary','target'=>'_blank']);
            }
            
            
            
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
      if ($enterpriseId > 0){
        echo "<div class='row'>";  
          echo "<h3>".__('Recibos por turno y por operador')."</h3>";
          
          if ($boolEditingToggleVisible){  
            echo '<div>'; 
              echo "<span>Editar recibos</span>";
              echo "<label class='switch'>";
                echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
                echo "<span class='slider round'></span>";
              echo "</label>";
            echo '</div>';  
          }
          else {
            echo '<div class="hidden">'; 
              echo "<label class='switch'>";
                echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
                echo "<span class='slider round'></span>";
              echo "</label>";
            echo '</div>';  
          }
          //pr($requestShifts);
          foreach ($shifts as $shift){
            $shiftTotalByReceipt=0;
            //pr($shift);
            echo "<div class='col-sm-12 col-xl-4' style='padding:5px;'>";
              echo "<h3>".$this->Html->Link($shift['Shift']['name'],['controller'=>'shifts','action'=>'detalle',$shift['Shift']['id']])."</h3>";
              $shiftTable="";
              $shiftTable.="<table id='".str_replace(' ','_',$shift['Shift']['name'])."' class='shiftTable'>";
                $shiftTableHeader="";
                $shiftTableHeader.="<thead>";
                  $shiftTableHeader.="<tr>";
                    $shiftTableHeader.="<th>Operador</th>";
                    $shiftTableHeader.="<th>Tipo Pago</th>";
                    $shiftTableHeader.="<th>Cliente</th>";
                    $shiftTableHeader.="<th>Monto</th>";
                    $shiftTableHeader.="<th>Moneda</th>";
                    $shiftTableHeader.="<th class='centered' style='width:180px;'>Total</th>";
                  $shiftTableHeader.="</tr>";
                $shiftTableHeader.="</thead>";
                $shiftTable.=$shiftTableHeader;
                $shiftTableRows="";
                if (!empty($shift['Operator'])){
                  //pr($shift['Operator']);
                  foreach ($shift['Operator'] as $operatorId){     
                    //echo "operator id is ".$operatorId."<br/>";
                    if ($operatorId>0){
                      $startingIndexEmpty=0;
                      $csCredit=[];
                      if (!empty($requestShifts) && !empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CREDIT])){
                        //pr($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CREDIT]);
                        $csCredit=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CREDIT];
                        $startingIndexEmpty=count($csCredit);
                      }
                    
                      $operatorShiftTotalByReceipt=0;
                      
                      $csCashAmount=0;
                      $usdCashAmount=0;
                      $csBacCardAmount=0;
                      $csBanproCardAmount=0;
                      $bacInvoiceList=$banproInvoiceList='';
                      $bacInvoicesForPaymentReceipt=$banproInvoicesForPaymentReceipt=[];
                      $bacInvoiceTotalAmount=$banproInvoiceTotalAmount='';
                      if (!empty($requestShifts[$shift['Shift']['id']]['Operator'])){
                        //pr($requestShifts[$shift['Shift']['id']]['Operator']);
                        $csCashAmount=(array_key_exists(PAYMENT_MODE_CASH,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'])?
                          (array_key_exists(CURRENCY_CS,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH]['Currency'])?
                            $requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH]['Currency'][CURRENCY_CS]:0):0);
                        $usdCashAmount=(array_key_exists(PAYMENT_MODE_CASH,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'])?
                          (array_key_exists(CURRENCY_USD,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH]['Currency'])?
                            $requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH]['Currency'][CURRENCY_USD]:0):0);
                        if (array_key_exists(PAYMENT_MODE_CARD_BAC,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'])){
                          if (array_key_exists(CURRENCY_CS,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['Currency'])){
                            $csBacCardAmount=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['Currency'][CURRENCY_CS]; 
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['invoice_list'])){
                               $bacInvoiceList=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['invoice_list'];
                            }
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['invoice_total_amount'])){
                              $bacInvoiceTotalAmount=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['invoice_total_amount'];
                            }
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['Invoice'])){
                              foreach($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BAC]['InvoiceData']['Invoice'] as $invoice)
                              $bacInvoicesForPaymentReceipt[]['id']=$invoice['id'];
                            }                          
                          }
                        }
                        if (array_key_exists(PAYMENT_MODE_CARD_BANPRO,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'])){
                          if (array_key_exists(CURRENCY_CS,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['Currency'])){
                            $csBanproCardAmount=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['Currency'][CURRENCY_CS];
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['invoice_list'])){
                              $banproInvoiceList=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['invoice_list'];
                            }
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['invoice_total_amount'])){
                              $banproInvoiceTotalAmount=$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['invoice_total_amount'];
                            }
                            if (!empty($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['Invoice'])){
                              foreach($requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CARD_BANPRO]['InvoiceData']['Invoice'] as $invoice)
                              $banproInvoicesForPaymentReceipt[]['id']=$invoice['id'];
                            }  
                          }
                        }  
                      }
                      
                      $operatorShiftTotalByReceipt+=$csCashAmount;
                      
                      $operatorShiftTotalByReceipt+=($exchangeRate*$usdCashAmount);
                      $operatorShiftTotalByReceipt+=$csBacCardAmount;
                      $operatorShiftTotalByReceipt+=$csBanproCardAmount;
                      //pr($csCredit);
                      if (count($csCredit)>0){
                        for ($c=0;$c<count($csCredit);$c++){
                          //pr($csCredit[$c]);
                          $operatorShiftTotalByReceipt+=empty($requestShifts)?0:$csCredit[$c]['payment_amount'];
                        }
                      }
                      $shiftTotalByReceipt+=$operatorShiftTotalByReceipt;
                     
                      $operatorTableRows="";
                    
                      $csCashRow="";
                      $csCashRow.="<tr class='cash cashCS' operatorid='".$operatorId."'>";
                        $csCashRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.0.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                        $csCashRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.0.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CASH,'class'=>'fixed'])."</td>";
                        $csCashRow.="<td class='client'>-</td>";
                        //echo "operator id is ".$operatorId."<br/>";
                        //pr($requestShifts[$shift['Shift']['id']]['Operator']);
                        $csCashRow.="<td class='amount cash'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.0.payment_amount',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>$csCashAmount,
                          'readonly'=>($boolEditingMode?false:'readonly')
                        ])."</td>";
                        $csCashRow.="<td class='currency'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.0.currency_id',['label'=>false,'value'=>CURRENCY_CS,'class'=>'fixed'])."</td>";
                        //$csCashRow.="<td class='total'><button type='button' class='recibosDolares' style='width:180px;'>".__('Registrar Recibos de Efectivo en US$')."</button></td>";                  
                        $csCashRow.="<td class='total'></td>";                  
                      $csCashRow.="</tr>";
                      $operatorTableRows.=$csCashRow;
                      
                      $usdCashRow="";
                      //$usdCashRow.="<tr class='cashUSD".(empty($requestShifts[$shift['Shift']['id']]['Operator'])?
                      //  "":(array_key_exists(PAYMENT_MODE_CASH,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode']) && array_key_exists(CURRENCY_USD,$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH]) && $requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['PaymentMode'][PAYMENT_MODE_CASH][CURRENCY_USD]>0?" hidden":""))."' operatorid='".$operatorId."'>";
                      $usdCashRow.="<tr class='cash cashUSD' operatorid='".$operatorId."'>";  
                        $usdCashRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.1.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                        $usdCashRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.1.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CASH,'class'=>'fixed'])."</td>";
                        $usdCashRow.="<td class='client'>-</td>";
                        $usdCashRow.="<td class='amount cash'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.1.payment_amount',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>$usdCashAmount,
                          'readonly'=>($boolEditingMode?false:'readonly')
                        ])."</td>";
                        $usdCashRow.="<td class='currency'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.1.currency_id',['label'=>false,'value'=>CURRENCY_USD,'class'=>'fixed'])."</td>";
                        //$usdCashRow.="<td class='total'><button type='button' class='removerRecibosDolares'>".__('Remover Recibos de Efectivo en US$')."</button></td>";                  
                        $usdCashRow.="<td class='total'></td>";                  
                      $usdCashRow.="</tr>";
                      $operatorTableRows.=$usdCashRow;
                      
                      $csBacCardRow="";
                      $csBacCardRow.='<tr class="bac cardCS" operatorid="'.$operatorId.'" shiftid="'.$shift['Shift']['id'].'" paymentmodeid="'.PAYMENT_MODE_CARD_BAC.'"  id="'.($shift['Shift']['id'].'_'.$operatorId.'_BAC').'"  rowcounter="2"  clientid="0">';
                        $csBacCardRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                        $csBacCardRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CARD_BAC,'class'=>'fixed'])."</td>";
                        $csBacCardRow.='<td class="client">';
                          $csBacCardRow.='<a href="#addInvoice" class="openAddInvoiceDialog hidden" role="button" data-client-id="0" data-toggle="modal"><span class="glyphicon glyphicon-plus"></span></a>';
                          $csBacCardRow.='<div style="display:inline-block;" class="invoiceInfo">';
                            $csBacCardRow.='<span class="invoiceSpan">'.$bacInvoiceList.'</span>';
                            if (!empty($bacInvoicesForPaymentReceipt)){
                              //pr($bacInvoicesForPaymentReceipt);
                              $iCounter=0;
                              foreach ($bacInvoicesForPaymentReceipt as $paymentReceiptInvoice){
                                $csBacCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.InvoiceData.Invoice.'.$iCounter.'.id',['value'=>$paymentReceiptInvoice['id'],'type'=>'hidden','class'=>'invoice']);
                                $iCounter++;
                              }
                            }
                            $csBacCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.InvoiceData.invoice_list',['value'=>$bacInvoiceList,'type'=>'hidden','class'=>'invoiceList']);
                            $csBacCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.InvoiceData.invoice_total_amount',['value'=>$bacInvoiceTotalAmount,'type'=>'hidden','class'=>'invoiceTotalAmount']);  
          
                          $csBacCardRow.='</div>';
                          $csBacCardRow.='<a href="#viewInvoices" class="openViewInvoicesDialog" role="button" data-toggle="modal"><span class="glyphicon glyphicon-eye-open"></span></a>';
                        $csBacCardRow.='</td>';
                        $csBacCardRow.="<td class='amount'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.payment_amount',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>$csBacCardAmount,
                          //'readonly'=>($boolEditingMode?false:'readonly'),
                          'readonly'=>true,
                        ])."</td>";
                        $csBacCardRow.="<td class='currency'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.2.currency_id',['label'=>false,'value'=>CURRENCY_CS,'class'=>'fixed'])."</td>";
                        $csBacCardRow.="<td class='total'> </td>";                  
                      $csBacCardRow.="</tr>";
                      $operatorTableRows.=$csBacCardRow;
                      
                      $csBanproCardRow="";
                      $csBanproCardRow.='<tr class="banpro cardCS" operatorid="'.$operatorId.'" shiftid="'.$shift['Shift']['id'].'" paymentmodeid="'.PAYMENT_MODE_CARD_BANPRO.'" id="'.($shift['Shift']['id'].'_'.$operatorId.'_Banpro').'"  rowcounter="3" clientid="0">';
                        $csBanproCardRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                        $csBanproCardRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CARD_BANPRO,'class'=>'fixed'])."</td>";
                        $csBanproCardRow.='<td class="client">';
                          $csBanproCardRow.='<a href="#addInvoice" class="openAddInvoiceDialog hidden" role="button" data-client-id="0" data-toggle="modal"><span class="glyphicon glyphicon-plus"></span></a>';
                          $csBanproCardRow.='<div style="display:inline-block;" class="invoiceInfo">';
                            $csBanproCardRow.='<span class="invoiceSpan">'.$banproInvoiceList.'</span>';
                            if (!empty($banproInvoicesForPaymentReceipt)){
                              //pr($banproInvoicesForPaymentReceipt);
                              $iCounter=0;
                              foreach ($banproInvoicesForPaymentReceipt as $paymentReceiptInvoice){
                                $csBanproCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.InvoiceData.Invoice.'.$iCounter.'.id',['value'=>$paymentReceiptInvoice['id'],'type'=>'hidden','class'=>'invoice']);
                                $iCounter++;
                              }
                            }
                            $csBanproCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.InvoiceData.invoice_list',['value'=>$banproInvoiceList,'type'=>'hidden','class'=>'invoiceList']);
                            $csBanproCardRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.InvoiceData.invoice_total_amount',['value'=>$banproInvoiceTotalAmount,'type'=>'hidden','class'=>'invoiceTotalAmount']);  
          
                          $csBanproCardRow.='</div>';
                          $csBanproCardRow.='<a href="#viewInvoices" class="openViewInvoicesDialog" role="button" data-toggle="modal"><span class="glyphicon glyphicon-eye-open"></span></a>';
                        $csBanproCardRow.='</td>';
                        $csBanproCardRow.="<td class='amount'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.payment_amount',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>$csBanproCardAmount,
                          //'readonly'=>($boolEditingMode?false:'readonly'),
                          'readonly'=>'readonly',
                        ])."</td>";
                        $csBanproCardRow.="<td class='currency'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.PaymentReceipt.3.currency_id',['label'=>false,'value'=>CURRENCY_CS,'class'=>'fixed'])."</td>";
                        $csBanproCardRow.="<td class='total'> </td>";                  
                      $csBanproCardRow.="</tr>";
                      $operatorTableRows.=$csBanproCardRow;
                      
                      //pr($csCredit);
                      
                      if (count($csCredit)>0){
                         for ($c=0;$c<count($csCredit);$c++){
                          //echo "c is ".$c."<br/>";
                          $clientRow="";
                          $clientRow.='<tr class="client credit creditCS" operatorid="'.$operatorId.'" shiftid="'.$shift['Shift']['id'].'" paymentmodeid="'.PAYMENT_MODE_CREDIT.'"  id="'.($shift['Shift']['id'].'_'.$operatorId.'_Credit_'.$c).'" rowcounter="'.$c.'" clientid="'.(empty($requestShifts)?0:$csCredit[$c]['client_id']).'">';
                            $clientRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                            $clientRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CREDIT,'class'=>'fixed'])."</td>";
                            $clientRow.='<td class="client">';
                              $clientRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.client_id',[
                                'label'=>false,
                                'class'=>'clientid',
                                'value'=>(empty($requestShifts)?0:$csCredit[$c]['client_id']),
                                'empty'=>[0=>'-- Seleccione Cliente --'],
                                //'readonly'=>($boolEditingMode?false:'readonly'),
                                'readonly'=>'readonly',
                                'div'=>['style'=>'width:250px;display:inline-block;'],
                              ]);
                              $clientRow.='<a href="#addInvoice" class="openAddInvoiceDialog hidden" role="button" data-client-id="0" data-toggle="modal"><span class="glyphicon glyphicon-plus"></span></a>';
                              $clientRow.='<div style="display:inline-block;" class="invoiceInfo">';
                                $clientRow.='<span class="invoiceSpan">'.$csCredit[$c]['InvoiceData']['invoice_list'].'</span>';
                                if (!empty($csCredit[$c]['InvoiceData']['Invoice'])){
                                  //pr($csCredit[$c]['InvoiceData']['Invoice']);
                                  $iCounter=0;
                                  foreach ($csCredit[$c]['InvoiceData']['Invoice'] as $paymentReceiptInvoice){
                                    $clientRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.InvoiceData.Invoice.'.$iCounter.'.id',['value'=>$paymentReceiptInvoice['id'],'type'=>'hidden','class'=>'invoice']);
                                    $iCounter++;
                                  }
                                }
                                $clientRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.InvoiceData.invoice_list',['value'=>$csCredit[$c]['InvoiceData']['invoice_list'],'type'=>'hidden','class'=>'invoiceList']);
                                $clientRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.InvoiceData.invoice_total_amount',['value'=>$csCredit[$c]['InvoiceData']['invoice_total_amount'],'type'=>'hidden','class'=>'invoiceTotalAmount']);  
                              $clientRow.='</div>';
                              $clientRow.='<a href="#viewInvoices" class="openViewInvoicesDialog" role="button" data-toggle="modal"><span class="glyphicon glyphicon-eye-open"></span></a>';
                            $clientRow.='</td>';
                            $clientRow.="<td class='amount'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.payment_amount',[
                              'label'=>false,
                              'type'=>'decimal',
                              'value'=>(empty($requestShifts)?0:$csCredit[$c]['payment_amount']),
                              'readonly'=>($boolEditingMode?false:'readonly'),
                              'readonly'=>'readonly',
                            ])."</td>";
                            $clientRow.="<td class='currency'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.currency_id',['label'=>false,'value'=>CURRENCY_CS,'class'=>'fixed'])."</td>";
                            $clientRow.="<td class='total'>";
                              //$clientRow.='<a href="#addInvoice" role="button" class="btn btn-large btn-primary" data-toggle="modal">Crear Factura</a>';
                              $clientRow.="<button type='button' class='addClientRow' >".__('Más Compras de Crédito')."</button>";
                              $clientRow.="<button type='button' class='removeClientRow ".($c!=0?"":"hidden")."' >".__('Remover Compra de Crédito')."</button>";
                            $clientRow.="</td>";   
                            //$clientRow.='<td class="productactions">';
                              //$clientRow.='<button class="removeItem" type="button"><span class="glyphicon glyphicon-remove"></span></button>';
                              //$clientRow.=$this->Html->link('<span class="glyphicon glyphicon-eye-open"></span>', ['controller' => 'products', 'action' => 'view',$requestProducts[$i]['ProviderQuotationProduct']['product_id']],['escape'=>false,'class'=>'productview','target'=>'_blank']);
                              //$clientRow.='<button class="addItem" type="button"><span class="glyphicon glyphicon-plus"></span></button>';
                            //$clientRow.='</td>';                          
                          $clientRow.='</tr>';
                          $operatorTableRows.=$clientRow;
                        }
                      }
                      if ($startingIndexEmpty<10){
                        for ($c=$startingIndexEmpty;$c<10;$c++){
                          $clientRow="";
                          if ($c==$startingIndexEmpty){
                            $clientRow.='<tr class="client credit" operatorid="'.$operatorId.'" shiftid="'.$shift['Shift']['id'].'" paymentmodeid="'.PAYMENT_MODE_CREDIT.'" id="'.($shift['Shift']['id'].'_'.$operatorId.'_Credit_'.$c).'" rowcounter="'.$c.'">';
                          }
                          else {
                            $clientRow.='<tr class="client hidden" operatorid="'.$operatorId.'" shiftid="'.$shift['Shift']['id'].'" paymentmodeid="'.PAYMENT_MODE_CREDIT.'" id="'.($shift['Shift']['id'].'_'.$operatorId.'_Credit_'.$c).'" rowcounter="'.$c.'">';
                          }
                            $clientRow.="<td class='operator'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.operator_id',['label'=>false,'value'=>$operatorId,'class'=>'fixed'])."</td>";
                            $clientRow.="<td class='paymentMode'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.payment_mode_id',['label'=>false,'value'=>PAYMENT_MODE_CREDIT,'class'=>'fixed'])."</td>";
                            
                            $clientRow.='<td class="client">';
                              $clientRow.=$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.client_id',[
                                'label'=>false,
                                'class'=>'clientid',
                                'value'=>(empty($requestShifts)?0:$requestShifts[$shift['Shift']['id']]),
                                'empty'=>[0=>'-- Seleccione Cliente --'],
                                //'readonly'=>($boolEditingMode?false:'readonly'),
                                'readonly'=>'readonly',
                                'div'=>['style'=>'width:250px;display:inline-block;'],
                              ]);
                              $clientRow.='<a href="#addInvoice" class="openAddInvoiceDialog hidden" role="button" data-client-id="0" data-toggle="modal"><span class="glyphicon glyphicon-plus"></span></a>';
                              $clientRow.='<div style="display:inline-block;" class="invoiceInfo">';
                              $clientRow.='</div>';
                              $clientRow.='<a href="#viewInvoices" class="openViewInvoicesDialog" role="button" data-toggle="modal"><span class="glyphicon glyphicon-eye-open"></span></a>';
                            $clientRow.='</td>';
                            
                            //$clientRow.="<td class='amount'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.payment_amount',['label'=>false,'type'=>'decimal','value'=>0,'readonly'=>($boolEditingMode?false:'readonly')])."</td>";
                            
                            $clientRow.="<td class='amount'>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.payment_amount',['label'=>false,'type'=>'decimal','value'=>0,'readonly'=>'readonly'])."</td>";
                            $clientRow.="<td class='currency'></span>".$this->Form->input('Shift.'.$shift['Shift']['id'].'.Operator.'.$operatorId.'.Credit.'.$c.'.currency_id',['label'=>false,'value'=>CURRENCY_CS,'class'=>'fixed'])."</td>";
                            $clientRow.="<td class='total'>";
                              $clientRow.="<button type='button' class='addClientRow' >".__('Más Compras de Crédito')."</button>";
                              $clientRow.="<button type='button' class='removeClientRow ".($c!=0?"":"hidden")."' >".__('Remover Compras de Crédito')."</button>";
                            $clientRow.="</td>";                  
                          $clientRow.="</tr>";
                          $operatorTableRows.=$clientRow;
                        }
                      }
                      
                      $operatorTotalRowCS="";
                      $operatorTotalRowCS.="<tr class='totalrow green operatortotal' operatorid='".$operatorId."'>";
                        $operatorTotalRowCS.="<td>Total ".($operatorId>0?$operators[$operatorId]:"")."</td>";
                        $operatorTotalRowCS.="<td> </td>";
                        $operatorTotalRowCS.="<td> </td>";
                        $operatorTotalRowCS.="<td class='amount currency'><span class='currency'></span><span class='amountright'>".$operatorShiftTotalByReceipt."</span></td>";
                        $operatorTotalRowCS.="<td>C$</td>";
                        $operatorTotalRowCS.="<td class='currency'><span>Total vendido ".($operatorId>0?$operators[$operatorId]:"")."</span><span class='amountright'>".(array_key_exists($operatorId,$requestShifts[$shift['Shift']['id']]['Operator'])?$requestShifts[$shift['Shift']['id']]['Operator'][$operatorId]['total_income']:0)."</span></td>";
                      $operatorTotalRowCS.="</tr>";
                      $operatorTableRows=$operatorTotalRowCS.$operatorTableRows.$operatorTotalRowCS;
                      $shiftTableRows.=$operatorTableRows;
                    }
                  }             
                }
               
                $shiftTotalRowCS="";
                $shiftTotalRowCS.="<tr class='totalrow shifttotal'>";
                  $shiftTotalRowCS.="<td>Total ".$shift['Shift']['name']."</td>";
                  $shiftTotalRowCS.="<td> </td>";
                  $shiftTotalRowCS.="<td> </td>";
                  $shiftTotalRowCS.="<td class='amount currency'><span class='currency'></span><span class='amountright'>".$shiftTotalByReceipt."</span></td>";
                  $shiftTotalRowCS.="<td>C$</td>";
                  $shiftTotalRowCS.="<td class='currency'><span>Total vendido</span><span class='amountright'>".(array_key_exists($shift['Shift']['id'],$requestShifts)?(array_key_exists('total_income',$requestShifts[$shift['Shift']['id']])?$requestShifts[$shift['Shift']['id']]['total_income']:0):0)."</span></td>";
                $shiftTotalRowCS.="</tr>";
                $shiftTableRows=$shiftTotalRowCS.$shiftTableRows.$shiftTotalRowCS;
                
                if (array_key_exists($shift['Shift']['id'],$requestShifts) && array_key_exists('total_calibration',$requestShifts[$shift['Shift']['id']]) && $requestShifts[$shift['Shift']['id']]['total_calibration']>0){
                  $shiftCalibrationTotalRowCS="";
                  $shiftCalibrationTotalRowCS.="<tr class='totalrow green shiftcalibrationtotal'>";
                    $shiftCalibrationTotalRowCS.="<td>Calibración ".$shift['Shift']['name']."</td>";
                    $shiftCalibrationTotalRowCS.="<td> </td>";
                    $shiftCalibrationTotalRowCS.="<td> </td>";
                    $shiftCalibrationTotalRowCS.="<td class='amount'></td>";
                    $shiftCalibrationTotalRowCS.="<td>C$</td>";
                    $shiftCalibrationTotalRowCS.="<td class='currency'><span>Total calibración</span><span class='amountright'>".(array_key_exists($shift['Shift']['id'],$requestShifts)?(array_key_exists('total_calibration',$requestShifts[$shift['Shift']['id']])?$requestShifts[$shift['Shift']['id']]['total_calibration']:0):0)."</span></td>";
                  $shiftCalibrationTotalRowCS.="</tr>";
                  $shiftTableRows=$shiftCalibrationTotalRowCS.$shiftTableRows.$shiftCalibrationTotalRowCS;
                  
                  $netTotalRowCS="";
                  $netTotalRowCS.="<tr class='totalrow shiftnettotal'>";
                    $netTotalRowCS.="<td>Neto ".$shift['Shift']['name']."</td>";
                    $netTotalRowCS.="<td> </td>";
                    $netTotalRowCS.="<td> </td>";
                    $netTotalRowCS.="<td class='currency amount'><span class='currency'></span><span class='amountright'>".$shiftTotalByReceipt."</span></td>";
                    $netTotalRowCS.="<td>C$</td>";
                    $netTotalRowCS.="<td class='currency'><span>Total vendido menos calibración</span><span class='amountright'>".($requestShifts[$shift['Shift']['id']]['total_income']-$requestShifts[$shift['Shift']['id']]['total_calibration'])."</span></td>";
                  $netTotalRowCS.="</tr>";
                  $shiftTableRows=$netTotalRowCS.$shiftTableRows.$netTotalRowCS;
                }
                $shiftTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$shiftTableRows."</tbody>";                  
                $shiftTable.=$shiftTableBody;
              $shiftTable.="</table>";
              echo $shiftTable;
            echo "</div>";  
          }  
          echo $this->Form->Submit(__('Grabar Recibos'),['id'=>'savePaymentReceipts','name'=>'savePaymentReceipts','style'=>'width:300px;','disabled'=>($boolEditingMode?false:true)]);
          
        echo "</div>"; 
      }        
    echo "</div>";
  echo "</fieldset>";  
  echo $this->Form->end();
    
  echo '<div id="addInvoice" class="modal fade">';
    echo '<div class="modal-dialog">';
      echo '<div class="modal-content">';
        echo '<div class="modal-header">';
          echo '<h4 class="modal-title">Añadir Factura</h4>';
        echo '</div>';
        echo '<div class="modal-body">';
          echo $this->Form->create("Invoice"); 
            echo '<fieldset>';
              echo $this->Form->input('id',['type'=>'hidden','default'=>0,]);
              echo $this->Form->input('order_id',['default'=>$orderId,'type'=>'hidden']);
              echo $this->Form->input('payment_mode_id');
              echo $this->Form->input('invoice_code',['type'=>'text']);
              echo $this->Form->input('client_id',['empty'=>[0=>'-- Seleccione Cliente --']]);
              echo $this->Form->input('invoice_date',['default'=>$paymentDate,'type'=>'date','dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')+1]);
              echo $this->Form->input('due_date',['default'=>$paymentDate,'type'=>'date','dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')+1]);
              echo $this->Form->input('sub_total_price',['type'=>'decimal']);
              
              echo $this->Form->input('Sender.calling_row_id',['type'=>'hidden']);
            echo '</fieldset>';
          echo $this->Form->end(); 	
        echo '</div>';
        echo '<div class="modal-footer">';
          echo '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
          echo '<button type="button" class="btn btn-primary" id="SaveInvoice">'.__("Guardar Factura").'</button>';
        echo '</div>';
      echo '</div>';
    echo '</div>';
  echo '</div>';
    
    echo '<div id="viewInvoices" class="modal fade">';
    echo '<div class="modal-dialog">';
      echo '<div class="modal-content">';
        echo '<div class="modal-header">';
          echo '<h4 class="modal-title">Ver Facturas</h4>';
        echo '</div>';
        echo '<div class="modal-body">';
          echo '<div id="invoiceTable"></div>';
        echo '</div>';
        echo '<div class="modal-footer">';
          echo '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
        echo '</div>';
      echo '</div>';
    echo '</div>';
  echo '</div>';  
  

?>
</div>