<?php
		include( '../../../../../config.ini.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$action = $_GET['fl'];
		switch ( $action ) {

			case 'seekPeopleLoged':
			//die( 'user. :' . $_GET['users'] );
				$excluyed_user = ( isset( $_GET['excluyed_user'] ) ? $_GET['excluyed_user'] : null );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : null );
				echo seekPeopleLoged( $_GET['key'], $_GET['users'], $_GET['type'], $excluyed_user, $counter, $link );
			break;
			
			case 'insertUserTransfer':
				$employee = $_GET['id'];
				$transfer_id = $_GET['transfer'];
				$parts_limit = $_GET['parts'];
				$total_parts = $_GET['parts_total'];
				echo insertUserTransfer( $transfer_id, $employee, $user_id, $parts_limit, $total_parts, $link );
			break;

			case 'getAssignedUsers':
			$excluyed_user = ( isset( $_GET['excluyed_user'] ) ? $_GET['excluyed_user'] : null );
			$excluyed_assignation = ( isset( $_GET['excluyed_assignation'] ) ? $_GET['excluyed_assignation'] : null );
				echo getAssignedUsers( $_GET['p_k'], $excluyed_user, $excluyed_assignation, $link );
			break;

			case 'getUsersCombo' : 
				echo getUsersCombo( $_GET['current_users'], $_GET['current_user'], $_GET['count'], 
					$_GET['val'], $_GET['name'], $link );
			break;

			case 'reassignTransfer':
				echo reassignTransfer( $_GET['p_k'], $_GET['users_array'], $user_id, $link );
			break;

			case 'changeUserToUser':
				echo changeUserToUser( $user_id, $_GET['old_user'], $_GET['new_user'],
					$_GET['transfer_id'], $link );
			break;

			case 'playSupply':
				echo playSupply( $_GET['transfer'], $link );
			break;

			case 'getDetail':
				if( !isset( $_GET['user_assignment_id'] ) ){
					$_GET['user_assignment_id'] = null;
				}

				echo getDetail( $_GET['transfer_id'], $_GET['user_assignment_id'], $link );
			break;

			case 'getTransferHeader' :
				echo getTransferHeader( $_GET['transfer_id'], $link );
			break;

			case 'transferOutput' :
				echo transferOutput( $_GET['id'], $link );
			break;

			case 'closeReassignTransfer' :
				echo closeReassignTransfer( $_GET['transfer_id'], $link );
			break;

			case 'deleteAssignmentDetail' :
				echo deleteAssignmentDetail( $_GET['user_part_id'], $link );
			break;

			case 'getReasignationDetail' : 
				$transfer_id = ( isset( $_GET['transfer_id'] ) ? $_GET['transfer_id'] : $_POST['transfer_id'] ); 
				$user_assignment_id = ( isset( $_GET['user_assignment_id'] ) ? $_GET['user_assignment_id'] : $_POST['user_assignment_id'] ); 
				$type = ( isset( $_GET['type'] ) ? $_GET['type'] : $_POST['type'] ); 
				echo getReasignationDetail( $transfer_id, $user_assignment_id, $type, $link );
			break;

			case 'save_reassignation' :
				$disabled_assignation_id = ( isset( $_GET['disabled_assignation_id'] ) ? $_GET['disabled_assignation_id'] : $_POST['disabled_assignation_id'] );
				echo save_reassignation( $_GET['data'], $_GET['transfer_id'], $disabled_assignation_id, $link );

			break;

			default:
				die( 'Permission Denied!' );
			break;
		}

	function save_reassignation( $data, $transfer_id, $disabled_assignation_id, $link ){
		$users = explode( "|", $data );
		$link->autocommit( false );
		$asignations_ids = "";
		$sql = "SELECT 
					id_transferencia_surtimiento AS transfer_supply_id
				FROM ec_transferencias_surtimiento
				WHERE id_transferencia = {$transfer_id}
				AND id_usuario_asignado = {$disabled_assignation_id}";
		$stm = $link->query( $sql ) or die( "Error consultar datos de usuario que sale de surtimiento : {$link->error}" );	
		$row = $stm->fetch_assoc();
	//deshabilita el usuario
		$sql = "UPDATE ec_transferencias_surtimiento 
					SET id_status_asignacion = 5 
				WHERE id_transferencia_surtimiento = {$row['transfer_supply_id']}";

		$stm = $link->query( $sql ) or die( "Error al deshabilitar usuario que sale de surtimiento : {$link->error}" );	

		$sql = "DELETE FROM ec_transferencias_surtimiento_detalle 
				WHERE id_transferencia_surtimiento = {$row['transfer_supply_id']}
				AND id_status_surtimiento IN( 1 )";
		//die( $sql );
				$stm = $link->query( $sql ) or die( "Error al eliminar detalles de reasignacion de usuario que sale : {$link->error}" );	
	//reasignar con los nuevo usarios
		foreach ( $users as $key => $user ) {
			$user = explode ("~", $user );
			$user_id = $user[0];
			$parts_limit = $user[1];
		//consulta el id de surtimeinto
			$sql = "SELECT 
						id_transferencia_surtimiento AS transfer_supply_id
					FROM ec_transferencias_surtimiento
					WHERE id_transferencia = {$transfer_id}
					AND id_usuario_asignado = {$user_id}";
			$stm = $link->query( $sql ) or die( "Error al consultar el id de cabecera de asignacion de transferencia : {$link->error}" );
			$row_tmp = $stm->fetch_assoc();
			$transfer_supply_id = $row_tmp['transfer_supply_id'];
		//elimina las asinaciones anteriores de los usuarios
			$sql = "DELETE FROM ec_transferencias_surtimiento_detalle 
					WHERE id_transferencia_surtimiento = {$transfer_supply_id}
					AND id_status_surtimiento IN( 1,2 )";
			$stm = $link->query( $sql ) or die( "Error al eliminar detalles de reasignacion de transferencia : {$link->error}" );	

		//consulta el numero de transferencias que faltan por surtir
			$sql = "SELECT
						tp.id_transferencia_producto,
						tp.cantidad,
						tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tsd.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				AND ppua.es_principal = '1'		
				AND ppua.habilitado = '1'
				WHERE tp.id_transferencia = '{$transfer_id}'
				AND tsd.id_transferencia_producto IS NULL
	            GROUP BY tp.id_transferencia_producto
	            ORDER BY ppua.letra_ubicacion_desde, 
	            ppua.numero_ubicacion_desde, 
	            ppua.pasillo_desde, 
	            ppua.altura_desde ASC
	            LIMIT {$parts_limit}";
			$stm = $link->query( $sql ) or die( "Error al consultar partidas pendientes de asignar!" . $sql .  $link->error );
		//	if( $stm->num_rows <= 0 ){
			//	return 'No hay detalles de transferencias por asignar!';
		//	}
			while ( $r = $stm->fetch_assoc() ) {
				$sql = "INSERT INTO ec_transferencias_surtimiento_detalle (id_transferencia_surtimiento, 
					id_transferencia_producto, id_status_surtimiento ) VALUES (
					'{$transfer_supply_id}', '{$r['id_transferencia_producto']}', 1 )";
				$exc = $link->query( $sql ) or die( "Error al insertar el detalle de surtimiento : " . $link->error );
			}
			$sql = "SELECT 
						ts.id_transferencia_surtimiento,
						COUNT( tsd.id_surtimiento_detalle ) 
					FROM ec_transferencias_surtimiento ts
					LEFT JOIN ec_transferencias_surtimiento_detalle tsd
					ON ts.id_transferencia_surtimiento = tsd.id_transferencia_surtimiento
					WHERE ts.id_transferencia = {$transfer_id} 
					AND ts.id_transferencia_surtimiento = {$transfer_supply_id}
					GROUP BY id_transferencia_surtimiento";
			$getCounts = $link->query( $sql ) or die( "Error al consultar los contadores : {$link->error}" );
			while( $count = $getCounts->fetch_row() ){
			//actualiza cabeceras de surtimiento
				$sql = "UPDATE ec_transferencias_surtimiento
							SET id_status_asignacion = 1 ,
							total_partidas = ({$count[1]}) /*impelemntacion Oscar 2023 para actualizar el numero de partidas*/ 
						WHERE id_transferencia_surtimiento = {$count[0]}";
				$link->query( $sql ) or die( "Error al actualizar cabecera de surtimiento : " . $link->error );
			}			
		}
		$link->autocommit( true );
		die('ok');
	}

	function deleteAssignmentDetail( $user_part_id, $link ){
		/*$sql = "DELETE FROM ec_transferencias_surtimiento_detalle 
				WHERE id_transferencia_surtimiento = {$user_part_id} 
				AND id_status_surtimiento IN( 1, 2 )";
		$stm = $link->query( $sql ) or die( "Error al liberar la asignacion del estatus Reasignando : {$link->error}" );
		*/
	//modificacion Oscar 2023 para actualizar el detalle de transferencias surtimiento a no surtida cuando esta en surtimiento
		$sql = "UPDATE ec_transferencias_surtimiento_detalle 
					SET id_status_surtimiento = 1
				WHERE id_transferencia_surtimiento = {$user_part_id} 
				AND id_status_surtimiento IN( 2 )";
		$stm = $link->query( $sql ) or die( "Error al liberar la asignacion del estatus Reasignando : {$link->error}" );
		return 'ok';
	}

	function closeReassignTransfer( $transfer_id, $link ){
		$sql = "UPDATE ec_transferencias_surtimiento 
					SET id_status_asignacion = IF( id_status_asignacion = 5, 5, 1 )
				WHERE id_transferencia = {$transfer_id}";
		$stm = $link->query( $sql ) or die( "Error al liberar la asignacion del estatus Reasignando : {$link->error}" );
		return 'ok';
	}


	function seekPeopleLoged( $txt, $users, $type, $excluyed_user = null, $counter, $link ){
		$condition = "";
		if( $excluyed_user != null ){
			$condition .= "AND u.id_usuario NOT IN( $excluyed_user )";
			//die( 'here' . $condition );
		}
		$condition_counter = '';
		if( $type == 2 ){
			$condition_counter = $counter;	
		}
		$sql= "SELECT
					u.id_usuario AS id,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
					IF( ts.id_transferencia_surtimiento IS NULL, 
						0, /*COUNT( ts.id_transferencia_surtimiento ) */
						SUM( IF( ts.id_status_asignacion <= 3, 1, 0 ) )
					) AS assigned_transfer
				FROM sys_users u
				LEFT JOIN ec_transferencias_surtimiento ts ON u.id_usuario = ts.id_usuario_asignado
				LEFT JOIN ec_registro_nomina rn ON u.id_usuario = rn.id_empleado
				WHERE u.id_sucursal = 1
				{$condition}
				AND rn.fecha = DATE_FORMAT( NOW(), '%Y-%m-%d')
				AND rn.hora_salida = '00:00:00'
				AND (";
		//busqueda por coincidencias
		$vals = explode( ' ' , $txt );
		foreach ($vals as $key => $ref) {
			$sql .= ( $key > 0 ? " AND" : "" ) . " CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno )";
			$sql .= " LIKE '%{$ref}%'";
		}
		$sql .= ")";
		$sql .= ( $users != null && $users != '' && $users != 'undefined' ?  " AND u.id_usuario NOT IN( {$users} )" : "" );
		$sql .= " GROUP BY u.id_usuario";
//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar usuarios logueados : " . $sql . $link->error );
		$resp = "";
		if( $stm->num_rows <= 0 ){
			return '<div>No se encontraron usuarios que coincidan con la búsqueda!</div>';
		}

		while ( $r = $stm->fetch_assoc() ) {
			$resp .= "<div 
						class=\"seeker_result\"
						onclick=\"addPeopleTransfer( '{$r['id']}', {$type}, '{$condition_counter}' );\"
					>
						{$r['name']} - Transferencias Asignadas : {$r['assigned_transfer']}
				</div>";
		}
		return $resp;
	}
//( $transfer_id, $employee, $user_id, $parts_limit, $total_parts, $link )
	function insertUserTransfer( $transfer, $employee, $user_id, $parts_limit, $total_parts, $link ){
	//inserta cabecera del surtimiento de transferencia
		$sql  = "INSERT INTO ec_transferencias_surtimiento (id_transferencia_surtimiento, id_transferencia, id_encargado_bodega, 
			id_usuario_asignado, total_partidas, id_status_asignacion )
			VALUES(null, '{$transfer}', '{$user_id}', '{$employee}', 0, 1 )";
		$stm = $link->query( $sql ) or die( "Error al insertar la cabecera del surtimiento de transferencia : " . $link->error );
		$header_id = $link->insert_id;
		//die( 'res: ' . $total_parts .  '/' .  $parts_limit );
		if( is_float( $total_parts / $parts_limit ) ){
			$parts_limit += 1;
			//$parts_limit = ceil( $total_parts / $parts_limit );
		}
	//consulta el numero de transferencias que faltan por surtir
		$sql = "SELECT
					tp.id_transferencia_producto,
					tp.cantidad,
					tp.id_proveedor_producto
			FROM ec_transferencia_productos tp
			LEFT JOIN ec_transferencias_surtimiento_detalle tsd
			ON tsd.id_transferencia_producto = tp.id_transferencia_producto
			LEFT JOIN ec_proveedor_producto pp
			ON tp.id_proveedor_producto = pp.id_proveedor_producto
			LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
			ON ppua.id_proveedor_producto = pp.id_proveedor_producto
			AND ppua.es_principal = '1'		
			AND ppua.habilitado = '1'
			WHERE tp.id_transferencia = '{$transfer}'
			AND tsd.id_transferencia_producto IS NULL
            GROUP BY tp.id_transferencia_producto
            ORDER BY ppua.letra_ubicacion_desde, 
            ppua.numero_ubicacion_desde, 
            ppua.pasillo_desde, 
            ppua.altura_desde ASC
            LIMIT {$parts_limit}";
		$stm = $link->query( $sql ) or die( "Error al consultar partidas pendientes de asignar!" . $sql .  $link->error );
		
		if( $stm->num_rows <= 0 ){
		//	return 'No hay detalles de transferencias por asignar!';
		}

		while ( $r = $stm->fetch_assoc() ) {
			$sql = "INSERT INTO ec_transferencias_surtimiento_detalle (id_transferencia_surtimiento, 
				id_transferencia_producto, id_status_surtimiento ) VALUES (
				'{$header_id}', '{$r['id_transferencia_producto']}', 1 )";
			$exc = $link->query( $sql ) or die( "Error al insertar el detalle de surtimiento : " . $link->error );
		}
	//recupera datos para la tabla de personas asignadas a la transferencia
		$sql = "SELECT 
					ts.id_transferencia_surtimiento AS id,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
					IF( ts.id_transferencia_surtimiento IS NULL, 0, COUNT( ts.id_transferencia_surtimiento ) ) AS assigned_transfer,
					u.id_usuario AS user_id
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tsd.id_transferencia_surtimiento = ts.id_transferencia_surtimiento
				LEFT JOIN sys_users u ON u.id_usuario = ts.id_usuario_asignado
				WHERE ts.id_transferencia_surtimiento = '{$header_id}'
				AND ts.id_usuario_asignado = '{$employee}'";
		$stm = $link->query( $sql ) or die( "Error al consultar datos de surtimiento : " . $link->error );
		$r = $stm->fetch_assoc();
	//actualiza el numero de partidas asignadas
		$sql = "UPDATE ec_transferencias_surtimiento SET total_partidas = '{$r['assigned_transfer']}'
				WHERE id_transferencia_surtimiento = '{$header_id}'";
		$stm = $link->query( $sql ) or die( "Error al actualizar numero de partidas : " . $link->error );
		return json_encode( $r );
	}

	function getAssignedUsers( $transfer_id, $excluyed_user = null, $excluyed_assignation = null, $link ){
		$condition = "";
		if( $excluyed_user != null ){
			$condition .= "AND u.id_usuario NOT IN( $excluyed_user )";
		}
		/*if( $excluyed_user != null ){

		}*/
		$sql = "SELECT 
					ts.id_transferencia_surtimiento AS id,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
					/*IF( ts.id_transferencia_surtimiento IS NULL, 0, COUNT( ts.id_transferencia_surtimiento ) ) AS assigned_transfer,*/
					SUM( IF( tsd.id_status_surtimiento IN( 1, 2), 1, 0 ) ) AS assigned_transfer,
					SUM( IF( tsd.id_status_surtimiento >= 3 , 1, 0 ) ) AS supplied_assigned_transfer,
					u.id_usuario AS user_id,
					IF( ts.id_status_asignacion = 5, 'canceled', '' ) AS assignment_status
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tsd.id_transferencia_surtimiento = ts.id_transferencia_surtimiento
				LEFT JOIN sys_users u ON u.id_usuario = ts.id_usuario_asignado
				WHERE ts.id_transferencia = '{$transfer_id}'
				{$condition}
				GROUP BY u.id_usuario";
		$stm = $link->query( $sql ) or die( "Error al consultar usuarios asignados : " . $link->error );
		$resp = array();
		while( $r = $stm->fetch_assoc() ){
			array_push( $resp, $r );
		}
		//die( $sql );
		return json_encode( $resp );
	}

	function getUsersCombo( $current_users, $current_user, $counter, $val, $name, $link ){
		$resp = '<select id="user_tmp" class="form-control" onchange="reassignUserToUser( this, ' . $counter . ' );"';
		$resp .= ' onblur="desedit_tmp_usr( ' . $counter . ' );" tabindex="' . $counter . '"';
		$resp .= '>';
		$resp .= '<option value="' . $val . '">' . $name . '</option>';
		$sql= "SELECT
					u.id_usuario AS id,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
					IF( ts.id_transferencia_surtimiento IS NULL, 0, COUNT( ts.id_transferencia_surtimiento ) ) AS assigned_transfer
				FROM sys_users u
				LEFT JOIN ec_transferencias_surtimiento ts ON u.id_usuario = ts.id_usuario_asignado
				LEFT JOIN ec_registro_nomina rn ON u.id_usuario = rn.id_empleado
				WHERE u.id_sucursal = 1
				AND rn.fecha = DATE_FORMAT( NOW(), '%Y-%m-%d')
				AND rn.hora_salida = '00:00:00'";
		$sql .= ( $current_users != null && $current_users != '' && $current_users != 'undefined' ?  " AND u.id_usuario NOT IN( {$current_users} )" : "" );
		$sql .= " GROUP BY u.id_usuario";
		$stm = $link->query( $sql ) or die( "Error al consultar usuarios logueados : " . $link->error );
		while ( $r = $stm->fetch_assoc() ) {
			$resp .= '<option value="' . $r['id'] . '">' . $r['name'] . ' ( ' . $r['assigned_transfer'] . ' ) </option>';
		}
		$resp .= '</select>';
		/*$resp .= '<button class="btn btn-warning form-control" onchange="reassignUserToUser( this, ' . $counter . ' );">';
			$resp .= '<i class="icon-spin3">Cambiar Usuario</i>';
		$resp .= '</button>';*/
		return $resp;
	}

	function changeUserToUser( $user, $old_user, $new_user, $transfer_id, $link ){
		$resp = '';
		$sql = "SELECT 
					ts.id_transferencia_surtimiento AS supply_transfer_id,
					COUNT( tsd.id_surtimiento_detalle ) AS supply_detail_counter
			FROM ec_transferencias_surtimiento ts
			LEFT JOIN ec_transferencias_surtimiento_detalle tsd
			ON tsd.id_transferencia_surtimiento = ts.id_transferencia_surtimiento
			WHERE ts.id_transferencia = '{$transfer_id}'
			AND ts.id_usuario_asignado = '{$old_user}'
			AND tsd.id_status_surtimiento = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar el id del registro de surtimiento a cambiar : " . $link->error );
		$row = $stm->fetch_assoc();
		$supply_transfer_id = $row['supply_transfer_id'];
		$supply_detail_counter = $row['supply_detail_counter'];

		$sql = "UPDATE ec_transferencias_surtimiento 
					SET id_status_asignacion = 5 
				WHERE  id_transferencia_surtimiento = '{$supply_transfer_id}'";
		$stm = $link->query( $sql ) or die( "Error al actualizar el antiguo registro de surtimiento : " . $link->error );
	
	//inserta cabecera del surtimiento de transferencia
		$sql  = "INSERT INTO ec_transferencias_surtimiento (id_transferencia_surtimiento, id_transferencia, id_encargado_bodega, 
			id_usuario_asignado, total_partidas, id_status_asignacion )
			VALUES(null, '{$transfer_id}', '{$user}', '{$new_user}', {$supply_detail_counter}, 1 )";
		$stm = $link->query( $sql ) or die( "Error al insertar el nuevo registro de surtimiento : " . $link->error );
		$header_id = $link->insert_id;
	//asigna los nuevos detalles
		$sql = "UPDATE ec_transferencias_surtimiento_detalle 
					SET id_transferencia_surtimiento = '{$header_id}'
				WHERE id_transferencia_surtimiento = '{$supply_transfer_id}'
				AND id_status_surtimiento = 1";
		$stm = $link->query( $sql ) or die( "Error al actualizar el detalle con el nuevo registro de surtimiento : " . $link->error );
		return 'ok|Las partidas del usuario fueron reasignadas exitosamente!';
	}
	
	function reassignTransfer( $transfer_id, $users_array, $user, $link ){
		//valido-invalido ~ id_transferencia_surt ~ id_usuario
		$users = explode( '|' , $users_array );
		$valids = array();
		$valids_txt = '';
		$valids_counter = 0;
		$invalids = '';
		$all_rows = '';
		foreach ($users as $key => $user) {
			$usr = explode( '~', $user );
			if( $usr[0] == 'is_valid' ){
				array_push( $valids, $user );
				$valids_counter ++;
				$valids_txt .= ( $valids_txt != '' ? ',' : '' ) . $usr[1];
			}else if( $usr[0] == 'is_invalid' ){
				$invalids .= ( $invalids != '' ? ',' : '' ) . $usr[1];
			}
			$all_rows .= ( $all_rows != '' ? ',' : '' ) . $usr[1];
		}
	//invalida usuarios
		if( $invalids != '' ){
			$sql = "UPDATE ec_transferencias_surtimiento 
						SET id_status_asignacion = 5
					WHERE id_transferencia_surtimiento IN( {$invalids} )
					AND id_status_asignacion NOT IN( 4, 5 )";
			$stm = $link->query( $sql ) or die( "Error al actualizar asignaciones invalidadas : " . $link->error );
		}

	/*elimina los registros que se le habian asignado al usuario
		$sql = "DELETE FROM ec_transferencias_surtimiento_detalle 
				WHERE id_transferencia_surtimiento IN( {$invalids} )
				AND id_status_surtimiento IN( 1, 2 )";
		$stm = $link->query( $sql ) or die( "Error al eliminar detalle de asignaciones invalidadas : " . $link->error );
	*/
	//elimina detalles que no se han surtido
		$sql = "DELETE FROM ec_transferencias_surtimiento_detalle 
				WHERE id_transferencia_surtimiento IN( {$all_rows} )
				AND id_status_surtimiento IN( 1 )";
		$stm = $link->query( $sql ) or die( "Error al eliminar detalle de asignaciones invalidadas : " . $link->error );

	//consulta el numero de transferencias que faltan por surtir
		$sql = "SELECT
					tp.id_transferencia_producto,
					tp.cantidad,
					tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tsd.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				AND ppua.es_principal = '1'
				AND ppua.habilitado = '1'
				WHERE tp.id_transferencia = '{$transfer_id}'
				AND tsd.id_transferencia_producto IS NULL
	            GROUP BY tp.id_transferencia_producto";
        $stm_1 = $link->query( $sql ) or die( "Error al consultar detalles de transferencias pendientes de asignar : " . $link->error );
	//inserta nuevos usuarios
		$parts_per_user = ( $stm_1->num_rows / $valids_counter );
	//
		if( is_float( $parts_per_user )  ){
			$parts_per_user = round( $parts_per_user ) + 1;
			//$parts_per_user = ceil( $parts_per_user );
		}
		$counter = 0;
		foreach ($valids as $key => $user ) {
			$usr = explode( '~', $user );
			$header_id = '';
			if( $usr[1] != '' && $usr[1] != null && $usr[1] != 'undefined' ){
				$header_id = $usr[1];
			}else{
				die( "Usuario no asignado!" );
			}

			$sql = "SELECT
					tp.id_transferencia_producto,
					tp.cantidad,
					tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tsd.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				AND ppua.es_principal = '1'
				AND ppua.habilitado = '1'
				WHERE tp.id_transferencia = '{$transfer_id}'
				AND tsd.id_transferencia_producto IS NULL
	            GROUP BY tp.id_transferencia_producto
	            ORDER BY ppua.letra_ubicacion_desde, 
	            ppua.numero_ubicacion_desde, 
	            ppua.pasillo_desde, 
	            ppua.altura_desde ASC
	            LIMIT {$parts_per_user}";
	       
	       	$stm = $link->query( $sql ) or die( "Error al consultar el detalle para reasignar : " . $link->error );
			//$parts_per_user -= $stm->num_rows;
			while ( $r = $stm->fetch_assoc() ) {
				$sql = "INSERT INTO ec_transferencias_surtimiento_detalle ( id_transferencia_surtimiento, 
					id_transferencia_producto, id_status_surtimiento ) VALUES (
					'{$header_id}', '{$r['id_transferencia_producto']}', 1 )";
				$exc = $link->query( $sql ) or die( "Error al insertar el detalle de surtimiento : " . $link->error );
			
			}
		}
		$condition = "";
		if( $invalids != '' ){
			$condition = "AND ts.id_transferencia_surtimiento NOT IN( {$invalids} )";
		}
		$sql = "SELECT 
					ts.id_transferencia_surtimiento,
					COUNT( tsd.id_surtimiento_detalle ) 
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON ts.id_transferencia_surtimiento = tsd.id_transferencia_surtimiento
				WHERE ts.id_transferencia = {$transfer_id}
				{$condition}
				GROUP BY ts.id_transferencia_surtimiento";
		$getCounts = $link->query( $sql ) or die( "Error al consultar los contadores : {$link->error}" );
		while( $count = $getCounts->fetch_row() ){
		//actualiza cabeceras de surtimiento
			$sql = "UPDATE ec_transferencias_surtimiento
						SET id_status_asignacion = 1,/*IF( id_status_asignacion = 5, 5, 1 )*/
						total_partidas = ({$count[1]}) /*impelemntacion Oscar 2023 para actualizar el numero de partidas*/ 
					WHERE id_transferencia_surtimiento = {$count[0]}";
			$link->query( $sql ) or die( "Error al actualizar cabeceras de surtimiento : " . $link->error );
		}
		return 'Transferencia reasignada exitosamente!';
	}

	function playSupply( $transfer_id, $link ){
//actualiza las tranferencias como pausadas durante la asignación
		$sql = "UPDATE ec_transferencias_surtimiento 
					SET id_status_asignacion = IF( id_status_asignacion = 5, 5, 1 ) 
				WHERE id_transferencia = '{$transfer_id}'
				AND id_status_asignacion < 4";
		$stm = $link->query( $sql ) or die( "Error al reanudar surtimientos de transferencia : " . $link->error );
		echo 'ok';
	}

	function  getDetail( $transfer_id, $user_assignment_id = null, $link ){
		$resp = '<p style="position:fixed; top : 30px; right : 8%;" class="text-right"><button type="button" class="btn btn-light" onclick="close_emergent( 1 );"><b>X</b></button></p>';
		$resp .= '<h2>Detalle del surtimiento</h2>';
		$resp .= '<table class="table table-bordered table-striped">';
		$resp .= '<thead class="header_fixed_0"><tr>';
			$resp .= '<th>Producto</th>';
			$resp .= '<th>Modelo</th>';
			$resp .= '<th>Cajas</th>';
			$resp .= '<th>Paquetes</th>';
			$resp .= '<th>Piezas</th>';
			$resp .= '<th>Total Surt</th>';
			$resp .= '<th>Usuario</th>';
			$resp .= '<th>Fecha / hora</th>';
			$resp .= '<th>Ubicación</th>';
		$resp .= '</tr></thead>';
		$sql = "SELECT
					tsd.id_surtimiento_detalle AS id,
					CONCAT( '(<b>', p.orden_lista, '</b>) ', p.nombre ) AS name,
					pp.clave_proveedor AS model,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS user_name,
					IF( tsu.cantidad_cajas_surtidas IS NULL, 0 , tsu.cantidad_cajas_surtidas ) AS boxes,
					IF( tsu.cantidad_paquetes_surtidos IS NULL, 0 , tsu.cantidad_paquetes_surtidos ) AS packs,
					IF( tsu.cantidad_piezas_surtidas IS NULL, 0 , tsu.cantidad_piezas_surtidas ) AS pieces,
					( SUM( tsu.total_piezas_surtidas ) ) AS total_pieces,
					tsu.fecha_alta AS date_time,
					IF( ppua.id_ubicacion_matriz IS NOT NULL,  CONCAT( ppua.letra_ubicacion_desde, 
							ppua.numero_ubicacion_desde,
							'p: ', ppua.pasillo_desde,
							' n: ', ppua.altura_desde,
							' - ', 
							ppua.letra_ubicacion_hasta, 
							ppua.numero_ubicacion_hasta,
							' p: ', ppua.pasillo_hasta,
							' n: ', ppua.altura_hasta
					), '') AS location
				FROM ec_transferencias_surtimiento_detalle tsd
				LEFT JOIN ec_transferencias_surtimiento ts
				ON tsd.id_transferencia_surtimiento = ts.id_transferencia_surtimiento
				LEFT JOIN ec_transferencia_productos tp 
				ON tp.id_transferencia_producto = tsd.id_transferencia_producto
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				AND ppua.es_principal = '1'
				AND ppua.habilitado = '1'
				LEFT JOIN ec_transferencias_surtimiento_usuarios tsu
				ON tsu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN sys_users u 
				ON u.id_usuario = ts.id_usuario_asignado
				WHERE tp.id_transferencia = '{$transfer_id}'";
		if( $user_assignment_id != null ){
			$sql .= " AND ts.id_transferencia_surtimiento IN( '{$user_assignment_id}' )";
		}
		$stm = $link->query( $sql . " GROUP BY tsd.id_surtimiento_detalle ORDER BY tsd.id_surtimiento_detalle ASC" ) or die( "Error al consultar detalles de transferencias pednientes de asignar : {$sql} " . $link->error );
		$resp .= '<tbody>';
//die( $sql );
		while( $row = $stm->fetch_assoc() ){
			$style = '';
			if( $row['total_pieces'] != null ){
				$style = ' style="background-color : green;"';
			}
			$resp .= "<tr {$style} >";
				$resp .= '<td class="no_visible">' . $row['id'] . '</td>';
				$resp .= '<td>' . $row['name'] . '</td>';
				$resp .= '<td>' . $row['model'] . '</td>';
				$resp .= '<td>' . $row['boxes'] . '</td>';
				$resp .= '<td>' . $row['packs'] . '</td>';
				$resp .= '<td>' . $row['pieces'] . '</td>';
				$resp .= '<td>' . $row['total_pieces'] . '</td>';//( $row['total_pieces'] == null ? 0 : $row['total_pieces'] )
				$resp .= '<td>' . $row['user_name'] . '</td>';
				$resp .= '<td>' . $row['date_time'] . '</td>';
				$resp .= '<td>' . $row['location'] . '</td>';
			$resp .= '</tr>';
		}
		$resp .= '</tbody>';
		$resp .= '</table>';
		$resp .= '<div class="row">';
		$resp .= '<div class="col-4"></div>';
		$resp .= '<div class="col-4">';
			$resp .= '<button class="btn btn-success form-control" onclick="close_subemergent();">';
				$resp .= '<i class="icon-ok-circle">Aceptar</i>';
			$resp .= '</button>';
		$resp .= '</div>';
		$resp .= '</div>';
		return $resp;
	}

	function getReasignationDetail( $transfer_id, $user_assignment_id, $type, $link ){
		$resp = "";
		$hidde = "";
		if( $type == 'reasignation' ){
			$hidde = "style=\"display : none;\"";
		}
		$sql = "SELECT
					tsd.id_surtimiento_detalle AS id,
					CONCAT( '(<b>', p.orden_lista, '</b>) ', p.nombre ) AS name,
					pp.clave_proveedor AS model,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS user_name,
					IF( tsu.cantidad_cajas_surtidas IS NULL, 0 , tsu.cantidad_cajas_surtidas ) AS boxes,
					IF( tsu.cantidad_paquetes_surtidos IS NULL, 0 , tsu.cantidad_paquetes_surtidos ) AS packs,
					IF( tsu.cantidad_piezas_surtidas IS NULL, 0 , tsu.cantidad_piezas_surtidas ) AS pieces,
					( SUM( tsu.total_piezas_surtidas ) ) AS total_pieces,
					tsu.fecha_alta AS date_time,
					IF( ppua.id_ubicacion_matriz IS NOT NULL,  CONCAT( ppua.letra_ubicacion_desde, 
							ppua.numero_ubicacion_desde,
							'p: ', ppua.pasillo_desde,
							' n: ', ppua.altura_desde,
							' - ', 
							ppua.letra_ubicacion_hasta, 
							ppua.numero_ubicacion_hasta,
							' p: ', ppua.pasillo_hasta,
							' n: ', ppua.altura_hasta
					), '') AS location
				FROM ec_transferencias_surtimiento_detalle tsd
				LEFT JOIN ec_transferencias_surtimiento ts
				ON tsd.id_transferencia_surtimiento = ts.id_transferencia_surtimiento
				LEFT JOIN ec_transferencia_productos tp 
				ON tp.id_transferencia_producto = tsd.id_transferencia_producto
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				AND ppua.es_principal = '1'
				AND ppua.habilitado = '1'
				LEFT JOIN ec_transferencias_surtimiento_usuarios tsu
				ON tsu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN sys_users u 
				ON u.id_usuario = ts.id_usuario_asignado
				WHERE tp.id_transferencia = '{$transfer_id}'
				AND ts.id_transferencia_surtimiento IN( '{$user_assignment_id}' )
				AND tsd.id_status_surtimiento IN( 1,2,3 )
				GROUP BY tsd.id_surtimiento_detalle 
				ORDER BY tsd.id_surtimiento_detalle ASC";
		$stm = $link->query( $sql ) or die( "Error al consultar detalles de transferencias que se tienen que reasignar : {$sql} " . $link->error );
		//$resp .= '<tbody>';
//die( $sql );
		while( $row = $stm->fetch_assoc() ){
			$style = '';
			if( $row['total_pieces'] != null ){
				$style = ' style="background-color : green;"';
			}
			$row['pieces'] = str_replace( ".0000", "", $row['pieces'] );
			$resp .= "<tr {$style} >";
				$resp .= '<td class="no_visible">' . $row['id'] . '</td>';
				$resp .= '<td>' . $row['name'] . '</td>';
				$resp .= '<td>' . $row['model'] . '</td>';
				$resp .= '<td>' . $row['boxes'] . '</td>';
				$resp .= '<td>' . $row['packs'] . '</td>';
				$resp .= '<td>' . $row['pieces'] . '</td>';
				$resp .= "<td {$hidde}>{$row['total_pieces']}</td>";//( $row['total_pieces'] == null ? 0 : $row['total_pieces'] )
				$resp .= "<td {$hidde}>{$row['user_name']}</td>";
				$resp .= "<td {$hidde}>{$row['date_time']}</td>";
				$resp .= '<td>' . $row['location'] . '</td>';
			$resp .= '</tr>';
		}
		return $resp;
	}

	function getTransferHeader( $transfer_id, $link ){
		$resp = '';
		$sql = "SELECT
				t.id_transferencia AS transfer_id,
				t.folio,
				so.nombre AS origen,
				sd.nombre AS destino,
				COUNT( tp.id_transferencia ) AS parts,
				COUNT( tsd.id_surtimiento_detalle ) AS parts_assigned
			FROM ec_transferencias t
			LEFT JOIN ec_transferencia_productos tp 
			ON tp.id_transferencia = t.id_transferencia
			LEFT JOIN sys_sucursales so ON so.id_sucursal = t.id_sucursal_origen
			LEFT JOIN sys_sucursales sd ON sd.id_sucursal = t.id_sucursal_destino
			LEFT JOIN ec_transferencias_surtimiento_detalle tsd ON tsd.id_transferencia_producto = tp.id_transferencia_producto
			WHERE t.id_transferencia = '{$transfer_id}'
			GROUP BY t.id_transferencia";
		$stm = $link->query( $sql ) or die( "Error al consultar cabecera de transferencia : " . $link->error );
		$r = $stm->fetch_assoc();
		$pend = $r['parts'] - $r['parts_assigned'];
		return "ok|{$r['parts_assigned']}|{$pend}";
	}
	function transferOutput( $transfer_id, $link ){
		$link->autocommit( false );
		/*$sql = "UPDATE ec_transferencias SET id_estado = '8' WHERE id_transferencia IN( {$transfer_id} )";
		$stm = $link->query( $sql ) or die( "Error al actualizar la transferencia a Salida : {$link->error}" );*/
	//busca el bloque de ytransferencia
		$sql = "SELECT 
					btvd.id_bloque_transferencia_validacion AS validation_block_id
				FROM ec_bloques_transferencias_validacion_detalle btvd
				WHERE btvd.id_transferencia = {$transfer_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar el bloque de transferencias : {$link->error}" );
		$row = $stm->fetch_assoc();
		$validation_block_id = $row['validation_block_id'];
	//busca folios de transferencias
		$sql = "SELECT 
					t.folio
				FROM ec_bloques_transferencias_validacion_detalle btvd
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar transferencias del bloque : {$link->error}" );
		//$row = $stm->fetch_assoc();
		$transfers = "<ul>";
		while( $row = $stm->fetch_assoc() ){
			$transfers .= "<li class=\"icon-right-big\" style=\"list-style:none; font-size : 150% !important; padding : 2%;\">{$row['folio']}</li>";
		}
		$transfer .= "</ul>";
	//actualiza transferencias del bloque a Salida
		$sql = "UPDATE ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON t.id_transferencia = btvd.id_transferencia
				SET t.id_estado = '8' 
				WHERE btvd.id_bloque_transferencia_validacion IN( {$validation_block_id} )";
		$stm = $link->query( $sql ) or die( "Error al actualizar a Salida las transferencias del blqoques de validación : {$link->error}" );
		
		$link->autocommit( true );
		
		return "ok|<div class=\"text-center\" style=\"box-shadow : 1px 1px 15px rgba( 0, 0, 0, .5); background-color : white;\">
					<br><h5 style=\"font-size : 150% !important;\">Las transferencias del bloque <b style=\"color : green; font-size : 130% !important;\">'{$validation_block_id}'</b> fueron puestas en SALIDA exitosamente :</h5><br>
					{$transfers}
					<br>
					<div class=\"row\">
						<div class=\"col-4\"></div>
						<div class=\"col-4\">
							<button class=\"btn btn-success form-control\" onclick=\"location.reload();\">
								<i class=\"icon-ok-circle\" style=\"font-size : 150% !important;\">Aceptar</i>
							</button>
						</div>
					</div>
					<br><br>
				</div>";
	}
?>





