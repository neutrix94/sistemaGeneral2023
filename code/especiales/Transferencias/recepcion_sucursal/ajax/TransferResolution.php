<?php

	class TransferResolution
	{
		private $link;
		private $current_sucursal;
		private $current_user;
		function __construct( $connection, $sucursal_id, $user_id ) {
			$this->link = $connection;
			$this->current_sucursal = $sucursal_id;
			$this->current_sucursal = $user_id;
		}

		public function insertResolutionHeader( $recepcion_block_id, $user, $sucursal, $header_data, $detail = null ){
	//inserta la cabecera de la Transferencia por resolución
			$sql="INSERT INTO ec_transferencias SET 
				id_usuario = {$user},
				folio = '',
				fecha = NOW(),
				hora = NOW(),
				id_sucursal_origen = {$header_data['store_origin']},
				id_sucursal_destino = {$header_data['store_destinity']},
				observaciones = 'Transferencia por Resolución',
				id_razon_social_venta = -1,
				id_razon_social_compra = 1,
				facturable = 0,
				porc_ganancia = 0,
				id_almacen_origen = {$header_data['warehouse_origin']},
				id_almacen_destino = {$header_data['warehouse_destinity']},
				id_tipo = 9,
				id_estado = 1,
				id_sucursal = {$this->current_sucursal}, 
				titulo_transferencia = 'Resolución'";

		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al insertar cabecera de Transferencia por resolución : {$sql} {$this->link->error}" );
			$header_id = $this->link->insert_id;
		//actualiza el folio de la transferencia por resolución
			$sql_folio = "";
			$sql = "UPDATE ec_transferencias 
					SET folio = ( 
							SELECT
								ax.folio
							FROM(
								SELECT 
									CONCAT(s1.prefijo, s2.prefijo, ' ', t.id_transferencia ) AS folio
								FROM ec_transferencias t
								LEFT JOIN sys_sucursales s1
								ON s1.id_sucursal = t.id_sucursal_origen
								LEFT JOIN sys_sucursales s2
								ON s2.id_sucursal = t.id_sucursal_destino
								WHERE t.id_transferencia = {$header_id}
							)ax 
						)
					WHERE id_transferencia = {$header_id}";

			$stm = $this->link->query( $sql ) or die( "Error al actaulizar folio de Transferencia por resolución : {$sql} {$this->link->error}" );
		//inserta la cabecera del movimiento de almacen
			//$movement_header_id = $this->insertMovementHeader( $header_id );
			if( $detail != null ){
				$resp = $this->insertResolutionDetail( $detail, $header_id );
			}
		//inserta transferencia en bloque de validación
			$sql = "INSERT INTO ec_bloques_transferencias_validacion SET 
						id_bloque_transferencia_validacion = NULL,
						fecha_alta = NOW(),
						validado = 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar cabecera de bloque de validación : {$this->link->error}" );
			$block_validation_id = $this->link->insert_id;
		//inserta eldetalle del bloque de validación
			$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle SET 
						id_bloque_transferencia_validacion_detalle = NULL,
						id_bloque_transferencia_validacion = {$block_validation_id},
						id_transferencia = {$header_id},
						fecha_alta = NOW(),
						invalidado = 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar detalle de bloque de validación : {$this->link->error}" );
		//actualiza la transferencia a salida para hacer los movimientos de almacen(origen)
/**** INSERTA MOVIMIENTOS DE SALIDA ****/
			$sql = "UPDATE ec_transferencias 
						SET id_estado = 2
					WHERE id_transferencia = {$header_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar transferencias : {$sql} {$this->link->error}" );
		//consulta datos de cabecera de la transferencia
			$sql = "SELECT 
					t.id_usuario, 
					t.id_sucursal_origen,
					t.id_sucursal_destino,
					t.id_transferencia, 
					t.id_almacen_origen,
					t.id_almacen_destino
				FROM ec_transferencias t
				WHERE t.id_transferencia = {$header_id}";
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar datos de la transferencia : {$sql} : {$this->link->error}" );
			$transfer_row = $stm_1->fetch_assoc();
		//inserta cabecera de movimiento de almacen
			$sql = "CALL spMovimientoAlmacen_inserta ( {$transfer_row['id_usuario']}, 'SALIDA POR TRANSFERENCIA', {$transfer_row['id_sucursal_origen']}, {$transfer_row['id_almacen_origen']}, 6,
				-1, -1, -1, {$header_id}, 9, NULL )";
			$stm_2 = $this->link->query( $sql ) or die( "Error al insertar el movimiento de almacen entrada por Procedure : {$sql} : {$this->link->error}" );
		//recupera id insertado
			$sql = "SELECT LAST_INSERT_ID() AS last_id";
			$stm_3 = $this->link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado : {$sql} : {$this->link->error}" );
			$movement_id = $stm_3->fetch_assoc();
			$movement_id = $movement_id['last_id'];
		//consulta datos del detalle de la transferencia
			$sql = "SELECT 
				tp.id_producto_or,
				tp.cantidad,
				tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia = {$header_id}
				AND tp.omite_movimiento_origen = 0";
			$stm_4 = $this->link->query( $sql ) or die( "Error al consultar el detalle de productos de la transferencia : {$sql} : {$this->link->error}" );
			//inserta detalle de movimientos de almacen
			while( $detail_row = $stm_4->fetch_assoc() ){
				$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_id}, {$detail_row['id_producto_or']}, {$detail_row['cantidad']}, 
							{$detail_row['cantidad']}, -1, -1, {$detail_row['id_proveedor_producto']}, 9, NULL )";
				$stm_5 = $this->link->query( $sql ) or die( "Error al insertar detalle de movimiento de almacen entrada desde procedure : {$sql} : {$this->link->error}" );
			}
		//actualiza la transferencia a recibida para hacer los movimientos de almacen(destino)
			$sql = "UPDATE ec_transferencias 
						SET id_estado = 9
					WHERE id_transferencia = {$header_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar transferencias : {$sql} {$this->link->error}" );

/**** INSERTA MOVIMIENTOS DE ENTRADA ****/
		//inserta cabecera de movimiento de almacen
			$sql = "CALL spMovimientoAlmacen_inserta ( {$transfer_row['id_usuario']}, 'SALIDA POR TRANSFERENCIA', {$transfer_row['id_sucursal_destino']}, {$transfer_row['id_almacen_destino']}, 5,
			-1, -1, -1, {$header_id}, 9, NULL )";
			$stm_2 = $this->link->query( $sql ) or die( "Error al insertar el movimiento de almacen salida por Procedure : {$sql} : {$this->link->error}" );
		//recupera id insertado
			$sql = "SELECT LAST_INSERT_ID() AS last_id";
			$stm_3 = $this->link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado : {$sql} : {$this->link->error}" );
			$movement_id = $stm_3->fetch_assoc();
			$movement_id = $movement_id['last_id'];
		//consulta datos del detalle de la transferencia
			$sql = "SELECT 
						tp.id_producto_or,
						tp.cantidad,
						tp.id_proveedor_producto
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia = {$header_id}
					AND tp.omite_movimiento_origen = 0";
			$stm_4 = $this->link->query( $sql ) or die( "Error al consultar el detalle de productos de la transferencia : {$sql} : {$this->link->error}" );
		//inserta detalle de movimientos de almacen
			while( $detail_row = $stm_4->fetch_assoc() ){
				$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_id}, {$detail_row['id_producto_or']}, {$detail_row['cantidad']}, 
							{$detail_row['cantidad']}, -1, -1, {$detail_row['id_proveedor_producto']}, 9, NULL )";
				$stm_5 = $this->link->query( $sql ) or die( "Error al insertar detalle de movimiento de almacen salida desde procedure : {$sql} : {$this->link->error}" );
			}
			return "La Transferencia por resolución fue generada exitosamente";
		}

		public function insertResolutionDetail( $stm, $transfer_id ){
//var_dump( $stm );
			$counter = 0;
			while( $detail = $stm->fetch_assoc() ){
				$counter ++;
				$quantity = 0;
				$ommit_movement = 0;
				if( $detail['pieces_stay'] != 0 && $detail['pieces_stay'] != 0.00 ){
					//Descomentar para pruebas
						//$quantity = $detail['pieces_stay'];
					//REVISAR SOBRE EL EJEMPLO NEGATIVO
					//if( $detail['pieces_stay'] > 0 ){
						$quantity = $detail['pieces_stay'] * -1;
					//}
				}else if( $detail['pieces_return'] != 0 && $detail['pieces_return'] != 0.00 ){
					$quantity = $detail['pieces_return'];
					$ommit_movement = 1;
				}else if( $detail['pieces_missing'] != 0 && $detail['pieces_missing'] != 0.00 ){
					$quantity = $detail['pieces_missing'] * 1;
				}
				$sql = "INSERT INTO ec_transferencia_productos SET 
						id_transferencia = {$transfer_id}, 
						id_producto_or = {$detail['product_id']}, 
						id_presentacion = -1, 
						cantidad_presentacion = {$quantity},
						cantidad = {$quantity}, 
						id_producto_de = {$detail['product_id']}, 
						referencia_resolucion = {$quantity}, 
						cantidad_cajas = 0, 
						cantidad_paquetes = 0, 
						cantidad_piezas = {$quantity}, 
						total_piezas_surtimiento = {$quantity},
						total_piezas_validacion = {$quantity},
						id_proveedor_producto = {$detail['product_provider_id']},
						numero_consecutivo = {$counter},
						omite_movimiento_origen = '{$ommit_movement}',
						omite_movimiento_destino = '{$ommit_movement}'";
				$stm_ins = $this->link->query( $sql ) or die( "Error al insertar el detalle de la transferncia por Resolución : {$this->link->error}" );
			}
			return 'ok';

		}

		/*public function insertMovementHeader( $transfer_id, $warehouse_origin ){
			$sql = "INSERT INTO ec_movimiento_almacen SET 
					id_movimiento_almacen = NULL,
					id_tipo_movimiento = 6,
					id_usuario = {$this->current_user},
					id_sucursal = {$this->current_sucursal},
					fecha = NOW(),
					hora = NOW(),
					observaciones = 'Movimiento de salida de transferncia por resolución',
					id_pedido = -1,
					id_orden_compra = -1,
					lote = '',
					id_maquila = -1,
					id_transferencia = {$transfer_id},
					id_almacen = {$warehouse_origin},
					status_agrupacion = -1,
					id_equivalente = 0";
			$stm->this->link->query( $sql ) or die( "Error al insertar cabecera del movimiento de salida : {$this->link->error}" );
			$sql = "SELECT last_insert_id() AS last_id";
			$stm = $this->link->query( $sql ) or die( "Error al recuperar el id insertado : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return "ok|{$row['last_id']}";
		}*/
	}
?>