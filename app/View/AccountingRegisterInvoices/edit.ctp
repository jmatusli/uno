<div class="accountingRegisterInvoices form">
<?php echo $this->Form->create('AccountingRegisterInvoice'); ?>
	<fieldset>
		<legend><?php echo __('Edit Accounting Register Invoice'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('accounting_register_id');
		echo $this->Form->input('invoice_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingRegisterInvoice.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingRegisterInvoice.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Register Invoices'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
	</ul>
</div>
