<?php
//die( "here" );
	/*include( '../../../conexionMysqli.php' );
	$bill = new Bill( $link );
	echo $bill->insertBillSystemCostumerSynchronization();*/
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
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_razones_sociales_tmp', 'id_cliente_facturacion_tmp', 'CL', $row['id_cliente_facturacion_tmp'] );
				$detail = $this->getTemporalCostumerDetail( $row['id_cliente_facturacion_tmp'] );
				if( sizeof( $detail ) > 0 ){
					$row['detail'] = $detail;
				}
				$json = json_encode( $row );
			//die( $json );
				$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio, 
	  			id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
				VALUES( NULL, {$this->store_id}, -1, '{$json}', NOW(), 'envia_cliente.php', 1 )";
				//die( $sql );
				$stm2 = $this->link->query( $sql ) or die( "Error al insertar registro de sincronizacion : {$this->link->error}" );
				$resp[] = $row;
			}
			$this->link->autocommit( true );//autoriza transaccion
			return $resp;
		}

		public function getTemporalCostumerDetail( $costumer_id ){
			$row = array();
			$sql = "SELECT 
						id_cliente_contacto_tmp,
						id_cliente_facturacion_tmp,
						nombre,
						telefono,
						celular,
						correo,
						uso_cfdi
		  			FROM vf_clientes_contacto_tmp
		  			WHERE id_cliente_facturacion_tmp = {$costumer_id}
		  			AND ( folio_unico IS NULL OR folio_unico = '' )"; //die( $sql );
		  	$stm = $this->link->query( $sql ) or die( "Error al consultar razones sociales pendientes de sincronizar : {$sql} {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_contacto_tmp', 'id_cliente_contacto_tmp', 'CLRZ', $row['id_cliente_contacto_tmp'] );
				//die( 'HERE : ' . $row['folio_unico'] );
				//$detail = $this->getTemporalCostumerDetail( $row['id_cliente_tmp'] );
				//if( sizeof( $detail ) > 0 ){
					//$row['detail'] = $detail;
				//}
				//var_dump( $row );
				return $row;
			}
			//var_dump( $row );
		}

		public function update_unique_code( $table, $keyname, $prefix, $id ){
		//genera el folio unico 
			$sql = "UPDATE {$table} 
						SET folio_unico = '{$this->store_prefix}_{$prefix}_{$id}' 
					WHERE {$keyname} = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar folio unico en {$table} : {$sql} {$this->link->error}" );
			return "{$this->store_prefix}_{$prefix}_{$id}";
		}

