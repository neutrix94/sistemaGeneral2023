<?php
//1. Incluye archivo %/concet.php%%
	require('../../../../conect.php');
//2. Hace extract de variables POST
	extract($_POST);

//3. Implementacion Oscar 15.08.2018 para filtrar productos externos
	if($muestra_prod_ext==1){
		$cond_externos=" AND sc.es_externo=1";
	}else{
		$cond_externos=" AND sc.es_externo=0";		
	}

//4. Busqueda por coincidencias
    $noms=explode(" ", $producto);//separamos las palabras en una arreglo para compararlas con mas exactitud en la consulta
	
//Modificación Oscar 22.02.2017
		$sql="SELECT CONCAT(p.nombre, IF(p.clave!='*',CONCAT(' Clave: ',p.clave),'') ), 
					p.id_productos AS ID,p.orden_lista,
					IF(pp.id_producto_presentacion IS NULL,'Pieza',
					CONCAT(pp.nombre,' ',pp.cantidad)) AS Presentacion,/*Modificación para incluir presentaciones Oscar 25.05.2018*/
					IF(et.id_exclusion_transferencia IS NULL,0,1) as excluido/*implementación de exclusiones de transferencias*/
					FROM ec_productos p
					LEFT JOIN ec_productos_presentaciones pp ON p.id_productos=pp.id_producto/*Modificación para incluir presentaciones Oscar 25.05.2018*/
					LEFT JOIN sys_sucursales suc ON suc.id_sucursal = IF( '$user_sucursal' = '-1', 1, $user_sucursal)
					LEFT JOIN sys_sucursales_producto sc on p.id_productos=sc.id_producto AND suc.id_sucursal=sc.id_sucursal  AND sc.estado_suc=1
					AND p.id_productos IN(SELECT id_producto FROM sys_sucursales_producto WHERE id_sucursal=1 and estado_suc=1)/*Modificacion Oscar 22.02.2018*/
					LEFT JOIN ec_exclusiones_transferencia et ON p.id_productos=et.id_producto
					/*JOIN ec_inventario_sincronizacion i on p.id_productos=i.id_producto AND i.id_sucursal='$sucDestino'*/
				WHERE ((p.orden_lista like '%$producto%' OR p.id_productos like '%$producto%' OR p.codigo_barras_1='$producto' OR p.clave like '%$producto%')";
	//ampliamos coincidencias
		for($i=0;$i<sizeof($noms);$i++){
        	if($i==0){
        		$operador=' OR (';
        	}else{
        		$operador=' AND ';
        	}
        	$sql.=$operador."p.nombre LIKE '%".$noms[$i]."%'";
        	//echo 'Ssql:'.$sql;
        }//fin de for i
    /*Se implementa el filtro $cond_externos Oscar 14.08.2018 patra filtrar productos internos, externos*/
		$sql.=")) AND p.habilitado=1 AND p.es_maquilado=0 AND p.muestra_paleta=0 AND p.id_productos!=1808 $cond_externos ORDER BY p.orden_lista ASC";/*Modificación para no mostrar productos con muestra_paleta Oscar 23-05-2018*/
		//echo $sql;
		$ejecuta=mysql_query($sql);
		if(!$ejecuta){
			die("Error al consultar!!!\n".mysql_error()."\n".$sql);
		}
		$num=mysql_num_rows($ejecuta);
		if($num<1){
			//die('aquí');
			$sql="SELECT
					pqt.id_paquete,
					pqt.nombre,
					GROUP_CONCAT(CONCAT(p.nombre,' (',pqd.cantidad_producto,' piezas)') SEPARATOR ' - ')
				FROM ec_paquetes pqt
				LEFT JOIN ec_paquete_detalle pqd ON pqt.id_paquete=pqd.id_paquete
				LEFT JOIN ec_productos p ON pqd.id_producto=p.id_productos
				WHERE pqt.id_paquete='$producto'";

    $noms=explode(" ", $producto);
		//ampliamos coincidencias
			for($i=0;$i<sizeof($noms);$i++){
        		if($i==0){
        			$operador=' OR (';
        		}else{
        			$operador=' AND ';
        		}
        		$sql.=$operador."pqt.nombre LIKE '%".$noms[$i]."%'";
        		//echo 'Ssql:'.$sql;
        	}//fin de for i
			$sql.=") GROUP BY pqt.id_paquete";
			
			$eje=mysql_query($sql)or die("Error al buscar coincidencias en paquetes!!!\n\n".$sql."\n\n".mysql_error());
			if(mysql_num_rows($eje)<1){
				die("No se econtraron coincidencias en productos ni paquetes!!!");
			}
			echo '<table width="100%" id="resulta">';
			echo'<tr><td></td></tr>';
			$contador=0;
			while($row=mysql_fetch_row($eje)){
				$contador++;//incrementamos el contador
				echo '<tr class="opcion" onclick="validaPaquete('.$row[0].');" id="r_'.$contador.'" tabindex="'.$contador.'" onkeyup="eje(event,'.$contador.','.$row[0].');">
				<td style="display:none;" id="id_'.$contador.'">'.$row[0].'</b></td>
				<td width=100%><span style="background-color:;"><b>'.$row[1].': </b> '.$row[2].'</td>
				</tr>';
			}
			echo '</table>';
		}else{
			echo '<table width="100%" id="resulta">';
			echo'<tr><td></td></tr>';
			$contador=0;
			while($row=mysql_fetch_row($ejecuta)){
		//sacamos inventario
		/*		$consInv="SELECT IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta)) as inventario
						FROM ec_movimiento_detalle md
						JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
						JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
						WHERE ma.id_sucursal=$sucDestino
						AND ma.id_almacen=$aD
						AND md.id_producto=$row[1]";
				$ejecuta1=mysql_query($consInv) or die($consInv);
				$inventario=mysql_fetch_row($ejecuta1);
		*/
				$contador++;//incrementamos contador
				echo '<tr class="opcion" onclick="validaProducto('.$row[1].','.$row[4].');">
				<td width=100%><span style="background-color:yellow;">
				<div class="resultado" id="r_'.$contador.'" tabindex="'.$contador.'" onkeyup="eje(event,'.$contador.','.$row[1].','.$row[4].');">
				'.$row[2].'-</span>'.$row[0].'
				 |<span style="background-color:#6C9831;"> '.$row[3].'</span> | '.
				'<span style="background-color:rgba(225,0,0,.7);">'.$row[4].'_</span>'.
				'</div></td></tr>
				<input type="hidden" value="'.$row[1].'" id="id_'.$contador.'">';
			}//cierra while
			echo '</table>';
		}//fin de else
?>