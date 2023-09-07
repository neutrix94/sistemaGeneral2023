<?php
	//buscamos la url	
	if(!isset($url_act))	
		$url_act=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	
	$user_id="NO";
	
	if(!isset($cierraSesion))
		$cierraSesion='';
		
	if(!isset($form_login))
		$form_login='';	
	
	
	if($cierraSesion == "YES")
	{
		setcookie($nombre_session, $user_id,time());

	/*implementación Oscar 28.06.2019 para eliminar la cookie*/
		unset($_COOKIE[$nombre_session]);
		session_destroy();
	/*fin de cambio Oscar 28.06.2019*/
		
		header("location: ".$rooturl."index.php");
	}
	
	
	if($form_login == 'YES')
	{
		$sql="SELECT
		    	/*0*/sys_users.id_usuario AS user_id,
				/*1*/sys_users.nombre AS user_name,
				/*2*/apellido_paterno AS user_ap,
				/*3*/apellido_materno AS user_am,
				/*4*/sys_users.sincroniza AS user_sinc,/**** se consulta columna sincroniza para acceder a archivos de sincronizacion de OSCAR**/
				/*5*/CONCAT(
			  			sys_users.nombre,
						IF(apellido_paterno IS NULL, '', CONCAT(' ', apellido_paterno)),
						IF(apellido_materno IS NULL, '', CONCAT(' ', apellido_materno))
			  		) AS user_fullname,
				/*6*/login AS user_login,
				/*7*/sys_users.telefono AS user_tel,
				/*8*/correo AS user_mail,
				/*9*/sys_sucursales.id_sucursal AS sucursal_id,
				/*10*/sys_sucursales.nombre AS sucursal_name,
				/*11*/sys_users.vende_mayoreo as mayoreo,
				/*12*/sys_users.tipo_perfil as perfil_usuario, /*implemetación Oscar 22.03,2018 para perfiles de usuario*/
				/*13*/IF((SELECT id_sucursal FROM sys_sucursales WHERE acceso=1)=-1,'linea','local') as user_tipo_sistema,/*implementación de Oscar 28.06.2018 para indicar sis se trata de un sistema en línea o local*/
				/*14*/sys_users.id_sucursal as sucursal_usuario,
				/*15*/IF(sys_users.id_usuario=sys_sucursales.id_encargado,1,0)as es_encargado
			  FROM sys_users
			  JOIN sys_sucursales ON sys_sucursales.id_sucursal = '$sucursal_user'
			  WHERE login='$login_user'
			  AND contrasena=md5('$pass_user')
			  AND (sys_sucursales.id_sucursal = sys_users.id_sucursal OR sys_users.id_sucursal = -1)";
		//die($sql);	  
			  
		$res=mysql_query($sql);
		if(!$res){
			Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
		}
/*implementación de Oscar 17.09.2018 para no dejar loguearse si la hora del servidor es la incorrecta*/
		$sql="SELECT  IF((SELECT now())<(SELECT CONCAT(ma.fecha,' ',ma.hora) FROM ec_movimiento_almacen ma order by ma.id_movimiento_almacen desc LIMIT 1),0,1)";
		$eje=mysql_query($sql)or die("Error al verificar hora correcta del servidor!!!\n\n".$sql."\n\n".mysql_error());
		$hora_correcta=mysql_fetch_row($eje);
		if($hora_correcta[0]==0){
			//Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
			$pant='<div style="position:absolute;background-image:url(\'img/img_casadelasluces/bg8.jpg\');width:100%;height:100%;top:0;left:0;">';
					$pant.='<p align="center" style="font-size:30px;color:red;">';
						$pant.='<img src="img/warning.gif">';
						$pant.='<br><b>La hora del servidor es incorrecta!!! <br>Pida al encargado que verifique la hora para poder iniciar sesión!!!!</b>';
						$pant.='<br><br><input type="button" value="Aceptar" style="padding:15px;font-size:25px;border-radius:10px;" onclick="location.href=\'index.php?\'">';
					$pant.='</p>';
			$pant.='</div>';
			die($pant);
		}
/*Fin de cambio Oscar 03.04.2019*/

		
		$num=mysql_num_rows($res);	  
		if($num <= 0){
			//die("WTF!");			
			$smarty->assign("error_login", "YES");			
		}
		else
		{
	
			$row=mysql_fetch_assoc($res);
			extract($row);

/**/
	$sql="(SELECT logueo_perfil 
		FROM sys_users_perfiles WHERE id_perfil={$perfil_usuario})
		UNION
		(SELECT IF(id_sucursal=-1,2,1) FROM sys_sucursales WHERE id_sucursal={$sucursal_id})";
	//die($sql);
	$eje_valida_log=mysql_query($sql);
	if(!$eje_valida_log){
		die("Error al consultar el tipo de logueo del perfil de usuario!!!\n\n".mysql_error()."\n\n".$sql);
	}
	$row_valida_tipo_log=mysql_fetch_row($eje_valida_log);
	if($row_valida_tipo_log[0]!=-1 && $row_valida_tipo_log[0]!=$row_valida_tipo_log[1]){
		die('<script>alert("El usuario no puede loguearse en este sistema, contacte al administrador del sistema!!!");location.href="index.php";</script>');
	}
/**/	

/*Implementación Oscar 03.04.2019 para validar que el usuario tenga asistencia en caso de que la sucursal requiera este paso*/	
	$sql="SELECT 
			IF(cs.solicitar_asistencia_iniciar_sesion=0,
				1,
				IF({$sucursal_usuario}=-1 OR {$es_encargado}=1,
					1,
					IF((SELECT count(*) FROM ec_registro_nomina WHERE id_empleado={$user_id} AND fecha IN(SELECT current_date()) )>0,
						1,
						0
					)
				)
			)
		FROM sys_sucursales s
		LEFT JOIN ec_configuracion_sucursal cs ON s.id_sucursal=cs.id_sucursal
		AND s.id_sucursal={$sucursal_id}";
	$validacion_login=mysql_query($sql)or die("Error al consultar asistencia del usuario en la sucursal!!!\n".mysql_error().mysql_error());
	$valid_log=mysql_fetch_row($validacion_login);
	if($valid_log[0]==0){
		die('<script>alert("Es necesario que registre su asistencia antes de iniciar sesión!!!");location.href="index.php";</script>');
	}
/*Fin de cambio Oscar 03.04.2019*/

		//Creamos la cookie			
			setcookie($nombre_session, $user_id."|".$sucursal_id."|".$user_sinc."|".$mayoreo.
			"|".$perfil_usuario."|".$user_tipo_sistema, 0);//se agrega $user sinc (OSCAR) //sq quitó time()+60*$dur_session 11.06.2018
			//se agrega $perfil_usuario 22.03.2018
			
			$_SESSION["mayoreo"]=$mayoreo;
			$_SESSION["user_id"]=$user_id;
			$_SESSION["user_name"]=$user_name;
			$_SESSION["user_ap"]=$user_ap;
			$_SESSION["user_am"]=$user_am;
			$_SESSION["user_fullname"]=$user_fullname;
			$_SESSION["user_login"]=$user_login;
			$_SESSION["user_tel"]=$user_tel;
			$_SESSION["user_mail"]=$user_mail;
			$_SESSION["user_group"]=$user_group;
			$_SESSION["user_sinc"]=$user_sinc;
			$_SESSION["perfil_usuario"]=$perfil_usuario;//se agrega el 22.03.2018 por Oscar
			$_SESSION["user_tipo_sistema"]=$user_tipo_sistema;//se agrega el 28.06.2018 por Oscar

/*implementación Oscar 04.10.2018*/
		$sql_1="SELECT count(*) from sys_respaldos WHERE realizado=0";
		//echo $sql;
		$eje=mysql_query($sql_1)or die("Error al consultar si es respaldo pendiente de configurar!!!");
		$resp_pend=mysql_fetch_row($eje);
		if($resp_pend[0]>0){
			//die("respaldo_pendiente");
		echo '<script>location.href="code/especiales/configInicial/configuracionInicial.php";</script>';
		}else{

/*implementacion Oscar 01.10.2019 para mandar a restauración con el usuario especial de restauración*/
		$sql="SELECT id_usuario FROM sys_users WHERE login='$login_user' AND contrasena=md5('$pass_user')";
		$eje=mysql_query($sql)or die("Error al validar el usuario especial!!!<br>".mysql_error()."<br>".$sql);
		if($num <= 0){		
			$smarty->assign("error_login", "YES");			
			die("1");
		}else{
			$r_esp=mysql_fetch_row($eje);
			if($r_esp[0]==-1){
				header("location: ".$rooturl."code/especiales/configInicial/configuracionInicial.php");
				die('');
			}
			//die($r_esp);
		}
/*Fin de cambio Oscar 01.10.2019*/

/*fin de cambio 04.10.2018*/
			header("location: $url_act");
			//die($user_id);
		}
		
		}//fin de else
	}
	else{
		
		if(!$_COOKIE[$nombre_session]){
			$user_id="NO";
			//die("??");
		}else{
			$arr=$_COOKIE[$nombre_session];
			$arr=explode('|', $arr);
			$user_id=$arr[0];	
			$user_sucursal=$arr[1];
			$user_sinc=$arr[2];//cremos variable de sincronizacion(OSCAR)
			$mayoreo=$arr[3];//cremos variable de mayoreo(OSCAR)
			$perfil_usuario=$arr[4];//creamos variable de perfil de usuario 22.03.2018
			$user_tipo_sistema=$arr[5];//creamos variable de perfil de usuario 22.03.2018
			
			$sql="SELECT
				  sys_users.id_usuario AS user_id,
				  sys_users.nombre AS user_name,
				  apellido_paterno AS user_ap,
				  apellido_materno AS user_am,
				  CONCAT(
					sys_users.nombre,
					IF(apellido_paterno IS NULL, '', CONCAT(' ', apellido_paterno)),
					IF(apellido_materno IS NULL, '', CONCAT(' ', apellido_materno))
				  ) AS user_fullname,
				  login AS user_login,
				  sys_users.telefono AS user_tel,
				  correo AS user_mail,
				  sys_sucursales.id_sucursal AS sucursal_id,
			      sys_sucursales.nombre AS sucursal_name
				  FROM sys_users
				  JOIN sys_sucursales ON sys_sucursales.id_sucursal = '$user_sucursal'  
				  WHERE sys_users.id_usuario='$user_id'";
			//die($sql);	  
				  
			$res=mysql_query($sql);
			if(!$res)
			{
				Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
			}
			$row=mysql_fetch_assoc($res);
			extract($row);
				
		}
	}	
