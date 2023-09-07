<?php

	extract($_POST);
	extract($_GET);	
	require("conect.php");
	
//accedemos a archivos de sincronización en caso de tener permiso de sincronización (OSCAR)
	/*if($user_sinc==1){
    	require('code/especiales/sincronizacion/sincronizar.php');
	}*/
	$smarty->display('general/principal.tpl');

?>