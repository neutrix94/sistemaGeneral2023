<?php
/*Version con insercion de movimientos por Procedure (2024-08-05)*/
	include( '../../../../../config.inc.php' );
	include( '../../../../../conect.php' );
	include( '../../../../../conexionMysqli.php' );
	$action = $_GET['fl'];

	switch ( $action ) {
		case 'validateBarcode':
			if( !isset( $_GET['manager_permission'] ) ){
				 $_GET['manager_permission'] = null;
			}
			if( !isset( $_GET['pieces_quantity'] ) ){
				 $_GET['pieces_quantity'] = null;
			}
			if( !isset( $_GET['permission_box'] ) ){
				 $_GET['permission_box'] = null;
			}
			if( !isset( $_GET['unique_code'] ) ){
				 $_GET['unique_code'] = null;
			}
			if( $_GET['barcode'] == '' ){
				$resp = "message_info|<h5 class=\"red\">El código de barras no puede ir vacío</h5>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-2\"></div>";
					$resp .= "<div class=\"col-8\">";
						$resp .= "<button class=\"btn btn-info form-control\" 
										onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
										<i class=\"icon-ok-circle\">Aceptar</i>
								</button>";
					$resp .= "</div>";
				$resp .= "</div>";
				return $resp;
			}
			echo validateBarcode( $_GET['barcode'], $_GET['transfers'], $user_id, 
				$_GET['manager_permission'], $_GET['pieces_quantity'], $_GET['permission_box'], $_GET['unique_code'], 
				$_GET['block_id'], $_GET['validation_token'], $link );
		break;
		case 'insertNewProductValidation' : 
		/*die( "{$_GET['block_id']}, {$_GET['transfers']}, {$_GET['p_id']}, {$_GET['p_p_id']}, {$_GET['box']},
			 {$_GET['pack']}, {$_GET['piece']}, {$_GET['barcode']}, {$_GET['unique_code']}, {$user_id}" );*/
			echo insertNewProductValidation( $_GET['block_id'], $_GET['transfers'], $_GET['p_id'], $_GET['p_p_id'], $_GET['box'],
			 $_GET['pack'], $_GET['piece'], $_GET['barcode'], $_GET['unique_code'], $user_id, $link );
		break;
		case 'loadLastValidations' :
		//die( $_GET['transfers'] );
			echo loadLastValidations( $_GET['transfers'], $user_id, $link );
		break;

		case 'getResumeHeader' : 
			echo getResumeHeader( $_GET['transfers'], $_GET['type'], $link );
		break;

		case 'saveValidation' :
			echo saveValidation( $_GET['transfers'], $_GET['validation_token'], $link );
		break;

		case 'validateManagerPassword' : 
			echo validateManagerPassword( $_GET['pass'], $link );
		break;

		case 'inventoryAdjustment' :
			echo inventoryAdjustment( $_GET['addition'], $_GET['substraction'], 
				$_GET['data_ok'], $user_id, $link );
		break; 

		case 'getOptionsByProductId' :
			echo getOptionsByProductId( $_GET['product_id'], $link );
		break;

		case 'seekRecivedProducts' : 
			echo seekRecivedProducts( $_GET['txt'], $_GET['transfers'], $link );
		break;

		case 'loadProductValidationDetail' :
			echo loadProductValidationDetail( $_GET['product_id'], $_GET['transfers'], $link );
		break; 

		case 'makeTransfersGroup' :
			echo makeTransfersGroup( $_GET['transfers'], $user_id, $link );
		break;

		case 'getPreviousRemoveTransferToValidation' : 
			if( !isset( $_GET['reset_unic_transfer'] ) ) {
				$_GET['reset_unic_transfer'] = null;
			}
			echo getPreviousRemoveTransferToValidation( $_GET['transfer_id'], $_GET['reset_unic_transfer'], $link );
		break;

		case 'removeTransferBlockDetail' :
			echo removeTransferBlockDetail( $_GET['transfer_id'], $_GET['transfer_product_id'], $link );
		break;

		case 'removeTransferBlock' :
			echo removeTransferBlock( $_GET['transfer_id'], $link );
		break;

		case 'getTransfersListValidation' :
			$filters = array( 'store_orig'=>$_GET['store_orig'],'store_dest'=>$_GET['store_dest'] );
			$orders = array( 'folio'=>$_GET['folio'],'status'=>$_GET['status'], 'block_id'=>$_GET['block_id']  );
			//var_dump($orders);
			echo getTransfersListValidation( $link , $sucursal_id, $filters, $orders );
		break;

		case 'getMessageToAddTransfer' :
			echo getMessageToAddTransfer( $_GET['transfers'], $_GET['transfer_to_add'], $_GET['validation_block_id'], 
				$_GET['validation_token'], $link );
		break;

		case 'addTransferBlock' :
			echo addTransferBlock( $_GET['transfer'], $_GET['block_id'], $link );
		break;

		case 'getPermissionToMAkeBlocks' :
			echo getPermissionToMAkeBlocks( $user_id, $link );
		break;

		case 'showHiddeValidatePendingForm' :
		//echo 'here';
			echo showHiddeValidatePendingForm( $_GET['transfer_product_id'], $link );
		break;

		case 'skipPendingValidation' :
			echo skipPendingValidation( $_GET['transfer_product_id'], $_GET['selected_case'], $link );
		break;

/*implementacion Oscar 2023 para validar que este completamente surtida la transferencia*/
		case 'validateTransferStatus' :
			echo validateTransferStatus( $_GET['transfers'], $link );
		break;
/*fin de cambio Oscar 2023*/
		
		case 'create_validation_token' :
			echo create_validation_token( $user_id, $_GET['validation_block_id'], $_GET['make_principal'], $link );
		break;

		case 'getUpdateValidationBlock' : 
			echo getUpdateValidationBlock( $_GET['validation_block_id'], $_GET['token'], $user_id, $link );
		break;

		case 'removeUnicToken' : 
			echo removeUnicToken( $_GET['token'], $link );
		break;

		case 'validate_devices_sessions' :
			echo validate_devices_sessions( $_GET['current_block'], $_GET['validation_token'], $link );
		break;

		case 'close_validation_session' : 
			echo close_validation_session( $_GET['validation_token'], $_GET['validation_block_id'], $link );
		break;

		case 'close_device_validation_session': 
			echo close_device_validation_session( $_GET['validation_token'], $_GET['validation_block_id'], $link );
		break;

		case 'reassign_principal_session_validation' :
			echo reassign_principal_session_validation( $_GET['validation_session_id'], $_GET['validation_block_id'], $link );
		break;

		case 'validate_permission_block' :
			$validation_token = ( isset( $_GET['validation_token'] ) ? $_GET['validation_token'] : '' );
			echo validate_permission_block( $validation_block_id, $validation_token, true, $link );
		break;

		case 'check_user_permission_to_edit_block' : 
			$validation_block_id = ( isset( $_GET['validation_block_id'] ) ? $_GET['validation_block_id'] : '' );
			$validation_token = ( isset( $_GET['validation_token'] ) ? $_GET['validation_token'] : '' );
			echo check_user_permission_to_edit_block( $validation_block_id, $validation_token, $user_id,
			$_GET['current_transfers'], $_GET['folio'], $link );
		break;

		case 'validateTokenIsValid' :
			echo validateTokenIsValid( $_GET['validation_token'], $user_id, $link );
		break;

		case 'cancel_validation_block_lock':
				echo cancel_validation_block_lock( $_GET['validation_block'], $link );
		break;

		default:
		//	die( "Permission Denied!" );
		break;
	}

	function cancel_validation_block_lock( $validation_block_id, $link ){
	//verifica si tiene un bloque de recepcion enlazado
		$sql = "SELECT
					btrd.id_bloque_transferencia_recepcion AS reception_block_id
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				WHERE btvd.id_bloque_transferencia_validacion = '{$validation_block_id}'";
		$stm = $link->query( $sql ) or die( "Error al consultar si la validacion esta enlazada a una recepcion : {$link->error}" );
		$link->autocommit( false );
		if( $stm->num_rows > 0 ){
			while( $row = $stm->fetch_assoc() ){
				$sql = "UPDATE ec_bloques_transferencias_recepcion 
							SET bloqueado = '0' 
						WHERE id_bloque_transferencia_recepcion = '{$row['reception_block_id']}'";
				$update = $link->query( $sql ) or die( "Error al desbloquear bloque de validación : {$link->error}" );
			}
		}
		$sql = "UPDATE ec_bloques_transferencias_validacion
					SET bloqueado = '0'
				WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
		$stm = $link->query( $sql ) or die( "Error al desbloquear el bloque de validación : {$link->error}" );
		$link->autocommit( true );		
		return 'ok';
	}

	function validateTokenIsValid( $validation_token, $user_id, $link ){
		$sql = "SELECT 
					CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
				FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
				LEFT JOIN sys_users u
				ON sdvt.id_usuario = u.id_usuario
				WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
				AND sdvt.id_usuario = '{$user_id}'";
		$stm = $link->query( $sql ) or die( "error|Error al validar el token : {$link->error}" );
		if( $stm->num_rows == 0 ){
		//verifica si es la sesion princial del bloque
			$sql = "SELECT 
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
					FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
					LEFT JOIN ec_bloques_transferencias_validacion btv
					ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
					LEFT JOIN sys_users u
					ON u.id_usuario = sdvt.id_usuario
					WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
					AND btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion";
			$stm = $link->query( $sql ) or die( "error|Error al validar si el token corresponde a la sesion principal : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
			//si es la sesion principal				
				return "message_error|<div class=\"row\">
				<h4>USUARIO INVÁLIDO PARA ESTE DISPOSITIVO!</h4>
				<p>Esta sesión de validación no coincide con el usuario que esta logueado actualmente en este dispositivo 
				y la sesión es la principal de la Validación, pide al usuario <b style=\"color : green;\">{$row['user_name']}</b> 
				que incie sesion en este dispositivo, ya que, de lo contrario esta Validación no podrá ser finalizada</p>
				<p align=\"center\">Da click en el botón de Aceptar para cambiar la sesión de usuario</p>
				<div class=\"col-4\"></div>
				<div class=\"col-4 text center\">
					<button
						type=\"button\"
						class=\"btn btn-success form-control\"
						onclick=\"finish_login_session();\"
					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
				</div>";
			}
		//si es una sesion normal
			//finaliza la sesion
			$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
						SET finalizada = '1' 
					WHERE token_unico_dispositivo = '{$validation_token}'";
			$stm = $link->query( $sql ) or die( "error|Error al finalizar el token del dispositivo : {$link->error}" );
			return "message_error|<div class=\"row\">
				<h4>Esta sesión de validación no coincide con el usuario que esta logueado actualmente en este dispositivo o la sesión ya venció</h4>
				<p align=\"center\">Da click en el botón de Aceptar para recargar esta pantalla</p>
				<div class=\"col-4\"></div>
				<div class=\"col-4 text center\">
					<button
						type=\"button\"
						class=\"btn btn-success form-control\"
						onclick=\"remove_validation_token();\"
					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
			</div>";
		}
		$sql = "SELECT 
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
				FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
				LEFT JOIN sys_users u
				ON u.id_usuario = sdvt.id_usuario
				WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
				AND btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion";
		$stm = $link->query( $sql ) or die( "error|Error al validar si el token corresponde a la sesion principal : {$link->error}" );
			
		return "ok|{$stm->num_rows}";
	}

	function check_user_permission_to_edit_block( $validation_block_id, $validation_token, $user, 
		$current_transfers, $folio, $link ){
//if( $validation_block_id == '' ){
	//verifica si el usuario tiene el permiso para crear bloques
		$sql = "SELECT
					IF( sp.ver = 1 OR sp.modificar = 1 OR sp.eliminar = 1 OR sp.nuevo = 1
					OR sp.imprimir = 1 OR sp.generar = 1, 1, 0 ) AS edit_permission,
					CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
				FROM sys_users u
				LEFT JOIN sys_users_perfiles up
				ON up.id_perfil = u.tipo_perfil
				LEFT JOIN sys_permisos sp
				ON sp.id_perfil = up.id_perfil
				WHERE u.id_usuario = {$user}
				AND sp.id_menu = 241";
		$stm = $link->query( $sql ) or die( "Error al consultar si el usuario tiene habilitado el permiso para editar bloques de validacion : {$link->error}" );
		$row = $stm->fetch_assoc();
		if( $row['edit_permission'] == 0 ){
			return "<div class=\"row\">
				<div class=\"col-1\"></div>
				<div class=\"col-10 text-center\">
					</h4>El usuario {$row['user_name']} no tiene el permiso para crear / editar bloques de validacion, 
					pide ayuda del encargado si deseas modificar un bloque de validación.</h4>
					<br>
					<br>
					<button
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"close_emergent();\"
					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
			</div>";
		}
	//verifica si es la sesion principal del bloque
		if( $validation_block_id != '' ){
			$sql = "SELECT
						id_sesion_principal
					FROM ec_bloques_transferencias_validacion btv
					LEFT JOIN ec_sesiones_dispositivos_validacion_transferencias sdvt
					ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
					WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
					AND btv.id_bloque_transferencia_validacion = '{$validation_block_id}'";
//die( $sql );
			$stm = $link->query( $sql ) or die( "Error al consultar si la sesión del dispositivo es la sesion principal del bloque de validacion : {$link->error}" );
			if( $stm->num_rows > 0 ){
				if( $transfers == '' ){
					$sql = "SELECT
								GROUP_CONCAT( DISTINCT( t.id_transferencia ) ) AS transfers
							FROM ec_bloques_transferencias_validacion_detalle btvd
							LEFT JOIN ec_transferencias t 
							ON t.id_transferencia = btvd.id_transferencia 
							WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}";
					$stm_aux = $link->query( $sql ) or die( "Error al consultar las transferencias actuales del bloque : {$link->error}" );
					$row = $stm_aux->fetch_assoc();
					$transfers = $row['transfers'];
				}
					return getMessageToAddTransfer( $transfers, $folio, $validation_block_id, $validation_token, $link );
			}else{
			//consulta el nombre del usuario principal
				$sql = "SELECT
							CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
						FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
						LEFT JOIN sys_users u 
						ON u.id_usuario = sdvt.id_usuario
						LEFT JOIN ec_bloques_transferencias_validacion btv
						ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
						WHERE btv.id_bloque_transferencia_validacion = '{$validation_block_id}'";
	//die( $sql ) ;
				$stm = $link->query( $sql ) or die( "Error al consular el nombre de la sesion principal : {$link->error}" );
				$row = $stm->fetch_assoc();
				return "<div class=\"row\">
					<div class=\"col-1\"></div>
					<div class=\"col-10 text-center\">
						</h4>Este dispositivo no es el dispositivo principal de validacion para el bloque {$validation_block_id}, 
						Pide al usuario <b style=\"color : green;\">{$row['user_name']}</b> de la sesión principal que edite este bloque si deseas agregar una transferencia a la validación</h4>
						<br>
						<br>
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
			}
		}
		return 'ok';
	}

	function reassign_principal_session_validation( $validation_session_id, $validation_block_id, $link ){
		$sql = "UPDATE ec_bloques_transferencias_validacion 
					SET id_sesion_principal = '{$validation_session_id}'
				WHERE id_bloque_transferencia_validacion = '{$validation_block_id}'";
		$stm = $link->query( $sql ) or die( "Error al actualizar la sesion principal del bloque : {$link->error}" );
	//bloque el dispositivo para que refresque
		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET bloqueada = '1' 
				WHERE id_sesion_dispositivo_validacion = {$validation_session_id}";
		$stm = $link->query( $sql ) or die ( "Error al bloquear la sesion del nuevo usuario principal: {$link->error}" );
		return "ok|<div class=\"text-center\">
			<h4>La sesion principal fue cambiada exitosamente</h4>
			<br>
			<h5>Da click en el botón de aceptar para continuar : </h5>
			<br>
			<button
				type=\"button\"
				class=\"btn btn-success\"
				onclick=\"close_validation_session();\"
			>
				<i class=\"icon-ok-circle\">Aceptar y cerrar Sesión</i>
			</button>
		</div>";
	}

	function close_device_validation_session( $validation_token, $validation_block_id, $link ){
		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET finalizada = '1'
				WHERE token_unico_dispositivo = '{$validation_token}'";
		$stm = $link->query( $sql ) or die( "Error al finalizar sesion de validacion : {$link->error}" );
	//consulta si es la sesion principal
		$sql = "SELECT 
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
				FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
				LEFT JOIN sys_users u
				ON u.id_usuario = sdvt.id_usuario
				WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
				AND btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion";
		$stm = $link->query( $sql ) or die( "error|Error al validar si el token corresponde a la sesion principal : {$link->error}" );
		if( $stm->num_rows > 0 ){
			$sql = "UPDATE ec_bloques_transferencias_validacion
						SET id_sesion_principal = 0
					WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
			$stm = $link->query( $sql ) or die( "error|Error al resetear la sesion principal del bloque : {$link->error}" );
		}
		return "ok|<div class=\"row\">
			<h5>Sesion finalizada Exitosamente!</h5>
			<div class=\"col-4\"></div>
			<div class=\"col-4\">
				<button
					type=\"button\"
					class=\"btn btn-success\"
					onclick=\"close_emergent_2();\"
				>
					<i class=\"icon-ok-circle\">Aceptar</i>
				</button>
			</div>
		</div>";
	}

	function close_validation_session( $validation_token, $validation_block_id, $link ){
	//verifica que no sea la sesion principal
		$sql = "SELECT
					id_sesion_dispositivo_validacion AS validation_session_id
				FROM ec_sesiones_dispositivos_validacion_transferencias
				WHERE token_unico_dispositivo = '{$validation_token}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el id de la sesion del token de validacion : {$link->error}" );
		$current_session = $stm->fetch_assoc();
	//consulta la sesion principal del bloque de validacion
		$sql = "SELECT
					id_sesion_principal AS principal_session
				FROM ec_bloques_transferencias_validacion
				WHERE id_bloque_transferencia_validacion = '{$validation_block_id}'";
		$stm = $link->query( $sql ) or die( "Error al consultar la sesion principal de validacion : {$link->error}" );
		$principal_session = $stm->fetch_assoc();
//die( $principal_session['principal_session'] . "==" .  $current_session['validation_session_id'] );
		if( $principal_session['principal_session'] == $current_session['validation_session_id'] ){
		//consulta si hay sesiones pendientes
			$sql = "SELECT 
						sdvt.id_sesion_dispositivo_validacion AS validation_session_id,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						sdvt.token_unico_dispositivo AS unic_token,
						IF( sp.ver = 1 OR sp.modificar = 1 OR sp.eliminar = 1 OR sp.nuevo = 1  
							OR sp.imprimir = 1 OR sp.generar = 1, 1, 0 ) AS edit_permission
					FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
					LEFT JOIN sys_users u 
					ON u.id_usuario = sdvt.id_usuario
					LEFT JOIN sys_users_perfiles up
					ON up.id_perfil = u.tipo_perfil
					LEFT JOIN sys_permisos sp
					ON sp.id_perfil = up.id_perfil 
					WHERE sp.id_menu = 241
					AND sdvt.id_bloque_validacion = '{$validation_block_id}'
					AND sdvt.token_unico_dispositivo != '{$validation_token}'
					AND sdvt.finalizada = 0";
			$stm = $link->query( $sql ) or die( "Error al consultar si hay validaciones pendientes de finalizar : {$link->error}" );
			if( $stm->num_rows > 0 ){
				return build_emergent_principal_sessions( $stm, $validation_token, $validation_block_id, $link );
			}
		}

		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET finalizada = '1' 
				WHERE token_unico_dispositivo = '{$validation_token}'";
		$stm = $link->query( $sql ) or die( "Error al finalizar la sesion de validacion : {$link->error}" );
	//consulta si es la sesion principal
		$sql = "SELECT 
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name
				FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion
				LEFT JOIN sys_users u
				ON u.id_usuario = sdvt.id_usuario
				WHERE sdvt.token_unico_dispositivo = '{$validation_token}'
				AND btv.id_sesion_principal = sdvt.id_sesion_dispositivo_validacion";
		$stm = $link->query( $sql ) or die( "error|Error al validar si el token corresponde a la sesion principal : {$link->error}" );
		if( $stm->num_rows > 0 ){
			$sql = "UPDATE ec_bloques_transferencias_validacion
						SET id_sesion_principal = 0
					WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
			$stm = $link->query( $sql ) or die( "error|Error al resetear la sesion principal del bloque : {$link->error}" );
		}
		return 'ok';
	}

	function build_emergent_principal_sessions( $stm, $validation_token, $validation_block_id, $link ){
		$resp = "";
		while ( $row = $stm->fetch_assoc() ) {
			if( $row['edit_permission'] == 1 ){
				$resp .= "<tr>
					<td>{$row['user_name']}</td>
					<td>{$row['unic_token']}</td>
					<td class=\"text-center\">
						<button
							type=\"button\"
							class=\"btn btn-warning\"
							onclick=\"reassign_principal_session_validation( {$row['validation_session_id']} );\"
						>
							<i class=\"icon-ok-circle\">Asignar</i>
						</button>
					</td>
				</tr>";
			}
		}
		if( $resp != "" ){
			return "<h4 style=\"position : sticky; top : -20px; background-color : white;\">Esta sesión de validación es la principal del bloque y aún hay sesiones de validacion sin finalizar, es necesario que 
			asignes la responsabilidad de este bloque a alguna de estas sesiones para poder finalizar tu sesión de validación: </h4>
			<div class=\"row\">
				<table class=\"table table-bordered table-striped\">
					<thead style=\"position : sticky; top : 60px; background-color : white;\">
						<tr>
							<th class=\"text-center\">Usuario</th>
							<th class=\"text-center\">Token</th>
							<th class=\"text-center\">Asignar</th>
						</tr>
					</thead>
					<tbody>
						{$resp}
					</tbody>
				</table><br>
				<div class=\"row\">
					<div class=\"col-4\"></div>
					<div class=\"col-4\">
						<button
							type=\"button\"
							class=\"btn btn-danger form-control\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-cancel-circled\">Cancelar</i>
						</button>
					</div>
				</div>
			</div>";
		}else{
			return "<h4 style=\"position : sticky; top : -20px; background-color : white;\">Esta sesión de validación es la principal del bloque y aún hay sesiones de validacion sin finalizar, es necesario que 
			asignes la responsabilidad de este bloque a una sesiones para poder finalizar tu sesión de validación, sin embargo, 
			actualmente no hay sesiones de validación con este permiso.</h4>
			<br>
			<div class=\"row\">
				<div class=\"col-4\"></div>
				<div class=\"col-4\">
					<button
						type=\"button\"
						class=\"btn btn-success form-control\"
						onclick=\"close_emergent();\"
					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
			</div>";

		}

	}


	function validate_devices_sessions( $current_block, $validation_token, $link ){
		$resp = "<h4 class=\"text-center\">Las siguientes sesiones de validación están pendientes de finalizar : </h4>
			<table class=\"table\">
			<thead>
				<tr>
					<th>Usuario</th>
					<th>Token</th>
					<th>Fecha de inicio</th>
					<th>Finalizar</th>
				</tr>
			</thead>
			<tbody>";
		$sql = "SELECT
					sdvt.token_unico_dispositivo AS unique_token,
					CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
					sdvt.fecha_sesion AS date_time
				FROM ec_sesiones_dispositivos_validacion_transferencias sdvt
				LEFT JOIN sys_users u
				ON u.id_usuario = sdvt.id_usuario
				WHERE sdvt.id_bloque_validacion = {$current_block}
				AND sdvt.token_unico_dispositivo != '{$validation_token}'
				AND sdvt.finalizada = 0";
		$stm = $link->query( $sql ) or die( "Error al consultar las sesiones de validación pendientes : {$link->error}" );
		if( $stm->num_rows == 0 ){
			return 'ok';
		}else{
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
					<td>{$row['user_name']}</td>
					<td>{$row['unique_token']}</td>
					<td>{$row['date_time']}</td>
					<td>
						<button
							type=\"button\"
							class=\"btn btn-danger\"
							onclick=\"close_device_validation_session( '{$row['unique_token']}' )\"
						>
							<i class=\"icon-erase\"></i>
						</button>
					</td>
				</tr>";
			}
			$resp .= "</tbody>
				</table>
				<br>
				<div class=\"row text-center\">
					<div class=\"col-4\"></div>
					<div class=\"col-4\">
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
			return $resp;
		}
	}

	function removeUnicToken( $token, $link ){
		$sql = "DELETE FROM ec_sesiones_dispositivos_validacion_transferencias 
				WHERE token_unico_dispositivo = '{$token}'";
		$stm = $link->query( $sql ) or die( "Error al eliminar el token : {$link->error}" );
		return 'ok|Token eliminado exitosamente!';
	}

	function create_validation_token( $user, $validation_block_id, $make_principal, $link ){
		$link->autocommit( false );
		$reception_block_id  = 'NULL';
	//verifica si la validacion esta enlazada a un bloque de recepcion
		$sql = "SELECT
					btrd.id_bloque_transferencia_recepcion AS reception_block_id
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
				WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}
				GROUP BY btrd.id_bloque_transferencia_recepcion";
		$stm = $link->query( $sql ) or die( "Error al consultar el bloque de recepcion relacionado a la validacion : {$link->error}" );
		if( $stm->num_rows > 0 ){
			$row = $stm->fetch_assoc();
			$reception_block_id = $row['reception_block_id'];
		}
	//inserta la sesion de validacion
		$sql = "INSERT INTO ec_sesiones_dispositivos_validacion_transferencias( id_sesion_dispositivo_validacion, 
			id_bloque_recepcion, id_bloque_validacion, id_usuario, fecha_sesion, bloqueada )
			VALUES ( NULL, {$reception_block_id}, {$validation_block_id}, {$user}, NOW(), 
				( SELECT bloqueado FROM ec_bloques_transferencias_validacion WHERE id_bloque_transferencia_validacion = {$validation_block_id} ) )";
		//die($sql);
		$stm = $link->query( $sql ) or die( "Error al insertar el registro de sesion de validacion  : {$link->error}" );
		$session_id = $link->insert_id;
	//generacion de token
		$sql = "SELECT 
					CONCAT( 'V', id_bloque_validacion, '_', 
						DATE_FORMAT( fecha_sesion, '%Y%m%d' ), '_',
						DATE_FORMAT( fecha_sesion, '%H%i%s' ), '_',
						id_usuario, '_',
						id_sesion_dispositivo_validacion
					) AS unic_token
				FROM ec_sesiones_dispositivos_validacion_transferencias
				WHERE id_sesion_dispositivo_validacion = {$session_id}";
		$stm = $link->query( $sql ) or die( "Error al general el token de sesion de validacion : {$link->error}" );		
		$row = $stm->fetch_assoc();
		$unic_token = $row['unic_token'];
	//actualiza el token en la sesion
		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
			SET token_unico_dispositivo = '{$unic_token}',
			fecha_modificacion = '0000-00-00 00:00:00'
			WHERE id_sesion_dispositivo_validacion = {$session_id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar el token de la sesion de validación : {$link->error}" );	
	//actualiza la sesion principal del bloque
		//die( "{$make_principal} == 'true'" );
		if( $make_principal == 1  ){//|| $is_principal_session == true
			$sql = "UPDATE ec_bloques_transferencias_validacion 
						SET id_sesion_principal = '{$session_id}'
					WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
	//		die( $sql );
			$stm = $link->query( $sql ) or die( "Error al actualizar la sesion principal de validación : {$link->error}" );
		}
		$link->autocommit( true );
		return "ok|{$unic_token}|{$validation_block_id}";
	} 

