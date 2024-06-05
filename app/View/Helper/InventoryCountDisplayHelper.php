<?php 
	class InventoryCountDisplayHelper extends AppHelper {
		var $helpers = array('Html'); // include the html helper
		
		function showInventoryTotals($stockItems,$productCategoryId,$headertitle){
			$divClass="";
			switch($productCategoryId){
				case CATEGORY_PRODUCED:
					$divClass="produced";
					break;
				case CATEGORY_OTHER:
					$divClass="other";
					break;
				case CATEGORY_RAW:
					$divClass="raw";
					break;
			}
		
			$inventoryTotalDiv="<div id='inventoryTotals'".(!empty($divClass)?(" class='".$divClass."'"):"").">";
			$inventoryTotalDiv.="<h3>".$headertitle."</h3>";
			$inventoryTotalDiv.="<table>";
			$inventoryTotalDiv.="<thead>";
			$inventoryTotalDiv.="<tr>";
			if ($productCategoryId==CATEGORY_PRODUCED){
					$inventoryTotalDiv.="<th>".__('Raw Material')."</th>";
			}
			$inventoryTotalDiv.="<th class='hidden'>Product Id</th>";
			$inventoryTotalDiv.="<th>".__('Product')."</th>";
			if ($productCategoryId==CATEGORY_PRODUCED){
				// should show the results for each production result code as a column
				$inventoryTotalDiv.="<th class='centered'>A</th>";
				$inventoryTotalDiv.="<th class='centered'>B</th>";
				$inventoryTotalDiv.="<th class='centered'>C</th>";
				//$inventoryTotalDiv.="<th>".__('Total Quantity')."</th>";
			}
			else {
				$inventoryTotalDiv.="<th class='centered'>".__('Inventory Total')."</th>";
			}
			$inventoryTotalDiv.="</tr>";
			$inventoryTotalDiv.="</thead>";
			$inventoryTotalDiv.="<tbody>";
			// print_r($stockItems);
			foreach ($stockItems as $stockitem){
				//echo "<pre>";
				//print_r($stockitem);
				//echo "</pre>";
				$inventoryTotalDiv.="<tr>"; 
				if ($productCategoryId==CATEGORY_PRODUCED){
					if (!empty($stockitem['0']['Remaining'])){
						// only print out lines that have remaining quantities
						$inventoryTotalDiv.="<td>".$stockitem['RawMaterial']['name']."</td>";
						$inventoryTotalDiv.="<td class='hidden'>"; 
						$inventoryTotalDiv.=$stockitem['Product']['id'];
						$inventoryTotalDiv.="</td>";
						$inventoryTotalDiv.="<td>"; 
						$inventoryTotalDiv.=$stockitem['Product']['name'];
						$inventoryTotalDiv.="</td>";
						$inventoryTotalDiv.="<td class='centered'>"; 
						$inventoryTotalDiv.=number_format($stockitem['0']['Remaining_A'],0,".",",");
						$inventoryTotalDiv.="</td>";
						$inventoryTotalDiv.="<td class='centered'>"; 
						$inventoryTotalDiv.=number_format($stockitem['0']['Remaining_B'],0,".",",");
						$inventoryTotalDiv.="</td>";
						$inventoryTotalDiv.="<td class='centered'>"; 
						$inventoryTotalDiv.=number_format($stockitem['0']['Remaining_C'],0,".",",");
						$inventoryTotalDiv.="</td>";
					}
				}
				else {
					$inventoryTotalDiv.="<td class='hidden'>"; 
					$inventoryTotalDiv.=$stockitem['Product']['id'];
					$inventoryTotalDiv.="</td>";
					$inventoryTotalDiv.="<td>"; 
					$inventoryTotalDiv.=$stockitem['Product']['name'];
					$inventoryTotalDiv.="</td>";
					$inventoryTotalDiv.="<td class='centered'>"; 
					// 20170320 COMMENTED OUT AS IT SEEMS POINTLESS TO HAVE NON INTEGER QUANTITIES
					//$inventoryTotalDiv.=number_format($stockitem[0]['inventory_total'],2,".",",");
					$inventoryTotalDiv.=number_format($stockitem[0]['inventory_total'],0,".",",");
					$inventoryTotalDiv.="</td>";
				}
				$inventoryTotalDiv.="</tr>";
			}
			$inventoryTotalDiv.="</tbody>";
			$inventoryTotalDiv.="</table>";	
			$inventoryTotalDiv.="</div>";	
			return $inventoryTotalDiv;
		}
	}
?>