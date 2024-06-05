<script>
	function formatNumbers(){
		$("td.number span.amountcenter").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			else {
				var percentageValue=parseFloat($(this).text());
				$(this).text(100*percentageValue);
			}
			$(this).number(true,2,'.',',');
			$(this).append(" %");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      $(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>

<div class="invoices resumen fullwidth">
<?php 	
  $excelOutput='';
  echo '<h2>'.__('Invoices').'</h2>';
  
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
          
          echo $this->Form->input('Report.payment_mode_id',['label'=>'Filtar x Modo Pago','default'=>$paymentModeId,'empty'=>[0=>'-- Todos Modos de Pago --']]);
          echo $this->Form->input('Report.shift_id',['label'=>'Filtrar x Turno','default'=>$shiftId,'empty'=>[0=>'-- Todos Turnos --']]);
          echo $this->Form->input('Report.operator_id',['label'=>'Filtrar x Operador','default'=>$operatorId,'empty'=>[0=>'-- Todos Operadores --']]);
          
          //echo $this->Form->input('Report.display_option_id',['label'=>'Mostrar','default'=>$displayOptionId]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=date('d_m_Y')."_".$enterprises[$enterpriseId]."_Resumen_Facturas.xlsx";
	
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenFacturas',$fileName], ['class' => 'btn btn-primary']);
        }
	
        
      echo '</div>';
      echo '<div class="col-sm-6">';
        echo '<h3>'. __('Actions').'</h3>';
        echo '<ul style="list-style:none;">';
          if ($bool_add_permission) { 
            echo '<li>'. $this->Html->link(__('New Invoice'), ['action' => 'crear']).'</li>';
            echo '<br/>';
          }
          echo '<li>'. $this->Html->link(__('List Clients'), ['controller' => 'third_parties', 'action' => 'resumenClientes']).' </li>';
          echo '<li>'. $this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente']).' </li>';          
        echo '</ul>';
      echo '</div>';
    echo '</div>';
    echo '<div class="rows" style="clear:left;">';  
    if ($enterpriseId == 0){
      echo '<h3>Seleccione una gasolinera para ver datos</h3>';
    }
    else {      
      echo '<div class="col-sm-12">';
        $invoiceTableHeadRow='';        
        
        $invoiceTableHeadRow.='<th>'. $this->Paginator->sort('invoice_date').'</th>';
        $invoiceTableHeadRow.='<th>'. $this->Paginator->sort('invoice_code').'</th>';
        $invoiceTableHeadRow.='<th>Cliente</th>';
        $invoiceTableHeadRow.='<th>'.__('Payment Mode').'</th>';
        $invoiceTableHeadRow.='<th>'.__('Shift').'</th>';
        $invoiceTableHeadRow.='<th>'.__('Operator').'</th>';
        $invoiceTableHeadRow.='<th class="centered">'. $this->Paginator->sort('sub_total_price').'</th>';
        $excelInvoiceTableHeadRow=$invoiceTableHeadRow;
        $invoiceTableHeadRow.='<th class="actions">'. __('Actions').'</th>';
        
        $invoiceTableHead='<thead><tr>'.$invoiceTableHeadRow.'</tr></thead>';
        $excelInvoiceTableHead='<thead><tr>'.$excelInvoiceTableHeadRow.'</tr></thead>';
        
        $excelInvoiceTableRows=$invoiceTableRows='';
        $grandSubtotal=0;
        foreach ($invoices as $invoice){
          $excelRow=$invoiceRow='';
          $grandSubtotal+=$invoice['Invoice']['sub_total_price'];
          $invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);
          
          $invoiceRow.='<tr '.($invoice['Invoice']['bool_annulled']?'class="italic"':'').'>';
            $invoiceRow.='<td>'.$this->Html->link($invoice['Invoice']['invoice_code'].($invoice['Invoice']['bool_annulled']?" (Anulada)":""), ['action' => 'detalle', $invoice['Invoice']['id']]).'</td>';
            $invoiceRow.='<td>'.$invoiceDateTime->format('d-m-Y').'&nbsp;</td>';
            $invoiceRow.='<td>'. $this->Html->link($invoice['Client']['company_name'], ['controller' => 'thirdParties', 'action' => 'verCliente', $invoice['Client']['id']]).'</td>';
            $invoiceRow.='<td>'.($boolPaymentModeDetailPermission?$this->Html->link($invoice['PaymentMode']['name'], ['controller' => 'paymentModes', 'action' => 'detalle', $invoice['PaymentMode']['id']]):$invoice['PaymentMode']['name']).'</td>';
            $invoiceRow.='<td>'.($boolShiftDetailPermission?$this->Html->link($invoice['Shift']['name'], ['controller' => 'shifts', 'action' => 'detalle', $invoice['Shift']['id']]):$invoice['Shift']['name']).'</td>';
            $invoiceRow.='<td>'.($boolOperatorDetailPermission?$this->Html->link($invoice['Operator']['name'], ['controller' => 'operators', 'action' => 'detalle', $invoice['Operator']['id']]):$invoice['Operator']['name']).'</td>';
            $invoiceRow.='<td class="centered amount CScurrency"><span class="currency">C$</span><span class="amountright">'. ($invoice['Invoice']['sub_total_price']).'</span></td>';
            
            $excelRow=$invoiceRow;
          $excelRow.='</tr>';            
          
            $invoiceRow.='<td class="actions">';
              if ($bool_edit_permission){
                $invoiceRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $invoice['Invoice']['id']]);
              }
              if ($bool_delete_permission){
                //$invoiceRow.=$this->Form->postLink(__('Delete'), ['action' => 'delete', $invoice['Invoice']['id']), [], __('Está seguro que quiere eliminar factura # %s?', $invoice['Invoice']['invoice_code']));
              }
            $invoiceRow.='</td>';
          $invoiceRow.='</tr>';
          $invoiceTableRows.=$invoiceRow; 
          
          $excelInvoiceTableRows.=$excelRow;  
        }
  
        $excelInvoiceTotalRow=$invoiceTotalRow='';
        
        $invoiceTotalRow.='<tr class="totalrow">';
          $invoiceTotalRow.='<td>';
          $invoiceTotalRow.='<td>Total C$</td>';
          $invoiceTotalRow.='<td></td>';
          $invoiceTotalRow.='<td></td>';
          $invoiceTotalRow.='<td></td>';
          $invoiceTotalRow.='<td></td>';
          $invoiceTotalRow.='<td class="centered amount CScurrency"><span class="currency">C$</span><span class="amountright">'.$grandSubtotal.'</span></td>';
          $invoiceTotalRow.='<td></td>';  
          $excelInvoiceTotalRow=$invoiceTotalRow;
        $excelInvoiceTotalRow.='</tr>';
          
          $invoiceTotalRow.='<td></td>';
        $invoiceTotalRow.='</tr>';
  
        $invoiceTableBody='<tbody>'.$invoiceTotalRow.$invoiceTableRows.$invoiceTotalRow.'</tbody>';
        $excelInvoiceTableBody='<tbody>'.$excelInvoiceTotalRow.$excelInvoiceTableRows.$excelInvoiceTotalRow.'</tbody>';
        
        $invoiceTable='<table id="facturas" cellpadding="0" cellspacing="0">'.$invoiceTableHead.$invoiceTableBody.'</table>';
        echo $invoiceTable;
        $excelInvoiceTable='<table id="facturas" cellpadding="0" cellspacing="0">'.$excelInvoiceTableHead.$excelInvoiceTableBody.'</table>';
        $excelOutput.=$excelInvoiceTable;
      echo '</div>';
    }
    echo '</div>';
  echo '</div>';
  $_SESSION['resumenFacturas'] = $excelOutput;
  
?>  
</div>
