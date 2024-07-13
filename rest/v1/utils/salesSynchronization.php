<?php

	class salesSynchronization
	{
		private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
			$this->link = $connection;
			$this->LOGGER = $Logger;
		}
//hacer jsons de rgistros de ventas
		public function setNewSynchronizationSales( $store_id, $system_store, $origin_store_prefix, $limit, $logger_id = false ){
			$log_steep_id = null;
			$sql = "CALL buscaVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de ventas", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de ventas", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de ventas : {$this->link->error} {$sql}" );
				}
			$sql = "CALL buscaDetalleVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de detalle de ventas", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de detalle de ventas", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de detalle de ventas : {$this->link->error} {$sql}" );
				}
			return 'ok';
		}

//hacer jsons de registros de cobros / pagos
		public function setNewSynchronizationPayments( $store_id, $system_store, $origin_store_prefix, $limit, $logger_id = false ){
			$log_steep_id = null;
			//buscaCajeroCobrosPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
			$sql = "CALL buscaCajeroCobrosPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de cobros pendientes de sincronizar", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de cobros pendientes de sincronizar", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de cobros pendientes de sincronizar : {$this->link->error} {$sql}" );
				}
			$sql = "CALL buscaPagosVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de pagos de ventas pendientes de sincronizar", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de pagos de ventas pendientes de sincronizar", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al generar registros de pagos de ventas pendientes de sincronizar : {$this->link->error} {$sql}" );
				}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationSales( $destinity_store_id, $limit, $petition_unique_folio, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			$resp['sales'] = array();
			$resp['queries'] = array();
			$sql = "SELECT 
						id_sincronizacion_venta,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '0000-00-00 00:00:00', '' ) AS data,
						tabla
					FROM sys_sincronizacion_ventas
					WHERE tabla = 'ec_pedidos'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$destinity_store_id}
					AND json != ''
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql );//or die( "Error al consultar los datos de jsons : {$sql} {$this->link->error}" );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta los datos de jsons", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar los datos de jsons", 'sys_sincronizacion_ventas', $sql, $this->link->error );
					}
					die( "Error al consultar los datos de jsons : {$this->link->error} {$sql}" );
				}
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' 
					&& json_decode($row['data']) != null && json_decode($row['data']) != '' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = str_replace( "\t", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp['sales'], json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
					//actualiza al status 2 los registros que va a enviar
						$sql = "UPDATE sys_sincronizacion_ventas SET id_status_sincronizacion = 2, folio_unico_peticion = '{$petition_unique_folio}' WHERE id_sincronizacion_venta = {$row['id_sincronizacion_venta']}";
						$stm_2 = $this->link->query( $sql );
						if( $logger_id ){
							$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Pone registro de sincronizacion de ventas en status 2", $sql );
						}
						if( $this->link->error ){
							if( $logger_id ){
								$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al poner registro de sincronizacion de ventas en status 2", 'sys_sincronizacion_ventas', $sql, $this->link->error );
							}
							die( "Error al poner registro de sincronizacion de ventas en status 2 : {$this->link->error} {$sql}" );
						}	
				}else{
					die("No es un JSON {$sql} {$row['data']}");
				}
			}

			$sql = "SELECT 
						id_sincronizacion_venta,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '0000-00-00 00:00:00', '' ) AS data,
						tabla
					FROM sys_sincronizacion_ventas
					WHERE tabla != 'ec_pedidos'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$destinity_store_id}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta los datos de jsons", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar los datos de jsons", 'sys_sincronizacion_ventas', $sql, $this->link->error );
					}
					die( "Error al consultar los datos de jsons : {$this->link->error} {$sql}" );
				}	
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' 
					&& json_decode($row['data']) != null && json_decode($row['data']) != '' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = str_replace( "\t", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp['queries'], json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				}else{
					die("No es un JSON {$row['data']}");
				}
			}

			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateSaleSynchronization( $rows, $petition_unique_folio, $status = 3, $logger_id = false ){
			$log_steep_id = null;
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_ventas 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza registros de sincronización exitosos", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar registros de sincronización exitosos", 'sys_sincronizacion_ventas', $sql, $this->link->error );
					}
					die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );
				}
		}
