<div class="accountingRegisterCashReceipts view">
<?php 
	echo "<h2>".__('Accounting Register Cash Receipt')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Accounting Register')."</dt>";
		echo "<dd>".$this->Html->link($accountingRegisterCashReceipt['AccountingRegister']['id'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegisterCashReceipt['AccountingRegister']['id']))."</dd>";
		echo "<dt>".__('Cash Receipt')."</dt>";
		echo "<dd>".$this->Html->link($accountingRegisterCashReceipt['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $accountingRegisterCashReceipt['CashReceipt']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Register Cash Receipt'), array('action' => 'edit', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Register Cash Receipt'), array('action' => 'delete', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Register Cash Receipts'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register Cash Receipt'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
