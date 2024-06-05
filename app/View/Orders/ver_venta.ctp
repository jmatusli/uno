<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='orders view'>";
  }
  else {
    echo "<div class='orders view fullwidth'>";
  }
	//pr($invoice);
	
	echo "<h2>".__('Exit')." ".$order['Order']['order_code']."</h2>";
	//echo "credit days is ".$creditDays."<br/>";
	echo "<dl>";
		$currencyAbbreviation="C$";
		if (!empty($invoice)){
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$currencyAbbreviation="US$";
			}
		}
		echo "<dt>".__('Sale Date')."</dt>";
		$orderDate=new DateTime($order['Order']['order_date']);
		echo "<dd>".$orderDate->format('d-m-Y')."</dd>";
		echo "<dt>".__('Order Code')."</dt>";
		echo "<dd>".$order['Order']['order_code']."</dd>";
		if (!empty($invoice)){
			//pr($invoice);
			if ($invoice['Invoice']['bool_credit']){
				$dueDate=new DateTime($invoice['Invoice']['due_date']);
				echo "<dt>".__('Due Date')."</dt>";
				echo "<dd>".$dueDate->format('d-m-Y')."</dd>";
			}
		}
		echo "<dt>".__('Client')."</dt>";
    $clientName=($order['ThirdParty']['id']==CLIENTS_VARIOUS?$order['Order']['extra_client_name']:$order['ThirdParty']['company_name']);
		echo "<dd>".($userrole != ROLE_SALES?$this->Html->link($clientName, ['controller' => 'third_parties', 'action' => 'verCliente', $order['ThirdParty']['id']]):$clientName)."</dd>";
		echo "<dt>".__('Comment')."</dt>";
    if (!empty($order['Order']['comment'])){
      echo "<dd>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$order['Order']['comment']))."</dd>";
    }
    else {
      echo "<dd>-</dd>";
    }
		if (!empty($invoice)){
			echo "<dt>".__('Subtotal Factura (sin IVA)')."</dt>";
			echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['sub_total_price'],2,".",",")."</dd>";
			echo "<dt>".__('IVA Factura')."</dt>";
			echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['IVA_price'],2,".",",")."</dd>";
			echo "<dt>".__('Total Factura (con IVA)')."</dt>";
			echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['total_price'],2,".",",")."</dd>";
			if ($invoice['Invoice']['bool_retention']&&!$invoice['Invoice']['bool_credit']){
				echo "<dt>".__('Monto de Retención')."</dt>";
				echo "<dd>".$invoice['Currency']['abbreviation']." ".$invoice['Invoice']['retention_amount']."</dd>";
				echo "<dt>".__('Número de Retención')."</dt>";
				echo "<dd>".$invoice['Invoice']['retention_number']."</dd>";
			}
		}
	echo "</dl>";
	
	echo "<div class='righttop'>";
	if (!empty($invoice)){
		if ($invoice['Invoice']['bool_credit']){	
			echo "<h4>Factura de Crédito</h4>";
			echo "<dl>";
				$invoiceDate=new DateTime($invoice['Invoice']['due_date']);
				echo "<dt>".__('Due Date')."</dt>";
				echo "<dd>".$invoiceDate->format('d-m-Y')."</dd>";
			echo "</dl>";
			if ($invoice['Invoice']['total_price_CS']==$invoice['Invoice']['pendingCS']){
				echo "<div>No se han realizado pagos para esta factura aun</div>";
			}
			else{
				if ($invoice['Invoice']['pendingCS']<=0){
					echo "<div>Esta factura está cancelada.</div>";
				}
				else {
					echo "<div>Saldo pendiente es: C$ ".number_format($invoice['Invoice']['pendingCS'],2,".",",")."</div>";
				}
			}
		}
		else {	
			echo "<h4>Factura de Contado</h4>";
			echo "<div>Pagado a caja ".$invoice['CashboxAccountingCode']['description']."</div>";
		}
		$boolPaid=$invoice['Invoice']['bool_paid'];
		
		if ($boolPaid){
			$statusText="El estado actual de la factura es: PAGADO";
			$changeText="Marcar factura ".$invoice['Invoice']['invoice_code']." como NO PAGADO";
			$confirmText="Está seguro que quiere marcar factura ".$invoice['Invoice']['invoice_code']." como NO PAGADO?";
		}
		else {
			$statusText="El estado actual de la factura es: NO PAGADO";
			$changeText="Marcar factura ".$invoice['Invoice']['invoice_code']." como PAGADO";
			$confirmText="Está seguro que quiere marcar factura ".$invoice['Invoice']['invoice_code']." como PAGADO?";
		}
		echo "<p>".$statusText."</p>";
		echo $this->Html->link($changeText,['controller'=>'invoices','action' => 'changePaidStatus',$invoice['Invoice']['id']], ['confirm' => $confirmText, 'class' => 'btn btn-primary']);
	}
	echo "</div>";
