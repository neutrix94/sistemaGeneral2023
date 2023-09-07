<?php

	include("../../conectMin.php");
/*implementación Oscar 04.10.2018*/
	$sql="INSERT INTO sys_respaldos ( id_respaldo, id_usuario, fecha, hora, observaciones, realizado ) VALUES
	( NULL, {$user_id}, now(), now(), 'Respaldo generado por el usuario $user_id desde $user_sucursal', 0 )";
	$eje=mysql_query($sql)or die("Error al generar el registro de sincronización\n\n".$sql."\n\n".mysql_error());
	$id_respaldo=mysql_insert_id();
	$sql="UPDATE sys_respaldos SET folio_unico = 'LINEA_RESPALDO_{$id_respaldo}' WHERE id_respaldo = $id_respaldo";
	$eje=mysql_query($sql) or die("Error al actualizar el folio_unico del respaldo!!!\n\n".$sql."\n\n".mysql_error());
/*fin de cambio Oscar 04.10.2018*/

/**********************Aqui se condiciona que comando se usa de acuerdo al O.S.****************/ 
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){

		$comando="c:/xampp/mysql/bin/mysqldump.exe -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys $dbName > ../../respaldos/".date('Ymd')."casa.sql 2>../../respaldos/errores.log";
	}else{
		//$comando="mysqldump -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys $dbName > ../../respaldos/".date('Ymd')."casa.sql 2>../../respaldos/errores.log";
		$comando="/Applications/XAMPP/bin/mysqldump -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys --triggers --routines --skip-opt $dbName > ../../respaldos/".date('Ymd')."casa.sql 2>../../respaldos/errores.log";
	}
/********************Este es el comando que deberia de reemplazar a los usuarios pero si se utiliza no hace el respaldo; gener un error*******************

	$comando="c:/xampp/mysql/bin/mysqldump.exe -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys --user=nombre  $dbName > ../../respaldos/".date('Ymd')."casa.sql 2>../../respaldos/errores.log";

	fuente:
	https://www.linuxtotal.com.mx/index.php?cont=info_admon_021
					|		|																						|
	--user=nombre	|-u nom	|El nombre de usuario de MySQL para conectarse al servidor indicado en la opción --host.|
					|		|																						|
/**************************/	

//$comando=utf8_decode($comando);
	
	
	//$comando="ls -l";
	
	//echo $comando."\n\n";
	
	unset($return);
	$salida = system($comando, $return);
	
	
	if($return == 0)
	{
		echo "exito|$rooturl/respaldos/".date('Ymd')."casa.sql";
	}
	else
	{			
		echo "Error, no se pudo generar el respaldo de la base de datos, intentalo mas tarde.";
	}
	
	
	$ar=fopen("../../respaldos/errores.log", "at");
	if($ar)
	{
		fwrite($ar, "\nComando ejecutado: $comando\nHora:".date('H:i:s'));
	}
	fclose($ar);

	$sql="UPDATE sys_respaldos SET realizado=1 WHERE id_respaldo=$id_respaldo";
	$eje=mysql_query($sql) or die("Error al actualizar el estatius del respaldo!!!\n\n".$sql."\n\n".mysql_error());
?>