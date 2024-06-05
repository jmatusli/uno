<div class="cashReceiptPaymentReceipts view">
<?php 
	echo "<h2>".__('Cash Receipt Payment Receipt')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Cash Receipt')."</dt>";
		echo "<dd>".$this->Html->link($cashReceiptPaymentReceipt['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $cashReceiptPaymentReceipt['CashReceipt']['id']))."</dd>";
		echo "<dt>".__('Payment Receipt')."</dt>";
		echo "<dd>".$this->Html->link($cashReceiptPaymentReceipt['PaymentReceipt']['id'], array('controller' => 'payment_receipts', 'action' => 'view', $cashReceiptPaymentReceipt['PaymentReceipt']['id']))."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['amount'])."</dd>";
		echo "<dt>".__('Increment')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['increment'])."</dd>";
		echo "<dt>".__('Discount')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['discount'])."</dd>";
		echo "<dt>".__('Erdiff')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['erdiff'])."</dd>";
		echo "<dt>".__('Payment')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment'])."</dd>";
		echo "<dt>".__('Payment Retention')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_retention'])."</dd>";
		echo "<dt>".__('Payment Credit CS')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_credit_CS'])."</dd>";
		echo "<dt>".__('Payment Increment CS')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_increment_CS'])."</dd>";
		echo "<dt>".__('Payment Discount CS')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_discount_CS'])."</dd>";
		echo "<dt>".__('Payment Erdiff CS')."</dt>";
		echo "<dd>".h($cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['payment_erdiff_CS'])."</dd>";
		echo "<dt>".__('Currency')."</dt>";
		echo "<dd>".$this->Html->link($cashReceiptPaymentReceipt['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $cashReceiptPaymentReceipt['Currency']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Cash Receipt Payment Receipt'), array('action' => 'edit', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Cash Receipt Payment Receipt'), array('action' => 'delete', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptPaymentReceipt['CashReceiptPaymentReceipt']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Cash Receipt Payment Receipts'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Cash Receipt Payment Receipt'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Payment Receipts'), array('controller' => 'payment_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Receipt'), array('controller' => 'payment_receipts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
