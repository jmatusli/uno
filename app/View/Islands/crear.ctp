<div class="islands form">
<?php 
	echo $this->Form->create('Island'); 
	echo "<div class='col-md-6'>";
		echo "<fieldset>";
			echo "<legend>".__('Add Island')."</legend>";
			echo $this->Form->input('name');
			echo $this->Form->input('description');
      echo $this->Form->input('enterprise_id',['label'=>'Empresa','empty'=>['0' =>'Seleccione Empresa']]);
			echo $this->Form->input('bool_active',['checked'=>true]);
		echo "</fieldset>"; 
	echo "</div>"; 
	echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Islands'), ['action' => 'resumen'])."</li>";
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