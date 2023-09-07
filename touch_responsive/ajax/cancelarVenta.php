<?php
	include("../../conectMin.php");
	
	$id_pedido = $_GET["idp"];
	
	$cs = "UPDATE ec_pedidos SET id_estatus = '4' WHERE id_pedido = '{$id_pedido}' ";
	
	if (mysql_query($cs)) echo "OK";
	
	mysql_close();
	
	exit ();

?>