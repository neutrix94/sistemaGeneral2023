<?php
    class RowsVerification{
		function __construct( $connection ){
			$this->link = $connection;
		}

        public function insertVerificationLog( $table, $unique_folio, $json_detail ){
            $sql = "INSERT INTO sys_sincronizacion_comprobaciones_log ( tabla, folio_unico_peticion, json_comprobacion, fecha_alta ) 
                        VALUES( '{$table}', '{$unique_folio}', '{$json_detail}', NOW() )";
            $stm = $this->link->query( $sql ) or die( "Error al insertar log de comprobacion : {$sql} : {$this->link->error}" );
            return 'ok';
        }

        public function getPendingWarehouseMovement( $origin_store_id, $destinity_store_id ){
            $resp = array();
            $pending_rows = array();
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
                    WHERE tabla = 'sys_sincronizacion_movimientos_almacen'
                    AND id_sucursal_origen = {$origin_store_id}
                    AND id_sucursal_destino = {$destinity_store_id}
                    AND hora_envio IS NOT NULL
                    AND( hora_llegada_destino IS NULL
                    OR hora_llegada_respuesta IS NULL
                    OR hora_finalizacion IS NULL )";
            $stm = $this->link->query( $sql ) or die( "Error al consultar las peticiones pendientes de alguna respuesta : {$sql} : {$this->link->error}" );
            $petition = $stm->fetch_assoc();
            $resp['petition'] = $petition;
            //$resp['petition_id'] = $petition['petition_id'];
            //$resp['unique_folio'] = $petition['unique_folio'];
            $sql = "SELECT 
                        json,
                        tabla,
                        registro_llave
                    FROM sys_sincronizacion_movimientos_almacen
                    WHERE folio_unico_peticion = '{$petition['unique_folio']}'/*
                    ANd id_status_sincronizacion = 2*/";
            $stm_2 = $this->link->query( $sql ) or die( "Error al consultar detalle de json : {$sql} : {$this->link->error}" );
            $resp['verification'] = ( $stm->num_rows > 0 ? true : false );
            while( $detail = $stm_2->fetch_assoc() ){
                //echo 'here';
                $pending_rows[] = $detail;
            }
            $resp['rows'] = $pending_rows;
            $pending_rows_string = json_encode( $pending_rows );
        //inserta el log
            $log = $this->insertVerificationLog( 'sys_sincronizacion_movimientos_almacen', $petition['unique_folio'], $pending_rows_string );
            if( $log != 'ok' ){
                return false;
            }
            return $resp;
        }
        
        public function validateIfExistsPetitionLog( $petition_log ){
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
            $stm = $this->link->query( $sql ) or die( "Error al consultar si existe la peticion en el destino : {$sql} : {$this->link->error}" );
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
                        VALUES ( {$petition_log['origin_store']}, {$petition_log['destinity_store']}, '{$petition_log['table_name']}', {$petition_log['petition_type']}, {$petition_log['datetime_start']}, 
                        {$petition_log['datetime_send']}, NOW(), NOW(), '', NOW(), NOW(), {$petition_log['unique_folio']} )";
                $stm = $this->link->query( $sql ) or die( "Error al insertar el registro de sincronizacion en el destino : {$sql} : {$this->link->error}" );
                $resp = $petition_log;
            //consulta la hor actual
                $sql = "SELECT NOW() AS current_date_time";
                $stm_2 = $this->link->query( $sql ) or die( "Error al consultar la fecha y hora : {$sql} : {$this->link->error}" );
                $row = $stm->fetch_assoc();
                $resp['datetime_destinity'] = $row['current_date_time'];
                $resp['datetime_send_response'] = $row['current_date_time'];
                $resp['response_content'] = $row['current_date_time'];
                $resp['datetime_response'] = $row['current_date_time'];
                $resp['datetime_end'] = $row['current_date_time'];
            }
            return $resp;
        }

        public function warehouseMovementsValidation( $movements ){
            $resp = array();
            $resp['ok_rows'] = "";
            $resp['error_rows'] = "";
            foreach ($movements as $key => $movement_) {
                $this->link->autocommit( false );
                $movement = json_decode( $movement_['json'] );
                //var_dump($movement);
            //consulta si la cabecera existe
                $sql = "SELECT id_movimiento_almacen AS movement_id FROM ec_movimiento_almacen WHERE folio_unico = '{$movement_['registro_llave']}'";
                $stm = $this->link->query( $sql );// or die( "Error al consultar si ya existe la cabecera de movimiento de almacen en la comprobacion : {$sql} : {$this->link->error}" );
                $movement_header_id = 0;
                if( $stm->num_rows <= 0 ){//no existe
                    //se inserta cabecera de movimiento de almacen por procedure
				    $sql = "CALL spMovimientoAlmacen_inserta( {$movement->id_usuario}, '{$movement->observaciones} \nInsertado desde API por sincronización', {$movement->id_sucursal},
                    {$movement->id_almacen}, {$movement->id_tipo_movimiento}, -1, -1, '{$movement->id_maquila}', {$movement->id_transferencia}, {$movement->id_pantalla}, '{$movement->folio_unico}' )";
                    $stm = $this->link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen : {$sql} : {$this->link->error}" );
                //recupera el id insertado
                    $sql = "SELECT MAX( id_movimiento_almacen ) AS movement_id FROM ec_movimiento_almacen";
                    $stm_3 = $this->link->query( $sql ) or die( "Error al recuperar el id de movimiento de almacen insertado por procedure : {$sql} : {$this->link->error}" );
                    $row = $stm_3->fetch_assoc();
                    $movement_header_id = $row['movement_id'];
                }else{
                    $row = $stm->fetch_assoc();
                    $movement_header_id = $row['movement_id'];
                }
            //inserta el detalle
                $movement_detail = $movement->movimiento_detail;
                foreach ($movement_detail as $key2 => $detail) {
                //comprueba si existe el folio unico del detalle
                    $sql = "SELECT id_movimiento_almacen_detalle FROM ec_movimiento_detalle WHERE folio_unico = '{$detail->folio_unico}'";
                    $stm_4 = $this->link->query( $sql ) or die( "Error al consultar si existe el detalle de movimiento de almacen : {$sql} : {$this->link->error}" );
                    if( $stm_4->num_rows <= 0 ){
                        $sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_header_id}, {$detail->id_producto}, {$detail->cantidad}, {$detail->cantidad_surtida},
                                    {$detail->id_pedido_detalle}, {$detail->id_oc_detalle}, {$detail->id_proveedor_producto}, {$movement->id_pantalla}, '{$detail->folio_unico}' )";
                        $stm_5 = $this->link->query( $sql ) or die( "Error al insertar detalle de movimientos de almacen : {$sql} : {$this->link->error}" );
                    }
                }
                $this->link->autocommit( true );
                $resp['ok_rows'] .= ( $resp['ok_rows'] == '' ? '' : '|' );
                $resp['ok_rows'] .= ( $movement_['registro_llave'] );
            }
            //var_dump( $resp );
            //die( $resp['ok_rows'] );
            return $resp;
        }

        public function updateLogAndJsonsRows( $log_response, $rows_response ){
            $this->link->autocommit( false );
            //die( "here1" );
            $sql = "UPDATE sys_sincronizacion_peticion SET hora_llegada_destino = IF( hora_llegada_destino IS NULL OR hora_llegada_destino = '', '{$log_response->datetime_destinity}', hora_llegada_destino ),
                        hora_respuesta = IF( hora_respuesta IS NULL OR hora_respuesta = '', '{$log_response->datetime_send_response}', hora_respuesta ),
                        contenido_respuesta = IF( contenido_respuesta IS NULL OR contenido_respuesta = '', '{$log_response->response_content}', contenido_respuesta ),
                        hora_llegada_respuesta = IF( hora_llegada_respuesta IS NULL OR hora_llegada_respuesta = '', NOW(), hora_llegada_respuesta ),
                        hora_llegada_respuesta = IF( hora_llegada_respuesta IS NULL OR hora_llegada_respuesta = '', NOW(), hora_llegada_respuesta ),
                        hora_finalizacion = NOW()
                    WHERE folio_unico = '{$log_response->unique_folio}'";//die( $sql );
            $stm = $this->link->query( $sql ) or die( "Error al actualizar la peticion (comprobación) local : {$sql} : {$this->link->error}" );
        //actualiza los registros correctos
            $ok_rows = $rows_response->ok_rows;
            $uniques_folios = '';
            $ok_rows = explode( '|', $ok_rows );//= str_replace( '|', ',', $ok_rows );
            foreach ($ok_rows as $key => $row) {
                $uniques_folios .= ( $uniques_folios == '' ? '' : ',' );
                $uniques_folios .= "'{$row}'";
            }
            $sql = "UPDATE sys_sincronizacion_movimientos_almacen SET id_status_sincronizacion = 3 WHERE registro_llave IN( $uniques_folios )";
            $stm = $this->link->query( $sql ) or die( "Error al actualizar detalles (jsons) local : {$sql} : {$this->link->error}" );
            $this->link->autocommit( true );
            return 'ok';
        }
    }




        /*public function insertVerification( $table, $primary_key, $primary_key_value, $secondary_key = null, $secondary_key_value = null, $third_key = null, $third_key_value = null ){
            $sql = "SELECT COUNT( * ) AS counter FROM $table WHERE $primary_key = '{$primary_key_value}'";
            if( $secondary_key != null ){
                $sql .= " AND {$secondary_key} = {$secondary_key_value}";
            }
            if( $third_key != null ){
                $sql .= " AND {$third_key} = {$third_key_value}";
            }
            $stm = $this->link->query( $sql ) or die( "Error al comprobar si el registro existe en insertVerification : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            if( $row['counter'] == 0 ){
                return false;//no existe
            }
            return true;//existe
        }

        public function updateVerification( $row ){
            
        }

        public function deleteVerification( $table, $primary_key, $primary_key_value, $secondary_key = null, $secondary_key_value = null, $third_key = null, $third_key_value = null ){
            $sql = "SELECT COUNT( * ) AS counter FROM $table WHERE $primary_key = '{$primary_key_value}'";
            if( $secondary_key != null ){
                $sql .= " AND {$secondary_key} = {$secondary_key_value}";
            }
            if( $third_key != null ){
                $sql .= " AND {$third_key} = {$third_key_value}";
            }
            $stm = $this->link->query( $sql ) or die( "Error al comprobar si el registro existe en deleteVerification : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            if( $row['counter'] == 0 ){
                return false;//no existe
            }
            return true;//existe
        }

        public function insertWarehouseMovementVerification( $unique_folio ){
            $sql = "SELECT COUNT( * ) AS counter FROM ec_movimiento_almacen WHERE folio_unico = '{$unique_folio}'";
            $stm = $this->link->query( $sql ) or die( "Error al comprobar si el registro de movimiento de almacen existe en insertWarehouseMovementVerification : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            if( $row['counter'] == 0 ){
                return false;//no existe
            }
            return true;//existe
        }*/

?>