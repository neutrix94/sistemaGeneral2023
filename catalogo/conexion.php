<?php
	if(!include('../config.inc.php')){
		include('../../config.inc.php');
	}

	$link=@mysql_connect($dbHost,$dbUser,$dbPassword);
	
	if(!$link){	
	
		die ("Sin conexion con el Servidor".mysql_error());
	}	
	
	$db=@mysql_select_db($dbName);
	
	
	if(!$link)
	{	
	
		die ("Sin conexion con la base de datos".mysql_error());
	}	

	mysql_set_charset("utf8");
?>