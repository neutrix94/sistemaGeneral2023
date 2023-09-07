<?php


	include("../../conectMin.php");
	
	extract($_GET);
	
	
	$sql="	SELECT
			id_subtipos,
			nombre
			FROM ec_subtipos
			WHERE id_tipo='$id_subcategoria'
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
			echo utf8_encode($row[$j]);
		}	
	}
	
	
		
	
?>