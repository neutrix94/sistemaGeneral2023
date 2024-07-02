<?php
	include( '../../../../../conect.php' );
	include( '../../../../../conexionMysqli.php' );
	if( isset( $_GET['costumer_fl'] ) || isset( $_POST['costumer_fl'] ) ){
		$BC = new BillCostumer( $link );
		$action = ( isset( $_GET['costumer_fl'] ) ? $_GET['costumer_fl'] : $_POST['costumer_fl'] );
		switch ( $action ) {
			case 'seek_by_rfc':
				$rfc = ( isset( $_GET['rfc'] ) ? $_GET['rfc'] : $_POST['rfc'] );
				echo $BC->seek_by_rfc( $rfc );
				return '';
			break;

			case 'getCostumerContacts' :
				$costumer_id = ( isset( $_GET['costumer_id'] ) ? $_GET['costumer_id'] : $_POST['costumer_id'] );
				echo $BC->getCostumerContacts( $costumer_id );
				return '';
			break;
			case 'saveCostumer' :
				$rfc = $_POST['rfc'];
				$name = $_POST['name'];
				$telephone = $_POST['telephone'];
				$email = $_POST['email'];
				$person_type = $_POST['person_type'];
				$street_name = $_POST['street_name'];
				$internal_number = $_POST['internal_number'];
				$external_number = $_POST['external_number'];
				$cologne = $_POST['cologne'];
				$municipality = $_POST['municipality'];
				$postal_code = $_POST['postal_code'];
				$location = $_POST['location'];
				$reference = $_POST['reference'];
				$country = $_POST['country'];
				$state = $_POST['state'];
				$token = $_POST['token'];
				$costumer_name = $_POST['costumer_name'];
				$cellphone = $_POST['cellphone'];
				$costumer_contacts = $_POST['costumer_contacts'];
				$fiscal_regime = $_POST['fiscal_regime'];
				$fiscal_cedule = $_POST['fiscal_cedule'];
				$costumer_id = $_POST['costumer_id'];
				echo $BC->saveCostumer( $rfc, $name, $telephone, $email, $person_type, $street_name, 
					$internal_number, $external_number, $cologne, $municipality, $postal_code, $location, 
					$reference, $country, $state, $token, $costumer_name, $cellphone, $fiscal_regime, 
					$fiscal_cedule, $costumer_contacts, $costumer_id, $user_sucursal );
				return '';
			break;

			case 'getCfdis' :
				$cfdi = ( isset( $_GET['cfdi'] ) ? $_GET['cfdi'] : null );
				echo $BC->getCfdis( $cfdi );
			break;

			default:
				die( "Access Denied on {$action}!" );
			break;
		}
	}

	class BillCostumer
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
	//funcion para recuperar los tipos de cfdi
		function getCfdis( $cfdi = null ){
			$resp = "";
			$sql = "SELECT
				clave AS clue,
				nombre AS name
			FROM vf_cfdi";
			//if( $cfdi != null ){
			//	$sql .= 
			//}
			$stm = $this->link->query( $sql ) or die( "Error al consultar los cfdis : {$this->link->error}" );
			$resp = "<option value=\"0\">-- Seleccionar --</option>";
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<option value=\"{$row['clue']}\"";
				$resp .= ( $cfdi != null && $cfdi == $row['clue'] ? ' selected' : '' );
				$resp .= ">{$row['name']}</option>";
			}
			return $resp;
		}
	//funciones para guardar clientes y sus contactos
		public function saveCostumer( $rfc, $name, $telephone, $email, $person_type, $street_name, 
						$internal_number, $external_number, $cologne, $municipality, $postal_code, $location, 
						$reference, $country, $state, $token, $costumer_name, $cellphone, $fiscal_regime, $fiscal_cedule, 
						$costumer_contacts, $costumer_id, $store_id ){
		//obtiene caracteres de reemplazo
			$sql = "SELECT caracter, codigo_reemplazo FROM vf_caracteres_especiales WHERE id_caracter_especial > 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los caracteres especiales : {$this->link->error}" );
			$replace = array();
			while ( $row = $stm->fetch_assoc() ) {
				$replace[] = $row;
			}
		//verifica datos del cliente por medio de API
			$local_path = "";
			$archivo_path = "../../../../../conexion_inicial.txt";
			if(file_exists($archivo_path) ){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
				$config=explode("<>",$line);
				$tmp=explode("~",$config[0]);
				$local_path = "localhost/" . base64_decode( $tmp[1] ) . "/rest/v1/facturaReceptor";
			}else{
				die("No hay archivo de configuración!!!");
			}
