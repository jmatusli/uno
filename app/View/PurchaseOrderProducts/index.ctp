<div class="purchaseOrderProducts index">
<?php 
	echo "<h2>".__('Purchase Order Products')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Purchase Order Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Purchase Orders'), array('controller' => 'purchase_orders', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Purchase Order'), array('controller' => 'purchase_orders', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
<div>
<?php
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('purchase_order_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('product_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('product_description')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('product_quantity')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('product_unit_cost')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('product_total_cost')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('purchase_order_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('product_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('product_description')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('product_quantity')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('product_unit_cost')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('product_total_cost')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	foreach ($purchaseOrderProducts as $purchaseOrderProduct){ 
		$pageRow=""		$pageRow=""		$pageRow.="<td>".$this->Html->link($purchaseOrderProduct['PurchaseOrder']['id'], array('controller' => 'purchase_orders', 'action' => 'view', $purchaseOrderProduct['PurchaseOrder']['id']))."</td>";
		$pageRow=""		$pageRow.="<td>".$this->Html->link($purchaseOrderProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $purchaseOrderProduct['Product']['id']))."</td>";
		$pageRow=""		$pageRow.="<td>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_description'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_quantity'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_unit_cost'])."</td>";
		$pageRow=""		$pageRow.="<td>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_total_cost'])."</td>";
		$pageRow=""		$pageRow=""
			$excelBody.="<tr>".$pageRow."</tr>";

			$pageRow.="<td class='actions'>";
				$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $purchaseOrderProduct['PurchaseOrderProduct']['id']));
				$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $purchaseOrderProduct['PurchaseOrderProduct']['id']));
				//$pageRow.=->postLink(__('Delete'), array('action' => 'delete', $purchaseOrderProduct['PurchaseOrderProduct']['id']), array(), __('Are you sure you want to delete # %s?', $purchaseOrderProduct['PurchaseOrderProduct']['id']));
			$pageRow.="</td>";

		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageTotalRow="";
	$pageTotalRow.="<tr class=\'totalrow\'>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
	$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
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

	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#ReportStartdateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(previousMonth);
		$('#ReportStartdateYear').val(previousYear);
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#ReportEnddateDay').val(daysInPreviousMonth);
		$('#ReportEnddateMonth').val(previousMonth);
		$('#ReportEnddateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#ReportStartdateYear').val());
		if (nextMonth==0){
			nextMonth=12;
		}
		if (nextMonth==1){
			nextYear+=1;
		}
		if (nextMonth<10){
			nextMonth="0"+nextMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(nextMonth);
		$('#ReportStartdateYear').val(nextYear);
		var daysInNextMonth=daysInMonth(nextMonth,nextYear);
		$('#ReportEnddateDay').val(daysInNextMonth);
		$('#ReportEnddateMonth').val(nextMonth);
		$('#ReportEnddateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
</script>