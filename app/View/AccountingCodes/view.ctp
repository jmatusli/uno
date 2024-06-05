<div class="accountingCodes view">
<?php 
	echo "<h2>".__('Accounting Code')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Code')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['code'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['description'])."</dd>";
		echo "<dt>".__('Lft')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['lft'])."</dd>";
		echo "<dt>".__('Rght')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['rght'])."</dd>";
		echo "<dt>".__('Parent Accounting Code')."</dt>";
		echo "<dd>".$this->Html->link($accountingCode['ParentAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingCode['ParentAccountingCode']['id']))."</dd>";
		echo "<dt>".__('Bool Main')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['bool_main'])."</dd>";
		echo "<dt>".__('Bool Creditor')."</dt>";
		echo "<dd>".h($accountingCode['AccountingCode']['bool_creditor'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Accounting Code'), array('action' => 'edit', $accountingCode['AccountingCode']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Accounting Code'), array('action' => 'delete', $accountingCode['AccountingCode']['id']), array(), __('Are you sure you want to delete # %s?', $accountingCode['AccountingCode']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Codes'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Code'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Parent Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Movements'), array('controller' => 'accounting_movements', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Accounting Movement'), array('controller' => 'accounting_movements', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
<div class="related">
<?php 
	if (!empty($accountingCode['ChildAccountingCode'])){
		echo "<h3>".__('Related Accounting Codes')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th>".__('Code')."</th>";
				echo "<th>".__('Description')."</th>";
				echo "<th>".__('Lft')."</th>";
				echo "<th>".__('Rght')."</th>";
				echo "<th>".__('Parent Id')."</th>";
				echo "<th>".__('Bool Main')."</th>";
				echo "<th>".__('Bool Creditor')."</th>";
				echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		foreach ($accountingCode['ChildAccountingCode'] as $childAccountingCode){ 
			echo "<tr>";
				echo "<td>".$childAccountingCode['code']."</td>";
				echo "<td>".$childAccountingCode['description']."</td>";
				echo "<td>".$childAccountingCode['lft']."</td>";
				echo "<td>".$childAccountingCode['rght']."</td>";
				echo "<td>".$childAccountingCode['parent_id']."</td>";
				echo "<td>".$childAccountingCode['bool_main']."</td>";
				echo "<td>".$childAccountingCode['bool_creditor']."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'accounting_codes', 'action' => 'view', $childAccountingCode['id']));
					echo $this->Html->link(__('Edit'), array('controller' => 'accounting_codes', 'action' => 'edit', $childAccountingCode['id']));
					echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_codes', 'action' => 'delete', $childAccountingCode['id']), array(), __('Are you sure you want to delete # %s?', $childAccountingCode['id']));
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($accountingCode['AccountingMovement'])){
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
		foreach ($accountingCode['AccountingMovement'] as $accountingMovement){ 
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
