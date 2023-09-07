<?php
	extract($_POST);
	include('../../../../conect.php');
	$sql="SELECT nombre FROM ec_productos WHERE id_productos='$id'";
	$ejecuta=mysql_query($sql);
	if($ejecuta){
		$row=mysql_fetch_row($ejecuta);
		echo $row[0];
	}else{
		echo 'error';
	}


?>