<?php
/*
	* Version Oscar 2024-11-22 para poner permisos de lectrua y escritura a archvivo donde se guardan datos de cedula fiscal en alta de clientes facturacion
*/
	$name = "archivoDatosQR.txt";
	if( file_exists($name) ){
		chmod($file, 0777);
	}else{
		$file = fopen("{$name}", "w");
		fclose($file);
		chmod($file, 0777);
	}
	$file = fopen("{$name}", "w");

	fwrite($file, $_POST['data'] . PHP_EOL );

	//fwrite($file, "Otra más" . PHP_EOL);

	fclose($file);
	echo $name;
?>
