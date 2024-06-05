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
<div class="thirdParties index enterprises fullwidth">
<?php 
  echo "<h2>".__('Enterprises')."</h2>";
 
  echo "<div class='container_fluid'>";
    echo "<div class='rows'>";
      echo "<div class='col-md-10'>";
        echo $this->Form->create('Report',['style'=>'width:100%']); 
        echo "<fieldset>"; 
          if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
            echo $this->Form->input('Report.user_id',array('label'=>__('Mostrar empresa asociado con Usuario'),'options'=>$users,'default'=>$userId,'empty'=>array('0'=>__('Todos Usuarios'))));
          }
          else {
            echo $this->Form->input('Report.user_id',array('label'=>__('Mostrar empresa asociado con Usuario'),'options'=>$users,'default'=>$userId,'type'=>'hidden'));
          }												
          echo $this->Form->input('Report.active_display_option_id',array('label'=>__('Empresas Activas'),'default'=>$activeDisplayOptionId));
          //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
          //  echo $this->Form->input('Report.aggregate_option_id',array('label'=>__('Mostrar y ordenar por'),'default'=>$aggregateOptionId));
          //}
          //else {
          //  echo $this->Form->input('Report.aggregate_option_id',array('label'=>__('Mostrar y ordenar por'),'default'=>AGGREGATES_NONE,'type'=>'hidden'));
          //}
          echo $this->Form->input('Report.searchterm',array('label'=>__('Buscar')));
      //echo "</div>";
      //echo "<div class='col-md-5'>";
        //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) {   
          //echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2015,'maxYear'=>date('Y')));
          //echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2015,'maxYear'=>date('Y')));
          //echo $this->Form->input('Report.currency_id',array('label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId));
        //}
        echo  "</fieldset>";
        echo "<br/>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
          if ($bool_add_permission) {
            echo "<li>".$this->Html->link(__('New Enterprise'), array('action' => 'crear'))."</li>";
            echo "<br/>";
          }
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
    
	echo "<br>";
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']);  

  
	$pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
    $pageHeader.="<tr>";
      $pageHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      //$pageHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
      //  $pageHeader.="<th># Salidas</th>";
      //  $pageHeader.="<th>$ Salidas</th>";
			//}
      $pageHeader.="<th class='actions'>".__('Actions')."</th>";
    $pageHeader.="</tr>";
  $pageHeader.="</thead>";
  $excelHeader.="<thead>";
    $excelHeader.="<tr>";
      $excelHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
      //  $excelHeader.="<th># Salidas</th>";
      //  $excelHeader.="<th>$ Salidas</th>";
			//}
    $excelHeader.="</tr>";
  $excelHeader.="</thead>";
	$pageBody="";
	$excelBody="";
  
	foreach ($enterprises as $enterprise){
    //pr($enterprise);
    
    $pageRow="";
    $pageRow.="<tr class='".($enterprise['Enterprise']['bool_active']?"":" italic")."'>"; 
      $pageRow.="<td>".$this->Html->link($enterprise['Enterprise']['company_name'].($enterprise['Enterprise']['bool_active']?"":" (Inactivo)"), ['action' => 'detalle', $enterprise['Enterprise']['id']])."</td>";
      $pageRow.="<td>".$enterprise['Enterprise']['first_name']."</td>";
      $pageRow.="<td>".$enterprise['Enterprise']['last_name']."</td>";
      $pageRow.="<td>".$enterprise['Enterprise']['email']."</td>";
      $pageRow.="<td>".$enterprise['Enterprise']['phone']."</td>";
      if (!empty($enterprise['Enterprise']['address'])){
        $pageRow.="<td >".$enterprise['Enterprise']['address']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if (!empty($enterprise['Enterprise']['ruc_number'])){
        $pageRow.="<td >".$enterprise['Enterprise']['ruc_number']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
      //  $pageRow.="<td>".count($enterprise['Order'])."</td>";
      //  $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$enterprise['Client']['order_total']."</span></td>";
			//}
      $excelBody.=($enterprise['Enterprise']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
			
      $pageRow.="<td class='actions'>";
        if ($bool_edit_permission){ 
          $pageRow.=$this->Html->link(__('Editar Cliente'), ['action' => 'editar', $enterprise['Enterprise']['id']]); 
        } 
        if ($bool_delete_permission){ 
          //$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'eliminar', $enterprise['Enterprise']['id']), array(), __('Está seguro que quiere eliminar la empresa %s?', $enterprise['Enterprise']['company_name'])); 
        }
      $pageRow.="</td>";
    $pageBody.=($enterprise['Enterprise']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
  }
  $totalRow="<tr class='totalrow'>";
    $totalRow.="<td>Total</td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    //if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
    //  $totalRow.="<td>".$totalQuantityOrders."</td>";
    //  $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalAmountOrders."</span></td>";
    //}
    $excelBody="<tbody>".$totalRow."</tr>".$excelBody.$totalRow."</tr>"."</tbody>";
    
    $totalRow.="<td></td>";
  $totalRow.="<tr></tr>";
      
	$pageBody="<tbody>".$totalRow.$pageBody.$totalRow."</tbody>";
  
  $table_id="Clientes";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
  
  $excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumenEmpresas'] = $excelOutput;
  
?>	
</div>