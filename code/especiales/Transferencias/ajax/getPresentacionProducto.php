<?php
//1. Incluye archivo %conect.php%%
	include('../../../../conect.php');
//2. Hace extract de variables POST
	extract($_POST);
//3. Consulta presentacion del producto y regresa los datos
	$sql="SELECT 
				IF(pp.cantidad IS NULL,1,pp.cantidad) AS cantidad,
				pp.nombre,
				p.nombre,
				pp.unidad_medida 
			FROM ec_productos_presentaciones pp /*ON sp.id_producto=pp.id_producto*/
			LEFT JOIN ec_productos p ON pp.id_producto=p.id_productos
			WHERE pp.id_producto=$id/*AND sp.id_sucursal=$sucursal_id*/";

	$eje=mysql_query($sql);
	if(!$eje){
		die("Error al verificar presentacion de producto\n".$sql."\n".mysql_error());
	}
	$rw=mysql_fetch_row($eje);
	die('ok|'.$rw[0].'|'.$rw[1].'|'.$rw[2].'|'.$rw[3]);
?>