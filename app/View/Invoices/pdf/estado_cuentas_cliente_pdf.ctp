<style>
  @media print{
		.noprint{ display:none;}
	}

	img.resize {
		width:400px; /* you can use % */
		height: auto;
	}
	
	div, span {
		font-size:1em;
	}
  .extraSmall {
    font-size:0.6em;
  }
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
  
  
	.left {
    text-align:left;
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
  .red {
    color:#f00;
  }
  .redBackground{
    background-color:red!important;
    color:#fff;
  }
  
  table {
		width:100%;
	}
  
  table.bordered {
		border-collapse:collapse; 
	}
  
  td.bordered {
    border:1px solid black;
    vertical-align:middle;
    text-align:center;
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
	$sundayDateTime=new DateTime($sundays[$sundayId]);
  $saturdayDateTime=new DateTime($saturdays[$sundayId]);
	$url="img/logo_uno.png";
	$imageurl=$this->App->assetUrl($url);
  
  $output='';
  $output.='<table>';
		$output.='<tr>';
      $output.='<td class="bold" style="width:50%;"><img src="'.$imageurl.'" class="resize"></img></td>';		
      $output.='<td class="right" style="width:50%;">
        <h1 class="centered">'.$enterprises[$enterpriseId].'</h1>
      </td>';
      
			
		$output.='</tr>';
	$output.='</table>';
  
  $output.='<h1 class="centered">Estado de Cuentas </h1>';
  $output.='<h1 class="centered">Período del '.($sundayDateTime->format('d/m/Y')).' al '.($saturdayDateTime->format('d/m/Y')).'</h1>';
  
  if (empty($invoices)){
    $output.='<h2>No hay facturas para esta semana</h2>';
  }
  else {
    $output.='<h2>CLIENTE: '.$clients[$clientId].'</h2>';
  }
  
  $invoiceTableHeader='';
  $invoiceTableHeader.='<thead>';
    $invoiceTableHeader.='<tr>';
      $invoiceTableHeader.='<th>#</th>';  
      $invoiceTableHeader.='<th>Fecha</th>';  
      $invoiceTableHeader.='<th>Factura</th>';  
      $invoiceTableHeader.='<th class="centered">Concepto</th>';  
      $invoiceTableHeader.='<th class="centered">Monto</th>';  
      $invoiceTableHeader.='<th class="centered">Saldo</th>';  
    $invoiceTableHeader.='</tr>';
  $invoiceTableHeader.='</thead>';
  
  $invoiceTableBodyRows='';
  $invoiceCounter=1;
  foreach ($invoices as $invoice){
    $invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);
  
    $invoiceTableBodyRows.='<tr>';
      $invoiceTableBodyRows.='<td>'.$invoiceCounter.'</td>';
      $invoiceTableBodyRows.='<td>'.$invoiceDateTime->format('d/m/Y').'</td>';  
      $invoiceTableBodyRows.='<td>'.$invoice['Invoice']['invoice_code'].'</td>';  
      $invoiceTableBodyRows.='<td class="centered">CONSUMO de COMBISTIBLE</td>';  
      $invoiceTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amount right">'.number_format($invoice['Invoice']['amount_cs'],2,'.',',').'</span></td>';  
      $invoiceTableBodyRows.='<td class="CScurrency"><span class="currency"></span><span class="amount right">'.number_format($invoice['Invoice']['saldo_cs'],2,'.',',').'</span></td>';  
    $invoiceTableBodyRows.='</tr>';
    $invoiceCounter++;
  }
  $invoiceTableTotalRow='';
  
  $invoiceTableTotalRow.='<tr class="totalrow">';
    $invoiceTableTotalRow.='<td colspan="4">Totales</td>';  
    $invoiceTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amount right">'.number_format($invoiceTotalsArray['Client'][$clientId]['Total']['amount_cs'],2,'.',',').'</span></td>';  
    $invoiceTableTotalRow.='<td class="CScurrency"><span class="currency"></span><span class="amount right">'.number_format($invoiceTotalsArray['Client'][$clientId]['Total']['saldo_cs'],2,'.',',').'</span></td>';  
    
  $invoiceTableTotalRow.='</tr>';
  
  $invoiceTableBody='<tbody>'.$invoiceTableTotalRow.$invoiceTableBodyRows.$invoiceTableTotalRow.'</tbody>';
  $invoiceTable='<table id="Estado_Cuentas_'.$sundays[$sundayId].'_'.$saturdays[$sundayId].'">'.$invoiceTableHeader.$invoiceTableBody.'</table>';
  $output.=$invoiceTable;
  
  $output.="<table class='noborder' style='margin-top:5em;'>";
		$output.="</tr>";
    $output.="<tr style='margin-bottom:2em;'>";
			$output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
				$output.="<div>Elaborado</div>";
				$output.="<br/>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
      $output.="<td  class='centered' style='width:40%;'>";
			$output.="</td>";
      $output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
        $output.="<div>Revisado</div>";
        $output.="<br/>";
        $output.="<div>&nbsp;</div>";
      $output.="</td>";
		$output.="</tr>";
	$output.="</table>";
  
  $output.='<table>';
		$output.='<tr>';
    $output.='<td class="left" style="width:80%;">
        <div>Recibido por cliente:</div>
        <div></div>
      </td>';
      $output.='<td>
        <div> </div>
        <div></div>
      </td>';
    $output.='</tr>';  
    $output.='<tr>';  
			$output.='<td class="left" style="width:20%;">
        <div>Nombre</div>
      </td>';
      $output.='<td style="border-bottom:solid 1px block;width:300px;">
        <div> </div>
      </td>';
      $output.='<td style="width:30%;">
        <div></div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>Firma</div>
      </td>';
      $output.='<td style="border-bottom:solid 1px block;width:300px;">
        <div></div>
      </td>';
      $output.='<td style="width:30%;">
        <div></div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>Cédula</div>
      </td>';
      $output.='<td style="border-bottom:solid 1px block;width:300px;">
        <div></div>
      </td>';
      $output.='<td style="width:30%;">
        <div></div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>Sello</div>
      </td>';
      $output.='<td style="border-bottom:solid 1px block;width:300px;">
        <div></div>
        <div></div>
      </td>';
      $output.='<td style="width:30%;">
        <div></div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>Fecha</div>
      </td>';
      $output.='<td style="border-bottom:solid 1px block;width:300px;">
        <div></div>
      </td>';
      $output.='<td style="width:30%;">
        <div></div>
      </td>';
    $output.='</tr>';
  $output.='</table>';  
  
  echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
