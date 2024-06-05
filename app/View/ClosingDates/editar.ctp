<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
<div class="closingDates form">
<?php 
  echo $this->Form->create('ClosingDate');
	echo '<fieldset>';
		echo '<legend>'.__('Edit Closing Date').'</legend>';
		echo $this->Form->input('ìd',['type'=>'hidden']);
		echo $this->Form->input('name');
    echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
		echo $this->Form->input('closing_date',['type'=>'date','dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')+1]);
	echo '</fieldset>';
  echo $this->Form->end(__('Submit')); 
?>

</div>
<div class="actions">
<?php 
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		//echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('ClosingDate.id')], [], __('Está seguro que quiere remover la fecha de cierre %s?', $this->Form->value('ClosingDate.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Closing Dates'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>