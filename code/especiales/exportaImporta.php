<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
	//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");

	
	if($procesa == 'SI')
    {


		//echo "Si entra";

		if($_FILES["file"]['tmp_name'])
        {
			$rutaAux=$_FILES["file"]['tmp_name'];
			
			//echo $rutaAux;
			
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				$comando="c:/xampp/mysql/bin/mysql.exe -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys $dbName < $rutaAux";//--disable-keys
			else
				$comando="mysql -h $dbHost  -u $dbUser --password=\"$dbPassword\" --disable-keys $dbName < $rutaAux";
			
			unset($return);
			$salida = system($comando, $return);
	
	
			if($return == 0)
			{
				//echo "exito|$rooturl/respaldos/".date('Ymd')."casa.sql";
				$smarty->assign("mensajes", "Se ha importado la base de datos exitosamente<br>&nbsp;");
			}
			else
			{
				//echo "Error, no se pudo generar el respaldo de la base de datos, intentalo mas tarde.";
				$smarty->assign("mensajes", "Error, no se pudo generar el respaldo de la base de datos, intentalo mas tarde.<br>&nbsp;");
			}
	
	
			$ar=fopen("../../respaldos/errores.log", "at");
			if($ar)
			{
				fwrite($ar, "\nComando ejecutado: $comando\nHora:".date('H:i:s'));
			}
			fclose($ar);
			
		}
		else
		{
			//echo "No archivo";
			$smarty->assign("mensajes", "No selecciono un archivo a importar<br>&nbsp;");
		}	

	}
	
	
	
	$smarty->display("especiales/exportaImporta.tpl");


?>