/*implementacion Oscar 2023 para validar que este completamente surtida la transferencia*/
	function validateTransferStatus( $transfers, $link ){
		$res = "";
		$sql = "SELECT
					t.folio AS transfer_folio,
					GROUP_CONCAT( 
						CONCAT( p.nombre, ' ( ', pp.clave_proveedor, ' ) pendientes : <b>', FORMAT( ( tp.cantidad - tp.total_piezas_surtimiento ), 2 ), '</b>' ) 
						SEPARATOR '<br>' 
					) AS transfer_products
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias t
				ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_transferencias_surtimiento_detalle tsd
				ON tp.id_transferencia_producto = tsd.id_transferencia_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_productos p 
				ON p.id_productos  = tp.id_producto_or
				WHERE tp.id_transferencia IN( ${transfers} )
				AND tsd.id_status_surtimiento IN( 1, 2 )
				GROUP BY t.id_transferencia";
		$stm = $link->query( $sql ) or die( "error|Error al validar que no haya registros pendientes de surtir : {$link->error}" );
		if( $stm->num_rows <= 0 ){
			return "ok|ok";
		}else{
			$resp .= "<h4  class=\"text-center\">Las siguientes transferencias tienen registros pendientes de surtir :</h4>";
			$resp .= "<div class=\"row\"><table class=\"table table-bordered table-striped\">
						<thead>
							<tr>
								<th>Transferencia</th>
								<th>Productos Pendientes</th>
							</tr>
						</thead>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
						<td>{$row['transfer_folio']}</td>
						<td>{$row['transfer_products']}</td>
					</tr>";
			}
			$resp .= "</table>
					</tr>
				<div class=\"row\">
					<div class=\"col-4\"></div>
					<div class=\"col-4\">
						<button
							type=\"button\"
							class=\"btn btn-success form-control\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
			return "ok|{$resp}";
		}
	}
/*fin de cambio Oscar 2023*/

	function getStoresFilter( $type, $current_sucursal = '', $link ){
		$sql = "SELECT id_sucursal AS store_id, nombre AS name FROM sys_sucursales WHERE id_sucursal > 0";
		$stm = $link->query( $sql ) or die( "Error al consultar lista de sucursales : {$link->error}" );
		$stores_options = "";
		while ( $row = $stm->fetch_assoc() ) {
			$stores_options .= "<option value=\"{$row['store_id']}\"" . ( $row['store_id'] == $current_sucursal ? ' selected' : '' ) . ">{$row['name']}</option>";
		}
		$resp = "<select id=\"store_filter_{$type}\" class=\"form-control\" onchange=\"reload_transfers_list_view( this );\">
					<option value=\"\">{$type}</option>
					{$stores_options}
				</select>";
		return $resp;
	}

	function skipPendingValidation( $transfer_product_id, $case_id, $link ){
		$link->autocommit( false );
		$sql = "UPDATE ec_transferencia_productos
					SET id_caso_surtimiento = {$case_id} 
				WHERE id_transferencia_producto = {$transfer_product_id}";
		$stm = $link->query( $sql ) or die( "Eror al omitir registro de trasnferencias : {$sql} {$link->error}" );

		$sql = "UPDATE ec_transferencias_surtimiento_usuarios
					SET id_caso_surtimiento = {$case_id} 
				WHERE id_transferencia_producto = {$transfer_product_id}";
		$stm = $link->query( $sql ) or die( "Error al omitir registro de transferencias ( surtimiento_detalle ) : {$sql} {$link->error}" );
		
		$link->autocommit( true );

		$resp = "ok|<div class=\"row\">
					<div class=\"col-2\"></div>
					<div class=\"col-8 text-center\">
						<h5>Registro Omitido exitosamente</h5>
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
		return $resp;
	}

	function getSupplyCasesCombo( $link, $option_selected = null ){
			$sql = "SELECT 
						id_caso_surtimiento AS supply_case_id,
						nombre_caso_surtimiento AS supply_case_name
					FROM ec_casos_surtimiento
					WHERE id_caso_surtimiento > 0
					AND tipo = 'validacion'";
			$stm = $link->query( $sql ) or die( "Error al consultar los casos de surtimiento : {$link->error}" );
			//echo 'hetre';
			$resp = "<select id=\"supply_case\" style=\"padding : 8px; border-radius: 5px; width:100%;\">
						<option value=\"\">-- Seleccionar --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<option value=\"{$row['supply_case_id']}\">{$row['supply_case_name']}</option>";
				//" . ( $option_selected != null && $option_selected == $row['supply_case_id'] ?' selected' : '' ) . "
			}
			$resp .= "</select>";
			return $resp;
		}

	function showHiddeValidatePendingForm( $transfer_product_id, $link ){
		$resp = "<div class=\"row\">
					<div class=\"col-1\"></div>
					<div class=\"col-10 text-center\">
						<h5>Motivo por el que no se surtió / validó :</h5>";
			$resp .= getSupplyCasesCombo( $link );//, $row['case_id'] 
		$resp .= "<br><br>
				<div class=\"row\">
					<div class=\"col-2\"></div>
					<div class=\"col-4\">
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"skip_pending_validation( {$transfer_product_id} );\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
					<div class=\"col-4\">
						<button
							type=\"button\"
							class=\"btn btn-danger\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-cancel-circled\">Cancelar</i>
						</button>
					</div>
				</div>";
		$resp .= "</div>
				</div>";
		return $resp;
	}

	function getPermissionToMAkeBlocks( $user, $link ){
		//consulta el permiso para asignar
		$sql = "SELECT 
					IF( p.ver = 1 OR p.modificar = 1 OR p.eliminar = 1 OR p.nuevo = 1 
						OR p.imprimir = 1 OR p.generar = 1, 1, 0 )
					AS permission
				FROM sys_permisos p
				LEFT JOIN sys_users_perfiles up
				ON up.id_perfil = p.id_perfil
				LEFT JOIN sys_users u
				ON u.tipo_perfil = up.id_perfil
				WHERE p.id_menu = 241
				AND u.id_usuario = {$user}";
//return ( " Perfil : {$sql}" );
		$stm_perm = $link->query( $sql ) or die( "Error al consultar permisos del perfil de usuario : {$link->error}" );
		$row = $stm_perm->fetch_assoc();
		//$edit_permission = $row['permission'];
		return $row['permission'];
	}

	function addTransferBlock( $transfer_id, $block_id, $link ){
		$link->autocommit( false );
		$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle ( id_bloque_transferencia_validacion, id_transferencia, fecha_alta )
			VALUES ( {$block_id}, {$transfer_id}, NOW() )";
		$stm = $link->query( $sql ) or die( "Error al insertar la transferencia en un bloque ya existente : {$link->error}");
	//verifica si tiene un bloque de recepcion enlazado
		$sql = "SELECT
					btrd.id_bloque_transferencia_recepcion AS reception_block_id
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				WHERE btvd.id_bloque_transferencia_validacion = '{$block_id}'";
		$stm = $link->query( $sql ) or die( "Error al consultar si la validacion esta enlazada a una recepcion : {$link->error}" );
//$link->autocommit( false );
		if( $stm->num_rows > 0 ){
			while( $row = $stm->fetch_assoc() ){
				$sql = "UPDATE ec_bloques_transferencias_recepcion 
							SET bloqueado = '0' 
						WHERE id_bloque_transferencia_recepcion = '{$row['reception_block_id']}'";
				$update = $link->query( $sql ) or die( "Error al desbloquear bloque de validación : {$link->error}" );
			}
		}
	//desbloquea el bloque de validacion
		$sql = "UPDATE ec_bloques_transferencias_validacion 
					SET bloqueado = '0'
				WHERE id_bloque_transferencia_validacion = '{$block_id}'";
		$stm = $link->query( $sql ) or die( "Error al desbloquear el bloque de validación : {$link->error}");
		//desbloquea el bloque de validacion
		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET bloqueada = '0'
				WHERE id_bloque_validacion = '{$block_id}'";
		$stm = $link->query( $sql ) or die( "Error al desbloquear el bloque de validación : {$link->error}");
	
		$link->autocommit( true );
		
		return 'ok';
	}

