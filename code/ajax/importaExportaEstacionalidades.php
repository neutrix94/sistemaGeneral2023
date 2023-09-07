<?php
	include("../../conectMin.php");//incluimos libreria de conexion
//recibimos variables
	$flag=$_GET['fl'];
	$id_estacionalidad=$_GET['estacionalidad_id'];
	if($flag!=1){
		$flag=$_POST['fl'];
		$id_estacionalidad=$_POST['id_estac'];
	}

//generación de archivo Excel
	if($flag==1){
	//consultamos datos
		$sql="SELECT 
					p.id_productos,/*0*/
					p.orden_lista,/*1*/
					p.nombre,/*2*/
					ep.id_estacionalidad,/*3*/
					ep.minimo,/*4*/
					ep.medio,/*5*/
					ep.maximo/*6*/
				FROM ec_productos p
				LEFT JOIN ec_estacionalidad_producto ep ON p.id_productos=ep.id_producto
				WHERE ep.id_estacionalidad=$id_estacionalidad
				ORDER BY p.orden_lista";
		$eje=mysql_query($sql)or die("Error al consultar datos para generar archivo de Excel!!!\n\n".$sql."\n\n".mysql_error());
	//armamos archivo
		$datos="id_producto,orden_lista,nombre,estacionalidad,minimo,medio,maximo\n";//formamos encabezado del csv
		while($r=mysql_fetch_row($eje)){
			$datos.=$r[0].",".$r[1].",".$r[2].",".$r[3].",".$r[4].",".$r[5].",".$r[6]."\n";//añadimos filas
		}//fin de while
	//consultamos nombre de la estacinalidad
		$sql="SELECT CONCAT('ESTACIONALIDAD',REPLACE(nombre,' ','')) FROM ec_estacionalidad WHERE id_estacionalidad=$id_estacionalidad";
		$eje=mysql_query($sql)or die("Error al consultar nombre de la estacionalidad!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
	//regresamos csv
		$nombre=trim($r[0]).'.csv';
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-disposition: attachment; filename="'.$nombre.'"');
		echo (utf8_decode($datos));	
	}

//calculo de etacionalidades
	if($flag==2){
		$array=explode("|",$_POST['arreglo']);
	//consultamos el factor de la sucursal en base a la estacionalidad y si la estacionalidad es la alta de la sucursal a la que pertenece
		$sql="SELECT s.factor_estacionalidad_minimo,/*0*/
					s.factor_estacionalidad_medio,/*1*/
					s.factor_estacionalidad_final,/*2*/
					es.es_alta,/*3*/
					s.id_sucursal/*4*/
					FROM sys_sucursales s
					LEFT JOIN ec_estacionalidad es ON s.id_estacionalidad=es.id_estacionalidad
					WHERE es.id_estacionalidad=$id_estacionalidad";
		$eje=mysql_query($sql)or die("Error al consultar el factores y datos de la estcionalidad!!!\n\n".$sql."\n\n".mysql_error());
		$factor=mysql_fetch_row($eje);
		mysql_query("BEGIN");//abrimos transacción
	//formamos repuesta
	//	$repuesta="";
		for($i=0;$i<sizeof($array);$i++){
			$datos=explode(",",$array[$i]);
			$sql="UPDATE ec_estacionalidad_producto SET maximo=$datos[1] WHERE id_producto=$datos[0] AND id_estacionalidad=$id_estacionalidad";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al acualizar en tabla ec_estacioanilad_producto!!!\n\n".$sql."\n\n".mysql_error());
			}	
		//si es estacionalidada alta
			if($factor[3]==1){
			¨/*Actualizamos el maximo de la estacionalidad baja*/
				$sql="UPDATE ec_estacionalidad_producto  
						SET maximo=ROUND($factor[2]*$datos[1]) 
						WHERE id_estacionalidad IN(SELECT id_estacionalidad FROM ec_estacionalidad WHERE id_sucursal=$factor[4] AND id_estacionalidad!=$id_estacionalidad)
						AND id_producto=$datos[0]";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al acualizar la estacionalidad baja del producto!!!\n\n".$sql."\n\n".mysql_error());
			}	

			}
			/*
			$respuesta.=$datos[0]."~";//id_producto
			$respuesta.=($datos[1]*$factor[0])."~";//minimo
			$respuesta.=($datos[1]*$factor[1])."~";//medio
			$respuesta.=$datos[1]."°";//maximo*/
		}
		mysql_query("COMMIT");//autorizamos transacción
		echo('ok|');//.$respuesta
	}
?>