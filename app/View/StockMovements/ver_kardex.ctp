<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	$(document).ready(function(){
		formatNumbers();
	});
</script>
<div class="stockMovements view kardex fullwidth">
<?php 
	echo "<h2>".__('Kardex')." ".$product['Product']['name']."</h2>"; 
  
	echo $this->Form->create('Report'); 
    echo "<fieldset>";
      echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
      echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
    echo "</fieldset>";
    echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
    echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 

	$inventoryTable="";
	$inventoryTableHeader="";
	$inventoryTableBody="";
	
	$inventoryTableHeader.="<thead>";
		$inventoryTableHeader.="<tr>";
			$inventoryTableHeader.="<th class='orderdate'>".__('Date')."</th>";
      $inventoryTableHeader.="<th>Tipo</th>";
			$inventoryTableHeader.="<th>".__('Proveedor o Cliente')."</th>";
			$inventoryTableHeader.="<th>Número orden</th>";
			$inventoryTableHeader.="<th class='centered' colspan='3'>".$this->Html->link($product['Product']['name'], ['controller' => 'products', 'action' => 'view', $product['Product']['id']])."</th>";
			
		$inventoryTableHeader.="</tr>";
	$inventoryTableHeader.="</thead>";

  //pr ($originalInventory);
  $tableRows="";
  $tableRows.="<tr>";
    $tableRows.="<td></td>";
    $tableRows.="<td></td>";
    $tableRows.="<td></td>";
    $tableRows.="<td>Inventario Inicial</td>";
    $tableRows.="<td class='centered number' style='font-weight:bold'>".$originalInventory['total_entries']."</td>";
    $tableRows.="<td class='centered number' style='font-weight:bold'>".$originalInventory['total_exits']."</td>";
    $tableRows.="<td class='centered number' style='font-weight:bold'>".$originalInventory['total_saldo']."</td>";
    
  $tableRows.="</tr>";
  
  $currentSaldo=$originalInventory['total_saldo'];
	
	foreach($resultMatrix as $row){
		//pr($row);
		$rowDateTime=new DateTime($row['date']);
    $currentSaldo=$currentSaldo+$row['total_entries']-$row['total_exits'];
    switch ($row['type']){
      case 'Compra':
        $providerAction="verProveedor";
        $orderAction="verEntrada";
        break;
      case 'Venta':    
      case 'Ajuste':      
      default:    
        $providerAction="";
        $orderAction="registrarVentas";
    }
		$tableRows.="<tr>";
			$tableRows.="<td class='orderdate'>".$rowDateTime->format('d-m-Y')."</td>";
			$tableRows.="<td>".$row['type']."</td>";
      $tableRows.="<td>".(empty($providerAction)?"-":($this->Html->Link($row['providerclient'],['controller'=>'third_parties','action'=>$providerAction,$row['providerid']])))."</td>";
      $tableRows.="<td>".($orderAction == 'verEntrada'?($this->Html->Link($row['ordercode'],['controller'=>'orders','action'=>$orderAction,$row['orderid']])):'-')."</td>";
      $tableRows.="<td class='centered number' style='font-weight:bold'>".$row['total_entries']."</td>";
      $tableRows.="<td class='centered number' style='font-weight:bold'>".$row['total_exits']."</td>";
      $tableRows.="<td class='centered number' style='font-weight:bold'>".$currentSaldo."</td>";
		$tableRows.="</tr>";
	}
	
	$totalRow="";
	$totalRow.="<tr class='totalrow'>";
		$totalRow.="<td>Total</td>";
		$totalRow.="<td></td>";
    $totalRow.="<td></td>";
		$totalRow.="<td>Inventario Final</td>";
		$totalRow.="<td class='centered number' style='font-weight:bold'>".$currentInventory['total_entries']."</td>";
    $totalRow.="<td class='centered number' style='font-weight:bold'>".$currentInventory['total_exits']."</td>";

    $totalRow.="<td class='centered number' style='font-weight:bold'>".$currentSaldo."</td>";
	$totalRow.="</tr>";
	
	$inventoryTableBody="<tbody>";
		$inventoryTableBody.="<tr>";
			$inventoryTableBody.="<td></td>";
			$inventoryTableBody.="<td></td>";
			$inventoryTableBody.="<td></td>";
      $inventoryTableBody.="<td></td>";
			$inventoryTableBody.="<td class='centered'>Entrada</td>";
      $inventoryTableBody.="<td class='centered'>Salida</td>";
      $inventoryTableBody.="<td class='centered'>Saldo</td>";
		$inventoryTableBody.="</tr>";
		$inventoryTableBody.=$totalRow.$tableRows.$totalRow;
	$inventoryTableBody.="</tbody>";
	
	$inventoryTable="<table id='kardex_".$product['Product']['name']."'>".$inventoryTableHeader.$inventoryTableBody."</table>";
		
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarKardex',$product['Product']['name']], ['class' => 'btn btn-primary']); 
	echo "<br/>";
	echo "<br/>";

	echo $inventoryTable; 
	$_SESSION['kardex'] = $inventoryTable;
?>