/*implementacion Oscar 2023 para bloqueos de sesiones de validacion / recepcion*/

	function getUpdateValidationBlock( $validation_block_id, $validation_token, $user_id, $link ){
	//verifica sobre el bloque
			$sql = "SELECT
						bloqueado AS is_locked
					FROM ec_bloques_transferencias_validacion
					WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
			$stm = $link->query( $sql ) or die( "Error al eliminar bloqueo :  {$link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['is_locked'] == 0 ){
			//desbloquea el token del dispositivo
				$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
							SET bloqueada = '0'
						WHERE token_unico_dispositivo = '{$validation_token}'";
				$stm = $link->query( $sql ) or die( "Error al desbloquear la sesión de recepción del dispositivo : {$link->error}" );
				
				return 'ok';
			}
		return 'no';
	}
	function validate_permission_block( $validation_block_id, $validation_token, $without_token = false, $link ){
		$sql= "SELECT 
					bloqueado AS is_locked
				FROM ec_bloques_transferencias_validacion
				WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
//die( 'error|' . $sql );
		$stm = $link->query( $sql ) or die( "error|Error al consultar si el bloque esta bloqueado : {$link->error}" );
		$row = $stm->fetch_assoc();
		
		$sql= "SELECT 
					bloqueada AS is_locked,
					finalizada AS was_finished
				FROM ec_sesiones_dispositivos_validacion_transferencias
				WHERE token_unico_dispositivo = '{$validation_token}'";
		$stm_token = $link->query( $sql ) or die( "Error al consultar si el token del dispositivo esta bloqueado : {$link->error}" );
		$row_token = $stm_token->fetch_assoc();
	//implementacion Oscar 2023 para validacion de token
		if( ( $row_token['was_finished'] == 1 || $row_token == null ) && $without_token == false ){
			$resp = "invalid_token|<div class=\"row\">
				<h5>La sesión de Validación es inválida</h5>
				<h4>Da click en continuar para recargar la página y vuelve a escanear las transferencias</h4>
				<div class=\"col-4\"></div>
				<div class=\"col-4 text-center\">
						<button
							class=\"btn btn-success form-control\"
							onclick=\"location.reload();\"
						>
							<i class=\"icon-ok-circle\">Continuar</i>
						</button>
				</div>
			</div>";
			die( $resp );
		}
	//fin de cambio Oscar 2023
		if( $row['is_locked'] == 1 || $row_token['is_locked'] == 1 ){
//die( 'error|' . $sql );
			$resp = "<div>
						<div class=\"text-center\">
							<h4>El bloque esta en proceso de edicion, espera mientras se termina de editar.
							Al terminar se actualizará la pantalla y deberas de escanear la(s) Transferencias para 
							continuar</h4>
							</br></br>
							<img src=\"../../../../img/img_casadelasluces/load.gif\" style=\"width : 25%;\">
						</div>
					</div>";
			$resp .= "<script>
						var cont = 0;
					    var id = setInterval(function(){
					    	var response = seek_update_reception_block( current_transfers_blocks );
					    	if( response == 'ok' ){
            					//clearInterval(id);
            					alert( 'La pantalla se va a recargar' );
            					location.reload();
					    	}
					    }, 10000); 

						function seek_update_reception_block( validation_block_id ){
							var url = 'ajax/db.php?fl=getUpdateValidationBlock&validation_block_id=' + current_transfers_blocks;
							url += '&token=' + localStorage.getItem( 'validation_token' );
							//alert( url );
							var response = ajaxR( url );
							//alert( response );
							return response;
						}
					</script>";
			die( "message|{$resp}" );
		}/*else{
		//verifica si tiene algun registro pendiente
		}*/
	}

	function block_validation_sessions( $validation_block_id, $validation_token, $link ){
	//consulta si hay un bloque de recepcion relacionado a la validacion
		$sql = "SELECT
					btrd.id_bloque_transferencia_recepcion AS reception_block_id
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
				WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}
				GROUP BY btrd.id_bloque_transferencia_recepcion";
		$stm = $link->query( $sql ) or die( "Error al consultar si hay un bloque de recepcion ligado al bloque de validacion : {$link->error}" );
		if( $stm->num_rows > 0 ){
			$row = $stm->fetch_assoc();
			$reception_block_id = $row['reception_block_id'];
	//bloquea el bloque de recepcion
			$sql = "UPDATE ec_bloques_transferencias_recepcion 
					SET bloqueado = '1'
					WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm = $link->query( $sql ) or die( "Error al bloquear el bloque de recepcion {$reception_block_id} : {$link->error}" );
 	//bloquea sesiones de la recepcion
			$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
					SET bloqueada = '1'
					WHERE id_bloque_recepcion = {$reception_block_id}";
			$stm = $link->query( $sql ) or die( "Error al bloquear las sesiones del bloque de recepcion {$reception_block_id} : {$link->error}" );
		}
	//bloquea el bloque de validacion
			$sql = "UPDATE ec_bloques_transferencias_validacion
					SET bloqueado = '1'
					WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
			$stm = $link->query( $sql ) or die( "Error al bloquear el bloque de valkidacion {$validation_block_id} : {$link->error}" );
	//bloquea sesiones de validacion
			$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET bloqueada = '1'
					WHERE id_bloque_validacion = {$validation_block_id}
					AND token_unico_dispositivo != '{$validation_token}'";
			//die( $sql );
			$stm = $link->query( $sql ) or die( "Error al bloquear las sesiones del bloque de validacion {$validation_block_id} : {$link->error}" );
			return 'ok';
	}
/*fin de cambio Oscar 2023*/

	function getMessageToAddTransfer( $transfers, $folio, $validation_block_id, $validation_token, $link ){//$reception_block_id agregado por oscar 2023 para el bloqueo de la validacion 
		$sql = "SELECT
					t.id_transferencia AS transfer_id
				FROM ec_transferencias t
				WHERE t.folio = '{$folio}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el id de la transferencia : {$link->error}" );
		$row = $stm->fetch_assoc();
		$transfer_id = $row['transfer_id'];
	//echo $sql;
/*implementacion Oscar 2023 para bloqueos de sesiones de validacion / recepcion*/
	//bloquea las sesiones de validacion
		$lock = block_validation_sessions( $validation_block_id, $validation_token, $link );
		if( $lock != 'ok' ){
			return "Error : {$lock}";
		}
/*fin de cambio Oscar 2023*/
		$resp = "<h3><i>ATENCIÓN!</i></h3>";
		$resp .= "<p>¿ Esta transferencia que escaneaste se enviará junto con estas transferencias ?</p>";
		$sql = "SELECT
					t.folio AS folio,
					t.fecha AS date,
					IF( t.id_tipo = 5, 'Urgente', 'Normal' ) AS type
				FROM ec_transferencias t
				WHERE t.id_transferencia IN( $transfers )";
		$stm = $link->query( $sql ) or die( "Error al consultar las transferencias del bloque : {$link->error}" );
		$resp .= "<table class=\"table table-bordered\">";
			$resp .= "<thead><tr><th>Folio</th><th>Fecha</th><th>Prioridad</th></tr></thead><tbody>";
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= "<tr>
						<td>{$row['folio']}</td>
						<td>{$row['date']}</td>
						<td>{$row['type']}</td>
					</tr>";
		}
		$resp .= "</tbody></table><br><br>";

		$resp .= "<div class=\"row\">
					<div class=\"col-1\"></div>
					<div class=\"col-5\">
						<span>Escribe la palabra JUNTO si la transferencia se enviará junto a estas transferencias</span>
						<input type=\"text\" id=\"together_option\" class=\"form-control\" placeholder=\"junto\">
					</div>
					<div class=\"col-5\">
						<span>Escribe la palabra SEPARADO si la transferencia se enviará aparte</span>
						<input type=\"text\" id=\"separate_option\" class=\"form-control\" placeholder=\"separado\">
					</div>
					<div class=\"col-1\"></div>

					<div class=\"col-3\"></div>
					<div class=\"col-6\">
						<button
							class=\"btn btn-success form-control\"
							onclick=\"option_add_transfer_validation({$transfer_id});\"
						>
							<i class=\"\">Aceptar</i>
						</button>
						<br><br>
						<button
							class=\"btn btn-danger form-control\"
							onclick=\"cancel_validation_block_lock( {$validation_block_id} );\"
						>
							<i class=\"\">Cancelar</i>
						</button>
					</div>
				</div>";
		$resp .= "";
		$resp .= "";
		return $resp;
	}
		
	function getTransfersListValidation( $link, $store_id, $filters = '', $orders = '' ){
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
					t.folio,
					s1.nombre AS origin,
					s2.nombre AS destination,
					ts.nombre AS status,
					IF( tvd.id_bloque_transferencia_validacion IS NULL, '', tvd.id_bloque_transferencia_validacion ) AS block,
					t.id_almacen_origen AS origin_warehouse,/*implementacion Oscar 2023*/
					t.id_almacen_destino AS destinity_warehouse/*implementacion Oscar 2023*/
				FROM ec_transferencias t
				LEFT JOIN sys_sucursales s1 ON s1.id_sucursal = t.id_sucursal_origen
				LEFT JOIN sys_sucursales s2 ON s2.id_sucursal = t.id_sucursal_destino
				LEFT JOIN ec_estatus_transferencia ts ON ts.id_estatus = t.id_estado
				LEFT JOIN ec_bloques_transferencias_validacion_detalle tvd
				ON tvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion tv
				ON tv.id_bloque_transferencia_validacion = tvd.id_bloque_transferencia_validacion
				WHERE t.id_estado IN( 3, 4, 5, 6 )
				AND t.id_transferencia > 0
				AND t.id_tipo NOT IN ( 6, 10, 11 )
				AND t.id_sucursal_origen = {$store_id}";
//die( $sql );
		if( $filters != '' ){
			//var_dump($filters);
			$condition .= ( $filters['store_orig'] != null ? " AND t.id_sucursal_origen = {$filters['store_orig']}" : "" );
			$condition .= ( $filters['store_dest'] != null ? " AND t.id_sucursal_destino = {$filters['store_dest']}" : "" );
			$sql .= $condition;
		}
	//echo $sql;
		if( $orders != '' && $orders != null ){
			$order_by = "";
			if( $orders['folio'] != '' && $orders['folio'] != null ){
				$order_by .= ( $order_by != "" ? ", " : "" );
				$tmp = explode('-', $orders['folio'] );
				$order_by .= "{$tmp[0]} {$tmp[1]}";
			}

			if( $orders['status'] != '' && $orders['status'] != null ){
				$order_by .= ( $order_by != "" ? ", " : "" );
				$tmp = explode('-', $orders['status'] );
				$order_by .= "{$tmp[0]} {$tmp[1]}";
			}

			if( $orders['block_id'] != '' && $orders['block_id'] != null ){
				$order_by .= ( $order_by != "" ? ", " : "" );
				$tmp = explode('-', $orders['block_id'] );
				$order_by .= "{$tmp[0]} {$tmp[1]}";
			}
			
			if( $order_by != ""){
				$sql .= " ORDER BY {$order_by}";
			}
		}
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : {$sql} " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '<tr><td colspan="8" align="center">Sin Transferencias por validar!</td></tr>';
		}

		$counter = 0;
		$block = "";
		$block_counter = 0;
		$color = "";
		while ( $r = $stm->fetch_assoc() ) {
			if( $block != $r['block'] ){
				$block_counter ++;
			}
			$block = $r['block'];
			$color = ( $block_counter % 2 == 0 ? '#FAD7A0' : 'silver' );
			$color = ( $block == '' ? 'white' : $color );
			$resp .= build_list_row( $r, $counter, $color );
			$counter ++;
		}
		return $resp;
	}

	function build_list_row( $row, $counter, $color = 'white' ) {//style=\"background-color : rgba({$row['block']}, 0,0, .5);\"
		$resp = "<tr style=\"background-color : {$color};\">
				<td id=\"validation_list_1_{$counter}\" class=\"no_visible\">{$row['transfer_id']}</td>
				<td><i class=\"icon-barcode btn btn-warning\" id=\"validation_list_9_{$counter}\" style=\"font-size : 120%;\"></i></td>
				<td id=\"validation_list_2_{$counter}\">{$row['folio']}</td>
				<td id=\"validation_list_3_{$counter}\">{$row['origin']}</td>
				<td id=\"validation_list_4_{$counter}\">{$row['destination']}</td>
				<td id=\"validation_list_5_{$counter}\">{$row['status']}</td>
				<td id=\"validation_list_6_{$counter}\">{$row['block']}</td>
				<td id=\"validation_list_7_{$counter}\" align=\"center\">
					<input 
						type=\"checkbox\" 
						id=\"validation_list_8_{$counter}\" 
						onclick=\"getAllGroup( {$counter} );\" 
						class=\"checkbox-warning\" 
						disabled
					>
				</td>
				<td id=\"validation_list_10_{$counter}\" class=\"no_visible\">{$row['origin_warehouse']}</td>
				<td id=\"validation_list_11_{$counter}\" class=\"no_visible\">{$row['destinity_warehouse']}</td>
			</tr>";
		return $resp;
	}

	function validateBarcode( $barcode, $transfers, $user, $excedent_permission = null, 
		$pieces_quantity = null, $permission_box = null, $unique_code = null, $block_id, $validation_token, $link ){

		validate_permission_block( $block_id, $validation_token, false, $link );
	
	//verifica que el código único no haya sido usado anteriormente
		if( $unique_code != null ){
			$sql = "SELECT
						t.folio,
						CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
						s1.nombre AS origin_name,
						s2.nombre AS destinity_name,
						tcu.id_status_transferencia_codigo AS unique_barcode_status,
						t.id_transferencia
					FROM ec_transferencia_codigos_unicos tcu
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON tcu.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_transferencias t
					ON t.id_transferencia = btvd.id_transferencia
					LEFT JOIN sys_users u
					ON u.id_usuario = tcu.id_usuario_validacion
					LEFT JOIN sys_sucursales s1 
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2 
					ON s2.id_sucursal = t.id_sucursal_destino
					WHERE tcu.codigo_unico = '{$unique_code}'";
			$stm = $link->query( $sql ) or die( "error|Error al validar si el código único ya fue registrado : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				$resp = "exception_repeat_unic|<br><h5 style=\"color : red; text-align : center;\">Este código único ya fue validado anteriormente : </h5>";
				$resp .= "<p>Código barras : {$barcode} --- Código Único : {$unique_code}</p>";
				$resp .= "<p><b>Escaneado por :</b> <b class=\"orange\">{$row['name']}</b></p>";
				$resp .= "<p><b>Pertenece a Transferencia : <b class=\"orange\">{$row['folio']}</b></p>";
				$resp .= "<p><b>Sucursal Origen :</b> <b class=\"orange\">{$row['origin_name']}</b></p>";
				$resp .= "<p><b>Sucursal Destino :</b> <b class=\"orange\">{$row['destinity_name']}</b></p>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-3\"></div>";
					$resp .= "<div class=\"col-6\">";
						$resp .= "<button 
									class=\"btn btn-warning form-control barcode_is_repeat_btn\" 
									onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">";
							$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
						$resp .= "</button>";
					$resp .= "</div>";
				$resp .= "</div>";
				return $resp;
			}
		}
	//verifica si el codigo de caja es de validacion de la caja
			if( $permission_box == null ){
				$sql = "SELECT 
							id_codigo_validacion
						FROM ec_codigos_validacion_cajas
						WHERE codigo_barras = '{$barcode}'";
				$stm = $link->query( $sql ) or die( "Error al consultar si es código de validación de caja : {$link->error}" );
				if( $stm->num_rows == 1 ){
					$resp = 'is_box_code|';
					$resp .= '<div>';
						$resp .= '<div class="row">';
							$resp .= '<div class="col-2"></div>';
							$resp .= '<div class="col-8">';
								$resp .= '<label for="tmp_sell_barcode">El código de barras del sello es válido, para continuar escaneé el código de barras de la caja : </label>';
								$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control" onkeyup="validateBarcode( this, event, null, null, 1 );"><br>';
								$resp .= '<button type="button" class="btn btn-success form-control"';
								$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
									$resp .= '<i class="icon-ok-circle">Aceptar</i>';
								$resp .= '</button><br><br>';
								$resp .= '<button type="button" class="btn btn-danger form-control"';
								$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
									$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
								$resp .= '</button>';
							$resp .= '</div>';
						$resp .= '</div>';
					$resp .= '</div>';
					return $resp;
				}
			}
	//verifica si el código de barras existe
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.id_producto AS product_id,
					IF( '$barcode' != pp.codigo_barras_pieza_1 AND '$barcode' != pp.codigo_barras_pieza_2 
					 AND '$barcode' != pp.codigo_barras_pieza_3 AND '$barcode' != pp.codigo_barras_presentacion_cluces_1
					 AND '$barcode' != pp.codigo_barras_presentacion_cluces_2 AND '$barcode' != pp.codigo_barras_caja_1 
					 AND '$barcode' != pp.codigo_barras_caja_2 , 1, 0 ) AS is_name_seeker
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
				OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
				OR pp.codigo_barras_caja_2 = '{$barcode}')";
		//return "error|{$sql}";
		$stm1 = $link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : " . $link->error );
		if( $stm1->num_rows <= 0 ){
			return seekByName( $barcode, $link );
		}
		

	//verifica que el proveedor producto exista en alguna transferencia
		$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
					OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 ) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					tp.cantidad_cajas,
					tp.cantidad_paquetes,
					tp.cantidad_piezas,
					tp.cantidad,
					pp.presentacion_caja AS pieces_per_box,
					pp.piezas_presentacion_cluces AS pieces_per_pack,
					SUM( IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
						) 
					) AS boxes_recived,
					SUM(IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) 
						) 
					) AS packs_recived,
					SUM(IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							tvu.cantidad_piezas_validadas 
						) 
					) AS pieces_recived,
					( SELECT 
						IF( pd.id_producto IS NULL, 
							0, 
							IF( pd.id_producto = p.id_productos, 
								1, 
								-1  
							) 
						) 
					  FROM ec_productos_detalle pd
					  WHERE pd.id_producto = p.id_productos
					  OR pd.id_producto_ordigen = p.id_productos
					) AS is_maquiled
				FROM ec_transferencia_productos tp/*ec_transferencias_surtimiento_usuarios tsu*/
				/*ON tp.id_transferencia_producto = tsu.id_transferencia_producto*/
				LEFT JOIN ec_transferencias t 
				ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias_validacion_usuarios tvu 
				ON tp.id_transferencia_producto = tvu.id_transferencia_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
					/*OR p.nombre LIKE '%{$barcode}%'*/
				)
				GROUP BY tp.id_transferencia_producto";
