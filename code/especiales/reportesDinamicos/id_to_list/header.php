<?php
	
	include( '../../../../conectMin.php' );
	include( '../../../../conexionMysqli.php' );
//validacion de permisos
	$sql = "SELECT IF( ver = 1 OR modificar = 1 OR eliminar = 1 OR imprimir =1 OR generar = 1, 1, 0)
			FROM sys_permisos WHERE id_perfil = '{$perfil_usuario}' AND id_menu = 224 ";
	$eje = $link->query( $sql ) or die("Error al validar el permiso : {$link->error}");
	$r = $eje->fetch_row(); 
	if ( $r[0] == 0 ){
		die('<script>alert("No tiene permiso para esta pantalla");location.href="../../../../index.php?";</script>');
	}
//obtener listas de precios
	$sql = "SELECT id_precio, nombre FROM ec_precios WHERE id_precio > 0";
	$eje = $link->query( $sql ) or die( "Error al consultar las listas de precios : {$link->error}");
	$lists = '<select id="list_id" class="form-control">';
	$lists .= '<option value="0">-- Click para seleccionar lista de Precios -- </option>';
	while ( $r = $eje->fetch_row() ) {
		$lists .= '<option value="' . $r[0] . '">' . $r[1] . '</option>';
	}
	$lists .= '</select>';
?>