<?php
	if (!empty($dueDate)){
		echo $this->Form->input('Invoice.due_date',array('type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','default'=>$dueDate));
	}
?>