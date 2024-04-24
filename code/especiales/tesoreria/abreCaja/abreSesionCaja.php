<?php
/*version casa 1.1*/
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
	$sql="SELECT *
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
	//Generamos el Folio
		$sql="SELECT
				CONCAT(
					/*IF((SELECT suc.id_sucursal FROM sys_sucursales suc WHERE suc.acceso=1)=-1,'LNA',''),*/
					'SC',
					s.prefijo,
					IF(
						ISNULL(MAX(CAST(REPLACE(folio, CONCAT('SC',s.prefijo), '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio, CONCAT('SC',s.prefijo), '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_sesion_caja sc
				LEFT JOIN sys_sucursales s ON sc.id_sucursal=s.id_sucursal
				WHERE REPLACE(folio,CONCAT('SC',s.prefijo), '') REGEXP ('[0-9]')
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
		$sql="INSERT INTO ec_sesion_caja ( id_sesion_caja, id_cajero, id_sucursal, folio, fecha, hora_inicio, hora_fin, 
			total_monto_ventas, total_monto_validacion, verificado, id_usuario_verifica, observaciones, caja_inicio, sincronizar )
			VALUES(null,{$user_id}, {$sucursal_id}, '{$folio}',now(),now(),'00:00:00',
			0, 0, 0,-1,'','{$_POST['cambio_caja']}', 1 )";
		$eje_1=mysql_query($sql);
		if(!$eje_1){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el registro de inicio de sesión de caja!!!\n".$error);
		}
	//inserta afiliacion(es)
		
	//inserta terminal(es)

/*Cambio Oscar 2021 para mandar aviso si la caja no corresponde a un corte anterior*/
		$new_id = mysql_insert_id();
//inserta afiliaciones
		$afiliaciones = explode( ",", $_POST['afiliaciones'] );
		foreach ($afiliaciones as $key => $afiliacion) {
			$sql = "INSERT INTO ec_sesion_caja_afiliaciones ( id_sesion_caja, id_cajero, id_afiliacion, habilitado, insertada_por_error_en_cobro )
			VALUES ( '{$new_id}', '{$user_id}', '{$afiliacion}', 1, 0 )";
			$stm = mysql_query( $sql ) or die( "Error al insertar afiliacion en sesion de caja : " . mysql_error() );
			$id_sesion_caja_afiliacion = mysql_insert_id();
			$sql = "CALL SincronizacionSesionCajaAfiliaciones(  'insert', {$id_sesion_caja_afiliacion} );";
			$stm = mysql_query( $sql ) or die( "Error al ejecutar procedure para sincronizar afiliacion en sesion de caja : " . mysql_error() );
		}
//inserta terminales
		$terminales = explode( ",", $_POST['terminales'] );
		foreach ($terminales as $key => $terminal) {
			$sql = "INSERT INTO ec_sesion_caja_terminales ( id_sesion_caja, id_cajero, id_terminal, habilitado, insertada_por_error_en_cobro )
			VALUES ( '{$new_id}', '{$user_id}', '{$terminal}', 1, 0 )";
			$stm = mysql_query( $sql ) or die( "Error al insertar terminal en sesion de caja : " . mysql_error() );
			$id_sesion_caja_terminal = mysql_insert_id();
			$sql = "CALL SincronizacionSesionCajaTerminales(  'insert', {$id_sesion_caja_terminal} );";
			$stm = mysql_query( $sql ) or die( "Error al ejecutar procedure para sincronizar terminal en sesion de caja : " . mysql_error() );
		}
		$sql = "SELECT 
				sc.caja_final,
				s.nombre
			FROM ec_sesion_caja sc
			LEFT JOIN sys_sucursales s ON s.id_sucursal = sc.id_sucursal
			WHERE sc.id_sucursal = '{$user_sucursal}'
			AND sc.id_sesion_caja != '{$new_id}'
			ORDER BY id_sesion_caja DESC
			LIMIT 1";
		$eje = mysql_query( $sql ) or die( "Error al consultar el corte de caja Anterior! " . mysql_error() );
		$r_c_a = mysql_fetch_row( $eje );

		if( $r_c_a[0] != $_POST['cambio_caja'] ){
			include('../../plugins/sendMail.php');
			$mail = new sendMail( '../../../../' );
			$mails = $mail->getSystemEmails( 'ec_sesion_caja' );
			$email_content = "<p>El monto de cambio inicial en caja es diferente al monto del corte anterior en la sucursal : {$rw[0]}</p>";
			$email_content .= "<p>Monto de caja anterior : $ <b>{$r_c_a[0]}</b></p>";
			$email_content .= "<p>Monto de caja inicial : $ <b>{$_POST['cambio_caja']}</b></p>";
			$mail->sendMailTo( "Diferencia de cambio en caja durante el Incio de Caja en  {$r_c_a[1]} {$folio} ", $email_content, $mails, null );
		}
/*Fin de cambio Oscar 2021*/

		mysql_query("COMMIT");//autorizamos transacción
	}else{
		die("No se pudo iniciar sesión; el usuario no es cajero o no pertenece a la sucursal!!!\nVerifique sus datos y vuelva a intentar");
	}
	die('ok');

?>