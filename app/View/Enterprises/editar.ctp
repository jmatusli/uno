<script>
  $('body').on('change','#ThirdPartyCreditDays',function(){
    setDisplayCreditAmount();
  });

  function setDisplayCreditAmount(){
    var creditDays=0;
    if (!isNaN($('#ThirdPartyCreditDays').val())){
      creditDays=parseInt($('#ThirdPartyCreditDays').val());
    }
    if (creditDays>0){
      $('#ThirdPartyCreditAmount').closest('div').show();
     
    }
    else {
      $('#ThirdPartyCreditAmount').val(0);
      $('#ThirdPartyCreditAmount').closest('div').hide();
     
    }
  }
  
  $(document).ready(function(){
		setDisplayCreditAmount();	
    <?php if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT) { ?>
      $('#VendorList').addClass('hidden');
    <?php } ?>
	});
  
</script>
<div class="thirdParties form">
<?php 
	//$this->Html->script->link('prototype', false); 
    //$this->Html->script->link('scriptaculous.js?load=effects,controls', false); 
	echo $this->Form->create('Enterprise');
	echo "<fieldset>";
		echo "<legend>".__('Edit Enterprise')." ".$this->request->data['Enterprise']['company_name']."</legend>";
    echo "<div class='container-fluid'>";
				echo "<div class='rows'>";	
					echo "<div class='col-md-6'>";
            echo $this->Form->input('id',['hidden'=>'hidden']);
            // echo $this->Form->input('bool_provider');
            echo $this->Form->input('company_name');
            echo $this->Form->input('bool_active');
            echo $this->Form->input('accounting_code_id',['empty'=>['0'=>__('Select Accounting Code')]]);
            if ($roleId==ROLE_ADMIN){
              echo $this->Form->input('credit_days');
            }
            else {
              echo $this->Form->input('credit_days',['readonly'=>'readonly']);
            }
            echo $this->Form->input('credit_amount');
            echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
            //echo "<div class='input text'><label for='LocationId'>Nombre</label>".$combobox->create('location_id', '/thirdParties/autoComplete', ['comboboxTitle' => "View Locations"])."</div>"; 
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('email');
            echo $this->Form->input('phone');
            echo $this->Form->input('address');
            echo $this->Form->input('ruc_number');
          echo "</div>";
					echo "<div class='col-md-4'>";
					echo "<h3>Usuarios Ya Asociados</h3>";
					if (empty($usersAssociatedWithClient)){
						echo "<p>No hay usuarios asociados con esta empresa aun</p>";
					}
					else {
					echo "<ul>";
						foreach ($usersAssociatedWithClient as $user){
							echo "<li>".$this->Html->Link((!empty($user['User']['first_name'])?$user['User']['first_name']." ".$user['User']['last_name']:$user['User']['username']),['controller'=>'user','action'=>'view',$user['User']['id']])."</li>";
						}
						echo "</ul>";
					}
				echo "</div>";
				echo "<div class='col-md-2'>";
					//pr($users);
					echo "<div id='UserList'>"
						;echo "<h3>Usuarios Relacionados</h3>";
						for ($u=0;$u<count($users);$u++){
							$userChecked=false;
							if (!empty($users[$u]['EnterpriseUser'])){
								//pr($users[$u]['EnterpriseUser']);
								$userChecked=$users[$u]['EnterpriseUser'][0]['bool_assigned'];
							}
							//pr($users[$u]);
							echo $this->Form->input('User.'.$u.'.id',['type'=>'checkbox','checked'=>$userChecked,'label'=>(!empty($users[$u]['User']['first_name'])?$users[$u]['User']['first_name']." ".$users[$u]['User']['last_name']:$users[$u]['User']['username']),'div'=>['class'=>'checkboxleftbig']]);
						}
					
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</fieldset>";

  echo $this->Form->Submit(__('Submit')); 
	echo $this->Form->end(); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('ThirdParty.id')], [], __('Está seguro que quiere eliminar la empresa %s?', $this->Form->value('Enterprise.id')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Enterprises'), ['action' => 'resumen'])."</li>";
	echo "</ul>";
?>
</div>
