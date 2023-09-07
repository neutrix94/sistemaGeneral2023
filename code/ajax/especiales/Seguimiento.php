<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
/*implementación Oscar 01.10.2018 para regresar el nombre de la sucursal y tipo de sistema en el encabezado*/
	if(isset($_GET['fl']) && $_GET['fl']==1){
		$sql="SELECT descripcion_sistema FROM sys_sucursales WHERE id_sucursal='$user_sucursal'";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al consultar descripción de sistema!!!\n\n".$sql."\n\n".mysql_error());
		$rw=mysql_fetch_row($eje);
		die('ok|'.$rw[0]);//regresamos respuesta
	}
/*fin de cambio 01.10.2018*/

	extract($_GET);
	
	echo "exito|0~0";
	
?>