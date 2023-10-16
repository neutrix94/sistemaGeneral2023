<?php


	include("../../../conect.php");

	
	extract($_GET);
	
	
	$sql="	SELECT
			id_subcategoria,
			nombre
			FROM ec_subcategoria
			WHERE id_categoria='$id_categoria'
			ORDER BY nombre";
			
			
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
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
	
	
		
	
?>