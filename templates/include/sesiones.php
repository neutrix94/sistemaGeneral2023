<?php


	//buscamos la url
	
	if(!isset($url_act))	
		$url_act=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];


	//validamos si viene de un logeo
	
	//die($form_login);
	
	$user_id="NO";
	
	if(!isset($cierraSesion))
		$cierraSesion='';
		
	if(!isset($form_login))
		$form_login='';	
	
	
	if($cierraSesion == "YES")
	{
		setcookie($nombre_session, $user_id, time());
		//die("?");
		header("location: ".$rooturl."index.php");
	}
	
	
	if($form_login == 'YES')
	{
		//validamos la existencia del usuario y contraseña
		
		//die("?");
		
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
			  JOIN sys_sucursales ON sys_sucursales.id_sucursal = '$sucursal_user'
			  WHERE login='$login_user'
			  AND contrasena=md5('$pass_user')
			  AND (sys_sucursales.id_sucursal = sys_users.id_sucursal OR sys_users.id_sucursal = -1)";
		//die($sql);	  
			  
		$res=mysql_query($sql);
		if(!$res)
		{
			Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
		}
		
		$num=mysql_num_rows($res);	  
		if($num <= 0)
		{
			//die("WTF!");			
			$smarty->assign("error_login", "YES");			
		}
		else
		{
			$row=mysql_fetch_assoc($res);
			extract($row);
			
			//die("SI LOG");
			
			//Creamos la cookie			
			
			setcookie($nombre_session, $user_id."|".$sucursal_id, time()+60*$dur_session);
			
			//echo $nombre_session;
			
			$_SESSION["user_id"]=$user_id;
			$_SESSION["user_name"]=$user_name;
			$_SESSION["user_ap"]=$user_ap;
			$_SESSION["user_am"]=$user_am;
			$_SESSION["user_fullname"]=$user_fullname;
			$_SESSION["user_login"]=$user_login;
			$_SESSION["user_tel"]=$user_tel;
			$_SESSION["user_mail"]=$user_mail;
			$_SESSION["user_group"]=$user_group;
			/*$_SESSION["user_escuela"]=$user_escuela;
			$_SESSION["escuela"]=$escuela;*/
			
			//die("location: $url_act");
			
			header("location: $url_act");
			//die($user_id);
		}
	}
	else
	{
		//die("FREAK");
		//buscamos si existe session abierta
		
		if(!$_COOKIE[$nombre_session])
		{
			$user_id="NO";
			
			//die("??");
		}
		else
		{
			$arr=$_COOKIE[$nombre_session];
			$arr=explode('|', $arr);
			$user_id=$arr[0];	
			$user_sucursal=$arr[1];
			
			//die("SI COOKIE: $user_id");
			
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
	
	
	
	


?>