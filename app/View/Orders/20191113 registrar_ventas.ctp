<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  var noAlerts=true;
  var selectedOperatorConfig=[];
  <?php 
    foreach (array_keys($shifts) as $shiftId){ 
      echo "selectedOperatorConfig[".$shiftId."]=2;";
    }
  ?>
  
	$('body').on('change','#InvoiceCurrencyId',function(){	
		var currencyid=$(this).children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
		calculateTotal();
	});	
  
  
  
  $('body').on('click','.operatorconfig',function(){	
		var operatorConfig=$(this).attr('operatorconfig');
    var shiftId=$(this).attr('shiftid');
    selectedOperatorConfig[shiftId]=operatorConfig;
    $(this).closest('div').find('.operatorcfg').val(operatorConfig);
    //mark selected option
    var tHeadCell=$(this).closest('th')
    markSelectedConfig(tHeadCell,operatorConfig);
   
    //style corresponding inputs
    colorRelevantBackground(shiftId,operatorConfig);
    /*
    $('.operatorCounter').each(function(){
      if ($(this).attr('shiftid') == shiftId){
        var operatorcounter=parseInt($(this).attr('operatorcounter'))
        $(this).val(0);
        switch (operatorConfig){
          case '1':
            $(this).closest('td').css('background-color','#82d7d7')
            if (operatorcounter > 1){
              $(this).addClass('hidden');
            }
            else {
              $(this).removeClass('hidden');
            }
            break;
          case '2':
            if ([1,2,3,4,5,6,10,11,12].includes(operatorcounter)){
              $(this).closest('td').css('background-color','#82d7d7')
              if (operatorcounter > 1){
                $(this).addClass('hidden');
              }
              else {
                $(this).removeClass('hidden');
              }
            }
            else {
              $(this).closest('td').css('background-color','#5fb0f6')
              if (operatorcounter != 13){
                $(this).addClass('hidden');
              }
              else {
                $(this).removeClass('hidden');
              }
            }
            break;
          case '3':
            if (operatorcounter <7){
              $(this).closest('td').css('background-color','#82d7d7')
              if (operatorcounter > 1){
                $(this).addClass('hidden');
              }
              else {
                $(this).removeClass('hidden');
              }
            }
            else {
              if (operatorcounter <13){
                $(this).closest('td').css('background-color','#5fb0f6')
                if (operatorcounter > 7){
                  $(this).addClass('hidden');
                }
                else {
                  $(this).removeClass('hidden');
                }
              }
              else {
                $(this).closest('td').css('background-color','#8291f6')
                if (operatorcounter > 13){
                  $(this).addClass('hidden');
                }
                else {
                  $(this).removeClass('hidden');
                }
              }
              
            }
            
            break;
          case '0':
          default:
            $(this).closest('td').css('background-color','')
        }
      }
    });
    */
	});
  
  function markSelectedConfig(tHeadCell,operatorConfig){
    tHeadCell.find('.operatorconfig').each(function(){
      if ($(this).attr('operatorconfig')== operatorConfig){
        $(this).css('border','solid 2px black');
        $(this).attr('selected',true)
      }
      else {
        $(this).css('border','0px');
        $(this).attr('selected',false)
      }
    });
  }
  function colorRelevantBackground(shiftId,operatorConfig,editingMode=false){
    $('.operatorCounter').each(function(){
      if ($(this).attr('shiftid') == shiftId){
        var operatorcounter=parseInt($(this).attr('operatorcounter'))
        if (!editingMode){
          $(this).val(0);
        }
        switch (operatorConfig){
          case '1':
            $(this).closest('td').css('background-color','#82d7d7')
            if (!editingMode){
              if (operatorcounter > 1){
                $(this).addClass('hidden');
              }
              else {
                $(this).removeClass('hidden');
              }
            }
            
            break;
          case '2':
            if ([1,2,3,4,5,6,10,11,12].includes(operatorcounter)){
              $(this).closest('td').css('background-color','#82d7d7')
              if (!editingMode){
                if (operatorcounter > 1){
                  $(this).addClass('hidden');
                }
                else {
                  $(this).removeClass('hidden');
                }
              }
            }
            else {
              $(this).closest('td').css('background-color','#5fb0f6')
              if (!editingMode){
                if (operatorcounter != 13){
                  $(this).addClass('hidden');
                }
                else {
                  $(this).removeClass('hidden');
                }
              }
            }
            break;
          case '3':
            if (operatorcounter <7){
              $(this).closest('td').css('background-color','#82d7d7')
              if (!editingMode){
                if (operatorcounter > 1){
                  $(this).addClass('hidden');
                }
                else {
                  $(this).removeClass('hidden');
                }
              }
            }
            else {
              if (operatorcounter <13){
                $(this).closest('td').css('background-color','#5fb0f6')
                if (!editingMode){
                  if (operatorcounter > 7){
                    $(this).addClass('hidden');
                  }
                  else {
                    $(this).removeClass('hidden');
                  }
                }
              }
              else {
                $(this).closest('td').css('background-color','#8291f6')
                if (!editingMode){
                  if (operatorcounter > 13){
                    $(this).addClass('hidden');
                  }
                  else {
                    $(this).removeClass('hidden');
                  }
                }
              }
              
            }
            
            break;
          case '0':
          default:
            $(this).closest('td').css('background-color','')
        }
      }
    });
  }
  
  
  $('body').on('change','.operatorCounter',function(){	
    var operatorConfig=0;
    $(this).closest('table').find('thead th.operatorconfiguration input.operatorconfig').each(function(){
      if ($(this).attr('selected')){
        operatorConfig=$(this).attr('operatorconfig')
      }
    });
    var operatorValue=$(this).val();
    var operatorCounter=parseInt($(this).attr('operatorcounter'));
    $(this).closest('table').find('.operatorCounter').each(function(){
      switch (operatorConfig){
        case '1':
          $(this).val(operatorValue); 
          break;
        case '2':
          var currentOperatorCounter = parseInt($(this).attr('operatorcounter'))
          if (operatorCounter == 1){
            if ( [1,2,3,4,5,6,10,11,12].includes(currentOperatorCounter)){
              $(this).val(operatorValue); 
            } 
          }
          if (operatorCounter == 13){
            if ( [7,8,9,13,14,15,16,17,18].includes(currentOperatorCounter)){
              $(this).val(operatorValue); 
            } 
          }
          
          break;
        case '3':
          var currentOperatorCounter = parseInt($(this).attr('operatorcounter'))
          if (operatorCounter == 1){
            if ( currentOperatorCounter > 1 && currentOperatorCounter < 7){
              $(this).val(operatorValue); 
            } 
          }
          else {
            if (operatorCounter == 7){
              if ( currentOperatorCounter > 7 && currentOperatorCounter < 13){
                $(this).val(operatorValue); 
              } 
            }
            else {
              if (operatorCounter == 13){
                if ( currentOperatorCounter > 13 && currentOperatorCounter < 19){
                  $(this).val(operatorValue); 
                } 
              }
            }
          }
          break;
        case '0':
        default:

      }
    });
    
  });

  
