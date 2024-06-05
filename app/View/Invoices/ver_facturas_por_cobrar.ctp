<script>
	
	function formatCurrencies(){
		$("td.number span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				var boolnegative=true;
				//$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("-");
			}
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("C$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("C$");
			}
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("US$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("US$");
			}
		});
	};
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>
<div class="invoices index fullwidth">
<?php 
	echo "<h1>".__('Reporte de Facturas por Cobrar para Cliente ').$client['ThirdParty']['company_name']."</h1>";	
  
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-6">';
        echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
				echo $this->Form->end(__('Refresh'));
        if ($enterpriseId > 0){
          $fileName=$enterprises[$enterpriseId].'_'.date('d_m_Y').'_Facturas_Cobrar_'.str_replace(' ', '_', $client['ThirdParty']['company_name']).'_.xlsx';
          //echo "fileName is ".$fileName."<br/>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarFacturasPorCobrar',$fileName], [ 'class' => 'btn btn-primary']); 
        }
      echo '</div>';
      echo '<div class="col-sm-6">';
        $contactData=(empty($client['ThirdParty']['first_name'])?"":strtoupper($client['ThirdParty']['first_name'])).(empty($client['ThirdParty']['last_name'])?"":(" ".strtoupper($client['ThirdParty']['last_name'])));
        if (!empty($contactData)){
          echo "<p>Contact: ".$contactData."</p>";
        }
        echo (empty($client['ThirdParty']['phone'])?"":("<p>Teléfono: ".$client['ThirdParty']['phone']."</p>"));
      echo '</div>';  
    echo '</div>';  
    echo '<div class="rows">';
      echo '<div class="col-sm-12">';
      if ($enterpriseId == 0){
        echo '<h2>Seleccione una gasolinera para ver datos</h2>';
      }
      else {
        $reportTable="";
        $table_id=substr("facturas_por_cobrar_".$client['ThirdParty']['company_name'],0,31);
        
        $reportTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
          $reportTable.="<thead>";
            $reportTable.="<tr>";
              $reportTable.="<th class='actions'></th>";
              $reportTable.="<th>Fecha de Emisión</th>";
              //$reportTable.="<th>Cliente</th>";
              $reportTable.="<th>Factura/Orden</th>";
              $reportTable.="<th class='centered'>Precio Total</th>";
              $reportTable.="<th class='centered'>Abonado</th>";
              $reportTable.="<th class='centered'>Saldo Pendiente</th>";
              //$reportTable.="<th class='centered'>Diferencia Cambiaria</th>";
              //$reportTable.="<th class='centered'>A pagar</th>";
              $reportTable.="<th>Fecha de Vencimiento</th>";
              $reportTable.="<th>Días de Crédito</th>";
              //$reportTable.="<th class='centered'>1-30</th>";
              //$reportTable.="<th class='centered'>31-60</th>";
              //$reportTable.="<th class='centered'>>60</th>";
            $reportTable.="</tr>";
          $reportTable.="</thead>";
          $reportTable.="<tbody>";
          $facturasPorCobrarBody="";
          $totalCSInvoice=0;
          $totalCSPaid=0;
          $totalCSPending=0;
          $totalCSUnder30=0;
          $totalCSUnder60=0;
          $totalCSOver60=0;
          foreach ($pendingInvoices as $invoice){
            $invoiceDate=new DateTime($invoice['Invoice']['invoice_date']);
            $dueDate=new DateTime($invoice['Invoice']['due_date']);
            $currentDate= new DateTime(date('Y-m-d'));
            $daysLate=$currentDate->diff($invoiceDate);
            //pr($daysLate);
            $currencyClass="CScurrency";
            if ($invoice['Currency']['id']==CURRENCY_USD){
              $currencyClass="USDcurrency";
            }
            $facturasPorCobrarBody.="<tr>";
              $facturasPorCobrarBody.="<td class='actions'>".$this->Html->link('Cancelar Factura', array('controller'=>'cash_receipts','action' => 'add', CASH_RECEIPT_TYPE_CREDIT))."</td>";
              $facturasPorCobrarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
              //$facturasPorCobrarBody.="<td>".$this->Html->link($invoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', CASH_RECEIPT_TYPE_CREDIT))."</td>";
              $facturasPorCobrarBody.="<td>".$this->Html->link($invoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'verVenta', $invoice['Order']['id']))."</td>";
              $facturasPorCobrarBody.="<td class='centered ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['total_price']."</span></td>";
              $facturasPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['paidCS']."</span></td>";
              $facturasPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
              if ($invoice['Currency']['id']==CURRENCY_CS){
                  $totalCSInvoice+=$invoice['Invoice']['total_price'];
              }
              elseif ($invoice['Currency']['id']==CURRENCY_USD) {
                $totalCSInvoice+=$invoice['Invoice']['total_price']*$exchangeRateCurrent;
              }
              $totalCSPaid+=$invoice['Invoice']['paidCS'];
              $totalCSPending+=$invoice['Invoice']['pendingCS'];
              //$facturasPorCobrarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'></span></td>";
              //$facturasPorCobrarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'></span></td>";
              $facturasPorCobrarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
              /*
              if ($daysLate->format('%d')<31){
                $facturasPorCobrarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $totalCSUnder30+=$invoice['Invoice']['pendingCS'];
              }
              else if ($daysLate->format('%d')<61){
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $facturasPorCobrarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $totalCSUnder60+=$invoice['Invoice']['pendingCS'];
              }
              else{
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $facturasPorCobrarBody.="<td class='centered number'>-</td>";
                $facturasPorCobrarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
                $totalCSOver60+=$invoice['Invoice']['pendingCS'];
              }
              */
              $facturasPorCobrarBody.="<td>".$daysLate->format('%a')."</td>";
            $facturasPorCobrarBody.="</tr>";
          }	
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";	
              $totalRow.="<td></td>";	
              $totalRow.="<td></td>";	
              $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSInvoice."</span></td>";
              $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPaid."</span></td>";
              $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
              $totalRow.="<td></td>";	
              $totalRow.="<td></td>";	
              //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder30."</span></td>";
              //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder60."</span></td>";
              //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSOver60."</span></td>";
            $totalRow.="</tr>";
            $reportTable.=$totalRow.$facturasPorCobrarBody.$totalRow;
          $reportTable.="</tbody>";
        $reportTable.="</table>";
        echo $reportTable;
        
        
        $excelTable="";
        $table_id=substr("facturas_por_cobrar_".$client['ThirdParty']['company_name'],0,31);
        $excelTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
          $excelTable.="<thead>";
            $excelTable.="<tr>";
              $excelTable.="<th>Fecha de Emisión</th>";
              $excelTable.="<th>Factura/Orden</th>";
              $excelTable.="<th></th>";
              $excelTable.="<th class='centered'>Precio Total</th>";
              $excelTable.="<th></th>";
              $excelTable.="<th class='centered'>Abonado</th>";
              $excelTable.="<th></th>";
              $excelTable.="<th class='centered'>Saldo Pendiente</th>";
              $excelTable.="<th>Fecha de Vencimiento</th>";
              $excelTable.="<th>Días de Crédito</th>";
            $excelTable.="</tr>";
          $excelTable.="</thead>";
          $excelTable.="<tbody>";
          $facturasPorCobrarBody="";
          $totalCSInvoice=0;
          $totalCSPaid=0;
          $totalCSPending=0;
          $totalCSUnder30=0;
          $totalCSUnder60=0;
          $totalCSOver60=0;
          foreach ($pendingInvoices as $invoice){
            $invoiceDate=new DateTime($invoice['Invoice']['invoice_date']);
            $dueDate=new DateTime($invoice['Invoice']['due_date']);
            $currentDate= new DateTime(date('Y-m-d'));
            $daysLate=$currentDate->diff($invoiceDate);
            //pr($daysLate);
            $facturasPorCobrarBody.="<tr>";
              $facturasPorCobrarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
              $facturasPorCobrarBody.="<td>".$this->Html->link($invoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'viewSale', $invoice['Order']['id']))."</td>";
              $facturasPorCobrarBody.="<td>".$invoice['Currency']['abbreviation']."</td>";
              $facturasPorCobrarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['total_price']."</span></td>";
              $facturasPorCobrarBody.="<td>C$</td>";
              $facturasPorCobrarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['paidCS']."</span></td>";
              $facturasPorCobrarBody.="<td>C$</td>";
              $facturasPorCobrarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
              if ($invoice['Currency']['id']==CURRENCY_CS){
                  $totalCSInvoice+=$invoice['Invoice']['total_price'];
              }
              elseif ($invoice['Currency']['id']==CURRENCY_USD) {
                $totalCSInvoice+=$invoice['Invoice']['total_price']*$exchangeRateCurrent;
              }
              $totalCSPaid+=$invoice['Invoice']['paidCS'];
              $totalCSPending+=$invoice['Invoice']['pendingCS'];
              
              $facturasPorCobrarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
              $facturasPorCobrarBody.="<td>".$daysLate->days."</td>";
            $facturasPorCobrarBody.="</tr>";
          }	
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";	
              $totalRow.="<td></td>";	
              $totalRow.="<td>C$</td>";	
              $totalRow.="<td><span class='amountright'>".$totalCSInvoice."</span></td>";
              $totalRow.="<td>C$</td>";	
              $totalRow.="<td><span class='amountright'>".$totalCSPaid."</span></td>";
              $totalRow.="<td>C$</td>";	
              $totalRow.="<td><span class='amountright'>".$totalCSPending."</span></td>";
              $totalRow.="<td class='centered'></td>";	
              $totalRow.="<td class='centered'></td>";	
            $totalRow.="</tr>";
            $excelTable.=$totalRow.$facturasPorCobrarBody.$totalRow;
          $excelTable.="</tbody>";
        $excelTable.="</table>";
        
        $_SESSION['facturasPorCobrar'] = $excelTable;
      }
      echo '</div>';  
    echo '</div>';
  echo '</div>';  
?>
</div>