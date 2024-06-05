<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
  $totalPriceFuels=0;
  $totalPriceLubricants=0;
  $fuelTables="<h4>No se registraron ventas de combustibles este día</h4>";
  $lubricantTable="<h4>No se registraron ventas de lubricantes este día</h4>";
  
  if (!empty($saleShifts)){
    $shiftTables=[];
    
    $totalQuantityFuels=0;
    foreach ($shifts as $shiftId=>$shiftName){
      $fuelTableHeader="<thead>";
        $fuelTableHeader.="<tr>";
          $fuelTableHeader.="<th style='width:100px;' >".$shiftName."</th>";
          $fuelTableHeader.="<th>".__('Hose')."</th>";
          $fuelTableHeader.="<th style='width:120px;' class='centered narrow'>Cierre</th>";
          $fuelTableHeader.="<th style='width:120px;' class='centered narrow'>Inicial</th>";
          $fuelTableHeader.="<th style='width:80px;' class='centered narrow'>Galones</th>";
          $fuelTableHeader.="<th style='width:80px;' class='centered narrow'>Precio C/U</th>";
          $fuelTableHeader.="<th style='width:120px;' class='centered narrow'>Venta</th>";
        $fuelTableHeader.="</tr>";
      $fuelTableHeader.="</thead>";
      
      $fuelTableRows="";  
      $totalShiftQuantityFuels=0;
      $totalShiftPriceFuels=0;
      
      foreach ($saleShifts['Shift'][$shiftId]['Operators'] as $operatorId=>$operatorData){
        $operatorTableRows="";
        $totalShiftQuantityFuels+=$operatorData['Operator']['total_gallons'];
        $totalShiftPriceFuels+=$operatorData['Operator']['total_price'];
        
        $firstRow=true;
        
        foreach ($operatorData['Hoses'] as $hoseId=>$hoseData){
          //pr($hoseData);
          $hoseTableRow="<tr id=".$hoseId.">";
          if ($firstRow){
            //$hoseTableRow.="<td class='operatorName'>".$this->Html->Link($operatorData['Operator']['name'],['controller'=>'operators','action'=>'view',$operatorId])."</td>";
            $hoseTableRow.="<td class='operatorName'>".$operatorData['Operator']['name']."</td>";
            $firstRow=false;
          }
          else{
            $hoseTableRow.="<td>".$hoseData['Hose']['island_name']."</td>";
          }
            $hoseTableRow.="<td class='hose'>";
              //$hoseTableRow.=$this->Html->link($hoseData['Hose']['name'],['controller'=>'hoses','action'=>'detalle',$hoseId]);
              $hoseTableRow.=$hoseData['Hose']['name'];
              $hoseTableRow.=" ";
              //$hoseTableRow.=$this->Html->link($hoseData['Hose']['fuel_name'],['controller'=>'products','action'=>'view',$hoseId]);
              $hoseTableRow.=$hoseData['Hose']['fuel_abbreviation'];              
            $hoseTableRow.="</td>";
            $hoseTableRow.="<td class='final centered'>".$hoseData['Hose']['final']."</td>";
            $hoseTableRow.="<td class='initial centered'>".$hoseData['Hose']['initial']."</td>"; 
            $hoseTableRow.="<td class='quantity centered'>".(empty($hoseData['Hose']['quantity'])?0:number_format($hoseData['Hose']['quantity'],2,".",","))."</td>";                     
            $hoseTableRow.="<td class='price centered'><span class='currency'>C$</span><span class='amountright'>".((float)$hoseData['Hose']['unit_price'])."</span></td>";    
            $hoseTableRow.="<td class='price centered'><span class='currency'>C$</span><span class='amountright'>".(empty($hoseData['Hose']['total_price'])?0:number_format($hoseData['Hose']['total_price'],2,".",","))."</span></td>";               
          $hoseTableRow.="</tr>"; 
          $operatorTableRows.=$hoseTableRow;  
        }
        $operatorTotalRow="<tr class='totalrow green'>";
          $operatorTotalRow.="<td colspan=3>Combustibles ".$shiftName." ".$operatorData['Operator']['name']."</td>";
          $operatorTotalRow.="<td class='centered'> </td>";
          $operatorTotalRow.="<td class='centered'>".number_format($operatorData['Operator']['total_gallons'],2,".",",")."</td>";
          $operatorTotalRow.="<td class='centered'> </td>";
          $operatorTotalRow.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($operatorData['Operator']['total_price'],2,".",",")."</span></td>";
        $operatorTotalRow.="</tr>";
        $operatorTableRows=$operatorTotalRow.$operatorTableRows.$operatorTotalRow;
        
        $fuelTableRows.=$operatorTableRows;
      }
      
      $fuelTotalRow="<tr class='totalrow'>";
        $fuelTotalRow.="<td colspan=3>Total Combustibles ".$shiftName."</td>";
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
      
      $calibrationsTable="";
      $calibrationsTable.="<table id='Turno".$shiftId."calibrationTable' class='calibrations' shiftid=".$shiftId.">";
        $calibrationsTable.="<thead>";
          $calibrationsTable.="<tr>";
          $calibrationsTable.="<th style='min-width:150px;'></th>";
          foreach ($fuels as $fuel){
            $calibrationsTable.="<th style='width:calc((100%-150px)/5);' class='centered narrow'>".$fuel['Product']['name']."</th>";
          }
            $calibrationsTable.="<th style='width:calc((100%-150px)/5);' class='centered narrow'>Total</th>";
          $calibrationsTable.="</tr>";
        $calibrationsTable.="</thead>";
        $calibrationsTable.="<tbody class='nomarginbottom' style='font-size:0.9em'>"; 
          //first row is 
          $calibrationsTable.="<tr id='Shift.".$shiftId.".fuelTotals'>";
            $shiftTotalFuel=0;
          
            $calibrationsTable.="<td>Registrado Lts</td>";
            foreach ($fuels as $fuel){
              $fuelId=$fuel['Product']['id'];
              $fuelLiters=empty($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['fuel_liters'])?0:$saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['fuel_liters'];
              
              $shiftTotalFuel += $fuelLiters;
              
              $calibrationsTable.="<td class='centered' style='width:calc((100%-150px)/5);'>".number_format($fuelLiters,2,".",",")."</td>";
            }
            $calibrationsTable.="<td class='centered' style='width:calc((100%-150px)/5);'>".number_format($shiftTotalFuel,2,".",",")."</td>";
           
          $calibrationsTable.="</tr>";
          // second row is the calibration amounts
          $calibrationsTable.="<tr id='Shift.".$shiftId.".calibrations'>";
            $shiftTotalCalibration=0;
            $calibrationsTable.="<td>Calibraciones Lts</td>";
            foreach ($fuels as $fuel){
              $fuelId=$fuel['Product']['id'];
              $calibrationLiters=empty($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['calibration_liters'])?0:$saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['calibration_liters'];
              $shiftTotalCalibration+=$calibrationLiters;
              $calibrationsTable.="<td class='calibration centered' style='width:calc((100%-150px)/5);'>".number_format($calibrationLiters,2,".",",")."</td>";
            }
            $calibrationsTable.="<td class='calibration centered' id='Shift".$shiftId."Calibration0' style='width:calc((100%-150px)/5);' >".number_format($shiftTotalCalibration,2,".",",")."</td>";
          $calibrationsTable.="</tr>";
          
          // third row is the net fuel totals
          $calibrationsTable.="<tr id='Shift.".$shiftId.".netFuelTotals'>";
            $shiftNetFuel =0;
            $calibrationsTable.="<td>Neto Lts</td>";
            foreach ($fuels as $fuel){
              $fuelId=$fuel['Product']['id'];
              $netLiters=empty($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters'])?0:$saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters'];
              $shiftNetFuel += $netLiters;
              $calibrationsTable.="<td class='netfueltotal centered' style='width:calc((100%-150px)/5);'>".number_format($netLiters,2,".",",")."</td>";
            }
            $calibrationsTable.="<td  class='centered' style='width:calc((100%-150px)/5);'>".number_format($shiftNetFuel,2,".",",")."</td>";
          $calibrationsTable.="</tr>";
          // fourth row is the net fuel total prices
          $calibrationsTable.="<tr id='Shift.".$shiftId.".netFuelTotals'>";
            $shiftNetPrice =0;
            $calibrationsTable.="<td>Precios Neto</td>";
            foreach ($fuels as $fuel){
              $fuelId=$fuel['Product']['id'];
              $netPrice=empty($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters'])?0:($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters']*$fuelPrices[$fuelId]['price']);
              $shiftNetPrice += $netPrice;
              
            
              $calibrationsTable.="<td class='netfueltotalprice centered' style='width:calc((100%-150px)/5);'>".number_format($netPrice,2,".",",")."</td>";
            }
            $calibrationsTable.="<td class='centered' style='width:calc((100%-150px)/5);'>".number_format($shiftNetPrice,2,".",",")."</td>";
          $calibrationsTable.="</tr>";
        $calibrationsTable.="</tbody>";
      $calibrationsTable.="</table>";  
      
      $shiftTables[] =[
        'shift_name'=>$shiftName,
        'fuelTable'=>$fuelTable,
        'calibrationsTable'=>$calibrationsTable
      ];
    }
    //pr($shiftTables);    
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
        $shiftFuelTableHead.="<th class='centered' style='width:120px;'>".$fuel['Product']['name']."</th>";
      }
      $shiftFuelTableHead.="<th class='centered' style='width:120px;'>".__('Total')."</th>";
    $shiftFuelTableHead.="</tr>";
  $shiftFuelTableHead.="</thead>";
  
  $fuelTotalTableRows="";
  $fuelLiterTotals=[];
  foreach ($fuels as $fuel){
    $fuelId=$fuel['Product']['id'];
    $fuelLiterTotals[$fuelId]=0;
  }    
  foreach ($shifts as $shiftId=>$shiftName){
    $fuelTotalTableRow="";
    $fuelTotalTableRow.="<tr>";
      $fuelTotalTableRow.="<td class='shift'>";
        $fuelTotalTableRow.=$shiftName;
      $fuelTotalTableRow.="</td>";
      $shiftTotal=0;
      foreach ($fuels as $fuel){
        $fuelId=$fuel['Product']['id'];
        $netLiters=empty($saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters'])?$fuel['Shift'][$shiftId]*GALLONS_TO_LITERS:$saleShifts['Shift'][$shiftId]['Calibration'][$fuelId]['Fuel']['net_liters'];
        $fuelLiterTotals[$fuelId]+=$netLiters;
        $shiftTotal+=$netLiters;
        
        $fuelTotalTableRow.="<td class='fuel_".$fuel['Product']['id']." centered'>";
          $fuelTotalTableRow.=number_format($netLiters,2,".",",");
        $fuelTotalTableRow.="</td>";
      }
      $fuelTotalTableRow.="<td class='shiftTotal centered'>".number_format($shiftTotal,2,".",",")."</td>";
      
    $fuelTotalTableRow.="</tr>";
    $fuelTotalTableRows.=$fuelTotalTableRow;
  }
  $fuelLitersTotalRow="";
  $fuelLitersTotalRow.="<tr class='totalrow green'>";
    $fuelLitersTotalRow.="<td>Totales litros</td>";
    $totalLiters=0;
    foreach ($fuels as $fuel){
      $fuelId=$fuel['Product']['id'];
      $fuelLitersTotalRow.="<td class='centered'>".number_format($fuelLiterTotals[$fuelId],2,".",",")."</td>";
      $totalLiters+=$fuelLiterTotals[$fuelId];
    }
    $fuelLitersTotalRow.="<td class='centered'>".number_format($totalLiters,2,".",",")."</td>";
  $fuelLitersTotalRow.="</tr>";
  
  $fuelGallonsTotalRow="";
  $fuelGallonsTotalRow.="<tr class='totalrow'>";
    $fuelGallonsTotalRow.="<td>Totales galones</td>";
    $totalGallons=0;
    
    foreach ($fuels as $fuel){
      $fuelId=$fuel['Product']['id'];
      $fuelGallonsTotalRow.="<td class='centered'>".number_format($fuelLiterTotals[$fuelId]/GALLONS_TO_LITERS,2,".",",")."</td>";
      $totalGallons+=$fuelLiterTotals[$fuelId]/GALLONS_TO_LITERS;
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
            //$islandTable.="<th class='centered' style='width:120px;'>".$this->Html->link($hose['name'],['controller'=>'hoses','action'=>'detalle',$hose['id']])."</th>";
            $islandTable.="<th class='centered' style='width:80px;'>".$hose['name']."</th>";
          }
          $islandTable.="<th class='centered' style='width:80px;'>".__('Total')." ".$island['Island']['name']."</th>";
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
            $previousMeasurementRow.="<td class='centered' style='font-size:14px;' hoseid=".$hose['id'].">".number_format((empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value']),2,".",",")."</td>";
          }
          $previousMeasurementRow.="<td class='centered previousMeasurementTotal' style='font-size:14px;'>".number_format($previousMeasurementTotal,2,".",",")."</td>";
        $previousMeasurementRow.="</tr>";
        //$islandTableRows.=$previousMeasurementRow;
        
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
        //$islandTableRows.=$currentMeasurementRow;
        
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
        //$islandTableRows.=$measurementDifferenceRow;
      
        $fuelMovementRow="";
        $fuelMovementRow.="<tr class='fuelTotals'>";
          $fuelMovementRow.="<td>Combustible registrado</td>";
          foreach ($island['Hose'] as $hose){
            $fuelMovementRow.="<td class='centered fuelTotal' hoseid=".$hose['id'].">".number_format($hose['fuel_total']*GALLONS_TO_LITERS,2,".",",")."</td>";
          }
          $fuelMovementRow.="<td class='centered fuelMovementTotal'>".number_format($island['fuel_total']*GALLONS_TO_LITERS,2,".",",")."</td>";
        $fuelMovementRow.="</tr>";
        //$islandTableRows.=$fuelMovementRow;
        
        $deviationRow="";
        $deviationRow.="<tr class='totalrow deviations'>";
          $deviationRow.="<td>Diferencia Lts</td>";
          foreach ($island['Hose'] as $hose){
            $deviationRow.="<td class='centered deviation' hoseid=".$hose['id'].">".number_format(((empty($hoseMeasurements)?0:$hoseMeasurements[$hose['id']])-(empty($hose['HoseMeasurement'])?0:$hose['HoseMeasurement'][0]['measurement_value'])-$hose['fuel_total']*GALLONS_TO_LITERS),2,".",",")."</td>";
          }
          $deviationRow.="<td class='centered deviationTotal'>".number_format(($differenceTotal-$island['fuel_total']*GALLONS_TO_LITERS),2,".",",")."</td>";
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
  
  $saleDateTime=new DateTime($saleDate);
  $namePdf="Informe Diario_Uno_".$saleDateTime->format('dmY');
  $output="";

  $output.="<div class='container-fluid'>";
    $output.="<div class='row'>";
      $output.="<div class='col-sm-12'>";	
        $output.="<h2>".__('Informe Diario Uno ').($saleDateTime->format('d-m-Y'))."</h2>";			
        $output.="<div class='col-sm-4 col-lg-6 totals'>";
          $totalPriceSales=$totalPriceFuels + $totalPriceLubricants;
          
          $output.="<h3>".__('Totales de Venta')."</h3>";	
          $output.="<table>";
            $output.="<tr>";
              $output.="<td style='width:70%'>";
                $output.="<div>Total Combustibles:<span>".$totalPriceFuels."</span></div>";
              $output.="</td>";
              $output.="<td style='width:30%'>";
                //$output.="<div>Fecha:<span class='underline'>".$orderDateTime->format('d-m-Y')."</span></div>";
              $output.="</td>";
            $output.="</tr>";
          /*  
            $output.="<tr>";
              $output.="<td style='width:70%'>";
                $output.="<div>Total Lubricantes:<span class='underline'>".$totalPriceLubricants."</span></div>";
              $output.="</td>";
              $output.="<td style='width:30%'>";
                //$output.="<div>Fecha:<span class='underline'>".$orderDateTime->format('d-m-Y')."</span></div>";
              $output.="</td>";
            $output.="</tr>";
            $output.="<tr>";
              $output.="<td style='width:70%'>";
                $output.="<div>Total Ventas:<span class='underline'>".$totalPriceSales."</span></div>";
              $output.="</td>";
              $output.="<td style='width:30%'>";
                //$output.="<div>Fecha:<span class='underline'>".$orderDateTime->format('d-m-Y')."</span></div>";
              $output.="</td>";
            $output.="</tr>";
          */
          $output.="</table>";	          
          /*
          $output.="<h4>".__('Parámetros')."</h4>";			
          $output.="<dl>";
            $output.="<dt>".__('Exchange Rate')."</dt>";
            $output.="<dd>".$exchangeRateOrder."</dd>";
           
          $output.="</dl>";
          */
        $output.="</div>";
      $output.="</div>";
    $output.="</div>";  
    $output.="<div class='row'>";      
      $output.="<div class='col-sm-12'>";
        $output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          foreach ($shiftTables as $shiftTable){
            $output.="<h3>".__('Combustibles')." ".$shiftTable['shift_name']."</h3>";
            $output.=$shiftTable['fuelTable'];
            $output.=$shiftTable['calibrationsTable'];
          }
        $output.="</div>";   
        $output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          $output.="<h3>".__('Lubricantes')."</h3>";
          $output.=$lubricantTable;
        $output.="</div>";   
        
        $output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          $output.="<h3>".__('Ventas  por Turno por Combustible')."</h3>";
          $output.=$shiftFuelTable;
        $output.="</div>";   
        //$output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
        //  $output.="<canvas id='finalTankGraph'></canvas>";
        //$output.="</div>"; 
        $output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
          $output.="<h3>".__('Medidas de Vara')."</h3>";
          $output.=$measurementTable;
        $output.="</div>"; 
        //pr($islandTables);
        foreach ($islandTables as $islandTable){
          //pr($islandTable);
          $output.="<div class='col-sm-12 col-lg-6' style='padding:5px;'>";
            //$output.="<h3>Medidas electrónicas ".$this->Html->Link($islandTable['islandName'],['controller'=>'islands','action'=>'detalle',$islandTable['islandId']])."</h3>";
            $output.="<h3>Medidas electrónicas ".$islandTable['islandName']."</h3>";
            $output.=$islandTable['table'];
          $output.="</div>";  
        }  
      $output.="</div>";  
    $output.="</div>";
  $output.="</div>";

	/*
	$output.="<div class='purchases view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>COMPRA # ".$order['Order']['order_code']."</div>";
	$orderDateTime=new DateTime($order['Order']['order_date']);
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
				$output.="<div>Proveedor:<span class='underline'>".$order['ThirdParty']['company_name']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
				$output.="<div>Fecha:<span class='underline'>".$orderDateTime->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
				$output.="<div>Costo Total: <span class='underline'>C$ ".number_format($order['Order']['total_price'],2,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";	
	
	$output.="<h3>".__('Lote de Inventario para esta Compra')."</h3>";
	if (!empty($order['StockMovement'])){
		$tableHeader="<thead>";
      $tableHeader="<tr>";
        $tableHeader.="<th>".__('Purchase Date')."</th>";
        $tableHeader.="<th>".__('Product')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Quantity')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Und.')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Total Price')."</th>";
      $tableHeader.="</tr>";
    $tableHeader.="</thead>";
    $tableBody="<tbody>";

    $subtotal=0;
    foreach ($order['StockMovement'] as $stockentry){
      $stockMovementDateTime=new DateTime($stockentry['movement_date']);
      //pr($stockentry);
      if ($stockentry['product_quantity']>0){
        $subtotal+=$stockentry['product_total_price'];
        $outputrow="<tr>";
          $outputrow.="<td>".$stockMovementDateTime->format('d-m-Y')."</td>";
          $outputrow.="<td>".$stockentry['Product']['name']."</td>";
          $outputrow.="<td class='centered'>".number_format($stockentry['product_quantity'],0,".",",")."</td>";
          $outputrow.="<td class='centered'>".number_format($stockentry['product_unit_price'],2,".",",")."</td>";
          $outputrow.="<td class='centered'>C$ ".number_format($stockentry['product_total_price'],2,".",",")."</td>";
        $outputrow.="</tr>";
        $tableBody.=$outputrow;
      }
    }
    $totalRows="";
    $totalRows.="<tr class='totalrow'>";
      $totalRows.="<td>Subtotal</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($subtotal,2,".",",")."</td>";
    $totalRows.="</tr>";
    $totalRows.="<tr class=''>";
      $totalRows.="<td>IVA</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['iva_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
    $totalRows.="<tr class=''>";
      $totalRows.="<td>Renta</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['rent_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
        $totalRows.="<tr class='totalrow'>";
      $totalRows.="<td>Total</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
    
    $tableBody.=$totalRows;
    $tableBody.="</tbody>";
		$table="<table cellpadding = '0' cellspacing = '0'>".$tableHeader.$tableBody."</table>";
    $output.=$table;
	}
	$output.="</div>"; 
*/
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	