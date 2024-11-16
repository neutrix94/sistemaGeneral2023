<?php
/**/
	class SynchronizationManagmentLog
	{
		private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
//	die( 'here' );
            include( '../../conexionMysqli.php' );
            $this->link = $link;
			//$this->link = $connection;
			$this->LOGGER = $Logger;
		}
//verificar si las APIS estan bloqueadas
		public function validate_apis_are_not_locked( $store_id, $logger_id = false ){
			$log_steep_id = null;
		//consulta apis generales
			$sql = "SELECT 
						bloquear_apis_sincronizacion AS apis_are_locked
					FROM sys_configuracion_sistema 
					WHERE id_configuracion_sistema = 1";
			$stm = $this->link->query( $sql );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se consulta que las apis no esten bloqueadas en general', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, 'sys_configuracion_sistema', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar si las apis estan bloqueadas : {$this->link->error}" );
			}
			$row = $stm->fetch_assoc();
			if( $row['apis_are_locked'] != 0 ){
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Las apis del servidor estan bloqueadas de manera general!', 'N/A' );
				}
				die( 'Las apis del servidor estan bloqueadas de manera general!' );
			}
		//consulta api especifica de la sucursal
			$sql = "SELECT 
						permite_sincronizar_manualmente AS permission
					FROM sys_resumen_sincronizacion_sucursales 
					WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se consulta que las apis no esten bloqueadas en la sucursal', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, 'sys_resumen_sincronizacion_sucursales', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar si las apis de la sucursal estan bloqueadas : {$sql} {$this->link->error}" );
			}
			$row = $stm->fetch_assoc();
			if( $row['permission'] != 1 ){
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Las apis de la sucursal estan bloqueadas!', 'N/A' );
				}
				return 'Las apis de la sucursal estan bloqueadas!';
			}
		//consulta si puede entrar la sincronizacion de acuerdo al numero de sincronizaciones configuradas 
			$sql = "SELECT 
						SUM( IF( permite_sincronizacion_automaticamente = 3, 1, 0 ) ) AS currently_synchronization,
						( SELECT limite_sincronizaciones_simultaneas FROM sys_configuracion_sistema ) AS synchronization_limit
					FROM sys_resumen_sincronizacion_sucursales";
			$stm = $this->link->query( $sql );

			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se consulta que las apis no esten bloqueadas en la sucursal', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, 'sys_resumen_sincronizacion_sucursales', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar si las apis de la sucursal estan bloqueadas : {$this->link->error}" );
			}
			$row = $stm->fetch_assoc();
			if( $row['synchronization_limit'] == 0 ){
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'El limite de sincronización es CERO!', "N/A" );
				}
				return "El limite de sincronización es CERO!";
			}
			if( $row['currently_synchronization'] > $row['synchronization_limit'] ){
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se llegó al límite de las sincronizaciones; limite : {$row['synchronization_limit']}; Sucursales sincronizando : {$row['currently_synchronization']}", "N/A" );
				}
				return "Se llegó al límite de las sincronizaciones; limite : {$row['synchronization_limit']}; Sucursales sincronizando : {$row['currently_synchronization']}";
			}
			return 'ok';
		}
//indicador de sucursal en sincronizacion
		public function updateSynchronizationStatus( $store_id, $type, $logger_id = false ){
			$log_steep_id = null;
			$sql = "UPDATE sys_resumen_sincronizacion_sucursales SET permite_sincronizacion_automaticamente = {$type} WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql );// or die( "Error al actualizar status de sincronización en sucursal : {$this->link->error}" );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se actualiza resumen de sincronizacion por sucursal', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, 'Error al actualizar status de sincronización en sucursal', 'sys_resumen_sincronizacion_sucursales', $sql, $this->link->error );
				}
				die( "Error al actualizar status de sincronización en sucursal : {$this->link->error}" );
			}
			return 'ok';
		} 			
