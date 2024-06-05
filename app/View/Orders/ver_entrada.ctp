<div class='orders view'>
<?php 
	$outputHeader="";
	$outputHeader.="<h2>".__('Purchase')." ".$order['Order']['order_code']."</h2>";
  $orderDateTime=new DateTime($order['Order']['order_date']);
  $outputHeader.="<div class='container-fluid'>";
    $outputHeader.="<div class='row'>";
      $outputHeader.="<div class='col-sm-6'>";
        $outputHeader.="<dl class='width100'>";
          $outputHeader.="<dt>".__('Purchase Date')."</dt>";
          $outputHeader.="<dd>".$orderDateTime->format('d-m-Y')."</dd>";
          $outputHeader.="<dt>".__('Order Code')."</dt>";
          $outputHeader.="<dd>".$order['Order']['order_code']."</dd>";
          $outputHeader.="<dt>".__('Invoice Code')."</dt>";
          $outputHeader.="<dd>".$order['Order']['invoice_code']."</dd>";
          $outputHeader.="<dt>".__('Provider')."</dt>";
          $outputHeader.="<dd>".$this->Html->link($order['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verProveedor', $order['ThirdParty']['id']))."</dd>";
          $outputHeader.="<dt>".__('Se aplica IVA?')."</dt>";
          $outputHeader.="<dd>".($order['Order']['bool_iva']?"Sí":"No")."</dd>";
        $outputHeader.="</dl>";
      $outputHeader.="</div>";
      $outputHeader.="<div class='col-sm-6'>";
        $outputHeader.="<dl class='width100'>";
          $outputHeader.="<dt>".__('Subtotal')."</dt>";
          $outputHeader.="<dd>C$ ".number_format($order['Order']['subtotal_price'],2,".",",")."</dd>";
          $outputHeader.="<dt>".__('IVA')."</dt>";
          $outputHeader.="<dd>C$ ".number_format($order['Order']['iva_price'],2,".",",")."</dd>";
          $outputHeader.="<dt>".__('Renta')."</dt>";
          $outputHeader.="<dd>C$ ".number_format($order['Order']['rent_price'],2,".",",")."</dd>";
          $outputHeader.="<dt>".__('Total')."</dt>";
          $outputHeader.="<dd>C$ ".number_format($order['Order']['total_price'],2,".",",")."</dd>";
        $outputHeader.="</dl>";
      $outputHeader.="</div>";
    $outputHeader.="</div>";
  $outputHeader.="</div>";
  echo $outputHeader;
?>
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		$companyName=str_replace(".","",$order['ThirdParty']['company_name']);
		$companyName=str_replace(" ","",$companyName);
		$namepdf="Compra_".$companyName."_".$order['Order']['order_code'];
		echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'verPdfEntrada','ext'=>'pdf', $order['Order']['id'],$namepdf))."</li>";
		echo "<br/>";
		if ($bool_edit_permission) { 
			echo "<li>".$this->Html->link(__('Edit Purchase'), array('action' => 'editarEntrada', $order['Order']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Eliminar Entrada'), array('action' => 'eliminarEntrada', $order['Order']['id']), array(), __('Está seguro que quiere eliminar la entrada # %s?', $order['Order']['order_code']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Purchases'), array('action' => 'resumenEntradas'))."</li>";
		if ($bool_add_permission) { 
			echo "<li>".$this->Html->link(__('New Purchase'), array('action' => 'crearEntrada'))."</li>";
		}
		echo "<br/>";
		if ($bool_provider_index_permission){
			echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))."</li>";
		}
		if ($bool_provider_add_permission) { 
			echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))."</li>";
		} 
	echo "</ul>";
?>
</div>
<?php
	$output="";
	$output.="<div class='related'>";
	$output.="<h3>".__('Lote de Inventario para esta Compra')."</h3>";
	if (!empty($order['StockMovement'])){
    $tableHeader="<thead>";
      $tableHeader="<tr>";
        $tableHeader.="<th>".__('Purchase Date')."</th>";
        $tableHeader.="<th>".__('Product')."</th>";
        $tableHeader.="<th>".__('Lot Identifier')."</th>";
        $tableHeader.="<th class='centered' style='min-width:20%;width:20%;'>".__('Quantity')."</th>";
        $tableHeader.="<th class='centered' style='min-width:20%;width:20%;'>".__('Precio Unitario')."</th>";
        $tableHeader.="<th class='centered' style='min-width:20%;width:20%;'>".__('Total Price')."</th>";
      $tableHeader.="</tr>";
    $tableHeader.="</thead>";
    $tableBody="<tbody>";

    $subtotal=0;
    foreach ($order['StockMovement'] as $stockentry){
      $stockMovementDateTime=new DateTime($stockentry['movement_date']);
      //pr($stockentry);
      if ($stockentry['product_quantity']>0){
        $subtotal+=$stockentry['product_total_price'];
        $outputrow="<tr>";
          $outputrow.="<td>".$stockMovementDateTime->format('d-m-Y')."</td>";
          $outputrow.="<td>".$stockentry['Product']['name']."</td>";
          $outputrow.="<td>".$stockentry['name']."</td>";
          $outputrow.="<td class='centered'>".number_format($stockentry['product_quantity'],0,".",",")."</td>";
          $outputrow.="<td class='centered'>".number_format($stockentry['product_unit_price'],2,".",",")."</td>";
          $outputrow.="<td class='centered'>C$ ".number_format($stockentry['product_total_price'],2,".",",")."</td>";
        $outputrow.="</tr>";
        $tableBody.=$outputrow;
      }
    }
    $totalRows="";
    $totalRows.="<tr class='totalrow'>";
      $totalRows.="<td>Subtotal</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($subtotal,2,".",",")."</td>";
    $totalRows.="</tr>";
    $totalRows.="<tr class=''>";
      $totalRows.="<td>IVA</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['iva_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
    $totalRows.="<tr class=''>";
      $totalRows.="<td>Renta</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['rent_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
        $totalRows.="<tr class='totalrow'>";
      $totalRows.="<td>Total</td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
    
    $tableBody.=$totalRows;
    $tableBody.="</tbody>";
		$table="<table cellpadding = '0' cellspacing = '0'>".$tableHeader.$tableBody."</table>";
    $output.=$table;
  }
  $output.="</div>";
  echo $output;
	$_SESSION['output_compra']=$output;
?>

</div>