<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
  $('body').on('change','#UserRoleId',function(){	
		var userRoleId=$(this).val();
    if (userRoleId==<?php echo ROLE_CLIENT; ?>){
      $('#clientDiv').show();  
    }
    else {
      $('#UserClientId').val(0);
      $('#clientDiv').hide();
    }
	});
  
  $(document).ready(function(){
    $('#clientDiv').hide();
  });
</script>
<div class="users form">
<?php 
  echo $this->Form->create('User'); 
	echo "<fieldset>";
		echo "<legend>".__('Add User')."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='rows'>";	
				echo "<div class='col-md-6'>";				
          echo $this->Form->input('username');
          echo $this->Form->input('password');
          echo $this->Form->input('role_id');
          echo $this->Form->input('first_name');
          echo $this->Form->input('last_name');
          echo $this->Form->input('email');
          
          echo $this->Form->input('bool_active',['value'=>1,'type'=>'hidden']);
          
          echo $this->Form->input('bool_show_in_list',['label'=>'Mostrar en listas','default'=>1,'div'=>['class'=>'checkboxleft']]);
          echo $this->Form->input('bool_view_all_users',['label'=>'Puede ver todos usuarios','default'=>0,'div'=>['class'=>'checkboxleft']]);
          
          echo $this->Form->input('client_id',['default'=>0,'empty'=>['--Seleccione cliente--'],'div'=>['id'=>'clientDiv']]);
          
          echo $this->Form->Submit(__('Submit'));
        echo "</div>";	
				echo "<div class='col-md-6'>";		
          //echo "<div id='ClientList' style='width:45%;float:left;clear:none;'>";
          //echo "<h3>Clientes Relacionados</h3>";
          /*for ($c=0;$c<count($clients);$c++){
            echo $this->Form->input('Client.'.$c.'.id',['type'=>'checkbox','default'=>false,'label'=>$clients[$c]['ThirdParty']['company_name'],'div'=>['class'=>'checkboxleftbig']]);
          }
          */
        echo "</div>";
      echo "</div>";
    echo "</div>";  
	echo "</fieldset>";
  echo $this->Form->end();
?>  
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
		<!--li><?php echo $this->Html->link(__('List Roles'), array('controller' => 'roles', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Role'), array('controller' => 'roles', 'action' => 'add')); ?> </li-->
		<br/>
		<?php if ($userRoleId==ROLE_ADMIN) { ?>	
		<li><?php echo $this->Html->link(__('List User Logs'), array('controller' => 'user_logs', 'action' => 'index')); ?> </li>
		<?php } ?>	
		<!--li><?php echo $this->Html->link(__('New User Log'), array('controller' => 'user_logs', 'action' => 'add')); ?> </li-->
	</ul>
</div>
