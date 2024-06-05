<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,2);
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
    
    var chartPrices = document.getElementById('priceGraph').getContext('2d');
    var priceChart = new Chart(chartPrices, {
      type: 'bar',
      data: {
        labels: [<?php echo "'".implode("','",$priceData['labels'])."'"; ?>],
        datasets: [{
          label: 'Precios históricos',
          data: [<?php echo "'".implode("','",$priceData['values'])."'"; ?>],
          backgroundColor: [<?php echo "'".implode("','",$priceData['backgroundColors'])."'"; ?>],
          borderColor: [<?php echo "'".implode("','",$priceData['borderColors'])."'"; ?>],
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
	});
</script>
<div class="products view">
<?php 
	//pr($product);
	
  echo "<div class='row'>";
    echo "<div class='col-xs-8'>";
        echo "<h2>".__('Product')." ".$product['Product']['name']." (".($product['Product']['bool_active']?"Activo":"Desactivado").")</h2>";
      echo $this->Form->create('Report'); 
      echo "<fieldset>";
        echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
        echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
      echo "</fieldset>";
      echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
      echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
      echo $this->Form->end(__('Refresh')); 
      
      echo "<dl class='width100'>";
        echo "<dt>". __('Orden en la lista')."</dt>";
        echo "<dd>". h($product['Product']['product_order'])."</dd>";
        echo "<dt>". __('Name')."</dt>";
        echo "<dd>". h($product['Product']['name'])."</dd>";
        echo "<dt>". __('Abbreviation')."</dt>";
        echo "<dd>". h($product['Product']['abbreviation'])."</dd>";
        echo "<dt>". __('Nombre Viejo')."</dt>";
        echo "<dd>". h($product['Product']['old_name'])."</dd>";
        
        echo "<dt>". __('Description')."</dt>";
        if (!empty($product['Product']['description'])){	
          echo "<dd>". h($product['Product']['description'])."</dd>";
        }
        else {
          echo "<dd>-</dd>";
        }
        
        echo "<dt>". __('Product Type')."</dt>";
        echo "<dd>". $this->Html->link($product['ProductType']['name'], array('controller' => 'product_types', 'action' => 'view', $product['ProductType']['id']))."</dd>";
      
        //echo "<dt>".__('Accounting Code')."</dt>";
        //if (!empty($product['AccountingCode']['code'])){	
        //	echo "<dd>".$this->Html->Link($product['AccountingCode']['code']." ".$product['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$product['AccountingCode']['id']))."</dd>";
        //}
        //else {	
        //	echo "<dd>-</dd>";
        //}
        echo "<dt>". __('Packaging Unit')."</dt>";
        echo "<dd>". h($product['Product']['packaging_unit'])."</dd>";
      echo "</dl>";
      
      echo "<h3>Lote asociado</h3>";
      echo "<dl class='width100'>";
        echo "<dt>".__('Lote asociado con producto')."</dt>";
        echo "<dd>".$product['StockItem'][0]['name']."</dd>";
        echo "<dt>".__('Cantidad de producto actual')."</dt>";
        echo "<dd>".$product['StockItem'][0]['remaining_quantity']."</dd>";
        echo "<dt>".__('Valor (costo) promedio actual de producto')."</dt>";
        echo "<dd>C$ ".$product['StockItem'][0]['product_unit_cost']."</dd>";
      echo "</dl>";
      
      
      echo "<h3>Costos por ".(empty($product['DefaultCostUnit'])?"UNIDAD":$product['DefaultCostUnit']['name'])."</h3>";
      echo "<dl class='width100'>";
        echo "<dt>".__('Costo preestablecido')."</dt>";
        echo "<dd>".((!empty($product['Product']['default_cost']) && $product['Product']['default_cost']>0)?($product['Product']['default_cost']." ".$product['DefaultCostCurrency']['abbreviation']):"-")."</dd>";
        echo "<dt>".__('Costo mínimo')."</dt>";
        echo "<dd>".(!empty($product['Product']['min_cost'])?$product['Product']['min_cost']:"0")." ".$product['DefaultCostCurrency']['abbreviation']."</dd>";
        echo "<dt>".__('Costo máximo')."</dt>";
        echo "<dd>".(!empty($product['Product']['max_cost'])?$product['Product']['max_cost']:"0")." ".$product['DefaultCostCurrency']['abbreviation']."</dd>";
      echo "</dl>";
      echo "<h3>Precio por ".(empty($product['DefaultPriceUnit'])?"UNIDAD":$product['DefaultPriceUnit']['name'])."</h3>";
      echo "<dl class='width100'>";
        echo "<dt>".__('Precio preestablecido')."</dt>";
        echo "<dd>".((!empty($product['Product']['default_price']) && $product['Product']['default_price']>0)?($product['Product']['default_price']." ".$product['DefaultCostCurrency']['abbreviation']):"-")."</dd>";
      echo "</dl>";
    echo "</div>";
    echo "<div class='col-xs-4'>";
      if (!empty($product['ProductPriceLog']) && count($product['ProductPriceLog'])>1){
        echo "<canvas id='priceGraph'></canvas>";
        echo "<h3>Precios históricos</h3>";
        echo "<ul>";
          foreach ($product['ProductPriceLog'] as $productPriceLog){
            //pr($productPriceLog);
            $priceDateTime=new DateTime($productPriceLog['price_datetime']);
            echo "<li>".($priceDateTime->format('d/m/Y H:i:s')).":".$productPriceLog['Currency']['abbreviation']." ".$productPriceLog['price']."</li>";
          }
        echo "</ul>";
      } 
    echo "</div>";    
  echo "</div>";  
  
  
?>	
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Product'), array('action' => 'edit', $product['Product']['id']))."</li>";
		}
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $product['Product']['id']), array(), __('Are you sure you want to delete # %s?', $product['Product']['id']))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Products'), array('action' => 'index'))."</li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Product'), array('action' => 'add'))."</li>";
		}
		echo "<br/>";
		if ($bool_producttype_index_permission){
			echo "<li>".$this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index'))."</li>";
		}
		if ($bool_producttype_add_permission){
			echo "<li>".$this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
	if (!empty($product['ProductProduction'])){
		echo "<h3>".__('Valores de Producción Aceptable')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Application Date')."</th>";
					echo "<th class='centered'>".__('Cantidad Aceptable')."</th>";
				echo "</tr>";
			echo "</thead>";		
			echo "<tbody>";
			foreach ($product['ProductProduction'] as $productProduction){
				$applicationDateTime=new DateTime($productProduction['application_date']);
				echo "<tr>";
					echo "<td>".$applicationDateTime->format('d-m-Y')."</td>";
					echo "<td class='centered number'>".$productProduction['acceptable_production']."</td>";
				echo "</tr>";
			}
				
			echo "</tbody>";
		echo "</table>";
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($product['StockMovement'])){
    $totalQuantity=0;
		$table="";
		$table.="<table cellpadding = '0' cellspacing = '0'>";
			$table.="<thead>";
				$table.="<tr>";
					$table.="<th>".__('Entry Date')."</th>";
					$table.="<th>".__('Entry')."</th>";
					$table.="<th class='centered'>".__('Quantity')."</th>";
				$table.="</tr>";
			$table.="</thead>";
			$tableRows="";
      foreach ($ordersForProductInPeriod as $order){
        if ($order['Order']['stock_movement_type_id']==MOVEMENT_PURCHASE){
          $entryDateTime=new DateTime($order['Order']['order_date']);
          $tableRows.="<tr>";
            $tableRows.="<td>".$entryDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$this->Html->link($order['Order']['order_code'], array('controller' => 'orders', 'action' => 'verEntrada', $order['Order']['id']))."</td>";
            $quantityEntered=0;
            foreach ($order['StockMovement'] as $stockMovement){
              if ($stockMovement['product_quantity']>0 && $stockMovement['bool_input']){
                $totalQuantity+=$stockMovement['product_quantity'];
                $quantityEntered+=$stockMovement['product_quantity'];
              }
            }    
            $tableRows.="<td class='centered number'>".$quantityEntered."</td>";
          $tableRows.="</tr>";
        }
      }
				$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
					$totalRow.="<td></td>";
					$totalRow.="<td class='centered number'>".$totalQuantity."</td>";
				$totalRow.="</tr>";
			
			$table.="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
		$table.="</table>";
		if ($totalQuantity>0){
			echo "<h3>".__('Entradas')."</h3>";
			echo $table;
		}
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($product['StockMovement'])){
    $totalQuantity=0;
    $table="";
		$table.="<table cellpadding = '0' cellspacing = '0'>";
			$table.="<thead>";
				$table.="<tr>";
					$table.="<th>".__('Fecha Salida')."</th>";
					$table.="<th>".__('Salida')."</th>";
          $table.="<th class='centered'>".__('Cantidad Utilizado')."</th>";
				$table.="</tr>";
			$table.="</thead>";
			$tableRows="";
      foreach ($ordersForProductInPeriod as $order){
        if ($order['Order']['stock_movement_type_id']==MOVEMENT_SALE){
          $saleDateTime=new DateTime($order['Order']['order_date']);
          $tableRows.="<tr>";
            $tableRows.="<td>".$saleDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$this->Html->link($order['Order']['order_code'], array('controller' => 'orders', 'action' => 'verEntrada', $order['Order']['id']))."</td>";
            
            $quantityExited=0;
            foreach ($order['StockMovement'] as $stockMovement){
              if ($stockMovement['product_quantity']>0 && !$stockMovement['bool_reclassification']){
                $totalQuantity+=$stockMovement['product_quantity'];
                $quantityExited+=$stockMovement['product_quantity'];
              }
            }    
            $tableRows.="<td class='centered number'>".$quantityExited."</td>";
          $tableRows.="</tr>";
        }
      }
      	$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
					$totalRow.="<td></td>";
          $totalRow.="<td class='centered number'>".$totalQuantity."</td>";
				$totalRow.="</tr>";
			
			$table.="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
		$table.="</table>";
    if ($totalQuantity>0){  		
			echo "<h3>".__('Ventas')."</h3>";
			echo $table;
		}
	}
?>
</div>