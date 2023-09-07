<?php
	$sql="SELECT 
			p.id_productos AS ID,
			p.orden_lista as ordenLista,
			p.clave,
			0 as InvOr,
			0 as InvDes,
			1 as Presentacion,
			0 as raciona,
			p.ubicacion_almacen as ubicacion_matriz,
			sp.ubicacion_almacen_sucursal
		FROM ec_productos p 
		LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
		WHERE p.id_categoria=35";
		
	$eje=mysql_query($sql)or die("Error al consultar los producos adicionales");
		
	while($row=mysql_fetch_assoc($eje)){ 
		$c++;
?>
	<tr id="fila'.$c.'" bgcolor="#FFFF99" class="filas">
	<!--Orden de lista-->
		<td align="right" width="10%" onclick="<?php echo 'resaltar('.$c.');';?>" id="<?php echo '0_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['ordenLista'];?></td>
		
	<!--Implementación Oscar 26.02.2019 para agregar el código alfanumérico (oculto) en la transferencia-->
		<td style="display:none;" id="<?php echo 'clave_'.$c;?>"><?php echo $row['clave'];?></td>
	<!--Fin de cambio Oscar 26.02.2019-->

    <!--Id del producto (celda oculta)-->
        <td id="<?php echo '1_'.$c;?>" style="display:none;"><?php echo $row['ID'];?></td>
	<!--Nombre del producto-->
		<td align="left" width="40%" title="<?php echo 'Clave: '.$row['clave'];?>" id="<?php echo '2_'.$c;?>" onclick="<?php echo 'resaltar('.$c.');';?>" <?php echo $resalta_rojo;?>><?php echo $row['Nombre'];?></td>
    <!--Inventario Almacén Origen-->
		<td align="right" width="11%" id="<?php echo '3_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['InvOr'];?></td>
    <!--Inventario Almacén Destino-->
		<td align="right" width="11%" id="<?php echo '4_'.$c;?>" <?php echo $resalta_rojo;?> onclick="<?php echo 'resaltar('.$c.');';if($user_sucursal==$destino){echo 'editaCelda(4,'.$c.');';}?>"><?php echo $row['InvDes'];?></td>
	<!--Estacionalidad máxima-->
		<td width="10%" align="right" id="<?php echo '5_'.$c;?>"  <?php echo $resalta_rojo;?> onclick="<?php if($user_sucursal==$destino){echo 'editaCelda(5,'.$c.');';}?>"><?php echo $row['maximo'];?></td>
	<!--Cantidad por pedir-->
	<td align="center" width="10%">
        	<input type="number" onkeydown="detiene(event);" class="pedir" id="<?php echo '6_'.$c;?>" value="<?php if($id_tipo==6){echo $row['InvOr'];}else{echo $row['CantidadPresentacion']*$row['Presentacion'];}?>"
     		onkeyup="<?php echo 'validar(event,'.$c.',2);operacion(event,'.$c.');';?>"
     		onclick="<?php echo 'resaltar('.$c.');';?>" <?php if($status!=1 && $status!=''){echo ' disabled ';}echo $invalida;?> <?php echo $resalta_rojo.' '.$titulo_caja_txt;?>/>
        </td>
   	<!--Id del detalle de la estacionalidad (celda oculta)-->
        <td  id="<?php echo '7_'.$c; ?>" style="display:none;"><?php echo $row['idEstProd'];?></td>
    <!--cantidad de la presentación-->
    	<td id="<?php echo '8_'.$c; ?>" style="display:none;"><?php echo $row['Presentacion'];?></td>
<!--Implementación Oscar 24.03.2019 para tener indicador de producto por racionar-->
    	<td id="<?php echo '9_'.$c; ?>" style="display:none;"><?php echo $row['raciona'];?></td>
<!--Fin de cambio Oscar 24.03.2019-->
<!--Implementación Oscar 16.04.2019 para agregar al csv ubicación de matriz y de sucursal destino-->
    	<td id="<?php echo '10_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_matriz'];?></td>
    	<td id="<?php echo '11_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_almacen_sucursal'];?></td>
<!--Fin de cambio Oscar 16.04.2019-->
    	
    <!--opción para eliminar la fila-->
		<td align="center" width="8%">
			<a href="<?php if($status==6){echo'javascript:restringe();';}else{echo 'javascript:eliminarFila('.$c.',1);';}?>" style="text-decoration:none;">
				<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font><font size="-1">&nbsp;</font><font size="-1"></font>
			</a>    
		</td>
<?php
	}
?>