?>
	<br/>
	<button onclick="printContent('printinfo')">Imprime Orden de Salida</button>
	
	<div class="related">
<?php 
	if (!empty($summedMovements)){
		echo "<h3>".__('Products Sold')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Unit Price')."</th>";
					echo "<th class='centered'>".__('Quantity')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Total Price')."</th>";
				echo "</tr>";
			echo "</thead>";
			
			$totalquantity=0;
			$totalprice=0;
			echo "<tbody>";
			foreach ($summedMovements as $summedMovement){ 
				echo "<tr>";
				if ($summedMovement['StockMovement']['production_result_code_id']>0){	
					echo "<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
				}
				else {
					echo "<td>".$summedMovement['Product']['name']."</td>";
				}
				echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</span></td>";
				echo "<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
				echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
				$totalquantity+=$summedMovement[0]['total_product_quantity'];
				$totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
				echo "</tr>";
			}
		
				echo "<tr class='totalrow'>";
					echo "<td>Sub Total</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".($totalquantity>0?number_format($totalprice/$totalquantity,2,".",","):"-")."</span></td>";
					echo "<td class='centered'>".number_format($totalquantity,0,".",",")."</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalprice,2,".",",")."</span></td>";
				echo "</tr>";
				
			echo "</tbody>";

		echo "</table>";
	}
	if (!empty($invoice)){
		if ($invoice['Invoice']['bool_credit']&&!empty($cashReceiptsForInvoice)){
			echo "<h3>".__('Pagos para esta Factura de Crédito')."</h3>";
			echo "<table cellpadding = '0' cellspacing = '0'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>Fecha Recibo</th>";
						echo "<th>Número Recibo</th>";
						echo "<th class='centered' style='width:70px'>Monto Pagado</th>";
					echo "</tr>";
				echo "</thead>";
				
				$totalpaidCS=0;
				echo "<tbody>";
				foreach ($cashReceiptsForInvoice as $cashReceipt){ 
					//pr($cashReceipt);
					$receiptDate=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
					echo "<tr>";
						echo "<td>".$receiptDate->format('d-m-Y')."</td>";
						echo "<td>".$this->Html->Link($cashReceipt['CashReceipt']['receipt_code'],array('controller'=>'cash_receipts','action'=>'view',$cashReceipt['CashReceipt']['id']))."</td>";
						echo "<td class='centered amount'><span class='currency'>".$cashReceipt['Currency']['abbreviation']." </span><span class='amountright'>".number_format($cashReceipt['CashReceiptInvoice']['payment'],2,".",",")."</td>";
						
						if ($cashReceipt['Currency']['id']==CURRENCY_USD){
							$totalpaidCS+=$cashReceipt['CashReceiptInvoice']['payment']*$exchangeRateCurrent;
						}
						else {
							$totalpaidCS+=$cashReceipt['CashReceiptInvoice']['payment'];
						}
					echo "</tr>";
				}
					echo "<tr class='totalrow'>";
						echo "<td>Total</td>";
						echo "<td></td>";
						echo "<td class='centered amount'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalpaidCS,2,".",",")."</span></td>";
					echo "</tr>";
				echo "</tbody>";

			echo "</table>";
		}
	}
?>

</div>


