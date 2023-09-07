<?php
	include( '../../conect.php' );
	$sql = "INSERT INTO ec_users_scores ( score_id, user_id, sucursal_id, score, score_value, created_at, sincronizar)
			VALUES ( NULL, $user_id, $sucursal_id, {$_POST['score']}, ROUND({$_POST['score']} * 3.33 ), NOW(), 1 )";
	$eje = mysql_query( $sql )or die ( "Error al insertar calificación de empleado : " . mysql_error() );
	echo "Calificación guardada, gracias por su preferencia";
?>