<div class="accountingRegisterCashReceipts form">
<?php echo $this->Form->create('AccountingRegisterCashReceipt'); ?>
	<fieldset>
		<legend><?php echo __('Add Accounting Register Cash Receipt'); ?></legend>
	<?php
		echo $this->Form->input('accounting_register_id');
		echo $this->Form->input('cash_receipt_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Accounting Register Cash Receipts'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
	</ul>
</div>
