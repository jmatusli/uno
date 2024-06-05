<div class="closingDates index">
<?php
	echo '<h2>'.__('Closing Dates').'</h2>';

  echo $this->Form->create('Report');
  echo "<fieldset>";
    echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);

    //echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')));
    //echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
  echo "</fieldset>";
  //echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
  //echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
  echo $this->Form->end(__('Refresh'));

	echo '<table cellpadding="0" cellspacing="0">';
    echo '<thead>';
      echo '<tr>';
        echo '<th>'.__('Closing Date').'</th>';
        echo '<th>'.__('Enterprise').'</th>';
        echo '<th>'.__('Name').'</th>';
        echo '<th class="actions">'.__('Actions').'</th>';
      echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($closingDates as $closingDate){
      $closingDateTime=new DateTime($closingDate['ClosingDate']['closing_date']);
      echo '<tr>';
        echo '<td>'.$this->Html->link($closingDateTime->format('d-m-Y'),['action'=>'detalle',$closingDate['ClosingDate']['id']]).'</td>';
        echo '<td>'.$closingDate['Enterprise']['company_name'].'</td>';
        echo '<td>'.$closingDate['ClosingDate']['name'].'</td>';
        echo '<td class="actions">';
        //if ($bool_edit_permission){
          echo $this->Html->link(__('Edit'), ['action' => 'editar', $closingDate['ClosingDate']['id']]); 
        //}
        echo '</td>';
      echo '</tr>';
    } 
    
    echo '</tbody>';
	echo '</table>';
?>
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('New Closing Date'), ['action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
