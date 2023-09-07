<?php
	include("../../../../conectMin.php");
//extraemos datos por post
	extract($_POST);

/*Cambio Oscar 13.02.2019 para mostrar el precio de compra del producto*/
	$sql="SELECT CONCAT(orden_lista,' ',nombre),CONCAT('Precio de compra: ',precio_compra) 
		FROM ec_productos
		WHERE id_productos='$id'";
	$eje=mysql_query($sql)or die("Error al buscar precio de compra del producto!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	$titulo=$r[0];
	$titulo_2=$r[1];
/*Fin de cambio 13.02.2019*/
	
	$sql="SELECT 
			pd.id_precio_detalle,
			lp.nombre,
			pd.de_valor,
			pd.a_valor,
			pd.precio_venta,
			pd.es_oferta,
			lp.id_precio
		FROM ec_precios_detalle pd
		LEFT JOIN ec_precios lp ON pd.id_precio=lp.id_precio
		WHERE id_producto=$id
		ORDER BY lp.nombre ASC";
	$eje=mysql_query($sql)or die("Error al buscar precios!!!\n\n".$sql."\n\n".mysql_error());
/*sacamos el status del producto
	$sql="SELECT habilitado FROM ec_productos WHERE id_productos=$id";
	$eje_2=mysql_query($sql) or die("Error al consultar el status general del producto!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje_2);*/
	
	echo "ok|";
?>
	<center>
		<p style="position:absolute;top:10px;color:white;font-size:25px;width:80%;left:10%;" align="center"><b id="info_cambios"></b></p>
	</center>
	
	<p align="right">
		<input type="button" value="X" onclick="cierra_eme_prod();"
		style="padding:10px;top:20px;right:20px;position:relative;background:red;color:white;border-radius:5px;font-size:25px;">
	</p>
<!--Creamos cuerpo de grid-->
<div style="top:10%;position:fixed;width:80%;height:350px;left:10%;overflow: scroll;background: white;">
	<table style="position: fixed;width:78.6%;">
		<tr>
			<th colspan="3">
				<?php echo $titulo;?>
			</th>
			<th colspan="3">
				<?php echo $titulo_2;?>
			</th>
		</tr>
		<tr>
			<th width="30%">Lista de Precio</th>
			<th width="20%">DE</th>
			<th width="20%">A</th>
			<th width="15%">Precio</th>
			<th width="7.5%">Oferta</th>
			<!--<th width="10%">Editar</th>-->
			<th width="7.5%">Eliminar</th>
		</tr>
	</table>
	<table style="position: absolute;top:25%;" bgcolor="white" id="precios_producto">
		
	<?php
		$c=0;
		while($r=mysql_fetch_row($eje)){
			$c++;
			if($r[5]==1){
				$ch="checked";
			}else{
				$ch="";
			}
			echo '<tr bgcolor="#FFF8BB" id="fila_prec_'.$c.'" >';
				
				echo '<td id="nom_lista_prec_'.$c.'" width="30%">';
					echo'<input type="hidden" value="'.$r[0].'" id="precio_'.$c.'">';//id del precio
					echo'<input type="hidden" value="'.$r[6].'" id="id_lista_precio_'.$c.'">';//id de lista de precio
					echo $r[1];//nombre de lista de precio
				echo '</td>';//id del registro del precio
				
				echo '<td align="center" width="20%"><input type="text" id="de_'.$c.'" value="'.$r[2].'" onkeyup="activa_edic_prec(event,'.$c.',1)" onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//cantidad minima
				echo '<td align="center width="20%""><input type="text" id="a_'.$c.'" value="'.$r[3].'" onkeyup="activa_edic_prec(event,'.$c.',1)"  onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//cantidad maxima
				echo '<td align="center" width="15%"><input type="text" id="mont_'.$c.'" value="'.$r[4].'" onkeyup="activa_edic_prec(event,'.$c.',1)"  onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//precio de venta
				echo '<td align="center" width="7.4%"><input type="checkbox" id="ofer_'.$c.'" '.$ch.' onclick="activa_edic_prec(event,'.$c.',1);"></td>';//oferta
				//echo '<td align="center"><input type="button" class="camb" id="edit_prec_'.$c.'" value="guardar" onclick="modifica_precio('.$c.',1);" disabled></td>';//editar
				echo '<td align="center" width="7.5%"><input type="button" class="camb" value="x" onclick="elimina_fila('.$c.',2);"></td>';//quitar
			echo '</tr>';	
		}
	?>
	</table>
</div>
	<input type="hidden" id="fil_tot_precios" value="<?php echo $c;?>">
	<p align="center" class="conf_gral" style="left:35%;" onclick="config_de_prod(<?php echo $id.','.$contador;?>);">
		<img src="../../../img/especiales/config.png" title="Ver Configuración General" height="50px">
		<span><b>Config</b></span>
	</p>
	<p align="center" class="conf_gral" style="left:45%;" onclick="agrega_filas_subg(<?php echo $id;?>,3);">
		<img src="../../../img/especiales/add.png" title="Ver Configuración General" height="50px">
		<span><b>Nuevo</b></span>
	</p>
	<p align="center" id="edita_precios_prod" class="conf_gral" style="left:55%;" onclick="modifica_precios(<?php echo $id.','.$contador;?>);">
		<img src="../../../img/especiales/save.png" title="Ver Configuración General" height="50px">
		<span><b>Guardar</b></span>
	</p>

	<style>	
		.conf_gral{
			position:absolute;top:80%;border:1px solid blue;border-radius:100%;width:95px;height:95px;background:white;
		}
		.conf_gral:hover{
			background:gray;
			color:white;
		}
		.camb{
			padding:5px;
			width: 89.5%;
		}
		.ent_prec{
			padding: 5%;
			width:85%;
			text-align: right;
		}
	</style>