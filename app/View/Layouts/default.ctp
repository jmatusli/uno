<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) intersinaptico (www.intersinaptico.com)
 * @link          http://www.intersinaptico.com
 * @package       app.View.Layouts
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'Gasolinera UNO');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		
		echo $this->Html->css('bootstrap.min.css');
		echo $this->Html->css('cake.generic.css');
		echo $this->Html->css('menu.css');
		echo $this->Html->css('uno.css');
    echo $this->Html->css('uno_tables.css');
		echo $this->Html->css('uno.print.css',array('media' => 'print'));
		

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->Html->script('jquery-1.9.1.min');
		echo $this->Html->script('date');
	?>
</head>
<body id="uno">
	<div id="container">
	<?php 
    echo '<div class="username noprint" style="position:absolute;right:20px;top:10px;font-weight:bold;">Usuario:'.$username.'</div>'; 
      echo '<div id="header">';
        echo '<div id="headerbar">';      
          echo $this->Html->image("logo_uno.png", ["alt" => "Uno",'url' => $userhomepage,'style'=>'width:15%;max-width:15%;padding-right:20px;']);
        
          echo '<nav role="navigation">';
         
            echo $this->MenuBuilder->build('main-menu',$active); 
            echo "<a href='javascript:window.print()' class='btn btn-primary print'>Imprimir</a>";
            echo $this->Html->link(__('Logout'),'/users/logout', ['class' => 'btn btn-primary logout']);	
          
          echo '</nav>';
          //pr($modificationInfo);
          if(!empty($modificationInfo)){
            if ($modificationInfo!=NA){
              //echo "<div class='useraction' style='position:absolute;right:350px;top:30px;'><div style='position:relative;'>".$modificationInfo."</div></div>";
              //echo "<div style='position:relative;'>".$modificationInfo."</div>";
              //echo $modificationInfo;
              echo "<div class='useractions' style='position:absolute;right:0px;top:0px;'>".$modificationInfo."</div>";
            }
          }
         
			?>
				<!--ul class="nav pull-right">
					<li class="dropdown user">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<span class="username">U
								<?php //echo $this->Auth->User('username'); ?>
							</span>
							<i class="icon-angle-down"></i>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="">
									<i class="icon-key"></i>
									<?php echo __('Change Password'); ?>
								</a>
							</li>
							<li>
								<a href="http://localhost:8080/siptarsupport/login/logout.siptar">
									<i class="icon-signout"></i>
									<?php echo __('Logout'); ?>
								</a>
							</li>
						</ul>
					</li>
				</ul-->				
			</div>
		</div>
		<?php 
			if ($sub!="NA"){
				echo '<div id="sub-menu">';
				echo $this->MenuBuilder->build($sub,$active); 
				echo '</div>';
			}
			
      echo "<div class='noprint' style='clear:left;'>";
        echo "<span style='margin-right:50px;color:blue;font-weight:500;'>Tasa de Cambio:".$currentExchangeRate."</span>";
        echo $exchangeRateUpdateNeeded?"<span style='color:red;font-weight:bold'>Tasa de cambio se venció, por favor ".$this->Html->Link('actualizar tasa!',['controller' => 'exchange_rates','action' => 'add'],['class' => 'btn btn-primary','target'=>'blank'])."</span>":"";
      echo "</div>";
      //pr($enterprises);
      if (!empty($enterprises)){
        
        //pr($priceUpdateNeeded);
        foreach ($enterprises as $currentEnterpriseId=>$enterpriseName){
          //echo "enterpriseid is ".$currentEnterpriseId." and enterpriseName is ".$enterpriseName."<br/>";
          echo "<div class='noprint' style='clear:left;'>";
          if ($priceUpdateNeeded[$currentEnterpriseId]){
            echo '<span style="color:red;font-weight:bold;margin-left:20px;">Precios de combustibles necesitan estar actualizados para gasolinera '.$enterpriseName.', por favor '.$this->Html->Link('actualiza los precios!',['controller' => 'productPriceLogs','action' => 'registrarPrecios',$currentEnterpriseId],['class' => 'btn btn-primary','target'=>'blank']).'</span>';
          }
          if ($inventoryMeasurementCorrectionNeeded[$currentEnterpriseId] && $userRoleId == ROLE_ADMIN){
            echo '<span style="color:red;font-weight:bold;margin-left:20px;">Se deben registrar ajustes de tanque para el inventario '.$this->Html->Link('registre ajuste de tanque aquí!',['controller' => 'stockMovements','action' => 'registrarAjusteTanque',$currentEnterpriseId],['class' => 'btn btn-primary','target'=>'blank']).' de gasolinera '.$enterpriseName.'</span>';
          }        
          echo "</div>";  
        }
      }  
      
		?>	
		<div id="content">
			
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->Session->flash('auth'); ?>
			<?php echo $this->fetch('content'); ?>
		</div>
		<?php 
			$currentController= $this->params['controller'];
			$currentAction= $this->params['action'];
			if (!($currentController=="users"&&$currentAction=="login")){
		?>	
		<script>       
      function roundToTwo(num) {    
        return +(Math.round(num + "e+2")  + "e-2");
      }

      function roundToFive(num) {    
        return +(Math.round(num + "e+5")  + "e-5");
      }      
    
			$('body').on('change','input[type=text]',function(){	
        if (!$(this).hasClass('keepcase')){
          var uppercasetext=$(this).val().toUpperCase();
          $(this).val(uppercasetext)
        }
			});
			function confirmBackspaceNavigations () {
				// http://stackoverflow.com/a/22949859/2407309
				var backspaceIsPressed = false
				$(document).keydown(function(event){
					if (event.which == 8) {
						backspaceIsPressed = true
					}
				})
				$(document).keyup(function(event){
					if (event.which == 8) {
						backspaceIsPressed = false
					}
				})
				$(window).on('beforeunload', function(){
					if (backspaceIsPressed) {
						backspaceIsPressed = false
						return "Está seguro de ir a la pantalla anterior?"
					}
				})
			} // confirmBackspaceNavigations
			
			$('#previousmonth').click(function(event){
				var thisMonth = parseInt($('#ReportStartdateMonth').val());
				var previousMonth= (thisMonth-1)%12;
				var previousYear=parseInt($('#ReportStartdateYear').val());
				if (previousMonth==0){
					previousMonth=12;
					previousYear-=1;
				}
				if (previousMonth<10){
					previousMonth="0"+previousMonth;
				}
				$('#ReportStartdateDay').val('1');
				$('#ReportStartdateMonth').val(previousMonth);
				$('#ReportStartdateYear').val(previousYear);
				var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
				$('#ReportEnddateDay').val(daysInPreviousMonth);
				$('#ReportEnddateMonth').val(previousMonth);
				$('#ReportEnddateYear').val(previousYear);
			});
			
			$('#nextmonth').click(function(event){
				var thisMonth = parseInt($('#ReportStartdateMonth').val());
				var nextMonth= (thisMonth+1)%12;
				var nextYear=parseInt($('#ReportStartdateYear').val());
				if (nextMonth==0){
					nextMonth=12;
				}
				if (nextMonth==1){
					nextYear+=1;
				}
				if (nextMonth<10){
					nextMonth="0"+nextMonth;
				}
				$('#ReportStartdateDay').val('1');
				$('#ReportStartdateMonth').val(nextMonth);
				$('#ReportStartdateYear').val(nextYear);
				var daysInNextMonth=daysInMonth(nextMonth,nextYear);
				$('#ReportEnddateDay').val(daysInNextMonth);
				$('#ReportEnddateMonth').val(nextMonth);
				$('#ReportEnddateYear').val(nextYear);
			});
      
      $('#previousyear').click(function(event){
				var previousYear=parseInt($('#ReportStartdateYear').val())-1;
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val('01');
				$('#ReportStartdateYear').val(previousYear);
				$('#ReportEnddateDay').val('31');
				$('#ReportEnddateMonth').val('12');
				$('#ReportEnddateYear').val(previousYear);
			});
			
			$('#nextyear').click(function(event){
				var nextYear=parseInt($('#ReportStartdateYear').val())+1;
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val('01');
				$('#ReportStartdateYear').val(nextYear);
				$('#ReportEnddateDay').val('31');
				$('#ReportEnddateMonth').val('12');
				$('#ReportEnddateYear').val(nextYear);
			});
			
			function daysInMonth(month,year) {
				return new Date(year, month, 0).getDate();
			}
      
      $('body').on('keypress','#content',function(e){
				 var node = (e.target) ? e.target : ((e.srcElement) ? e.srcElement : null);
				if(e.which == 13 && node.type !="textarea") { // Checks for the enter key
				//if(e.which == 13) { // Checks for the enter key
					e.preventDefault(); // Stops IE from triggering the button to be clicked
				}
			});
      
      $('body').on('click','div.numeric input',function(){
				if (!$(this).attr('readonly')){
					if ($(this).val()=="0"){
						$(this).val("");
					}
				}
			});
			
			$('body').on('click','div.decimal input',function(){
				if (!$(this).attr('readonly')){
					if ($(this).val()=="0"){
						$(this).val("");
					}
				}
			});
			
			$('body').on('blur','div.numeric input',function(){
				if (!$(this).val()||isNaN($(this).val())){
					$(this).val(0);
				}
			});	
			$('body').on('blur','div.decimal input',function(){
				if (!$(this).val()||isNaN($(this).val())){
					$(this).val(0);
				}
			});	
			
			$(document).ready(function(){
				confirmBackspaceNavigations ()
			});
		</script>
		<?php
			}
		?>
		<div id="footer">
			<?php 
				echo '<div id="copyright">Copyright 2019-'.date('Y').' @ Intersinaptico</div>';
				echo $this->Html->link(
					$this->Html->image('logo_intersinaptico_50.jpg', array('alt' => 'intersinaptico', 'border' => '0')),
					'http://www.intersinaptico.com/',
					array('target' => '_blank', 'escape' => false, 'id' => 'intersinaptico')
				);
        echo "<div style='padding-left:300px;'>sesión hasta ".date('d-m-Y H:i:s',$this->Session->read('Config.time'))."</div>";
			?>
		</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
	<?php echo $this->Html->script('bootstrap.min'); ?>
	<?php echo $this->Html->script('jquery.number'); ?>
</body>
</html>
