<div class="tanks index">
<?php 
	echo "<h2>".__('Tanks')."</h2>";
	echo "<table>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('enterprise_id')."</th>";
        echo "<th>".$this->Paginator->sort('product_id')."</th>";
        echo "<th>".$this->Paginator->sort('bool_active')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($tanks as $tank){
      if ($tank['Tank']['bool_active']){
        echo "<tr>";
      }
      else {
        echo "<tr class='italic'>";
      }
        echo "<td>".$this->Html->link($tank['Tank']['name'],['action' => 'detalle', $tank['Tank']['id']])."</td>";
        echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($tank['Enterprise']['company_name'],['controller'=>'enterprises','action' => 'detalle', $tank['Enterprise']['id']]);
        }
        else {
          echo $tank['Enterprise']['company_name'];
        }
        echo "</td>";
        echo "<td>";
        if ($userRole == ROLE_ADMIN){
          echo $this->Html->link($tank['Product']['name'],['controller'=>'products','action' => 'view', $tank['Product']['id']]);
        }
        else {
          echo $tank['Product']['name'];
        }
        echo "</td>";
        echo "<td>".($tank['Tank']['bool_active']?__('Active'):__('Inactive'))."</td>";
      
        echo "<td class='actions'>";
          if ($bool_edit_permission){
            echo $this->Html->link(__('Edit'), ['action' => 'editar', $tank['Tank']['id']]); 
          }
          if ($bool_delete_permission){
            // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $tank['Tank']['id']), array(), __('Are you sure you want to delete # %s?', $tank['Tank']['id'])); 
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
		echo "<li>".$this->Html->link(__('New Tank'), ['action' => 'crear'])."</li>";
    /*
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Enterprises'), array('controller' => 'enterprises', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Enterprise'), array('controller' => 'enterprises', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
    */
	echo "</ul>";
?>
</div>