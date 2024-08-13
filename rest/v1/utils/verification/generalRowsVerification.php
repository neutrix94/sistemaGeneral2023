<?php
    class generalRowsVerification{
        private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
			$this->link = $connection;
            $this->LOGGER = $Logger;
		}

        public function insertVerificationLog( $table, $unique_folio, $json_detail, $logger_id = false ){
            $log_steep_id = null;
            $json_detail = str_replace( "'", "\'", $json_detail );
            $sql = "INSERT INTO sys_sincronizacion_comprobaciones_log ( tabla, folio_unico_peticion, json_comprobacion, fecha_alta ) 
                        VALUES( '{$table}', '{$unique_folio}', '{$json_detail}', NOW() )";
            $stm = $this->link->query( $sql );
                if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta log de comprobacion", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar log de comprobacion", 'sys_sincronizacion_comprobaciones_log', $sql, $this->link->error );
					}
					die( "Error al insertar log de comprobacion : {$this->link->error} {$sql}" );
				}
            return 'ok';
        }

        public function getPendingRows( $origin_store_id, $destinity_store_id, $table_name, $logger_id = false){
            $log_steep_id = null;
            $resp = array();
            $pending_rows = array();
            $sql = "SELECT 
                        sp.id_peticion AS petition_id,
                        sp.folio_unico AS unique_folio,
                        sp.id_sucursal_origen AS origin_store,
                        sp.id_sucursal_destino AS destinity_store,
                        sp.tabla AS table_name,
                        sp.tipo AS petition_type,
                        sp.hora_comienzo AS datetime_start,
                        sp.hora_envio AS datetime_send,
                        sp.hora_llegada_destino AS datetime_destinity,
                        sp.hora_respuesta AS datetime_send_response,
                        sp.contenido_respuesta AS response_content,
                        sp.hora_llegada_respuesta AS datetime_response,
                        sp.hora_finalizacion AS datetime_end
                    FROM sys_sincronizacion_peticion sp
                    LEFT JOIN {$table_name} sr
                    ON sr.folio_unico_peticion = sp.folio_unico
                    WHERE sp.tabla = '{$table_name}'
                    AND sp.id_sucursal_origen = {$origin_store_id}
                    AND sp.id_sucursal_destino = {$destinity_store_id}
                    AND sp.hora_envio IS NOT NULL
                    AND( sp.hora_llegada_destino IS NULL
                    OR sp.hora_llegada_respuesta IS NULL
                    OR sp.hora_finalizacion IS NULL )
                    OR sr.status_sincronizacion = 2
                    GROUP BY sp.id_peticion";
            $stm = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta peticiones pendientes de alguna respuesta", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar las peticiones pendientes de alguna respuesta", "{$table_name}", $sql, $this->link->error );
                    }
                    die( "Error al consultar las peticiones pendientes de alguna respuesta : {$this->link->error} {$sql}" );
                }
            $petition = $stm->fetch_assoc();
            $resp['petition'] = $petition;
            $sql = "SELECT 
                        datos_json,
                        id_sincronizacion_registro synchronization_row_id,
                        tipo AS tabla
                    FROM {$table_name}
                    WHERE folio_unico_peticion = '{$petition['unique_folio']}'
                    AND status_sincronizacion = 2";
            $stm_2 = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta detalle de json", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar detalle de json", "{$table_name}", $sql, $this->link->error );
                    }
                    die( "Error al consultar detalle de json en {$table_name} : {$this->link->error} {$sql}" );
                }
            $resp['verification'] = ( $stm->num_rows > 0 ? true : false );
            while( $detail = $stm_2->fetch_assoc() ){
                //echo 'here';
                $pending_rows[] = $detail;
            }
            $resp['rows'] = $pending_rows;
            $pending_rows_string = json_encode( $pending_rows );
        //inserta el log
            $log = $this->insertVerificationLog( "{$table_name}", $petition['unique_folio'], $pending_rows_string, $logger_id );
            if( $log != 'ok' ){
                return false;
            }
            return $resp;
        }
        
        public function validateIfExistsPetitionLog( $petition_log, $logger_id = false ){
            $log_steep_id = null;
           // var_dump( $petition_log );
            $resp = array();
        //verifica si existe el log de peticion
            $sql = "SELECT
                        id_peticion AS petition_id,
                        folio_unico AS unique_folio,
                        id_sucursal_origen AS origin_store,
                        id_sucursal_destino AS destinity_store,
                        tabla AS table_name,
                        tipo AS petition_type,
                        hora_comienzo AS datetime_start,
                        hora_envio AS datetime_send,
                        hora_llegada_destino AS datetime_destinity,
                        hora_respuesta AS datetime_send_response,
                        contenido_respuesta AS response_content,
                        hora_llegada_respuesta AS datetime_response,
                        hora_finalizacion AS datetime_end
                    FROM sys_sincronizacion_peticion
                    WHERE folio_unico = '{$petition_log['unique_folio']}'";
            $stm = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta si existe la peticion en el destino", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar si existe la peticion en el destino", 'sys_sincronizacion_peticion', $sql, $this->link->error );
                    }
                    die( "Error al consultar si existe la peticion en el destino : {$this->link->error} {$sql}" );
                }
            if( $stm->num_rows > 0 ){//existe
                $row = $stm->fetch_assoc();
                $resp = $row;
                $resp['datetime_destinity'] = ( $resp['datetime_destinity'] == null || $resp['datetime_destinity'] == null ? 'null' : $resp['datetime_destinity'] );
                $resp['datetime_send_response'] = ( $resp['datetime_send_response'] == null || $resp['datetime_send_response'] == 'null' ? null : $resp['datetime_send_response'] );
                $resp['response_content'] = ( $resp['response_content'] == null || $resp['response_content'] == 'null' ? null : $resp['response_content'] );
                $resp['datetime_response'] = ( $resp['datetime_response'] == null || $resp['datetime_response'] == 'null' ? null : $resp['datetime_response'] );
                $resp['datetime_end'] = ( $resp['datetime_end'] == null || $resp['datetime_end'] == 'null' ? null : $resp['datetime_end'] );
            }else{//no_existe
                $sql = "INSERT INTO sys_sincronizacion_peticion ( id_sucursal_origen, id_sucursal_destino, tabla, tipo, hora_comienzo, 
                            hora_envio, hora_llegada_destino, hora_respuesta, contenido_respuesta, hora_llegada_respuesta, hora_finalizacion, folio_unico )
                        VALUES ( {$petition_log['origin_store']}, {$petition_log['destinity_store']}, '{$petition_log['table_name']}', '{$petition_log['petition_type']}', '{$petition_log['datetime_start']}', 
                        '{$petition_log['datetime_send']}', NOW(), NOW(), '', NOW(), NOW(), '{$petition_log['unique_folio']}' )";
                $stm = $this->link->query( $sql );
                    if( $logger_id ){
                        $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta peticion en el destino", $sql );
                    }
                    if( $this->link->error ){
                        if( $logger_id ){
                            $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar peticion en el destino", 'sys_sincronizacion_peticion', $sql, $this->link->error );
                        }
                        die( "Error al insertar peticion en el destino : {$this->link->error} {$sql}" );
                    }
                // or die( "Error al insertar el registro de sincronizacion en el destino : {$sql} : {$this->link->error}" );
                $resp = $petition_log;
            //consulta la hor actual
                $sql = "SELECT NOW() AS current_date_time";
                $stm_2 = $this->link->query( $sql ) or die( "Error al consultar la fecha y hora : {$sql} : {$this->link->error}" );
                $row = $stm_2->fetch_assoc();
                $resp['datetime_destinity'] = $row['current_date_time'];
                $resp['datetime_send_response'] = $row['current_date_time'];
                $resp['response_content'] = $row['current_date_time'];
                $resp['datetime_response'] = $row['current_date_time'];
                $resp['datetime_end'] = $row['current_date_time'];
            }
            return $resp;
        }

        public function RowsValidation( $rows, $table_name, $logger_id = false ){
            $log_steep_id = null;
            $resp = array();
            $resp['ok_rows'] = "";
            $resp['error_rows'] = "";
            $queries = array();
			foreach ($rows as $key => $row_) {
				$sql = "";
				$condition = "";
                $row_['datos_json'] = str_replace( "\n", " ", $row_['datos_json'] );//se eliminan saltos de linea
                $row = json_decode( $row_['datos_json'], true );//
                //var_dump( $row );die('here');
				if( isset( $row['primary_key'] ) && isset( $row['primary_key_value'] ) ){
					$condition .= "WHERE {$row['primary_key']} = '{$row['primary_key_value']}'";
				}
				if( isset( $row['secondary_key'] ) && isset( $row['secondary_key_value'] ) ){
					$condition .= " AND {$row['secondary_key']} = '{$row['secondary_key_value']}'";
				}
				$condition = str_replace( "'(", "(", $condition );
				$condition = str_replace( ")'", ")", $condition );
				switch ( $row['action_type'] ) {
					case 'insert' :
                    //verifica si existe el registro
                        $verification_sql = "SELECT {$row['primary_key']} FROM {$row['table_name']} {$condition}";//die( $verification_sql );
                        $verification_stm   = $this->link->query( $verification_sql );
                            if( $logger_id ){
                                $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "(INSERT); Verifica si existe el registro en tabla {$row['table_name']} : ", $verification_sql );
                            }
                            if( $this->link->error ){
                                $ok = false;
                                if( $logger_id ){
                                    $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error en INSERT al verificar si existe el registro en tabla {$row['table_name']}", "{$row['table_name']}", $verification_sql, $this->link->error );
                                }
                                die( "Error en INSERT al verificar si existe el registro en tabla {$row['table_name']} : {$this->link->error} : {$verification_sql}" );
                            }
                        if( $verification_stm->num_rows <= 0) {//si el registro no existe
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
                            array_push( $queries, array( "query"=>$sql, "row_id"=>$row_['synchronization_row_id']) );
                            if( $row['table_name'] != 'ec_pedidos' && $row['table_name'] != 'ec_pedidos_detalle' ){
                                $sql = "UPDATE {$row['table_name']} SET sincronizar = 0 {$condition}";
                                array_push( $queries, array( "query"=>$sql, "row_id"=>"n/a") );
                            }
                        }else{//si el registro ya existe en el destino
                            $resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row_['synchronization_row_id']}'";
                        }
/*Implementacion Oscar 2024-02-12 para crear carpetas mediante la sincronizacion*/
						if( $row['table_name'] == 'sys_carpetas' ){
							mkdir( "../../{$row['`path`']}/{$row['nombre_carpeta']}" , 0777);
                            if( $logger_id ){
                                $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "(INSERT); Creacion de carpeta por sincronizacion {$row['table_name']} : ", "mkdir( \"../../{$row['`path`']}/{$row['nombre_carpeta']}\" , 0777);" );
                            }
							chmod( "../../{$row['`path`']}/{$row['nombre_carpeta']}" , 0777 );
                            if( $logger_id ){
                                $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "(INSERT); Cambia permisos de carpeta por sincronizacion {$row['table_name']} : ", "chmod( \"../../{$row['`path`']}/{$row['nombre_carpeta']}\" , 0777 );" );
                            }
						}
