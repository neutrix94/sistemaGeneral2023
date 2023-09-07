<?php
	include('../../config.inc.php');
	include('../../conexionMysqli.php');
	$p_p_k = $_POST['id'];
	$sql = "CALL sp_productos_limpieza({$p_p_k})";
	$exc = $link->query( $sql ) or die( "Error al mandar llamar reseteo de producto : " . $link->error );
	die( 'ok' );
?>