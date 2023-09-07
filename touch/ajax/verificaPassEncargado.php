<?php
	require('../../conectMin.php');

	if(isset($_GET['flag']) && $_GET['flag']=='mayoreo'){
		$clave_precio=$_GET['clave_may'];
		$clave_user=$_GET['clave'];
		$vta_transf=$_GET['clave_tr_vta'];
	//verfificamos que la lista de precios exista
		$sql="SELECT id_precio FROM ec_precios WHERE clave_precio='$clave_precio'";
		$eje=mysql_query($sql)or die("Error al consultar la lista de precios\n\n".mysql_error()."\n\n".$sql);
		if(mysql_num_rows($eje)<=0){
			die("No se encontró la lista de precios con la Clave ".$clave_precio."\nVerifique la clave y vuelva a intentar!!!");
		}else{
			$id_precio=mysql_fetch_row($eje);
		}
	//verificamos la contraseña del usuario
		$sql="SELECT id_usuario FROM sys_users WHERE id_usuario=$user_id AND vende_mayoreo='$clave_user'";
		$eje=mysql_query($sql)or die("Error al validar clave de mayoreo del vendedor\n\n".mysql_error()."\n\n".$sql);
		if(mysql_num_rows($eje)<=0){
			die("La contraseña es incorrecta!!!");
		}
	//verificamos si la transferencia existe
		if($vta_transf!='' && $vta_transf!=null){
			$sql="SELECT id_transferencia FROM ec_transferencias WHERE folio='$vta_transf'";
			//die($sql);
			$eje=mysql_query($sql)or die("Error al validar clave de mayoreo del vendedor\n\n".mysql_error()."\n\n".$sql);
			if(mysql_num_rows($eje)==1){
				$trans=mysql_fetch_row($eje);
			}
		}
		die('ok|'.$id_precio[0].'|'.$trans[0].'|');

	}

	$pass=$_POST['clave'];
	if($pass==''||$pass==null){
		die('no');
	}
	$pass=md5($pass);//CIFRAMOS EN MD5

	$sql="SELECT contrasena FROM sys_users WHERE id_usuario=(SELECT id_encargado FROM sys_sucursales WHERE id_sucursal='$user_sucursal')";
	$eje=mysql_query($sql);
	if(!$eje){
		die("Error!!!:\n".mysql_error());
	}
	$rw=mysql_fetch_row($eje);
	if($rw[0]==$pass){
		die('ok');
	}
	echo 'no';
?>