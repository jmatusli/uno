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

  echo '<h1>Estado de Cuentas</h1>';
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-8">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          
          echo $this->Form->input('Report.sunday_id',['default'=>$sundayId]);
					echo $this->Form->input('Report.saturday_id',['default'=>$saturdayId]);
					echo $this->Form->input('Report.payment_mode_id',['label'=>'Filtar x Modo Pago','default'=>$paymentModeId,'empty'=>[0=>'-- Todos Modos de Pago --']]);
          echo $this->Form->input('Report.invoice_display_option_id',['label'=>'Facturas','default'=>$invoiceDisplayOptionId]);
          echo $this->Form->input('Report.amount_display_option_id',['label'=>'Montos','default'=>$amountDisplayOptionId]);
				echo "</fieldset>";
				//echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				//echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=$enterprises[$enterpriseId].'_'.date('d_m_Y').'_Estado_Cuentas.xlsx';
          //echo "fileName is ".$fileName."<br/>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarEstadoCuentas',$fileName], ['class' => 'btn btn-primary']);
        }
	
        
      echo '</div>';
      echo '<div class="col-sm-4">';
      /*  
        echo '<h3>'. __('Actions').'</h3>';
        echo '<ul style="list-style:none;">';
          if ($bool_add_permission) { 
            echo '<li>'. $this->Html->link(__('New Invoice'), ['action' => 'crear']).'</li>';
            echo '<br/>';
          }
          echo '<li>'. $this->Html->link(__('List Clients'), ['controller' => 'third_parties', 'action' => 'resumenClientes']).' </li>';
          echo '<li>'. $this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente']).' </li>';          
        echo '</ul>';
      */
      echo '</div>';
    echo '</div>';
    echo '<div class="rows" style="clear:left;">';  
    if ($enterpriseId == 0){
      echo '<h3>Seleccione una gasolinera para ver datos</h3>';
    }
    else {      
      echo '<div class="col-sm-12">';
      if (!empty($weeks)){
        foreach ($weeks['Week'] as $weekId=>$weekData){
          //$sundayDateTime=new DateTime($allSundays[$weekId]);
          //$saturdayDateTime=new DateTime($allSaturdays[$weekId]);
          echo '<h2>Semana de '.$sundays[$weekId].' a '.$saturdays[$weekId].'</h2>';
          
          if (empty($weekData['Total'])){
            echo '<h3>No hay datos para esta semana</h3>';
          }
          else {
            $weekTableHeader='';
            $weekTableHeader.='<thead>';
              $weekTableHeader.='<tr>';
                $weekTableHeader.='<th>Cliente</th>';  
                foreach($weekData['DayTotal'] as $invoiceDate=>$invoiceTotal){
                  $invoiceDateTime=new DateTime($invoiceDate);
                  $weekTableHeader.='<th class="centered">'.$invoiceDateTime->format('d-m').'</th>';  
                }
                $weekTableHeader.='<th class="centered">Total</th>';  
              $weekTableHeader.='</tr>';
            $weekTableHeader.='</thead>';
            
            //pr($weekData);
            //echo 'weekData Total is '.$weekData['Total'].'<br/>';
            
            $weekTableBodyRows='';
            foreach ($weekData['Client'] as $clientId => $clientData){
              $weekTableBodyRows.='<tr>';
                if ($clientId == 0){
                  $weekTableBodyRows.='<td>'.$paymentModes[$paymentModeId].'</td>';  
                }
                else {
                  $weekTableBodyRows.='<td>'.$this->Html->Link($clients[$clientId],['action'=>'estadoCuentasCliente',$clientId,$weekId]).'</td>';  
                }
                foreach($clientData['DayTotal'] as $clientDate=>$clientTotal){
                  $weekTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$clientTotal.'</span></td>';  
                }
                $weekTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$clientData['Total'].'</span></td>';  
              $weekTableBodyRows.='</tr>';
            }
             
            $weekExcelBody='<tbody>'.$weekTableBodyRows.'</tbody>';
            
            $weekTableTotalRow='';
            $weekTableTotalRow.='<tr class="totalrow">';
              $weekTableTotalRow.='<td>Total</td>';  
              foreach($weekData['DayTotal'] as $invoiceDate=>$invoiceTotal){
                $weekTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$invoiceTotal.'</span></td>';  
              }
              $weekTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amountright">'.$weekData['Total'].'</span></td>';  
            $weekTableTotalRow.='</tr>';
            
            $weekTableBody='<tbody>'.$weekTableTotalRow.$weekTableBodyRows.$weekTableTotalRow.'</tbody>';
            $weekTable='<table id="Estado_Cuentas_'.$sundays[$weekId].'_'.$saturdays[$weekId].'">'.$weekTableHeader.$weekTableBody.'</table>';
            echo $weekTable;
            $excelTable='<table id="Estado_Cuentas_'.$sundays[$weekId].'">'.$weekTableHeader.$weekExcelBody.'</table>';
            $excelOutput.=$excelTable;
          }          
        }
      }
      
      $_SESSION['estadoCuentas'] = $excelOutput;
  
      echo '</div>';
    }
    echo '</div>';
	echo '</div>';
?>
</div>