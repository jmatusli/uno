<?php
	
	echo "<div id='configoptions'>";
	echo "<h1>".__('Configuration Options')."</h1>";
	
	if($userRoleId!=ROLE_FOREMAN){
		echo "<div class='col-md-4'>";
			//echo $this->Html->image("product.jpg", ["alt" => "Product",'url' => ['controller' => 'products','action' => 'index']]); 
			echo "<h2>".__('Products')."</h2>";
			echo "<div>".$this->Html->link(__('Product Types'), ['controller' => 'productTypes', 'action' => 'index'])."</div>";
			echo "<div>".$this->Html->link(__('Products'), ['controller' => 'products', 'action' => 'index'])."</div>";
      echo "<h2>".__('Proveedores y clientes')."</h2>";
			echo "<div>".$this->Html->link(__('Providers'), ['controller' => 'thirdParties', 'action' => 'resumenProveedores'])."</div>";
			echo "<div>".$this->Html->link(__('Clients'), ['controller' => 'thirdParties', 'action' => 'resumenClientes'])."</div>";
      if ($userRoleId==ROLE_ADMIN||$userRoleId==ROLE_ASSISTANT){
        echo "<div>".$this->Html->link(__('Asociar Clientes y Usuarios'), ['controller' => 'thirdParties', 'action' => 'asociarClientesUsuarios'])."</div>";
        echo "<div>".$this->Html->link(__('Reasignar Clientes a Usuarios'), ['controller' => 'thirdParties', 'action' => 'reasignarClientes'])."</div>";
        echo "<div>".$this->Html->link(__('Asociar Clientes y Empresas'), ['controller' => 'thirdParties', 'action' => 'asociarClientesEmpresas'])."</div>";
      }
    echo "</div>";
	}
	echo "<div class='col-md-4'>";
		//echo $this->Html->image("production.jpg", ["alt" => "Production",'url' => ['controller' => 'production_runs','action' => 'index']]); 
		echo "<h2>".__('Gasolinera')."</h2>";
		echo "<div>".$this->Html->link(__('Hoses'), ['controller' => 'hoses', 'action' => 'resumen'])."</div>";
    echo "<div>".$this->Html->link(__('Contadores Mangueras'), ['controller' => 'hoseCounters', 'action' => 'registrarContadores'])."</div>";
    echo "<div>".$this->Html->link(__('Islands'), ['controller' => 'islands', 'action' => 'resumen'])."</div>";
    echo "<div>".$this->Html->link(__('Tanks'), ['controller' => 'tanks', 'action' => 'resumen'])."</div>";
    echo "<br/>";
		echo "<div>".$this->Html->link(__('Operators'), ['controller' => 'operators', 'action' => 'resumen'])."</div>";
		echo "<div>".$this->Html->link(__('Shifts'), ['controller' => 'shifts', 'action' => 'resumen'])."</div>";
		//echo "<div>".$this->Html->link(__('Warehouses'), ['controller' => 'warehouses', 'action' => 'index'])."</div>";
		//echo "<div>".$this->Html->link(__('Production Run Types'), ['controller' => 'production_run_types', 'action' => 'index'])."</div>";
	echo "</div>";
	
	if($userRoleId!=ROLE_FOREMAN){
		echo "<div class='col-md-4'>";
			//echo $this->Html->image("user.jpg", ["alt" => "User",'url' => ['controller' => 'users','action' => 'index']]); 
			echo "<h2>".__('Usuarios')."</h2>";
			if ($userRoleId==ROLE_ADMIN){
				echo "<div>".$this->Html->link(__('User Management'), ['controller' => 'users', 'action' => 'index'])."</div>";
				echo "<div>".$this->Html->link(__('Papeles de Usuarios'), ['controller' => 'roles', 'action' => 'index'])."</div>";
				echo "<div>".$this->Html->link(__('Permisos de Usuarios'), ['controller' => 'users', 'action' => 'rolePermissions'])."</div>";
        echo "<div>".$this->Html->link(__('Enterprises'), ['controller' => 'enterprises', 'action' => 'resumen'])."</div>";
        echo "<div>".$this->Html->link(__('Asociar Gasolineras y Usuarios'), ['controller' => 'enterprises', 'action' => 'asociarEmpresasUsuarios'])."</div>";
			}
      echo "<h2>".__('Employees')."</h2>";
			echo "<div>".$this->Html->link(__('Employees'), ['controller' => 'employees', 'action' => 'resumen'])."</div>";
			echo "<div>".$this->Html->link(__('Employee Holidays'), ['controller' => 'employeeHolidays', 'action' => 'index'])."</div>";
			if ($userRoleId==ROLE_ADMIN){
				echo "<div>".$this->Html->link(__('Motivos de Vacaciones'), ['controller' => 'holidayTypes', 'action' => 'index'])."</div>";
			}
      if ($userRoleId==ROLE_ADMIN){
        echo "<h2>".__('Otros')."</h2>";
        echo "<div>".$this->Html->link(__('Constants'), ['controller' => 'constants', 'action' => 'index'])."</div>";
        echo "<div>".$this->Html->link(__('Units'), ['controller' => 'units', 'action' => 'resumen'])."</div>";
        echo "<div>".$this->Html->link(__('Payment Modes'), ['controller' => 'paymentModes', 'action' => 'index'])."</div>";
			}
      if ($userRoleId==ROLE_ADMIN){
				echo "<div>".$this->Html->link(__('Closing Dates'), ['controller' => 'closingDates', 'action' => 'resumen'])."</div>";
			}
		echo "</div>";
	}

	echo "</div>";
