<?php
	
	echo "Prueba de actualización desde un archivo cvs en Excel";
	if(!include('../../../../conect.php')){
		die('no hay archivo de conexion');
	}
	$archivo=fopen("nombres_productos.csv","r");
	if(!$archivo){
		die("No se encuentra el archivo!!!");
	}
	$datos="";
	while($dat=fgetcsv($archivo,1000,",")){
		$datos.=$dat[0].'~'.$dat[1].'~'.$dat[2].'~'.$dat[3].'~'.$dat[4]."~".$dat[5].'|';
		//echo $datos."<br>";
	}
	//print_r($datos);
	$aux=explode("|",$datos);
	//echo 'tamaño: '.sizeof($aux)."\n";
//marcamos inicio de transaccion
	mysql_query('begin');
	$cont=0;
	for($i=2;$i<sizeof($aux);$i++){
		$cont++;
		$rw=explode("~",$aux[$i-1]);
		//echo '<br>';
		//$sql=utf8_encode("UPDATE ec_productos SET orden_lista='$rw[1]',clave='$rw[2]',nombre='$rw[3]',nombre_etiqueta='$rw[4]',ubicacion_almacen='$rw[5]'
		//	WHERE id_productos=$rw[0]");/*,ubicacion='$rw[5]'*/
			$sql=utf8_encode("UPDATE ec_productos SET nombre='$rw[1]',precio_compra='$rw[2]',nombre_etiqueta='$rw[3]',ultima_modificacion=now() WHERE id_productos=$rw[0]");/*,ubicacion='$rw[5]'*/
		echo '<br>'.$sql;	
		$eje=mysql_query($sql);
		if(!$eje){
			die("Error en la consulta\n".mysql_error()."\n".$sql);
			mysql_query("rollback");
		}
	}
	mysql_query("commit");
	echo '<br>ok!!!<br>Actualizados: '.$cont;
/*
	
	$dbHost="casadelasluces.com.mx";
	$dbUser="cdelaslu_cluces";
	$dbPassword="P4ssgr4l";
	$dbName="cdelaslu_2018";
	/*************************Definicion de ruta
*/
?>
