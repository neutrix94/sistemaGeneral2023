<SELECT id="cambiaSuc" onchange="cargaSucursal();" style="padding:10px;width:100px;border-radius:5px;">
<?php
	if($sucursal_id>=1){
		//if(isset($id_suc_adm)){
		//	$da=mysql_query("SELECT nombre from sys_sucursales WHERE id_sucursal=$id_suc_adm");
		//}else{
		$da=mysql_query("SELECT nombre from sys_sucursales WHERE id_sucursal=$sucursal_id");
		//}
		
		if(!$da){
			die('Error al sacar nombre de sucursal actual!!!'.$da."\n\n".mysql_error());
		}
		$dat=mysql_fetch_row($da);
		$nombre=$dat[0];
?>
		<option value="<?php echo $sucursal;?>"><?php echo $nombre;?></option>
<?php
	}//termina if $sucursal>0
	else if($sucursal_id==-1){//si es línea
		if(isset($id_suc_adm)){
		 	$suc_sel=mysql_query("SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal=$id_suc_adm")or die("Error al consultar la sucursal");
			$suc_sel_1=mysql_fetch_row($suc_sel);	
			echo '<option value="'.$suc_sel_1[0].'">'.$suc_sel_1[1].'</option>';
		 //armamos consulta de sucs
			$sucs="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal!=$id_suc_adm AND id_sucursal>0";
		}else{
			$sucs="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal>=1";
		}
	
	//die($sql);
	/*if($sucursal_id==1||$sucursal_id==-1){
		//echo 'suc';
		$WHERE2="WHERE id_sucursal>0 ORDER by id_sucursal ASC";
	}else{
		$WHERE2='WHERE id_sucursal='.$sucursal_id;
	}*/
	//$sql="SELECT id_sucursal,id_producto,descripcion,existencias FROM ec_inventario_sincronizacion";
	//$sucs.=' '.$WHERE2;
//	die($sucs);
	$ejSucs=mysql_query($sucs);
	if(!$ejSucs){
		die('Error al consultar las sucursales existentes '.mysql_error());
	}
	while($ro=mysql_fetch_row($ejSucs)){
?>
		<option value="<?php echo $ro[0];?>"><?php echo $ro[1];?></option>	
<?php		
	}
}//fina de else
?>
</SELECT>