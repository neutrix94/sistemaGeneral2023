<?php

	class salesSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
//hacer jsons de movimientos de almacen
		public function setNewSynchronizationSales( $store_id, $system_store, $origin_store_prefix, $limit ){
			$sql = "CALL buscaVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de ventas : {$this->link->error} {$sql}";
			}
			$sql = "CALL buscaDetalleVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de detalle de ventas : {$this->link->error} {$sql}";
			}
			$sql = "CALL buscaPagosVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de pagos de ventas : {$this->link->error} {$sql}";
			}
			/*$sql = "CALL buscaPagosVentasPendientesDeSincronizar( {$store_id}, {$system_store}, '{$origin_store_prefix}', {$limit} )"; 
			$stm = $this->link->query( $sql );
			if( ! $stm ){
				return "Error al generar registros de ventas : {$this->link->error} {$sql}";
			}*/
			return 'ok';
		}
//hacer / obtener jsons de movimientos de almacen
		public function getSynchronizationSales( $destinity_store_id, $limit ){
			$resp = array();
			$resp['sales'] = array();
			$resp['queries'] = array();
			$sql = "SELECT 
						id_sincronizacion_venta,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '0000-00-00 00:00:00', '' ) AS data,
						tabla
					FROM sys_sincronizacion_ventas
					WHERE tabla = 'ec_pedidos'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$destinity_store_id}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons : {$sql} {$this->link->error}" );
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' 
					&& json_decode($row['data']) != null && json_decode($row['data']) != '' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = str_replace( "\t", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp['sales'], json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				}else{
					die("No es un JSON {$sql} {$row['data']}");
				}
			}

			$sql = "SELECT 
						id_sincronizacion_venta,
						REPLACE( REPLACE( REPLACE( json, '\r\n', ' ' ), '\n', '' ), '0000-00-00 00:00:00', '' ) AS data,
						tabla
					FROM sys_sincronizacion_ventas
					WHERE tabla != 'ec_pedidos'
					AND id_status_sincronizacion IN( 1 )
					AND id_sucursal_destino = {$destinity_store_id}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons : {$sql} {$this->link->error}" );
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' 
					&& json_decode($row['data']) != null && json_decode($row['data']) != '' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = str_replace( "\t", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );
					
					array_push( $resp['queries'], json_decode($row['data']) );//decodifica el JSON
					$movements_counter ++;
				}else{
					die("No es un JSON {$row['data']}");
				}
			}

			//var_dump( $resp );
			return $resp;
		}
//actualizacion de registros de sincronizacion
		public function updateSaleSynchronization( $rows, $petition_unique_folio, $status = 3 ){
			$sql = "";
				$sql = "UPDATE sys_sincronizacion_ventas 
	              SET id_status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE registro_llave IN( {$rows} )";
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$this->link->error} {$sql}" );	
		}
