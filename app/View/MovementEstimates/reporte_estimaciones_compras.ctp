<script>
  $('body').on('change','tr.entries td div input',function(){
    var currentValue = parseFloat($(this).val());
    if (isNaN(currentValue)){
      alert('El valor introducido '+currentValue+' no es un número válido, se reseteó a cero.');
      $(this).val(0);
    }
    else {
      if (currentValue != 0){
        var dayCounter=$(this).closest('tr').attr('day');
        var fuelId = $(this).closest('td').attr('fuelid');
        var previousValue = parseFloat($(this).attr('previousvalue'));
        var counterDifference = currentValue-previousValue
        
        $(this).attr('previousvalue',currentValue)
        updateRowTotal(dayCounter,'entries')
        updateExistenceLaterDays(dayCounter,fuelId,counterDifference, 'entries')
      }
    }
  });

  $('body').on('change','tr.sales td div input',function(){
    var currentValue = parseFloat($(this).val());
    if (isNaN(currentValue)){
      alert('El valor introducido '+currentValue+' no es un número válido, se reseteó a cero.');
      $(this).val(0);
    }
    else {
      var dayCounter=$(this).closest('tr').attr('day');
      var fuelId = $(this).closest('td').attr('fuelid');
      var previousValue = parseFloat($(this).attr('previousvalue'));
      var counterDifference = currentValue-previousValue
      
      $(this).attr('previousvalue',currentValue)
      updateRowTotal(dayCounter,'sales')
      updateExistenceLaterDays(dayCounter,fuelId,counterDifference, 'sales')
    }
  });
  
  function updateRowTotal(dayCounter,rowClass){
    var totalValue=0;
    $('tr.'+rowClass+'[day="'+dayCounter+'"] td div input').each(function(){
      var currentValue=parseFloat($(this).val());
      if (!isNaN(currentValue)){
        totalValue+=currentValue;
      }
    });
    $('tr.'+rowClass+'[day="'+dayCounter+'"] td.total span.amountright').html(roundToTwo(totalValue));
  }
  
  function updateExistenceLaterDays(dayCounter,fuelId,counterDifference,changeType){
    $('tr.inventories').each(function(){
      var rowDayCounter=$(this).attr('day');
      if (rowDayCounter >= dayCounter){
        // update the inventory value
        var inventoryValue=parseFloat($('tr.inventories[day="'+rowDayCounter+'"] td[fuelid="'+fuelId+'"] div input').val())
        if (changeType == 'sales'){
          inventoryValue-=counterDifference
        }
        else {
          inventoryValue+=counterDifference
        }
        $('tr.inventories[day="'+rowDayCounter+'"] td[fuelid="'+fuelId+'"] div input').val(roundToTwo(inventoryValue))
        // update the days value
        var saleValue=parseFloat($('tr.sales[day="'+rowDayCounter+'"] td[fuelid="'+fuelId+'"] div input').val())
        var remainingDays=0
        if (saleValue != 0){
          remainingDays=inventoryValue/saleValue
        }
        $('tr.remainingdays[day="'+rowDayCounter+'"] td[fuelid="'+fuelId+'"]').html(roundToTwo(remainingDays))  
        // update the inventory total 
        updateRowTotal(dayCounter,'inventories')
      }
    });
  }

  function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,2);
		});
	}

  function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}

  $(document).ready(function(){
    formatNumbers();
		
    $('select.fixed option:not(:selected)').attr('disabled', true);
  });

</script>

<div class="stockMovements fullwidth report">

