<?php

	class movementsSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
//obtener fecha y hora actual
		public function getCurrentTime(){
			 $sql = "SELECT NOW() AS date_time";
			$stm = $this->link->query( $sql ) or die( "Error al consultar hora de llegada al Web Service : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['date_time'];
		}

		public function getSystemConfiguration(){
			$sql = "SELECT 
		            TRIM(value) AS value, 
		            ( SELECT id_sucursal FROM sys_sucursales WHERE acceso = '1' ) AS system_store,
		            NOW() AS process_initial_date_time 
		          FROM api_config WHERE name = 'path'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
			$config_row = $stm->fetch_assoc();
			//$path = trim ( $config_row['value'] );
			//$system_store = $config_row['system_store'];
			//$initial_time = $config_row['process_initial_date_time'];
			return $config_row;
		}

		public function setNewSynchronizationMovements( $system_store ){
			$resp = array();
			$sql = "SELECT 
			       		IF( limite < 0, 999999, limite ) AS movements_limit 
					FROM sys_limites_sincronizacion 
					WHERE tabla = 'ec_movimiento_almacen'";
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos de almacen : {$this->link->error}";
			}
			$limit_row = $stm->fetch_assoc( );
			$limit = $limit_row['movements_limit'];
			$sql = "SELECT 
			       		IF( limite < 0, 999999, limite ) AS movements_limit 
					FROM sys_limites_sincronizacion 
					WHERE tabla = 'ec_movimiento_almacen'";
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos de almacen : {$this->link->error}";
			}
			$limit_row = $stm->fetch_assoc( );
			$limit = $limit_row['movements_limit'];
			$sql = "CALL buscaMovimientosPendientes( {$system_store}, {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos de almacen : {$this->link->error}";
			}
			return 'ok';
		}

		public function getSynchronizationMovements( $system_store ){
			$resp = array();
			$sql = "SELECT 
			       		IF( limite < 0, 999999, limite ) AS movements_limit 
					FROM sys_limites_sincronizacion 
					WHERE tabla = 'ec_movimiento_almacen'";
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de movimientos de almacen : {$this->link->error}";
			}
			$limit_row = $stm->fetch_assoc( );
			$limit = $limit_row['movements_limit'];
			$sql = "SELECT 
						id_sincronizacion_movimiento_almacen,
						REPLACE( json, '\r\n', ' ' ) AS data,
						tabla
					FROM sys_sincronizacion_movimientos_almacen
					WHERE tabla = 'ec_movimiento_almacen'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$system_store} 
					/*LIMIT {$limit}*/";
		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons : {$this->link->error}" );
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' ){
				//	echo 'here';
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					//decodifica el JSON
					array_push( $resp, json_decode($row['data']) );
					$movements_counter ++;
				}
			}
			//var_dump( $resp );
			return $resp;
		}

		public function insertPetitionLog( $system_store, $initial_time ){
			$resp = array();
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tipo, hora_comienzo, 
			hora_envio ) VALUES( NULL, '{$system_store}', -1, 'MOVIMIENTOS DE ALMACEN', '{$initial_time}', NOW() )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registro de peticion : {$this->link->error}" );
			$sql = "SELECT LAST_INSERT_ID() AS id";
			$stm = $this->link->query( $sql ) or die( "Error al consultar El útimo id insertado : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$id = $row['id'];
			$sql = "UPDATE sys_sincronizacion_peticion SET folio_unico = CONCAT( 'REQ_MA_{$id}' ) WHERE id_peticion = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar Folio Unico de Petición : {$this->link->error}" );
			$this->link->autocommit( true );

