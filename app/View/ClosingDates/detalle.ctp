<div class="closingDates view">
<?php 
  echo '<h2>'.__('Closing Date').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($closingDate['ClosingDate']['name']).'</dd>';
    echo '<dt>'.__('Enterprise').'</dt>';
		echo '<dd>'.h($closingDate['Enterprise']['company_name']).'</dd>';
		echo '<dt>'.__('Closing Date').'</dt>';
		echo '<dd>'.h($closingDate['ClosingDate']['closing_date']).'</dd>';
	echo '</dl>';
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('Edit Closing Date'), ['action' => 'editar', $closingDate['ClosingDate']['id']]).' </li>';
		//echo '<li>'.$this->Form->postLink(__('Delete Closing Date'), ['action' => 'delete', $closingDate['ClosingDate']['id']], [], __('Are you sure you want to delete # %s?', $closingDate['ClosingDate']['id'])).' </li>';
    echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Closing Dates'), ['action' => 'resumen']).' </li>';
		echo '<li>'.$this->Html->link(__('New Closing Date'), ['action' => 'crear']).' </li>';
	echo '</ul>';
?>
</div>