<?php 
	echo '<h2>Estimaciones de Compras por Cliente</h2>';
	echo $this->Form->create('MovementEstimate'); 
		echo "<fieldset>";		
      echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
			//echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Inicio Estimaciones'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userRoleId != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y'),'class'=>'fixed']);
      //echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('Fin Estimaciones'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userRoleId != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y'),'class'=>'fixed']);
		
      //  echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
      //  echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
      if (count($enterprises)>1){
        echo $this->Form->submit(__('Seleccionar Gasolinera'),['id'=>'selectEnterprise','style'=>'width:300px;']); 
      }
      if ($enterpriseId > 0){
        $fileName=$enterprises[$enterpriseId]."_Reporte_Estimaciones_Compras_".date('dmY').".xlsx";
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteEstimacionesCompras',$fileName], ['class' => 'btn btn-primary']); 
      }
      $weekDays=['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
      
      $inventoryDateTime=new DateTime($inventoryDate);
      
      
      if ($enterpriseId == 0){
        echo '<h2>Selecciona una gasolinera para grabar o ver los estimados</h2>';
      }
      else {
        $currentFuelArray=$fuelArray;
        //pr($fuelArray);
        $estimationTableHeader="";
        $estimationTableHeader.="<thead>";
          $estimationTableHeader.="<tr>";
            $estimationTableHeader.="<th>Fecha</th>";
            foreach($fuelArray as $fuelId=>$fuelData){
              //pr($fuelData);
              $estimationTableHeader.="<th class='centered'>".$this->Html->link($fuelData['name'],['controller'=>'products','action'=>'view',$fuelId])."</th>";
            }
            $estimationTableHeader.="<th>".__('Total')."</th>";
          $estimationTableHeader.="</tr>";
        $estimationTableHeader.="</thead>";
        
        $estimationExcelBody=$estimationTableBody="";
        $rowTotal=0;
        $excelTableRow=$tableRow="";
        
        $initialMovementTotal=0;
        foreach($fuelArray as $fuelId=>$fuelData){
          $initialMovementTotal+=$fuelData['movement'];
        }
        
        // INITIAL ROW IS INITIAL EXISTENCE
        $tableRow.="<tr>";
          $tableRow.="<td>Existencia ".$inventoryDateTime->format('d-m-Y')."</td>";
          foreach($fuelArray as $fuelId=>$fuelData){
            $tableRow.="<td style='font-weight:700;font-size:1em;text-align:center;'>".number_format($fuelData['existence'],2,'.',',')."</td>";
            $rowTotal+=$fuelData['existence'];
          }
          $tableRow.="<td style='font-weight:700;font-size:1em;text-align:right;'>".number_format($rowTotal,2,'.',',')."</td>";
        $tableRow.="</tr>";
        
        $lastEstimateDateTime=new DateTime(date('Y-m-d'));
        //pr($estimates['Entry']);
        
        foreach ($dateArray as $dayCounter=>$futureDate){
          $rowTotal=0;
          $futureDateTime=new DateTime($futureDate);
          $dayOfWeek = date('w', strtotime($futureDate));
          // FIRST ROW IS DATE
          $tableRow.="<tr>";
            $tableRow.="<td colspan='6' class='centered' style='font-weight:700;font-size:1.5em;'>".$weekDays[$dayOfWeek]." ".$futureDateTime->format('d-m-Y')."</td>";
          $tableRow.="</tr>";
          $excelTableRow.="<tr>";
            $excelTableRow.="<td colspan='6' class='centered' style='font-weight:700;font-size:1.5em;'>".$weekDays[$dayOfWeek]." ".$futureDateTime->format('d-m-Y')."</td>";
          $excelTableRow.="</tr>";
          // SECOND ROW IS ENTRIES
          $tableRow.="<tr class='entries' day='".$dayCounter."'>";
            $tableRow.="<td>Entradas</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $tableRow.="<td fuelid=".$fuelId.">";
              $tableRow.=$this->Form->input('Entry.'.($futureDateTime->format('Y-m-d')).'.Fuel.'.$fuelId,[
                'label'=>false,
                'type'=>'decimal',
                'class'=>'width100',
                'default'=>(empty($estimates)?0:empty($estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?0:$estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]),
                'previousvalue'=>(empty($estimates)?0:empty($estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?0:$estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]),
              ]);
              $tableRow.="</td>";
            }
            $tableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>0</span></td>";
          $tableRow.="</tr>";
          
          $entryTotalValue=0;
          $excelTableRow.="<tr class='entries' day='".$dayCounter."'>";
            $excelTableRow.="<td>Entradas</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $entryValue=(empty($estimates)?0:empty($estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?0:$estimates['Entry'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]);
              //echo "entryvalue is ".$entryValue."<br/>";
              $entryTotalValue+=$entryValue;
              $excelTableRow.="<td fuelid=".$fuelId.">".number_format($entryValue)."</td>";
            }
            $excelTableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>".number_format($entryTotalValue)."</span></td>";
          $excelTableRow.="</tr>";
          // THIRD ROW IS SALES
          $tableRow.="<tr class='sales' day='".$dayCounter."'>";
            $tableRow.="<td>Ventas</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $tableRow.="<td fuelid='".$fuelId."'>".$this->Form->input('Sale.'.($futureDateTime->format('Y-m-d')).'.Fuel.'.$fuelId,[
                'label'=>false,
                'type'=>'decimal',
                'class'=>'width100',
                'default'=>($dayOfWeek ==0 || $dayOfWeek ==6? 0: (empty($estimates)?0:empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?round($fuelData['movement'],2):$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])),
                'previousvalue'=>($dayOfWeek ==0 || $dayOfWeek ==6? 0: (empty($estimates)?0:empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?round($fuelData['movement'],2):$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])),
              ])."</td>";
              //pr($estimates['Sale']);
              if (!empty($estimates) && !empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]) && $estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId] > 0){
                $lastEstimateDateTime=$futureDateTime;
              }
            }
            $tableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>".number_format(($dayOfWeek ==0 || $dayOfWeek ==6? 0:$initialMovementTotal),2,'.',',')."</span></td>";
          $tableRow.="</tr>";
          $saleTotalValue=0;
          $excelTableRow.="<tr class='sales' day='".$dayCounter."'>";
            $excelTableRow.="<td>Ventas</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $saleValue=($dayOfWeek ==0 || $dayOfWeek ==6? 0: (empty($estimates)?0:empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])?round($fuelData['movement'],2):$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]));
              //echo "saleValue is ".$saleValue."<br/>";
              $saleTotalValue+=$saleValue;
              $excelTableRow.="<td fuelid=".$fuelId.">".number_format($saleValue,2,'.',',')."</td>";
            }
            $excelTableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>".number_format($saleTotalValue,2,'.',',')."</span></td>";
          $excelTableRow.="</tr>";
          // FOURTH ROW IS REMAINING EXISTENCE
          $tableRow.="<tr class='inventories' day='".$dayCounter."'>";
            $tableRow.="<td>Existencias</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              if ($dayOfWeek >0 && $dayOfWeek < 6){
                if (!empty($estimates) && !empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])){
                  
                  //echo "la cantidad estimada es ".$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]."<br/>";
                  $currentFuelArray[$fuelId]['existence']-=$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId];
                }
                else {  
                  $currentFuelArray[$fuelId]['existence']-=$fuelData['movement'];
                }
              }
              else {
                 if (!empty($estimates) && !empty($estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId])){
                  
                  //echo "la cantidad estimada es ".$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId]."<br/>"; $currentFuelArray[$fuelId]['existence']-=$estimates['Sale'][$futureDateTime->format('Y-m-d')]['Fuel'][$fuelId];
                }
              }
              if ($fuelId == 3){
                //echo "la existencia corriente para ".$fuelId." es ".$currentFuelArray[$fuelId]['existence']."<br/>";
              }
              
              $tableRow.="<td fuelid='".$fuelId."'>".$this->Form->input('Inventory.'.$fuelId,['label'=>false,'type'=>'decimal','class'=>'width100','value'=>round($currentFuelArray[$fuelId]['existence'],2),'readonly'=>'readonly'])."</td>";
              $rowTotal+=$currentFuelArray[$fuelId]['existence'];
            }
            $tableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>".number_format($rowTotal,2,'.',',')."</span></td>";
          $tableRow.="</tr>";
          $existenceTotalValue=0;
          $excelTableRow.="<tr class='inventories' day='".$dayCounter."'>";
            $excelTableRow.="<td>Existencias</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $existenceValue=$currentFuelArray[$fuelId]['existence'];
              
              $existenceTotalValue+=$existenceValue;
              $excelTableRow.="<td fuelid=".$fuelId.">".number_format($existenceValue,2,'.',',')."</td>";
            }
            $excelTableRow.="<td class='total' style='font-weight:700;font-size:1.2em;'><span class='amountright'>".number_format($existenceTotalValue,2,'.',',')."</span></td>";
          $excelTableRow.="</tr>";
          
          // FIFTH ROW IS REMAINING DAYS
          $tableRow.="<tr class='remainingdays totalrow' day='".$dayCounter."'>";
            $tableRow.="<td>Días de Venta</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $tableRow.="<td  fuelid='".$fuelId."' style='font-weight:700;font-size:1.3em;text-align:center'>".number_format($fuelData['existence']/$fuelData['movement'],2,'.',',')."</td>";
            }
            $tableRow.="<td></td>";
          $tableRow.="</tr>";
          $excelTableRow.="<tr class='remainingdays totalrow' day='".$dayCounter."'>";
            $excelTableRow.="<td>Días de Venta</td>";
            foreach($currentFuelArray as $fuelId=>$fuelData){
              $excelTableRow.="<td  fuelid='".$fuelId."' style='font-weight:700;font-size:1.3em;text-align:center'>".number_format($fuelData['existence']/$fuelData['movement'],2,'.',',')."</td>";
            }
            $excelTableRow.="<td></td>";
          $excelTableRow.="</tr>";
        }
        
        $estimationTableBody=$tableRow;
        $estimationExcelBody=$excelTableRow;
        
        $estimationTable="<table id='estimacion_ventas'>".$estimationTableHeader.$estimationTableBody."</table>";
        $excelTable="<table id='estimacion_ventas'>".$estimationTableHeader.$estimationExcelBody."</table>";
        $latestCounterDateTime=new DateTime($latestCounterDate);	
        $dayOfWeek = date('w', strtotime($latestCounterDate));
        
        echo "<p class='info'>La cantidad de ventas se base en el último día registrado, siendo ".$weekDays[$dayOfWeek]." ".($latestCounterDateTime->format('d-m-Y')).".</p>";  
        if (empty($estimates)){
          echo '<p class="info">Las estimaciones mostradas son basados en las ventas previas</p>';          
        }
        else {
          echo '<p class="info">Las estimaciones mostradas son basados en las estimaciones previas hasta la fecha '.($lastEstimateDateTime->format('d-m-Y')).'.</p>';          
        }
          
        echo $estimationTable; 
      
    $_SESSION['reporteEstimacionesCompras'] = $excelTable;
    echo $this->Form->submit(__('Guardar'),['id'=>'saveEstimates','name'=>'saveEstimates']);
  }
    echo "</fieldset>";
     
  echo $this->Form->end(); 
?>
</div>