<?php
	include("../../conectMin.php");
	$datos=$_GET['dat'];
	$id_ptq=$_GET['id_paquete'];
	$arr=explode("|",$datos);
//consulamos nombre, cantidad requerida, inventario del producto
	$dts_2="";
	for($i=0;$i<sizeof($arr)-1;$i++){
		$sql="SELECT 
			p.nombre,
			p_d.cantidad_producto,
			SUM(IF(md.id_movimiento IS NULL,0,md.cantidad*tm.afecta)) AS existencia,
			p.id_productos
			FROM ec_productos p
			LEFT JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			LEFT JOIN ec_paquete_detalle p_d ON p.id_productos=p_d.id_producto
			WHERE p.id_productos='$arr[$i]'
			AND ma.id_sucursal='$user_sucursal'
			AND p_d.id_paquete=$id_ptq";
		$eje=mysql_query($sql)or die("Error al consultar inventarios del producto!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
	//guardamos detalles
		$dts.=$r[0]."~".$r[1]."~".$r[2]."~".$r[3]."|";
	}
	echo 'ok|';//regresamos respuesta satisfacoria
//regresammos tabla
	echo '<table style="position:relative;width:80%;left:10%;">';
		echo '<tr>';
			echo '<th width="40%">Producto</th>';
			echo '<th width="20%">Cant Paquete</th>';
			echo '<th width=20%">Bodega</th>';
			echo '<th width="20%">Exhibición</th>';
		echo '</tr>';
	//descomprimimos datos
		$arr=explode("|",$dts);
		$ids="";
		for($i=0;$i<sizeof($arr)-1;$i++){
			$arr_tmp=explode("~",$arr[$i]);
			/*if($arr_tmp[2]<=0){
				$ids.=$arr_tmp[3]."~";//concatenamos los ids de productos
			}*/
			echo '<tr>';
				echo '<td width="40%">'.$arr_tmp[0].'</td>';
				echo '<td width="20%" align="right">'.$arr_tmp[1].'</td>';
				echo '<td width="20%" align="right"><input type="text" id="bdg_'.($i+1).'" value="0" style="width:98%;text-align:right;"></td>';//.$arr_tmp[2].
				echo '<td width="20%" align="right"><input type="text" id="exh_'.($i+1).'" value="0" style="width:98%;text-align:right;"></td>';
			echo '</tr>';
		}
	echo '</table><br>';
	echo '<p align="center"><input type="text" style="padding:10px;border-radius:8px;width:20%;"><br><br>';
	echo '<button style="padding:10px;border-radius:5px;" onclick="insertaMovExhibTemporal('.$ids.',\'pqt\');"><b>Aceptar</b></button></p>';
?>
<style>
	th{
		padding: 10px;
		background: rgba(225,0,0,.8);
		color: white;
	}
</style>