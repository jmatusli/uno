<div class="units form">
<?php echo $this->Form->create('Unit'); ?>
	<fieldset>
		<legend><?php echo __('Edit Unit'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('target_unit_id');
		echo $this->Form->input('conversion_factor');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Unit'), ['action' => 'eliminar', $this->Form->value('Unit.id')], [], __('Está seguro que quiere eliminar unidad %s?', $this->Form->value('Unit.name')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Units'), ['action' => 'resumen'])."</li>";

	echo "</ul>";
?>	
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
