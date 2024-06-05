<div class="tanks form">
<?php echo $this->Form->create('Tank'); ?>
	<fieldset>
		<legend><?php echo __('Edit Tank'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('enterprise_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('bool_active');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Tank'), ['action' => 'eliminar', $this->Form->value('Tank.id')], [], __('Are you sure you want to delete %s?', $this->Form->value('Tank.id')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Tanks'), ['action' => 'resumen'])."</li>";
	echo "</ul>";
?>	
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
