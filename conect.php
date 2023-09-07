<?php
	$user_group="";
	//incluimos la libreria de configuraciones generales
	include('config.inc.php');	


	//incluimos la libreria general de funciones
	include($codepath."/general/funciones.php");
	
	//inicializamos Smarty
	
	include($smartypath."Smarty.class.php");
	$smarty = new Smarty;	
	
	$smarty->setTemplateDir($template_dir);
	$smarty->setCompileDir($compile_dir);
	
	$smarty->assign("rooturl", $rooturl);
	
	//Conectamos con la base de datos
	$link=@mysql_connect($dbHost, $dbUser, $dbPassword);
	
	if(!$link){	
		Muestraperror($smarty, "Error al conectar con el servidor de base de datos", 'No aplica', mysql_error(), "", 'conect.php');
	}	
	
	$db=@mysql_select_db($dbName);
	
	
	if(!$link)
	{	
		Muestraperror($smarty, "Error al conectar con el servidor de base de datos", 'No aplica', mysql_error(), "", 'conect.php');
	}
	
	mysql_set_charset("utf8");
	/*mysql_query("SET time_zone='-05:00'") or die(mysql_error());
	$eje=mysql_query("SELECT NOW()");
	$r=mysql_fetch_row($eje);
	die($r[0]);
//	mysql_query("SET time_zone='America/Mexico_City'") or die(mysql_error());

	DIE('S');	*/

	
//llenamos el combo de sucursales
	/*$sql="SELECT id_sucursal, nombre FROM sys_sucursales WHERE acceso=1 ORDER BY nombre";*/
/*Modificación de Oscar 28.06.2018 para que si el sistema es línea muestre todas las sucursales, de lo contrario solo se muestra la sucursal de acceso*/
	$sql="SELECT id_sucursal, nombre FROM sys_sucursales WHERE if((SELECT id_sucursal from sys_sucursales where acceso=1)=-1,acceso=0 OR acceso=1,acceso=1) ORDER BY nombre";
/*Fin del cambio*/
	$res=mysql_query($sql);
	if(!$res)
	{
		Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
	}
	$arrZonaids=array();
	$arrZonanames=array();
	$num=mysql_num_rows($res);
	for($i=0;$i<$num;$i++)
	{
	/*implementación Oscar 26.10.2018 para que no APAREZCA SUCURSAL POR DEFAULT EN LÍNEA*/
		if($num>1&&$i==0){
			array_push($arrZonaids, 0);
			array_push($arrZonanames, '--Seleccionar--');
		}
	/*fin de cambio 26.10.2018*/
	
		$row=mysql_fetch_row($res);
		array_push($arrZonaids, $row[0]);
		array_push($arrZonanames, $row[1]);
	}
	
	
	
	
		
	$smarty->assign("arrZonaids", $arrZonaids);
	$smarty->assign("arrZonanames", $arrZonanames);
	
	//Checamos sesion
		
	require('include/sesiones.php');
	
	$smarty->assign("url_act", $url_act);
		
	//die($user_id);
	if($user_id == 'NO')
	{
	    //echo "???";
        
		$smarty->display('general/login.tpl');
		die();
	}
	
    extract($_GET);
    
     //Buscamos imagen del menu
    $sql="SELECT
          m2.icono
          FROM sys_menus m
          JOIN sys_menus m2 ON m.menu_padre = m2.id_menu
          WHERE m.tabla_relacionada = '".base64_decode($tabla)."'
          AND m.no_tabla='".base64_decode($no_tabla)."'";
          
   $res=mysql_query($sql);    
   if(!$res)
   {
       // Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
   }
   else
   {
       if(mysql_num_rows($res) > 0)
       {
       
            $row=mysql_fetch_row($res);
            
            $smarty->assign("imgMenu", $row[0]);
       }
   }  
	
	  
//aqui inicializamos variable de tipo de usuario 22.03.2018
/*	$sql1_1="SELECT prf.id_perfil 
			FROM sys_users_perfiles prf
			LEFT JOIN sys_users usr ON prf.id_perfil=usr.tipo_perfil
			WHERE usr.id_usuario=$user_id";
	$res1_1=mysql_query($sql1_1);	  
	if(!$res1_1)
	{
		Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql1_1, 'conect.php');
	}
	$perf_user=mysql_fetch_row($res1_1);
	$perfil_usuario=$perf_user[0];*/
	//die("perfil: ".$perfil_usuario."\n\nUser_id: ".$user_id.$sql1_1);

