<div class="employees index">
<?php	
	echo '<h2>'.__('Employees').'</h2>';
  
  echo $this->Form->create('Report');
    echo "<fieldset>";
      echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
    echo "</fieldset>";
    echo $this->Form->submit(__('Refresh',['div'=>['style'=>'clear:left;']]));
    //echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenRecibos'], ['class' => 'btn btn-primary']); 
    echo $this->Form->end();
        
  if ($enterpriseId == 0){
    echo '<h3>Seleccione una gasolinera para ver los empleados</h3>';
  }
  else {
    if (empty($employees)){
      echo '<h3>No hay empleados activos registrados para esta gasolinera</h3>';
    }
    else {
      echo '<table cellpadding="0" cellspacing="0">';
        echo '<thead>';
          echo '<tr>';
            echo '<th>'.$this->Paginator->sort('first_name').'</th>';
            echo '<th>'.$this->Paginator->sort('last_name').'</th>';
            echo '<th>Empresa</th>';
            echo '<th>'.$this->Paginator->sort('position','Cargo').'</th>';
            echo '<th>'.$this->Paginator->sort('starting_date').'</th>';
            echo '<th>'.$this->Paginator->sort('ending_date').'</th>';
            echo '<th class="centered">Días Acumulados</th>';
            echo '<th class="centered">Días Descansados</th>';
            echo '<th class="centered">Saldo</th>';
            echo '<th class="actions">'.__('Actions').'</th>';
          echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($employees as $employee){
          $startingDate= new DateTime($employee['Employee']['starting_date']);
          $endingDate= new DateTime($employee['Employee']['ending_date']);
          //echo "holidays earned is ".$employee['Employee']['holidays_earned']."<br/>";
          echo "<tr>";
            echo "<td>".$this->Html->link($employee['Employee']['first_name'], ['action' => 'detalle', $employee['Employee']['id']])."</td>";
            echo "<td>".$this->Html->link($employee['Employee']['last_name'], ['action' => 'detalle', $employee['Employee']['id']])."</td>";
            echo "<td>".($userRoleId == ROLE_ADMIN?$this->Html->link($employee['Enterprise']['company_name'], ['controller'=>'enterprises','action' => 'detalle', $employee['Enterprise']['id']]):$employee['Enterprise']['company_name'])."</td>";
            echo "<td>".h($employee['Employee']['position'])."&nbsp;</td>";
            echo "<td>".$startingDate->format('d-m-Y')."&nbsp;</td>";
            echo "<td>".$endingDate->format('d-m-Y')."&nbsp;</td>";
            echo "<td class='centered'>".number_format($employee['Employee']['holidays_earned'],2,".",",")."&nbsp;</td>";
            echo "<td class='centered'>".number_format($employee['Employee']['holidays_taken'],2,".",",")."&nbsp;</td>";
            echo "<td class='centered'>".number_format(($employee['Employee']['holidays_earned']-$employee['Employee']['holidays_taken']),2,".",",")."&nbsp;</td>";
            echo "<td class='actions'>";
              if ($bool_edit_permission){
                echo $this->Html->link(__('Edit'), ['action' => 'editar', $employee['Employee']['id']]); 
              }
              if ($bool_delete_permission){
                //echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $employee['Employee']['id']], [], __('Are you sure you want to delete %s?', ($employee['Employee']['first_name']." ".$employee['Employee']['last_name']))); 
              }
            echo "</td>";
          echo "</tr>";
        }
        echo '</tbody>';
      echo '</table>';
    }
  }
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee'), ['action' => 'crear'])."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('Empleados Desactivados'), ['action' => 'resumenEmpleadosDesactivados'])."</li>";
		echo "<br/>";
		if ($bool_employeeholiday_index_permission) {
			echo "<li>".$this->Html->link(__('List Employee Holidays'), ['controller' => 'employee_holidays', 'action' => 'index'])." </li>";
		}
		if ($bool_employeeholiday_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), ['controller' => 'employee_holidays', 'action' => 'add'])." </li>";
		}
	echo "</ul>";
?>		
</div>
