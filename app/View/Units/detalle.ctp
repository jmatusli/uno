<div class="units view">
<?php 
	echo "<h2>".__('Unit')." ".$unit['Unit']['name']."</h2>";
  //pr($unit);
  echo "<br/>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($unit['Unit']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".(empty($unit['Unit']['description'])?"-":$unit['Unit']['description'])."</dd>";
		echo "<dt>".__('Target Unit')."</dt>";
		echo "<dd>".(empty($unit['TargetUnit']['name'])?"-":$this->Html->link($unit['TargetUnit']['name'],['action'=>'detalle',$unit['TargetUnit']['id']]))."</dd>";
		echo "<dt>".__('Conversion Factor')."</dt>";
		echo "<dd>".(empty($unit['Unit']['conversion_factor'])?"-":$unit['Unit']['conversion_factor'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Unit'), ['action' => 'editar', $unit['Unit']['id']])."</li>";
      echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			echo "<li>".$this->Form->postLink(__('Delete Unit'), ['action' => 'eliminar', $unit['Unit']['id']], [], __('Está seguro que quiere eliminar el tanque %s?', $unit['Unit']['name']))."</li>";
      echo "<br/>";
    }
		echo "<li>".$this->Html->link(__('List Units'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Unit'), ['action' => 'crear'])."</li>";
	echo "</ul>";
?> 
</div>
