<div class="units index">
<?php 
	echo "<h2>".__('Units')."</h2>";
	echo "<table>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('description')."</th>";
        echo "<th>".$this->Paginator->sort('target_unit_id')."</th>";
        echo "<th>".$this->Paginator->sort('conversion_factor')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($units as $unit){
      echo "<tr>";
        echo "<td>".$this->Html->link($unit['Unit']['name'],['action' => 'detalle', $unit['Unit']['id']])."</td>";
        echo "<td>".(empty($unit['Unit']['description'])?"-":$unit['Unit']['description'])."</td>";
        echo "<td>".$this->Html->link($unit['TargetUnit']['name'],['action' => 'detalle', $unit['TargetUnit']['id']])."</td>";
        echo "<td class='actions'>";
          if ($bool_edit_permission){
            echo $this->Html->link(__('Edit'), ['action' => 'editar', $unit['Unit']['id']]); 
          }
          if ($bool_delete_permission){
            // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $unit['Unit']['id']), array(), __('Are you sure you want to delete # %s?', $unit['Unit']['id'])); 
          }
        echo "</td>";
        echo "<td>".(empty($unit['Unit']['conversion_factor'])?"-":$unit['Unit']['conversion_factor'])."</td>";
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
		echo "<li>".$this->Html->link(__('New Unit'), ['action' => 'crear'])."</li>";
	echo "</ul>";
?>
</div>