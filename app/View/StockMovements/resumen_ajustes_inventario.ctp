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
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
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
<div class="adjustments index fullwidth">
<?php 
	echo "<h2>".__('Resumen Ajustes de Inventario')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
			echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
      echo $this->Form->input('Report.stock_movement_type_id',['label'=>__('Tipo ajuste'),'default'=>0,'empty'=>[0=>'Seleccione tipo ajuste']]);
			//echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId]);
			
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh'));
   echo "<div class='container-fluid'>";
      echo "<div class='rows'>";
        echo "<div class='col-sm-4'>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']); 
        echo "</div>";
        if ($userrole===ROLE_ADMIN){  
          echo "<div class='col-sm-4'>";
            echo $this->Html->link(__('Registrar Ajustes de Inventario'), ['action' => 'registrarAjuste'],['class' => 'btn btn-primary','target'=>'blank']); 
          echo "</div>";  
        }
      echo "</div>";    
    echo "</div>";     

	$excelOutput="";

	$tableHeader="<thead>";
		$tableHeader.="<tr>";
			$tableHeader.="<th>Fecha</th>";
			$tableHeader.="<th># Ajuste</th>";
      $tableHeader.="<th>Producto</th>";
      $tableHeader.="<th>Cantidad</th>";
      //$tableHeader.="<th class='actions'>".__('Actions')."</th>";
		$tableHeader.="</tr>";
	$tableHeader.="</thead>";
	$excelTableHeader="<thead>";
		$excelTableHeader.="<tr>";
			$excelTableHeader.="<th>Fecha</th>";
			$excelTableHeader.="<th># Ajuste</th>";
      $excelTableHeader.="<th>Producto</th>";
      $excelTableHeader.="<th>Cantidad</th>";
		$excelTableHeader.="</tr>";
	$excelTableHeader.="</thead>";
	
  	
	foreach ($adjustments as $adjustmentStockMovementTypeId=>$adjustmentData){
    //pr($adjustmentData);
    echo "<h2>".$stockMovementTypes[$adjustmentStockMovementTypeId]."</h2>";

    if (empty($adjustmentData['Adjustments'])){
      echo "<p>No hay ajustes para este tipo de ajustes</p>";
    }
    else {
      $tableBody="";
      $excelBody="";

      $totalLowered=0;
      $totalRaised=0;
      
      foreach ($adjustmentData['Adjustments'] as $movement){
        if ($movement['StockMovement']['product_quantity']>0){
      
          $adjustmentDateTime=new DateTime($movement['StockMovement']['movement_date']);
          if (!$movement['StockMovement']['bool_input']){
            $totalLowered+=$movement['StockMovement']['product_quantity'];
          }
          else {
            $totalRaised+=$movement['StockMovement']['product_quantity'];
          }
          $tableRow="";
          $tableRow.="<td>".$adjustmentDateTime->format('d-m-Y')."</td>";
          $tableRow.="<td>".(empty($movement['StockMovement']['adjustment_code'])?"-":$movement['StockMovement']['adjustment_code'])."</td>";
          $tableRow.="<td>".$movement['Product']['name']."</td>";
          $tableRow.="<td class='number'><span class='amountright'>".($movement['StockMovement']['bool_input']?$movement['StockMovement']['product_quantity']:-$movement['StockMovement']['product_quantity'])."</span></td>";
          
          $excelRow=$tableRow;
          $excelBody.="<tr>".$excelRow."</tr>";
              
          //$tableRow.="<td class='actions'>";
          //  $tableRow.=$this->Form->postLink(__('Eliminar ajuste'), ['action' => 'eliminarAjuste',$movement['StockMovement']['adjustment_code']],[], __('Está seguro que quiere  eliminar ajuste # %s?', $movement['StockMovement']['adjustment_code']));
          //$tableRow.="</td>";
          
          $tableBody.="<tr>".$tableRow."</tr>";	
        }
      }  
      $tableTotalRows="";
      $tableTotalRows.="<tr class='totalrow'>";
        $tableTotalRows.="<td>Total Salida</td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td class='number'><span class='amountright'>".$totalLowered."</span></td>";
        $tableTotalRows.="<td></td>";
      $tableTotalRows.="</tr>";
      $tableTotalRows.="<tr class='totalrow'>";
        $tableTotalRows.="<td>Total Entrada</td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td class='number'><span class='amountright'>".$totalRaised."</span></td>";
        $tableTotalRows.="<td></td>";
      $tableTotalRows.="</tr>";
      $tableTotalRows.="<tr class='totalrow'>";
        $tableTotalRows.="<td>Total Neto</td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td></td>";
        $tableTotalRows.="<td class='number'><span class='amountright'>".($totalLowered+$totalRaised)."</span></td>";
        $tableTotalRows.="<td></td>";
      $tableTotalRows.="</tr>";
      
      $tableBody="<tbody>".$tableTotalRows.$tableBody.$tableTotalRows."</tbody>";
      $table_id=$stockMovementTypes[$adjustmentStockMovementTypeId];
      $table="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$tableHeader.$tableBody."</table>";
      echo $table;
      $excelOutput.="<table id='".$table_id."'>".$excelTableHeader.$excelBody."</table>";
    }  
  }
	$_SESSION['ajustesInventario'] = $excelOutput;
?>
</div>