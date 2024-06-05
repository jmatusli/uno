<div class="stockItems view report">
<?php 
	echo "<h2>".__('Reporte Detalle de Movimiento de Preforma')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	$producttable="";
	$producttableheader="";
	$producttablebody="";
	
	$totalentrada=0;
	$totalsalida=0;
	$totalsaldo=0;
	$totalproducts=array();
	
	//pr($productData);
	if ($productData['ProductType']['product_category_id']==CATEGORY_RAW){
		
		$producttableheader.="<thead>";
		$producttableheader.="<tr>";
		$producttableheader.="<th>".__('Date')."</th>";
		$producttableheader.="<th>".__('Invoice Code')."</th>";
		$producttableheader.="<th>".__('Provider')."</th>";
		$producttableheader.="<th class='centered'>".__('Number of Boxes')."</th>";
		$producttableheader.="<th class='centered'>".__('Units')."</th>";
		$producttableheader.="<th class='centered'>".__('Exit to Production')."</th>";
		$producttableheader.="<th class='centered'>".__('Saldo')."</th>";
		$producttableheader.="<th class='centered'>".__('Production Run Code')."</th>";
		$producttableheader.="<th class='separator'></th>";
		
		// generate an array that determines whether the column will be shown or not based on initial and final inventory				
		$visiblearray=array();
		for ($i=0;$i<3*count($allFinishedProducts);$i++){
			$visiblearray[$i]=0;
		}
		foreach ($thisProductOrders as $productOrder){
			foreach ($productOrder['StockMovement'] as $purchaseMovement){
				foreach ($purchaseMovement[0] as $productionMovementAndRun){
					if ($productionMovementAndRun['ProductionMovement']['product_quantity']>0){
						for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
							$visiblearray[$f]+=$productionMovementAndRun['ProductionRun'][0][$f];
						}
					}
				}
			}
		}
		for ($r=0;$r<count($finishedreclassified);$r++){
			$visiblearray[$r]+=$finishedreclassified[$r];
		}
		//pr($visiblearray);
		for ($i=0;$i<count($visiblearray);$i++){
			if ($i%3==2){
				if ($visiblearray[$i-2]>0 || $visiblearray[$i-1]>0 || $visiblearray[$i]>0){
					$visiblearray[$i-2]=false;
					$visiblearray[$i-1]=false;
					$visiblearray[$i]=false;
				}
				else {
					$visiblearray[$i-2]=true;
					$visiblearray[$i-1]=true;
					$visiblearray[$i]=true;
				}
			}
		}
		//pr($visiblearray);
		
		// then add the production outcomes with a foreach
		for ($i=0;$i<count($allFinishedProducts);$i++){
			$finishedProduct=$allFinishedProducts[$i];
			if (!$visiblearray[3*$i+2]){
				$producttableheader.="<th  class='centered' colspan='3'>".$this->Html->link($finishedProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $finishedProduct['Product']['id']))."</th>";
				
				//$producttableheader.="<th></th>";
				//$producttableheader.="<th></th>";
				//$producttableheader.="<th class='miniseparator'>&nbsp;</th>";
			}
		}
		$producttableheader.="</tr>";
		$producttableheader.="</thead>";
		
		$producttablebody.="<tbody>";
		$producttablebody.="<tr>";
		$producttablebody.="<td>Inventario Inicial</td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td class='centered number'>".$initialStock."</td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		
		$producttablebody.="<td class='separator'></td>";
		// then add the production outcomes with a foreach
		for ($i=0;$i<count($allFinishedProducts);$i++){
			$finishedProduct=$allFinishedProducts[$i];
			if (!$visiblearray[3*$i+2]){
				foreach ($allProductionResultCodes as $productionResultCode){
					$producttablebody.="<td class='centered'>".$productionResultCode['ProductionResultCode']['code']."</td>";
				}
			}
		}
		$producttablebody.="</tr>";
		
		
		$productionrunids=array();
		
		foreach ($thisProductOrders as $productOrder){
			foreach ($productOrder['StockMovement'] as $purchaseMovement){
				if ($productOrder['Order']['order_date']>=$startDate && $productOrder['Order']['order_date']<$endDatePlusOne){
					$totalentrada+=$purchaseMovement['product_quantity'];
					
					$totalsaldo+=$purchaseMovement['product_quantity']*$purchaseMovement['product_unit_price'];
					// get the purchase specific data
					$producttablebody.="<tr>";
					$orderdate=new DateTime($productOrder['Order']['order_date']);
					$producttablebody.="<td>".$orderdate->format('d-m-Y')."</td>";
					$producttablebody.="<td>".$productOrder['Order']['order_code']."</td>";
					$producttablebody.="<td>".$productOrder['ThirdParty']['company_name']."</td>";
					if ($purchaseMovement['Product']['packaging_unit']!=0){
						$numboxes=floor($purchaseMovement['product_quantity']/$purchaseMovement['Product']['packaging_unit']);
					}
					else {
						$numboxes="-";
					}
					$producttablebody.="<td class='centered'>".$numboxes."</td>";
					$producttablebody.="<td class='centered number'>".$purchaseMovement['product_quantity']."</td>";
					$producttablebody.="<td></td>";
					$producttablebody.="<td class='centered currency'><span>".$purchaseMovement['product_quantity']*$purchaseMovement['product_unit_price']."</span></td>";
					$producttablebody.="<td></td>";
					$producttablebody.="<td class='separator'>&nbsp;</td>";
					$producttablebody.="</tr>";
				}
				// get the consumption data
				
				foreach ($purchaseMovement[0] as $productionMovementAndRun){
					//pr ($productionMovementAndRun);
					if ($productionMovementAndRun['ProductionMovement']['product_quantity']>0 && $productionMovementAndRun['ProductionRun']['production_run_date']>=$startDate && $productionMovementAndRun['ProductionRun']['production_run_date']<$endDatePlusOne){
						$totalsalida+=$productionMovementAndRun['ProductionMovement']['product_quantity'];
						$producttablebody.="<tr>";
						
						$productionrundate=new DateTime($productionMovementAndRun['ProductionRun']['production_run_date']);
						$producttablebody.="<td>".$productionrundate->format('d-m-Y')."</td>";
						$producttablebody.="<td></td>";
						$producttablebody.="<td></td>";
						$producttablebody.="<td></td>";
						$producttablebody.="<td></td>";
						$producttablebody.="<td class='centered number'>".$productionMovementAndRun['ProductionMovement']['product_quantity']."</td>";
						$producttablebody.="<td class='centered currency'><span>".$purchaseMovement['product_unit_price']*$productionMovementAndRun['ProductionMovement']['product_quantity']."</span></td>";
						$totalsaldo-=$purchaseMovement['product_unit_price']*$productionMovementAndRun['ProductionMovement']['product_quantity'];
						$producttablebody.="<td>".$productionMovementAndRun['ProductionRun']['production_run_code']."</td>";
						$producttablebody.="<td class='separator'>&nbsp;</td>";
						
						// check if there is a production run that has been divided but for which the other stockitem is not shown
						$warningsign=false;
						$totalpartials=0;
						for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
							$totalpartials+=$productionMovementAndRun['ProductionRun'][0][$f];
						}
						if ($totalpartials>$productionMovementAndRun['ProductionMovement']['product_quantity']){
							$warningsign=true;
						}
						
						for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
							//if (($f/3==0) && $f>0){
								//$producttablebody.="<td class='miniseparator'>&nbsp;</td>";
							//}
							if (!$visiblearray[$f]){
								$producttablebody.="<td".($warningsign?" class='warning centered number'":" class='centered number'").">".$productionMovementAndRun['ProductionRun'][0][$f]."</td>";
							}
						}
						$alreadyregistered=false;
						for ($i=0;$i<count($productionrunids);$i++){
							if ($productionrunids[$i]==$productionMovementAndRun['ProductionRun']['id']){
								$alreadyregistered=true;
							}
						}
						if (!$alreadyregistered){
							foreach (array_keys($productionMovementAndRun['ProductionRun'][0] + $totalproducts) as $key) {
								$totalproducts[$key] = (isset($productionMovementAndRun['ProductionRun'][0][$key]) ? $productionMovementAndRun['ProductionRun'][0][$key] : 0) + (isset($totalproducts[$key]) ? $totalproducts[$key] : 0);
							}
						}
						$producttablebody.="</tr>";
						$productionrunids[]=$productionMovementAndRun['ProductionRun']['id'];
					}
				}
				
			}
		}
		$producttablebody.="<tr>";
		$producttablebody.="<td>Total Reclasificado</td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td class='centered number'>".$rawreclassified."</td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		$producttablebody.="<td></td>";
		
		$producttablebody.="<td class='separator'></td>";
		
		// then add the production outcomes with a foreach
		//for ($i=0;$i<count($allFinishedProducts);$i++){
		//	$finishedProduct=$allFinishedProducts[$i];
		//	if (!$visiblearray[3*$i+2]){
		//		foreach ($allProductionResultCodes as $productionResultCode){
		//			$producttablebody.="<td class='centered'>".$productionResultCode['ProductionResultCode']['code']."</td>";
		//		}
		//	}
		//}
		
		for ($r=0;$r<count($finishedreclassified);$r++){
			if (!$visiblearray[$r]){
				$producttablebody.="<td class='centered'>".$finishedreclassified[$r]."</td>";
			}
		}
		
		
		$producttablebody.="</tr>";
		$totalrow="<tr class='totalrow'>";
			$totalrow.="<td>Total</td>";
			$totalrow.="<td></td>";
			$totalrow.="<td></td>";
			$totalrow.="<td></td>";
			$totalrow.="<td class='centered number'>".($initialStock+$totalentrada+$rawreclassified)."</td>";
			$totalrow.="<td class='centered number'>".$totalsalida."</td>";
			$totalrow.="<td class='centered currency'><span>".$totalsaldo."</span></td>";
			$totalrow.="<td></td>";
			$totalrow.="<td class='separator'>&nbsp;</td>";

			for ($f=0;$f<sizeof($totalproducts);$f++){
				if (!$visiblearray[$f]){
					$totalrow.="<td class='centered number'>".($totalproducts[$f]+$finishedreclassified[$f])."</td>";
				}
			}
		$totalrow.="</tr>";
		
		$producttablebody="<tbody>".$totalrow.$producttablebody.$totalrow."</tbody>";
		
		$producttable="<table id='preformas_".$productData['Product']['name']."'>";
		$producttable.=$producttableheader;
		$producttable.=$producttablebody;
		$producttable.="</table>";
	}
	
	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteProductoMateriaPrima'), array( 'class' => 'btn btn-primary')); 

	echo "<h2>".__('Report')." para Producto ".$productData['Product']['name']."</h2>";
	echo $producttable; 
	
	$_SESSION['productReport'] = $producttable;
	
	
?>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
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
		formatCurrencies();
	});

</script>	