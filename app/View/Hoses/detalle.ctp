<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			/*$(this).parent().prepend("C$ ");*/
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		formatPercentages();
	});
</script>
<div class="hoses view">
<?php 
	echo "<h2>".__('Hose')." ".$hose['Hose']['name']."</h2>";
  echo "<div class='container-fluid'>";
    echo "<div class='rows'>";
      echo "<div class='col-sm-12'>";
        echo "<dl>";
          echo "<dt>".__('Description')."</dt>";
          if (!empty($hose['Hose']['description'])){
            echo "<dd>".h($hose['Hose']['description'])."</dd>";
          }
          else {
            echo "<dd>-</dd>";
          }
          echo "<dt>".__('Activo')."</dt>";
          echo "<dd>".($hose['Hose']['bool_active']?"Activo":"Deshabilitado")."</dd>";
          
          echo "<dt>".__('Enterprise')."</dt>";
          echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($hose['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'detalle',$hose['Enterprise']['id']]):$hose['Enterprise']['name'])."</dd>";
          echo "<dt>".__('Island')."</dt>";
          echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($hose['Island']['name'],['controller'=>'islands','action'=>'detalle',$hose['Island']['id']]):$hose['Island']['name'])."</dd>";
        echo "</dl>";
        echo "<dl>";
          echo "<dt>".__('Product')."</dt>";
          echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($hose['Product']['name'],['controller'=>'products','action'=>'view',$hose['Product']['id']]):$hose['Product']['name'])."</dd>";        
        echo "</dl>";
        echo $this->Form->create('Report'); 
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
        echo "</fieldset>";
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";
      echo "<div class='col-sm-6'>";
        
      echo "</div>";
    echo "</div>";
  echo "</div>";  
	
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Hose'), ['action' => 'editar', $hose['Hose']['id']])."</li>";
			echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			echo "<li>".$this->Form->postLink(__('Delete Hose'), ['action' => 'eliminar', $hose['Hose']['id']], [], __('Está seguro que quiere eliminar manguera %s?', $hose['Hose']['name']))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Hoses'), ['action' => 'resumen'])."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Hose'), ['action' => 'crear'])."</li>";
		}
    /*
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
    */
		foreach ($otherHoses as $otherHose){
			echo "<li>".$this->Html->link($otherHose['Hose']['name'], ['controller' => 'hoses', 'action' => 'detalle',$otherHose['Hose']['id']])."</li>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php
  /*
	if (!empty($soldProductsPerOperator)){
		echo "<h3>Productos fabricados en la máquina por cada operador en el período</h3>";
		echo "<table class='grid'>";
			echo "<thead>";
			// First the line with the raw material names
				echo "<tr>";
					echo "<th></th>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						//pr($rawMaterial);
						echo "<th  class='centered' colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
					}
				echo "</tr>";
			echo "</thead>";
					
			echo "<tbody>";
			// Then the line with the finished product names 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered' colspan='3'>".$product['finished_product_name']."</td>";					
							}
						}
					}
				echo "</tr>";

				// Then the line with the production result codes 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered'>A</td>";					
								echo "<td class='centered'>B</td>";
								echo "<td class='centered'>C</td>";
							}
						}
					}
				echo "</tr>";
			
				$totalsArray=array();
				//pr($producedProductsPerOperator);
				$firstrow=true;
				$operatorRows="";
				foreach ($producedProductsPerOperator as $operatorData){
					$operatorRow="";
					$productQuantityForRow=0;
					$operatorRow.="<tr>";
						$operatorRow.="<td>".$this->Html->link($operatorData['operator_name'], array('controller' => 'operators','action' => 'view',$operatorData['operator_id']))."</td>";
						$productCounter=0;
						foreach ($operatorData['rawmaterial'] as $rawMaterial){
							foreach ($rawMaterial['products'] as $product){
								if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
									foreach($product['product_quantity'] as $quantity){
										if ($quantity>0){
											$operatorRow.="<td class='centered bold number'>".$quantity."</td>";
										}
										else {
											$operatorRow.="<td class='centered'>-</td>";
										}
										if ($firstrow){
											$totalsArray[$productCounter]=$quantity;
										}
										else{
											$totalsArray[$productCounter]+=$quantity;
										}
										$productQuantityForRow+=$quantity;
										$productCounter++;
									}
								}
							}
						}
						//pr($totalsArray);
						$firstrow=false;
					$operatorRow.="</tr>";
					if ($productQuantityForRow){
						$operatorRows.=$operatorRow;
					}
				}
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						$totalRows.="<td class='centered number'>".$totalsArray[$i]."</td>";
					}
				$totalRows.="</tr>";
				
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						if ($i%3==0){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i]+$totalsArray[$i+1]+$totalsArray[$i+2]))."</span></td>";
						}
						elseif ($i%3==1){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-1]+$totalsArray[$i]+$totalsArray[$i+1]))."</span></td>";
						}
						elseif ($i%3==2){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-2]+$totalsArray[$i-1]+$totalsArray[$i]))."</span></td>";
						}
					}
				$totalRows.="</tr>";
				echo $totalRows.$operatorRows.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}
  */
?>
</div>