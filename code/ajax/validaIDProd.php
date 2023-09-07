<?php

	include("../../conectMin.php");



	extract($_GET);
	
	
	$sql="SELECT COUNT(1) FROM ec_productos WHERE id_productos='$id'";
	
	$res=mysql_query($sql) or die(mysql_error());
	
	
	$row=mysql_fetch_row($res);
	
	if($row[0] > 0)
	{
		die("El ID ya existe, elija otro.");
	}
	
	die("exito");


?>