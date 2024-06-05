<script>
	function proposeName(){
		var proposedName = "Cierre "+$('#ClosingDateClosingDateMonth option:selected').text()+" "+$('#ClosingDateClosingDateYear option:selected').text();
		$('#ClosingDateName').val(proposedName);
	}
	
	$('#ClosingDateClosingDateMonth').change(function(){
		proposeName();
	});
	
	$('#ClosingDateClosingDateYear').change(function(){
		proposeName();
	});
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});

	$(document).ready(function(){
		proposeName();
	});
</script>
<div class="closingDates form">
<?php 
  echo $this->Form->create('ClosingDate');
	echo '<fieldset>';
		echo '<legend>'. __('Add Closing Date').'</legend>';
		echo $this->Form->input('name');
		echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
		echo $this->Form->input('closing_date',['type'=>'date','dateFormat'=>'DMY','default'=>$proposedClosingDate,'minYear'=>2019,'maxYear'=>date('Y')+1]);
	echo '</fieldset>';
  echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Closing Dates'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>
