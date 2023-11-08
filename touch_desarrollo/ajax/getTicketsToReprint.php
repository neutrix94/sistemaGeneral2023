<?php
	include( '../../conect.php' );
	include( '../../conexionMysqli.php' );
	$condition = "";
	if( isset( $_GET['key'] ) ){
		$key = $_GET['key'];
		$condition = " AND ( CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) LIKE '%{$key}%'";
		$condition .= " OR p.folio_nv LIKE '%{$key}%' OR p.total LIKE '%{$key}%'";
		$condition .= " ) ";
	}
	$current_year = date("Y");
	//die( "YEAR : {$current_year}" );
	$sql = "SELECT
				CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS user_name,
				p.folio_nv,
				p.total,
				p.id_pedido,
				p.fecha_alta
			FROM ec_pedidos p
			LEFT JOIN sys_users u
			ON p.id_usuario = u.id_usuario
			WHERE p.id_sucursal = {$user_sucursal}
			AND p.fecha_alta LIKE '%{$current_year}%'
			{$condition}
			ORDER BY p.id_pedido DESC
			LIMIT 30";//die( $sql );
	$stm = $link->query( $sql ) or die( "Error al consultar los datos de las notas de venta : {$link->error}" );
	$resp = "";
	while ( $row = $stm->fetch_assoc() ) {
		$resp .= "<tr>
			<td class=\"text-start\">{$row['user_name']}</td>
			<td class=\"text-center\">{$row['folio_nv']}</td>
			<td class=\"text-end\">{$row['total']}</td>
			<td class=\"text-end\">{$row['fecha_alta']}</td>
			<td class=\"text-center\">
				<button
					type=\"button\"
					class=\"btn btn-light\"
					onclick=\"print_ticket( {$row['id_pedido']} );\"
				>
					<i class=\"icon-print\"></i>
				</button>
			</td>
		</tr>";
	}
	die( $resp );
?>