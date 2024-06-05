<div class="accountingRegisters form">
<?php echo $this->Form->create('AccountingRegister'); ?>
	<fieldset>
		<legend><?php echo __('Add Accounting Register'); ?></legend>
	<?php
		echo $this->Form->input('register_code');
		echo $this->Form->input('concept');
		echo $this->Form->input('register_date');
		echo $this->Form->input('amount');
		echo $this->Form->input('currency_id');
		echo $this->Form->input('accounting_register_type_id');
		echo $this->Form->input('observation');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Register Types'), array('controller' => 'accounting_register_types', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register Type'), array('controller' => 'accounting_register_types', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Movements'), array('controller' => 'accounting_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Movement'), array('controller' => 'accounting_movements', 'action' => 'add')); ?> </li>
	</ul>
</div>
