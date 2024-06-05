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
	$invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);
  $dueDateTime=new DateTime($invoice['Invoice']['due_date']);
	$url="img/logo_uno.png";
	$imageurl=$this->App->assetUrl($url);
  
  $output='';
  $output.='<table>';
		$output.='<tr>';
			/*
      $output.='<td class="extraSmall left" style="width:30%;">
        <br/>
        <div>Dirección: Km 10.1 Carretera Nueva León 150 m arriba</div>
        <div>Tell:2299-1123</div>
        <div>'.COMPANY_MAIL.'<div>
        <div>www.ornasa.com</div>
      </td>';
      */
      $output.='<td class="big left" style="width:35%;">
        <br/>
        <div>'.$invoice['Enterprise']['company_name'].'</div>
      </td>';
      $output.='<td class="bold" style="width:38.33%;"><img src="'.$imageurl.'" class="resize"></img></td>';		
			$output.='<td class="bold" style="width:26.66%;">
        <br/>
        <table class="bordered big">
          <thead class="redBackground">
            <tr class="centered" >
              <th>Día</th>
              <th>Mes</th>
              <th>Año</th>
            </tr>
          </thead>
          <tbody class="centered">
            <tr>
              <td>'.$invoiceDateTime->format("d").'</td>
              <td>'.$invoiceDateTime->format("m").'</td>
              <td>'.$invoiceDateTime->format("Y").'</td>
            </tr>
          </tbody>
        </table>  
      </td>';
		$output.='</tr>';
	$output.='</table>';
  
  $output.='<table>';
		$output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>'.__("Invoice Date").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoiceDateTime->format('d-m-Y').'</div>
      </td>';
      $output.='<td class="left" style="width:20%;">
        <div>'.__("Invoice Code").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['Invoice']['invoice_code'].'</div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>'.__("Due Date").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$dueDateTime->format('d-m-Y').'</div>
      </td>';
      $output.='<td class="left" style="width:20%;">
        <div>'.__("Payment Mode").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['PaymentMode']['name'].'</div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>'.__("Shift").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['Shift']['name'].'</div>
      </td>';
      $output.='<td class="left" style="width:20%;">
        <div>'.__("Operator").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['Operator']['name'].'</div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>'.__("Client").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['Client']['company_name'].'</div>
      </td>';
      $output.='<td class="left" style="width:20%;">
        <div>'.__("Registrado por").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>'.$invoice['CreatingUser']['username'].'</div>
      </td>';
    $output.='</tr>';
    $output.='<tr>';
			$output.='<td class="left" style="width:20%;">
        <div>'.__("Precio Subtotal").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        <div>C$ '.number_format($invoice['Invoice']['sub_total_price'],2,".",",").'</div>
      </td>';
      /*
      $output.='<td class="left" style="width:20%;">
        <div>'.__("Invoice Code").'</div>
      </td>';
      $output.='<td class="" style="width:25%;">
        //<div>'.$invoice['Invoice']['invoice_code'].'</div>
      </td>';
      */
    $output.='</tr>';
  $output.='</table>';  
  
  echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
