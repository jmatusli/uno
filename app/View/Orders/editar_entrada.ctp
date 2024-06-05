<script>
  $('body').on('change','.productquantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
    var thisRow=$(this).closest('tr');
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
  
  $('body').on('change','.productunitprice',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			//var roundedValue=roundToFive($(this).find('div input').val());
			//$(this).find('div input').val(roundedValue);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	
	function calculateRow(rowid) {    
		var currentrow=$('#productsForPurchase').find("[row='" + rowid + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitprice=parseFloat(currentrow.find('td.productunitprice div input').val());
		
		var totalprice=quantity*unitprice;
		
		currentrow.find('td.producttotalprice div input').val(roundToTwo(totalprice));
	}
	
  $('body').on('change','#OrderBoolIva',function(){
		calculateTotal();
	});

  $('body').on('change','#OrderAdjustmentPrice',function(){
		calculateTotal();
	});

  $('body').on('change','#OrderRentPrice',function(){
		calculateTotal();
	});
		
	function calculateTotal(){
		var boolIva=$('#OrderBoolIva').is(':checked');
		var totalProductQuantity=0;
		var subtotalPrice=0;
		var adjustmentPrice=0
    var rentPrice=0
		var ivaPrice=0
    var totalPrice=0
		$("#productsForPurchase tbody tr:not(.totalrow.hidden)").each(function() {
			var currentProductQuantity = $(this).find('td.productquantity div input');
			if (!isNaN(currentProductQuantity.val())){
				var currentQuantity = parseFloat(currentProductQuantity.val());
				totalProductQuantity += currentQuantity;
			}
			
			var currentProduct = $(this).find('td.producttotalprice div input');
			if (!isNaN(currentProduct.val())){
				var currentPrice = parseFloat(currentProduct.val());
				subtotalPrice += currentPrice;
			}
		});
		$('#productsForPurchase tbody tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));
		$('#productsForPurchase tbody tr.totalrow.subtotal td.totalprice div input').val(subtotalPrice.toFixed(2));
    $('#OrderSubtotal').val(subtotalPrice.toFixed(2));

    adjustmentPrice=parseFloat(roundToTwo($('#productsForPurchase tbody tr.adjustment td.totalprice div input').val()))
    if (isNaN(rentPrice)){
      alert('El ajuste establecido '+adjustmentPrice+' no es válido y se resetea a 0!')
      $('#productsForPurchase tbody tr.adjustment td.totalprice div input').val(0)
      adjustmentPrice=0
    }
    $('#OrderAdjustment').val(adjustmentPrice.toFixed(2));
    

    rentPrice=parseFloat(roundToTwo($('#productsForPurchase tbody tr.rent td.totalprice div input').val()))
    if (isNaN(rentPrice)){
      alert('El precio de renta establecido '+rentPrice+' no es válido y se resetea a 0!')
      $('#productsForPurchase tbody tr.rent td.totalprice div input').val(0)
      rentPrice=0
    }
    $('#OrderRent').val(rentPrice.toFixed(2));    
    
		if (boolIva){
			ivaPrice=roundToTwo(0.15*rentPrice);
		}
		$('#productsForPurchase tbody tr.iva td.totalprice div input').val(ivaPrice.toFixed(2));
    $('#OrderIva').val(ivaPrice.toFixed(2));
    
    
		
    totalPrice=subtotalPrice + adjustmentPrice + rentPrice + ivaPrice;
		$('#productsForPurchase tbody tr.totalrow.total td.totalprice div input').val(totalPrice.toFixed(2));
    $('#OrderTotal').val(totalPrice.toFixed(2));
		
		return false;
	}

	$('body').on('click','#addMaterial',function(){
		var tableRow=$('#productsForPurchase tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});
	$('body').on('click','.removeMaterial',function(){
		var tableRow=$(this).parent().parent().remove();
		calculateTotal();
	});	
	
	$(document).ready(function(){
		$('select.fixed option:not(:selected)').attr('disabled', true);
    calculateTotal();
	});
</script>
<div class="orders form fullwidth">
<?php 
	echo $this->Form->create('Order'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit Purchase')."</legend>";
    echo "<div class='container-fluid'>";
        echo "<div class='row'>";
          echo "<div class='col-sm-6'>";
            echo $this->Form->input('id');
            echo $this->Form->input('order_date',array('label'=>__('Purchase Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')));
            echo $this->Form->input('order_code');
            echo $this->Form->input('invoice_code');
            echo $this->Form->input('third_party_id',array('label'=>__('Provider')));
            echo $this->Form->input('enterprise_id');
            echo $this->Form->input('bool_iva');
          echo "</div>";
          echo "<div class='col-sm-3' style='padding:0 5px 0 30px;'>";
            echo "<h4>".__('Precio de Entrada')."</h4>";			
            echo $this->Form->input('subtotal',['label'=>__('SubTotal'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('adjustment',['label'=>__('Ajuste'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('rent',['label'=>'Renta','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('iva',['label'=>__('IVA'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('total',['label'=>__('Total'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
          echo "</div>";
          echo "<div class='col-sm-2 actions'>";
            echo "<h3>".__('Actions')."</h3>";
            echo "<ul style='list-style:none;'>";
              if ($bool_delete_permission){
                echo "<!--li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Order.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Order.id')))."</li-->";
              }
              echo "<li>".$this->Html->link(__('List Purchases'), array('action' => 'resumenEntradas'))."</li>";
              echo "<br/>";
              if ($bool_provider_index_permission) {
                echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))."</li>";
              }
              if ($bool_provider_add_permission) {
                echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))."</li>";
              } 
            echo "</ul>";
          echo "</div>";
        echo "</div>";
        echo "<div class='row'>";
          echo "<div class='col-md-12'>";
            echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
            echo "<h3>Productos en Entrada</h3>";
            echo "<p class='comment'>Note que la aplicación presupone que las entradas de combustibles se registren en galones.</p>";
            echo "<table id='productsForPurchase'>";
              echo "<thead>";
                echo "<tr>";
                  echo "<th>".__('Product')."</th>";
                  echo "<th style='width:20%;min-width:20%;'>".__('Quantity')."</th>";
                  echo "<th style='width:20%;min-width:20%;'>".__('Precio Unitario')."</th>";
                  echo "<th style='width:25%;min-width:25%;'>".__('Price')."</th>";
                  echo "<th></th>";
                echo "</tr>";
              echo "</thead>";
              echo "<tbody style='font-size:75%;'>";
              for ($i=0;$i<count($requestProducts);$i++) { 
                $product=$requestProducts[$i];
                echo "<tr row='".$i."'>";
                  echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>$product['product_id'],'empty' =>[0=>__('Choose a Product')],'style'=>'font-size:90%'])."</td>";
                  echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',['type'=>'decimal','label'=>false,'default'=>round($product['product_quantity'],2),'class'=>'width100'])."</td>";
                  echo "<td class='productunitprice'><span class='currency'></span>".$this->Form->input('Product.'.$i.'.product_unit_price',['label'=>false,'type'=>'decimal','value'=>round($product['product_unit_price'],4),'class'=>'width100'])."</td>";
                  echo "<td  class='producttotalprice'>". $this->Form->input('Product.'.$i.'.product_total_price',['type'=>'decimal','label'=>false,'default'=>round($product['product_total_price'],2),'class'=>'width100','readonly'=>'readonly'])."</td>";
                  echo "<td><button class='removeMaterial'>".__('Remover')."</button></td>";
                echo "</tr>";
              }
              
              for ($i=count($requestProducts);$i<=25;$i++) { 
                if ($i == count($requestProducts)){
                  echo "<tr row='".$i."'>";
                }
                else {
                  echo "<tr row='".$i."' class='hidden'>";
                }
                  echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>'0','empty' =>[0=>__('Choose a Product')],'style'=>'font-size:90%'])."</td>";
                  echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',['type'=>'decimal','label'=>false,'default'=>0,'class'=>'width100'])."</td>";
                  echo "<td class='productunitprice'><span class='currency'></span>".$this->Form->input('Product.'.$i.'.product_unit_price',['label'=>false,'type'=>'decimal','default'=>'0','class'=>'width100'])."</td>";
                  echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',['type'=>'decimal','label'=>false,'default'=>0,'readonly'=>'readonly','class'=>'width100'])."</td>";
                  echo "<td><button class='removeMaterial'>".__('Remover')."</button></td>";
                echo "</tr>";
              }
                echo "<tr class='totalrow subtotal'>";
                  echo "<td>Subtotal</td>";
                  echo "<td class='productquantity amount right'><span></span></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('subtotal_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0','class'=>'width100'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
                echo "<tr class='adjustment'>";
                  echo "<td>Ajuste (promo)</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('adjustment_price',['label'=>false,'type'=>'decimal','default'=>'0','class'=>'width100'])."</td>";
                  echo "<td></td>";
                echo "</tr>";	
                echo "<tr class='rent'>";
                  echo "<td>Renta</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('rent_price',['label'=>false,'type'=>'decimal','default'=>'0','class'=>'width100'])."</td>";
                  echo "<td></td>";
                echo "</tr>";
                echo "<tr class='iva'>";
                  echo "<td>IVA</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('iva_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0','class'=>'width100'])."</td>";
                  echo "<td></td>";
                echo "</tr>";                
                echo "<tr class='totalrow total'>";
                  echo "<td>Total</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('total_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
              echo "</tbody>";
            echo "</table>";
            echo "<button id='addMaterial' type='button'>".__('Add Purchase Item')."</button>";	
          echo "</div>";
        echo "</fieldset>";
      echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
    echo $this->Form->end();
  echo "</div>";
?>
</div>