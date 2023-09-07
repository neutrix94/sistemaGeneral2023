<?php

	class Bill
	{
		private $link;	
		private $store_id;
		private $store_prefix;
		function __construct( $connection, $store_id, $store_prefix ){
			$this->link = $connection;
			$this->store_id = $store_id;
			$this->store_prefix = $store_prefix;
		}

/*Generacion de sincronizacion de clietenes ( temporal )*/
		public function getTemporalCostumer(  ){
			$resp = array();
			$sql = "SELECT 
						id_cliente_facturacion_tmp,
						rfc,
						razon_social,
						id_tipo_persona,
						entrega_cedula_fiscal,
						url_cedula_fiscal,
						calle,
						no_int,
						no_ext,
						colonia,
						del_municipio,
						cp,
						estado,
						pais,
						regimen_fiscal,
						productos_especificos
	          FROM vf_clientes_razones_sociales_tmp
	          WHERE folio_unico IS NULL 
	          OR folio_unico = ''";
	  		$stm = $this->link->query( $sql ) or die( "Error al consultar clientes pendientes de sincronizar : {$sql} {$this->link->error}" );
			
			$this->link->autocommit( false );//comienza transaccion
			while( $row = $stm->fetch_assoc() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_razones_sociales_tmp', 'id_cliente_tmp', 'CL', $row['id_cliente_tmp'] );
				$detail = $this->getTemporalCostumerDetail( $row['id_cliente_tmp'] );
				if( sizeof( $detail ) > 0 ){
					$row['detail'] = $detail;
				}
				$json = json_encode( $row );
				$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio, 
	  			id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
				VALUES( NULL, {$this->store_id}, -1, '{$json}', NOW(), 'envia_cliente.php', 1 )";
				$stm2 = $this->link->query( $sql ) or die( "Error al insertar registro de sincronizacion : {$this->link->error}" );
				$resp[] = $row;
			}
			$this->link->autocommit( true );//autoriza transaccion
			return $resp;
		}

		public function getTemporalCostumerDetail( $costumer_id ){
			$sql = "SELECT 
						id_cliente_contacto_tmp,
						id_cliente_facturacion_tmp,
						nombre,
						telefono,
						celular,
						correo,
						uso_cfdi
		  			FROM vf_clientes_contacto_tmp
		  			WHERE id_cliente_tmp = {$costumer_id}
		  			AND ( folio_unico IS NULL OR folio_unico = '' )";
		  	$stm = $this->link->query( $sql ) or die( "Error al consultar razones sociales pendientes de sincronizar : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_contacto_tmp', 'id_cliente_contacto_tmp', 'CLRZ', $row['id_cliente_contacto_tmp'] );
				//$detail = $this->getTemporalCostumerDetail( $row['id_cliente_tmp'] );
				//if( sizeof( $detail ) > 0 ){
					//$row['detail'] = $detail;
				//}
				//var_dump( $row );
			}
			return $row;
		}

		public function update_unique_code( $table, $keyname, $prefix, $id ){
		//genera el folio unico 
			$sql = "UPDATE {$table} 
						SET folio_unico = '{$this->store_prefix}_{$prefix}_{$id}' 
					WHERE {$keyname} = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar folio unico en {$table} : {$this->link->error}" );
			return "{$this->store_prefix}_{$prefix}_{$id}";
		}
/*Insercion de clientes*/
		public function insertCostumers( $costumers ){
			$this->link->autocommit( false );
			foreach ( $costumers as $key => $costumer ) {
			//inserta cabecera 
				$sql = "INSERT INTO vf_clientes_facturacion_tmp ( id_cliente, nombre, telefono, celular, correo,
						fecha_alta, fecha_ultima_actualizacion, folio_unico, sincronizar )
						VALUES( NULL, '{$costumer['nombre']}', '{$costumer['telefono']}', '{$costumer['celular']}', '{$costumer['correo']}', 
						'{$costumer['fecha_alta']}', '{$costumer['fecha_ultima_actualizacion']}', 1 )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar cliente : {$this->link->error}" );
				$costumer_id = $this->link->insert_id;
			//inserta detalle
				$detail = $costumer['detail'];
				$sql = "INSERT INTO vf_clientes_razones_sociales ( id_cliente_facturacion_detalle, id_cliente, rfc, 
						razon_social, id_tipo_persona, entrega_cedula_fiscal, url_cedula_fiscal, calle, no_int, 
						no_ext, colonia, del_municipio, cp, localidad, referencia, estado, pais, fecha_alta,
						fecha_ultima_actualizacion, folio_unico, sincronizar )
						VALUES( '{$detail['id_cliente_facturacion_detalle']}', '{$costumer_id}', '{$detail['rfc']}', 
						'{$detail['razon_social']}', '{$detail['id_tipo_persona']}', '{$detail['entrega_cedula_fiscal']}', 
						'{$detail['url_cedula_fiscal']}', '{$detail['calle']}', '{$detail['no_int']}', '{$detail['no_ext']}', 
						'{$detail['colonia']}', '{$detail['del_municipio']}', '{$detail['cp']}', '{$detail['localidad']}', 
						'{$detail['referencia']}', '{$detail['estado']}', '{$detail['pais']}', '{$detail['fecha_alta']}',
						'{$detail['fecha_ultima_actualizacion']}', '{$detail['folio_unico']}', 0 )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar cliente : {$this->link->error}" );
			}
			$this->link->autocommit( true );
			return 'ok';
		}
	}
?>