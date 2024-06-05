<div class="accountingMovements view">
<?php 
	echo "<h2>".__('Accounting Movement')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Accounting Register')."</dt>";
		echo "<dd>".$this->Html->link($accountingMovement['AccountingRegister']['id'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingMovement['AccountingRegister']['id']))."</dd>";
		echo "<dt>".__('Accounting Code')."</dt>";
		echo "<dd>".$this->Html->link($accountingMovement['AccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingMovement['AccountingCode']['id']))."</dd>";
		echo "<dt>".__('Concept')."</dt>";
		echo "<dd>".h($accountingMovement['AccountingMovement']['concept'])."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".h($accountingMovement['AccountingMovement']['amount'])."</dd>";
		echo "<dt>".__('Currency')."</dt>";
		echo "<dd>".$this->Html->link($accountingMovement['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $accountingMovement['Currency']['id']))."</dd>";
		echo "<dt>".__('Bool Debit')."</dt>";
		echo "<dd>".h($accountingMovement['AccountingMovement']['bool_debit'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Movement'), array('action' => 'edit', $accountingMovement['AccountingMovement']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Movement'), array('action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['AccountingMovement']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Movements'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Movement'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