//inserción de movimientos
		public function insertSales( $data ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$sales = $data["sales"];
			$queries = $data["queries"];
			$updates = array();
			$this->link->autocommit( false );
			foreach ($sales as $key => $sale) {
				$ok = true;
			//inserta cabecera
				$sql = "INSERT INTO ec_pedidos ( folio_nv, id_cliente, fecha_alta, subtotal, total, pagado, 
					id_sucursal, id_usuario, descuento, folio_abono, correo, facebook, ultima_sincronizacion, 
					ultima_modificacion, tipo_pedido, id_status_agrupacion, id_cajero, id_devoluciones, 
					venta_validada, folio_unico, id_sesion_caja, tipo_sistema )
				VALUES ( '{$sale['folio_nv']}', {$sale['id_cliente']}, '{$sale['fecha_alta']}', '{$sale['subtotal']}', '{$sale['total']}', '{$sale['pagado']}', 
					'{$sale['id_sucursal']}', '{$sale['id_usuario']}', '{$sale['descuento']}', '{$sale['folio_abono']}', '{$sale['correo']}', '{$sale['facebook']}', '{$sale['ultima_sincronizacion']}', 
					'{$sale['ultima_modificacion']}', '{$sale['tipo_pedido']}', '{$sale['id_status_agrupacion']}', '{$sale['id_cajero']}', '{$sale['id_devoluciones']}', 
					'{$sale['venta_validada']}', '{$sale['folio_unico']}', {$sale['id_sesion_caja']}, '{$sale['tipo_sistema']}' )";

				$stm_head = $this->link->query( $sql )or die( "Error al sincronizar cabecera de venta : {$sql} {$this->link->error}" );;
				if( ! $stm_head ){
					return array( "error"=>"Error al insertar cabecera de venta : {$this->link->error} {$sql}");
					$ok = false;
				}
				$sql = "SELECT LAST_INSERT_ID() AS last_id";
				$stm = $this->link->query( $sql ) or die( "Error al recuperar el id insertado : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$sale_id = $row['last_id'];
			//inserta detalle(s) 
				$sale_detail = $sale['sale_detail'];
				foreach ($sale_detail as $key2 => $detail) {
					if( $ok == true ){
						$sql = "INSERT INTO ec_pedidos_detalle ( id_pedido, id_producto, cantidad, precio, monto, 
							cantidad_surtida, descuento, es_externo, id_precio, folio_unico ) 
						VALUES ( '{$sale_id}', '{$detail['id_producto']}', '{$detail['cantidad']}', '{$detail['precio']}', '{$detail['monto']}', 
							'{$detail['cantidad_surtida']}', '{$detail['descuento']}', '{$detail['es_externo']}', '{$detail['id_precio']}', '{$detail['folio_unico']}' )"; 
						$stm = $this->link->query( $sql ) or die( "Error al insertar detalle de venta : {$sql} {$this->link->error}");
						if( ! $stm ){
							return array( "error"=>"Error al insertar detalle de venta : {$this->link->error}");
						  $ok = false;
						}
					}
				}
			//iniserta pago(s) 
				$sale_payments = $sale['sale_payments'];
				foreach ($sale_payments as $key2 => $payment) {
					if( $ok == true ){
						$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto,
						referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc, exportado, es_externo,
						id_cajero, folio_unico, id_sesion_caja) 
						VALUES ( '{$sale_id}', '{$payment['id_tipo_pago']}', '{$payment['fecha']}', '{$payment['hora']}', '{$payment['monto']}',
						'{$payment['referencia']}', '{$payment['id_moneda']}', '{$payment['tipo_cambio']}', '{$payment['id_nota_credito']}', 
						'{$payment['id_cxc']}', '{$payment['exportado']}', '{$payment['es_externo']}',
						'{$payment['id_cajero']}', '{$payment['folio_unico']}', {$payment['id_sesion_caja']} )"; 
						$stm = $this->link->query( $sql ) or die( "Error al insertar pago de venta : {$sql} {$this->link->error}" );
						if( ! $stm ){
							return array( "error"=>"Error al insertar pago de venta : {$sql} {$this->link->error}");
						  $ok = false;
						}
					}
				}
				if( $ok == true ){
					$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
					$resp["tmp_ok"] .= ( $resp["tmp_ok"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
				}else{
					$resp["error_rows"] .= ( $resp["error_rows"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
					$resp["tmp_no"] .= ( $resp["tmp_no"] == '' ? '' : ',' ) . "'{$sale['folio_unico']}'";
				}
			}

	//sincroniza los registros de sincronizacion
			foreach ($queries as $key => $row) {
				//$tmp = json_decode($row);
				//echo $tmp['action_type'];
				$ok = true;
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
				$sql = "";
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

						$sql = str_replace( "'(", "(", $sql );
						$sql = str_replace( ")'", ")", $sql );
						
						$stm = $this->link->query( $sql ) or die( "Error al ejecutar consuta adicional : {$sql} {$this->link->error}" );
						if( ! $stm ){
							return array( "error"=>"Error al insertar pago de venta : {$sql} {$this->link->error}");
						  $ok = false;
						}else{
							$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['folio_unico']}'";

						}
					break;
					
					default:
						die( "JSON incorrecto : {$row['action_type']}" );
					break;
				}
			}
	//
		    $this->link->autocommit( true );
			return $resp;
		}
	}
?>