//bloquear modulo de sincronizacion
		public function block_sinchronization_module( $table, $logger_id = false ){
			$log_steep_id = null;
			$sql = "SELECT sincronizando AS synchronizing FROM sys_limites_sincronizacion WHERE tabla = '{$table}'";
			$stm = $this->link->query( $sql );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se consulta si el modulo esta en proceso de sincronizacion', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al marcar que el modulo {$table} esta sincronizando", 'sys_limites_sincronizacion', $sql, $this->link->error );
				}
				die( "Error al marcar que el modulo {$table} esta sincronizando : {$this->link->error} {$sql}" );
			}
			
				//or die( "Error al consultar si el modulo esta sincronizando : {$this->link->error} {$sql}" );
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
		public function release_sinchronization_module( $table, $logger_id = false ){
			$log_steep_id = null;
		//actualiza indicador de que la tabla esta sincronizando
			$sql = "UPDATE sys_limites_sincronizacion SET sincronizando = 0 WHERE tabla = '{$table}'";
			$stm = $this->link->query( $sql );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, 'Se actualiza el indicador de sincronizacion en limites de sincronizacion', $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al marcar que el modulo {$table} esta sincronizando", 'sys_limites_sincronizacion', $sql, $this->link->error );
				}
				die( "Error al marcar que el modulo {$table} esta sincronizando : {$this->link->error} {$sql}" );
			}
			return 'ok';
		}
//obtener configuracion, fecha y hora actual
		public function getSystemConfiguration( $table, $logger_id = false ){
			$log_steep_id = null;
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
			$stm = $this->link->query( $sql );// or die( "Error al consultar path de api : {$this->link->error}" );			
			
			if( $logger_id ){//die( "1" );
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se consulta la configuracion para el modulo {$table}", $sql );
			}
			//die( "1.1" );
			if( $this->link->error ){die( "2" );
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar configuracion de modulo {$table}", 'api_config', $sql, $this->link->error );
				}
				die( "Error al consultar configuracion de modulo {$table} : {$this->link->error} {$sql}" );
			}
			//die( "3" );
			$config_row = $stm->fetch_assoc();
			$config_row['logger_sql'] = $sql;
			return $config_row;
		}
//obtener hora actual
		public function getCurrentTime( $logger_id = false ){
			$log_steep_id = null;
			$sql = "SELECT NOW() AS date_time";
			$stm = $this->link->query( $sql );// or die( "Error al consultar hora de llegada al Web Service : {$this->link->error}" );
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta hora y fecha", $sql );
			}
			if( $this->link->error ){
				if( $logger_id ){
					$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar hora y fecha", 'api_config', $sql, $this->link->error );
				}
				die( "Error al consultar hora y fecha : {$this->link->error} {$sql}" );
			}
			$row = $stm->fetch_assoc();
			return $row['date_time'];
		}

//envio de peticiones
		public function sendPetition( $url, $post_data, $logger_id = false ){
			$resp = "";
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			if( $logger_id ){
				$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Envia peticion a {$url}", $post_data );
			}
			return $resp;
		}
//inserta generacion de peticion ( origen )
		public function insertPetitionLog( $system_store, $destinity_store, $store_prefix, $initial_time, $log_type, $table_name, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tabla, tipo, hora_comienzo, 
			hora_envio ) VALUES( NULL, '{$system_store}', '{$destinity_store}', '{$table_name}', '{$log_type}', '{$initial_time}', NOW() )";
			$stm = $this->link->query( $sql );// or die( "Error al insertar registro de peticion : {$this->link->error}" );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se inserta registro de peticion en sys_sincronizacion_peticion", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar registro de peticion", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al insertar registro de peticion : {$this->link->error} {$sql}" );
				}
			$sql = "SELECT MAX( id_peticion ) AS id FROM sys_sincronizacion_peticion ";//LAST_INSERT_ID()
			$stm = $this->link->query( $sql ) or die( "Error al consultar El útimo id insertado : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$id = $row['id'];
			$sql = "UPDATE sys_sincronizacion_peticion SET folio_unico = CONCAT( '{$store_prefix}_REQ_MA_{$id}' ) WHERE id_peticion = {$id}";
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se actualiza folio unico de peticion en sys_sincronizacion_peticion", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar folio unico de peticion", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al actualizar folio unico de peticion : {$this->link->error} {$sql}" );
				}
			if( $logger_id ){
				$sql = "UPDATE LOG_sincronizaciones SET folio_unico_sincronizacion = '{$store_prefix}_REQ_MA_{$id}' 
							WHERE id_sincronizacion = {$logger_id}";
				$this->link->query( $sql ) or die( "Error al actualizar folio unico en LOG_sincronizaciones : {$this->link->error}" );
			}
			$this->link->autocommit( true );
			return $this->getPetitionLog( $row['id'] );
		}
