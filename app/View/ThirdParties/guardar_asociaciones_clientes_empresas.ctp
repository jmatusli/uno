<?php 
	$filename="Resumen_Asociaciones_Clientes_Empresas_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	