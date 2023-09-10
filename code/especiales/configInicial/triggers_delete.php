<?php
	include('../../../conexionMysqli.php');
	$sql = "SHOW TRIGGERS";
	$stm = $link->query( $sql ) or die( "Error al listar triggers : {$link->error}" );
	$link->autocommit( false );
	$c = 0;
	while ( $row = $stm->fetch_assoc() ) {
		$sql = "DROP TRIGGER IF EXISTS {$row['Trigger']}";
		$stm_del = $link->query( $sql ) or die( "Error al eliminar triggers : {$link->error}" );
		$c++;
	}
	die( "CONTADOR Triggers eliminados : " . $c );
	$link->autocommit( true );
?>