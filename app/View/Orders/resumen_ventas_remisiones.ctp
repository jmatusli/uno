<div class="orders index sales fullwidth">
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
  
  function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
    formatPercentages();
	});
</script>
<?php 
  $salestableheader="<thead>";
		$salestableheader.="<tr>";
			$salestableheader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salestableheader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$salestableheader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$salestableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase A')."</th>";
			$salestableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Tapones')."</th>";
      if ($userrole != ROLE_SALES){
        $salestableheader.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }
      $salestableheader.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
      $salestableheader.="<th class='centered'>".__('Invoice Price')." C$</th>";
      $salestableheader.="<th class='centered'>".__('Invoice Price')." US$</th>";      
		$salestableheader.="</tr>";
	$salestableheader.="</thead>";
	
	$salesTableHeaderWithActions="<thead>";
		$salesTableHeaderWithActions.="<tr>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('order_code')."</th>";
			$salesTableHeaderWithActions.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase A')."</th>";
			$salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Tapones')."</th>";
      if ($userrole != ROLE_SALES){
        $salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }  
      $salesTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
      $salesTableHeaderWithActions.="<th class='centered'>".__('Invoice Price')." C$</th>";     
      $salesTableHeaderWithActions.="<th class='centered'>".__('Invoice Price')." US$</th>";
      $salesTableHeaderWithActions.="<th class='actions'>".__('Actions')."</th>";
		$salesTableHeaderWithActions.="</tr>";
	$salesTableHeaderWithActions.="</thead>";
	
	$salesTableBodyWithActions=$salestablebody="<tbody>";
  $totalPriceProductsCash=0;
  $totalPriceProductsCredit=0;
	$totalpriceproducts=0;
	$totalinvoicepriceCS=0; 
	$totalinvoicepriceUSD=0; 
  $totalCostCash=0;
  $totalCostCredit=0;
	$totalcost=0;
	$totalproduced=0;
	$totalother=0;
	
	foreach ($sales as $sale){
		//pr($sale);
    
    if ($sale['Invoice']['bool_credit']){
      $totalPriceProductsCredit+=$sale['Order']['total_price'];
      $totalCostCredit+=$sale['Order']['total_cost'];
    }
    else {
      $totalPriceProductsCash+=$sale['Order']['total_price'];
      $totalCostCash+=$sale['Order']['total_cost'];
    }
  
    $totalpriceproducts+=$sale['Order']['total_price'];
    $totalcost+=$sale['Order']['total_cost'];
    
    if (($sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CASH) || (!$sale['Invoice']['bool_credit'] && $paymentOptionId !=INVOICES_CREDIT)){
      
      if (!empty($sale['Invoice']['Currency'])){
        if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
          $totalinvoicepriceCS+=$sale['Invoice']['total_price']; 
        }
        elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
          $totalinvoicepriceUSD+=$sale['Invoice']['total_price']; 
        }
      }

      $totalproduced+=$sale['Order']['quantity_produced'];
      $totalother+=$sale['Order']['quantity_other'];

      $orderdate=new DateTime($sale['Order']['order_date']);
      $invoiceCode=$sale['Order']['order_code'].(($sale['Invoice']['bool_annulled']==1)?" (Anulado)":"");
        
      // for the excel
      $salestablebody.="<tr".($sale['Invoice']['bool_credit']?"":" style='background-color:#f00;'").">";
        $salestablebody.="<td>".$orderdate->format('d-m-Y')."</td>";
        $salestablebody.="<td>".$invoiceCode."</td>";
        $salestablebody.="<td>".$sale['ThirdParty']['company_name']."</td>";        
        $salestablebody.="<td class='centered'>".round($sale['Order']['quantity_produced'],4)."</td>";
        $salestablebody.="<td class='centered'>".round($sale['Order']['quantity_other'],4)."</td>";
        if ($userrole != ROLE_SALES){
          $salestablebody.="<td class='centered'>".round($sale['Order']['total_cost'],2)."</td>";
        }
        $salestablebody.="<td class='centered'>".round($sale['Order']['total_price'],2)."</td>";
        if (!empty($sale['Invoice']['Currency'])){
          if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
            $salestablebody.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
            $salestablebody.="<td class='centered'>-</td>";
          }
          elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
            $salestablebody.="<td class='centered'>-</td>";
            $salestablebody.="<td class='centered'>".round($sale['Invoice']['total_price'],2)."</td>";
          }
        }
        else {
          $salestablebody.="<td class='centered'>-</td>";
          $salestablebody.="<td class='centered'>-</td>";
        }

      $salestablebody.="</tr>";
      if ($sale['Invoice']['bool_annulled']==1){
        $salesTableBodyWithActions.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'")." class='italic'>";		
      }
      else {
        $salesTableBodyWithActions.="<tr".($sale['Invoice']['bool_credit']?"":" style='color:#f00;'").">";		
      }
        $salesTableBodyWithActions.="<td>".$orderdate->format('d-m-Y')."</td>";
        $salesTableBodyWithActions.="<td>".($bool_sale_view_permission?$this->Html->link($invoiceCode, ['action' => 'verVenta', $sale['Order']['id']]):$invoiceCode)."</td>";
        
        $salesTableBodyWithActions.="<td>".($userrole != ROLE_SALES?$this->Html->link($sale['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']]):$sale['ThirdParty']['company_name'])."</td>";
        $salesTableBodyWithActions.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_produced']."</span></td>";
        $salesTableBodyWithActions.="<td class='centered number'><span class='amountright'>".$sale['Order']['quantity_other']."</span></td>";
        if ($userrole != ROLE_SALES){
          $salesTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_cost']."</span></td>";
        }
        $salesTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_price']."</span></td>";
        if (!empty($sale['Invoice']['Currency'])){
          if ($sale['Invoice']['Currency']['id']==CURRENCY_CS){
            $salesTableBodyWithActions.="<td class='centered  CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
            $salesTableBodyWithActions.="<td class='centered'>-</td>";
          }
          elseif ($sale['Invoice']['Currency']['id']==CURRENCY_USD){
            $salesTableBodyWithActions.="<td class='centered'>-</td>";
            $salesTableBodyWithActions.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
          }
        }
        else {
          $salesTableBodyWithActions.="<td class='centered'>-</td>";
          $salesTableBodyWithActions.="<td class='centered'>-</td>";
        }

        $salesTableBodyWithActions.="<td class='actions'>";
          $orderCode=str_replace(' ','',$sale['Order']['order_code']);
          $orderCode=str_replace('/','',$orderCode);
          $filename='Venta_Factura_'.$orderCode;
          //if ($userrole==ROLE_ADMIN) { 
          if ($bool_sale_edit_permission){
            $salesTableBodyWithActions.=$this->Html->link(__('Edit'), array('action' => 'editarVenta', $sale['Order']['id'])); 
            //$salesTableBodyWithActions.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $sale['Order']['id']), array(), __('Are you sure you want to delete exit # %s?', $sale['Order']['order_code']));
            //$salesTableBodyWithActions.=$this->Form->postLink(__('Anular'), array('action' => 'anularVenta', $sale['Order']['id']), array(), __('Seguro que quiere anular la venta # %s?', $sale['Order']['order_code']));
          }
          if ($userrole != ROLE_SALES){
            $salesTableBodyWithActions.=$this->Html->link(__('Pdf'), array('action' => 'verPdfVenta','ext'=>'pdf',$sale['Order']['id'],$filename));	
          }
          $salesTableBodyWithActions.=$this->Html->link(__('Imprimir'), array('action' => 'imprimirVenta', $sale['Order']['id'])); 
        $salesTableBodyWithActions.="</td>";
      $salesTableBodyWithActions.="</tr>";
    }
	}
	//echo "total price USD is ".$totalpriceUSD."<br/>";
	
  
	
	$totalrow="<tr class='totalrow'>";
		$totalrow.="<td>Total C$</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td></td>";
		$totalrow.="<td class='centered number'><span class='amountright'>".$totalproduced."</span></td>";
		$totalrow.="<td class='centered number'><span class='amountright'>".$totalother."&nbsp;</span></td>";
    
    switch ($paymentOptionId){
      case INVOICES_ALL:
        if ($userrole != ROLE_SALES){
          $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalcost."</span></td>";
        }
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalpriceproducts."</span></td>";
        break;
      case INVOICES_CASH:
        if ($userrole != ROLE_SALES){
          $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCash."</span></td>";
        }
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCash."</span></td>";
        break;
      case INVOICES_CREDIT:
        if ($userrole != ROLE_SALES){
          $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCostCredit."</span></td>";
        }
        $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceProductsCredit."</span></td>";
        break;
    }
    
    $totalrow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalinvoicepriceCS."</span></td>";
    $totalrow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalinvoicepriceUSD."</span></td>";
    
		$totalrow.="<td></td>";
	$totalrow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
    $totalRowForExcel.="<td>Total C$</td>";
    $totalRowForExcel.="<td></td>";
    $totalRowForExcel.="<td></td>";
    
    $totalRowForExcel.="<td class='centered'>".round($totalproduced,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalother,2)."</td>";
    if ($userrole != ROLE_SALES){
      $totalRowForExcel.="<td class='centered'>".round($totalcost,2)."</td>";
    }
    $totalRowForExcel.="<td class='centered'>".round($totalpriceproducts,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalinvoicepriceCS,2)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalinvoicepriceUSD,2)."</td>";
	$totalRowForExcel.="</tr>";
	
	
	$salestablebody=$totalRowForExcel.$salestablebody.$totalRowForExcel."</tbody>";
	$salesTableBodyWithActions=$totalrow.$salesTableBodyWithActions.$totalrow."</tbody>";
	
	$salesTableWithActions="<table cellpadding='0' cellspacing='0' id='ventas'>".$salesTableHeaderWithActions.$salesTableBodyWithActions."</table>";
	$salestable="<table cellpadding='0' cellspacing='0' id='ventas'>".$salestableheader.$salestablebody."</table>";
	
	$remissionstableheader="<thead>";
		$remissionstableheader.="<tr>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$remissionstableheader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase B')."</th>";
			$remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase C')."</th>";
      if ($userrole != ROLE_SALES){
        $remissionstableheader.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }
      $remissionstableheader.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
      $remissionstableheader.="<th class='centered'>".__('Remission Price')." C$</th>";
      $remissionstableheader.="<th class='centered'>".__('Remission Price')." US$</th>";
      
		$remissionstableheader.="</tr>";
	$remissionstableheader.="</thead>";
	
	$remissionsTableHeaderWithActions="<thead>";
		$remissionsTableHeaderWithActions.="<tr>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('order_code')."</th>";
			$remissionsTableHeaderWithActions.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase B')."</th>";
			$remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Cantidad Envase C')."</th>";
      if ($userrole != ROLE_SALES){
        $remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('Costo Total')."</th>";
      }  
      $remissionsTableHeaderWithActions.="<th class='centered'>".$this->Paginator->sort('total_price')."</th>";
      $remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." C$</th>";
      $remissionsTableHeaderWithActions.="<th class='centered'>".__('Remission Price')." US$</th>";
			$remissionsTableHeaderWithActions.="<th class='actions'>".__('Actions')."</th>";
		$remissionsTableHeaderWithActions.="</tr>";
	$remissionsTableHeaderWithActions.="</thead>";
	
	$remissionsTableBodyWithActions=$remissionstablebody="<tbody>";
	
	$totalpriceproductsremissions=0; 
	$totalremissionpriceCS=0; 
	$totalremissionpriceUSD=0; 
	$totalcostremissions=0;
	$totalproduced=0;
	$totalother=0;
		
	foreach ($remissions as $sale){
    $totalpriceproductsremissions+=$sale['Order']['total_price'];
    //pr($sale);
    if (!empty($sale['CashReceipt']['Currency'])){
      if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
        $totalremissionpriceCS+=$sale['CashReceipt']['amount']; 
      }
      elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
        $totalremissionpriceUSD+=$sale['CashReceipt']['amount']; 
      }
    }
    if ($userrole != ROLE_SALES){
      $totalcostremissions+=$sale['Order']['total_cost'];
    }
		$totalproduced+=$sale['Order']['quantity_produced_B'];
		$totalother+=$sale['Order']['quantity_produced_C'];

		$orderdate=new DateTime($sale['Order']['order_date']);
		$invoiceCode=$sale['Order']['order_code'];
		if ($sale['CashReceipt']['bool_annulled']==1){
			$invoiceCode.=" (Anulado)";
		}
		
		if ($sale['CashReceipt']['bool_annulled']==1){
			$remissionstablebody.="<tr class='italic'>";		
		}
		else {
			$remissionstablebody.="<tr>";		
		}
			$remissionstablebody.="<td>".$orderdate->format('d-m-Y')."</td>";
			$remissionstablebody.="<td>".$invoiceCode."</td>";
			$remissionstablebody.="<td>".$sale['ThirdParty']['company_name']."</td>";
			$remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_B'],4)."</td>";
			$remissionstablebody.="<td class='centered'>".round($sale['Order']['quantity_produced_C'],4)."</td>";
      if ($userrole != ROLE_SALES){
        $remissionstablebody.="<td class='centered'>".round($sale['Order']['total_cost'],4)."</td>";
      }
      $remissionstablebody.="<td class='centered'>".round($sale['Order']['total_price'],4)."</td>";
      if (!empty($sale['CashReceipt']['Currency'])){
        if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
          $remissionstablebody.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
          $remissionstablebody.="<td class='centered'>-</td>";
        }
        elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
          $remissionstablebody.="<td class='centered'>-</td>";
          $remissionstablebody.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
        }
      }
      else {
        $remissionstablebody.="<td class='centered'>-</td>";
        $remissionstablebody.="<td class='centered'>-</td>";
      }
		$remissionstablebody.="</tr>";
		
		if ($sale['CashReceipt']['bool_annulled']==1){
			$remissionsTableBodyWithActions.="<tr class='italic'>";		
		}
		else {
			$remissionsTableBodyWithActions.="<tr>";		
		}
		$remissionsTableBodyWithActions.="<td>".$orderdate->format('d-m-Y')."</td>";
		$remissionsTableBodyWithActions.="<td>".($bool_remission_view_permission?$this->Html->link($invoiceCode, ['action' => 'verRemision', $sale['Order']['id']]):$invoiceCode)."</td>";
		$remissionsTableBodyWithActions.="<td>".($userrole != ROLE_SALES?$this->Html->link($sale['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $sale['ThirdParty']['id']]):$sale['ThirdParty']['company_name'])."</td>";
		$remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_B']."</td>";
		$remissionsTableBodyWithActions.="<td class='centered number'>".$sale['Order']['quantity_produced_C']."</td>";
    if ($userrole != ROLE_SALES){
      $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_cost']."</span></td>";
    }
    $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['Order']['total_price']."</span></td>";
    if (!empty($sale['CashReceipt']['Currency'])){
      if ($sale['CashReceipt']['Currency']['id']==CURRENCY_CS){
        $remissionsTableBodyWithActions.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
        $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
      }
      elseif ($sale['CashReceipt']['Currency']['id']==CURRENCY_USD){
        $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
        $remissionsTableBodyWithActions.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$sale['CashReceipt']['amount']."</span></td>";
      }
    }
    else {
      $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
      $remissionsTableBodyWithActions.="<td class='centered'>-</td>";
    }
		$remissionsTableBodyWithActions.="<td class='actions'>";
			$orderCode=str_replace(' ','',$sale['Order']['order_code']);
			$orderCode=str_replace('/','',$orderCode);
			$filename='Remision_'.$orderCode;
			//if ($userrole==ROLE_ADMIN) { 
			if ($bool_remission_edit_permission) { 
				$remissionsTableBodyWithActions.=$this->Html->link(__('Edit'), array('action' => 'editarRemision', $sale['Order']['id'])); 
				//$remissionsTableBodyWithActions.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $sale['Order']['id']), array(), __('Are you sure you want to delete exit # %s?', $sale['Order']['order_code']));
				//$remissionsTableBodyWithActions.=$this->Form->postLink(__('Anular'), array('action' => 'anularRemision', $sale['Order']['id']), array(), __('Seguro que quiere anular la remisión # %s?', $sale['Order']['order_code']));
			} 
			if ($userrole != ROLE_SALES){
        $remissionsTableBodyWithActions.=$this->Html->link(__('Pdf'), array('action' => 'verPdfRemision','ext'=>'pdf',$sale['Order']['id'],$filename));				
      }
		$remissionsTableBodyWithActions.="</td>";
		$remissionsTableBodyWithActions.="</tr>";
	}
	
	$totalrow="<tr class='totalrow'>";
		$totalrow.="<td>Total C$</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td></td>";
		$totalrow.="<td class='centered number'>".$totalproduced."</td>";
		$totalrow.="<td class='centered number'>".$totalother."</td>";
    if ($userrole != ROLE_SALES){
      $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalcostremissions."</span></td>";
    }  
    $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalpriceproductsremissions."</span></td>";
    $totalrow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalremissionpriceCS."</span></td>";
    $totalrow.="<td class='centered USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalremissionpriceUSD."</span></td>";
    $totalrow.="<td></td>";
	$totalrow.="</tr>";
	
	
	$totalRowForExcel="<tr class='totalrow'>";
		$totalRowForExcel.="<td>Total C$</td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td></td>";
		$totalRowForExcel.="<td class='centered'>".round($totalproduced,4)."</td>";
		$totalRowForExcel.="<td class='centered'>".round($totalother,4)."</td>";
    if ($userrole != ROLE_SALES){
      $totalRowForExcel.="<td class='centered'>".round($totalcostremissions,4)."</td>";
    }
    $totalRowForExcel.="<td class='centered'>".round($totalpriceproductsremissions,4)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalremissionpriceCS,4)."</td>";
    $totalRowForExcel.="<td class='centered'>".round($totalremissionpriceCS,4)."</td>";
	$totalRowForExcel.="</tr>";

	
	$remissionstablebody=$totalRowForExcel.$remissionstablebody.$totalRowForExcel."</tbody>";
	$remissionsTableBodyWithActions=$totalrow.$remissionsTableBodyWithActions.$totalrow."</tbody>";
	
	$remissionsTableWithActions="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionsTableHeaderWithActions.$remissionsTableBodyWithActions."</table>";
	$remissionstable="<table cellpadding='0' cellspacing='0' id='remisiones'>".$remissionstableheader.$remissionstablebody."</table>";


	echo "<h2>Ventas y Remisiones</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-5'>";	
        echo $this->Form->create('Report'); 
        echo "<fieldset>"; 
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.payment_option_id',array('label'=>__('Visualizar Contado/Crédito'),'default'=>$paymentOptionId));
        echo "</fieldset>"; 
        if ($userrole != ROLE_SALES){
          echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
          echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
        }
        echo $this->Form->end(__('Refresh')); 
	
        if ($userrole != ROLE_SALES){
          echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenVentasRemisiones'), array( 'class' => 'btn btn-primary')); 
        }
        
      echo "</div>";
			echo "<div class='col-md-5'>";	
				$utilityTable="";
				$totalProductionRuns=0;
				$totalAcceptableRuns=0;
        
        if ($userrole==ROLE_ADMIN){
          $utilitySummaryTableHeader="<thead>";
            $utilitySummaryTableHeader.="<tr>";
              $utilitySummaryTableHeader.="<th>Tipo de Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Precio Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Costo Producto Ventas</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
            $utilitySummaryTableHeader.="</tr>";
          $utilitySummaryTableHeader.="</thead>";
          
					$utilityTableRows="";
          $utilityTableRows.="<tr>";
            $utilityTableRows.="<td>Ventas Contado</td>";  
            
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalPriceProductsCash."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalCostCash."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($totalPriceProductsCash-$totalCostCash)."</span></td>";  
            if (!empty($totalPriceProductsCash)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceProductsCash-$totalCostCash)/$totalPriceProductsCash)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          $utilityTableRows.="<tr>";
            $utilityTableRows.="<td>Ventas Crédito</td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalPriceProductsCredit."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalCostCredit."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($totalPriceProductsCredit-$totalCostCredit)."</span></td>";  
            if (!empty($totalPriceProductsCredit)){
              $utilityTableRows.="<td class='centered percentage'><span>".(100*($totalPriceProductsCredit-$totalCostCredit)/$totalPriceProductsCredit)."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
            }              
          $utilityTableRows.="</tr>";
          
          $utilityTableTotalRow="";
          $utilityTableTotalRow.="<tr class='totalrow'>";
            $utilityTableTotalRow.="<td>Todas Ventas</td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalpriceproducts."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".$totalcost."</span></td>";  
            $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($totalpriceproducts-$totalcost)."</span></td>"; 
            if (!empty($totalpriceproducts)){
              $utilityTableTotalRow.="<td class='centered percentage'><span>".(100*($totalpriceproducts-$totalcost)/$totalpriceproducts)."</span></td>";
            }
            else {
              $utilityTableTotalRow.="<td class='centered percentage'><span>0</span></td>";
            }
            $utilityTableTotalRow.="</tr>";
          
					$utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
				$utilityTable.="<table>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
        
        echo "<h2>Utilidad de Ventas</h2>";
				echo $utilityTable;
      }
			echo "</div>";
			echo "<div class='col-md-2'>";	
				echo "<div class='actions fullwidth' style=''>";	
        if ($userrole != ROLE_SALES){
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
            if ($bool_sale_add_permission) {
              echo "<li>".$this->Html->link(__('New Sale'), array('action' => 'crearVenta'))."</li>";
              echo "<br/>";
            }
            if ($bool_remission_add_permission) {
              echo "<li>".$this->Html->link(__('New Remission'), array('action' => 'crearRemision'))."</li>";
              echo "<br/>";
            }
            if ($bool_client_index_permission){
              echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
            }
            if ($bool_client_add_permission) {
              echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
            }
          echo "</ul>";
        }
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			        
?>
</div>
<div class='related'>
<?php
	echo "<h3>Ventas</h3>";
  echo "<p class='comment'>Facturas de contado aparecen <span style='color:#f00;'>en rojo</span></p>";
	echo $salesTableWithActions;
	
	echo "<h3>Remisiones</h3>";
	echo $remissionsTableWithActions;
	
	$_SESSION['resumenVentasRemisiones'] = $salestable.$remissionstable;
?>
</div>