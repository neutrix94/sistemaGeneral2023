<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	//print_r($_GET);
	
	header("Content-Type: text/plain;charset=utf-8");
	mysql_set_charset("utf8");
	if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
	
	extract($_GET);
    
    if(!isset($sucur))
        $sucur=-1;
	
	$sql="
			SELECT
			tp.id_transferencia_producto,
			tp.id_transferencia,
			p.nombre,
			tp.cantidad_entrada,
			p.id_productos
			FROM ec_transferencia_productos tp
			JOIN ec_productos p ON tp.id_producto_or = p.id_productos
			WHERE tp.id_transferencia='$id'  
	     ";
        $res=mysql_query($sql) or die("Error en:\$sql\n\nDescripcion:\n".mysql_error());
		
		$num=mysql_num_rows($res);		
		
		echo 'exito';
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo 
			'|NO~$LLAVE~'.$row[4].'~'.$row[2].'~'.$row[3];
		}
  
?>