//inserción de movimientos
		public function insertSales( $data, $logger_id = false ){
			$log_steep_id = null;
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$sales = $data["sales"];
			$queries = $data["queries"];
			$updates = array();
			foreach ($sales as $key => $sale) {
				$ok = true;
				$this->link->autocommit( false );
			//inserta cabecera
				$sql = "INSERT INTO ec_pedidos ( folio_nv, id_cliente, fecha_alta, subtotal, total, pagado, 
					id_sucursal, id_usuario, descuento, folio_abono, correo, facebook, ultima_sincronizacion, 
					ultima_modificacion, tipo_pedido, id_status_agrupacion, id_cajero, id_devoluciones, 
					venta_validada, folio_unico, id_sesion_caja, tipo_sistema )
				VALUES ( '{$sale['folio_nv']}', {$sale['id_cliente']}, '{$sale['fecha_alta']}', '{$sale['subtotal']}', '{$sale['total']}', '{$sale['pagado']}', 
					'{$sale['id_sucursal']}', '{$sale['id_usuario']}', '{$sale['descuento']}', '{$sale['folio_abono']}', '{$sale['correo']}', '{$sale['facebook']}', '{$sale['ultima_sincronizacion']}', 
					'{$sale['ultima_modificacion']}', '{$sale['tipo_pedido']}', '{$sale['id_status_agrupacion']}', '{$sale['id_cajero']}', '{$sale['id_devoluciones']}', 
					'{$sale['venta_validada']}', '{$sale['folio_unico']}', {$sale['id_sesion_caja']}, '{$sale['tipo_sistema']}' )";
				$stm_head = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta Cabecera de venta", $sql );
					}
					if( $this->link->error ){
						$ok = false;
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar Cabecera de venta", 'ec_pedidos', $sql, $this->link->error );
						}
					}
				if( $ok == true ){
					$sql = "SELECT MAX( id_pedido ) AS last_id FROM ec_pedidos";
					$stm = $this->link->query( $sql );
						if( $logger_id ){
							$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Recupera id de Cabecera de venta", $sql );
						}
						if( $this->link->error ){
							$ok = false;
							if( $logger_id ){
								$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al recuperar id de Cabecera de venta", 'ec_pedidos', $sql, $this->link->error );
							}
						}
					$row = $stm->fetch_assoc();
					$sale_id = $row['last_id'];
				//inserta detalle(s) 
					if( $ok == true ){
						$sale_detail = $sale['sale_detail'];
						foreach ($sale_detail as $key2 => $detail) {
							if( $ok == true ){
								$sql = "INSERT INTO ec_pedidos_detalle ( id_pedido, id_producto, cantidad, precio, monto, 
									cantidad_surtida, descuento, es_externo, id_precio, folio_unico ) 
								VALUES ( '{$sale_id}', '{$detail['id_producto']}', '{$detail['cantidad']}', '{$detail['precio']}', '{$detail['monto']}', 
									'{$detail['cantidad_surtida']}', '{$detail['descuento']}', '{$detail['es_externo']}', '{$detail['id_precio']}', '{$detail['folio_unico']}' )"; 
								$stm = $this->link->query( $sql );// or die( "Error al insertar detalle de venta : {$sql} {$this->link->error}");
									if( $logger_id ){
										$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta detalle de venta", $sql );
									}
									if( $this->link->error ){
										$ok = false;
										if( $logger_id ){
											$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar detalle de venta", 'ec_pedidos_detalle', $sql, $this->link->error );
										}
									}
							}
						}
					//inserta la referencia de la devolucion
						$return_reference = $sale['return_reference'];
						foreach ($return_reference as $key2 => $reference) {
							if( $ok == true ){
								$sql = "INSERT INTO ec_pedidos_referencia_devolucion ( id_pedido, total_venta, monto_venta_mas_ultima_devolucion, saldo_a_favor, folio_unico, sincronizar )  
								VALUES ( '{$sale_id}', '{$reference['total_venta']}', '{$reference['monto_venta_mas_ultima_devolucion']}', '{$reference['saldo_a_favor']}', 
									'{$reference['folio_unico']}', 1 )"; 
								$stm = $this->link->query( $sql );// or die( "Error al insertar detalle de venta : {$sql} {$this->link->error}");
									if( $logger_id ){
										$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta referencia de devolucion", $sql );
									}
									if( $this->link->error ){
										$ok = false;
										if( $logger_id ){
											$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar referencia de devolucion", 'ec_pedidos_referencia_devolucion', $sql, $this->link->error );
										}
									}
							}
						}
					}
				}
				if( $ok == true ){
					$this->link->commit();
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
				}else{
					$this->link->rollback();
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
				}
			}

	//sincroniza los registros de sincronizacion
			foreach ($queries as $key => $row) {
				//$tmp = json_decode($row);
				//echo $tmp['action_type'];
				$ok = true;
				$sql = "";
				$condition = "";
				if( isset( $row['primary_key'] ) && isset( $row['primary_key_value'] ) ){
					$condition .= "WHERE {$row['primary_key']} = '{$row['primary_key_value']}'";
				}
				if( isset( $row['secondary_key'] ) && isset( $row['secondary_key_value'] ) ){
					$condition .= " AND {$row['secondary_key']} = '{$row['secondary_key_value']}'";
				}

				$condition = str_replace( "'(", "(", $condition );
				$condition = str_replace( ")'", ")", $condition );
				$sql = "";
				switch ( $row['action_type'] ) {
					case 'insert' :
						$sql = "INSERT INTO {$row['table_name']} ( ";
						$fields = "";
						$values   = "";
						foreach ($row as $key2 => $value) {
							if( $key2 != 'table_name' && $key2 != 'action_type' && $key2 != 'primary_key' 
								&& $key2 != 'primary_key_value' && $key2 != 'secondary_key' 
								&& $key2 != 'secondary_key_value' && $key2 != 'synchronization_row_id' ){
								$fields .= ( $fields == "" ? "" : ", " );
								$fields .= "{$key2}";
								$values .= ( $values == "" ? "" : ", " );
								$values .= "'{$value}'";
							}
						}
						$fields .= " )";
						$sql .=  "{$fields} VALUES ( {$values} )";

						$sql = str_replace( "'(", "(", $sql );
						$sql = str_replace( ")'", ")", $sql );
						
						$stm = $this->link->query( $sql );// or die( "Error al ejecutar consuta adicional : {$sql} {$this->link->error}" );
							if( $logger_id ){
								$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Procesa consulta de venta", $sql );
							}
							if( $this->link->error ){
								$ok = false;
								if( $logger_id ){
									$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al procesar consulta de venta", $row['table_name'], $sql, $this->link->error );
								}
							}else{
								$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['folio_unico']}'";
							}
					break;
					
					default:
						die( "JSON incorrecto ( sin accion ) : {$row['action_type']}" );
					break;
				}
			}
	//
	//$this->link->autocommit( true );
			return $resp;
		}
	}
?>