<?php
/**/
	class SynchronizationManagmentLog
	{
		private $link;
		function __construct( $connection ){
//	die( 'here' );
			$this->link = $connection;
		}
//verificar si las APIS estan bloqueadas
		public function validate_apis_are_not_locked( $store_id ){
		//consulta apis generales
			$sql = "SELECT 
						bloquear_apis_sincronizacion AS apis_are_locked
					FROM sys_configuracion_sistema 
					WHERE id_configuracion_sistema = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si las apis estan bloqueadas : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['apis_are_locked'] != 0 ){
				return 'Las apis del servidor estan bloqueadas de manera general!';
			}
		//consulta api especifica de la sucursal
			$sql = "SELECT 
						permite_sincronizar_manualmente AS permission
					FROM sys_resumen_sincronizacion_sucursales 
					WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si las apis de la sucursal estan bloqueadas : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['permission'] != 1 ){
				return 'Las apis de la sucursal estan bloqueadas!';
			}
		//consulta si puede entrar la sincronizacion de acuerdo al numero de sincronizaciones configuradas 
			$sql = "SELECT 
						SUM( IF( permite_sincronizacion_automaticamente = 3, 1, 0 ) ) AS currently_synchronization,
						( SELECT limite_sincronizaciones_simultaneas FROM sys_configuracion_sistema ) AS synchronization_limit
					FROM sys_resumen_sincronizacion_sucursales";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si las apis de la sucursal estan bloqueadas : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['synchronization_limit'] == 0 ){
				return "El limite de sincronización es CERO!";
			}
			if( $row['currently_synchronization'] > $row['synchronization_limit'] ){
				return "Se llegó al límite de las sincronizaciones; limite : {$row['synchronization_limit']}; Sucursales sincronizando : {$row['currently_synchronization']}";
			}
			return 'ok';
		}
//indicador de sucursal en sincronizacion
		public function updateSynchronizationStatus( $store_id, $type ){
			$sql = "UPDATE sys_resumen_sincronizacion_sucursales SET permite_sincronizacion_automaticamente = {$type} WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar status de sincronización en sucursal : {$this->link->error}" );
			return 'ok';
		} 			
//bloquear modulo de sincronizacion
		public function block_sinchronization_module( $table ){
			$sql = "SELECT sincronizando AS synchronizing FROM sys_limites_sincronizacion WHERE tabla = '{$table}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el modulo esta sincronizando : {$this->link->error} {$sql}" );
			$row = $stm->fetch_assoc();
			if( $row['synchronizing'] == 1 ){
				return "La tabla '{$table}' ya esta sincronizando!";
			}
		//actualiza indicador de que la tabla esta sincronizando
			$sql = "UPDATE sys_limites_sincronizacion SET sincronizando = 1 WHERE tabla = '{$table}'";
			$stm = $this->link->query( $sql ) or die( "Error al marcar que el modulo '{$table}' esta sincronizando : {$this->link->error} {$sql}" );
			return 'ok';
		}
//liberar modulo de sinconizacion
		public function release_sinchronization_module( $table ){
		//actualiza indicador de que la tabla esta sincronizando
			$sql = "UPDATE sys_limites_sincronizacion SET sincronizando = 0 WHERE tabla = '{$table}'";
			$stm = $this->link->query( $sql ) or die( "Error al marcar que el modulo '{$table}' esta sincronizando : {$this->link->error} {$sql}" );
			return 'ok';
		}
//obtener configuracion, fecha y hora actual
		public function getSystemConfiguration( $table ){
			$sql = "SELECT 
		            TRIM(value) AS value, 
		            ( SELECT id_sucursal FROM sys_sucursales WHERE acceso = '1' ) AS system_store,
		            ( SELECT TRIM( prefijo ) FROM sys_sucursales WHERE acceso = '1' ) AS store_prefix,
		            NOW() AS process_initial_date_time,
		            (SELECT 
			       		IF( limite <= 0, 999999, limite ) 
					FROM sys_limites_sincronizacion 
					WHERE tabla = '{$table}') AS rows_limit 
		          FROM api_config WHERE name = 'path'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
			$config_row = $stm->fetch_assoc();
			return $config_row;
		}
//obtener hora actual
		public function getCurrentTime(){
			 $sql = "SELECT NOW() AS date_time";
			$stm = $this->link->query( $sql ) or die( "Error al consultar hora de llegada al Web Service : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['date_time'];
		}

//envio de peticiones
		public function sendPetition( $url, $post_data ){
			$resp = "";
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			return $resp;
		}
//inserta generacion de peticion ( origen )
		public function insertPetitionLog( $system_store, $destinity_store, $store_prefix, $initial_time, $log_type ){
			$resp = array();
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tipo, hora_comienzo, 
			hora_envio ) VALUES( NULL, '{$system_store}', '{$destinity_store}', '{$log_type}', '{$initial_time}', NOW() )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registro de peticion : {$this->link->error}" );
			$sql = "SELECT LAST_INSERT_ID() AS id";
			$stm = $this->link->query( $sql ) or die( "Error al consultar El útimo id insertado : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$id = $row['id'];
			$sql = "UPDATE sys_sincronizacion_peticion SET folio_unico = CONCAT( '{$store_prefix}_REQ_MA_{$id}' ) WHERE id_peticion = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar Folio Unico de Petición : {$this->link->error}" );
			$this->link->autocommit( true );
			return $this->getPetitionLog( $row['id'] );
		}