//echo $name;
//die( 'here : ' . $name );
			$name = str_replace('"', '&quot;', $name );
			
			foreach ($replace as $key => $rep) {
			//	$name = str_replace( ''.$rep['caracter'].'', "{$rep['codigo_reemplazo']}", $name );//nombre razon social
				//$name = str_replace( "&QUOT;", "&quot;", $name );//nombre razon social
				//$row[23] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[23] );//calle
				//$row[26] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[26] );//colonia
				//$row[27] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[27] );//del_municipio
			}
			//die( "nombre : " . $name );

			$data = array( "rfc"=>$rfc, "nombre"=>$name, "usoCFDI"=>"G03", "domicilioFiscal"=>$postal_code, 
				"regimenFiscal"=>$fiscal_regime );//var_dump( $data );die('');
			$sql = "select token from api_token where id_user=0 and expired_in > now() limit 1;";
			$stm = $this->link->query($sql) or die( "Error al consultar el token : {$this->link->error}" );
			$respuesta = $stm->fetch_assoc();
			$token = $respuesta['token'];

			$post_data = json_encode( $data );
			//die( $post_data );
			$crl = curl_init( $local_path );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			//die( $resp );
			$result = json_decode( $resp );
			//var_dump($result);
			if( $result->status != 200 ){
			//casos de respuesta
				if( isset( $result->result ) ){
					//var_dump( $result );
					//var_dump( $result->result[4]->Key );
					//var_dump( $result->result[5]->Key );
					//die( '' );
					if( $result->result[4]->Key == "regimenFiscalEsperado" && $result->result[3]->Key == "regimenFiscalReportado" 
						|| $result->result[5]->Key == "regimenFiscalEsperado" && $result->result[4]->Key == "regimenFiscalReportado" ){
						if( $result->result[3]->Value != $result->result[4]->Value ){
							$result->result = "El régimen fiscal es inválido!";
						}
					}else if( $result->result[0]->Key == "Mensaje" ){
						$result->result = $result->result[0]->Value;
					}else if( $result->result[0]->Key == "message" ){
						$result->result = $result->result[0]->Value;
					}
					if( $result->result == "Hace falta información del receptor, Regimen Fiscal" ){
						$result->result = "Selecciona un régimen fiscal Válido!";
					}
					if( $result->result == "CFDI40143 - Este RFC del receptor no existe en la lista de RFC inscritos no cancelados del SAT." 
						 ){//|| strpos($result->result, 'RFC' ) != false 
						//die('here');
						$result->result = "El RFC es Inválido!";
					}
					if( $result->result == "CFDI40145 - El campo Nombre del receptor, debe pertenecer al nombre asociado al RFC registrado en el campo Rfc del Receptor." ){
						$result->result = "El nombre / Razón Social es Inválido!";
					}
					if (strpos($result->result, 'DomicilioFiscalReceptor') != false ){
						$result->result = "El código postal es incorrecto!";
					}
					//var_dump( $result );
					die( "<div class=\"row\">
						<h2 class=\"text-center text-danger fs-1\">{$result->result}</h2>
						<h2 class=\"text-center text-primary\">Verifica y vuelve a intenar.</h2>
						<button
							type=\"button\"
							onclick=\"close_emergent();\"
							class=\"btn btn-danger\"
						>
							<i class=\"icon-ok-circled\">Aceptar</i>
						</button>
					</div>" );
				}else{
					var_dump($result);
					die('');
				}
					//die( $result->result );
					//return 
				//die( $result->result );
			}else{
				//var_dump( $result );
			}
			$this->link->autocommit( false );
		//inserta el registro del cliente
			$sql = "INSERT INTO vf_clientes_razones_sociales_tmp  
						SET rfc = '{$rfc}', 
						razon_social = '{$name}', 
						id_tipo_persona = '{$person_type}', 
						entrega_cedula_fiscal = IF( '{$fiscal_cedule}' = '', 0, 1 ), 
						url_cedula_fiscal = '{$fiscal_cedule}',
						calle = '{$street_name}',
						no_int = '{$internal_number}',
						no_ext = '{$external_number}',
						colonia = '{$cologne}',
						del_municipio = '{$municipality}',
						cp = '{$postal_code}',
						estado = '{$state}',
						pais = '{$country}',
						regimen_fiscal = '{$fiscal_regime}',
						id_cliente_facturacion = IF( '$costumer_id' = '' OR '$costumer_id' = '0', '0', '{$costumer_id}' )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar cliente : {$this->link->error}" );
			$customer_id = $this->link->insert_id;
		//inserta el detalle del cliente
			$contacts = explode( "|~|", $costumer_contacts );
			//$contacts_to_insert = array();
			foreach ($contacts as $key => $value) {
				if( $value != '' ){
					$contact = explode("~", $value);
					$sql = "INSERT INTO vf_clientes_contacto_tmp
								SET id_cliente_facturacion_tmp = '{$customer_id}',
								nombre = '{$contact[0]}',
								telefono = '{$contact[1]}',
								celular = '{$contact[2]}',
								correo = '{$contact[3]}',
								uso_cfdi = '{$contact[4]}',
								fecha_alta = NOW(),
								id_cliente_facturacion = IF( '$costumer_id' = '' OR '$costumer_id' = '0', '0', '{$costumer_id}' ),
								id_cliente_contacto = IF( '{$contact[6]}' = '' OR '{$contact[6]}' = '0', '0', '{$contact[6]}' )";
								//die( $sql );
					$stm_contact = $this->link->query( $sql ) or die( "Error al insertar contacto(s) del cliente : {$this->link->error}" );
				}
			}
			$this->link->autocommit( true );
		//consume el api para subir/descargar clientes a linea
			$local_path = "";
			$archivo_path = "../../../../../conexion_inicial.txt";
			if(file_exists($archivo_path) ){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
				$config=explode("<>",$line);
				$tmp=explode("~",$config[0]);
				$local_path = "localhost/" . base64_decode( $tmp[1] ) . "/rest/facturacion/envia_cliente";
			}else{
				die("No hay archivo de configuración!!!");
			}
			//die( $local_path );
			$crl = curl_init( $local_path );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			//curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
$resp = curl_exec($crl);//envia peticion 
			curl_close($crl);
			//die( "{$resp}" );
			if( $resp != "ok" ){
				var_dump( $resp );
				die( "Error!" );
			}
			$sql = "SELECT folio_unico FROM vf_clientes_razones_sociales WHERE rfc = '{$rfc}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el folio unico del cliente final : {$this->link->error}" );
			$final_costumer = $stm->fetch_assoc();
		//elimina el token
			//$sql = "DELETE FROM vf_tokens_alta_clientes WHERE token = '{$token}'";
			//$stm = $this->link->query( $sql ) or die( "Error al eliminar el token : {$this->link->error}" );
			die( 'ok|' . $final_costumer['folio_unico'] );
		}
		public function getCostumerContacts( $costumer_id ){
			$resp = array();
			$sql = "SELECT
						id_cliente_contacto AS contact_id,
						id_cliente_facturacion AS costumer_id,
						nombre AS name,
						telefono AS telephone,
						celular AS cellphone,
						correo AS email,
						uso_cfdi AS cdfi_use,
						folio_unico AS unique_folio
					FROM vf_clientes_contacto 
					WHERE id_cliente_facturacion = {$costumer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de contacto : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return "ok|" . json_encode($resp);
		}

		public function seek_by_rfc( $rfc ){
		//consume el api para subir/descargar clientes a linea
			$local_path = "";
			$archivo_path = "../../../../../conexion_inicial.txt";
			if(file_exists($archivo_path) ){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
				$config=explode("<>",$line);
				$tmp=explode("~",$config[0]);
				$local_path = "localhost/" . base64_decode( $tmp[1] ) . "/rest/facturacion/envia_cliente";
			}else{
				die("No hay archivo de configuración!!!");
			}
			//die( $local_path );
			$crl = curl_init( $local_path );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			//curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion 
			curl_close($crl);
		//busca en base de datos
			$sql = "SELECT 
						crs.id_cliente_facturacion As costumer_id,
						crs.rfc AS rfc,
						crs.razon_social AS bussines_name,
						crs.id_tipo_persona AS person_type,
						crs.entrega_cedula_fiscal AS delivery_fiscal_certificate,
						crs.url_cedula_fiscal AS fiscal_certificate_url,
						crs.calle AS street_name,
						crs.no_int AS internal_number,
						crs.no_ext AS external_number,
						crs.colonia AS cologne,
						crs.del_municipio AS municipality,
						crs.cp AS postal_code,
						crs.estado AS state,
						crs.pais AS country,
						crs.regimen_fiscal AS tax_regime,
						crs.folio_unico AS unique_folio
					FROM vf_clientes_razones_sociales crs
					WHERE crs.rfc = '{$rfc}'";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el RFC existe : {$this->link->error}." );
			if($stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				return "ok|".json_encode( $row );
			}else{
			//consume API para descargar clientes pendientes
				$costumersSinchronization = $this->getCostumersByAPI();
			//vuelve a consultar si el cliente existe
				$stm = $this->link->query( $sql ) or die( "Error al consultar si el RFC existe : {$this->link->error}." );
				if($stm->num_rows > 0 ){
					$row = $stm->fetch_assoc();
					return "ok|" . json_encode( $row );
				}else{
					die( "El RFC : {$rfc} no esta registrado, captúrtalo para continuar!" );
				}
			}
		}

//consume api para descargar clientes
		public function getCostumersByAPI(){
	//consulta id de sucursal y configuraciones para consumo de API
			$resp = null;
			$sql = "SELECT
						id_sucursal AS store_id,
						( SELECT value FROM api_config WHERE name = 'path' ) AS api_path
					FROM sys_sucursales WHERE acceso = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar configuraciones para consumo de API : {$this->link->error}." );
			$row = $stm->fetch_assoc();
			$store_id = $row['store_id'];
			$api_path = $row['api_path'];
		//consume API

			return 'resp';
		}
//enviar peticion
		public function send_petition( $api_path, $post_data ){

		}
	}
?>