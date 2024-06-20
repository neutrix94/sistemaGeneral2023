<?php

	class productProviderMovementsSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
		
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationProductProviderMovements( $store_id, $system_store, $origin_store_prefix, $limit ){
			$sql = "CALL buscaMovimientosProveedorProductoPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos proveedor producto por sincronizar : {$this->link->error} {$sql}";
			}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationProductProviderMovements( $system_store, $limit, $petition_unique_folio ){
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
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons : {$this->link->error}" );
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
					$sql = "UPDATE sys_sincronizacion_movimientos_proveedor_producto SET id_status_sincronizacion = 2, folio_unico_peticion = '{$petition_unique_folio}' 
					WHERE id_sincronizacion_movimiento_proveedor_producto = {$row['id_sincronizacion_movimiento_proveedor_producto']}";
					$stm_2 = $this->link->query( $sql ) or die( "Error al poner registro de sincronizacion de detalle movimiento de almacen proveedor producto en status 2 : {$sql} : {$this->link->error}" );
				}
			}
			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateProductProviderMovementsSynchronization( $rows, $petition_unique_folio, $status = 3 ){
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_movimientos_proveedor_producto 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );	
		}
//inserción de movimientos
		public function insertProductProviderMovements( $product_providers_movements ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			foreach ( $product_providers_movements as $key => $p_p_movement ) {
				//$ok = true;
			//consulta el id del detalle a nivel producto
				$sql = "{$p_p_movement['id_movimiento_almacen_detalle']}";
				$stm = $this->link->query( $sql );
				if( $this->link->error ){
					$ok = false;
					// or die( "Error al consultar el id de cabecera de movimientos de almacen : {$sql} : {$this->link->error}" );
				}
				$row = $stm->fetch_row();
				$p_p_movement['id_movimiento_almacen_detalle'] = $row[0];
			//consulta el id de la validacion si es el caso
				if( $p_p_movement['id_pedido_validacion'] != -1 && $p_p_movement['id_pedido_validacion'] != "-1" ){
					$sql = "{$p_p_movement['id_pedido_validacion']}";
					$stm = $this->link->query( $sql );
					if( $this->link->error ){
						$ok = false;
						//die( "Error al consultar el id de la validacion relacionada al detalle pp : {$sql} : {$this->link->error}" );
					}
					$row = $stm->fetch_row();
					$p_p_movement['id_pedido_validacion'] = $row[0];
				}
				if( $ok == true ){
					$this->link->autocommit( false );//declara inicio de la transaccion
				//inserta registro a nivel proveedor producto
					$sql = "CALL spMovimientoDetalleProveedorProducto_inserta( {$p_p_movement['id_movimiento_almacen_detalle']}, {$p_p_movement['id_proveedor_producto']}, {$p_p_movement['cantidad']}, 
								{$p_p_movement['id_sucursal']}, {$p_p_movement['id_tipo_movimiento']}, {$p_p_movement['id_almacen']}, {$p_p_movement['id_pedido_validacion']}, 
								{$p_p_movement['id_pantalla']}, '{$p_p_movement['folio_unico']}' )";
					/*$sql = str_replace("' (", "(", $sql);
					$sql = str_replace("'(", "(", $sql);
					$sql = str_replace(")'", ")", $sql);
					$sql = str_replace(") '", ")", $sql);*/
					$sql = str_replace("NULL, ,", "NULL, NULL,", $sql);
					$stm_head = $this->link->query( $sql );//or die( "Error al insertar de movimiento de almacen proveedor producto : {$sql} {$this->link->error}" );
					if( ! $this->link->error ){
						$this->link->commit();
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
						$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
						//$ok = false;
					}else{
						$this->link->rollback();
					//inserta el log del error en tabla de errores
						$sql = "INSERT INTO sys_sincronizacion_log_errores_registros ( tabla, folio_unico_registro, instruccion_sql, error_sql, fecha_alta )
									VALUES ( 'sys_sincronizacion_movimientos_proveedor_producto', '{$p_p_movement['folio_unico']}', '{$sql}', '{$this->link->error}', NOW() )";
						$stm = $this->link->query( $sql );// or die( "Error al insertar error en sys_sincronizacion_log_errores_registros : {$this->link->error}" );
						if( $this->link->error ){
							echo "Error al insertar el log de error en sincronización : {$this->link->error}";
						}
						$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
						$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
					}
				}
			}
			$this->link->close();
			return $resp;
		}


//actualización de inventario almacen producto
		public function updateProductProviderInventory( $movements ){
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
		}
	}
?>