<div class="tanks view">
<?php 
	echo "<h2>".__('Tank')." ".$tank['Tank']['name']."</h2>";
  echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate]);
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
  echo "<br/>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($tank['Tank']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".(empty($tank['Tank']['description'])?"-":$tank['Tank']['description'])."</dd>";
		echo "<dt>".__('Enterprise')."</dt>";
		echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($tank['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'detalle',$tank['Enterprise']['id']]):$tank['Enterprise']['company_name'])."</dd>";
		echo "<dt>".__('Combustible')."</dt>";
		echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($tank['Product']['name'],['controller'=>'products','action'=>'view',$tank['Product']['id']]):$tank['Product']['name'])."</dd>";
		echo "<dt>".__('Bool Active')."</dt>";
		echo "<dd>".h($tank['Tank']['bool_active'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Tank'), ['action' => 'editar', $tank['Tank']['id']])."</li>";
      echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			echo "<li>".$this->Form->postLink(__('Delete Tank'), ['action' => 'eliminar', $tank['Tank']['id']], [], __('Está seguro que quiere eliminar el tanque %s?', $tank['Tank']['name']))."</li>";
      echo "<br/>";
    }
		echo "<li>".$this->Html->link(__('List Tanks'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Tank'), ['action' => 'crear'])."</li>";
	echo "</ul>";
?> 
</div>
