<script>
	function formatNumbers(){
		$("td.number span").each(function(){
      if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
		formatCurrencies();
	});
</script>
<div class="stockItems view report fullwidth">

<?php 
	echo "<h2>".__('Estado de Resultados')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')]);
    echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);

	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteEstado'), array( 'class' => 'btn btn-primary')); 
	echo "<br/>";
	echo "<br/>";

	// visualizar totales
  $total_quantity_all=0;
  $total_price_all=0;
  $total_cost_all=0;
  $total_gain_all=0;
	$fuelTable="<table id='tapones'>";
		$fuelTable.="<thead>";
			$fuelTable.="<tr>";
				$fuelTable.="<th class='hidden'>Product Id</th>";
				$fuelTable.="<th>".__('Product')."</th>";
				$fuelTable.="<th class='centered'>".__('Cantidad')."</th>";
				$fuelTable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$fuelTable.="<th class='centered'>".__('Costo de Compra')."</th>";
				$fuelTable.="<th class='centered'>".__('Utilidad')."</th>";
				$fuelTable.="<th class='centered'>".__('Margen Utilidad')."</th>";
			$fuelTable.="</tr>";
		$fuelTable.="</thead>";
		$fuelTable.="<tbody>";

		foreach ($fuels as $fuel){
			$total_quantity_all+=$fuel['total_quantity'];
			$total_price_all+=$fuel['total_price'];
			$total_cost_all+=$fuel['total_cost']; 
			$total_gain_all+=$fuel['total_gain'];

			$fuelTable.="<tr>"; 			
				$fuelTable.="<td class='hidden'>".$fuel['id']."</td>";
				$fuelTable.="<td>".$this->Html->link($fuel['name'], array('controller' => 'products', 'action' => 'view', $fuel['id']))."</td>";
				$fuelTable.="<td class='centered number'><span>".$fuel['total_quantity']."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$fuel['total_price']."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$fuel['total_cost']."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$fuel['total_gain']."</span></td>";
				if (!empty($fuel['total_price'])){
					$fuelTable.="<td class='centered percentage'><span>".(100*$fuel['total_gain']/$fuel['total_price'])."</span></td>";
				}
				else {
					$fuelTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$fuelTable.="</tr>";
		}
			$fuelTable.="<tr class='totalrow'>";
				$fuelTable.="<td>Total</td>";
				$fuelTable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				$fuelTable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";			
				if (!empty($total_price_all)){
					$fuelTable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				}
				else {
					$fuelTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$fuelTable.="</tr>";
		$fuelTable.="</tbody>";
	$fuelTable.="</table>";	
	
	$total_quantity_fuels=$total_quantity_all;
	$total_price_fuels=$total_price_all;
	$total_cost_fuels=$total_cost_all;
	$total_gain_fuels=$total_gain_all;

	$overviewTable="<table id='overview'>";
		$overviewTable.="<thead>";
			$overviewTable.="<tr>";
				$overviewTable.="<th></th>";
				$overviewTable.="<th class='centered'>Total Combustibles</th>";
				//$overviewTable.="<th class='centered'>Total Lubricantes</th>";
			$overviewTable.="</tr>";
		$overviewTable.="</thead>";
		
		$overviewTable.="<tbody>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Cantidad</td>";
				$overviewTable.="<td class='centered number'><span>".$total_quantity_fuels."</span></td>";
				//$overviewTable.="<td class='centered number'><span>".$total_quantity_caps."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Venta</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_price_fuels."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_price_caps."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Costo</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_cost_fuels."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_cost_caps."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Utilidad</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_gain_fuels."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_gain_caps."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Margen Utilidad</td>";
				if (!empty($total_price_fuels)){
					$overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_fuels/$total_price_fuels)."</span></td>";
				}
				else {
					$overviewTable.="<td class='centered percentage'><span>0</span></td>";
				}
				//if (!empty($total_price_caps)){
				//	$overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_caps/$total_price_caps)."</span></td>";
				//}
				//else {
				//	$overviewTable.="<td class='centered percentage'><span>0</span></td>";
				//}
			$overviewTable.="</tr>";
      /*
      $overviewTable.="<tr>";
				$overviewTable.="<td>Ajustes y Reclasificaciones</td>";
				$overviewTable.="<td class='centered number'><span>".($stockMovementsAdjustmentsInA[0]['StockMovement']['total_in_A']-$stockMovementsAdjustmentsOutA[0]['StockMovement']['total_out_A'])."</span></td>";
				$overviewTable.="<td class='centered number'><span>".($stockMovementsAdjustmentsInCaps[0]['StockMovement']['total_in_caps']-$stockMovementsAdjustmentsOutCaps[0]['StockMovement']['total_out_caps'])."</span></td>";
			$overviewTable.="</tr>";
      */
		$overviewTable.="</tbody>";
	$overviewTable.="</table>";
	
  $clientTable="<table id='clientes'>";
		$clientTable.="<thead>";
			$clientTable.="<tr>";
				$clientTable.="<th>".__('Client')."</th>";
        //$clientTable.="<th>Galones</th>";
				$clientTable.="<th class='centered'>".__('Venta Combustible')."</th>";
        //$clientTable.="<th>Costo</th>";
        //$clientTable.="<th>Utilidad</th>";
			$clientTable.="</tr>";
		$clientTable.="</thead>";
		$clientTable.="<tbody>";

		$totalSalesClients=0;
		
		foreach ($salesClientPeriod as $clientSale){
			$totalSalesClients+= $clientSale['PaymentReceipt']['total_client'];
				$clientTable.="<tr>"; 				
        $clientTable.="<td>".$this->Html->link($clients[$clientSale['PaymentReceipt']['client_id']], array('controller' => 'third_parties', 'action' => 'verCliente', $clientSale['PaymentReceipt']['client_id']))."</td>";
        //$clientTable.='<td class="centered number"><span>0</span></td>';
        $clientTable.='<td class="centered currency"><span class="CScurrency"></span><span class="amountright">'.$clientSale['PaymentReceipt']['total_client'].'</span></td>';
        //$clientTable.='<td class="centered currency"><span class="CScurrency"></span><span class="amountright">0</span></td>';
        //$clientTable.='<td class="centered number"><span>0</span></td>';
      $clientTable.="</tr>";
		}
			$clientTable.="<tr class='totalrow'>";
				$clientTable.='<td>Total</td>';
        //$clientTable.='<td class="centered number"></td>';
				$clientTable.='<td class="centered currency"><span>'.$totalSalesClients.'</span></td>';
        //$clientTable.='<td class="centered currency"><span>0</span></td>';
        //$clientTable.='<td class="centered percentage"><span>0</span></td>';
				
			$clientTable.="</tr>";
		$clientTable.="</tbody>";
	$clientTable.="</table>";
  
	echo $overviewTable;
	
  echo "<h2>Combustibles</h2>"; 
	echo $fuelTable; 
	
	echo "<h2>".__('Venta Por Cliente')."</h2>"; 
	echo $clientTable; 
	
	
	$_SESSION['statusReport'] = $overviewTable.$fuelTable.$clientTable;
  
  /*	
	$clientTable="<table id='clientes'>";
		$clientTable.="<thead>";
			$clientTable.="<tr>";
				$clientTable.="<th>".__('Client')."</th>";
				
				$clientTable.="<th class='centered'>".__('Cantidad Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Venta Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Costo Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Utilidad Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Margen Utilidad Botellas')."</th>";
				
				$clientTable.="<th class='centered'>".__('Cantidad Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Venta Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Costo Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Utilidad Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Margen Utilidad Tapones')."</th>";
			$clientTable.="</tr>";
		$clientTable.="</thead>";
		$clientTable.="<tbody>";

		$total_quantity_all_bottles=0;
		$total_price_all_bottles=0;
		$total_cost_all_bottles=0;
		$total_gain_all_bottles=0;
		
		$total_quantity_all_caps=0;
		$total_price_all_caps=0;
		$total_cost_all_caps=0;
		$total_gain_all_caps=0;
		
		foreach ($clientutility as $client){
			if (($client['bottle_total_price']+$client['cap_total_price'])>0){
				$total_quantity_all_bottles+=$client['bottle_total_quantity'];
				$total_price_all_bottles+=$client['bottle_total_price'];
				$total_cost_all_bottles+=$client['bottle_total_cost']; 
				$total_gain_all_bottles+=$client['bottle_total_gain'];

				$total_quantity_all_caps+=$client['cap_total_quantity'];
				$total_price_all_caps+=$client['cap_total_price'];
				$total_cost_all_caps+=$client['cap_total_cost']; 
				$total_gain_all_caps+=$client['cap_total_gain'];
				
				$clientTable.="<tr>"; 				
					$clientTable.="<td>".$this->Html->link($client['name'], array('controller' => 'third_parties', 'action' => 'verCliente', $client['id']))."</td>";
					
					$clientTable.="<td class='centered number'><span>".$client['bottle_total_quantity']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_price']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_cost']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_gain']."</span></td>";
					if (!empty($client['bottle_total_price'])){
						$clientTable.="<td class='centered percentage'><span>".(100*$client['bottle_total_gain']/$client['bottle_total_price'])."</span></td>";
					}
					else {
						$clientTable.="<td class='centered percentage'><span>0</span></td>";
					}
					
					$clientTable.="<td class='centered number'><span>".$client['cap_total_quantity']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_price']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_cost']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_gain']."</span></td>";
					if (!empty($client['cap_total_price'])){
						$clientTable.="<td class='centered percentage'><span>".(100*$client['cap_total_gain']/$client['cap_total_price'])."</span></td>";
					}
					else {
						$clientTable.="<td class='centered percentage'><span>0</span></td>";
					}	
				$clientTable.="</tr>";
			}
		}
			$clientTable.="<tr class='totalrow'>";
				$clientTable.="<td>Total</td>";
				
				$clientTable.="<td class='centered number'><span>".$total_quantity_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_price_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_cost_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_gain_all_bottles."</span></td>";
				if (!empty($total_price_all_bottles)){
					$clientTable.="<td class='centered percentage'><span>".(100*$total_gain_all_bottles/$total_price_all_bottles)."</span></td>";
				}
				else {
					$clientTable.="<td class='centered percentage'><span>0</span></td>";
				}
				
				$clientTable.="<td class='centered number'><span>".$total_quantity_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_price_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_cost_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_gain_all_caps."</span></td>";			
				if (!empty($total_price_all_caps)){
					$clientTable.="<td class='centered percentage'><span>".(100*$total_gain_all_caps/$total_price_all_caps)."</span></td>";			
				}
				else {
					$clientTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$clientTable.="</tr>";
		$clientTable.="</tbody>";
	$clientTable.="</table>";	
	*/
?>
</div>