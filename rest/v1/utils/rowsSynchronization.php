<?php
/* ( 2024-08-08 ) Se agrega condicion para actualizar detalles de movimientos en libreria de registros de sincronizacion*/
	class rowsSynchronization
	{
		private $link;
		private $LOGGER;
		function __construct( $connection, $Logger = null ){
			$this->link = $connection;
			$this->LOGGER = $Logger;
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationRows( $system_store, $destinity_store, $limit, $table, $petition_unique_folio, $logger_id = false ){
            $log_steep_id = null;
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_registro AS synchronization_row_id,
						REPLACE( datos_json, '\r\n', ' ' ) AS data
					FROM {$table}
					WHERE status_sincronizacion IN( 1 )
					AND sucursal_de_cambio = {$system_store}
					AND id_sucursal_destino = {$destinity_store}
					AND datos_json != '' AND datos_json IS NOT NULL
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql );
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Consulta los datos de jsons de registros de sincronizacion en {$table}", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al consultar los datos de jsons de registros de sincronizacion en {$table}", "{$table}", $sql, $this->link->error );
					}
					die( "Error al consultar los datos de jsons de registros de sincronizacion en {$table} : {$this->link->error} {$sql}" );
				}
			// or die( "Error al consultar los datos de jsons de registros de sincronizacion on {$table} : {$this->link->error} : {$sql}" );
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

					$row['data'] = str_replace('"}', '", "synchronization_row_id" : "' . $row['synchronization_row_id'] . '" }', $row['data'] );
					
					array_push( $resp, json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				//actualiza al status 2 los registros que va a enviar
					$sql = "UPDATE {$table} SET status_sincronizacion = 2, folio_unico_peticion = '{$petition_unique_folio}' WHERE id_sincronizacion_registro = {$row['synchronization_row_id']}";
					$stm_2 = $this->link->query( $sql );
						if( $logger_id ){
							$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza registro de sincronizacion {$table} a status 2", $sql );
						}
						if( $this->link->error ){
							if( $logger_id ){
								$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al poner registro de sincronizacion {$table} en status 2", "{$table}", $sql, $this->link->error );
							}
							die( "Error al poner registro de sincronizacion {$table} en status 2 : {$this->link->error} {$sql}" );
						}
				}
			}
			return $resp;
		}
//inserción de movimientos
		public function insertRows( $rows, $logger_id = false ){
            $log_steep_id = null;
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';
			$queries = array();
			foreach ($rows as $key => $row) {
				$sql = "";
				$condition = "";
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
						array_push( $queries, array( "query"=>$sql, "row_id"=>$row['synchronization_row_id'] ) );
						if( $row['table_name'] != 'ec_pedidos' && $row['table_name'] != 'ec_pedidos_detalle' ){
							$sql = "UPDATE {$row['table_name']} SET sincronizar = 0 {$condition}";
							array_push( $queries, array( "query"=>$sql, "row_id"=>"n/a" ) );
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
						array_push( $queries, array( "query"=>$sql, "row_id"=>$row['synchronization_row_id'] ) );
					break;
					case 'delete' :
						$sql = "DELETE FROM {$row['table_name']} {$condition}";
						if( $row['table_name'] == 'ec_movimiento_detalle' ){
						//procedure aqui
							$aux = "SELECT id_movimiento_almacen_detalle AS detail_id FROM ec_movimiento_detalle WHERE folio_unico = '{$row['primary_key_value']}'";
							$aux_stm = $this->link->query( $aux );// or die( "Error al consultar id de detalle mov almacen :" );
							$aux_row = $aux_stm->fetch_assoc();
							$sql = "CALL spMovimientoAlmacenDetalle_elimina( {$aux_row['detail_id']}, NULL );";
						}
						array_push( $queries, array( "query"=>$sql, "row_id"=>$row['synchronization_row_id'] ) );
					break;

					case 'sql_instruction' : 
						$sql = $row['sql'];
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
						array_push( $queries, array( "query"=>$sql, "row_id"=>$row['synchronization_row_id'] ) );
					break;
					
					default:
					break;
				}
			}
		//ejecuta las consultas
			foreach ($queries as $key2 => $query_) {
				$ok = true;
				$this->link->autocommit(false);
				$query = str_replace( "'(", "(", $query_['query'] );
				$query = str_replace( ")'", ")", $query );
				$stm = $this->link->query( $query );
					if( $logger_id ){
						$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Ejecuta consulta SQL", $query );
					}
					if( $this->link->error ){
						$ok = false;
						if( $logger_id ){
							$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al ejecutar consulta ", "sys_sincronizacion_registros", $query, $this->link->error );
						}
					}
				if( $ok == true && $query_['row_id'] != 'n/a' ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$query_['row_id']}'";
					$this->link->commit();
				}else if( ! $ok && $query_['row_id'] != 'n/a' ){
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$query_['row_id']}'";
					$this->link->rollback();
				}
			}
			return $resp;
		}

//actualizacion de registros de sincronizacion
		public function updateRowSynchronization( $rows, $petition_unique_folio, $table, $status = null, $sum = false, $logger_id = false ){
            $log_steep_id = null;
			$sql = "";
			if( $status != null ){//actualiza status y folio unico de peticion
				$sql = "UPDATE {$table} 
	              SET status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE id_sincronizacion_registro IN( {$rows} )";
	   	 		$stm = $this->link->query( $sql );
	   		}
				if( $logger_id ){
					$log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Actualiza JSON de sincronizacion a status {$status}", $sql );
				}
				if( $this->link->error ){
					if( $logger_id ){
						$this->LOGGER->insertErrorSteepRow( $log_steep_id, "Error al actualizar JSON de sincronizacion {$table} en status {$status}", "{$table}", $sql, $this->link->error );
					}
					die( "Error al actualizar JSON de sincronizacion {$table} en status {$status} : {$this->link->error} {$sql}" );
				}
		}
	}
?>