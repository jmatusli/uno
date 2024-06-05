<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
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

  $('body').on('change','.measurement div input',function(){	
    var measurement=parseFloat($(this).val());
    //alert('the measurement value is '+measurement);
    var fuelId=$(this).closest('td').attr('fuelid');
    //alert('fuelid is '+fuelId);
    
    // calculate deviation
    var calculatedExistence=parseFloat($(this).closest('table').find('tr.fuelExistences td[fuelid=\''+fuelId+'\'] input').val());
    //alert('the calculated existence is '+calculatedExistence);
    var deviation=roundToThree(calculatedExistence-measurement)
    $(this).closest('table').find('tr.deviations td[fuelid=\''+fuelId+'\'] div input').val(deviation);
    
    var tableId=$(this).closest('table').attr('id');
    // calculate row total current measurements
    calculateTotalMeasurements(tableId);
    // calculate row total deviations
    calculateTotalDeviations(tableId);
	});	
   
	function calculateTotalMeasurements(tableId){
    var measurementTotal=0;
		$('table#'+tableId).find('tr.measurements td.measurement div input').each(function(){
      measurementTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.measurements td.measurementTotal div input').val(roundToThree(measurementTotal))
	}
  
  function calculateTotalDeviations(tableId){
    var deviationTotal=0
		$('table#'+tableId).find('tr.deviations td.deviation div input').each(function(){
      deviationTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.deviations td.deviationTotal div input').val(roundToThree(deviationTotal))
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
	/*
	$('#TankMeasurementMeasurementDateDay').change(function(){
		//updateExchangeRate();
	});	
	$('#TankMeasurementMeasurementDateMonth').change(function(){
		//updateExchangeRate();
	});	
	$('#TankMeasurementMeasurementDateYear').change(function(){
		//updateExchangeRate();
	});	
	function updateExchangeRate(){
		var measurementday=$('#TankMeasurementMeasurementDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#TankMeasurementMeasurementDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#TankMeasurementMeasurementDateYear').children("option").filter(":selected").val();
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
	*/
  
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
    $('.measurement div input').each(function(){	
      var measurement=parseFloat($(this).val());
      var fuelId=$(this).closest('td').attr('fuelid');
      var fuelExistence=parseFloat($(this).closest('table').find('tr.fuelExistences td[fuelid=\''+fuelId+'\'] input').val());
      var deviation=roundToThree(fuelExistence-measurement);
       $(this).closest('table').find('tr.deviations td[fuelid=\''+fuelId+'\'] div input').val(deviation);
    });
    var tableId="tankMeasurements";
    calculateTotalMeasurements(tableId);
    calculateTotalDeviations(tableId);    
    /*
		var initialChartTanks = document.getElementById('initialTankGraph').getContext('2d');
    var initialTankChart = new Chart(initialChartTanks, {
      type: 'bar',
      data: {
        labels: [<?php echo "'".implode("','",$initialTankData['labels'])."'"; ?>],
        datasets: [{
          label: 'Estado inicial tanques',
          data: [<?php echo implode(",",$initialTankData['values']); ?>],
          backgroundColor: [<?php echo "'".implode("','",$initialTankData['backgroundColors'])."'"; ?>],
          borderColor: [<?php echo "'".implode("','",$initialTankData['borderColors'])."'"; ?>],
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
    
    var finalChartTanks = document.getElementById('finalTankGraph').getContext('2d');
    var finalTankChart = new Chart(finalChartTanks, {
      type: 'bar',
      data: {
          labels: [<?php echo "'".implode("','",$finalTankData['labels'])."'"; ?>],
          datasets: [{
              label: 'Estado final tanques',
              data: [<?php echo implode(",",$finalTankData['values']); ?>],
              backgroundColor: [<?php echo "'".implode("','",$finalTankData['backgroundColors'])."'"; ?>],
              borderColor: [<?php echo "'".implode("','",$finalTankData['borderColors'])."'"; ?>],
              borderWidth: 1
          }]
      },
      options: {
          scales: {
              yAxes: [{
                  ticks: {
                      beginAtZero: true
                  }
              }]
          }
      }
    });
		*/
		$('#saving').addClass('hidden');
    $('#chkEditingMode').trigger('change');
    
    $('#saveTankMeasurements').removeAttr('disabled');
    $('#saveTankMeasurements').css("background-color","#62af56");
	});
  
  $('body').on('click','#saveTankMeasurements',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#TankMeasurementRegistrarMedidasForm',function(e){	
    if($("#saveTankMeasurements").data('clicked'))
    {
      $('#saveTankMeasurements').attr('disabled', 'disabled');
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

<div class="tankMeasurements form sales fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando las medidas...</p>";
    echo "</div>";
  echo "</div>";
  
	echo $this->Form->create('TankMeasurement');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Medidas de Vara')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";	
						echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
            echo $this->Form->input('measurement_date',['label'=>__('Date'),'default'=>$measurementDate,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
						//echo $this->Form->input('comment',['type'=>'textarea','rows'=>2]);
            echo $this->Form->Submit(__('Cambiar Fecha'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
          echo "</div>";
					
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";  
      if ($enterpriseId == 0){
        echo "<h2>".__('Seleccione una gasolinera para ver datos')."</h2>";
      }
      else {
        /*
          echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            echo "<canvas id='initialTankGraph'></canvas>";
          echo "</div>";
        */
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<h3>".__('Totales ventas combustible por turno en galones')."</h3>";
          $fuelTotalTable="";
          $fuelTotalTable.="<table id='fuelTotals'>";
            $fuelTotalTable.="<thead>";
              $fuelTotalTable.="<tr>";
                $fuelTotalTable.="<th style='width:100px;' >".__('Shift')."</th>";
                foreach ($fuels as $fuel){
                  //pr($fuel);
                  $fuelTotalTable.="<th class='centered' style='width:120px;font-size:0.85em;'>";
                  $fuelTotalTable.=$fuel['Product']['abbreviation']."<br/>";
                  $fuelTotalTable.=$fuel['ProductPriceLog'][0]['Currency']['abbreviation']." ".number_format($fuel['ProductPriceLog'][0]['price']*GALLONS_TO_LITERS,2,'.',',');
                  $fuelTotalTable.=" / Gln</th>";
                }
                $fuelTotalTable.="<th class='centered' style='width:120px;'>".__('Total')."</th>";
              $fuelTotalTable.="</tr>";
            $fuelTotalTable.="</thead>";
            
            $fuelTotalTableRows="";
            $fuelGallonTotals=[];
            for ($i=0;$i<count($fuels);$i++){
              $fuelGallonTotals[$i]=0;
            }
            foreach ($shifts as $shiftId=>$shiftName){
              for ($i=0;$i<count($fuels);$i++){
                $fuelGallonTotals[$i]+=$fuels[$i]['Shift'][$shiftId];
              }
              
              $fuelTotalTableRow="";
              $fuelTotalTableRow.="<tr>";
                $fuelTotalTableRow.="<td class='shift'>";
                  $fuelTotalTableRow.=$shiftName;
                  $fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift.'.$shiftId,['label'=>false,'value'=>$shiftId,'type'=>'hidden']);
                $fuelTotalTableRow.="</td>";
                $shiftTotal=0;
                foreach ($fuels as $fuel){
                  $fuelTotalTableRow.="<td class='fuel_".$fuel['Product']['id']." centered'>";
                    //$fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift'.$shiftId[.'.FuelTotal.'.$fuel['Product']['id'],['type'=>'decimal','label'=>false,'readonly'=>'readonly','value'=>$shift['FuelTotals'][$fuel['Product']['id']]]);
                    //$fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift.'.$shiftId.'.Fuel.'.$fuel['Product']['id'],['label'=>false,'value'=>$fuel['Product']['id'],'type'=>'hidden']);
                    
                    $fuelTotalTableRow.=number_format($fuel['Shift'][$shiftId],2,".",",");
                    $shiftTotal+=$fuel['Shift'][$shiftId];
                  $fuelTotalTableRow.="</td>";
                }
                //$fuelTotalTableRow.="<td class='shiftTotal centered'>".$this->Form->input('FuelTotal.Shift'.$shiftId.'.Fuel',['type'=>'decimal','label'=>false,'value'=>$shift['FuelTotals'][0],'readonly'=>'readonly'])."</td>";
                $fuelTotalTableRow.="<td class='shiftTotal centered'>".number_format($shiftTotal,2,".",",")."</td>";
                
              $fuelTotalTableRow.="</tr>";
              $fuelTotalTableRows.=$fuelTotalTableRow;
            }
            
            $totalGallons=0;
            $fuelGallonsTotalRow="";
            $fuelGallonsTotalRow.="<tr class='totalrow'>";
              $fuelGallonsTotalRow.="<td>Totales galones</td>";
              
              
              for ($i=0;$i<count($fuels);$i++){
                $fuelGallonsTotalRow.="<td class='centered'>".number_format($fuelGallonTotals[$i],2,".",",")."</td>";
                $totalGallons+=$fuelGallonTotals[$i];
              }
              $fuelGallonsTotalRow.="<td class='centered'>".number_format($totalGallons,2,".",",")."</td>";
            $fuelGallonsTotalRow.="</tr>";
            
            $fuelPercentageTotalRow="";
            $fuelPercentageTotalRow.="<tr class='totalrow green'>";
              $fuelPercentageTotalRow.="<td>%</td>";
              for ($i=0;$i<count($fuels);$i++){
                $fuelPercentageTotalRow.="<td class='centered percentage'><span class='amountcenter'>".($totalGallons>0?($fuelGallonTotals[$i]/$totalGallons):0)."</span)</td>";
              }
              $fuelPercentageTotalRow.="<td class='centered percentage'><span class='amountcenter'>1</span)</td>";
            $fuelPercentageTotalRow.="</tr>";
            
            
            $fuelTotalTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$fuelPercentageTotalRow.$fuelGallonsTotalRow.$fuelTotalTableRows.$fuelGallonsTotalRow.$fuelPercentageTotalRow."</tbody>";                  
            $fuelTotalTable.=$fuelTotalTableBody;
          $fuelTotalTable.="</table>";
          echo $fuelTotalTable;
        echo "</div>";   
        /*
          echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            echo "<canvas id='finalTankGraph'></canvas>";
          echo "</div>";  
        */  
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<h3>".__('Comprobación existencias con medidas de vara')."</h3>";
          if ($boolEditingToggleVisible){  
            echo "<span>Editar medidas de vara (tanques)</span>";
            echo "<label class='switch'>";
              echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
              echo "<span class='slider round'></span>";
            echo "</label>";
          }
          $measurementTable="";
          $measurementTable.="<table id='tankMeasurements'>";
            $measurementTable.="<thead>";
              $measurementTable.="<tr>";
                $measurementTable.="<th style='width:100px;' > </th>";
                foreach ($fuels as $fuel){
                  $measurementTable.="<th class='centered' style='width:120px;'>".$fuel['Product']['name']." (Galones)</th>";
                }
                $measurementTable.="<th class='centered' style='width:120px;'>".__('Total')."</th>";
              $measurementTable.="</tr>";
            $measurementTable.="</thead>";
            
            $measurementTableRows="";
              $totalInitialExistence=0;
              $initialExistenceRow="";
              $initialExistenceRow.="<tr>";
                $initialExistenceRow.="<td class='shift'>Existencia anterior</td>";
                foreach ($fuels as $fuel){
                  //pr($fuel);
                  $initialExistenceRow.="<td class='centered'>".number_format($fuel['Product']['initial_existence'],2,".",",")."</td>";
                  $totalInitialExistence+=$fuel['Product']['initial_existence'];
                }
                $initialExistenceRow.="<td class='centered'>".number_format($totalInitialExistence,2,".",",")."</td>";
              $initialExistenceRow.="</tr>";
              $measurementTableRows.=$initialExistenceRow;
              
              $totalEntered=0;
              $entryRow="";
              $entryRow.="<tr>";
                $entryRow.="<td class='shift'>Recibido</td>";
                foreach ($fuels as $fuel){
                  $entryRow.="<td class='centered'>".number_format($fuel['Product']['entered'],2,".",",")."</td>";
                  $totalEntered+=$fuel['Product']['entered'];
                }
                $entryRow.="<td class='centered'>".number_format($totalEntered,2,".",",")."</td>";
              $entryRow.="</tr>";
              $measurementTableRows.=$entryRow;
              
              $saldoAfterEntryRow="";
              $saldoAfterEntryRow.="<tr class='totalrow green'>";
                $saldoAfterEntryRow.="<td class='shift'>Saldo</td>";
                foreach ($fuels as $fuel){
                  $saldoAfterEntryRow.="<td class='centered'>".number_format($fuel['Product']['initial_existence']+$fuel['Product']['entered'],2,".",",")."</td>";
                }
                $saldoAfterEntryRow.="<td class='centered'>".number_format($totalInitialExistence+$totalEntered,2,".",",")."</td>";
              $saldoAfterEntryRow.="</tr>";
              $measurementTableRows.=$saldoAfterEntryRow;
            
              $totalExited=0;
              $soldRow="";
              $soldRow.="<tr>";
                $soldRow.="<td class='shift'>Vendido</td>";
                foreach ($fuels as $fuel){
                  $soldRow.="<td class='centered'>".number_format($fuel['Product']['exited'],2,".",",")."</td>";
                  $totalExited+=$fuel['Product']['exited'];
                }
                $soldRow.="<td class='centered'>".number_format($totalExited,2,".",",")."</td>";
              $soldRow.="</tr>";
              $measurementTableRows.=$soldRow;
              
              $totalFinalExistence=0;
              $saldoAfterSaleRow="";
              $saldoAfterSaleRow.="<tr class='totalrow green fuelExistences'>";
                $saldoAfterSaleRow.="<td class='shift'>Saldo Calculado</td>";
                foreach ($fuels as $fuel){
                  $saldoAfterSaleRow.="<td class='centered fuelExistence' fuelid=".$fuel['Product']['id'].">";
                    $saldoAfterSaleRow.=number_format($fuel['Product']['final_existence'],2,".",",");
                    $saldoAfterSaleRow.=$this->Form->input('Fuel.'.$fuel['Product']['id'].'.FuelExistence',[
                      'type'=>'hidden',
                      'label'=>false,
                      'readonly'=>'readonly',
                      'value'=>$fuel['Product']['final_existence']
                    ]);
                  $saldoAfterSaleRow.="</td>";
                  $totalFinalExistence+=$fuel['Product']['final_existence'];
                }
                $saldoAfterSaleRow.="<td class='centered fuelExistenceTotal'>".number_format($totalFinalExistence,2,".",",")."</td>";
              $saldoAfterSaleRow.="</tr>";
              $measurementTableRows.=$saldoAfterSaleRow;
              
              $measurementRow="";
              $measurementRow.="<tr class='measurements'>";
                $measurementRow.="<td>Existencia medida</td>";
                foreach ($fuels as $fuel){
                  $measurementRow.="<td class='centered measurement' fuelid=".$fuel['Product']['id'].">".$this->Form->input('Fuel.'.$fuel['Product']['id'].'.TankMeasurement.measurement_value',[
                    'type'=>'decimal',
                    'label'=>false,
                    'value'=>empty($requestTankMeasurements)?0:$requestTankMeasurements[$fuel['Product']['id']],
                    'style'=>'font-size:0.9em;',
                  ])."</td>";
                }
                $measurementRow.="<td class='centered measurementTotal'>".$this->Form->input('FuelTotal.TankMeasurement.measurement_value',[
                  'type'=>'decimal',
                  'label'=>false,
                  'readonly'=>'readonly',
                  'value'=>empty($requestTankMeasurements)?0:$requestTankMeasurements[$fuel['Product']['id']],
                  'style'=>'font-size:0.9em;',
                ])."</td>";
              $measurementRow.="</tr>";
              $measurementTableRows.=$measurementRow;
              
              $differenceRow="";
              $differenceRow.="<tr class='totalrow deviations'>";
                $differenceRow.="<td class='shift'>Diferencia</td>";
                foreach ($fuels as $fuel){
                  $differenceRow.="<td class='centered deviation' fuelid=".$fuel['Product']['id'].">".$this->Form->input('Fuel.'.$fuel['Product']['id'].'.Deviation',[
                    'type'=>'decimal',
                    'label'=>false,
                    'readonly'=>'readonly',
                    'default'=>$fuel['Product']['final_existence'],
                    'style'=>'font-size:0.9em;'])."</td>";
                }
                $differenceRow.="<td class='centered deviationTotal'>".$this->Form->input('FuelTotal.Deviation',['type'=>'decimal','label'=>false,'readonly'=>'readonly','default'=>$totalFinalExistence,'style'=>'font-size:0.9em;'])."</td>";
              $differenceRow.="</tr>";
              $measurementTableRows.=$differenceRow;
              
            $measurementTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$measurementTableRows."</tbody>";                  
            $measurementTable.=$measurementTableBody;
          $measurementTable.="</table>";
          echo $measurementTable;
          
          echo $this->Form->Submit(__('Grabar Medidas de Vara y Continuar a Informe III Medidas Manguera'),['id'=>'saveTankMeasurements','name'=>'saveTankMeasurements','style'=>'width:300px;']);
        echo "</div>";  

      }
      echo "</div>";
    echo "</div>";
  echo "</fieldset>";
  echo $this->Form->end();
?>
</div>