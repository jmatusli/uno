<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  var csCurrencyId=<?php echo CURRENCY_CS; ?>;
  var usdCurrencyId=<?php echo CURRENCY_USD; ?>;

  $('body').on('click','.recibosDolares',function(){	
    var operatorId=$(this).closest('tr').attr('operatorid');
    $(this).closest('tbody').find('tr.cashUSD[operatorid=\''+operatorId+'\']').removeClass('hidden');
	});

	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToThree(num) {    
		return +(Math.round(num + "e+3")  + "e-3");
	}
  
  function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
  function formatPercentages(){
		$("td.percentage span.amountcenter").each(function(){
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
			$(this).find('.amountcenter').number(true,2);
      
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
      
			$(this).find('.currency').text("C$");
		});
	}
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountcenter').text())<0){
				$(this).find('.amountcenter').prepend("-");
			}
			$(this).find('.amountcenter').number(true,2);
      
      if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
      
			$(this).find('.currency').text("US$");
		});
	}

	$(document).ready(function(){
    formatNumbers();
		formatCurrencies();
    formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
  });
  
</script>

<div class="orders form paymentreceipts fullwidth">
<?php 
	echo "<h2>".__('Reporte de Recibos')."</h2>";
	echo "<div class='container-fluid'>";
		echo "<div class='rows'>";
			echo "<div class='col-sm-8'>";			
				echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
					echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')]);
          //echo $this->Form->input('Report.display_option_id',['label'=>__('Mostrar'),'default'=>$displayOptionId]);
          echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          //echo $this->Form->input('Report.shift_id',['label'=>__('Shift'),'default'=>$shiftId,'empty'=>[0=>'Seleccione Turno']]);
          //echo $this->Form->input('Report.operator_id',['label'=>__('Opera'),'default'=>$operatorId,'empty'=>[0=>'Seleccione Operador']]);
				echo "</fieldset>";
        
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->submit(__('Refresh',['div'=>['style'=>'clear:left;']]));
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenRecibos'], ['class' => 'btn btn-primary']); 
        echo $this->Form->end();
				
        

			echo "</div>";
			echo "<div class='col-sm-4'>";			
      /*
      echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          if ($bool_add_permission) { 
            echo "<li>".$this->Html->link(__('Informe IV Registrar Pagos'), ['action' => 'registrarRecibos'])."</li>";
            echo "<br/>";
          }
        echo "</ul>";
      */
			echo "</div>";
		echo "</div>";
	echo "</div>";
?>
</div>
<div class='related'>
<?php
	$tableHeader='<thead>';
    $tableHeader.='<tr>';
      $tableHeader.='<th>Fecha</th>';
      $tableHeader.='<th>Efectivo C$</th>';
      $tableHeader.='<th>BAC</th>';
      $tableHeader.='<th>Banpro</th>';
      $tableHeader.='<th>Crédito</th>';
      $tableHeader.='<th>Recibos totales</th>';
      $tableHeader.='<th>Ventas Neto</th>';
      $tableHeader.='<th>Calibraciones</th>';
      $tableHeader.='<th>Ventas Totales</th>';
    $tableHeader.='</tr>';
  $tableHeader.='</thead>';
  
  $totalCashCS=0;
  $totalCardBAC=0;
  $totalCardBANPRO=0;
  $totalCreditCS=0;
  $totalReceipts=0;
  $totalNetSales=0;
  $totalCalibrations=0;
  $totalTotalSales=0;
  
  
  $paymentRows='';
  if (!empty($paymentsArray)){
    foreach ($paymentsArray as $paymentReceiptDate=>$paymentInfo){
      $paymentReceiptDateTime=new DateTime($paymentReceiptDate);
      
      $totalCashCS+=$paymentInfo['total_cash'];
      $totalCardBAC+=$paymentInfo['total_card_bac'];
      $totalCardBANPRO+=$paymentInfo['total_card_banpro'];
      $totalCreditCS+=$paymentInfo['total_credit'];
      $totalReceipts+=$paymentInfo['total_received'];
      $totalNetSales+=$paymentInfo['net_price'];
      $totalCalibrations+=$paymentInfo['total_calibration'];
      $totalTotalSales+=$paymentInfo['total_price'];
      
      
       $paymentRows.="<tr>";
        $paymentRows.="<td>".$this->Html->Link($paymentReceiptDateTime->format('d-m-Y'),['action'=>'registrarRecibos',$paymentReceiptDate])."</td>";
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_cash'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_card_bac'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_card_banpro'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_credit'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_received'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['net_price'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_calibration'].'</span></td>'; 
        $paymentRows.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$paymentInfo['total_price'].'</span></td>'; 

      $paymentRows.="</tr>";
    }  
  }
  $totalRow="";
  $totalRow.="<tr class='totalrow'>";
    $totalRow.="<td>Total</td>";
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalCashCS.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalCardBAC.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalCardBANPRO.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalCreditCS.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalReceipts.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalNetSales.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalCalibrations.'</span></td>'; 
    $totalRow.='<td class="centered CScurrency"><span class="currency">C$ </span><span class="amountright">'.$totalTotalSales.'</span></td>'; 
  $totalRow.="</tr>";
  
  $tableBody= '<tbody>'.$totalRow.$paymentRows.$totalRow.'</tbody>';
  $table='<table id="recibos" cellpadding="0" cellspacing="0">'.$tableHeader.$tableBody.'</table>';    
  echo '<h3>Recibos por modo de pago</h3>';
  echo '<p class="info">Montos en US$ se convertieron a C$ según la tasa de cambio registrada para el día correspondiente</p>';
  echo $table;
  
  $_SESSION['resumenRecibos'] = $table;
?>
</div>