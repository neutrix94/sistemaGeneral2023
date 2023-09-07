<?php
	//php_track_vars;
	
	extract($_GET);
	extract($_POST);
	
//CONECCION Y PERMISOS A LA BASE DE DATOS
	include("../../conect.php");
	
	
	
	$smarty->display("especiales/seguimiento.tpl");
	
?>