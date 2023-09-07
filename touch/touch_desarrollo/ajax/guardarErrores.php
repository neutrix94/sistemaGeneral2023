<?php
	include('../../conectMin.php');
	$error=$_GET['msg'];
	$sql="INSERT INTO sys_bitacora_errores VALUES(null,$user_sucursal,'$error',now(),$user_id,'',0)";
	$eje=mysql_query($sql)or die("Error al insertar la diferencia en la bitácora!!!\n".mysql_error());
	echo 'ok';
?>