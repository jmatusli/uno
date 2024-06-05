<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			/*$(this).parent().prepend("C$ ");*/
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
<div class="machines index">
<?php 
	echo "<h2>".__('Islands')."</h2>";
	//pr($islands);
	echo "<table cellpadding='0' cellspacing='0'>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('description')."</th>";
        echo "<th>".$this->Paginator->sort('enterprise_id')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
		foreach ($islands as $island){
			if ($island['Island']['bool_active']){
				echo "<tr>";
			}
			else {
				echo "<tr class='italic'>";
			}
				echo "<td>".$this->Html->link($island['Island']['name'].($island['Island']['bool_active']?"":" (Deshabilitada)"),array('action'=>'detalle',$island['Island']['id']))."</td>";
				echo "<td>".$island['Island']['description']."</td>";
        echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($island['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $island['Enterprise']['id']]);
        }
        else {
          echo $island['Enterprise']['company_name'];
        }
        echo "</td>";
				echo "<td class='actions'>";
					if($bool_edit_permission){
						echo $this->Html->link(__('Edit'), ['action' => 'editar', $island['Island']['id']]); 
					}
					if($bool_delete_permission){
						// echo $this->Form->postLink(__('Delete'), ['action' => 'eliminar', $island['Island']['id']], [], __('Are you sure you want to delete %s?', $island['Island']['name']));
					}
				echo "</td>";
			echo "</tr>";
		}
    echo "</tbody>";
	echo "</table>";
?>  
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Island'), ['action' => 'crear'])."</li>";
			echo "<br/>";
		}
    /*
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
    */
	echo "</ul>";
?>
</div>