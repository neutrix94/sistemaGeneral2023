<?php

	class movementsSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationMovements( $store_id, $system_store, $origin_store_prefix, $limit ){
			$sql = "CALL buscaMovimientosPendientes( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos de almacen : {$this->link->error}";
			}
			$sql = "CALL buscaDetallesMovimientosPendientes( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de detalles movimientos de almacen : {$this->link->error}";
			}
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationMovements( $system_store, $limit, $type ){
			$condition = "";
			if( $type == 1 ){
				$condition = "AND id_status_sincronizacion IN( 1 )";
			}elseif( $type == 2 ){
				$condition = "AND id_status_sincronizacion IN( 3 ) AND movimiento_sumado = 0";
			}
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_movimiento_almacen,
						REPLACE( json, '\r\n', ' ' ) AS data,
						tabla
					FROM sys_sincronizacion_movimientos_almacen
					WHERE tabla = 'ec_movimiento_almacen'
					AND id_sucursal_destino = {$system_store}
					{$condition}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons : {$this->link->error}" );
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
				}
			}
			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateMovementSynchronization( $rows, $petition_unique_folio, $status = null, $sum = false ){
			$sql = "";
			if( $status != null ){//actualiza status y folio unico de peticion
				$sql = "UPDATE sys_sincronizacion_movimientos_almacen 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";

	   		}else if( $sum == true ){//actualiza a sumado y folio unico de peticion
				$sql = "UPDATE sys_sincronizacion_movimientos_almacen 
	              SET movimiento_sumado = '1',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   		}
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );
		
		}
//inserción de movimientos
		public function insertMovements( $movements ){
//oscar 2023
$file = fopen("movements_log.txt", "w");
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			$this->link->autocommit( false );
			foreach ($movements as $key => $movement) {
				$ok = true;//{$movement['id_orden_compra']}

$movement_detail = $movement['movimiento_detail'];
$is_valid = true;
foreach ($movement_detail as $key2 => $detail) {
//echo 'here';
	if( $detail['id_pedido_detalle'] != -1 && $detail['id_pedido_detalle'] != '' 
		&& $detail['id_pedido_detalle'] != null ){
		$sql = "{$detail['id_pedido_detalle']}";
//echo $sql;
fwrite($file, "{$sql}\n" );
		$stm_aux = $this->link->query( $sql ) or die( "Error al consultar si existe el detalle de venta : {$this->link->error}" );
		if( $stm_aux->num_rows <= 0 ){
			$is_valid = false;
			$ok = false;
		}else{
			$is_valid = true;
			$ok = true;
		}
	}	
}

if( $is_valid == true ){
				$sql = "INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, 
				id_orden_compra, lote, id_maquila, id_transferencia, id_almacen, status_agrupacion, folio_unico, insertado_por_sincronizacion, id_pantalla, sincronizar )
				VALUES ( NULL, {$movement['id_tipo_movimiento']}, {$movement['id_usuario']}, {$movement['id_sucursal']}, '{$movement['fecha']}', '{$movement['hora']}', 
				'{$movement['observaciones']} \nInsertado desde API por sincronización', -1, 
				'-1', '{$movement['lote']}', '{$movement['id_maquila']}', '{$movement['id_transferencia']}', 
				'{$movement['id_almacen']}', '{$movement['status_agrupacion']}', '{$movement['folio_unico']}', '1', '{$movement['id_pantalla']}', '1' )";
fwrite($file, "{$sql}\n");
			//reemplazamiento de comillas en consultas
				$sql = str_replace( "'(", "(", $sql );
				$sql = str_replace( "' (", "(", $sql );
				$sql = str_replace( ")'", ")", $sql );
				$sql = str_replace( ") '", ")", $sql );

				$stm_head = $this->link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen : {$sql} {$this->link->error}" );
				if( ! $stm_head ){
					return array( "error"=>"Error al insertar cabecera de movimiento de almacen : {$this->link->error} {$sql}");
					$ok = false;
				}
				$sql = "SELECT LAST_INSERT_ID() AS last_id";
				$stm = $this->link->query( $sql ) or die( "Error al recuperar el id insertado : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$movement_id = $row['last_id'];
				$movement_detail = $movement['movimiento_detail'];

				foreach ($movement_detail as $key2 => $detail) {
					if( $ok == true ){
						$sql = "INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, cantidad, cantidad_surtida, 
						id_pedido_detalle, id_oc_detalle, id_proveedor_producto, id_equivalente, sincronizar, folio_unico, insertado_por_sincronizacion ) 
						VALUES ( NULL, '{$movement_id}', '{$detail['id_producto']}', '{$detail['cantidad']}', '{$detail['cantidad']}', 
						{$detail['id_pedido_detalle']}, -1, IF( {$detail['id_proveedor_producto']} IS NULL OR '{$detail['id_proveedor_producto']}' = '', NULL, '{$detail['id_proveedor_producto']}' ), '0', 
						'1', '{$detail['folio_unico']}', '1' )"; 
						$stm = $this->link->query( $sql ) or die( "Error al insertar detalle de movimiento de almacen : {$sql} {$this->link->error}" );
fwrite($file, "{$sql}\n" );
						if( ! $stm ){
							return array( "error"=>"Error al insertar detalle de movimiento de almacen : {$this->link->error}");
						  $ok = false;
						}
					}
				}
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
				}
fwrite($file, "\n\n" );
			}
		}
fclose($file);
		    $this->link->autocommit( true );
			return $resp;
		}
//actualización de inventario almacen producto
		public function updateInventory( $movements ){
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
		}
	}
?>