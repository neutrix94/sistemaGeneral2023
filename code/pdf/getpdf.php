<?php
	extract($_GET);
	$f=$_GET['f'];
	
	if(substr($f,0,3)!='tmp' or strpos($f,'/') or strpos($f,'\\'))
    	die('Nombre de archivo incorrecto');
	if(!file_exists($f))
    	die('El archivo no existe');
	if($HTTP_SERVER_VARS['HTTP_USER_AGENT']=='contype'){
		Header('Content-Type: application/pdf');
		exit;
	}
	Header('Content-Type: application/pdf');
	Header('Content-Length: '.filesize($f));
	readfile($f);
	unlink($f);
	exit;
?>