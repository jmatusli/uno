<div class="hoses form">
<?php 
	echo $this->Form->create('Hose'); 
	echo "<div class='col-md-6'>";
		echo "<fieldset>";
			echo "<legend>".__('Add Hose')."</legend>";
			echo $this->Form->input('name');
			echo $this->Form->input('description');
			echo $this->Form->input('bool_active',array('checked'=>true));
      echo $this->Form->input('enterprise_id',['label'=>'Enterprise','default'=>$enterpriseId,'empty'=>['0' =>'-- Seleccione Gasolinera --']]);
      echo $this->Form->input('island_id',['label'=>'Island','empty'=>['0' =>'-- Seleccione Isla --']]);
      echo $this->Form->input('product_id',['label'=>'Product','empty'=>['0' =>'-- Seleccione Producto --']]);
		echo "</fieldset>"; 
	echo "</div>"; 
	echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Hoses'), ['action' => 'resumen'])."</li>";
	echo "</ul>";
?>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>