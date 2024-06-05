<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>

   $('body').on('change','#chkEditingMode',function(){	
    if ($(this).is(':checked')){
      $('.currentMeasurement div input').removeAttr('readonly',false)
      $('#saveHoseMeasurements').attr('disabled',false)
    }
    else {
      $('.currentMeasurement div input').attr('readonly','readonly')
      $('#saveHoseMeasurements').attr('disabled',true)
    }
	});	

  $('body').on('change','.currentMeasurement div input',function(){	
    var currentMeasurement=parseFloat($(this).val());
    //alert('the current measurement value is '+currentMeasurement);
    var hoseId=$(this).closest('td').attr('hoseid');
    //alert('hoseid is '+hoseId);
    
    // calculate measuremement difference
    var previousMeasurement=parseFloat($(this).closest('table').find('tr.previousMeasurements td[hoseid=\''+hoseId+'\'] div input').val());
    //alert('the previous measurement value is '+previousMeasurement);
    var measurementDifference=roundToThree(currentMeasurement-previousMeasurement);
    //alert('the measurement difference is '+measurementDifference);
    $(this).closest('table').find('tr.measurementDifferences td[hoseid=\''+hoseId+'\'] div input').val(measurementDifference);
    
    // calculate deviation
    var fuelRegistered=parseFloat($(this).closest('table').find('tr.fuelTotals td[hoseid=\''+hoseId+'\'] div input').val());
    var deviation=roundToThree(measurementDifference-fuelRegistered)
    $(this).closest('table').find('tr.deviations td[hoseid=\''+hoseId+'\'] div input').val(deviation);
    
    var tableId=$(this).closest('table').attr('id');
    // calculate row total current measurements
    calculateTotalCurrentMeasurements(tableId);
    // calculate row total measurement differences
    calculateTotalMeasurementDifferences(tableId);
    // calculate row total deviations
    calculateTotalDeviations(tableId);
	});	
   
	function calculateTotalCurrentMeasurements(tableId){
    var currentMeasurementTotal=0;
		$('table#'+tableId).find('tr.currentMeasurements td.currentMeasurement div input').each(function(){
      currentMeasurementTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.currentMeasurements td.currentMeasurementTotal div input').val(roundToThree(currentMeasurementTotal))
	}
  function calculateTotalMeasurementDifferences(tableId){
    var measurementDifferenceTotal=0
		$('table#'+tableId).find('tr.measurementDifferences td.measurementDifference div input').each(function(){
      measurementDifferenceTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.measurementDifferences td.measurementDifferenceTotal div input').val(roundToThree(measurementDifferenceTotal))
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
	$('#HoseMeasurementHoseMeasurementDateDay').change(function(){
		//updateExchangeRate();
	});	
	$('#HoseMeasurementHoseMeasurementDateMonth').change(function(){
		//updateExchangeRate();
	});	
	$('#HoseMeasurementHoseMeasurementDateYear').change(function(){
		//updateExchangeRate();
	});	
	function updateExchangeRate(){
		var orderday=$('#HoseMeasurementHoseMeasurementDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#HoseMeasurementHoseMeasurementDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#HoseMeasurementHoseMeasurementDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#HoseMeasurementExchangeRate').val(exchangerate);
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
			}
		});
	}
*/	
	$(document).ready(function(){
		$('#HoseMeasurementHoseMeasurementDateHour').val('08');
		$('#HoseMeasurementHoseMeasurementDateMin').val('00');
		$('#HoseMeasurementHoseMeasurementDateMeridian').val('am');
    
    $('.currentMeasurement div input').each(function(){	
      var currentMeasurement=parseFloat($(this).val());
      var hoseId=$(this).closest('td').attr('hoseid');
      var previousMeasurement=parseFloat($(this).closest('table').find('tr.previousMeasurements td[hoseid=\''+hoseId+'\'] div input').val());
      var measurementDifference=roundToThree(currentMeasurement-previousMeasurement);
      $(this).closest('table').find('tr.measurementDifferences td[hoseid=\''+hoseId+'\'] div input').val(measurementDifference);
      var fuelRegistered=parseFloat($(this).closest('table').find('tr.fuelTotals td[hoseid=\''+hoseId+'\'] div input').val());
      var deviation=roundToThree(measurementDifference-fuelRegistered)
      $(this).closest('table').find('tr.deviations td[hoseid=\''+hoseId+'\'] div input').val(deviation);
    });
    $('.islandTable').each(function(){	
      var tableId=$(this).attr('id');
      calculateTotalCurrentMeasurements(tableId);
      calculateTotalMeasurementDifferences(tableId);
      calculateTotalDeviations(tableId);    
    });
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
    
    $('#saveHoseMeasurements').removeAttr('disabled');
    $('#saveHoseMeasurements').css("background-color","#62af56");
	});
  
  $('body').on('click','#saveHoseMeasurements',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#HoseMeasurementRegistrarMedidasForm',function(e){	
    if($("#saveHoseMeasurements").data('clicked'))
    {
      $('#saveHoseMeasurements').attr('disabled', 'disabled');
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

<div class="orders form fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando las medidas...</p>";
    echo "</div>";
  echo "</div>";
  
	echo $this->Form->create('HoseMeasurement');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Medidas Electrónicas de Islas')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";	
						echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
            echo $this->Form->input('measurement_date',['label'=>__('Date'),'type'=>'date','dateFormat'=>'DMY','default'=>$measurementDate,'minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->Submit(__('Cambiar Fecha'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
            echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
						echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
						//echo $this->Form->input('comment',['type'=>'textarea','rows'=>2]);
          echo "</div>";
					
        echo "</div>";
      echo "</div>"; 
      
      /*
        echo "<div class='row'>";  
          echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            echo "<canvas id='initialTankGraph'></canvas>";
          echo "</div>";
          echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            echo "<canvas id='finalTankGraph'></canvas>";
          echo "</div>";  
        echo "</div>"; 
      */ 
        
      echo "<div class='row'>";  
        echo "<h3>".__('Comprobación contadores mangueras con medidas electrónicas')."</h3>";
        //echo $this->Form->input('bool_editing_mode',['label'=>'Editar Datos','type'=>'checkbox','after'=>'<span class="slider round"></span>']);
        
        if ($boolEditingToggleVisible){  
          echo "<span>Editar medidas electrónicas</span>";
          echo "<label class='switch'>";
            echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
            echo "<span class='slider round'></span>";
          echo "</label>";
        }
        foreach ($islands as $island){
          echo "<div class='col-sm-12 col-xl-4' style='padding:5px;'>";
            echo "<h3>".$this->Html->Link($island['Island']['name'],['controller'=>'islands','action'=>'detalle',$island['Island']['id']])."</h3>";
            $islandTable="";
            $islandTable.="<table id='".str_replace(' ','_',$island['Island']['name'])."' class='islandTable'>";
              $islandTable.="<thead>";
                $islandTable.="<tr>";
                  $islandTable.="<th style='width:100px;'>".$island['Island']['name']."</th>";
                  foreach ($island['Hose'] as $hose){
                    $islandTable.="<th class='centered' style='width:120px;'>".$this->Html->link($hose['name'],['controller'=>'hoses','action'=>'detalle',$hose['id']])."</th>";
                  }
                  $islandTable.="<th class='centered' style='width:120px;'>".__('Total')." ".$island['Island']['name']."</th>";
                $islandTable.="</tr>";
              $islandTable.="</thead>";
              $islandTableRows="";
                $previousMeasurementRow="";
                $previousMeasurementTotal=0;
                $previousMeasurementRow.="<tr class='previousMeasurements'>";
                  $previousMeasurementRow.="<td class='shift'>Medida anterior</td>";
                  foreach ($island['Hose'] as $hose){
                    if (!empty($hose['HoseMeasurement'])){
                      $previousMeasurementTotal+=$hose['HoseMeasurement'][0]['measurement_value'];
                      $previousMeasurementRow.="<td class='centered' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.PreviousHoseMeasurement.measurement_value',['label'=>false,'type'=>'decimal','value'=>$hose['HoseMeasurement'][0]['measurement_value'],'readonly'=>'readonly','class'=>'width100','style'=>'font-size:14px'])."</td>";
                    }
                    else{
                      $previousMeasurementRow.="<td class='centered' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.PreviousHoseMeasurement.measurement_value',['label'=>false,'type'=>'decimal','value'=>0,'readonly'=>'readonly','class'=>'width100','style'=>'font-size:14px'])."</td>";
                    }
                    
                  }
                  $previousMeasurementRow.="<td class='centered previousMeasurementTotal'>".$this->Form->input('HoseTotal.PreviousHoseMeasurement.measurement_value',['label'=>false,'type'=>'decimal','value'=>$previousMeasurementTotal,'readonly'=>'readonly','class'=>'width100','style'=>'font-size:14px'])."</td>";
                $previousMeasurementRow.="</tr>";
                $islandTableRows.=$previousMeasurementRow;
                
                //pr($requestHoseMeasurements);
                $currentMeasurementRow="";
                $currentMeasurementTotal=0;
                $currentMeasurementRow.="<tr class='currentMeasurements smallText'>";
                  $currentMeasurementRow.="<td class='shift'>Medida Actual</td>";
                  foreach ($island['Hose'] as $hose){
                    $currentHostMeasurement=empty($requestHoseMeasurements)?0:$requestHoseMeasurements[$hose['id']];
                    /* 20191105 DO NOT ESTABLISH THE HOSE MEASUREMENT IF NOT REGISTERED YET, NEEDS TO BE REGISTERED FROM 0
                    //echo "current hose measurement is ".$currentHostMeasurement."<br/>";
                    
                    if ($currentHostMeasurement==0){
                      if (!empty($hose['HoseMeasurement'])){
                        $currentHostMeasurement=$hose['HoseMeasurement'][0]['measurement_value'];
                      }
                      $currentHostMeasurement+=$hose['fuel_total'];
                    }
                    //echo "current hose measurement is ".$currentHostMeasurement."<br/>";
                    */
                    $currentMeasurementTotal+=$currentHostMeasurement;
                    $currentMeasurementRow.="<td class='centered currentMeasurement' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.HoseMeasurement.measurement_value',[
                      'label'=>false,
                      'type'=>'decimal',
                      'value'=>$currentHostMeasurement,
                      'readonly'=>($boolEditingMode?false:'readonly'),
                      'class'=>'width100',
                      'style'=>'font-size:14px'])."</td>";
                  }
                  $currentMeasurementRow.="<td class='centered currentMeasurementTotal'>".$this->Form->input('HoseTotal.HoseMeasurement.measurement_value',['label'=>false,'type'=>'decimal','value'=>$currentMeasurementTotal,'readonly'=>'readonly','class'=>'width100','style'=>'font-size:14px'])."</td>";
                $currentMeasurementRow.="</tr>";
                $islandTableRows.=$currentMeasurementRow;
                
                $measurementDifferenceRow="";
                $measurementDifferenceRow.="<tr class='totalrow green measurementDifferences smallText'>";
                  $measurementDifferenceRow.="<td>Resta medidas</td>";
                  foreach ($island['Hose'] as $hose){
                    $measurementDifferenceRow.="<td class='centered measurementDifference' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.MeasurementDifference',['label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                  }
                  $measurementDifferenceRow.="<td class='centered measurementDifferenceTotal'>".$this->Form->input('HoseTotal.MeasurementDifference',['label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                $measurementDifferenceRow.="</tr>";
                $islandTableRows.=$measurementDifferenceRow;
              
                $fuelMovementRow="";
                $fuelMovementRow.="<tr class='fuelTotals'>";
                  $fuelMovementRow.="<td>Combustible registrado</td>";
                  foreach ($island['Hose'] as $hose){
                    $fuelMovementRow.="<td class='centered fuelTotal' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.FuelTotal',['label'=>false,'type'=>'decimal','value'=>$hose['fuel_total'],'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                  }
                  $fuelMovementRow.="<td class='centered fuelMovementTotal'>".$this->Form->input('HoseTotal.FuelTotal',['label'=>false,'type'=>'decimal','default'=>$island['fuel_total'],'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                $fuelMovementRow.="</tr>";
                $islandTableRows.=$fuelMovementRow;
                
                $deviationRow="";
                $deviationRow.="<tr class='totalrow deviations' >";
                  $deviationRow.="<td>Diferencia</td>";
                  foreach ($island['Hose'] as $hose){
                    $deviationRow.="<td class='centered deviation' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.Deviation',['label'=>false,'type'=>'decimal','default'=>-$hose['fuel_total'],'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                  }
                  $deviationRow.="<td class='centered deviationTotal'>".$this->Form->input('HoseTotal.Deviation',['label'=>false,'type'=>'decimal','default'=>-$island['fuel_total'],'readonly'=>'readonly','style'=>'font-size:16px'])."</td>";
                $deviationRow.="</tr>";
                $islandTableRows.=$deviationRow;
                
              $islandTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$islandTableRows."</tbody>";                  
              $islandTable.=$islandTableBody;
            $islandTable.="</table>";
            echo $islandTable;
          echo "</div>";  
        } 
        echo $this->Form->Submit(__('Grabar Medidas Electrónicas y Continuar a Informe IV Recibos'),[
          'id'=>'saveHoseMeasurements',
          'name'=>'saveHoseMeasurements',
          'style'=>'width:600px;',
          'disabled'=>($boolEditingMode?false:true)
        ]);
        
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>