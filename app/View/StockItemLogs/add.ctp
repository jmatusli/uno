<div class="stockItemLogs form">
<?php echo $this->Form->create('StockItemLog'); ?>
	<fieldset>
		<legend><?php echo __('Add Stock Item Log'); ?></legend>
	<?php
		echo $this->Form->input('stock_item_id');
		echo $this->Form->input('stock_movement_id');
		echo $this->Form->input('production_movement_id');
		echo $this->Form->input('stockitem_date');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('product_unit_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Stock Item Logs'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stock_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stock_movements', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Movements'), array('controller' => 'production_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Movement'), array('controller' => 'production_movements', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
