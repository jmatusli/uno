<style>
  @media print{
		.noprint{ display:none;}
	}

	img.resize {
		width:400px; /* you can use % */
		height: auto;
	}
	
	div, span {
		font-size:1em;
	}
  .extraSmall {
    font-size:0.6em;
  }
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
  
  
	.left {
    text-align:left;
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
  .red {
    color:#f00;
  }
  .redBackground{
    background-color:red!important;
    color:#fff;
  }
  
  table {
		width:100%;
	}
  
  table.bordered {
		border-collapse:collapse; 
	}
  
  td.bordered {
    border:1px solid black;
    vertical-align:middle;
    text-align:center;
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
	$paymentDateTime=new DateTime($paymentDate);
	$url="img/logo_uno.png";
	$imageurl=$this->App->assetUrl($url);
  
  $output='';
  $output.='<table>';
		$output.='<tr>';
			/*
      $output.='<td class="extraSmall left" style="width:20%;">
        <br/>
        <div>Dirección: Km 10.1 Carretera Nueva León 150 m arriba</div>
        <div>Tell:2299-1123</div>
        <div>administración @ornasa.com<div>
        <div>www.ornasa.com</div>
      </td>';
      */
      $output.='<td class="big left" style="width:20%;">
        <br/>
        <div>'.$enterprises[$enterpriseId].'</div>
      </td>';
      $output.='<td class="bold" style="width:53.33%;"><img src="'.$imageurl.'" class="resize"></img></td>';		
			$output.='<td class="bold" style="width:26.66%;">
        <table class="bordered big">
          <thead class="redBackground">
            <tr class="centered" >
              <th>Día</th>
              <th>Mes</th>
              <th>Año</th>
            </tr>
          </thead>
          <tbody class="centered">
            <tr>
              <td>'.$paymentDateTime->format("d").'</td>
              <td>'.$paymentDateTime->format("m").'</td>
              <td>'.$paymentDateTime->format("Y").'</td>
            </tr>
          </tbody>
        </table>  
      </td>';
		$output.='</tr>';
	$output.='</table>';
  
  $fuelTableHead='';
  $fuelTableHead.='<thead>';
    $fuelTableHead.='<tr>';
      $fuelTableHead.='<th style="min-width:130px;"></th>';
      foreach ($fuelTotals as $fuelId => $fuelData){
        $fuelTableHead.='<th class="centered">'.$fuelData['name'].'</th>';
      }
      $fuelTableHead.='<th class="centered">Total</th>';
    $fuelTableHead.='</tr>';
  $fuelTableHead.='</thead>';
  
  $totalGallons=0;
  $fuelQuantityRow='';
  $fuelQuantityRow.='<tr>';
    $fuelQuantityRow.='<td>Galones</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $totalGallons+=$fuelData['total_gallons'];
      $fuelQuantityRow.='<td class="centered number"><span class="number">'.number_format($fuelData['total_gallons'],2,'.',',').'</span></td>';
    }
    $fuelQuantityRow.='<td class="centered number"><span class="number">'.number_format($totalGallons,2,'.',',').'</span></td>';
  $fuelQuantityRow.='</tr>';
  
  $totalPrice=0;
  $fuelTotalPriceRow='';
  $fuelTotalPriceRow.='<tr>';
    $fuelTotalPriceRow.='<td>Precio Total</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $totalPrice+=$fuelData['total_price'];
      $fuelTotalPriceRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($fuelData['total_price'],2,'.',',').'</span></td>';
    }
    $fuelTotalPriceRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($totalPrice,2,'.',',').'</span></td>';
  $fuelTotalPriceRow.='</tr>';
  
  $fuelUnitPriceRow='';
  $fuelUnitPriceRow.='<tr>';
    $fuelUnitPriceRow.='<td>Precio por Litro</td>';
    foreach ($fuelTotals as $fuelId => $fuelData){
      $fuelUnitPriceRow.='<td class="amount"><span class="currency">C$</span><span class="amount right">'.number_format($fuelData['unit_price'],2,'.',',').'</span></td>';
    }
    $fuelUnitPriceRow.='<td class="amount"></td>';
  $fuelUnitPriceRow.='</tr>';
  
  $fuelTableRows=$fuelQuantityRow.$fuelUnitPriceRow.$fuelTotalPriceRow;
  $fuelTableBody='<tbody>'.$fuelTableRows.'</tbody>';
  $fuelOverviewTable='<table>'.$fuelTableHead.$fuelTableBody.'</table>';
  
  $paymentModeByShiftTableHead='';
  $paymentModeByShiftTableHead.='<thead>';
    $paymentModeByShiftTableHead.='<tr>';
      $paymentModeByShiftTableHead.='<th></th>';
      foreach ($shiftList as $shiftId => $shiftName){
        $paymentModeByShiftTableHead.='<th class="centered">'.$shiftName.'</th>';
      }
      $paymentModeByShiftTableHead.='<th class="centered">Total</th>';
    $paymentModeByShiftTableHead.='</tr>';
  $paymentModeByShiftTableHead.='</thead>';
  
  
  $shiftTotals=[];
  $grandTotal=0;
  foreach (array_keys($shiftList) as $shiftId){
    $shiftTotals[$shiftId]['total']=0;
  }
  $paymentModeByShiftTableRows='';
  foreach($paymentModeTotals['PaymentMode'] as $paymentModeId=>$paymentModeData){
    //pr($paymentModeData);
    foreach ($paymentModeData['Currency'] as $currencyId=>$currencyPaymentData){
      if ($paymentModeId == PAYMENT_MODE_CASH || $currencyId == CURRENCY_CS){      
        $paymentModeRow='';
        $paymentModeRow.='<tr>';
          $grandTotal+=$currencyPaymentData['total'];
          $paymentModeRow.='<td>'.$paymentModes[$paymentModeId].($paymentModeId == PAYMENT_MODE_CASH?(" ".$currencies[$currencyId]):"").'</td>';
          foreach (array_keys($shiftList) as $shiftId){
            if ($currencyId == CURRENCY_USD){      
              $shiftTotals[$shiftId]['total']+=$paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total']*$exchangeRate;
            }
            else {
              $shiftTotals[$shiftId]['total']+=$paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total'];
            }
            $paymentModeRow.='<td class="centered amount"><span class="currency '.($currencyId==CURRENCY_USD?" USDCurrency":"CSCurrency").'">'.($currencyId==CURRENCY_USD?"US$":"C$").'</span><span class="amount right">'.number_format($paymentModeTotals['Shift'][$shiftId]['PaymentMode'][$paymentModeId]['Currency'][$currencyId]['total'],2,'.',',').'</span></td>';
          }
          $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($currencyPaymentData['total'],2,'.',',').'</span></td>';
        $paymentModeRow.='</tr>';
        $paymentModeByShiftTableRows.=$paymentModeRow;
      }
    }
  }
  
  foreach($clientPaymentTotals['Client'] as $clientId=>$clientData){
    //pr($clientData);
    $paymentModeRow='';
    $paymentModeRow.='<tr>';
      $grandTotal+=$clientData['total'];
      $paymentModeRow.='<td>'.$fullClients[$clientId].'</td>';
      foreach (array_keys($shiftList) as $shiftId){
        if (array_key_exists($clientId,$clientPaymentTotals['Shift'][$shiftId]['Client'])){
        $shiftTotals[$shiftId]['total']+=$clientPaymentTotals['Shift'][$shiftId]['Client'][$clientId]['total'];
        $paymentModeRow.='<td class="centered amount"><span  class="currency">C$</span><span class="amount right">'.number_format($clientPaymentTotals['Shift'][$shiftId]['Client'][$clientId]['total'],2,'.',',').'</span></td>';
        }
        else {
          $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format(0,2,'.',',').'</span></td>';
        }
      }
      $paymentModeRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($clientData['total'],2,'.',',').'</span></td>';
      
    $paymentModeRow.='</tr>';
    $paymentModeByShiftTableRows.=$paymentModeRow;
  }
  
  $totalRow='';
  $totalRow.='<tr>';
    $totalRow.='<td>TOTAL C$</td>';
    foreach (array_keys($shiftList) as $shiftId){
      $totalRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($shiftTotals[$shiftId]['total'],2,'.',',').'</span></td>';
    }
    $totalRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.number_format($grandTotal,2,'.',',').'</span></td>';
  $totalRow.='</tr>';
  
  $paymentModeByShiftTableBody='<tbody>'.$paymentModeByShiftTableRows.'</tbody>';
  $paymentModeByShiftOverviewTable='<table>'.$paymentModeByShiftTableHead.$paymentModeByShiftTableBody.'</table>';
    
  if ($enterpriseId >0){
    $output.='<h2>Resumen ventas por combustible</h2>';
    $output.=$fuelOverviewTable;
    
    $output.='<h2>Resumen pagos por turno</h2>';
    $output.=$paymentModeByShiftOverviewTable;              
  }
  
  echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
