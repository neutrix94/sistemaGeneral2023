<?php
	include('../../../../conexionMysqli.php');

	$searched = $_POST['search'];
	$specific_search = explode( ' ', $searched );
	$sql = "SELECT 
				ax.id_usuario,
				ax.nombre,
				ax.login,
				ax.id_sucursal
			FROM(
				SELECT
					id_usuario, 
					CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre,
					login,
					id_sucursal
				FROM sys_users
				WHERE IF('{$_POST['sucursal_id']}' != '0', id_sucursal = '{$_POST['sucursal_id']}', id_sucursal !=0)
				GROUP BY id_usuario
			)ax 
			WHERE ax.login LIKE '%{$searched}%'
			OR (";

	$c = 0;
	foreach ( $specific_search as $key => $search_one ) {
		$sql .= ( $c > 0 ? " AND" : null);
		$sql .= " ax.nombre LIKE '%{$search_one}%'";
		$c ++;
	}
	$sql .= ") GROUP BY ax.id_usuario";
	$eje = $link->query( $sql ) or die( "Error al consultar usuarios : {$link->error}" );

	echo 'ok';//die($sql);
	while( $r = $eje->fetch_row() ){
		echo "|{$r[0]}~{$r[1]}~{$r[2]}~{$r[3]}";
	}
?>