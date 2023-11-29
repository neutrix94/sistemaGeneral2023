<?php

	class salesValidationSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationsalesValidation( $store_id, $system_store, $origin_store_prefix, $limit ){
			$sql = "CALL buscaValidacionesProveedorProductoPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de validaciones de ventas por sincronizar : {$this->link->error} {$sql}";
			}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationsalesValidation( $system_store, $limit ){
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_validacion,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '\r', '' ) AS data,
						tabla
					FROM sys_sincronizacion_validaciones_ventas
					WHERE tabla = 'ec_pedidos_validacion_usuarios'
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
		public function updateSalesValidationSynchronization( $rows, $petition_unique_folio, $status = 3 ){
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_validaciones_ventas 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );	
		}
//inserción de movimientos
		public function insertSalesValidation( $validations ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			$this->link->autocommit( false );
			foreach ( $validations as $key => $validation ) {
				$ok = true;
				$sale_detail_id_field  = ( $validation['id_pedido_detalle'] != '' && $validation['id_pedido_detalle'] != null ? "  id_pedido_detalle," : "" );
				$sale_detail_id_value  = ( $validation['id_pedido_detalle'] != '' && $validation['id_pedido_detalle'] != null ? "  {$validation['id_pedido_detalle']}," : "" );
			//inserta cabecera
				$sql = "INSERT INTO ec_pedidos_validacion_usuarios ({$sale_detail_id_field} id_producto, piezas_validadas, 
					piezas_devueltas, id_usuario, id_sucursal, fecha_alta, folio_unico, tipo_sistema, validacion_finalizada, id_proveedor_producto )
				VALUES ({$sale_detail_id_value} '{$validation['id_producto']}', '{$validation['piezas_validadas']}', 
					'{$validation['piezas_devueltas']}', '{$validation['id_usuario']}', '{$validation['id_sucursal']}', 
					'{$validation['fecha_alta']}', '{$validation['folio_unico']}', '{$validation['tipo_sistema']}', 
					'{$validation['validacion_finalizada']}', ";
			//valida si el proveedor producto es nullo o vacío
				if( $validation['id_proveedor_producto'] == null || $validation['id_proveedor_producto'] == NULL
					|| $validation['id_proveedor_producto'] == 'null' || $validation['id_proveedor_producto'] == 'NULL'
					|| $validation['id_proveedor_producto'] == '' ){
					$sql .= "NULL )";
				}else{
					$sql .= "'{$validation['id_proveedor_producto']}' )";
				}
				//'{$validation['id_proveedor_producto']}' )";
				$stm = $this->link->query( $sql ) or die( "Error insertar validacion de venta por sincronizacion : {$sql} {$this->link->error}" );
				
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$validation['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$validation['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$validation['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$validation['folio_unico']}'";
				}
			}
		    $this->link->autocommit( true );
			return $resp;
		}
	}
?>