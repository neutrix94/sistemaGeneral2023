<?php
	include('../../conectMin.php');
	$sql="SELECT descuento FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	//die($sql);
	$eje=mysql_query($sql);
	if(!$eje){
		die('no');
	}
	$nR=mysql_num_rows($eje);
	if($nR<1){
		die('no');
	}
	$rw=mysql_fetch_row($eje);
	echo ($rw[0]*100);

?>