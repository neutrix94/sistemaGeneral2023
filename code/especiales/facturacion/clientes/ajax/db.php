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
				echo $BC->saveCostumer( $rfc, $name, $telephone, $email, $person_type, $street_name, 
					$internal_number, $external_number, $cologne, $municipality, $postal_code, $location, 
					$reference, $country, $state, $token, $costumer_name, $cellphone );
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
						$reference, $country, $state, $token, $costumer_name, $cellphone ){
			$this->link->autocommit( false );
		//inserta el registro del cliente
			$sql = "INSERT INTO vf_clientes_razones_sociales_tmp  
						SET rfc = '{$rfc}', 
						razon_social = '{$name}', 
						id_tipo_persona = '', 
						entrega_cedula_fiscal = '', 
						url_cedula_fiscal = '',
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
			foreach ($variable as $key => $value) {
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
		//elimina el token
			$sql = "DELETE FROM vf_tokens_alta_clientes WHERE token = '{$token}'";
			$stm = $this->link->query( $sql ) or die( "Error al eliminar el token : {$this->link->error}" );
			$this->link->autocommit( true );
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