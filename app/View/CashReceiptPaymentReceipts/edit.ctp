<div class="cashReceiptPaymentReceipts form">
<?php echo $this->Form->create('CashReceiptPaymentReceipt'); ?>
	<fieldset>
		<legend><?php echo __('Edit Cash Receipt Payment Receipt'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('cash_receipt_id');
		echo $this->Form->input('payment_receipt_id');
		echo $this->Form->input('amount');
		echo $this->Form->input('increment');
		echo $this->Form->input('discount');
		echo $this->Form->input('erdiff');
		echo $this->Form->input('payment');
		echo $this->Form->input('payment_retention');
		echo $this->Form->input('payment_credit_CS');
		echo $this->Form->input('payment_increment_CS');
		echo $this->Form->input('payment_discount_CS');
		echo $this->Form->input('payment_erdiff_CS');
		echo $this->Form->input('currency_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('CashReceiptPaymentReceipt.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('CashReceiptPaymentReceipt.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipt Payment Receipts'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Payment Receipts'), array('controller' => 'payment_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Payment Receipt'), array('controller' => 'payment_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
	</ul>
</div>
