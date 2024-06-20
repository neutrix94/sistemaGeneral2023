<?php

	header('Content-Type: text/html; charset=utf-8');

	$main_path = getenv('PATH_STORAGE') ?: '../..';

	if(file_exists("{$main_path}/conexion_inicial.txt")){
//1. Elimina el archivo /conexion_inicial.txt si existe
		unlink("{$main_path}/conexion_inicial.txt");
	}
//2. Crea la cadena con los datos del formulario
	$cadena_datos='';
	$cadena_datos.=base64_encode($_POST['host_local']).'~';
	$cadena_datos.=base64_encode($_POST['ruta_local']).'~';
	$cadena_datos.=base64_encode($_POST['nombre_local']).'~';
	$cadena_datos.=base64_encode($_POST['usuario_local']).'~';
	$cadena_datos.=base64_encode($_POST['pass_local']);

	$cadena_datos.="~<>";

	$cadena_datos.=base64_encode($_POST['host_linea']).'~';
	$cadena_datos.=base64_encode($_POST['ruta_linea']).'~';
	$cadena_datos.=base64_encode($_POST['nombre_linea']).'~';
	$cadena_datos.=base64_encode($_POST['usuario_linea']).'~';
	$cadena_datos.=base64_encode($_POST['pass_linea']);

	$cadena_datos.="<>";
	
	$cadena_datos.=$_POST['ru_or']."~";
	$cadena_datos.=$_POST['ru_des'];
	//die($cadena_datos);
	$cadena_datos.='<>'.$_POST['archivo_jar'];
	
	$cadena_datos .= '<>'.$_POST['impresion'];
	$cadena_datos .= '<>'.$_POST['impresora'];
	$cadena_datos .= '<>'.$_POST['intervalo_impresion'];


	$cadena_datos .= '<>'.$_POST['retraso_sis_sinc'];
	$cadena_datos .= '<>'.$_POST['puerto_sis_sinc'];
	$cadena_datos .= '<>'.$_POST['puerto_sis_imp'];
	$cadena_datos.= '<>'.$_POST['store_id'];
	$cadena_datos.= '<>'.$_POST['system_type'];
				
//3. Crea archivo /conexion_inicial.txt
	$fp = fopen("{$main_path}/conexion_inicial.txt", "w");
		fputs($fp,$cadena_datos);
		fclose($fp);

	if(file_exists("{$main_path}/config.inc.php")){
//4. Elimina archivo /config.inc.php
		unlink("{$main_path}/config.inc.php");
	}

//5. Crea archivo /config.inc.php
	$ini=fopen("{$main_path}/config.inc.php", "w");
	$datos="<?php\n";
	$datos.="session_start();\n";
	$datos.="//Definiciones de base de datos\n";
	$datos.="	\$dbHost='".$_POST['host_local']."';\n";
	$datos.="	\$dbUser='".$_POST['usuario_local']."';\n";
	$datos.="	\$dbPassword='".$_POST['pass_local']."';\n";
	$datos.="	\$dbName='".$_POST['nombre_local']."';\n";
	$datos.="//Definicion de rutas\n";
	$datos.="	if(isset(\$_SERVER['HTTP_HOST'])){\n";
	$datos.="		\$rooturl = ((isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS']=='on') ? 'https://' : 'http://').\$_SERVER['HTTP_HOST'].'/".
		$_POST['ruta_local']."/';\n";
	$datos.="	}\n";
	$datos.="	\$rootpath = dirname(__FILE__);\n";
	$datos.="	\$includepath=\$rootpath.'/include/';\n";
	$datos.="	\$smartypath=\$rootpath.'/include/smarty/';\n";
	$datos.="	\$codepath=\$rootpath.'/code/';\n";
	$datos.="	\$template_dir=\$rootpath.'/templates/';\n";
	$datos.="	\$compile_dir=\$rootpath.'/templates_c/';\n";
	$datos.="//datos de la sesion\n";
	$datos.="	\$nombre_session='casaDev';\n";
	$datos.="	\$dur_session=0;//50000 modificado el Oscar 11.06.2018 para cerrar sesión al cerrar el explorador\n";
	$datos.="	date_default_timezone_set('America/Mexico_City');\nheader('Content-Type: text/html; charset=utf-8');\n?>";
	
	fwrite($ini, $datos);
	fclose($ini);

	