//actualiza log de peticion ( origen )
		public function updatePetitionLog( $destinity_time, $response_time, $response_string, $unique_folio, $logger_id = false ){
			$log_steep_id = null;
			$response_string = str_replace( "'", "\'", $response_string );
		    $sql = "UPDATE sys_sincronizacion_peticion 
		              SET hora_llegada_destino = '{$destinity_time}',
		                hora_respuesta = '{$response_time}',
		                contenido_respuesta = '{$response_string}',
		                hora_llegada_respuesta = NOW(),
		                hora_finalizacion = NOW()
		            WHERE folio_unico = '{$unique_folio}'";
		    $stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se actualiza respuesta de petición : sys_sincronizacion_peticion", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar Error al actualizar respuesta de petición :", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al actualizar respuesta de petición : {$this->link->error} {$sql}" );
				}
			return $this->getPetitionLog( $unique_folio );
		}
//inserta respuesta ( destino )
		public function insertResponse( $log, $request_initial_time, $logger_id = false ){//, $tmp_ok, $tmp_no
			$log_steep_id = null;
			$tmp_ok = str_replace("'", "\'", $tmp_ok );
			$tmp_no = str_replace("'", "\'", $tmp_no );
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sincronizacion_peticion ( id_peticion, id_sucursal_origen, id_sucursal_destino, tabla, tipo, hora_comienzo, 
			hora_envio, folio_unico, hora_llegada_destino, hora_respuesta ) VALUES( NULL, '{$log['origin_store']}', 
			'{$log['destinity_store']}', '{$log['table_name']}', '{$log['type']}', '{$log['intial_time']}', 
			'{$log['shipping_time']}', '{$log['unique_folio']}', '{$request_initial_time}', NOW() )";//, contenido_respuesta, '{$tmp_ok} | {$tmp_no}'
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Se Inserta registro de peticion (respuesta)", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar registro de peticion (respuesta)", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "insertResponse : Error al insertar registro de peticion (respuesta) : {$this->link->error} {$sql}" );
				}
			$this->link->autocommit( true );
			return $this->getPetitionLog( $log['unique_folio'] );
		}
//actualiza respuesta ( destino )
		public function updateResponseLog( $response_string, $unique_folio, $logger_id = false ){
			$log_steep_id = null;
			//$response_string = str_replace( "'", "\'", $response_string );
		    $sql = "UPDATE sys_sincronizacion_peticion 
		              SET hora_respuesta = NOW(),
		                contenido_respuesta = \"{$response_string}\"
		            WHERE folio_unico = '{$unique_folio}'";
		    $stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza respuesta de petición", $sql, true );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar respuesta de petición", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al actualizar respuesta de petición : {$this->link->error} {$sql}" );
				}
			// or die( "Error al actualizar respuesta de sincronización : {$sql} {$this->link->error}" );
			return $this->getPetitionLog( $unique_folio );
		}
