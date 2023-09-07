<?php

	
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	//print_r($_GET);
	
	
	extract($_GET);
	
	if($tipo == 1)
	{
	

		$sql="SELECT
				id_pedido,
				folio_pedido,
				c.nombre,
				total,
				(SELECT fecha FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido ORDER BY id_pedido_pago DESC LIMIT 1),
				(SELECT id_guia FROM ec_guias WHERE id_pedido = p.id_pedido),
				0,
				'ver'
				FROM ec_pedidos p
				JOIN ec_clientes c ON p.id_cliente = c.id_cliente
				WHERE id_tipo_envio=3
				AND enviado=0";
	
	//Buscamos los datos de la consulta final
		$res=mysql_query($sql) or die("Error en:\$sql\n\nDescripcion:\n".mysql_error());
		
		$num=mysql_num_rows($res);		
		
		echo "exito";
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo "|";
			for($j=0;$j<sizeof($row);$j++)
			{	
				if($j > 0)
					echo "~";
				echo utf8_encode($row[$j]);
			}	
		}
	}
	
	if($tipo == 2)
	{
		$sql="
			  (
			  	SELECT 0, '-sin guía-'
			  )
			  UNION
		      (
				SELECT
		      	g.id_guia,
			  	g.numero
			  	FROM ec_guias g
				LEFT JOIN ec_pedidos p ON g.id_pedido = p.id_pedido
			  	WHERE g.usada=0
				AND (g.id_pedido IS NULL OR p.enviado=0)
			  )";
		$res=mysql_query($sql);
		if(!$res)
			die("Error en:\n$sql\n\nDescripción:\n".mysql_error());	  
		$num=mysql_num_rows($res);		
		echo "exito|$num";		
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo "|";
			for($j=0;$j<sizeof($row);$j++)	
			{
				if($j > 0)
					echo "~";
				echo $row[$j];	
			}
		}
	}
	
	
	if($tipo == 3)
	{
		$strTrans="AUTOCOMMIT=0";
		mysql_query($strTrans);
		mysql_query("BEGIN");
		
		$aux=explode('|', $pedidos);
		for($i=0;$i<sizeof($aux);$i++)
		{
			$ax=explode('~', $aux[$i]);
			if($ax[1] != 0)
			{
				//buscamos si no hay una guia con ese pedido
				$sql="UPDATE ec_guias SET id_pedido=NULL WHERE id_pedido=".$ax[0];
				$res=mysql_query($sql);
				if(!$res)
				{
					echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
					mysql_query("ROLLBACK");
					die();
				}
			
				$sql="UPDATE ec_guias SET id_pedido='".$ax[0]."' WHERE id_guia=".$ax[1];
				$res=mysql_query($sql);
				if(!$res)
				{
					echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
					mysql_query("ROLLBACK");
					die();
				}
			}
			if($ax[1] == 0)
			{
				$sql="UPDATE ec_guias SET id_pedido=NULL WHERE id_pedido=".$ax[0];
				$res=mysql_query($sql);
				if(!$res)
				{
					echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
					mysql_query("ROLLBACK");
					die();
				}
			}
			if($ax[2] == 1)
			{
				$sql="UPDATE ec_pedidos SET enviado=1 WHERE id_pedido=".$ax[0];
				$res=mysql_query($sql);
				if(!$res)
				{
					echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
					mysql_query("ROLLBACK");
					die();
				}
				
				
				
			}
			
			
			
		}
	


		mysql_query("COMMIT");
		die("exito");
	}


?>