<?php
//recibimos datos
	$info=explode("|",$_POST['datos']);
	$prefijo=$_POST['pref'];
	//die($prefijo);
	$respuesta="MODELO,DESCRIPCION,P/S,PRECIO\n";
	for($i=0;$i<sizeof($info);$i++){
		$aux[2]=str_replace("'","",$aux[2]);
		$aux=explode(',',$info[$i]);
		$respuesta.=$prefijo.$aux[0].',';
		$respuesta.=$aux[1].','.$aux[2].','.$aux[3];
		if($i<(sizeof($info)-1)){
			$respuesta.="\n";
		}
	}
	//die($info);
//creamos el nombre del archivo
	$nombre=$prefijo."preciosProv.csv";
//generamos descarga
	header('Content-Type: aplication/octect-stream');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Disposition: attachment; filename="'.$nombre.'"');
	echo(utf8_decode($respuesta));
//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
	die('');//<script>window.close();</script>
?>