//die('error|' . $sql);
		$stm2 = $link->query( $sql ) or die( "error|Error al buscar el producto por código de barras :  " . $link->error );
	//verifica si el producto existe en la transferencia
		if( $stm2->num_rows <= 0 ){
			$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
					OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 ) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					( SELECT 
						IF( pd.id_producto IS NULL, 
							0, 
							IF( pd.id_producto = p.id_productos, 
								1, 
								-1  
							) 
						) 
					  FROM ec_productos_detalle pd
					  WHERE pd.id_producto = p.id_productos
					  OR pd.id_producto_ordigen = p.id_productos
					) AS is_maquiled
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_transferencia_productos tp
				ON tp.id_producto_or = pp.id_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tp.id_producto_or = p.id_productos
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')";
//return '|' . $sql;
			$stm3 = $link->query( $sql ) or die( "error|Error al consultar si el producto existe en la transferencia : " . $link->error );
			if( $stm3->num_rows <= 0){
				$resp = 'message_info|<br/><h3 class="inform_error">El producto no pertenece a esta(s) Transferencia(s).<br />Verifique los datos y vuelva a intentar</h3>';
					$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">Aceptar</button></div><br/>';
				return $resp;
			}else{
				$inform = $stm3->fetch_assoc();
				
				//die( '|here' );
				$input_pieces = "";
				if( $inform['piece'] != 0 ){
					$input_pieces = "<p class=\"text-center\">Ingresa el número de piezas : </p>
					<input type=\"number\" class=\"form-control\" 
						id=\"new_supply_pieces_quantity\"
						onkeyup=\"validate_is_not_decimal( this );\"> <br>";////implementacion Oscar 2023/09/26 para evitar numeros decimales en emergente de piezas
				}

				$pieces = ( $pieces_quantity != null ? $pieces_quantity : $inform['piece'] );

				$resp = 'manager_password|<br/><h3 class="inform_error">El modelo del producto es incorrecto<br />Si se va a enviar, pide la autorización del encargado : </h3>'; 
				$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= $input_pieces;
				$resp .= '<input type="password" id="manager_password" class="form-control emergent_manager_password"><br>';
				$resp .= '<button class="btn btn-success form-control" onclick="save_new_supply( ';
					$resp .= "{$block_id}, {$inform['product_id']}, {$inform['product_provider_id']}, 
					{$inform['box']}, {$inform['pack']}, {$pieces}, '{$barcode}', '{$unique_code}' ";
				$resp .= ' );">Aceptar</button> <br><br>';
				
				$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
				$resp .= 'Cancelar</button></div></div><br>';
					
				return $resp;
			}
		}
		$row = $stm2->fetch_assoc();
		//validacion para no dejar pasar códigos estandar si es paquete o caja
		if( ( $unique_code == null || $unique_code == '' ) && ( $row['pack'] == 1 || $row['box'] == 1 ) ){
			return "message_info|
				<div class=\"row\">
					<div class=\"col-1\"></div>
					<div class=\"col-10 text-center\">
						<h5>El código de barras que se escaneo es de caja o paquete y no cuenta con un 
						código único, envié una fotografía o captura de pantalla al encargado de sistemas :</h5>
						<p>Código escaneado : <b style=\"color : red;\">{$barcode}</b></p>
						<br>
						<br>
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
						<br>
						<br>
					</div>
				</div>
			";
		}
		
		if( $row['piece'] == 1 && $pieces_quantity == null 
			&& $excedent_permission == null && $permission_box == '' ){
			if( $row['is_maquiled'] == 1 || $row['is_maquiled'] == -1  ){

					$sql_maq = "SELECT 
									total_piezas_surtimiento AS quantity
								FROM ec_transferencia_productos
								WHERE id_transferencia_producto = {$row['transfer_product_id']}";
					$stm_maq = $link->query( $sql_maq ) or die( "Error al consultar la cantidad pedida : {$link->error}" );
					$row_maq = $stm_maq->fetch_assoc();
					$initial_quantity = $row_maq['quantity'];

					include( '../../../plugins/maquile.php' );
					$Maquile = new maquile( $link );

					$function_js = 'setPiecesQuantity( 1 );';
					//die( 'error|' . $sql_maq );
					return "pieces_form|" . $Maquile->make_form( $row['product_id'], 0, $function_js, $initial_quantity, 'Cantidad surtida : ', 'close_emergent();' );
					//die('');
			}
		//regresa formulario de piezas
			$resp = 'pieces_form|<div class="row">';
					$resp .= '<div><h5>Ingresa el número de Piezas : </h5></div>';
					$resp .= '<div class="col-2"></div>';
					$resp .= '<div class="col-8">';
						$resp .= '<input type="number" class="form-control" id="pieces_quantity_emergent"';
						$resp .= ' onkeyup="validate_is_not_decimal( this );">';//implementacion Oscar 2023/09/26 para evitar numeros decimales en emergente de piezas
						$resp .= '<button type="button" class="btn btn-success form-control"';
						$resp .= ' onclick="setPiecesQuantity();">';
							$resp .= 'Aceptar';
						$resp .= '</button><br><br>';
						$resp .= '<button type="button" class="btn btn-danger form-control"';
						$resp .= ' onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
							$resp .= 'Cancelar';
						$resp .= '</button>';
					$resp .= '</div>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}

		if( $permission_box == null && $row['box'] == 1  && $row['pieces_per_box'] > 1 ){
			$resp = 'message_info|<div class="row">';
				$resp .= '<div class="col-2"></div>';
				$resp .= '<div class="col-8"><h5>Para escanear la caja primero escaneé el sello de caja, si este esta roto escaneé los paquetes </h5>';
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
						$resp .= 'Aceptar';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}else if( $permission_box != null && $row['box'] != 1 ){
				$resp = 'is_not_a_box_code|';
				$resp .= '<div>';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-2"></div>';
						$resp .= '<div class="col-8">';
							$resp .= '<label for="tmp_sell_barcode">El código de barras no pertenece a una caja, para continuar escaneé el código de barras de la caja : </label>';
							$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control"><br>';
							$resp .= '<button type="button" class="btn btn-success form-control"';
							$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
								$resp .= '<i class="icon-ok-circle">Aceptar</i>';
							$resp .= '</button><br>';
							$resp .= '<button type="button" class="btn btn-danger form-control"';
							$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
								$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
							$resp .= '</button>';
						$resp .= '</div>';
					$resp .= '</div>';
				$resp .= '</div>';
				return $resp;
		}

		if( $pieces_quantity != null ){
			$row['piece'] = $pieces_quantity;
		}//die( 'example|' . $row['product_id'] );
		return insertProductValidation( $row, $user, $transfers, $excedent_permission, $permission_box, $barcode, $unique_code, $block_id, $link );
		
	}

	function insertProductValidation( $data, $user, $transfers, $excedent_permission = null, $permission_box = 1, $barcode, $unique_code = null, $block_id, $link ){

		$link->autocommit( false );
	//verifica transferencias pendientes de validación	
		$sql = "SELECT 
					ax.product_transfer_id,
					ax.boxes_to_validate,
					ax.packs_to_validate,
					ax.pieces_to_validate,
					ax.pending_to_validate
				FROM(
					SELECT
						tp.id_transferencia_producto AS product_transfer_id,
						( SUM( IF( tp.cantidad_cajas_surtidas = 0, tp.cantidad_cajas, tp.cantidad_cajas_surtidas ) ) - SUM( tp.cantidad_cajas_validacion ) ) AS boxes_to_validate,
						( SUM( IF( tp.cantidad_paquetes_surtidos = 0, tp.cantidad_paquetes, tp.cantidad_paquetes_surtidos ) ) - SUM( tp.cantidad_paquetes_validacion ) ) AS packs_to_validate,
						( SUM( IF( tp.cantidad_piezas_surtidas = 0, tp.cantidad_piezas, tp.cantidad_piezas_surtidas ) ) - SUM( tp.cantidad_piezas_validacion ) ) AS pieces_to_validate,
						( SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) ) - SUM( tp.total_piezas_validacion ) ) AS pending_to_validate
					FROM ec_transferencia_productos tp
				/*LEFT JOIN ec_productos p ON tp.id_producto_or = p.id_productos*/
				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or = '{$data['product_id']}'
				AND tp.id_proveedor_producto = '{$data['product_provider_id']}'
				GROUP BY tp.id_transferencia_producto
				/*AND SUM( tp.total_piezas_surtimiento ) > SUM( tp.total_piezas_validacion )*/
				)ax
				WHERE 1/*ax.pending_to_validate > 0*/
				GROUP BY ax.product_transfer_id
				ORDER BY ax.product_transfer_id";/*ax.pending_to_validate, DESC*/
		$stm = $link->query( $sql ) or die( "error|Error al consultar transferencias pendientes de validar : " . $link->error );
//echo "<br><br>1 : {$sql}<br>";
	//verifica que la cantidad que se va a validar no supere la cantidad pedida
		$sql = "SELECT 
					CONCAT( p.nombre, ' <b> ( MODELO : ', pp.clave_proveedor, ' )</b>' ) AS description_name,
					SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) )
					- SUM( tp.total_piezas_validacion ) AS total_to_validation,
					SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) ) AS pieces_total,
					SUM( tp.total_piezas_validacion ) AS validated_pieces,
					( ( pp.presentacion_caja * {$data['box']} ) 
								+ ( pp.piezas_presentacion_cluces * {$data['pack']} ) 
								+ {$data['piece']} ) AS supplie
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or = '{$data['product_id']}'
				AND tp.id_proveedor_producto = '{$data['product_provider_id']}'";
		$stm2 = $link->query( $sql ) or die( "error|Verifica que la cantidad que se va a validar no supere la cantidad pedida : {$link->error}" );
		$comparation_row = $stm2->fetch_assoc();
