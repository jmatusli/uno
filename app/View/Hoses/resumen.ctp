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
<div class="hoses index">
<?php 
	echo "<h2>".__('Hoses')."</h2>";
	//pr($hoses);
	echo "<table cellpadding='0' cellspacing='0'>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('enterprise_id')."</th>";
        echo "<th>".$this->Paginator->sort('island_id')."</th>";
        echo "<th>".$this->Paginator->sort('product_id')."</th>";
        echo "<th>".$this->Paginator->sort('description')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
		foreach ($hoses as $hose){
			if ($hose['Hose']['bool_active']){
				echo "<tr>";
			}
			else {
				echo "<tr class='italic'>";
			}
				echo "<td>".$this->Html->link($hose['Hose']['name'].($hose['Hose']['bool_active']?"":" (Deshabilitada)"),['action'=>'detalle',$hose['Hose']['id']])."</td>";
				echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($hose['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $hose['Enterprise']['id']]);
        }
        else {
          echo $hose['Enterprise']['company_name'];
        }
        echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($hose['Island']['name'],['controller'=>'islands','action' => 'detalle', $hose['Island']['id']]);
        }
        else {
          echo $hose['Island']['name'];
        }
        echo "</td>";
        echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($hose['Product']['name'],['controller'=>'[products','action' => 'view', $hose['Product']['id']]);
        }
        else {
          echo $hose['Product']['name'];
        }
        echo "</td>";
        echo "<td>".$hose['Hose']['description']."</td>";
				echo "<td class='actions'>";
					if($bool_edit_permission){
						echo $this->Html->link(__('Edit'), ['action' => 'editar', $hose['Hose']['id']]); 
					}
					if($bool_delete_permission){
						// echo $this->Form->postLink(__('Delete'), ['action' => 'eliminar', $hose['Hose']['id']], [], __('Está seguro que quiere eliminar manguera %s?', $hose['Hose']['name']));
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
			echo "<li>".$this->Html->link(__('New Hose'), ['action' => 'crear'])."</li>";
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