<?php
	include('../../../../conexionMysqli.php');
	/*
		id : user_id,
		date : new_date,
		start : new_start_hour,
		end : new_final_hour
	*/
	$initial_hour = $_POST['start'];
	$final_hour = $_POST['end'];
	$date = $_POST['date'];
	$user = $_POST['id'];
//comprueba que no haya registros de nomina pendientes de registrar salida
	$sql = "SELECT
				id_registro_nomina
			FROM ec_registro_nomina
			WHERE fecha = '{$date}'
			AND hora_salida = '00:00:00'";
	$eje = $link->query( $sql ) or die( "Error al consultar si hay registros pendientes : {$link->error}" );
	//die('num : ' . $eje->num_rows );
	if( $eje->num_rows > 0 ){
		die( "Hay registros de asistencia pendientes de marcar la hora de salida, verifique y vuelva a intentar!" );
	}
//comprueba que el registro no existe
	$sql = "SELECT 
				id_registro_nomina
			FROM ec_registro_nomina
			WHERE 
			fecha = '{$date}'
			AND id_empleado = '{$user}'
			AND (
				( '{$initial_hour}' BETWEEN hora_entrada AND hora_salida )
				OR ( '{$final_hour}' BETWEEN hora_entrada AND hora_salida )
			)";
	$eje = $link->query( $sql ) or die( "Error al consultar si el registro ya existe : {$link->error}" );
	//die('num : ' . $eje->num_rows );
	if( $eje->num_rows > 0 ){
		die( "La fecha y hora que intenta agregar choca con otros registros de nómina existentes, verifique y vuela a intentar!" );
	}
//inserta el registro de nomina
	$sql = "INSERT INTO ec_registro_nomina 
				( id_registro_nomina, fecha, hora_entrada, hora_salida, id_empleado, id_sucursal, fecha_alta)
			VALUES ( NULL, '{$date}', '{$initial_hour}', '{$final_hour}', '{$user}', 
				(SELECT id_sucursal FROM sys_users WHERE id_usuario = '{$user}'),
				NOW() )";
	$eje = $link->query( $sql ) or die( "Error al insertar el registro de nómina : {$link->error}" );
	die('ok');
?>