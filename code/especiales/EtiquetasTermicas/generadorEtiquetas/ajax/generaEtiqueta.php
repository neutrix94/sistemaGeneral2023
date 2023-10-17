<?php
	include( '../../../../../conectMin.php' );
	include( '../../../../../conexionMysqli.php' );

	$archivo_path = "../../../../../conexion_inicial.txt";
	if(file_exists($archivo_path)){
		$file = fopen($archivo_path,"r");
		$line=fgets($file);
		fclose($file);
	    $config=explode("<>",$line);
	    $tmp=explode("~",$config[2]);
	    $ruta_or=$tmp[0];
	    $ruta_des=$tmp[1];
	}else{
		die("No hay archivo de configuración!!!");
	}

	$file_name = date('Y_m_d_H_i_s_') . uniqid() . '.txt';
	//$file_name = "2022_12_19_13_22_47_63a0ba07120a5.txt";
	//die( $file_name );
	$sql = "";
//genera archivo
	$fh = fopen("../../../../../cache/ticket/{$file_name}", 'w') or die("Se produjo un error al crear el archivo");
	$resp = "
	^XA
	^FO250, 70^ADN, 11, 7^FD CDLL 2022^FS
	^FO320, 105^ADN, 11, 7^FD Prueba 1 ^FS
	^FO30, 150^ADN, 11, 7^FD Texto de muestra 1 ^FS
	^FO350, 200^ADN, 11, 7
	^BCN, 80, Y, Y, N^FD corptectr>147896325 ^FS
	^XZ
		";
	fwrite($fh, $resp) or die("No se pudo escribir en el archivo");
	fclose($fh);
//genera registro de descarga
	if($user_tipo_sistema=='linea'){
//die( 'Here' );
		$sql_arch="INSERT INTO sys_archivos_descarga SET 
				id_archivo=null,
				tipo_archivo='txt',
				nombre_archivo='{$file_name}',
				ruta_origen='$ruta_or',
				ruta_destino='$ruta_des',
				id_sucursal=(SELECT sucursal_impresion_local FROM ec_configuracion_sucursal WHERE id_sucursal='$user_sucursal'),
				id_usuario='$user_id',
				observaciones=''";
		$inserta_reg_arch=$link->query( $sql_arch )or die( "Error al guardar el registro de sincronización del ticket de reimpresión!!!\n\n". $link->error . "\n\n" . $sql_arch );
	}
	die( 'ok|Impresion Generada.' );
?>