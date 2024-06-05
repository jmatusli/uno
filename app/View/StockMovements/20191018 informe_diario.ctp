<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<?php //pr($finalTankData); ?>
<script>
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	$(document).ready(function(){
    
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
    
    return true;
  });
</script>

<div class="stockMovements view report fullwidth">
<?php 
  $totalPriceFuels=0;
  $totalPriceLubricants=0;
  $fuelTables="<h4>No se registraron ventas de combustibles este día</h4>";
  $lubricantTable="<h4>No se registraron ventas de lubricantes este día</h4>";
  
  if (!empty($saleShifts)){
    $fuelTables="";
    
    $totalQuantityFuels=0;
    foreach ($shifts as $shiftId=>$shiftName){
      $fuelTableHeader="<thead>";
        $fuelTableHeader.="<tr>";
          $fuelTableHeader.="<th style='width:200px;' >".$shiftName."</th>";
          $fuelTableHeader.="<th>".__('Hose')."</th>";
          $fuelTableHeader.="<th style='width:120px;' class='centered narrow'>Cierre</th>";
          $fuelTableHeader.="<th style='width:120px;' class='centered narrow'>Inicial</th>";
          $fuelTableHeader.="<th style='width:80px;' class='centered narrow'>Litros</th>";
          $fuelTableHeader.="<th style='width:80px;' class='centered narrow'>Precio C/U</th>";
          $fuelTableHeader.="<th style='width:100px;' class='centered narrow'>Venta</th>";
        $fuelTableHeader.="</tr>";
      $fuelTableHeader.="</thead>";
      
      $fuelTableRows="";  
      $totalShiftQuantityFuels=0;
      $totalShiftPriceFuels=0;
      
           
      foreach ($islands as $island){
        $islandTableRows="";
        $firstRow=true;
        
        $totalIslandQuantityFuels=0;
        $totalIslandPriceFuels=0;
        foreach ($island['Hose'] as $hose){
          if (!empty($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity'])){
            $totalIslandQuantityFuels+=$saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity'];
            $totalIslandPriceFuels+=$saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price'];
          }
          $hoseTableRow="<tr id=".$hose['id'].">";
            if ($firstRow){
              $hoseTableRow.="<td class='operatorName'>".$this->Html->Link($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['operator_name'],['controller'=>'operators','action'=>'view',$saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['operator_id']])."</td>";
              $firstRow=false;
            }
            else{
              $hoseTableRow.="<td>".$island['Island']['name']."</td>";
            }
            $hoseTableRow.="<td class='hose'>";
              $hoseTableRow.=$this->Html->link($hose['name'],['controller'=>'hoses','action'=>'detalle',$hose['id']]);
              $hoseTableRow.=" ";
              $hoseTableRow.=$this->Html->link($hose['Product']['name'],['controller'=>'products','action'=>'view',$hose['Product']['id']]);                                    
            $hoseTableRow.="</td>";
            $hoseTableRow.="<td class='final centered'>".$hose['HoseCounter'][0]['counter_value']."</td>";
            $hoseTableRow.="<td class='initial centered'>".$hose['HoseCounter'][0]['counter_value']."</td>"; 
            $hoseTableRow.="<td class='quantity centered'>".(empty($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity'])?0:number_format($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity'],2,".",","))."</td>";                     
            $hoseTableRow.="<td class='price centered'><span class='currency'>C$</span><span class='amountright'>".((float)$hose['Product']['default_price'])."</span></td>";    
            $hoseTableRow.="<td class='price centered'><span class='currency'>C$</span><span class='amountright'>".(empty($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price'])?0:number_format($saleShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price'],2,".",","))."</span></td>";               
          $hoseTableRow.="</tr>"; 
          $islandTableRows.=$hoseTableRow;  
        }
        $islandTotalRow="<tr class='totalrow green'>";
          $islandTotalRow.="<td>Combustibles ".$shiftName." ".$island['Island']['name']."</td>";
          $islandTotalRow.="<td class='centered'> </td>";
          $islandTotalRow.="<td class='centered'> </td>";
          $islandTotalRow.="<td class='centered'> </td>";
          $islandTotalRow.="<td class='centered'>".number_format($totalIslandQuantityFuels,2,".",",")."</td>";
          $islandTotalRow.="<td class='centered'> </td>";
          $islandTotalRow.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalIslandPriceFuels,2,".",",")."</span></td>";
        $islandTotalRow.="</tr>";
        $islandTableRows=$islandTotalRow.$islandTableRows.$islandTotalRow;
        
        $totalShiftQuantityFuels+=$totalIslandQuantityFuels;
        $totalShiftPriceFuels+=$totalIslandPriceFuels;
        
        $fuelTableRows.=$islandTableRows;
      }
      
      $fuelTotalRow="<tr class='totalrow'>";
        $fuelTotalRow.="<td>Total Combustibles ".$shiftName."</td>";
        $fuelTotalRow.="<td class='centered'> </td>";
        $fuelTotalRow.="<td class='centered'> </td>";
        $fuelTotalRow.="<td class='centered'> </td>";
        $fuelTotalRow.="<td class='centered'>".number_format($totalShiftQuantityFuels,2,".",",")."</td>";
        $fuelTotalRow.="<td class='centered'> </td>";
        $fuelTotalRow.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalShiftPriceFuels,2,".",",")."</span></td>";
      $fuelTotalRow.="</tr>";
      $fuelTableRows=$fuelTotalRow.$fuelTableRows.$fuelTotalRow;
      
      $totalQuantityFuels+=$totalShiftQuantityFuels;
      $totalPriceFuels+=$totalShiftPriceFuels;
      
      $fuelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$fuelTableRows."</tbody>";                  
      $fuelTable="<table id='Turno_".$shiftName."_combustibles' >".$fuelTableHeader.$fuelTableBody."</table>";
      
      $fuelTables[]=[
        'shift_name'=>$shiftName,
        'table'=>$fuelTable,
      ];
    }
    //pr($fuelTables);    
  }
  
  if (!empty($saleLubricants)){
    $lubricantTableHead="<thead>";
      $lubricantTableHead.="<tr>";
        $lubricantTableHead.="<th>".__('Producto')."</th>";
        $lubricantTableHead.="<th style='width:100px;' class='centered narrow'>Unidades</th>";
        $lubricantTableHead.="<th style='width:120px;' class='centered narrow'>Precio C/U</th>";
        $lubricantTableHead.="<th style='width:120px;' class='centered narrow'>Venta</th>";
      $lubricantTableHead.="</tr>";
    $lubricantTableHead.="</thead>";
    
    $lubricantTableRows="";  
    $totalQuantityLubricants=0;
    $totalPriceLubricants=0;
    for ($i=0;$i<count($saleLubricants);$i++) { 
      $totalQuantityLubricants+=$saleLubricants['Lubricant'][$i]['lubricant_quantity'];
      $totalPriceLubricants+=$saleLubricants['Lubricant'][$i]['lubricant_total_price'];
    
      $lubricantTableRow="";
      $lubricantTableRow.="<tr>";
        $lubricantTableRow.="<td class='lubricantId'>".$this->Html->Link($saleLubricants['Lubricant'][$i]['lubricant_name'],['controller'=>'products','action'=>'view',$saleLubricants['Lubricant'][$i]['lubricant_id']])."</td>";
        $lubricantTableRow.="<td class='lubricantQuantity centered'>".number_format($saleLubricants['Lubricant'][$i]['lubricant_quantity'],2,".",",")."</td>";
        $lubricantTableRow.="<td class='lubricantUnitPrice centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($saleLubricants['Lubricant'][$i]['lubricant_unit_price'],2,".",",")."</span></td>";
        $lubricantTableRow.="<td class='lubricantTotalPrice centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($saleLubricants['Lubricant'][$i]['lubricant_total_price'],2,".",",")."</span></td>";
      $lubricantTableRow.="</tr>";
      $lubricantTableRows.=$lubricantTableRow;
    }
    $lubricantTotalRow="<tr class='totalrow'>";
      $lubricantTotalRow.="<td>Total Lubricantes</td>";
      $lubricantTotalRow.="<td class='centered'>".$totalQuantityLubricants."</td>";
      $lubricantTotalRow.="<td class='centered'> </td>";
      $lubricantTotalRow.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalPriceLubricants,2,".",",")."</span></td>";
    $lubricantTotalRow.="</tr>";
    $lubricantTableRows=$lubricantTotalRow.$lubricantTableRows.$lubricantTotalRow;
    $lubricantTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$lubricantTableRows."</tbody>";                  
    $lubricantTable="<table id='lubricantes'>".$lubricantTableHead.$lubricantTableBody."</table>";;
  }
  
  $shiftFuelTableHead="<thead>";
    $shiftFuelTableHead.="<tr>";
      $shiftFuelTableHead.="<th style='width:100px;' >".__('Shift')."</th>";
      foreach ($fuels as $fuel){
        $shiftFuelTableHead.="<th class='centered' style='width:120px;'>".$fuel['Product']['name']." (Lts)</th>";
      }
      $shiftFuelTableHead.="<th class='centered' style='width:120px;'>".__('Total')."</th>";
    $shiftFuelTableHead.="</tr>";
  $shiftFuelTableHead.="</thead>";
  
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
          
          $fuelTotalTableRow.=number_format($fuel['Shift'][$shiftId]*GALLONS_TO_LITERS,2,".",",");
          $shiftTotal+=$fuel['Shift'][$shiftId];
        $fuelTotalTableRow.="</td>";
      }
      //$fuelTotalTableRow.="<td class='shiftTotal centered'>".$this->Form->input('FuelTotal.Shift'.$shiftId.'.Fuel',['type'=>'decimal','label'=>false,'value'=>$shift['FuelTotals'][0],'readonly'=>'readonly'])."</td>";
      $fuelTotalTableRow.="<td class='shiftTotal centered'>".number_format($shiftTotal*GALLONS_TO_LITERS,2,".",",")."</td>";
      
    $fuelTotalTableRow.="</tr>";
    $fuelTotalTableRows.=$fuelTotalTableRow;
  }
  $fuelLitersTotalRow="";
  $fuelLitersTotalRow.="<tr class='totalrow green'>";
    $fuelLitersTotalRow.="<td>Totales litros</td>";
    $totalLiters=0;
    for ($i=0;$i<count($fuels);$i++){
      $fuelLitersTotalRow.="<td class='centered'>".number_format($fuelGallonTotals[$i]*GALLONS_TO_LITERS,2,".",",")."</td>";
      $totalLiters+=$fuelGallonTotals[$i]*GALLONS_TO_LITERS;
    }
    $fuelLitersTotalRow.="<td class='centered'>".number_format($totalLiters,2,".",",")."</td>";
  $fuelLitersTotalRow.="</tr>";
  
  $fuelGallonsTotalRow="";
  $fuelGallonsTotalRow.="<tr class='totalrow'>";
    $fuelGallonsTotalRow.="<td>Totales galones</td>";
    $totalGallons=0;
    
    for ($i=0;$i<count($fuels);$i++){
      $fuelGallonsTotalRow.="<td class='centered'>".number_format($fuelGallonTotals[$i],2,".",",")."</td>";
      $totalGallons+=$fuelGallonTotals[$i];
    }
    $fuelGallonsTotalRow.="<td class='centered'>".number_format($totalGallons,2,".",",")."</td>";
  $fuelGallonsTotalRow.="</tr>";
  $shiftFuelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$fuelGallonsTotalRow.$fuelLitersTotalRow.$fuelTotalTableRows.$fuelLitersTotalRow.$fuelGallonsTotalRow."</tbody>";                  
  $shiftFuelTable="<table id='fuelTotals'>".$shiftFuelTableHead.$shiftFuelTableBody."</table>";
  
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
            $saldoAfterSaleRow.=$this->Form->input('Fuel.'.$fuel['Product']['id'].'.FuelExistence',['type'=>'hidden','label'=>false,'readonly'=>'readonly','value'=>$fuel['Product']['final_existence']]);
          $saldoAfterSaleRow.="</td>";
          $totalFinalExistence+=$fuel['Product']['final_existence'];
        }
        $saldoAfterSaleRow.="<td class='centered fuelExistenceTotal'>".number_format($totalFinalExistence,2,".",",")."</td>";
      $saldoAfterSaleRow.="</tr>";
      $measurementTableRows.=$saldoAfterSaleRow;
      
      //pr($tankMeasurements);
      $measurementRow="";
      $measurementRow.="<tr class='measurements'>";
        $measurementRow.="<td>Existencia medida</td>";
        $totalMeasurements=0;
        foreach ($fuels as $fuel){
          $measurementRow.="<td class='centered measurement' fuelid=".$fuel['Product']['id'].">".number_format($tankMeasurements[$fuel['Product']['id']],2,".",",")."</td>";
          $totalMeasurements+=$tankMeasurements[$fuel['Product']['id']];
        }
        $measurementRow.="<td class='centered measurementTotal'>".number_format($totalMeasurements,2,".",",")."</td>";
      $measurementRow.="</tr>";
      $measurementTableRows.=$measurementRow;
      
      $differenceRow="";
      $differenceRow.="<tr class='totalrow deviations'>";
        $differenceRow.="<td class='shift'>Diferencia</td>";
        foreach ($fuels as $fuel){
          $differenceRow.="<td class='centered deviation' fuelid=".$fuel['Product']['id'].">".number_format($fuel['Product']['final_existence'],2,".",",")."</td>";
        }
        $differenceRow.="<td class='centered deviationTotal'>".number_format($totalFinalExistence,2,".",",")."</td>";
      $differenceRow.="</tr>";
      $measurementTableRows.=$differenceRow;
      
    $measurementTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$measurementTableRows."</tbody>";                  
    $measurementTable.=$measurementTableBody;
  $measurementTable.="</table>";
  
  $islandTables=[];
  foreach ($islandsWithMeasurements as $island){          
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
        $previousMeasurementRow.="<tr class='previousMeasurements smallText'>";
          $previousMeasurementRow.="<td class='shift'>Medida anterior</td>";
          foreach ($island['Hose'] as $hose){
            //pr($hose);
            $previousMeasurementTotal+=(empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value']);
            $previousMeasurementRow.="<td class='centered' hoseid=".$hose['id'].">".number_format((empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value']),2,".",",")."</td>";
          }
          $previousMeasurementRow.="<td class='centered previousMeasurementTotal'>".number_format($previousMeasurementTotal,2,".",",")."</td>";
        $previousMeasurementRow.="</tr>";
        $islandTableRows.=$previousMeasurementRow;
        
        //pr($requestHoseMeasurements);
        $currentMeasurementRow="";
        $currentMeasurementTotal=0;
        $currentMeasurementRow.="<tr class='currentMeasurements smallText'>";
          $currentMeasurementRow.="<td class='shift'>Medida Actual</td>";
          foreach ($island['Hose'] as $hose){
            $currentMeasurementTotal+=(empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']]);
            $currentMeasurementRow.="<td class='centered currentMeasurement' hoseid=".$hose['id'].">".number_format((empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']]),2,".",",")."</td>";
          }
          $currentMeasurementRow.="<td class='centered currentMeasurementTotal'>".number_format($currentMeasurementTotal,2,".",",")."</td>";
        $currentMeasurementRow.="</tr>";
        $islandTableRows.=$currentMeasurementRow;
        
        $measurementDifferenceRow="";
        $differenceTotal=0;
        $measurementDifferenceRow.="<tr class='totalrow green measurementDifferences smallText'>";
          $measurementDifferenceRow.="<td>Resta medidas</td>";
          foreach ($island['Hose'] as $hose){
            $differenceTotal+=((empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']])-(empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value']));
            $measurementDifferenceRow.="<td class='centered measurementDifference' hoseid=".$hose['id'].">".number_format(((empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']])-(empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value'])),2,".",",")."</td>";
          }
          $measurementDifferenceRow.="<td class='centered measurementDifferenceTotal'>".number_format($differenceTotal,2,".",",")."</td>";
        $measurementDifferenceRow.="</tr>";
        $islandTableRows.=$measurementDifferenceRow;
      
        $fuelMovementRow="";
        $fuelMovementRow.="<tr class='fuelTotals'>";
          $fuelMovementRow.="<td>Combustible registrado</td>";
          foreach ($island['Hose'] as $hose){
            $fuelMovementRow.="<td class='centered fuelTotal' hoseid=".$hose['id'].">".number_format($hose['fuel_total'],2,".",",")."</td>";
          }
          $fuelMovementRow.="<td class='centered fuelMovementTotal'>".number_format($island['fuel_total'],2,".",",")."</td>";
        $fuelMovementRow.="</tr>";
        $islandTableRows.=$fuelMovementRow;
        
        $deviationRow="";
        $deviationRow.="<tr class='totalrow deviations'>";
          $deviationRow.="<td>Diferencia</td>";
          foreach ($island['Hose'] as $hose){
            $deviationRow.="<td class='centered deviation' hoseid=".$hose['id'].">".number_format((((empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']])-(empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value']))-$hose['fuel_total']),2,".",",")."</td>";
          }
          $deviationRow.="<td class='centered deviationTotal'>".number_format(($differenceTotal-$island['fuel_total']),2,".",",")."</td>";
        $deviationRow.="</tr>";
        $islandTableRows.=$deviationRow;
        
      $islandTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$islandTableRows."</tbody>";                  
      $islandTable.=$islandTableBody;
    $islandTable.="</table>";
    $islandTables[]=[
      'islandId'=>$island['Island']['id'],
      'islandName'=>$island['Island']['name'],
      'table'=>$islandTable,
    ];
  }  
  
  
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-sm-12'>";	
        echo "<div class='col-sm-8 col-lg-6'>";	
          echo $this->Form->create('Report');
          echo "<fieldset>";
            echo "<legend>".__('Informe Diario')."</legend>";
						echo  $this->Form->input('enterprise_id',['label'=>__('Enterprise'),'default'=>0]);
            echo $this->Form->input('order_date',['label'=>__('Date'),'type'=>'date','default'=>$saleDate,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->Submit(__('Cambiar Fecha'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
          echo "</fieldset>";  
          echo $this->Form->end();    
        echo "</div>";
        echo "<div class='col-sm-4 col-lg-6 totals'>";
          $totalPriceSales=$totalPriceFuels + $totalPriceLubricants;
          
          echo "<h4>".__('Totales de Venta')."</h4>";			
          echo "<dl>";
            echo "<dt>Precio TotalCombustibles</dt>";
            echo "<dd>C$ ".$totalPriceFuels."</dd>";
            echo "<dt>Precio Total Lubricantes</dt>";
            echo "<dd>C$ ".$totalPriceLubricants."</dd>";
            echo "<dt>Precio Total Ventas</dt>";
            echo "<dd>C$ ".$totalPriceSales."</dd>";
          echo "</dl>";
          echo "<h4>".__('Parámetros')."</h4>";			
          echo "<dl>";
            echo "<dt>".__('Exchange Rate')."</dt>";
            echo "<dd>".$exchangeRateOrder."</dd>";
           
          echo "</dl>";
          //echo "<h4>".__('Actions')."</h3>";
          //echo "<ul>";
          //if ($bool_client_add_permission) {
          //  echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
          //}
          //echo "</ul>";
        echo "</div>";
      echo "</div>";
    echo "</div>";  
    echo "<div class='row'>";      
      echo "<div class='col-sm-12'>";
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          foreach ($fuelTables as $fuelTable){
            echo "<h3>".__('Combustibles')." ".$fuelTable['shift_name']."</h3>";
            echo $fuelTable['table'];
          }
        echo "</div>";   
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<h3>".__('Lubricantes')."</h3>";
          echo $lubricantTable;
        echo "</div>";   
        
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<h3>".__('Ventas  por Turno por Combustible')."</h3>";
          echo $shiftFuelTable;
        echo "</div>";   
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<canvas id='finalTankGraph'></canvas>";
        echo "</div>"; 
        echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          echo "<h3>".__('Medidas de Vara')."</h3>";
          echo $measurementTable;
        echo "</div>"; 
        //pr($islandTables);
        foreach ($islandTables as $islandTable){
          //pr($islandTable);
          echo "<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            echo "<h3>Medidas electrónicas ".$this->Html->Link($islandTable['islandName'],['controller'=>'islands','action'=>'detalle',$islandTable['islandId']])."</h3>";
            echo $islandTable['table'];
          echo "</div>";  
        }  
      echo "</div>";  
    echo "</div>";
  echo "</div>";

?>
</div>