/*Insercion de clientes en linea*/
		public function insertCostumers( $costumers ){
			$rows = "";
			$this->link->autocommit( false );
			foreach ( $costumers as $key => $costumer ) {
				//consulta si el cliente ya existe
				$sql = "SELECT id_cliente_facturacion, folio_unico FROM vf_clientes_razones_sociales WHERE rfc = '{$costumer['rfc']}'";
				$stm_check = $this->link->query( $sql ) or die( "Error al consultar si el cliente existe : {$this->link->error}" );
				if( $stm_check->num_rows > 0 ){
					$row_costumer = $stm_check->fetch_assoc();
					$costumer['folio_unico'] = $row_costumer['folio_unico'];//valor de folio unico
					$sql = "UPDATE vf_clientes_razones_sociales
								SET /*1*/id_cliente_facturacion, 
								/*2*/rfc = '{$costumer['rfc']}', 
								/*3*/razon_social = '{$costumer['razon_social']}', 
								/*4*/id_tipo_persona = '{$costumer['id_tipo_persona']}',
								/*5*/entrega_cedula_fiscal = '{$costumer['entrega_cedula_fiscal']}', 
								/*6*/url_cedula_fiscal = '{$costumer['url_cedula_fiscal']}', 
								/*7*/calle = '{$costumer['calle']}', 
								/*8*/no_int = '{$costumer['no_int']}', 
								/*9*/no_ext = '{$costumer['no_ext']}', 
								/*10*/colonia = '{$costumer['colonia']}', 
								/*11*/del_municipio = '{$costumer['del_municipio']}', 
								/*12*/cp = '{$costumer['cp']}', 
								/*13*/estado = '{$costumer['estado']}', 
								/*14*/pais = '{$costumer['pais']}', 
								/*15*/regimen_fiscal = '{$costumer['regimen_fiscal']}', 
								/*16*/productos_especificos = '{$costumer['productos_especificos']}', 
								/*17*/fecha_alta = NOW(), 
								/*18*/sincronizar = 1
							WHERE id_cliente_facturacion = {$row_costumer['id_cliente_facturacion']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar cliente de facturacion : {$sql} {$this->link->error}" );
				//inserta el registro de sincronizacion del cliente
					$synchronization = $this->insertCostumerSynchronizationRows( $costumer, 'update' );
				}else{
				//inserta cabecera 
					$sql = "INSERT INTO vf_clientes_razones_sociales ( /*1*/id_cliente_facturacion, /*2*/rfc, /*3*/razon_social, /*4*/id_tipo_persona,
							/*5*/entrega_cedula_fiscal, /*6*/url_cedula_fiscal, /*7*/calle, /*8*/no_int, /*9*/no_ext, /*10*/colonia, /*11*/del_municipio, 
							/*12*/cp, /*13*/estado, /*14*/pais, /*15*/regimen_fiscal, /*16*/productos_especificos, /*17*/fecha_alta, /*18*/sincronizar )
							VALUES( /*1*/NULL, /*2*/'{$costumer['rfc']}', /*3*/'{$costumer['razon_social']}', 
							/*4*/'{$costumer['id_tipo_persona']}', /*5*/'{$costumer['entrega_cedula_fiscal']}', /*6*/'{$costumer['url_cedula_fiscal']}',
							/*7*/'{$costumer['calle']}', /*8*/'{$costumer['no_int']}', /*9*/'{$costumer['no_ext']}', /*10*/'{$costumer['colonia']}', 
							/*11*/'{$costumer['del_municipio']}', /*12*/'{$costumer['cp']}', /*13*/'{$costumer['estado']}', /*14*/'{$costumer['pais']}', 
							/*15*/'{$costumer['regimen_fiscal']}', /*16*/'{$costumer['productos_especificos']}', /*17*/NOW(), /*18*/1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar cliente de facturacion : {$sql} {$this->link->error}" );
				//obtiene el id insertado
					$costumer_id = $this->link->insert_id;
					$costumer['id_cliente'] = $costumer_id;
					$costumer['folio_unico'] = "CLIENTE_{$costumer_id}";
				//actualiza el folio_unico de la cabecera de cliente
					$sql = "UPDATE vf_clientes_razones_sociales 
								SET folio_unico = '{$costumer['folio_unico']}' 
							WHERE id_cliente_facturacion = {$costumer['id_cliente']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del cliente : {$this->link->error}" );
				
				//inserta el registro de sincronizacion del cliente
					$synchronization = $this->insertCostumerSynchronizationRows( $costumer, 'insert' );
				}
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
				$sql = "UPDATE vf_clientes_contacto SET folio_unico = '{$detail['folio_unico']}' WHERE id_cliente_contacto = {$detail_id}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del cliente : {$this->link->error}" );
			//inserta el registro de sincronizacion del cliente
				$synchronization = $this->insertCostumerContactSynchronizationRows( $detail, $costumer['folio_unico'] );
			//inserta los registros de sincronizacion de clientes en los sistemas de facturacion
				$billSystemCostumerSynchronization = $this->insertBillSystemCostumerSynchronization( $costumer, $detail );
				
				$rows .= ( $rows == "" ? "" : "," );
				$rows .= $costumer['detail']['synchronization_row_id'];
				//var_dump(  $costumer['detail'] );
			}
		//autoriza transaccion
			$this->link->autocommit( true );
			//die( "Rows : {$rows}" );
			return $rows;
		}

/*Insercion de clientes en local*/
		public function insertCostumersLocal( $costumer ){
//foreach ( $costumers as $key => $costumer ) {
				$this->link->autocommit( false );
				//consulta si el cliente ya existe
				$sql = "SELECT id_cliente_facturacion, folio_unico FROM vf_clientes_razones_sociales WHERE rfc = '{$costumer->rfc}'";
				$stm_check = $this->link->query( $sql ) or die( "Error al consultar si el cliente existe : {$this->link->error}" );
				if( $stm_check->num_rows > 0 ){
					$costumer_row = $stm_check->fetch_assoc();
				//actualiza cabecera
					$sql = "UPDATE vf_clientes_razones_sociales SET 
							/*3*/razon_social = '{$costumer->razon_social}', 
							/*4*/id_tipo_persona = '{$costumer->id_tipo_persona}',
							/*5*/entrega_cedula_fiscal = '{$costumer->entrega_cedula_fiscal}', 
							/*6*/url_cedula_fiscal = '{$costumer->url_cedula_fiscal}', 
							/*7*/calle = '{$costumer->calle}', 
							/*8*/no_int = '{$costumer->no_int}', 
							/*9*/no_ext = '{$costumer->no_ext}', 
							/*10*/colonia = '{$costumer->colonia}', 
							/*11*/del_municipio = '{$costumer->del_municipio}', 
							/*12*/cp = '{$costumer->cp}', 
							/*13*/estado = '{$costumer->estado}', 
							/*14*/pais = '{$costumer->pais}', 
							/*15*/regimen_fiscal = '{$costumer->regimen_fiscal}', 
							/*16*/productos_especificos = '{$costumer->productos_especificos}', 
							/*17*/fecha_alta = '{$costumer->fecha_alta}', 
							/*18*/sincronizar = '1'
							WHERE folio_unico = {$costumer_row['folio_unico']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar cliente de facturacion en local : {$sql} {$this->link->error}" );
				}else{
				//inserta cabecera 
					$sql = "INSERT INTO vf_clientes_razones_sociales ( /*1*/id_cliente_facturacion, /*2*/rfc, /*3*/razon_social, /*4*/id_tipo_persona,
							/*5*/entrega_cedula_fiscal, /*6*/url_cedula_fiscal, /*7*/calle, /*8*/no_int, /*9*/no_ext, /*10*/colonia, /*11*/del_municipio, 
							/*12*/cp, /*13*/estado, /*14*/pais, /*15*/regimen_fiscal, /*16*/productos_especificos, /*17*/fecha_alta, /*18*/sincronizar, folio_unico )
							VALUES( /*1*/NULL, /*2*/'{$costumer->rfc}', /*3*/'{$costumer->razon_social}', 
							/*4*/'{$costumer->id_tipo_persona}', /*5*/'{$costumer->entrega_cedula_fiscal}', /*6*/'{$costumer->url_cedula_fiscal}',
							/*7*/'{$costumer->calle}', /*8*/'{$costumer->no_int}', /*9*/'{$costumer->no_ext}', /*10*/'{$costumer->colonia}', 
							/*11*/'{$costumer->del_municipio}', /*12*/'{$costumer->cp}', /*13*/'{$costumer->estado}', /*14*/'{$costumer->pais}', 
							/*15*/'{$costumer->regimen_fiscal}', /*16*/'{$costumer->productos_especificos}', /*17*/NOW(), /*18*/1, '{$costumer->folio_unico}' )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar cliente de facturacion en local : {$sql} {$this->link->error}" );
				}
			//die( 'here2 : ' . $sql );
			//obtiene el id insertado
				$costumer_id = $this->link->insert_id;
				//$costumer->id_cliente = $costumer_id;
				
			//inserta detalle ( contactos )
				//$detail = $costumer['detail'];
				//$detail['id_cliente_facturacion'] = $costumer_id;
				//$sql = "INSERT INTO vf_clientes_contacto ( /*1*/id_cliente_contacto, /*2*/id_cliente_facturacion, /*3*/nombre, /*4*/telefono, /*5*/celular, /*6*/correo,
				//		/*7*/uso_cfdi, /*8*/fecha_alta, /*9*/fecha_ultima_actualizacion, /*10*/folio_unico, /*11*/sincronizar )
				//		VALUES( /*1*/NULL, /*2*/'{$detail['id_cliente_facturacion']}', /*3*/'{$detail['nombre']}', /*4*/'{$detail['telefono']}', /*5*/'{$detail['celular']}', 
				//			/*6*/'{$detail['correo']}', /*7*/'{$detail['uso_cfdi']}', /*8*/NOW(), /*9*/'0000/00/00', 
				//			/*10*/'', /*11*/1 )";
				//$stm = $this->link->query( $sql ) or die( "Error al insertar contacto del cliente de facturacion : {$this->link->error}" );
		//obtiene el id insertado
				//$detail_id = $this->link->insert_id;
				//$detail['id_cliente_contacto'] = $detail_id;
				//$detail['folio_unico'] = "CONTACTO_{$detail_id}";
			//inserta el registro de sincronizacion del cliente
				$this->link->autocommit( true );
//}
			return 'ok';
		}

/*Insercion de contactos en local*/
		public function insertCostumerContactLocal( $contact ){
				$this->link->autocommit( false );
				//consulta si el cliente ya existe
				$sql = "SELECT id_cliente_contacto FROM vf_clientes_contacto WHERE folio_unico = '{$contact->folio_unico}'";
				$stm_check = $this->link->query( $sql ) or die( "Error al consultar si el contacto existe : {$this->link->error}" );
				if( $stm_check->num_rows > 0 ){
					$costumer_row = $stm_check->fetch_assoc();
				//actualiza contacto
					$sql = "UPDATE vf_clientes_contacto SET 
							/*1*/nombre = '{$contact->nombre}', 
							/*2*/telefono = '{$contact->telefono}',
							/*3*/celular = '{$costumer->celular}', 
							/*4*/correo = '{$costumer->correo}', 
							/*5*/uso_cfdi = '{$costumer->uso_cfdi}', 
							/*6*/fecha_ultima_actualizacion = NOW(), 
							/*7*/sincronizar = '1'
							WHERE folio_unico = {$costumer_row['folio_unico']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar contacto de facturacion en local : {$sql} {$this->link->error}" );
				}else{
				//inserta contacto 
					$sql = "INSERT INTO vf_clientes_contacto ( /*1*/id_cliente_contacto, /*2*/id_cliente_facturacion, /*3*/nombre, /*4*/telefono, /*5*/celular, /*6*/correo,
						/*7*/uso_cfdi, /*8*/fecha_alta, /*9*/fecha_ultima_actualizacion, /*10*/folio_unico, /*11*/sincronizar )
						VALUES( /*1*/NULL, /*2*/( SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE folio_unico = '{$contact->id_cliente_facturacion}' LIMIT 1 ), 
							/*3*/'{$contact->nombre}', /*4*/'{$contact->telefono}', /*5*/'{$contact->celular}', 
							/*6*/'{$contact->correo}', /*7*/'{$contact->uso_cfdi}', /*8*/NOW(), /*9*/'0000/00/00', 
							/*10*/'{$contact->folio_unico}', /*11*/1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar contacto de facturacion en local : {$sql} {$this->link->error}" );
				}
				$this->link->autocommit( true );
			return 'ok';
		}

		/*insercion de registros de sincronizacion clientes para sucursales locales en sistema general*/
		public function insertCostumerSynchronizationRows( $costumer, $type ){
		//consulta razon socialDECLARE store_id INTEGER;
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT 
						NULL,
						-1,
						id_sucursal,
						CONCAT('{',
							'\"table_name\" : \"vf_clientes_razones_sociales\",',
							'\"action_type\" : \"{$type}\",',
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
							'\"folio_unico\" : \"', '{$costumer['folio_unico']}', '\",',
							'\"sincronizar\" : \"1\"',
							'}'
						),
						NOW(),
						'{$type}_insertCostumerSynchronizationRows_facturacion.php',
						1
					FROM sys_sucursales 
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion del cliente : {$this->link->error}" );
			return 'ok';
		}
		/*insercion de registros de sincronizacion clientes para sucursales locales en sistema general*/
		public function insertCostumerContactSynchronizationRows( $detail, $costumer_unique_folio ){
		//consulta razon socialDECLARE store_id INTEGER;
  	
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT
						NULL,
						-1,
						id_sucursal,
						CONCAT('{',
							'\"table_name\" : \"vf_clientes_contacto\",',
							'\"action_type\" : \"insert\",',
							'\"id_cliente_facturacion\" : \"', '{$costumer_unique_folio}', '\",',
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
					WHERE id_sucursal > 0";/*
							(SELECT 
								id_cliente_facturacion
							FROM vf_clientes_razones_sociales 
							WHERE folio_unico = '{$costumer_unique_folio}' LIMIT 1 )*/
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de contacto de cliente : {$this->link->error}" );
			return 'ok';
		}

		public function insertBillSystemCostumerSynchronization( $costumer, $detail ){
			//var_dump( $costumer );
			//var_dump( $detail );
			require_once( 'SynchronizationManagmentLog.php' );
			$SynchronizationManagmentLog = new SynchronizationManagmentLog( $this->link );
		//arma la estructura de las peticiones
			$data = array( "costumer"=>$costumer, "contact"=>$contact );
			$post_data = json_encode( $data );
			$endpoints = array();
			//return $post_data; 
		//obtiene las rutas de los sistemas de facturacion
			$sql = "SELECT 
						endpoint_api,
						razon_social
					FROM vf_razones_sociales_emisores
					WHERE habilitado = 1
					AND id_razon_social > 0";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los endpoints de razones sociales : {$this->link->error}" );	
			while( $row = $stm->fetch_assoc() ){
				if( $row['endpoint_api'] == '' ){
					die( "La razon social : {$row['razon_social']} no tiene configurado ningun endpoint, verifica y vuelve a intentar!" );
				}
				$endpoints[] = "{$row['endpoint_api']}/rest/facturacion/inserta_cliente";
			}
			foreach ( $endpoints as $key => $endpoint ) {
			//	echo $endpoint;
				$resp = $SynchronizationManagmentLog->sendPetition( $endpoint, $post_data );
				if( $resp != 'ok' ){
					die( "Error {$endpoint}: " . $resp );
				}
			}
			//var_dump( $endpoints );
			return 'ok';
		}
	}
?>