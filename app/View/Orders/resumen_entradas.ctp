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
	
	function formatPercentages(){
		$("td.percentage span.amountright").each(function(){
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
		formatPercentages();
	});
</script>
<div class="orders index purchases">
<?php 
	echo "<h2>".__('Entradas')."</h2>";
	
	echo "<div class='container-fluid'>";
		echo "<div class='rows'>";
			echo "<div class='col-md-6'>";			
				echo $this->Form->create('Report');
				echo "<fieldset>";
          echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);

					echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2019,'maxYear'=>date('Y')));
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
			echo "</div>";
			echo "<div class='col-md-4'>";			
				
			echo "</div>";
		echo "</div>";
	echo "</div>";
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) { 
			echo "<li>".$this->Html->link(__('New Purchase'), array('action' => 'crearEntrada'))."</li>";
			echo "<br/>";
		}
		if ($bool_provider_index_permission) { 		
			echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))." </li>";
		}
		if ($bool_provider_add_permission) { 
			echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))." </li>";
		} 
	echo "</ul>";
?>
</div>
<div class='related'>";
<?php
  if ($enterpriseId == 0){
    echo '<h2>Seleccione una gasolinera para ver datos</h2>';
  }
  else {
    echo "<table cellpadding='0' cellspacing='0'>";
      echo "<thead>";
        echo "<tr>";
          echo "<th>".$this->Paginator->sort('order_date',__('Purchase Date'))."</th>";
          echo "<th>".$this->Paginator->sort('order_code','# Orden')."</th>";
          echo "<th>".$this->Paginator->sort('invoice_code','# Factura')."</th>";
          echo "<th>".$this->Paginator->sort('third_party_id',__('Proveedor'))."</th>";
          echo "<th>Productos</th>";
          echo "<th>".$this->Paginator->sort('Cantidad Combustible')."</th>";
          //echo "<th>".$this->Paginator->sort('Cantidad Otro')."</th>";
          echo "<th class='centered'>".$this->Paginator->sort('subtotal_price',__('Subtotal'))."</th>";
          //echo "<th class='centered'>Ajuste</th>";
          echo "<th class='centered'>Renta</th>";
          echo "<th class='centered'>IVA</th>";
          echo "<th class='centered'>Total</th>";
          echo "<th class='actions'>".__('Actions')."</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";
        $totalSubtotal=0; 
        $totalFuelCount=0;
        $totalOtherCount=0;
        
        $purchaseRows="";
        foreach ($purchases as $purchase){ 
          //pr($purchase);
          $totalSubtotal+=$purchase['Order']['subtotal_price']; 
          $orderDateTime=new DateTime($purchase['Order']['order_date']);
          
          $fuelCount=0;
          $otherCount=0;
          foreach ($purchase['StockMovement'] as $stockMovement){
            if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_FUELS){
              $fuelCount+=$stockMovement['product_quantity'];
              $totalFuelCount+=$stockMovement['product_quantity'];
            }
            else {
              $otherCount+=$stockMovement['product_quantity'];
              $totalOtherCount+=$stockMovement['product_quantity'];
            }
          }
          
          $purchaseRows.="<tr>";
          
          $purchaseRows.="<td>".$orderDateTime->format('d-m-Y')."</td>";
          $purchaseRows.="<td>".$this->Html->link($purchase['Order']['order_code'], array('action' => 'verEntrada', $purchase['Order']['id']))."</td>";
          $purchaseRows.="<td>".$this->Html->link($purchase['Order']['invoice_code'], array('action' => 'verEntrada', $purchase['Order']['id']))."</td>";
          $purchaseRows.="<td>".$this->Html->link($purchase['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verProveedor', $purchase['ThirdParty']['id']))."</td>";
          $purchaseRows.="<td>";
          foreach($purchase['StockMovement'] as $stockMovement){
            $purchaseRows.=number_format($stockMovement['product_quantity'],0,'',',')." ".$stockMovement['Product']['name']." (Und C$ ".number_format($stockMovement['product_unit_price'],2,'.',',').")<br/>";
          }
          $purchaseRows.="</td>";
          $purchaseRows.="<td class='centered'>".($fuelCount>0?number_format($fuelCount,0,".",","):"-")."</td>";
          //$purchaseRows.="<td class='centered'>".($otherCount>0?number_format($otherCount,0,".",","):"-")."</td>";
          $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['subtotal_price'],4,".",",")."</td>";
          //$purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['adjustment_price'],4,".",",")."</td>";
          $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['rent_price'],4,".",",")."</td>";
          $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['iva_price'],4,".",",")."</td>";
          $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['total_price'],4,".",",")."</td>";
          $purchaseRows.="<td class='actions'>";
            $companyName=str_replace(".","",$purchase['ThirdParty']['company_name']);
            $companyName=str_replace(" ","",$companyName);
            $namepdf="Compra_".$companyName."_".$purchase['Order']['order_code'];
            if ($bool_edit_permission) { 
              $purchaseRows.=$this->Html->link(__('Edit'), array('action' => 'editarEntrada', $purchase['Order']['id'])); 
            } 
            if ($bool_delete_permission) { 
              // $purchaseRows.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $purchase['Order']['id']), array(), __('Are you sure you want to delete # %s?', $purchase['Order']['id'])); 
            }
            $purchaseRows.=$this->Html->link(__('Guardar como pdf'), array('action' => 'verPdfEntrada','ext'=>'pdf', $purchase['Order']['id'],$namepdf));
          $purchaseRows.="</td>";
          
          $purchaseRows.="</tr>";
        } 
        $totalRow="";
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td class='centered'>".number_format($totalFuelCount,0,".",",")."</td>";
          //$totalRow.="<td class='centered'>".number_format($totalOtherCount,0,".",",")."</td>";
          $totalRow.="<td class='centered'><span class='currency'>C$ </span>".number_format($totalSubtotal,4,".",",")."</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
        $totalRow.="</tr>";
        echo $totalRow.$purchaseRows.$totalRow;
      echo "</tbody>";
    echo "</table>";
  }  
?>	
</div>

