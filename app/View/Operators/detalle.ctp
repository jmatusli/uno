<div class="operators view">
<?php 
  echo "<h2>".__('Operator')." ".$operator['Operator']['name']."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate]);
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
  echo "<dl>";
    echo "<dt>".__('Enterprise')."</dt>";
    echo "<dd>".($userRole == ROLE_ADMIN?$this->Html->link($operator['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'detalle',$operator['Enterprise']['id']]):$operator['Enterprise']['company_name'])."</dd>";
  echo "</dl>";
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Operator'), ['action' => 'editar', $operator['Operator']['id']])."</li>";
			echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Operator'), ['action' => 'eliminar', $operator['Operator']['id']], array(), __('Are you sure you want to delete %s?', $operator['Operator']['id']))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Operators'), array('action' => 'resumen'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Operator'), array('action' => 'crear'))."</li>";
		}
    /*
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
    */
		foreach ($otherOperators as $otherOperator){
			echo "<li>".$this->Html->link($otherOperator['Operator']['name'], ['controller' => 'Operators', 'action' => 'detalle',$otherOperator['Operator']['id']])."</li>";
		}
	echo "</ul>";
?>
</div>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
		
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		formatPercentages();
	});
</script>