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
				$sql = "INSERT INTO vf_clientes_razones_sociales ( /*1*/id_cliente_facturacion, /*2*/rfc, /*3*/razon_social, /*4*/id_tipo_persona,
						/*5*/entrega_cedula_fiscal, /*6*/url_cedula_fiscal, /*7*/calle, /*8*/no_int, /*9*/no_ext, /*10*/colonia, /*11*/del_municipio, 
						/*12*/cp, /*13*/estado, /*14*/pais, /*15*/regimen_fiscal, /*16*/productos_especificos, /*17*/fecha_alta, /*18*/sincronizar )
						VALUES( NULL,  /*1*/'{$costumer['id_cliente_facturacion']}', /*2*/'{$costumer['rfc']}', /*3*/'{$costumer['razon_social']}', 
						/*4*/'{$costumer['id_tipo_persona']}', /*5*/'{$costumer['entrega_cedula_fiscal']}', /*6*/'{$costumer['url_cedula_fiscal']}',
						/*7*/'{$costumer['calle']}', /*8*/'{$costumer['no_int']}', /*9*/'{$costumer['no_ext']}', /*10*/'{$costumer['colonia']}', 
						/*11*/'{$costumer['del_municipio']}', /*12*/'{$costumer['cp']}', /*13*/'{$costumer['estado']}', /*14*/'{$costumer['pais']}', 
						/*15*/'{$costumer['regimen_fiscal']}', /*16*/'{$costumer['productos_especificos']}', /*17*/NOW(), /*18*/1 )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar cliente de facturacion : {$this->link->error}" );
			//obtiene el id insertado
				$costumer_id = $this->link->insert_id;
				$costumer['id_cliente'] = $costumer_id;
				$costumer['folio_unico'] = "CLIENTE_{$costumer_id}";
			//actualiza el folio_unico de la cabecera de cliente
				$sql = "UPDATE vf_clientes_razones_sociales SET folio_unico = '{$costumer['folio_unico']}' WHERE id_cliente = {$costumer['id_cliente']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del cliente : {$this->link->error}" );
			//inserta el registro de sincronizacion del cliente
				$synchronization = $this->insertCostumerSynchronizationRows( $costumer );
			//inserta detalle ( contactos )
				$detail = $costumer['detail'];
				$detail['id_cliente_facturacion'] = $costumer_id;
				$sql = "INSERT INTO vf_clientes_contacto ( /*1*/id_cliente_contacto, /*2*/id_cliente_facturacion, /*3*/nombre, /*4*/telefono, /*5*/celular, /*6*/correo,
						/*7*/uso_cfdi, /*8*/fecha_alta, /*9*/fecha_ultima_actualizacion, /*10*/folio_unico, /*11*/sincronizar )
						VALUES( /*1*/NULL, /*2*/'{$detail['id_cliente_facturacion']}', /*3*/'{$detail['nombre']}', /*4*/'{$detail['telefono']}', /*5*/'{$detail['celular']}', 
							/*6*/'{$detail['correo']}', /*7*/'{$detail['uso_cfdi']}', /*8*/NOW(), /*9*/'0000/00/00', 
							/*10*/'', /*11*/1 )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar contacto del cliente de facturacion : {$this->link->error}" );
		//obtiene el id insertado
				$detail_id = $this->link->insert_id;
				$detail['id_cliente_contacto'] = $detail_id;
				$detail['folio_unico'] = "CONTACTO_{$detail_id}";
			//actualiza el folio_unico de la cabecera de cliente
				$sql = "UPDATE vf_clientes_contacto SET folio_unico = '{$detail['folio_unico']}' WHERE id_cliente_contacto = {$detail_id}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del cliente : {$this->link->error}" );
			//inserta el registro de sincronizacion del cliente
				$synchronization = $this->insertCostumerContactSynchronizationRows( $detail, $costumer['folio_unico'] );
			}
			$this->link->autocommit( true );
			return 'ok';
		}

	/*insercion de registros de sincronizacion clientes para sucursales locales en sistema general*/
		public function insertCostumerSynchronizationRows( $costumer ){
		//consulta razon socialDECLARE store_id INTEGER;
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT 
						NULL,
						-1,
						id_sucursal,
						CONCAT('{',
							'\"table_name\" : \"vf_clientes_razones_sociales\",',
							'\"action_type\" : \"insert\",',
							'\"rfc\" : \"', '{$costumer['rfc']}', '\",',
							'\"razon_social\" : \"', '{$costumer['razon_social']}', '\",',
							'\"id_tipo_persona\" : \"', '{$costumer['id_tipo_persona']}', '\",',
							'\"entrega_cedula_fiscal\" : \"', '{$costumer['entrega_cedula_fiscal']}', '\",',
							'\"url_cedula_fiscal\" : \"', '{$costumer['url_cedula_fiscal']}', '\",',
							'\"calle\" : \"', '{$costumer['calle']}', '\",',
							'\"no_int\" : \"', '{$costumer['no_int']}', '\",',
							'\"no_ext\" : \"', '{$costumer['no_ext']}', '\",',
							'\"colonia\" : \"', '{$costumer['colonia']}', '\",',
							'\"del_municipio\" : \"', '{$costumer['del_municipio']}', '\",',
							'\"cp\" : \"', '{$costumer['cp']}', '\",',
							'\"estado\" : \"', '{$costumer['estado']}', '\",',
							'\"pais\" : \"', '{$costumer['pais']}', '\",',
							'\"regimen_fiscal\" : \"', '{$costumer['regimen_fiscal']}', '\",',
							'\"productos_especificos\" : \"',  '{$costumer['productos_especificos']}', '\",',
							'\"folio_unico\" : \"',  '{$costumer['folio_unico']}', '\",',
							'\"sincronizar\" : \"1\"',
							'}'
						),
						NOW(),
						'insertCostumerSynchronizationRows_facturacion.php',
						1
					FROM sys_sucursales 
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincroniacion del cliente : {$this->link->error}" );
			return 'ok';
		}

	/*insercion de registros de sincronizacio clientes para sucursales locales en sistema general*/
		public function insertCostumerContactSynchronizationRows( $detail, $costumer_unique_folio ){
		//consulta razon socialDECLARE store_id INTEGER;
  	
			$sql = "INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT
						NULL,
						-1,
						id_sucursal,
						CONCAT('{',
							'\"table_name\" : \"vf_clientes_contacto\",',
							'\"action_type\" : \"insert\",',
							'\"id_cliente_facturacion\" : \"', 
							(SELECT 
								id_cliente_facturacion
							FROM vf_clientes_razones_sociales 
							WHERE folio_unico = '{$costumer_unique_folio}' LIMIT 1 ), '\",',
							'\"nombre\" : \"', '{$detail['nombre']}', '\",',
							'\"telefono\" : \"', '{$detail['telefono']}', '\",',
							'\"celular\" : \"', '{$detail['celular']}', '\",',
							'\"correo\" : \"', '{$detail['correo']}', '\",',
							'\"uso_cfdi\" : \"', '{$detail['uso_cfdi']}', '\",',
							'\"fecha_alta\" : \"', '{$detail['fecha_alta']}', '\",',
							'\"fecha_ultima_actualizacion\" : \"', '{$detail['fecha_ultima_actualizacion']}', '\",',
							'\"folio_unico\" : \"', '{$detail['fecha_alta']}', '\",',
							'\"sincronizar\" : \"', 1, '\"',
							'}'
						),
						NOW(),
						'insertCostumerContactSynchronizationRows_facturacion.php',
						1
					FROM sys_sucursales 
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de contacto de cliente : {$this->link->error}" );
			return 'ok';
		}
	}
?>