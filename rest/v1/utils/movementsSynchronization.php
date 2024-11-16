<?php

	class movementsSynchronization
	{
		private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
			$this->link = $connection;
			$this->LOGGER = $Logger;
		}
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationMovements( $store_id, $system_store, $origin_store_prefix, $limit, $logger_id = false ){
			$log_steep_id = null;
			$sql = "CALL buscaMovimientosPendientes( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de Movimientos de Almacen pendientes", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de movimientos de almacen", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de movimientos de almacen : {$this->link->error} {$sql}" );
				}
			$sql = "CALL buscaDetallesMovimientosPendientes( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )";			
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de detalles de Movimientos de Almacen pendientes", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de detalles de movimientos de almacen", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de detalles de movimientos de almacen : {$this->link->error} {$sql}" );
				}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationMovements( $system_store, $limit, $type, $petition_unique_folio, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_movimiento_almacen,
						REPLACE( json, '\r\n', ' ' ) AS data,
						tabla
					FROM sys_sincronizacion_movimientos_almacen
					WHERE tabla = 'ec_movimiento_almacen'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$system_store}
					AND json != ''
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta JSONs de Movimientos de Almacen", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar JSONs de Movimientos de Almacen", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al consultar JSONs de Movimientos de Almacen : {$this->link->error} {$sql}" );
				}
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp, json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				//actualiza al status 2 los registros que va a enviar
					$sql = "UPDATE sys_sincronizacion_movimientos_almacen SET id_status_sincronizacion = 2, folio_unico_peticion = '{$petition_unique_folio}' WHERE id_sincronizacion_movimiento_almacen = {$row['id_sincronizacion_movimiento_almacen']}";
					$stm_2 = $this->link->query( $sql );
						if( $logger_id ){
							$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza registro de sincronizacion a status 2", $sql );
						}
						if( $this->link->error ){
							if( $logger_id ){
								$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al poner registro de sincronizacion de movimiento de almacen en status 2", 'sys_sincronizacion_peticion', $sql, $this->link->error );
							}
							die( "Error al poner registro de sincronizacion de movimiento de almacen en status 2 : {$this->link->error} {$sql}" );
						}
				}
			}
			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateMovementSynchronization( $rows, $petition_unique_folio, $status = null, $sum = false, $logger_id = false ){
			$log_steep_id = null;
			$sql = "";
			if( $rows != '' && $rows != null  ){
				if( $status != null ){//actualiza status y folio unico de peticion
					$sql = "UPDATE sys_sincronizacion_movimientos_almacen 
					SET id_status_sincronizacion = '{$status}',
						folio_unico_peticion = '{$petition_unique_folio}' 
					WHERE registro_llave IN( {$rows} )";

				}/*else if( $sum == true ){//actualiza a sumado y folio unico de peticion
					$sql = "UPDATE sys_sincronizacion_movimientos_almacen 
					SET movimiento_sumado = '1',
						folio_unico_peticion = '{$petition_unique_folio}' 
					WHERE registro_llave IN( {$rows} )";
				}*/
				$stm = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza status de registros de sincronizacion", $sql );
					}
					if( $this->link->error ){
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar status de registros de sincronizacion", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						die( "Error al actualizar status de registros de sincronizacion : {$this->link->error} {$sql}" );
					}
				//or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );
			}
		}
