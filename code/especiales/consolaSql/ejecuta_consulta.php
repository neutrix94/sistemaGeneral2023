<?php
	include("../../../conectMin.php");
//descarga de csv
	if(isset($_POST['fl']) && $_POST['fl']==1){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="exportacion_tabla.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}

	$consulta=trim($_POST['cadena_original']);
	$sincronizar=$_POST['generar_reg'];
//vemos de que tipo de consulta se trata
	$es_trigger=0;
	$arr=explode(" ", $consulta);
	$tipo=2;
	$arr[0]=strtoupper($arr[0]);
	//$arr[1]=strtoupper($arr[1]);

	if(trim($arr[0])=='SELECT' || $arr[0]=='DESCRIBE' || $arr[0]=='SHOW' ||$arr[0]=='USE')
		$tipo=0;

	if($arr[0]=='INSERT')
		$tipo=1;
	
	if($arr[0]=='UPDATE')
		$tipo=2;
	
	if($arr[0]=='DELETE')
		$tipo=3;

//vemos si se trata de un trigger 
	if($arr[0]=='CREATE'){
		$tmp=strtoupper($arr[1]);
		if($tmp=='TRIGGER'){
			$es_trigger=1;
		}
	}

	mysql_query("BEGIN");//declaramos el inicio de transacción
	$eje=mysql_query($consulta); 
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");
		die("Error al ejecutar consulta: ".$error);
	}
//	die("1:".$consulta);
	if($tipo!=0){
		if($es_trigger==1){
			$consulta=str_replace("\"\'", "\"\***", $consulta);//die($consulta);
			$consulta=str_replace("\'\"", "\***\"", $consulta);//die($consulta);
//			die("2:".$consulta);
		}else{
			$consulta=str_replace("\'", "\\\'", $consulta);//die($consulta);
			$consulta=str_replace("'", "\'", $consulta);//die($consulta);
			$consulta=str_replace("cdelaslu_armandopruebas2019", "pruebas_oscar", $consulta);
			$consulta=str_replace("cdelaslu_oscarpruebas2019", "pruebas_oscar", $consulta);
			$consulta=str_replace("cdelaslu_2018", "cdelasluces", $consulta);
		}
		if($es_trigger==1){
			$consulta=str_replace("***", "\'", $consulta);//die($consulta);
		}
		
//die($consulta);
	if($sincronizar==1){
		$sql="INSERT INTO ec_sincronizacion_registros 
			SELECT
				null,
				$user_sucursal,
				id_sucursal,
				'',/*tabla*/
				0,
				$tipo,
				7,
				'{$consulta}',
				2,
				0,
				'Comando ejecutado desde la consola del sistema',
				now(),
				0,
				0,
				'n/a'
			FROM sys_sucursales WHERE id_sucursal>0";
			//die($sql);

		
		
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al insertar registros de sincronización: ".$sql.'<br>'.$error);
		}
		$msg_sinc='La consulta fue ejecutada exitosamente en esta Base de Datos<br>Se han creado los registros de sincronización para las sucursales locales exitosamente!!!';
	}else{
		$msg_sinc='La consulta solo fue generada para este servidor!!!';
	}
		//mysql_query("ROLLBACK");
		mysql_query("COMMIT");
		echo 'ok|ok|<p style="font-size:22px;color:blue;">'.$msg_sinc.'</p>';
	}else{
		//echo mysql_field_name($eje,0);
		$field = mysql_num_fields($eje);
    	$names; 
        for ( $i = 0; $i < $field; $i++ ) { 
            $names[$i] = mysql_field_name($eje, $i);
        }
        //print_r($names);
		echo 'ok|select|';
		echo '<table id="grid_resultado" width="100%">';
		$c=0;
		while($r=mysql_fetch_row($eje)){
		if($c==0){
			echo '<tr>';
			for($i=0;$i<sizeof($names);$i++){
				echo '<th>'.$names[$i].'</th>';
			}
			echo '</tr>';
		}
			echo '<tr>';
			for($i=0;$i<sizeof($r);$i++){
				echo '<td>'.$r[$i].'</td>';
			}
			echo '</tr>';
			$c++;
		}
		echo '</table>';
	//	die("cols: ".$cols);
	}
?>
<style type="text/css">
	th{background: rgba(225,0,0,.5);padding: 5px;position: relative;top:55%;}
</style>