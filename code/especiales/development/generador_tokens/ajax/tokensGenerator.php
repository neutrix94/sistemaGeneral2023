<?php
	if( isset( $_POST['tokenFl'] ) ){
		include('../../../../../conectMin.php');
		include('../../../../../conexionMysqli.php');
		$action = $_POST['tokenFl'];
		$tokensGenerator = new tokensGenerator( $link, $user_id );
		switch ( $action ) {
			case 'getStoresUsers':
				echo $tokensGenerator->getStoresUsers( $_POST['store'] );
			break;

			case 'getToken':
				echo $tokensGenerator->getToken( $_POST['user'], $_POST['store'] );
			break;

			case 'seekToken':
				echo $tokensGenerator->seekToken( $_POST['token'], $_POST['device_token'] );
			break;
			default:
				die( "Permission Denied on : {$action}!" );
			break;
		}
	}

		/**/
		


	class tokensGenerator
	{
		private $link;
		private $user_id;
		function __construct( $connection, $user_id )
		{
			$this->link = $connection;
			$this->user_id = $user_id;
		}

		public function seekToken( $token, $device_token ){
		//busca que el token no este ocupado
			$sql = "SELECT 
						id_usuario AS user_id,
						id_token, 
						token,
						token_dispositivo
					FROM ec_asistencia_tokens 
					WHERE token = '{$token}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de token : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return "error|Token invalido!\nSolicita un token e ingresalo :";
			}
			$token = $stm->fetch_assoc();
			if( $token['token_dispositivo'] != '' && $token['token_dispositivo'] != $device_token ){
				return "error|Este token ya fue utilizado en otro dispositivo,\nSolicita un nuevo token e ingresalo :";
			}
		//busca datos de sesion dispostivo
			$sql = "SELECT 
						id_usuario AS user_id,
						token_unico AS device_token
					FROM sys_sesiones_dispositivos 
					WHERE token_unico = '{$device_token}'";
//die( 'info|' . $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de sesion : {$this->link->error}" );
			$device_token = $stm->fetch_assoc();
			if( $device_token['user_id'] != $token['user_id'] ){
				return "error|Este token no pertenece a este usuario!\nSolicita un token para tu usuario e ingresalo :";
			}
			$sql = "UPDATE ec_asistencia_tokens SET token_dispositivo = '{$device_token['device_token']}' WHERE id_token = {$token['id_token']}";
			$stm = $this->link->query( $sql ) or die( "Error al enlazar el token con la sesion del dispositivo : {$this->link->error}" );
			return 'ok|Token insertado exitosamente!';	
		}
		public function getToken( $user, $store ){
			//die( $user ."+". $store );
			$this->link->autocommit( false );
			$sql = "INSERT INTO ec_asistencia_tokens ( id_sucursal, id_usuario )
					VALUES ( {$store}, {$user} )";
//	die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al insertar datos_del token : {$this->link->error}" );
			$id = $this->link->insert_id;
		//forma token
			$token = "{$store}-{$user}-" . uniqid() . "-{$id}";
			$sql = "UPDATE ec_asistencia_tokens SET token = '{$token}' WHERE id_token = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar token : {$this->link->error}" );
			$this->link->autocommit( true );
			return $token;
		}

		public function getStores(){
			$sql = "SELECT id_sucursal, nombre FROM sys_sucursales";
			$stm = $this->link->query( $sql ) or die( "Error al consultar sucursales : {$this->link->error}" );
			$stores = $this->getRowArray( $stm );
			return $this->build_combo( $stores, 'store_id', 'getStoresUsers()' );
		}
		public function getStoresUsers( $store_id ){
			$sql = "SELECT 
						id_usuario, 
						CONCAT( nombre, apellido_paterno, ' ', apellido_materno )
					FROM sys_users 
					WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar usuarios de la sucursal : {$this->link->error}" );
			$users = $this->getRowArray( $stm );
			//var_dump($users);
			return $this->build_combo( $users, 'user_id', '' );
		}

		public function getAssocArray( $stm ){
			$resp = array();
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return $resp;
		}
		public function getRowArray( $stm ){
			$resp = array();
			while ( $row = $stm->fetch_row() ) {
				$resp[] = $row;
			}
			return $resp;
		}

		public function build_combo( $data, $object_id, $onchange = '' ){
			$resp = "<select id=\"{$object_id}\" onchange=\"{$onchange}\" class=\"form-control\">
						<option value=\"0\">-- Seleccionar --</option>";
			foreach ($data as $key => $dat) {
				$resp .= "<option value=\"{$dat[0]}\">{$dat[1]}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}
	}
?>