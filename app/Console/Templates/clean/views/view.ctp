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
<div class="<?php echo $pluralVar; ?> view">
<?php
	echo "<?php \n";
	echo "\techo \"<h2>\".__('{$singularHumanName}').\"</h2>\";\n";
	echo "\techo \"<dl>\";\n";

	foreach ($fields as $field) {
		$isKey = false;
		if (!empty($associations['belongsTo'])) {
			foreach ($associations['belongsTo'] as $alias => $details) {
				if ($field === $details['foreignKey']) {
					if (!($field=='id'||$field=='created'||$field=='modified')){
						$isKey = true;
						echo "\t\techo \"<dt>\".__('" . Inflector::humanize(Inflector::underscore($alias)) . "').\"</dt>\";\n";
						echo "\t\techo \"<dd>\".\$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], array('controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}'])).\"</dd>\";\n";
						break;
					}
				}
			}
		}
		if ($isKey !== true) {
			if (!($field=='id'||$field=='created'||$field=='modified')){
				echo "\t\techo \"<dt>\".__('" . Inflector::humanize($field) . "').\"</dt>\";\n";
				echo "\t\techo \"<dd>\".h(\${$singularVar}['{$modelClass}']['{$field}']).\"</dd>\";\n";
			}
		}
	}

	echo "\techo \"</dl>\";\n";
	echo "?> \n";
?>
</div>
<div class="actions">
<?php
	echo "<?php \n";
	echo "\techo \"<h3>\".__('Actions').\"</h3>\";\n";
	echo "\techo \"<ul>\";\n";

	echo "\t\techo \"<li>\".\$this->Html->link(__('Edit " . $singularHumanName ."'), array('action' => 'edit', \${$singularVar}['{$modelClass}']['{$primaryKey}'])).\"</li>\";\n";
	echo "\t\techo \"<li>\".\$this->Form->postLink(__('Delete " . $singularHumanName . "'), array('action' => 'delete', \${$singularVar}['{$modelClass}']['{$primaryKey}']), array(), __('Are you sure you want to delete # %s?', \${$singularVar}['{$modelClass}']['{$primaryKey}'])).\"</li>\";\n";
	echo "\t\techo \"<li>\".\$this->Html->link(__('List " . $pluralHumanName . "'), array('action' => 'index')).\"</li>\";\n";
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
	echo "?> \n";
?>
</div>
<?php
if (!empty($associations['hasOne'])) {
	foreach ($associations['hasOne'] as $alias => $details){ ?>
	<div class="related">
<?php 
	echo "<?php ";
	echo "\tif (!empty(".${$singularVar}['{$alias}'].")){\n"; 
		echo "\t\techo \"<h3>\".__('Related " . Inflector::humanize($details['controller']) . "').\"</h3>\";\n";
		echo "\t\techo \"<dl>\";\n";
			echo "\t\tforeach (".$details['fields']." as ".$field.") {\n";
				if (!($field=='id'||$field=='created'||$field=='modified')){
					echo "\t\t\techo \"<dt>\".__('" . Inflector::humanize($field) . "').\"</dt>\";\n";
					echo "\t\t\techo \"<dd>\".\${$singularVar}['{$alias}']['{$field}'].\"</dd>\";\n";
				}
			echo "\t\t}\n";
		echo "\t\techo \"</dl>\";\n";
		echo "\t\t}\n";
		echo "\techo \"<div class='actions'>\";\n";
			echo "\t\techo \"<ul>\";\n";
				echo "\t\t\techo \"<li>\".\$this->Html->link(__('Edit " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'edit', \${$singularVar}['{$alias}']['{$details['primaryKey']}'])); ?></li>\n"; ?>
			echo "\t\techo \"</ul>\";\n";
		echo "\techo \"</div>\";\n";
	
?>	
	</div>
<?php	
	}
}

if (empty($associations['hasMany'])) {
	$associations['hasMany'] = array();
}
if (empty($associations['hasAndBelongsToMany'])) {
	$associations['hasAndBelongsToMany'] = array();
}
$relations = array_merge($associations['hasMany'], $associations['hasAndBelongsToMany']);
foreach ($relations as $alias => $details){
	$otherSingularVar = Inflector::variable($alias);
	$otherPluralHumanName = Inflector::humanize($details['controller']);
?>
<div class="related">
<?php
	echo "<?php \n";	
	echo "\tif (!empty($".$singularVar."['".$alias."'])){\n"; 
	echo "\t\techo \"<h3>\".__('Related ".$otherPluralHumanName."').\"</h3>\";\n";
	echo "\t\techo \"<table cellpadding = '0' cellspacing = '0'>\";\n";
	echo "\t\t\techo \"<tr>\";\n";
	foreach ($details['fields'] as $field) {
		if (!($field=='id'||$field=='created'||$field=='modified')){
			echo "\t\t\t\techo \"<th>\".__('" . Inflector::humanize($field) . "').\"</th>\";\n";
		}
	}
		echo "\t\t\t\techo\"<th class='actions'>\".__('Actions').\"</th>\";\n";
	echo "\t\t\techo \"</tr>\";\n";

	echo "\t\tforeach ($".$singularVar."['".$alias."'] as $".$otherSingularVar."){ \n";
		echo "\t\t\techo \"<tr>\";\n";
			foreach ($details['fields'] as $field) {
				if (!($field=='id'||$field=='created'||$field=='modified')){
					echo "\t\t\t\techo \"<td>\".$".$otherSingularVar."['".$field."'].\"</td>\";\n";
				}
			}

			echo "\t\t\t\techo \"<td class='actions'>\";\n";
			echo "\t\t\t\t\techo \$this->Html->link(__('View'), array('controller' => '{$details['controller']}', 'action' => 'view', \${$otherSingularVar}['{$details['primaryKey']}']));\n";
			echo "\t\t\t\t\techo \$this->Html->link(__('Edit'), array('controller' => '{$details['controller']}', 'action' => 'edit', \${$otherSingularVar}['{$details['primaryKey']}']));\n";
			echo "\t\t\t\t\techo \$this->Form->postLink(__('Delete'), array('controller' => '{$details['controller']}', 'action' => 'delete', \${$otherSingularVar}['{$details['primaryKey']}']), array(), __('Are you sure you want to delete # %s?', \${$otherSingularVar}['{$details['primaryKey']}']));\n";
			echo "\t\t\t\techo \"</td>\";\n";
		echo "\t\t\techo \"</tr>\";\n";

	echo "\t\t}\n";

	echo "\t\techo \"</table>\";\n";
	echo "\t}\n"; 
	echo "?>\n";	
	/*
	<div class="actions">
		<ul>
			<li><?php echo "<?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'add')); ?>"; ?> </li>
		</ul>
	</div>
	*/
?>
</div>
<?php
}
?>
