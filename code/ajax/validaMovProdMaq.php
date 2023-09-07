<?php
	include("../../conectMin.php");
	$id_prod=$_GET['id_pr'];
	$sql="SELECT es_maquilado FROM ec_productos WHERE id_productos='$id_prod'";
	$eje=mysql_query($sql)or die("Erorr al consultar si el producto es maquilado!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if($r[0]==0){
		die('ok|');
	}else{
		die('ok|maquilado|Este producto es maquilado, Realmente desea agregarlo al movimiento de Almacen?');
	}
?>