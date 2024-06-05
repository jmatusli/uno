<div class="tanks form">
<?php echo $this->Form->create('Tank'); ?>
	<fieldset>
		<legend><?php echo __('Add Tank'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('enterprise_id',['label'=>'Empresa','empty'=>['0' =>'Seleccione Empresa']]);
    echo $this->Form->input('product_id',['label'=>'Combustible','empty'=>['0' =>'Seleccione Combustible']]);
		echo $this->Form->input('bool_active',['default'=>'1']);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
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
