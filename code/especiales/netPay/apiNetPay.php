<?php
	include( '../../../conexionMysqli.php' );

	class apiNetPay{
		private $link;
		private $store_id;
		private $NetPayStoreId;
		private $system_type;
		private $Logger;
		function __construct( $connection, $store_id, $system_type, $Logger = null )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
			$this->system_type = $system_type;
			$this->Logger = $Logger;
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
		public function getEndpoint( $terminal_id, $endpoint_type, $log_id = null ){
			$steep_log_id = 0;
			$sql = "SELECT 
						{$endpoint_type} AS endpoint
					FROM ec_terminales_integracion_smartaccounts tis 
					LEFT JOIN ec_tipos_bancos tb
					ON tis.id_tipo_terminal = tb.id_tipo_banco
					WHERE tis.id_terminal_integracion = '{$terminal_id}'
					OR tis.numero_serie_terminal = '{$terminal_id}'";//die( $sql );
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta el endpoint de la integracion SmartAccounts", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_terminales_integracion_smartaccounts', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar endpoint {$endpoint_type} : {$this->link->error}" );
			}
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

		public function getToken( $terminal_id, $log_id = null ){
			$steep_log_id = 0;
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
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta el token correspondiente de la terminal", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'vf_tokens_terminales_netpay', 
					'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar el token correspondiente de la terminal!" );
			}
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

		public function insertNetPetitionRow( $user_id, $store_id, $terminal_id, $store_id_netpay, $sale_folio, $log_id = null ){
			$steep_log_id = 0;
		//consulta token
			$sql = "SELECT token FROM api_token WHERE id_user = {$user_id} and expired_in > now() limit 1";//-1
			$stm = $this->link->query($sql);
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta token de usario en api token", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'api_token', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar el token : {$this->link->error}" );
			}
			$respuesta = $stm->fetch_assoc();
			$token = $respuesta['token'];
		//consuta path de API linea
			$sql = "SELECT 
						prefijo,
						(SELECT value FROM api_config WHERE `name` = 'path' ) AS api_path
					FROM sys_sucursales 
					WHERE acceso = 1";
			$stm = $this->link->query( $sql );// 
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta prefijo de sucursal y path de API para generar el folio unico ", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'sys_sucursales', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar prefijo de sucursal y path de API para generar el folio unico : {$this->link->error}" );
			}
			$row = $stm->fetch_assoc();
			$prefix = $row['prefijo'];
			$path_api = $row['api_path'];

		//consume el webservice para insertar la peticion servidor en linea	
			$post_data = json_encode( array( "id_usuario"=>"{$user_id}", "id_sucursal"=>"{$store_id}", "terminal_id"=>"{$terminal_id}", "store_id_netpay"=>"{$store_id_netpay}", "sale_folio"=>$sale_folio ) );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Envia peticion a api : {$path_api}/rest/netPay/insertar_peticion_transaccion ", "{$post_data}" );
			}
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => "{$path_api}/rest/netPay/insertar_peticion_transaccion",
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
				"token: {$token}"
				)
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode( $response );//json_encode(),
			//var_dump($result);
			if( $result->status != '200' && $result->status != 200 ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'sys_sucursales', 'N/A', "{$post_data}", "Error al consumir API para insertar peticion de netPay en servidor linea : {$path_api}/rest/netPay/insertar_peticion_transaccion" );
				}
				var_dump( $result );
				die( "Error al consumir API para insertar peticion de netPay en servidor linea : {$path_api}" . $result->message );
			}
			$folio_transaccion = $result->folio_unico_transaccion;
			$sql = "INSERT INTO vf_transacciones_netpay ( folio_unico, id_cajero, id_sucursal, terminalId, store_id_netpay, folio_venta ) 
					VALUES ( '{$folio_transaccion}', '{$user_id}', '{$store_id}', '{$terminal_id}', '{$store_id_netpay}', '{$sale_folio}' )";
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$sql = "UPDATE LOG_cobros SET folio_unico_cobro = '{$folio_transaccion}' WHERE id_log_cobro = {$log_id}";
				$stm_log = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del log de cobros : {$sql} : {$this->link->error}" );
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta el id de transaccion netPay en servidor origen", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', 'N/A', $sql, $this->link->error );
				}
				die( "Error al insertar el id de transaccion netPay en servidor origen : {$this->link->error}" );
			}
			return $folio_transaccion;
		}
		public function getTerminal( $terminal_id, $store_id, $log_id = null ){
			$steep_log_id = 0;
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
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta datos de la terminal", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_terminales_integracion_smartaccounts / ec_terminales_sucursales_smartaccounts / vf_razones_sociales_emisores', 
					'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar datos de la terminal : {$sql} {$this->link->error}" );
			}
			$row = $stm->fetch_assoc();
			return $row;
		}
	//peticion de venta
		public function salePetition(  $apiUrl, $amount = 0.01, $terminal_id, $user_id, $store_id, $sale_folio, $session_id, $id_devolucion_relacionada = 0, $log_id = null ){
			$steep_log_id = 0;
			$terminal = $this->getTerminal( $terminal_id, $store_id, $log_id );
			//var_dump( $terminal );
			$token = $this->getToken( $terminal['terminal_serie'], $log_id );
			//var_dump( $token );
			//return '';
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $terminal['terminal_serie'], 'password', 'smartPos', 'netpay' );
			}
			$folio_unico_transaccion = $this->insertNetPetitionRow( $user_id, $store_id, $terminal['terminal_serie'], $terminal['store_id'], $sale_folio, $log_id );
		//arreglo de prueba
			$data = array( 
						"traceability"=>array(  
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>"{$terminal['store_id']}",
							"id_devolucion_relacionada"=>$id_devolucion_relacionada,
							"tipo_sistema"=>$this->system_type,
							"folio_unico_transaccion"=>"{$folio_unico_transaccion}"
						),
			            "serialNumber"=>"{$terminal['terminal_serie']}",
			            "amount"=> $amount,
			            "folioNumber"=> "{$folio_unico_transaccion}",
			            /*"storeId"=>"9194",*/
			            /*"storeId"=>"{$this->NetPayStoreId}",*/
			            "storeId"=>"{$terminal['store_id']}",
   						"isSmartAccounts"=>"true",
						"disablePrintAnimation"=> ( $terminal['print_ticket'] == 1 ? false : true ) );
			//var_dump($data);
			//die( '' );
			$post_data = json_encode( $data, true );
