<script>
	function formatNumbers(){
		$("td.number span.amount.right").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
      var negative=false
      if (parseFloat($(this).text())<0){
				negative=true
			}
			$(this).number(true,2,'.',',');
      if (negative){
        $(this).prepend("-");
      }
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

<div class="resumen fullwidth">
<?php 	
  $excelOutput='';
  echo '<h2>'.__('Reporte Medidas de Mangueras').'</h2>';
  
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.hose_id',['label'=>'Filtrar x Manguera','default'=>$hoseId,'empty'=>[0=>'-- Todas Mangueras --']]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=date('d_m_Y')."_".$enterprises[$enterpriseId]."_Reporte_Medidas_Mangueras.xlsx";
	
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteMedidasMangueras',$fileName], ['class' => 'btn btn-primary']);
        }
	
        
      echo '</div>';
      echo '<div class="col-sm-6">';
       
      echo '</div>';
    echo '</div>';
    if ($enterpriseId == 0){
      echo '<h3>Seleccione una gasolinera para ver datos</h3>';
    }
    else {
      echo '<div class="rows">';  
        echo '<div class="col-sm-12">';      
        if (empty($measurementsArray)){
          echo '<h3>No se han definido manguear para la gasolinera</h3>';
        }
        else {
          $measurementTableHeadRow='';        
          $measurementTableHeadRow.='<tr>';
            $measurementTableHeadRow.='<th></th>';
            foreach ($measurementsArray['Hose'] as $hoseId=>$hoseData){
              //$measurementTableHeadRow.='<th class="separator" style="background-color:rgba(0,0,0,0)!important"></th>';
              //$measurementTableHeadRow.='<th class="centered" colspan="3">'.$hoseData['hose_name'].' ('.$hoseData['product_name'].')</th>';
              $measurementTableHeadRow.='<th class="centered">'.$hoseData['hose_name'].' ('.$hoseData['product_name'].')</th>';
            }
            $measurementTableHeadRow.='<th class="separator"></th>';
            //$measurementTableHeadRow.='<th class="centered" colspan="3">Total</th>';
            $measurementTableHeadRow.='<th class="centered">Total</th>';
          $measurementTableHeadRow.='</tr>';
          $measurementTableHeadRow.='<tr>';
            $measurementTableHeadRow.='<th>Fecha</th>';
            foreach ($measurementsArray['Hose'] as $hoseId=>$hoseData){
              //$measurementTableHeadRow.='<th class="separator"></th>';
              //$measurementTableHeadRow.='<th class="centered">Contador</th>';
              //$measurementTableHeadRow.='<th class="centered">Medida</th>';
              $measurementTableHeadRow.='<th class="centered">Dif</th>';
            }
            $measurementTableHeadRow.='<th class="separator"></th>';
            //$measurementTableHeadRow.='<th class="centered">Contador</th>';
            //$measurementTableHeadRow.='<th class="centered">Medida</th>';
            $measurementTableHeadRow.='<th class="centered">Dif</th>';
          $measurementTableHeadRow.='</tr>';
          $excelMeasurementTableHead=$measurementTableHead='<thead>'.$measurementTableHeadRow.'</thead>';
          
          $hoseTotals=[];
          foreach (array_keys($measurementsArray['Hose']) as $hoseId){
            $hoseTotals[$hoseId]=[
              'total_counter'=>0,
              'total_measurement'=>0,
            ];
          }  
          $excelMeasurementTableRows=$measurementTableRows='';
          foreach ($measurementsArray['Measurement'] as $measurementDate=>$measurementData){
            $excelRow=$measurementRow='';
            $measurementDateTime=new DateTime($measurementDate);
            $measurementRow.='<tr>';
              $measurementRow.='<td>'.$measurementDateTime->format('d-m-Y').'&nbsp;</td>';
            
            foreach (array_keys($measurementsArray['Hose']) as $hoseId){
              $hoseTotals[$hoseId]['total_counter']+=$measurementData['Hose'][$hoseId]['counter_value'];
              $hoseTotals[$hoseId]['total_measurement']+=$measurementData['Hose'][$hoseId]['measurement_value'];
              
              //$measurementRow.='<td class="separator"></td>'; 
              //$measurementRow.='<td class="number"><span class="amount right">'.$measurementData['Hose'][$hoseId]['counter_value'].'</span></td>';
              //$measurementRow.='<td class="number"><span class="amount right">'.$measurementData['Hose'][$hoseId]['measurement_value'].'</span></td>';
              $measurementRow.='<td class="number"><span class="amount right">'.($measurementData['Hose'][$hoseId]['counter_value']-$measurementData['Hose'][$hoseId]['measurement_value']).'</span></td>';
            }  
            $measurementRow.='<td class="separator"></td>'; 
            //$measurementRow.='<td class="number"><span class="amount right">'.$measurementData['DayTotal']['counter_value'].'</span></td>';
            //$measurementRow.='<td class="number"><span class="amount right">'.$measurementData['DayTotal']['measurement_value'].'</span></td>';
            $measurementRow.='<td class="number"><span class="amount right">'.($measurementData['DayTotal']['counter_value']-$measurementData['DayTotal']['measurement_value']).'</span></td>'; 
              $excelRow=$measurementRow;
            $excelRow.='</tr>';            
            $measurementTableRows.=$measurementRow; 
            
            $excelMeasurementTableRows.=$excelRow;  
          }
            
          $excelMeasurementTotalRow=$measurementTotalRow='';
          
          $measurementTotalRow.='<tr class="totalrow">';
            $measurementTotalRow.='<td>Total</td>';
            foreach (array_keys($measurementsArray['Hose']) as $hoseId){
              //$measurementTotalRow.='<td class="separator"></td>';
              //$measurementTotalRow.='<td class="number"><span class="amount right">'.$hoseTotals[$hoseId]['total_counter'].'</span></td>';
              //$measurementTotalRow.='<td class="number"><span class="amount right">'.$hoseTotals[$hoseId]['total_measurement'].'</span></td>';
              $measurementTotalRow.='<td class="number"><span class="amount right">'.($hoseTotals[$hoseId]['total_counter']-$hoseTotals[$hoseId]['total_measurement']).'</span></td>';
            }
            $measurementTotalRow.='<td class="separator"></td>'; 
            //$measurementTotalRow.='<td class="number"><span class="amount right">'.$measurementsArray['GrandTotal']['counter_value'].'</span></td>';
            //$measurementTotalRow.='<td class="number"><span class="amount right">'.$measurementsArray['GrandTotal']['measurement_value'].'</span></td>';
            $measurementTotalRow.='<td class="number"><span class="amount right">'.($measurementsArray['GrandTotal']['counter_value']-$measurementsArray['GrandTotal']['measurement_value']).'</span></td>';
              
            $excelMeasurementTotalRow=$measurementTotalRow;
          $excelMeasurementTotalRow.='</tr>';
          $measurementTotalRow.='</tr>';
    
          $measurementTableBody='<tbody>'.$measurementTotalRow.$measurementTableRows.$measurementTotalRow.'</tbody>';
          $excelMeasurementTableBody='<tbody>'.$excelMeasurementTotalRow.$excelMeasurementTableRows.$excelMeasurementTotalRow.'</tbody>';
          
          $measurementTable='<table id="medidas_vara" cellpadding="0" cellspacing="0">'.$measurementTableHead.$measurementTableBody.'</table>';
          echo $measurementTable;
          $excelMeasurementTable='<table id="facturas" cellpadding="0" cellspacing="0">'.$excelMeasurementTableHead.$excelMeasurementTableBody.'</table>';
          $excelOutput.=$excelMeasurementTable;
          
        }  
        echo '</div>';
      echo '</div>';
    }
  echo '</div>';
  $_SESSION['reporteMedidasTanques'] = $excelOutput;
  
?>  
</div>
