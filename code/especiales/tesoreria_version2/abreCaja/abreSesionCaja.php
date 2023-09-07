<?php
/*version 30.10.2019*/
	include('../../../../conectMin.php');
	$log=$_POST['login'];
	$pss=md5($_POST['contrasena']);
//extraemos la fecha actual desde mysql
	$sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
	$eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
	$fecha_actual=mysql_fetch_row($eje);
//consultamos si la sucursal es multicajero
	$sql="SELECT multicajero FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar si la  sucursal admite multicajero");
	$r=mysql_fetch_row($eje);
	$multicajero=$r[0];

/**********************Validaciones de un solo cajero***********************/
if($multicajero==0){
//vemos si hay un logueo del mismo dia
	$sql="SELECT 
			COUNT(sc.id_sesion_caja),
			CONCAT(u.nombre,' ',u.apellido_paterno) as nombre_logueo
		FROM ec_sesion_caja sc
		LEFT JOIN sys_users u ON sc.id_cajero=u.id_usuario
		WHERE sc.fecha LIKE '%$fecha_actual[0]%'
		AND sc.hora_fin='00:00:00' 
		AND sc.id_sucursal='$sucursal_id'";
	//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar si hay logueo activo en el día actual!!!\n".mysql_error());
	$r=mysql_fetch_row($eje);
	
	if($r[0]>0){
		die("El cajero ".$r[1]." ya esta logueado el día de hoy; Pida que cierre su sesión de caja para continuar!!!");
	}

//vemos si hay una sesion del mismo cajero que no fue cerrada
	$sql="SELECT 
			sc.id_sesion_caja,
			DATE_FORMAT(sc.fecha,'%Y-%m-%d')
		FROM ec_sesion_caja sc
		WHERE sc.hora_fin='00:00:00' 
		AND sc.id_sucursal='$sucursal_id'";
	$eje=mysql_query($sql)or die("Error al consultar si hay logueo activo!!!\n".mysql_error());

//	die($sql);
	if(mysql_num_rows($eje)>0){
		while($r=mysql_fetch_row($eje)){
			$sql="UPDATE ec_sesion_caja SET hora_fin='23:59:59',observaciones='1___' WHERE id_sesion_caja=$r[0]";
			$eje_1=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al acualizar el registro de sesion de caja!!!\n".$error);
			}
		}//fin de while
	}
}//fin de si la sucursal no es multicajero

/**********************Validaciones de multicajero***********************/

else if($multicajero==1){
//verificamos que no exista una sesion del mismo dia, mismo usuario que este abierta
	$sql="SELECT 
			COUNT(sc.id_sesion_caja),
			DATE_FORMAT(sc.fecha,'%Y-%m-%d')
		FROM ec_sesion_caja sc
		WHERE sc.hora_fin='00:00:00' 
		AND sc.id_cajero=$user_id
		AND sc.fecha='$fecha_actual[0]'
		AND sc.id_sucursal='$sucursal_id'";
	$eje=mysql_query($sql)or die("Error al consultar si hay logueo activo!!!\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if($r[0]>0){
		die("Este usuario ya tiene un logueó en este día, no es necesario que vuelva a iniciar sesión!!!");
	}
//cerramos la sesión de un día antes si no fue cerrada

}


	mysql_query("BEGIN");//declaramos el inicio de transacción
//checamos que los datos del cajero sean validos y realmente sea un cajero
	$sql="SELECT count(u.id_usuario)
		FROM sys_users u
		WHERE u.login='$log'
		AND u.contrasena='$pss'
/*		AND u.tipo_perfil=7*/
		AND u.id_sucursal=$sucursal_id";
	$eje=mysql_query($eje);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transacción
		$eje=mysql_query($sql)or die("Error al verificar datos del cajero!!!\n".$error);
	}

	if(mysql_num_rows($eje)==1){
	//consultamos el tipo de sistema
		$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al consultar el tipo de sistema!!!".$error);
		}
		$r_suc=mysql_fetch_row($eje);
	//Generamos el Folio
		$sql="SELECT
				CONCAT(
					IF((SELECT suc.id_sucursal FROM sys_sucursales suc WHERE suc.acceso=1)=-1,'LNA',''),
					'SC',
					s.prefijo,
					IF(
						ISNULL(MAX(CAST(REPLACE(folio, CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio, CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_sesion_caja sc
				LEFT JOIN sys_sucursales s ON sc.id_sucursal=s.id_sucursal
				WHERE REPLACE(folio,CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') REGEXP ('[0-9]')
				AND s.id_sucursal='$user_sucursal'";
		$eje_1=mysql_query($sql);
		if(!$eje_1){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al construir el folio de sesión de caja!!!\n".$error);
		}
		$fol=mysql_fetch_row($eje_1);
		$folio=$fol[0];	
	//insertamos la sesion de caja
		$sql="INSERT INTO ec_sesion_caja VALUES(null,$user_id,$sucursal_id,'$folio',now(),now(),'00:00:00',0,0,0,0,1,-1,'')";
		$eje_1=mysql_query($sql);
		if(!$eje_1){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el registro de inicio de sesión de caja!!!\n".$error);
		}
		mysql_query("COMMIT");//autorizamos transacción
	}else{
//		echo $sql;
		die("No se pudo iniciar sesión; el usuario no es cajero o no pertenece a la sucursal!!!\nVerifique sus datos y vuelva a intentar");
	}
	die('ok');

?>