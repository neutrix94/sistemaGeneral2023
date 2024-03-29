<?php
	include( '../../../conexionMysqli.php' );

	class apiNetPay{
		private $link;
		private $store_id;
		private $NetPayStoreId;
		function __construct( $connection, $store_id )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
			//$this->NetPayStoreId = $this->getCurrentStoreId();
			//die( $this->NetPayStoreId );
		}
	/*obtener el storeId actual
		public function getCurrentStoreId(){
			$sql = "SELECT
						rse.store_id_netpay AS storeId
					FROM sys_sucursales s
					LEFT JOIN vf_razones_sociales_emisores rse
					ON rse.id_razon_social = s.razon_social_actual
					WHERE s.id_sucursal = {$this->store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el StoreId actual : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['storeId'];
		}*/
	//obtencion de endpoints
		public function getEndpoint( $terminal_id, $endpoint_type ){
			$sql = "SELECT 
						{$endpoint_type} AS endpoint
					FROM ec_terminales_integracion_smartaccounts tis 
					LEFT JOIN ec_tipos_bancos tb
					ON tis.id_tipo_terminal = tb.id_tipo_banco
					WHERE tis.id_terminal_integracion = '{$terminal_id}'
					OR tis.numero_serie_terminal = '{$terminal_id}'";//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar endpoint {$endpoint_type} : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['endpoint'];
		}
	//generacion de token
		public function requireToken( $terminal_id, $grantType = 'password', $user = 'smartPos', $password = 'netpay' ){
		//consulta el usuario y password de las APIS
			$sql = "SELECT 
						usuario_api AS API_USER,
						password_api AS API_PASSWORD
					FROM ec_tipos_bancos
					WHERE id_tipo_banco = 2";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los parametros de token para API : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$user = $row['API_USER'];
			$password = $row['API_PASSWORD'];
			$apiUrl = $this->getEndpoint( $terminal_id, 'endpoint_token' );//obtiene url de api token
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $apiUrl,
			  	CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => "grant_type={$grantType}&username={$user}&password={$password}",
				/*CURLOPT_POSTFIELDS => "grant_type=password&username=Nacional&password=netpay",*/
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/x-www-form-urlencoded",
					"Authorization: Basic dHJ1c3RlZC1hcHA6c2VjcmV0"
				),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			//var_dump($response);
			curl_close($curl);
			$result = json_decode( $response );
			//die( "result : {$result}" );
			//var_dump($result);
		//guarda el token en la base de datos
			$sql = "INSERT INTO vf_tokens_terminales_netpay( id_token_terminal, id_razon_social, access_token, token_type, 
				refresh_token, expires_in, scope, jti ) VALUES ( NULL, '{$terminal_id}', '{$result->access_token}', '{$result->token_type}', 
				'{$result->refresh_token}', '{$result->expires_in}', '{$result->scope}', '{$result->jti}' )";
			$this->link->query( $sql ) or die( "Error al insertar el token en la base de datos : {$this->link->error}" );
			
			$response = $this->getToken( $terminal_id );
			return $response;
		}

		public function getToken( $terminal_id ){
			$sql = "SELECT 
						id_token_terminal,
						id_razon_social,
						access_token,
						token_type,
						refresh_token,
						expires_in,
						scope,
						jti
					FROM vf_tokens_terminales_netpay
					WHERE id_razon_social = '{$terminal_id}'
					ORDER BY id_token_terminal DESC
					LIMIT 1";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el token corresponsiente de la terminal!" );
			$row = $stm->fetch_assoc();
			return $row;
		}
	//renovacion de token
		public function refreshToken( $token, $terminal_id ){
			$apiUrl = $this->getEndpoint( $terminal_id, 'endpoint_token' );//obtiene url de api token
			$refresh_token = $token['refresh_token'];
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "grant_type=refresh_token&refresh_token={$refresh_token}",
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/x-www-form-urlencoded",
			    "Authorization: Basic dHJ1c3RlZC1hcHA6c2VjcmV0"
			  ),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode( $response );
			//var_dump($result);
		//guarda el token en la base de datos
			$sql = "INSERT INTO vf_tokens_terminales_netpay( id_token_terminal, id_razon_social, access_token, token_type, 
				refresh_token, expires_in, scope, jti ) VALUES ( NULL, '{$terminal_id}', '{$result->access_token}', '{$result->token_type}', 
				'{$result->refresh_token}', '{$result->expires_in}', '{$result->scope}', '{$result->jti}' )";
			$this->link->query( $sql ) or die( "Error al insertar el token en la base de datos : {$this->link->error}" );
			
			$response = $this->getToken( $terminal_id );
			return $response;
			//return $response;
		}
		public function insertNetPetitionRow(){
			$sql = "INSERT INTO vf_transacciones_netpay ( id_transaccion_netpay ) VALUES ( NULL )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el id de transaccion netPay : {$this->link->error}" );
			$sql = "SELECT LAST_INSERT_ID()";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el ultimo id insertado : {$this->link->error}" );
			$row = $stm->fetch_row();
			return $row[0];
		}
		public function getTerminal( $terminal_id, $store_id ){
			$sql = "SELECT 
						tis.numero_serie_terminal AS terminal_serie,
						tis.imprimir_ticket AS print_ticket,
						tis.store_id AS store_id
						/*rse.store_id_netpay AS store_id*/
					FROM ec_terminales_integracion_smartaccounts tis
					LEFT JOIN ec_terminales_sucursales_smartaccounts tss
					ON tss.id_terminal = tis.id_terminal_integracion
					LEFT JOIN vf_razones_sociales_emisores rse
					ON rse.id_razon_social = tss.id_razon_social
					WHERE tis.id_terminal_integracion = {$terminal_id}
					OR tis.numero_serie_terminal = {$terminal_id}
					AND tss.id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la terminal : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
	//peticion de venta
		public function salePetition(  $apiUrl, $amount = 0.01, $terminal_id, $user_id, $store_id, $sale_folio, $session_id, $id_devolucion_relacionada = 0 ){
			$terminal = $this->getTerminal( $terminal_id, $store_id );
			//var_dump( $terminal );
			$token = $this->getToken( $terminal['terminal_serie'] );
			//var_dump( $token );
			//return '';
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal['terminal_serie'], 'password', 'smartPos', 'netpay' );
			}
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( 
						"traceability"=>array(  
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>"{$terminal['store_id']}",
							"id_devolucion_relacionada"=>$id_devolucion_relacionada
						),
			            "serialNumber"=>"{$terminal['terminal_serie']}",
			            "amount"=> $amount,
			            "folioNumber"=> "{$petition_id}",
			            /*"storeId"=>"9194",*/
			            /*"storeId"=>"{$this->NetPayStoreId}",*/
			            "storeId"=>"{$terminal['store_id']}",
   						"isSmartAccounts"=>"true",
						"disablePrintAnimation"=> ( $terminal['print_ticket'] == 1 ? false : true ) );
			//var_dump($data);
			//die( '' );
			$post_data = json_encode( $data, true );
