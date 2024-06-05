<div class="accountingRegisters view">
<?php 
	echo "<h2>".__('Accounting Register')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Register Code')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['register_code'])."</dd>";
		echo "<dt>".__('Concept')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['concept'])."</dd>";
		echo "<dt>".__('Register Date')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['register_date'])."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['amount'])."</dd>";
		echo "<dt>".__('Currency Id')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['currency_id'])."</dd>";
		echo "<dt>".__('Accounting Register Type')."</dt>";
		echo "<dd>".$this->Html->link($accountingRegister['AccountingRegisterType']['Name'], array('controller' => 'accounting_register_types', 'action' => 'view', $accountingRegister['AccountingRegisterType']['id']))."</dd>";
		echo "<dt>".__('Observation')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['observation'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Register'), array('action' => 'edit', $accountingRegister['AccountingRegister']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Register'), array('action' => 'delete', $accountingRegister['AccountingRegister']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegister['AccountingRegister']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Register Types'), array('controller' => 'accounting_register_types', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register Type'), array('controller' => 'accounting_register_types', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Movements'), array('controller' => 'accounting_movements', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Movement'), array('controller' => 'accounting_movements', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
<div class="related">
<?php 
	if (!empty($accountingRegister['AccountingMovement'])){
		echo "<h3>".__('Related Accounting Movements')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th>".__('Accounting Register Id')."</th>";
				echo "<th>".__('Accounting Code Id')."</th>";
				echo "<th>".__('Concept')."</th>";
				echo "<th>".__('Amount')."</th>";
				echo "<th>".__('Currency Id')."</th>";
				echo "<th>".__('Bool Debit')."</th>";
				echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){ 
			echo "<tr>";
				echo "<td>".$accountingMovement['accounting_register_id']."</td>";
				echo "<td>".$accountingMovement['accounting_code_id']."</td>";
				echo "<td>".$accountingMovement['concept']."</td>";
				echo "<td>".$accountingMovement['amount']."</td>";
				echo "<td>".$accountingMovement['currency_id']."</td>";
				echo "<td>".$accountingMovement['bool_debit']."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'accounting_movements', 'action' => 'view', $accountingMovement['id']));
					echo $this->Html->link(__('Edit'), array('controller' => 'accounting_movements', 'action' => 'edit', $accountingMovement['id']));
					echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_movements', 'action' => 'delete', $accountingMovement['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['id']));
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>
</div>
