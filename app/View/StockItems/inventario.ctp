<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
  
  $('body').on('click','.stockQuantity',function(){
    $(this).attr('readonly',false);
    //var thisRow=$(this).closest('tr');
	});
  
  $('body').on('blur','.stockQuantity',function(){
    $(this).attr('readonly',true);
		//var thisRow=$(this).closest('tr');
	});
  
  $('body').on('keypress','.stockQuantity',function(e){
    if(e.which == 13) { // Checks for the enter key
      $(this).trigger('blur');
    }
	});
  
  $('body').on('click','.stockQuantityModal',function(){
    //var currentStockQuantity=$(this).val();
    var thisRow=$(this).closest('tr');
    var currentAverageCost=thisRow.find('td.averageCost').find('span.amountright').html();
    var fullProductName=thisRow.find('span.fullproductname').html()+" A";
    
    $('#changeStockQuantityProductId').val($(this).val());
    $('#changeStockQuantityRawMaterialId').val($(this).val());
    $('#changeStockQuantityProductQualityId').val($(this).val());
    
    $('#changeStockQuantityProductName').val(fullProductName);
    
    $('#changeStockQuantityOriginalQuantity').val($(this).val());
    $('#changeStockQuantityUpdatedQuantity').val($(this).val());
    
    $('#changeStockQuantityAverageCost').val(currentAverageCost);
    $('#changeStockQuantityUpdatedCost').val(currentAverageCost);
    $('#changeStockQuantity').modal('show');		
	});
  
  $('body').on('click','#saveChangeStockQuantity',function(){
    /*
    var clientid=$('#EditClientId').val();
		var clientemail=$('#EditClientEmail').val();
    var clientphone=$('#EditClientPhone').val();
    var clientaddress=$('#EditClientAddress').val();
    var clientrucnumber=$('#EditClientRucNumber').val();
    $.ajax({
			url: '<?php echo $this->Html->url('/'); ?>thirdParties/saveexistingclient/',
      data:{"clientid":clientid,"clientemail":clientemail,"clientphone":clientphone,"clientaddress":clientaddress,"clientrucnumber":clientrucnumber},
			cache: false,
			type: 'POST',
			success: function (data) {
				if (data=="1"){
					alert("El cliente se guardó.");
				}
        else {
          console.log(data);
          alert(data);
        }
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
    */
		$('#changeStockQuantity').modal('hide');		
	});
    
  $(document).ready(function(){
		$('.editedStockQuantity').addClass('hidden');
    
    var chartTanks = document.getElementById('tankGraph').getContext('2d');
    var tankChart = new Chart(chartTanks, {
      type: 'bar',
      data: {
        labels: [<?php echo "'".implode("','",$tankData['labels'])."'"; ?>],
        datasets: [{
          label: '<?php echo $tankData['legend']; ?>',
          data: [<?php echo implode(",",$tankData['values']); ?>],
          backgroundColor: [<?php echo "'".implode("','",$tankData['backgroundColors'])."'"; ?>],
          borderColor: [<?php echo "'".implode("','",$tankData['borderColors'])."'"; ?>],
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
  });
  

</script>
<div class="stockItems inventory">
<?php
  echo "<h2>Inventario</h2>"; 
	echo $this->Form->create('Report'); 
	
	echo "<fieldset>"; 
		echo  $this->Form->input('Report.inventorydate',['type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>($userRoleId!=ROLE_SALES?2013:date('Y')-1),'maxYear'=>date('Y')]);
		echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
    echo  $this->Form->input('Report.display_option_id',['label'=>__('Mostrar'),'default'=>$displayOptionId]);
	echo  "</fieldset>";
  if ($userRoleId!=ROLE_SALES){
    echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
    echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
  }
	echo $this->Form->end(__('Refresh')); 
  
  if ($enterpriseId == 0){
    echo '<h2 style="clear:left;">Por favor seleccione una gasolinera para ver datos</h2>';
  }
  else {
    if ($userRoleId!=ROLE_SALES){
      echo "<div class='container-fluid'>";
        echo "<div class='rows'>";
          echo "<div class='col-sm-4'>";
            echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteInventario'], ['class' => 'btn btn-primary']); 
          echo "</div>";
          echo "<div class='col-sm-4'>";
            echo $this->Html->link(__('Hoja de Inventario'), ['action' => 'verPdfHojaInventario','ext'=>'pdf',$inventoryDate,$enterpriseId,$filename],['class' => 'btn btn-primary','target'=>'blank']); 
          echo "</div>";
          if ($userRoleId===ROLE_ADMIN){  
            echo "<div class='col-sm-4'>";
              echo $this->Html->link(__('Ajustes de Inventario'), ['controller'=>'stockMovements','action' => 'registrarAjuste',$enterpriseId],['class' => 'btn btn-primary','target'=>'blank']); 
            echo "</div>";  
          }
        echo "</div>";    
      echo "</div>";      
      echo "<br/>";
    }
  }
  
/*
  echo "<div class='container-fluid'>";
    echo "<div class='rows'>";
      echo "<div class='col-sm-4'>";
        echo "<h2>Tapones</h2>";
      echo "</div>";
      if ($userRoleId===ROLE_ADMIN){  
        echo "<div class='col-sm-4'>";
          echo $this->Html->link(__('Ajustes de Inventario de Tapones'), ['action' => 'ajustesInventario',$enterpriseId,PRODUCT_TYPE_CAP],['class' => 'btn btn-primary','target'=>'blank']); 
        echo "</div>";  
      }
    echo "</div>";  
  echo "</div>";  
	

	$otherMaterialTable="<table id='tapones' cellpadding='0' cellspacing='0'>";
		$otherMaterialTable.="<thead>";
			$otherMaterialTable.="<tr>";
				$otherMaterialTable.="<th>".__('Producto')."</th>";
				if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
					$otherMaterialTable.="<th class='centered'>".__('Average Unit Price')."</th>";
				}
				$otherMaterialTable.="<th class='centered'>".__('Remaining')."</th>";
				if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
					$otherMaterialTable.="<th class='centered'>".__('Total Value')."</th>";
				}
			$otherMaterialTable.="</tr>";
		$otherMaterialTable.="</thead>";
		$otherMaterialTable.="<tbody>";

		$valuetapones=0;
		$quantitytapones=0; 
		$tableRows="";
		foreach ($tapones as $stockItem){
			$remaining="";
			$average="";
			$totalvalue="";
			if ($stockItem['0']['Remaining']!=""){
				$remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
				$packagingunit=$stockItem['Product']['packaging_unit'];
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0){
					$numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
					$leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
					$remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
					if ($leftovers >0){
						$remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
					}
					else {
						$remaining.=")";
					}
				}
				$average=$stockItem['0']['Remaining']>0?number_format($stockItem['0']['Saldo']/$stockItem['0']['Remaining'],4,".",","):0;
				$totalvalue=$stockItem['0']['Saldo'];
				$valuetapones+=$stockItem['0']['Saldo'];
				$quantitytapones+=$stockItem['0']['Remaining'];
			}
			else {
				$remaining= "0";
				$average="0";
				$totalvalue="0";
			}
      if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
        $tableRows.="<tr>";
          if($userRoleId!=ROLE_SALES) {
            $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stock_movements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']])."</td>";
          }
          else {
            $tableRows.="<td>".$stockItem['Product']['name']."</td>";
          }
          if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
            $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
          }
          $tableRows.="<td class='centered'>".$remaining."</td>";
          if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
            $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
          }
        $tableRows.="</tr>";
      }
		}
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				if($quantitytapones>0){
					$avg=$valuetapones/$quantitytapones;
				}
				else {
					$avg=0;
				}
				if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
				}
				$totalRow.="<td class='centered number'>".$quantitytapones."</td>";
				if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuetapones."</span></td>";
				}
			$totalRow.="</tr>";
			$otherMaterialTable.=$totalRow.$tableRows.$totalRow;
		$otherMaterialTable.="</tbody>";
	$otherMaterialTable.="</table>";
	echo $otherMaterialTable;
*/

  if ($enterpriseId > 0){

    //echo "<h2>Suministros</h2>";
	
    $productTables="";
    
    foreach ($productCategories as $productCategory){
      echo "<h2>".$productCategory['ProductCategory']['name']."</h2>";
      foreach ($productCategory['ProductType'] as $productType){
        $productTypeId=$productType['id'];
        $productTable="<table id='".substr($productType['name'],0,30)."' cellpadding='0' cellspacing='0'>";
        $productTable.="<thead>";
          $productTable.="<tr>";
            $productTable.="<th>".__('Product')."</th>";
            if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
              $productTable.="<th class='centered'>".__('Costo Promedio')."</th>";
            }
            $productTable.="<th class='centered'>".__('Remaining')."</th>";
            if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
              $productTable.="<th class='centered'>".__('Total Value')."</th>";
            }
          $productTable.="</tr>";
        $productTable.="</thead>";
        $productTable.="<tbody>";

        $valueSuministros=0;
        $quantitySuministros=0; 
        $tableRows="";
        foreach ($productType['products'] as $stockItem){
          //pr($stockItem);
          $remaining="";
          $average="";
          $totalvalue="";
          if ($stockItem['0']['Remaining']!=""){
            $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
            $packagingunit=$stockItem['Product']['packaging_unit'];
            // if there are products and the value of packaging unit is not 0, show the number of packages
            if ($packagingunit!=0){
              $numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
              $leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
              $remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
              if ($leftovers >0){
                $remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
              }
              else {
                $remaining.=")";
              }
            }
            $average=$stockItem['0']['Remaining']>0?number_format($stockItem['0']['Saldo']/$stockItem['0']['Remaining'],4,".",","):0;
            $totalvalue=$stockItem['0']['Saldo'];
            $valueSuministros+=$stockItem['0']['Saldo'];
            $quantitySuministros+=$stockItem['0']['Remaining'];
          }
          else {
            $remaining= "0";
            $average="0";
            $totalvalue="0";
          }
          if ($displayOptionId!=DISPLAY_STOCK || $remaining!="0"){
            $tableRows.="<tr>";
              if($userRoleId!=ROLE_SALES) {
                $tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stock_movements', 'action' => 'verKardex', $stockItem['Product']['id']])."</td>";
              }
              else {
                $tableRows.="<td>".$stockItem['Product']['name']."</td>";
              }
              //$tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], ['controller' => 'stock_movements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']])."</td>";
              //$tableRows.="<td>".$stockItem['Product']['name']."</td>";
              if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
                $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
              }
              $tableRows.="<td class='centered'>".$remaining." ".(empty($stockItem['0']['Unit']['name'])?"Uds":$stockItem['0']['Unit']['abbreviation'])."</td>";
              if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
                $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
              }
            $tableRows.="</tr>";
          }
        }
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";
              if($quantitySuministros>0){
                $avg=$valueSuministros/$quantitySuministros;
              }
              else {
                $avg=0;
              }
              if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
                $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
              }
              $totalRow.="<td class='centered number'>".$quantitySuministros."</td>";
              if($userRoleId!=ROLE_FOREMAN && $userRoleId!=ROLE_SALES) {
                $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valueSuministros."</span></td>";
              }
            $totalRow.="</tr>";
            $productTable.=$totalRow.$tableRows.$totalRow;
          $productTable.="</tbody>";
        $productTable.="</table>";
        echo "<div class='container-fluid'>";
          echo "<div class='row'>";
            if ($userRoleId===ROLE_ADMIN){  
              echo "<div class='col-sm-4'>";
                echo "<h3>".$productType['name']."</h3>";
              echo "</div>";
              echo "<div class='col-sm-4'>";
                echo $this->Html->link(__('Ajustes de Inventario de '.$productType['name']), ['action' => 'ajustesInventario',$enterpriseId,$productTypeId],['class' => 'btn btn-primary','target'=>'blank']); 
              echo "</div>"; 
              echo "<div class='col-sm-4' style='clear:right;'></div>";             
            }
            else {
              echo "<div class='col-sm-12'>";
                echo "<h3>".$productType['name']."</h3>";
              echo "</div>";
            }
            
          echo "</div>"; 
          echo "<div class='row'>";
            if ($productType['id']==PRODUCT_TYPE_FUELS){
              echo "<div class='col-sm-4'>";  
                echo "<canvas id='tankGraph'></canvas>";
              echo "</div>"; 
              echo "<div class='col-sm-8'>";
                echo $productTable;
              echo "</div>";
            }
            else {
              echo "<div class='col-sm-12'>";
                echo $productTable;
              echo "</div>";
            }
          echo "</div>";
        echo "</div>";        
        $productTables.=$productTable;      
      }
    }
    
    
    if ($userRoleId != ROLE_SALES){
      $_SESSION['inventoryReport'] = $productTables;
    }
    else {
      $_SESSION['inventoryReport'] = $productTables;
    }  
  }
  
?>

</div>

<script>
	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#ReportInventorydateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#ReportInventorydateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#ReportInventorydateDay').val(daysInPreviousMonth);
		$('#ReportInventorydateMonth').val(previousMonth);
		$('#ReportInventorydateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#ReportInventorydateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#ReportInventorydateYear').val());
		if (nextMonth==0){
			nextMonth=12;
		}
		if (nextMonth==1){
			nextYear+=1;
		}
		if (nextMonth<10){
			nextMonth="0"+nextMonth;
		}
		var daysInNextMonth=daysInMonth(nextMonth,nextYear);
		$('#ReportInventorydateDay').val(daysInNextMonth);
		$('#ReportInventorydateMonth').val(nextMonth);
		$('#ReportInventorydateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
	
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});
</script>