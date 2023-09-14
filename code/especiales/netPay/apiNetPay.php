<?php
	include( '../../../conexionMysqli.php' );
	//$apiNetPay = new apiNetPay( $link );
	//echo $apiNetPay->requireToken();
	//echo $apiNetPay->getToken( '1494113052' );
	//echo $apiNetPay->refreshToken();
	/*$apiUrl = "http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale";
	$amount = 10.00;
	echo $apiNetPay->salePetition( $apiUrl, $amount );*/
	//$apiUrl = "http://nubeqa.netpay.com.mx:3334/integration-service/transactions/cancel";
	//$orderId = "230907115744-1494113052";
	//echo $apiNetPay->saleCancelation( $apiUrl, $orderId );
	class apiNetPay{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}
	//generacion de token
		public function requireToken( $terminal_id = '1494113052', $grantType = 'password', $user = 'Nacional', $password = 'netpay' ){
		//recupera el id de la terminal
		//	$sql = "SELECT numero_serie";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://nubeqa.netpay.com.mx:3334/oauth-service/oauth/token",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "grant_type={$grantType}&username={$user}&password={$password}",
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/x-www-form-urlencoded",
			    "Authorization: Basic dHJ1c3RlZC1hcHA6c2VjcmV0"
			  ),
			));

			$response = curl_exec($curl);
			//var_dump($response);
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
		//recuperar refresh token
			//$refresh_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsib2F1dGgyX2lkIl0sInVzZXJfbmFtZSI6ImludGVncmFjaW9uZXNAbmV0cGF5LmNvbS5teCIsInNjb3BlIjpbInJlYWQiLCJ3cml0ZSJdLCJhdGkiOiJhYWE3NDdiOC0zNGI2LTQzODUtYmUyMC01NzFmZDE1Y2Q2MTYiLCJleHAiOjE2OTQyMjUwMTAsImF1dGhvcml0aWVzIjpbIlJPTEVfVVNFUiJdLCJqdGkiOiJhY2EyMmEzMy05NDVlLTQyYzgtYjE4Ni1hNjczOTVhY2UzYmMiLCJjbGllbnRfaWQiOiJ0cnVzdGVkLWFwcCJ9.ha9zOM-pYQBgO2YtQsy-lbmV8ZNMa7FNsGfjcj1NymWgZO4lpTrHN1PT9Z47aCexC_pK655utkil2qpcJxHJ4IjiT5l_zrDKBHkuNqIQt0wwFpIOnN_Ml9tqLISBucBmykFeNJcMS0Sh4ZkleO6QpLqJ8L_PqAmmtlwMAwFgZEWZSjFl9VIEyasrMkL2N7hYvMVloDCLc9R5iVYNL4GUJ2KtnsjxqN9DG-WI0sCGHe_mxzAIf2_OHSSSXr0AC7KOVvd6mXvY9Nx8B0Uu9oE0HxB1q3Rh4X1hn2L7kAre2pwM4ZTEiuSxAOEUDpfAtl-z2Lfi_yBli3EP1NdFFZTagQ";
			$refresh_token = $token['refresh_token'];
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://nubeqa.netpay.com.mx:3334/oauth-service/oauth/token",
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
		public function getTerminal( $terminal_id ){
			$sql = "SELECT 
						numero_serie_terminal AS terminal_serie,
						imprimir_ticket AS print_ticket
					FROM ec_afiliaciones
					WHERE id_afiliacion = {$terminal_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la terminal : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
	//peticion de venta
		public function salePetition(  $apiUrl, $amount = 0.01, $terminal_id, $user_id, $store_id, $sale_folio, $session_id ){
			
			$terminal = $this->getTerminal( $terminal_id );
			//var_dump( $terminal );
			$token = $this->getToken( $terminal['terminal_serie'] );
			//var_dump( $token );
			//return '';
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal['terminal_serie'], 'password', 'Nacional', 'netpay' );
			}
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( 
						"traceability"=>array(  
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}"
						),
			            "serialNumber"=>"{$terminal['terminal_serie']}",
			            "amount"=> $amount,
			            "folioNumber"=> "{$petition_id}",
			            "storeId"=>"9194",
						"disablePrintAnimation"=> ( $terminal['print_ticket'] == 1 ? false : true ) );
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
			//var_dump($response);die('');
			if( isset( $result->error ) ){
				if( $result->error == 'invalid_token' ){//token expirado
					//die( 'here' );
					$this->refreshToken( $token, $terminal['terminal_serie'] );
					return $this->salePetition( $apiUrl, $amount = 0.01, $terminal['terminal_serie'], $user_id, 
										$store_id, $sale_folio, $session_id );//$terminal_id = '1494113052'
				}
			}
			$result = json_encode( $result, true );
			//die( 'here' );
			return $result;
			//return $response;
		}
	//cancelacion de cobro
		public function saleCancelation( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, $session_id ){
			$token = $this->getToken( $terminal );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal, 'password', 'Nacional', 'netpay' );
			}
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"petition_id"=>"{$petition_id}"
						),
			            "serialNumber"=>"{$terminal}",
			            "orderId"=> $orderId,
			            "storeId"=>"9194",
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
		public function saleReprint( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, $session_id ){
			$token = $this->getToken( $terminal );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal, 'password', 'Nacional', 'netpay' );
			}
			$petition_id = $this->insertNetPetitionRow();
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"petition_id"=>"{$petition_id}"
						),
			            "serialNumber"=>"{$terminal}",
			            "orderId"=> $orderId,
			            "storeId"=>"9194",
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