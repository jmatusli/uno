<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
/*
   $('body').on('change','#chkEditingMode',function(){	
    if ($(this).is(':checked')){
      $('.measurement div input').attr('readonly',false)
      $('#saveTankMeasurements').attr('disabled',false)
    }
    else {
      $('.measurement div input').attr('readonly','readonly')
      $('#saveTankMeasurements').attr('disabled',true)
    }
	});	
*/

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
    
    $('#saveTankAdjustments').removeAttr('disabled');
    $('#saveTankAdjustments').css("background-color","#62af56");
	});
  
  $('body').on('click','#saveTankAdjustments',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#StockMovementRegistrarAjusteTanqueForm',function(e){	
    if($("#saveTankAdjustments").data('clicked'))
    {
      $('#saveTankAdjustments').attr('disabled', 'disabled');
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

<div class="stockMovements form tankAdjustments fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando los ajustes de inventario para combustibles...</p>";
    echo "</div>";
  echo "</div>";
  
  $sundayMeasurementDateTime=new DateTime(date("Y-m-d",strtotime($inventoryMeasurementStatus['sunday_measurement_date'])));

  echo "<h2>Registrar Ajustes de Inventario para Tanques</h2>";
  if (!$inventoryMeasurementStatus['measurements_present']){
    
    echo "<h3>En este momento no se pueden registrar ajustes de inventario en base a medidas de vara porque las medidas de vara no han estado registrado aun para el útlimo domingo ".$sundayMeasurementDateTime->format('d-m-Y')."</h3>";    
  }
  elseif ($inventoryMeasurementStatus['adjustments_present']){
    echo "<h3>Ya se registraron los ajustes de inventario en base a medida de vara para esta semana,</h3>";    
  }
  else {
    echo $this->Form->create('Adjustment');
    echo "<fieldset id='mainform'>";
      echo "<legend>".('Registrar Ajustes de Inventario para Combustibles en base a Medidas de Vara con fecha '.($sundayMeasurementDateTime->format('d-m-Y')))."</legend>";
      echo $this->Form->input('enterprise_id',['label'=>__('Enterprise'),'default'=>$enterpriseId,'empty'=>['0'=>'--Seleccione Gasolinera--']]);
      echo $this->Form->submit('Cambiar Gasolinera',['name'=>'submitEnterprise','id'=>'submitEnterprise','style'=>'width:200px;']);
      
      echo $this->Form->input('adjustment_date',['label'=>'Fecha ajuste','type'=>'date','dateFormat'=>'DMY','default'=>$inventoryMeasurementStatus['sunday_measurement_date'],'minYear'=>2019,'maxYear'=>date('Y')]);
      echo $this->Form->input('comment',['type'=>'textarea','rows'=>2]);
      echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
      echo "<p class='info'>Se muestran los valores de medida tanto para inventario y para medidas el día ".($sundayMeasurementDateTime->format('d-m-Y'))."</p>";
      
      $adjustmentsTableHeader="";
      $adjustmentsTableHeader.="<thead>";
        $adjustmentsTableHeader.="<tr>";
          $adjustmentsTableHeader.="<th>Producto</th>";
          $adjustmentsTableHeader.="<th class='centered' style='min-width:150px;width:150px;'>Según Inventario</th>";
          $adjustmentsTableHeader.="<th class='centered' style='min-width:150px;width:150px;'>Medida Vara</th>";
          $adjustmentsTableHeader.="<th class='centered' style='min-width:150px;width:150px;'>Ajuste</th>";
          $adjustmentsTableHeader.="<th>Dirección</th>";
        $adjustmentsTableHeader.="</tr>";
      $adjustmentsTableHeader.="</thead>";
      
      $totalInventory=0;
      $totalMeasurement=0;
      $adjustmentsTableBodyRows="";
      foreach ($inventoryMeasurementStatus['fuel_values'] as $fuelId=>$fuelData){
        $inventoryValue=$fuelData['inventory_value'];
        $measurementValue=$fuelData['sunday_measurement_value'];
        
        $totalInventory+=$inventoryValue;
        $totalMeasurement+=$measurementValue;
        
        $tableRow="";
        $tableRow.="<tr style='font-size:0.85em;'>";
          $tableRow.="<td>".$this->Form->input('StockMovement.'.$fuelId.'.product_id',['label'=>false,'value'=>$fuelId,'class'=>'fixed'])."</td>";
          $tableRow.="<td>".$this->Form->input('StockMovement.'.$fuelId.'.inventory_value',['label'=>false,'type'=>'decimal','class'=>'width100','value'=>$inventoryValue,'readonly'=>'readonly'])."</td>";
          $tableRow.="<td>".$this->Form->input('StockMovement.'.$fuelId.'.measurement_value',['label'=>false,'type'=>'decimal','class'=>'width100','value'=>$measurementValue,'readonly'=>'readonly'])."</td>";          $tableRow.="<td>".$this->Form->input('StockMovement.'.$fuelId.'.product_quantity',['label'=>false,'type'=>'decimal','class'=>'width100','value'=>round($inventoryValue-$measurementValue,3),'readonly'=>'readonly'])."</td>";          $tableRow.="<td>".$this->Form->input('StockMovement.'.$fuelId.'.bool_input',['label'=>false,'options'=>$movementDirections,'value'=>($inventoryValue > $measurementValue?0:1),'class'=>'fixed'])."</td>";

        $tableRow.="</tr>";
        
        $adjustmentsTableBodyRows.=$tableRow;
      }
      
      $totalRow="";
      $totalRow.="<tr class='totalrow'>";
        $totalRow.="<td>Total</td>";
        $totalRow.="<td class='right'>".number_format($totalInventory,3,'.',',')."</td>";
        $totalRow.="<td class='right'>".number_format($totalMeasurement,3,'.',',')."</td>";
        $totalRow.="<td class='right'>".number_format($totalInventory - $totalMeasurement,3,'.',',')."</td>";
        $totalRow.="<td>".($totalInventory > $totalMeasurement?'Diminuir':'Aumentar')."</td>";

      $totalRow.="</tr>";
      
      $adjustmentsTableBody="<tbody>".$totalRow.$adjustmentsTableBodyRows.$totalRow."</tbody>";
      $adjustmentsTable="<table>".$adjustmentsTableHeader.$adjustmentsTableBody."</table>";
      
      echo $adjustmentsTable;
      
 
      
      echo $this->Form->submit('Grabar Ajustes',['id'=>'saveTankAdjustments','name'=>'saveTankAdjustments','style'=>'width:300px;']);
      echo $this->Form->end();
    echo "</fieldset>";
  }

	/*
    if ($boolEditingToggleVisible){  
      echo "<span>Editar medidas de vara (tanques)</span>";
      echo "<label class='switch'>";
        echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
        echo "<span class='slider round'></span>";
      echo "</label>";
    }
  */  
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";					
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
    echo "</div>";  
?>
</div>