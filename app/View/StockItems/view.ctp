<div class="stockItems view">
<h2><?php echo __('Stock Item'); ?></h2>
	<dl>
		<!--dt><?php echo __('Id'); ?></dt->
		<!--dd>
			<?php echo h($stockItem['StockItem']['id']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['name']); ?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Description'); ?></dt-->
		<!--dd>
			<?php echo h($stockItem['StockItem']['description']); ?>
			&nbsp;
		</dd-->
		<!--dt><?php echo __('Stock Movement Id'); ?></dt-->
		<!--dd>
			<?php echo h($stockItem['StockItem']['stock_movement_id']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Product'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItem['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockItem['Product']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Unit Price'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['product_unit_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Original Quantity'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['original_quantity']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('StockItem Remaining Quantity'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['remaining_quantity']); ?>
			&nbsp;
		</dd>
		<?php if ($stockItem['ProductionResultCode']['code']!=""):?>
		<dt><?php echo __('Production Result Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItem['ProductionResultCode']['code'], array('controller' => 'production_result_codes', 'action' => 'view', $stockItem['ProductionResultCode']['id'])); ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($stockItem['StockItem']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<?php /* ?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($userrole==ROLE_ADMIN) { ?>
		<li><?php echo $this->Html->link(__('Edit Stock Item'), array('action' => 'edit', $stockItem['StockItem']['id'])); ?> </li>
		<?php } ?>
		<!--li><?php // echo $this->Form->postLink(__('Delete Stock Item'), array('action' => 'delete', $stockItem['StockItem']['id']), array(), __('Are you sure you want to delete # %s?', $stockItem['StockItem']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Stock Items'), array('action' => 'index')); ?> </li>
		<!--li><?php // echo $this->Html->link(__('New Stock Item'), array('action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole==ROLE_ADMIN) { ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
		<!--li><?php // echo $this->Html->link(__('List Production Result Codes'), array('controller' => 'production_result_codes', 'action' => 'index')); ?> </li-->
		<!--li><?php // echo $this->Html->link(__('New Production Result Code'), array('controller' => 'production_result_codes', 'action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Production Movements'), array('controller' => 'production_movements', 'action' => 'index')); ?> </li>
		<!--li><?php // echo $this->Html->link(__('New Production Movement'), array('controller' => 'production_movements', 'action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stock_movements', 'action' => 'index')); ?> </li>
		<!--li><?php // echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stock_movements', 'action' => 'add')); ?> </li-->
	</ul>
</div>
<?php */ ?>
<?php /* ?>
<div class="related">
	<h3><?php echo __('Related Product'); ?></h3>
	<?php if (!empty($stockItem['Product'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Description'); ?></th>
		<th><?php echo __('Bool Raw'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Stock Item Id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($stockItem['Product'] as $product): ?>
		<tr>
			<td><?php echo $product['id']; ?></td>
			<td><?php echo $product['name']; ?></td>
			<td><?php echo $product['description']; ?></td>
			<td><?php echo $product['bool_raw']; ?></td>
			<td><?php echo $product['created']; ?></td>
			<td><?php echo $product['modified']; ?></td>
			<td><?php echo $product['stockitem_id']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'products', 'action' => 'view', $product['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'products', 'action' => 'edit', $product['id'])); ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'products', 'action' => 'delete', $product['id']), array(), __('Are you sure you want to delete # %s?', $product['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<?php */ ?>
<?php if (sizeof($stockItem['ProductionMovement'])>0) { ?>
<div class="related">
	<?php 
		echo $stockItem['Product']['ProductType']['product_category_id'];
		switch ($stockItem['Product']['ProductType']['product_category_id']){
		case CATEGORY_RAW:
			echo "<h3>".__('Used in Production Runs')."</h3>";
			break;
		case CATEGORY_PRODUCED:
			echo "<h3>".__('Produced in Production Run')."</h3>";
			break;
		default:
			echo "<h3>".__('Related Production Movements')."</h3>";
	}
	?>
	
	<?php if (!empty($stockItem['ProductionMovement'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<!--th><?php echo __('Id'); ?></th-->
		<th><?php echo __('Movement Date'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<!--th><?php echo __('Description'); ?></th-->
		<!--th><?php echo __('Stock Item Id'); ?></th-->
		<th><?php echo __('Production Run Code'); ?></th>
		<!--th><?php echo __('Product Id'); ?></th-->
		<th><?php echo __('Product Unit Price'); ?></th>
		<th>
		<?php 
			switch ($stockItem['Product']['ProductType']['product_category_id']){
				case CATEGORY_RAW:
					echo __('Product Quantity Used');
					break;
				case CATEGORY_PRODUCED:
					echo __('Product Quantity Produced');
					break;
				default:
					echo __('Product Quantity');
			}
		?>
		</th>
		<th><?php echo __('Total Price'); ?></th>
		<!--th><?php echo __('Created'); ?></th-->
		<!--th><?php echo __('Modified'); ?></th-->
		<!--th class="actions"><?php echo __('Actions'); ?></th-->
	</tr>
	<?php 
		$quantityproducts=0; 
		$valueproducts=0; 
	?>
	<?php foreach ($stockItem['ProductionMovement'] as $productionMovement): ?>
	<?php 
		if (!$productionMovement['bool_input']){
			$valueproducts+=$productionMovement['product_unit_price']*$productionMovement['product_quantity']; 
			$quantityproducts+=$productionMovement['product_quantity']; 
		}
	?>
		<tr>
			<!--td><?php echo $productionMovement['id']; ?></td-->
			<td><?php echo $productionMovement['movement_date']; ?></td>
			<td><?php echo $productionMovement['name']; ?></td>
			<!--td><?php echo $productionMovement['description']; ?></td-->
			<!--td><?php echo $productionMovement['stockitem_id']; ?></td-->
			<td><?php echo $this->Html->Link($productionMovement['ProductionRun']['production_run_code'],array('controller'=>'production_runs','action'=>'view',$productionMovement['ProductionRun']['id'])); ?></td>
			<!--td><?php echo $productionMovement['Product']['name']; ?></td-->
			<td><?php echo round($productionMovement['product_unit_price'],2); ?></td>
			<td><?php echo $productionMovement['product_quantity']; ?></td>
			<td><?php echo round($productionMovement['product_quantity']*$productionMovement['product_unit_price'],0); ?></td>
			<!--td><?php echo $productionMovement['created']; ?></td-->
			<!--td><?php echo $productionMovement['modified']; ?></td-->
			<!--td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'production_movements', 'action' => 'view', $productionMovement['id'])); ?>
				<?php // echo $this->Html->link(__('Edit'), array('controller' => 'production_movements', 'action' => 'edit', $productionMovement['id'])); ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'production_movements', 'action' => 'delete', $productionMovement['id']), array(), __('Are you sure you want to delete # %s?', $productionMovement['id'])); ?>
			</td-->
		</tr>
	<?php endforeach; ?>
	<tr class='totalrow'>
		<td>Total</td>
		<td></td>
		<td></td>
		<td></td>
		<td><?php echo round($quantityproducts,2); ?>&nbsp;</td>
		<td><?php echo round($valueproducts,0); ?>&nbsp;</td>
	</tr>
	</table>
<?php endif; ?>

	<!--div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Production Movement'), array('controller' => 'production_movements', 'action' => 'add')); ?> </li>
		</ul>
	</div-->
</div>
<?php } ?>
<?php if ($stockItem['StockMovement'][0]['id']!="") { 
	// echo "size stockmovement is ".sizeof($stockItem['StockMovement']);
?>
<div class="related">
	<?php 
		switch ($stockItem['Product']['ProductType']['product_category_id']){
			case CATEGORY_RAW:
				echo "<h3>".__('Acquired in Purchase')."</h3>";
				break;
			case CATEGORY_PRODUCED:
				echo "<h3>".__('Exited in Exit Order')."</h3>";
				break;
			default:
				echo "<h3>".__('Related Stock Movements')."</h3>";
		}
	?>
	<?php if (!empty($stockItem['StockMovement'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<!--th><?php echo __('Id'); ?></th-->
		<th>
		<?php
			switch ($stockItem['Product']['ProductType']['product_category_id']){
				case CATEGORY_RAW:
					echo __('Purchase Date');
					break;
				case CATEGORY_PRODUCED:
					echo __('Exit Date');
					break;
				default:
					echo __('Movement Date');
			}
		?>
		</th>
		<th><?php echo __('Name'); ?></th>
		<!--th><?php echo __('Description'); ?></th-->
		<!--th><?php echo __('Order Id'); ?></th-->
		<th><?php echo __('Order Code'); ?></th>
		<!--th><?php echo __('Product Id'); ?></th-->
		<th><?php echo __('Product Unit Price'); ?></th>
		<th>
		<?php 
			switch ($stockItem['Product']['ProductType']['product_category_id']){
				case CATEGORY_RAW:
					echo __('Product Quantity Bought');
					break;
				case CATEGORY_PRODUCED:
					echo __('Product Quantity Sold');
					break;
				default:
					echo __('Product Quantity');
			}
		?>
		</th>
		<th><?php echo __('Total Price'); ?></th>
		<!--th><?php echo __('Created'); ?></th-->
		<!--th><?php echo __('Modified'); ?></th-->
		<!--th class="actions"><?php echo __('Actions'); ?></th-->
	</tr>
	<?php 
		$valueproducts=0;
		$quantityproducts=0; 
	?>
	<?php foreach ($stockItem['StockMovement'] as $stockMovement): ?>
	<?php 
		if (!$stockMovement['bool_input']){
			$quantityproducts+=$stockMovement['product_quantity']; 
			$valueproducts+=$stockMovement['product_quantity']*$stockMovement['product_unit_price']; 
		}
	?>
		<tr>
			<!--td><?php echo $stockMovement['id']; ?></td-->
			<td><?php echo $stockMovement['movement_date']; ?></td>
			<td><?php echo $stockMovement['name']; ?></td>
			<!--td><?php echo $stockMovement['description']; ?></td-->
			<!--td><?php echo $stockMovement['order_id']; ?></td-->
			<td><?php echo $stockMovement['Order']['order_code']; ?></td>
			<!--td><?php echo $stockMovement['product_id']; ?></td-->
			<td><?php echo round($stockMovement['product_unit_price'],2); ?></td>
			<td><?php echo $stockMovement['product_quantity']; ?></td>
			<td><?php echo round($stockMovement['product_quantity']*$stockMovement['product_unit_price'],0); ?></td>
			<!--td><?php echo $stockMovement['created']; ?></td-->
			<!--td><?php echo $stockMovement['modified']; ?></td-->
			<!--td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'stock_movements', 'action' => 'view', $stockMovement['id'])); ?>
				<?php // echo $this->Html->link(__('Edit'), array('controller' => 'stock_movements', 'action' => 'edit', $stockMovement['id'])); ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'stock_movements', 'action' => 'delete', $stockMovement['id']), array(), __('Are you sure you want to delete # %s?', $stockMovement['id'])); ?>
			</td-->
		</tr>
	<?php endforeach; ?>
	<tr class='totalrow'>
		<td>Total</td>
		<td></td>
		<td></td>
		<td></td>
		<td><?php echo round($quantityproducts,2); ?>&nbsp;</td>
		<td><?php echo round($valueproducts,0); ?>&nbsp;</td>
		<td></td>
	</tr>

	</table>
<?php endif; ?>

	<!--div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stock_movements', 'action' => 'add')); ?> </li>
		</ul>
	</div-->
</div>
<?php } ?>