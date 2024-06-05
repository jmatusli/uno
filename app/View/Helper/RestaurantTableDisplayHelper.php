<?php 
	class RestaurantTableDisplayHelper extends AppHelper {
		var $helpers = array('Html'); // include the html helper
		
		function showRestaurantTables($locations,$selectedTableId){
			$columnCounter=0;
			$maxColumns=4;
			
			$tableDiv="<div id='tables'>";
			foreach ($locations as $location){
				$tableLinkRef="";
				$locationClass="restauranttable";
				//$tableLinkName= h($location['Location']['name'])."\r\n (".h($location['Location']['category']).")";
				$tableLinkName= h($location['Location']['name']);
				if ($selectedTableId==$location['Location']['id']){
					$locationClass.=" currenttable";
					$tableLinkRef=$tableLinkName;
				}
				if ($location['Location']['status']=="disponible"){
					$locationClass.=" green";
					$tableLinkRef=$this->Html->link(__('Servir '.$tableLinkName), array('controller' => 'orders', 'action' => 'add','?'=>array('tableID' => h($location['Location']['id']),'tableName' => h($location['Location']['name'])))); 
				}
				else {
				
				
					$locationClass.=" red";
					// TODO look up how to get the open order instead of the first order i.e. where are orders for the location defined?  Do they have to be set in the display method of the controller?  
					$tableLinkRef= $this->Html->link(__('Editar '.$tableLinkName), array('controller' => 'orders', 'action' => 'edit', $location['Order'][0]['id'],'?'=>array('tableID' => h($location['Location']['id']),'tableName' => h($location['Location']['name'])))); 	
									
				}
				if ($columnCounter%$maxColumns==0){
					$locationClass.=" clearLeft";
				}
				$tableDiv.='<div class="restotable '.$locationClass.'">';
				$tableDiv.=$tableLinkRef;
				$tableDiv.="</div>\r\n";
			
				$columnCounter++;
			}
			$tableDiv.="</div>";	
			return $tableDiv;
		}
	}
?>