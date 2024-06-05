<div class="shifts index">
	<h2><?php echo __('Shifts'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
      <th><?php echo $this->Paginator->sort('enterprise_id'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($shifts as $shift): ?>
	<tr>
	<?php 
    echo "<td>".$this->Html->link($shift['Shift']['name'], ['action' => 'view', $shift['Shift']['id']])."&nbsp;</td>";
		echo "<td>".h($shift['Shift']['description'])."&nbsp;</td>";
    echo "<td>";
    if ($userRole == ROLE_ADMIN){
      echo $this->Html->link($shift['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $shift['Enterprise']['id']]);
    }
    else {
      echo $shift['Enterprise']['company_name'];
    }
    echo "</td>";
  ?>
		<td class="actions">
			
			<? if ($bool_edit_permission){ ?>
			<?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $shift['Shift']['id']]); ?>
			<? } ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shift['Shift']['id']), array(), __('Are you sure you want to delete # %s?', $shift['Shift']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Shift'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
