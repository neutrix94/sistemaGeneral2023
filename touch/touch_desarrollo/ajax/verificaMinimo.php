<?php
	include('../../conectMin.php');
	extract($_GET);
//verificamos pedido
	if(isset($t_v)&&$t_v==2){
		$sql="SELECT cantidad FROM ec_pedidos_detalle WHERE id_pedido=$id_ped AND id_producto=$id_pr";
		$eje=mysql_query($sql);
		if(!$eje){
			die("Error al consultar cantidad del pedido!!!\n".mysql_error()."\n".$sql);
		}
		$rw=mysql_fetch_row($eje);
		die($rw[0]);	
	}
	//die($user_sucursal);
	//die($id_pr);
//generamos consulta
	$sql="SELECT pd.de_valor
			FROM ec_precios_detalle pd
			RIGHT JOIN sys_sucursales s ON s.id_precio=pd.id_precio
			WHERE pd.id_producto=$id_pr AND s.id_sucursal=$user_sucursal
			ORDER BY pd.de_valor ASC";
	//die($sql);
//ejecutamos consulta
	$eje=mysql_query($sql);
	if(!$eje){
		die("Error al checar minimo de venta del producto!!!\n".mysql_error()."\n".$sql);
	}
	$rw=mysql_fetch_row($eje);
//regresamos primer resultado
	echo $rw[0];

?>