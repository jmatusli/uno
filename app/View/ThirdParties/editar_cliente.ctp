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
	echo $this->Form->create('ThirdParty');
	echo "<fieldset>";
		echo "<legend>".__('Edit Client')." ".$this->request->data['ThirdParty']['company_name']."</legend>";
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
					echo "<div class='col-md-3'>";
          /*
					echo "<h3>Usuarios Ya Asociados</h3>";
					if (empty($usersAssociatedWithClient)){
						echo "<p>No hay usuarios asociados con este cliente aun</p>";
					}
					else {
					echo "<ul>";
						foreach ($usersAssociatedWithClient as $user){
							echo "<li>".$this->Html->Link((!empty($user['User']['first_name'])?$user['User']['first_name']." ".$user['User']['last_name']:$user['User']['username']),array('controller'=>'user','action'=>'view',$user['User']['id']))."</li>";
						}
						echo "</ul>";
					}
          */
          echo "<h3>Empresas Ya Asociados</h3>";
					if (empty($enterprisesAssociatedWithClient)){
						echo "<p>No hay empresas asociados con este cliente aun</p>";
					}
					else {
					echo "<ul>";
						foreach ($enterprisesAssociatedWithClient as $enterprise){
            //pr($enterprise);
							echo "<li>".$this->Html->Link($enterprise['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'ver',$enterprise['Enterprise']['id']])."</li>";
						}
						echo "</ul>";
					}
				echo "</div>";
				echo "<div class='col-md-3'>";
					//pr($users);
					/*
					echo "<div id='VendorList'>"
						;echo "<h3>Vendedores Relacionados</h3>";
						for ($u=0;$u<count($users);$u++){
							$userChecked=false;
							if (!empty($users[$u]['ClientUser'])){
								//pr($users[$u]['ClientUser']);
								$userChecked=$users[$u]['ClientUser'][0]['bool_assigned'];
							}
							//pr($users[$u]);
							echo $this->Form->input('User.'.$u.'.id',array('type'=>'checkbox','checked'=>$userChecked,'label'=>(!empty($users[$u]['User']['first_name'])?$users[$u]['User']['first_name']." ".$users[$u]['User']['last_name']:$users[$u]['User']['username']),'div'=>array('class'=>'checkboxleftbig')));
						}
          echo "</div>";  
					*/
          echo "<div id='EnterpriseList' style='width:45%;float:left;clear:none;padding-left:5%;'>";
            echo "<h3>Empresas Relacionadas</h3>";
            for ($e=0;$e<count($enterprises);$e++){
              $enterpriseChecked=false;
							if (!empty($enterprises[$e]['ClientEnterprise'])){
								//pr($enterprises[$e]['ClientEnterprise']);
								$enterpriseChecked=$enterprises[$e]['ClientEnterprise'][0]['bool_assigned'];
							}
							//pr($enterprises[$e]);
              echo $this->Form->input('Enterprise.'.$e.'.id',['type'=>'checkbox','checked'=>$enterpriseChecked,'label'=>$enterprises[$e]['Enterprise']['company_name'],'div'=>['class'=>'checkboxleftbig']]);
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
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ThirdParty.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ThirdParty.id')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Clients'), array('action' => 'resumenClientes'))."</li>";
		echo "<br/>";
		if ($bool_saleremission_index_permission) {
			echo "<li>".$this->Html->link(__('Todas Ventas y Remisiones'), array('controller' => 'orders', 'action' => 'resumenVentasRemisiones'))."</li>";
		}
		if ($bool_sale_add_permission) {
			echo "<li>".$this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'crearVenta'))."</li>";
		}
		if ($bool_remission_add_permission) {
			echo "<li>".$this->Html->link(__('New Remission'), array('controller' => 'orders', 'action' => 'crarRemision'))."</li>";
		}
	echo "</ul>";
?>
</div>
