<?php
	if( isset( $_GET['freeTransferFl'] ) || isset( $_POST['freeTransferFl'] ) ){
		include( '../../../../conect.php' );
		include( '../../../../conexionMysqli.php' );
		$fT = new fastTransfers( $link );
		$action =( isset( $_GET['freeTransferFl'] ) ? $_GET['freeTransferFl'] : $_POST['freeTransferFl'] );
		$resp = "";
		switch ( $action ) {
			case 'transferAuthorization':
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
			break;
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
						$resp = $fT->buildMessage( "Transferencia puesta en Transito exitosamente!", 'reload' );
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

		public function getTransferInfo( $transfer_id ){
			$sql = "SELECT 
					id_estado AS status,
					id_tipo AS type
				FROM ec_transferencias
				WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de transferencia : {$this->link->error}" );
			$transfer = $stm->fetch_assoc();
			return $transfer;
		}

		public function updateTransferStatus( $transfer_id, $status_id, $observations = "" ){
			$observations = ( $observations == "" ? "" : " - {$observations}" );
			$sql = "UPDATE ec_transferencias 
						SET id_estado = {$status_id},
						observaciones = CONCAT( observaciones, '{$observations}' )
					WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar status y observaciones de Transferencia : {$this->link->error}" );
		}

		public function updateTransferDetail( $transfer_id ){
		//actualiza las cantidades recibidas
			$sql = "UPDATE ec_transferencia_productos 
					SET cantidad_piezas_recibidas = cantidad,
						total_piezas_recibidas = cantidad
					WHERE id_transferencia = {$transfer_id}";
			$this->link->query( $sql ) or die( "Error al actualizar cantidades recibidas del detalle de Transferencia : {$this->link->error}" );
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