//6. Elimina archivo /conexionDoble.php
	if(file_exists("{$main_path}/conexionDoble.php")){
		unlink("{$main_path}/conexionDoble.php");
	}
//7. Crea archivo /conexionDoble.php
	$ini=fopen("{$main_path}/conexionDoble.php", "w");

	$datos='';
	$datos.="<?php\n";
	$datos.="//incluimos la libreria de configuraciones generales\n";
	$datos.="	include('config.inc.php');";		
	$datos.="//incluimos la libreria general de funciones\n";
	$datos.="	include(\$codepath.\"/general/funciones.php\");\n";

	$datos.="//definimos zona horaria\n";
	$datos.="	date_default_timezone_set('America/Mexico_City');\n";

	$datos.="	\$hostLocal='".$_POST['host_local']."';\n";
	$datos.="	\$userLocal='".$_POST['usuario_local']."';\n";
	$datos.="	\$passLocal='".$_POST['pass_local']."';\n";
	$datos.="	\$nombreLocal='".$_POST['nombre_local']."';\n";//cdelasluces
	$datos.="	\$local=@mysql_connect(\$hostLocal, \$userLocal, \$passLocal);\n";
	$datos.="	//comprobamos conexion local\n";
	$datos.="	if(!\$local){	//si no hay conexion\n";
	$datos.="		echo 'no hay conexion local';//finaliza programa\n";
	$datos.="	}else{\n";
	$datos.="	//echo'conexion local'.\$nombreLocal;\n";
	$datos.="	}\n";
	$datos.="	\$dblocal=@mysql_select_db(\$nombreLocal);\n";
	$datos.="	if(!\$dblocal){\n";
	$datos.="		echo 'BD local no encontrada';\n";
	$datos.="	}else{\n";
	//$datos.="	//echo '<br> bd local encontrada';\n";
	$datos.="	}\n";
	
	$datos.="/***********************************CONEXIONES BD FORANEA*******************************************/\n";

	$datos.="	\$hostLinea='".$_POST['host_linea']."';\n";
	$datos.="	\$userLinea='".$_POST['usuario_linea']."';\n";
	$datos.="	\$passLinea='".$_POST['pass_linea']."';\n";
	$datos.="	\$nombreLinea='".$_POST['nombre_linea']."';\n";
	$datos.="	\$linea=@mysql_connect(\$hostLinea,\$userLinea,\$passLinea);\n";
	//$lnk=@mysql_connect($server, $user, $password);
	$datos.="	\$indicador=\"\";\n";
	$datos.="	if(!\$linea){\ndie('Sin conexión a Línea');\n}\n";
	/*$datos.="	/*Cambio para verificar conexion en recepcion de transferencias(07-11-2017)*\n";
	$datos.="	if(isset(\$verifServ) && \$verifServ==1){\n";
	$datos.="		die('no');\n";
	$datos.="	}\n";
	$datos.="		\$estadoConexion='rgba(225,0,0,.5)';\n";
	$datos.="		\$icono='noSinc.png';\n";
	$datos.="		\$titulo='SIN CONEXION!!!<br>Verifique su conexión a internet';\n";
	$datos.="		\$indicador='sin conexion';\n";
	$datos.="	}else{\n";
	$datos.="		\$estadoConexion='#4E9D12';//rgba(0,225,0,.5)\n";
	$datos.="		\$icono='syncro.gif';\n";
	$datos.="		\$titulo='CONECTADO; Listo para sincronizar!!!';\n";
	$datos.="	}\n";*/	
	$datos.="	\$dblinea=@mysql_select_db(\$nombreLinea);";
	$datos.="	if(!\$dblinea){\n";
	$datos.="		echo('BD en linea no encontrada');\n";
	$datos.="	}else{\n";
	$datos.="		//echo '<br>bd en linea encontrada';\n";
	$datos.="	}\n";	
	$datos.="	require('include/sesiones.php');\n";
	$datos.="\n	header('Content-Type: text/html; charset=utf-8');\n\n";
//implementacion Oscar 2023
	$datos .= "	mysql_set_charset(\"utf8\", \$local);\n";
	$datos .= "	mysql_set_charset(\"utf8\", \$linea);";
//fin de cambio Oscar 2023

	$datos.="?>";

	fwrite($ini, $datos);
	fclose($ini);

	echo 'ok';
?>