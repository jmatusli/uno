<div class="paymentReceipts form">
<?php echo $this->Form->create('PaymentReceipt'); ?>
	<fieldset>
		<legend><?php echo __('Add Payment Receipt'); ?></legend>
	<?php
		echo $this->Form->input('payment_date');
		echo $this->Form->input('payment_amount');
		echo $this->Form->input('currency_id');
		echo $this->Form->input('operator_id');
		echo $this->Form->input('shift_id');
		echo $this->Form->input('payment_mode_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Payment Receipts'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Payment Modes'), array('controller' => 'payment_modes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Payment Mode'), array('controller' => 'payment_modes', 'action' => 'add')); ?> </li>
	</ul>
</div>
