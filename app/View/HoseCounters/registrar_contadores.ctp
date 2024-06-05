<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>

   $('body').on('change','#chkEditingMode',function(){	
    if ($(this).is(':checked')){
      $('.currentCounter div input').removeAttr('readonly',false)
      $('#saveHoseCounters').attr('disabled',false)
    }
    else {
      $('.currentCounter div input').attr('readonly','readonly')
      $('#saveHoseCounters').attr('disabled',true)
    }
	});	

  $('body').on('change','.currentCounter div input',function(){	
    var currentCounter=parseFloat($(this).val());
    //alert('the current counter value is '+currentCounter);
    var hoseId=$(this).closest('td').attr('hoseid');
    //alert('hoseid is '+hoseId);
    /*
    // calculate measuremement difference
    var previousCounter=parseFloat($(this).closest('table').find('tr.previousCounters td[hoseid=\''+hoseId+'\'] div input').val());
    //alert('the previous counter value is '+previousCounter);
    var counterDifference=roundToThree(currentCounter-previousCounter);
    //alert('the counter difference is '+counterDifference);
    $(this).closest('table').find('tr.counterDifferences td[hoseid=\''+hoseId+'\'] div input').val(counterDifference);
    
    // calculate deviation
    var fuelRegistered=parseFloat($(this).closest('table').find('tr.fuelTotals td[hoseid=\''+hoseId+'\'] div input').val());
    var deviation=roundToThree(counterDifference-fuelRegistered)
    $(this).closest('table').find('tr.deviations td[hoseid=\''+hoseId+'\'] div input').val(deviation);
    */
    var tableId=$(this).closest('table').attr('id');
    // calculate row total current counters
    calculateTotalCurrentCounters(tableId);
    
    /*
    // calculate row total counter differences
    calculateTotalCounterDifferences(tableId);
    // calculate row total deviations
    calculateTotalDeviations(tableId);
    */
	});	
   
	function calculateTotalCurrentCounters(tableId){
    var currentCounterTotal=0;
		$('table#'+tableId).find('tr.currentCounters td.currentCounter div input').each(function(){
      currentCounterTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.currentCounters td.currentCounterTotal div input').val(roundToThree(currentCounterTotal))
	}
  function calculateTotalCounterDifferences(tableId){
    var counterDifferenceTotal=0
		$('table#'+tableId).find('tr.counterDifferences td.counterDifference div input').each(function(){
      counterDifferenceTotal+=parseFloat($(this).val());
    });
    $('table#'+tableId).find('tr.counterDifferences td.counterDifferenceTotal div input').val(roundToThree(counterDifferenceTotal))
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
	$('#HoseCounterHoseCounterDateDay').change(function(){
		//updateExchangeRate();
	});	
	$('#HoseCounterHoseCounterDateMonth').change(function(){
		//updateExchangeRate();
	});	
	$('#HoseCounterHoseCounterDateYear').change(function(){
		//updateExchangeRate();
	});	
	function updateExchangeRate(){
		var orderday=$('#HoseCounterHoseCounterDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#HoseCounterHoseCounterDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#HoseCounterHoseCounterDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#HoseCounterExchangeRate').val(exchangerate);
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
			}
		});
	}
