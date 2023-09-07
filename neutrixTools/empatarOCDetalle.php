<?php
	include('config.inc.php');
	$link_local = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	if( $link_local->connect_error ){
		die( "Error al conectar con la Base de Datos Local : " . $link_local->connect_error);
	}else{
		echo "<p>Conectado en Local</p>";
	}
	$link_local->set_charset("utf8mb4");

	$link_remote = mysqli_connect( 'lacasadelasluces.com', 'wwlaca_production2022', 'ZI6&knjM1**#', 'wwlaca_sistema_general');
	if( $link_remote->connect_error ){
		die( "Error al conectar con la Base de Datos Línea : " . $link_remote->connect_error);
	}else{
		echo "<p>Conectado en Línea</p>";
	}
	$link_remote->set_charset("utf8mb4");
	$link_remote->autocommit( false );
	

	$sql= "SELECT id_proveedor_producto, id_oc_detalle FROM ec_oc_detalle WHERE id_proveedor_producto IS NOT NULL";
	$stm = $link_local->query( $sql ) or die( "Error al consultar los id´s de proveedor producto : {$link_local->error}" );

	$resp = "";
	$counter = 1;
	while ( $row = $stm->fetch_assoc() ) {
		$resp .= ( $resp == "" ? "" : ";\n" );
		$sql = "SELECT id_proveedor_producto FROM ec_proveedor_producto WHERE id_proveedor_producto = {$row['id_proveedor_producto']}";
		$stm_1 = $link_remote->query( $sql ) or die( "Error al consultar si existe el proveedor - producto : {$link_remote->error}" );
		if( $stm_1->num_rows == 1 ){
			$sql = "UPDATE ec_oc_detalle SET id_proveedor_producto = {$row['id_proveedor_producto']} WHERE id_oc_detalle = {$row['id_oc_detalle']}";
			$resp .= $sql;
			$link_remote->query( $sql ) or die( "Error al actualizar el id de proveedor producto : {$link_remote->error} {$sql}" );
			$counter ++;
		}
			
	}
	$link_remote->autocommit( true );
	echo "<textarea>{$resp}</textarea>";
	echo $counter;


?>