//echo "<br><br>2: {$sql}<br>";
		//return 'error|'. $sql;
		$description = '';
		if( ( $stm->num_rows <= 0 || $comparation_row['supplie'] > $comparation_row['total_to_validation'] ) 
			&& $excedent_permission == null ){
			//while( $r = $stm->fetch_assoc() ){
				$numeric_value = '';
				if( $data['piece'] != 0 ){
					$numeric_value = $data['piece'];
					$description = 'La pieza';
				}else if( $data['pack'] != 0 ){
					$numeric_value = $data['pack'];
					$description = 'El paquete';
				}else if( $data['box'] != 0 ){
					$numeric_value = $data['box'];
					$description = 'La caja';
				}
			$resp = 'amount_exceeded|<h5>' . $description . ' que escaneo supera la cantidad surtida, si se va a enviar';

			$resp .= ' pida la autorización del encargado : </h5>';
			$resp .= "<p class=\"orange\">{$comparation_row['description_name']}</p>";
			
			$resp .= '<div class="row"><div class="col-2"></div>';
				$resp .= '<div class="col-8">';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad total de surtimiento : <br><b class=\"orange\">" . round( $comparation_row['pieces_total'], 4 ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad surtida : <br><b class=\"orange\">" . round ( $comparation_row['validated_pieces'], 4 ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad pendiente de validar : <br><b class=\"orange\">" . ($comparation_row['total_to_validation'] <= 0 ? 0 : round( $comparation_row['total_to_validation'], 4 ) ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad que se intenta validar : <br><b class=\"orange\">". round( $comparation_row['supplie'], 4 ) . "</b></p>";
						$resp .= '</div>';
					$resp .= '</div>';
					
					$resp .= '<input type="password" class="form-control" id="manager_password">';
					$res .= '<p id="response_password"></p>';
					$resp .= '<button type="button" class="btn btn-success form-control';
						$resp .= ' form-control" onclick="confirm_exceeds( ' . ( $permission_box == 1 ? '1'  : '' ) . ' );">';
						$resp .= '<i class="icon-ok-circle">Aceptar</i>';
					$resp .= '</button>';

					$resp .= '<button type="button" class="btn btn-danger form-control';
						$resp .= ' form-control" onclick="return_exceeds();">';
						$resp .= '<i class="icon-ok-circle">Regresar producto</i>';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}else{

		//si encuentra registros pendientes
			$quantity = 0;
			if( $data['piece'] != 0 ){
				$quantity = $data['piece'];
			}else if( $data['pack'] != 0 ){
				$quantity = $data['pieces_per_pack'];
				$data['pack'] = 0;
			}else if( $data['box'] != 0 ){
				$quantity = $data['pieces_per_box'];
				$data['box'] = 0;
			}
			$transfers_total = $stm->num_rows;
			$transfers_counter = 1;
			$more_than_one_transfer = 0;
//			echo 'ok|';
			while( $transfer = $stm->fetch_assoc() ){
				$assign_quantity = 0;
					if( $quantity > 0 ){
						//piezas surtidas vs piezas_validadas		
						if( $transfer['pending_to_validate'] > $quantity ){
							$assign_quantity = $quantity;
						}
						if( $transfer['pending_to_validate'] == $quantity ){
							$assign_quantity = $quantity;
						}
						if( $transfer['pending_to_validate'] < $quantity ){
							$assign_quantity = $transfer['pending_to_validate'];
							if( $excedent_permission != null 
							&& $transfers_counter == $transfers_total ){
								$assign_quantity = $quantity;
							}
						}
						if( $assign_quantity > 0 ){
							//inserta el registro de validación
							$sql_2 = "INSERT INTO ec_transferencias_validacion_usuarios ( id_transferencia_validacion, id_transferencia_producto,
							id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_validadas, cantidad_paquetes_validados, 
							cantidad_piezas_validadas, fecha_validacion, id_status, codigo_barras, codigo_unico )
							VALUES( NULL, '{$transfer['product_transfer_id']}', '{$user}', '{$data['product_id']}', '{$data['product_provider_id']}', 
								'0', '0', '{$assign_quantity}', NOW(), 1, '{$barcode}', '{$unique_code}' )";
//echo "<br><br>3 : {$sql}<br>";
							$stm_2 = $link->query( $sql_2 ) or die( "error|Error al insertar el registro de validación : {$link->error}" );
							$validation_detail_id = $link->insert_id;
							//echo ( 'Error : ' . $sql );
						//actualiza la validacion del producto en la transferencia
							$sql_3 = "UPDATE ec_transferencia_productos tp 
									LEFT JOIN ec_proveedor_producto pp 
									ON tp.id_proveedor_producto = pp.id_proveedor_producto
								SET tp.cantidad_cajas_validacion =  ( tp.cantidad_cajas_validacion + {$data['box']} ),
								tp.cantidad_paquetes_validacion =  ( tp.cantidad_paquetes_validacion + {$data['pack']} ),
								tp.cantidad_piezas_validacion =  ( tp.cantidad_piezas_validacion + {$assign_quantity} ),
								tp.total_piezas_validacion = ( tp.total_piezas_validacion + {$assign_quantity} )
								WHERE tp.id_transferencia_producto = '{$transfer['product_transfer_id']}'
								AND pp.id_proveedor_producto = '{$data['product_provider_id']}'";
							$stm_3 = $link->query( $sql_3 ) or die( "error|Error al actualizar las piezas validadas en la transferencia : {$link->error}" );
//echo "<br><br>4 : {$sql}<br>";							
							//echo ( '|Error 2: ' . $sql_3 );
						//se avctualiza la cantidad
							$quantity  -= $assign_quantity;
						}
					}
				//}
				$transfers_counter ++;//incrementa contador de detalles de transferencias
			}

		//código unico
			if( $unique_code != null ){
				$sql = "INSERT INTO ec_transferencia_codigos_unicos ( /*1*/id_transferencia_codigo, /*2*/id_bloque_transferencia_validacion,
					/*3*/id_bloque_transferencia_recepcion, /*4*/id_usuario_validacion, /*5*/id_usuario_recepcion, /*6*/id_status_transferencia_codigo, 
					/*7*/nombre_status, /*8*/fecha_alta, /*9*/codigo_unico, /*10*/piezas_contenidas, /*11*/id_transferencia_validacion )
					SELECT 
						/*1*/NULL, 
						/*2*btv.id_bloque_transferencia_validacion*/'{$block_id}',
						/*3*/NULL,
						/*4*/{$user}, 
						/*5*/NULL, 
						/*6*/1, 
						/*7*/(SELECT nombre_status FROM ec_status_transferencias_codigos_unicos WHERE id_status_transferencia_codigo = 1), 
						/*8*/NOW(),
						/*9*/'{$unique_code}',
						/*10*/( SELECT 
									( {$data['box']} * pp.presentacion_caja )
									+ ( {$data['pack']} * pp.piezas_presentacion_cluces )
									+ ( {$data['piece']} )
								FROM ec_proveedor_producto pp
								WHERE pp.id_proveedor_producto = {$data['product_provider_id']}
							),
						/*11*/{$validation_detail_id}
					FROM ec_transferencia_productos tp
					/*LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON tp.id_transferencia = btvd.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btv
					ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion*/
					WHERE tp.id_transferencia_producto = {$data['transfer_product_id']}
					GROUP BY tp.id_transferencia_producto";
				$stm = $link->query( $sql ) or die( "error|Error al insertar el código único : {$sql} {$link->error}" );
//echo "<br><br>5 : {$sql}<br>";
			}
		//asigna ajuste de inventario pendiente
			$sql = "UPDATE ec_diferencias_inventario_proveedor_producto 
						SET id_usuario_resuelve = '{$user}'
					WHERE id_transferencia_producto = '{$data['transfer_product_id']}'
					AND ajustado = '0'";
			$stm = $link->query( $sql ) or die( "Error al actualizar el usuario en el ajuste : {$link->error}" );
//echo "<br><br>6 :{$sql}<br>";
		}//fin de else si encuentra registros pendientes
		$link->autocommit( true );

		$resp = '<div class="row">';
			$resp .= '<div class="col-3"></div>';
			$resp .= '<div class="col-6">';
				$resp .= '<button class="btn btn-success form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
					$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		
		return "ok|<p align=\"center\">Código Validado exitosamente</p>";//{$resp}
	}

	function loadLastValidations( $transfers, $user, $link ){
		$sql = "SELECT
					tvu.id_transferencia_validacion AS transfer_validation_id,
					p.id_productos AS product_id,
					CONCAT( p.nombre, ' ( MODELO : <b>', pp.clave_proveedor, '</b> )' ) AS name,
					t.id_transferencia AS transfer,
					IF(	tvu.cantidad_cajas_validadas > 0, 
						CONCAT( tvu.cantidad_cajas_validadas, ' caja', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
						IF( tvu.cantidad_paquetes_validados > 0,
							CONCAT( tvu.cantidad_paquetes_validados, ' paquete', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
							CONCAT( tvu.cantidad_piezas_validadas, ' pieza', IF( tvu.cantidad_piezas_validadas > 1, 's', '' ))
						)
					) AS recived
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp 
				ON tvu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tvu.id_producto = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tvu.id_proveedor_producto = pp.id_proveedor_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND tvu.id_usuario = '{$user}'
				ORDER BY tvu.id_transferencia_validacion DESC
				LIMIT 3";
				//die( $sql );
		$stm = $link->query( $sql )or die( "Error al consultar las últimas revisiones : " . $link->error );
		return buildLastValidations( $stm );	
	}

	function buildLastValidations( $stm ){
		$resp = '';
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= '<tr>';
			$resp .= '<td class="no_visible">' . $row['transfer_validation_id'] . '</td>';
			$resp .= '<td>' . $row['name'] . '</td>';
			$resp .= '<td>' . $row['recived'] . '</td>';
			$resp .= '<td>' . $row['transfer'] . '</td>';
			$resp .= '</tr>';
		}
		return $resp;
	}
/*generacion de tablas de resumen*/
	function getResumeHeader( $transfers, $type, $link ){
		if( $type == 1 ){
			$title = 'Partidas Pendientes';
		}else{
			$title = 'Partidas Agregadas ( autorizadas )';
		}
		$resp = '<center class="list_header_sticky top-10"><h6><b>' . $title . '</b></h6></center>';
		$resp .= '<table class="table table-bordered table-striped table_70">';
			$resp .= '<thead class="list_header_sticky top8">';
				$resp .= '<tr>';
					$resp .= '<th>#</th>';
					$resp .= '<th>Producto</th>';
					$resp .= '<th>Transferencia</th>';
					$resp .= '<th>';
					$resp .= ( $type == 1 ? 'Faltante' : 'Agregadas' );
					$resp .= '</th>';
					$resp .= "<th class=\"text-center\">Surtió</th>";//Oscar 2023/11/20
				$resp .= '</tr>';
			$resp .= '</thead>';
			$resp .= '<tbody id="validation_resume_' . $type . '">';
			$resp .= getResumeRows( $transfers, $type, $link );
			$resp .= '</tbody>';
		$resp .= '</table>';
		return $resp;
	}

/*generacion de registros de resumen*/
	function getResumeRows( $transfers, $type, $link ){
		$resp = '';
		if( $type == 1 ){
			$sql = "SELECT
						ax.name,
						ax.reference,
						ax.difference,
						ax.assortment_quantity,
						ax.transfer_product_id,
						IF( ts.id_transferencia_surtimiento IS NULL, 'Sin asigar', CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) ) AS username
					FROM(
						SELECT
							CONCAT( p.nombre, ' <b>', pp.clave_proveedor, '<b>' ) AS name,
							t.id_transferencia AS reference, 
							SUM( IF(tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento) ) 
							- SUM( tp.total_piezas_validacion ) AS difference,
							tp.total_piezas_surtimiento AS assortment_quantity,
							tp.id_transferencia_producto AS transfer_product_id
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_productos p 
						ON tp.id_producto_or = p.id_productos
						LEFT JOIN ec_proveedor_producto pp 	
						ON tp.id_proveedor_producto = pp.id_proveedor_producto
						LEFT JOIN ec_transferencias t 
						ON tp.id_transferencia = t.id_transferencia
						/*LEFT JOIN ec_transferencias_resolucion tr
						ON tr.id_transferencia_producto = tp.id_transferencia_producto*/
						WHERE tp.id_transferencia IN( {$transfers} )
						AND tp.id_caso_surtimiento NOT IN( 2, 3, 4 )
						/*AND tr.id_transferencia_producto IS NULL*/
						GROUP BY tp.id_transferencia_producto, tp.id_proveedor_producto
					)ax
					LEFT JOIN ec_transferencias_surtimiento_detalle tsd
					ON tsd.id_transferencia_producto = ax.transfer_product_id
					LEFT JOIN ec_transferencias_surtimiento ts
					ON ts.id_transferencia_surtimiento = tsd.id_transferencia_surtimiento
					LEFT JOIN sys_users u
					ON u.id_usuario = ts.id_usuario_asignado
					WHERE ax.difference > 0
					GROUP BY ax.transfer_product_id";
               //die( $sql );
        }else{
			$sql = "SELECT
					CONCAT( p.nombre, ' <b>', pp.clave_proveedor, '</b>' ) AS name,
					t.id_transferencia AS reference, 
					SUM( tp.cantidad ) AS difference,
					tp.total_piezas_surtimiento AS assortment_quantity,
					tp.id_transferencia_producto AS transfer_product_id,
						IF( ts.id_transferencia_surtimiento IS NULL, '-', CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) ) AS username
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias t 
				ON tp.id_transferencia = t.id_transferencia

					LEFT JOIN ec_transferencias_surtimiento_detalle tsd
					ON tsd.id_transferencia_producto = tp.id_transferencia_producto
					LEFT JOIN ec_transferencias_surtimiento ts
					ON ts.id_transferencia_surtimiento = tsd.id_transferencia_surtimiento
					LEFT JOIN sys_users u
					ON u.id_usuario = ts.id_usuario_asignado

				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.agregado_en_surtimiento = 1
                GROUP BY tp.id_transferencia_producto, tp.id_proveedor_producto";/*
                ORDER BY CONCAT( p.nombre, pp.clave_proveedor )*/
        }
       // echo $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar registros pendientes de validar : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '';
		}
		$counter = 0;
		while ( $row = $stm->fetch_assoc() ) {
			$counter ++;
			if( $row['name'] != '' && $row['name'] != null ){
				$resp .= '<tr';
				$resp .= ( $row['assortment_quantity'] == 0 ? ' class="no_assortment_row"' : '' );
				$resp .= '>';
					//$resp .= '<td class="no_visible">' . $row[''] . '</td>';
					$resp .= '<td>' . $counter . '</td>';
					$resp .= '<td>' . $row['name'] . '</td>';
					$resp .= '<td>' . $row['reference'] . '</td>';
					$resp .= '<td align="right">' . round($row['difference'], 4) . '</td>';
					$resp .= '<td align="center">' . $row['username'] . '</td>';
				if( $type == 1 ){	
					$resp .= "<td align=\"center\">";
					$resp .= "<button
						type=\"button\"
						class=\"btn btn-danger\"
						onclick=\"show_hidde_validate_pending_form( {$row['transfer_product_id']} );\"
					>	
						<i class=\"icon-cancel-alt-filled\"></i>
					</button>";
				$resp .= "</td>";
				}
				$resp .= '</tr>';
			}
		}
		return $resp;
	}

	function saveValidation( $transfers, $validation_token, $link ){
		$link->autocommit( false );
	//verifica que no haya ningún registro sin surtir
		$sql = "SELECT 
					tp.id_transferencia_producto AS transfer_product_id,
					p.nombre AS product_name,
					pp.presentacion_caja AS pieces_per_box,
					pp.clave_proveedor AS provider_clue,
					tp.cantidad AS pending_supply
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p
				ON p.id_productos = tp.id_producto_or
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				WHERE tp.total_piezas_surtimiento = 0
				AND tp.id_caso_surtimiento IN( 0, -1, 1 )
				AND tp.total_piezas_validacion = 0
				AND tp.id_transferencia IN( {$transfers} )";
		$stm = $link->query( $sql ) or die( "Error al consultar si hay productos pendientes de surtir" );
		if( $stm->num_rows > 0 ){
			return buildPendingToSupplyView( $stm );
		}
		//manda a hacer las resoluciones correspondientes
		$resolutions = makeResolutionValidation( $transfers, $link );
		if( $resolutions != 'ok' ){
			return $resolutions;
		}
		$sql = "UPDATE ec_transferencias SET id_estado = 7 WHERE id_transferencia IN( {$transfers} )";
		$stm = $link->query( $sql ) or die( "Error al actualizar las Trasnferencias a Validadas : " . $link->error );
	//Impementacion Oscar 2023 para marcar como finalizada la sesion de validacion del dispositivo que finaliza la validacion de Transferencias
		$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
					SET finalizada = '1'
				WHERE token_unico_dispositivo = '{$validation_token}'";
		$stm = $link->query( $sql ) or die( "Error al finalizar la sesion de validacion del dispositivo : {$link->error}" );
		$link->autocommit( true );
		return 'ok|Transferencias Validadas exitosamente!';
	}

	function buildPendingToSupplyView( $stm ){
		$resp .= "<h5>Los siguientes productos no fueron surtidos, verifica con el usuario </h5>
				<table>
					<thead>
						<tr>
							<th>Producto</th>
							<th>Clave Proveedor</th>
							<th>Pzs por caja</th>
							<th>Pendiente de surtir</th>
						</tr>
					<thead>
					<tbody>";
		while( $row = $stm->fetch_assoc() ){
			$resp .= "<tr>
						<td class=\"no-visible\">{$row['transfer_product_id']}</td>
						<td>{$row['product_name']}</td>
						<td>{$row['pieces_per_box']}</td>
						<td>{$row['provider_clue']}</td>
						<td>{$row['pending_supply']}</td>
					</tr>";
		}

		$resp .= "<tbody>
			</table>
			<div class=\"row\">
				<div class=\"col-3\"></div>
				<div class=\"col-6\">
					<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"close_emergent();\">
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
				<div></div>
			</div>";
		return $resp;
	}

