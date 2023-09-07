<?php

	include("../../conectMin.php");
	
	extract($_GET);
	
	header("Content-Type: text/plain;charset=utf-8");
	mysql_set_charset("utf8");
	if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
	
	//buscamos el periodo activo
	/*$sql="SELECT id_periodo FROM eye_periodo WHERE activo=1";	
	$res=mysql_query($sql);	
	if(!$res)	  
	{
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
	}	
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);		
		$periodo=$row[0];
	}
	*/
	
	//Buscamos el query de datos
	$sql="SELECT query FROM sys_grid WHERE tabla_relacionada='$tabla' AND id_grid=$id_grid";
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	if(mysql_num_rows($res) <= 0)
		die("Error: No se encontro la informacion requerida");
	
	$row=mysql_fetch_row($res);
	$consulta=$row[0];
	
	$consulta=str_replace('$llave', $id, $consulta);
	$consulta=str_replace('$periodo', $periodo, $consulta);
	
	//Buscamos los datos de la consulta final
	$res=mysql_query($consulta) or die("Error en:\n$consulta\n\nDescripcion:\n".mysql_error());
	
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

?>