/*Escribir json en txt*/
$file = fopen("debug.txt", "w");
fwrite($file, $post_data);
fclose($file);
/**/
		//envia peticion
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,//"http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $post_data,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/json",
			    "Authorization: Bearer {$token['access_token']}"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			$result = json_decode( $response );//json_encode(),
			$result->petition_id = $petition_id; 
			//var_dump($response);die('');
			if( isset( $result->error ) ){
				if( $result->error == 'invalid_token' ){//token expirado
					//die( 'here' );
					//$this->refreshToken( $token, $terminal['terminal_serie'] );
					$sql = "DELETE FROM vf_tokens_terminales_netpay";
					$stm = $this->link->query( $sql ) or die( "Error al eliminar tokens : {$this->link->error}" );
					return $this->salePetition( $apiUrl, $amount = 0.01, $terminal['terminal_serie'], $user_id, 
										$store_id, $sale_folio, $session_id, $id_devolucion_relacionada );
					return false;
				}
			}
			$result = json_encode( $result, true );
			//die( 'here' );
			return $result;
			//return $response;
		}
	//cancelacion de cobro
		public function saleCancelation( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, $session_id, $store_id_netpay ){
			$token = $this->getToken( $terminal );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal, 'password', 'smartPos', 'netpay' );
			}

			$terminal_data = $this->getTerminal( $terminal, $store_id );
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"petition_id"=>"{$petition_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay
						),
			            "serialNumber"=>"{$terminal}",
			            "orderId"=> $orderId,
			            /*"storeId"=>"9194",
			            "storeId"=>"{$this->NetPayStoreId}",*/
			            "storeId"=>"{$store_id_netpay}",
   						"isSmartAccounts"=>"true",
						"disablePrintAnimation"=>false
					);
			$post_data = json_encode( $data, true );
		//envia peticion
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,//"http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $post_data,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/json",
			    "Authorization: Bearer {$token['access_token']}"
			  ),
			));

			/*$response = curl_exec($curl);
			curl_close($curl);
			return $response;*/
			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode( $response );//json_encode(),
			$result->petition_id = $petition_id; 
			//var_dump($response);
			//die( '' );
			$result = json_encode( $result, true );
			return $result;
		}

		//}
	//reimpresion de cobro
		public function saleReprint( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, $session_id, $store_id_netpay ){
			$token = $this->getToken( $terminal );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal, 'password', 'smartPos', 'netpay' );
			}
			$terminal_data = $this->getTerminal( $terminal, $store_id );
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"petition_id"=>"{$petition_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay
						),
			            "serialNumber"=>"{$terminal}",
			            "orderId"=> $orderId,
			            /*"storeId"=>"9194",
			            "storeId"=>"{$this->NetPayStoreId}",*/
			            "storeId"=>"{$store_id_netpay}",
   						"isSmartAccounts"=>"true",
						"disablePrintAnimation"=>false
					);
			//var_dump( $data );return '';
			$post_data = json_encode( $data, true );
		//envia peticion
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,//"http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $post_data,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/json",
			    "Authorization: Bearer {$token['access_token']}"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode( $response );//json_encode(),
			$result->petition_id = $petition_id; 
			//var_dump($response);
			//die( '' );
			$result = json_encode( $result, true );
			return $result;
		}

	//reversado
		public function Reverse( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, $session_id, $store_id_netpay ){
			$token = $this->getToken( $terminal );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal, 'password', 'smartPos', 'netpay' );
			}
			$terminal_data = $this->getTerminal( $terminal, $store_id );
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"petition_id"=>"{$petition_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay
						),
			            "serialNumber"=>"{$terminal}",
			            "orderId"=> $orderId,
					    "folioId"=>"{{folioId}}",
			            /*"storeId"=>"9194",
			            "storeId"=>"{$this->NetPayStoreId}",*/
			            "storeId"=>"{$store_id_netpay}",
   						"isSmartAccounts"=>"true",
						"disablePrintAnimation"=>false
					);
			//var_dump( $data );return '';
			$post_data = json_encode( $data, true );
		//envia peticion
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,//"http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $post_data,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/json",
			    "Authorization: Bearer {$token['access_token']}"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode( $response );//json_encode(),
			$result->petition_id = $petition_id; 
			//var_dump($response);
			//die( '' );
			$result = json_encode( $result, true );
			return $result;
		}
	}
?>