//resolución de la validación
	function makeResolutionValidation( $transfers, $link ){
		$resp = 'ok';
		$link->autocommit( false );
	//colnsulta las transferencias con diferencia
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
                    ma.id_movimiento_almacen AS warehouse_movement_id
					/*GROUP_CONCAT( tp.id_transferencia_producto SEPARATOR ',' )*/
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias t 
				ON t.id_transferencia = tp.id_transferencia
                LEFT JOIN ec_movimiento_almacen ma
                ON ma.id_transferencia = t.id_transferencia
				WHERE tp.cantidad != tp.total_piezas_validacion
				AND t.id_transferencia IN( {$transfers} )
				GROUP BY tp.id_transferencia";
		$stm = $link->query( $sql ) or die( "Error al consultar las transferencias con diferencias : {$link->error}" );

		while( $row = $stm->fetch_assoc() ){
			//$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento, /*3*/id_producto,
			//			/*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle, /*7*/id_oc_detalle, /*8*/id_proveedor_producto )
			//		SELECT
			//			/*1*/NULL,
			//			/*2*/{$row['warehouse_movement_id']},
			//			/*3*/tp.id_producto_or,
			//			/*4*/( tp.total_piezas_validacion - tp.cantidad ),
			//			/*5*/( tp.total_piezas_validacion - tp.cantidad ),
			//			/*6*/-1,
			//			/*7*/-1,
			//			/*8*/tp.id_proveedor_producto
			//		FROM ec_transferencia_productos tp
			//		WHERE tp.id_transferencia IN( '{$row['transfer_id']}' )
			//		AND tp.cantidad <> tp.total_piezas_validacion";
			$sql = "SELECT
						tp.id_producto_or AS product_id,
						( tp.total_piezas_validacion - tp.cantidad ) AS quantity,
						tp.id_proveedor_producto AS product_provider_id
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia IN( '{$row['transfer_id']}' )
					AND tp.cantidad <> tp.total_piezas_validacion";
			$stm2 = $link->query( $sql ) or die( "Error al consultar los movimientos de la resolución : {$sql} {$link->error}" );
			while( $row_2 = $stm2->fetch_assoc() ){
				$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$row['warehouse_movement_id']}, {$row_2['product_id']}, {$row_2['quantity']}, {$row_2['quantity']},
							-1, -1, {$row_2['product_provider_id']}, 5 )";
				$stm_3 = $link->query( $sql ) or die( "Error al insertar los detalles de movimientos de la resolución : {$sql} {$link->error}" );
			}
		}
		$link->autocommit( true );
		return $resp;
	}

	function validateManagerPassword( $password, $link ){
		$sql = "SELECT id_usuario FROM sys_users WHERE contrasena = md5( '{$password}' )";
		$stm = $link->query( $sql ) or die( "Error al verificar password de encargado : " . $link->error );
		if( $stm->num_rows <= 0 ){
			die( 'La contraseña del encargado es incorrecta.' );
		}
		return 'ok';
	}


	function insertNewProductValidation( $block_id, $transfers, $product_id, $product_provider_id, $box, $pack, $piece,
	$barcode, $unique_code, $user, $link ){
		//die( 'ok|here' );
		$link->autocommit( false );
	//verifica a ue transferencia se le asignara el producto
		$sql = "SELECT 
					t.id_transferencia AS transfer_id,
					/*ma.id_movimiento_almacen AS mov_id,*/
					( SELECT id_movimiento_almacen FROM ec_movimiento_almacen WHERE id_transferencia IN ( t.id_transferencia ) LIMIT 1 ) AS mov_id,
					SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) AS difference
				FROM ec_transferencias t
				LEFT JOIN ec_movimiento_almacen ma
				ON ma.id_transferencia = t.id_transferencia
				LEFT JOIN ec_transferencia_productos tp
				ON t.id_transferencia = tp.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or IN( {$product_id} )
				ORDER BY SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) DESC
				LIMIT 1";
		//return $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar en que transferencia esta el producto : " . $link->error );
	//vuelve a validar que el producto exista en alguna transferencia
		if( $stm->num_rows <= 0 ){
			die( "error|<h5>El producto no pertence a ninguna Transferencia <br /> Aparte el producto de la transferencia para que no sea enviado a la sucursal</h5>" );
		}
		$transf = $stm->fetch_assoc();
		$transfer_id = $transf['transfer_id'];
		$mov_id = $transf['mov_id'];

	//inserta el detalle en transferencia producto
		$sql = "INSERT INTO ec_transferencia_productos( /*1*/id_transferencia, /*2*/id_producto_or, 
			/*3*/id_presentacion, /*4*/cantidad_presentacion, /*5*/cantidad, /*6*/id_producto_de, 
			/*7*/referencia_resolucion, /*8*/cantidad_cajas, /*9*/cantidad_paquetes, 
			/*10*/cantidad_piezas, /*11*/id_proveedor_producto, /*12*/cantidad_cajas_surtidas,
			/*13*/cantidad_paquetes_surtidos, /*14*/cantidad_piezas_surtidas, 
			/*15*/total_piezas_surtimiento, /*16*/cantidad_cajas_validacion, 
			/*17*/ cantidad_paquetes_validacion, /*18*/ cantidad_piezas_validacion, 
			/*19*/total_piezas_validacion, /*20*/agregado_en_surtimiento )
			SELECT
			/*1*/'{$transfer_id}',
			/*2*/'{$product_id}',
			/*3*/-1,
			/*4*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece} ,
			/*5*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack}) 
					+ {$piece} ,
			/*6*/'{$product_id}',
			/*7*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*8*/'{$box}',
			/*9*/'{$pack}',
			/*10*/'{$piece}',
			/*11*/'{$product_provider_id}',
			/*12*/'{$box}',
			/*13*/'{$pack}',
			/*14*/'{$piece}',
			/*15*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*16*/'{$box}',
			/*17*/'{$pack}',
			/*18*/'{$piece}',
			/*19*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*20*/'1'
			FROM ec_proveedor_producto pp
			WHERE pp.id_proveedor_producto = '{$product_provider_id}'";
		$stm = $link->query( $sql ) or die( "Error al insertar el nuevo registro en la transferencia" . $link->error );
		$new_detail_id  = $link->insert_id;
	//inserta el detalle del movimiento de almacen
		/*$sql = "INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida, 
				id_pedido_detalle, id_oc_detalle, id_proveedor_producto )
				SELECT 
					'{$mov_id}',
					tp.id_producto_or,
					tp.cantidad,
					tp.cantidad,
					-1,
					-1, 
					tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia_producto = '{$new_detail_id}'";*/
		
		$sql = "SELECT 
					tp.id_producto_or As product_id,
					tp.cantidad AS quantity,
					tp.id_proveedor_producto AS product_provider_id
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia_producto = '{$new_detail_id}'";
		$stm2 = $link->query( $sql )or die( "Error al consultar el detalle del movimiento de almacen : {$sql}" . $link->error );
		while( $row_2 = $stm2->fetch_assoc() ){
			$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$mov_id}, {$row_2['product_id']}, {$row_2['quantity']}, {$row_2['quantity']},
						-1, -1, {$row_2['product_provider_id']}, 5 )";
			$stm_3 = $link->query( $sql ) or die( "Error al insertar los detalles de movimientos de la resolución : {$sql} {$link->error}" );
		}
	//inserta el registro de validación
		$sql_2 = "INSERT INTO ec_transferencias_validacion_usuarios ( id_transferencia_validacion, id_transferencia_producto,
		id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_validadas, cantidad_paquetes_validados, 
		cantidad_piezas_validadas, fecha_validacion, id_status, codigo_barras, codigo_unico )
		VALUES( NULL, '{$new_detail_id}', '{$user}', '{$product_id}', '{$product_provider_id}', 
			'0', '0', '{$piece}', NOW(), 1, '{$barcode}', '{$unique_code}' )";
		$stm_2 = $link->query( $sql_2 ) or die( "error|Error al insertar el registro de validación : {$link->error}" );
		$validation_detail_id = $link->insert_id;
	//inserta el código único si es el caso 
