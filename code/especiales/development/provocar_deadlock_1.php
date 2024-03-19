<?php
	include( '../../../conexionMysqli.php' );
	$link->autocommit( false );
	echo "<br>Inicia<br>";
	$sql = "SELECT * from ec_movimiento_detalle_proveedor_producto LIMIT 100";
	$link->query( $sql ) or die( "Error : {$sql}  {$link->error}" );
	//sleep(600);
	echo "<br>Fin";
	$link->autocommit( true );
?>