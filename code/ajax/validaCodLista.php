<?php
	include('../../conectMin.php');
	extract($_POST);
	//die($id.",".$acc.",".$datos);
//validación de orden de lista
	if(!isset($fl)){
		$sql="SELECT orden_lista,nombre,id_productos FROM ec_productos WHERE orden_lista LIKE '%$datos%' ORDER BY orden_lista ASC";
		$eje=mysql_query($sql)or die("Error al buscar coincidencias de Orden de Lista!!!\n\n".$sql."\n\n".mysql_error());
		$num=mysql_num_rows($eje);
		echo 'ok|';
		if($num>0){
			$res=0;
			echo "<table><tr><td><td></tr>";
			while($r=mysql_fetch_row($eje)){
				echo '<tr class="opc_o_l"><td>'.$r[0].'</td><td>'.$r[1].'</td></tr>';
				if($num==1){
					$res=$r[2];
				}
			}
			echo "</table>";
			echo '<style>.opc_o_l:hover{background:#BDB76B;}</style>';
		}
		echo '|'.$num;

		$sql="SELECT orden_lista,nombre,id_productos FROM ec_productos WHERE orden_lista='$datos'";
		$eje=mysql_query($sql)or die("Error al buscar coincidencias de Orden de Lista!!!\n\n".$sql."\n\n".mysql_error());
		$num=mysql_num_rows($eje);
		$r=mysql_fetch_row($eje);
		$res=$r[2];
	//si es edicion
		if($acc==1&&$num==1&&$res==$id){
			die('|0');
		}
		die('|'.$num);
	}

//validacion de login de usuario
	if($fl=='login'){
		$sql="SELECT nombre, apellido_paterno,id_usuario FROM sys_users WHERE login='$datos' ORDER BY id_usuario ASC";
		$eje=mysql_query($sql)or die("Error al buscar coincidencias de Login de Usuarios!!!\n\n".$sql."\n\n".mysql_error());
		$num=mysql_num_rows($eje);
		$r=mysql_fetch_row($eje);
		if($num==1){
			$res=$r[2];
		}
		echo 'ok|';
		if($acc==1&&$num==1&&$res==$id){
			die('|0');
		}
		die('|'.$num);
	}

?>