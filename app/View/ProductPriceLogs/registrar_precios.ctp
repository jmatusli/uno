<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','.currentMeasurement div input',function(){	
    
	});	
   
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToThree(num) {    
		return +(Math.round(num + "e+3")  + "e-3");
	}
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
  
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
    $('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>

<div class="productpricelogs form fullwidth">
<?php 
  
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Precios de Productos')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-xs-12'>";	
					echo "<div class='col-sm-9 col-lg-6'>";	
						echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
            echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
            echo $this->Form->Submit(__('Cambiar Fecha'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            //echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
            //echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
      if ($enterpriseId == 0){
        echo '<h2 style="clear:left;">Por favor seleccione una gasolinera para ver datos</h2>';
      }
      else {
        echo "<div class='row'>";
          echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices2','name'=>'savePrices2','style'=>'width:300px;']);

          $priceDateTime=new DateTime($priceDateTime);
          foreach ($productTypes as $productType){
            if (!empty($productType['Product'])){ 
              //pr($productType['Product']);
              echo "<div class='col-xs-12 col-lg-6' style='padding:5px;'>";
                echo "<h3>Precios para línea de producto ".$productType['ProductType']['name']." en fecha ".$priceDateTime->format('d-m-Y')."</h3>";
                echo '<p class="info">'.($productType['ProductType']['id']==PRODUCT_TYPE_FUELS?'Precios por litro':'Precios por unidad').'</p>';
                
                $priceTableHead="<thead>";
                  $priceTableHead.="<tr>";
                    $priceTableHead.="<th style='width:100px;'>".__('Product')."</th>";
                    $priceTableHead.="<th style='width:100px;'>".__('Precio')."</th>";
                    $priceTableHead.="<th style='width:100px;'>".__('Fecha precio antes de esta fecha')."</th>";
                    $priceTableHead.="<th style='width:100px;'>".__('Precio anterior')."</th>";
                  $priceTableHead.="</tr>";
                $priceTableHead.="</thead>";
                
                $priceTableBodyRows="";
                foreach ($productType['Product'] as $product){
                  //if ($product['id'] == 1){\
                  //  pr($product);
                  //}
                  $formattedPreviousProductPriceLogDateTime="N/A";
                  if (!empty($product['PreviousProductPriceLog'])){
                    $previousProductPriceLogDateTime=new DateTime($product['PreviousProductPriceLog'][0]['price_datetime']);
                    $formattedPreviousProductPriceLogDateTime=$previousProductPriceLogDateTime->format('d-m-Y H:i:s');
                  }
                  $priceTableBodyRows.="<tr productid=".$product['id'].">";
                    $priceTableBodyRows.="<td>".$this->Html->link($product['name'],['controller'=>'products','action'=>'view',$product['id']])."</td>";
                    $priceTableBodyRows.="<td>".$this->Form->Input('Product.'.$product['id'].'.price',['label'=>false,'type'=>'decimal','value'=>(empty($product['ProductPriceLog'])?0:(float)$product['ProductPriceLog'][0]['price'])])."</td>";
                    $priceTableBodyRows.="<td>".$formattedPreviousProductPriceLogDateTime."</td>";
                    $priceTableBodyRows.="<td>".number_format((empty($product['PreviousProductPriceLog'])?0:$product['PreviousProductPriceLog'][0]['price']),2,'.',',')."</td>";
                  $priceTableBodyRows.="</tr>";
                }  
                  
                $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";                  
                
              $priceTable="<table id='precios_".$productType['ProductType']['name']."_".$priceDateTime->format('Ymd')."'>".$priceTableHead.$priceTableBody."</table>";
               echo $priceTable;
               echo "</div>";  
            }
          }
          echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices','name'=>'savePrices','style'=>'width:300px;']);
        echo "</div>"; 
      }  
      echo $this->Form->end();
    echo "</div>";
  echo "</div>";
echo "</fieldset>";
?>
</div>

<script>
	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#MoseMeasurementMeasurementDateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#HoseMeasurementMeasurementDateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#HoseMeasurementMeasurementDateDay').val(daysInPreviousMonth);
		$('#HoseMeasurementMeasurementDateMonth').val(previousMonth);
		$('#HoseMeasurementMeasurementDateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#HoseMeasurementMeasurementDateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#HoseMeasurementMeasurementDateYear').val());
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
		$('#HoseMeasurementMeasurementDateDay').val(daysInNextMonth);
		$('#HoseMeasurementMeasurementDateMonth').val(nextMonth);
		$('#HoseMeasurementMeasurementDateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
</script>