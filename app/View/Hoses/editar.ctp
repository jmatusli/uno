<div class="hoses form">
<?php 
	echo $this->Form->create('Hose'); 
	echo "<div class='col-md-6'>";
		echo "<fieldset>";
			echo "<legend>".__('Edit Hose')."</legend>";

			echo $this->Form->input('id',array('hidden'=>'hidden'));
			echo $this->Form->input('name');
			echo $this->Form->input('description');
			echo $this->Form->input('bool_active');
      echo $this->Form->input('enterprise_id');
      echo $this->Form->input('island_id');
      echo $this->Form->input('product_id');

		echo "</fieldset>";
	echo "</div>";
	echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Hose'), ['action' => 'eliminar', $this->Form->value('Hose.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('Hose.id')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Hoses'), ['action' => 'resumen'])."</li>";
    /*
		echo "<br/>";
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
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>