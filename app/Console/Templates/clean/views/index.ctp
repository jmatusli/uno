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
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.views
 * @since         CakePHP(tm) v 1.2.0.5234
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<div class="<?php echo $pluralVar; ?> index">
<?php 
echo "<?php \n"; 
	echo "\techo \"<h2>\".__('{$pluralHumanName}').\"</h2>\";\n"; 
	
	echo "\techo \$this->Form->create('Report');\n";
	echo "\t\techo \"<fieldset>\";\n";
	echo "\t\t\techo \$this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>\$startDate));\n";
	echo "\t\t\techo \$this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>\$endDate));\n";
	echo "\t\techo \"</fieldset>\";\n";
	echo "\t\techo \"<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>\";\n";
	echo "\t\techo \"<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>\";\n";
	echo "\techo \$this->Form->end(__('Refresh'));\n";
	echo "\techo \$this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));\n"; 
echo "?> \n"; 	
?>
</div>
<div class='actions'>
<?php
echo "<?php \n"; 
	echo "\techo \"<h3>\".__('Actions').\"</h3>\";\n";
	echo "\techo \"<ul>\";\n";
		echo "\t\techo \"<li>\".\$this->Html->link(__('New " . $singularHumanName . "'), array('action' => 'add')).\"</li>\";\n";
		echo "\t\techo \"<br/>\";\n";
		$done = array();
		foreach ($associations as $type => $data) {
			foreach ($data as $alias => $details) {
				if ($details['controller'] != $this->name && !in_array($details['controller'], $done)) {
					echo "\t\techo \"<li>\".\$this->Html->link(__('List " . Inflector::humanize($details['controller']) . "'), array('controller' => '{$details['controller']}', 'action' => 'index')).\"</li>\";\n";
					echo "\t\techo \"<li>\".\$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'add')).\"</li>\";\n";
					$done[] = $details['controller'];
				}
			}
		}

	echo "\techo \"</ul>\";\n";
echo "?>\n"; 
?>
</div>
<div>
<?php
	echo "<?php\n";
		echo "\t\$pageHeader=\"<thead>\";\n"; 			
			echo "\t\t\$pageHeader.=\"<tr>\";\n"; 
				foreach ($fields as $field){
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\t\$pageHeader.=\"<th>\".\$this->Paginator->sort('{$field}').\"</th>\";\n"; 
					}
				}
				echo "\t\t\t\$pageHeader.=\"<th class='actions'>\".__('Actions').\"</th>\";\n"; 
			echo "\t\t\$pageHeader.=\"</tr>\";\n"; 
		echo "\t\$pageHeader.=\"</thead>\";\n"; 
		echo "\t\$excelHeader=\"<thead>\";\n"; 			
			echo "\t\t\$excelHeader.=\"<tr>\";\n"; 
				foreach ($fields as $field){
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\t\$excelHeader.=\"<th>\".\$this->Paginator->sort('{$field}').\"</th>\";\n"; 
					}
				}
			echo "\t\t\$excelHeader.=\"</tr>\";\n"; 
		echo "\t\$excelHeader.=\"</thead>\";\n\n"; 
		
		echo "\t\$pageBody=\"\";\n"; 			
		echo "\t\$excelBody=\"\";\n\n"; 			
			
		echo "\tforeach (\${$pluralVar} as \${$singularVar}){ \n";

			foreach ($fields as $field) {
				echo "\t\t\$pageRow=\"\"";
				$isKey = false;
				if (!empty($associations['belongsTo'])) {
					foreach ($associations['belongsTo'] as $alias => $details) {
						if ($field === $details['foreignKey']) {
							if (!($field=='id'||$field=='created'||$field=='modified')){
								$isKey = true;
								echo "\t\t\$pageRow.=\"<td>\".\$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], array('controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}'])).\"</td>\";\n";
								break;
							}
						}
					}
				}
				if ($isKey !== true) {
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\$pageRow.=\"<td>\".h(\${$singularVar}['{$modelClass}']['{$field}']).\"</td>\";\n";
					}
				}
			}
			echo "\n\t\t\t\$excelBody.=\"<tr>\".\$pageRow.\"</tr>\";\n\n";
			
			echo "\t\t\t\$pageRow.=\"<td class='actions'>\";\n";
			echo "\t\t\t\t\$pageRow.=\$this->Html->link(__('View'), array('action' => 'view', \${$singularVar}['{$modelClass}']['{$primaryKey}']));\n";
			echo "\t\t\t\t\$pageRow.=\$this->Html->link(__('Edit'), array('action' => 'edit', \${$singularVar}['{$modelClass}']['{$primaryKey}']));\n";
			echo "\t\t\t\t//\$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'delete', \${$singularVar}['{$modelClass}']['{$primaryKey}']), array(), __('Are you sure you want to delete # %s?', \${$singularVar}['{$modelClass}']['{$primaryKey}']));\n";
			echo "\t\t\t\$pageRow.=\"</td>\";\n";
		echo "\n\t\t\$pageBody.=\"<tr>\".\$pageRow.\"</tr>\";\n";	
		echo "\t}\n\n";
		echo "\t\$pageTotalRow=\"\";\n";
		echo "\t\$pageTotalRow.=\"<tr class=\'totalrow\'>\";\n";
		foreach ($fields as $field) {
			echo "\t\t\$pageTotalRow.=\"<td></td>\";\n";
		}	
		echo "\t\$pageTotalRow.=\"</tr>\";\n\n";
		
		echo "\t\$pageBody=\"<tbody>\".\$pageTotalRow.\$pageBody.\$pageTotalRow.\"</tbody>\";\n";
		echo "\t\$table_id=\"\";\n";
		echo "\t\$pageOutput=\"<table cellpadding='0' cellspacing='0' id='\".\$table_id.\"'>\".\$pageHeader.\$pageBody.\"</table>\";\n";
		echo "\techo \$pageOutput;\n";
		echo "\t\$excelOutput=\"<table cellpadding='0' cellspacing='0' id='\".\$table_id.\"'>\".\$excelHeader.\$excelBody.\"</table>\";\n";
		echo "\t\$_SESSION['resumen'] = \$excelOutput;\n";
		
		//echo "\techo \"<p>\";\n";
		//echo "\t\techo \$this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));"; 
		//echo "\techo \"</p>\";\n";
		//echo "\techo \"<div class='paging'>\";\n";
			//echo "\t\techo \$this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));\n";
			//echo "\t\techo \$this->Paginator->numbers(array('separator' => ''));\n";
			//echo "\t\techo \$this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));\n";
		//echo "\techo \"</div>\";\n";
	echo "?>\n"; 
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
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
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});

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
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
</script>