<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='related'>";
    if (!empty($invoice)){
      if (!empty($invoice['AccountingRegisterInvoice'])){
        foreach ($invoice['AccountingRegisterInvoice'] as $accountingRegisterInvoice){
          $accountingRegister=$accountingRegisterInvoice['AccountingRegister'];
          echo "<h3>Comprobante ".$accountingRegister['register_code']."</h3>";
          $accountingMovementTable= "<table cellpadding = '0' cellspacing = '0'>";
            $accountingMovementTable.="<thead>"; 
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<th>".__('Accounting Code')."</th>";
                $accountingMovementTable.= "<th>".__('Description')."</th>";
                $accountingMovementTable.= "<th>".__('Concept')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Debe')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Haber')."</th>";
                //$accountingMovementTable.= "<th></th>";
              $accountingMovementTable.= "</tr>";
            $accountingMovementTable.="</thead>";
            $totalDebit=0;
            $totalCredit=0;
            $accountingMovementTable.="<tbody>";				
            foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
              //pr($accountingMovement);
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['concept']."</td>";
                
                if ($accountingMovement['bool_debit']){
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $totalDebit+=$accountingMovement['amount'];
                }
                else {
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $totalCredit+=$accountingMovement['amount'];
                }
                //$accountingMovementTable.= "<td>".($accountingMovement['bool_debit']?__('Debe'):__('Haber'))."</td>";
                //$accountingMovementTable.= "<td class='actions'>";
                  //$accountingMovementTable.= $this->Html->link(__('View'), array('controller' => 'accounting_movements', 'action' => 'view', $accountingMovement['id'])); 
                
                  //$accountingMovementTable.= $this->Html->link(__('Edit'), array('controller' => 'accounting_movements', 'action' => 'edit', $accountingMovement['id'])); 
                  //$accountingMovementTable.= $this->Form->postLink(__('Delete'), array('controller' => 'accounting_movements', 'action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['id'])); 
                //$accountingMovementTable.= "</td>";
              $accountingMovementTable.= "</tr>";
            } 
            if (!empty($accountingRegister['AccountingMovement'])){
              $accountingMovementTable.= "<tr class='totalrow'>";
                $accountingMovementTable.= "<td>Total</td>";
                $accountingMovementTable.= "<td></td>";
                $accountingMovementTable.= "<td></td>";
                $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalDebit."</span></td>";
                $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalCredit."</span></td>";
              $accountingMovementTable.= "</tr>";
            }
            $accountingMovementTable.= "</tbody>";
          $accountingMovementTable.= "</table>";
          echo $accountingMovementTable;				
        }
      }
    }
    echo "</div>";  
  }
?>	