/*
  $('body').on('focus','.quantity div input',function(){	
		var productQuantity=parseFloat($(this).val());
		$(this).attr('previousQuantity',productQuantity);
  });

  $('body').on('change','.quantity div input',function(){	
    var newQuantity=parseFloat($(this).val());
    var previousQuantity = parseFloat($(this).attr('previousQuantity'));
    var diffQuantity =newQuantity-previousQuantity;
    
    var unitprice=parseFloat($(this).closest('tr').find('td.hose input.productunitprice').val());
		$(this).closest('tr').find('td.price div input').val(roundToTwo(unitprice*newQuantity));
    
    var initialquantity=parseFloat($(this).closest('tr').find('td.initial input').val());
    $(this).closest('tr').find('td.final div input').val(roundToTwo(initialquantity+newQuantity));
    
    var hoseid=parseInt($(this).closest('tr').attr('id'))
    var fuelshiftid=parseInt($(this).closest('table').attr('fuelshiftid'))
    var afternoonshiftid=parseInt(<?php echo SHIFT_AFTERNOON; ?>)
    var nightshiftid=parseInt(<?php echo SHIFT_NIGHT; ?>)
    
    if (fuelshiftid < nightshiftid){
      if (fuelshiftid < afternoonshiftid){
        afternoonhoserow=$("table[fuelshiftid='"+afternoonshiftid+"']").find('tbody tr#'+hoseid);
        var initialquantityafternoon=parseFloat(afternoonhoserow.find('td.initial input').val());
        var finalquantityafternoon=parseFloat(afternoonhoserow.find('td.final input').val());
        afternoonhoserow.find('td.initial input').val(roundToTwo(initialquantityafternoon + diffQuantity))
        afternoonhoserow.find('td.final input').val(roundToTwo(finalquantityafternoon + diffQuantity))
      }
      nighthoserow=$("table[fuelshiftid='"+nightshiftid+"']").find('tr#'+hoseid);
      var initialquantitynight=parseFloat(nighthoserow.find('td.initial input').val());
      var finalquantitynight=parseFloat(nighthoserow.find('td.final input').val());
      nighthoserow.find('td.initial input').val(roundToTwo(initialquantitynight + diffQuantity))
      nighthoserow.find('td.final input').val(roundToTwo(finalquantitynight + diffQuantity)) 
    }
		calculateTotalFuels();
	});	
*/  
  $('body').on('focus','.final div input',function(){	
    if (!isNaN($(this).val())){
      var newFinalCounter=parseFloat($(this).val());
      var initialCounter= parseFloat($(this).closest('tr').find('td.initial div input').val());
      if ((newFinalCounter - initialCounter) < -0.001){
        $(this).attr('previouscounter',initialCounter);
      }  
      else {
        $(this).attr('previouscounter',newFinalCounter);
      }
    }
		
  });

  $('body').on('change','.final div input',function(){
    var shiftContainer=$(this).closest('div.shiftContainer');
  
    var newFinalCounter=parseFloat($(this).val());
    var initialCounter= parseFloat($(this).closest('tr').find('td.initial div input').val());
    
    var fuelQuantity=newFinalCounter-initialCounter;
    var unitPrice=parseFloat($(this).closest('tr').find('td.hose input.productunitprice').val());  
    
    var admissibleValue=true
    if (isNaN($(this).val())){
      if (!noAlerts){
        alert('el nuevo valor para el contador final '+($(this).val())+' no es un valor numérico.  Por favor vuelva a registrarlo.');
      }
      admissibleValue=false
    }
    else {
      if ((newFinalCounter - initialCounter) < -0.001){
        if (!noAlerts){
          alert('el nuevo valor para el contador final '+newFinalCounter+' está menor que el valor del contador inicial '+initialCounter+'.  Por favor vuelva a registrarlo.');
        }
        admissibleValue=false
      }
    }
    
    if (!admissibleValue){
      $(this).val(0);
              
      $(this).closest('tr').find('td.quantity div input').val(0);
      $(this).closest('tr').find('td.price div input').val(0);  
      
      var previousFinalCounter = parseFloat($(this).attr('previouscounter'));
      var diffCounter =newFinalCounter-previousFinalCounter;
      var hoseid=parseInt($(this).closest('tr').attr('id'))
      var fuelshiftid=parseInt($(this).closest('table').attr('fuelshiftid'))
      var afternoonshiftid=parseInt(<?php echo SHIFT_AFTERNOON; ?>)
      var nightshiftid=parseInt(<?php echo SHIFT_NIGHT; ?>)
      if (fuelshiftid < nightshiftid){
        if (fuelshiftid < afternoonshiftid){
          afternoonhoserow=$("table[fuelshiftid='"+afternoonshiftid+"']").find('tbody tr#'+hoseid);
          afternoonhoserow.find('td.initial input').val(initialCounter) 
          var finalquantityafternoon=parseFloat(afternoonhoserow.find('td.final input').val());
          fuelQuantity=finalquantityafternoon-initialCounter;
          if (fuelQuantity>0){
            afternoonhoserow.find('td.quantity div input').val(roundToTwo(fuelQuantity));
            afternoonhoserow.find('td.price div input').val(roundToTwo(unitPrice*fuelQuantity));
          }
          else {
            afternoonhoserow.find('td.quantity div input').val(0);
            afternoonhoserow.find('td.price div input').val(0);
          }
        }
        nighthoserow=$("table[fuelshiftid='"+nightshiftid+"']").find('tr#'+hoseid);
        var finalquantitynight=parseFloat(nighthoserow.find('td.final input').val());
        if (finalquantityafternoon>0){
          nighthoserow.find('td.initial input').val(finalquantityafternoon)
          fuelQuantity=finalquantitynight-finalquantityafternoon;
        }
        else {
          nighthoserow.find('td.initial input').val(initialCounter)
          fuelQuantity=finalquantitynight-initialCounter;
        }
        if (fuelQuantity>0){
          nighthoserow.find('td.quantity div input').val(roundToTwo(fuelQuantity));
          nighthoserow.find('td.price div input').val(roundToTwo(unitPrice*fuelQuantity));
        }
        else {
          nighthoserow.find('td.quantity div input').val(0);
          nighthoserow.find('td.price div input').val(0);
        }
      }
    }
    else {
      $(this).closest('tr').find('td.quantity div input').val(roundToTwo(fuelQuantity));
      $(this).closest('tr').find('td.price div input').val(roundToTwo(unitPrice*fuelQuantity));
      
      var previousFinalCounter = parseFloat($(this).attr('previouscounter'));
      var diffCounter =newFinalCounter-previousFinalCounter;
      var hoseid=parseInt($(this).closest('tr').attr('id'))
      var fuelshiftid=parseInt($(this).closest('table').attr('fuelshiftid'))
      var afternoonshiftid=parseInt(<?php echo SHIFT_AFTERNOON; ?>)
      var nightshiftid=parseInt(<?php echo SHIFT_NIGHT; ?>)
      if (fuelshiftid < nightshiftid){
        if (fuelshiftid < afternoonshiftid){
          afternoonhoserow=$("table[fuelshiftid='"+afternoonshiftid+"']").find('tbody tr#'+hoseid);
          var initialquantityafternoon=parseFloat(afternoonhoserow.find('td.initial input').val());
          var finalquantityafternoon=parseFloat(afternoonhoserow.find('td.final input').val());
          
          var newInitialCounter=initialquantityafternoon + diffCounter
          newFinalCounter=0
          afternoonhoserow.find('td.initial input').val(newInitialCounter) 
          if (finalquantityafternoon - initialquantityafternoon > -0.001){
            if (finalquantityafternoon - initialquantityafternoon < diffCounter){
              //in this case the finalquantity becomes smaller than the initialquantity, and there was something wrong with the measurement
              afternoonhoserow.find('td.final input').val(0)
              newFinalCounter=0
              // modify the diffCounter to reset the value of the initial night counter
              //var previousAfternoonCounter=parseFloat(afternoonhoserow.find('td.final input').attr('previouscounter')
              diffCounter=diffCounter + initialquantityafternoon-finalquantityafternoon
              // reset the attribute value to equal the initialquantity
              afternoonhoserow.find('td.final input').attr('previouscounter',(initialquantityafternoon + diffCounter))
            }
            else {
              // do nothing, the value has already been filled out
              //afternoonhoserow.find('td.final input').val(finalquantityafternoon + diffCounter)
              newFinalCounter=finalquantityafternoon
              // quench the diffCounter to not propagate value
              diffCounter=0
              
            }
          }
          else {
            newFinalCounter=newInitialCounter
            afternoonhoserow.find('td.final input').attr('previouscounter',(initialquantityafternoon + diffCounter))
          }
          
          fuelQuantity=newFinalCounter-newInitialCounter;
          if (fuelQuantity>0){
            afternoonhoserow.find('td.quantity div input').val(roundToTwo(fuelQuantity));
            afternoonhoserow.find('td.price div input').val(roundToTwo(unitPrice*fuelQuantity));
          }
          else {
            afternoonhoserow.find('td.quantity div input').val(0);
            afternoonhoserow.find('td.price div input').val(0);
          }
          
        }
        nighthoserow=$("table[fuelshiftid='"+nightshiftid+"']").find('tr#'+hoseid);
        var initialquantitynight=parseFloat(nighthoserow.find('td.initial input').val());
        var finalquantitynight=parseFloat(nighthoserow.find('td.final input').val());
        newInitialCounter=initialquantitynight + diffCounter
        newFinalCounter=0
        nighthoserow.find('td.initial input').val(newInitialCounter)
        if (finalquantitynight - initialquantitynight > -0.001){
          
          if (finalquantitynight - initialquantitynight < diffCounter){
            //in this case the finalquantity becomes smaller than the initialquantity, and there was something wrong with the measurement
            nighthoserow.find('td.final input').val(0)
            newFinalCounter=0
          }
          else {
            // do nothing, the value has already been filled out
            //nighthoserow.find('td.final input').val(finalquantitynight + diffCounter)
            newFinalCounter=finalquantitynight
          }
        }
        else {
          nighthoserow.find('td.final input').attr('previouscounter',(initialquantitynight + diffCounter))
          newFinalCounter=initialquantitynight + diffCounter
        }
        fuelQuantity=newFinalCounter-newInitialCounter;
        if (fuelQuantity>0){
          nighthoserow.find('td.quantity div input').val(roundToTwo(fuelQuantity));
          nighthoserow.find('td.price div input').val(roundToTwo(unitPrice*fuelQuantity));
        }
        else {
          nighthoserow.find('td.quantity div input').val(0);
          nighthoserow.find('td.price div input').val(0);
        }
      }
    }
    calculateTotalFuels(shiftContainer);
	});	

  function calculateTotalFuels(shiftContainer){
		var currencyId=$('#SaleCurrencyId').children("option").filter(":selected").val();
    var currentPrice=0;
		var totalPrice=0;
    
    var shiftId=0
    var fuelId=0;
    var fuelQuantity=0;
    var shiftFuelTotal=0
    
    var fuelQuantities=[]
		//$("table.combustibles tbody tr:not(.hidden)").each(function() {
    shiftContainer.find("table.combustibles").each(function() {
      
      shiftId=$(this).attr('fuelShiftId');
      shiftFuelTotal=0
    <?php 
      foreach ($fuels as $fuel){ 
        echo "fuelQuantities[".$fuel['Product']['id']."]=0;\n";
      }
    ?>
    	$(this).find("tbody tr:not(.hidden)").each(function() {
        var priceInput=$(this).find('td.price div input');
        
        currentPrice = parseFloat($(this).find('td.price div input').val());
        totalPrice = totalPrice + currentPrice;
        
        fuelId=parseInt($(this).find('td.hose input.fuelid').val())
        fuelQuantity= parseFloat($(this).find('td.quantity div input').val())
        fuelQuantities[fuelId]+=fuelQuantity;
        
      });
      
      for (var key in fuelQuantities) {
        $('#Shift'+shiftId+'fuelTotal'+key).html(roundToFour(fuelQuantities[key]));
        shiftFuelTotal+=fuelQuantities[key]
      }
      $('#Shift'+shiftId+'fuelTotal0').html(roundToFour(shiftFuelTotal));
      $('#Shift'+shiftId+'FuelTotalPrice').html(totalPrice);
    });
		
    var calibrationTableId=shiftContainer.find('table.calibrations').attr('id')
		calculateCalibratedPrice(calibrationTableId)
    
		return false;
	}
  
  $('body').on('change','.calibration div input',function(){	
    var calibrationTableId=$(this).closest('table.calibrations').attr('id')
    calculateCalibratedPrice(calibrationTableId);
  });
  
  function calculateCalibratedPrice(calibrationTableId){
    var calibrationRow=$('#'+calibrationTableId).find('tr.calibrations');
    var shiftId=calibrationRow.attr('shiftid');
    
    var calibrationQuantity=0;
    var calibrationTotal=0;
    
    var fuelId=0;
    var fuelTotalQuantity=0;
    
    var netFuelTotalQuantity=0;
    var totalNetFuelTotalQuantity=0;
    
    var fuelPrices=[]
    <?php 
      foreach ($fuels as $fuel){
        echo "fuelPrices[".$fuel['Product']['id']."]=".$fuel['ProductPriceLog'][0]['price'].";\n";
      }
    ?>
    var netFuelTotalPrice=0;
    var totalNetFuelTotalPrice=0;
    
    calibrationRow.find('td div input').each(function(){
      // calculate total calibration
      calibrationQuantity=parseFloat($(this).val())
      calibrationTotal+=calibrationQuantity;
      
      // calculate netTotalFuels
      fuelId=$(this).attr('fuelId');
      fuelTotalQuantity=parseFloat($('#Shift'+shiftId+'fuelTotal'+fuelId).html())
      // calculate net fuel quantities
      netFuelTotalQuantity=roundToFour(fuelTotalQuantity-calibrationQuantity);
      $('#Shift'+shiftId+'NetFuelTotal'+fuelId).val(netFuelTotalQuantity)
      totalNetFuelTotalQuantity+=netFuelTotalQuantity
      // calculate net fuel total price
      netFuelTotalPrice=roundToFour(netFuelTotalQuantity*fuelPrices[fuelId])
      $('#Shift'+shiftId+'NetFuelTotalPrice'+fuelId).val(netFuelTotalPrice)
      totalNetFuelTotalPrice+=netFuelTotalPrice
      
    });
    // display total calibration  
    $('#Shift'+shiftId+'Calibration0').html(roundToFour(calibrationTotal))
    // display total net fuel quantity
    $('#Shift'+shiftId+'TotalNetFuelTotal').val(roundToFour(totalNetFuelTotalQuantity))
    // display net fuel price
    $('#Shift'+shiftId+'TotalNetFuelTotalPrice').val(roundToFour(totalNetFuelTotalPrice))
    // call calculateTotal to take into account the calibrations
    calculateTotal();
  }
  
  var lubricantUnitPrices=<?php  echo json_encode($lubricantPrices); ?>;
  
  $('body').on('change','.lubricantId div select',function(){	
		var lubricantId=$(this).val();
    var lubricantUnitPrice=parseFloat(lubricantUnitPrices[lubricantId])
    $(this).closest('tr').find('td.lubricantUnitPrice div input').val(lubricantUnitPrice)
		var lubricantQuantity=parseFloat($(this).closest('tr').find('td.lubricantQuantity div input').val());
		$(this).closest('tr').find('td.lubricantTotalPrice div input').val(roundToTwo(lubricantUnitPrice*lubricantQuantity));
		calculateTotalLubricants();
	});	
	$('body').on('change','.lubricantQuantity div input',function(){	
    var lubricantQuantity=parseFloat($(this).val());
		var lubricantUnitPrice=parseFloat($(this).closest('tr').find('td.lubricantUnitPrice div input').val());
		$(this).closest('tr').find('td.lubricantTotalPrice div input').val(roundToTwo(lubricantUnitPrice*lubricantQuantity));
		calculateTotalLubricants();
	});	
	
  $('body').on('click','#addLubricant',function(){	
		var tableRow=$('#lubricantes tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeLubricant',function(){	
		var tableRow=$(this).parent().parent().remove();
		calculateTotalLubricants();
	});	
  
	function calculateTotalLubricants(){
		var currencyId=$('#SaleCurrencyId').children("option").filter(":selected").val();
    var currentPrice=0;
		var totalPrice=0;
		$("#lubricantes tbody tr:not(.hidden)").each(function() {
			currentPrice = parseFloat($(this).find('td.lubricantTotalPrice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
		$('#totalPriceLubricants').val(roundToTwo(totalPrice));
		calculateTotal();
		return false;
	}
	
  function calculateTotal(){
		
    var totalPriceFuels=0
    var netPriceCalibrations=0;
    var netPriceFuels=0;
    <?php 
      foreach ($shifts as $shiftId=>$shiftName){
        echo "totalPriceFuels+=roundToFour(parseFloat($('#Shift".$shiftId."FuelTotalPrice').html()))\n";
        foreach ($fuels as $fuel){
          $fuelId=$fuel['Product']['id'];
          $fuelPrice=$fuel['ProductPriceLog'][0]['price'];
          echo "netPriceCalibrations+=roundToFour(parseFloat($('#Shift".$shiftId."Calibration".$fuelId."').val())*$fuelPrice)\n";
        }
        echo "netPriceFuels+=roundToFour(parseFloat($('#Shift".$shiftId."TotalNetFuelTotalPrice').val()))\n";
      }
    ?>
    $('#totalPriceFuels').val(roundToTwo(totalPriceFuels))
    $('#totalNetPriceCalibrations').val(roundToTwo(netPriceCalibrations))
    $('#totalNetPriceFuels').val(roundToTwo(netPriceFuels))
    var totalPriceLubricants=parseFloat($('#totalPriceLubricants').val());
    $('#totalPrice').val(roundToTwo(netPriceFuels + totalPriceLubricants));
		return false;
	}
  
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
  function roundToFour(num) {    
		return +(Math.round(num + "e+4")  + "e-4");
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
	

	$('#OrderOrderDateDay').change(function(){
		updateExchangeRate();
	});	
	$('#OrderOrderDateMonth').change(function(){
		updateExchangeRate();
	});	
	$('#OrderOrderDateYear').change(function(){
		updateExchangeRate();
	});	
	function updateExchangeRate(){
		var orderday=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#OrderExchangeRate').val(exchangerate);
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
			}
		});
	}
  
  $('body').on('change','#chkEditingMode',function(){	
    $('select option').attr('disabled', false);
    if ($(this).is(':checked')){
      $('.operatorId div select').removeClass('fixed')
      $('.final div input').attr('readonly',false)
      $('.calibration div input').attr('readonly',false)
      
      $('.lubricantId div select').removeClass('fixed')
      $('.lubricantQuantity div input').attr('readonly',false)
      <?php if ($daysSinceLastFuelPriceUpdate<7){ ?>
      $('#saveSales').attr('disabled',false)
      <?php } ?>
    }
    else {
      $('.operatorId div select').addClass('fixed')
      $('.final div input').attr('readonly','readonly')
      $('.calibration div input').attr('readonly','readonly')
      
      $('.lubricantId div select').addClass('fixed')
      $('.lubricantQuantity div input').attr('readonly','readonly')
      
      $('#saveSales').attr('disabled',true)      
    }
    $('select.fixed option:not(:selected)').attr('disabled', true);
	});	
	
	$(document).ready(function(){
		$('#OrderOrderDateHour').val('07');
		$('#OrderOrderDateMin').val('00');
		$('#OrderOrderDateMeridian').val('am');
    $('#OrderOrderDateHour').hide();
		$('#OrderOrderDateMin').hide();
		$('#OrderOrderDateMeridian').hide();
		
		var currencyid=$('#InvoiceCurrencyId').children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
    
    $('.final div input').each(function(){
      if ($(this).val()>0){
        //alert ("the final is "+$(this).val());
        $(this).trigger('change');
      }
    });
    // for triggering product changes, alerts are switched off initially
    noAlerts=false
    $('select.fixed option:not(:selected)').attr('disabled', true);
    <?php if ($boolEditingToggleVisible){ ?>
    $('#chkEditingMode').trigger("change");
    <?php }?>
    $('#saving').addClass('hidden');
    var shiftId='0';
    var operatorConfig='0';
    <?php 
      if (!empty($shiftOperatorConfigs)){
        foreach ($shiftOperatorConfigs as $shiftId=>$operatorConfig){
          echo "shiftId='".$shiftId."'\n";
          echo "operatorConfig='".$operatorConfig."'\n";
    ?>
    colorRelevantBackground(shiftId,operatorConfig,true)
    <?php  
        }
      }
    ?>  
    <?php  if ($boolEditingMode && !$buttonDisabled){?>
      $('#saveSales').css("background-color","#62af56");
      $('#saveSales').removeAttr('disabled');
    <?php  }
    else {?>
      $('#saveSales').css("background-color","#888888");
    <?php  }?>
    
    
    <?php 
      if ($priceUpdateNeeded){
        echo 'alert("Se vencieron los precios de los combustibles, por favor registrarlos ya!")';
      }
    ?>
	});
  
  $('body').on('click','#saveSales',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#OrderRegistrarVentasForm',function(e){	
    if($("#saveSales").data('clicked'))
    {
    
      $('#saveSales').attr('disabled', 'disabled');
      $('#saveSales').css("background-color","#888888");
      $('#saveSales').val("Guardando datos...");
      
      $("#mainform").fadeOut();
      $("#saving").removeClass('hidden');
      $("#saving").fadeIn();
      var opts = {
          lines: 12, // The number of lines to draw
          length: 7, // The length of each line
          width: 4, // The line thickness
          radius: 10, // The radius of the inner circle
          color: '#000', // #rgb or #rrggbb
          speed: 1, // Rounds per second
          trail: 60, // Afterglow percentage
          shadow: false, // Whether to render a shadow
          hwaccel: false // Whether to use hardware acceleration
      };
      var target = document.getElementById('saving');
      var spinner = new Spinner(opts).spin(target);
    }
    
    return true;
  });
</script>

<div class="orders form sales fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la venta...</p>";
    echo "</div>";
  echo "</div>";
  
	echo $this->Form->create('Order');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Registrar Ventas')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
        //pr($saleDate);
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-8 col-lg-6'>";	
						echo  $this->Form->input('enterprise_id',['label'=>__('Enterprise'),'default'=>0]);
            echo $this->Form->input('order_date',['label'=>__('Date'),'default'=>$saleDate,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->Form->Submit(__('Cambiar Fecha'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);

						echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
						echo $this->Form->input('exchange_rate',['default'=>$exchangeRateOrder,'class'=>'narrow','readonly'=>'readonly']);
						echo $this->Form->input('comment',['type'=>'textarea','rows'=>2]);
            echo $this->Form->input('Order.currency_id',['default'=>CURRENCY_CS,'empty'=>['0'=>'Seleccione Moneda'],'class'=>'narrow fixed']);
            //echo $this->Form->input('Invoice.bool_retention',['type'=>'checkbox','label'=>'Retención']);
						//echo $this->Form->input('Invoice.retention_number',['label'=>'Número Retención']);
						//echo $this->Form->input('Invoice.bool_IVA',['type'=>'checkbox','label'=>'Se aplica IVA','checked'=>'checked']);
					echo "</div>";
					echo "<div class='col-sm-4 col-lg-6 totals'>";
            //echo $this->Form->Submit(__('Actualizar Inventario'),['id'=>'refresh','name'=>'refresh']);
						//echo  $this->Form->input('inventory_display_option_id',['label'=>__('Mostrar Inventario'),'default'=>$inventoryDisplayOptionId]);
            //echo $this->Form->Submit(__('Mostrar/Esconder Inventario'),['id'=>'showinventory','name'=>'showinventory']);
            echo "<h3>".__('Fuel Prices')."</h3>";
            echo "<dl id='fuelPrices'>";
            foreach ($fuels as $fuel){
              echo "<dt>".$fuel['Product']['name']."</dt>";
              echo "<dd>".$fuel['ProductPriceLog'][0]['Currency']['abbreviation']." ".$fuel['ProductPriceLog'][0]['price']."</dd>";
            }
            echo "</dl>";
            echo "<h4>".__('Totales de Venta')."</h4>";			
						echo $this->Form->input('Order.total_price_fuels',['label'=>__('Total Combustibles'),'id'=>'totalPriceFuels','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('Order.total_price_calibrations',['label'=>__('Valor Calibraciones'),'id'=>'totalNetPriceCalibrations','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('Order.total_net_price_fuels',['label'=>__('Neto Combustibles'),'id'=>'totalNetPriceFuels','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('Order.total_price_lubricants',['label'=>__('Total Lubricantes'),'id'=>'totalPriceLubricants','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
            echo $this->Form->input('Order.total_price',['label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
						
						echo "<h4>".__('Actions')."</h3>";
						echo "<ul>";
						if ($bool_client_add_permission) {
							echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
						}
						echo "</ul>";
					echo "</div>";
        echo "</div>";
      echo "</div>";  
      echo "<div class='row'>";      
        echo "<div class='col-sm-12'>";
          if ($boolEditingToggleVisible){ 
            echo "<div>";
              echo "<span>Editar Ventas</span>";
              echo "<label class='switch'>";
                echo "<input id='chkEditingMode' type='checkbox'".($boolEditingMode?" checked":"").">";
                echo "<span class='slider round'></span>";
              echo "</label>";
            echo "</div>";
          }
          echo "<div class='col-sm-12' style='padding:5px;'>";
            echo "<h3>".__('Combustibles')."</h3>";
            foreach ($shifts as $shiftId=>$shiftName){
              $shiftTable="";
              $shiftTable.="<table id='Turno".$shiftId."combustibles' class='combustibles' fuelshiftid=".$shiftId.">";
                $shiftTable.="<thead>";
                  $shiftTable.="<tr>";
                    $shiftTable.="<th style='min-width:80px;width:80px;max-width:75px;font-size:0.9em;' >".$shiftName."</th>";
                    $shiftTable.="<th style='min-width:80px;width:120px;'>".__('Hose')."</th>";
                    $shiftTable.="<th style='width:200px;max-width:200px;' class='operatorconfiguration'>";
                    $shiftTable.="<div>".__('Operator')."</div><div><span># ";
                    for ($i=0;$i<4;$i++){
                      $shiftTable.=$this->Form->input('Shift.'.$shiftId.'.operatorConfig.'.$i,[
                        'label'=>false,
                        'value'=>$i,
                        'readonly'=>'readonly',
                        'class'=>'operatorconfig',
                        'operatorconfig'=>$i,
                        'shiftid'=>$shiftId,
                        'style'=>'font-size:0.85em;margin-right:5px;width:20px;float:left;color:#0000ff;',
                        'div'=>false,
                        'selected'=>false,
                       ]);
                    }
                   $shiftTable.=$this->Form->input('Shift.'.$shiftId.'.OperatorConfiguration',[
                      'label'=>false,
                      'type'=>'hidden',
                      'readonly'=>'readonly',
                      'class'=>'operatorcfg',
                     ]);
                    $shiftTable.="</div></th>";
                    $shiftTable.="<th style='min-width:120px;width:150px;' class='centered narrow'>Cierre</th>";
                    $shiftTable.="<th style='min-width:120px;width:150px;' class='centered narrow'>Inicial</th>";
                    $shiftTable.="<th style='min-width:120px;width:150px;' class='centered narrow'>Litros</th>";
                    $shiftTable.="<th style='min-width:120px;width:150px;' class='centered narrow'>Venta</th>";
                  $shiftTable.="</tr>";
                $shiftTable.="</thead>";
                $shiftTable.="<tbody class='nomarginbottom' style='font-size:0.9em'>";                  
                  $operatorCounter=1;
                  foreach ($islands as $island){
                    $shiftTableRow="";
                    $firstRow=true;
                    
                    //pr($island['Hose']);
                    foreach ($island['Hose'] as $hose){
                      $shiftTableRow.="<tr id=".$hose['id'].">";
                      /*
                      if ($firstRow){
                        $shiftTableRow.="<td class='operatorId'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.operator_id',['label'=>false,'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['operator_id'])?0:$requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['operator_id']),'empty'=>[0=>'Seleccione Operador'],'style'=>'font-size:0.85em;'])."</td>";
                        $firstRow=false;
                      }
                      else{
                        $shiftTableRow.="<td>".$island['Island']['name']."</td>";
                      }
                      */
                        $shiftTableRow.="<td>".$island['Island']['name']."</td>";
                        $shiftTableRow.="<td class='hose'>";
                          $shiftTableRow.=$this->Html->link($hose['name'],['controller'=>'hoses','action'=>'detalle',$hose['id']]);
                          $shiftTableRow.=" ";
                          $shiftTableRow.=$this->Html->link($hose['Product']['name'],['controller'=>'products','action'=>'view',$hose['Product']['id']]);                                       
                          $shiftTableRow.=$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.fuel_id',['label'=>false,'class'=>'fuelid','type'=>'hidden','value'=>$hose['Product']['id'],'readonly'=>'readonly']);                            $shiftTableRow.=$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.product_unit_price',['label'=>false,'class'=>'productunitprice','type'=>'hidden','value'=>$hose['Product']['ProductPriceLog'][0]['price'],'readonly'=>'readonly']);                           
                        $shiftTableRow.="</td>";
                        $shiftTableRow.="<td class='operatorId'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.operator_id',[
                          'label'=>false,
                          'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['operator_id'])?0:$requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['operator_id']),
                          'empty'=>[0=>'Seleccione Operador'],
                          'style'=>'font-size:0.85em;',
                          'shiftid'=>$shiftId,
                          'operatorcounter'=>$operatorCounter,
                          'class'=>'operatorCounter'
                         ])."</td>";
                        $operatorCounter++;
                        $shiftTableRow.="<td class='final'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.final',[
                          'label'=>false,
                          'default'=>0,
                          //'value'=>$hose['HoseCounter'][0]['counter_value'],
                          'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['final'])?0:round((float)($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['final']),2)),
                          'previouscounter'=>$hose['HoseCounter'][0]['counter_value'],
                          'type'=>'decimal',
                          'class'=>'width100',
                          
                        ])."</td>";
                        $shiftTableRow.="<td class='initial'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.initial',['label'=>false,'value'=>$hose['HoseCounter'][0]['counter_value'],'readonly'=>'readonly','style'=>'text-align:right;'])."</td>"; 
                        $shiftTableRow.="<td class='quantity'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.quantity',[
                          'label'=>false,
                          'default'=>0,
                          'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity'])?0:round((float)($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['quantity']),2)),
                          //'previousQuantity'=>0,
                          'type'=>'decimal',
                          'class'=>'width100',
                          'readonly'=>'readonly',
                        ])."</td>";                     
                        $shiftTableRow.="<td class='price'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.price',['label'=>false,'default'=>0,'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price'])?0:(float)$requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price']),'type'=>'decimal','readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'])."</td>";               
                      $shiftTableRow.="</tr>";      
                    }
                    $shiftTable.=$shiftTableRow;
                  }
                $shiftTable.="</tbody>";
              $shiftTable.="</table>";
              //pr($requestShifts['Shift'][$shiftId]['Calibration']);
              $calibrationsTable="";
              $calibrationsTable.="<span id='Shift".$shiftId."FuelTotalPrice' class='hidden'>0</span>";
              $calibrationsTable.="<table id='Turno".$shiftId."calibrationTable' class='calibrations' shiftid=".$shiftId.">";
                $calibrationsTable.="<thead>";
                  $calibrationsTable.="<tr>";
                  $calibrationsTable.="<th style='min-width:150px;'></th>";
                  foreach ($fuels as $fuel){
                    $calibrationsTable.="<th style='width:calc((100%-150px)/5);' class='centered narrow'>".$fuel['Product']['name']."</th>";
                  }
                    $calibrationsTable.="<th style='width:calc((100%-150px)/5);' class='centered narrow'>Total</th>";
                  $calibrationsTable.="</tr>";
                $calibrationsTable.="</thead>";
                $calibrationsTable.="<tbody class='nomarginbottom' style='font-size:0.9em'>"; 
                  //first row is 
                  $calibrationsTable.="<tr id='Shift.".$shiftId.".fuelTotals'>";
                    $calibrationsTable.="<td>Total Registrado</td>";
                    foreach ($fuels as $fuel){
                      $calibrationsTable.="<td id='Shift".$shiftId."fuelTotal".$fuel['Product']['id']."' style='width:calc((100%-150px)/5);text-align:right;'>0</td>";
                    }
                    $calibrationsTable.="<td id='Shift".$shiftId."fuelTotal0' style='width:calc((100%-150px)/5);text-align:right;'>0</td>";
                   
                  $calibrationsTable.="</tr>";
                  // second row is the calibration amounts
                  $calibrationsTable.="<tr id='Shift.".$shiftId.".calibrations' class='calibrations' shiftid='".$shiftId."'>";
                    $calibrationsTable.="<td>Calibraciones</td>";
                    foreach ($fuels as $fuel){
                      $fuelId=$fuel['Product']['id'];
                      $calibrationsTable.="<td class='calibration centered' style='width:calc((100%-150px)/5);'>".$this->Form->input('Shift.'.$shiftId.'.Calibration.'.$fuelId,[
                        'label'=>false,
                        'default'=>0,
                        'value'=>(empty($requestShifts['Shift'][$shiftId]['Calibration'][$fuelId])?0:$requestShifts['Shift'][$shiftId]['Calibration'][$fuelId]),
                        'type'=>'decimal',
                        'class'=>'width100',
                        'shiftId'=>$shiftId,
                        'fuelid'=>$fuel['Product']['id'],
                       ])."</td>";
                    }
                    $calibrationsTable.="<td id='Shift".$shiftId."Calibration0' style='width:calc((100%-150px)/5);text-align:right;' >0</td>";
                  $calibrationsTable.="</tr>";
                  // third row is the net fuel totals
                  $calibrationsTable.="<tr id='Shift.".$shiftId.".netFuelTotals'>";
                    $calibrationsTable.="<td>Combustibles Neto</td>";
                    foreach ($fuels as $fuel){
                      $calibrationsTable.="<td class='netfueltotal centered' style='width:calc((100%-150px)/5);'>".$this->Form->input('Shift.'.$shiftId.'.NetFuelTotal.'.$fuel['Product']['id'],[
                        'label'=>false,
                        'default'=>0,
                        'value'=>0,
                        'type'=>'decimal',
                        'class'=>'width100',
                        'shiftId'=>$shiftId,
                        'readonly'=>'readonly',
                        'fuelid'=>$fuel['Product']['id'],
                       ])."</td>";
                    }
                    $calibrationsTable.="<td  class='centered' style='width:calc((100%-150px)/5);'>".$this->Form->input('Shift.'.$shiftId.'.TotalNetFuelTotal',[
                      'label'=>false,
                      'default'=>0,
                      'value'=>0,
                      'type'=>'decimal',
                      'class'=>'width100',
                      'shiftId'=>$shiftId,
                      'readonly'=>'readonly',
                    ])."</td>";
                  $calibrationsTable.="</tr>";
                  // fourth row is the net fuel total prices
                  $calibrationsTable.="<tr id='Shift.".$shiftId.".netFuelTotals'>";
                    $calibrationsTable.="<td>Precios Neto</td>";
                    foreach ($fuels as $fuel){
                      $calibrationsTable.="<td class='netfueltotalprice centered' style='width:calc((100%-150px)/5);'>".$this->Form->input('Shift.'.$shiftId.'.NetFuelTotalPrice.'.$fuel['Product']['id'],[
                        'label'=>false,
                        'default'=>0,
                        'value'=>0,
                        'type'=>'decimal',
                        'shiftId'=>$shiftId,
                        'readonly'=>'readonly',
                        'fuelid'=>$fuel['Product']['id'],
                        'before'=>'<span class=\'currency\'>C$</span>',
                       ])."</td>";
                    }
                    $calibrationsTable.="<td class='centered' style='width:calc((100%-150px)/5);'>".$this->Form->input('Shift.'.$shiftId.'.TotalNetFuelTotalPrice',[
                      'label'=>false,
                      'default'=>0,
                      'value'=>0,
                      'type'=>'decimal',
                      'shiftId'=>$shiftId,
                      'readonly'=>'readonly',
                      'before'=>'<span class=\'currency\'>C$</span>',
                    ])."</td>";
/*
$shiftTableRow.="<td class='price'>".$this->Form->input('Shift.'.$shiftId.'.Island.'.$island['Island']['id'].'.Hose.'.$hose['id'].'.price',[
'label'=>false,
'default'=>0,
'value'=>(empty($requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price'])?
0:(float)$requestShifts['Shift'][$shiftId]['Island'][$island['Island']['id']]['Hose'][$hose['id']]['price']),
'type'=>'decimal',
'readonly'=>'readonly',
'before'=>'<span class=\'currency\'>C$</span>'])."</td>";               
*/
                  $calibrationsTable.="</tr>";
                $calibrationsTable.="</tbody>";
              $calibrationsTable.="</table>";  
                  
              echo "<div class='shiftContainer' shiftId='".$shiftId."'>";
                echo $shiftTable;
                echo "<h4>".__('Totales (Lts) y Calibraciones')."</h4>";
                echo $calibrationsTable;
              echo "</div>"; 
              //echo $this->Form->Submit(__('Guardar Turno'),['id'=>'SaveShift'.$shiftId,'name'=>'SaveShift'.$shiftId]);
            }
            //echo $this->Form->Submit(__('Guardar Ventas de Combustibles'),['id'=>'saveFuels','name'=>'saveFuels']);
          echo "</div>";   
          
          echo "<div class='col-sm-12' style='padding:5px;'>";
            echo "<h3>".__('Lubricantes')."</h3>";
            //foreach ($shifts as $shiftId=>$shiftName){
              $lubricantTable="";
              $lubricantTable.="<table id='lubricantes'>";
                $lubricantTable.="<thead>";
                  $lubricantTable.="<tr>";
                    //$lubricantTable.="<th style='width:80px;' >".$shiftName."</th>";
                    $lubricantTable.="<th>".__('Producto')."</th>";
                    //$lubricantTable.="<th style='width:120px;' class='centered narrow'>Cierre</th>";
                    //$lubricantTable.="<th style='width:120px;' class='centered narrow'>Inicial</th>";
                    $lubricantTable.="<th style='width:100px;' class='centered narrow'>Unidades</th>";
                    $lubricantTable.="<th style='width:120px;' class='centered narrow'>Precio</th>";
                    $lubricantTable.="<th style='width:120px;' class='centered narrow'>Venta</th>";
                    $lubricantTable.="<th>Acciones</th>";
                  $lubricantTable.="</tr>";
                $lubricantTable.="</thead>";
                $lubricantTable.="<tbody class='nomarginbottom' style='font-size:0.9em'>";                  
                
                for ($i=0;$i<count($requestLubricants);$i++) { 
                  //pr($requestLubricants['Lubricant'][$i]);
                  $lubricantTableRow="";
                  $lubricantTableRow.="<tr>";
                    $lubricantTableRow.="<td class='lubricantId'>";
                      $lubricantTableRow.=$this->Form->input('Lubricant.'.$i.'.lubricant_id',['label'=>false,'value'=>$requestLubricants['Lubricant'][$i]['lubricant_id'],'empty' =>[0=>__('--Lubricante--')]]);
                    $lubricantTableRow.="</td>";
                    $lubricantTableRow.="<td class='lubricantQuantity'>".$this->Form->input('Lubricant.'.$i.'.lubricant_quantity',['type'=>'decimal','label'=>false,'value'=>(float)$requestLubricants['Lubricant'][$i]['lubricant_quantity']])."</td>";
                    $lubricantTableRow.="<td class='lubricantUnitPrice'>".$this->Form->input('Lubricant.'.$i.'.lubricant_unit_price',['type'=>'decimal','label'=>false,'value'=>(float)$requestLubricants['Lubricant'][$i]['lubricant_unit_price'],'before'=>'<span class=\'currency\'>C$</span>','readonly'=>'readonly'])."</td>";
                    $lubricantTableRow.="<td  class='lubricantTotalPrice'>".$this->Form->input('Lubricant.'.$i.'.lubricant_total_price',['type'=>'decimal','label'=>false,'value'=>(float)$requestLubricants['Lubricant'][$i]['lubricant_total_price'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'])."</td>";
                    $lubricantTableRow.="<td><button class='removeLubricant'>".__('Remover Lubricante')."</button></td>";
                  $lubricantTableRow.="</tr>";
                  $lubricantTable.=$lubricantTableRow;
                }
                for ($i=count($requestLubricants);$i<50;$i++) { 
                  $lubricantTableRow="";
                  if ($i==count($requestLubricants)){
                    $lubricantTableRow.="<tr>";
                  } 
                  else {
                    $lubricantTableRow.="<tr class='hidden'>";
                  } 
                    $lubricantTableRow.="<td class='lubricantId'>";
                      $lubricantTableRow.=$this->Form->input('Lubricant.'.$i.'.lubricant_id',['label'=>false,'default'=>'0','empty' =>[0=>__('--Lubricante--')]]);
                    $lubricantTableRow.="</td>";
                    $lubricantTableRow.="<td class='lubricantQuantity'>".$this->Form->input('Lubricant.'.$i.'.lubricant_quantity',['type'=>'decimal','label'=>false,'default'=>'0'])."</td>";
                    $lubricantTableRow.="<td class='lubricantUnitPrice'>".$this->Form->input('Lubricant.'.$i.'.lubricant_unit_price',['type'=>'decimal','label'=>false,'default'=>'0','before'=>'<span class=\'currency\'>C$</span>','readonly'=>'readonly'])."</td>";
                    $lubricantTableRow.="<td  class='lubricantTotalPrice'>".$this->Form->input('Lubricant.'.$i.'.lubricant_total_price',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'])."</td>";
                    $lubricantTableRow.="<td><button class='removeLubricant'>".__('Remover Lubricante')."</button></td>";
                  $lubricantTableRow.="</tr>";
                  $lubricantTable.=$lubricantTableRow;
                }
                $lubricantTable.="</tbody>";
              $lubricantTable.="</table>";
              echo $lubricantTable;
              echo "<button id='addLubricant' type='button'>".__('Añadir Lubricante')."</button>";

              $buttonText='Grabar Combustibles y continuar a paso 2 registrar medidas de vara para tanque';
              $buttonBackgroundColor="#62af56";
              
              $weekDay=date('w',strtotime($saleDate));
              //echo "weekday is ".$weekDay."<br/>";
              
              if ($weekDay == 0 && $daysSinceLastFuelPriceUpdate<7){
                $buttonText='Grabar Combustibles manteniendo precios viejos y continuar a paso 2 registrar medidas de vara para tanque';
              }
              elseif ($daysSinceLastFuelPriceUpdate>5){
                $buttonText='No se pueden grabar las ventas con precios viejos, registra nuevos precios primero.';
                
              }
              //,'disabled'=>($boolEditingMode?false:true)
              echo $this->Form->Submit($buttonText,['id'=>'saveSales','name'=>'saveSales','disabled'=>($boolEditingMode && !$buttonDisabled?false:true),'style'=>'width:800px;']);
            //}
            /*
            echo "<h3>".__('Totales por combustible por turno')."</h3>";
              $fuelTotalTable="";
              $fuelTotalTable.="<table id='fuelTotals'>";
                $fuelTotalTable.="<thead>";
                  $fuelTotalTable.="<tr>";
                    $fuelTotalTable.="<th style='width:100px;' >".__('Shift')."</th>";
                    foreach ($fuels as $fuel){
                      $fuelTotalTable.="<th style='width:120px;'>".$fuel['Product']['name']." (Lts)</th>";
                    }
                    $fuelTotalTable.="<th>".__('Total')."</th>";
                  $fuelTotalTable.="</tr>";
                $fuelTotalTable.="</thead>";
                $fuelTotalTableRows="";
                foreach ($shifts as $shiftId=>$shiftName){
                  $fuelTotalTableRow="";
                  $fuelTotalTableRow.="<tr>";
                    $fuelTotalTableRow.="<td class='shift'>";
                      $fuelTotalTableRow.=$shiftName;
                      $fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift.'.$shiftId,['label'=>false,'value'=>$shiftId,'type'=>'hidden']);
                    $fuelTotalTableRow.="</td>";
                    foreach ($fuels as $fuel){
                      $fuelTotalTableRow.="<td class='fuel_".$fuel['Product']['id']."'>";
                        $fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift'.$shiftId.'.FuelTotal.'.$fuel['Product']['id'],['type'=>'decimal','label'=>false,'readonly'=>'readonly','default'=>0]);
                        $fuelTotalTableRow.=$this->Form->input('FuelTotal.Shift.'.$shiftId.'.Fuel.'.$fuel['Product']['id'],['label'=>false,'value'=>$fuel['Product']['id'],'type'=>'hidden']);
                      $fuelTotalTableRow.="</td>";
                    }
                    $fuelTotalTableRow.="<td class='shiftTotal'>".$this->Form->input('FuelTotal.Shift'.$shiftId.'.Fuel',['type'=>'decimal','label'=>false,'value'=>0,'readonly'=>'readonly'])."</td>";
                    
                  $fuelTotalTableRow.="</tr>";
                  $fuelTotalTableRows.=$fuelTotalTableRow;
                }
                $fuelLitersTotalRow="";
                $fuelGallonsTotalRow="";
                $fuelTotalTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$fuelGallonsTotalRow.$fuelLitersTotalRow.$fuelTotalTableRows.$fuelLitersTotalRow.$fuelGallonsTotalRow."</tbody>";                  
                $fuelTotalTable.=$fuelTotalTableBody;
              $fuelTotalTable.="</table>";
              echo $fuelTotalTable;
              echo "<button id='addLubricant' type='button'>".__('Añadir Lubricante')."</button>";

              //echo $this->Form->Submit(__('Guardar Turno'),['id'=>'SaveShift'.$shiftId,'name'=>'SaveShift'.$shiftId]);
              
              $buttonText='Grabar Combustibles y Lubricantes y Pasar a paso 2 registrar medidas de vara para tanque';
              echo $this->Form->Submit($buttonText,['id'=>'submit','name'=>'submit','style'=>'width:300px;']);              
            //}
            */
          echo "</div>";   
        echo "</div>";  
        
        

      
    echo "<div id='editClient' class='modal fade'>";
      echo "<div class='modal-dialog'>";
        echo "<div class='modal-content'>";
          //echo $this->Form->create('EditClient', array('enctype' => 'multipart/form-data')); 
          echo "<div class='modal-header'>";
            //echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
            echo "<h4 class='modal-title'>Editar Cliente</h4>";
          echo "</div>";
          
          echo "<div class='modal-body'>";
            echo $this->Form->create('EditClient'); 
              echo "<fieldset>";
                echo $this->Form->input('id',['type'=>'hidden']);
                echo $this->Form->input('first_name',['readonly']);
                echo $this->Form->input('last_name',['readonly']);
                echo $this->Form->input('email');
                echo $this->Form->input('phone');
                echo $this->Form->input('address');
                echo $this->Form->input('ruc_number');
              echo "</fieldset>";
            echo $this->Form->end(); 	
          echo "</div>";
          echo "<div class='modal-footer'>";
            echo "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
            echo "<button type='button' class='btn btn-primary' id='EditClientSave'>".__('Guardar Cambios')."</button>";
          echo "</div>";
          
        echo "</div>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
echo "</fieldset>";
?>
</div>