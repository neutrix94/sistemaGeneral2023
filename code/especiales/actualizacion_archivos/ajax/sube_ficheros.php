<?php
	if(!include("../../../../conectMin.php")){
		die("Sin archivo de conexión!!!");
	}

	$ruta_origen=str_replace("\\","/", $rooturl);
	//die($rooturl);
	$ruta_origen.="/code/especiales/actualizacion_archivos/archivos_sistema/";

	$num_archivos=count($_FILES['archivo']['name']);
	for($i=0;$i<=$num_archivos;$i++){
		if(!empty($_FILES['archivo']['name'][$i])){
			$nombre=$_FILES['archivo']['name'][$i];
			$ruta_destino=str_replace("\\","/",$_POST['ruta_destino'][$i]);
			$ruta_destino.="/";
			$sql="INSERT INTO sys_archivos_descarga
				SELECT
					null,
					'sistema',
					'$nombre',
					'$ruta_origen',
					'$ruta_destino',
					id_sucursal,
					'$user_id',
					'',
					'0'
				FROM sys_sucursales WHERE id_sucursal>0";
			$eje=mysql_query($sql)or die("Error al insertar el registro de descargas!!!<br>".$sql."<br>".mysql_error());
		//pasamos el archivo
			$ruta="../archivos_sistema/".$_FILES['archivo']['name'][$i];
			$ruta_temporal=$_FILES['archivo']['tmp_name'][$i];
			move_uploaded_file($ruta_temporal, $ruta);
			echo "archivo ".$ruta." subido exitosamente!!!<br>";
		}
	}
	echo '<p align="center" style="font-size:50px;">Archivos subidos correctamente!!!<br>REDIRECCIONANDO....</p>';
	echo '<script>setTimeout(function(){location.href="../carga_archivos.php?";},2000)</script>';
	//die('arch:'.$num_archivos);
?>