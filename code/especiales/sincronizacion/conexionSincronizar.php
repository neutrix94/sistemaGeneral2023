<?php
	
	$hostLocal="localhost";
	$userLocal="root";
	$passLocal="";
	$nombreLocal="pruebas_oscar";//cdelasluces
	$local=@mysql_connect($hostLocal, $userLocal, $passLocal);
//comprobamos conexion local
	if(!$local){	//si no hay conexion
		//echo 'no hay conexion local';//finaliza programa
	}else{
		//echo'conexion local'.$nombreLocal;
	}
	$dblocal=@mysql_select_db($nombreLocal);
	if(!$dblocal){
		//echo 'BD local no encontrada';
	}else{
		//echo '<br> bd local encontrada';
	}
	
/***********************************CONEXIONES BD FORANEA**********************************************/
	$hostLinea="casadelasluces.com.mx";
	$userLinea="cdelaslu_cluces";
	$passLinea="P4ssgr4l";
	$nombreLinea="cdelaslu_pruebasOscar2018";
	$linea=@mysql_connect($hostLinea,$userLinea,$passLinea);
	//$lnk=@mysql_connect($server, $user, $password);
	$indicador="";
	if(!$linea){
		//echo 'sin conexion a servidor en linea';
		$estadoConexion='rgba(225,0,0,.5)';
		$icono='noSinc.png';
		$titulo='SIN CONEXION!!!'.'\n\n'.'Verifique su conexión a internet';
		$indicador="sin conexion";
	}else{
		//echo '<br>conectado en linea '.$nombreLinea;
		$estadoConexion='#4E9D12';//rgba(0,225,0,.5)
		$icono='syncro.gif';
		$titulo='CONECTADO'.'\n\n'.'Listo para sincronizar!!!';
	}	
	$dblinea=@mysql_select_db($nombreLinea);
	if(!$dblinea){
		//echo('BD en linea no encontrada');
	}else{
		//echo '<br>bd en linea encontrada';
	}
	//mysql_query("SET time_zone='-05:00'", $linea) or die(mysql_error());
?>