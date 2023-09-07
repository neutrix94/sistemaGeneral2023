<?php
	include("../../../conectMin.php");

	//die('aqui');
	$flag=$_POST['fl'];
	if($flag==1){
		$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal>0 ORDER BY nombre ASC";

		
	}else if($flag==2){
		$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal=-1";
	}
	$eje=mysql_query($sql)or die("Error al consultar lista de sucursales!!!\n\n".$sql."\n\n".mysql_error());

	echo 'ok|<select class="combo" id="id_suc" onchange="prepara_acciones(this);">';
		echo '<option value="0">--Seleccionar--</option>';
		while($r=mysql_fetch_row($eje)){
			echo '<option value="'.$r[0].'">'.$r[1].'</option>';
		}
	echo '</select>';
?>