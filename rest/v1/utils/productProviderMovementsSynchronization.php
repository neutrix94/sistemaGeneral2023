<?php

	class productProviderMovementsSynchronization
	{
		private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
			$this->link = $connection;
			$this->LOGGER = $Logger;
		}
		
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationProductProviderMovements( $store_id, $system_store, $origin_store_prefix, $limit, $logger_id = false ){
			$log_steep_id = null;
			$sql = "CALL buscaMovimientosProveedorProductoPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Genera registros de Movimientos de Almacen Ptoveedor Producto", $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al generar registros de movimientos proveedor producto por sincronizar", 'sys_sincronizacion_peticion', $sql, $this->link->error );
				}
				die( "Error al generar registros de movimientos proveedor producto por sincronizar : {$this->link->error} {$sql}" );
			}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationProductProviderMovements( $system_store, $limit, $type, $petition_unique_folio, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_movimiento_proveedor_producto,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '\r', '' ) AS data,
						tabla
					FROM sys_sincronizacion_movimientos_proveedor_producto
					WHERE tabla = 'ec_movimiento_detalle_proveedor_producto'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$system_store}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql );// or die( "Error al consultar los datos de jsons : {$this->link->error}" );
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
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' 
					&& json_decode($row['data']) != null && json_decode($row['data']) != '' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = str_replace( "\t", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp, json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				//actualiza al status 2 los registros que va a enviar
					$sql = "UPDATE sys_sincronizacion_movimientos_proveedor_producto SET id_status_sincronizacion = 2, folio_unico_peticion = '{$petition_unique_folio}' WHERE id_sincronizacion_movimiento_proveedor_producto = {$row['id_sincronizacion_movimiento_proveedor_producto']}";
					$stm_2 = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza registro de sincronizacion movimiento proveedor producto a status 2", $sql );
					}
					if( $this->link->error ){
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al poner registro de sincronizacion de movimiento proveedor producto en status 2", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						die( "Error al poner registro de sincronizacion de movimiento proveedor producto en status 2 : {$this->link->error} {$sql}" );
					}
				}
			}
			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateProductProviderMovementsSynchronization( $rows, $petition_unique_folio, $status = 3, $logger_id = false ){
			$log_steep_id = null;
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_movimientos_proveedor_producto 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql );
			
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza status de registros de sincronizacion proveedor producto", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar status de registros de sincronizacion proveedor producto", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al actualizar status de registros de sincronizacion proveedor producto : {$this->link->error} {$sql}" );
				}// or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );	
		}
//inserción de movimientos
		public function insertProductProviderMovements( $product_providers_movements, $logger_id = false ){
			$log_steep_id = null;
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			foreach ( $product_providers_movements as $key => $p_p_movement ) {
				if( $p_p_movement['id_movimiento_almacen_detalle'] != -1 && $p_p_movement['id_movimiento_almacen_detalle'] != '' && $p_p_movement['id_movimiento_almacen_detalle'] != null ){
					$sql = $p_p_movement['id_movimiento_almacen_detalle'];
					$stm = $this->link->query( $sql ) or die( "Error al consultar detalle de movimeinto a nivel producto en inserción de movimientos proveedor producto : {$sql} : {$this->link->error}" );
					$row = $stm->fetch_row();
					$p_p_movement['id_movimiento_almacen_detalle'] = $row[0];
				}
				if( $p_p_movement['id_pedido_validacion'] != -1 && $p_p_movement['id_pedido_validacion'] != '' && $p_p_movement['id_pedido_validacion'] != null ){
					$sql = $p_p_movement['id_pedido_validacion'];
					$stm = $this->link->query( $sql ) or die( "Error al consultar id de validacion en inserción de movimientos proveedor producto : {$sql} : {$this->link->error}" );
					$row = $stm->fetch_row();
					$p_p_movement['id_pedido_validacion'] = $row[0];
					if( $p_p_movement['id_pedido_validacion'] == '' || $p_p_movement['id_pedido_validacion'] == null ){
						$p_p_movement['id_pedido_validacion']= -1;
					}
				}
				$this->link->autocommit( false );
				$ok = true;
			//inserta cabecera
				$sql = "CALL spMovimientoDetalleProveedorProducto_inserta( {$p_p_movement['id_movimiento_almacen_detalle']}, {$p_p_movement['id_proveedor_producto']}, {$p_p_movement['cantidad']}, 
				{$p_p_movement['id_sucursal']}, {$p_p_movement['id_tipo_movimiento']}, {$p_p_movement['id_almacen']}, {$p_p_movement['id_pedido_validacion']}, 
				{$p_p_movement['id_pantalla']}, '{$p_p_movement['folio_unico']}' )";
				/*$sql = str_replace("' (", "(", $sql);
				$sql = str_replace("'(", "(", $sql);
				$sql = str_replace(")'", ")", $sql);
				$sql = str_replace(") '", ")", $sql);
				$sql = str_replace("NULL, ,", "NULL, NULL,", $sql);*/
				$stm_head = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta movimiento detalle proveedor producto", $sql );
					}
					if( $this->link->error ){
						$ok = false;
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar movimiento detalle proveedor producto", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						//die( "Error al insertar cabecera de movimientos de almacen : {$this->link->error} {$sql}" );
					}
				if( $ok == true ){
					$this->link->commit();
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
				}else{
					$this->link->rollback();
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
				}
			}
			return $resp;
		}


//actualización de inventario almacen producto
/*		public function updateProductProviderInventory( $movements ){
			$updates = array();
			$updates_logs = array();
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';
			foreach ($movements as $key => $movement) {
		    		if( $movement['id_proveedor_producto'] == NULL || $movement['id_proveedor_producto'] == null
		    		||  $movement['id_proveedor_producto'] == '' ){//para evitar error de pp con id pp null
		    			 $movement['id_proveedor_producto'] = "NULL";
		    		}
					$sql = "UPDATE ec_inventario_proveedor_producto
						SET inventario = ( inventario + {$movement['cantidad_surtida']} )
					WHERE id_proveedor_producto = {$movement['id_proveedor_producto']}
					AND id_almacen = {$movement['id_almacen']}";
		    		array_push( $updates, $sql );
		    		if( $movement['id_proveedor_producto'] == 'NULL' ){//para evitar error de pp con id pp null
		    			 $movement['id_proveedor_producto'] = 0;
		    		}
		    		$sql = "INSERT INTO log_mov_prov_prod ( log_inv_prov_prod, id_proveedor_producto, id_almacen, 
		    			cantidad, folio_unico ) VALUES ( NULL, {$movement['id_proveedor_producto']}, {$movement['id_almacen']},
		    			{$movement['cantidad_surtida']}, '{$movement['folio_unico']}' )";
		    		array_push( $updates_logs, $sql );
			}
		//ejecuta las consultas de actualizacion de inventario
			//$this->link->autocommit( false );
		    foreach( $updates as $update ){
		        $stm = $this->link->query( $update ) or die( "Error al actualizar el inventario proveedor producto : {$sql} {$this->link->error}" );
		    	if( !$stm ){
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
	    		}else{
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";

	    		}
		    }

		    foreach( $updates_logs as $update_log ){
		        $stm = $this->link->query( $update_log ) or die( "Error al insertar log de suma inventario proveedor producto : {$this->link->error}" );
		    }
			//$this->link->autocommit( true );
			return $resp;
		}*/
	}
?>