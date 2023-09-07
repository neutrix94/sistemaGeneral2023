<?php
	include("../../../../conectMin.php");
if(!isset($id_proveedor)){
	$id_proveedor=$_GET['prov'];
}
	$sql="SELECT
			p.id_productos,
			p.orden_lista,
			p_p.clave_proveedor,
			prov.nombre_comercial,
			p.nombre,
			p_p.precio_pieza,
			p_p.presentacion_caja,
			p_p.id_proveedor_producto,
			''
			/*p_p.nombre_producto_proveedor*/
		FROM ec_proveedor_producto p_p
		LEFT JOIN ec_productos p ON p_p.id_producto=p.id_productos
		LEFT JOIN ec_proveedor prov ON p_p.id_proveedor=prov.id_proveedor
		WHERE prov.id_proveedor=$id_proveedor";
	$eje=mysql_query($sql)or die("Error al consultar la taba ec_proveedor_produto!!!\n\n".$sql."\n\n".mysql_error());

if(isset($_GET['flag']) && $_GET['flag']!=null){//si es descargar el csv
	$datos="codigo_proveedor,nombre_producto,piezas_caja,precio\n";//encabezado del archivo
//llenamos los datos del archivo
	while($r=mysql_fetch_row($eje)){
		$datos.=$r[2].",";
		$datos.=$r[4].",";
		$datos.=$r[6].",";
		$datos.=$r[5]."\n";
	}
	$nombre="proveedor-producto.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($datos));

}else{//si es llenar la tabla
	echo '<table>';
	$c=0;
	while($r=mysql_fetch_row($eje)){
		$c++;
		if($c%2==0){
			$color="#E6E8AB";
		}else{
			$color="#BAD8E6";
		}
		echo '<tr id="fila_'.$c.'" style="background:'.$color.'">';
		echo '<td width="10%" align="center">'.$r[1].'</td>';
		echo '<td width="10%">'.$r[2].'</td>';
		echo '<td width="10%">'.$r[3].'</td>';
		echo '<td width="20%">'.$r[4].'</td>';
		echo '<td width="20%">'.$r[8].'</td>';
		echo '<td width="10%" align="right">'.$r[5].'</td>';
		echo '<td width="10%" align="right">'.$r[6].'</td>';
		echo '<td width="8%" align="center"><img src="../../../img/especiales/del.png" width="50%" onclick="eliminar('.$c.','.$r[7].');"></td>';
		echo '</tr>';
	}
	echo '</table>';
}
?>