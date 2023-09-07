<?php
	if(!require('../../conect.php')){
		die('Sin conexion!!!');
	}
	extract($_GET);
	//die($cant);
	//cant="+can+"&clave="+busqueda+"&id_pedido="+id_p
	$sql="SELECT pe.cantidad
			FROM ec_pedidos_detalle pe
			LEFT JOIN ec_productos p ON p.id_productos=pe.id_producto
			WHERE p.orden_lista='$clave'
			AND pe.id_pedido='$id_pedido'";
	$eje=mysql_query($sql)or die("Error!!!\n\n".$sql."\n\n".mysql_error());
	$rw=mysql_fetch_row($eje);
	if($cant>$rw[0]){
		die('no');
	}else{
		echo 'ok';
	}
?>