//echo "<br>unique_code : {$unique_code}<br>";
		if( $unique_code != null ){
			$sql = "INSERT INTO ec_transferencia_codigos_unicos ( /*1*/id_transferencia_codigo, /*2*/id_bloque_transferencia_validacion,
				/*3*/id_bloque_transferencia_recepcion, /*4*/id_usuario_validacion, /*5*/id_usuario_recepcion, /*6*/id_status_transferencia_codigo, 
				/*7*/nombre_status, /*8*/fecha_alta, /*9*/codigo_unico, /*10*/piezas_contenidas, /*11*/id_transferencia_validacion )
				SELECT 
					/*1*/NULL, 
					/*2*/'{$block_id}',
					/*3*/NULL,
					/*4*/{$user}, 
					/*5*/NULL, 
					/*6*/1, 
					/*7*/(SELECT 
							nombre_status 
						FROM ec_status_transferencias_codigos_unicos 
						WHERE id_status_transferencia_codigo = 1), 
					/*8*/NOW(),
					/*9*/'{$unique_code}',
					/*10*/( SELECT 
								( {$box} * pp.presentacion_caja )
								+ ( {$pack} * pp.piezas_presentacion_cluces )
								+ ( {$piece} )
							FROM ec_proveedor_producto pp
							WHERE pp.id_proveedor_producto = {$product_provider_id}
						),
					/*11*/{$validation_detail_id}
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia_producto = {$new_detail_id}
				GROUP BY tp.id_transferencia_producto";
			$stm = $link->query( $sql ) or die( "error|Error al insertar el código único : {$sql} {$link->error}" );
		}
		$link->autocommit( true );
		return "<div class=\"text-center\">
					<h5>El producto fue agregado y validado exitosamente.</h5><br><br>
					<button class=\"btn btn-success\" onclick=\"close_emergent();\">
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
					<br><br>
				<div>";
	}

	function getInventoryAdjudments( $user, $link ){
		$resp = '';
		$sql = "SELECT 
					dipp.id_diferencia_inventario AS row_id,
					dipp.id_producto AS product_id,
					dipp.id_proveedor_producto AS product_provider_id,
					p.nombre AS name,
					pp.clave_proveedor AS provider_clue,
					ipp.inventario AS virual_inventory,
					IF( ppua.id_ubicacion_matriz IS NULL, 
						'No hay ubicaciones registradas',
						GROUP_CONCAT( 
							CONCAT( 
								IF( ppua.letra_ubicacion_desde = '', '', ppua.letra_ubicacion_desde ),
								IF( ppua.numero_ubicacion_desde = '', '', CONCAT( '-', ppua.numero_ubicacion_desde ) ),
								IF( ppua.letra_ubicacion_hasta = '', '', CONCAT( ' a ', ppua.letra_ubicacion_hasta ) )/*,
								IF( ppua.pasillo_hasta = '', '', CONCAT( '-', ppua.pasillo_hasta ) ),
								IF( ppua.altura_desde = '', '', CONCAT( ', f', ppua.altura_desde ) ),
								IF( ppua.altura_hasta = '', '', CONCAT( '-', ppua.altura_hasta ) ),
								IF( ppua.altura_de = '', '', CONCAT( ', n', ppua.altura_de ) ),
								IF( ppua.altura_a = '', '', CONCAT( '-', ppua.altura_a ) )*/
							)
							SEPARATOR '~' 
						)
					) AS locations
				FROM ec_diferencias_inventario_proveedor_producto dipp
				LEFT JOIN ec_productos p
				ON p.id_productos = dipp.id_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = dipp.id_proveedor_producto
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_producto = dipp.id_producto
				AND ipp.id_proveedor_producto = dipp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				WHERE ipp.id_almacen = 1
				AND dipp.ajustado = '0'
				AND dipp.id_usuario_resuelve = '{$user}'
				GROUP BY dipp.id_proveedor_producto";
	//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar los ajustes pendientes de realizar : {$link->error}" );
		if( $stm->num_rows <= 0 ){
			$resp = 'ok';
		}else{
			$resp = '<div class="row adjustments_list">';
				$resp .= '<div class="col-12">';
					$resp .= '<h5 class="orange">Para continuar es necesario hacer el ajuste de los';
					$resp .= ' siguientes inventarios : </h5>';
					//$resp .= '';
					$resp .= '<div class="adjudments_container">';
					$resp .= '<table class="table table-striped table-bordered table_70">';
						$resp .= '<thead class="list_header_sticky">';
							$resp .= '<tr>';
								$resp .= '<th width="25%">Producto</th>';
								$resp .= '<th width="25%">Modelo</th>';
								$resp .= '<th width="20%">Inv. Virtual</th>';
								$resp .= '<th width="20%">Inv. Físico</th>';
								$resp .= '<th width="10%">Ubic</th>';
							$resp .= '</tr>';
						$resp .= '</thead>';
						$resp .= '<tbody id="inventoryAdjudments">';
					$counter = 0;
					while ( $row = $stm->fetch_assoc() ) {
							$resp .= '<tr ">';
								$resp .= '<td id="adjustment_1_' . $counter . '" class="no_visible">' . $row['row_id'] .' </td>';
								$resp .= '<td id="adjustment_2_' . $counter . '" class="no_visible">' . $row['product_id'] .' </td>';
								$resp .= '<td id="adjustment_3_' . $counter . '" class="no_visible">' . $row['product_provider_id'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_4_' . $counter . '">' . $row['name'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_5_' . $counter . '">' . $row['provider_clue'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_6_' . $counter . '">' . $row['virual_inventory'] .' </td>';
								$resp .= '<td style="vertical-align : middle;"><input id="adjustment_7_' . $counter . '" type="number" class="form-control"';
								$resp .= ' onchange="calculate_adjustment_differece( ' . $counter . ' );"></td>';
								$resp .= '<td id="adjustment_8_' . $counter . '" class="no_visible">0</td>';
								$resp .= '<td id="adjustment_9_' . $counter . '" class="no_visible">' . $row['locations'] . '</td>';
								$resp .= '<td style="vertical-align : middle;">';
									$resp .= '<button onclick="sow_adjustemt_locations( ' . $counter .  ' );" class="btn-info">';
										$resp .= '<i class="icon-location"></i>';
									$resp .= '</button>';
								$resp .= '</td>';
							$resp .= '</tr>';
						$counter ++;
					}
						$resp .= '</tbody>';
					$resp .= '</table>';
					$resp .= '</div>';
				//$resp .= '</div>';
			$resp .= '</div><br><br>';

			$resp .= '<div class="row adjudments_buttons" style="margin-top : 40px;">';
				$resp .= '<div class="col-1"></div>';//
				$resp .= '<div class="col-10">';//adjudments_buttons
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="save_adjustment();">';
						$resp .= '<i class="icon-ok-circle">Guardar Ajuste</i>';
					$resp .= '</button><br><br>';
					$resp .= '<input type="password" class="form-control" id="manager_password" 
								placeholder="Password de encargado">';
					$resp .= '<br><button type="button" class="btn btn-warning form-control" 
						onclick="omit_inventory_adjustment();">';
						$resp .= '<i class="">Omitir ajuste</i>';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
		}
		return $resp;
	}

//-8, +9
	function inventoryAdjustment( $addition, $substraction, $data_ok, $user, $link ){
		//die( "$addition, $substraction, $data_ok, $user" );
		$resp = '';
		$link->autocommit( false );
		if( $substraction != '' &&  $substraction != null  ){
	//inserta la cabecera del movimiento de almacen ( resta )
			//$sql = "INSERT INTO ec_movimiento_almacen ( /*1*/id_movimiento_almacen, /*2*/id_tipo_movimiento, 
			//	/*3*/id_usuario, /*4*/id_sucursal, /*5*/fecha, /*6*/hora, /*7*/observaciones, /*8*/id_pedido,
			//	/*9*/id_orden_compra, /*10*/lote, /*11*/id_maquila, /*12*/id_transferencia, /*13*/id_almacen )
			//		VALUES( /*1*/NULL, /*2*/8, /*3*/{$user}, /*4*/1, /*5*/NOW(), /*6*/NOW(), 
			//			/*7*/'RESTA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', /*8*/-1, /*9*/-1, /*10*/NULL,
			//			/*11*/-1, /*12*/-1, /*13*/1 )";
			$sql = "CALL spMovimientoAlmacen_inserta ( {$user}, 'RESTA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', 1, 1, 8,
						-1, -1, -1, -1, 5 )";
			$stm = $link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen ( ajuste ): {$sql} : {$link->error}" );
			//$mov_header_id = (int) $link->insert_id;
		//recupera el id insertado
			$sql = "SELECT MAX(id_movimiento_almacen) AS movement_header_id FROM ec_movimiento_almacen";
			$stm = $link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado por ajuste ( resta ) : {$sql} : {$link->error}" );
			$row = $stm->fetch_assoc();
			$mov_header_id = $row['movement_header_id'];
			$substraction_array = explode( '|', $substraction );
			//die( $substraction );
			foreach ( $substraction_array as $key => $sub ) {
				$sub = explode( '~', $sub );
				if( $sub[0] != '' && $sub[0] != null ){
				//	$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento,
				//		/*3*/id_producto, /*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle,/*7*/id_oc_detalle,
				//		/*8*/id_proveedor_producto ) VALUES ( /*1*/NULL, /*2*/{$mov_header_id},/*3*/{$sub[1]}, /*4*/{$sub[3]}, 
				//		/*5*/{$sub[3]}, /*6*/-1, /*7*/-1, /*8*/{$sub[2]} )";
					$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$mov_header_id}, {$sub[1]}, {$sub[3]}, {$sub[3]}, 
								-1, -1, {$sub[2]}, 5 )";
					$exc = $link->query( $sql ) or die ( "Error al insertar el detalle del movimiento de almacen 1 : {$link->error}" );	
					
					$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
								SET ajustado = '1' WHERE id_diferencia_inventario = {$sub[0]}";
					$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 1 : {$link->error} {$sql}" );			
				}
		//die( $sql );
			}
		}


		if( $addition != '' &&  $addition != null  ){
	//inserta la cabecera del movimiento de almacen ( suma )
			//$sql = "INSERT INTO ec_movimiento_almacen ( /*1*/id_movimiento_almacen, /*2*/id_tipo_movimiento, 
			//	/*3*/id_usuario, /*4*/id_sucursal, /*5*/fecha, /*6*/hora, /*7*/observaciones, /*8*/id_pedido,
			//	/*9*/id_orden_compra, /*10*/lote, /*11*/id_maquila, /*12*/id_transferencia, /*13*/id_almacen )
			//		VALUES( /*1*/NULL, /*2*/9, /*3*/{$user}, /*4*/1, /*5*/NOW(), /*6*/NOW(), 
			//			/*7*/'SUMA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', /*8*/-1, /*9*/-1, /*10*/NULL,
			//			/*11*/-1, /*12*/-1, /*13*/1 )";
			$sql = "CALL spMovimientoAlmacen_inserta ( {$user}, 'SUMA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', 1, 1, 9,
						-1, -1, -1, -1, 5 )";
			$stm = $link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen ( ajuste ): {$link->error}" );
			//$mov_header_id = (int) $link->insert_id;

			//recupera el id insertado
			$sql = "SELECT MAX(id_movimiento_almacen) AS movement_header_id FROM ec_movimiento_almacen";
			$stm = $link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado por ajuste ( resta ) : {$sql} : {$link->error}" );
			$row = $stm->fetch_assoc();
			$mov_header_id = $row['movement_header_id'];

			$addition_array = explode( '|', $addition );
			foreach ( $addition_array as $key => $add ) {
				$add = explode( '~', $add );
				if( $add[0] != '' && $add[0] != null ){
			//		$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento,
			//			/*3*/id_producto, /*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle,/*7*/id_oc_detalle,
			//			/*8*/id_proveedor_producto ) VALUES ( /*1*/NULL, /*2*/{$mov_header_id},/*3*/{$add[1]}, /*4*/{$add[3]}, 
			//			/*5*/{$add[3]}, /*6*/-1, /*7*/-1, /*8*/{$add[2]} )";
					
					$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$mov_header_id}, {$add[1]}, {$add[3]}, {$add[3]}, 
								-1, -1, {$add[2]}, 5 )";
					$exc = $link->query( $sql) or die( "Error al insertar el detalle del movimiento de almacen 2 : {$link->error}" );	
					
					$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
								SET ajustado = '1' WHERE id_diferencia_inventario = {$add[0]}";
					$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 2 : {$link->error}" );			
				}
			}
		}

		$ok_array = explode( '|', $addition );
		foreach ( $ok_array as $key => $ok ) {
			if( $ok[0] != '' && $ok[0] != null ){
				$ok = explode( '~', $ok );
				$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
							SET ajustado = '1' WHERE id_diferencia_inventario = {$ok[0]}";
				$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 3 : {$link->error}" );			
			}
		}

		$link->autocommit( true );

		$resp = '<h5 style="color : green;">Ajuste de inventario guardado exitosamente!</h5>';
		$resp .= '<div class="row">';
			$resp .= '<div class="col-2"></div>';
			$resp .= '<div class="col-8">';
				$resp .= '<button type="button" class="btn btn-success" onclick="location.reload();">';
					$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		return $resp;
	}

	function seekByName( $barcode, $link ){
		$barcode_array = explode(' ', $barcode );
		$condition = " OR (";
		foreach ($barcode_array as $key => $barcode_txt ) {
			$condition .= ( $condition == ' OR (' ? '' : ' AND' );
			$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
		}
		$condition .= " )";
		$sql = "SELECT
				pp.id_producto AS product_id,
				CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>' ) AS name
			FROM ec_productos p
			LEFT JOIN ec_proveedor_producto pp
			ON pp.id_producto = p.id_productos
			WHERE p.muestra_paleta = 0
			AND p.es_maquilado = 0
			AND p.habilitado = 1
			AND ( pp.clave_proveedor LIKE '%{$barcode}%'
			{$condition} ) AND pp.id_proveedor_producto IS NOT NULL
			GROUP BY p.id_productos";
		$stm_name = $link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / modelo : {$link->error}" );
		if( $stm_name->num_rows <= 0 ){
			return 'exception|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
			. '<div class="row"><div class="col-2"></div><div class="col-8">'
			. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">Aceptar</button></div><br/><br/>';
		}

		$resp = "seeker|";
		while ( $row_name = $stm_name->fetch_assoc() ) {
			$resp .= "<div class=\"group_card\" onclick=\"setProductByName( {$row_name['product_id']} );\">";
				$resp .= "<p>{$row_name['name']}</p>";
			$resp .= "</div>";
		}
		//echo $resp;
		return $resp;
	}
	function getOptionsByProductId( $product_id, $link ){
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.clave_proveedor AS provider_clue,
					pp.piezas_presentacion_cluces AS pack_pieces,
					pp.presentacion_caja AS box_pieces,
					ipp.inventario AS inventory,
					pp.codigo_barras_pieza_1 AS piece_barcode_1
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_producto = pp.id_producto 
				AND ipp.id_proveedor_producto = pp.id_proveedor_producto
				WHERE pp.id_producto = {$product_id}
				AND ipp.id_almacen = 1";
		$stm_name = $link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$link->error}" ); 
		$resp = "<div class=\"row\">";
			//$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-12\">";
				$resp .= "<h5>Seleccione el modelo del producto : </h5>";
				$resp .= "<table class=\"table table-bordered table-striped table_70\">";
				$resp .= "<thead>
							<tr>
								<th>Modelo</th>
								<th>Inventario</th>
								<th>Pzs x caja</th>
								<th>Pzs x paquete</th>
								<th>Seleccionar</th>
							</tr>
						</thead><tbody id=\"model_by_name_list\" >";
				$counter = 0;
				while( $row_name = $stm_name->fetch_assoc() ){
					$resp .= "<tr>";
						$resp .= "<td id=\"p_m_1_{$counter}\" align=\"center\">{$row_name['provider_clue']}</td>";
						$resp .= "<td id=\"p_m_2_{$counter}\" align=\"center\">{$row_name['inventory']}</td>";
						$resp .= "<td id=\"p_m_3_{$counter}\" align=\"center\">{$row_name['box_pieces']}</td>";
						$resp .= "<td id=\"p_m_4_{$counter}\" align=\"center\">{$row_name['pack_pieces']}</td>";
						$resp .= "<td align=\"center\"><input type=\"radio\" id=\"p_m_5_{$counter}\" 
							value=\"{$row_name['piece_barcode_1']}\"  name=\"search_by_name_selection\"></td>";
					$resp .= "</tr>";
					$counter ++;
				}
				$resp .= "</tbody></table>";
			$resp .= "</div>";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">
						<button class=\"btn btn-success form-control\" onclick=\"setProductModel();\">
							<i class=\"icon-ok-circle\">Continuar</i>
						</button><br><br>
						<button class=\"btn btn-danger form-control\"
							onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
							<i class=\"icon-ok-circle\">Cancelar</i>
						</button>
					</div>";
		$resp .= "</div>";
		return $resp;
	}

	function getBarcodesTypes( $link ){
		$sql = "SELECT 
					omitir_codigos_barras_unicos AS skip_unique_barcodes
				FROM sys_configuracion_sistema";
		$stm = $link->query( $sql ) or die( "Error al consultar configuración de códigos de barras : {$link->error}" );
		$row = $stm->fetch_assoc();
		return "<input type=\"hidden\" id=\"skip_unique_barcodes\" value=\"{$row['skip_unique_barcodes']}\">";
	}

	function seekRecivedProducts( $txt, $transfers, $link ){
		$array_txt = explode(' ', $txt );

		$condition = " AND ( (";
		foreach ($array_txt as $key => $word) {
			$condition .= ( $key > 0 ? ' AND' : '' );
			$condition .= " p.nombre LIKE '%{$word}%'";
		}
		$condition .= " ) OR p.clave LIKE '%{$txt}%' OR p.orden_lista LIKE '%{$txt}%' 
					OR pp.codigo_barras_pieza_1 = '{$txt}'
					OR pp.codigo_barras_pieza_2 = '{$txt}'
					OR pp.codigo_barras_pieza_3 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_1  = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
					OR pp.codigo_barras_caja_1 = '{$txt}'
					OR pp.codigo_barras_caja_2 = '{$txt}')";

		$sql = "SELECT
					CONCAT( p.nombre, 
						' <b>(Modelo ' , pp.clave_proveedor , ')</b> <b>(', 
						SUM( ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
							+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces )
							+ tvu.cantidad_piezas_validadas
						),
						' piezas validadas)</b>' 
					)AS name,
					tvu.id_producto AS product_id,
					tvu.id_proveedor_producto
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp
				ON tp.id_transferencia_producto = tvu.id_transferencia_producto
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				WHERE tp.id_transferencia IN( {$transfers} )
				AND p.muestra_paleta = 0
				AND p.es_maquilado = 0
				AND p.habilitado = 1 
				{$condition}
				GROUP BY tp.id_proveedor_producto";
		//echo $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar coincidencias de productos :recibidos {$link->error} {$sql}" );
		if( $stm->num_rows <= 0 ){
			return "<div class=\"response_recived\">Sin coincidencias!</div>";
		}
		while( $r = $stm->fetch_assoc() ){
			$resp .= "<div class=\"response_recived\" onclick=\"load_product_validation_detail( this, {$r['product_id']} );\">{$r['name']}</div>";
		}
		return $resp;
	}

	function loadProductValidationDetail( $product_id, $transfers, $link ){
		
		$sql = "SELECT
					/*tvu.id_transferencia_validacion AS transfer_validation_id,*/
					p.id_productos AS product_id,
					CONCAT( p.nombre, ' ( MODELO : <b>', pp.clave_proveedor, '</b> )' ) AS name,
					t.id_transferencia AS transfer,
					IF(	tvu.cantidad_cajas_validadas > 0, 
						CONCAT( tvu.cantidad_cajas_validadas, ' caja', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
						IF( tvu.cantidad_paquetes_validados > 0,
							CONCAT( tvu.cantidad_paquetes_validados, ' paquete', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
							CONCAT( tvu.cantidad_piezas_validadas, ' pieza', IF( tvu.cantidad_piezas_validadas > 1, 's', '' ))
						)
					) AS recived
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp 
				ON tvu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tvu.id_producto = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tvu.id_proveedor_producto = pp.id_proveedor_producto
				WHERE t.id_transferencia IN( {$transfers} )
				/*AND tvu.id_usuario = '{$user}'*/
				AND tvu.id_producto = {$product_id}
				GROUP BY tvu.id_transferencia_validacion
				ORDER BY tvu.id_transferencia_validacion DESC";
		$stm = $link->query( $sql ) or die( "Error al consultar detalles de surtimiento de producto : {$link->error}" ); 
		//return $sql;
		return buildLastValidations( $stm );
	}

	function makeTransfersGroup( $transfers, $user, $link ){
		$link->autocommit( false );
		$block_id = null;
	//agrupa y consulta las transferencias que ya están en un grupo
		$sql = "SELECT 
					t.id_transferencia AS transfer_id,
					btvd.id_bloque_transferencia_validacion AS validation_transfer_block_id
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				WHERE btvd.id_transferencia IS NULL
				AND t.id_transferencia IN( {$transfers} )";
		$stm_block = $link->query( $sql ) or die( "error|Error al consultar bloques : {$link->error}" );
		if( $stm_block->num_rows > 0 ){
		//inserta cabecera del bloque
			$sql = "INSERT INTO ec_bloques_transferencias_validacion ( id_bloque_transferencia_validacion, fecha_alta, validado )
			VALUES( NULL, NOW(), 0 )";	
			$stm = $link->query( $sql ) or die( "error|Error al insertar cabecera del bloque : {$link->error}" );
			$header_id = $link->insert_id;
			$block_id = $header_id;
			$arr_transfers = explode( ',', $transfers );
		//inserta detalles del bloque
			while( $row = $stm_block->fetch_assoc() ){
				//foreach ( $arr_transfers as $key => $transfer ) {
				$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle ( id_bloque_transferencia_validacion_detalle, id_bloque_transferencia_validacion, 
				id_transferencia, fecha_alta, invalidado ) VALUES ( NULL, {$header_id}, {$row['transfer_id']}, NOW(), 0 )";
				$stm = $link->query( $sql ) or die("error|Error al insertar el detalle del bloque de validación : {$link->error}");
			}
		}

/*implementacion Oscar 2023 para error de no cambia status a validando*/
		$sql = "UPDATE ec_transferencias SET id_estado = 6 WHERE id_transferencia IN( {$transfers} )";
		$stm = $link->query( $sql ) or die("error|Error al actualizar transferencias al status de validación : {$link->error}");				
/*fin de implementacion Oscar 2023*/

/*implementacion Oscar 2023 para eliminar el bloqueo de la validacion*/
	//verifica si el usuario tiene el permiso de editar bloques de validacion
		$sql = "SELECT 
					IF( perm.ver = 1 OR perm.modificar = 1 OR perm.eliminar = 1 OR perm.nuevo = 1 
						OR perm.imprimir = 1 OR perm.generar = 1, 1, 0 ) AS edit_permission
				FROM sys_permisos perm
				LEFT JOIN sys_users_perfiles up
				ON perm.id_perfil = up.id_perfil 
				LEFT JOIN sys_users u
				ON up.id_perfil = u.tipo_perfil
				WHERE perm.id_menu = 250
				AND u.id_usuario = {$user}";
		$stm = $link->query( $sql ) or die( "Error al consultar si el usuario tiene el permiso para desbloquear bloque de validacion : {$link->error}" );
		$row = $stm->fetch_assoc();
		if( $row['edit_permission'] == 1 ){
		//consulta el id del bloque 
			$sql = "SELECT 
					btvd.id_bloque_transferencia_validacion AS validation_transfer_block_id
				FROM ec_bloques_transferencias_validacion_detalle btvd
				LEFT JOIN ec_transferencias t
				ON btvd.id_transferencia = t.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				GROUP BY btvd.id_bloque_transferencia_validacion";
			$stm = $link->query( $sql ) or die( "Error al consultar el bloque de validacion : {$link->error}" );
			$row = $stm->fetch_assoc();
			$block_id = $row['validation_transfer_block_id'];
			$sql = "UPDATE ec_bloques_transferencias_validacion 
					SET bloqueado = '0'
					WHERE id_bloque_transferencia_validacion = {$block_id}";
			$stm = $link->query( $sql ) or die( "Error al desbloquear bloque de validacion : {$sql} {$link->error}" );
		//verifica si tiene un bloque de recepcion enlazado
			$sql = "SELECT
						btrd.id_bloque_transferencia_recepcion AS reception_block_id
					FROM ec_bloques_transferencias_recepcion_detalle btrd
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					WHERE btvd.id_bloque_transferencia_validacion = '{$block_id}'";
			$stm = $link->query( $sql ) or die( "Error al consultar si la validacion esta enlazada a una recepcion : {$link->error}" );
//$link->autocommit( false );
			if( $stm->num_rows > 0 ){
				while( $row = $stm->fetch_assoc() ){
					$sql = "UPDATE ec_bloques_transferencias_recepcion 
								SET bloqueado = '0' 
							WHERE id_bloque_transferencia_recepcion = '{$row['reception_block_id']}'";
					$update = $link->query( $sql ) or die( "Error al desbloquear bloque de validación : {$link->error}" );
				}
			}
		}
/**/
	///
		$link->autocommit( true );

	//implementacion Oscar 2023 para hacer sesion principal en el bloque de validacion
		$sql = "SELECT 
					id_sesion_principal AS principal_session
				FROM ec_bloques_transferencias_validacion
				WHERE id_bloque_transferencia_validacion = '{$block_id}'";
		$stm = $link->query( $sql ) or die( "Error al consultar la sesion principal del bloque de validación : {$link->error}" );
		$block_row = $stm->fetch_assoc();
		//die( $sql );
		return 'ok|' . $block_id . '|' . ( ( $block_row['principal_session'] != 0 && $block_row['principal_session'] != '' ) || $block_id == '' ? 0 : 1 );//( $block_row['principal_session'] == null ? 0 : 1 );
	}

	function  getPreviousRemoveTransferToValidation( $transfer_id, $reset_all = null, $link ){
		$resp = "";
		$resp .= "<div class=\"col-12\">";
		if( $reset_all != null ){
			$resp .= "<p align=\"justify\" style=\"color:red;\">Se va a resetear la validación de la transferencia, si ya hay productos que tenia por enviar, regresalos a Matriz</p>";
		}else{
			$change_products = "";
			$no_exists_products = "";
			$no_exists_products_provider = "";
		//consulta el bloque al que corresponde
			$sql = "SELECT 
						btvd.id_bloque_transferencia_validacion AS block_id,
						btvd.id_bloque_transferencia_validacion_detalle AS block_detail_id
					FROM ec_bloques_transferencias_validacion_detalle btvd
					WHERE btvd.id_transferencia = {$transfer_id}";
			$stm_1 = $link->query( $sql ) or die( "Error al consultar el bloque de la validadcion de la transferencia : {$link->error}" );
			$row_block = $stm_1->fetch_assoc();
		//verifica si fue recibido algo en esta transferencia
			$sql = "SELECT 
						CONCAT( p.nombre , ' <b>(MODELO : ', pp.clave_proveedor, ')</b> ' ) AS name,
						tp.id_transferencia_producto AS transfer_product_id,
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						tp.cantidad_cajas_validacion AS validated_boxes,
						tp.cantidad_paquetes_validacion AS validated_packs,
						tp.cantidad_piezas_validacion AS validated_pieces,
						tp.total_piezas_validacion AS validated_total
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia IN( {$transfer_id} )
					AND ( tp.cantidad_cajas_validacion > 0 OR tp.cantidad_paquetes_validacion > 0
						OR tp.cantidad_piezas_validacion > 0 OR tp.total_piezas_validacion > 0 )";
			$stm_2 = $link->query( $sql ) or die( "Error al consultar los detalles que fueroin validados en la transferencia por quitar : {$link->error}"  );
			if( $stm_2->num_rows > 0 ){
				while( $row_validated = $stm_2->fetch_assoc() ){
					$sql = "SELECT 
								tp.id_transferencia_producto
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON tp.id_transferencia = btvd.id_transferencia 
							LEFT JOIN ec_bloques_transferencias_validacion btv
							ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
							WHERE btvd.id_transferencia = {$row_block['block_id']}
							AND tp.id_transferencia NOT IN( {$transfer_id} )
							AND tp.id_proveedor_producto = {$row_validated['transfer_product_id']}
							AND tp.id_producto_or = {$row_validated['product_id']}";
					$stm_3 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al proveedor-producto : {$link->error}" );
					if( $stm3->num_rows <= 0 ){
					//verifica si el producto existe en alguna transferencia del bloque
						$sql = "SELECT 
									tp.id_transferencia_producto
								FROM ec_transferencia_productos tp
								LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
								ON tp.id_transferencia = btvd.id_transferencia 
								LEFT JOIN ec_bloques_transferencias_validacion btv
								ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
								WHERE btvd.id_transferencia = {$row_block['block_id']}
								AND tp.id_transferencia NOT IN( {$transfer_id} )
								AND tp.id_producto_or = {$row_validated['product_id']}";
						$stm_4 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al producto : {$link->error}" );
						if( $stm_4->num_rows <= 0 ){
						//crea el registro informativo de asignación
							$no_exists_products .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
								$no_exists_products .= "<div class=\"row\">";
									$no_exists_products .= "<div class=\"col-9\">";				
										$no_exists_products .= "<p><b class=\"orange\">Producto : </b> {$row_validated['name']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-3\">";	
										$no_exists_products .= "<button type=\"button\" class=\"btn btn-danger\"
																onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																	<i class=\"icon-cancel-alt-filled\"></i>
															</button>";	
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Cajas : </b>{$row_validated['validated_boxes']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated['validated_packs']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Piezas : </b>{$row_validated['validated_pieces']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Total : </b>{$row_validated['validated_total']}</p>";
									$no_exists_products .= "</div>";	
								$no_exists_products .= "</div>";
									//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
							$no_exists_products .= "</div>";
						}else{
							$row_validated_1 = $stm_4->fetch_assoc();
						//crea el registro informativo de asignación
							$no_exists_products_provider .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
								$no_exists_products_provider .= "<div class=\"row\">";
									$no_exists_products_provider .= "<div class=\"col-9\">";				
										$no_exists_products_provider .= "<p><b class=\"orange\">Producto : </b> {$row_validated_1['name']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products .= "<div class=\"col-3\">";		
										$no_exists_products_provider .= "<button type=\"button\" class=\"btn btn-danger\"
																onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																	<i class=\"icon-cancel-alt-filled\"></i>
															</button>";	
									$no_exists_products .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Cajas : </b>{$row_validated_1['validated_boxes']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated_1['validated_packs']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Piezas : </b>{$row_validated_1['validated_pieces']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Total : </b>{$row_validated_1['validated_total']}</p>";
									$no_exists_products_provider .= "</div>";	
								$no_exists_products_provider .= "</div>";
									//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
							$no_exists_products_provider .= "</div>";
						}
					}else{
					//crea el registro informativo de asignación
						$change_products .= 'here';
						$change_products .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
							$change_products .= "<div class=\"row\">";
								$change_products .= "<div class=\"col-9\">";				
									$change_products .= "<p><b class=\"orange\">Producto : </b> {$row_validated['name']}</p>";
								$change_products .= "</div>";		
								$change_products .= "<div class=\"col-3\">";		
									$change_products .= "<button type=\"button\" class=\"btn btn-danger\"
															onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																<i class=\"icon-cancel-alt-filled\"></i>
														</button>";	
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Cajas : </b>{$row_validated['validated_boxes']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated['validated_packs']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Piezas : </b>{$row_validated['validated_pieces']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Total : </b>{$row_validated['validated_total']}</p>";
								$change_products .= "</div>";	
							$change_products .= "</div>";
								//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
						$change_products .= "</div>";
					}
					//}
				}
				if( $change_products != '' ){
					$resp .= "<h5 class=\"orange\" style=\"color : green;\">Estos productos ya fueron validados y serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $change_products;
				}
				if( $no_exists_products_provider != '' ){
					$resp .= "<h5 class=\"orange\">Estos productos ya fueron validados, el modelo no fue pedido y serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $no_exists_products_provider;
				}
				if( $no_exists_products != '' ){
					$resp .= "<h5 class=\"orange\" style=\"color : red;\">Estos productos ya fueron validados y no  estaban contemplados en las transferencias serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $no_exists_products;
				}
			}else{
				$resp .= "<p class=\"green\">No se validó ningún producto en esta transferencia <i class=\"icon-ok-circle\"></i></p>";
			}
		}
			
		$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">
						<label for=\"manager_password\">Pida al encargado que ingrese su contraseña <b class=\"orange\">*</b> : </label>
						<input type=\"password\" class=\"form-control\" id=\"manager_password\"><br>
						<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"confirm_remove_transfer_block();\">
							<i class=\"icon-ok-circle\">Aceptar y continuar</i>
						</button><br><br>
						<button type=\"button\" class=\"btn btn-danger form-control\" onclick=\"close_emergent( )\">
							<i class=\"icon-cancel-circled\">Cancelar</i>
						</button><br><br><br><br>
					</div>";
		$resp .= "</div>";
			//$resp .= "<div class=\"col-1\"></div>";
		
		return $resp;
	}

	function removeTransferBlockDetail( $transfer_id, $transfer_product_id, $link ){

		$link->autocommit( false );

		/*$sql = "SELECT 
					id_bloque_transferencia_validacion 
				FROM ec_bloques_transferencias_validacion_detalle
				WHERE id_transferencia = {$transfer_id}";	
		$stm = $link->query( $sql ) or die( "Error al consultar id de bloque de validación : " . $link->error );*/
	//resetea toda la validacion
		/*$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE id_bloque_transferencia_validacion = '{$details_nums['block_id']}'";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos del bloque de transferencia : {$link->error}");*/
	//elimina los códigos único
		$sql = "DELETE FROM ec_transferencia_codigos_unicos 
				WHERE id_transferencia_validacion IN ( SELECT id_transferencia_validacion FROM ec_transferencias_validacion_usuarios WHERE id_transferencia_producto = {$transfer_product_id} )";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos de validación de transferencia : {$link->error}" );
		
		$sql = "DELETE FROM ec_transferencias_validacion_usuarios 
				WHERE id_transferencia_producto IN( {$transfer_product_id} )";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalles de validación de transferencia : {$link->error}") ;
		
		$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_cajas_validacion = 0,
						cantidad_paquetes_validacion = 0,
						cantidad_piezas_validacion = 0,
						total_piezas_validacion = 0
				WHERE id_transferencia_producto = {$transfer_product_id}";
		$stm_update = $link->query( $sql ) or die( "Error al poner en cero las piezas validadas en el detalle de transferencia : {$link->error}" );

		$link->autocommit( true );
		$resp = "<div>";
			$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">";
				$resp .= "<button type=\"button\" class=\"btn btn-succes\" onclick=\"close_emergent_2();\">
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>";
			$resp .= "</div>";
			$resp .= "</div>";
		$resp .= "</div>";
		return $resp;
	}

	function removeTransferBlock( $transfer_id, $link ){
		$resp = "";
		$link->autocommit( false );
		
		$sql = "SELECT 
					COUNT( btvd.id_bloque_transferencia_validacion_detalle ) AS counter,
					btv.id_bloque_transferencia_validacion AS block_id
				FROM ec_bloques_transferencias_validacion_detalle btvd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				WHERE btv.id_bloque_transferencia_validacion 
				IN ( SELECT 
						id_bloque_transferencia_validacion 
					FROM ec_bloques_transferencias_validacion_detalle
					WHERE id_transferencia = {$transfer_id}
				)";
		$stm = $link->query( $sql ) or die( "Error al consultar cuantas transferencias conforman el bloque : {$link->error}");
		$details_nums = $stm->fetch_assoc();
		$num_block_details = $details_nums['counter'];
		if( $num_block_details <= 1 ){
		//resetea toda la validacion
			$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE id_bloque_transferencia_validacion = '{$details_nums['block_id']}'";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos del bloque de transferencia : {$link->error}");
			$sql = "DELETE FROM ec_transferencias_validacion_usuarios 
					WHERE id_transferencia_producto IN( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE id_transferencia = {$transfer_id})";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalles de validación de transferencia : {$link->error}");
			
			$sql = "DELETE FROM ec_bloques_transferencias_validacion_detalle WHERE id_bloque_transferencia_validacion = {$details_nums['block_id']}";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalle de bloque de transferencia : {$link->error}");
			
			$sql = "DELETE FROM ec_bloques_transferencias_validacion WHERE id_bloque_transferencia_validacion = {$details_nums['block_id']}";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar bloque de transferencia : {$link->error}");
			
			$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_cajas_validacion = 0,
						cantidad_paquetes_validacion = 0,
						cantidad_piezas_validacion = 0,
						total_piezas_validacion = 0
					WHERE id_transferencia = {$transfer_id}";
			$stm_update = $link->query( $sql ) or die( "Error al resetear detalles de validación de productos de transferencia : {$link->error}");
			$sql = "UPDATE ec_transferencias SET id_estado = 4 WHERE id_transferencia = {$transfer_id} ";
			$stm_update = $link->query( $sql ) or die( "Error al actualizar la transferencia a Surtiendo y revisando : {$link->error}");
			
			$resp .= "<p align=\"center\" style=\"color : green;\">";
				$resp .= "La validación de la Transferencia fue reiniciada exitosamente!";
			$resp .= "</p>";

		}else{
		//si el bloque es de más de una transferencia
			$change_products = "";
			$no_exists_products = "";
			$no_exists_products_provider = "";
		//consulta el bloque al que corresponde
			$sql = "SELECT 
						btvd.id_bloque_transferencia_validacion AS block_id,
						btvd.id_bloque_transferencia_validacion_detalle AS block_detail_id
					FROM ec_bloques_transferencias_validacion_detalle btvd
					WHERE btvd.id_transferencia = {$transfer_id}";
			$stm_1 = $link->query( $sql ) or die( "Error al consultar el bloque de la validadcion de la transferencia : {$link->error}" );
			$row_block = $stm_1->fetch_assoc();
		//verifica si fue validado algo en la transferencia
			$sql = "SELECT 
						tp.id_transferencia_producto AS transfer_product_id,
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						tp.cantidad_cajas_validacion AS validated_boxes,
						tp.cantidad_paquetes_validacion AS validated_packs,
						tp.cantidad_piezas_validacion AS validated_pieces,
						tp.total_piezas_validacion AS validated_total
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia IN( {$transfer_id} )
					AND ( tp.cantidad_cajas_validacion > 0 OR tp.cantidad_paquetes_validacion > 0
					OR tp.cantidad_piezas_validacion > 0 OR tp.total_piezas_validacion > 0 )";
			$stm_2 = $link->query( $sql ) or die( "Error al consultar los detalles que fueron validados en la transferencia por quitar : {$link->error}"  );
			if( $stm_2->num_rows > 0 ){
				while( $row_validated = $stm_2->fetch_assoc() ){
					$sql = "SELECT 
								tp.id_transferencia_producto AS transfer_product_id
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON tp.id_transferencia = btvd.id_transferencia 
							LEFT JOIN ec_bloques_transferencias_validacion btv
							ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
							WHERE btvd.id_bloque_transferencia_validacion = {$row_block['block_id']}
							AND tp.id_transferencia NOT IN( {$transfer_id} )
							AND tp.id_proveedor_producto = {$row_validated['product_provider_id']}
							AND tp.id_producto_or = {$row_validated['product_id']}";
				//$resp .= $sql;
					$stm_3 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al proveedor-producto : {$link->error}" );
					
					if( $stm_3->num_rows <= 0 ){
					//verifica si el producto existe en alguna transferencia del bloque
						$sql = "SELECT 
									tp.id_transferencia_producto
								FROM ec_transferencia_productos tp
								LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
								ON tp.id_transferencia = btvd.id_transferencia 
								LEFT JOIN ec_bloques_transferencias_validacion btv
								ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
								WHERE btvd.id_transferencia = {$row_block['block_id']}
								AND tp.id_transferencia NOT IN( {$transfer_id} )
								AND tp.id_producto_or = {$row_validated['product_id']}";
						$stm_4 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al producto : {$link->error}" );
						if( $stm_4->num_rows <= 0 ){
							//$resp .= 'here_1 ';
						//crea el registro informativo de asignación
						}else{
							//$resp .= 'here_2 ';
							$row_validated_1 = $stm_4->fetch_assoc();
						}
					}else{
					//crea el registro informativo de asignación
						//elimina los códigos único
					//$row_validated['']
						$transfer_detail_destinity = $stm_3->fetch_assoc();
					//consulta los códigos únicos						
						$sql = "UPDATE ec_transferencias_validacion_usuarios 
									SET id_transferencia_producto = {$transfer_detail_destinity['transfer_product_id']}
								WHERE id_transferencia_producto = {$row_validated['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Error al actualizar los códigos únicos por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;
						$sql = "UPDATE ec_transferencia_productos SET 
										cantidad_cajas_validacion = ( cantidad_cajas_validacion + {$row_validated['validated_boxes']} ),
										cantidad_paquetes_validacion = ( cantidad_paquetes_validacion + {$row_validated['validated_packs']} ),
										cantidad_piezas_validacion = ( cantidad_paquetes_validacion + {$row_validated['validated_pieces']} ),
										total_piezas_validacion = ( total_piezas_validacion + {$row_validated['validated_total']} )
								WHERE id_transferencia_producto = {$transfer_detail_destinity['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Error al actualizar las piezas validadas en el detalle por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;

						$sql = "UPDATE ec_transferencia_productos SET 
										cantidad_cajas_validacion = 0,
										cantidad_paquetes_validacion = 0,
										cantidad_piezas_validacion = 0,
										total_piezas_validacion = 0
								WHERE id_transferencia_producto = {$row_validated['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Error al poner en cero las piezas validadas en el detalle por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;
					
					}
				}//fin de while
				$sql = "DELETE FROM ec_bloques_transferencias_validacion_detalle WHERE id_transferencia = {$transfer_id}";
				$stm_5 = $link->query( $sql ) or die( "Error al eliminar la transferncia del bloque de validación : {$link->error}" );
			}
		}
		$link->autocommit( true );
		$resp .= "<p align=\"center\">";
			$resp .= "<button class=\"btn btn-success\" onclick=\"location.reload();\">";
				$resp .= "<i class=\"icon-ok-circle\">Aceptar y recargar pantalla</i>";
			$resp .= "</button>";
		$resp .= "</p>";
		return $resp;
	}
?>
