<?php
	include("../../../conectMin.php");
	//id_registro="+id+"&hora_s="+h_s+"&pss="+p_e
//extraemos datos por get
	extract($_GET);
	$pass=md5($pss);//encriptamos contraseña
//verificamos password de encargado
	$sql="SELECT u.id_usuario
			FROM sys_users u
			JOIN sys_sucursales s ON s.id_encargado=u.id_usuario
			WHERE s.id_sucursal='$user_sucursal'
			AND u.contrasena='$pass'";
	$eje=mysql_query($sql)or die("Error al consultar password de encargado\n\n".$sql."\n\n".mysql_error());
	if(mysql_num_rows($eje)!=1){
		die('ok|El password del encargado en incorrecto, verifique y vuelva a intentar!!!');
	}
//Actualizamos hora de Salida del registro
	$sql="UPDATE ec_registro_nomina SET hora_salida='$hora_s' WHERE id_registro_nomina='$id_registro'";
	$eje=mysql_query($sql)or die("Error al actualizar el registro de Salida!!!\n\n".$sql."\n\n".mysql_error());
	echo "ok|ok";
?>