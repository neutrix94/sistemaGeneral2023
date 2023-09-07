<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	
	//Buscamos el descuento
	
	$sql="SELECT
	      monto_desc,
	      porc_desc,
	      min_compra_desc
	      FROM ec_clientes
	      WHERE id_cliente=$id_cliente";
	
	$res=mysql_query($sql);
	if(!$res)
	{
		//mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}
	
	$row=mysql_fetch_row($res);
	
	if($subtotal > $row[2])
	{
		if($row[0] > 0)
		{
			echo "exito|$row[0]|".($iva-$row[0]*0.16);
		}
		else
		{
			echo "exito|".(($row[1]/100)*$subtotal)."|".($iva-(($row[0]/100)*$subtotal)*0.16);	
		}
	}
	else
	{
		echo "exito|0|$iva";		
	}
	
?>