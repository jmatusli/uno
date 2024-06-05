<?php 
  if (empty($invoices)){
    echo 'No hay facturas registrados para el recibo en que se hizo clic';
  }
  else {
    $tableHead='';
    $tableHead.='<thead>';
      $tableHead.='<tr>';
        $tableHead.='<th>Fecha</th>';
        $tableHead.='<th>Código</th>';
        $tableHead.='<th class="centered">Monto</th>';
        $tableHead.='<th class="actions">Acciones</th>';
      $tableHead.='</tr>';
    $tableHead.='</thead>';
    
    $totalAmount=0;
    $tableRows='';
    
    foreach ($invoices as $invoice){
      $invoiceDateTime=new DateTime($invoice['Invoice']['invoice_date']);
      $totalAmount+=$invoice['Invoice']['sub_total_price'];
      $tableRow='';
      $tableRow.='<tr callingRowId="'.$callingRowId.'">';
        $tableRow.='<td>'.($invoiceDateTime->format('d-m-Y')).'</td>';
        $tableRow.='<td>'.$invoice['Invoice']['invoice_code'].'</td>';
        $tableRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$invoice['Invoice']['sub_total_price'].'</span></td>';
        $tableRow.='<td>';
        //if ($bool_delete_permission && empty($invoice['CashReceiptInvoice'])){
          if ($editingMode && empty($invoice['CashReceiptInvoice'])){
          $tableRow.='<button class="eliminarFactura" invoiceId="'.$invoice['Invoice']['id'].'">Eliminar Factura</button>';
        }
        $tableRow.='</td>';
      $tableRow.='</tr>';
      $tableRows.=$tableRow;
    }
    $totalRow='';
    $totalRow.='<tr class="totalrow">';
      $totalRow.='<td>Total</td>';
      $totalRow.='<td></td>';
      $totalRow.='<td class="centered amount"><span class="currency">C$</span><span class="amount right">'.$totalAmount.'</span></td>';
      $totalRow.='<td></td>';
    $totalRow.='</tr>';
    $tableBody='<tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
    $table='<table>'.$tableHead.$tableBody.'</table>';
    echo $table;
  }