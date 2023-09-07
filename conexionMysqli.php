<?php
	include('config.inc.php');
	$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	if( $link->connect_error ){
		die( "Error al conectar con la Base de Datos : " . $link->connect_error);
	}
	$link->set_charset("utf8mb4");
?>