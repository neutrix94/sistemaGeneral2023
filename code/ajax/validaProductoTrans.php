<?php
	include("../../conectMin.php");
	
	extract($_GET);
	
	
	//Buscamos por orden de lista
	$sql="SELECT id_productos FROM ec_productos WHERE orden_lista='$code'";
	
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);
		
		//echo "exito|$row[0]";
	}
	else
	{
		//Buscamos por codigo 1
		$sql="SELECT id_productos, nombre FROM ec_productos WHERE codigo_barras_1='$code'";
	
		$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
		if(mysql_num_rows($res) > 0)
		{
			$row=mysql_fetch_row($res);
		
			//echo "exito|$row[0]";
		}
		else
		{	
	
			//Buscamos por codigo 2
			$sql="SELECT id_productos, nombre FROM ec_productos WHERE codigo_barras_2='$code'";
	
			$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
			if(mysql_num_rows($res) > 0)
			{
				$row=mysql_fetch_row($res);
			
				//echo "exito|$row[0]";
			}
			else
			{
	
				//Buscamos por codigo 3
				$sql="SELECT id_productos, nombre FROM ec_productos WHERE codigo_barras_3='$code'";
	
				$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
				if(mysql_num_rows($res) > 0)
				{
					$row=mysql_fetch_row($res);
		
					//echo "exito|$row[0]";
				}
				else
				{
					//Buscamos por codigo 4
					$sql="SELECT id_productos, nombre FROM ec_productos WHERE codigo_barras_4='$code'";
	
					$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
					if(mysql_num_rows($res) > 0)
					{
						$row=mysql_fetch_row($res);
		
						//echo "exito|$row[0]";
					}
				}
			}
		}			
	}
	
	
	if(!isset($row))
		die("No se encontro la clave del producto insertado");
		
	echo "exito|$row[0]|$row[1]";
	
		
	
?>