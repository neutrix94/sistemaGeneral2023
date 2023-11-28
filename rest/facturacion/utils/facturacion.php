<?php
//ok 2023/11/25
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
  			$this->link->set_charset("utf8mb4");
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
						productos_especificos,
						id_cliente_facturacion
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
				$json = json_encode( $row, JSON_UNESCAPED_UNICODE );
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
			$resp = array();
			$sql = "SELECT 
						id_cliente_contacto_tmp,
						id_cliente_facturacion_tmp,
						nombre,
						telefono,
						celular,
						correo,
						uso_cfdi,
						id_cliente_contacto,
						id_cliente_facturacion
		  			FROM vf_clientes_contacto_tmp
		  			WHERE id_cliente_facturacion_tmp = {$costumer_id}
		  			AND ( folio_unico IS NULL OR folio_unico = '' )"; //die( $sql );
		  	$stm = $this->link->query( $sql ) or die( "Error al consultar razones sociales pendientes de sincronizar : {$sql} {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_contacto_tmp', 'id_cliente_contacto_tmp', 'CLRZ', $row['id_cliente_contacto_tmp'] );
				$resp[] = $row;
				//die( 'HERE : ' . $row['folio_unico'] );
				//$detail = $this->getTemporalCostumerDetail( $row['id_cliente_tmp'] );
				//if( sizeof( $detail ) > 0 ){
					//$row['detail'] = $detail;
				//}
				//var_dump( $row );
			}
			return $resp;
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
				//var_dump( $costumer['id_cliente_facturacion_tmp'] );
				$insert = $this->insertLineCostumer( $costumer );
				if( $insert != "ok" ){
					die( "Error en objeto insertCostumers : {$insert}" );
				}
			//inserta los registros de sincronizacion de clientes en los sistemas de facturacion
				
				$rows .= ( $rows == "" ? "" : "," );
				$rows .= $costumer['detail'][0]['synchronization_row_id'];
			}
		//autoriza transaccion
			$this->link->autocommit( true );
			//die( "Rows : {$rows}" );
			return $rows;
		}

		public function insertLocalCostumers( $costumers ){
			$rows = "";
			$this->link->autocommit( false );
			foreach ( $costumers as $key => $costumer ) {
				//var_dump( $costumer['id_cliente_facturacion_tmp'] );
				$insert = $this->insertLocalCostumer( $costumer );
				if( $insert != "ok" ){
					die( "Error en objeto insertCostumers : {$insert}" );
				}
			//inserta los registros de sincronizacion de clientes en los sistemas de facturacion
				
				$rows .= ( $rows == "" ? "" : "," );
				$rows .= $costumer['detail'][0]['synchronization_row_id'];
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
							WHERE folio_unico = '{$costumer_row['folio_unico']}'";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar cliente de facturacion en local : {$sql} {$this->link->error}" );
				}else{
				//inserta cabecera 
					$sql = "INSERT INTO vf_clientes_razones_sociales ( /*1*/id_cliente_facturacion, /*2*/rfc, /*3*/razon_social, /*4*/id_tipo_persona,
							/*5*/entrega_cedula_fiscal, /*6*/url_cedula_fiscal, /*7*/calle, /*8*/no_int, /*9*/no_ext, /*10*/colonia, /*11*/del_municipio, 
							/*12*/cp, /*13*/estado, /*14*/pais, /*15*/regimen_fiscal, /*16*/productos_especificos, /*17*/fecha_alta, /*18*/sincronizar, folio_unico )
							VALUES( /*1*/{$costumer->id_cliente_facturacion}, /*2*/'{$costumer->rfc}', /*3*/'{$costumer->razon_social}', 
							/*4*/'{$costumer->id_tipo_persona}', /*5*/'{$costumer->entrega_cedula_fiscal}', /*6*/'{$costumer->url_cedula_fiscal}',
							/*7*/'{$costumer->calle}', /*8*/'{$costumer->no_int}', /*9*/'{$costumer->no_ext}', /*10*/'{$costumer->colonia}', 
							/*11*/'{$costumer->del_municipio}', /*12*/'{$costumer->cp}', /*13*/'{$costumer->estado}', /*14*/'{$costumer->pais}', 
							/*15*/'{$costumer->regimen_fiscal}', /*16*/'{$costumer->productos_especificos}', /*17*/NOW(), /*18*/1, '{$costumer->folio_unico}' )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar cliente de facturacion en local : {$sql} {$this->link->error}" );
				}
			//die( 'here2 : ' . $sql );
			//obtiene el id insertado
				$costumer_id = $this->link->insert_id;
				$this->link->autocommit( true );
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
					$contact->id_cliente_contacto = str_replace('CONTACTO_', '', $contact->id_cliente_contacto );
					$sql = "INSERT INTO vf_clientes_contacto ( /*1*/id_cliente_contacto, /*2*/id_cliente_facturacion, /*3*/nombre, /*4*/telefono, /*5*/celular, /*6*/correo,
						/*7*/uso_cfdi, /*8*/fecha_alta, /*9*/fecha_ultima_actualizacion, /*10*/folio_unico, /*11*/sincronizar )
						VALUES( /*1*/{$contact->id_cliente_contacto}, /*2*/( SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE folio_unico = '{$contact->id_cliente_facturacion}' LIMIT 1 ), 
							/*3*/'{$contact->nombre}', /*4*/'{$contact->telefono}', /*5*/'{$contact->celular}', 
							/*6*/'{$contact->correo}', /*7*/'{$contact->uso_cfdi}', /*8*/NOW(), /*9*/'0000/00/00', 
							/*10*/'{$contact->folio_unico}', /*11*/1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar contacto de facturacion en local 1 : " . var_dump( $contact ) . "{$sql} {$this->link->error}" );
				}
				$this->link->autocommit( true );
			die( 'ok' );
		}

		/*insercion de registros de sincronizacion clientes para sucursales locales en sistema general
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
							'\"id_cliente_facturacion\" : \"', '{$costumer['id_cliente_facturacion']}', '\",',
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
		/*insercion de registros de sincronizacion clientes para sucursales locales en sistema general
		public function insertCostumerContactSynchronizationRows( $detail, $type, $costumer_unique_folio = '' ){
		//consulta razon socialDECLARE store_id INTEGER;
  	
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT
						NULL,
						-1,
						id_sucursal,
						CONCAT('{',
							'\"table_name\" : \"vf_clientes_contacto\",',
							'\"action_type\" : \"{$type}\",',
							'\"id_cliente_contacto\" : \"{$detail['id_cliente_contacto']}\",',
							IF( '{$costumer_unique_folio}' != '',
								CONCAT( '\"id_cliente_facturacion\" : \"', '{$costumer_unique_folio}', '\",' ),
								''
							),
							'\"nombre\" : \"', '{$detail['nombre']}', '\",',
							'\"telefono\" : \"', '{$detail['telefono']}', '\",',
							'\"celular\" : \"', '{$detail['celular']}', '\",',
							'\"correo\" : \"', '{$detail['correo']}', '\",',
							'\"uso_cfdi\" : \"', '{$detail['uso_cfdi']}', '\",',
							'\"fecha_alta\" : \"', NOW(), '\",',
							'\"fecha_ultima_actualizacion\" : \"', NOW(), '\",',
							'\"folio_unico\" : \"', '{$detail['folio_unico']}', '\",',
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
		}*/

		public function insertLineCostumer( $costumer ){
			//var_dump( $costumer['url_cedula_fiscal'] );
			$action = "";
		//verifica si el cliente existe en relacion al RFC
			$sql = "SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE rfc = '{$costumer['rfc']}'";
			$check_stm = $this->link->query( $sql ) or die( "Error al consultar si el cliente existe en linea por RFC : {$this->link->error}" );
			if( $check_stm->num_rows > 0 ){
				$aux_row = $check_stm->fetch_assoc();
				$costumer['id_cliente_facturacion'] = "{$aux_row['id_cliente_facturacion']}";
			}
			//$costumer_id = "";
			$sql = ( $costumer['id_cliente_facturacion'] == "" || $costumer['id_cliente_facturacion'] == 0 ? "INSERT INTO" : "UPDATE" );
			$sql .= " vf_clientes_razones_sociales SET
						rfc = '{$costumer['rfc']}', 
						razon_social = '{$costumer['razon_social']}', 
						id_tipo_persona = '{$costumer['id_tipo_persona']}',
						entrega_cedula_fiscal = '{$costumer['entrega_cedula_fiscal']}', 
						url_cedula_fiscal = '{$costumer['url_cedula_fiscal']}', 
						calle = '{$costumer['calle']}', 
						no_int = '{$costumer['no_int']}', 
						no_ext = '{$costumer['no_ext']}', 
						colonia = '{$costumer['colonia']}', 
						del_municipio = '{$costumer['del_municipio']}', 
						cp = '{$costumer['cp']}', 
						estado = '{$costumer['estado']}', 
						pais = '{$costumer['pais']}', 
						regimen_fiscal = '{$costumer['regimen_fiscal']}', 
						productos_especificos = '{$costumer['productos_especificos']}', 
						fecha_alta = NOW(), 
						sincronizar = 1";
			if ( $costumer['id_cliente_facturacion'] == "" || $costumer['id_cliente_facturacion'] == 0 ){
				$action = "INSERTAR";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo cliente : {$this->link->error}" );
				$costumer['id_cliente_facturacion'] = "{$this->link->insert_id}";
				$costumer['folio_unico'] = "CLIENTE_{$costumer['id_cliente_facturacion']}";
			//actualiza el folio unico
				$sql = "UPDATE vf_clientes_razones_sociales 
							SET folio_unico = '{$costumer['folio_unico']}' 
						WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del nuevo cliente : {$this->link->error}" );
			}else{
				$action = "ACTUALIZAR";
				$costumer['folio_unico'] = "CLIENTE_{$costumer['id_cliente_facturacion']}";
				$sql .= " WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el cliente : {$this->link->error}" );
			}
		//procesa el detalle
			foreach ( $costumer['detail'] as $key => $contact ) {
				$sql = ( $costumer['detail'][$key]['id_cliente_contacto'] == "" || $costumer['detail'][$key]['id_cliente_contacto'] == "0" ? "INSERT INTO" : "UPDATE" );
				$costumer['detail'][$key]['id_cliente_facturacion'] = $costumer['id_cliente_facturacion'];
				$sql .= " vf_clientes_contacto SET 
							id_cliente_facturacion = '{$costumer['detail'][$key]['id_cliente_facturacion']}',
							nombre = '{$costumer['detail'][$key]['nombre']}', 
							telefono = '{$costumer['detail'][$key]['telefono']}',
							celular = '{$costumer['detail'][$key]['celular']}', 
							correo = '{$costumer['detail'][$key]['correo']}', 
							uso_cfdi = '{$costumer['detail'][$key]['uso_cfdi']}', 
							fecha_ultima_actualizacion = NOW(), 
							sincronizar = '1'";
				if( $costumer['detail'][$key]['id_cliente_contacto'] == "" || $costumer['detail'][$key]['id_cliente_contacto'] == "0" ){
						
						$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo contacto : {$this->link->error}" );
						$costumer['detail'][$key]['id_cliente_contacto'] = $this->link->insert_id;
						$costumer['detail'][$key]['folio_unico'] = "CONTACTO_{$costumer['detail'][$key]['id_cliente_contacto']}";
					//actualiza el folio unico
						$sql = "UPDATE vf_clientes_contacto 
									SET folio_unico = '{$costumer['detail'][$key]['folio_unico']}' 
								WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
						$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del nuevo cliente : {$this->link->error}" );
				}else{
					$costumer['detail'][$key]['folio_unico'] = "CONTACTO_{$costumer['detail'][$key]['id_cliente_contacto']}";
					$sql .= " WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar el contacto : {$this->link->error}" );
				}
			}
		//inserta el registro de sincronizacion para sucursales locales
			$costumer_json = json_encode( $costumer, JSON_UNESCAPED_UNICODE );
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT
						NULL,
						-1,
						id_sucursal,
						'{$costumer_json}',
						NOW(),
						'facturacion_insertLineCostumer.php',
						1
					FROM sys_sucursales 
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de cliente poara equipos locales: {$this->link->error}" );
			return 'ok';
		}

		public function insertLocalCostumer( $costumer ){
			//var_dump( $costumer['id_cliente_facturacion'] );
			$action = "";
			$costumer_exists = false;
		//consulta si el cliente existe
			$sql = "SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
			$stm = $this->link->query( $sql );
			if( $stm->num_rows > 0 ){
				$costumer_exists = true;
			}
			$sql = ( $costumer_exists == false ? "INSERT INTO" : "UPDATE" );
			$sql .= " vf_clientes_razones_sociales SET ";
			if( $costumer_exists == false ){
				$sql .= " id_cliente_facturacion = {$costumer['id_cliente_facturacion']}, ";
			}
			$sql .= "rfc = '{$costumer['rfc']}', 
						razon_social = '{$costumer['razon_social']}', 
						id_tipo_persona = '{$costumer['id_tipo_persona']}',
						entrega_cedula_fiscal = '{$costumer['entrega_cedula_fiscal']}', 
						url_cedula_fiscal = '{$costumer['url_cedula_fiscal']}', 
						calle = '{$costumer['calle']}', 
						no_int = '{$costumer['no_int']}', 
						no_ext = '{$costumer['no_ext']}', 
						colonia = '{$costumer['colonia']}', 
						del_municipio = '{$costumer['del_municipio']}', 
						cp = '{$costumer['cp']}', 
						estado = '{$costumer['estado']}', 
						pais = '{$costumer['pais']}', 
						regimen_fiscal = '{$costumer['regimen_fiscal']}', 
						productos_especificos = '{$costumer['productos_especificos']}', 
						fecha_alta = NOW(), 
						folio_unico = '{$costumer['folio_unico']}',
						sincronizar = 1";
			//die( $sql );
			if ( $costumer_exists == false ){
				$action = "INSERTAR";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo cliente : {$this->link->error}" );
			}else{
				$action = "ACTUALIZAR";
				$sql .= " WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el cliente : {$this->link->error}" );
			}
		//procesa el detalle
			foreach ( $costumer['detail'] as $key => $contact ) {
				$contact_exists = false;
			//consulta si el contacto existe
				$sql = "SELECT id_cliente_contacto FROM vf_clientes_contacto WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
				$stm = $this->link->query( $sql );
				if( $stm->num_rows > 0 ){
					$contact_exists = true;
				}
				$sql = ( $contact_exists == false ? "INSERT INTO" : "UPDATE" );
				$costumer['detail'][$key]['id_cliente_facturacion'] = $costumer['id_cliente_facturacion'];
				$sql .= " vf_clientes_contacto SET ";
				if( $contact_exists == false ){
					$sql .= "id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}, ";
				}
				$sql .= "id_cliente_facturacion = '{$costumer['detail'][$key]['id_cliente_facturacion']}',
							nombre = '{$costumer['detail'][$key]['nombre']}', 
							telefono = '{$costumer['detail'][$key]['telefono']}',
							celular = '{$costumer['detail'][$key]['celular']}', 
							correo = '{$costumer['detail'][$key]['correo']}', 
							uso_cfdi = '{$costumer['detail'][$key]['uso_cfdi']}', 
							fecha_ultima_actualizacion = NOW(), 
							sincronizar = '1',
							folio_unico = '{$costumer['detail'][$key]['folio_unico']}'";
				if( $contact_exists == false  ){
				//echo ( $sql );
						$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo contacto : {$this->link->error}" );
				}else{
				//echo ( $sql );
					$sql .= " WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar el contacto : {$this->link->error}" );
				}
			}
			return 'ok';
		}

		/*public function insertLineCostumnerContact( $costumer_contact ){
			$sql = "";
		}*/
	}
?>