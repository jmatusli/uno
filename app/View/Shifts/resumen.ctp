<div class="shifts index">
<?php 
  echo "<h2>".__('Shifts')."</h2>";
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
        echo "<th>".$this->Paginator->sort('description')."</th>";
        echo "<th>".$this->Paginator->sort('enterprise_id')."</th>";
        echo "<th>".$this->Paginator->sort('bool_active')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($shifts as $shift){
      if ($shift['Shift']['bool_active']){
        echo "<tr>";
      }
      else {
        echo "<tr class='italic'>";
      }
        echo "<td>".$this->Html->link($shift['Shift']['name'], ['action' => 'detalle', $shift['Shift']['id']])."&nbsp;</td>";
        echo "<td>".h($shift['Shift']['description'])."&nbsp;</td>";
        echo "<td>";
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Html->link($shift['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $shift['Enterprise']['id']]);
        }
        else {
          echo $shift['Enterprise']['company_name'];
        }
        echo "</td>";
        echo "<td>".($shift['Shift']['bool_active']?__('Active'):__('Inactive'))."</td>";
      
        echo "<td class='actions'>";
          if ($bool_edit_permission){
            echo $this->Html->link(__('Edit'), ['action' => 'editar', $shift['Shift']['id']]); 
          }
          if ($bool_delete_permission){
            // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shift['Shift']['id']), [], __('Are you sure you want to delete # %s?', shift['Shift']['id'])); 
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
    echo "<li>".$this->Html->link(__('New Shift'), ['action' => 'crear'])."</li>";
  }
	echo "</ul>";
?>
</div>
