<?php

	include("../../conectMin.php");
	
	extract($_GET);
	
	//Buscamos los datos de la consulta
	
	$sql="SELECT
	      sql_combo,
	      order_combo
	      FROM sys_catalogos
	      WHERE id_catalogo='$id'";
		  
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	if(mysql_num_rows($res) > 0)
	{
	
		$row=mysql_fetch_row($res);
	
		$sql=$row[0]." ".$row[1];
		
		/*if($row[9] == '$DATE')
			$row[9]=date("Y-m-d");
		if($row[9] == '$TIME')
			$row[9]=date("h:i:s");	
		if($row[9] == '$USUARIO')
			$row[9]=$user_id;
		if($row[9] == '$SUCURSAL')
			$row[9]=$user_sucursal;*/
			
		$sql=str_replace('$DATE', date("Y-m-d"), $sql);
		$sql=str_replace('$TIME', date("h:i:s"), $sql);
		$sql=str_replace('$USUARIO', $user_id, $sql);
		$sql=str_replace('$SUCURSAL', $user_sucursal, $sql);
		$sql=str_replace('$LLAVE', $val, $sql);	
		
		
		$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
		
		$num=mysql_num_rows($res);
		
		
		echo "exito|$num";
		
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo "|".utf8_encode($row[0])."~".utf8_encode($row[1]);
		}	  
	}
	else
		die("Error, dato no encontrado");
	
	
?>