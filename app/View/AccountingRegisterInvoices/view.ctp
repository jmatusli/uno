<div class="accountingRegisterInvoices view">
<?php 
	echo "<h2>".__('Accounting Register Invoice')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Accounting Register')."</dt>";
		echo "<dd>".$this->Html->link($accountingRegisterInvoice['AccountingRegister']['id'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegisterInvoice['AccountingRegister']['id']))."</dd>";
		echo "<dt>".__('Invoice')."</dt>";
		echo "<dd>".$this->Html->link($accountingRegisterInvoice['Invoice']['id'], array('controller' => 'invoices', 'action' => 'view', $accountingRegisterInvoice['Invoice']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Register Invoice'), array('action' => 'edit', $accountingRegisterInvoice['AccountingRegisterInvoice']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Register Invoice'), array('action' => 'delete', $accountingRegisterInvoice['AccountingRegisterInvoice']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterInvoice['AccountingRegisterInvoice']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Register Invoices'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register Invoice'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
