<?php 
	class EnterpriseFilterHelper extends AppHelper {
		var $helpers = ['Form','Html']; // include the html helper
		
		function displayEnterpriseFilter($enterprises,$userRoleId,$default){
      $enterpriseFilterInput='';
      switch (count($enterprises) ){
        case 0:
          $enterpriseFilterInput=$this->Form->input('enterprise_id',['label'=>'Gasolinera','default'=>$default,'type'=>'hidden']);
          break;  
        case 1:
          $enterpriseFilterInput=$this->Form->input('enterprise_id',['label'=>'Gasolinera','default'=>$default]);
          break;  
        default:
          $enterpriseFilterInput=$this->Form->input('enterprise_id',['label'=>'Gasolinera','default'=>$default,'empty'=>[0=>'-- Seleccione Gasolinera --']]);
      }
			return $enterpriseFilterInput;
		}
	}
?>