//inserción de movimientos
		public function insertMovements( $movements, $logger_id = false ){
		//verifica si esta habilitada la transaccion en el módulo de movimientos de almacen
			$sql = "SELECT habilitar_transaccion AS transaction_configuration FROM sys_limites_sincronizacion WHERE tabla = 'ec_movimiento_almacen'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si esta habilitada la transacción en ec_movimiento_almacen : {$sql} : {$this->link->error}" );
			$transaction_row = $stm->fetch_assoc();
			$transaction = ( $transaction_row['transaction_configuration'] == 1 ? true : false );
			$log_steep_id = null;
			$file = fopen("movements_log.txt", "w");
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			foreach ($movements as $key => $movement) {
				if( $transaction ){
					$this->link->autocommit( false );
				}
				$ok = true;//{$movement['id_orden_compra']}
				$movement_detail = $movement['movimiento_detail'];
				$is_valid = true;
				foreach ($movement_detail as $key2 => $detail) {
					if( $detail['id_pedido_detalle'] != -1 && $detail['id_pedido_detalle'] != '' 
						&& $detail['id_pedido_detalle'] != null ){
						$sql = "{$detail['id_pedido_detalle']}";
						$stm_aux = $this->link->query( $sql );
						if( $this->link->error ){
							// or die( "Error al consultar si existe el detalle de venta : {$this->link->error}" );
						}
						if( $stm_aux->num_rows <= 0 ){
							$is_valid = false;
							$ok = false;
						}else{
							$is_valid = true;
							$ok = true;
						}
					}	
				}
				if( $is_valid == true && $ok == true ){
				//se inserta cabecera de movimiento de almacen por procedure
					$sql =  "CALL spMovimientoAlmacen_inserta( {$movement['id_usuario']}, '{$movement['observaciones']} \nInsertado desde API por sincronización', {$movement['id_sucursal']},
						{$movement['id_almacen']}, {$movement['id_tipo_movimiento']}, -1, -1, '{$movement['id_maquila']}', {$movement['id_transferencia']}, {$movement['id_pantalla']}, 
						'{$movement['folio_unico']}' )";
				//fwrite($file, "{$sql}\n");
				//reemplazamiento de comillas en consultas
					$sql = str_replace( "'(", "(", $sql );
					$sql = str_replace( "' (", "(", $sql );
					$sql = str_replace( ")'", ")", $sql );
					$sql = str_replace( ") '", ")", $sql );

					$stm_head = $this->link->query( $sql );//or die( "Error al insertar cabecera de movimiento de almacen : {$sql} {$this->link->error}" );
					
						if( $logger_id ){
							$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta cabecera de movimientos de almacen", $sql );
						}
						if( $this->link->error ){
							$ok = false;
							if( $logger_id ){
								$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar cabecera de movimientos de almacen", 'sys_sincronizacion_peticion', $sql, $this->link->error );
							}
							//die( "Error al insertar cabecera de movimientos de almacen : {$this->link->error} {$sql}" );
						}
/*Prueba Oscar de temporizador para insertar otros movimientos de almacen*/
				//sleep(40);//timer de espera de 1 minuto
/*/Prueba Oscar de temporizador para insertar otros movimientos de almacen*/
				//recupera el id insertado	//
					//$movement_id = $this->link->insert_id;
					$sql = "SELECT LAST_INSERT_ID() AS last_id";
					$stm_last = $this->link->query($sql) or die( "Error al recuperar el id insertado : {$sql} : {$this->link->error}" );
					$row_last = $stm_last->fetch_assoc();
					$movement_id = $row_last['last_id'];
					$movement_detail = $movement['movimiento_detail'];
					if($ok == true ){
						foreach ($movement_detail as $key2 => $detail) {
							if( $ok == true ){
								$sql = "CALL spMovimientoAlmacenDetalle_inserta( {$movement_id}, {$detail['id_producto']}, {$detail['cantidad']}, {$detail['cantidad']}, {$detail['id_pedido_detalle']},
											-1, IF( {$detail['id_proveedor_producto']} IS NULL OR '{$detail['id_proveedor_producto']}' = '', NULL, '{$detail['id_proveedor_producto']}' ), 
											{$movement['id_pantalla']}, '{$detail['folio_unico']}' )";
								$stm = $this->link->query( $sql );
									if( $logger_id ){
										$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta detalle de movimientos de almacen", $sql );
									}
									if( $this->link->error ){
										$ok = false;
										if( $logger_id ){
											$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar detalle de movimientos de almacen", 'sys_sincronizacion_peticion', $sql, $this->link->error );
										}
										//die( "Error al insertar detalle de movimientos de almacen : {$this->link->error} {$sql}" );
									}
							}
						}
					}
					if( $ok == true ){
						if( $transaction ){
							$this->link->commit();
						}
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
						$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					}else{
						if( $transaction ){
							$this->link->rollback();
						}
						$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
						$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					}
				}
			}
			//fclose($file);
			return $resp;
		}
//actualización de inventario almacen producto
		/*public function updateInventory( $movements, $logger_id = false ){
			$updates = array();
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';
			foreach ($movements as $key => $movement) {
				$movement_detail = $movement['movimiento_detail'];
				foreach ($movement_detail as $key2 => $detail) {
					$sql = "UPDATE ec_almacen_producto
						SET inventario = ( inventario + {$detail['cantidad_surtida']} )
					WHERE id_producto = {$detail['id_producto']}
					AND id_almacen = {$movement['id_almacen']}";
		    		array_push( $updates, $sql );
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
				}
			}
		//ejecuta las consultas de actualizacion de inventario
			//$this->link->autocommit( false );
		    foreach( $updates as $update ){
		        $stm = $this->link->query( $update ) or die( "Error al actualizar el inventario almacen producto : {$this->link->error}" );
		    	if( !$stm ){
					//$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
	    		}else{
					//$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";

	    		}
		    }
			//$this->link->autocommit( true );
			return $resp;
		}*/
	}
?>