//Llenamos el menu	
	$sql="SELECT
	      id_menu,
		  nombre,
		  icono
		  FROM sys_menus
		  WHERE id_menu = menu_padre
		  AND 
		  	(SELECT COUNT(1) 
		  		FROM sys_permisos p 
		  		JOIN sys_menus m ON p.id_menu = m.id_menu/*cambio de Oscar 22.03.2018 para perfiles de usuario*/
		  		LEFT JOIN sys_users_perfiles perf ON p.id_perfil=perf.id_perfil  
		  		LEFT JOIN sys_users usr ON perf.id_perfil=usr.tipo_perfil
		  		WHERE usr.id_usuario=$user_id AND m.menu_padre = sys_menus.id_menu AND p.ver=1) > 0
		  		AND enlistado=1
		/**/
			
		/**/
		  ORDER BY orden";
	//echo $sql;

	$res=mysql_query($sql);	  
	if(!$res)
	{
		Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sql, 'conect.php');
	}
	$menus=array();
	
	$num=mysql_num_rows($res);
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
	
		$sq="SELECT
		     nombre,
			 es_listado,
			 tabla_relacionada,
			 liga,
			 no_tabla
			 FROM sys_menus
			 WHERE menu_padre=".$row[0]."
		
			 AND id_menu <> ".$row[0]."
			 AND (SELECT COUNT(1) FROM sys_permisos p WHERE p.id_perfil=$perfil_usuario AND p.id_menu = sys_menus.id_menu AND p.ver=1) > 0
			 ORDER BY orden";/*cambio del 22.03.2018; se cambia p.id_usuario = $user_id por p.id_perfil=$perfil_usuario*/
		
		$re=mysql_query($sq);
		if(!$re)
		{
			Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sq, 'conect.php');
		}
		
		$aux=array();
		$nu=mysql_num_rows($re);
		for($j=0;$j<$nu;$j++)
		{
			$ro=mysql_fetch_row($re);
			$ro[2]=base64_encode($ro[2]);
			$ro[4]=base64_encode($ro[4]);
			array_push($aux, $ro);
		}		
		array_push($row, $aux);
		
		array_push($menus, $row);
	}	  
	$smarty->assign("menus", $menus);
	
	
	if(!isset($user_sucursal))
		$user_sucursal='';
		
	if(!isset($sucursal))
		$sucursal='';	
	
	
	
   //Buscamos todos los permiso
   
  	$sq="SELECT
  	     pf.nombre,
  	     IF(fu.permiso IS NULL, 0, fu.permiso)
  	     FROM sys_permisos_funciones pf
  	     LEFT JOIN sys_permisos_funcusers fu ON pf.id_permiso_funcion = fu.id_permiso_funcion 
  	     AND fu.id_perfil=$perfil_usuario";//cambio por implementación de perfiles Oscar 23/03/2018 fu.id_usuario=$user_id
  	      
    $re=mysql_query($sq);	
    if(!$re)
	{
		Muestraperror($smarty, "Error al buscar las zonas", mysql_errno(), mysql_error(), $sq, 'conect.php');
	}
	
	$nu=mysql_num_rows($re);
	for($i=0;$i<$nu;$i++)
	{
		$ro=mysql_fetch_row($re);
		$aux=$ro[0];
		$$aux=$ro[1];
		
		//echo $$aux."|";
		
		//print_r($r);
		
		$smarty->assign($ro[0], $ro[1]);
		
	}
	
	
	//Buscamos si tiene permiso para ventas
	
	$sql="SELECT
				nuevo
			FROM sys_permisos
			WHERE id_menu IN (43, 226)
			AND id_perfil=$perfil_usuario";/*cambio del 22.03.2018; se cambia id_usuario = $user_id por id_perfil=$perfil_usuario*/
		
	$res=mysql_query($sql) or die("Error en:".$sql."\n\n".mysql_error());		
	$row=mysql_fetch_row($res);
	
	
	$smarty->assign("ver_pantalla_ventas", $row[0]);
/*implementacion Oscar 2021 para ver la pantalla de ventas responsiva*/
	$row=mysql_fetch_row($res);
	$smarty->assign("ver_pantalla_responsive", $row[0]);
/*fin de cambio Oscar 2021*/

	
	
	
	//Buscamos si tiene autorizaciones pendientes
	$sql="	SELECT
			u.id_usuario
			FROM ec_registro_nomina r
			JOIN ec_nomina_configuracion c ON r.id_sucursal = c.id_sucursal
			JOIN sys_users u ON c.usuario_resp = u.id_usuario
			LEFT JOIN ec_alerta a ON a.fecha = DATE_FORMAT(NOW(), '%Y-%m-%d')
			AND a.tipo = 'code/general/listados.php?tabla=ZWNfcmVnaXN0cm9fbm9taW5h&no_tabla=MA=='
			LEFT JOIN ec_alerta_registro ar ON a.id_alerta = ar.id_alerta AND ar.id_usuario = u.id_usuario
			WHERE r.hora_salida = '00:00:00'
			AND r.id_sucursal=$user_sucursal
			AND u.id_usuario=$user_id
			AND id_alerta_registro IS NULL";
			
			
	$res=mysql_query($sql) or die(mysql_error());		
	
	
	
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);
	
		$sql="INSERT INTO ec_alerta(nombre, fecha, hora, tipo)
							VALUES('Hay registros de nomina sin cerrar', NOW(), NOW(), 'code/general/listados.php?tabla=ZWNfcmVnaXN0cm9fbm9taW5h&no_tabla=MA==')";
							
		mysql_query($sql) or die(mysql_error());		
		
		
		$id_alerta=mysql_insert_id();
		
		
		$sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
									VALUES($id_alerta, ".$row[0].", '', 0)";
									
		mysql_query($sql) or die(mysql_error());									
							
	}
	
	
			
		       
    	
	
	//Enviamos variables de entorno
	
	$smarty->assign("rootpath", $rootpath);
	$smarty->assign("rooturl", $rooturl);
	$smarty->assign("user_id", $user_id);
	$smarty->assign("user_name", $user_name);
	$smarty->assign("user_ap", $user_ap);
	$smarty->assign("user_am", $user_am);
	$smarty->assign("user_fullname", $user_fullname);
	$smarty->assign("user_login", $user_login);
	$smarty->assign("user_tel", $user_tel);
	$smarty->assign("user_mail", $user_mail);
	$smarty->assign("user_group", $user_group);
	$smarty->assign("sucursal_id", $sucursal_id);
	$smarty->assign("sucursal_name", $sucursal_name);
	

?>