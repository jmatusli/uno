<?php 
	$filename="Resumen_Asociaciones_Empresas_Usuarios_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	