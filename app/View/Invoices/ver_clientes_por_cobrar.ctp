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
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
    formatPercentages();
	});
</script>
<div class="invoices index fullwidth">
<?php 
	echo "<h1>".__('Reporte de Clientes por Cobrar')."</h1>";
	echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
				echo $this->Form->end(__('Refresh'));
        if ($enterpriseId > 0){
          $fileName=$enterprises[$enterpriseId].'_'.date('d_m_Y').'_Clientes_x_Cobrar.xlsx';
          //echo "fileName is ".$fileName."<br/>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarClientesPorCobrar',$fileName],['class' => 'btn btn-primary']); 
        }
      echo '</div>';
      echo '<div class="col-sm-6">';
        
      echo '</div>';  
    echo '</div>';  
    echo '<div class="rows">';
      echo '<div class="col-sm-12">';
      if ($enterpriseId == 0){
        echo '<h2>Seleccione una gasolinera para ver datos</h2>';
      }
      else {
        $reportTable="";
        $table_id="clientes_por_cobrar";
        $reportTable.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
          $reportTable.="<thead>";
            $reportTable.="<tr>";
              $reportTable.="<th>Cliente</th>";
              $reportTable.="<th>Contacto</th>";
              $reportTable.="<th class='centered'>Saldo Pendiente</th>";
              //$reportTable.="<th class='centered'>2</th>";
              //$reportTable.="<th class='centered'>3</th>";
              //$reportTable.="<th class='centered'>>3+</th>";
              for ($i=1;$i<=3;$i++){
                $reportTable.='<th class="centered">'.$sundays[$i].'-'.$saturdays[$i].'</th>';
              }
              $reportTable.='<th class="centered">Antes del '.$sundays[3].'</th>';
              //$reportTable.="<th class='centered'>Promedio Crédito Año</th>";
            $reportTable.="</tr>";
          $reportTable.="</thead>";
          $reportTable.="<tbody>";
          $totalPending=[
            '1'=>0,
            '2'=>0,
            '3'=>0,
            '4'=>0,
          ];
          $totalCSPending=0;
          //$totalCSUnder30=0;
          //$totalCSUnder45=0;
          //$totalCSUnder60=0;
          //$totalCSOver60=0;
          $clientBody="";
          foreach ($clients as $client){
            
            $totalCSPending+=$client['saldo'];
              //$totalCSUnder30+=$client['pendingUnder30'];
              //$totalCSUnder45+=$client['pendingUnder45'];
              //$totalCSUnder60+=$client['pendingUnder60'];
              //$totalCSOver60+=$client['pendingOver60'];
              for ($i=1;$i<=4;$i++){
                $totalPending[$i]+=$client['pending'][$i];
              }
                $contactData=(empty($client['ThirdParty']['first_name'])?"":strtoupper($client['ThirdParty']['first_name'])).(empty($client['ThirdParty']['last_name'])?"":(" ".strtoupper($client['ThirdParty']['last_name'])));
            $contactData.=(empty($client['ThirdParty']['phone'])?"-":((empty($contactData)?"":"<br/>")."Tel: ".$client['ThirdParty']['phone']));
            //pr($client);
            if ($client['saldo']>0){
              $clientBody.="<tr>";
                $clientBody.="<td>".$this->Html->link($client['ThirdParty']['company_name'], array('controller' => 'invoices', 'action' => 'verFacturasPorCobrar', $client['ThirdParty']['id']))."</td>";
                $clientBody.="<td>".$contactData."</td>";
                $clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['saldo']."</span></td>";
                //$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder30']."</span></td>";
                //$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder45']."</span></td>";
                //$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder60']."</span></td>";
                //$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingOver60']."</span></td>";
                for ($i=1;$i<=4;$i++){
                  //echo 'i is '.$i.'<br/>';
                  $clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pending'][$i]."</span></td>";
                }
                //$clientBody.="<td class='centered'><span class='amountright'>".round($client['historicalCredit'])."</span></td>";
                
              $clientBody.="</tr>";
            }
          }	
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";	
              $totalRow.="<td></td>";	
              $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
              //$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder30."</span></td>";
              //$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder45."</span></td>";
              //$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder60."</span></td>";
              //$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSOver60."</span></td>";
              for ($i=1;$i<=4;$i++){
                $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPending[$i]."</span></td>";
              }
              //$totalRow.="<td class='centered'><span class='amountright'></span></td>";
            $totalRow.="</tr>";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total %</td>";	
              $totalRow.="<td></td>";
              $totalRow.="<td class='centered percentage'><span class='centered'>".round($totalCSPending/$totalCSPending,2)." </span></td>";
              //$totalRow.="<td class='centered'>".round(100*$totalCSUnder30/$totalCSPending,2)." %</td>";
              //$totalRow.="<td class='centered'>".round(100*$totalCSUnder45/$totalCSPending,2)." %</td>";
              //$totalRow.="<td class='centered'>".round(100*$totalCSUnder60/$totalCSPending,2)." %</td>";
              //$totalRow.="<td class='centered'>".round(100*$totalCSOver60/$totalCSPending,2)." %</td>";
              for ($i=1;$i<=4;$i++){
                $totalRow.="<td class='centered percentage'><span class='centered'>".round($totalPending[$i]/$totalCSPending,2)."</span></td>";
              }
              //$totalRow.="<td class='centered'></td>";
            $totalRow.="</tr>";
            $reportTable.=$totalRow.$clientBody.$totalRow;
          $reportTable.="</tbody>";
        $reportTable.="</table>";
        echo $reportTable;
        
        $_SESSION['clientesPorCobrar'] = $reportTable;
      }
      echo '</div>';
    echo '</div>';
  echo '</div>';
?>
</div>