<?php
/*Version con insercion de movimientos por Procedure (2024-08-05)*/
	if( isset( $_GET['fl'] ) ){
		include( '../../../../../config.inc.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );

		$action = $_GET['fl'];

		switch ( $action ) {
			/*case 'getPermission_':
				echo 
			break;*/

			case 'loadLastReceptions' :
				echo loadLastReceptions( $_GET['transfers'], $user_id, $sucursal_id, $link );
			break;

			case 'getReceptionResumen' : 
				echo getReceptionResumen( $_GET['type'], $_GET['transfers'], $_GET['reception_block_id'], $link );
			break;
			case 'insertNewProductReception' : 
				echo insertNewProductReception( $_GET['transfers'], $_GET['p_id'], $_GET['p_p_id'], 
						$_GET['box'], $_GET['pack'], $_GET['piece'], $link );
			break;

			case 'getReceptionProductDetail' :
				echo getReceptionProductDetail( $_GET['transfers'], $_GET['p_id'], $_GET['p_p_id'], $user_id, $link );
			break;

			case 'validateManagerPassword' : 
				echo validateManagerPassword( $_GET['pass'], $sucursal_id, $link );
			break;

			case 'getProductResolution' :
				echo getProductResolution( $_GET['t_p'], $_GET['p_id'], $_GET['type'], $link, 
					$_GET['difference'], $user_id, $sucursal_id, $_GET['transfers'], $_GET['reception_block_id'] );
			break;

			case 'getOptionsByProductId' :
				echo getOptionsByProductId( $_GET['product_id'], $link );
			break;

			case 'getTransfersToCorrection':
				echo getTransfersToCorrection( $_GET['sucursal_id'], $link );
			break;

			case 'getTransfersToReceive':
				echo getTransfersToReceive( $sucursal_id, $perfil_usuario, $link );
			break;

			case 'setTransferToReceive' :
				$transfers_ids = ( isset( $_GET['transfers_ids'] ) ? $_GET['transfers_ids'] : '');
				$validation_blocks = ( isset( $_GET['validation_blocks'] ) ? $_GET['validation_blocks'] : '');
				$reception_blocks = ( isset( $_GET['reception_blocks'] ) ? $_GET['reception_blocks'] : '');
				echo setTransferToReceive( $transfers_ids, $validation_blocks, $reception_blocks, $sucursal_id, 
					$user_id, $_GET['new_transfers'], $link );
			break;

			case 'showUnicCodesPendingToRecive' :
				echo showUnicCodesPendingToRecive( $_GET['validations_blocks'], $link );
			break;

			case 'receiveUniqueCode': 
				echo receiveUniqueCode( $_GET['p_k'], $link );
			break;

			case 'getMessageToAddTransfer' :
				echo getMessageToAddTransfer( $_GET['transfers'], $_GET['transfer_to_add'], 
					$_GET['reception_block_id'], $_GET['reception_token'], $user_id, $link );
			break;

			case 'finishTransfersReception' : 
				
		//die( "{$_GET['transfers']}, {$_GET['reception_block_id']}, {$user_id}, {$sucursal_id}" );
				echo finishTransfersReception( $_GET['transfers'], $_GET['reception_block_id'], $user_id, $sucursal_id, $link );
			break;

			case 'addTransferBlock' :
				echo addTransferBlock(  );
			break;

			case 'getResumeCounterForms' :
//die( 'here' );
				echo getResumeCounterForms( $_GET['reception_block_id'], $_GET['type'], $sucursal_id, $link );
			break;

			case 'getBlocksInResolution' :
				echo getBlocksInResolution( $sucursal_id, null, $link );
			break;
		/*implementacion Oscar 2023 para remoover el codigo unico de la resolucion*/
			case 'remove_resolution_unique_code' :
				echo remove_resolution_unique_code( $_GET['resolution_row_id'], $_GET['unique_code'], $link );
			break;
		/*fin de cambio Oscar 2023*/
		/*implementacion OScar 2023 para verificar que la transferencia este como terminada antes de continuar con la resolucion*/
			case 'checkTransferStatus' : 
				echo checkTransferStatus( $_GET['transfer_block_id'], $link );
			break;
			default:
				die( "Permission Denied on {$action}!" );
			break;

			case 'validate_transfers_are_completed' : 
				echo validate_transfers_are_completed( $_GET['transfers'], $link );
			break;

			case 'create_reception_token' : 
				echo create_reception_token( $user_id, $_GET['reception_block_id'], $link );
			break;
			case 'getUpdateReceptionBlock' : 
				echo getUpdateReceptionBlock( $_GET['reception_block_id'], $_GET['reception_token'], $user_id, $link );
			break;

			case 'removeUnicToken' : 
				echo removeUnicToken( $_GET['token'], $link );
			break;

			case 'validate_devices_sessions' :
				echo validate_devices_sessions( $_GET['current_block'], $_GET['reception_token'], $link );
			break;

			case 'close_reception_session' : 
				echo close_reception_session( $_GET['reception_token'], $link );
			break;
		//obtener bloques de validacion y transferencias apartir de un bloque de recepcion
			case 'getDataByBlock' : 
				$storeReceptionDb = new storeReceptionDb( $link );
				echo $storeReceptionDb->getDataByBlock( $_GET['reception_block_id'] );
			break;

			case 'seekTransfer': 
				echo getBlocksInResolution( $sucursal_id, $_GET['key'], $link );
			break;

			case 'validate_transfers_were_finished' :
				$storeReceptionDb = new storeReceptionDb( $link );
				echo $storeReceptionDb->validate_transfers_were_finished( $_GET['transfers'] );
			break;
		}
	}

	class storeReceptionDb
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function getDataByBlock( $reception_block_id ){
		//
			$resp = "ok|";
			$sql = "SELECT
						GROUP_CONCAT( 
							DISTINCT( btvd.id_bloque_transferencia_validacion ) 
							SEPARATOR ','
						) AS validation_blocks
					FROM ec_bloques_transferencias_recepcion_detalle btrd
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					WHERE btrd.id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los bloques de validacion : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resp .= "{$row['validation_blocks']}|";

			$sql = "SELECT
						GROUP_CONCAT( 
							DISTINCT( t.id_transferencia ) 
							SEPARATOR ','
						) AS transfers
					FROM ec_bloques_transferencias_validacion_detalle btvd
					LEFT JOIN ec_transferencias t
					ON t.id_transferencia = btvd.id_transferencia
					WHERE btvd.id_bloque_transferencia_validacion IN( {$row['validation_blocks']} )
					AND t.id_tipo NOT IN( 9, 12 )";	
			$stm = $this->link->query( $sql ) or die( "Error al consultar las transferencias : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resp .= "{$row['transfers']}";
			return $resp;
		}

		public function seekTransfer( $txt ){
			$sql = "SELECT 
						btr.id_bloque_transferencia_recepcion AS reception_block_id,
						GROUP_CONCAT( DISTINCT( t.id_transferencia ) SEPARATOR ',' ) AS transfers_ids,
						GROUP_CONCAT( DISTINCT( t.folio ) SEPARATOR ',' ) AS transfers_folios
					FROM ec_transferencias t
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON t.id_transferencia = btvd.id_transferencia
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
					WHERE ( t.folio LIKE '%{$txt}%'
					OR btrd.id_bloque_transferencia_recepcion = '{$txt}' )
					AND t.id_estado IN ( 8, 9 )
					AND t.id_tipo NOT IN ( 9,12 ) /*hasta aqui me quede Oscar 13-marzo-2023*/
					GROUP BY btr.id_bloque_transferencia_recepcion";
			$stm = $this->link->query( $sql );
		}

		public function validate_transfers_were_finished( $transfers ){
			$sql = "SELECT 
						id_estado
					FROM ec_transferencias
					WHERE id_transferencia IN( {$transfers} )
					AND id_estado = 9";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los status de las trnsferencias : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				return 'ok';
			}else{
				return "La(s) Transferencia(s) no han sido finalizadas y no puede realizarse el conteo, finalizalas y vuele a intentar";
			}	
		}
	}


	function close_reception_session( $reception_token, $link ){
		$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
					SET finalizada = '1' 
				WHERE token_unico_dispositivo = '{$reception_token}'";
		$stm = $link->query( $sql ) or die( "Error al finalizar la sesion de recepcion  : {$link->error}" );
		return 'ok';
	}

	function validate_devices_sessions( $current_block, $reception_token, $link ){
		$resp = "<h4 class=\"text-center\">Las siguientes sesiones de recepciones estan pendientes de finalizar : </h4>
			<table class=\"table\">
			<thead>
				<tr>
					<th>Usuario</th>
					<th>Token</th>
					<th>Fecha de inicio</th>
				</tr>
			</thead>
			<tbody>";
		$sql = "SELECT
					sdrt.token_unico_dispositivo AS unique_token,
					CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
					sdrt.fecha_sesion AS date_time
				FROM ec_sesiones_dispositivos_recepcion_transferencias sdrt
				LEFT JOIN sys_users u
				ON u.id_usuario = sdrt.id_usuario
				WHERE sdrt.id_bloque_recepcion = {$current_block}
				AND sdrt.token_unico_dispositivo != '{$reception_token}'
				AND sdrt.finalizada = 0";
		$stm = $link->query( $sql ) or die( "Error al consultar las sesiones de recepcion pendientes : {$link->error}" );
		if( $stm->num_rows == 0 ){
			return 'ok';
		}else{
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
					<td>{$row['user_name']}</td>
					<td>{$row['unique_token']}</td>
					<td>{$row['date_time']}</td>
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
		$sql = "DELETE FROM ec_sesiones_dispositivos_recepcion_transferencias 
				WHERE token_unico_dispositivo = '{$token}'";
		$stm = $link->query( $sql ) or die( "Error al eliminar el token : {$link->error}" );
		return 'ok|Token eliminado exitosamente!';
	}

	function create_reception_token( $user, $reception_block_id, $link ){
		$link->autocommit( false );
		//$reception_block_id  = 'NULL';
	//inserta la sesion de recepcion
		$sql = "INSERT INTO ec_sesiones_dispositivos_recepcion_transferencias( id_sesion_dispositivo_recepcion, 
			id_bloque_recepcion, id_usuario, fecha_sesion )
			VALUES ( NULL, {$reception_block_id}, {$user}, NOW() )";
		//die($sql);
		$stm = $link->query( $sql ) or die( "Error al insertar el registro de sesion de recepcion : {$link->error}" );
		$session_id = $link->insert_id;
	//generacion de token
		$sql = "SELECT 
					CONCAT( 'R', id_bloque_recepcion, '_', 
						DATE_FORMAT( fecha_sesion, '%Y%m%d' ), '_',
						DATE_FORMAT( fecha_sesion, '%H%i%s' ), '_',
						id_usuario, '_',
						id_sesion_dispositivo_recepcion
					) AS unic_token
				FROM ec_sesiones_dispositivos_recepcion_transferencias
				WHERE id_sesion_dispositivo_recepcion = {$session_id}";
		$stm = $link->query( $sql ) or die( "Error al general el token de sesion de recepcion : {$link->error}" );		
		$row = $stm->fetch_assoc();
		$unic_token = $row['unic_token'];
	//actualiza el token en la sesion
		$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
			SET token_unico_dispositivo = '{$unic_token}',
			fecha_modificacion = '0000-00-00 00:00:00'
			WHERE id_sesion_dispositivo_recepcion = {$session_id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar el token de la sesion de recepcion : {$link->error}" );
		$link->autocommit( true );
		return "ok|{$unic_token}|{$reception_block_id}";
	} 


	function validate_transfers_are_completed( $transfers, $link ){
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
		
		$sql = "SELECT
					t.folio AS transfer_folio,
					et.nombre AS status_name
				FROM ec_transferencias t
				LEFT JOIN ec_estatus_transferencia et
				ON t.id_estado = et.id_estatus
				WHERE t.id_transferencia IN( {$transfers} )
				AND t.id_estado < 7
				GROUP BY t.id_transferencia";
		$stm_2 = $link->query( $sql ) or die( "error|Error al validar que no haya transferencias pendientes de validar : {$link->error}" );
		
		if( $stm->num_rows <= 0 && $stm_2->num_rows <= 0 ){
			return "ok|ok";
		}else{
			$resp .= "<h4  class=\"text-center\">Las siguientes transferencias tienen registros pendientes de surtir :</h4>";
			$resp .= "<div class=\"row\"><table class=\"table table-bordered table-striped\">
						<thead>
							<tr>
								<th>Transferencia</th>
								<th>Productos Pendientes</th>
							</tr>
						</thead>
						<tbody>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
						<td>{$row['transfer_folio']}</td>
						<td>{$row['transfer_products']}</td>
					</tr>";
			}
			while ( $row_2 = $stm_2->fetch_assoc() ) {
				$resp .= "<tr>
						<td>{$row_2['transfer_folio']}</td>
						<td>{$row_2['status_name']}</td>
					</tr>";
			}
			$resp .= "</tbody>
					</table>
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

	function checkTransferStatus( $transfer_block_id, $link ){
		$sql = "SELECT 
					t.id_transferencia
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btrd.id_bloque_transferencia_recepcion = '{$transfer_block_id}'
				AND t.id_estado != 9";
		$stm = $link->query( $sql ) or die( "Error al consultar si hay alguna transferencia sin terminar en el bloque : {$link->error}" );
		//echo $sql;
		if( $stm->num_rows > 0 ){
			return "<div class=\"row\">
						<h4>Primero hay que finalizar las transferencias desde el apartado de <b>'Verificar'</b> antes de continuar!</h4>
						<div class=\"col-4\"></div>
						<div class=\"col-4\">
							<button
								class=\"btn btn-success form-control\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
		}else{
			return 'ok';
		}
	}
	
/*implementacion Oscar 2023 para remoover el codigo unico de la resolucion*/
	function remove_resolution_unique_code( $resolution_unique_code_id, $unique_code, $link ){
	//consulta el id de codigo unico en resolucion para sacar el registro de resolucion
		$sql = "SELECT 
					id_transferencia_codigo AS transfer_code_id,
					piezas_contenidas AS pieces_quantity,
					codigo_unico AS unique_code
				FROM ec_transferencia_codigos_unicos
				WHERE id_bloque_transferencia_resolucion = '{$resolution_unique_code_id}'
				AND codigo_unico = '{$unique_code}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el codigo unico relacionado al registro de resolucion : {$link->error}" );
		$row = $stm->fetch_assoc();
		$transfer_code_id = $row['transfer_code_id'];
		$resolution_pieces = $row['pieces_quantity'];
		$unique_code = $row['unique_code'];
	//Elimina e;l codigo unico
		$sql = "DELETE FROM ec_transferencia_codigos_unicos 
				WHERE id_transferencia_codigo = {$transfer_code_id}";
//echo $sql . "<br>";

		$stm = $link->query( $sql ) or die( "Error al eliminar el codigo unico de la resolución : {$link->error}" );
	//Elimina el registro de escaneo de resolucion
		$sql = "DELETE FROM ec_bloques_transferencias_resolucion_escaneos 
				WHERE codigo_unico = '{$unique_code}'
				AND id_bloque_transferencia_resolucion = {$resolution_unique_code_id}";
		$stm = $link->query( $sql ) or die( "Error al eliminar el escaneo de la resolucion : {$link->error}" );
	//actualiza la cantidad de piezas en resolucion
		$sql = "UPDATE ec_bloques_transferencias_resolucion 
					SET piezas_no_corresponden = ( piezas_no_corresponden - {$resolution_pieces} )
				WHERE id_bloque_transferencia_resolucion = {$resolution_unique_code_id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar las piezas en resolucion de la transferencia : {$link->error}" );
	//consulta si el registro tiene piezas en resolucion aun
		$sql = "SELECT
					piezas_faltantes AS pieces_missing,
					piezas_sobrantes AS pieces_excedent,
					piezas_no_corresponden AS pieces_does_not_correspond
				FROM ec_bloques_transferencias_resolucion
				WHERE id_bloque_transferencia_resolucion = {$resolution_unique_code_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar si aun existen piezas en resolucion : {$link->error}" );
		$row = $stm->fetch_assoc();
		if( $row['pieces_missing'] == 0 && $row['pieces_excedent'] == 0 && $row['pieces_does_not_correspond'] == 0 ){
		//Elimina el registro de resolucion
			$sql = "DELETE FROM ec_bloques_transferencias_resolucion WHERE id_bloque_transferencia_resolucion = {$resolution_unique_code_id}";
			$stm = $link->query( $sql ) or die( "Error al eliminar el registro de resolucion de transferencias : {$link->error}" );
		}			
		//$link->autocommit( true );
		return "ok|El codigo fue removido de la resolucion exitosamente.";
	}
/*fin de cambio Oscar 2023*/

	function getBlocksInResolution( $store_id, $key = null, $link ){
		$condition = "";
		if( $key != null ){
		//consulta el bloque de recepcion
			$sql = "SELECT 	
						DISTINCT( btrd.id_bloque_transferencia_recepcion ) AS reception_block_id
					FROM ec_bloques_transferencias_recepcion_detalle btrd
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_transferencias t 
					ON t.id_transferencia = btvd.id_transferencia 
					WHERE t.folio LIKE '%{$key}%'";
			$stm = $link->query( $sql )or die( "Error al consultar el bloque de la transferencia : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				$condition = " AND btrd.id_bloque_transferencia_recepcion = '{$row['reception_block_id']}'";
			}else{
				$condition = " AND btrd.id_bloque_transferencia_recepcion = ''";
			}
		}
		$sql = "SELECT 
					btrd.id_bloque_transferencia_recepcion AS transfer_recepcion_block_id,
					GROUP_CONCAT( DISTINCT( t.folio ) SEPARATOR '<br>' ) AS transfers,
					btr.recibido AS block_was_received,
					IF( t.id_estado = 9, 1, 0 ) AS transfer_has_received
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				LEFT JOIN ec_productos_resoluciones_tmp prt
				ON prt.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
				LEFT JOIN ec_bloques_transferencias_recepcion btr
				ON btrd.id_bloque_transferencia_recepcion = btr.id_bloque_transferencia_recepcion 
				WHERE t.id_sucursal_destino = {$store_id}
				/*AND ( ( btr.recibido = 0 AND t.id_estado IN ( 9 ) ) || t.id_estado IN ( 8 ) ) */
				AND t.id_estado IN ( 8, 9 )
				AND t.id_tipo NOT IN ( 9, 12 )
				{$condition}
				/*AND prt.resuelto = 0
				AND prt.id_producto_resolucion IS NOT NULL*/
				GROUP BY btrd.id_bloque_transferencia_recepcion
				ORDER BY btrd.id_bloque_transferencia_recepcion DESC";
//die( $sql );
		$resp = 'ok|';
		$stm = $link->query( $sql ) or die( "Error al consultar los bloques en resolución : {$link->error}" );
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= "<tr>
						<td class=\"text-center\">{$row['transfer_recepcion_block_id']}</td>
						<td class=\"text-center\">{$row['transfers']}</td>
						<td class=\"text-center\">
							<button
								type=\"button\"
								class=\"btn btn-warning" . ( $row['transfer_has_received'] == 1 ? " no_visible" : "" ). "\"
								onclick=\"setResolutionBlock( {$row['transfer_recepcion_block_id']}, true );\"
							>
								<i class=\"icon-right-big\"></i>
							</button>

							<button
								type=\"button\"
								class=\"btn btn-warning" . ( $row['transfer_has_received'] == 1 ? " _no_visible" : "" ). "\"
								onclick=\"setResolutionBlock( {$row['transfer_recepcion_block_id']}, false, true );\"
							>
								<i class=\"icon-eye\"></i>
							</button>
						</td>
						<td class=\"text-center\">
							<button
								type=\"button\"
								class=\"btn btn-warning" . ( $row['transfer_has_received'] == 0 ? " no_visible" : "" ) . "\"
								onclick=\"setResolutionBlock( {$row['transfer_recepcion_block_id']}, false, false );\"
							>
								<i class=\"icon-up-hand\"></i>
							</button>
						</td>
					</tr>";
		}
		return $resp;
	}

	function getResumeCounterForms( $reception_block_id, $type, $store_id, $link ){
	//consulta las transferencias del bloque
		$sql = "SELECT
					GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btrd.id_bloque_transferencia_recepcion = {$reception_block_id}";
	//	die( $sql );
		$stm = $link->query( $sql ) or die( "error|Error al consultar las transferencias del bloque de recepción : {$this->link->error} {$sql}" );
		$row_transfers = $stm->fetch_assoc();
		$stm = '';
		$prefix  = "";

		$sql = "SELECT 
					ax.transfer_resolution_id,
					ax.transfer_products_ids,
					ax.product_id,
					ax.product_name,
					ax.is_maquiled,
					ax.quantity AS missing_quantity,
					ax.excedent_quantity,
					ax.does_not_correspond_quantity,
					SUM( IF( md.id_movimiento_almacen_detalle IS NULL 
					OR alm.id_sucursal != {$store_id}
					OR alm.es_almacen != '1', 0, ( tm.afecta * md.cantidad ) ) ) AS productInventory
				FROM(
					SELECT
					GROUP_CONCAT( DISTINCT( tp.id_transferencia_producto ) SEPARATOR '/' ) AS transfer_products_ids,
					p.id_productos AS product_id,
					p.nombre AS product_name,
					(SELECT 
						IF( p.id_productos = id_producto OR p.id_productos = id_producto_ordigen, 1, 0  ) 
					FROM ec_productos_detalle
					WHERE id_producto = p.id_productos OR id_producto_ordigen = p.id_productos
					) AS is_maquiled,
					( SUM( tp.total_piezas_recibidas ) ) AS quantity,
					(SELECT
						IF(btr.id_bloque_transferencia_resolucion IS NULL, 
							'',
							GROUP_CONCAT( DISTINCT( btr.id_bloque_transferencia_resolucion ) SEPARATOR '/' )
						) 
					FROM ec_bloques_transferencias_resolucion btr
					WHERE btr.id_bloque_transferencia_recepcion = {$reception_block_id}
					AND btr.id_producto = p.id_productos
					) AS transfer_resolution_id,
					(SELECT
						IF(btr.id_bloque_transferencia_resolucion IS NULL, 
							0,
							SUM( btr.piezas_sobrantes )
						) 
					FROM ec_bloques_transferencias_resolucion btr
					WHERE btr.id_bloque_transferencia_recepcion = {$reception_block_id}
					AND btr.id_producto = p.id_productos
					) AS excedent_quantity,
					(SELECT
						IF(btr.id_bloque_transferencia_resolucion IS NULL, 
							0,
							SUM( btr.piezas_no_corresponden )
						) 
					FROM ec_bloques_transferencias_resolucion btr
					WHERE btr.id_bloque_transferencia_recepcion = {$reception_block_id}
					AND btr.id_producto = p.id_productos
					) AS does_not_correspond_quantity
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_transferencias t ON t.id_transferencia = tp.id_transferencia
				WHERE tp.total_piezas_validacion != tp.total_piezas_recibidas
				AND t.id_transferencia IN( {$row_transfers['transfers_ids']} )
				AND tp.resuelto = 0
				GROUP BY p.id_productos
			)ax 
			LEFT JOIN ec_movimiento_detalle md
			ON md.id_producto = ax.product_id
			LEFT JOIN ec_movimiento_almacen ma
			ON md.id_movimiento = ma.id_movimiento_almacen
			LEFT JOIN ec_almacen alm 
			ON ma.id_almacen = alm.id_almacen
			AND alm.id_sucursal = ma.id_sucursal
			LEFT JOIN ec_tipos_movimiento tm
			ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
			GROUP BY ax.product_id";
//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar los productos que no corresponden : {$link->error}"  );

		$resp = 'ok|';

		$counter = 0;
		$used_products = '';
		while ( $row = $stm->fetch_assoc() ) {
		//
			$btr = str_replace( '/', ',', $row['transfer_resolution_id'] ); 
			$tp = str_replace( '/', ',', $row['transfer_products_ids'] ); 
			if( $btr == '' || $btr == null ){
				$btr = -10000;
			}
			if( $tp == '' || $tp == null ){
				$tp = -10000;
			}

			$sql = "SELECT
						prt.id_producto_resolucion AS resolution_product_id,
						prt.conteo_fisico AS fisic_count,
						prt.conteo_excedente AS fisic_excedent
					FROM ec_productos_resoluciones_tmp prt
					WHERE prt.id_bloque_transferencia_recepcion = {$reception_block_id}
					AND prt.id_producto = {$row['product_id']}";
			$stm_aux = $link->query( $sql ) or die( "Error al consultar si ya habia un conteo previo : {$link->error}" );
//die('here');
			$previous_count_id = '';
			$fisic_count = '';
			$fisic_excedent = '';
			if( $stm_aux->num_rows > 0 ){
				$row_aux = $stm_aux->fetch_assoc();
				$previous_count_id = $row_aux['resolution_product_id'];
				$fisic_count = $row_aux['fisic_count'];
				$fisic_excedent = $row_aux['fisic_excedent'];
			}
			$onfocus = "";	
			if( $row['is_maquiled'] == 1 ){
				$onfocus = "onfocus=\"getResolutionMaquileForm( this, {$row['product_id']} );\"";
			}
			$row['productInventory'] = str_replace('.0000', '', $row['productInventory'] );
			$row['missing_quantity'] = str_replace('.0000', '', $row['missing_quantity'] );
			$resp .= "<tr style=\"color : blue;\">
						<td id=\"id_0_{$counter}\" class=\"no_visible\">{$row['product_id']}</td>
						<td id=\"1_{$counter}\" class=\"no_visible\">{$row['product_id']}</td>
						<td id=\"2_{$counter}\">{$row['product_name']}</td>
						<td id=\"3_{$counter}\" class=\"text-end\">{$row['productInventory']}</td>
						<td id=\"6_{$counter}\" class=\"no_visible\">{$row['missing_quantity']}</td>
						<td class=\"text-end\">
							<input type=\"number\" id=\"4_{$counter}\" class=\"form-control text-end\" 
							value=\"{$fisic_count}\" 
							{$onfocus}>
						</td>
						<td class=\"text-end\">
							<input type=\"number\" id=\"5_{$counter}\" class=\"form-control text-end\" 
							value=\"{$fisic_excedent}\" 
							{$onfocus}>
						</td>
						<td class=\"text-end no_visible\" id=\"7_{$counter}\">
							{$row['transfer_products_ids']}
						</td>
						<td id=\"0_{$counter}\" class=\"no_visible\">{$row['transfer_resolution_id']}</td>
						<td id=\"8_{$counter}\" class=\"no_visible\">{$previous_count_id}</td>
					</tr>";
			$counter ++;
			$used_products .= ( $used_products == '' ? '' : ',');
			$used_products .= $row['product_id'];
		}

		$products_condition = ( $used_products == '' ? '' : " AND p.id_productos NOT IN( {$used_products} ) ");
		//consulta de productos en resolucion
		$sql = "SELECT
					ax.transfer_resolution_id,
					ax.product_id,
					ax.product_name,
					ax.quantity,
					ax.is_maquiled,
					SUM( IF( md.id_movimiento_almacen_detalle IS NULL, 0, ( tm.afecta * md.cantidad ) ) ) AS productInventory
				FROM(
					SELECT
						GROUP_CONCAT( btr.id_bloque_transferencia_resolucion SEPARATOR '/' ) AS transfer_resolution_id,
						btr.id_producto AS product_id,
						p.nombre AS product_name,
						SUM( IF( btr.id_bloque_transferencia_resolucion IS NULL, 0, ( btr.piezas_no_corresponden + btr.piezas_sobrantes ) ) ) AS quantity,
						(SELECT 
							IF( p.id_productos = id_producto OR p.id_productos = id_producto_ordigen, 1, 0  ) 
						FROM ec_productos_detalle
						WHERE id_producto = p.id_productos OR id_producto_ordigen = p.id_productos
						) AS is_maquiled
					FROM ec_bloques_transferencias_resolucion btr
					LEFT JOIN ec_productos p
					ON p.id_productos  = btr.id_producto
					WHERE btr.id_bloque_transferencia_recepcion = {$reception_block_id}
					{$products_condition}
					GROUP BY btr.id_producto
				)ax
				LEFT JOIN ec_movimiento_detalle md
				ON md.id_producto = ax.product_id
				LEFT JOIN ec_movimiento_almacen ma
				ON md.id_movimiento = ma.id_movimiento_almacen
				LEFT JOIN ec_almacen alm 
				ON alm.id_almacen = alm.id_almacen
				AND alm.id_sucursal = ma.id_sucursal
				LEFT JOIN ec_tipos_movimiento tm
				ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
				WHERE 1
				AND alm.id_sucursal = {$store_id}
				AND alm.es_almacen = 1
				GROUP BY ax.product_id";

				$stm = $link->query( $sql ) or die( "Error al consultar los productos que no corresponden : {$link->error}"  );

				while ( $row = $stm->fetch_assoc() ) {
					$sql = "SELECT
								prt.id_producto_resolucion AS resolution_product_id,
								prt.conteo_fisico AS fisic_count,
								prt.conteo_excedente AS fisic_excedent
							FROM ec_productos_resoluciones_tmp prt
							WHERE prt.id_bloque_transferencia_recepcion = {$reception_block_id}
							AND prt.id_producto = {$row['product_id']}";
					$stm_aux = $link->query( $sql ) or die( "Error al consultar si ya habia un conteo previo : {$link->error}" );
		//die('here');
					$previous_count_id = '';
					$fisic_count = '';
					$fisic_excedent = '';
					if( $stm_aux->num_rows > 0 ){
						$row_aux = $stm_aux->fetch_assoc();
						$previous_count_id = $row_aux['resolution_product_id'];
						$fisic_count = $row_aux['fisic_count'];
						$fisic_excedent = $row_aux['fisic_excedent'];
					}
					$onfocus = "";
					if( $row['is_maquiled'] == 1 ){
						$onfocus = "onfocus=\"getResolutionMaquileForm( this, {$row['product_id']} );\"";
					}
					$row['productInventory'] = str_replace('.0000', '', $row['productInventory'] );
					$resp .= "<tr style=\"color : red;\">
								<td id=\"1_{$counter}\" class=\"no_visible\">{$row['product_id']}</td>
								<td id=\"2_{$counter}\">{$row['product_name']}</td>
								<td id=\"3_{$counter}\" class=\"text-end\">{$row['productInventory']}</td>
								<td id=\"6_{$counter}\" class=\"no_visible\">{$row['quantity']}</td>
								<td class=\"text-end\">
									<input type=\"number\" id=\"4_{$counter}\" class=\"form-control text-end\" 
										value=\"{$fisic_count}\"
										{$onfocus}
									>
								</td>
								<td class=\"text-end\">
									<input type=\"number\" id=\"5_{$counter}\" class=\"form-control text-end\" 
										value=\"{$fisic_excedent}\"
										{$onfocus} 
									>
								</td>
								<td class=\"text-end no_visible\" id=\"7_{$counter}\">
									{$row['transfer_products_ids']}
								</td>
								<td id=\"0_{$counter}\" class=\"no_visible\">{$row['transfer_resolution_id']}</td>
								<td id=\"8_{$counter}\" class=\"no_visible\">{$previous_count_id}</td>
							</tr>";
					$counter ++;
				}
		return $resp;
	}

	function getUpdateReceptionBlock( $reception_block_id, $reception_token, $user_id, $link ){
	//verifica sobre el bloque
		$sql = "SELECT
					bloqueado AS is_locked
				FROM ec_bloques_transferencias_recepcion
				WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
		$stm = $link->query( $sql ) or die( "Error al eliminar bloqueo :  {$link->error}" );
		$row = $stm->fetch_assoc();
		if( $row['is_locked'] == 0 ){
		//desbloquea el token del dispositivo
			$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
						SET bloqueada = '0'
					WHERE token_unico_dispositivo = '{$reception_token}'";
			$stm = $link->query( $sql ) or die( "Error al desbloquear la sesión de recepción del dispositivo : {$link->error}" );
			return 'ok';
		}
		return 'no';
	}

	function finishTransfersReception( $transfers, $reception_block_id, $user, $sucursal, $link ){
		$link->autocommit( false );
		$resp = "";
		$sql = "UPDATE ec_transferencias 
					SET id_estado = 9 
				WHERE id_transferencia IN( $transfers )";
//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al actualizar la(s) Transferencia( s ) a recibidas : {$sql}  {$link->error}" );
	//verifica si hay registros en resolución
		$sql = "SELECT
					btr.id_producto AS product_id,
					btr.id_proveedor_producto AS product_provider_id,
					btr.piezas_se_quedan AS pieces_stay,
					btr.piezas_se_regresan AS pieces_return,
					btr.piezas_faltaron AS pieces_missing
				FROM ec_bloques_transferencias_resolucion btr
				LEFT JOIN ec_productos p 
				ON p.id_productos = btr.id_producto
				WHERE btr.id_bloque_transferencia_recepcion IN( {$reception_block_id} )
				ORDER BY p.orden_lista ASC";
		$stm = $link->query( $sql ) or die( "Error al consultar detalles por resolver : {$link->error}" );
		
		if( $stm->num_rows > 0 ){
			$resp = "show_view( this, '.validate_transfers');close_emergent();";
		/*	$sql = "SELECT 
						t.id_sucursal_origen AS store_destinity,
						t.id_sucursal_destino AS store_origin,
						t.id_almacen_origen AS warehouse_destinity,
						t.id_almacen_destino AS warehouse_origin
					FROM ec_transferencias t
					WHERE id_transferencia IN( $transfers )
					LIMIT 1";
			$stm_trans = $link->query( $sql ) or die( "Error al consultar detalle de transferecia para Resolución : {$link->error}" );
			$trans_row = $stm_trans->fetch_assoc();
			$header_data = array( 'store_origin'=>$trans_row['store_origin'], 'store_destinity'=>$trans_row['store_destinity'], 
				'warehouse_origin'=>$trans_row['warehouse_origin'], 'warehouse_destinity'=>$trans_row['warehouse_destinity'] );
			include( 'TransferResolution.php' );
			$TransferResolution = new TransferResolution( $link, $user, $sucursal );
			$resp = $TransferResolution->insertResolutionHeader( $recepcion_block_id, $user, $sucursal, $header_data, $stm );*/
		}else{
			$resp = "close_emergent();";
			$sql = "SELECT
						id_producto_resolucion
					FROM ec_productos_resoluciones_tmp
					WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm_sel = $link->query( $sql ) or die( "error|Error al consultar si hay produtos en resolucion : {$link->error}" );
			if( $stm_sel->num_rows > 0 ){
			}else{	
				$sql = "UPDATE ec_bloques_transferencias_recepcion 
							SET recibido = '1'
						WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
				$stm_upd = $link->query( $sql ) or die( "Error al actualizar el bloque a resuelto : {$link->error}" );
			}
		}
	//elimina el bloque de recepcion actual
		$sql = "DELETE FROM ec_transferencias_recepcion_actual WHERE id_sucursal = {$sucursal}";
		$link->query( $sql ) or die( "Error al eliminar los registros de transferencias por recibir : {$link->error}" );
		$transfers_array = explode( ',', $transfers_ids );

		$link->autocommit( true );

		return "ok|<div class=\"row\">
				<div class=\"col-1\"></div>
				<div class=\"col-10 text-center\">
					<h5>Transferencia(s) Finalizada(s) exitosamente.</h5>
					<button onclick=\"{$resp}\" class=\"btn btn-success\">
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>
			</div>";

	}

	function validate_scanner_type( $row, $barcode, $pieces_quantity, $excedent_permission, $permission_box, $transfers, $link ){
		if( $row['piece'] == 1 && $pieces_quantity == null
			&& $excedent_permission == null && $permission_box == '' ){
			if( $row['is_maquiled'] == 1 || $row['is_maquiled'] == -1  ){
					
					$sql_maq = "SELECT 
									SUM( tp.total_piezas_validacion ) AS quantity
								FROM ec_transferencia_productos tp
								WHERE tp.id_transferencia IN( {$transfers} )
								AND tp.id_proveedor_producto = {$row['product_provider_id']}";
				//	die( 'error|' . $sql_maq );
					$stm_maq = $link->query( $sql_maq ) or die( "error|Error al consultar la cantidad pedida : {$link->error}" );
					$row_maq = $stm_maq->fetch_assoc();
					$initial_quantity = $row_maq['quantity'];

					include( '../../../plugins/maquile.php' );
					$Maquile = new maquile( $link );
					$function_js = "setPiecesQuantity( '{$barcode}', 1 );";
					
					return "pieces_form|" . $Maquile->make_form( $row['product_id'], 0, $function_js, $initial_quantity, 'Cantidad enviada : ', 'close_emergent();' );
					//return "pieces_form|" . $Maquile->make_form( $row['product_id'], 0, $function_js );
					//die('');
			}
			$resp = 'pieces_form|<div class="row">';
					$resp .= '<div><h5>Ingresa el número de Piezas : </h5></div>';
					$resp .= '<div class="col-2"></div>';
					$resp .= '<div class="col-8">';
						$resp .= '<input type="number" class="form-control" id="pieces_quantity_emergent">';
						$resp .= '<button type="button" class="btn btn-success form-control"';
						$resp .= ' onclick="setPiecesQuantity( \'' . $barcode . '\' );">';
							$resp .= 'Aceptar';
						$resp .= '</button>';
						$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\' );">';
							$resp .= '<i class="icon-ok-circle">Cancelar</i>';
						$resp .= '</button>';
					$resp .= '</div>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}
		if( $permission_box == null && $row['box'] == 1 && $row['pieces_per_box'] > 1 ){
		//return "message_info|1 : {$permission_box} - {$row['box']}";
			$resp = 'scan_seil_barcode|<div class="row">';
				$resp .= '<div class="col-2"></div>';
				$resp .= '<div class="col-8"><h5>Para escanear la caja primero escanea el sello de caja, si este esta roto escanea los paquetes </h5>';
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\' );">';
						$resp .= 'Aceptar';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}else if( $permission_box != null && $row['box'] != 1 ){
		//return "message_info|2 : {$permission_box} - {$row['box']}";
				$resp = 'is_not_a_box_code|';
				$resp .= '<div>';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-2"></div>';
						$resp .= '<div class="col-8">';
							$resp .= '<label for="tmp_sell_barcode">El código de barras no pertenece a una caja, para continuar escanea el código de barras de la caja : </label>';
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
		return 'ok';
	}

	function validateIsBoxSeal( $barcode, $link ){
		$sql = "SELECT 
					id_codigo_validacion
				FROM ec_codigos_validacion_cajas
				WHERE codigo_barras = '{$barcode}'";
		$stm = $link->query( $sql ) or die( "error|Error al consultar si es código de validación de caja : {$link->error}" );
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
		return 'ok';
	}

/*implementacion Oscar 2023 para validar que el usuario separa los codigos unico que son de diferente almacen*/
	function validate_the_same_warehouses( $unique_code, $transfers, $link ){
		$resp = 'ok|';
		$sql = "SELECT 
					id_almacen_origen AS origin_warehouse,
					id_almacen_destino AS destinity_warehouse,
					id_sucursal_destino AS destinity_store
				FROM ec_transferencias 
				WHERE id_transferencia IN( {$transfers} )
				GROUP BY id_almacen_origen";
		$stm = $link->query( $sql ) or die( "Error al consultar los almacenes de las transferencias : {$link->error}" );
		$warehouse_row = $stm->fetch_assoc();
	//consulta si el codigo unico pertenece a almacenes
		$sql = "SELECT
					t.id_almacen_origen AS origin_warehouse,
					t.id_almacen_destino AS destinity_warehouse,
					t.id_sucursal_destino AS destinity_store
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_transferencia_codigos_unicos tcu
				ON tcu.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				WHERE tcu.codigo_unico = '{$unique_code}'
				GROUP BY t.id_almacen_origen
				AND tcu.insertado_por_resolucion = 0";
		$stm = $link->query( $sql ) or die( "error|Error al verificar que el codigo unico pertenezca a los almacenes : {$link->error}" );
	//die( 'here|here' );
		$row_uc = $stm->fetch_assoc();
		if( $warehouse_row['origin_warehouse'] != $row_uc['origin_warehouse'] 
			|| $warehouse_row['destinity_warehouse'] != $row_uc['destinity_warehouse'] ){
			if( $warehouse_row['destinity_store'] == $row_uc['destinity_store'] ){
				$resp = "manager_password|<div class=\"row\">
					<h5 class=\"text-center\">Error!!!</h5>
					<h6>El codigo unico escaneado pertenece a almacenes diferentes, <b>NO RECIBIR</b>!!!</h6>
					<p>Pide al encargado que ingrese su contraseña para continuar : </p>
					<div class=\"col-4\"></div>
					<div class=\"col-4\">
						<input type=\"password\" id=\"manager_password\" class=\"form-control\">
						<br>
						<button
							class=\"btn btn-success form-control\"
							type=\"button\"
							onclick=\"mannager_has_separated_unique_code();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
				die( $resp );
			}
		}
		return $resp;
	}
/*fin de cambio Oscar 2023*/

	function validateUniqueCode( $barcode, $unique_code, $transfers, $validation_blocks, $reception_block_id, 
		$scanned_data, $user, $sucursal, $link ){
//implementacion Oscar 2023
		$validate_the_same_warehouses = validate_the_same_warehouses( $unique_code, $transfers, $link );
		if( $validate_the_same_warehouses != 'ok' ){
			return $validate_the_same_warehouses;
		}

//echo "here|";
			$sql_base = "SELECT
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
					WHERE tcu.codigo_unico = '{$unique_code}'";//IS NULL NOT IN( {$reception_block_id} )
					//AND tcu.id_bloque_transferencia_recepcion
		//verifica que exista
			$sql = "SELECT 
						id_transferencia_codigo 
					FROM ec_transferencia_codigos_unicos 
					WHERE codigo_unico = '{$unique_code}'";
			$stm = $link->query( $sql ) or die( "error|Error al consultar si el código único esta registrado : {$link->error}" );
			
//die( '|here' );
			if( $stm->num_rows <= 0 ){
				include( 'Resolution.php' );
			//inserta recepcion de bloque
				$Resolution = new Resolution( $link, $user, $sucursal );
				$quantity_to_separate = 0;
				if( $scanned_data['box'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_box'];
				}else if( $scanned_data['pack'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_pack'];
				}elseif ( $scanned_data['piece'] != 0 ) {
					$quantity_to_separate = ( $pieces_quantity != null ? $pieces_quantity : 100.10 );
				}
				$resolution_detail_id = $Resolution->insertBlockResolution( 'does_not_correspond', $reception_block_id, $transfers, $user, $quantity_to_separate, $scanned_data, $barcode, $unique_code, true );
				$resolution_detail_id = explode( '|', $resolution_detail_id );

				return "manager_password|<div class=\"row text-center\">
							<div class=\"col-1\"></div>
							<div class=\"col-10 text-center\">
								<h5>El código de barras es único y no corresponde a esta recepción!</h5>
								<p>Código : {$barcode} --- Código Único : {$unique_code}</p>
								<p style=\"color : red;\">Lleva este producto con el encargado y pidele que ingrese su 
								contraseña para continuar!</p>
								<div class=\"row\">
									<div class=\"col-1 text-center\"></div>
									<div class=\"col-10 text-center\">

										<div class=\"row\">
											<div class=\"col-5\">
												<input 
													type=\"text\" 
													id=\"unique_code_resolution_field\"
													class=\"form-control\" 
													placeholder=\"Escribe Resolucion\">
											</div>
											<div class=\"col-5\">
												<input 
													type=\"text\" 
													id=\"unique_code_return_field\"
													class=\"form-control\" 
													placeholder=\"Escribe Cancelar\">
											</div>
										</div>
										<p align=\"center\">Pide al encargado que ingrese su contraseña : </p>
										<br>
											<input type=\"password\" class=\"form-control\" id=\"manager_password\">
										<br>
										<div class=\"row\">
											<div class=\"col-6\">

												<button
													type=\"button\"
													class=\"btn btn-success form-control\"
													onclick=\"confirm_product_was_separated( 1, '{$barcode}', '{$unique_code}', '{$is_a_box}', 'unique_code_resolution' );\"
												>
													<i class=\"icon-ok-circle\">Aceptar</i>
												</button>
											</div>
											<div class=\"col-6\">
												<button
													type=\"button\"
													class=\"btn btn-danger form-control\"
													onclick=\"confirm_product_was_separated( 2, '{$barcode}', '{$unique_code}', '{$is_a_box}', 'unique_code_resolution', '{$resolution_detail_id[1]}' );\"
												>
													<i class=\"icon-cancel-cirlce\">Cancelar</i>
												</button>
											</div>
										<div>
									</div>
								</div>
							</div>
						</div>";
			}
//verifica si el código único pertenece a las transferencias
			$sql_transf = "{$sql_base} AND tcu.id_bloque_transferencia_validacion IN( {$validation_blocks} )";
			$stm = $link->query( $sql_transf ) or die( "error|Error al verificar si el codigo único pertenece al bloque : {$link->error}" );
	//	die( "message_info|{$sql_transf}" );
			if( $stm->num_rows <= 0 ){

				include( 'Resolution.php' );
			//inserta recepcion de bloque
				$Resolution = new Resolution( $link, $user, $sucursal );
				$quantity_to_separate = 0;
				if( $scanned_data['box'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_box'];
				}else if( $scanned_data['pack'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_pack'];
				}elseif ( $scanned_data['piece'] != 0 ) {
					$quantity_to_separate = ( $pieces_quantity != null ? $pieces_quantity : 100.10 );
				}
				$resolution_detail_id = $Resolution->insertBlockResolution( 'does_not_correspond', $reception_block_id, $transfers, $user, $quantity_to_separate, $scanned_data, $barcode, $unique_code, true );
				$resolution_detail_id = explode( '|', $resolution_detail_id );

				$validate_unique_code_in_other_transfer = validate_unique_code_in_other_transfer( $barcode, $unique_code, $transfers, $validation_blocks, $reception_block_id, $sucursal, $link );
				if( $validate_unique_code_in_other_transfer != 'ok' ){
					return "manager_password|{$validate_unique_code_in_other_transfer}";
				}

				/*return "manager_password|<div class=\"row text-center\">
							<div class=\"col-1\"></div>
							<div class=\"col-10 text-center\">
								<h5>El código de barras es único y no correponde a ninguna de las transferencias!</h5>
								<p>Código : {$barcode} --- Código Único : {$unique_code}</p>
								<p>Lleva este producto con el encargado y pidele que ingrese su 
								contraseña para continuar!</p>
							<div class=\"row\">
								<div class=\"col-4\"></div>
								<div class=\"col-4 text-center\">
									<input type=\"password\" id=\"manager_password\" class=\" form-control\"><br><br>
									<button
										type=\"button\"
										class=\"btn btn-success form-control\"
										onclick=\"confirm_product_was_separated();\"
									>
										<i class=\"icon-ok-circle\">Aceptar</i>
									</button>
								</div>
							</div>
							</div>
						</div>";*/
				return "manager_password|<div class=\"row text-center\">
							<div class=\"col-1\"></div>
							<div class=\"col-10 text-center\">
								<h5>El código de barras es único y no corresponde a esta recepción!</h5>
								<p>Código : <b>{$barcode}</b> <br> Código Único : <b>{$unique_code}</b></p>
								<p style=\"color : red;\">Lleva este producto con el encargado y pidele que ingrese su 
								contraseña para continuar!</p>
								<div class=\"row\">
									<div class=\"col-1 text-center\"></div>
									<div class=\"col-10 text-center\">

										<div class=\"row\">
											<div class=\"col-5\">
												<input 
													type=\"text\" 
													id=\"unique_code_resolution_field\"
													class=\"form-control\" 
													placeholder=\"Escribe Resolucion\">
											</div>
											<div class=\"col-5\">
												<input 
													type=\"text\" 
													id=\"unique_code_return_field\"
													class=\"form-control\" 
													placeholder=\"Escribe Cancelar\">
											</div>
										</div>
										<p align=\"center\">Pide al encargado que ingrese su contraseña : </p>
										<br>
											<input type=\"password\" class=\"form-control\" id=\"manager_password\">
										<br>
										<div class=\"row\">
											<div class=\"col-6\">

												<button
													type=\"button\"
													class=\"btn btn-success form-control\"
													onclick=\"confirm_product_was_separated( 1, '{$barcode}', '{$unique_code}', '{$is_a_box}', 'unique_code_resolution' );\"
												>
													<i class=\"icon-ok-circle\">Aceptar</i>
												</button>
											</div>
											<div class=\"col-6\">
												<button
													type=\"button\"
													class=\"btn btn-danger form-control\"
													onclick=\"confirm_product_was_separated( 2, '{$barcode}', '{$unique_code}', '{$is_a_box}', 'unique_code_resolution', '{$resolution_detail_id[1]}' );\"
												>
													<i class=\"icon-cancel-cirlce\">Cancelar</i>
												</button>
											</div>
										<div>
									</div>
								</div>
							</div>
						</div>";
				
			}
//verifica si el código único pertenece a las transferencias
			$sql_transf = "{$sql_base} AND tcu.id_bloque_transferencia_validacion IN( {$validation_blocks} ) 
			AND tcu.id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm = $link->query( $sql_transf ) or die( "error|Error al validar si el código único ya fue recibido : {$link->error}" );
			if( $stm->num_rows > 0 ){
//echo "here_2";
				$row = $stm->fetch_assoc();
				if( $row['unique_barcode_status'] != 1 ){
					$resp = "exception_repeat_unic|<h5 class=\"orange\">Este código único ya fue recibido anteriormente</h5>";
					$resp .= "<p>Código : <b>{$barcode}</b> <br> Código Único : <b style=\"color : green;\">{$unique_code}</b></p>";
					$resp .= "<p>Escaneado por : {$row['name']}</p>";
					$resp .= "<p>Pertenece a Transferencia : {$row['folio']}</p>";
					$resp .= "<p>Sucursal Origen : <b class=\"orange\">{$row['origin_name']}</b></p>";
					$resp .= "<p>Sucursal Origen : <b class=\"orange\">{$row['destinity_name']}</b></p>";
					$resp .= "<div class=\"row\">";
						$resp .= "<div class=\"col-3\"></div>";
						$resp .= "<div class=\"col-6\">";
							$resp .= "<button 
										class=\"btn btn-warning form-control\" 
										onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' ); lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\">";
								$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
							$resp .= "</button>";
						$resp .= "</div>";
					$resp .= "</div>";
					return $resp;
				}
			}
		return 'ok';
	}

	function validate_unique_code_in_other_transfer( $barcode, $unique_code, $transfers, $validation_blocks, $reception_block_id, $sucursal_id, $link ){
		$resp = 'ok';
	/*implementacion Oscar 2023 para tomar los status configurados en el tipo de recepcion ( configuracioon del sistema )*/
		$sql = "SELECT 
					ttv.status_transferencias AS transfer_status
				FROM sys_configuracion_sistema cs
				LEFT JOIN sys_tipos_transferencias_validacion ttv
				ON ttv.id_tipo_transferencia_validacion = cs.tipo_recepcion_transferencia
				WHERE cs.id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar configuracion de transferencias : {$link->error}" );
		$row = $stm->fetch_assoc();
		$transfer_status = $row['transfer_status'];
	/*fin de cambio Oscar 2023*/
	/*implementacion Oscar 2023*/
		$sql = "SELECT
					id_almacen_origen AS origin_warehouse,
					id_almacen_destino AS destinity_warehouse
				FROM ec_transferencias 
				WHERE id_transferencia IN ( {$transfers} )
				GROUP BY id_almacen_origen";
		$warehouse_stm = $link->query( $sql ) or die( "Error al consultar las sucursales : {$link->error}" );
		$warehouse_row = $warehouse_stm->fetch_assoc();

	/**/
		$sql = "SELECT 
					t.id_transferencia AS transfer_id,
					t.folio AS folio
				FROM ec_transferencia_codigos_unicos tcu
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = tcu.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t 
				ON t.id_transferencia = btvd.id_transferencia
				WHERE tcu.codigo_unico = '{$unique_code}'
				AND t.id_estado IN( {$transfer_status} )
				AND t.id_sucursal_destino = {$sucursal_id}
				AND t.id_almacen_origen = {$warehouse_row['origin_warehouse']}
				AND t.id_almacen_destino = {$warehouse_row['destinity_warehouse']}
				AND t.id_transferencia NOT IN( {$transfers} )";	
		//die( $sql );
		//return $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar la posible transferencia : {$link->error}" );
		if( $stm->num_rows > 0 ){
			$resp = "<div class=\"row\">
						<div class=\"col-12\">
							<h5>Este codigo único pertenece a otra Transferencia, 
							puedes agregar la Transferencia a esta recepción ó enviar este producto a resolución </h5>
						";
			while( $row = $stm->fetch_assoc() ) {
				$resp .= "<p>Transferencia : {$row['folio']}</p>";
			}
			$resp .= "</div>
					<div class=\"col-6\">
						<input type=\"text\" id=\"validate_option_unique_code_add\" class=\"form-control\" placeholder=\"Escribe agregar\">
					</div>
					<div class=\"col-6\">
						<input type=\"text\" id=\"validate_option_unique_code_resolution\" class=\"form-control\" placeholder=\"Escribe resolucion\">
					</div>
					<div class=\"col-12 text-center\">
						<input type=\"password\" id=\"manager_password\" class=\"form-control\">
						<br>
						<button
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"validate_option_unique_code();\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
						<br>
						<button
							type=\"button\"
							class=\"btn btn-danger\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-cancel-circled\">Cancelar</i>
						</button>
					</div>
				</div>";
		}
		return $resp;
	}

	function validate_permission_block( $reception_block_id, $reception_token, $link ){
	//busca a nivel bloque
		$sql= "SELECT 
					bloqueado AS is_locked
				FROM ec_bloques_transferencias_recepcion
				WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";

		$stm = $link->query( $sql ) or die( "Error al consultar si el bloque esta bloqueado : {$link->error}" );
		$row = $stm->fetch_assoc();

		$sql= "SELECT 
					bloqueada AS is_locked
				FROM ec_sesiones_dispositivos_recepcion_transferencias
				WHERE token_unico_dispositivo = '{$reception_token}'";
//die( 'error|' . $sql );
		$stm_ = $link->query( $sql ) or die( "Error al consultar si el token del dispositivo esta bloqueado : {$link->error}" );
		$row_ = $stm_->fetch_assoc();

		if( $row['is_locked'] == 1 || $row_['is_locked'] == 1 ){
			$resp = "<div>
						<div class=\"text-center\">
							El bloque esta en proceso de edicion, espera mientras se termina de editar.
							Al terminar se actualizará la pantalla y deberas de escanear la(s) Transferencias para 
							continuar
							<img src=\"\" style=\"\">
						</div>
					</div>";
			$resp .= "<script>
						var cont = 0;
					    var id = setInterval(function(){
					    	var response = seek_update_reception_block( global_current_reception_blocks, '{$reception_token}' );
					    	if( response == 'ok' ){
            					//clearInterval(id);
            					alert( 'La pantalla se va a recargar' );
            					location.reload();
					    	}
					    }, 10000); 

						function seek_update_reception_block( reception_block_id, reception_token ){
							var url = 'ajax/db.php?fl=getUpdateReceptionBlock&reception_block_id=' + reception_block_id;
							url += '&reception_token=' + reception_token;
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

/*Buscar por codigo de barras e inserción de detalles recibidos*/
	
	function validateBarcode( $barcode, $transfers, $user, $excedent_permission = null, 
		$pieces_quantity = null, $permission_box = null, $unique_code = null, $was_find_by_name = 0, 
		$validation_blocks, $reception_block_id = null, $sucursal, $reception_token, $link ){
	//valida que el bloque no este bloqueado
		validate_permission_block( $reception_block_id, $reception_token, $link );
//inserta el registro de escaneo temporal
		$sql = "INSERT INTO validation_scan_tmp SET 
					id_scann_tmp = NULL,
					id_usuario = {$user},
					codigo_barras = '{$barcode}',
					codigo_unico = '{$unique_code}',
					bloque_recepcion = {$reception_block_id},
					fecha_alta = NOW()";
		$stm_tmp = $link->query( $sql ) or die( "Error al insertar el registro temporal : {$link->error}" );

	//verifica si el codigo de caja es de validacion de la caja
		$is_box_seal = validateIsBoxSeal( $barcode, $link );
		if( $is_box_seal != 'ok' ){
			return $is_box_seal;
		}
	//verifica si el código de barras existe
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.id_producto AS product_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
						OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 
					) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 
						OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
						1, 0 
					) AS pack,
					pp.piezas_presentacion_cluces AS pieces_per_pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					pp.presentacion_caja AS pieces_per_box,
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
				LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos
				WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
				OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
				OR pp.codigo_barras_caja_2 = '{$barcode}')";
		$stm1 = $link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : " . $link->error );

		if( $stm1->num_rows <= 0 ){
			return seekByName( $barcode, $link );
		}
		$scanned_data = $stm1->fetch_assoc();
		$validation_data = validate_scanner_type( $scanned_data, $barcode, $pieces_quantity, $excedent_permission, $permission_box, $transfers, $link );
		if( $validation_data != 'ok' ){
			return $validation_data;
		}

		//validacion para no dejar pasar códigos estandar si es paquete o caja
		if( ( $unique_code == null || $unique_code == '' ) && ( $scanned_data['pack'] == 1 || $scanned_data['box'] == 1 ) ){
			return "exception|
				<div class=\"row\">
					<div class=\"col-1\"></div>
					<div class=\"col-10 text-center\">
						<h5>El código de barras que se escaneo es de caja o paquete y no cuenta con un 
						código único, envié una fotografía o captura de pantalla al encargado de sistemas :</h5>
						<p>Código escaneado : <b style=\"color : red;\">{$barcode}</b></p>
						<br>
						<p>Lleva este producto con el encargado y pidele que ingrese su 
						contraseña para continuar!</p>
						<div class=\"row\">
							<div class=\"col-2 text-center\"></div>
							<div class=\"col-8 text-center\">
								<input type=\"password\" class=\"form-control\" id=\"manager_password\"><br>

								<button
									type=\"button\"
									class=\"btn btn-success form-control\"
									onclick=\"confirm_product_was_separated();\"
								>
									<i class=\"icon-ok-circle\">Aceptar</i>
								</button>
							</div>
						</div> 
					</div>
				</div>";
		}

	//verifica que el código único no haya sido usado anteriormente
		if( $unique_code != null ){
			$unique_code_validation = validateUniqueCode( $barcode, $unique_code, $transfers, $validation_blocks, 
				$reception_block_id, $scanned_data, $user, $sucursal, $link );
			if( $unique_code_validation != 'ok' ){
				return $unique_code_validation;
			}
		}
			//if( $permission_box == null ){
				
			//}
	//verifica que el proveedor producto exista en alguna transferencia
		$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
						OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 
					) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					tp.cantidad_cajas,
					tp.cantidad_paquetes,
					tp.cantidad_piezas,
					tp.cantidad,
					SUM( IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							( tru.cantidad_cajas_recibidas * pp.presentacion_caja ) 
						) 
					) AS validated_boxes,
					pp.presentacion_caja AS pieces_per_box,
					pp.piezas_presentacion_cluces AS pieces_per_pack,
					SUM(IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							( tru.cantidad_paquetes_recibidos * pp.piezas_presentacion_cluces ) 
						) 
					) AS validated_packs,
					SUM(IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							tru.cantidad_piezas_recibidas 
						) 
					) AS validated_pieces
				/*FROM ec_transferencias_validacion_usuarios tvu
				ON tp.id_transferencia_producto = tvu.id_transferencia_producto*/
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_transferencias_recepcion_usuarios tru 
				ON tp.id_transferencia_producto = tru.id_transferencia_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
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
					1, 0 ) AS 'box'
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_transferencia_productos tp
				ON tp.id_producto_or = pp.id_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')";
			$stm3 = $link->query( $sql ) or die( "error|Error al consultar si el producto existe en la transferencia : {$link->error} {$sql}" );
			if( $stm3->num_rows <= 0){

				include( 'Resolution.php' );

			//inserta recepcion de bloque
				$Resolution = new Resolution( $link, $user, $sucursal );
				$quantity_to_separate = 0;
				if( $scanned_data['box'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_box'];
				}else if( $scanned_data['pack'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_pack'];
				}elseif ( $scanned_data['piece'] != 0 ) {
					$quantity_to_separate = ( $pieces_quantity != null ? $pieces_quantity : 100.10 );
				}
				//return 
				$Resolution->insertBlockResolution( 'does_not_correspond', $reception_block_id, $transfers, $user, $quantity_to_separate, $scanned_data, $barcode, $unique_code);
				$inform = $stm3->fetch_assoc();
				//$resp = 'exception|<br/><h3 class="inform_error">El producto no pertenece a esta(s) Transferencia(s).<br />Este producto tiene que ser devuelto a Matriz</h3>';	
				$resp = 'exception|<br/><h3 class="inform_error">El producto no corresponde a la(s) Transferencia(s)<br />';
					$resp .= '<b class="red">Aparta este producto, NO ACOMODAR!</b></h3>'; 
				$resp .= "<p>Lleva este producto con el encargado y pidele que ingrese su 
					contraseña para continuar!</p>
					<div class=\"row\">
						<div class=\"col-2 text-center\"></div>
						<div class=\"col-8 text-center\">
							<input type=\"password\" class=\"form-control\" id=\"manager_password\"><br>

							<button
								type=\"button\"
								class=\"btn btn-success form-control\"
								onclick=\"confirm_product_was_separated();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>"; 
				//$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				//	$resp .= '<button class="btn btn-warning form-control" onclick="close_emergent();">';
				//		$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				//	$resp .= '</button>';
				//$resp .= '<input type="password" id="manager_password" class="form-control emergent_manager_password"><br />';
				//$resp .= '<button class="btn btn-danger form-control" onclick="save_new_reception_detail( ';
				//	$resp .= " {$inform['product_id']}, {$inform['product_provider_id']}, {$inform['box']}, {$inform['pack']}, {$inform['piece']} ";
				//$resp .= ' );">Aceptar</button></div><br/><br/>';
				$resp .= "</div></div><br/><br/>";
				return $resp;
			}else{
				include( 'Resolution.php' );
			//inserta recepcion de bloque
				$Resolution = new Resolution( $link, $user, $sucursal );
				$quantity_to_separate = 0;
				if( $scanned_data['box'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_box'];
				}else if( $scanned_data['pack'] != 0 ){
					$quantity_to_separate = $scanned_data['pieces_per_pack'];
				}elseif ( $scanned_data['piece'] != 0 ) {
					$quantity_to_separate = ( $pieces_quantity != null ? $pieces_quantity : 100.10 );
				}
				$Resolution->insertBlockResolution( 'does_not_correspond', $reception_block_id, $transfers, $user, $quantity_to_separate, $scanned_data, $barcode, $unique_code );
				//return 
				$inform = $stm3->fetch_assoc();
				$resp = 'exception|<br/><h3 class="inform_error">El modelo del producto no corresponde a la(s) Transferencia(s)<br />';
					$resp .= '<b class="red">Aparte este producto, NO ACOMODAR!</b></h3>'; 
				$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= "<p>Lleva este producto con el encargado y pidele que ingrese su 
					contraseña para continuar!</p>
					<div class=\"row\">
						<div class=\"col-2 text-center\"></div>
						<div class=\"col-8 text-center\">
							<input type=\"password\" class=\"form-control\" id=\"manager_password\"><br>

							<button
								type=\"button\"
								class=\"btn btn-success form-control\"
								onclick=\"confirm_product_was_separated();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
					//$resp .= '<button class="btn btn-warning form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\' );">';
					//	$resp .= '<i class="icon-ok-circle">Aceptar</i>';
					//$resp .= '</button>';
				//$resp .= '<input type="password" id="manager_password" class="form-control emergent_manager_password"><br />';
				//$resp .= '<button class="btn btn-danger form-control" onclick="save_new_reception_detail( ';
					//$resp .= " {$inform['product_id']}, {$inform['product_provider_id']}, {$inform['box']}, {$inform['pack']}, {$inform['piece']} ";
				//$resp .= ' );">Aceptar</button></div><br/><br/>';
				$resp .= "</div></div><br/><br/>";
				return $resp;
			}
		}
		$row = $stm2->fetch_assoc();

		if( $pieces_quantity != null ){
			$row['piece'] = $pieces_quantity;
		}
		return insertProductReception( $row, $user, $transfers, $excedent_permission, $was_find_by_name, $barcode, 
			$unique_code, $reception_block_id, $sucursal, $permission_box, $link );
	}

	function insertProductReception( $data, $user, $transfers, $excedent_permission = null, $was_find_by_name = 0, $barcode, 
		$unique_code = null, $reception_block_id, $sucursal, $permission_box, $link ){
		$link->autocommit( false );
//echo "|";
//		var_dump( $data );
	//verifica transferencias pendientes de recepcion	
		$sql = "SELECT 
					ax.product_transfer_id,
					ax.boxes_to_recive,
					ax.packs_to_recive,
					ax.pieces_to_recive,
					ax.pending_to_recive
				FROM(
					SELECT
						tp.id_transferencia_producto AS product_transfer_id,
						( SUM( tp.cantidad_cajas_validacion ) - SUM( tp.cantidad_cajas_recibidas ) ) AS boxes_to_recive,
						( SUM( tp.cantidad_paquetes_validacion ) - SUM( tp.cantidad_paquetes_recibidos ) ) AS packs_to_recive,
						( SUM( tp.cantidad_piezas_validacion ) - SUM( tp.cantidad_piezas_recibidas ) ) AS pieces_to_recive,
						( SUM( tp.total_piezas_validacion ) - SUM( tp.total_piezas_recibidas ) ) AS pending_to_recive
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
				ORDER BY ax.product_transfer_id DESC";/*ax.pending_to_recive,*/
//echo "<br>Consulta 1 : {$sql}<br><br>";
		$stm = $link->query( $sql ) or die( "error|Error al consultar transferencias pendientes de recibir : " . $link->error );
		//return 'error|'. $sql;
		//else{
			//echo ('ok|here');
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
				if( $quantity > 0 && $transfer['pending_to_recive'] > 0 ){
				//piezas surtidas vs piezas_validadas		
					if( $transfer['pending_to_recive'] > $quantity ){
						$assign_quantity = $quantity;
					}
					if( $transfer['pending_to_recive'] == $quantity ){
						$assign_quantity = $quantity;
					}
					if( $transfer['pending_to_recive'] < $quantity ){
						$assign_quantity = $transfer['pending_to_recive'];
						if( $excedent_permission != null 
						&& $transfers_counter == $transfers_total ){
							$assign_quantity = $quantity;
						}
					}

					if( $assign_quantity > 0 ){
					//inserta el registro de recepción
						$sql = "INSERT INTO ec_transferencias_recepcion_usuarios ( id_transferencia_recepcion, id_transferencia_producto,
						id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_recibidas, cantidad_paquetes_recibidos, cantidad_piezas_recibidas, 
						fecha_recepcion, id_status, validado_por_nombre, codigo_validacion, codigo_unico )
						VALUES( NULL, '{$transfer['product_transfer_id']}', '{$user}', '{$data['product_id']}', '{$data['product_provider_id']}', 
							'{$data['box']}', '{$data['pack']}', '{$assign_quantity}', NOW(), 1, '{$was_find_by_name}', '{$barcode}', '{$unique_code}' )";
						$stm_3 = $link->query( $sql ) or die( "error|Error al insertar el registro de recepción : " . $link->error );

					//actualiza la validacion del producto en la transferencia
						$sql_3 = "UPDATE ec_transferencia_productos tp 
								LEFT JOIN ec_proveedor_producto pp 
								ON tp.id_proveedor_producto = pp.id_proveedor_producto
							SET tp.cantidad_cajas_recibidas =  ( tp.cantidad_cajas_recibidas + {$data['box']} ),
							tp.cantidad_paquetes_recibidos =  ( tp.cantidad_paquetes_recibidos + {$data['pack']} ),
							tp.cantidad_piezas_recibidas =  ( tp.cantidad_piezas_recibidas + {$assign_quantity} ),
							tp.total_piezas_recibidas = ( tp.total_piezas_recibidas + {$assign_quantity} )
							WHERE tp.id_transferencia_producto = '{$transfer['product_transfer_id']}'
							AND pp.id_proveedor_producto = '{$data['product_provider_id']}'";
						$stm_4 = $link->query( $sql_3 ) or die( "error|Error al actualizar las piezas validadas en la transferencia : {$link->error}" );
					
					//actualiza la cantidad
						$quantity  -= $assign_quantity;
					}
				}
				$transfers_counter ++;//incrementa contador de detalles de transferencias
			}//fin de while
		if( $quantity > 0 && $excedent_permission != null){
			include( 'Resolution.php' );
		//inserta recepcion de bloque
			$Resolution = new Resolution( $link, $user, $sucursal );		
			$link->autocommit( true );
			return $Resolution->insertBlockResolution( 'excedent', $reception_block_id, $transfers, $user, $quantity, $data, $barcode, $unique_code );
		}


		if( $quantity > 0 && $excedent_permission == null ){
			//verifica que la cantidad que se va a validar no supere la cantidad pedida
			$sql = "SELECT 
						CONCAT( p.nombre, ' <b> ( CLAVE PROVEEDOR : ', pp.clave_proveedor, ' )</b>' ) AS description_name,
						SUM( tp.total_piezas_validacion ) - SUM( tp.total_piezas_recibidas ) AS total_to_receive,
						SUM( tp.total_piezas_validacion ) AS pieces_total,
						SUM( tp.total_piezas_recibidas ) AS received_pieces,
						( ( pp.presentacion_caja * {$data['box']} ) 
									+ ( pp.piezas_presentacion_cluces * {$data['pack']} ) 
									+ {$quantity} ) AS supplie
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					WHERE tp.id_transferencia IN( {$transfers} )
					AND tp.id_producto_or = '{$data['product_id']}'
					AND tp.id_proveedor_producto = '{$data['product_provider_id']}'";
	//echo "";
			$stm2 = $link->query( $sql ) or die( "error|Verifica que la cantidad que se va a recibir no supere la cantidad validada : {$link->error}" );
			$comparation_row = $stm2->fetch_assoc();
			//while( $r = $stm->fetch_assoc() ){
				$description = '';
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
				}//
			$resp = 'amount_exceeded|<h5>' . $description . ' El escaneo supera la cantidad enviada, sigue las instrucciones y ';

			$resp .= ' pida la autorización del encargado para continuar: </h5>';
			$resp .= "<p>Código : <b>{$barcode}</b> <br> Código Único : <b style=\"color : green;\">{$unique_code}</b></p>";
			$resp .= "<p class=\"orange\">{$comparation_row['description_name']}</p>";//{$sql}
//$resp .= $sql;

	//$resp .= '<p>perm : ' . $excedent_permission  . '</p>';
			
			$resp .= '<div class="row"><div class="col-2"></div>';
				$resp .= '<div class="col-8">';

				//$resp .= "<br>Consulta 2 : {$sql}<br><br>";
				
					$resp .= '<div class="row">';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad total enviada : <br><b class=\"orange\">" . round( $comparation_row['pieces_total'], 4 ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad recibida : <br><b class=\"orange\">" . round( $comparation_row['received_pieces'] , 4 ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad pendiente de Recibir : <br><b class=\"orange\">" . ($comparation_row['total_to_receive'] <= 0 ? 0 : round( $comparation_row['total_to_receive'], 4 ) ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad que se intenta recibir : <br><b class=\"orange\">" . round( $comparation_row['supplie'], 4 ) . "</b></p>";
						$resp .= '</div>';

						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Acomoda : <br><b class=\"orange\">" . ($comparation_row['total_to_receive'] <= 0 ? 0 : round( $comparation_row['total_to_receive'], 4 ) ) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Lleva con el encargado : <br><b class=\"orange\">{$comparation_row['supplie']}</b></p>";
						$resp .= '</div>';

					$resp .= '</div>';
					
					$resp .= '<input type="password" class="form-control" id="manager_password">';
					$res .= '<p id="response_password"></p>';
					$resp .= '<button type="button" class="btn btn-success form-control';
						$resp .= ' form-control" onclick="confirm_exceeds( \'' . $barcode . '\', '. $quantity . ( $permission_box != null ? ', ' . $permission_box : '') . ');">';//' . ( $permission_box == 1 ? '1'  : '' ) . '
						$resp .= '<i class="icon-ok-circle">Aceptar</i>';
					$resp .= '</button>';

					/*$resp .= '<button type="button" class="btn btn-danger form-control';
						$resp .= ' form-control" onclick="return_exceeds();">';
						$resp .= '<i class="icon-ok-circle">Regresar producto</i>';
					$resp .= '</button>';
				$resp .= '</div>';*/
			$resp .= '</div>';
			$link->autocommit( true );
			return $resp;
		}else{
	//inserta código unico
			if( $unique_code != null ){
				$sql = "UPDATE ec_transferencia_codigos_unicos 
							SET id_bloque_transferencia_recepcion = '{$reception_block_id}',
							id_status_transferencia_codigo = 2, 
							id_usuario_recepcion = {$user}
						WHERE codigo_unico = '{$unique_code}'";
		//echo "<br>Consulta 5 : {$sql}<br><br>";
				$stm_5 = $link->query( $sql ) or die( "error|Error al actualizar el código único : {$sql}{$link->error}" );
		}
		}
		//}
		
		$link->autocommit( true );
		//echo(  $sql );
		return 'ok|Producto Recibido exitosamente!';
	}
	
/*Fin de Proceso*/


	function block_reception_sessions( $reception_block_id, $reception_token, $link ){
	//bloquea el bloque de validacion
			$sql = "UPDATE ec_bloques_transferencias_recepcion
					SET bloqueado = '1'
					WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
		//die( $sql );
			$stm = $link->query( $sql ) or die( "Error al bloquear el bloque de recepcion {$reception_block_id} : {$link->error}" );
	//bloquea sesiones de validacion
			$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
					SET bloqueada = '1'
					WHERE id_bloque_recepcion = {$reception_block_id}
					AND token_unico_dispositivo != '{$reception_token}'";
	//die( $sql );
			$stm = $link->query( $sql ) or die( "Error al bloquear las sesiones del bloque de recepcion {$reception_block_id} : {$link->error}" );
			return 'ok';
	}
/*fin de cambio Oscar 2023*/
	function getMessageToAddTransfer( $transfers, $folio, $reception_block_id, $reception_token, $user_id, $link ){
		$link->autocommit( false );
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
					btvd.id_bloque_transferencia_validacion AS reception_block_id,
					t.id_almacen_origen AS origin_warehouse,
					t.id_almacen_destino AS destinity_warehouse
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				WHERE t.folio = '{$folio}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el id de la transferencia : {$link->error}" );
		$row = $stm->fetch_assoc();
		$transfer_id = $row['transfer_id'];
/*implementacion Oscar 2023 para bloqueos de sesiones de validacion / recepcion*/
	//bloquea las sesiones de validacion
		$lock = block_reception_sessions( $reception_block_id, $reception_token, $link );
		if( $lock != 'ok' ){
			return "Error : {$lock}";
		}
/*fin de cambio Oscar 2023*/
	//	$reception_block_id = $row['reception_block_id'];
	//echo $sql;
/*inserta registros de bloqueo
		$sql = "UPDATE ec_bloques_transferencias_recepcion 
					SET bloqueado = '1' 
				WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
//die( "Error|{$sql}" );
		$stm = $link->query( $sql ) or die( "Error al bloquear el bloque de transferencia : {$link->error}" );

		$sql = "INSERT INTO ec_bloqueos_recepcion_transferencia ( id_bloqueo_recepcion, id_usuario, 
			id_bloque_recepcion, status ) 
				SELECT
					NULL, 
					tru.id_usuario,
					{$reception_block_id},
					1
				FROM ec_transferencias_recepcion_usuarios tru
				LEFT JOIN ec_transferencia_productos tp
				ON tru.id_transferencia_producto = tp.id_transferencia_producto
				WHERE tp.id_transferencia IN( {$transfers} )
				GROUP BY tru.id_usuario";
	//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al insertar registros de bloqueo de transferencia : {$sql} {$link->error}" );
*/
		$resp = "<h3><i>ATENCIÓN!</i></h3>";
		$resp .= "<p>¿ Esta transferencia \"{$folio}\" que escaneaste se recibirá junto con estas transferencias ?</p>";
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
/*
	<div class=\"col-5\">
						<span>Escribe la palabra SEPARADO si la transferencia se recibirá aparte</span>
						<input type=\"text\" id=\"separate_option\" class=\"form-control\" placeholder=\"separado\">
					</div>
*/
		$resp .= "<div class=\"row\">
					<div class=\"col-3\"></div>
					<div class=\"col-6\">
						<span>Escribe la palabra JUNTO si la transferencia se recibirá junto a estas transferencias</span>
						<input type=\"text\" id=\"together_option\" class=\"form-control\" placeholder=\"junto\">
					</div>
					
					<div class=\"col-3\"></div>

					<div class=\"col-3\"></div>
					<div class=\"col-6\">
						<button
							class=\"btn btn-success form-control\"
							onclick=\"option_add_transfer_validation({$transfer_id}, {$reception_block_id} );\"
						>
							<i class=\"\">Aceptar</i>
						</button>
						<br><br>
						<button
							class=\"btn btn-danger form-control\"
							onclick=\"close_emergent();\"
						>
							<i class=\"\">Cancelar</i>
						</button>
					</div>
				</div>";
		$resp .= "";
		$resp .= "";
		$link->autocommit( true );
		return $resp;
	}
		
	function getTransfersListValidation( $link ){
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
					t.folio,
					s1.nombre AS origin,
					s2.nombre AS destination,
					ts.nombre AS status,
					IF( tvd.id_bloque_transferencia_validacion IS NULL, '', tvd.id_bloque_transferencia_validacion ) AS block
				FROM ec_transferencias t
				LEFT JOIN sys_sucursales s1 ON s1.id_sucursal = t.id_sucursal_origen
				LEFT JOIN sys_sucursales s2 ON s2.id_sucursal = t.id_sucursal_destino
				LEFT JOIN ec_estatus_transferencia ts ON ts.id_estatus = t.id_estado
				LEFT JOIN ec_bloques_transferencias_validacion_detalle tvd
				ON tvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion tv
				ON tv.id_bloque_transferencia_validacion = tvd.id_bloque_transferencia_validacion
				WHERE t.id_estado IN( 3, 4, 5, 6 )
				AND t.id_transferencia > 0";
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : " . $link->error );
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

	function receiveUniqueCode( $id, $link ){
		$sql = "UPDATE ec_transferencia_codigos_unicos 
					SET id_status_transferencia_codigo = 4
				WHERE id_transferencia_codigo = {$id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar el código único a actualizado en piezas : " . $link->error );
		return 'ok';
	}

	function showUnicCodesPendingToRecive( $validations_blocks, $link ){
		$sql = "SELECT
					tcu.id_transferencia_codigo AS transfer_code_id,
					CONCAT( p.nombre, ' CLAVE PROVEEDOR : ', pp.clave_proveedor ) AS product_name,
					tcu.codigo_unico AS unic_code
				FROM ec_transferencia_codigos_unicos tcu
				LEFT JOIN ec_transferencias_validacion_usuarios tvu
				ON tcu.id_transferencia_validacion = tvu.id_transferencia_validacion
				LEFT JOIN ec_productos p
				ON p.id_productos = tvu.id_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tvu.id_proveedor_producto
				WHERE tcu.id_transferencia_recepcion IS NULL
				AND tcu.id_bloque_transferencia_validacion = {$validations_blocks}";//validations_blocks
		$stm = $link->query( $sql ) or die( "Error al consultar los códigos únicos pendientes de validar : " . $link->error );
		$resp = "<table class=\"table\">";
		if( $stm->num_rows <= 0 ){
				$resp .= "<tr><td class=\"text-center\">No hay <b>Códigos Únicos</b> pendientes de recibir</td></tr>";
		}else{
			$resp .= "<thead>
					<tr>
						<th>Producto</th>
						<th>Código Único</th>
						<th>Quitar</th>
					</tr>
				</thead>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr><td>{$row['product_name']}</td>";
				$resp .= "<td>{$row['unic_code']}</td>";
				$resp .= "<td><button
								type=\"button\"
								class=\"btn btn-info\"
								onclick=\"receive_unique_code( this, {$row['transfer_code_id']} );\"
							>
								Recibir
							</button>
						</td>
					</tr>";
			}
		}
		$resp .= "</table>";
		$resp .= "<div class=\"row\">
					<div class=\"col-2\"></div>
					<div class=\"col-8\">
						<button
							type=\"button\"
							class=\"btn btn-success form-control\"
							onclick=\"close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\"
						>
							Aceptar
						</button>
					</div>
				</div>";
		return $resp;
	}

	function setTransferToReceive( $transfers_ids, $validation_blocks, $reception_blocks, $sucursal_id, $user_id, $new_transfers  = '', $link ){
		//elimina los registros transferencias que se encuentran en recepcion
		$link->autocommit( false );
		/*$validation_blocks = array();
		$reception_blocks = array();*/
//die( " error|transferers_ids : {$transfers_ids}, validation_blocks : {$validation_blocks}, reception_blocks : {$reception_blocks} " );
		//verifica que las transferencias solo pertenezcan al bloque
//	die( "Validation : " . $validation_blocks );

		$sql = "SELECT 
					id_bloque_transferencia_recepcion AS reception_block_id
				FROM ec_transferencias_recepcion_actual
				WHERE id_sucursal = {$sucursal_id}
				GROUP BY id_bloque_transferencia_recepcion";
		$stm = $link->query( $sql ) or die( "Error al consultar el bloque de recepcion actual : {$link->error}" );
		
		if( $stm->num_rows > 0 ){
			$row = $stm->fetch_assoc();
			if( $row['reception_block_id'] != $reception_blocks ){
				return "exception|<div class=\"text-center\">
	           					<h5>La(s) transferencia(s) Que intentas validar no correponden al bloque que se esta recibiendo actualmente, 
	           					verifica y vuelve a intentar</h5>
		           				<button
		           					type=\"button\"
		           					class=\"btn btn-success\"
		           					onclick=\"location.reload();\"
		           				>
		           					<i class=\"icon-ok-circle\">Aceptar</i>
		           				</button>
	           				</div>";
			}
		}
		//	die( $sql );
		if( $reception_blocks == '' ){
		//verifica que los bloques no esten enlazados a un bloque de recepcion
			$sql = "SELECT t.id_transferencia AS transfer_id,
						   t.folio,
						   btrd.id_bloque_transferencia_recepcion 
					FROM ec_transferencias t
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btvd.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion btv
					ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
					WHERE t.id_transferencia IN ( {$transfers_ids} )
					AND btrd.id_bloque_transferencia_recepcion IS NOT NULL
					/*AND btrd.id_bloque_transferencia_recepcion IN( {$validation_blocks} )*/";
          // die( $sql );
           	$stm = $link->query( $sql ) or die( "Error al validar que no haya bloques creados anteriormente : {$link->error} {$sql}" );
           	
           	$block_recepcion_array = explode( ',', $reception_blocks );

           	if( $stm->num_rows > 0 || sizeof( $block_recepcion_array ) > 1 ){
           		return "exception|<div class=\"text-center\">
           					<h5>No se pueden recibir transferencias de diferentes bloques, da click en aceptar para recargar la pantalla y vuelva a intentar</h5>
	           				<button
	           					type=\"button\"
	           					class=\"btn btn-success\"
	           					onclick=\"location.reload();\"
	           				>
	           					<i class=\"icon-ok-circle\">Aceptar</i>
	           				</button>
           				</div>";
           	}
		//inserta el bloque de recepcion
			$sql = "INSERT INTO ec_bloques_transferencias_recepcion ( id_bloque_transferencia_recepcion, fecha_alta, recibido )
					VALUES( NULL, NOW(), 0 )";
			$stm = $link->query( $sql ) or die( "Error al insertar bloque de recepcion de Transferencia : {$link->error}" );
			$reception_blocks = $link->insert_id;
		//inserta los detalles del bloque de recepción
			$sql = "INSERT INTO ec_bloques_transferencias_recepcion_detalle ( id_bloque_transferencia_recepcion_detalle, id_bloque_transferencia_recepcion,
				id_bloque_transferencia_validacion, fecha_alta )
				SELECT 
					NULL,
					{$reception_blocks},
					btvd.id_bloque_transferencia_validacion,
					NOW()
				FROM ec_bloques_transferencias_validacion btvd
				WHERE btvd.id_bloque_transferencia_validacion IN( {$validation_blocks} )";
			$stm = $link->query( $sql ) or die( "Error al insertar detalles de bloques de recepcion de Transferencia : {$sql} {$link->error}" );

		}else{//inserta los detalles del bloque de recepción
			
			$sql = "SELECT t.id_transferencia AS transfer_id,
						   t.folio,
						   btrd.id_bloque_transferencia_recepcion 
					FROM ec_transferencias t
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btvd.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion btv
					ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
					WHERE t.id_transferencia IN ( {$transfers_ids} )
					/*AND btrd.id_bloque_transferencia_recepcion IN( {$validation_blocks} )*/
                    AND btrd.id_bloque_transferencia_recepcion NOT IN( {$reception_blocks} )";
           	$stm = $link->query( $sql ) or die( "Error al validar que no haya bloques equivocados : {$link->error} {$sql}" );
           	
           	$block_recepcion_array = explode( ',', $reception_blocks );

           	if( $stm->num_rows > 0 || sizeof( $block_recepcion_array ) > 1 ){
           		return "exception|<div class=\"text-center\">
           					<h5>No se pueden recibir transferencias de diferentes bloques, da click en aceptar para recargar la pantalla y vuelva a intentar</h5>
	           				<button
	           					type=\"button\"
	           					class=\"btn btn-success\"
	           					onclick=\"location.reload();\"
	           				>
	           					<i class=\"icon-ok-circle\">Aceptar</i>
	           				</button>
           				</div>";
           	}
			$sql = "INSERT INTO ec_bloques_transferencias_recepcion_detalle ( id_bloque_transferencia_recepcion_detalle, id_bloque_transferencia_recepcion,
				id_bloque_transferencia_validacion, fecha_alta )
				SELECT 
					NULL,
					{$reception_blocks},
					btv.id_bloque_transferencia_validacion,
					NOW()
				FROM ec_bloques_transferencias_validacion btv
				WHERE btv.id_bloque_transferencia_validacion IN( {$validation_blocks} )
				AND btv.id_bloque_transferencia_validacion 
				NOT IN( SELECT
							id_bloque_transferencia_validacion
						FROM ec_bloques_transferencias_recepcion_detalle 
						WHERE id_bloque_transferencia_recepcion IN( {$reception_blocks} )
					)";	
			$stm = $link->query( $sql ) or die( "Error al agregar bloques de validacion a bloques de recepción : {$link->error} {$sql}" );//die( $sql );		
		}
/*implementacion Oscar 2023 para actualizar el id de bloque de recepcion relacionado a los bloques de validacion*/
			$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
						SET id_bloque_recepcion = {$reception_blocks}
					WHERE id_bloque_validacion IN( {$validation_blocks} )";
			$upd = $link->query( $sql ) or die( "Error al actualizar las sesiones de validacion relacionadas al bloque : {$link->error}" );
/*fin de cambio Oscar 2023*/

	//elimina los registros anteriores
		$sql = "DELETE FROM ec_transferencias_recepcion_actual WHERE id_sucursal = {$sucursal_id}";
		$link->query( $sql ) or die( "Error al eliminar los registros de transferencias por recibir : {$link->error}" );
		$transfers_array = explode( ',', $transfers_ids );

		foreach ( $transfers_array as $key => $transfer ) {
			$sql = "INSERT INTO ec_transferencias_recepcion_actual (
					 /*1*/id_transferencia_recepcion_actual,
					/*2*/id_sucursal,
					/*3*/id_bloque_transferencia_validacion,
					/*4*/id_bloque_transferencia_recepcion,
					/*5*/id_usuario_alta,
					/*6*/fecha_alta )
				SELECT
					/*id_transferencia_recepcion_actual*/NULL,
					/*id_sucursal*/'{$sucursal_id}',
					/*id_bloque_transferencia_validacion*/btv.id_bloque_transferencia_validacion,
					/*id_bloque_transferencia_recepcion*/btrd.id_bloque_transferencia_recepcion,
					/*id_usuario_alta*/'{$user_id}',
					/*fecha_alta*/NOW()
				FROM ec_bloques_transferencias_validacion btv
				LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
				ON btv.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
				WHERE btv.id_bloque_transferencia_validacion IN( {$validation_blocks} )
				GROUP BY btrd.id_bloque_transferencia_recepcion";
			$link->query( $sql ) or die( "Error al insertar la transferencia por recibir : {$link->error}" );

		}
	//actualiza el status de usuarios bloqueados en recpecion 
		$sql = "UPDATE ec_bloqueos_recepcion_transferencia 
					SET status = 2
				WHERE id_bloque_recepcion = {$reception_blocks}";
		$stm = $link->query( $sql ) or die( "Error al insertar la actualizar el bloqueo de usuarios en recepción : {$link->error}" );
	//actualiza el bloqueo del bloque de recpecion 
		$sql = "UPDATE ec_bloques_transferencias_recepcion 
					SET bloqueado = '0' 
				WHERE id_bloque_transferencia_recepcion = {$reception_blocks}";
		$stm = $link->query( $sql ) or die( "Error al bloquear el bloque de transferencia : {$link->error}" );
		if( $new_transfers != '' ){
			//echo 'here|here_';
			include ( 'reassignResolution.php' );
			$reassignResolution = new reassignResolution( $link, $reception_blocks );
			$transfers_array = explode(',', $new_transfers );
			foreach ( $transfers_array as $key => $transfer_id ) {
				$reassign = $reassignResolution->assignResolutionProductsToTransfer( $transfer_id );
				if( $reassign != true ){
					die( "Error : {$reassign}" );
				}
			}
		}
		$link->autocommit( true );	
		return "ok|{$validation_blocks}|{$reception_blocks}";
	}


	function getTransfersToReceive( $store_id, $user_profile_id, $link ){
	//implementacion Oscar 2023 para listar transferencias de acuerdo a la configuracion
		$sql = "SELECT 
					ttv.status_transferencias AS transfer_status
				FROM sys_configuracion_sistema cs
				LEFT JOIN sys_tipos_transferencias_validacion ttv
				ON ttv.id_tipo_transferencia_validacion = cs.tipo_recepcion_transferencia
				WHERE cs.id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar configuracion de transferencias : {$link->error}" );
		$row = $stm->fetch_assoc();
		$transfer_status = $row['transfer_status'];
	//fin de cambio Oscar 2023
		$resp = '';
		$sql = "SELECT 
					IF( COUNT( btvd.id_bloque_transferencia_validacion_detalle ) <= 0,
						'Sin bloque',
						COUNT( btvd.id_bloque_transferencia_validacion_detalle )
					) AS counter,
					IF( COUNT( btrd.id_bloque_transferencia_recepcion ) <= 0,
						'Sin bloque',
						COUNT( btrd.id_bloque_transferencia_recepcion )
					) AS counter_reception,
					btr.id_bloque_transferencia_recepcion AS reception_blocks,
					btv.id_bloque_transferencia_validacion AS validation_blocks,
					t.id_transferencia AS transfer_id,
					t.folio AS folio,
					/*GROUP_CONCAT( CONCAT( '<div>', t.id_transferencia, '</div>' ) SEPARATOR '<br>' ) AS folio,*/
					CONCAT( t.fecha, ' ', t.hora ) AS date_time,
					t.id_almacen_origen AS origin_warehouse,
					t.id_almacen_destino AS destinity_warehouse
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion btr
				ON btr.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
				WHERE t.id_estado IN( {$transfer_status} )/*implementacion Oscar 2023*/
				AND t.id_sucursal_destino = '{$store_id}'
				GROUP BY t.id_transferencia
				/*GROUP BY btr.id_bloque_transferencia_recepcion, btrd.id_bloque_transferencia_recepcion_detalle*/";
	//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar las transferencias por recibir : " . $link->error );
	//consulta el permiso para asignar
		$sql = "SELECT 
					IF( ver = 1 OR modificar = 1 OR eliminar = 1 OR nuevo = 1 OR imprimir = 1 OR generar = 1, 1, 0 )
					AS permission
				FROM sys_permisos 
				WHERE id_menu = 242
				AND id_perfil = {$user_profile_id}";
		$stm_perm = $link->query( $sql ) or die( "Error al consultar permisos del perfil de usuario : {$link->error}" );
		$row = $stm_perm->fetch_assoc();
		$edit_permission = $row['permission'];
		$disabled = ( $edit_permission == 1 ? '' : 'disabled' );

		$counter = 0;
		$current_block = 'null';
		$color = "red";
		$blocks_counter = 0;

		while ( $row = $stm->fetch_assoc() ) {
			if( $current_block != $row['reception_blocks'] ){
				$blocks_counter ++;
				$current_block = $row['reception_blocks'];
				$color = ( $blocks_counter % 2 ? 'rgba( 0, 0, 0, .3 )' : 'rgba( 225, 0, 0, .3 )' );
				
			}
			$resp .= "<tr style=\"background : {$color};\">";
			$resp .= "<td rowspan=\"1\" id=\"reception_list_0_{$counter}\" class=\"text-center\" style=\"vertical-align : middle !important;\">";	
			//$resp .= $row['counter_reception'];
			$resp .= "<input 
						type=\"checkbox\" 
						id=\"reception_block_{$counter}\" 
						style=\"transform : scale( 1.6 );\"
						value=\"{$row['reception_blocks']}\"
						onclick=\"setGlobalBlock( {$counter} );\">
					{$row['reception_blocks']}";

			if( $current_block != $row['reception_blocks']  ){//{$row['reception_blocks']}{$row['counter']}{$row['reception_blocks']}
				/*if ( $row['counter_reception'] != 'Sin bloque' ){
					$resp .= "<div></div>";
					$resp .= "<input 
								type=\"checkbox\" 
								id=\"reception_block_{$counter}\" 
								style=\"transform : scale( 1.6 );\"
								value=\"\"
								{$disabled}>";
				}else{
					$resp .= $row['counter_reception'];
				}*/
				
			}
				$resp .= "</td>";
				$resp .= "<td class=\"text-center\"><i class=\"icon-barcode btn btn-warning\" id=\"validation_list_9_{$counter}\" style=\"font-size : 120%;\"></i></td>";
				$resp .= "<td id=\"reception_list_1_{$counter}\" class=\"text-center\">{$row['validation_blocks']}</td>";
				$resp .= "<td id=\"reception_list_2_{$counter}\"class=\"no_visible\">{$row['transfer_id']}</td>";
				//$resp .= "<td></td>";
				$resp .= "<td id=\"reception_list_3_{$counter}\" class=\"text-center\" style=\"font-size : 80%;\">{$row['folio']}</td>";
				$resp .= "<td id=\"reception_list_4_{$counter}\" class=\"text-center no_visible\">{$row['date_time']}</td>";
				$resp .= "<td id=\"reception_list_5_{$counter}\" class=\"text-center no_visible\">";
				$resp .= " <input 
								type=\"checkbox\" 
								id=\"receive_{$counter}\" 
								onclick=\"getAllGroup( {$counter}, '.transfers_list_content' )\"
								value=\"{$row['validation_blocks']}\"
								disabled>
						</td>";
				$resp .= "<td class=\"no_visible\" id=\"reception_list_6_{$counter}\">{$current_block}</td>";
				$resp .= "<td class=\"text-center\">
							<button 
								type=\"button\" 
								class=\"btn btn-\"
								onclick=\"print_block_ticket( {$row['reception_blocks']} )\"
							>
								<img src=\"../../../../img/impresion_tkt.png\" width=\"30px\">
							</button>
							<button 
								type=\"button\" 
								class=\"btn btn\"
								onclick=\"block_ticket_pdf( {$row['reception_blocks']} )\"
							>
								<img src=\"../../../../img/img_casadelasluces/pdf_icon.png\" width=\"30px\">
							</button>
						</td>";
		/*implementacion Oscar 2023*/
			$resp .= "<td id=\"reception_list_10_{$counter}\" class=\"text-center no_visible\" style=\"font-size : 80%;\">{$row['origin_warehouse']}</td>";
			$resp .= "<td id=\"reception_list_11_{$counter}\" class=\"text-center no_visible\">{$row['destinity_warehouse']}</td>";
		/*fin de cambio Oscar 2023*/
			$resp .= "</tr>";

			$current_block = $row['reception_blocks'];
			$counter ++;
		}
		return $resp;
	}

	/*function getTransfersToReceive ( $sucursal, $user_profile_id, $link  ){
		$sql = "SELECT 
					id_bloque_transferencia_recepcionPrimary
				FROM ec_bloques_transferencias_recepcion
				WHERE recibido = '0'";
		$stm = $link->query( $sql ) or die( "Error al consultar los bloques de recpcion : {$link->error}" );
		
		//lista los bloques que no estan en ningún bloque de transferencia
			$sql = "SELECT
						id_bloque_transferencia_validacion,
						t.folio,
						CONCAT( t.fecha, ' ', t.hora ) AS transfer_date_time,
					FROM ec_bloques_transferencias_validacion_detalle btvd
					LEFT JOIN ec_transferencias t
					ON t.id_transferencia = btvd.id_transferencia
					WHERE t.id_estado = 8";
		while ( <= 10) {
			$sql = "SELECT 
					IF( COUNT( btvd.id_bloque_transferencia_validacion_detalle ) <= 0,
						'Sin bloque',
						COUNT( btvd.id_bloque_transferencia_validacion_detalle )
					) AS counter,
					IF( COUNT( btrd.id_bloque_transferencia_recepcion ) <= 0,
						'Sin bloque',
						COUNT( btrd.id_bloque_transferencia_recepcion )
					) AS counter_reception,
					btr.id_bloque_transferencia_recepcion AS reception_blocks,
					btv.id_bloque_transferencia_validacion AS validation_blocks,
					t.id_transferencia AS transfer_id,
					GROUP_CONCAT( CONCAT( '<div>', t.id_transferencia, '</div>' ) SEPARATOR '<br>' ) AS folio,
					CONCAT( t.fecha, ' ', t.hora ) AS date_time
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion btr
				ON btr.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
				WHERE t.id_estado = 8
				AND t.id_sucursal_destino = '{$sucursal}'
				GROUP BY btr.id_bloque_transferencia_recepcion, btrd.id_bloque_transferencia_recepcion_detalle";

		}
	}*/

/*	function getTransfersToReceive( $store_id, $user_profile_id, $link ){
		$resp = '';
		$sql = "SELECT 
					IF( COUNT( btvd.id_bloque_transferencia_validacion_detalle ) <= 0,
						'Sin bloque',
						COUNT( btvd.id_bloque_transferencia_validacion_detalle )
					) AS counter,
					IF( COUNT( btrd.id_bloque_transferencia_recepcion ) <= 0,
						'Sin bloque',
						COUNT( btrd.id_bloque_transferencia_recepcion )
					) AS counter_reception,
					btr.id_bloque_transferencia_recepcion AS reception_blocks,
					btv.id_bloque_transferencia_validacion AS validation_blocks,
					t.id_transferencia AS transfer_id,
					t.folio AS folio,
					CONCAT( t.fecha, ' ', t.hora ) AS date_time
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_recepcion btr
				ON btr.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
				WHERE t.id_estado = 8
				AND t.id_sucursal_destino = '{$store_id}'
				GROUP BY t.id_transferencia";

		$stm = $link->query( $sql ) or die( "Error al consultar las transferencias por recibir : " . $link->error );
	//consulta el permiso para asignar
		$sql = "SELECT 
					IF( ver = 1 OR modificar = 1 OR eliminar = 1 OR nuevo = 1 OR imprimir = 1 OR generar = 1, 1, 0 )
					AS permission
				FROM sys_permisos 
				WHERE id_menu = 242
				AND id_perfil = {$user_profile_id}";
		$stm_perm = $link->query( $sql ) or die( "Error al consultar permisos del perfil de usuario : {$link->error}" );
		$row = $stm_perm->fetch_assoc();
		$edit_permission = $row['permission'];
		$disabled = ( $edit_permission == 1 ? '' : 'disabled' );

		$counter = 0;
		$current_block = 'null';
		$color = "red";
		$blocks_counter = 0;

		while ( $row = $stm->fetch_assoc() ) {
			if( $current_block != $row['reception_blocks'] ){
				$blocks_counter ++;
				$current_block = $row['reception_blocks'];
				$color = ( $blocks_counter % 2 ? 'rgba( 0, 0, 0, .3 )' : 'rgba( 225, 0, 0, .3 )' );
				
			}
			$resp .= "<tr style=\"background : {$color};\">";
			$resp .= "<td rowspan=\"1\" id=\"reception_list_0_{$counter}\" class=\"text-center\" style=\"vertical-align : middle !important;\">";	
			
			$resp .= "<input 
						type=\"checkbox\" 
						id=\"reception_block_{$counter}\" 
						style=\"transform : scale( 1.6 );\"
						value=\"{$row['reception_blocks']}\"
						onclick=\"setGlobalBlock( {$counter} );\">
					{$row['reception_blocks']}";

			if( $current_block != $row['reception_blocks']  ){
				
			}
				$resp .= "</td>";
				$resp .= "<td class=\"text-center\"><i class=\"icon-barcode btn btn-warning\" id=\"validation_list_9_{$counter}\" style=\"font-size : 120%;\"></i></td>";
				$resp .= "<td id=\"reception_list_1_{$counter}\" class=\"text-center\">{$row['validation_blocks']}</td>";
				$resp .= "<td id=\"reception_list_2_{$counter}\"class=\"no_visible\">{$row['transfer_id']}</td>";
				
				$resp .= "<td id=\"reception_list_3_{$counter}\" class=\"text-center\" style=\"font-size : 80%;\">{$row['folio']}</td>";
				$resp .= "<td id=\"reception_list_4_{$counter}\" class=\"text-center no_visible\">{$row['date_time']}</td>";
				$resp .= "<td id=\"reception_list_5_{$counter}\" class=\"text-center no_visible\">";
				$resp .= " <input 
								type=\"checkbox\" 
								id=\"receive_{$counter}\" 
								onclick=\"getAllGroup( {$counter}, '.transfers_list_content' )\"
								value=\"{$row['validation_blocks']}\"
								disabled>
						</td>";
				$resp .= "<td class=\"no_visible\" id=\"reception_list_6_{$counter}\">{$current_block}</td>";
			$resp .= "</tr>";

			$current_block = $row['reception_blocks'];
			$counter ++;
		}
		return $resp;
	}*/

	function getTransfersToCorrection( $sucursal_id, $link ){
	//
		$sql = "SELECT 
					tbae.id_bloque_autorizacion_edicion,
					tbae.id_bloque_transferencia_recepcion/*,
					GROUP_CONCAT(  )*/
				FROM ec_transferencias_bloques_autorizacion_edicion tbae
				LEFT JOIN ec_bloques_transferencias_recepcion btv
				ON btv.id_bloque_transferencia_recepcion = tbae.id_bloque_transferencia_recepcion
				WHERE tbae.id_sucursal = {$sucursal_id}
				AND tbae.editado = 0";
		$stm = $link->query( $sql ) or die( "Error al consultar las ediciones de transferencias permitidas : {$link->error}" );
		$resp = "<div class=\"row\">";
			$resp .= "<div class=\"col-1\"></div>";
			$resp .= "<div class=\"col-10\">";
				$resp .= "<table class=\"table\">";
					$resp .= "<thead>";
						$resp .= "<tr>";
							$resp .= "<th>Bloque<br>Recepcion</th>";
							$resp .= "<th>Bloque<br>Validación</th>";
							$resp .= "<th>Transferencias</th>";
						$resp .= "</tr>";
					$resp .= "</thead>";
					$resp .= "<tbody id=\"transfers_to_edit_list\">";
					$resp .= "</tbody>";
				$resp .= "</table>";
			$resp .= "</div>";
		$resp .= "</div>";

		$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">";
				$resp .= "<button class=\"btn btn-success form-control\" onclick=\"setTransferToReceive( '#transfers_to_edit_list' );\">";
					$resp .= "<i class=\"icon-ok-circle\">Editar</i>";
				$resp .= "</button>";
				$resp .= "<br><br>";
				$resp .= "<button class=\"btn btn-danger form-control\" onclick=\"close_emergent();\">";
					$resp .= "<i class=\"icon-ok-circle\">Cancelar</i>";
				$resp .= "</button>";

			$resp .= "</div>";
		$resp .= "</div>";
		return $resp;
	}


	function  validateManagerPassword( $password, $sucursal_id, $link ){
		$sql = "SELECT 
					u.id_usuario 
				FROM sys_users u 
				LEFT JOIN sys_sucursales s
				ON s.id_encargado = u.id_usuario
				WHERE u.contrasena = md5( '{$password}' )
				AND s.id_sucursal = {$sucursal_id}";
		$stm = $link->query( $sql ) or die( "Error al verificar password de encargado : " . $link->error );
		if( $stm->num_rows <= 0 ){
			die( 'La contraseña del encargado es incorrecta.' );
		}
		return 'ok';
	}
/*cargar ultimas recepciones*/
	function loadLastReceptions( $transfers, $user, $sucursal, $link ){
		$sql = "SELECT
					tru.id_transferencia_recepcion AS transfer_reception_id,
					p.id_productos AS product_id,
					CONCAT( p.nombre, ' ( CLAVE PROVEEDOR : <b>', pp.clave_proveedor, '</b> )' ) AS name,
					t.folio AS transfer,
					IF(	tru.cantidad_cajas_recibidas > 0, 
						CONCAT( tru.cantidad_cajas_recibidas, ' caja', IF( tru.cantidad_cajas_recibidas > 1, 's', '' )),
						IF( tru.cantidad_paquetes_recibidos > 0,
							CONCAT( tru.cantidad_paquetes_recibidos, ' paquete', IF( tru.cantidad_cajas_recibidas > 1, 's', '' )),
							CONCAT( tru.cantidad_piezas_recibidas, ' pieza', IF( tru.cantidad_piezas_recibidas > 1, 's', '' ))
						)
					) AS recived,
					pp.id_proveedor_producto AS product_provider_id,
					sp.ubicacion_almacen_sucursal AS location
				FROM ec_transferencias_recepcion_usuarios tru
				LEFT JOIN ec_transferencia_productos tp 
				ON tru.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tru.id_producto = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tru.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN sys_sucursales_producto sp 
				ON sp.id_producto = pp.id_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND tru.id_usuario = '{$user}'
				AND sp.id_sucursal = '{$sucursal}'
				ORDER BY tru.id_transferencia_recepcion DESC
				LIMIT 3";
				//die( $sql );
		$stm = $link->query( $sql )or die( "Error al consultar las últimas revisiones : " . $link->error );
		return buildLastReceptions( $stm );	
	}

	function buildLastReceptions( $stm ){
		$resp = '';
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= '<tr>';
				$resp .= '<td class="no_visible">' . $row['transfer_reception_id'] . '</td>';
				$resp .= '<td>' . $row['name'] . '</td>';
				$resp .= '<td>' . $row['recived'] . '</td>';
				$resp .= '<td style="font-size : 50%;">' . $row['transfer'] . '<br /> <b>Ubicación : ' . $row['location'] . '</b></td>';
				$resp .= '<td><button class="btn btn-warning"';
				$resp .= ' onclick="getReceptionProductDetail(' . $row['product_id'] . ', ' . $row['product_provider_id'] . ' );"><i class="icon-eye"></i></button></td>';
			$resp .= '</tr>';
		}
		return $resp;
	}
//resumen
	function getReceptionResumen( $type, $transfers, $block_id, $link ){
		$resp = "";
		$sql = "";
		//die( 'type : ' . $type );
		switch ( $type ) {
			case 1:
				//$final_type = 'missing';
				$sql = "SELECT 
							GROUP_CONCAT( tp.id_transferencia_producto SEPARATOR '-' )AS transfer_product_id,
							CONCAT( '( <b>', p.orden_lista, '</b>) ', p.nombre, 
									IF( pp.id_proveedor_producto IS NULL, 
										'',
										CONCAT( ' ( CLAVE PROVEEDOR : ', pp.clave_proveedor, ' ) ' )
									) 

							) AS name,
							SUM( tp.cantidad  - tp.total_piezas_recibidas ) AS difference,
							tp.id_producto_or AS product_id,
							tp.id_proveedor_producto AS product_provider_id,
							tp.id_transferencia AS transfer_id,
							'missing' AS type,
							IF( tr.id_bloque_transferencia_resolucion IS NULL 
								OR tr.piezas_sobrantes > 0 
								OR tr.piezas_no_corresponden > 0, 
								'', 
								tr.id_bloque_transferencia_resolucion ) AS was_solved
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_bloques_transferencias_resolucion tr
						ON tr.id_proveedor_producto = tp.id_proveedor_producto
						AND tr.id_bloque_transferencia_recepcion = {$block_id}
						LEFT JOIN ec_productos p 
						ON tp.id_producto_or = p.id_productos
						LEFT JOIN ec_proveedor_producto pp
						ON tp.id_proveedor_producto = pp.id_proveedor_producto
						WHERE tp.id_transferencia IN( {$transfers} )
						AND ( tp.cantidad - tp.total_piezas_recibidas ) > 0
						AND tp.id_transferencia_producto IS NOT NULL
						GROUP BY tp.id_proveedor_producto";
//die( $sql );
			break;
			
			case 2:
				//$final_type = 'excedent';
				$sql = "SELECT 
							btr.id_bloque_transferencia_resolucion AS block_resolution_id,
							CONCAT( '( <b>', p.orden_lista, '</b>) ', p.nombre, 
								' ( CLAVE PROVEEDOR : ', pp.clave_proveedor, ' ) ' ) AS name,
							IF ( btr.piezas_sobrantes <= 0, 
								btr.piezas_no_corresponden, 
								btr.piezas_sobrantes ) AS difference,
							btr.id_producto AS product_id,
							'' AS transfer_id,
							'excedent' AS type,
							btr.resuelto AS was_solved
						FROM ec_bloques_transferencias_resolucion btr
						LEFT JOIN ec_productos p
						ON btr.id_producto = p.id_productos
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_proveedor_producto = btr.id_proveedor_producto
						WHERE btr.id_bloque_transferencia_recepcion = {$block_id}
						AND ( btr.piezas_sobrantes > 0 OR btr.piezas_no_corresponden > 0 )";
			break;
			
			case 3:
				
				$sql = "SELECT 
							GROUP_CONCAT( tp.id_transferencia_producto SEPARATOR '-' )AS transfer_product_id,
							CONCAT( '( <b>', p.orden_lista, '</b>) ', p.nombre, 
									IF( pp.id_proveedor_producto IS NULL, 
										'',
										CONCAT( ' ( CLAVE PROVEEDOR : ', pp.clave_proveedor, ' ) ' )
									) 

							) AS name,
							SUM( tp.total_piezas_recibidas ) AS difference,
							tp.id_producto_or AS product_id,
							tp.id_proveedor_producto AS product_provider_id,
							tp.id_transferencia AS transfer_id,
							'missing' AS type,
							IF( tr.id_bloque_transferencia_resolucion IS NULL 
								OR tr.piezas_sobrantes > 0 
								OR tr.piezas_no_corresponden > 0, 
								'', 
								tr.id_bloque_transferencia_resolucion ) AS was_solved
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_bloques_transferencias_resolucion tr
						ON tr.id_proveedor_producto = tp.id_proveedor_producto
						AND tr.id_bloque_transferencia_recepcion = {$block_id}
						LEFT JOIN ec_productos p 
						ON tp.id_producto_or = p.id_productos
						LEFT JOIN ec_proveedor_producto pp
						ON tp.id_proveedor_producto = pp.id_proveedor_producto
						WHERE tp.id_transferencia IN( {$transfers} )
						AND tp.total_piezas_recibidas > 0
						AND tp.id_transferencia_producto IS NOT NULL
						GROUP BY tp.id_proveedor_producto";
			break;
			
			default:
				return 'Permission denied on getReceptionResumen!';	
			break;
		}

		$stm = $link->query( $sql ) or die( "Error al consultar los productos del resumen : {$link->error}");
		$total_rows = $stm->num_rows;
		$counter = 0;
		while ( $row = $stm->fetch_assoc() ){
			$row['was_solved'] = ( $row['was_solved'] == 0 ? '' : $row['was_solved'] );
			//$color = ( $row['was_solved'] != ''  ? 'green; color : white;' : '' );
			$resp .= "<tr id=\"{$row['type']}_row_{$counter}\" style=\"background-color : {$color};\">";
				$resp .= "<td id=\"{$row['type']}_row_1_{$counter}\" class=\"no_visible\">{$row['transfer_product_id']}</td>";
				$resp .= "<td id=\"{$row['type']}_row_2_{$counter}\">{$row['name']}</td>";
				$resp .= "<td id=\"{$row['type']}_row_3_{$counter}\" class=\"text-center\">{$row['difference']}</td>";
				$resp .= "<td id=\"{$row['type']}_row_4_{$counter}\" class=\"no_visible\"></td>";
				$resp .= "<td id=\"{$row['type']}_row_5_{$counter}\" class=\"no_visible\">{$row['was_solved']}</td>";
			$resp .= "</tr>";
			$counter ++;
		}
		return $total_rows . '|' . $resp;// . " - {$sql}";// . $sql
	}


	function insertNewProductReception( $transfers, $product_id, $product_provider_id, $box, $pack, $piece, $link ){
	//verifica a ue transferencia se le asignara el producto
		$sql = "SELECT 
					t.id_transferencia AS transfer_id,
					ma.id_movimiento_almacen AS mov_id,
					SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) AS difference
				FROM ec_transferencias t
				LEFT JOIN ec_transferencia_productos tp
				ON t.id_transferencia = tp.id_transferencia
				LEFT JOIN ec_movimiento_almacen ma
				ON ma.id_transferencia = t.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or IN( {$product_id} )
				ORDER BY SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) DESC
				LIMIT 1";
		$stm = $link->query( $sql ) or die( "Error al consultar en que transferencia esta el producto : " . $link->error );
	//vuelve a validar que el producto exista en alguna transferencia
		if( $stm->num_rows <= 0 ){
			die( "error|<h5>El producto no pertence a ninguna Transferencia <br /> Aparta el producto de la transferencia para regresarlo</h5>" );
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
			/*19*/total_piezas_validacion, /*20*/cantidad_cajas_recibidas, /*21*/cantidad_paquetes_recibidos, 
			/*22*/cantidad_piezas_recibidas,/*23*/total_piezas_recibidas,/*24*/agregado_surtimiento_validacion )
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
			/*20*/'{$box}',
			/*21*/'{$pack}',
			/*22*/'{$piece}',
			/*23*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*24*/'1'
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
					tp.id_producto_or,
					tp.cantidad,
					tp.cantidad
					tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia_producto = '{$new_detail_id}'";
		$stm_detail = $link->query( $sql )or die( "Error al consultar el detalle para insertar detalle movimiento de almacen por procedure : " . $link->error );
		$detail_row = $stm_detail->fetch_assoc();
		$sql = "CALL spMovimientoAlmacenDetalle_inserta( {$mov_id}, {$detail_row['id_producto_or']}, {$detail_row['cantidad']}, {$detail_row['cantidad']}, -1, -1, {$detail_row['id_proveedor_producto']}, 8, NULL );";
		$stm = $link->query( $sql )or die( "Error al insertar el detalle del movimiento de almacen por procedure : {$sql} : {$link->error}" );
		return "El producto fue agregado y validado exitosamente!";
	}

//obtener detalle de la recepción
	function getReceptionProductDetail( $transfers, $product_id, $product_provider_id, $user, $link ){
		$sql = "SELECT
					tru.id_transferencia_recepcion AS row_id,
					( ( tru.cantidad_cajas_recibidas * pp.presentacion_caja )
					+ ( tru.cantidad_paquetes_recibidos * pp.piezas_presentacion_cluces )
					+ tru.cantidad_piezas_recibidas ) AS pieces_recived,
					CONCAT( u.nombre, 
							IF( u.apellido_paterno = '', '', CONCAT(' ', u.apellido_paterno) ), 
							IF( u.apellido_materno = '', '', CONCAT(' ', u.apellido_materno) ) 
					) AS user_name,
					tru.fecha_recepcion AS dateTime,
					tru.codigo_validacion AS validation_barcode,
					IF( tru.cantidad_cajas_recibidas != 0, 'box', 
						IF( tru.cantidad_piezas_recibidas != 0, 'pack', 'piece' )
					) AS type_barcode 
				FROM ec_transferencias_recepcion_usuarios tru
				LEFT JOIN ec_transferencia_productos tp
				ON tru.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = tp.id_transferencia
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tru.id_proveedor_producto
				LEFT JOIN sys_users u 
				ON u.id_usuario = tru.id_usuario
				WHERE tru.id_producto = '{$product_id}'
				AND tru.id_proveedor_producto = '{$product_provider_id}'
				/*AND tru.id_usuario = '{$user}'*/
				AND t.id_transferencia IN( {$transfers} )";
		$stm = $link->query( $sql ) or die( "Error al consultar historial de productos recibidos : " . $link->error . $sql );
		return buildReceptionProductDetail( $stm );
	}

	function buildReceptionProductDetail( $stm ){
		$user_name = '';
		$resp = "<div class=\"row group_card\">";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : green;\"></i>Códigos Únicos";
			$resp .= "</div>";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : yellow;\"></i>Caja / Paquete";
			$resp .= "</div>";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : red;\"></i>Pieza";
			$resp .= "</div>";
		$resp .= "</div>";
		$resp .= '<table class="table table-bordered table-striped">';
			$resp .= '<thead>';
				$resp .= '<tr>';
					$resp .= '<th>Piezas Recibidas</th>';
					$resp .= '<th>Escaneo</th>';
					$resp .= '<th>Fecha / hora</th>';
				$resp .= '</tr>';
			$resp .= '<thead>';
			$resp .= '<tbody>';
		while( $row = $stm->fetch_assoc() ){
			$color = '';
			if( $user_name != $row['user_name'] ){
				$resp .= '<tr>';
					$resp .= "<td colspan=\"3\">{$row['user_name']}</td>";
				$resp .= '</tr>';
			}
			$resp .= '<tr';
		//color de la fila
			if( $row['type_barcode'] == 'box' || $type_barcode == 'pack' ){
				$color = "yellow";
			}else{
				$color = "red";
			}
			$aux = explode($row['validation_barcode'], ' ');
			if( sizeof( $aux ) == 4 ){
				$color = "green";
			}
		//si fue por nombre quita el código de barras
			$row['validation_barcode'] = ( $row['validation_barcode'] == 'Por nombre' ? '' : $row['validation_barcode'] );

			$resp .= " style=\"background : {$color};\"";
			$resp .= '>';
				$resp .= '<td class="text-center">' . $row['pieces_recived'] . '</td>' ;
				$resp .= '<td class="text-center">' . $row['validation_barcode'] . '</td>' ;
				$resp .= '<td class="text-center">' . $row['dateTime'] . '</td>' ;
			$resp .= '</tr>';

			$user_name = $row['user_name'];
		}
			$resp .= '</tbody>';
		$resp .= '</table> <br />';
		$resp .= '<div class="row">';
			$resp .= '<div class="col-2"></div>';
			$resp .= '<div class="col-8">';
				$resp .= '<button class="btn btn-success form-control" onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">';
					$resp .= 'Aceptar';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		return $resp;
	}

	function getProductResolution( $transfer_product_id, $product_id, $type, $link, $difference, 
		$user, $sucursal, $transfers, $reception_block_id ){
		include( 'Resolution.php' );
		$resp = '';
		$Resolution = new Resolution( $link, $user, $sucursal );
		switch ( $type ) {
			case 'missing':
				$general_info = $Resolution->getTransferDetailInfoToResolve( $difference, $transfer_product_id, 1 );
				$resp = $Resolution->getFormMissing( $difference, $transfer_product_id, $transfers, $reception_block_id, $general_info );
			break;
			
			case 'excedent' :
				$general_info = $Resolution->getTransferDetailInfoToResolve( $difference, $transfer_product_id, 2 );
				$resp = $Resolution->getFormExcedent( $difference, $transfer_product_id, $transfers, $reception_block_id, $general_info );
			break;

			case 'does_not_correspond' :
				$general_info = $Resolution->getTransferDetailInfoToResolve( $difference, $transfer_product_id, 3 );
				$resp = $Resolution->getFormDoesntCorrespond( $difference, $transfer_product_id, $transfers, $reception_block_id, $general_info );
			break;

			default:
				die( "Action <b>'{$type}'</b> is not valid!
						<br>
						<button type=\"button\" onclick=\"close_emergent();\" class=\"btn btn-danger\">Cerrar</button>" );
			break;
		}
		return $resp;
	}

	function seekByName( $barcode, $link ){
		//die('|here');
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
			{$condition} OR p.orden_lista = '{$barcode}'  ) AND pp.id_proveedor_producto IS NOT NULL
			GROUP BY p.id_productos";
		$stm_name = $link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$link->error}" );
		if( $stm_name->num_rows <= 0 ){
			return 'message_info|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
			. '<div class="row"><div class="col-2"></div><div class="col-8">'
			. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\' );lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">Aceptar</button></div><br/><br/>';
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
								<th>Clave Prov</th>
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

	function getSpecialPermissions( $user_id, $store_id, $link ){
		$sql = "SELECT 
					perm.id_menu AS menu_id,
					IF( perm.ver = 1 OR perm.modificar = 1 OR perm.eliminar = 1 
						OR perm.nuevo = 1 OR perm.imprimir = 1 OR perm.generar = 1, 1, 0 ) AS permission
				FROM sys_permisos perm
				LEFT JOIN sys_users_perfiles up
				ON perm.id_perfil = up.id_perfil
				LEFT JOIN sys_users u 
				ON u.tipo_perfil = up.id_perfil
				WHERE perm.id_menu IN ( 242, 250, 259, 260 )
				AND u.id_usuario = {$user_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar permisos especiales : {$link->error}" );
		$row = $stm->fetch_assoc();
		$resp = "<input type=\"hidden\" id=\"make_transfer_permission\" value=\"{$row['permission']}\">";
		$row = $stm->fetch_assoc();
		$resp .= "<input type=\"hidden\" id=\"finish_transfer_permission\" value=\"{$row['permission']}\">";

		$row = $stm->fetch_assoc();
		$resp .= "<input type=\"hidden\" id=\"show_reception_blocks_permission\" value=\"{$row['permission']}\">";

		$row = $stm->fetch_assoc();
		$resp .= "<input type=\"hidden\" id=\"finish_resolution_permission\" value=\"{$row['permission']}\">";

		$sql = "SELECT
					id_bloque_transferencia_recepcion AS current_block
				FROM ec_transferencias_recepcion_actual
				WHERE id_sucursal = {$store_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar permisos especiales : {$link->error}" );
		$row = $stm->fetch_assoc();
		$resp .= "<input type=\"hidden\" id=\"current_store_reception_block\" value=\"{$row['current_block']}\">";

		return $resp;
	}

?>