/*fin de cambio Oscar 2024-02-12*/
					break;
					case 'update' :
						$sql = "UPDATE {$row['table_name']} SET ";
						$fields = "";
						foreach ($row as $key2 => $value) {
							if( $key2 != 'table_name' && $key2 != 'action_type' && $key2 != 'primary_key' 
								&& $key2 != 'primary_key_value' && $key2 != 'secondary_key' 
								&& $key2 != 'secondary_key_value' && $key2 != 'synchronization_row_id' ){
								$fields .= ( $fields == "" ? "" : ", " );
								$fields .= "{$key2} = '{$value}'";
							}
						}
						$sql .= "{$fields} {$condition}";
						if( $row['table_name'] == 'ec_movimiento_detalle' ){
						//procedure aqui
							$aux = "SELECT id_movimiento_almacen_detalle AS detail_id FROM ec_movimiento_detalle WHERE folio_unico = '{$row['primary_key_value']}'";
							$aux_stm = $this->link->query( $aux );// or die( "Error al consultar id de detalle mov almacen :" );
							$aux_row = $aux_stm->fetch_assoc();
                            $sql = "CALL spMovimientoAlmacenDetalle_actualiza( {$aux_row['detail_id']}, {$row['cantidad']}, {$row['id_proveedor_producto']}, NULL );";
						}
					    array_push( $queries, array( "query"=>$sql, "row_id"=>$row_['synchronization_row_id'] ) );
					break;
					case 'delete' :
                    //verifica si existe el registro
                        $verification_sql = "SELECT {$row['primary_key']} FROM {$row['table_name']} {$condition}";
                        $verification_stm = $this->link->query( $verification_sql );
                        if( $verification_stm->num_rows > 0) {//si el registro no existe
                            $sql = "DELETE FROM {$row['table_name']} {$condition}";
						    if( $row['table_name'] == 'ec_movimiento_detalle' ){
                            //procedure aqui
                                $aux = "SELECT id_movimiento_almacen_detalle AS detail_id FROM ec_movimiento_detalle WHERE folio_unico = '{$row['primary_key_value']}'";
                                $aux_stm = $this->link->query( $aux );// or die( "Error al consultar id de detalle mov almacen :" );
                                $aux_row = $aux_stm->fetch_assoc();
                                $sql = "CALL spMovimientoAlmacenDetalle_elimina( {$aux_row['detail_id']}, NULL );";
                            }
					        array_push( $queries, array( "query"=>$sql, "row_id"=>$row_['synchronization_row_id'] ) );
                        }else{//si el registro ya no existe en el destino
                            $resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row_['synchronization_row_id']}'";
                        }
					break;

					case 'sql_instruction' : 
						$sql = $row['sql'];
						//$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
					    array_push( $queries, array( "query"=>$sql, "row_id"=>$row_['synchronization_row_id'] ) );//se manda ejecutar de nuevo al ser una consulta dinamica
					break;
					
					default:
						//var_dump($row);
						//die( "JSON incorrecto : {$row['action_type']}" );
					break;
				}
            //ejecuta instrucciones sql
                foreach ($queries as $key2 => $query_) {
                    $ok = true;
                    $this->link->autocommit(false);
                    $query = str_replace( "'(", "(", $query_['query'] );
                    $query = str_replace( ")'", ")", $query );
                    $stm = $this->link->query( $query );//
                        if( $logger_id ){
                            $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Ejecuta consulta SQL : ", $query );
                        }
                        if( $this->link->error ){
                            $ok = false;
                            if( $logger_id ){
                                $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al ejecutar consulta ", "$table_name", $query, $this->link->error );
                            }
                            die( "Error : {$sql} : {$this->link->error}" );
                        }
                    if( $ok == true && $query_['row_id'] != 'n/a' ){
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$query_['row_id']}'";
						$this->link->commit();
                    }else if( $ok == false  && $query_['row_id'] != 'n/a' ){
						$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$query_['row_id']}'";
						$this->link->rollback();
                    }
                }    
			}
            return $resp;
        }

        public function updateLogAndJsonsRows( $log_response, $rows_response, $table_name, $logger_id = false ){
            $log_steep_id = null;
            $this->link->autocommit( false );
            $log_response->response_content = str_replace( "'", "\'", $log_response->response_content );
            $sql = "UPDATE sys_sincronizacion_peticion SET hora_llegada_destino = IF( hora_llegada_destino IS NULL OR hora_llegada_destino = '', '{$log_response->datetime_destinity}', hora_llegada_destino ),
                        hora_respuesta = IF( hora_respuesta IS NULL OR hora_respuesta = '', '{$log_response->datetime_send_response}', hora_respuesta ),
                        contenido_respuesta = IF( contenido_respuesta IS NULL OR contenido_respuesta = '', '{$log_response->response_content}', contenido_respuesta ),
                        hora_llegada_respuesta = IF( hora_llegada_respuesta IS NULL OR hora_llegada_respuesta = '', NOW(), hora_llegada_respuesta ),
                        hora_finalizacion = IF( hora_finalizacion IS NULL OR hora_finalizacion = '', NOW(), hora_finalizacion )
                    WHERE folio_unico = '{$log_response->unique_folio}'";//die( $sql );
            $stm = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza la peticion (comprobación)", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar la peticion (comprobación) ", 'sys_sincronizacion_peticion', $sql, $this->link->error );
                    }
                    die( "Error al actualizar la peticion (comprobación) : {$this->link->error} {$sql}" );
                }
        //actualiza los registros correctos
            $ok_rows = $rows_response->ok_rows;
            /*$uniques_folios = '';
            $ok_rows = explode( '|', $ok_rows );//= str_replace( '|', ',', $ok_rows );
            foreach ($ok_rows as $key => $row) {
                $uniques_folios .= ( $uniques_folios == '' ? '' : ',' );
                $uniques_folios .= "'{$row}'";
            }*/
            if( $ok_rows != '' ){
                $sql = "UPDATE {$table_name} SET status_sincronizacion = 3 WHERE id_sincronizacion_registro IN( $ok_rows )";
                $stm = $this->link->query( $sql );
                    if( $logger_id ){
                        $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualizar detalles (jsons)", $sql );
                    }
                    if( $this->link->error ){
                        if( $logger_id ){
                            $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar detalles (jsons)", "{$table_name}", $sql, $this->link->error );
                        }
                        die( "Error al actualizar detalles (jsons) local : {$this->link->error} {$sql}" );
                    }
            }
            $this->link->autocommit( true );
            return 'ok';
        }
    }

?>