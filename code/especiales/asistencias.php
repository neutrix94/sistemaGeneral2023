<?php
	//php_track_vars;
	
	extract($_GET);
	extract($_POST);
	
//CONECCION Y PERMISOS A LA BASE DE DATOS
	include("../../conect.php");
//seleccionamos permisos de acuerdo al perfil
	$sql="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_menu=196 AND id_perfil=$perfil_usuario";
	$eje=mysql_query($sql)or die("Error al consultar los permisos del perfil!!<br>".mysql_error()."<br>".$sql);	
	$r=mysql_fetch_row($eje);

//verifica que no sea sistemna en linea
	$sql = "SELECT id_sucursal AS system_type FROM sys_sucursales WHERE acceso = 1";
	$stm = mysql_query( $sql ) or die( "Error al consultar el tipo de sistema : " . mysql_error() );
	$row = mysql_fetch_assoc( $stm );
	if( $row['system_type'] == -1 && $sucursal_id != 1 ){
		die( "<script>
				alert( 'NO esta permitido que registres tu asistencia en el sistema en linea, REGISTRATE LOCALMENTE!!!' );
				location.href = '../../index.php?';
			</script>" );
	}
	$smarty->assign('ver_log_login',$r[0]);
	$smarty->display("especiales/asistencias.tpl");
	
?>