<?php
	include('../../../../conectMin.php');
	extract($_POST);
	$sql="SELECT 
			pp.id_proveedor_producto,
			pp.id_proveedor,
			p.nombre_comercial,
			pp.precio_pieza,
			pp.presentacion_caja,
			pp.clave_proveedor
			FROM ec_proveedor_producto pp
			LEFT JOIN ec_proveedor p ON pp.id_proveedor=p.id_proveedor
			WHERE pp.id_producto=$prod";
	$eje=mysql_query($sql)or die("Error al consultar proveedores del producto!!!\n\n".$sql."\n\n".mysql_error());
	echo 'ok|';
?>
	<center>
		<p style="position:absolute;top:10px;color:white;font-size:25px;width:80%;left:10%;" align="center"><b id="info_cambios"></b></p>
	</center>
	
	<p align="right">
		<input type="button" value="X" onclick="cierra_eme_prod();"
		style="padding:10px;top:20px;right:20px;position:relative;background:red;color:white;border-radius:5px;font-size:25px;">
	</p>
<div id="prov_listado_prod">
	<table width="100%" id="t_prov_prod"><!--style="position:relative;left:25%;top:20%;"-->
		<tr>
			<th>Proveedor</td>
			<th>Precio por Pieza</td>
			<th>Piezas por Caja</td>
			<th>Código Prov</th>
		<!--	<th>Editar</th>
			<th>Eliminar</th>-->
		</tr>
	<?php
		$c=0;
		while($r=mysql_fetch_row($eje)){
			$c++;
			echo '<tr bgcolor="#FFF8BB" style="height:40px;width="100%" id="fila_prov_'.$c.'">';
				echo '<td id="id_prov_'.$c.'" style="display:none;">'.$r[1].'</td>';//id del proveedor (oculto)
				echo '<td width="40%" id="nom_prov_'.$c.'">'.$r[2].'</td>';//nombre del proveedor
			//entradas de texto
				echo '<td width="20%"  align="center"><input type="text" value="'.$r[3].'" class="ent_txt" id="p_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');this.select();"></td>';
				echo '<td width="20%"  align="center"><input type="text" value="'.$r[4].'" class="ent_txt" id="c_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');this.select();"';
				echo ' onblur="verifica_prov_prod(this);"></td>';
				echo  '<td width="20%"  align="center"><input type="text" value="'.$r[5].'" class="ent_txt" id="clave_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');this.select();"></td>';
				echo '<td id="id_prov_prod_'.$c.'" style="display:none;">'.$r[0].'</td>';
				//echo '<td width="10%" align="center"><input type="button" value="Editar" onclick="edita_prov('.$r[0].','.$c.')" id="edit_'.$c.'" disabled></td>';
				//echo '<td width="10%" align="center"><input type="button" value="x" onclick="elimina_fila('.$c.',3)"></td>';
			echo '</tr>';
		}
	?>
	</table>
<!--Aqui almacenamos el total de filas-->
	<input type="hidden" id="fil_tot_provs" value="<?php echo $c;?>">
</div>
	<p align="center" class="bot_provs" style="left:45%;" onclick="agrega_filas_subg(<?php echo $prod;?>,1);">
		<img src="../../../img/especiales/add.png" title="Agregar nuevo registro" height="50px">
		<span><b>Agregar</b></span>
	</p>
	<p align="center" id="edita_precios_prod" class="bot_provs" style="left:55%;" onclick="modifica_proveedores(<?php echo $prod;?>);">
		<img src="../../../img/especiales/save.png" title="Ver Configuración General" height="50px">
		<span><b>Guardar</b></span>
	</p>
	<style>
		.bot_provs{
			position:absolute;top:75%;border:1px solid blue;border-radius:100%;width:95px;height:95px;background:white;
		}
		.ent_txt{
			width:86%;
			padding: 5%;
			text-align: right;
		}
		#prov_listado_prod{
			position: absolute;
			top:20%;
			width: 50%;
			height: 300px;
			overflow: auto;
			background: white;
			right: 25%
		}
	</style>