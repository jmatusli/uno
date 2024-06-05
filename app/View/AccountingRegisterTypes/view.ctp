<div class="accountingRegisterTypes view">
<?php 
	echo "<h2>".__('Accounting Register Type')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Abbreviation')."</dt>";
		echo "<dd>".h($accountingRegisterType['AccountingRegisterType']['abbreviation'])."</dd>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($accountingRegisterType['AccountingRegisterType']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($accountingRegisterType['AccountingRegisterType']['description'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Register Type'), array('action' => 'edit', $accountingRegisterType['AccountingRegisterType']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Register Type'), array('action' => 'delete', $accountingRegisterType['AccountingRegisterType']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterType['AccountingRegisterType']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Register Types'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register Type'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
<div class="related">
<?php 
	if (!empty($accountingRegisterType['AccountingRegister'])){
		echo "<h3>".__('Related Accounting Registers')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th>".__('Register Code')."</th>";
				echo "<th>".__('Concept')."</th>";
				echo "<th>".__('Register Date')."</th>";
				echo "<th>".__('Amount')."</th>";
				echo "<th>".__('Currency Id')."</th>";
				echo "<th>".__('Accounting Register Type Id')."</th>";
				echo "<th>".__('Observation')."</th>";
				echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		foreach ($accountingRegisterType['AccountingRegister'] as $accountingRegister){ 
			echo "<tr>";
				echo "<td>".$accountingRegister['register_code']."</td>";
				echo "<td>".$accountingRegister['concept']."</td>";
				echo "<td>".$accountingRegister['register_date']."</td>";
				echo "<td>".$accountingRegister['amount']."</td>";
				echo "<td>".$accountingRegister['currency_id']."</td>";
				echo "<td>".$accountingRegister['accounting_register_type_id']."</td>";
				echo "<td>".$accountingRegister['observation']."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegister['id']));
					echo $this->Html->link(__('Edit'), array('controller' => 'accounting_registers', 'action' => 'edit', $accountingRegister['id']));
					echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_registers', 'action' => 'delete', $accountingRegister['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegister['id']));
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>
</div>
