<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  var inventoryValues = <?php echo json_encode($inventoryQuantities); ?>;

  $('body').on('change','.productid div select',function(){
    var productId=$(this).val();
    if (productId != 0){
      $(this).closest('tr').find('td.inventory div input').val(inventoryValues[productId]);
    }
    else {
      $(this).closest('tr').find('td.inventory div input').val(0);
    }
    changeRow($(this).closest('tr'));
	});
  
  $('body').on('change','.quantity',function(){
    changeRow($(this).closest('tr'));
		calculateTotal();
	});	
  $('body').on('change','.direction',function(){
    changeRow($(this).closest('tr'));
		calculateTotal();
	});
  function changeRow(row){
    var inventory=parseFloat(row.find('td.inventory div input').val())
    var quantity=parseFloat(row.find('td.quantity div input').val())
    var boolInput=parseInt(row.find('td.direction div select').val())
    var newInventory=0;
    if (!isNaN(inventory)){
      newInventory+=inventory
    }
    if (!isNaN(quantity)){
      if (boolInput==0){
        newInventory-=quantity
      }
      else {
        newInventory+=quantity
      }
    }
    row.find('td.resultinginventory div input').val(newInventory)
  }
  
  function calculateTotal(){
    var totalProductQuantity=0;
		$("#adjustments tbody tr:not(.totalrow,.hidden)").each(function() {
			var currentProductQuantity = $(this).find('td.quantity div input');
      var currentQuantity=0
			if (!isNaN(currentProductQuantity.val())){
				currentQuantity = parseFloat(currentProductQuantity.val());
			}
      var boolInput=parseInt($(this).find('td.direction div select').val())
      if (boolInput){
        totalProductQuantity += currentQuantity;
      }
      else {
        totalProductQuantity -= currentQuantity;
      }
		});
		$('#adjustments tbody tr.totalrow td.quantity').text(Math.abs(totalProductQuantity).toFixed(2));
    $('#adjustments tbody tr.totalrow td.direction').text(totalProductQuantity >0?'Aumentar Inventario':'Diminuir Inventario');
		return true;
	}

	$('body').on('click','#addAdjustment',function(){
		var tableRow=$('#adjustments tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});
	$('body').on('click','.removeAdjustment',function(){
		var tableRow=$(this).closest('tr').remove();
		calculateTotal();
	});	

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
		$("td.number").each(function(){
			$(this).number(true,2);
		});
	}
	function formatPercentages(){
		$("td.percentage span.amountcenter").each(function(){
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
			$(this).find('.amountcenter').number(true,2);
      
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
      
			$(this).find('.currency').text("C$");
		});
	}
  
	$(document).ready(function(){
    formatNumbers();
		formatCSCurrencies();
		formatPercentages();
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
    
    var tableId="tankMeasurements";
    $('#saving').addClass('hidden');
    //$('#chkEditingMode').trigger('change');
    
    $('#saveAdjustments').removeAttr('disabled');
    $('#saveAdjustments').css("background-color","#62af56");
	});
  
  $('body').on('click','#saveAdjustments',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#StockMovementRegistrarAjusteTanqueForm',function(e){	
    if($("#saveAdjustments").data('clicked'))
    {
      $('#saveAdjustments').attr('disabled', 'disabled');
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

<div class="stockMovements form adjustments fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando los ajustes de inventario...</p>";
    echo "</div>";
  echo "</div>";
  
  $adjustmentDateTime=new DateTime($adjustmentDate);

  echo "<h2>Registrar Ajustes Manuales de Inventario</h2>";
  echo $this->Form->create('Adjustment');
  echo "<fieldset id='mainform'>";
    echo "<legend>".('Registrar Ajustes de Inventario Manuales con fecha '.($adjustmentDateTime->format('d-m-Y')))."</legend>";
    echo $this->Form->input('enterprise_id',['label'=>__('Enterprise'),'default'=>$enterpriseId,'empty'=>['0'=>'--Seleccione Gasolinera--']]);
    echo $this->Form->submit('Cambiar Gasolinera',['name'=>'submitEnterprise','id'=>'submitEnterprise','style'=>'width:200px;']);
    
    echo $this->Form->input('adjustment_date',['label'=>'Fecha ajuste','type'=>'date','dateFormat'=>'DMY','default'=>$adjustmentDate,'minYear'=>2019,'maxYear'=>date('Y')]);
    echo $this->Form->input('comment',['type'=>'textarea','rows'=>2]);
    echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
    
    $adjustmentsTableHeader="";
    $adjustmentsTableHeader.="<thead>";
      $adjustmentsTableHeader.="<tr>";
        $adjustmentsTableHeader.="<th>Producto</th>";
        $adjustmentsTableHeader.="<th class='centered' style='min-width:150px;width:150px;'>Inventario Actual</th>";
        $adjustmentsTableHeader.="<th class='centered' style='min-width:150px;width:150px;'>Ajuste</th>";
        $adjustmentsTableHeader.="<th>Dirección</th>";
        $adjustmentsTableHeader.="<th>Nuevo Valor</th>";
        $adjustmentsTableHeader.="<th></th>";
      $adjustmentsTableHeader.="</tr>";
    $adjustmentsTableHeader.="</thead>";
    
    $totalInventory=0;
    $totalMeasurement=0;
    $adjustmentsTableBodyRows="";
    
    for ($i=0;$i<count($requestMovements);$i++){
      $tableRow="";
      $tableRow.="<tr style='font-size:0.85em;'>";
        $tableRow.="<td class='productid'>".$this->Form->input('StockMovement.'.$i.'.product_id',['label'=>false,'default'=>0,'empty'=>[0=>'--Seleccione producto--']])."</td>";
        $tableRow.="<td class='inventory'>".$this->Form->input('StockMovement.'.$i.'.inventory_value',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
          'readonly'=>'readonly',
        ])."</td>";
        $tableRow.="<td class='quantity'>".$this->Form->input('StockMovement.'.$i.'.product_quantity',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
        ])."</td>";
        $tableRow.="<td class='direction'>".$this->Form->input('StockMovement.'.$i.'.bool_input',['label'=>false,'options'=>$movementDirections,'default'=>0])."</td>";
        $tableRow.="<td class='resultinginventory quantity'>".$this->Form->input('StockMovement.'.$i.'.resulting_inventory_value',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
          'readonly'=>'readonly',
        ])."</td>";
        $tableRow.="<td><button class='removeAdjustment'>".__('Remover')."</button></td>";
      $tableRow.="</tr>";
      $adjustmentsTableBodyRows.=$tableRow;
    }
    
    for ($i=count($requestMovements);$i<MAX_ROWS;$i++){
      $tableRow="";
      if ($i == count($requestMovements)){
        $tableRow.="<tr style='font-size:0.85em;'>";
      }
      else {
        $tableRow.="<tr style='font-size:0.85em;' class='hidden'>";
      }
        $tableRow.="<td class='productid'>".$this->Form->input('StockMovement.'.$i.'.product_id',['label'=>false,'default'=>0,'empty'=>[0=>'--Seleccione producto--']])."</td>";
        $tableRow.="<td class='inventory'>".$this->Form->input('StockMovement.'.$i.'.inventory_value',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
          'readonly'=>'readonly',
        ])."</td>";
        $tableRow.="<td class='quantity'>".$this->Form->input('StockMovement.'.$i.'.product_quantity',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
        ])."</td>";
        $tableRow.="<td class='direction'>".$this->Form->input('StockMovement.'.$i.'.bool_input',['label'=>false,'options'=>$movementDirections,'default'=>0])."</td>";
        $tableRow.="<td class='resultinginventory quantity'>".$this->Form->input('StockMovement.'.$i.'.resulting_inventory_value',[
          'label'=>false,
          'type'=>'decimal',
          'class'=>'width100',
          'default'=>0,
          'readonly'=>'readonly',
        ])."</td>";
        $tableRow.="<td><button class='removeAdjustment'>".__('Remover')."</button></td>";
      $tableRow.="</tr>";
      
      $adjustmentsTableBodyRows.=$tableRow;
    }
    
    $totalRow="";
    $totalRow.="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td> </td>";
      //$totalRow.="<td class='right'>0</td>";
      $totalRow.="<td class='quantity right'>0</td>";
      $totalRow.="<td class='direction'></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";

    $totalRow.="</tr>";
    
    $adjustmentsTableBody="<tbody>".$totalRow.$adjustmentsTableBodyRows.$totalRow."</tbody>";
    $adjustmentsTable="<table id='adjustments'>".$adjustmentsTableHeader.$adjustmentsTableBody."</table>";
    
    echo $adjustmentsTable;
    echo "<button id='addAdjustment' type='button'>".__('Añadir Ajuste')."</button>";	

    echo $this->Form->submit('Grabar Ajustes',['id'=>'saveAdjustments','name'=>'saveAdjustments','style'=>'width:300px;']);
    echo $this->Form->end();
  echo "</fieldset>";
  /*
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";					
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
    echo "</div>";  
  */
?>
</div>