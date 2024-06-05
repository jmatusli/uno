<div class="accountingCodes form">
<?php echo $this->Form->create('AccountingCode'); ?>
	<fieldset>
		<legend><?php echo __('Edit Accounting Code'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('code');
		echo $this->Form->input('description');
		echo $this->Form->input('lft');
		echo $this->Form->input('rght');
		echo $this->Form->input('parent_id');
		echo $this->Form->input('bool_main');
		echo $this->Form->input('bool_creditor');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingCode.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingCode.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Movements'), array('controller' => 'accounting_movements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Movement'), array('controller' => 'accounting_movements', 'action' => 'add')); ?> </li>
	</ul>
</div>
