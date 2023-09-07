<?php
	include('../../conectMin.php');

	$flag=$_POST['fl'];//recibimos la variable flag para cer de que caso se trata
	$suc_sel=$_POST['suc_selecc'];//recibimos la sucursal
	//die('flag'.$flag);
	if(isset($_GET['fl']) && $_GET['fl']=='resetear_cont_fol'){
		mysql_query("BEGIN");//declaramos el inicio de transaccion

	//eliminamos todos los registros que existan en la tabla del contador
		$sql="DELETE FROM cont_folios_vta";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transaccion
			die("Error al eliminar la tabla de contador de folios venta!!!".$error);
		}
	//regresamos el contador a 1
		$sql="ALTER TABLE `cont_folios_vta` auto_increment = 1";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transaccion
			die("Error al regresar a 1 el contador de folios venta!!!".$error);
		}
		mysql_query("COMMIT");//autorizamos la transaccion
	//eliminamos los codigos de barras anteriores
		$files = glob('../../img/codigos_barra/*'); //obtenemos el nombre de todos los ficheros
		foreach($files as $file){
 			echo 'here';
 			$lastModifiedTime = filemtime($file);
    		$currentTime = time();
    		$timeDiff = abs($currentTime - $lastModifiedTime)/(60*60); //en horas
    		if(is_file($file) && $timeDiff > 1){
    			unlink($file);	
    			echo $file.'\n';
    		}
		}
		
		die("El contador de folios de venta fue reseteado exitosamente!!!");
	}
/*Implementación Oscar 04.04.2019 para habilitar los productos que tienen estacionalidad en la sucursal/ deshabilitar los productos que no tienen estacionalidad en la sucursal*/
	if($flag==7){
		mysql_query("BEGIN");//marcamos inicio transacción
	//deshabilitamos todos los productos de la tabla de sys_sucursales_producto
		$sql="UPDATE sys_sucursales_producto SET estado_suc=0 WHERE id_sucursal=$suc_sel";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al deshabilitar todos los productos en la sucursal patra después habilitarlos!!!\n\n".$error."\n\n".$sql);
		}

		$sql="UPDATE sys_sucursales_producto SET estado_suc=1
			WHERE id_sucursal=$suc_sel
			AND id_producto IN(SELECT 
								ep.id_producto
								FROM ec_estacionalidad_producto ep
								LEFT JOIN ec_estacionalidad e on ep.id_estacionalidad=e.id_estacionalidad
								LEFT JOIN sys_sucursales s ON e.id_estacionalidad=s.id_estacionalidad
								WHERE s.id_sucursal=$suc_sel AND ep.maximo>0)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al habilitar los productos con estacionalidad en la sucursal!!!\n\n".$error."\n\n".$sql);
		}
		mysql_query("COMMIT");//autrizamos la transacción
		die('ok');
	}
/*Fin de cambio Oscar 04.04.2019*/

