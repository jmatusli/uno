<div class="stockItems form">
<?php echo $this->Form->create('StockItem'); ?>
	<fieldset>
		<legend><?php echo __('Add Stock Item'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('stock_movement_id');
		echo $this->Form->input('product_type_id');
		echo $this->Form->input('unit_price');
		echo $this->Form->input('original_quantity');
		echo $this->Form->input('remaining_quantity');
		echo $this->Form->input('production_result_code_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Stock Items'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Result Codes'), array('controller' => 'production_result_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Result Code'), array('controller' => 'production_result_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Movements'), array('controller' => 'production_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Movement'), array('controller' => 'production_movements', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stock_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stock_movements', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>