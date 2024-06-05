<div class="cashReceipts index">
<?php

	echo "<h2>".__('Recibos de Caja')."</h2>";
	
	echo $this->Form->create('Report');
	echo "<fieldset>";
    echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014, 'maxYear'=>date('Y')));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014, 'maxYear'=>date('Y')));
		echo $this->Form->input('Report.cash_receipt_type_id',array('options'=>$cashReceiptTypes,'label'=>__('Cash Receipt Type'),'default'=>'0','empty'=>array('0'=>'Seleccione Tipo de Recibo')));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
?>
</div>
<div class="actions">	
<?php		
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link('Nuevo Recibo de Caja (Factura)',['action' => 'crear',CASH_RECEIPT_TYPE_CREDIT])."</li>";
			echo "<br/>";
			echo "<li>".$this->Html->link(__('Nuevo Recibo de Caja (Otros Ingresos)'),['action' => 'crear',CASH_RECEIPT_TYPE_OTHER])."</li>";
			echo "<br/>";
		}
		if ($bool_client_index_permission) { 
			echo "<li>".$this->Html->link(__('List Clients'), ['controller' => 'third_parties', 'action' => 'resumenClientes'])."</li>";
		}
		if ($bool_client_add_permission) { 
			echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
		} 
	
	echo "</ul>";
?>
</div>
<div class="cashReceipts index fullwidth">
<?php 	
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('receipt_date')."</th>";
				echo "<th>".$this->Paginator->sort('receipt_code')."</th>";
				echo "<th>".$this->Paginator->sort('cash_receipt_type_id')."</th>";
				echo "<th>".$this->Paginator->sort('Client.company_name','Cliente')."</th>";
				echo "<th>".$this->Paginator->sort('concept')."</th>";
				echo "<th>".$this->Paginator->sort('amount','Monto')."</th>";
			
				//echo "<th>".$this->Paginator->sort('Order.order_code','Salida')."</th>";
				//echo "<th>".$this->Paginator->sort('cashbox_accounting_code_id')."</th>";
				//echo "<th>".$this->Paginator->sort('observation')."</th>";
				//echo "<th>".$this->Paginator->sort('bool_cash')."</th>";
				//echo "<th>".$this->Paginator->sort('cheque_number')."</th>";
				//echo "<th>".$this->Paginator->sort('cheque_bank')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		//pr($cashReceipts);
		$total_CS=0;
		$total_USD=0;
		foreach ($cashReceipts as $cashReceipt) {
			//pr($cashReceipt);
			if ($cashReceipt['CashReceipt']['bool_annulled']){
				echo "<tr class='italic'>";
			}
			else {
				echo "<tr>";
			}
				$receiptDateTime=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
				$receiptCode=$cashReceipt['CashReceipt']['receipt_code'];
				if ($cashReceipt['CashReceipt']['bool_annulled']){
					$receiptCode.=" (Anulado)";
				}
				echo "<td>".$receiptDateTime->format('d-m-Y')."</td>";
				if (!empty($cashReceipt['CashReceipt']['order_id'])){
					if ($cashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_REMISSION){
						echo "<td>".$this->Html->link($receiptCode,array('controller'=>'orders','action'=>'verRemision',$cashReceipt['CashReceipt']['order_id']))."</td>";
					}
				}
				else {
					echo "<td>".$this->Html->link($receiptCode,array('action'=>'detalle',$cashReceipt['CashReceipt']['id']))."</td>";
					
				}
				echo "<td>".$this->Html->link($cashReceipt['CashReceiptType']['name'], ['controller' => 'cash_receipt_types', 'action' => 'view', $cashReceipt['CashReceiptType']['id']])."</td>";
				if ($cashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_OTHER){
					echo "<td>".$cashReceipt['CashReceipt']['received_from']."</td>";
				}
				else {
					echo "<td>".$this->Html->link($cashReceipt['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $cashReceipt['Client']['id']))."</td>";
				}
				echo "<td>".h($cashReceipt['CashReceipt']['concept'])."</td>";
				echo "<td><span class='currency'>".$cashReceipt['Currency']['abbreviation']."</span><span class='amountright'>".number_format($cashReceipt['CashReceipt']['amount'],2,".",",")."</span></td>";
				if ($cashReceipt['Currency']['id']==CURRENCY_CS){
					$total_CS+=$cashReceipt['CashReceipt']['amount'];
				}
				elseif ($cashReceipt['Currency']['id']==CURRENCY_USD){
					$total_USD+=$cashReceipt['CashReceipt']['amount'];
				}
				
				echo "<td class='actions'>";
					$receiptCode=str_replace(' ','',$cashReceipt['CashReceipt']['receipt_code']);
					$receiptCode=str_replace('/','',$receiptCode);
					$fileName=$enterprises[$enterpriseId].'_Recibo_Caja_'.$receiptCode;
					if ($userRoleId == ROLE_ADMIN) { 
						if ($bool_edit_permission){
              echo $this->Html->link(__('Edit'), ['action' => 'editar', $cashReceipt['CashReceipt']['id']]);            
						}
					}
					echo $this->Html->link(__('Pdf'), ['action' => 'detallePdf','ext'=>'pdf',$cashReceipt['CashReceipt']['id'],$fileName],['target'=>'_blank']);
				echo "</td>";
			echo "</tr>";
		}
		$totalRow="";
		if ($total_CS>0){
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".number_format($total_CS,2,".",",")."</span></td>";
        $totalRow.="<td></td>";
			$totalRow.="</tr>";
		}
		if ($total_USD>0){
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td></td>";
				$totalRow.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".number_format($total_USD,2,".",",")."</span></td>";
			$totalRow.="</tr>";
		}
		echo $totalRow;
		echo "</tbody>";
	echo "</table>";
	
?>

</div>