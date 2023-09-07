<?php

	include("../panel/config.inc.php");
	extract($_GET);
	
	
	$link=@mysql_connect($dbHost, $dbUser, $dbPassword);	
	if(!$link)
	{	
		die("$callback('Error al conectar con el servidor de base de datos');");
	}	
	
	$db=@mysql_select_db($dbName);	
	if(!$db)
	{	
		die("$callback('Error al conectar con el servidor de base de datos');");
	}
	
	
	$sql="SELECT mensaje FROM eye_mensaje WHERE id_mensaje=$id_mensaje";
	
	$res=mysql_query($sql) or die("$callback('".mysql_error()."')");
	$row=mysql_fetch_row($res);
	
	echo "$callback('".$row[0]."')";
	
?>