/*implementación Oscar 07.12.2018 para la generación de estacionalidades*/
	if($flag==6){
	/*consultamos los ids de estacionalidades de la sucursal
		$sql="(SELECT id_estacionalidad FROM ec_estacionalidad WHERE es_alta=1 AND id_sucursal=$suc_sel) UNION ";
		$sql.="(SELECT id_estacionalidad FROM ec_estacionalidad WHERE es_alta=0 AND id_sucursal=$suc_sel)";
		$eje=mysql_query($sql)or die("Error al consultar datos!!!\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$estacionalidad_alta=$r[0];
		$r=mysql_fetch_row($eje);
		$estacionalidad_baja=$r[0];

		$sql = "UPDATE ec_estacionalidad_producto epf
				LEFT JOIN ec_estacionalidad_producto epa
				ON ep.id ";

	//consultamos los datos de factores de estacionalidades de las sucursal
		$sql="SELECT 
				f1.factor AS factor_urgente,
				f2.factor AS factor_medio,
				f3.factor AS factor_final,
				f4.factor AS factor_minio_surtir
			FROM ec_factores_estacionalidad_categorias fc 
			WHERE id_sucursal=$suc_sel";
		$eje=mysql_query($sql)or die("Error al consultar datos!!!\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$factor_final=$r[2];
		$factor_minimo=$r[0];
		$factor_medio=$r[1];
	//consultamos los valores de estacionalidad alta de la sucursal
		$sql="SELECT id_producto,maximo FROM ec_estacionalidad_producto WHERE id_estacionalidad='$estacionalidad_alta'";
		$eje=mysql_query($sql)or die("Error al consultar el detalle de la estacionalidad alta de la sucursal!!!\n\n".mysql_error());
	
	//declaramos el inicio de la transacción
		mysql_query("BEGIN");
		while($r=mysql_fetch_row($eje)){//recorremos los resultados
			$sql="UPDATE ec_estacionalidad_producto SET 
					maximo=ROUND($r[1]*$factor_final),
					medio=ROUND(maximo*$factor_medio),
					minimo=ROUND(maximo*$factor_minimo)
					WHERE id_estacionalidad=$estacionalidad_baja AND id_producto=$r[0]";
			$actualiza=mysql_query($sql);
			if(!$actualiza){
				mysql_query("ROLLBACK");
				die("Error al actualizar los niveles de estacionalidad final!!!\n\n".$sql."\n\n".mysql_error());
			} 
		}//fin de while
		mysql_query("COMMIT");*/
	//declaramos el inicio de la transacción
		mysql_query("BEGIN");
		$sql = "UPDATE ec_estacionalidad ef
					LEFT JOIN ec_estacionalidad ea
					ON ef.id_sucursal = ea.id_sucursal AND ea.es_alta = 1
					RIGHT JOIN sys_sucursales s
					ON s.id_sucursal = ef.id_sucursal
					AND s.id_sucursal = ea.id_sucursal
					LEFT JOIN ec_estacionalidad_producto epf
					ON ef.id_estacionalidad = epf.id_estacionalidad
					LEFT JOIN ec_estacionalidad_producto epa
					ON ea.id_estacionalidad = epa.id_estacionalidad
					AND epf.id_producto = epa.id_producto
					LEFT JOIN ec_productos p 
					ON p.id_productos = epa.id_producto
					LEFT JOIN ec_factores_estacionalidad_categorias fec
					ON fec.id_categoria = p.id_categoria
					AND fec.id_tipo_factor = 3
					SET epf.maximo = ROUND( epa.maximo * fec.factor )
				WHERE ef.es_alta = 0
				AND IF( {$suc_sel} = -1, s.id_sucursal > 0, s.id_sucursal = {$suc_sel} )";
//echo $sql;
			$actualiza=mysql_query($sql);
			if(!$actualiza){
				mysql_query("ROLLBACK");
				die("Error al actualizar los niveles de estacionalidad final!!!\n\n".$sql."\n\n".mysql_error());
			} 
		mysql_query("COMMIT");
		die('ok');
	}
/*Fin de cambio Oscar 07.12.2018*/

/*implmentacion Oscar 01.11.2018 para habilitar productos de categoría General y el 18000*/
	if($flag==5){
			$accion="habilitar productos de familia General";
		if($suc_sel==-1){//si el línea habilitamos de la tabla de productos
			$sql="UPDATE ec_productos SET habilitado=1 WHERE id_categoria=1 OR id_productos=1808";
		}else if($suc_sel>0){//si es una sucursal habilitamos los productos en la tabla de sucursal por producto
			$sql="UPDATE
						sys_sucursales_producto sp
						LEFT JOIN ec_productos p On sp.id_producto=p.id_productos
						SET estado_suc=1
					WHERE (p.id_categoria=1 OR sp.id_producto=1808) AND sp.id_sucursal=$suc_sel";
		}
	}
/*Fin de camnbio Oscar 01.11.2018*/

/*Deshabilitar los productos sin inventario*/
	if($flag==0){
		if($suc_sel>0){//si es una sucursal diferente de línea
			$sql="UPDATE sys_sucursales_producto SET estado_suc=0 
				WHERE id_producto IN(SELECT
							aux.id_productos
						FROM(
							SELECT 
								p.id_productos,
								SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=$suc_sel,0,(md.cantidad*tm.afecta))) AS inventario
								FROM ec_productos p
								LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
								LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
								LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
								WHERE p.id_productos>1 AND p.muestra_paleta=0 AND p.muestra_paleta=0
								GROUP BY p.id_productos
							)aux
						WHERE aux.inventario=0
						)
				AND id_sucursal=$suc_sel";

		}else if($suc_sel==-1){//si es linea
			$sql="UPDATE ec_productos SET habilitado=0 
					WHERE id_productos IN(SELECT
							aux.id_productos
						FROM(
							SELECT 
								p.id_productos,
								SUM(IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))) AS inventario
								FROM ec_productos p
								LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
								LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
								LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
								WHERE p.id_productos>1 AND p.muestra_paleta=0 AND p.muestra_paleta=0
								GROUP BY p.id_productos
							)aux
						WHERE aux.inventario=0
						)";
			$accion=" deshabiltar los productos sin inventario";

		}
	}


