<script>
	function formatCurrencies(){
		$("td.number span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				var boolnegative=true;
				//$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("-");
			}
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("C$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("C$");
			}
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("US$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("US$");
			}
		});
	};
  
  $('body').on('change','#ReportSundayId',function(){
     disableSaturdayBeforeSelectedSunday();
  });
  
  function disableSaturdayBeforeSelectedSunday(){
    var selectedSundayId=$('#ReportSundayId').val();
    $('#ReportSaturdayId option').each(function(){
      $(this).removeAttr('disabled');
      if ($(this).val() > selectedSundayId){
        $(this).attr('disabled', true);
      }
    });
  }
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
    
    disableSaturdayBeforeSelectedSunday();
	});
</script>

<div class="invoices index fullwidth">
<?php 
  $excelOutput='';
  $sundayDateTime=new DateTime($sundays[$sundayId]);
  $saturdayDateTime=new DateTime($saturdays[$sundayId]);
  
  echo '<h1 class="centered">Estación de servicios '.$enterprises[$enterpriseId].'</h1>';
  echo '<h1 class="centered">Estado de Cuentas </h1>';
  echo '<h1 class="centered">Período del '.($sundayDateTime->format('d/m/Y')).' al '.($saturdayDateTime->format('d/m/Y')).'</h1>';
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-8">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          
          echo $this->Form->input('Report.sunday_id',['default'=>$sundayId]);
					//echo $this->Form->input('Report.saturday_id',['default'=>$saturdayId]);
					//echo $this->Form->input('Report.payment_mode_id',['label'=>'Filtar x Modo Pago','default'=>$paymentModeId,'empty'=>[0=>'-- Todos Modos de Pago --']]);
          //echo $this->Form->input('Report.invoice_display_option_id',['label'=>'Facturas','default'=>$invoiceDisplayOptionId]);
          //echo $this->Form->input('Report.amount_display_option_id',['label'=>'Montos','default'=>$amountDisplayOptionId]);
				echo "</fieldset>";
				//echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				//echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=$enterprises[$enterpriseId].'_'.date('d_m_Y').'_Estado_Cuentas_'.$clients[$clientId].'.xlsx';
          //echo "fileName is ".$fileName."<br/>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarEstadoCuentasCliente',$fileName], ['class' => 'btn btn-primary','style'=>'margin-right:10px;']);
          
          $fileName=$enterprises[$enterpriseId].'_'.date('d_m_Y').'_Estado_Cuentas_'.$clients[$clientId];
          echo $this->Html->link('Pdf factura',['action'=>'estadoCuentasClientePdf','ext'=>'pdf',$clientId,$sundayId,$enterpriseId,$fileName],['class'=>'btn btn-primary','target'=>'_blank']);
        }
	
        
      echo '</div>';
      
    echo '</div>';
    echo '<div class="rows" style="clear:left;">';  
    echo "<br/>";
    if ($enterpriseId == 0){
      echo '<h2>Seleccione una gasolinera para ver datos</h2>';
    }
    else {      
      echo '<div class="col-sm-12">';      
      if (empty($invoices)){
        echo '<h2>No hay facturas para esta semana</h2>';
      }
      else {
        echo '<h2>CLIENTE: '.$clients[$clientId].'</h2>';
      
        $invoiceTableHeader='';
        $invoiceTableHeader.='<thead>';
          $invoiceTableHeader.='<tr>';
            $invoiceTableHeader.='<th>#</th>';  
            $invoiceTableHeader.='<th>Fecha</th>';  
            $invoiceTableHeader.='<th>Factura</th>';  
            $invoiceTableHeader.='<th class="centered">Concepto</th>';  
            $invoiceTableHeader.='<th class="centered">Monto</th>';  
            $invoiceTableHeader.='<th class="centered">Saldo</th>';  
          $invoiceTableHeader.='</tr>';
        $invoiceTableHeader.='</thead>';
        
        $invoiceTableBodyRows='';
        $invoiceCounter=1;
        foreach ($invoices as $invoice){
          $invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);
        
          $invoiceTableBodyRows.='<tr>';
            $invoiceTableBodyRows.='<td>'.$invoiceCounter.'</td>';
            $invoiceTableBodyRows.='<td>'.$invoiceDateTime->format('d/m/Y').'</td>';  
            $invoiceTableBodyRows.='<td>'.$this->Html->link($invoice['Invoice']['invoice_code'],['action'=>'detalle',$invoice['Invoice']['id']]).'</td>';  
            $invoiceTableBodyRows.='<td class="centered">CONSUMO de COMBISTIBLE</td>';  
            $invoiceTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$invoice['Invoice']['amount_cs'].'</span></td>';  
            $invoiceTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$invoice['Invoice']['saldo_cs'].'</span></td>';  
          $invoiceTableBodyRows.='</tr>';
          $invoiceCounter++;
        }
           
        $invoiceExcelBody='<tbody>'.$invoiceTableBodyRows.'</tbody>';
        
        $invoiceTableTotalRow='';
        
        $invoiceTableTotalRow.='<tr class="totalrow">';
          $invoiceTableTotalRow.='<td colspan="4">Totales</td>';  
          $invoiceTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$invoiceTotalsArray['Client'][$clientId]['Total']['amount_cs'].'</span></td>';  
          $invoiceTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$invoiceTotalsArray['Client'][$clientId]['Total']['saldo_cs'].'</span></td>';  
          
        $invoiceTableTotalRow.='</tr>';
        
        $invoiceTableBody='<tbody>'.$invoiceTableTotalRow.$invoiceTableBodyRows.$invoiceTableTotalRow.'</tbody>';
        $invoiceTable='<table id="Estado_Cuentas_'.$sundays[$sundayId].'_'.$saturdays[$sundayId].'">'.$invoiceTableHeader.$invoiceTableBody.'</table>';
        echo $invoiceTable;
        $excelTable='<table id="Estado_Cuentas_'.$sundays[$sundayId].'">'.$invoiceTableHeader.$invoiceExcelBody.'</table>';
        $excelOutput.=$excelTable;
      }          
      
      $_SESSION['estadoCuentasCliente'] = $excelOutput;
  
      echo '</div>';
    }
    echo '</div>';
	echo '</div>';
?>
</div>