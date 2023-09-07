<?php

		//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	
	$sql="SELECT
	      pagado,
	      facturado
	      FROM ec_pedidos
	      WHERE id_pedido=$id";
		  
	$res=mysql_query($sql);
	if(!$res)
	{
		//mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}	  
	
	
	if(mysql_num_rows($res) <= 0)
		die("No se encontro la factura solicitada");
	
	$row=mysql_fetch_row($res);
	
	if($row[0] == '0')
		die("No es posible facturar una nota de venta no pagada");
	
	if($row[1] == '1')
		die("La nota de venta ya esta facturada");
	
	
	die("exito|".base64_encode($id));
	
	

?>