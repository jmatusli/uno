<div class="paymentReceipts view">
<?php 
	echo "<h2>".__('Payment Receipt')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Payment Date')."</dt>";
		echo "<dd>".h($paymentReceipt['PaymentReceipt']['payment_date'])."</dd>";
		echo "<dt>".__('Payment Amount')."</dt>";
		echo "<dd>".h($paymentReceipt['PaymentReceipt']['payment_amount'])."</dd>";
		echo "<dt>".__('Currency')."</dt>";
		echo "<dd>".$this->Html->link($paymentReceipt['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $paymentReceipt['Currency']['id']))."</dd>";
		echo "<dt>".__('Operator')."</dt>";
		echo "<dd>".$this->Html->link($paymentReceipt['Operator']['name'], array('controller' => 'operators', 'action' => 'view', $paymentReceipt['Operator']['id']))."</dd>";
		echo "<dt>".__('Shift')."</dt>";
		echo "<dd>".$this->Html->link($paymentReceipt['Shift']['name'], array('controller' => 'shifts', 'action' => 'view', $paymentReceipt['Shift']['id']))."</dd>";
		echo "<dt>".__('Payment Mode')."</dt>";
		echo "<dd>".$this->Html->link($paymentReceipt['PaymentMode']['name'], array('controller' => 'payment_modes', 'action' => 'view', $paymentReceipt['PaymentMode']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Payment Receipt'), array('action' => 'edit', $paymentReceipt['PaymentReceipt']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Payment Receipt'), array('action' => 'delete', $paymentReceipt['PaymentReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $paymentReceipt['PaymentReceipt']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Payment Receipts'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Receipt'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Payment Modes'), array('controller' => 'payment_modes', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Mode'), array('controller' => 'payment_modes', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
