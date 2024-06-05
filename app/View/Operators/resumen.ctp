<div class="operators index">
<?php 
  echo "<h2>".__('Operators')."</h2>";
  if (count($enterprises) > 1){
    echo $this->Form->create('Report');
    echo "<fieldset>";
      echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
    echo "</fieldset>";
    echo $this->Form->end('Aplicar filtro');
  }
  
	echo "<table>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('enterprise_id')."</th>";
        echo "<th>".$this->Paginator->sort('bool_active')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($operators as $operator){
      if ($operator['Operator']['bool_active']){
        echo "<tr>";
      }
      else {
        echo "<tr class='italic'>";
      }
        echo "<td>".$this->Html->link($operator['Operator']['name'],['action' => 'detalle', $operator['Operator']['id']])."</td>";
        echo "<td>";
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Html->link($operator['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $operator['Enterprise']['id']]);
        }
        else {
          echo $operator['Enterprise']['company_name'];
        }
        echo "</td>";
        echo "<td>".($operator['Operator']['bool_active']?__('Active'):__('Inactive'))."</td>";
      
        echo "<td class='actions'>";
          if ($bool_edit_permission){
            echo $this->Html->link(__('Edit'), ['action' => 'editar', $operator['Operator']['id']]); 
          }
          if ($bool_delete_permission){
            // echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $operator['Operator']['id']], [], __('Are you sure you want to delete # %s?', $operator['Operator']['id'])); 
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
			echo "<li>".$this->Html->link(__('New Operator'), ['action' => 'crear'])."</li>";
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
