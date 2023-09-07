<?php
	if(!include('../../conectMin.php')){
		die("Sin archivo de conexion...!!!");
	}
	$sql="SELECT descuento FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql);
	if(!$eje){
		die("Error al consultar el descuento por sucursal!!!\n\n".$sql."\n\n".mysql_error());
	}
	$rw=mysql_fetch_row($eje);
	echo 'ok|'.$rw[0];
?>