<?php
//1. Incluye archivo %/conectMin.php%%
	include("../../../../conect.php");
//2. Hace extract de variables POST
	extract($_POST);
//3. Consulta informacion del producto
	$sql="SELECT
			aux2.id_productos AS ID,
			aux2.orden_lista AS ordenLista,
			IF(pp.id_producto_presentacion IS NULL,aux2.nombre,CONCAT(aux2.nombre,' (',pp.nombre,' de ',pp.cantidad,')')) AS Nombre,
			aux2.InvOr,
			aux2.InvDes,
			aux2.maximo,
			aux2.idEstProd,
			IF(pp.id_producto_presentacion IS NULL,1,pp.cantidad) as Presentacion,
			$cant AS cantidadPresentacion,
			pp.nombre as nombrePres,
			aux2.clave,
			aux2.observaciones
		FROM(
			SELECT
				aux.id_productos,
				aux.orden_lista,
				aux.nombre,
				aux.InvOr,
				aux.InvDes,
				IF(ep.id_estacionalidad_producto IS NULL,0, ep.id_estacionalidad_producto) AS idEstProd,
				IF(ep.id_estacionalidad_producto IS NULL,aux.maximo_existencia,ep.maximo) as maximo,
				aux.clave,
				aux.observaciones
    		FROM(
				SELECT
					p.id_productos,
    				p.orden_lista,
    				p.nombre,
			/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
					REPLACE(p.clave,',','*') as clave,
			/*Fin de cambio Oscar 26.02.2019*/
       				p.maximo_existencia,
    				SUM(IF(m.id_almacen=$aOrigen,IF(md.cantidad IS NULL, 0, md.cantidad_surtida*tm.afecta),0)) AS InvOr,
					SUM(IF(m.id_almacen=$aDestino,IF(md.cantidad IS NULL, 0, md.cantidad_surtida*tm.afecta),0)) AS InvDes,
					p.observaciones
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos= md.id_producto
				LEFT JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen AND m.id_sucursal IN($sOr,$sDes)
				LEFT JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
				WHERE";
	/*3.1. Implementacion de Oscar para importacion de CSV*/
		if($fl=='import_csv'){
			$sql.=" p.id_productos IN(";
			$arr=explode("|", $id);
			for($i=0;$i<sizeof($arr);$i++){
				if($arr[$i]!='' && $arr[$i]!=null){
					$arr_2=explode(",", $arr[$i]);
					$sql.=$arr_2[0];
					if($i<sizeof($arr)-1){
						$sql.=",";
					}
				}
			}
			$sql.=") GROUP BY p.id_productos";
		}else{
			$sql.=" p.id_productos=".$id;
		}

		$sql.=")aux
    			LEFT JOIN ec_estacionalidad_producto ep ON aux.id_productos = ep.id_producto
   	 			JOIN sys_sucursales s ON s.id_estacionalidad = ep.id_estacionalidad AND s.id_sucursal=$sDes
    			GROUP BY aux.id_productos
    	)aux2
    	LEFT JOIN ec_productos_presentaciones pp ON aux2.id_productos=pp.id_producto ORDER BY aux2.orden_lista ASC";
    	//ORDER BY aux.orden_lista ASC
    //die('ok.|'.$sql);
    $sql=str_replace(",) GROUP BY p.id_productos", ") GROUP BY p.id_productos", $sql);
    	
    $res=mysql_query($sql)or die("Error al consultar datos del producto para generar la fila!!!\n\n".mysql_error()."\n\n".$sql);
   //llenamos tabla de datos	
	echo 'ok|';
	$count=0;
	$ar=explode("|", $id);
//4. Generacion de fila(s)
	while($row=mysql_fetch_assoc($res)){//mientras se encuentren resultados en el arreglo de la consulta;
		$invalida="";
		echo 'Nombre:'.$row['Nombre'];
/*implementacion Oscar 02.10.2018*/
		$arr_2=explode(",", $ar[$count]);
		if($fl=='import_csv'){
			$row['cantidadPresentacion']=$arr_2[1];
		$count++;
			//echo '1_ '.$arr_2[1];
			//die($arr_2[1]);
		}
/*fin de cambio Oscar 02.10.2018*/
	
	//implementacion de Oscar 21.02.2017
		if($row['InvOr']<$cant/*$row['cantidadSurtir']*/){
			$resalta_rojo='style="color:red;"';
		}else{
			$resalta_rojo="";
		}
		if($id_tipo==6 AND $row['InvOr']<=0){
			}else{
				if(isset($row['valido'])){
					$aux=$row['valido'];
					if($aux==1){
						$validos++;	
					}else if($aux==0){
						$invalida="";//aqui sehabilitamos(por ahora no se usa)
					}
				}	
				$c++;//incrementamos contador
				if($c%2==0){
					echo '<tr id="fila'.$c.'" bgcolor="#FFFF99" class="filas">';//onclick="resaltar('.$c.');"
				}else{	
					echo '<tr id="fila'.$c.'" bgcolor="#CCCCCC" class="filas">';//onclick="resaltar('.$c.');"
				}
	?>
	<!--Orden de lista-->
		<td align="right" width="10%" onclick="<?php echo 'resaltar('.$c.');';?>" id="<?php echo '0_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['ordenLista'];?></td>
    <!--Id del producto (celda oculta)-->
        <td id="<?php echo '1_'.$c;?>" style="display:none;"><?php echo $row['ID'];?></td>
        
	<!--Implementación Oscar 26.02.2019 para agregar el código alfanumérico (oculto) en la transferencia-->
		<td style="display:none;" id="<?php echo 'clave_'.$c;?>"><?php echo $row['clave'];?></td>
	<!--Fin de cambio Oscar 26.02.2019-->

	<!--Nombre del producto-->
		<td align="left" width="40%" title="<?php echo 'Clave: '.$row['clave'];?>" id="<?php echo '2_'.$c;?>" onclick="<?php echo 'resaltar('.$c.');';?>" <?php echo $resalta_rojo;?>><?php echo $row['Nombre'];?></td>
    <!--Inventario Almacén Origen-->
		<td align="right" width="11%" id="<?php echo '3_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['InvOr'];?></td>
    <!--Inventario Almacén Destino-->
		<td align="right" width="11%" id="<?php echo '4_'.$c;?>" <?php echo $resalta_rojo;?> onclick="<?php echo 'resaltar('.$c.');';if($user_sucursal==$sDes){echo 'editaCelda(4,'.$c.');';}?>"><?php echo $row['InvDes'];?></td>
	<!--Estacionalidad máxima-->
		<td width="10%" align="right" id="<?php echo '5_'.$c;?>"  <?php echo $resalta_rojo;?> onclick="<?php if($user_sucursal==$sDes){echo 'editaCelda(5,'.$c.');';}?>"><?php echo $row['maximo'];?></td>
	<!--Cantidad por pedir-->
	<td align="center" width="10%">
        	<input type="text" class="pedir" id="<?php echo '6_'.$c;?>" value="<?php if($id_tipo==6){echo $row['InvOr'];}else{echo $row['cantidadPresentacion'];}?>"
     		onkeyup="<?php echo 'validar(event,'.$c.',2);operacion(event,'.$c.');';?>"
     		onclick="<?php echo 'resaltar('.$c.');';?>" <?php if($status!=1 && $status!=''){echo ' disabled ';}echo $invalida;?> <?php echo $resalta_rojo;?>/>
        </td>
   	<!--Id del detalle de la estacionalidad (celda oculta)-->
        <td  id="<?php echo '7_'.$c; ?>" style="display:none;"><?php echo $row['idEstProd'];?></td>
    <!--cantidad de la presentación-->
    	<td id="<?php echo '8_'.$c; ?>" style="display:none;"><?php echo $row['Presentacion'];?></td>
    	<td id="<?php echo '12_'.$c; ?>" style="display:none;"><?php echo $row['observaciones'];?></td>
    <!--opción para eliminar la fila-->
		<td align="center" width="8%">
			<a href="<?php if($status==6){echo'javascript:restringe();';}else{echo 'javascript:eliminarFila('.$c.',1);';}?>" style="text-decoration:none;">
				<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font><font size="-1">&nbsp;</font><font size="-1"></font></td>
			</a>    
	<?php
		if($fl=='import_csv'){
			echo '</tr>';
		}else{
			echo'</tr>|'.$row['Presentacion'].'|'.$row['nombrePres'];//regresamos la presentación
		}//fin de else
		 //print_r($extae);
		}//fin de else
	}//fin de while	
	if($fl=='import_csv'){
		echo "|".$count;
	}
?>	