/*Habilitar los productos con inventario*/
	if($flag==1){
		if($suc_sel>0){//si es una sucursal diferente de línea
			$sql="UPDATE sys_sucursales_producto SET estado_suc=1 
				WHERE id_producto IN(SELECT
							aux.id_productos
						FROM(
							SELECT 
								p.id_productos,
								SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=$suc_sel,0,(md.cantidad*tm.afecta))) AS inventario
								FROM ec_productos p
								LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
								LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
								LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
								WHERE p.id_productos>1 AND p.muestra_paleta=0 AND p.muestra_paleta=0
								GROUP BY p.id_productos
							)aux
						WHERE aux.inventario!=0
						)
				AND id_sucursal=$suc_sel";

		}else if($suc_sel==-1){//si es linea
			$sql="UPDATE ec_productos SET habilitado=1 
					WHERE id_productos IN(SELECT
							aux.id_productos
						FROM(
							SELECT 
								p.id_productos,
								SUM(IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))) AS inventario
								FROM ec_productos p
								LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
								LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
								LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
								WHERE p.id_productos>1 AND p.muestra_paleta=0 AND p.muestra_paleta=0
								GROUP BY p.id_productos
							)aux
						WHERE aux.inventario!=0
						)";
			$accion=" habiltar los productos con inventario";
		}
	}

/*Deshabilitar todos los productos*/	
	if($flag==2){
		if($suc_sel>0){//si es una sucursal diferente de lína
			$sql="UPDATE sys_sucursales_producto set estado_suc=0 WHERE id_sucursal=$suc_sel";
		}else if($suc_sel==-1){//si es línea
			$sql="UPDATE ec_productos SET habilitado=0";
		}
		$accion=" deshabiltar todos los productos";
	}

/*Habilitar todos los productos*/
	if($flag==3){
		if($suc_sel>0){//si es una sucursal diferente de lína
			$sql="UPDATE sys_sucursales_producto set estado_suc=1 WHERE id_sucursal=$suc_sel";
		}else if($suc_sel==-1){//si es línea
			$sql="UPDATE ec_productos SET habilitado=1";
		}
		$accion=" deshabiltar todos los productos";
	}

/*Actualizar los productos que se componen de una maquila Oscar 25.10.2018*/
	if($flag==4){
		$sql="SELECT 
				pd.id_producto_ordigen,
				sp.estado_suc,
				pd.id_producto,
				sp.id_sucursal
			FROM ec_productos_detalle pd
			LEFT JOIN sys_sucursales_producto sp ON pd.id_producto_ordigen=sp.id_producto";
	//si es una sucursal diferente a línea
		if($suc_sel>0){
			$sql.=" WHERE sp.id_sucursal=".$suc_sel;
		}
		$eje=mysql_query($sql)or die("Error al consultar información de productos origen!!!\n\n".mysql_error());
		mysql_query("BEGIN");//marcamos el inicio de la transaccion
		while($r=mysql_fetch_row($eje)){
			$sql_1="UPDATE sys_sucursales_producto SET estado_suc=$r[1] WHERE id_producto=$r[2] AND id_sucursal=$r[3]";
			$eje_1=mysql_query($sql_1);
			if(!$eje_1){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al actualizar status del producto maquilado!!!\n\n".mysql_error());
			}
		
		}//fin de while
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok');
	}/*Actualizar los productos que se componen de una maquila Oscar 25.10.2018*/
	if($flag==5 && $suc_sel==-1){//si es línea habilitamos de la tabla de productos
		mysql_query("BEGIN");//marcamos el inicio de la transaccion
		$sql="UPDATE ec_productos SET habilitado=1";
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok');
	}
/*fin de cambio Oscar */

	mysql_query("begin");//marcamos inicio de transacción
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("rollback");//cancelamo transacción
		die("Error al ".$accion."!!!".$error."\n\n".$sql);
	}
	mysql_query("commit");//autorizamos transacción
	echo 'ok';
?>