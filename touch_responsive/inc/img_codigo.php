<?php
	include('../../include/barcode/barcode.php');

/*implementacion scar 19.08.2019 para crear el codigo de barras para la credencial del usuario*/
	if(isset($_POST['flag']) && $_POST['flag']=='credencial'){
		include('../../conectMin.php');
		$id=$_POST['id_usuario'];
	//consultamos datos y rutas del usuario
		$sql="SELECT u.codigo_barras_usuario,
				CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) as nombre,
				IF(u.id_sucursal=-1,'MULTISUCURSAL',s.nombre),
				up.nombre
			FROM sys_users u
			JOIN sys_sucursales s ON u.id_sucursal=s.id_sucursal
			JOIN sys_users_perfiles up ON up.id_perfil=u.tipo_perfil
			WHERE id_usuario=$id";
		
		$eje=mysql_query($sql)or die("Error al consultar el codigo de barras")or die("Error al consultar info del codigo de usuario");
		if(!$eje){
			die("Errror\n".mysql_error());
		}
		$r=mysql_fetch_row($eje);
	//verificamos si la imagen existe y si existe la creamos
		$filepath="../../img/codigos_barra_usuarios/".$r[0].".png";
		//die($filepath);
		if(!file_exists($filepath)){
			barcode( $filepath, $r[0],'70','horizontal','code128',false,1);
		}
		include('../../code/ajax/generadorCredencialUsuarios.php');
		die('ok|'.$r[0]);
	}
/*fin de cambio Oscar 19.08.2019*/

//	include('../../include/barcode/barcode.php');
	$folio=$_POST['text'];
	$filepath="../../img/codigos_barra/".$folio.".png";
	barcode( $filepath, $folio,'70','horizontal','code128',true,1);
?>