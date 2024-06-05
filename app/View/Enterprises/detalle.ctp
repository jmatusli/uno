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

<div class="enterprises view">
<h2><?php echo __('Enterprise')." ".$enterprise['Enterprise']['company_name'].($enterprise['Enterprise']['bool_active']?"":" (Inactivo)"); ?></h2>
	<dl>
		<dt><?php echo __('Company Name'); ?></dt>
		<dd>
			<?php echo h($enterprise['Enterprise']['company_name']); ?>
			&nbsp;
		</dd>
	<?php
    /*
		echo "<dt>".__('Accounting Code')."</dt>";
		if (!empty($enterprise['AccountingCode']['code'])){	
			echo "<dd>".$this->Html->Link($enterprise['AccountingCode']['code']." ".$enterprise['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$enterprise['AccountingCode']['id']))."</dd>";
		}
		else {	
			echo "<dd>-</dd>";
		}
    */
		if (!empty($enterprise['Enterprise']['first_name'])){	
			echo "<dt>".__('First Name')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['first_name']."</dd>";
		}
		if (!empty($enterprise['Enterprise']['last_name'])){	
			echo "<dt>".__('Last Name')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['last_name']."</dd>";
		}
		if (!empty($enterprise['Enterprise']['email'])){	
			echo "<dt>".__('Email')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['email']."</dd>";
		}
		if (!empty($enterprise['Enterprise']['phone'])){	
			echo "<dt>".__('Phone')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['phone']."</dd>";
		}
    if (!empty($enterprise['Enterprise']['address'])){	
			echo "<dt>".__('Address')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['address']."</dd>";
		}
    if (!empty($enterprise['Enterprise']['ruc_number'])){	
			echo "<dt>".__('Ruc Number')."</dt>";
			echo "<dd>".$enterprise['Enterprise']['ruc_number']."</dd>";
		}
	
	echo "</dl>";
	/*
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	
  echo $this->Form->end(__('Refresh')); 
  */
?>
	
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission) {
			echo "<li>".$this->Html->link(__('Edit Enterprise'), ['action' => 'editar', $enterprise['Enterprise']['id']])."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission) {
      //echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'eliminar', $enterprise['Enterprise']['id']), array(), __('Are you sure you want to delete # %s?', $enterprise['Enterprise']['company_name']))."</li>";
			//echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Enterprises'), ['action' => 'resumen'])."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Enterprise'), ['action' => 'crear'])."</li>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php
  /*
  //pr($enterprise['Order']);
  if (!empty($enterprise['Order'])){
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
    foreach ($enterprise['Order'] as $sale){
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
  */
?>
</div>
<div class="related">
<?php 
  //pr($enterprise);
	if(!empty($enterprise['EnterpriseUser'])){
		echo "<h3>".__('Usuarios asociados con esta Empresa')."</h3>";
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
        //pr($enterpriseUser);
        $tableBody.=($user['EnterpriseUser'][0]['bool_assigned']?"<tr>":"<tr class='italic'>");
          $tableBody.="<td>".$user['User']['username']."</td>";
          $tableBody.="<td>".$user['User']['first_name']."</td>";
          $tableBody.="<td>".$user['User']['last_name']."</td>";
          $tableBody.="<td>".$user['User']['email']."</td>";
          $tableBody.="<td>".$user['User']['phone']."</td>";
          $tableBody.="<td>";
          foreach ($user['EnterpriseUser'] as $enterpriseUser){
            //pr($enterpriseUser);
            $assignmentDateTime=new DateTime($enterpriseUser['assignment_datetime']);
            $tableBody.=($enterpriseUser['bool_assigned']?"Asignado":"Desasignado")." el ".($assignmentDateTime->format('d-m-Y H:i:s'))."<br>";
          }  
          $tableBody.="</td>";
          $tableBody.="<td class='actions'>";
            $tableBody.=$this->Html->link(__('View'), ['controller' => 'users', 'action' => 'view', $user['User']['id']]);
            $tableBody.=($bool_user_edit_permission?$this->Html->link(__('Edit'), ['controller' => 'users', 'action' => 'edit', $user['User']['id']]):"");
          $tableBody.="</td>";
        $tableBody.="</tr>";
      }
      $tableBody.="</tbody>";
      echo $tableBody;
		echo "</table>";
	}
?>
</div>
