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
    if ($('#UserRoleId').val() !=<?php echo ROLE_CLIENT; ?>){
      $('#clientDiv').hide();
    }
  });
</script>
<div class="users form">
<?php 
  echo $this->Form->create('User'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit User')."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='rows'>";	
				echo "<div class='col-md-6'>";
					echo $this->Form->input('id');
            echo $this->Form->input('id');
            echo $this->Form->input('username');
            echo $this->Form->input('pwd',['value'=>'','required'=>false,'label'=>__('Password'),'type'=>'password']);
            echo $this->Form->input('role_id');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('email');
            echo $this->Form->input('phone');
            echo $this->Form->input('bool_active',['div'=>['class'=>'checkboxleft']]);
            echo $this->Form->input('bool_show_in_list',['label'=>'Mostrar en listas','div'=>['class'=>'checkboxleft']]);
            echo $this->Form->input('bool_view_all_users',['label'=>'Puede ver todos usuarios','div'=>['class'=>'checkboxleft']]);
            
            echo $this->Form->input('client_id',['empty'=>['--Seleccione cliente--'],'div'=>['id'=>'clientDiv']]);
            
            echo $this->Form->Submit(__('Submit'));
          echo "</div>";
				echo "<div class='col-md-3'>";
					echo "<h3>Clientes Ya Asociados</h3>";
					echo "<ul>";
					foreach ($clientsAssociatedWithUser as $client){
						echo "<li>".$this->Html->Link($client['ThirdParty']['company_name'],['controller'=>'clients','action'=>'view',$client['ThirdParty']['id']])."</li>";
					}
					echo "</ul>";
				echo "</div>";
				echo "<div class='col-md-3'>";
					//echo "<div id='ClientList' style='width:45%;float:left;clear:none;' class='col-md-6'>";
					echo "<div id='ClientList'>";
						echo "<h3>Asociar con Clientes</h3>";
						for ($c=0;$c<count($clients);$c++){
							$clientChecked=false;
							if (!empty($clients[$c]['ClientUser'])){
								$clientChecked=$clients[$c]['ClientUser'][0]['bool_assigned'];;
							}
							echo $this->Form->input('Client.'.$c.'.id',['type'=>'checkbox','checked'=>$clientChecked,'label'=>$clients[$c]['ThirdParty']['company_name'],'div'=>['class'=>'checkboxleftbig']]);
						}
					echo "</div>";
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

		<!--li><?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('User.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('User.id'))); ?></li-->
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
