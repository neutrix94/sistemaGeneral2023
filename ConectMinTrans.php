<?php

	//incluimos la libreria de configuraciones generales
	include('config.inc.php');	
	
	//incluimos la libreria general de funciones
	//include($codepath."/general/funciones.php");		
	//Conectamos con la base de datos
	$link=@mysql_connect($dbHost, $dbUser, $dbPassword);
	
	if(!$link)
	{	
		die("Error al conectar con la base de datos");
	}	
	
	
	$db=@mysql_select_db($dbName);
	
	
	if(!$link)
	{	
		die("Error al conectar con la base de datos");
	}
	
	mysql_set_charset("utf8");
	//mysql_query("SET time_zone='-05:00'") or die(mysql_error());
	//mysql_query("SET time_zone='America/Mexico_City'") or die(mysql_error());
	//Checamos sesion
		
	require('include/sesiones.php');
	
		
	if($user_id == 'NO')
    {
        if($redirect == 'SI')
        {
            //echo "SIIII";
            header("location: ".$rooturl."index.php");
        }    
        else
		  die("La sesion ha caducado.");
    } 
	
	
	$sq="SELECT
  	     pf.nombre,
  	     IF(fu.permiso IS NULL, 0, fu.permiso)
  	     FROM sys_permisos_funciones pf
  	     LEFT JOIN sys_permisos_funcusers fu ON pf.id_permiso_funcion = fu.id_permiso_funcion 
  	     AND fu.id_perfil=$perfil_usuario";//modificación Oscar 23.03.2018 por implementación de perfiles id_usuario=$user_id
  	      
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
	}
	
	
?>