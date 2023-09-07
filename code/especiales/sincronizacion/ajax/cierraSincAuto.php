<?php
	include("../../../../conectMin.php");
//libermaos el serevidor de ventas
	$sql="UPDATE sys_menus SET en_uso=0 WHERE liga='code/especiales/sincronizacion/pantalla-sincVentas.php?'";
	$eje=mysql_query($sql)or die("Error al liberar el meú de sincronización automática!!!\n\n".$sql."\n\n".mysql_error());
	echo 'ok';
?>