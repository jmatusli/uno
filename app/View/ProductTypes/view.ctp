<div class="productTypes view">
<h2><?php echo __('Product Type'); ?></h2>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($productType['ProductType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($productType['ProductType']['description']); ?>
			&nbsp;
		</dd>
	<?php 
		echo "<dt>".__('Accounting Code')."</dt>";
		if (!empty($productType['AccountingCode']['code'])){	
			echo "<dd>".$this->Html->Link($productType['AccountingCode']['code']." ".$productType['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$productType['AccountingCode']['id']))."</dd>";
		}
		else {	
			echo "<dd>-</dd>";
		}
	?>
	</dl>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
	if ($bool_edit_permission){
		echo "<li>".$this->Html->link(__('Edit Product Type'), array('action' => 'edit', $productType['ProductType']['id']))."</li>";
		echo "<br/>";
	}
	if ($bool_delete_permission) { 
		//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $productType['ProductType']['id']), array(), __('Are you sure you want to delete # %s?', $productType['ProductType']['id']))."</li>";
		echo "<br/>";
	}
	echo "<li>".$this->Html->link(__('List Product Types'), array('action' => 'index'))."</li>";
	if ($bool_add_permission) { 
		echo "<li>".$this->Html->link(__('New Product Type'), array('action' => 'add'))."</li>";
	}
	echo "<br/>";
	if ($bool_product_index_permission) { 
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
	}
	if ($bool_product_add_permission) { 
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	} 
	echo "</ul>";
?>	
</div>

<div class="related">
	<?php if (!empty($productType['Product'])): ?>
	<h3><?php echo __('Related Products for Product Type'); ?></h3>
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<!--th><?php echo __('Id'); ?></th-->
		<th><?php echo __('Name'); ?></th>
		<!--th><?php echo __('Product Type Id'); ?></th-->
		<!--th><?php echo __('Created'); ?></th-->
		<!--th><?php echo __('Modified'); ?></th-->
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($productType['Product'] as $product): ?>
		<tr>
			<!--td><?php echo $product['id']; ?></td-->
			<td><?php echo $product['name']; ?></td>
			<!--td><?php echo $product['product_type_id']; ?></td-->
			<!--td><?php echo $product['created']; ?></td-->
			<!--td><?php echo $product['modified']; ?></td-->
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'products', 'action' => 'view', $product['id'])); ?>
				<? if ($userrole!=ROLE_FOREMAN){ ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'products', 'action' => 'edit', $product['id'])); ?>
				<? } ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'products', 'action' => 'delete', $product['id']), array(), __('Are you sure you want to delete # %s?', $product['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<? if ($userrole!=ROLE_FOREMAN){ ?>
			<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
			<? } ?>
		</ul>
	</div>
</div>