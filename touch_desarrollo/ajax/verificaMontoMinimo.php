<?php
	include("../../conectMin.php");
	$monto_pedido=$_POST['monto'];
	//die($monto_pedido);
//calculamos el monto mínimo para el apartado
	$sql="SELECT ROUND((min_apart)*$monto_pedido) FROM sys_sucursales WHERE id_sucursal='$user_sucursal'";
	$eje=mysql_query($sql)or die("Error al consultar el porcentaje mínimo de apartado de la sucursal\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	echo "ok|".$r[0];
?>