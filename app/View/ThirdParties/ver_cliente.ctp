<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CSCurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDCurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>

<div class="thirdParties view clients">
<h2><?php echo __('Client')." ".$client['ThirdParty']['company_name'].($client['ThirdParty']['bool_active']?"":" (Inactivo)"); ?></h2>
	<dl>
		<dt><?php echo __('Company Name'); ?></dt>
		<dd>
			<?php echo h($client['ThirdParty']['company_name']); ?>
			&nbsp;
		</dd>
	<?php 
    echo "<dt>".__('Empresas Asociados')."</dt>";
    echo "<dd>";
    if (empty($linkedEnterprises)){
      echo "&nbsp;";
    }
    else {
      $enterpriseCounter=0;
      foreach ($linkedEnterprises as $enterpriseId => $enterpriseName){
        $enterpriseCounter++;
        if($userrole == ROLE_ADMIN){
          echo $this->Html->link($enterpriseName,['controller'=>'enterprises','action' => 'detalle', $enterpriseId]);          
        }
        else {
          echo $enterpriseName;
        }
        if ($enterpriseCounter<count($client['ClientEnterprise'])){
          echo "<br/>";
        }
      }
    }
    echo "</dd>";  
		echo "<dt>".__('Accounting Code')."</dt>";
		if (!empty($client['AccountingCode']['code'])){	
			echo "<dd>".$this->Html->Link($client['AccountingCode']['code']." ".$client['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$client['AccountingCode']['id']))."</dd>";
		}
		else {	
			echo "<dd>-</dd>";
		}
		echo "<dt>".__('Credit Days')."</dt>";
		if (!empty($client['ThirdParty']['credit_days'])){	
			echo "<dd>".$client['ThirdParty']['credit_days']."</dd>";
		}
		else {	
			echo "<dd>0</dd>";
		}
    echo "<dt>".__('Credit Amount')."</dt>";
		if (!empty($client['ThirdParty']['credit_amount'])){	
			echo "<dd>".$client['CreditCurrency']['abbreviation']." ".number_format($client['ThirdParty']['credit_amount'],2,'.',',')."</dd>";
		}
		else {	
			echo "<dd>0</dd>";
		}
    echo "<dt>Pago Pendiente</dt>";
		if (!empty($client['ThirdParty']['pending_payment'])){	
			echo "<dd>C$ ".number_format($client['ThirdParty']['pending_payment'],2,'.',',')."</dd>";
		}
		else {	
			echo "<dd>C$ 0.00</dd>";
		}
		if (!empty($client['ThirdParty']['first_name'])){	
			echo "<dt>".__('First Name')."</dt>";
			echo "<dd>".$client['ThirdParty']['first_name']."</dd>";
		}
		if (!empty($client['ThirdParty']['last_name'])){	
			echo "<dt>".__('Last Name')."</dt>";
			echo "<dd>".$client['ThirdParty']['last_name']."</dd>";
		}
		if (!empty($client['ThirdParty']['email'])){	
			echo "<dt>".__('Email')."</dt>";
			echo "<dd>".$client['ThirdParty']['email']."</dd>";
		}
		if (!empty($client['ThirdParty']['phone'])){	
			echo "<dt>".__('Phone')."</dt>";
			echo "<dd>".$client['ThirdParty']['phone']."</dd>";
		}
    if (!empty($client['ThirdParty']['address'])){	
			echo "<dt>".__('Address')."</dt>";
			echo "<dd>".$client['ThirdParty']['address']."</dd>";
		}
    if (!empty($client['ThirdParty']['ruc_number'])){	
			echo "<dt>".__('Ruc Number')."</dt>";
			echo "<dd>".$client['ThirdParty']['ruc_number']."</dd>";
		}
	?>	
	</dl>
	
	<?php echo $this->Form->create('Report'); ?>
	<fieldset>
	<?php
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	?>
	</fieldset>
	<button id='previousmonth' class='monthswitcher'><?php echo __('Previous Month'); ?></button>
	<button id='nextmonth' class='monthswitcher'><?php echo __('Next Month'); ?></button>
	<?php echo $this->Form->end(__('Refresh')); ?>
	
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission) {
			echo "<li>".$this->Html->link(__('Edit Client'), array('action' => 'editarCliente', $client['ThirdParty']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $client['ThirdParty']['id']), array(), __('Are you sure you want to delete # %s?', $client['ThirdParty']['id']))."</li>";
			//echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Clients'), array('action' => 'resumenClientes'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Client'), array('action' => 'crearCliente'))."</li>";
		}
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
<div class="related">
<?php 
  //pr($client['Order']);
  if (!empty($client['Order'])){
    echo "<h3>".__('Related Sales to Client')."</h3>";
    $tableHead="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Exit Date')."</th>";
        $tableHead.="<th>".__('Order Code')."</th>";
        $tableHead.="<th class='centered'>".__('Total Price')."</th>";
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $totalPrice=0;
    $pageRows="";
    foreach ($client['Order'] as $sale){
      $orderDateTime=new DateTime($sale['order_date']);
      $totalPrice+=$sale['total_price'];
      $pageRow="<tr>";
        $pageRow.="<td>".$orderDateTime->format('d-m-Y')."</td>";
        $pageRow.="<td>".$this->Html->Link($sale['order_code'],['controller'=>'orders','action'=>'verVenta',$sale['id']])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$sale['total_price']."</span></td>";
      $pageRow.="</tr>";
      $pageRows.=$pageRow;  
    }
    
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalPrice."</span></td>";
      $totalRow.="<td></td>";
    $totalRow.="</tr>";
    $tableBody="<tbody>".$totalRow.$pageRows.$totalRow."</tbody>";
    echo "<table>".$tableHead.$tableBody."</table>";
  }
?>

</div>


<div class="related">
<?php 
	if(!empty($client['ClientUser'])){
		echo "<h3>".__('Vendedores asociados con este Cliente')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
      $tableHeader="";
      $tableHeader.="<thead>";
        $tableHeader.="<tr>";
          $tableHeader.="<th>".__('Username')."</th>";
          $tableHeader.="<th>".__('First Name')."</th>";
          $tableHeader.="<th>".__('Last Name')."</th>";
          $tableHeader.="<th>".__('Email')."</th>";
          $tableHeader.="<th>".__('Phone')."</th>";
          $tableHeader.="<th style='width:15%;'>Historial de Asignaciones</th>";
          $tableHeader.="<th class='actions'>".__('Actions')."</th>";
        $tableHeader.="</tr>";
      $tableHeader.="</thead>";
      echo $tableHeader;
      $tableBody="";
      $tableBody.="<tbody>";
      foreach ($uniqueUsers as $user){
        //pr($clientUser);
        $tableBody.=($user['ClientUser'][0]['bool_assigned']?"<tr>":"<tr class='italic'>");
          $tableBody.="<td>".$user['User']['username']."</td>";
          $tableBody.="<td>".$user['User']['first_name']."</td>";
          $tableBody.="<td>".$user['User']['last_name']."</td>";
          $tableBody.="<td>".$user['User']['email']."</td>";
          $tableBody.="<td>".$user['User']['phone']."</td>";
          $tableBody.="<td>";
          foreach ($user['ClientUser'] as $clientUser){
            //pr($clientUser);
            $assignmentDateTime=new DateTime($clientUser['assignment_datetime']);
            $tableBody.=($clientUser['bool_assigned']?"Asignado":"Desasignado")." el ".($assignmentDateTime->format('d-m-Y H:i:s'))."<br>";
          }  
          $tableBody.="</td>";
          $tableBody.="<td class='actions'>";
            $tableBody.=$this->Html->link(__('View'), array('controller' => 'users', 'action' => 'view', $user['User']['id']));
            $tableBody.=($bool_user_edit_permission?$this->Html->link(__('Edit'), array('controller' => 'users', 'action' => 'edit', $user['User']['id'])):"");
          $tableBody.="</td>";
        $tableBody.="</tr>";
      }
      $tableBody.="</tbody>";
      echo $tableBody;
		echo "</table>";
	}
?>
</div>
