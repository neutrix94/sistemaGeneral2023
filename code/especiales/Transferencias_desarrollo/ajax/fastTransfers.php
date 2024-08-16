<?php
	if( isset( $_GET['freeTransferFl'] ) || isset( $_POST['freeTransferFl'] ) ){
		include( '../../../../conect.php' );
		include( '../../../../conexionMysqli.php' );
		$fT = new fastTransfers( $link );
		$action =( isset( $_GET['freeTransferFl'] ) ? $_GET['freeTransferFl'] : $_POST['freeTransferFl'] );
		$resp = "";
		switch ( $action ) {
			case 'buildEmergent' : 
				$transfer_id = ( isset( $_POST['transfer_id'] ) ? $_POST['transfer_id'] : $_GET['transfer_id'] );
				$transfer_info = $fT->getTransferInfo( $transfer_id );
				$id_tipo = $transfer_info['transfer_type'];
				$id_estado = $transfer_info['transfer_status'];
				echo $fT->buildEmergent( $id_tipo, $id_estado );
			break;

			case 'updateTransfer' : 
				$transfer_id = ( isset( $_POST['transfer_id'] ) ? $_POST['transfer_id'] : $_GET['transfer_id'] );
				$transfer_info = $fT->getTransferInfo( $transfer_id );
				$id_estado = $transfer_info['transfer_status'];
				$id_tipo = $transfer_info['transfer_type'];
				$sql = "SELECT bloquear_apis_en_transferencia_rapida AS lock_synchronization FROM sys_configuracion_sistema";
				$stm = $link->query( $sql ) or die( "Error al consultar bloqueos de APIS : {$sql} : {$link->error}" );
				$row = $stm->fetch_assoc();
				$lock_synchronization = $row['lock_synchronization'];
				switch( $id_tipo ){
					case '10' ://Transferencia entre la misma sucursal
					case '6' ://Vaciado de almacen
						switch( $id_estado ){
							case '1' :
								if( $lock_synchronization = 1 ){
									$fT->lock_and_unlock_synchronization_apis( 1 );
								}
								echo json_encode( $fT->updateTransferStatus( $user_id, $transfer_id, 2 ) );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
							break;
							case '2' :
								$actualizacion_transferencia = $fT->updateTransferStatus( $user_id, $transfer_id, 7 );//
								$actualizacion_detalle_transferencia = $fT->updateTransferDetail( $transfer_id );//actualiza cantidades recibidas
								echo json_encode( array( "actualizacion_transferencia"=>$actualizacion_transferencia, "actualizacion_detalle_transferencia"=>$actualizacion_detalle_transferencia ) );
							break;
							case '7' :
								echo json_encode( $fT->updateTransferStatus( $user_id, $transfer_id, 9 ) );//finzalizacion de transferencias
								if( $lock_synchronization = 1 ){
									$fT->lock_and_unlock_synchronization_apis( 0 );
								}
							break;
						}
					break;
					case '11' ://Transferencia a otra sucursal
						switch( $id_estado ){
							case '1' :
								if( $lock_synchronization = 1 ){
									$fT->lock_and_unlock_synchronization_apis( 1 );
								}
								echo json_encode( $fT->updateTransferStatus( $user_id, $transfer_id, 2, "SALIDA TRANSFERENCIA" ) );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
							break;
							case '2' :
								$inserta_bloque_validacion = $fT->insertValidationBlock( $transfer_id, $user_id );//inserta el bloque de validacion
								$actualiza_status_transferencia = $fT->updateTransferStatus( $user_id, $transfer_id, 6, $observations );//pasa a Revision Finalizada para que se reciba desde el listado
								echo json_encode( array( "inserta_bloque_validacion"=>$inserta_bloque_validacion, "actualiza_status_transferencia"=>$actualiza_status_transferencia ) );
							break;
							case '6' :
								echo json_encode( $fT->updateTransferStatus( $user_id, $transfer_id, 7, $observations ) );//pasa a Revision Finalizada para que se reciba desde el listado
								if( $lock_synchronization = 1 ){
									$fT->lock_and_unlock_synchronization_apis( 0 );
								}
							break;
						}
					break;
				}
			break;
			/*case 'transferAuthorization':
				$transfer_id = ( isset( $_GET['transfer_id'] ) ? $_GET['transfer_id'] : $_POST['transfer_id'] );
				$transfer = $fT->getTransferInfo( $transfer_id );
//terminar transferencias entre la misma sucursal
				if( $transfer['type'] == 10 ){
					if( $transfer['status'] != 1 ){
						$resp = $fT->buildMessage( "La transferencia ya fue finalizada anteriormente y No se puede volver a finalizar", 'reload' );
					}else{
						$fT->updateTransferStatus( $transfer_id, 2 );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
						$fT->updateTransferDetail( $transfer_id );//actualiza las cantidades recibidas en el detalle de transferencias
						$fT->updateTransferStatus( $transfer_id, 9 );//pasa a transferencia recibida para hacer movimientos de almacen de entrada
						$resp = $fT->buildMessage( "Transferencia Finalizada exitosamente!", 'reload' );
					}
//pasar transferencia a autorizada
				}else{
					if( $transfer['type'] != 11 && $transfer['type'] != 6 ){
					$resp = $fT->buildMessage( "Este tipo de transferencia no puede ser autorizado desde esta pantalla", 'reload' );
					}else if( $transfer['status'] >= 2 ){
						$resp = $fT->buildMessage( "La transferencia ya habia sido Autorizada anteriormente, no se puede volver a autorizar!", 'reload' );
					}else if( $transfer['type'] == 10 ){
						$resp = $fT->buildMessage( "Esta transferencia es entre la misma sucursal y no permite autorizar desde este boton", 'close_emergent' );
					}else{
						$fT->updateTransferStatus( $transfer_id, 2 );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
						$fT->insertValidationBlock( $transfer_id, $user_id );//inserta el bloque de validacion
						$fT->updateTransferStatus( $transfer_id, 7, $observations );//pasa a Revision Finalizada para que se reciba desde el listado
						$resp = $fT->buildMessage( "Transferencia Autorizada / puesta en Recepcion exitosamente!", 'reload' );
						//$resp = $fT->buildMessage( "Transferencia Autorizada exitosamente!", 'reload' );
					}
				}
			break;*/
//pasar transferencia a revision finalizada
			case 'putTransferInTransit' : 
				$transfer_id = ( isset( $_GET['transfer_id'] ) ? $_GET['transfer_id'] : $_POST['transfer_id'] );
				$observations = ( isset( $_GET['observations'] ) ? $_GET['observations'] : $_POST['observations'] );
				$transfer = $fT->getTransferInfo( $transfer_id );
				if( $transfer['type'] == 10 ){
					$resp = $fT->buildMessage( "Este tipo de transferencia es entre la misma sucursal y no puede ser puesta en Transito desde este boton.", 'reload' );
				}else{
					if( $transfer['type'] != 11 && $transfer['type'] != 6 ){
						$resp = $fT->buildMessage( "Este tipo de transferencia no puede ser puesta en Transito desde esta pantalla", 'reload' );
					}else if( $transfer['status'] < 2 ){
						$resp = $fT->buildMessage( "La transferencia no se ah autorizado, primero autorizala para que se pueda poner en Transito!", 'reload' );
					}else if( $transfer['status'] > 7 ){
						$resp = $fT->buildMessage( "La transferencia ya se habia puesto en Transito anteriormente, no se puede volver a poner en Transito!", 'reload' );
					}else{
						$fT->insertValidationBlock( $transfer_id, $user_id );
						$fT->updateTransferStatus( $transfer_id, 8, $observations );//pasa a Revision Finalizada para que se reciba desde el listado
						$resp = $fT->buildMessage( "Transferencia puesta en Transito exitosamente.", 'reload' );
					}
				}
			break;

			case 'finishTransfer' :
				$transfer_id = ( isset( $_GET['transfer_id'] ) ? $_GET['transfer_id'] : $_POST['transfer_id'] );
				$observations = ( isset( $_GET['observations'] ) ? $_GET['observations'] : $_POST['observations'] );
				$transfer = $fT->getTransferInfo( $transfer_id );
				if( $transfer['status'] == 9 ){
						$resp = $fT->buildMessage( "Esta transferencia ya fue finalizada Anteriormente y no se puede volver a finalizar", 'reload' );
				}else if( $transfer['type'] == 10 ){
					$fT->updateTransferStatus( $transfer_id, 2 );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
					$fT->updateTransferDetail( $transfer_id );//actualiza las cantidades recibidas en el detalle de transferencias
					$fT->updateTransferStatus( $transfer_id, 9 );//pasa a transferencia recibida para hacer movimientos de almacen de entrada
					$resp = $fT->buildMessage( "Transferencia Finalizada exitosamente!", 'reload' );
				}else{
					if( $transfer['type'] != 11 && $transfer['type'] != 6 ){
						$resp = $fT->buildMessage( "Este tipo de transferencia no puede ser finalizada desde esta pantalla", 'reload' );
					}else if( $transfer['status'] == 1 ){
						$fT->updateTransferStatus( $transfer_id, 2 );//pasa a pendiente de surtir para hacer movimientos de almacen de salida
						$fT->insertValidationBlock( $transfer_id, $user_id );
						$fT->updateTransferDetail( $transfer_id );//actualiza las cantidades recibidas en el detalle de transferencias
						$fT->updateTransferStatus( $transfer_id, 9, $observations );//pasa a transferencia recibida para hacer movimientos de almacen de entrada
						$resp = $fT->buildMessage( "Transferencia Finalizada exitosamente!", 'close_emergent' );
					}else if( $transfer['status'] == 2 ){
						$fT->insertValidationBlock( $transfer_id, $user_id );
						$fT->updateTransferDetail( $transfer_id );//actualiza las cantidades recibidas en el detalle de transferencias
						$fT->updateTransferStatus( $transfer_id, 9, $observations );//pasa a transferencia recibida para hacer movimientos de almacen de entrada
					}else if( $transfer['status'] == 7 || $transfer['status'] == 8 ){
						$fT->updateTransferDetail( $transfer_id );//actualiza las cantidades recibidas en el detalle de transferencias
						$fT->updateTransferStatus( $transfer_id, 9, $observations );//pasa a transferencia recibida para hacer movimientos de almacen de entrada					
						$resp = $fT->buildMessage( "Transferencia Finalizada exitosamente!", 'reload' );
					}else{
						$resp = $fT->buildMessage( "La transferencia esta en status {$transfer['status']} y este status no esta contemplado 
							para esta pantalla, avisa al encargado y / o contacta a SISTEMAS", 'close_emergent' );
					}
				}
			break;
			
			default:
				$resp = "Permission denied on fastTransfersClass to '{$action}'";
			break;

		}
		die( $resp );
	}

	class fastTransfers
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function getCurrentTime(){
			$sql = "SELECT NOW() AS date_time";
			$stm = $this->link->query( $sql ) or die( "Error al consultar fecha y hora actual : {$sql} : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['date_time'];
		}

		public function lock_and_unlock_synchronization_apis( $status ){
			$sql = "UPDATE sys_configuracion_sistema SET bloquear_apis_sincronizacion = {$status}";
			$this->link->query( $sql ) or die( json_encode( array( "Error"=>"Error al actualizar APIS de sincronización a : {$status}. : {$sql} : {$this->link->error}" ) ) );
		}
		public function getTransferInfo( $transfer_id ){
			$sql = "SELECT 
					id_estado AS transfer_status,
					id_tipo AS transfer_type
				FROM ec_transferencias
				WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de transferencia : {$this->link->error}" );
			$transfer = $stm->fetch_assoc();
			return $transfer;
		}

		public function updateTransferStatus( $user_id, $transfer_id, $status_id, $observations = "" ){
			$this->link->autocommit( false );
			$observations = ( $observations == "" ? "" : " - {$observations}" );
			$movs_almacen = array();
			$sql = "UPDATE ec_transferencias 
						SET id_estado = {$status_id},
						observaciones = CONCAT( observaciones, '{$observations}' )
					WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar status y observaciones de Transferencia : {$this->link->error}" );
			if( $status_id == 2 ){
				$movs_almacen = $this->insertTransferMovements( $user_id, $transfer_id, $status_id );
			}else if( $status_id == 9 ){
				$movs_almacen = $this->insertTransferMovements( $user_id, $transfer_id, $status_id );
			}
			$this->link->autocommit( true );
			return ( array( "sql_instruction"=>$sql, "movimientos"=>$movs_almacen ) );
		}

		public function updateTransferDetail( $transfer_id ){
		//actualiza las cantidades recibidas
			$sql = "UPDATE ec_transferencia_productos 
					SET cantidad_piezas_recibidas = cantidad,
						total_piezas_recibidas = cantidad
					WHERE id_transferencia = {$transfer_id}";
			$this->link->query( $sql ) or die( "Error al actualizar cantidades recibidas del detalle de Transferencia : {$this->link->error}" );
			return ( array( "sql_instruction"=>$sql ) );
		}	

		public function insertValidationBlock( $transfer_id, $user_id ){
		//inserta el bloque de validacion
			$sql = "INSERT INTO ec_bloques_transferencias_validacion SET
					 id_bloque_transferencia_validacion = NULL,
					 fecha_alta = NOW(),
					 validado = '0'";
			$insert_block = $this->link->query( $sql ) or die( "Error al insertar cabecera del bloque de validación : {$this->link->error}" );
			$block_id = $this->link->insert_id;
		//inserta detalle del bloque de validacion
			$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle SET 
					id_bloque_transferencia_validacion_detalle = NULL,
					id_bloque_transferencia_validacion = {$block_id},
					id_transferencia = {$transfer_id},
					fecha_alta = NOW(),
					invalidado = '0'";
			$insert_block_detail = $this->link->query( $sql ) or die( "Error al insertar detalle del bloque de validación : {$this->link->error}" );
		//inserta los escaneos de recepcion
			$sql = "INSERT INTO ec_transferencias_validacion_usuarios 
					(/*1*/id_transferencia_validacion,/*2*/id_transferencia_producto,/*3*/id_usuario,
					/*4*/id_producto,/*5*/id_proveedor_producto,/*6*/cantidad_cajas_validadas,
					/*7*/cantidad_paquetes_validados,/*8*/cantidad_piezas_validadas,/*9*/fecha_validacion,
					/*10*/id_status,/*11*/validado_por_nombre )
					SELECT 
						/*1*/NULL,
						/*2*/tp.id_transferencia_producto,
						/*3*/{$user_id},
						/*4*/tp.id_producto_or,
						/*5*/tp.id_proveedor_producto,
						/*6*/0,
						/*7*/0,
						/*8*/tp.cantidad,
						/*9*/NOW(),
						/*10*/1,
						/*11*/0
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia = {$transfer_id}";
			//die( $sql );
			$stm_ins = $this->link->query( $sql ) or die( "Error al insertar el detalle de escaneos de recepcion : {$this->link->error}" );
		//actualiza los codigos unicos
			$sql = "UPDATE ec_transferencia_codigos_unicos 
						SET id_transferencia = NULL,
						id_bloque_transferencia_validacion = {$block_id}
					WHERE id_transferencia = {$transfer_id}";
			$stm_upd = $this->link->query( $sql ) or die( "Error al actualizar codigos unicos : {$this->link->error}" );
			return $block_id;
		}

		public function insertTransferMovements( $user_id, $transfer_id, $transfer_status ){
			$sql = "";
			$sql_detail = "";
			$action_note = "";
			$movement_type = 0;
			$resp = array();
			if( $transfer_status == 2 ){//autorizacion de transferencia
				$sql = "SELECT id_sucursal_origen AS store_id, id_almacen_origen AS warehouse_id FROM ec_transferencias WHERE id_transferencia = {$transfer_id}";
				$sql_detail = "SELECT cantidad AS quantity, id_producto_or AS product_id, id_proveedor_producto AS product_provider_id FROM ec_transferencia_productos WHERE id_transferencia = {$transfer_id}";
				$action_note = "SALIDA DE TRANSFERENCIA";
				$movement_type = 6;
			}else if( $transfer_status == 9 ){//recepcion de transferencia
				$sql = "SELECT id_sucursal_destino AS store_id, id_almacen_destino AS warehouse_id FROM ec_transferencias WHERE id_transferencia = {$transfer_id}";
				$sql_detail = "SELECT total_piezas_recibidas AS quantity, id_producto_or AS product_id, id_proveedor_producto AS product_provider_id FROM ec_transferencia_productos WHERE id_transferencia = {$transfer_id}";
				$action_note = "ENTRADA DE TRANSFERENCIAS";
				$movement_type = 5;
			}else{
				die( "La actualización del status '{$transfer_status}' no requiere movimientos de almacen." );
			}//die("HERE1");
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos generales de transferencia para hacer movimiento de almacen : {$sql} : {$this->link->error}" );
			$resp['consulta_datos_transferencia'] = $sql;
			$row = $stm->fetch_assoc();
		//inserta la cabecera del movimiento de almacen
			$sql = "CALL spMovimientoAlmacen_inserta ( {$user_id}, '{$action_note}', {$row['store_id']}, {$row['warehouse_id']}, {$movement_type}, -1, -1, -1, {$transfer_id}, 21, NULL )";
			$resp['inserta_cabecera_movimiento'] = $sql;
			$stm = $this->link->query( $sql ) or die( "Error al insertar movimiento por {$action_note} por Procedure : {$sql} : {$this->link->error}" );
		//recupera el id de cabecera de movimiento de almacen
			$sql = "SELECT LAST_INSERT_ID() AS last_id";
			$stm2 = $this->link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado : {$sql} : {$this->link->error}" );
			$resp['recupera_id_cabecera_movimiento'] = $sql;
			$row = $stm2->fetch_assoc();
			$movement_id = $row['last_id'];
		//consulta el detalle de la transferencia para insertar detalle de movmiento de almacen
			$stm_3 = $this->link->query( $sql_detail ) or die( "Error al consultar el detalle de transferecia para insertar detalles de movimiento de almacen : {$sql} : {$this->link->error}" );
			$resp['consulta_detalle_transferencia'] = $sql;
			$resp['inserta_detalle_movimiento'] = array();
			while( $row = $stm_3->fetch_assoc() ){
				$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_id}, {$row['product_id']}, {$row['quantity']}, {$row['quantity']}, -1, -1, {$row['product_provider_id']}, 21, NULL )";
				$stm_4 = $this->link->query( $sql ) or die( "Error al insertar detalle por procedure : {$sql} : {$this->link->error}" );
				array_push( $resp['inserta_detalle_movimiento'], $sql );
			}
			return $resp;
		}

		public function buildEmergent( $transfer_type, $transfer_status ){
			$resp = "";
			$class_one = ( $transfer_status >= 2 ? 'text-success' : 'text-secondary' );
			$text_one = 'Actualizar transferencia a pendiente de surtir y hacer movimientos de Salida de almacén origen';
			$class_two = ( $transfer_status >= 2 ? 'text-success' : 'text-secondary' );
			$text_two = ( $transfer_type != 11 ? 'Actualizar cantidades recibidas en detalle de transferencia' : 
				'Crear bloque de validación de Transferencia' );
			$class_three = ( $transfer_status >= 2 ? 'text-success' : 'text-secondary' );
			$text_three = ( $transfer_type != 11 ? 'Actualiza Status de Transferencia a status de Revisión Finalizada' 
				: 'Actualizar transferencia a recibida y hacer movimientos de Entrada en almacén destino' );
			$steep_one_start = $this->getCurrentTime();
			$steep_one_finish = $this->getCurrentTime();
			$actions = "<table class=\"table\">
					<thead>
						<tr>
							<th>Acción</th>
							<th>Hora Inicio</th>
							<th>Hora Fin</th>
							<th>Ver Log</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<p class=\"icon-ok-circle {$class_one}\" id=\"step_1_icon\">{$text_one}</p>
							</td>
							<td></td>
							<td></td>
							<td class=\"text-center\">
								<button 
									type=\"button\"
									class=\"btn btn-info\"
									onclick=\"show_transfer_steps_log( 'one' );\"
								>
									<i class=\"icon-code\"></i>
								</button>
							</td>
						</tr>
						<tr>
							<td colspan=\"4\"><pre><code class=\"json\" id=\"json_steep_one\"></code></pre></td>
						</tr>
						<tr>
							<td>
								<p class=\"icon-ok-circle {$class_two}\" id=\"step_2_icon\">{$text_two}</p>
							</td>
							<td>{$steep_one_start}</td>
							<td>{$steep_one_finish}</td>
							<td class=\"text-center\">
								<button 
									type=\"button\"
									class=\"btn btn-info\"
									onclick=\"show_transfer_steps_log( 'two' );\"
								>
									<i class=\"icon-code\"></i>
								</button>
							</td>
						</tr>
						<tr>
							<td colspan=\"4\"><pre><code class=\"json\" id=\"json_steep_two\"></code></pre></td>
						</tr>
						<tr>
							<td>
								<p class=\"icon-ok-circle {$class_three}\" id=\"step_3_icon\">{$text_three}</p>
							</td>
							<td></td>
							<td></td>
							<td class=\"text-center\">
								<button 
									type=\"button\"
									class=\"btn btn-info\"
									onclick=\"show_transfer_steps_log( 'three' );\"
								>
									<i class=\"icon-code\"></i>
								</button>
							</td>
						</tr>
						<tr>
							<td colspan=\"4\"><pre><code class=\"json\" id=\"json_steep_three\"></code></pre></td>
						</tr>
					</tbody>
				</table>";
			$resp .= "<div class=\"\">
				<h2 class=\"text-center text-success\" style=\"text-align : center;\">Procesando Transferencia...</h2>
				{$actions}
				<div class=\"text-center\">
					<button
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"close_emergent();location.reload();\"
						id=\"btn_close_emergent\" 
						style=\"display:none !important;\"
					>
						<i class=\"icon-ok-circled\">Aceptar</i>
					</button>
				</div>
			</div>
			<style>
				.code_textarea{
					position : relative;
					background-color:white;
					width : 100%;
					height : 200px;
				}
			</style>";
			return $resp;
		}

		public function buildMessage( $message, $btn_action ){
			$onlick = "";
			switch ( $btn_action ) {
				case 'reload':
					$onclick = "location.reload();";
				break;
				case 'close_emergent':
					$onclick = "close_emergent();";
				break;
			}
			$resp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\">
							<div class=\"row\">
								<div class=\"col-12 text-center\">
									<h5>{$message}</h5>
									<br>
									<button 
										type=\"button\"
										class=\"btn btn-success\"
										onclick=\"{$onclick}\"
									>
										<i>Aceptar</i>
									</button>
									<br>
								</div>
							</div>";
			return $resp;
		}
	}


				
?>

