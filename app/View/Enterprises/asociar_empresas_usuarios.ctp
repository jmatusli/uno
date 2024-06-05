<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>

<div class="enterprises asociarempresasusuarios fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('EnterpriseUser');
	echo "<fieldset>";
		echo "<p class='comment'></p>";
		echo $this->Form->input('user_id',['label'=>'Usuario','default'=>$selectedUserId,'empty'=>[0=>'Seleccione Usuario']]);
		echo $this->Form->input('enterprise_id',['label'=>'Enterprise','default'=>$selectedEnterpriseId,'empty'=>[0=>'Seleccione Empresa']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Gasolineras con Usuarios')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesEmpresasUsuarios'],['class' => 'btn btn-primary']); 
		echo "<p class='comment'>Cuando se cambia la asociación para una empresa y un usuario, se guardarán las asociaciones de todos usuarios con esta empresa</p>";
		
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Enterprise')."</th>";
        foreach ($selectedUsers as $userId=>$userValue){
          $tableHead.="<th>".$this->Html->link($userValue,['controller'=>'users','action'=>'view',$userId])."</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.="<th>".__('Enterprise')."</th>";
        foreach ($selectedUsers as $userId=>$userValue){
          $excelHead.="<th>".$userValue."</th>";
        }
      $excelHead.="</tr>";
    $excelHead.="</thead>";
    
    $tableBody="<tbody>";
    for ($c=0;$c<count($selectedEnterprises);$c++){
      //pr($selectedEnterprises[$c]);
      $tableBody.="<tr>";
        $tableBody.="<td>";
          $tableBody.=$this->Html->link($selectedEnterprises[$c]['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'ver',$selectedEnterprises[$c]['Enterprise']['id']]);
          $tableBody.=$this->Form->input('Enterprise.'.$selectedEnterprises[$c]['Enterprise']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $tableBody.="</td>";
        if (empty($selectedEnterprises[$c]['Users'])){
          foreach ($selectedUsers as $userId=>$userValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('Enterprise.'.$selectedEnterprises[$c]['Enterprise']['id'].'.User.'.$userId.'.bool_assigned',['type'=>'checkbox','label'=>false,'checked'=>false,'class'=>'assignment']);
            $tableBody.="</td>";
          }
        }
        else {
          foreach ($selectedUsers as $userId=>$userValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('Enterprise.'.$selectedEnterprises[$c]['Enterprise']['id'].'.User.'.$userId.'.bool_assigned',['type'=>'checkbox','label'=>false,'checked'=>$selectedEnterprises[$c]['Users'][$userId],'class'=>'assignment']);
            $tableBody.="</td>";
          }
        }
      $tableBody.="</tr>";			
		}
		$tableBody.="</tbody>";
    $excelBody="</tbody>";
    $excelBody="<tbody>";
    for ($c=0;$c<count($selectedEnterprises);$c++){
      //pr($selectedEnterprises[$c]);
      $excelBody.="<tr>";
        $excelBody.="<td>";
          $excelBody.=$this->Html->link($selectedEnterprises[$c]['Enterprise']['company_name'],['controller'=>'enterprises','action'=>'ver',$selectedEnterprises[$c]['Enterprise']['id']]);
          $excelBody.=$this->Form->input('Enterprise.'.$selectedEnterprises[$c]['Enterprise']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $excelBody.="</td>";
        if (empty($selectedEnterprises[$c]['Users'])){
          foreach ($selectedUsers as $userId=>$userValue){
            $excelBody.="<td>0</td>";
          }
        }
        else {
          foreach ($selectedUsers as $userId=>$userValue){
            $excelBody.="<td>".($selectedEnterprises[$c]['Users'][$userId]?"1":"0")."</td>";
          }
        }
      $excelBody.="</tr>";			
		}
		$excelBody.="</tbody>";
		$table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
    echo $table;
    $excelTable="<table cellpadding='0' cellspacing='0' id='asoc_cliente_vendedor'>".$excelHead.$excelBody."</table>";
    $_SESSION['resumenAsociacionesEmpresasUsuarios'] = $excelTable;
    /*
		echo "<p>";
	
			echo $this->Paginator->counter([
			'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
			]);
		echo "</p>";
		echo "<div class='paging'>";
			echo $this->Paginator->prev('< ' . __('previous'), [], null, ['class' => 'prev disabled']);
			echo $this->Paginator->numbers(['separator' => '']);
			echo $this->Paginator->next(__('next'), [], null, ['class' => 'next disabled']);
		echo "</div>";
  */
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
