<?php
    class returnRowsVerification{
        private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = false ){
			$this->link = $connection;
            $this->LOGGER = $Logger;
		}



        public function getPendingValidationReturns( $origin_store_id, $destinity_store_id, $logger_id = false ){
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
                    LEFT JOIN sys_sincronizacion_devoluciones sd
                    ON sd.folio_unico_peticion = sp.folio_unico
                    WHERE sp.tabla = 'sys_sincronizacion_devoluciones'
                    AND sp.id_sucursal_origen = {$origin_store_id}
                    AND sp.id_sucursal_destino = {$destinity_store_id}
                    AND sp.hora_envio IS NOT NULL
                    AND( sp.hora_llegada_destino IS NULL
                    OR sp.hora_llegada_respuesta IS NULL
                    OR sp.hora_finalizacion IS NULL )
                    OR sd.id_status_sincronizacion = 2
                    GROUP BY sp.id_peticion";
            $stm = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta peticiones pendientes de alguna respuesta", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar las peticiones pendientes de alguna respuesta", 'sys_sincronizacion_peticion', $sql, $this->link->error );
                    }
                    die( "Error al consultar las peticiones pendientes de alguna respuesta : {$this->link->error} {$sql}" );
                }
            $petition = $stm->fetch_assoc();
            $resp['petition'] = $petition;
            //$resp['petition_id'] = $petition['petition_id'];
            //$resp['unique_folio'] = $petition['unique_folio'];
            $sql = "SELECT 
                        json,
                        tabla,
                        registro_llave
                    FROM sys_sincronizacion_devoluciones
                    WHERE folio_unico_peticion = '{$petition['unique_folio']}'
                    AND id_status_sincronizacion = 2";
            $stm_2 = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta detalle de json", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar detalle de json", 'sys_sincronizacion_devoluciones', $sql, $this->link->error );
                    }
                    die( "Error al consultar detalle de json : {$this->link->error} {$sql}" );
                }
            $resp['verification'] = ( $stm->num_rows > 0 ? true : false );
            while( $detail = $stm_2->fetch_assoc() ){
                //echo 'here';
                $pending_rows[] = $detail;
            }
            $resp['rows'] = $pending_rows;
            $pending_rows_string = json_encode( $pending_rows );
        //inserta el log
            $log = $this->insertVerificationLog( 'sys_sincronizacion_movimientos_proveedor_producto', $petition['unique_folio'], $pending_rows_string, $logger_id );
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

        public function returnsValidation( $returns, $logger_id = false ){
            $log_steep_id = null;
            $resp = array();
            $resp['ok_rows'] = "";
            $resp['error_rows'] = "";

            foreach ($returns as $key => $return_) {
                $this->link->autocommit( false );
                $return = json_decode( $return_['json'] );
                if (is_object($return) && get_class($return) === 'stdClass') {
                    $return = json_decode(json_encode($return), true);
                }
                //var_dump($movement);
            //consulta si la cabecera existe
                $sql = "SELECT id_devolucion AS return_id FROM ec_devolucion WHERE folio_unico = '{$return_['registro_llave']}'";
                $stm = $this->link->query( $sql );
                    if( $logger_id ){
                        $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta si ya existe la cabecera de movimiento de almacen en la comprobacion", $sql );
                    }
                    if( $this->link->error ){
                        if( $logger_id ){
                            $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar si ya existe la cabecera de movimiento de almacen en la comprobacion", 'ec_movimiento_almacen', $sql, $this->link->error );
                        }
                        die( "Error al consultar si ya existe la cabecera de movimiento de almacen en la comprobacion : {$this->link->error} {$sql}" );
                    }
                $return_header_id = 0;
                if( $stm->num_rows <= 0 ){//no existe
                //se inserta la cabecera de la devolucion
                    $sql = "INSERT INTO ec_devolucion ( id_usuario, id_sucursal, fecha, hora, id_pedido, 
                    folio, es_externo, status, observaciones, tipo_sistema, id_status_agrupacion, folio_unico )
                    VALUES ( '{$return['id_usuario']}', '{$return['id_sucursal']}', '{$return['fecha']}', 
                        '{$return['hora']}', {$return['id_pedido']}, '{$return['folio']}', '{$return['es_externo']}', 
                        '{$return['status']}', '{$return['observaciones']}', '{$return['tipo_sistema']}', 
                        '{$return['id_status_agrupacion']}', '{$return['folio_unico']}' )";
                        $stm = $this->link->query( $sql );
                        if( $logger_id ){
                            $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta cabecera de devolución", $sql );
                        }
                        if( $this->link->error ){
                            if( $logger_id ){
                                $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar cabecera de devolución", 'ec_devolucion', $sql, $this->link->error );
                            }
                            die( "Error al insertar cabecera de devolución : {$this->link->error} {$sql}" );
                        }
                //recupera el id insertado
                    $sql = "SELECT MAX( id_devolucion ) AS return_id FROM ec_devolucion";
                    $stm_3 = $this->link->query( $sql ) or die( "Error al recuperar el id de cabecera de devolución insertada : {$sql} : {$this->link->error}" );
                    $row = $stm_3->fetch_assoc();
                    $return_header_id = $row['return_id'];
                }else{
                    $row = $stm->fetch_assoc();
                    $return_header_id = $row['return_id'];
                }
            //inserta el detalle
				$return_detail = $return['return_detail'];
                foreach ($return_detail as $key2 => $detail) {
                //comprueba si existe el folio unico del detalle
                    $sql = "SELECT id_devolucion_detalle FROM ec_devolucion_detalle WHERE folio_unico = '{$detail["folio_unico"]}'";
                    $stm_4 = $this->link->query( $sql ) or die( "Error al consultar si existe el detalle de devolución : {$sql} : {$this->link->error}" );
                    if( $stm_4->num_rows <= 0 ){
                        
						$sql = "INSERT INTO ec_devolucion_detalle ( id_devolucion, id_producto, id_proveedor_producto, 
						cantidad, folio_unico, id_pedido_detalle ) 
						VALUES ( '{$return_header_id}', '{$detail['id_producto']}', '{$detail['id_proveedor_producto']}', '{$detail['cantidad']}',
							'{$detail['folio_unico']}', "; 
						$sql .= ( $detail['id_pedido_detalle'] != null ? "'{$detail['id_pedido_detalle']}'" : "'0'" ) . " )";
						
						$sql = str_replace( "'(", "(", $sql );
						$sql = str_replace( ")'", ")", $sql );
                        $stm_5 = $this->link->query( $sql );
                            if( $logger_id ){
                                $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Inserta detalle de devolución", $sql );
                            }
                            if( $this->link->error ){
                                if( $logger_id ){
                                    $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al insertar detalle de devolución", 'ec_movimiento_detalle', $sql, $this->link->error );
                                }
                                die( "Error al insertar detalle de devolución : {$this->link->error} {$sql}" );
                            }
                    }
                }
                $this->link->autocommit( true );
                $resp['ok_rows'] .= ( $resp['ok_rows'] == '' ? '' : '|' );
                $resp['ok_rows'] .= ( $return_['registro_llave'] );
            }
            return $resp;
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

        public function updateLogAndJsonsRows( $log_response, $rows_response, $logger_id = false ){
            $log_steep_id = null;
            $this->link->autocommit( false );
            //die( "here1" );
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
            $uniques_folios = '';
            $ok_rows = explode( '|', $ok_rows );//= str_replace( '|', ',', $ok_rows );
            foreach ($ok_rows as $key => $row) {
                $uniques_folios .= ( $uniques_folios == '' ? '' : ',' );
                $uniques_folios .= "'{$row}'";
            }
            $sql = "UPDATE sys_sincronizacion_devoluciones SET id_status_sincronizacion = 3 WHERE registro_llave IN( $uniques_folios )";
            $stm = $this->link->query( $sql );
                if( $logger_id ){
                    $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualizar detalles (jsons)", $sql );
                }
                if( $this->link->error ){
                    if( $logger_id ){
                        $this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar detalles (jsons)", 'sys_sincronizacion_devoluciones', $sql, $this->link->error );
                    }
                    die( "Error al actualizar detalles (jsons) local : {$this->link->error} {$sql}" );
                }
            $this->link->autocommit( true );
            return 'ok';
        }
    }
?>