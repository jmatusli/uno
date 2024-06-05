<div class="accountingRegisterTypes form">
<?php echo $this->Form->create('AccountingRegisterType'); ?>
	<fieldset>
		<legend><?php echo __('Add Accounting Register Type'); ?></legend>
	<?php
		echo $this->Form->input('abbreviation');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Accounting Register Types'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
	</ul>
</div>
