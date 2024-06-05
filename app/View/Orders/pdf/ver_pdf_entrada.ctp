<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$output="";
	$output.="<div class='purchases view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>COMPRA # ".$order['Order']['order_code']."</div>";
	$orderDateTime=new DateTime($order['Order']['order_date']);
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
				$output.="<div>Proveedor:<span class='underline'>".$order['ThirdParty']['company_name']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
				$output.="<div>Fecha:<span class='underline'>".$orderDateTime->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
				$output.="<div>Costo Total: <span class='underline'>C$ ".number_format($order['Order']['total_price'],2,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";	
	
	$output.="<h3>".__('Lote de Inventario para esta Compra')."</h3>";
	if (!empty($order['StockMovement'])){
		$tableHeader="<thead>";
      $tableHeader="<tr>";
        $tableHeader.="<th>".__('Purchase Date')."</th>";
        $tableHeader.="<th>".__('Product')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Quantity')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Und.')."</th>";
        $tableHeader.="<th class='centered' style='min-width:15%;width:15%;'>".__('Total Price')."</th>";
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
      $totalRows.="<td class='centered'>C$ ".number_format($subtotal,2,".",",")."</td>";
    $totalRows.="</tr>";
    $totalRows.="<tr class=''>";
      $totalRows.="<td>IVA</td>";
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
      $totalRows.="<td class='centered'>C$ ".number_format($order['Order']['rent_price'],2,".",",")."</td>";
    $totalRows.="</tr>";
        $totalRows.="<tr class='totalrow'>";
      $totalRows.="<td>Total</td>";
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

	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	