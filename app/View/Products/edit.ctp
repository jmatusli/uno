<script>
	$('body').on('change','#ProductProductTypeId',function(){	
		
	});
	
	$(document).ready(function(){
		//$('#ProductProductTypeId').trigger('change');
	});
</script>

<div class="products form">
<?php 
  echo $this->Form->create('Product');
	echo "<fieldset>";
		echo "<legend>".__('Edit Product')."</legend>";
		//pr($this->request->data);
		echo $this->Form->input('id');
		echo $this->Form->input('name');
    echo $this->Form->input('product_order',['label'=>'Orden en listas']);
    echo $this->Form->input('abbreviation');
    echo $this->Form->input('old_name',['label'=>'Nombre viejo']);
		echo $this->Form->input('description');
    echo $this->Form->input('bool_active',['label'=>'Activo']);
		echo $this->Form->input('product_type_id');
    echo $this->Form->input('packaging_unit');
    echo $this->Form->input('bool_island',['label'=>'Producto que se vende en isla','div'=>['class'=>'input checkboxleft']]);
    echo $this->Form->input('product_order',['label'=>'Orden en lista']);
		//echo $this->Form->input('accounting_code_id',['empty'=>['0'=>__('Select Accounting Code')]]);
    echo  "<div class='row'>";
    echo "<h3>Costos en</h3>";
      echo "<div class='col-xs-6'>";
        echo $this->Form->input('default_cost_currency_id',['label'=>'Moneda para costos','div'=>['class'=>'input select label50']]);
      echo "</div>";
      echo "<div class='col-xs-6' style='clear:right;'>";
        echo $this->Form->input('default_cost_unit_id',['label'=>'Unidad para costos','options'=>$units,'empty'=>[0=>'Unidad'],'div'=>['class'=>'input select label50']]);
      echo "</div>";
      echo  "<p class='comment'>El costo preestablecido estará utilizado en las ordenes de compra  si la moneda corresponde.</P>";
      echo $this->Form->input('default_cost',['label'=>'Costo preestablecido','div'=>['class'=>'input number']]); 
      echo "<div class='col-xs-6' >";
        echo $this->Form->input('min_cost',['label'=>'Costo mínimo admisible','div'=>['class'=>'input number label50']]);  
      echo "</div>";
      echo "<div class='col-xs-6' style='clear:right;'>";
        echo $this->Form->input('max_cost',['label'=>'Costo máximo admisible','div'=>['class'=>'input number label50']]);  
      echo "</div>"; 

      echo "<h3>Precios en</h3>";
      echo "<div class='col-xs-6'>";
        echo $this->Form->input('default_price_currency_id',['label'=>'Moneda para precios','div'=>['class'=>'input select label50']]);
      echo "</div>";
      echo "<div class='col-xs-6' style='clear:right;'>";
        echo $this->Form->input('default_price_unit_id',['label'=>'Unidad para precios','options'=>$units,'default'=>0,'empty'=>[0=>'Unidad'],'div'=>['class'=>'input select label50']]);
      echo "</div>";
      echo $this->Form->input('default_price',['label'=>'Precio preestablecido','div'=>['class'=>'input number']]);       
    echo "</div>";
		echo $this->Form->input('enterprise_id',['type'=>'hidden','value'=>ENTERPRISE_LAS_PALMAS]);
	echo "</fieldset>";
  echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Products'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_producttype_index_permission){
			echo "<li>".$this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index'))."</li>";
		}
		if ($bool_producttype_add_permission){
			echo "<li>".$this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
</div>