//recupera el registro
		    $sql  = "SELECT 
		              id_peticion AS petition_id,
		              id_sucursal_origen AS origin_store,
		              id_sucursal_destino AS destinity_store,
		              tipo AS petition_type,
		              hora_comienzo AS intial_time,
		              hora_envio AS request_time,
		              folio_unico AS unique_folio
		            FROM sys_sincronizacion_peticion 
		            WHERE id_peticion = {$id}";
		    $stm = $this->link->query( $sql ) or die( "Error al recuperar datos de Peticion " );
		    $resp = $stm->fetch_assoc();
		   	return $resp;
		}

		public function updatePetitionLog( $destinity_time, $response_time, $response_string, $unique_folio ){
			$response_string = str_replace( "'", "\'", $response_string );
		    $sql = "UPDATE sys_sincronizacion_peticion 
		              SET hora_llegada_destino = '{$destinity_time}',
		                hora_respuesta = '{$response_time}',
		                contenido_respuesta = '{$response_string}',
		                hora_llegada_respuesta = NOW()
		            WHERE folio_unico = '{$unique_folio}'";
		    $stm = $this->link->query( $sql ) or die( "Error al actualizar respuesta de sincronización : {$sql} {$this->link->error}" );
		}

		public function sendPetition( $path, $post_data ){
			$resp = "";
			$url = $path.'/rest/v1/inserta_movimientos_almacen';
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			//envia peticion
			$resp = curl_exec($crl);
			//return $result;
			//Cierra curl sesión
			curl_close($crl);
			return $resp;
		}

		public function updateMovementSynchronization( $rows, $status ){
			$sql = "UPDATE sys_sincronizacion_movimientos_almacen 
              SET id_status_sincronizacion = '{$status}' 
            WHERE registro_llave IN( {$rows} )";
   	 		$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );
		}

		public function insertMovements( $movements ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			foreach ($movements as $key => $movement) {
				$this->link->autocommit( false );
				$ok = true;
				$sql = "INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, 
				id_orden_compra, lote, id_maquila, id_transferencia, id_almacen, status_agrupacion, id_equivalente, folio_unico, insertado_por_sincronizacion )
				VALUES ( NULL, {$movement['id_tipo_movimiento']}, {$movement['id_usuario']}, {$movement['id_sucursal']}, '{$movement['fecha']}', '{$movement['hora']}', 
				'{$movement['observaciones']} \nInsertado desde API por sincronización', -1/*{$movement['id_pedido']}*/, 
				{$movement['id_orden_compra']}, '{$movement['lote']}', {$movement['id_maquila']}, -1/*{$movement['id_transferencia']}*/, 
				{$movement['id_almacen']}, {$movement['status_agrupacion']}, {$movement['id_equivalente']}, '{$movement['folio_unico']}', '1' )";

				$stm_head = $this->link->query( $sql );//or die( "Error al insertar cabecera de movimiento de almacen : {$sql} {$this->link->error}" );
				if( ! $stm_head ){
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
						VALUES ( NULL, '{$movement_id}', '{$detail['id_producto']}', '{$detail['cantidad']}', '{$detail['cantidad_surtida']}', 
						-1, -1, IF( {$detail['id_proveedor_producto']} IS NULL OR '{$detail['id_proveedor_producto']}' = '', NULL, '{$detail['id_proveedor_producto']}' ), '0', 
						'0', '{$detail['folio_unico']}', '1' )"; 
						$stm = $this->link->query( $sql );
						if( ! $stm ){
						  return "error  : {$this->link->error}";
						  $ok = false;
						}
					//actuualiza el inventario
						$sql = "UPDATE ec_almacen_producto ap
									SET ap.inventario = ( ap.inventario + 
										( SELECT 
											( tm.afecta * {$detail['cantidad']} ) 
											FROM ec_tipos_movimiento tm
											WHERE tm.id_tipo_movimiento = {$movement['id_tipo_movimiento']}
										) 
									)
								WHERE ap.id_producto = {$detail['id_producto']}
    							AND ap.id_almacen = {$movement['id_almacen']}";
    					$stm = $this->link->query( $sql ) or die( "Error al actualizar el inventario a nivel producto : {$this->link->error}" );
					//inserta el proveedor producto
						/*if( $detail['id_proveedor_producto'] != "NULL" ){
							$this->insertProductProviderDetail(  );
						}*/
					}
				}
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$movement['folio_unico']}'";
				}
				$this->link->autocommit( true );
			}
			return $resp;
		}

		public function insertResponse( $log, $request_initial_time, $tmp_ok, $tmp_no ){
			$tmp_ok = str_replace("'", "\'", $tmp_ok );
			$tmp_no = str_replace("'", "\'", $tmp_no );
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tipo, hora_comienzo, 
			hora_envio, folio_unico, hora_llegada_destino, hora_respuesta, contenido_respuesta ) VALUES( NULL, '{$log['origin_store']}', 
			'{$log['destinity_store']}', '{$log['petition_type']}', '{$log['intial_time']}', 
			'{$log['request_time']}', '{$log['unique_folio']}', '{$request_initial_time}', NOW(), '{$tmp_ok} | {$tmp_no}' )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registro de peticion : {$sql} {$this->link->error} {$sql}" );
			$this->link->autocommit( true );
			//recupera el registro
			$sql  = "SELECT 
			        hora_llegada_destino AS destinity_time,
			        hora_respuesta AS response_time,
			        contenido_respuesta AS response_string,
			        folio_unico AS unique_folio
			      FROM sys_sincronizacion_peticion 
			      WHERE folio_unico = '{$log['unique_folio']}'";
			$stm = $this->link->query( $sql ) or die( "Error al recuperar datos de Peticion {$this->link->error}" );
			//obtiene los movimientos que tiene que regresar para el nodo local
			$resp = $stm->fetch_assoc();
			return $resp;
		}

		public function getPendingResponses( $system_store, $unique_folio ){
			$resp = array();
			$sql = "SELECT 
						folio_unico AS unique_folio
					FROM sys_sincronizacion_peticion
					WHERE folio_unico != '{$unique_folio}'
					AND id_sucursal_origen = {$system_store}
					AND contenido_respuesta IS NULL";
//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al consultar los registros de sincronizacion pendientes : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push( $resp, $row );
			}
			return $resp;
		}

		public function validatePendingPetition( $petitions ){
			$resp = array();
			foreach( $petitions as $petition ){
				$sql = "SELECT 
							hora_llegada_destino AS destinity_time,
							hora_respuesta AS response_time,
							contenido_respuesta AS response_string
						FROM sys_sincronizacion_peticion
						WHERE folio_unico = '{$petition['unique_folio']}'";
				$stm = $this->link->query( $sql ) or die( "Error al consultar datos de respuesta : {$this->link->error}" );
				$tmp = array();
				if( $stm->num_rows <= 0 ){
					$tmp['response_string'] = "error";
				}
				while ( $row = $stm->fetch_assoc() ) {
					$tmp = $row;	
				}
				$tmp['unique_folio'] = $petition['unique_folio'];
				array_push( $resp, $tmp );
			}
			return $resp;
		}

		public function updatePendingPetitions( $before_petitions ){
			//var_dump($before_petitions);
			foreach( $before_petitions as $before_petition ){
			//var_dump($before_petition);
				$updates = ( $before_petition->destinity_time != '' && $before_petition->destinity_time != null ? "hora_llegada_destino = '{$before_petition->destinity_time}'," : "" );
				$updates .= ( $before_petition->response_time != '' && $before_petition->response_time != null ? "hora_respuesta = '{$before_petition->response_time}'," : "" );
			//die( "here" );
				$tmp = str_replace( "'", "\'", $before_petition->response_string );

				$sql = "UPDATE sys_sincronizacion_peticion
							SET {$updates}
							contenido_respuesta = '{$tmp}'
						WHERE folio_unico = '{$before_petition->unique_folio}'";
		//echo ( $sql );
				$stm = $this->link->query( $sql ) or die( "Error al actualizar datos de respuesta pendiente : {$sql} {$this->link->error}" );
				$tmp = array();
				if( $before_petition->response_string != "error" ){
					$tmp = explode( ' | ', $before_petition->response_string );
					if( $tmp[0] != "" && $tmp[0] != null && $tmp[0] != "e" ){
						$this->updateMovementSynchronization( $tmp[0], 3 );
					}
					if( $tmp[1] != "" && $tmp[1] != null && $tmp[1] != "e" ){
						$this->updateMovementSynchronization( $tmp[1], 2 );
					}
				}
			}
		}

	}
?>
