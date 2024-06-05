<div class="stockItems view report fullwidth">

<?php
	
	echo "<button id='onlyProblems' type='button'>".__('Only Show Problems')."</button>";
	echo "<h3>".$this->Html->link('Recreate All StockItemLogs',['action' => 'recreateAllStockItemLogs'])."</h3>";
  /*
	echo $this->Form->create('Report');
		echo "<fieldset>";
			//echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			//echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			
			//echo $this->Form->input('Report.product_category_id',['label'=>__('Categoría de Producto'),'default'=>$productCategoryId]);
		echo "</fieldset>";
		echo "<br/>";
	echo $this->Form->end(__('Refresh'));
	*/
	$productTable="";
	
	$productTable="<table id='productos'>";
	
		$productTable.="<thead>";
			$productTable.="<tr>";
				$productTable.="<th>Producto</th>";
				$productTable.="<th>Entradas</th>";
				$productTable.="<th>Salidas</th>";
				$productTable.="<th>Saldo</th>";
				$productTable.="<th>Saldo Lote</th>";
				$productTable.="<th>Saldo Log</th>";
				$productTable.="<th>Acciones</th>";
			$productTable.="</tr>";
		$productTable.="</thead>";
		
		$productTable.="<tbody>";
		
		$totalCategoriesInMovements=0;
		$totalCategoriesOutMovements=0;
		$totalCategoriesSaldoStockItem=0;
		$totalCategoriesSaldoStockItemLog=0;
		
		foreach ($productCategories as $productCategory){
      $categoryRows="";
    
      $totalTypesInMovements=0;
      $totalTypesOutMovements=0;
      $totalTypesSaldoStockItem=0;
      $totalTypesSaldoStockItemLog=0;
      
      foreach ($productCategory['ProductType'] as $productType){
        $typeRows="";
        
        foreach ($productType['Product'] as $product){
          $saldoMovement=$product['total_in_movements']-$product['total_out_movements'];
          
          $totalTypesInMovements+=$product['total_in_movements'];
					$totalTypesOutMovements+=$product['total_out_movements'];
					$totalTypesSaldoStockItem+=$product['total_saldo_stock_item'];
					$totalTypesSaldoStockItemLog+=$product['total_saldo_stock_item_log'];
          
          $typeRows.="<tr>";
            $typeRows.="<td>".$this->Html->link($product['name'],['action' => 'view', $product['StockItem'][0]['id']])."</td>";            
            $typeRows.="<td>".$product['total_in_movements']."</td>";
            $typeRows.="<td>".$product['total_out_movements']."</td>";
            $typeRows.="<td".(abs($product['total_saldo_stock_item']-$saldoMovement)>0.00001?" class='warning'":"").">".$saldoMovement."</td>";
            $typeRows.="<td".(abs($product['total_saldo_stock_item'] - $saldoMovement)>0.00001 || abs($product['total_saldo_stock_item'] - $product['total_saldo_stock_item_log'])>0.00001?" class='warning'":"").">".$product['total_saldo_stock_item']."</td>";
            $typeRows.="<td".(abs($product['total_saldo_stock_item'] - $product['total_saldo_stock_item_log'])>0.00001?" class='warning'":"").">".$product['total_saldo_stock_item_log']."</td>";
            $typeRows.="<td>".$this->Html->link('Recreate StockItemLogs',['action' => 'recreateStockItemLogsForSquaring', $product['StockItem'][0]['id']])."</td>";
          $typeRows.="</tr>";
        }
        
        $typeTotalRow="";
        $typeTotalRow.="<tr class='totalrow green'>";
          $typeTotalRow.="<td>Total ".$productType['name']."</td>";
          $typeTotalRow.="<td>".$totalTypesInMovements."</td>";
          $typeTotalRow.="<td>".$totalTypesOutMovements."</td>";
          $typeTotalRow.="<td>".($totalTypesInMovements-$totalTypesOutMovements)."</td>";
          $typeTotalRow.="<td>".$totalTypesSaldoStockItem."</td>";
          $typeTotalRow.="<td>".$totalTypesSaldoStockItemLog."</td>";
          $typeTotalRow.="<td></td>";
        $typeTotalRow.="</tr>";
        
        $categoryRows.=$typeTotalRow.$typeRows.$typeTotalRow;
        
        $totalCategoriesInMovements+=$totalTypesInMovements;
        $totalCategoriesOutMovements+=$totalTypesOutMovements;
        $totalCategoriesSaldoStockItem+=$totalTypesSaldoStockItem;
        $totalCategoriesSaldoStockItemLog+=$totalTypesSaldoStockItemLog;
      }
      
      $categoryTotalRow="";
      $categoryTotalRow.="<tr class='totalrow'>";
        $categoryTotalRow.="<td>Total ".$productCategory['ProductCategory']['name']."</td>";
        $categoryTotalRow.="<td>".$totalCategoriesInMovements."</td>";
        $categoryTotalRow.="<td>".$totalCategoriesOutMovements."</td>";
        $categoryTotalRow.="<td>".($totalCategoriesInMovements-$totalCategoriesOutMovements)."</td>";
        $categoryTotalRow.="<td>".$totalCategoriesSaldoStockItem."</td>";
        $categoryTotalRow.="<td>".$totalCategoriesSaldoStockItemLog."</td>";
        $categoryTotalRow.="<td></td>";
      $categoryTotalRow.="</tr>";
      
      $productTable.=$categoryTotalRow.$categoryRows.$categoryTotalRow;
      
    }
    
    $productTable.="</tbody>";
	$productTable.="</table>";
	
	//echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteProductos'], [ 'class' => 'btn btn-primary']); 

	
	//echo "<h2>".__('Products')."</h2>"; 
	echo $productTable; 
	
	$_SESSION['productsReport'] = $productTable;
?>

<script>
	$('#onlyProblems').click(function(){
		$("tbody tr:not(.totalrow)").each(function() {
			$(this).hide();
		});
		$("td.warning").each(function() {
			$(this).parent().show();
		});
	});
</script>