//verificamos si es un respaldo pendiente de configurar	
	
	//Verificamos si no esta atorado
	$sql="	SELECT
			IF(	TIMEDIFF(NOW(), ultima_sincronizacion) > '00:03:00',
			1,
			0)
			FROM ec_sincronizacion
			WHERE en_proceso=1
			AND es_server=0";
			
	$res=mysql_query($sql);
	$row=mysql_fetch_row($res);
	
	if($row[0] == '1')
	{
		mysql_query("UPDATE ec_sincronizacion SET en_proceso=0");
	}		
	
	
	
	//Checamos la ultima sincronizacion
	$sql="	SELECT
			IF(
				(
					TIMEDIFF(NOW(), ultima_sincronizacion) > CONCAT('00:', IF(periodo < 10, CONCAT('0', periodo), periodo), ':00')
					OR ultima_sincronizacion='0000-00-00 00:00:00'
				) AND en_proceso=0 AND es_server=0,
				1,
				0
			),
			ruta_php,
			sincroniza
			FROM ec_sincronizacion
			JOIN sys_users ON id_usuario='$user_id'";
			
	$res=mysql_query($sql) or die(mysql_error());
	
	if(mysql_num_rows($res) > 0)
	{
	
		$row=mysql_fetch_row($res);
	
	
	
		//die("Sincroniza: ".$row[2]);
	
		if($row[0] == '1' && $row[2] == '1')
		{
	
			if(filesize("$rootpath/respaldos/logSincro.txt") > 1000000)
				unlink("$rootpath/respaldos/logSincro.txt");
	
	
			$ar=fopen("$rootpath/respaldos/logSincro.txt", "at");
			if($ar)
			{
				fwrite($ar, "\n\n\nSincronizacion detectada: ".date('Y-m-d H:i:s'));
			}
		
			$sOp=php_uname() ;
			
			if(strstr($sOp, "Linux"))
				$sOp="Linux";
			
			if(strstr($sOp, "Windows"))
				$sOp="Windows";	
			
			
			$cadena="\nNo se encontro SO valido ".date('Y-m-d H:i:s');	
			
		
		
		
			$sql="UPDATE ec_sincronizacion SET en_proceso=1";
			mysql_query($sql);
				
				
			if($ar)
			{
				fwrite($ar, "\nSe marco en proceso la operacion ".date('Y-m-d H:i:s'));
			}
		
			if($sOp == "Linux")
				$comando="php $rootpath/respaldos/sincroniza.php - $rootpath 2> $rootpath/respaldos/errores.log";
			if($sOp == "Windows")
				$comando=$row[1]."/php.exe $rootpath/respaldos/sincroniza.php - $rootpath";	
		
			unset($return);
			$salida = system($comando, $return);
	
	
			if($return == 0)
			{
				$cadena="\nSe mando a llamar exitosamente el cron en Linux ".date('Y-m-d H:i:s');
			}
			else
			{
				$cadena="\nError al ejecutar el cron ".date('Y-m-d H:i:s');
				
				$sql="UPDATE ec_sincronizacion SET en_proceso=0";
				mysql_query($sql);
				
				
				if($ar)
				{
					fwrite($ar, "\nSe desmarco en proceso la operacion ".date('Y-m-d H:i:s'));
				}
			}
		
		
			if($ar)
			{
				fwrite($ar, $cadena);
			}
		
			fclose($ar);	
		
		}
	}
	


?>