<div id='printinfo'>
<!-- buscar el  que ha sido asignado con el insert -->
<?php 
  echo "<div id='companytitle'>ORNASA</div>";
  echo "<br/>";
  echo "<br/>";
  
  //visualize the date
  $orderdate=new DateTime($order['Order']['order_date']);
  echo "<div class='orderdate'>";
    echo "<table>";
      echo "<thead>";
        echo "<tr>";
          echo "<th>Día</th>";
          echo "<th>Mes</th>";
          echo "<th>Año</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";
        echo "<tr>";
          echo "<td>".date_format($orderdate,'d')."</td>";
          echo "<td>".date_format($orderdate,'m')."</td>";
          echo "<td>".date_format($orderdate,'Y')."</td>";
        echo "</tr>";
      echo "</tbody>";
    echo "</table>";
  echo "</div>";
  
  echo "<div class='ordertext'><span style='width:15%'>Factura Número:</span>".$order['Order']['order_code']."</div>"; 
  echo "<br/>";
  echo "<div class='ordertext'><span style='width:15%'>Cliente:</span>".$order['ThirdParty']['company_name']."</div>";
  
  if (!empty($summedMovements)){
    echo "<table class='producttable'>";
      echo "<thead>";
        echo "<tr>";		
          echo "<th class='productname'>Producto</th>";
          echo "<th class='unitprice'>Precio Unidad</th>";
          echo "<th class='quantity'>Cantidad</th>";
          echo "<th class='totalprice'>Precio</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";				
  
      $totalquantity=0;
      $totalprice=0;
        foreach ($summedMovements as $summedMovement){ 
          echo "<tr>";
            if ($summedMovement['StockMovement']['production_result_code_id']>0){
              echo "<td class='productname'>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
            }
            else {
              echo "<td class='productname'>".$summedMovement['Product']['name']."</td>";
            }
            
            echo "<td class='unitprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</td>";
            echo "<td class='quantity'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
            echo "<td class='totalprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</td>";
            $totalquantity+=$summedMovement[0]['total_product_quantity'];
            $totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
          echo "</tr>";
        }
        echo "<tr><td class='totaltext bottomrow'>SUB TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>SUB TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>IVA</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($invoice['Invoice']['IVA_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($invoice['Invoice']['total_price'],2,".",",")."</td></tr>";
      echo "</tbody>";
    
    echo "</table>";
  }			
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";	
?>
</div>
</div>
<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='actions'>";
      echo "<h3>". __('Actions')."</h3>";
      echo "<ul>";
        $orderCode=str_replace(' ','',$order['Order']['order_code']);
        $orderCode=str_replace('/','',$orderCode);
        $filename='Factura_'.$orderCode;
        echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'verPdfVenta','ext'=>'pdf',$order['Order']['id'],$filename))."</li>";
        echo "<br/>";
        if ($bool_sale_edit_permission) { 
          echo "<li>".$this->Html->link(__('Edit Sale'), array('action' => 'editarVenta', $order['Order']['id']))."</li>";
        }
        if ($bool_delete_permission) { 		
          echo "<li>".$this->Form->postLink(__('Eliminar Venta'), array('action' => 'delete', $order['Order']['id']), array(), __('Está seguro que quiere eliminar Venta # %s?', $order['Order']['order_code']))."</li>";
          echo "<br/>";
        }
        echo "<li>".$this->Html->link(__('List Sales'), array('action' => 'resumenVentasRemisiones'))."</li>";
        if ($bool_sale_add_permission) { 
          echo "<li>".$this->Html->link(__('New Sale'), array('action' => 'crearVenta'))."</li>";
          echo "<br/>"; 
        } 
        if ($bool_client_index_permission) { 
          echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
        }
        if ($bool_client_add_permission) { 
          echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
        }
      echo "</ul>";
    echo "</div>";
  }
 ?>



<script type="text/javascript">
	<!--
		function printContent(id){
			str=document.getElementById(id).innerHTML
			newwin=window.open('','printwin','left=5,top=5,width=640,height=480')
			newwin.document.write('<HTML>\n<HEAD>\n')
			
			newwin.document.write('<style type="text/css">\n')
			newwin.document.write('#all {font-size:12px;}\n')
			newwin.document.write('#companytitle {font-size:20px;font-weight:bold;}\n')
			newwin.document.write('.ordertext {font-size:14px;font-weight:bold;}\n')
			newwin.document.write('.orderdate {width:20%;float:right;clear:right;}\n')
			newwin.document.write('.producttable {width:100%;}\n')
			newwin.document.write('.productname {width:45%;}\n')
			newwin.document.write('.unitprice {width:15%;}\n')
			newwin.document.write('.quantity {width:20%;}\n')
			newwin.document.write('.totalprice {width:20%;}\n')
			newwin.document.write('td {border:1px solid black;}\n')
			newwin.document.write('td.bottomrow {border:0px;}\n')
			newwin.document.write('td.totaltext {font-weight:bold;}\n')
			newwin.document.write('</style>\n')
			
			newwin.document.write('<script>\n')
			newwin.document.write('function chkstate(){\n')
			newwin.document.write('if(document.readyState=="complete"){\n')
			newwin.document.write('window.close()\n')
			newwin.document.write('}\n')
			newwin.document.write('else{\n')
			newwin.document.write('setTimeout("chkstate()",2000)\n')
			newwin.document.write('}\n')
			newwin.document.write('}\n')
			newwin.document.write('function print_win(){\n')
			newwin.document.write('window.print();\n')
			newwin.document.write('chkstate();\n')
			newwin.document.write('}\n')
			newwin.document.write('<\/script>\n')
			newwin.document.write('</HEAD>\n')
			newwin.document.write('<BODY style="margin:2px;max-height:300px;" onload="print_win()">\n')
			newwin.document.write('<div id="all" style="font-size:11px;">\n')
			newwin.document.write(str)
			newwin.document.write('</div>\n')
			newwin.document.write('</BODY>\n')
			newwin.document.write('</HTML>\n')
			newwin.document.close()
		}
	//-->
</script>
