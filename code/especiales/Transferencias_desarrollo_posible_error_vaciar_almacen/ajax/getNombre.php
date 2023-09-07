<?php
//1. Hace extraxt de variables POST
	extract($_POST);
//2. Incluye archivo %/conectMin.php%%
	include('../../../../conect.php');
//3. Consulta el nombre del producto
	$sql="SELECT nombre FROM ec_productos WHERE id_productos='$id'";
	$ejecuta=mysql_query($sql);
	if($ejecuta){
		$row=mysql_fetch_row($ejecuta);
		echo $row[0];
	}else{
		echo 'error';
	}
?>