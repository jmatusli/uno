<script>
	function formatNumbers(){
		$("td.number span.amountcenter").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
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
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>
<div class="orders index purchases fullwidth">
<?php 
  $excelOutput='';

  $utilitySummaryTableHeader="<thead>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th>Combustible</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Precio</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Costo</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
      $utilitySummaryTableHeader.="</tr>";
    $utilitySummaryTableHeader.="</thead>";
    
    $totalPrice=0;
    $totalCost=0;
    $utilityTableRows="";
    $excelUtilityTableRows="";
    foreach ($fuelUtilities as $fuelId=>$fuelUtility){
      $totalPrice+=$fuelUtility['price'];
      $totalCost+=$fuelUtility['cost'];
      
      $utilityTableRows.="<tr>";
        $utilityTableRows.="<td>".$this->Html->link($fuelUtility['name'],['controller'=>'products','action'=>'view',$fuelId])."</td>";  
        
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$fuelUtility['price']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$fuelUtility['cost']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($fuelUtility['price']-$fuelUtility['cost'])."</span></td>";  
        if (!empty($fuelUtility['price'])){
          $utilityTableRows.="<td class='centered percentage'><span>".(($fuelUtility['price']-$fuelUtility['cost'])/$fuelUtility['price'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
        }              
      $utilityTableRows.="</tr>";      
      
      $excelUtilityTableRows.="<tr>";
        $excelUtilityTableRows.="<td>".$fuelUtility['name']."</td>";  
        
        $excelUtilityTableRows.="<td class='centered CScurrency'>".$fuelUtility['price']."</td>"; 
        $excelUtilityTableRows.="<td class='centered CScurrency'>".$fuelUtility['cost']."</td>";  
        $excelUtilityTableRows.="<td class='centered CScurrency'>".($fuelUtility['price']-$fuelUtility['cost'])."</td>";  
        if (!empty($fuelUtility['price'])){
          $excelUtilityTableRows.="<td class='centered percentage'>".(($fuelUtility['price']-$fuelUtility['cost'])/$fuelUtility['price'])."</td>";
        }
        else {
          $excelUtilityTableRows.="<td class='centered percentage'>0</td>";
        }              
      $excelUtilityTableRows.="</tr>";      
    }
    
    $utilityTableTotalRow="";
    $utilityTableTotalRow.="<tr class='totalrow'>";
      $utilityTableTotalRow.="<td>Total</td>";  
      $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalPrice."</span></td>";  
      $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalCost."</span></td>";  
      $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($totalPrice-$totalCost)."</span></td>"; 
      if (!empty($totalPrice)){
        $utilityTableTotalRow.="<td class='centered percentage'><span>".(($totalPrice-$totalCost)/$totalPrice)."</span></td>";
      }
      else {
        $utilityTableTotalRow.="<td class='centered percentage'><span>0</span></td>";
      }
    $utilityTableTotalRow.="</tr>";
    
    $excelUtilityTableTotalRow="";
    $excelUtilityTableTotalRow.="<tr class='totalrow'>";
      $excelUtilityTableTotalRow.="<td>Total C$</td>";  
      $excelUtilityTableTotalRow.="<td class='centered CScurrency'>".$totalPrice."</td>";  
      $excelUtilityTableTotalRow.="<td class='centered CScurrency'>".$totalCost."</td>";  
      $excelUtilityTableTotalRow.="<td class='centered CScurrency'>".($totalPrice-$totalCost)."</td>"; 
      if (!empty($totalPrice)){
        $excelUtilityTableTotalRow.="<td class='centered percentage'>".(($totalPrice-$totalCost)/$totalPrice)."</td>";
      }
      else {
        $excelUtilityTableTotalRow.="<td class='centered percentage'>0</td>";
      }
    $excelUtilityTableTotalRow.="</tr>";
    
    $utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
    $excelUtilityTableBody="<tbody>".$excelUtilityTableTotalRow.$excelUtilityTableRows.$excelUtilityTableTotalRow."</tbody>";
  $utilityTable="<table id='utilidad'>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
  
  $excelUtilityTable="<table id='utilidad'>".$utilitySummaryTableHeader.$excelUtilityTableBody."</table>";
  //pr($excelUtilityTable);
  $excelOutput.=$excelUtilityTable;      
        
	echo "<h2>".__('Reporte de Ventas')."</h2>";
	
	echo "<div class='container-fluid'>";
		echo "<div class='rows'>";
			echo "<div class='col-sm-12 col-md-6'>";			
				echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
          //echo $this->Form->input('Report.enterprise_id',['label'=>'Gasolinera','default'=>$enterpriseId]);
          echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          echo $this->Form->input('Report.display_option_id',['label'=>'Mostrar','default'=>$displayOptionId]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=date('d_m_Y')."_".$enterprises[$enterpriseId]."_Reporte_Ventas.xlsx";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteVentas',$fileName], ['class' => 'btn btn-primary']);
        }
			echo "</div>";
			echo "<div class='col-sm-12 col-md-6'>";			
      if ($userRoleId == ROLE_ADMIN){
        echo "<h2>Utilidad de Ventas por Combustible</h2>";
        echo $utilityTable;
      }
      
      /*
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          if ($bool_add_permission) { 
            echo "<li>".$this->Html->link(__('New Purchase'), array('action' => 'crearEntrada'))."</li>";
            echo "<br/>";
          }
          if ($bool_provider_index_permission) { 		
            echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))." </li>";
          }
          if ($bool_provider_add_permission) { 
            echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))." </li>";
          } 
        echo "</ul>";
      */  
			echo "</div>";
		echo "</div>";
	echo "</div>";
?>
</div>
<div class='related'>
<?php
  if ($enterpriseId == 0){
    echo '<h3>Seleccione una gasolinera para ver datos</h3>';
  }
  else {
  
    foreach ($classifiers['enum'] as $enumId=>$enumData){
      $salesTableHead='';
      $salesTableHead.='<thead>';
        $salesTableHead.='<tr>';
          $salesTableHead.='<th>Fecha Informe</th>';
          foreach ($fuels as $fuelId=>$fuelName){
            $salesTableHead.='<th>'.$fuelName.'</th>';
          }
          //foreach ($fuels as $fuelId=>$fuelName){
          //  $salesTableHead.='<th>C$ '.$fuelName.'</th>';
          //}
          $salesTableHead.='<th>Galones Total</th>';
          $salesTableHead.='<th>C$ Total</th>';
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
            $salesTableHead.='<th>Efectivo C$</th>';
            $salesTableHead.='<th>Efectivo US$</th>';
            $salesTableHead.='<th>Crédito</th>';
            $salesTableHead.='<th>T BAC</th>';
            $salesTableHead.='<th>T BANPRO</th>';
          }
        $salesTableHead.='</tr>';
      $salesTableHead.='</thead>';
      
      $excelSalesTableHead='';
      $excelSalesTableHead.='<thead>';
        $excelSalesTableHead.='<tr>';
          $excelSalesTableHead.='<th>Fecha Informe</th>';
          foreach ($fuels as $fuelId=>$fuelName){
            $excelSalesTableHead.='<th>'.$fuelName.'</th>';
          }
          //foreach ($fuels as $fuelId=>$fuelName){
          //  $excelSalesTableHead.='<th>C$ '.$fuelName.'</th>';
          //}
          $excelSalesTableHead.='<th>Galones Total</th>';
          $excelSalesTableHead.='<th>C$</th>';
          $excelSalesTableHead.='<th>C$ Total</th>';
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
            $excelSalesTableHead.='<th>C$</th>';
            $excelSalesTableHead.='<th>Efectivo C$</th>';
            $excelSalesTableHead.='<th>US$</th>';
            $excelSalesTableHead.='<th>Efectivo US$</th>';
            $excelSalesTableHead.='<th>C$</th>';
            $excelSalesTableHead.='<th>Crédito</th>';
            $excelSalesTableHead.='<th>C$</th>';
            $excelSalesTableHead.='<th>T BAC</th>';
            $excelSalesTableHead.='<th>C$</th>';
            $excelSalesTableHead.='<th>T BANPRO</th>';
          }  
        $excelSalesTableHead.='</tr>';
        
      $excelSalesTableHead.='</thead>';
      
      $totalQuantities=[
        0=>0
      ];
      foreach ($fuels as $fuelId=>$fuelName){
        $totalQuantities[$fuelId]=0;
      } 
      $totalPrices=[
        0=>0
      ];
      foreach ($fuels as $fuelId=>$fuelName){
        $totalPrices[$fuelId]=0;
      }  
      $totalFuelCount=0;
      $totalOtherCount=0;
      
      if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
        $totalCashCs=0;
        $totalCashUsd=0;
        $totalCredit=0;
        $totalCardBac=0;
        $totalCardBanpro=0;
      }
      
      $saleRows="";
      $excelSaleRows="";
      $daysCounted=0;
      //pr($salesArray['2019-10-01']);
      //foreach ($salesArray as $saleDate=>$fuelInfo){
      foreach ($enumData['sales'] as $saleDate=>$fuelInfo){
        $saleDateTime=new DateTime($saleDate);
        //pr($fuelInfo);
        foreach ($fuelInfo['Fuel'] as $fuelId=>$fuelData){
          //pr($fuelData);
          $totalQuantities[$fuelId]+=$fuelData['quantity_gallons'];
          $totalPrices[$fuelId]+=$fuelData['price'];
        }
        $rowTotalCs=0;
        if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
          if (array_key_exists('Cash',$fuelInfo)){
            $totalCashCs+=$fuelInfo['Cash']['total_cash_cs'];
            $totalCashUsd+=$fuelInfo['Cash']['total_cash_usd'];
            $totalCredit+=$fuelInfo['Credit']['total_credit'];
            $totalCardBac+=$fuelInfo['Card']['total_bac'];
            $totalCardBanpro+=$fuelInfo['Card']['total_banpro'];
            
            $rowTotalCs+=$fuelInfo['Cash']['total_cash_cs'];
            $rowTotalCs+=$fuelInfo['Cash']['total_cash_usd']*$fuelInfo['exchange_rate_usd'];
            $rowTotalCs+=$fuelInfo['Credit']['total_credit'];
            $rowTotalCs+=$fuelInfo['Card']['total_bac'];
            $rowTotalCs+=$fuelInfo['Card']['total_banpro'];
          }
        }
        if ($fuelInfo['Fuel'][0]['price']>0){
          $daysCounted++;
        }
        
        $saleRows.='<tr'.($classifiers['type'] == 'All' && $fuelInfo['Fuel'][0]['price']-$rowTotalCs>100?' class="warning"':'').'>';
          $saleRows.="<td>".$this->Html->Link($saleDateTime->format('d-m-Y'),['controller'=>'stockMovements','action'=>'informeDiario',$saleDate])."</td>";
          foreach ($fuelInfo['Fuel'] as $fuelId=>$fuelData){
            
            if ($fuelId>0){
              $saleRows.='<td class="centered number"><span class="amountcenter">'.$fuelData['quantity_gallons'].'</span></td>';  
            }
          } 
          //foreach ($fuelInfo['Fuel'] as $fuelId=>$fuelData){
          //  if ($fuelId>0){
          //    $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$fuelData['price'].'</span></td>';  
          //  }
          //}           
          $saleRows.='<td class="centered number"><span class="amountcenter">'.$fuelInfo['Fuel'][0]['quantity_gallons'].'</span></td>';  
          $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$fuelInfo['Fuel'][0]['price'].'</span></td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
            $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amount center">'.(array_key_exists('Cash',$fuelInfo)?$this->Html->link(number_format($fuelInfo['Cash']['total_cash_cs'],2,".",","),['controller'=>'paymentReceipts','action'=>'registrarRecibos',$saleDate,PAYMENT_MODE_CASH,CURRENCY_CS]):"-").'</span></td>'; 
            $saleRows.='<td class="centered USDcurrency"><span class="currency">US$ </span><span class="amount center">'.(array_key_exists('Cash',$fuelInfo)?$this->Html->link(number_format($fuelInfo['Cash']['total_cash_usd'],2,".",","),['controller'=>'paymentReceipts','action'=>'registrarRecibos',$saleDate,PAYMENT_MODE_CASH,CURRENCY_USD]):"-").'</span></td>'; 
            $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amount center">'.(array_key_exists('Credit',$fuelInfo)?$this->Html->link(number_format($fuelInfo['Credit']['total_credit'],2,".",","),['controller'=>'paymentReceipts','action'=>'registrarRecibos',$saleDate,PAYMENT_MODE_CREDIT]):"-").'</span></td>'; 
            $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amount center">'.(array_key_exists('Card',$fuelInfo)?$this->Html->link(number_format($fuelInfo['Card']['total_bac'],2,".",","),['controller'=>'paymentReceipts','action'=>'registrarRecibos',$saleDate,PAYMENT_MODE_CARD_BAC]):"-").'</span></td>'; 
            $saleRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amount center">'.(array_key_exists('Card',$fuelInfo)?$this->Html->link(number_format($fuelInfo['Card']['total_banpro'],2,".",","),['controller'=>'paymentReceipts','action'=>'registrarRecibos',$saleDate,PAYMENT_MODE_CARD_BANPRO]):"-").'</span></td>'; 
          }
        $saleRows.="</tr>";
        
        $excelSaleRows.="<tr>";
          $excelSaleRows.="<td>".$saleDateTime->format('d-m-Y')."</td>";
          foreach ($fuelInfo['Fuel'] as $fuelId=>$fuelData){
            if ($fuelId>0){
              $excelSaleRows.='<td class="centered number"><span class="amountcenter">'.$fuelData['quantity_gallons'].'</span></td>';  
            }
          } 
          $excelSaleRows.='<td class="centered number"><span class="amountcenter">'.$fuelInfo['Fuel'][0]['quantity_gallons'].'</span></td>';  
          $excelSaleRows.='<td class="centered">C$</td>'; 
          $excelSaleRows.='<td class="centered">'.$fuelInfo['Fuel'][0]['price'].'</td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
          
            $excelSaleRows.='<td class="centered">C$</td>'; 
            $excelSaleRows.='<td class="centered">'.(array_key_exists('Cash',$fuelInfo)?round($fuelInfo['Cash']['total_cash_cs'],2):0).'</td>'; 
            $excelSaleRows.='<td class="centered">US$</td>'; 
            $excelSaleRows.='<td class="centered">'.(array_key_exists('Cash',$fuelInfo)?round($fuelInfo['Cash']['total_cash_usd'],2):0).'</td>'; 
            $excelSaleRows.='<td class="centered">C$</td>'; 
            $excelSaleRows.='<td class="centered">'.(array_key_exists('Credit',$fuelInfo)?round($fuelInfo['Credit']['total_credit'],2):0).'</td>'; 
            $excelSaleRows.='<td class="centered">C$</td>'; 
            $excelSaleRows.='<td class="centered">'.(array_key_exists('Card',$fuelInfo)?round($fuelInfo['Card']['total_bac'],2):0).'</td>'; 
            $excelSaleRows.='<td class="centered">C$</td>'; 
            $excelSaleRows.='<td class="centered">'.(array_key_exists('Card',$fuelInfo)?round($fuelInfo['Card']['total_banpro'],2):0).'</td>'; 
          }
        $excelSaleRows.="</tr>";
      } 
      $totalRow="";
      $totalRow.="<tr class='totalrow'>";
        $totalRow.="<td>Total</td>";
        foreach ($fuels as $fuelId=>$fuelName){
          $totalRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[$fuelId].'</span></td>';
        }
        //foreach ($fuels as $fuelId=>$fuelName){
        // $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalPrices[$fuelId].'</span></td>';
        //}
        $totalRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[0].'</span></td>';  
        $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalPrices[0].'</span></td>'; 
        if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
        
          $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalCashCs.'</span></td>'; 
          $totalRow.='<td class="centered USDcurrency"><span class="currency">US$ </span><span class="amountcenter">'.$totalCashUsd.'</span></td>'; 
          $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalCredit.'</span></td>'; 
          $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalCardBac.'</span></td>'; 
          $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.$totalCardBanpro.'</span></td>'; 
        }
      $totalRow.="</tr>";
      
      $extraRows='';
      if ($daysCounted>0){
        $averageRow='';
        $averageRow.='<tr>';
          $averageRow.="<td>Promedio</td>";
          foreach ($fuels as $fuelId=>$fuelName){
            $averageRow.='<td class="centered number"><span class="amountcenter">'.number_format($totalQuantities[$fuelId]/$daysCounted,2,".",",").'</span></td>';
          }
          $averageRow.='<td class="centered number"><span class="amountcenter">'.number_format($totalQuantities[0]/$daysCounted,2,".",",").'</span></td>';  
          $averageRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($totalPrices[0]/$daysCounted,2,".",",").'</span></td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
          
            $averageRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($totalCashCs/$daysCounted,2,".",",").'</span></td>'; 
            $averageRow.='<td class="centered USDcurrency"><span class="currency">US$ </span><span class="amountcenter">'.number_format($totalCashUsd/$daysCounted,2,".",",").'</span></td>'; 
            $averageRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($totalCredit/$daysCounted,2,".",",").'</span></td>'; 
            $averageRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($totalCardBac/$daysCounted,2,".",",").'</span></td>'; 
            $averageRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($totalCardBanpro/$daysCounted,2,".",",").'</span></td>'; 
          }
          $averageRow.='</tr>';
        $extraRows.=$averageRow;
        $daysInMonthInfo='';
        if (empty($daysInMonth)){
         $daysInMonth=count($enumData['sales']);
         $daysInMonthInfo='<p class="info">Como el período abarca varios meses, la proyección se hizo en base a la cantidad de días en la selección de período, no en el total de días en el mes</p>';
        }
        //echo "daysInMonth is ".$daysInMonth."<br/>";
        $projectionRow='';
        $projectionRow.='<tr>';
          $projectionRow.="<td>Proyección cierre</td>";
          foreach ($fuels as $fuelId=>$fuelName){
            $projectionRow.='<td class="centered number"><span class="amountcenter">'.number_format($daysInMonth*$totalQuantities[$fuelId]/$daysCounted,2,".",",").'</span></td>';
          }
          $projectionRow.='<td class="centered number"><span class="amountcenter">'.number_format($daysInMonth*$totalQuantities[0]/$daysCounted,2,".",",").'</span></td>';  
          $projectionRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalPrices[0]/$daysCounted,2,".",",").'</span></td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
          
            $projectionRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalCashCs/$daysCounted,2,".",",").'</span></td>'; 
            $projectionRow.='<td class="centered USDcurrency"><span class="currency">US$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalCashUsd/$daysCounted,2,".",",").'</span></td>'; 
            $projectionRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalCredit/$daysCounted,2,".",",").'</span></td>'; 
            $projectionRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalCardBac/$daysCounted,2,".",",").'</span></td>'; 
            $projectionRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountcenter">'.number_format($daysInMonth*$totalCardBanpro/$daysCounted,2,".",",").'</span></td>'; 
          }
        $projectionRow.='</tr>';
        $extraRows.=$projectionRow;
        
      }
      
      $excelTotalRow="";
      $excelTotalRow.="<tr class='totalrow'>";
        $excelTotalRow.="<td>Total</td>";
        foreach ($fuels as $fuelId=>$fuelName){
          $excelTotalRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[$fuelId].'</span></td>';
        }
        $excelTotalRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[0].'</span></td>';  
        $excelTotalRow.='<td class="centered CScurrency">C$</td>'; 
        $excelTotalRow.='<td class="centered">'.$totalPrices[0].'</td>'; 
        if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
        
          $excelTotalRow.='<td class="centered">C$</td>'; 
          $excelTotalRow.='<td class="centered">'.$totalCashCs.'</td>'; 
          $excelTotalRow.='<td class="centered">US$</td>'; 
          $excelTotalRow.='<td class="centered">'.$totalCashUsd.'</td>'; 
          $excelTotalRow.='<td class="centered">C$</td>'; 
          $excelTotalRow.='<td class="centered">'.$totalCredit.'</td>'; 
          $excelTotalRow.='<td class="centered">C$</td>'; 
          $excelTotalRow.='<td class="centered">'.$totalCardBac.'</td>'; 
          $excelTotalRow.='<td class="centered">C$</td>'; 
          $excelTotalRow.='<td class="centered">'.$totalCardBanpro.'</td>'; 
        }
      $excelTotalRow.="</tr>";
      
      $excelExtraRows='';
      if ($daysCounted>0){
        $excelAverageRow="";
        $excelAverageRow.="<tr>";
          $excelAverageRow.="<td>Promedio</td>";
          foreach ($fuels as $fuelId=>$fuelName){
            $excelAverageRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[$fuelId]/$daysCounted.'</span></td>';
          }
          $excelAverageRow.='<td class="centered number"><span class="amountcenter">'.$totalQuantities[0]/$daysCounted.'</span></td>';  
          $excelAverageRow.='<td class="centered CScurrency">C$</td>'; 
          $excelAverageRow.='<td class="centered">'.$totalPrices[0]/$daysCounted.'</td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
            $excelAverageRow.='<td class="centered">C$</td>'; 
            $excelAverageRow.='<td class="centered">'.$totalCashCs/$daysCounted.'</td>'; 
            $excelAverageRow.='<td class="centered">US$</td>'; 
            $excelAverageRow.='<td class="centered">'.$totalCashUsd/$daysCounted.'</td>'; 
            $excelAverageRow.='<td class="centered">C$</td>'; 
            $excelAverageRow.='<td class="centered">'.$totalCredit/$daysCounted.'</td>'; 
            $excelAverageRow.='<td class="centered">C$</td>'; 
            $excelAverageRow.='<td class="centered">'.$totalCardBac/$daysCounted.'</td>'; 
            $excelAverageRow.='<td class="centered">C$</td>'; 
            $excelAverageRow.='<td class="centered">'.$totalCardBanpro/$daysCounted.'</td>'; 
          }
        $excelAverageRow.="</tr>";
        $excelExtraRows.=$excelAverageRow;
        
        $excelProjectionRow="";
        $excelProjectionRow.="<tr>";
          $excelProjectionRow.="<td>Proyección cierre</td>";
          foreach ($fuels as $fuelId=>$fuelName){
            $excelProjectionRow.='<td class="centered number"><span class="amountcenter">'.$daysInMonth*$totalQuantities[$fuelId]/$daysCounted.'</span></td>';
          }
          $excelProjectionRow.='<td class="centered number"><span class="amountcenter">'.$daysInMonth*$totalQuantities[0]/$daysCounted.'</span></td>';  
          $excelProjectionRow.='<td class="centered CScurrency">C$</td>'; 
          $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalPrices[0]/$daysCounted.'</td>'; 
          if ($classifiers['type'] == 'All' || $classifiers['type'] == 'Operator'){
            $excelProjectionRow.='<td class="centered">C$</td>'; 
            $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalCashCs/$daysCounted.'</td>'; 
            $excelProjectionRow.='<td class="centered">US$</td>'; 
            $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalCashUsd/$daysCounted.'</td>'; 
            $excelProjectionRow.='<td class="centered">C$</td>'; 
            $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalCredit/$daysCounted.'</td>'; 
            $excelProjectionRow.='<td class="centered">C$</td>'; 
            $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalCardBac/$daysCounted.'</td>'; 
            $excelProjectionRow.='<td class="centered">C$</td>'; 
            $excelProjectionRow.='<td class="centered">'.$daysInMonth*$totalCardBanpro/$daysCounted.'</td>'; 
          }
        $excelProjectionRow.="</tr>";
        $excelExtraRows.=$excelProjectionRow;
      } 
      $salesTableBody="<tbody>".$totalRow.$saleRows.$totalRow.$extraRows."</tbody>";
      $excelSalesTableBody="<tbody>".$excelTotalRow.$excelSaleRows.$excelTotalRow.$excelExtraRows."</tbody>";
       
      $tableId="ventas_".$enumData['title_name'];
      $salesTable='<table id="'.$tableId.'" cellpadding="0" cellspacing="0">'.$salesTableHead.$salesTableBody.'</table>';  
      if ($classifiers['type'] == 'All'){
        echo '<p>Filas con una diferencia de mas de C$ 100 entre el monto según galones y el monto según recibos <span class="warning">salen en rojo</span></p>';
      }
      else {
        echo '<h2>'.__($classifiers['type']).' '.$enumData['title_name'].'</h2>';
      }
      
      echo $salesTable;
      echo $daysInMonthInfo;
      
      $excelSalesTable='<table id="'.$tableId.'" cellpadding="0" cellspacing="0">'.$excelSalesTableHead.$excelSalesTableBody.'</table>';  
      $excelOutput.=$excelSalesTable;
    }
    $_SESSION['reporteVentas'] = $excelOutput;
  }
?>	
</div>