*/	
	$(document).ready(function(){
		$('#HoseCounterHoseCounterDateHour').val('08');
		$('#HoseCounterHoseCounterDateMin').val('00');
		$('#HoseCounterHoseCounterDateMeridian').val('am');
    
    $('.currentCounter div input').each(function(){	
      var currentCounter=parseFloat($(this).val());
      var hoseId=$(this).closest('td').attr('hoseid');
      var previousCounter=parseFloat($(this).closest('table').find('tr.previousCounters td[hoseid=\''+hoseId+'\'] div input').val());
      var counterDifference=roundToThree(currentCounter-previousCounter);
      $(this).closest('table').find('tr.counterDifferences td[hoseid=\''+hoseId+'\'] div input').val(counterDifference);
      var fuelRegistered=parseFloat($(this).closest('table').find('tr.fuelTotals td[hoseid=\''+hoseId+'\'] div input').val());
      var deviation=roundToThree(counterDifference-fuelRegistered)
      $(this).closest('table').find('tr.deviations td[hoseid=\''+hoseId+'\'] div input').val(deviation);
    });
    $('.islandTable').each(function(){	
      var tableId=$(this).attr('id');
      calculateTotalCurrentCounters(tableId);
      calculateTotalCounterDifferences(tableId);
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
	});
  
  $('body').on('click','#saveHoseCounters',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#HoseCounterRegistrarMedidasForm',function(e){	
    if($("#saveHoseCounters").data('clicked'))
    {
      $('#saveHoseCounters').attr('disabled', 'disabled');
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
  
	echo $this->Form->create('HoseCounter');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Contadores Análogos de Islas')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";	
						echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
            echo  $this->Form->input('shift_id',['label'=>__('Shift'),'default'=>$shiftId]);
            echo $this->Form->input('counter_date',['label'=>__('Date'),'type'=>'date','dateFormat'=>'DMY','default'=>$counterDate,'minYear'=>2019,'maxYear'=>date('Y')]);
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
        //echo "<h3>".__('Comprobación contadores mangueras con medidas electrónicas')."</h3>";
        //echo $this->Form->input('bool_editing_mode',['label'=>'Editar Datos','type'=>'checkbox','after'=>'<span class="slider round"></span>']);
        
        if ($boolEditingToggleVisible){  
          echo "<span>Editar contadores análogos</span>";
          echo "<label class='switch'>";
            echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
            echo "<span class='slider round'></span>";
          echo "</label>";
        }
        echo "<p class='warning'>Para cada contador existen tres valores por día, un valor al final de cada turno registrado.  Por defecto se muestran/graban los valores al final del turno de noche, ya que esto es el valor con que comienza el siguiente día. </p>";
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
                $previousCounterRow="";
                $previousCounterTotal=0;
                /*
                $previousCounterRow.="<tr class='previousCounters smallText'>";
                  $previousCounterRow.="<td class='shift'>Contador anterior</td>";
                  foreach ($island['Hose'] as $hose){
                    if (!empty($hose['HoseCounter'])){
                      $previousCounterTotal+=$hose['HoseCounter'][0]['counter_value'];
                      $previousCounterRow.="<td class='centered' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.PreviousHoseCounter.counter_value',['label'=>false,'type'=>'decimal','value'=>$hose['HoseCounter'][0]['counter_value'],'readonly'=>'readonly','class'=>'width100'])."</td>";
                    }
                    else{
                      $previousCounterRow.="<td class='centered' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.PreviousHoseCounter.counter_value',['label'=>false,'type'=>'decimal','value'=>0,'readonly'=>'readonly','class'=>'width100'])."</td>";
                    }
                    
                  }
                  $previousCounterRow.="<td class='centered previousCounterTotal'>".$this->Form->input('HoseTotal.PreviousHoseCounter.counter_value',['label'=>false,'type'=>'decimal','value'=>$previousCounterTotal,'readonly'=>'readonly','class'=>'width100'])."</td>";
                $previousCounterRow.="</tr>";
                $islandTableRows.=$previousCounterRow;
                */
                //pr($requestHoseCounters);
                $currentCounterRow="";
                $currentCounterTotal=0;
                $currentCounterRow.="<tr class='currentCounters smallText'>";
                  $currentCounterRow.="<td class='shift'>Contador Actual</td>";
                  foreach ($island['Hose'] as $hose){
                    $currentCounterTotal+=(empty($requestHoseCounters)?0:$requestHoseCounters[$hose['id']]);
                    $currentCounterRow.="<td class='centered currentCounter' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.HoseCounter.counter_value',['label'=>false,'type'=>'decimal','value'=>(empty($requestHoseCounters)?0:$requestHoseCounters[$hose['id']]),'readonly'=>($boolEditingMode?false:'readonly'),'class'=>'width100'])."</td>";
                  }
                  $currentCounterRow.="<td class='centered currentCounterTotal'>".$this->Form->input('HoseTotal.HoseCounter.counter_value',['label'=>false,'type'=>'decimal','value'=>$currentCounterTotal,'readonly'=>'readonly','class'=>'width100'])."</td>";
                $currentCounterRow.="</tr>";
                $islandTableRows.=$currentCounterRow;
                /*
                $counterDifferenceRow="";
                $counterDifferenceRow.="<tr class='totalrow green counterDifferences smallText'>";
                  $counterDifferenceRow.="<td>Resta medidas</td>";
                  foreach ($island['Hose'] as $hose){
                    $counterDifferenceRow.="<td class='centered counterDifference' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.CounterDifference',['label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly'])."</td>";
                  }
                  $counterDifferenceRow.="<td class='centered counterDifferenceTotal'>".$this->Form->input('HoseTotal.CounterDifference',['label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly'])."</td>";
                $counterDifferenceRow.="</tr>";
                $islandTableRows.=$counterDifferenceRow;
                
                $fuelMovementRow="";
                $fuelMovementRow.="<tr class='fuelTotals'>";
                  $fuelMovementRow.="<td>Combustible registrado</td>";
                  foreach ($island['Hose'] as $hose){
                    $fuelMovementRow.="<td class='centered fuelTotal' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.FuelTotal',['label'=>false,'type'=>'decimal','default'=>$hose['fuel_total'],'readonly'=>'readonly'])."</td>";
                  }
                  $fuelMovementRow.="<td class='centered fuelMovementTotal'>".$this->Form->input('HoseTotal.FuelTotal',['label'=>false,'type'=>'decimal','default'=>$island['fuel_total'],'readonly'=>'readonly'])."</td>";
                $fuelMovementRow.="</tr>";
                $islandTableRows.=$fuelMovementRow;
                
                $deviationRow="";
                $deviationRow.="<tr class='totalrow deviations'>";
                  $deviationRow.="<td>Diferencia</td>";
                  foreach ($island['Hose'] as $hose){
                    $deviationRow.="<td class='centered deviation' hoseid=".$hose['id'].">".$this->Form->input('Hose.'.$hose['id'].'.Deviation',['label'=>false,'type'=>'decimal','default'=>-$hose['fuel_total'],'readonly'=>'readonly'])."</td>";
                  }
                  $deviationRow.="<td class='centered deviationTotal'>".$this->Form->input('HoseTotal.Deviation',['label'=>false,'type'=>'decimal','default'=>-$island['fuel_total'],'readonly'=>'readonly'])."</td>";
                $deviationRow.="</tr>";
                $islandTableRows.=$deviationRow;
                */
              $islandTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$islandTableRows."</tbody>";                  
              $islandTable.=$islandTableBody;
            $islandTable.="</table>";
            echo $islandTable;
          echo "</div>";  
        }  
        echo $this->Form->Submit(__('Grabar Contadores Análogos'),['id'=>'saveHoseCounters','name'=>'saveHoseCounters','style'=>'width:300px;','disabled'=>($boolEditingMode?false:true)]);
        
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>

<script>
	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#MoseCounterCounterDateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#HoseCounterCounterDateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#HoseCounterCounterDateDay').val(daysInPreviousMonth);
		$('#HoseCounterCounterDateMonth').val(previousMonth);
		$('#HoseCounterCounterDateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#HoseCounterCounterDateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#HoseCounterCounterDateYear').val());
		if (nextMonth==0){
			nextMonth=12;
		}
		if (nextMonth==1){
			nextYear+=1;
		}
		if (nextMonth<10){
			nextMonth="0"+nextMonth;
		}
		var daysInNextMonth=daysInMonth(nextMonth,nextYear);
		$('#HoseCounterCounterDateDay').val(daysInNextMonth);
		$('#HoseCounterCounterDateMonth').val(nextMonth);
		$('#HoseCounterCounterDateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
	
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});
</script>