<?php

	class returnsSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationReturns( $store_id, $system_store, $origin_store_prefix, $limit ){
		//crea JSONS de devoluciones
			$sql = "CALL buscaDevolucionesPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de devoluciones pendientes de sincronizar : {$this->link->error} {$sql}";
			}
		//crea JSONS de pagos de devoluciones
			$sql = "CALL buscaPagosDevolucionesPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de pagos de devolucion pendientes de sincronizar : {$this->link->error} {$sql}";
			}
			return 'ok';
		}

//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationReturns( $system_store, $limit ){
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_devolucion,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '\r', '' ) AS data,
						tabla
					FROM sys_sincronizacion_devoluciones
					WHERE tabla = 'ec_devolucion'
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
		public function updateReturnSynchronization( $rows, $petition_unique_folio, $status = 3 ){
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_devoluciones 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );	
		}
//inserción de movimientos
		public function insertReturns( $returns ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$updates = array();
			$this->link->autocommit( false );
			foreach ($returns as $key => $return) {
				$ok = true;
			//inserta cabecera
				$sql = "INSERT INTO ec_devolucion ( id_usuario, id_sucursal, fecha, hora, id_pedido, 
				folio, es_externo, status, observaciones, tipo_sistema, id_status_agrupacion, folio_unico )
				VALUES ( '{$return['id_usuario']}', '{$return['id_sucursal']}', '{$return['fecha']}', 
					'{$return['hora']}', {$return['id_pedido']}, '{$return['folio']}', '{$return['es_externo']}', 
					'{$return['status']}', '{$return['observaciones']}', '{$return['tipo_sistema']}', 
					'{$return['id_status_agrupacion']}', '{$return['folio_unico']}' )";
				$sql = str_replace( "'(", "(", $sql );
				$sql = str_replace( ")'", ")", $sql );

				$stm_head = $this->link->query( $sql );//or die( "Error al insertar cabecera de movimiento de almacen : {$sql} {$this->link->error}" );
				if( ! $stm_head ){
					return array( "error"=>"Error al insertar cabecera de devolucion : {$sql} {$this->link->error} ");
					$ok = false;
				}
				$sql = "SELECT LAST_INSERT_ID() AS last_id";
				$stm = $this->link->query( $sql ) or die( "Error al recuperar el id insertado : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$return_id = $row['last_id'];
			//inserta detalle(s) 
				$return_detail = $return['return_detail'];
				foreach ($return_detail as $key2 => $detail) {
					if( $ok == true ){
						$sql = "INSERT INTO ec_devolucion_detalle ( id_devolucion, id_producto, id_proveedor_producto, 
						cantidad, folio_unico, id_pedido_detalle ) 
						VALUES ( '{$return_id}', '{$detail['id_producto']}', '{$detail['id_proveedor_producto']}', '{$detail['cantidad']}',
							'{$detail['folio_unico']}', "; 
						$sql .= ( $detail['id_pedido_detalle'] != null ? "'{$detail['id_pedido_detalle']}'" : "'0'" ) . " )";
						
						$sql = str_replace( "'(", "(", $sql );
						$sql = str_replace( ")'", ")", $sql );
						
						$stm = $this->link->query( $sql ) or die( "Error al insertar detalle de devolucion : {$sql} {$this->link->error}" );
						if( ! $stm ){
							return array( "error"=>"Error al insertar detalle de devolucion : {$this->link->error}" );
						  $ok = false;
						}
					}
				}
			//iniserta pago(s) 
				$return_payments = $return['return_payments'];
				foreach ($return_payments as $key2 => $payment) {
					if( $ok == true ){
						$sql = "INSERT INTO ec_devolucion_pagos ( id_devolucion, id_tipo_pago, monto, referencia, 
							es_externo, fecha, hora, id_cajero, folio_unico, id_sesion_caja ) 
						VALUES ( '{$return_id}', '{$payment['id_tipo_pago']}', '{$payment['monto']}', 
							'{$payment['referencia']}', '{$payment['es_externo']}', '{$payment['fecha']}', '{$payment['hora']}', 
							'{$payment['id_cajero']}', '{$payment['folio_unico']}', {$payment['id_sesion_caja']} )";
						
						$sql = str_replace( "'(", "(", $sql );
						$sql = str_replace( ")'", ")", $sql );

						$stm = $this->link->query( $sql ) or die( "Error al insertar pago de devolucion : {$sql} {$this->link->error}" );
						if( ! $stm ){
							return array( "error"=>"Error al insertar pago de devolucion : {$this->link->error}");
						  $ok = false;
						}
					}
				}
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$return['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$return['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$return['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$return['folio_unico']}'";
				}
			}
		    $this->link->autocommit( true );
			return $resp;
		}
	}
?>