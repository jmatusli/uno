<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
	$('body').on('change','#InvoiceBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#InvoiceCurrencyId').parent().addClass('hidden');
			$('#InvoiceBoolCredit').parent().addClass('hidden');
			$('#divDueDate').addClass('hidden');
			$('#InvoiceCashboxAccountingCodeId').parent().addClass('hidden');
			$('#InvoiceBoolRetention').parent().addClass('hidden');
			$('#InvoiceRetentionNumber').parent().addClass('hidden');
			$('#InvoiceBoolIVA').parent().addClass('hidden');
			$('#productsForSale').addClass('hidden');
		}
		else {
			$('#InvoiceCurrencyId').parent().removeClass('hidden');
			$('#InvoiceBoolCredit').parent().removeClass('hidden');
			if ($('#InvoiceBoolCredit').is(':checked')){
				$('#divDueDate').removeClass('hidden');
			}
			else {
				$('#InvoiceCashboxAccountingCodeId').parent().removeClass('hidden');
			}
			$('#InvoiceBoolRetention').parent().removeClass('hidden');
			if ($('#InvoiceBoolRetention').is(':checked')){
				$('#InvoiceRetentionNumber').parent().removeClass('hidden');
			}
			$('#InvoiceBoolIVA').parent().removeClass('hidden');
			$('#productsForSale').removeClass('hidden');
		}
	});	
	
	$('body').on('change','#InvoiceCurrencyId',function(){	
		var currencyid=$(this).children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
		calculateTotal();
	});	

  $('body').on('change','#SaveAllowed',function(){	
    if ($(this).is(':checked')){
      $(this).val(1);
    }
    else {
      $(this).val(0);
    }
  });

  $('body').on('change','#OrderThirdPartyId',function(){	
		var clientId=$(this).val();
		if (clientId>0){
			$.ajax({
        url: '<?php echo $this->Html->url('/'); ?>thirdParties/getclientinfo/',
        data:{"clientid":clientId},
        dataType:'json',
        cache: false,
        type: 'POST',
        success: function (clientdata) {
          $('#ClientData').removeClass('hidden');
          var clientFirstName=clientdata.ThirdParty.first_name;
          var clientLastName=clientdata.ThirdParty.last_name;
          var clientEmail=clientdata.ThirdParty.email;
          var clientPhone=clientdata.ThirdParty.phone;
          var clientAddress=clientdata.ThirdParty.address;
          var clientRucNumber=clientdata.ThirdParty.ruc_number;
                   
          $('#ClientFirstName').html(clientFirstName!=""?clientFirstName:"-");
          $('#ClientLastName').html(clientLastName!=""?clientLastName:"-");
          $('#ClientPhone').html(clientPhone!=""?clientPhone:"-");
          $('#ClientEmail').html(clientEmail!=""?clientEmail:"-");
          $('#ClientAddress').html(clientAddress!=""?clientAddress:"-");
          $('#ClientRucNumber').html(clientRucNumber!=""?clientRucNumber:"-");
          
          $('#EditClientId').val(clientId);
          $('#EditClientFirstName').val(clientFirstName);
          $('#EditClientLastName').val(clientLastName);
          $('#EditClientPhone').val(clientPhone);
          $('#EditClientEmail').val(clientEmail);
          $('#EditClientAddress').val(clientAddress);
          $('#EditClientRucNumber').val(clientRucNumber);
        },
				error: function(e){
          $('#clientData').addClass('hidden');
					alert(e.responseText);
					console.log(e);
				}
			});
		}
    else {
      $('#clientData').addClass('hidden');
    }
    
    if (clientId!=<?php echo CLIENTS_VARIOUS; ?>){
      $('#extraClientData').addClass('hidden')
    }
    else {
      $('#extraClientData').removeClass('hidden')
    }
		setDueDate();
		setOrderType();
	});  
  
  $('body').on('click','#EditClientSave',function(){
		var clientid=$('#EditClientId').val();
		var clientemail=$('#EditClientEmail').val();
    var clientphone=$('#EditClientPhone').val();
    var clientaddress=$('#EditClientAddress').val();
    var clientrucnumber=$('#EditClientRucNumber').val();
    $.ajax({
			url: '<?php echo $this->Html->url('/'); ?>thirdParties/saveexistingclient/',
      data:{"clientid":clientid,"clientemail":clientemail,"clientphone":clientphone,"clientaddress":clientaddress,"clientrucnumber":clientrucnumber},
			cache: false,
			type: 'POST',
			success: function (data) {
				if (data=="1"){
					alert("El cliente se guardó.");
				}
        else {
          console.log(data);
          alert(data);
        }
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
		$('#editClient').modal('hide');		
	});
  
  /*
  $('body').on('hidden.bs.modal','#editClient',function(){
		$('#EditClientId').val('0');
		$('#EditClientFirstName').val('');
		$('#EditClientFirstName').val('');
		$('#EditClientEmail').val('');
		$('#EditClientPhone').val('');
		$('#EditClientAddress').val('');
		$('#EditClientRucNumber').val('');
	});
	*/
	
	$('body').on('change','#InvoiceBoolCredit',function(){	
    $('#creditWarning').empty();
    if  (<?php echo $roleId; ?> != <?php echo ROLE_ADMIN; ?>){
      $('#SaveAllowed').prop('checked',false);  
      $('#SaveAllowed').val(0);
    }
    else {
      $('#SaveAllowed').prop('checked',true);  
      $('#SaveAllowed').val(1);
    }
    setCreditConditions();
  });
    
  function setCreditConditions(){  
    boolCreditAllowed= checkIfCreditIsAllowed();
    if (boolCreditAllowed=="ok"){
      if ($('#InvoiceBoolCredit').is(':checked')){
        $('#divDueDate').removeClass('hidden');
        $('#InvoiceCashboxAccountingCodeId').parent().addClass('hidden');
        $('#InvoiceBoolRetention').parent().addClass('hidden');
        $('#InvoiceRetentionNumber').parent().addClass('hidden');
        $('#retentionPrice').parent().addClass('hidden');
      }
      else {
        $('#divDueDate').addClass('hidden');
        $('#InvoiceCashboxAccountingCodeId').parent().removeClass('hidden');
        $('#InvoiceBoolRetention').parent().removeClass('hidden');
        $('#InvoiceBoolRetention').parent().removeClass('hidden');
        if ($('#InvoiceBoolRetention').is(':checked')){
          $('#InvoiceRetentionNumber').parent().removeClass('hidden');
          $('#retentionPrice').parent().removeClass('hidden');
        }
      }
      $('#SaveAllowed').prop('checked',true);  
      $('#SaveAllowed').val(1);
    }
    else {
      $('#creditWarning').html(boolCreditAllowed);
      $('#SaveAllowed').prop('checked',false);  
      $('#SaveAllowed').val(0);
      if  (<?php echo $roleId; ?> != <?php echo ROLE_ADMIN; ?>){
        $('#InvoiceBoolCredit').prop('checked',false);
      }
      else {
        // $('#SaveAllowed').prop('checked',false);  
        // $('#SaveAllowed').val(0);
      }
    }
	}
  
  function checkIfCreditIsAllowed(){
    var creditCheckResult="";
    if ($('#InvoiceBoolCredit').is(':checked')){
      var clientId=$('#OrderThirdPartyId').val();
      if (clientId>0){
        var creditdays=$('#CreditDays').val();
        if (creditdays==0){
          creditCheckResult+="Este cliente no tiene ni una plaza ni un límite de crédito, entonces solamente se pueden emitir facturas de contado.  ";
        }
        creditCheckResult+=getCreditStatus();  
      }
    }
    if (creditCheckResult==""){
      creditCheckResult="ok";
    }
    else {
      creditCheckResult="Factura de crédito no permitido.  "+creditCheckResult;
    }
    return creditCheckResult;
  }
  
  function getCreditStatus(){
    result="";
    var clientId=$('#OrderThirdPartyId').val();
    // var totalAmount=$('totalPrice').val();
    if (clientId>0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>third_parties/getcreditstatus/'+clientId,
        dataType:'json',
        cache: false,
        async:false,
        type: 'GET',
        success: function (creditstatus) {
          var clientname=creditstatus.ThirdParty.company_name;
          var creditamount=creditstatus.ThirdParty.credit_amount;
          var pendingpayment=creditstatus.ThirdParty.pending_payment;
          var creditsaldo=creditstatus.ThirdParty.credit_saldo;
          
          $('#CreditSaldo').val(creditsaldo);
          if (creditsaldo <0){
            result= "El cliente "+clientname+" tiene un límite de crédito de "+creditamount+", pero un pago pendiente de "+pendingpayment+" entonces solamente se pueden emitir facturas de contado.  ";  
          }
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
          result="Problema buscando estado de crédito.  "
        }
      });        
    }
    return result;
  }
  
	$('body').on('change','#InvoiceBoolRetention',function(){	
		if ($(this).is(':checked')){
			calculateTotal();
			$('#retentionPrice').parent().removeClass('hidden');
			$('#InvoiceRetentionNumber').parent().removeClass('hidden');
		}
		else {
			$('#retentionPrice').parent().addClass('hidden');
			$('#InvoiceRetentionNumber').parent().addClass('hidden');
		}
	});
	
	$('body').on('change','#InvoiceBoolIVA',function(){	
		calculateTotal();
	});
	
	$('body').on('click','#addMaterial',function(){	
		var tableRow=$('#productsForSale tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeMaterial',function(){	
		var tableRow=$(this).parent().parent().remove();
		calculateTotal();
	});	
	
	$('body').on('change','.productid div select',function(){	
		var productid=$(this).val();
		var affectedproductid=$(this).attr('id');
		if (productid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>products/getproductcategoryid/'+productid,
				cache: false,
				type: 'GET',
				success: function (categoryid) {
					if (categoryid==<?php echo CATEGORY_PRODUCED; ?>){
						$('#'+affectedproductid).closest('tr').find('td.rawmaterialid div').removeClass('hidden');
						$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').removeClass('hidden');
					}
					else {
						$('#'+affectedproductid).closest('tr').find('td.rawmaterialid div').addClass('hidden');
						$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').addClass('hidden');
					}
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
				}
			});
		}
	});	
	
	$('body').on('change','.productunitprice div input',function(){	
		var unitprice=$(this).val();
		var productquantity=parseFloat($(this).closest('tr').find('td.productquantity div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
	});	
	
	$('body').on('change','.productquantity div input',function(){	
		var productquantity=$(this).val();
		var unitprice=parseFloat($(this).closest('tr').find('td.productunitprice div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
	});	
	
	function calculateTotal(){
		var currencyid=$('#InvoiceCurrencyId').children("option").filter(":selected").val();
		var totalPrice=0;
		$("#productsForSale tbody tr:not(.hidden)").each(function() {
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
		$('#subTotalPrice').val(roundToTwo(totalPrice));
		
    if (totalPrice>=$('#CreditSaldo').val()){
      checkIfCreditIsAllowed();
    }
    
		if ($('#InvoiceBoolRetention').is(':checked')){
			$('#retentionPrice').val(roundToTwo(0.02*totalPrice));
		}
		else {
			$('#retentionPrice').val(0);
		}
		if ($('#InvoiceBoolIVA').is(':checked')){
			$('#ivaPrice').val(roundToTwo(0.15*totalPrice));
			$('#totalPrice').val(roundToTwo(totalPrice)+roundToTwo(0.15*totalPrice));
		}
		else {
			$('#ivaPrice').val('0.00');
			$('#totalPrice').val(roundToTwo(totalPrice));
		}
		
		return false;
	}
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
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
	

	$('#OrderOrderDateDay').change(function(){
		setDueDate();
		updateExchangeRate();
	});	
	$('#OrderOrderDateMonth').change(function(){
		setDueDate();
		updateExchangeRate();
	});	
	$('#OrderOrderDateYear').change(function(){
		setDueDate();
		updateExchangeRate();
	});	
	function setDueDate(){
		var clientid=$('#OrderThirdPartyId').children("option").filter(":selected").val();
		var emissionday=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var emissionmonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var emissionyear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
		if (clientid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>orders/setduedate/',
				data:{"clientid":clientid,"emissionday":emissionday,"emissionmonth":emissionmonth,"emissionyear":emissionyear},
				cache: false,
				type: 'POST',
				success: function (data) {
					$('#divDueDate').html(data);
				},
				error: function(e){
					//console.log(e);
					$('#divDueDate').html(e.responseText);
				}
			});
		}
	}
	function setOrderType(){
		var clientid=$('#OrderThirdPartyId').children("option").filter(":selected").val();
		if (clientid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>third_parties/getcreditdays/',
				data:{"clientid":clientid},
				cache: false,
				type: 'POST',
				success: function (creditdays) {
          $('#CreditDays').val(creditdays);  
					if (creditdays==0){
						$('#InvoiceBoolCredit').prop('checked',false);
					}
					else {
						$('#InvoiceBoolCredit').prop('checked',true);
					}
					$('#InvoiceBoolCredit').trigger("change");
				},
				error: function(e){
					console.log(e);
					//$('#divDueDate').html(e.responseText);
				}
			});
		}
	}
	
	function updateExchangeRate(){
		var orderday=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#OrderExchangeRate').val(exchangerate);
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
			}
		});
	}
	
	$(document).ready(function(){
		$('#OrderOrderDateHour').val('02');
		$('#OrderOrderDateMin').val('00');
		$('#OrderOrderDateMeridian').val('pm');
		
		if ($('#InvoiceBoolAnnulled').is(':checked')){
			$('#InvoiceCurrencyId').parent().addClass('hidden');
			$('#InvoiceBoolCredit').parent().addClass('hidden');
			$('#divDueDate').addClass('hidden');
			$('#InvoiceCashboxAccountingCodeId').parent().addClass('hidden');
			$('#InvoiceBoolRetention').parent().addClass('hidden');
			$('#InvoiceRetentionNumber').parent().addClass('hidden');
			$('#InvoiceBoolIVA').parent().addClass('hidden');
			$('#productsForSale').addClass('hidden');
		}
		else {
			$('#InvoiceCurrencyId').parent().removeClass('hidden');
			$('#InvoiceBoolCredit').parent().removeClass('hidden');
			if ($('#InvoiceBoolCredit').is(':checked')){
				$('#divDueDate').removeClass('hidden');
			}
			else {
				$('#InvoiceCashboxAccountingCodeId').parent().removeClass('hidden');
			}
			$('#InvoiceBoolRetention').parent().removeClass('hidden');
			if ($('#InvoiceBoolRetention').is(':checked')){
				$('#InvoiceRetentionNumber').parent().removeClass('hidden');
			}
			$('#InvoiceBoolIVA').parent().removeClass('hidden');
			$('#productsForSale').removeClass('hidden');
		}
		
		if ($('#InvoiceBoolCredit').is(':checked')){
			$('#InvoiceCashboxAccountingCodeId').parent().addClass('hidden');
		}
		else {
			$('#divDueDate').addClass('hidden');
		}
		if (!$('#InvoiceBoolRetention').is(':checked')){
			$('#retentionPrice').parent().addClass('hidden');
			$('#InvoiceRetentionNumber').parent().addClass('hidden');
		}
		$('.productionresultcodeid div select option:not(:selected)').attr('disabled', true);
		$('.productionresultcodeid div select option:not(:selected)').attr('disabled', true);
		
		var currencyid=$('#InvoiceCurrencyId').children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
		if ($('#ThirdPartyId').val()>0){
			$('#ClientData').removeClass('hidden');
		}
    else {
      $('#ClientData').addClass('hidden');
    }
    if ($('#ThirdPartyId').val()!=<?php echo CLIENTS_VARIOUS; ?>){
      $('#extraClientData').addClass('hidden')
    }
    else {
      $('#extraClientData').removeClass('hidden')
    }
    
    $('#saving').addClass('hidden');
	});
  
  $('body').on('click','#submit',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#OrderCrearVentaForm',function(e){	
    if($("#submit").data('clicked'))
    {
      $('#submit').attr('disabled', 'disabled');
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
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la venta...</p>";
    echo "</div>";
  echo "</div>";
  
	echo $this->Form->create('Order');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Add Sale')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='rows'>";
				echo "<div class='col-sm-12 col-md-8'>";	
					echo "<div class='col-sm-6 col-md-8'>";	
						echo  $this->Form->input('warehouse_id',['label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>['0'=>'Todas Bodegas']]);
            echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
						echo  $this->Form->input('inventory_display_option_id',['label'=>__('Mostrar Inventario'),'default'=>$inventoryDisplayOptionId]);
						echo $this->Form->Submit(__('Mostrar/Esconder Inventario'),['id'=>'showinventory','name'=>'showinventory']);

						echo $this->Form->input('order_date',['label'=>__('Sale Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
						echo $this->Form->input('order_code',['default'=>$newInvoiceCode,'class'=>'narrow','readonly'=>'readonly']);
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
						echo $this->Form->input('exchange_rate',['default'=>$exchangeRateOrder,'class'=>'narrow','readonly'=>'readonly']);
						echo $this->Form->input('Invoice.bool_annulled',['type'=>'checkbox','label'=>'Anulada']);
						
            echo $this->Form->input('third_party_id',['label'=>__('Client'),'default'=>'0','empty'=>['0'=>'Seleccione Cliente']]);
            echo "<div id='extraClientData'>";
              echo $this->Form->input('extra_client_name',['label'=>__('Nombre Adicional Cliente')]);
              echo $this->Form->input('extra_client_phone',['label'=>__('Teléfono Adicional Cliente')]);
              echo $this->Form->input('extra_client_address',['label'=>__('Dirección Adicional Cliente')]);
              echo $this->Form->input('extra_client_ruc_number',['label'=>__('Número RUC Adicional Cliente')]);
            echo "</div>";  
            echo $this->Form->input('comment',['type'=>'textarea','rows'=>3]);
            
						echo $this->Form->input('Invoice.currency_id',['default'=>CURRENCY_CS,'empty'=>['0'=>'Seleccione Moneda'],'class'=>'narrow']);
            
            echo "<p class='notallowed' id='creditWarning'></p>";
            echo $this->Form->input('ThirdParty.credit_days',['type'=>'hidden','label'=>false,'id'=>'CreditDays']);
            echo $this->Form->input('ThirdParty.credit_saldo',['type'=>'hidden','label'=>false,'id'=>'CreditSaldo']);
            if ($roleId==ROLE_ADMIN){
              echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'checkbox','label'=>'Guardar Venta','value'=>1]);
            }
            else {
              echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'hidden','label'=>'Guardar Venta','readonly'=>'readonly','value'=>1]);
            }
						echo $this->Form->input('Invoice.bool_credit',['type'=>'checkbox','label'=>'Crédito']);
						
            echo "<div id='divDueDate'>";
							echo $this->Form->input('Invoice.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
						echo "</div>";
            
						echo $this->Form->input('Invoice.cashbox_accounting_code_id',['empty'=>['0'=>'Seleccione Caja'],'class'=>'narrow','options'=>$accountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN]);
						echo $this->Form->input('Invoice.bool_retention',['type'=>'checkbox','label'=>'Retención']);
						//echo $this->Form->input('Invoice.retention_amount',['label'=>'Monto Retención','type'=>'decimal']);
						echo $this->Form->input('Invoice.retention_number',['label'=>'Número Retención']);
						echo $this->Form->input('Invoice.bool_IVA',['type'=>'checkbox','label'=>'Se aplica IVA','checked'=>'checked']);
					echo "</div>";
					echo "<div class='col-sm-6 col-md-4 totals'>";
						echo "<h4>".__('Sale Price')."</h4>";			
						echo $this->Form->input('Invoice.sub_total_price',['label'=>__('SubTotal'),'id'=>'subTotalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
						echo $this->Form->input('Invoice.IVA_price',['label'=>__('IVA'),'id'=>'ivaPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
						echo $this->Form->input('Invoice.total_price',['label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
						echo $this->Form->input('Invoice.retention_amount',['label'=>__('Retención'),'id'=>'retentionPrice','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            
						echo "<h4>".__('Actions')."</h3>";
						echo "<ul>";
						if ($bool_client_add_permission) {
							echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
						}
						echo "</ul>";
            echo "<div id='ClientData'>";
              echo "<h4>".__('Datos del Cliente')."</h4>";			
              echo  "<dl class='narrow'>";
                echo "<dt>".__('First Name')."</dt>";
                echo "<dd id= 'ClientFirstName'>&nbsp;</dd>";
                echo "<dt>".__('Last Name')."</dt>";
                echo "<dd id= 'ClientLastName'>&nbsp;</dd>";
                echo "<dt>".__('Email')."</dt>";
                echo "<dd id= 'ClientEmail'>&nbsp;</dd>";
                echo "<dt>".__('Phone')."</dt>";
                echo "<dd id= 'ClientPhone'>&nbsp;</dd>";
                echo "<dt>".__('Address')."</dt>";
                echo "<dd id= 'ClientAddress'>&nbsp;</dd>";
                echo "<dt>".__('Ruc Number')."</dt>";
                echo "<dd id= 'ClientRucNumber'>&nbsp;</dd>";
              echo  "</dl>";
              echo "<a href='#editClient' role='button' class='btn btn-large btn-primary' data-toggle='modal'>Editar Cliente</a>";
            echo "</div>";
					echo "</div>";
        echo "</div>";  
        echo "<div class='col-sm-12 col-md-4'>";
          if (!empty($inventoryDisplayOptionId)){	
            echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, CATEGORY_PRODUCED,'Productos Fabricados');
            echo $this->InventoryCountDisplay->showInventoryTotals($otherMaterialsInventory, CATEGORY_OTHER,'Otros Productos');
            echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory, CATEGORY_RAW,'Materia Prima');
          }
        echo "</div>";
          
        echo "<div class='col-sm-12'>";
          echo "<table id='productsForSale'>";
            echo "<thead>";
              echo "<tr>";
                echo "<th>".__('Product')."</th>";
                echo "<th style='width:170px;'>".__('Raw Material')."</th>";
                echo "<th style='width:80px;'>".__('Quality')."</th>";
                echo "<th class='centered narrow'>".__('Quantity Product')."</th>";
                echo "<th class='currencyinput'>".__('Unit Price')."</th>";
                echo "<th class='currencyinput'>".__('Total Price')."</th>";
                echo "<th></th>";
              echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            for ($i=0;$i<count($requestProducts);$i++) { 
              echo "<tr>";
                echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'value'=>$requestProducts[$i]['Product']['product_id'],'empty' =>array(0=>__('Choose a Product'))))."</td>";
                echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'value'=>$requestProducts[$i]['Product']['raw_material_id'],'empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
                if (!empty($requestProducts[$i]['Product']['production_result_code_id'])){
                  echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'value'=>$requestProducts[$i]['Product']['production_result_code_id']))."</td>";
                }
                else {
                  echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>'0','div'=>array('class'=>'hidden')))."</td>";
                }
                echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['product_quantity']))."</td>";
                echo "<td class='productunitprice'>".$this->Form->input('Product.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['product_unit_price'],'before'=>'<span class=\'currency\'>C$</span>'))."</td>";
                echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['product_total_price'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
                echo "<td><button class='removeMaterial'>".__('Remove Sale Item')."</button></td>";
              echo "</tr>";
            }
            for ($i=count($requestProducts);$i<7;$i++) { 
              if ($i==count($requestProducts)){
                echo "<tr>";
              } 
              else {
                echo "<tr class='hidden'>";
              } 
                echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
                echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
                echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>PRODUCTION_RESULT_CODE_A,'readonly'=>'readonly'))."</td>";
                echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                echo "<td class='productunitprice'>".$this->Form->input('Product.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'default'=>'0','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
                echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
                echo "<td><button class='removeMaterial'>".__('Remove Sale Item')."</button></td>";
              echo "</tr>";
            } 						
            echo "</tbody>";
          echo "</table>";
          echo "<button id='addMaterial' type='button'>".__('Add Product')."</button>	";
          echo $this->Form->Submit(__('Submit'),array('id'=>'submit','name'=>'submit'));
          echo $this->Form->end();
         echo "</div>";
        
      echo "</div>";        
    echo "</div>";
      
    echo "<div id='editClient' class='modal fade'>";
      echo "<div class='modal-dialog'>";
        echo "<div class='modal-content'>";
          //echo $this->Form->create('EditClient', array('enctype' => 'multipart/form-data')); 
          echo "<div class='modal-header'>";
            //echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
            echo "<h4 class='modal-title'>Editar Cliente</h4>";
          echo "</div>";
          
          echo "<div class='modal-body'>";
            echo $this->Form->create('EditClient'); 
              echo "<fieldset>";
                echo $this->Form->input('id',['type'=>'hidden']);
                echo $this->Form->input('first_name',['readonly']);
                echo $this->Form->input('last_name',['readonly']);
                echo $this->Form->input('email');
                echo $this->Form->input('phone');
                echo $this->Form->input('address');
                echo $this->Form->input('ruc_number');
              echo "</fieldset>";
            echo $this->Form->end(); 	
          echo "</div>";
          echo "<div class='modal-footer'>";
            echo "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
            echo "<button type='button' class='btn btn-primary' id='EditClientSave'>".__('Guardar Cambios')."</button>";
          echo "</div>";
          
        echo "</div>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
echo "</fieldset>";
?>
</div>