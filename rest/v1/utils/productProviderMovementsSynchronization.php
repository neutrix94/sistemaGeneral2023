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
		public function getSynchronizationProductProviderMovements( $system_store, $limit ){
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
			$this->link->autocommit( false );
			foreach ( $product_providers_movements as $key => $p_p_movement ) {
				$ok = true;
			//inserta cabecera
				$sql = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_almacen_detalle, 
				id_proveedor_producto, cantidad, fecha_registro, id_sucursal, status_agrupacion, 
				id_tipo_movimiento, id_almacen, id_pedido_validacion, folio_unico, sincronizar, id_pantalla, insertado_por_sincronizacion )
				VALUES ( {$p_p_movement['id_movimiento_almacen_detalle']}, {$p_p_movement['id_proveedor_producto']}, 
					'{$p_p_movement['cantidad']}', '{$p_p_movement['fecha_registro']}', '{$p_p_movement['id_sucursal']}', 
					'{$p_p_movement['status_agrupacion']}', '{$p_p_movement['id_tipo_movimiento']}', 
					'{$p_p_movement['id_almacen']}', '{$p_p_movement['id_pedido_validacion']}', '{$p_p_movement['folio_unico']}', '1', '{$p_p_movement['id_pantalla']}', '1' )";
				$sql = str_replace("' (", "(", $sql);
				$sql = str_replace("'(", "(", $sql);
				$sql = str_replace(")'", ")", $sql);
				$sql = str_replace(") '", ")", $sql);
				$sql = str_replace("NULL, ,", "NULL, NULL,", $sql);
				$stm_head = $this->link->query( $sql )or die( "Error al insertar de movimiento de almacen proveedor producto : {$sql} {$this->link->error}" );
				if( ! $stm_head ){
					return array( "error"=>"Error al insertar movimiento detalle proveedor producto : {$this->link->error} {$sql}");
					$ok = false;
				}
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$p_p_movement['folio_unico']}'";
				}
			}
		    $this->link->autocommit( true );
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