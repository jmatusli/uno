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
<div class="thirdParties form clients">
<?php 
  echo $this->Form->create('ThirdParty');
	echo "<fieldset>";
		echo "<legend>".__('Add Client')."</legend>";
    echo "<div class='container-fluid'>";
      echo "<div class='rows'>";	
        echo "<div class='col-md-6'>";	
		
          //echo $this->Form->input('bool_provider');
          echo $this->Form->input('company_name');
          echo $this->Form->input('bool_active',['default'=>true]);
          echo $this->Form->input('accounting_code_id',['default'=>$newClientCode,'class'=>'fixedselection','empty'=>['0'=>__('Select Accounting Code')]]);
          if ($roleId==ROLE_ADMIN){
            echo $this->Form->input('credit_days',['default'=>0]);
          }
          else {
            echo $this->Form->input('credit_days',['default'=>0,'readonly'=>'readonly']);
          }
          echo $this->Form->input('credit_amount');
          echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
          
          echo $this->Form->input('first_name');
          echo $this->Form->input('last_name');
          echo $this->Form->input('email');
          echo $this->Form->input('phone');
          echo $this->Form->input('address');
          echo $this->Form->input('ruc_number');
        echo "</div>";	
        echo "<div class='col-md-6'>";		
          /*echo "<div id='VendorList' style='width:45%;float:left;clear:none;padding-left:5%;'>";
            echo "<h3>Vendedores Relacionados</h3>";
            for ($u=0;$u<count($users);$u++){
              
              echo $this->Form->input('User.'.$u.'.id',['type'=>'checkbox','default'=>false,'label'=>(!empty($users[$u]['User']['first_name'])?$users[$u]['User']['first_name']." ".$users[$u]['User']['last_name']:$users[$u]['User']['username']),'div'=>['class'=>'checkboxleftbig']]);
            }
          echo "</div>";
          */
          echo "<div id='EnterpriseList' style='width:45%;float:left;clear:none;padding-left:5%;'>";
            echo "<h3>Empresas Relacionadas</h3>";
            for ($e=0;$e<count($enterprises);$e++){
              
              echo $this->Form->input('Enterprise.'.$e.'.id',['type'=>'checkbox','default'=>false,'label'=>$enterprises[$e]['Enterprise']['company_name'],'div'=>['class'=>'checkboxleftbig']]);
            }
          echo "</div>";
        echo "</div>";  
      echo "</div>";
    echo "</div>";
	echo "</fieldset>";

  echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
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
<script>
	$(document).ready(function(){
		$('select.fixedselection option:not(:selected)').attr('disabled', true);
	});
</script>