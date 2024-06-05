<script>
	function formatNumbers(){
		$("td.number span.amount").each(function(){
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

<div class="resumen fullwidth">
<?php 	
  $excelOutput='';
  echo '<h2>'.__('Reporte Medidas de Vara').'</h2>';
  
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
          
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.tank_id',['label'=>'Filtrar x Tanque','default'=>$tankId,'empty'=>[0=>'-- Todos Tanques --']]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
        
        if ($enterpriseId > 0){
          $fileName=date('d_m_Y')."_".$enterprises[$enterpriseId]."_Reporte_Medidas_Tanques.xlsx";
	
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteMedidasTanques',$fileName], ['class' => 'btn btn-primary']);
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
          echo '<h3>No se han definido tanques para la gasolinera</h3>';
        }
        else {
          $measurementTableHeadRow='';        
          $measurementTableHeadRow.='<tr>';
            $measurementTableHeadRow.='<th></th>';
            foreach ($measurementsArray['Tank'] as $tankId=>$tankData){
              $measurementTableHeadRow.='<th class="separator" style="background-color:rgba(0,0,0,0)!important"></th>';
              $measurementTableHeadRow.='<th class="centered" colspan="3">'.$tankData['tank_name'].' ('.$tankData['product_name'].')</th>';
            }
            $measurementTableHeadRow.='<th class="separator"></th>';
            $measurementTableHeadRow.='<th class="centered" colspan="3">Total</th>';
          $measurementTableHeadRow.='</tr>';
          $measurementTableHeadRow.='<tr>';
            $measurementTableHeadRow.='<th>Fecha</th>';
            foreach ($measurementsArray['Tank'] as $tankId=>$tankData){
              $measurementTableHeadRow.='<th class="separator"></th>';
              $measurementTableHeadRow.='<th class="centered">Invent</th>';
              $measurementTableHeadRow.='<th class="centered">Vara</th>';
              $measurementTableHeadRow.='<th class="centered">Dif</th>';
            }
            $measurementTableHeadRow.='<th class="separator"></th>';
            $measurementTableHeadRow.='<th class="centered">Invent</th>';
            $measurementTableHeadRow.='<th class="centered">Vara</th>';
            $measurementTableHeadRow.='<th class="centered">Dif</th>';
          $measurementTableHeadRow.='</tr>';
          $excelMeasurementTableHead=$measurementTableHead='<thead>'.$measurementTableHeadRow.'</thead>';
          
          $tankTotals=[];
          foreach (array_keys($measurementsArray['Tank']) as $tankId){
            $tankTotals[$tankId]=[
              'total_inventory'=>0,
              'total_measurement'=>0,
              'total_calibration'=>0,
            ];
          }  
          $excelMeasurementTableRows=$measurementTableRows='';
          foreach ($measurementsArray['Measurement'] as $measurementDate=>$measurementData){
            $excelRow=$measurementRow='';
            $measurementDateTime=new DateTime($measurementDate);
            $measurementRow.='<tr>';
              $measurementRow.='<td>'.$measurementDateTime->format('d-m-Y').'&nbsp;</td>';
            
            foreach (array_keys($measurementsArray['Tank']) as $tankId){
              $tankTotals[$tankId]['total_inventory']+=$measurementData['Tank'][$tankId]['inventory_value'];
              $tankTotals[$tankId]['total_measurement']+=$measurementData['Tank'][$tankId]['measurement_value'];
              $tankTotals[$tankId]['total_calibration']+=$measurementData['Tank'][$tankId]['calibration_value'];
            
              $measurementRow.='<td class="separator"></td>'; 
              $measurementRow.='<td class="number"><span class="amount right">'.$measurementData['Tank'][$tankId]['inventory_value'].'</span></td>';
              $measurementRow.='<td class="number"><span class="amount right">'.$measurementData['Tank'][$tankId]['measurement_value'].'</span></td>';
              $measurementRow.='<td class="number"><span class="amount right">'.($measurementData['Tank'][$tankId]['inventory_value']-$measurementData['Tank'][$tankId]['measurement_value']).'</span></td>';
            }  
            $measurementRow.='<td class="separator"></td>'; 
            $measurementRow.='<td class="number"><span class="amount right">'.$measurementData['DayTotal']['inventory_value'].'</span></td>';
            $measurementRow.='<td class="number"><span class="amount right">'.$measurementData['DayTotal']['measurement_value'].'</span></td>';
            $measurementRow.='<td class="number"><span class="amount right">'.($measurementData['DayTotal']['inventory_value']-$measurementData['DayTotal']['measurement_value']).'</span></td>'; 
              $excelRow=$measurementRow;
            $excelRow.='</tr>';            
            $measurementTableRows.=$measurementRow; 
            
            $excelMeasurementTableRows.=$excelRow;  
          }
            
          $excelMeasurementTotalRow=$measurementTotalRow='';
          
          $measurementTotalRow.='<tr class="totalrow">';
            $measurementTotalRow.='<td>Total</td>';
            foreach (array_keys($measurementsArray['Tank']) as $tankId){
              $measurementTotalRow.='<td class="separator"></td>';
              $measurementTotalRow.='<td class="number"><span class="amount right">'.$tankTotals[$tankId]['total_inventory'].'</span></td>';
              $measurementTotalRow.='<td class="number"><span class="amount right">'.$tankTotals[$tankId]['total_measurement'].'</span></td>';
              $measurementTotalRow.='<td class="number"><span class="amount right">'.($tankTotals[$tankId]['total_inventory']-$tankTotals[$tankId]['total_measurement']).'</span></td>';
            }
            $measurementTotalRow.='<td class="separator"></td>'; 
            $measurementTotalRow.='<td class="number"><span class="amount right">'.$measurementsArray['GrandTotal']['inventory_value'].'</span></td>';
            $measurementTotalRow.='<td class="number"><span class="amount right">'.$measurementsArray['GrandTotal']['measurement_value'].'</span></td>';
            $measurementTotalRow.='<td class="number"><span class="amount right">'.($measurementsArray['GrandTotal']['inventory_value']-$measurementsArray['GrandTotal']['measurement_value']).'</span></td>';
              
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