/*Escribir json en txt*/
$file = fopen("salePetition.txt", "w");
fwrite($file, $post_data);
fclose($file);
/**/
		//envia peticion
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Envia peticion a api netPay : {$apiUrl}", "{$post_data}" );
			}
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
			$result->folio_unico_transaccion = $folio_unico_transaccion; 
			//var_dump($result);die('');
			if( isset( $result->error ) ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', 'N/A', "{$post_data}", "Error al consumir API de NETPAY : {$apiUrl} : {$response}" );
				}
				//var_dump($result->error);die('here');
				if( $result->error == 'invalid_token' ){//token expirado
					$sql = "DELETE FROM vf_tokens_terminales_netpay";
					$stm = $this->link->query( $sql );//
				/*Logger*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Elimina token caducado ( NETPAY )", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
							$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'vf_tokens_terminales_netpay', 
							'N/A', $sql, $this->link->error );
						}
						die( "Error al eliminar token caducado ( NETPAY ) : {$sql} {$this->link->error}" );
					}
					return $this->salePetition( $apiUrl, $amount = 0.01, $terminal['terminal_serie'], $user_id, 
										$store_id, $sale_folio, $session_id, $id_devolucion_relacionada, $log_id );
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
			$folio_unico_transaccion = $this->insertNetPetitionRow( $user_id, $store_id, $terminal_data['terminal_serie'], $terminal_data['store_id'], $sale_folio );
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay,
							"tipo_sistema"=>$this->system_type,
							"folio_unico_transaccion"=>"{$folio_unico_transaccion}"
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
			$file = fopen("cancel.txt", "w");
			fwrite($file, $post_data);
			fclose($file);
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
			$result->folio_unico_transaccion = $folio_unico_transaccion; 
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
			$terminal_data = $this->getTerminal( $terminal, $store_id );//var_dump( $terminal_data['store_id'] );die('');
			$folio_unico_transaccion = $this->insertNetPetitionRow( $user_id, $store_id, $terminal_data['terminal_serie'], $terminal_data['store_id'], $sale_folio );
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay,
							"tipo_sistema"=>$this->system_type,
							"folio_unico_transaccion"=>"{$folio_unico_transaccion}"
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
			$file = fopen("reprint.txt", "w");
			fwrite($file, $post_data);
			fclose($file);
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
			$result->folio_unico_transaccion = $folio_unico_transaccion; 
			//var_dump($response);
			//die( '' );
			$result = json_encode( $result, true );
			return $result;
		}

	//obtiene status de transaccion por folio
		public function getStatusByFolio( $folio_unico_transaccion ){//consulta datos de la transaccion
			$sql = "SELECT
						terminalId,
						store_id_netpay
					FROM vf_transacciones_netpay
					WHERE folio_unico = '{$folio_unico_transaccion}'";
			$stm = $this->link->query( $sql );
			if( $this->link->error ){
				return json_encode( array( "status"=>400, "error"=>$this->link->error) );
			}
			$request_info = $stm->fetch_assoc();
			$apiUrl = $this->getEndpoint( $request_info['terminalId'], 'endpoint_reimpresion' );//obtiene url de api token
			$token = $this->getToken( $request_info['terminalId'] );
			if( sizeof($token) == 0 || $token == null ){
				$token = $this->requireToken( $request_info['terminalId'], 'password', 'smartPos', 'netpay' );
			}
			
		//arreglo de prueba
			$data = array( "serialNumber"=>"{$request_info['terminalId']}", 
						"orderId"=>"", 
						"folioId"=>"{$folio_unico_transaccion}", 
						"storeId"=>"{$request_info['store_id_netpay']}", 
						"isSmartAccounts"=>true,
						"disablePrintAnimation"=>true
					);
			//var_dump( $data );return '';
			$post_data = json_encode( $data, true );
			$file = fopen("reprintByFolio.txt", "w");
			fwrite($file, $post_data);
			fclose($file);
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
			$result->folio_unico_transaccion = $folio_unico_transaccion; 
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
			$folio_unico_transaccion = $this->insertNetPetitionRow( $user_id, $store_id, $terminal_data['terminal_serie'], $terminal_data['store_id'], $sale_folio );
		//arreglo de prueba
			$data = array( "traceability"=>array(   
							"id_sucursal"=>"{$store_id}", 
							"id_cajero"=>"{$user_id}", 
							"folio_venta"=>"{$sale_folio}", 
							"id_sesion_cajero"=>"{$session_id}",
							"smart_accounts"=>true,
							"store_id_netpay"=>$store_id_netpay,
							"tipo_sistema"=>$this->system_type,
							"folio_unico_transaccion"=>"{$folio_unico_transaccion}"
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
			$result->folio_unico_transaccion = $folio_unico_transaccion; 
			//var_dump($response);
			//die( '' );
			$result = json_encode( $result, true );
			return $result;
		}
	}
?>