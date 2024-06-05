<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
  function formatCSCurrencies(){
		$("td.CSCurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);-
      $(this).parent().find('span.currency').text('C$ ');
		});
		
	}
  function formatUSDCurrencies(){
		$("td.USDCurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
      $(this).parent().find('span.currency').text('US$ ');
		});
		
	}
	
	$(document).ready(function(){
    formatNumbers();
		formatCSCurrencies();
    formatUSDCurrencies();
  });
</script>
<div class="thirdParties index clients fullwidth">
<?php 
  echo "<h2>".__('Clients')."</h2>";
 
  echo "<div class='container_fluid'>";
    echo "<div class='rows'>";
      echo "<div class='col-md-5'>";
        echo $this->Form->create('Report',['style'=>'width:100%']); 
        echo "<fieldset>"; 
          if ($userRoleId == ROLE_ADMIN || $userRoleId == ROLE_ASSISTANT) { 
            echo $this->Form->input('Report.user_id',array('label'=>__('Mostrar Cliente asociado con Usuario'),'options'=>$users,'default'=>$userId,'empty'=>array('0'=>__('Todos Usuarios'))));
          }
          else {
            echo $this->Form->input('Report.user_id',array('label'=>__('Mostrar Cliente asociado con Usuario'),'options'=>$users,'default'=>$userId,'type'=>'hidden'));
          }										
          //if ($userRoleId==ROLE_ADMIN || $userRoleId==ROLE_ASSISTANT) { 
          //  echo $this->Form->input('Report.enterprise_id',array('label'=>__('Mostrar Cliente asociado con Empresa'),'default'=>$enterpriseId,'empty'=>array('0'=>__('Todas Empresas'))));
          //}
          //else {
            echo $this->Form->input('Report.enterprise_id',array('label'=>__('Mostrar Cliente asociado con Empresa'),'default'=>$enterpriseId,'type'=>'hidden'));
          //}	          
          echo $this->Form->input('Report.active_display_option_id',array('label'=>__('Clientes Activos'),'default'=>$activeDisplayOptionId));
          if ($userRoleId == ROLE_ADMIN || $userRoleId == ROLE_ASSISTANT) { 
            echo $this->Form->input('Report.aggregate_option_id',array('label'=>__('Mostrar y Ordenar Por'),'default'=>$aggregateOptionId));
          }
          else {
            echo $this->Form->input('Report.aggregate_option_id',array('label'=>__('Mostrar y Ordenar Por'),'default'=>AGGREGATES_NONE,'type'=>'hidden'));
          }
          echo $this->Form->input('Report.searchterm',array('label'=>__('Buscar')));
      echo "</div>";
      echo "<div class='col-md-5'>";
        if ($userRoleId == ROLE_ADMIN || $userRoleId == ROLE_ASSISTANT) {   
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2015,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2015,'maxYear'=>date('Y')));
          //echo $this->Form->input('Report.currency_id',array('label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId));
        }
        echo  "</fieldset>";
        echo "<br/>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
          if ($bool_add_permission) {
            echo "<li>".$this->Html->link(__('New Client'), ['action' => 'crearCliente'])."</li>";
            echo "<br/>";
          }
          if ($bool_edit_permission) {
            echo "<li>".$this->Html->link(__('Asociar Clientes y Empresas'), ['action' => 'asociarClientesEmpresas'])."</li>";
            echo "<br/>";
          }
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
      echo "</div>";
    echo "</div>";
  echo "</div>";
    
	echo "<br>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenClientes'), array( 'class' => 'btn btn-primary'));  

  
	$pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
    $pageHeader.="<tr>";
      $pageHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('enterprise_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $pageHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      $pageHeader.="<th class='centered'>Pago Pendiente</th>";
      $pageHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      if ($userRoleId == ROLE_ADMIN || $userRoleId == ROLE_ASSISTANT) { 
        $pageHeader.="<th># Salidas</th>";
        $pageHeader.="<th>$ Salidas</th>";
			}
      $pageHeader.="<th class='actions'>".__('Actions')."</th>";
    $pageHeader.="</tr>";
  $pageHeader.="</thead>";
  $excelHeader.="<thead>";
    $excelHeader.="<tr>";
      $excelHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('enterprise_id')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $excelHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      $excelHeader.="<th class='centered'>Pago Pendiente</th>";
      $excelHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      if ($userRoleId==ROLE_ADMIN||$userRoleId==ROLE_ASSISTANT) { 
        $excelHeader.="<th># Salidas</th>";
        $excelHeader.="<th>$ Salidas</th>";
			}
    $excelHeader.="</tr>";
  $excelHeader.="</thead>";
	$pageBody="";
	$excelBody="";

  $totalPaymentPending=0;
  $totalQuantityOrders=0;
  $totalAmountOrders=0;
  
	foreach ($clients as $client){
    //pr($client);
    $totalPaymentPending+=$client['ThirdParty']['pending_payment'];
    $totalQuantityOrders+=count($client['Order']);
    $totalAmountOrders+=$client['Client']['order_total'];
  
    $pageRow="";
    $pageRow.="<tr class='".($client['ThirdParty']['bool_active']?"":" italic").($client['ThirdParty']['credit_amount']>=$client['ThirdParty']['pending_payment']?"":" redbg")."'>"; 
      $pageRow.="<td>".$this->Html->link($client['ThirdParty']['company_name'].($client['ThirdParty']['bool_active']?"":" (Inactivo)"), ['action' => 'verCliente', $client['ThirdParty']['id']])."</td>";
      
      $pageRow.="<td>";
      $enterpriseCounter=0;
      //pr($client);
      foreach ($linkedEnterprises as $enterpriseId => $enterpriseName){
        $enterpriseCounter++;
        if($userRoleId == ROLE_ADMIN){
          $pageRow.=$this->Html->link($enterpriseName,['controller'=>'enterprises','action' => 'detalle', $enterpriseId]);          
        }
        else {
          $pageRow.=$enterpriseName;
        }
        if ($enterpriseCounter<count($client['ClientEnterprise'])){
          $pageRow.="<br/>";
        }
      }
      $pageRow.="</td>";
      if (!empty($client['AccountingCode']['code'])){
        $pageRow.="<td>".$this->Html->link($client['AccountingCode']['code']." ".$client['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$client['AccountingCode']['id']))."</td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if (!empty($client['ThirdParty']['credit_days'])){
        $pageRow.="<td class='centered'>".$client['ThirdParty']['credit_days']."</td>";
      }
      else {
        $pageRow.="<td class='centered'>0</td>";
      }
      if (!empty($client['ThirdParty']['credit_amount'])){
        $pageRow.="<td class='centered ".($client['ThirdParty']['credit_currency_id']==CURRENCY_USD?'USDCurrency':'CSCurrency')."' ><span class='currency'></span><span class='amountright'>".$client['ThirdParty']['credit_amount']."</span></td>";
      }
      else {
        $pageRow.="<td class='centered'>-</td>";
      }
      $pageRow.="<td class='centered CSCurrency'><span class='currency'></span><span class='amountright'>".$client['ThirdParty']['pending_payment']."</span></td>";
      
      $pageRow.="<td>".$client['ThirdParty']['first_name']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['last_name']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['email']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['phone']."</td>";
      if (!empty($client['ThirdParty']['address'])){
        $pageRow.="<td >".$client['ThirdParty']['address']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if (!empty($client['ThirdParty']['ruc_number'])){
        $pageRow.="<td >".$client['ThirdParty']['ruc_number']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if ($userRoleId==ROLE_ADMIN||$userRoleId==ROLE_ASSISTANT) { 
        $pageRow.="<td>".count($client['Order'])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$client['Client']['order_total']."</span></td>";
			}
      $excelBody.=($client['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
			
      $pageRow.="<td class='actions'>";
        if ($bool_edit_permission){ 
          $pageRow.=$this->Html->link(__('Editar Cliente'), ['action' => 'editarCliente', $client['ThirdParty']['id']]); 
        } 
        if ($bool_delete_permission){ 
          //$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'deleteClient', $client['ThirdParty']['id']), array(), __('Está seguro que quiere eliminar el cliente # %s?', $client['ThirdParty']['company_name'])); 
        }
      $pageRow.="</td>";
    $pageBody.=($client['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
  }
  $totalRow="<tr class='totalrow'>";
    $totalRow.="<td>Total</td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalPaymentPending."</span></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    if ($userRoleId==ROLE_ADMIN||$userRoleId==ROLE_ASSISTANT) { 
      $totalRow.="<td>".$totalQuantityOrders."</td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalAmountOrders."</span></td>";
    }
    $excelBody="<tbody>".$totalRow."</tr>".$excelBody.$totalRow."</tr>"."</tbody>";
    
    $totalRow.="<td></td>";
  $totalRow.="<tr></tr>";
      
	$pageBody="<tbody>".$totalRow.$pageBody.$totalRow."</tbody>";
  
  $table_id="Clientes";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
  
  $excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumenClientes'] = $excelOutput;
  
?>	
</div>