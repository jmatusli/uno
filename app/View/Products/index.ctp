<div class="products index">
<?php
	echo "<h2>".__('Products')."</h2>";
  echo "<p class='comment'>Productos desactivados <span class='italic'>aparecen en cursivo</span></p>";
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Product'), ['action' => 'add'])."</li>";
			echo "<br/>";
		}
		if ($bool_producttype_index_permission){
			echo "<li>".$this->Html->link(__('List Product Types'), ['controller' => 'product_types', 'action' => 'index'])."</li>";
		}
		if ($bool_producttype_add_permission){
			echo "<li>".$this->Html->link(__('New Product Type'), ['controller' => 'product_types', 'action' => 'add'])."</li>";
		}
	echo "</ul>";
?>
</div>
<div class="products">
<?php
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('abbreviation')."</th>";
				//echo "<th style='width:10%;'>".$this->Paginator->sort('description')."</th>";
				//echo "<th>".$this->Paginator->sort('accounting_code_id')."</th>";
				echo "<th>".$this->Paginator->sort('product_type_id')."</th>";
        echo "<th>".$this->Paginator->sort('default_cost','Costo preestablecido')."</th>";
        echo "<th>".$this->Paginator->sort('default_price','Precio actual')."</th>";
				//echo "<th>".$this->Paginator->sort('packaging_unit')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($products as $product){
			echo "<tr".($product['Product']['bool_active']?"":" class='italic'").">";
				echo "<td>".$this->Html->link($product['Product']['name'], ['action' => 'view', $product['Product']['id']])."</td>";
				echo "<td>".h($product['Product']['abbreviation'])."&nbsp;</td>";
        //echo "<td>".h($product['Product']['description'])."&nbsp;</td>";
				//if (!empty($product['AccountingCode']['code'])){
				//	echo "<td>".$this->Html->link($product['AccountingCode']['code']." (".$product['AccountingCode']['description'].")",['controller'=>'accounting_codes','action'=>'view',$product['AccountingCode']['id']])."</td>";
				//}
				//else {
				//	echo "<td>-</td>";
				//}
				echo "<td>".$this->Html->link($product['ProductType']['name'], ['controller' => 'product_types', 'action' => 'view', $product['ProductType']['id']])."</td>";
        echo "<td>".$product['DefaultCostCurrency']['abbreviation']." ".h($product['Product']['default_cost'])."&nbsp;</td>";
				echo "<td>".$product['DefaultPriceCurrency']['abbreviation']." ".h($product['Product']['default_price'])."&nbsp;</td>";
        //echo "<td>".h($product['Product']['packaging_unit'])."&nbsp;</td>";
				echo "<td class='actions'>";
					if ($bool_edit_permission){ 
						echo $this->Html->link(__('Edit'), ['action' => 'edit', $product['Product']['id']]); 
					}
					if ($bool_delete_permission){ 					
						//echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $product['Product']['id']], [], __('Are you sure you want to delete # %s?', $product['Product']['id'])); 
					}
				echo "</td>";
			echo "</tr>";
		}	
		echo "</tbody>";
	echo "</table>";
?>
</div>