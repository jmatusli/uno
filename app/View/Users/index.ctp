<div class="users index" style="overflow-x:auto">
<?php 
	echo "<h2>".__('Users')."</h2>";
  echo "<p class='comment'>Usuarios desactivados aparecen <i>en cursivo</i></p>";
  echo "<table cellpadding='0' cellspacing='0' id='users'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>". $this->Paginator->sort('username')."</th>";
				//echo "<!--th>". $this->Paginator->sort('password')."</th-->";
				echo "<th>". $this->Paginator->sort('role_id')."</th>";	
				echo "<th>". $this->Paginator->sort('first_name')."</th>";
				echo "<th>". $this->Paginator->sort('last_name')."</th>";
				echo "<th>". $this->Paginator->sort('email')."</th>";
				echo "<th>". $this->Paginator->sort('phone')."</th>";
				echo "<th class='actions'>". __('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
	foreach ($users as $user){
      if ($user['User']['bool_active']){
        echo "<tr>";
      }
      else {
        echo "<tr class='italic'>";
      }
				echo "<td>". $this->Html->link($user['User']['username'],array('action'=>'view',$user['User']['id']))."</td>";
				//echo "<!--td>". h($user['User']['password'])."&nbsp;</td-->";
				echo "<td>". $this->Html->link($user['Role']['name'], array('controller' => 'roles', 'action' => 'view', $user['Role']['id']))."</td>";
				echo "<td>". h($user['User']['first_name'])."&nbsp;</td>";
				echo "<td>". h($user['User']['last_name'])."&nbsp;</td>";
				echo "<td>". h($user['User']['email'])."&nbsp;</td>";
				echo "<td>". h($user['User']['phone'])."&nbsp;</td>";
				echo "<td class='actions'>";
					//echo $this->Html->link(__('View'), array('action' => 'view', $user['User']['id'])); 
					if ($bool_edit_permission){
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $user['User']['id'])); 
					} 
					// echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $user['User']['id']), array(), __('Are you sure you want to delete %s?', $user['User']['username'])); 
				echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($userRoleId!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?></li>
		<?php } ?>	
		<br/>
		<!--li><?php echo $this->Html->link(__('List Roles'), array('controller' => 'roles', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Role'), array('controller' => 'roles', 'action' => 'add')); ?> </li-->
		<?php if ($userRoleId!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('List User Logs'), array('controller' => 'user_logs', 'action' => 'index')); ?> </li>
		<?php } ?>	
		<!--li><?php echo $this->Html->link(__('New User Log'), array('controller' => 'user_logs', 'action' => 'add')); ?> </li-->
	</ul>
</div>
