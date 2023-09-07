<?php

	include("../../conectMin.php");
/*implementación Oscar 11.06.2019 para sacar el toipo de afecta del combo de concepto de movimeinto banco o caja*/
	if(isset($_GET['flag']) && $_GET['flag']=='combo_conc_mov'){
		$id=$_GET['id_conc'];
		$sql="SELECT afecta FROM ec_concepto_movimiento WHERE id_concepto_movimiento=$id";
		$eje=mysql_query($sql)or die("Error al consultar como afecta el concepto de movimiento!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		die("exito|".$r[0]);
	}
	if(isset($_GET['flag']) && $_GET['flag']=='checa_movs'){
		$id=$_GET['id_mov'];
		$sql="SELECT COUNT(*) FROM ec_bitacora_movimiento_caja WHERE id_movimiento=$id";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al consultar como afecta el concepto de movimiento!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]<=0){
			die("No hay historial de cambios de este movimiento de caja o cuenta!!!");
		}
		die("exito|".$r[0]);
	}
/*Fin de cambio Oscar 11.06.2019*/
	
	extract($_GET);
	
	
	$sql="	SELECT
			cantidad
			FROM ec_productos_presentaciones
			WHERE id_producto_presentacion = $id_presentacion";
			
			
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	
	$row=mysql_fetch_row($res);
	
	echo "exito|".$row[0];		
	
	
?>