<?php
	include( 'config.ini.php' );
	include( 'conect.php' );
	include( 'conexionMysqli.php' );
	$sql = "SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE id_transferencia = 7042";
	$stm = $link->query( $sql ) or die( "Error al consultar el detalle de la transferencia : {$link->error}" );
	$counter = 0;
	while( $row = $stm->fetch_assoc() ){
		$counter++;
		$sql = "UPDATE ec_transferencia_productos 
					SET numero_consecutivo = '{$counter}'
				WHERE id_transferencia_producto IN( {$row['id_transferencia_producto']} )";
		$stm_upd = $link->query( $sql ) or die( "Error al actualizar el consecutivo : {$link->error}" );
	}
	echo 'ok';
?>