//actualiza log de peticion ( origen )
		public function updatePetitionLog( $destinity_time, $response_time, $response_string, $unique_folio ){
			$response_string = str_replace( "'", "\'", $response_string );
		    $sql = "UPDATE sys_sincronizacion_peticion 
		              SET hora_llegada_destino = '{$destinity_time}',
		                hora_respuesta = '{$response_time}',
		                contenido_respuesta = '{$response_string}',
		                hora_llegada_respuesta = NOW()
		            WHERE folio_unico = '{$unique_folio}'";
		    $stm = $this->link->query( $sql ) or die( "Error al actualizar respuesta de sincronización : {$sql} {$this->link->error}" );
			return $this->getPetitionLog( $unique_folio );
		}
//inserta respuesta ( destino )
		public function insertResponse( $log, $request_initial_time ){//, $tmp_ok, $tmp_no
			$tmp_ok = str_replace("'", "\'", $tmp_ok );
			$tmp_no = str_replace("'", "\'", $tmp_no );
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tipo, hora_comienzo, 
			hora_envio, folio_unico, hora_llegada_destino, hora_respuesta ) VALUES( NULL, '{$log['origin_store']}', 
			'{$log['destinity_store']}', '{$log['type']}', '{$log['intial_time']}', 
			'{$log['shipping_time']}', '{$log['unique_folio']}', '{$request_initial_time}', NOW() )";//, contenido_respuesta, '{$tmp_ok} | {$tmp_no}'
			$stm = $this->link->query( $sql ) or die( "Error al insertar registro de peticion : {$sql} {$this->link->error} {$sql}" );
			$this->link->autocommit( true );
			return $this->getPetitionLog( $log['unique_folio'] );
		}
//actualiza respuesta ( destino )
		public function updateResponseLog( $response_string, $unique_folio ){
			$response_string = str_replace( "'", "\'", $response_string );
		    $sql = "UPDATE sys_sincronizacion_peticion 
		              SET hora_respuesta = NOW(),
		                contenido_respuesta = '{$response_string}'
		            WHERE folio_unico = '{$unique_folio}'";
		    $stm = $this->link->query( $sql ) or die( "Error al actualizar respuesta de sincronización : {$sql} {$this->link->error}" );
			return $this->getPetitionLog( $unique_folio );
		}
//recupera el registro de peticion
		public function getPetitionLog( $unique_folio ){
			$stm = "";
			$sql_base  = "SELECT 
				        folio_unico AS unique_folio,
				        id_sucursal_origen AS origin_store,
				        id_sucursal_destino AS destinity_store,
				        tipo AS type,
				        hora_comienzo AS intial_time,
				        hora_envio AS shipping_time, 
				        hora_llegada_destino AS destinity_time,
				        hora_respuesta AS response_time,
				        contenido_respuesta AS response_string
			      FROM sys_sincronizacion_peticion";//OR id_peticion = '{$unique_folio}'
			$sql = $sql_base . " WHERE folio_unico = '{$unique_folio}'";
			$stm = $this->link->query( $sql ) or die( "Error al recuperar datos de Peticion por folio {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				$sql = $sql_base . " WHERE id_peticion = '{$unique_folio}'";
				$stm = $this->link->query( $sql ) or die( "Error al recuperar datos de Peticion por iid {$this->link->error}" );
				if( $stm->num_rows <= 0 ){
					die( "La peticion no fue encontrada con la clave : {$unique_folio}" );
				}
			}
			$resp = $stm->fetch_assoc();
			return $resp;
		}
//obtener respuestas pendientes ( sin hora de respuesta )
		public function getPendingResponses( $system_store, $unique_folio ){
			$resp = array();
			$sql = "SELECT 
						folio_unico AS unique_folio
					FROM sys_sincronizacion_peticion
					WHERE id_sucursal_origen = {$system_store}
					AND hora_respuesta IS NULL";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los registros de sincronizacion pendientes : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push( $resp, $row );
			}
			return $resp;
		}

//validacion de peticiones pendientes en servidor destino
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
					$tmp['response_string'] = "La peticion no llego al servidor destino.";
				}
				while ( $row = $stm->fetch_assoc() ) {
					$tmp = $row;	
				}
				$tmp['unique_folio'] = $petition['unique_folio'];
				array_push( $resp, $tmp );
			}
			return $resp;
		}
//actualizacion de peticiones en servidor origen
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
							contenido_respuesta = '{$tmp}',
							hora_llegada_respuesta = NOW()
						WHERE folio_unico = '{$before_petition->unique_folio}'";
		//echo ( $sql );
				$stm = $this->link->query( $sql ) or die( "Error al actualizar datos de respuesta pendiente : {$sql} {$this->link->error}" );
				$tmp = array();
				if( $before_petition->response_string != "error" ){
					$tmp = explode( ' | ', $before_petition->response_string );
					if( $tmp[0] != "" && $tmp[0] != null && $tmp[0] != "e" ){
						$this->updateMovementSynchronization( $tmp[0], $unique_folio, 3, null );
					}
					if( $tmp[1] != "" && $tmp[1] != null && $tmp[1] != "e" ){
						$this->updateMovementSynchronization( $tmp[1], $unique_folio, 2, null );
					}
				}
			}
		}

		public function updateModuleResume( $module, $action_type, $response, $store_id ){//subida, bajada
		//actualiza el resumen del modulo
			$sql = "UPDATE sys_resumen_sincronizacion_sucursales_detalle 
						SET 
						contenido_ultima_respuesta_{$action_type} = '{$response}',
						fecha_hora_ultima_actualizacion_{$action_type} = NOW()
					WHERE id_modulo = ( SELECT id_modulo FROM sys_limites_sincronizacion WHERE tabla = '{$module}' )
					AND id_resumen_sincronizacion_sucursal = {$store_id}";
			$this->link->query( $sql ) or die( "Error al actualizar el resumen de respuesta : {$sql} {$this->link->error}" );
			return 'ok';
		}

	}
?>