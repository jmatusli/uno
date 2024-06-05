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
  
  $('body').on('change','#OrderThirdPartyId',function(){	
		var clientId=$(this).val();
		if (clientId>0){
      loadClientData(clientId);
			
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
  
  function loadClientData(clientId){
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
  
<?php
	if (!$bool_invoicetype_editable){
?>
		$('body').on('click','#InvoiceBoolCredit',function(){
			return false;
		});
<?php 
	}
	else {
?>
		$('body').on('change','#InvoiceBoolCredit',function(){			   
			if ($(this).is(':checked')){
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
		});
<?php 
	}
?>
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
 
	$('.productprice').change(function(){
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
	
	$('body').on('change','#OrderThirdPartyId',function(){	
		setDueDate();
											
				 
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
			$('#InvoiceBoolRetention').parent().addClass('hidden');
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
		
		calculateTotal();
		
		$('.productid div select').each(function(){	
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
    
    loadClientData(<?php echo $this->request->data['Order']['third_party_id'] ?>)
		
	});
</script>


<div class="orders form sales fullwidth">
<?php 
	$currencyAbbreviation="C$";
	//pr($this->request->data['Invoice']);
	if (!empty($this->request->data['Invoice'])){
    if (array_key_exists('currency_id',$this->request->data['Invoice'])){
      if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
        $currencyAbbreviation="US$";
      }
    }
	}

	$orderDateTime=new DateTime($orderDate);

  //pr($this->request->data);
	echo $this->Form->create('Order'); 
	echo "<fieldset>";
		echo "<legend>".__('Editar Venta')." ".$this->request->data['Order']['order_code']."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='rows'>";
				echo '<div class="col-sm-12 col-md-8">';
					echo '<div class="col-sm-6 col-md-8">';
						// 20170322 warehouseid can be deduced from stockmovement
						echo  $this->Form->input('warehouse_id',['label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>['0'=>'Todas Bodegas']]);
						echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
            echo  $this->Form->input('inventory_display_option_id',['label'=>__('Mostrar Inventario'),'default'=>$inventoryDisplayOptionId]);
						echo $this->Form->Submit(__('Mostrar/Esconder Inventario'),['id'=>'showinventory','name'=>'showinventory']);
            
						echo $this->Form->input('order_date',['label'=>__('Sale Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
						echo $this->Form->input('order_code',['class'=>'narrow','readonly'=>'readonly']);
            echo $this->Form->input('user_id',['label'=>'Vendedor']);
						echo $this->Form->input('exchange_rate',['default'=>$exchangeRateOrder,'class'=>'narrow','readonly'=>'readonly']);
						if (!empty($this->request->data['Invoice'])){
							echo $this->Form->input('Invoice.id',['type'=>'hidden','default'=>$this->request->data['Invoice']['id']]);
							echo $this->Form->input('Invoice.bool_annulled',['type'=>'checkbox','label'=>'Anulada','default'=>$this->request->data['Invoice']['bool_annulled']]);
						}
						else {
							echo $this->Form->input('Invoice.id',['type'=>'hidden','default'=>'0']);
							echo $this->Form->input('Invoice.bool_annulled',['type'=>'checkbox','label'=>'Anulada','default'=>false]);
						}
						
            echo $this->Form->input('third_party_id',['label'=>__('Client'),'empty'=>['0'=>'Seleccione Cliente']]);
            echo "<div id='extraClientData'>";
              echo $this->Form->input('extra_client_name',['label'=>__('Nombre Adicional Cliente')]);
              echo $this->Form->input('extra_client_phone',['label'=>__('Teléfono Adicional Cliente')]);
              echo $this->Form->input('extra_client_address',['label'=>__('Dirección Adicional Cliente')]);
              echo $this->Form->input('extra_client_ruc_number',['label'=>__('Número RUC Adicional Cliente')]);
            echo "</div>";  
            
            echo $this->Form->input('comment',['type'=>'textarea','rows'=>3]);
						
            if (!empty($this->request->data['Invoice'])){
							echo $this->Form->input('Invoice.currency_id',['empty'=>['0'=>'Seleccione Moneda'],'class'=>'narrow','default'=>$this->request->data['Invoice']['currency_id'],'label'=>'Moneda']);
							echo $this->Form->input('Invoice.bool_credit',['type'=>'checkbox','label'=>'Crédito','checked'=>$this->request->data['Invoice']['bool_credit']]);
						}
						else {
							echo $this->Form->input('Invoice.currency_id',['empty'=>['0'=>'Seleccione Moneda'],'class'=>'narrow','default'=>0,'label'=>'Moneda']);
							echo $this->Form->input('Invoice.bool_credit',['type'=>'checkbox','label'=>'Crédito','checked'=>false]);
						}
            
						echo "<div id='divDueDate'>";
						if (!empty($this->request->data['Invoice'])){
							echo $this->Form->input('Invoice.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y'),'div'=>['id'=>'divDueDate'],'default'=>$this->request->data['Invoice']['due_date']]);
						}
						else {
							echo $this->Form->input('Invoice.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y'),'div'=>['id'=>'divDueDate']]);
						}
						echo "</div>";
            
						if (!empty($this->request->data['Invoice'])){
							echo $this->Form->input('Invoice.cashbox_accounting_code_id',['empty'=>['0'=>'Seleccione Caja'],'class'=>'narrow','options'=>$accountingCodes,'default'=>$this->request->data['Invoice']['cashbox_accounting_code_id']]);
							echo $this->Form->input('Invoice.bool_retention',['type'=>'checkbox','label'=>'Retención','checked'=>$this->request->data['Invoice']['bool_retention']]);
							
							echo $this->Form->input('Invoice.retention_number',['label'=>'Número Retención','default'=>$this->request->data['Invoice']['retention_number']]);
							echo $this->Form->input('Invoice.bool_IVA',['type'=>'checkbox','label'=>'Se aplica IVA','checked'=>$this->request->data['Invoice']['bool_IVA']]);
						}
						else {
							echo $this->Form->input('Invoice.cashbox_accounting_code_id',['empty'=>['0'=>'Seleccione Caja'],'class'=>'narrow','options'=>$accountingCodes]);
							echo $this->Form->input('Invoice.bool_retention',['type'=>'checkbox','label'=>'Retención']);
							
							echo $this->Form->input('Invoice.retention_number',['label'=>'Número Retención']);
							echo $this->Form->input('Invoice.bool_IVA',['type'=>'checkbox','label'=>'Se aplica IVA','checked'=>'checked']);
						}
					echo "</div>";
					echo "<div class='col-sm-6 col-md-4 totals'>";
						echo "<h4>".__('Sale Price')."</h4>";			
						if (!empty($this->request->data['Invoice'])){
							echo $this->Form->input('Invoice.sub_total_price',['label'=>__('SubTotal'),'id'=>'subTotalPrice','default'=>$this->request->data['Invoice']['sub_total_price'],'readonly'=>'readonly','between'=>'<span class="currencyrighttop">'.$currencyAbbreviation.'</span>','type'=>'decimal','style'=>'width:50%;']);
							echo $this->Form->input('Invoice.IVA_price',['label'=>__('IVA'),'id'=>'ivaPrice','default'=>$this->request->data['Invoice']['IVA_price'],'readonly'=>'readonly','between'=>'<span class="currencyrighttop">'.$currencyAbbreviation.'</span>','type'=>'decimal','style'=>'width:50%;']);
							echo $this->Form->input('Invoice.total_price',['label'=>__('Total'),'id'=>'totalPrice','default'=>$this->request->data['Invoice']['total_price'],'readonly'=>'readonly','between'=>'<span class="currencyrighttop">'.$currencyAbbreviation.'</span>','type'=>'decimal','style'=>'width:50%;']);
							echo $this->Form->input('Invoice.retention_amount',['label'=>__('Retención'),'id'=>'retentionPrice','default'=>$this->request->data['Invoice']['retention_amount'],'between'=>'<span class="currencyrighttop">'.$currencyAbbreviation.'</span>','type'=>'decimal','style'=>'width:50%;']);
						}
						else {
							echo $this->Form->input('Invoice.sub_total_price',['label'=>__('SubTotal'),'id'=>'subTotalPrice','readonly'=>'readonly','default'=>$subtotalNoInvoice,'between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal']);
							echo $this->Form->input('Invoice.IVA_price',['label'=>__('IVA'),'id'=>'ivaPrice','readonly'=>'readonly','default'=>$subtotalNoInvoice*0.15,'between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal']);
							echo $this->Form->input('Invoice.total_price',['label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>$subtotalNoInvoice*1.15,'between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal']);
							echo $this->Form->input('Invoice.retention_amount',['label'=>__('Retención'),'id'=>'retentionPrice','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal']);
						}
						echo "<h4>".__('Actions')."</h3>";
						echo "<ul>";
							if ($bool_client_add_permission) {
								echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
							}
						echo "</ul>";
            echo "<div id='ClientData'>";
              echo "<h4>".__('Datos del Cliente')."</h4>";			
              echo "<dl class='narrow'>";
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
              echo "</dl>";
              echo "<a href='#editClient' role='button' class='btn btn-large btn-primary' data-toggle='modal'>Editar Cliente</a>";
            echo "</div>";
					echo "</div>";
        echo "</div>";  
				echo "<div class='col-sm-12 col-md-4'>";
				if (!empty($inventoryDisplayOptionId)){
					echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, CATEGORY_PRODUCED,'Productos Fabricados en '.$orderDateTime->format('d-m-Y'));
					echo $this->InventoryCountDisplay->showInventoryTotals($otherMaterialsInventory, CATEGORY_OTHER,'Otros Productos en '.$orderDateTime->format('d-m-Y'));
					echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory, CATEGORY_RAW,'Materia Prima en '.$orderDateTime->format('d-m-Y'));
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
							for ($i=count($requestProducts);$i<25;$i++) { 
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
      
			echo "<div class='rows'>";
				
			echo "</div>";
		echo "</div>";
	echo "</fieldset>";
?>
</div>