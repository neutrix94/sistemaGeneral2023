<SELECT id="cambiaSuc" onchange="cargaSucursal();" style="padding:10px;width:100px;border-radius:5px;">
<?php
	if($sucursal_id>=2){
		$da=mysql_query("SELECT nombre from sys_sucursales WHERE id_sucursal=$sucursal");
		if(!$da){
			die('Error al sacar nombre de sucursal actual!!!');
		}
		$dat=mysql_fetch_row($da);
		$nombre=$dat[0];
?>
		<option value="<?php echo $sucursal;?>"><?php echo $nombre;?></option>
<?php
	}//termina if $sucursal>0
	else{
	if(isset($id_suc_adm)){
		$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal=$id_suc_adm";
		$eje_suc=mysql_query($sql)or die("Error al checar datos de la sucursal");
		$r_s=mysql_fetch_row($eje_suc);
		echo '<option value="'.$r_s[0].'">'.$r_s[1].'</option>';
	}
	$sucs="SELECT id_sucursal,nombre FROM sys_sucursales";
	if($sucursal_id==1||$sucursal_id==-1){
		echo 'suc';
		$WHERE2="WHERE id_sucursal>0 AND id_sucursal!=$id_suc_adm ORDER by id_sucursal ASC";
	}else{
		$WHERE2='WHERE id_sucursal='.$sucursal_id;
	}
	//$sql="SELECT id_sucursal,id_producto,descripcion,existencias FROM ec_inventario_sincronizacion";
	$sucs.=' '.$WHERE2;
//	die($sucs);
	$ejSucs=mysql_query($sucs);
	if(!$ejSucs){
		die('Error al consultar las sucursales existentes');
	}
	while($ro=mysql_fetch_row($ejSucs)){
?>
		<option value="<?php echo $ro[0];?>"><?php echo $ro[1];?></option>	
<?php		
	}
}//fina de else
?>
</SELECT>