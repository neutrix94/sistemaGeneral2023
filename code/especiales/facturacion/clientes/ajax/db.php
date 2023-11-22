<?php
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
				echo $BC->saveCostumer( $rfc, $name, $telephone, $email, $person_type, $street_name, 
					$internal_number, $external_number, $cologne, $municipality, $postal_code, $location, 
					$reference, $country, $state, $token, $costumer_name, $cellphone, $fiscal_regime, 
					$fiscal_cedule, $costumer_contacts );
				return '';
			break;

			case 'getCfdis' :
				echo $BC->getCfdis();
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
						$reference, $country, $state, $token, $costumer_name, $cellphone, $fiscal_regime, $fiscal_cedule, $costumer_contacts ){
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
			$data = array( "rfc"=>$rfc, "nombre"=>$name, "usoCFDI"=>"G03", "domicilioFiscal"=>$postal_code, 
				"regimenFiscal"=>$fiscal_regime );
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
				}
					//die( $result->result );
					//return 
				//die( $result->result );
			}
			$this->link->autocommit( false );
		//inserta el registro del cliente
			$sql = "INSERT INTO vf_clientes_razones_sociales_tmp  
						SET rfc = '{$rfc}', 
						razon_social = '{$name}', 
						id_tipo_persona = '', 
						entrega_cedula_fiscal = IF( '{$fiscal_cedule}' = '', 0, 1 ), 
						url_cedula_fiscal = '{$fiscal_cedule}',
						calle = '{$street_name}',
						no_int = '{$internal_number}',
						no_ext = '{$external_number}',
						colonia = '{$cologne}',
						del_municipio = '{$municipality}',
						cp = '{postal_code}',
						estado = '{$state}',
						pais = '{$country}'";
			$stm = $this->link->query( $sql ) or die( "Error al insertar cliente : {$this->link->error}" );
			$customer_id = $this->link->insert_id;
		//inserta el detalle del cliente
			$contacts = explode( "|~|", $costumer_contacts );
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
								fecha_alta = NOW()";
					$stm_contact = $this->link->query( $sql ) or die( "Error al insertar contacto(s) del cliente : {$this->link->error}" );  
				}
			}
			$this->link->autocommit( true );
		//consume el api para subir clientes a linea
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
			die( "{$resp}" );
		//elimina el token
			$sql = "DELETE FROM vf_tokens_alta_clientes WHERE token = '{$token}'";
			$stm = $this->link->query( $sql ) or die( "Error al eliminar el token : {$this->link->error}" );
	//consume api para subir cliente
			die( 'ok' );
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
						uso_cfdi AS cdfi_use
					FROM vf_clientes_contacto 
					WHERE id_cliente_facturacion = {$costumer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de contacto : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return "ok|" . json_encode($resp);
		}

		public function seek_by_rfc( $rfc ){
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
						crs.regimen_fiscal AS tax_regime
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