//recupera el registro de peticion
		public function getPetitionLog( $unique_folio, $logger_id = false ){
			$log_steep_id = null;
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
				        contenido_respuesta AS response_string,
						tabla AS table_name
			      FROM sys_sincronizacion_peticion";//OR id_peticion = '{$unique_folio}'
			$sql = $sql_base . " WHERE folio_unico = '{$unique_folio}'";
			$stm = $this->link->query( $sql ) or die( "Error al recuperar datos de Peticion por folio : {$sql} : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				$sql = $sql_base . " WHERE id_peticion = '{$unique_folio}'";
				$stm = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Recupera informacion de registro de petición", $sql );
					}
					if( $this->link->error ){
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al recuperar informacion de registro de petición", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						die( "Error al recuperar informacion de registro de petición : {$this->link->error} {$sql}" );
					}
				if( $stm->num_rows <= 0 ){
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "La peticion con el folio unico : {$unique_folio} no fue encontrada.", "N/A" );
					}
					die( "La peticion con el folio unico : {$unique_folio} no fue encontrada." );
				}
			}
			$resp = $stm->fetch_assoc();
			return $resp;
		}
//obtener respuestas pendientes ( sin hora de respuesta )
		public function getPendingResponses( $system_store, $unique_folio, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			$sql = "SELECT 
						folio_unico AS unique_folio
					FROM sys_sincronizacion_peticion
					WHERE id_sucursal_origen = {$system_store}
					AND hora_respuesta IS NULL";
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Recuperar respuestas pendientes", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al recuperar respuestas pendientes", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al recuperar respuestas pendientes : {$this->link->error} {$sql}" );
				}
			while( $row = $stm->fetch_assoc() ){
				array_push( $resp, $row );
			}
			return $resp;
		}

//validacion de peticiones pendientes en servidor destino
		public function validatePendingPetition( $petitions, $logger_id = false ){
			$log_steep_id = null;
			$resp = array();
			foreach( $petitions as $petition ){
				$sql = "SELECT 
							hora_llegada_destino AS destinity_time,
							hora_respuesta AS response_time,
							contenido_respuesta AS response_string
						FROM sys_sincronizacion_peticion
						WHERE folio_unico = '{$petition['unique_folio']}'";
				$stm = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Validación de peticiones pendientes en servidor destino", $sql );
					}
					if( $this->link->error ){
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al validar peticiones pendientes", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						die( "Error al validar peticiones pendientes : {$this->link->error} {$sql}" );
					}
				// or die( "Error al consultar datos de respuesta : {$this->link->error}" );
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
		public function updatePendingPetitions( $before_petitions, $logger_id = false ){
			$log_steep_id = null;
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
							hora_llegada_respuesta = NOW(),
							hora_finalizacion = NOW()
						WHERE folio_unico = '{$before_petition->unique_folio}'";
		//echo ( $sql );
				$stm = $this->link->query( $sql );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza peticione pendiente en servidor destino", $sql );
					}
					if( $this->link->error ){
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar datos de respuesta pendiente :", 'sys_sincronizacion_peticion', $sql, $this->link->error );
						}
						die( "Error al actualizar datos de respuesta pendiente : {$this->link->error} {$sql}" );
					}
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

		public function updateModuleResume( $module, $action_type, $response, $store_id, $logger_id = false ){//subida, bajada
			$log_steep_id = null;
		//actualiza el resumen del modulo
			$sql = "UPDATE sys_resumen_sincronizacion_sucursales_detalle 
						SET 
						contenido_ultima_respuesta_{$action_type} = '{$response}',
						fecha_hora_ultima_actualizacion_{$action_type} = NOW()
					WHERE id_modulo = ( SELECT id_modulo FROM sys_limites_sincronizacion WHERE tabla = '{$module}' )
					AND id_resumen_sincronizacion_sucursal = {$store_id}";
			$this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza resumen de respuesta", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar el resumen de respuesta :", 'sys_sincronizacion_peticion', $sql, $this->link->error );
					}
					die( "Error al actualizar el resumen de respuesta : {$this->link->error} {$sql}" );
				}
			return 'ok';
		}

	}
?>