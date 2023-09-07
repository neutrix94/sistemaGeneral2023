<?php
	include("../../conectMin.php");
	
	extract($_GET);
	
	
	$consulta="SELECT sql_combo, order_combo FROM sys_catalogos WHERE id_catalogo=$id_catalogo";
	
	$res=mysql_query($consulta) or die("Error en:\n$consulta\n\nDescripcion:\n".mysql_error());
	
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);
		
		$row[0]=str_replace('$llaveDep', $valor, $row[0]);
		
		$consulta=$row[0]." ".$row[1];
		
		$consulta=str_replace('$escuela', $user_escuela, $consulta);
		
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
				echo $row[$j];
			}	
		}
		
	}
	else
		die("No se encontro el dato");
	

?>