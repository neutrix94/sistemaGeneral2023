<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	$sql="SELECT precio_venta_mayoreo, porc_iva, porc_ieps, precio_compra FROM ec_productos WHERE id_productos=$id";
	$res=mysql_query($sql);
	if(!$res)
	{
		//mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);
		
		//Buscamos si hay un modificador de precio para el cliente
		if($id_cliente != '')
        {
    		$sq="SELECT
    		      IF(precio > 0,
    			  	precio,
    				IF(descuento_porc > 0,
    					(1-descuento_porc/100)*$row[0],
    					IF(descuento_monto > 0,
    						$row[0]-descuento_monto,
    						$row[0]
    					)
    				)
    			  )
    		      FROM ec_clientes_productos
    		      WHERE id_cliente=$id_cliente
    		      AND id_producto=$id
    		";
    		//echo $sq;
    		$re=mysql_query($sq);
    		if(!$re)
    		{
    			//mysql_query("ROLLBACK"); 
    			die("Error en:\n$sq\n\nDescripcion:\n".mysql_error());
    		}
    		if(mysql_num_rows($re) > 0)
    		{
    			$ro=mysql_fetch_row($re);
    			$row[0]=$ro[0];
    		}
    	}
    	//Buscamos si hay un modificador de precio para proveedor
    	if($id_proveedor != '')
        {
            $sq="SELECT precio FROM ec_proveedor_producto WHERE id_proveedor=$id_proveedor";
            $re=mysql_query($sq);
            if(!$re)
            {
                //mysql_query("ROLLBACK"); 
                die("Error en:\n$sq\n\nDescripcion:\n".mysql_error());
            }
            if(mysql_num_rows($re) > 0)
            {
                $ro=mysql_fetch_row($re);
                $row[3]=$ro[0];
            }
        }
    					
		echo "exito|$row[0]|$row[1]|$row[2]|$row[3]";
	}
	else
		die("Error, no se encontro el producto $id");
	
?>