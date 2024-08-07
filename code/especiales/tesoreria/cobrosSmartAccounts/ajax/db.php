<?php
/*version 1.2 2024-07-04 Hacer configurable el tiempo de espera de respuesta del websocket*/
	if( isset( $_GET['fl'] ) || isset( $_POST['fl'] ) ){
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		include('./Logger.php');
		
		$sql = "SELECT id_sucursal AS system_type FROM sys_sucursales WHERE acceso = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar el tipo de sistema : {$link->error}" );
		$row = $stm->fetch_assoc();
		$system_type = $row['system_type'];
		include( '../../../netPay/apiNetPay.php' );
		$Logger = null;
		$log_id = null;
		$log = ( isset( $_GET['log_status'] ) ? $_GET['log_status'] : $_POST['log_status'] );
		if( $log == 1 ){
			$Logger = new Logger( $link );//instancia de clase Logger
		}
		$apiNetPay = new apiNetPay( $link, $sucursal_id, $system_type, ( $Logger == null ? null : $Logger ) );
		$Payments = new Payments( $link, $user_sucursal, ( $Logger == null ? null : $Logger ) );
		$action = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
		switch ( $action ) {
			case 'getSaleData':
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				$sale_folio = ( isset( $_GET['folio'] ) ? $_GET['folio'] : $_POST['folio'] );
				if( $Logger != null ){
					$log_ = $Logger->insertLoggerRow( $sale_folio, $user_id, 'ec_pedidos/ec_cajero_cobros/ec_pedido_pagos/ec_devolucion_pagos', $system_type, ( $system_type != -1 ? $sucursal_id : -1 ) );//inserta cabecera de log
					$log_id = $log_['id_log'];
				}
				$Payments->getSaleData( $sale_id, $sale_folio, $user_id, $log_id );
			break;
			case 'sendPaymentPetition' :
				if( $Logger != null ){
					$log_ = $Logger->insertLoggerRow( 'N/A', $user_id, 'vf_transacciones_netPay', $system_type, ( $system_type != -1 ? $sucursal_id : -1 ) );//inserta cabecera de log
					$log_id = $log_['id_log'];
				}
				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_venta', $log_id );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/sale";//http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale
				//die( 'here : ' . $apiUrl );
			//recibe variables
				$amount = ( isset( $_GET['amount'] ) ? $_GET['amount'] : $_POST['amount'] );
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );

				$pago_por_saldo_a_favor = 0;
				$id_venta_origen = 0;
				if( isset( $_GET['pago_por_saldo_a_favor'] ) || isset( $_POST['pago_por_saldo_a_favor'] ) ){
					$pago_por_saldo_a_favor = ( isset( $_GET['pago_por_saldo_a_favor'] ) ? $_GET['pago_por_saldo_a_favor'] : $_POST['pago_por_saldo_a_favor'] );
				}
				if( isset( $_GET['id_venta_origen'] ) || isset( $_POST['id_venta_origen'] ) ){
					$id_venta_origen = ( isset( $_GET['id_venta_origen'] ) ? $_GET['id_venta_origen'] : $_POST['id_venta_origen'] );
				}
				
		
				$validation = $Payments->validate_payment_is_not_bigger( $sale_id, $amount, $log_id );
				$terminal_id = ( isset( $_GET['terminal_id'] ) ? $_GET['terminal_id'] : $_POST['terminal_id'] );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : $_POST['counter'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$id_devolucion_relacionada = 0;
				$id_devolucion_relacionada = ( isset( $_GET['id_devolucion_relacionada'] ) ? $_GET['id_devolucion_relacionada'] : $_POST['id_devolucion_relacionada'] );
			//consume servicio de venta
				$req = $apiNetPay->salePetition( $apiUrl, $amount, $terminal_id, $user_id, 
					$sucursal_id, $sale_folio, $session_id, $id_devolucion_relacionada, $log_id );
	//die("here");
				$resp = json_decode( $req );
				if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
					$transaction_id = $resp->petition_id;
					$is_payment_petition = true;
					include( '../vistas/formularioNetPay.php' );
					echo "<script type=\"text/JavaScript\">
						console.log( \"Si entra bien\" );
							setTimeout( function (){ 
								informar_folio( '{$resp->folio_unico_transaccion}' );
							}, 1000 );
						</script>";
				}else{
					die( "<div class=\"row text-center\">
							<h2 class=\"text-center\">Ocurrio un error :</h2>
							<h4>Codigo : {$resp->code}</h4>
							<h4>Mensaje : {$resp->message}</h4>
							<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circle\">Aceptar y cerrar</i>
							</button>
						</div>" );
				}
				return '';
			break;

			case 'getTerminals' :
				$user_id = ( isset( $_GET['user_id'] ) ? $_GET['user_id'] : $_POST['user_id'] );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : $_POST['counter'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->getTerminals( $user_id, $counter, $user_sucursal, $session_id );
			break;

			case 'cancelEvents' :
				$transaction_id = ( isset( $_GET['transaction_id'] ) ? $_GET['transaction_id'] : $_POST['transaction_id'] );
				echo $Payments->cancelEvents( $transaction_id );
			break;

			case 'rePrintByOrderId' :
				$transaction_id = ( isset( $_GET['transaction_id'] ) ? $_GET['transaction_id'] : $_POST['transaction_id'] );
				$data = $Payments->getOrderResponse( $transaction_id );
				//$apiNetPay = new apiNetPay( $link );
				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$terminal_id = $data['terminalId'];// ( isset( $_GET['terminal_id'] ) ? $_GET['terminal_id'] : $_POST['terminal_id'] );
				$store_id_netpay = $data['store_id_netpay'];
				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_reimpresion' );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/reprint";//http://nubeqa.netpay.com.mx:3334/integration-service/transactions/reprint";
				//die( $apiUrl );
				$print = $apiNetPay->saleReprint( $apiUrl, $data['orderId'], $data['terminalId'],
										$user_id, $sucursal_id, $sale_folio, $session_id, $store_id_netpay );
				//saleReprint( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, session_id )
				$resp = json_decode( $print );
				if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
					$counter = 'null';
					include( '../vistas/formularioNetPay.php' );
				}else{
					die( "<div class=\"row text-center\">
							<h2 class=\"text-center\">Ocurrio un error :</h2>
							<h4>Codigo : {$resp->code}</h4>
							<h4>Mensaje : {$resp->message}</h4>
							<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circle\">Aceptar y cerrar</i>
							</button>
						</div>" );
				}
				return '';
				//return $print;
			break;
			case 'rePrintByOrderIdManual' :
				$orderId = ( isset( $_GET['orderId'] ) ? $_GET['orderId'] : $_POST['orderId'] );
				$data = $Payments->getOrderResponse( $orderId, true );
				//$apiNetPay = new apiNetPay( $link );
				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				
				$terminal_id = $data['terminalId'];
				$store_id_netpay = $data['store_id_netpay'];

				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_reimpresion' );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/reprint";//http://nubeqa.netpay.com.mx:3334/integration-service/transactions/reprint";
				$print = $apiNetPay->saleReprint( $apiUrl, $data['orderId'], $data['terminalId'],
										$user_id, $sucursal_id, $sale_folio, $session_id, $store_id_netpay );
				//saleReprint( $apiUrl, $orderId, $terminal, $user_id, $store_id, $sale_folio, session_id )
				$resp = json_decode( $print );
				if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
					$counter = 'null';
					include( '../vistas/formularioNetPay.php' );
				}else{
					die( "<div class=\"row text-center\">
							<h2 class=\"text-center\">Ocurrio un error :</h2>
							<h4>Codigo : {$resp->code}</h4>
							<h4>Mensaje : {$resp->message}</h4>
							<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circle\">Aceptar y cerrar</i>
							</button>
						</div>" );
				}
				return '';
				//return $print;
			break;
			case 'cancelByOrderId' :
				$transaction_id = ( isset( $_GET['transaction_id'] ) ? $_GET['transaction_id'] : $_POST['transaction_id'] );
				$data = $Payments->getOrderResponse( $transaction_id );

				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				//echo $Payments->cancelByOrderId( $transaction_id );
				//include( '../../../../netPay/apiNetPay.php' );
				//$apiNetPay = new apiNetPay( $link );
				$terminal_id = $data['terminalId'];
				$store_id_netpay = $data['store_id_netpay'];
				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_cancelacion' );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/cancel";//"http://nubeqa.netpay.com.mx:3334/integration-service/transactions/cancel";
				$cancel = $apiNetPay->saleCancelation( $apiUrl, $data['orderId'], $data['terminalId'],
										$user_id, $sucursal_id, $sale_folio, $session_id, $store_id_netpay );
			//	die("pasa : {$cancel}");
				$resp = json_decode( $cancel );
				if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
					$counter = 'null';
					include( '../vistas/formularioNetPay.php' );
				}else{
					die( "<div class=\"row text-center\">
							<h2 class=\"text-center\">Ocurrio un error :</h2>
							<h4>Codigo : {$resp->code}</h4>
							<h4>Mensaje : {$resp->message}</h4>
							<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circle\">Aceptar y cerrar</i>
							</button>
						</div>" );
				}
				return '';
				//return $cancel;
			break;
/*implementacion Oscar 2023/10/10*/
			case 'insertCashPayment' : 
				$ammount = ( isset( $_GET['ammount'] ) ? $_GET['ammount'] : $_POST['ammount'] );
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				$ammount_permission = 0;
				$pago_por_saldo_a_favor = 0;
				$id_venta_origen = 0;
				$id_devolucion_relacionada = 0;
				$tipo_pago = 1;
				$id_caja_cuenta = -1;
				if( isset( $_GET['pago_por_saldo_a_favor'] ) || isset( $_POST['pago_por_saldo_a_favor'] ) ){
					$pago_por_saldo_a_favor = ( isset( $_GET['pago_por_saldo_a_favor'] ) ? $_GET['pago_por_saldo_a_favor'] : $_POST['pago_por_saldo_a_favor'] );
				}
				if( isset( $_GET['id_venta_origen'] ) || isset( $_POST['id_venta_origen'] ) ){
					$id_venta_origen = ( isset( $_GET['id_venta_origen'] ) ? $_GET['id_venta_origen'] : $_POST['id_venta_origen'] );
				}
				if( isset( $_GET['ammount_permission'] ) || isset( $_POST['ammount_permission'] ) ){
					$ammount_permission = ( isset( $_GET['ammount_permission'] ) ? $_GET['ammount_permission'] : $_POST['ammount_permission'] );
				}
				if( isset( $_GET['id_devolucion_relacionada'] ) || isset( $_POST['id_devolucion_relacionada'] ) ){
					$id_devolucion_relacionada = ( isset( $_GET['id_devolucion_relacionada'] ) ? $_GET['id_devolucion_relacionada'] : $_POST['id_devolucion_relacionada'] );
				}
				if( isset( $_GET['tipo_pago'] ) || isset( $_POST['tipo_pago'] ) ){
					$tipo_pago = ( isset( $_GET['tipo_pago'] ) ? $_GET['tipo_pago'] : $_POST['tipo_pago'] );
				}
				if( isset( $_GET['id_caja_cuenta'] ) || isset( $_POST['id_caja_cuenta'] ) ){
					$id_caja_cuenta = ( isset( $_GET['id_caja_cuenta'] ) ? $_GET['id_caja_cuenta'] : $_POST['id_caja_cuenta'] );
				}
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				//if ( $ammount_permission == 0 ) {
					$validation = $Payments->validate_payment_is_not_bigger( $sale_id, $ammount );
				//}
				echo $Payments->insertCashPayment( $ammount, $sale_id, $user_id, $session_id, $pago_por_saldo_a_favor, $id_venta_origen, 
					$id_devolucion_relacionada, $tipo_pago, $id_caja_cuenta );
			break;

			case 'getHistoricPayment' :
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				echo $Payments->getHistoricPayment( $sale_id );
			break;

			case 'seekTerminalByQr' :
				$qr_txt = ( isset( $_GET['qr_txt'] ) ? $_GET['qr_txt'] : $_POST['qr_txt'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->seekTerminalByQr( $qr_txt, $sucursal_id, $session_id );

			break;

			case 'setPaymentWhithouthIntegration' :
				$afiliation_id = ( isset( $_GET['afiliation_id'] ) ? $_GET['afiliation_id'] : $_POST['afiliation_id'] );
				$ammount = ( isset( $_GET['ammount'] ) ? $_GET['ammount'] : $_POST['ammount'] );
				$authorization_number = ( isset( $_GET['authorization_number'] ) ? $_GET['authorization_number'] : $_POST['authorization_number'] );
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );

				$is_per_error = ( isset( $_GET['is_per_error'] ) ? $_GET['is_per_error'] : $_POST['is_per_error'] );
				
				$validation = $Payments->validate_payment_is_not_bigger( $sale_id, $ammount );
				$id_devolucion_relacionada = 0;
				if( isset( $_GET['id_devolucion_relacionada'] ) || isset( $_POST['id_devolucion_relacionada'] ) ){
					$id_devolucion_relacionada = ( isset( $_GET['id_devolucion_relacionada'] ) ? $_GET['id_devolucion_relacionada'] : $_POST['id_devolucion_relacionada'] );
				}

				$pago_por_saldo_a_favor = 0;
				$id_venta_origen = 0;
				if( isset( $_GET['pago_por_saldo_a_favor'] ) || isset( $_POST['pago_por_saldo_a_favor'] ) ){
					$pago_por_saldo_a_favor = ( isset( $_GET['pago_por_saldo_a_favor'] ) ? $_GET['pago_por_saldo_a_favor'] : $_POST['pago_por_saldo_a_favor'] );
				}
				if( isset( $_GET['id_venta_origen'] ) || isset( $_POST['id_venta_origen'] ) ){
					$id_venta_origen = ( isset( $_GET['id_venta_origen'] ) ? $_GET['id_venta_origen'] : $_POST['id_venta_origen'] );
				}

				echo $Payments->setPaymentWhithouthIntegration( $afiliation_id, $ammount, $authorization_number, $is_per_error, $sale_id, $session_id, $user_id, $pago_por_saldo_a_favor, $id_venta_origen, $id_devolucion_relacionada );
			break;

			case 'getTicketsToReprint' :
				$key = ( isset( $_GET['key'] ) ? $_GET['key'] : '' );
				echo $Payments->getLastTickets( $key, $user_sucursal );
			break;

			case 'validatePayments' : 
				$sale_id = $_GET['sale_id'];
				echo $Payments->validatePayments( $sale_id );
			break;
				
			case 'delete_payment_saved' : 
				$payment_id = $_GET['payment_id'];
				$session_id = $_GET['current_session_id'];
				echo $Payments->delete_payment_saved( $payment_id, $session_id );
			break;
//afiliaciones
			case 'obtenerListaAfiliaciones' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaAfiliaciones( $session_id, $user_id, $user_sucursal );
			break;

			case 'obtenerListaAfiliacionesActuales':
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaAfiliacionesActuales( $session_id );
			break;

			case 'checkAfiliationSesion' : 				
				$enabled = ( isset( $_GET['enabled'] ) ? $_GET['enabled'] : $_POST['enabled'] );
				$session_terminal_id = ( isset( $_GET['session_terminal_id'] ) ? $_GET['session_terminal_id'] : $_POST['session_terminal_id'] );
				echo $Payments->checkAfiliationSesion( $enabled, $session_terminal_id );
			break;

			/*case 'agregarAfiliacionSesion' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$mannager_password = ( isset( $_GET['mannager_password'] ) ? $_GET['mannager_password'] : $_POST['mannager_password'] );
				$id_afiliacion = ( isset( $_GET['id_afiliacion'] ) ? $_GET['id_afiliacion'] : $_POST['id_afiliacion'] );
				$check_password = $Payments->check_mannager_password( $sucursal_id, $mannager_password );
				$error = ( isset( $_GET['error'] ) ? $_GET['error'] : $_POST['error'] );
				echo $Payments->agregarAfiliacionSesion( $session_id, $user_id, $id_afiliacion, $error );
			break;*/
//afiliaciones
			case 'obtenerListaAfiliaciones' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaAfiliaciones( $session_id, $user_id );
			break;

			case 'obtenerListaAfiliacionesActuales':
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaAfiliacionesActuales( $session_id );
			break;

			case 'checkAfiliationSesion' : 				
				$enabled = ( isset( $_GET['enabled'] ) ? $_GET['enabled'] : $_POST['enabled'] );
				$session_terminal_id = ( isset( $_GET['session_terminal_id'] ) ? $_GET['session_terminal_id'] : $_POST['session_terminal_id'] );
				echo $Payments->checkAfiliationSesion( $enabled, $session_terminal_id );
			break;

			case 'guardaAfiliacionSesion' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$mannager_password = ( isset( $_GET['mannager_password'] ) ? $_GET['mannager_password'] : $_POST['mannager_password'] );
				$afiliaciones = ( isset( $_GET['afiliations'] ) ? $_GET['afiliations'] : $_POST['afiliations'] );
				$check_password = $Payments->check_mannager_password( $sucursal_id, $mannager_password );
				//$error = ( isset( $_GET['error'] ) ? $_GET['error'] : $_POST['error'] );
				echo $Payments->guardaAfiliacionSesion( $session_id, $user_id, $afiliaciones );
			break;
	//terminales
			case 'obtenerListaTerminales' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaTerminales( $session_id, $user_id, $user_sucursal );
			break;

			case 'obtenerListaTerminalesActuales':
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->obtenerListaTerminalesActuales( $session_id );
			break;

			case 'checkTerminalSesion' : 				
				$enabled = ( isset( $_GET['enabled'] ) ? $_GET['enabled'] : $_POST['enabled'] );
				$session_terminal_id = ( isset( $_GET['session_terminal_id'] ) ? $_GET['session_terminal_id'] : $_POST['session_terminal_id'] );
				echo $Payments->checkTerminalSesion( $enabled, $session_terminal_id );
			break;

			case 'agregarTerminalSesion' :
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$mannager_password = ( isset( $_GET['mannager_password'] ) ? $_GET['mannager_password'] : $_POST['mannager_password'] );
				$id_terminal = ( isset( $_GET['id_terminal'] ) ? $_GET['id_terminal'] : $_POST['id_terminal'] );
				$check_password = $Payments->check_mannager_password( $sucursal_id, $mannager_password );
				$error = ( isset( $_GET['error'] ) ? $_GET['error'] : $_POST['error'] );
				echo $Payments->agregarTerminalSesion( $session_id, $user_id, $id_terminal );
			break;

			case 'show_pending_payment_responses' :
				echo $Payments->show_pending_payment_responses( $sucursal_id );
			break;
			
			default :
				die( "Access denied on '{$action}'" );
			break;
		}
	}
	/**
	* 
	*/
	class Payments
	{
		private $link;
		private $store_id;
		private $Logger;
		function __construct( $connection, $store_id, $Logger = null )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
			$this->Logger = $Logger;
			
		}

		public function show_pending_payment_responses( $store_id ){
			$resp = "<table class=\"table table-striped table-bordered\" style=\"width : 100%;\" border=\"1\">
				<thead>
					<tr>
						<th class=\"text-center\">Folio Venta</th>
						<th class=\"text-center\">Monto Cobro</th>
						<th class=\"text-center\">Fecha</th>
					</tr>
				<thead>
				<tbody>";
			$sql = "SELECT
						vtn.folio_venta AS sale_folio,
						IF( vtn.amount = 0 OR vtn.amount = '', '0.0', vtn.amount ) AS amount,
						vtn.fecha_alta AS date_time
					FROM vf_transacciones_netpay vtn
					WHERE vtn.notificacion_vista = 0
					AND vtn.id_sucursal = {$store_id}
					ORDER BY vtn.folio_venta desc";
			$stm = $this->link->query( $sql ) or die( "Error al recuperar las transacciones pendientes de llegar : {$sql} : {$this->link->error}" );
			
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<tr><td class=\"text-start\">{$row['sale_folio']}</td>";
				$resp .= "<td class=\"text-center\"> $ {$row['amount']}</td>";
				$resp .= "<td class=\"text-end\">{$row['date_time']}</td></tr>";
			}
			$resp .= "</tbody>
				</table>
			<br><br>
			<div style=\"text-align: center;\">
				<button type=\"button\" class=\"btn btn-warning\" onclick=\"close_emergent_window();\">
					<i>Aceptar y cerrar</i>
				</button>
			</div>";
			return $resp;
		}

		public function getWebSocketURL(){
			$sql = "SELECT url_websocket_pagos FROM sys_configuracion_sistema LIMIT 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la URL de los WebSockets  de pagos : {$sql} : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['url_websocket_pagos'];
		}

		public function check_mannager_password( $sucursal_id, $mannager_password ){
			$sql = "SELECT 
						u.id_usuario 
					FROM sys_users u
					LEFT JOIN sys_sucursales s
					ON s.id_encargado = u.id_usuario
					WHERE s.id_sucursal = {$sucursal_id}
					AND u.contrasena = md5( '{$mannager_password}' )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el password de encargado es corecto : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				return 'ok';
			}else{
				die( "La contraseña del encargado es incorrecta : {$this->link->error}" );
			}
		}

		public function validate_payment_is_not_bigger( $sale_id, $ammount, $log_id = null ){
		//busqueda por id
			$sql = "SELECT
						p.total AS sale_total,
						SUM( pp.monto ) AS payments_total,
						p.pagado AS was_payed
					FROM ec_pedidos p
					LEFT JOIN ec_pedido_pagos pp
					ON pp.id_pedido = p.id_pedido
					WHERE p.id_pedido = '{$sale_id}'
					GROUP BY p.id_pedido";//OR foilio_nv = '{$sale_id}'
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta suma de pagos para la comprobacion por id", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos / ec_pedido_pagos', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar pagos para comprobacion por id : {$this->link->error}" );
			}

			if( $stm->num_rows <= 0 ){	//busqueda por folio
			
				$sql = "SELECT
						ROUND( p.total, 2 ) AS sale_total,
						ROUND( SUM( pp.monto ), 2 ) AS payments_total,
						p.pagado AS was_payed
					FROM ec_pedidos p
					LEFT JOIN ec_pedido_pagos pp
					ON pp.id_pedido = p.id_pedido
					WHERE p.folio_nv = '{$sale_id}'
					GROUP BY p.id_pedido";//
				$stm = $this->link->query( $sql );
			/*Logger*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta suma de pagos para la comprobacion por folio", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos / ec_pedido_pagos', 'N/A', $sql, $this->link->error );
					}
					die( "Error al consultar pagos para comprobacion por folio : {$this->link->error}" );
				}
			}
			if( $stm->num_rows == 0 ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos / ec_pedido_pagos', 'N/A', "La nota de venta {$sale_id} no fue encontrada.", 'N/A' );
				}	
				die( "error|La nota de venta {$sale_id} no fue encontrada." );
			}

			$row = $stm->fetch_assoc();
			$sale_total = $row['sale_total'];//round( $row['sale_total'] );
			$payments_total = $row['payments_total'];//round( $row['payments_total'] );
			//$rest = round( $row['sale_total'] - $row['payments_total'] );
		//consulta los pagos por devolucion
			$sql = "SELECT 
						SUM( dp.monto ) AS pagos_devolucion
					FROM ec_devolucion_pagos dp
					LEFT JOIN ec_devolucion d
					ON dp.id_devolucion = d.id_devolucion
					WHERE d.id_pedido IN( {$sale_id} )";
			$stm = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta suma de pagos por devolucion ", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_devolucion / ec_devolucion_pagos', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar pagos por devolucion para comprobacion : {$this->link->error}" );
			}
			$devolucion_row = $stm->fetch_assoc();
			$pagos_dev = $devolucion_row['pagos_devolucion'];
			$tmp_total =  $payments_total + $ammount - $pagos_dev;//round()
			$rest = ($sale_total - $tmp_total);
			//if( $sale_total < $tmp_total ){
			if( $rest >= -1 && $rest <=1 ){

			}else{
				if( $tmp_total > $sale_total ){
					if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos / ec_pedido_pagos', 'N/A', "El pago no puede ser mayor al total de la venta : {$sale_total} - {$tmp_total} = {$rest}", 'N/A' );
					}	
					die( "<div class=\"row\" style=\"padding:15px;\">
						<h2 class=\"text-center text-danger\">El pago no puede ser mayor al total de la venta.</h2>
						<div class=\"col-3\"></div>
						<div class=\"col-6\">
							<br>
							<button
								type=\"button\"
								class=\"btn btn-danger form-control\"
								onclick=\"close_emergent();\"	
							>
								<i class=\"icon-ok-circled\">Aceptar</i>
							</button>
						</div>
					</div>" );//error|
				}
			}
			return 'ok';
		}

		public function validatePayments( $sale_id ){
			$saldo_favor = 0;
			$sql = "SELECT
						ROUND( SUM( monto_devolucion_interna ), 2 ) AS a_favor
					FROM ec_pedidos_relacion_devolucion
					WHERE id_pedido_relacionado = {$sale_id}
					AND id_sesion_caja_pedido_relacionado = 0";
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion  ( saldo a favor ): {$this->link->error}" );
			if( $stm_1->num_rows > 0 ){
				$tmp_row = $stm_1->fetch_assoc();
				$saldo_favor = $tmp_row['a_favor'];
				if( $saldo_favor == '' || $saldo_favor == null ){///asigna valor 0 si el resultado de la consulta er nullo o vacio
					$saldo_favor = 0;
				}
			}
			$sql = "SELECT
						p.total AS sale_total,
						SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ) + {$saldo_favor} AS payments_total,
						p.pagado AS was_payed
					FROM ec_pedidos p
					LEFT JOIN ec_pedido_pagos pp
					ON pp.id_pedido = p.id_pedido
					WHERE p.id_pedido = {$sale_id}";
			//die( $sql );
			$difference = round( $row['sale_total'], 2 ) - round( $row['payments_total'], 2 );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los totales para validar : {$sql} : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['was_payed'] == 1 && ( $difference == 1 || $difference == -1) ){
				die( "<div class=\"row\">
					<h3 class=\"text-center text-danger fs-2\">La venta no esta liquidada, registra todos los pagos y vuelve a intentar</h3>
					<div class=\"\">
						<div class=\"col-6 text-primary\">
							Total : {$row['sale_total']}
						</div>
						<div class=\"col-6 text-success\">
							Pagado : {$row['payments_total']}
						</div>
					</div>
					<button class=\"btn btn-danger\" onclick=\"close_emergent();\">
						<i class=\"icon-cancel-circled\">Aceptar y cerrar</i>
					</button>

				</div>" );
			}
			die( 'ok|' );
		}

		public function setPaymentWhithouthIntegration( $afiliation_id, $ammount, $authorization_number, $is_per_error, $sale_id, $session_id, $user_id, $pago_por_saldo_a_favor = 0, $id_venta_origen = 0, $id_devolucion_relacionada = 0 ){	
			$this->link->autocommit( false );
				if( $id_devolucion_relacionada > 0 ){
					//die( "Entra en reinsertaPagosPorDevolucionCaso2" );
					$this->reinsertaPagosPorDevolucionCaso2( $sale_id, $user_id, $session_id, 'n/a', 0, 0 );
				}else{
					//die( "No Entra en reinsertaPagosPorDevolucionCaso2" );
				}
			//if( $pago_por_saldo_a_favor > 0 ){
				$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id, $pago_por_saldo_a_favor );
			//}
		//inserta el cobro del cajero en efectivo
			$sql_cc = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, {$afiliation_id}, -1, 7, {$ammount}, NOW(), NOW(), '{$authorization_number}', 1)";
			$stm_cc = $this->link->query( $sql_cc ) or die( "Error al insertar el cobro del cajero en setPaymentWhithouthIntegration: {$this->link->error}" );
			$id_cajero_cobro = $this->link->insert_id;//die( 'here' );
		//consulta entre interno y externo
		    $sql = "SELECT
						ROUND( ax.internal/ax.total, 6 ) AS internal_porcent,
						ROUND( ax.external/ax.total, 6 ) AS external_porcent
		            FROM(
		            	SELECT
			                SUM( pd.monto ) AS total,
			                SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
			                SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
						FROM ec_pedidos_detalle pd
						LEFT JOIN sys_sucursales_producto sp
						ON pd.id_producto = sp.id_producto
						AND sp.id_sucursal = {$this->store_id}
						WHERE pd.id_pedido = {$sale_id}
		            )ax";
			$stm = $this->link->query( $sql ) or die( "Error al consultar porcentajes de pagos : {$sql} {$this->link->error}" );
			
			$row = $stm->fetch_assoc();
		//inserta pago interno		
			if( $row['internal_porcent'] > 0 ){
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
				VALUES( {$sale_id}, {$id_cajero_cobro}, 7, NOW(), NOW(), ( {$ammount}*{$row['internal_porcent']} ), '', 1, 1, -1, -1, 0, {$user_id}, {$session_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$this->link->error}" );
			}
		//inserta pago externo		
			if( $row['external_porcent'] > 0 ){
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
				VALUES( {$sale_id}, {$id_cajero_cobro}, 7, NOW(), NOW(), ( {$ammount}*{$row['external_porcent']} ), '', 1, 1, -1, -1, 1, {$user_id}, {$session_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$this->link->error}" );
			}
		//actualiza el pago
			$sql = "UPDATE ec_pedido_pagos 
						SET id_cajero = {$user_id}, 
						id_sesion_caja = {$session_id},
						id_cajero_cobro = {$id_cajero_cobro}
					WHERE id_cajero = 0 AND id_sesion_caja = 0
					AND id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al enlazar el cobro al cajero : {$this->link->error}" );

		//actualiza la venta
			$sql = "UPDATE ec_pedidos 
						SET id_cajero = {$user_id}, 
						id_sesion_caja = {$session_id}
					WHERE id_cajero = 0 AND id_sesion_caja = 0
					AND id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al enlazar la venta al cajero : {$this->link->error}" );
		//actualiza el satus del pedido
			$sql = "SELECT 
						SUM( pp.monto ) AS payments,
						p.total AS total 
					FROM ec_pedido_pagos pp
					LEFT JOIN ec_pedidos p 
					ON p.id_pedido = pp.id_pedido
					WHERE pp.id_pedido = {$sale_id}";
			$stm_2 = $this->link->query( $sql ) or die( "Error al consultar suma de pagos de la venta : {$sql}\n{$this->link->error}" );
			$row = $stm_2->fetch_assoc();
			if( $row['payments'] >= $row['total']  ){
				$sql = "UPDATE ec_pedidos SET pagado = 1 WHERE id_pedido = {$sale_id}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar la cabecera del pedido a pagada : {$this->link->error}" );
			}
			if( $is_per_error == 1 ){
			//consulta el id de registro de afiliacion de la sesion de caja
				$sql = "UPDATE ec_sesion_caja_afiliaciones 
							SET habilitado = 0 
						WHERE id_sesion_caja = {$session_id}
						AND id_afiliacion = {$afiliation_id}";
				$stm = $this->link->query( $sql ) or die( "Error al desahabilitar la afiliacion de sesion de caja por cobro unico {$this->link->error}" );		
			}
			$this->link->autocommit( true );
			return "ok|Pago registrado exitosamente.";
		}

		public function seekTerminalByQr( $qr_txt, $sucursal_id, $session_id ){
			$sql = "SELECT
						a.id_afiliacion AS afiliation_id,
						a.no_afiliacion AS afiliation_number,
						sca.insertada_por_error_en_cobro AS is_per_error
					FROM ec_afiliaciones a
					LEFT JOIN ec_afiliacion_sucursal afs
					ON a.id_afiliacion = afs.id_afiliacion
					LEFT JOIN ec_sesion_caja_afiliaciones sca
					ON sca.id_afiliacion = a.id_afiliacion
					WHERE a.no_afiliacion = '{$qr_txt}'
					AND sca.id_sesion_caja = {$session_id}
					AND sca.habilitado=1";//AND( IF( sca.insertada_por_error_en_cobro = 1, sca.utilizada_en_error = 0, 1=1 ) )
			$stm = $this->link->query( $sql ) or die( "Error al consultar la afiliacion en la sesion : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( "La terminal '{$qr_txt}' no fue encontrada en la sesion de cajero actual, verifica y vuelve a intentar." );
			}else{
				$row = $stm->fetch_assoc();
				return "ok|" . json_encode( $row );
			}
		}	

		public function getHistoricPayment( $sale_id ){
			$resp = "";
			$amount_payed = 0;
		//verifica si el cobro fue finalizado
			$sql = "SELECT cobro_finalizado FROM ec_pedidos WHERE id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar status de cobro en pedido : {$this->link->error}" );
			$sale_row = $stm->fetch_assoc();
			$sql = "SELECT
						cc.id_cajero_cobro AS payment_id,
						cc.monto AS amount,
						tp.nombre AS payment_type,
						CONCAT( cc.fecha, ' ', cc.hora ) AS datetime,
						cc.id_terminal AS terminal_id,
						cc.observaciones,
						cc.cobro_cancelado,
						cc.id_tipo_pago
					FROM ec_cajero_cobros cc
					LEFT JOIN ec_tipos_pago tp
					ON cc.id_tipo_pago = tp.id_tipo_pago
					WHERE cc.id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el historico de cobros : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$resp = "<table class=\"table table-bordered table-striped\">
					<thead>
						<tr>
							<th class=\"text-center col-3\">Tipo Pago</th>
							<th class=\"text-center col-3\">Monto</th>
							<th class=\"text-center col-3\">Fecha / Hora</th>
							<th class=\"text-center col-3\">Acciones</th>
						</tr>
					</thead>
					<tbody>";
				while( $row = $stm->fetch_assoc() ){
					$color = "";
					$aux_row = array();
					$disabled = "";
					if( $row['cobro_cancelado'] == 1 ){
						$disabled = "disabled";
					}
					$onclick = "delete_payment_saved( {$row['payment_id']}, {$sale_id} );";
					if( $sale_row['cobro_finalizado'] == 1 || $sale_row['cobro_finalizado'] == '1' ){
						$onclick = "alert( 'El cobro ya fue finalizado y no es posible eliminar pagos.' );return false;";
					}
					$button = "<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"{$onclick}\"
								style=\"padding : 0px !important;\"
								{$disabled}
							>
								<i class=\"icon-cancel-circle\"></i>
							</button>";
					if( $row['amount'] < 0 ){
						if( $row['id_tipo_pago'] != 3 ){
							$row['payment_type'] = "Devuelto al cliente";
						}
							
						$color = "class=\"text-danger\"";
					}
					if( $row['terminal_id'] > 0 ){
						$sql_tmp = "SELECT folio_unico FROM vf_transacciones_netpay WHERE orderId = '{$row['observaciones']}'";
						$stm_aux = $this->link->query( $sql_tmp ) or die( "Error al conultar id de transaccion : {$this->link->error}" );
						$aux_row = $stm_aux->fetch_assoc();
						$button = "<button
								type=\"button\"
								class=\"btn btn-warning\"
								style=\"padding : 0px !important;\"
								onclick=\"rePrintByOrderId( '{$aux_row['folio_unico']}' );\"
							>
								<i class=\"icon-print-3\"></i>
							</button>";
					/*deshabilitado por Oscar 2024-03-22
						<button
							type=\"button\"
							class=\"btn btn-danger\"
							style=\"padding : 0px !important;\"
							onclick=\"cancelByOrderId( '{$aux_row['id_transaccion_netpay']}' );\"
						>
							<i class=\"icon-cancel-circled\"></i>
						</button>
					*/
					}
					$resp .= "<tr {$color}>
						<td class=\"text-center\">{$row['payment_type']}</td>
						<td class=\"text-center\">{$row['amount']}</td>
						<td class=\"text-center\">{$row['datetime']}</td>
						<td class=\"text-center\">
							{$button}
						</td>
					</tr>";
					$amount_payed += $row['amount'];
				}
				$resp .= "</tbody>
					<tfoot>
						<tr>
							<td colspan=\"4\" class=\"text-end\">Total pagado : $ {$amount_payed}</td>
						</tr>
					</tfoot>
					</table>";
			}
			return "ok|{$resp}";
		}

		public function insertCashPayment( $ammount, $sale_id, $user_id, $session_id, $pago_por_saldo_a_favor = 0, $id_venta_origen = 0, 
			$id_devolucion_relacionada = 0, $tipo_pago = 1, $id_caja_cuenta = -1 ){
//die( "Monto : {$ammount} - pago_por_saldo_a_favor : $pago_por_saldo_a_favor - Id_venta Origen : {$id_venta_origen}" );
			$this->link->autocommit( false );
			//caso 0 : el cliente tiene saldo a favor, y su nueva nota es menor a su saldo a afavor
			if( $ammount < 0 && $pago_por_saldo_a_favor > 0 ){//die( 'caso 1' );
			//insertar pago
				//desahabilitado por Oscar 2024-02-16$this->insertPayment( $pago_por_saldo_a_favor, $sale_id, $user_id, $session_id );
				$sale_id = $this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id, ( $pago_por_saldo_a_favor * -1 )  );
				$this->insertReturnPayment( $ammount, $sale_id, $user_id, $session_id, $id_venta_origen, true );

			}else if( $ammount > 0 ){
			//	die( "Entra en este caso" );
				//$ammount = 
				if( $id_devolucion_relacionada != 0 ){
					$this->reinsertaPagosPorDevolucionCaso2( $sale_id, $user_id, $session_id, 'n/a', 0, 0 );
					//die( "Entra en este caso" );
				}else{
					//die( "NO Entra en este caso" );
				}//die( "caso 2 : cobrar al cliente con dev o sin dev" );
				$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
				$this->insertPayment( $ammount, $sale_id, $user_id, $session_id, $tipo_pago, $id_caja_cuenta );
			}else if( $ammount < 0 ){
				//die( "caso 3 : devolver efectivo al cliente cuando no se agregan productos : {$sale_id}" );
				//$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
			//insertar pago
				$this->insertReturnPayment( $ammount, $sale_id, $user_id, $session_id );
			}else if( $ammount == 0 ){//die( "caso 4 : no se devulve dinero al cliente ni se cobra pero se inserta pago" );
				//$ammount = 
				$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
				if( $ammount != 0 ){
					$this->insertPayment( $ammount, $sale_id, $user_id, $session_id, $tipo_pago, $id_caja_cuenta );
				}
			}
			//$stm = $this->link->query( $sql ) or die( "Error al consultar la suma de los pagos : {$this->link->error}" );
		$this->link->autocommit( true );
			return 'ok|';
		}

		/*public function insertSpecialPayment( $ammount, $sale_id, $user_id, $session_id ){
		//inserta cajero cobro	
			$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
			monto, fecha, hora, observaciones, sincronizar) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, -1, -1, 2, {$ammount}, NOW(), NOW(), 'Pago por saldo a Favor', 1)";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el pago por saldo a favor : {$this->link->error}" );
		//inserta el poedido pago
			
		$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
		id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
		VALUES( {$sale_id}, {$id_cajero_cobro}, {$type}, NOW(), NOW(), ( {$ammount}*{$row['internal_porcent']} ), '', 1, 1, -1, -1, 0, {$user_id}, {$session_id} )";
		$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$this->link->error}" );
			return 'ok';
			//$sql = "INSERT INTO ec_cajero_cobros";
		}*/

		public function insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id, $saldo_especial = 0, $id_caja_cuenta = -1, $log_id = null ){
			$steep_log_id = 0;
//echo "<br>INSERTPAYMENTDEPENDING<br>{$log_id}";
			$total_devolver_cajero = 0;
			$devolucion_interna = 0;
			$devolucion_externa = 0;
			$sql = "SELECT
						id_pedido_relacion_devolucion,
						id_pedido_original,
						monto_pedido_original,
						id_sesion_caja_pedido_orginal, 
						id_devolucion_interna,
						id_devolucion_externa,
						monto_interno_por_devolver,
						monto_externo_por_devolver,
						monto_devolucion_externa,
						id_pedido_relacionado,
						monto_pedido_relacionado,
						id_sesion_caja_pedido_relacionado,
						saldo_a_favor,
						monto_devolucion_tomado_a_favor
					FROM ec_pedidos_relacion_devolucion
					WHERE id_pedido_relacionado = {$sale_id}
					AND id_sesion_caja_pedido_relacionado = 0";
			$stm_1 = $this->link->query( $sql );
		/*Logger*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta relacion de pedidos y devolucion", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos_relacion_devolucion', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar relacion de pedidos y devolucion : {$this->link->error}" );
			}
		/*</Logger>*/
			if( $stm_1->num_rows == 1 ){
				$row = $stm_1->fetch_assoc();
				$this->reinsertaPagosPorDevolucion( $row['id_pedido_original'], $user_id, $session_id, '$folio_devolucion', $row['monto_interno_por_devolver'], $row['monto_externo_por_devolver'] );
		//verifica que el pedido no sea un apartado
				$sql = "SELECT 
							p.pagado AS was_payed, 
							p.total AS sale_total, 
							SUM( pp.monto ) AS payments_amount
						FROM ec_pedidos p
						LEFT JOIN ec_pedido_pagos pp
						ON p.id_pedido = pp.id_pedido 
						WHERE p.id_pedido = {$row['id_pedido_relacionado']}";
				$stm_2 = $this->link->query($sql);
			/*Logger*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta si la venta esta pagada", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos - ec_pedido_pagos', 'N/A', $sql, $this->link->error );
					}
					die( "Error al consultar si la venta esta pagada : {$this->link->error}" );
				}
			/*</Logger>*/
				$row_2 = $stm_2->fetch_assoc();
			//inserta el pago de la devolucion interna
				if( $row['monto_interno_por_devolver'] > 0 ){
					$sql = "INSERT INTO ec_devolucion_pagos( id_devolucion_pago, id_devolucion, id_tipo_pago, monto, es_externo, fecha, hora,
					id_cajero, id_sesion_caja ) VALUES( NULL, {$row['id_devolucion_interna']}, 1, {$row['monto_interno_por_devolver']}, 0, 
					NOW(), NOW(), {$user_id}, {$session_id} )";
					$stm_3 = $this->link->query($sql);
				/*Logger*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta pago de devolucion interna", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_devolucion_pagos', 'N/A', $sql, $this->link->error );
						}
						die( "Error al insertar pago de devolucion interna : {$this->link->error}" );
					}
				/*</Logger>*/
//echo $sql . "<br><br>";
					$devolucion_interna = $this->link->insert_id;
					$total_devolver_cajero += $row['monto_interno_por_devolver'];
				}
			//inserta el pago de la devolucion externa
				if( $row['monto_externo_por_devolver'] > 0 ){
					$sql = "INSERT INTO ec_devolucion_pagos( id_devolucion_pago, id_devolucion, id_tipo_pago, monto, es_externo, fecha, hora,
					id_cajero, id_sesion_caja ) VALUES( NULL, {$row['id_devolucion_externa']}, 1, {$row['monto_externo_por_devolver']}, 1, 
					NOW(), NOW(), {$user_id}, {$session_id} )";
					$stm_4 = $this->link->query($sql);
				/*Logger*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta pago de devolucion externa", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_devolucion_pagos', 'N/A', $sql, $this->link->error );
						}
						die( "Error al insertar pago de devolucion externa : {$this->link->error}" );
					}
				/*</Logger>*/
					$devolucion_externa = $this->link->insert_id;
					$total_devolver_cajero += $row['monto_externo_por_devolver'];
				}
				$total_devolver_cajero = round( $total_devolver_cajero, 2 ) * -1;
/*inserta el pago por devolucion en el cajero*/
			//inserta el cobro del cajero en efectivo por devolucion
				if( $saldo_especial == 0 ){
					$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
						monto, fecha, hora, observaciones, sincronizar) 
					VALUES ( NULL, {$this->store_id}, {$row['id_pedido_original']}, {$user_id}, {$session_id}, -1, {$id_caja_cuenta}, 1, {$total_devolver_cajero}, NOW(), NOW(), '', 1)";
				}else{
					$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
						monto, fecha, hora, observaciones, sincronizar) 
					VALUES ( NULL, {$this->store_id}, {$row['id_pedido_original']}, {$user_id}, {$session_id}, -1, {$id_caja_cuenta}, 1, {$saldo_especial}, NOW(), NOW(), '', 1)";
					
				}

				if( $row['monto_devolucion_tomado_a_favor'] > 0 ){
					$this->insertPayment( $row['monto_devolucion_tomado_a_favor'], $sale_id, $user_id, $session_id, 2 );
				}
				$stm = $this->link->query( $sql );
			/*Logger*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta el cobro del cajero en insertPaymentsDepending", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_cajero_cobros', 'N/A', $sql, $this->link->error );
					}
					die( "Error al insertar el cobro del cajero en insertPaymentsDepending : {$this->link->error}" );
				}
			/*</Logger>*/
				$id_cajero_cobro = $this->link->insert_id;
				
				$sql = "UPDATE ec_devolucion_pagos 
							SET id_cajero_cobro = {$id_cajero_cobro} 
						WHERE id_devolucion_pago IN( {$devolucion_interna}, {$devolucion_externa} )";
				$stm_update = $this->link->query( $sql );	
			/*<Logger>*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Actualiza las devoluciones relacionadas", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_devolucion_pagos', 'N/A', $sql, $this->link->error );
					}
					die( "Error al actualizar las devoluciones relacionadas : {$this->link->error}" );
				}
			/*</Logger>*/
/**/			
//echo $sql . "<br><br>";
				$sql = "UPDATE ec_pedidos_relacion_devolucion 
					SET id_sesion_caja_pedido_relacionado = {$session_id} 
				WHERE id_pedido_relacionado = {$row['id_pedido_relacionado']}";
				$stm = $this->link->query( $sql );
			/*Logger*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Actualiza ec_pedidos_relacion_devolucion de pagos", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos_relacion_devolucion', 'N/A', $sql, $this->link->error );
					}
					die( "Error al actualizar ec_pedidos_relacion_devolucion de pagos : {$sql} {$this->link->error}" );
				}
//echo $sql . "<br><br>";
/*Implementacion Oscar 2024-04-11 para imprimir el ticket de la nota dependiente*/
				
/*fin de cambio Oscar 2024-04-11*/
			//modifica el monto para hacer cuadrar el pago por devolucion
				return $row['id_pedido_original'];
				//return $amount;
			}else{
				return $sale_id;
			}
		}

		public function insertPayment( $ammount, $sale_id, $user_id, $session_id, $type = 1, $id_caja_cuenta = -1 ){//$type = 1 ( efectivo )
			//die( 'insertPayment' );
//echo "<br>INSERTPAYMENT<br>";
//echo $sql . "<br><br>";
		//inserta el cobro del cajero en efectivo
			$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar ) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, -1, {$id_caja_cuenta}, {$type}, {$ammount}, NOW(), NOW(), '', 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del cajero en insertPayment : {$this->link->error}" );
			$id_cajero_cobro = $this->link->insert_id;

		//consulta entre interno y externo
		    $sql = "SELECT
		              ROUND( ax.internal/ax.total, 6 ) AS internal_porcent,
		              ROUND( ax.external/ax.total, 6 ) AS external_porcent
		            FROM(
		              SELECT
		                SUM( pd.monto ) AS total,
		                SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
		                SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
		              FROM ec_pedidos_detalle pd
		              LEFT JOIN sys_sucursales_producto sp
		              ON pd.id_producto = sp.id_producto
		              AND sp.id_sucursal = {$this->store_id}
		              WHERE pd.id_pedido = {$sale_id}
		            )ax";
			$stm = $this->link->query( $sql ) or die( "Error al consultar porcentajes de pagos : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
		//inserta pago interno		
			if( $row['internal_porcent'] > 0 ){
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
				VALUES( {$sale_id}, {$id_cajero_cobro}, {$type}, NOW(), NOW(), ( {$ammount}*{$row['internal_porcent']} ), '', 1, 1, -1, -1, 0, {$user_id}, {$session_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$this->link->error}" );
//echo $sql . "<br><br>";
			}
		//inserta pago externo		
			if( $row['external_porcent'] > 0 ){
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
				VALUES( {$sale_id}, {$id_cajero_cobro}, {$type}, NOW(), NOW(), ( {$ammount}*{$row['external_porcent']} ), '', 1, 1, -1, -1, 1, {$user_id}, {$session_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$this->link->error}" );
//echo $sql . "<br><br>";
			}
//echo $sql . "<br><br>";
		//actualiza el pago
			$sql = "UPDATE ec_pedido_pagos 
						SET id_cajero = {$user_id}, 
						id_sesion_caja = {$session_id}
					WHERE id_cajero = 0 AND id_sesion_caja = 0
					AND id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al enlazar el cobro al cajero : {$this->link->error}" );
//echo $sql . "<br><br>";
		//actualiza la venta
			$sql = "UPDATE ec_pedidos 
						SET id_cajero = {$user_id}, 
						id_sesion_caja = {$session_id}
					WHERE id_cajero = 0 AND id_sesion_caja = 0
					AND id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al enlazar la venta al cajero : {$this->link->error}" );
//echo $sql . "<br><br>";
		//actualiza el satus del pedido
			$sql = "SELECT 
						SUM( pp.monto ) AS payments,
						p.total AS total 
					FROM ec_pedido_pagos pp
					LEFT JOIN ec_pedidos p 
					ON p.id_pedido = pp.id_pedido
					WHERE pp.id_pedido = {$sale_id}";
			$stm_2 = $this->link->query( $sql ) or die( "Error al consultar suma de pagos de la venta : {$sql}\n{$this->link->error}" );
//echo $sql . "<br><br>";
			$row = $stm_2->fetch_assoc();
			if( $row['payments'] >= $row['total']  ){
				$sql = "UPDATE ec_pedidos SET pagado = 1 WHERE id_pedido = {$sale_id}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar la cabecera del pedido a pagada : {$this->link->error}" );
//echo $sql . "<br><br>";
			}
		}

		public function reinsertaPagosPorDevolucion( $id_venta, $id_cajero, $id_sesion_caja, $folio_devolucion, $monto_dev_interna, $monto_dev_externa ){
			$sql = "SELECT * FROM ec_pedido_pagos WHERE id_pedido = {$id_venta} AND monto > 0 AND referencia = ''";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los pagos anteriores : {$this->link->error}" );
			$this->link->autocommit(false);
			//die( "here" );
			$row = array();
			while( $row = $stm->fetch_assoc() ){
			//inserta pagos en negativo{$row['id_cajero_cobro']}
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
					id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
					VALUES ( '{$row['id_pedido']}', '1', '{$row['id_tipo_pago']}', now(), now(), ( {$row['monto']}*-1 ), 'Pago Anulado por devolucion {$folio_devolucion}', '{$row['id_moneda']}', '{$row['tipo_cambio']}', 
					'{$row['id_nota_credito']}', '{$row['id_cxc']}', '0', '{$row['es_externo']}', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}')";
				$insert = $this->link->query( $sql ) or die( "Error al insertar los cobros del cajero : {$this->link->error}" );
				$sql = "UPDATE ec_pedido_pagos SET referencia = 'Pago para anular por devolucion {$folio_devolucion}' WHERE id_pedido_pago = {$row['id_pedido_pago']}";
				$insert = $this->link->query( $sql ) or die( "Error al insertar los cobros del cajero : {$this->link->error}" );
				
			}
		
		//inserta los pagos de acuerdo a la devolucion interna 
			/*$sql = "SELECT 
						d.monto_devolucion
					FROM ec_devolucion d
					LEFT JOIN ec_devolucion_pagos dp
					ON d.id_devolucion = dp.id_devolucion
					WHERE dp.es_externo = 0
					AND id_pedido = {$id_venta}";
			$stm_aux = $this->link->query( $sql ) or die( "Error al consltar devolucion interna : {$this->link->error}" );
			if( $stm_aux->num_rows > 0 ){*/
			if( $monto_dev_interna > 0 ){
				//$row_aux = $stm_aux->fetch_assoc();
			//inserta los pagos de acuerdo a la devolucion interna{$row['id_cajero_cobro']}
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja, referencia )
				VALUES ( '{$id_venta}', '1', '1', now(), now(), ROUND( {$monto_dev_interna}, 4 ), '1', '-1', 
				'-1', '-1', '0', '0', '{$id_cajero}', NULL, '{$row['sincronizar']}', '{$id_sesion_caja}', 
				'Devolucion interna {$folio_devolucion}')";
				$insert = $this->link->query( $sql ) or die( "Error al insertar el cobro interno del cajero en relacion a la devolucion : {$this->link->error}" );
			}

		//inserta los pagos de acuerdo a la devolucion externa
			/*$sql = "SELECT 
						d.monto_devolucion
					FROM ec_devolucion d
					LEFT JOIN ec_devolucion_pagos dp
					ON d.id_devolucion = dp.id_devolucion
					WHERE dp.es_externo = 1
					AND id_pedido = {$id_venta}";
			$stm_aux = $this->link->query( $sql ) or die( "Error al consltar devolucion interna : {$this->link->error}" );
			if( $stm_aux->num_rows > 0 ){*/
			if( $monto_dev_externa > 0 ){
				//$row_aux = $stm_aux->fetch_assoc();
			//inserta los pagos de acuerdo a la devolucion externa
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja, referencia )
				VALUES ( '{$id_venta}', '1', '1', now(), now(),  ROUND( {$monto_dev_externa}, 4 ), '1', '-1', 
				'-1', '-1', '0', '1', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}', 
				'Devolucion externa {$folio_devolucion}')";
				$insert = $this->link->query( $sql ) or die( "Error al insertar el cobro externo del cajero en relacion a la devolucion : {$this->link->error}" );
			}


		//inserta pagos de acuerdo al nuevo porcentaje entre internos y externos
			$sql = "SELECT
					ROUND( ax.internal/ax.total, 6 ) AS internal_porcent,
					ROUND( ax.external/ax.total, 6 ) AS external_porcent,
					ROUND( ax.total * porcentaje, 2 ) AS total,
					'{$row['id_cajero_cobro']}' AS id_cajero_cobro
				FROM(
					SELECT
						SUM( pd.monto ) AS total,
						ROUND( p.total / p.subtotal, 6 ) AS porcentaje,
						SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
						SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos p
					ON p.id_pedido = pd.id_pedido
					LEFT JOIN sys_sucursales_producto sp
					ON pd.id_producto = sp.id_producto
					AND sp.id_sucursal = {$this->store_id}
					WHERE pd.id_pedido = {$id_venta}
				)ax";
			//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al consultar porcentajes de pagos : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			
			//$sql = "SELECT id_cajero_cobro, monto FROM ec_cajero_cobros WHERE id_pedido = {$id_venta}";//die( $sql );
			//$stm_cc = $this->link->query( $sql ) or die( "Error al consultar los cajeros cobros en reinsertaPagosPorDevolucion : {$this->link->error}" );
			//while( $row_cc = $stm_cc->fetch_assoc() ){
				if( $row['internal_porcent'] > 0 ){
				//die( "inserta pagos internos" );
					if( $row['internal_porcent'] >= 0.99 ){
						$row['internal_porcent'] = 1;
					}
				//inserta pagos internos
					$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
						id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
						VALUES ( '{$id_venta}', '1', '1', now(), now(), ROUND( {$row['total']}*{$row['internal_porcent']}, 4 ), '', '1', '-1', 
						'-1', '-1', '0', '0', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}')";
					$insert = $this->link->query( $sql ) or die( "Error al insertar el cobro interno del cajero : {$this->link->error}" );
				}
				if( $row['external_porcent'] > 0 ){
					if( $row['external_porcent'] >= 0.99 ){
						$row['external_porcent'] = 1;
					}
				//inserta pagos externos
					$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
						id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
						VALUES ( '{$id_venta}', '1', '1', now(), now(), ROUND( {$row['total']}*{$row['external_porcent']}, 4 ), '', '1', '-1', 
						'-1', '-1', '0', '1', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}')";
					$insert = $this->link->query( $sql ) or die( "Error al insertar el cobro interno del cajero : {$this->link->error}" );
				}
			//}
			//die( "here" );
			$this->link->autocommit(true);
		}

		public function reinsertaPagosPorDevolucionCaso2 ( $id_venta, $id_cajero, $id_sesion_caja, $folio_devolucion, $monto_dev_interna, $monto_dev_externa, $log_id = null ){
			$steep_log_id = 0;
			$sql = "SELECT * FROM ec_pedido_pagos WHERE id_pedido = {$id_venta} AND monto > 0 AND referencia = ''";
			$stm = $this->link->query( $sql );
		/*<Logger>*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta pagos anteriores", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
				$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar los pagos anteriores : {$this->link->error}" );
			}
		/*</Logger>*/
			$this->link->autocommit(false);
			$total_pagado = 0;
			//die( "here" );
			$row = array();
			while( $row = $stm->fetch_assoc() ){
			//inserta pagos en negativo
				$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
					id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
					VALUES ( '{$row['id_pedido']}', '{$row['id_cajero_cobro']}', '{$row['id_tipo_pago']}', now(), now(), ( {$row['monto']}*-1 ), 'Pago Anulado por devolucion {$folio_devolucion}', '{$row['id_moneda']}', '{$row['tipo_cambio']}', 
					'{$row['id_nota_credito']}', '{$row['id_cxc']}', '{$row['exportado']}', '{$row['es_externo']}', '{$id_cajero}', NULL, '{$row['sincronizar']}', '{$id_sesion_caja}')";
				$insert = $this->link->query( $sql );
			/*<Logger>*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta los cobros del cajero", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $this->link->error );
					}
					die( "Error al insertar los cobros del cajero : {$this->link->error}" );
				}
			/*</Logger>*/ 
				$sql = "UPDATE ec_pedido_pagos SET referencia = 'Pago para anular por devolucion {$folio_devolucion}' WHERE id_pedido_pago = {$row['id_pedido_pago']}";
				$insert = $this->link->query( $sql );
			/*<Logger>*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Actualiza referencias de los cobros del cajero", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
					$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $this->link->error );
					}
					die( "Error al actualizar referencias de los cobros del cajero : {$this->link->error}" );
				}
			/*</Logger>*/
				$total_pagado += $row['monto'];
			}

		//inserta pagos de acuerdo al nuevo porcentaje entre internos y externos
			$sql = "SELECT
					ROUND( ax.internal/ax.total_nota, 6 ) AS internal_porcent,
					ROUND( ax.external/ax.total_nota, 6 ) AS external_porcent,
					ROUND( ax.total * porcentaje, 2 ) AS total,
					'{$row['id_cajero_cobro']}' AS id_cajero_cobro
				FROM(
					SELECT
						{$total_pagado} AS total,
						p.subtotal AS total_nota,
						ROUND( p.total / p.subtotal, 6 ) AS porcentaje,
						SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
						SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos p
					ON p.id_pedido = pd.id_pedido
					LEFT JOIN sys_sucursales_producto sp
					ON pd.id_producto = sp.id_producto
					AND sp.id_sucursal = {$this->store_id}
					WHERE pd.id_pedido = {$id_venta}
				)ax";
			//die($sql);
			$stm = $this->link->query( $sql );
		/*<Logger>*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta porcentajes de pagos", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
				$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos_detalle - ec_pedidos - sys_sucursales_producto', 'N/A', $sql, $this->link->error );
				}
				die( "Error al consultar porcentajes de pagos : {$sql} {$this->link->error}" );
			}
		/*</Logger>*/
			$row = $stm->fetch_assoc();
				if( $row['internal_porcent'] > 0 ){
				//die( "inserta pagos internos" );
					if( $row['internal_porcent'] >= 0.99 ){
						$row['internal_porcent'] = 1;
					}
				//inserta pagos internos{$row['id_cajero_cobro']}
					$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
						id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
						VALUES ( '{$id_venta}', '1', '1', now(), now(), ROUND( {$total_pagado}*{$row['internal_porcent']}, 4 ), '', '1', '-1', 
						'-1', '-1', '0', '0', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}')";
					$insert = $this->link->query( $sql );
				/*<Logger>*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta el pago interno del cajero", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $this->link->error );
						}
						die( "Error al insertar el pago interno del cajero : {$this->link->error}" );
					}
				/*</Logger>*/
				}
				if( $row['external_porcent'] > 0 ){
					if( $row['external_porcent'] >= 0.99 ){
						$row['external_porcent'] = 1;
					}
				//inserta pagos externos{$row['id_cajero_cobro']}
					$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
						id_nota_credito, id_cxc, exportado, es_externo, id_cajero, folio_unico, sincronizar, id_sesion_caja )
						VALUES ( '{$id_venta}', '1', '1', now(), now(), ROUND( {$total_pagado}*{$row['external_porcent']}, 4 ), '', '1', '-1', 
						'-1', '-1', '0', '1', '{$id_cajero}', NULL, '1', '{$id_sesion_caja}')";
					$insert = $this->link->query( $sql );
				/*<Logger>*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Inserta el pago externo del cajero", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $this->link->error );
						}
						die( "Error al insertar el pago externo del cajero : {$this->link->error}" );
					}
				/*</Logger>*/
				}
			//}
		//actualiza la sesion de cabecera de devolucion
			$sql = "UPDATE ec_devolucion SET id_cajero = {$id_cajero}, id_sesion_caja = {$id_sesion_caja} WHERE id_pedido = {$id_venta}";
			$stm = $this->link->query( $sql );
		/*<Logger>*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Actualiza las cabeceras de devolucion", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
				$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_devolucion', 'N/A', $sql, $this->link->error );
				}
				die( "Error al actualizar las cabeceras de devolucion : {$this->link->error}"  );
			}
		/*</Logger>*/
		//actualiza la referencia de la devolucion 
			$sql = "UPDATE ec_pedidos_referencia_devolucion SET monto_venta_mas_ultima_devolucion = total_venta WHERE id_pedido = {$id_venta}";
			$stm = $this->link->query( $sql );
		/*<Logger>*/
			if( $log_id != null ){
				$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Actualiza la referencia de devolucion", $sql );
			}
			if( $this->link->error ){
				if( $log_id != null ){
				$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos_referencia_devolucion', 'N/A', $sql, $this->link->error );
				}
				die( "Error al actualizar la referencia de devolucion : {$this->link->error}"  );
			}
		/*</Logger>*/
			$this->link->autocommit(true);
		}

		public function insertReturnPayment( $ammount, $sale_id, $user_id, $session_id, $id_venta_origen = 0, $recalcular_por_devolucion = true ){
			$devolucion_interna = 0;
			$devolucion_externa = 0;
			$id_dev_interna = '';
			$id_dev_externa = '';
			$folio_devolucion = '';
			//die( 'insertReturnPayment' );
//echo "<br>INSERTRETURNPAYMENT<br>";
		//dev interna
			$sql = "(SELECT 
						id_devolucion,
						folio
		               FROM ec_devolucion dev
		            WHERE id_pedido = {$sale_id}
		            AND es_externo = 0)";
			$stm = $this->link->query( $sql ) or die( "Error al consultar devolucion interna : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_row();
				$id_dev_interna = $row[0];
				$folio_devolucion = $row['folio'];
			}
		//dev externa
			$sql = "(SELECT 
						id_devolucion,
						folio
		               FROM ec_devolucion dev
		            WHERE id_pedido = {$sale_id}
		            AND es_externo = 1)";
			$stm = $this->link->query( $sql ) or die( "Error al consultar devolucion externa : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_row();
				$id_dev_externa = $row[0];
				$folio_devolucion = $row['folio'];
			}
		//consulta montos internos / externos
		   	$sql = "SELECT 
		   				SUM( IF( d.es_externo = 1, d.monto_devolucion, 0 ) ),
		   				SUM( IF( d.es_externo = 0, d.monto_devolucion, 0 ) ),
		   				SUM( IF( d.id_devolucion IS NULL, 0, d.monto_devolucion ) )
		   			FROM ec_devolucion d
		   			WHERE d.id_pedido = {$sale_id}
		   			AND d.id_cajero = 0
		   			AND d.id_sesion_caja = 0";
		   			//die( $sql );
		    /*$sql="SELECT 
		            SUM(IF(pp.es_externo=1,pp.monto,0))-IF(ax.devExternos IS NULL,0,ax.devExternos) as externos,
		            SUM(IF(pp.es_externo=0,pp.monto,0))-IF(ax.devInternos is null,0,ax.devInternos )as internos,
		            SUM(pp.monto)-IF(ax.totalDev is null,0,ax.totalDev) as total 
		        FROM(
		            SELECT 
		                {$sale_id} as id_pedido,
		                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=1,dp.monto,0))) as devExternos,
		                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=0,dp.monto,0))) as devInternos,
		                SUM(IF(dev.id_devolucion IS NULL,0,dp.monto)) as totalDev
		                FROM ec_devolucion dev
		                LEFT JOIN ec_devolucion_pagos dp ON dev.id_devolucion=dp.id_devolucion
		                WHERE dev.id_pedido = {$sale_id}
		            )ax
		        LEFT JOIN ec_pedido_pagos pp ON pp.id_pedido=ax.id_pedido
		        WHERE pp.id_pedido = {$sale_id}";*/
		// die($sql);
		    $eje = $this->link->query($sql) or die( "Error al consultar montos de devolución\n{$sql}\n{$this->link->error}" );
//echo $sql . "<br><br>";
		    $datos_1 = $eje->fetch_row();
		    $datos_1[0] = round( $ammount * ( $datos_1[0] / $datos_1[2] ), 6 )*-1;
		    $datos_1[1] = round( $ammount * ( $datos_1[1] / $datos_1[2] ), 6 )*-1;
		    //var_dump( $datos_1 );die( '' );
//$this->link->autocommit( false );
		//insertamos las devoluciones completas
		    //externa
		    if( $datos_1[0]>0 && $id_dev_externa != 0 && $id_dev_externa != '' ){
		        $sql="INSERT INTO ec_devolucion_pagos ( id_devolucion_pago, id_devolucion, id_tipo_pago, monto,
		        referencia, es_externo, fecha, hora, id_cajero, id_sesion_caja )
		        VALUES(null,$id_dev_externa,1,$datos_1[0],'$datos_1[0]',1,now(),now(), {$user_id}, {$session_id} )";//modificacion Oscar 2023/10/12 {$id_cajero}, {$id_sesion_caja}
		        $eje = $this->link->query($sql) or die( "Error al insertar el pago de la devolución externa\n{$sql}\n{$this->link->error}" );
		       	$devolucion_externa = $this->link->insert_id;
//echo $sql . "<br><br>";	   		
			//actualiza la sesion de la cabecera de devoluciones
				$sql = "UPDATE ec_devolucion SET id_cajero = {$user_id}, id_sesion_caja = {$session_id} 
				WHERE id_devolucion = {$id_dev_externa}";
		    	$eje = $this->link->query($sql) or die( "Error al actualizar cajero de la devolución externa\n{$sql}\n{$this->link->error}" );
//echo $sql . "<br><br>";	   		
		    }
		//interna
		    if( $datos_1[1]>0 && $id_dev_interna != 0 && $id_dev_interna != '' ){
		        $sql="INSERT INTO ec_devolucion_pagos ( id_devolucion_pago, id_devolucion, id_tipo_pago, monto,
		        referencia, es_externo, fecha, hora, id_cajero, id_sesion_caja )
		        VALUES(null,$id_dev_interna,1,$datos_1[1],'$datos_1[1]',0,now(),now(), {$user_id}, {$session_id} )";//modificacion Oscar 2023/10/12 {$id_cajero}, {$id_sesion_caja}
		        $eje = $this->link->query( $sql ) or die( "Error al insertar el pago de la devolución interna\n{$sql}\n{$this->link->error}" );
		        $devolucion_interna = $this->link->insert_id;
//echo $sql . "<br><br>";
		   	//actualiza la sesion de la cabecera de devoluciones
				$sql = "UPDATE ec_devolucion SET id_cajero = {$user_id}, id_sesion_caja = {$session_id} 
				WHERE id_devolucion = {$id_dev_interna}";
		    	$eje = $this->link->query($sql) or die( "Error al actualizar cajero de la devolución interna\n{$sql}\n{$this->link->error}" );	
//echo $sql . "<br><br>";   		
		    }   
		//consulta si tiene devolucion relacionada
			$sql = "SELECT id_pedido FROM ec_devolucion WHERE id_devolucion IN ( '{$id_dev_interna}', '{$id_dev_externa}' )";
			$dev_stm = $this->link->query( $sql ) or die( "Error al consultar pedido relacionado : {$this->link->error} {$sql}" );
			if( $dev_stm->num_rows > 0 ){//si tiene saldo a favor de una venta origen
				$row_dev = $dev_stm->fetch_assoc();
				//die( "entra reinsertaPagosPorDevolucion" );
		    	$sale_id = ( $id_venta_origen > 0 ? $id_venta_origen : $sale_id );
				if( $recalcular_por_devolucion ){
					$this->reinsertaPagosPorDevolucion( $row_dev['id_pedido'], $user_id, $session_id, $folio_devolucion, $datos_1[1], $datos_1[0] );
				}
			}else{
				//die( "no entra en reinsertaPagosPorDevolucion" );
			}
		//inserta el cobro del cajero en efectivo por devolucion
			$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, -1, -1, 1, {$ammount}, NOW(), NOW(), '', 1)";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del cajero en insertReturnPayment: {$this->link->error}" );
			$id_cajero_cobro = $this->link->insert_id;
			$sql = "UPDATE ec_devolucion_pagos 
						SET id_cajero_cobro = {$id_cajero_cobro} 
					WHERE id_devolucion_pago IN( {$devolucion_interna}, {$devolucion_externa} )";
			$eje = $this->link->query($sql) or die( "Error al actualizar cajero cobro en devolucion : \n{$sql}\n{$this->link->error}" );
//echo $sql . "<br><br>";
		
//$this->link->autocommit( true );
		}

		public function getOrderResponse( $transaction_id, $is_manual = false ){
			if( ! $is_manual ){
				$sql = "SELECT 
							orderId,
							terminalId,
							store_id_netpay
						FROM vf_transacciones_netpay
						WHERE folio_unico = '{$transaction_id}'";
			}else{
				$sql = "SELECT 
							orderId,
							terminalId,
							store_id_netpay
						FROM vf_transacciones_netpay
						WHERE orderId = '{$transaction_id}'";
			}
			$stm = $this->link->query( $sql ) or die( "Error al consultar OrderId de la transacion : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}

	/*	public function rePrintByOrderId( $transaction_id ){
			$sql = "SELECT 
						orderId 
					FROM vf_transacciones_netpay
					WHERE id_transaccion_netpay = {$transaction_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar OrderId de la transacion : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['orderId'];
		}
*/
		public function cancelEvents( $transaction_id ){
			$sql = "UPDATE vf_transacciones_netpay SET message = 'Cancelado' WHERE id_transaccion_netpay = {$transaction_id}";
			$stm = $this->link->query( $sql ) or die( "Error al canelar la transacion : {$this->link->error}" );
			//die( $sql );
			return 'ok';
		}

		public function getTerminals( $user_id, $c = 0, $store_id = 1, $session_id ){
			$resp = "";
			$sql="SELECT 
					tis.id_terminal_integracion AS afiliation_id,
					CONCAT( tis.nombre_terminal, ' - terminal : ', tis.numero_serie_terminal, ' - storeId :', tis.store_id ) AS afiliation_number
				FROM ec_terminales_integracion_smartaccounts tis
				/*LEFT JOIN ec_terminales_cajero_smartaccounts tcs
				ON tis.id_terminal_integracion = tcs.id_terminal*/
				LEFT JOIN ec_terminales_sucursales_smartaccounts tss
				ON tss.id_terminal = tis.id_terminal_integracion
				LEFT JOIN vf_razones_sociales_emisores rse
				ON rse.id_razon_social = tss.id_razon_social
				LEFT JOIN ec_sesion_caja_terminales sct
				ON sct.id_terminal = tis.id_terminal_integracion
				WHERE /*tcs.id_cajero = '{$user_id}' 
				AND tcs.activo = 1
				AND */tss.estado_suc = 1
				AND tss.id_sucursal = {$store_id}
				AND sct.id_sesion_caja = {$session_id}
				AND sct.habilitado = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar las terminales del cajero : {$this->link->error}" );
			$tarjetas_cajero='';
			$resp .= "<tr id=\"card_payment_row_{$c}\">
				<td class=\"col-5\">
					<select id=\"tarjeta_{$c}\" class=\"form-select\">";
			if( $stm->num_rows > 1 ){
				$resp .= "<option vlaue=\"0\">--Seleccionar--</option>";
			}
			while($r = $stm->fetch_assoc() ){
					$resp .= "<option value=\"{$r['afiliation_id']}\">{$r['afiliation_number']}</option>";
			}
			$resp .= "</select>
					</td>
					<td class=\"col-6\">
						<div class=\"input-group\">
							<input type=\"text\" class=\"form-control text-end\" id=\"t{$c}\" value=\"\" 
							onkeydown=\"prevenir(event);\" onkeyup=\"validateNumberInput( this );valida_tca(this,event,1,'.$c.');\">
							<button
								class=\"btn btn-primary no_visible\"
								onclick=\"sendTerminalPetition( {$c} );\"
								id=\"payment_btn_{$c}\"
							>
								<i class=\"icon-credit-card-alt\"></i>
							</button>
							<button
								class=\"btn btn-warning no_visible\"
								onclick=\"reprintPayment( {$c}, {$r['afiliation_id']} );\"
								id=\"reprint_btn_{$c}\"
							>
								<i class=\"icon-print-6\"></i>
							</button>
							<button
								class=\"btn btn-danger \"
								onclick=\"removePaymentTmp( {$c} );\"
								id=\"cancel_btn_{$c}\"
							>
								<i class=\"icon-cancel-circle\"></i>
							</button>
						</div>
						<p class=\"text-center text-danger hidden\" id=\"t{$c}_alerta\">Campo numérico*</p>
					</td>
				</tr>";	
			//	$c++;
			//}
			return $resp;
			//onclick=\"cancelPayment( {$c}, {$r['afiliation_id']} );\" no_visible
			//echo '<input type="hidden" id="cantidad_tarjetas" value="'.$c.'">';
		}

		public function checkAccess( $user_id ){
			$sql="SELECT 
					IF(p.ver=1 OR p.modificar=1,1,0),
					( SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1 LIMIT 1 ) AS system_type,
					( SELECT tiempo_espera_respuesta_websocket FROM ec_configuracion_sucursal WHERE id_sucursal = {$this->store_id} ) AS max_execution_time/*1.2 tiempo espera dinamico 2024-07-04*/
				FROM sys_permisos p
				LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
				LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
				WHERE p.id_menu=200
				AND u.id_usuario={$user_id}";
			$stm = $this->link->query( $sql ) or die("Error al consultar el permiso de cajero : {$sql} : {$this->link->error}");
			//$es_cajero=mysql_fetch_row($eje);
			$es_cajero = $stm->fetch_row();
			$max_execution_time = $es_cajero[2];/*1.2 tiempo espera dinamico 2024-07-04*/
			$system_type = $es_cajero[1];//tipo de sistema
			if($es_cajero[0] == 0 ){
				die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla.\nContacta al administrador desl sistema.");location.href="../../../../index.php?";</script>');
			}
		//validamos que haya una sesion de caja iniciada con este cajero; de lo contrario avisamos que no hay sesión de caja y no dejamos acceder a esta pantalla
			$sql="SELECT 
					count(id_sesion_caja) 
				FROM ec_sesion_caja 
				WHERE id_cajero=$user_id
				AND hora_fin='00:00:00' 
				AND fecha=current_date()";
			$stm = $this->link->query( $sql ) or die( '<script>alert("Para realizar una venta pide al cajero inciar sesión de caja.");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>' );
			$r=$stm->fetch_row();
			if($r[0]!=1){
				die('<script>alert("Para realizar una venta pide al cajero inciar sesión de caja.");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>');
			}
		/*Implementacion Oscar 2024-06-24 para creacion / renovacion de Token */
		//consulta si tiene token activo
			$sql = "SELECT token FROM api_token WHERE id_user = {$user_id}";// AND expired_in > NOW()
			$stm = $this->link->query( $sql ) or die( "Error al consultar si hay token activo para pantalla de cobros : {$this->link->error}" );
		//obtiene el password del usuario
			$sql = "SELECT 
						id_usuario AS user_id, 
						login, 
						contrasena AS password,
						( SELECT `value` FROM api_config WHERE `key` = 'api' ) AS api_path 
					FROM sys_users WHERE id_usuario = '{$user_id}'";
			$stm2 = $this->link->query( $sql ) or die( "Error al consultar password de usuario : {$this->link->error}" );
			$user = $stm2->fetch_assoc();
			if( $stm->num_rows <= 0 ){
				//die(  "{$user['user_id']} / {$user['login']} / {$user['password']} / {$user['api_path']}" );
			//consume servcio para obtener token
				$post_data = json_encode( array( "user"=>"{$user['login']}", "password"=>"{$user['password']}" ) );
				$result = json_decode( $this->sendPetition( "{$user['api_path']}/rest/netPay/token", $post_data ) );
				
				if( $result->status != "OK" && $result->status != "OK" ){
					die("Error al generar el token para pantalla de cobros : {$result}");
				}else{
					//var_dump( $result->result->created_in );die('');
				//inserta el token en la tabla api_token
					if( $system_type > 0 ){
						$sql = "INSERT INTO api_token ( id_user, token, created_in, expired_in ) 
							VALUES ( {$user_id}, '{$result->result->access_token}', 
							'{$result->result->created_in}', '{$result->result->expired_in}' )";	//die( $sql );			
						$stm3 = $this->link->query( $sql ) or die( "Error al insertar token en cliente : {$this->link->error}" );
					}
				}
				return array( "status"=>200, "token"=>$result->result->access_token, "max_execution_time"=>$max_execution_time );/*1.2 tiempo espera dinamico 2024-07-04*/
			}else{
				$row = $stm->fetch_assoc();
			//validacion / renovacion de token
				$post_data = null;
				$result = json_decode( $this->sendPetition( "{$user['api_path']}/rest/netPay/valida_token", $post_data, $row['token'] ) );
				//var_dump($result);die('');
			//actualiza la caducidad del token en local
				$sqlAPIConfig="SELECT value FROM api_config c WHERE c.key='token' and name='time_value' limit 1";
				$resultadoConfig = $this->link->query($sqlAPIConfig) or die( "Error al consultar los parametros del token : {$sql} : {$this->link->error}" );
				$time_value = $resultadoConfig->fetch_assoc();
				$sql = "UPDATE api_token SET expired_in = TIMESTAMPADD(SECOND,{$time_value['value']},NOW()) WHERE token = '{$row['token']}'";
				$stm = $this->link->query( $sql ) or die( "Error al renovar el token en local : {$sql} : {$this->link->error}");
				return array( "status"=>200, "token"=>$row['token'], "max_execution_time"=>$max_execution_time  );/*1.2 tiempo espera dinamico 2024-07-04*/
				
			}
		}

		public function sendPetition( $url, $post_data, $token = '' ){
			//die( $url );
			$resp = "";
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . "{$token}" )
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			return $resp;
		}

		public function getBoxesMoney( $store_id ){	
			$resp = "";
			$sql="SELECT 
					bc.id_caja_cuenta,
					bc.nombre 
				FROM ec_caja_o_cuenta bc
				LEFT JOIN ec_caja_o_cuenta_sucursal bcs 
				ON bc.id_caja_cuenta=bcs.id_caja_o_cuenta 
				WHERE bcs.estado_suc=1
				AND bcs.id_sucursal = '{$store_id}'";
			$stm = $this->link->query( $sql ) or die("Error al listar los bancos o cajas : {$this->link->error}" );
			$resp = '<select id="caja_o_cuenta" class="form-select"><option value="0">--SELECCIONAR--</option>';
			while( $r = $stm->fetch_row() ){
				$resp .= '<option value="'.$r[0].'">'.$r[1].'</option>';
			}
			$resp .= '</select>';
			return $resp;
		}

		public function getLastTickets( $key = '', $store_id ){
			$condition = "";
			if( isset( $_GET['key'] ) ){
				$key = $_GET['key'];
				$condition = " AND ( CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) LIKE '%{$key}%'";
				$condition .= " OR p.folio_nv LIKE '%{$key}%' OR p.total LIKE '%{$key}%' OR c.nombre LIKE '%{$key}%'";
				$condition .= " ) ";
			}
			$current_year = date("Y");
			//die( "YEAR : {$current_year}" );
			$sql = "SELECT
						CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS user_name,
						p.folio_nv,
						p.total,
						p.id_pedido,
						p.fecha_alta,
						c.nombre AS costumer_name,
						TRUNCATE( SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ), 2 ) AS payments_amount
					FROM ec_pedidos p
					LEFT JOIN sys_users u
					ON p.id_usuario = u.id_usuario
					LEFT JOIN ec_clientes c
					ON p.id_cliente = c.id_cliente
					LEFT JOIN ec_pedido_pagos pp
					ON pp.id_pedido = p.id_pedido
					WHERE p.id_sucursal = {$store_id}
					AND p.fecha_alta LIKE '%{$current_year}%'
					{$condition}
					GROUP BY p.id_pedido
					ORDER BY p.id_pedido DESC
					LIMIT 30";//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de las notas de venta : {$this->link->error}" );
			$resp = "";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
					<td class=\"text-start\">{$row['user_name']}</td>
					<td class=\"text-center\">{$row['folio_nv']}</td>
					<td class=\"text-end text-primary\">{$row['total']}</td>
					<td class=\"text-end text-success\">{$row['payments_amount']}</td>
					<td class=\"text-end\">{$row['costumer_name']}</td>
					<td class=\"text-end\">{$row['fecha_alta']}</td>
					<td class=\"text-center\">
						<button
							type=\"button\"
							class=\"btn btn-light\"
							onclick=\"print_ticket( {$row['id_pedido']} );\"
						>
							<i class=\"icon-print\"></i>
						</button>
					</td>
				</tr>";
			}
			die( $resp );
		}

		public function delete_payment_saved( $payment_id, $session_id ){
			$sql = "SELECT 
						cc.id_sesion_caja,
						tp.nombre AS payment_type
					FROM ec_cajero_cobros cc
					LEFT JOIN ec_tipos_pago tp
					ON cc.id_tipo_pago = tp.id_tipo_pago
					WHERE cc.id_cajero_cobro = {$payment_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre del tipo de cobro por anular : {$sql} : {$this->link->error}");
			$row = $stm->fetch_assoc();
			$payment_type = $row["payment_type"];

		//validamos que la sesion de cajero sea la misma que la del pago
			if( $row['id_sesion_caja'] != $session_id ){
				die( "El cobro no puede ser cancelado porque la sesion de caja actual no corresponde a la sesion que lo cobro." );// {$row['id_sesion_caja']} != {$session_id}
			}
			
			$this->link->autocommit( false );//inicio de transaccion
		//anula cobro
			$sql = "UPDATE ec_cajero_cobros SET cobro_cancelado = 1 WHERE id_cajero_cobro = {$payment_id}";
			$stm = $this->link->query( $sql ) or die( "Error al anular el cobro del cajero : {$this->link->error}" );
			$sql = "INSERT INTO ec_cajero_cobros ( id_sucursal, id_pedido, id_devolucion, id_cajero, id_sesion_caja, id_afiliacion, id_terminal, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, cobro_cancelado, sincronizar ) 
				SELECT 
					id_sucursal, 
					id_pedido, 
					id_devolucion, 
					id_cajero, 
					id_sesion_caja, 
					id_afiliacion, 
					id_terminal, 
					id_banco, 
					3, 
					(monto*-1), 
					NOW(), 
					NOW(), 
					CONCAT( 'Cobro para anular el cobro ', id_cajero_cobro, ' -{$payment_type}-' ), 
					1, 
					1
				FROM ec_cajero_cobros WHERE id_cajero_cobro = {$payment_id}";
			$stm = $this->link->query( $sql ) or die( "Error al re-insertar el cobro {$this->link->error}" );
			$cobro_id = $this->link->insert_id;
		//inserta cobro por anulacion
			$sql = "UPDATE ec_pedido_pagos SET pago_cancelado = 1, referencia = 'Pago anulado por el usuario' WHERE id_cajero_cobro = {$payment_id}";
			$stm = $this->link->query( $sql ) or die( "Error al anular el pago : {$this->link->error}" );
			$sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_cajero_cobro, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
				id_nota_credito, id_cxc, exportado, es_externo, id_cajero, sincronizar, id_sesion_caja, pago_cancelado )
				SELECT
					id_pedido, 
					{$cobro_id}, 
					3, 
					NOW(), 
					NOW(), 
					(monto * -1), 
					CONCAT( 'Pago para anular el pago ', id_pedido_pago ), 
					id_moneda, 
					tipo_cambio, 
					id_nota_credito, 
					id_cxc, 
					exportado, 
					es_externo, 
					id_cajero, 
					sincronizar, 
					id_sesion_caja, 
					1
				FROM ec_pedido_pagos WHERE id_cajero_cobro = {$payment_id}";
			$stm = $this->link->query( $sql ) or die( "Error al re-insertar el pago {$this->link->error}" );
			$this->link->autocommit( true );
			die( 'ok' );
		}
//Afiliaciones
		public function obtenerListaAfiliaciones( $session_id, $user_id, $store_id ){
			$resp = "<select class=\"form-select\" id=\"afiliacion_combo_tmp\">
			<option value=\"0\">--Seleccionar--</option>";
			$sql = "SELECT 
					a.id_afiliacion AS afiliation_id,
					a.no_afiliacion AS afiliation_number
				FROM ec_afiliaciones a
				/*LEFT JOIN ec_afiliaciones_cajero ac 
				ON ac.id_afiliacion=a.id_afiliacion*/
				LEFT JOIN ec_afiliacion_sucursal afs
				ON afs.id_afiliacion = a.id_afiliacion
				LEFT JOIN ec_sesion_caja_afiliaciones sca
				ON sca.id_afiliacion = a.id_afiliacion
				AND sca.id_sesion_caja = {$session_id}
				WHERE /*ac.id_cajero='{$user_id}'
				AND */sca.id_afiliacion IS NULL 
				AND afs.estado_suc=1
				AND afs.id_sucursal = {$store_id}";//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar las terminales : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<option value=\"{$row['afiliation_id']}\">{$row['afiliation_number']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function obtenerListaAfiliacionesActuales( $session_id ){
			$resp = "<table class=\"table fs-5\">
				<thead>
					<tr>
						<th class=\"text-center\">Terminal</th>
						<th class=\"text-center\">Habilitada</th>
						<th class=\"text-center\">Cobro<br>único</th>
					</tr>
				<thead>
				<tbody id=\"afiliations_table_body\">";
			$sql = "SELECT
						sca.id_sesion_caja_afiliaciones AS sesion_afiliation_id,
						a.id_afiliacion AS afiliation_id,
						a.no_afiliacion AS afiliation_number,
						sca.habilitado AS enabled,
						sca.insertada_por_error_en_cobro AS is_per_error
					FROM ec_sesion_caja_afiliaciones sca
					LEFT JOIN ec_afiliaciones a
					ON a.id_afiliacion = sca.id_afiliacion
					WHERE sca.id_sesion_caja = {$session_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar afiliaciones de la sesion acual : {$this->link->error} {$sql}" );
			$counter = 0;
			while( $row = $stm->fetch_assoc() ){
				$enabled = ( $row['enabled'] == 1 ? 'checked' : '');
				$is_per_error = ( $row['is_per_error'] == 1 ? 'checked' : '');
				$disabled = ( $row['is_per_error'] == 1 ? 'disabled' : '');
				$resp .= "<tr>
					<td id=\"afiliation_0_{$counter}\"  class=\"text-center no_visible\">
						{$row['sesion_afiliation_id']}
					</td>
					<td id=\"afiliation_1_{$counter}\" afiliation_id=\"{$row['afiliation_id']}\" class=\"text-center\">
						{$row['afiliation_number']}
					</td>
					<td class=\"text-center\">
						<input id=\"afiliation_2_{$counter}\" type=\"checkbox\" {$enabled} onclick=\"checkAfiliationSesion( this, {$row['afiliation_id']} );\" {$disabled}>
					</td>
					<td class=\"text-center\">
						<input id=\"afiliation_3_{$counter}\" type=\"checkbox\" {$is_per_error} onclick=\"checkTerminalError( this, {$row['afiliation_id']} );\">
					</td>
				</tr>";
				$counter ++;
			}
			$resp .= "</tbody>
			</table>";
			//die($resp);
			return $resp;
		}

		public function guardaAfiliacionSesion( $session_id, $user_id, $afiliations ){
			$afiliations = explode( "|~|", $afiliations );
			foreach ( $afiliations as $key => $afiliation ) {
				$afiliation = explode( "|", $afiliation );
				if( $afiliation[0] == '' || $afiliation[0] == null ){//si no tiene id de detalle
					$sql = "INSERT INTO ec_sesion_caja_afiliaciones ( id_sesion_caja, id_cajero, id_afiliacion, habilitado, insertada_por_error_en_cobro )
					VALUES ( '{$session_id}', '{$user_id}', '{$afiliation[1]}', '{$afiliation[2]}', '{$afiliation[3]}' )";//die( $sql );
				}else{//si ya tiene id de detalle y es actulizacion
					$sql = "UPDATE ec_sesion_caja_afiliaciones SET habilitado = {$afiliation[2]}, insertada_por_error_en_cobro = {$afiliation[3]} 
					WHERE id_sesion_caja_afiliaciones = {$afiliation[0]}";
				}
				$stm = $this->link->query( $sql ) or die( "Error al agregar/ actualizar afiliacion a la sesion de caja actual : {$sql} : {$this->link->error}" );
			//sincronizacion de afiliaciones por sesion de caja
				if( $afiliation[0] == '' || $afiliation[0] == null ){//si no tiene id de detalle
				//consulta el id insertado de la afiliacion por sesion de caja
					$sql = "SELECT MAX( id_sesion_caja_afiliaciones ) AS last_id FROM ec_sesion_caja_afiliaciones";
					$stm = $this->link->query( $sql ) or die( "Error al consultar el ultimo id insertado en ec_sesion_caja_afiliaciones : {$sql} : {$this->link->error}" );
					$row = $stm->fetch_assoc();
					$id_sesion_caja_afiliacion = $row['last_id'];
				//envia a crear registro JSNO de sincronizacion
					$sql = "CALL SincronizacionSesionCajaAfiliaciones(  'insert', {$id_sesion_caja_afiliacion} );";
					$stm = $this->link->query( $sql ) or die( "Error al ejecutar procedure para sincronizar terminal en sesion de caja : {$sql} : {$this->link->error}" );
				}
			}
			return 'ok';
		}

		public function checkAfiliationSesion( $enabled, $session_terminal_id ){
			$sql = "UPDATE ec_sesion_caja_afiliaciones SET habilitado = '{$enabled}' WHERE id_sesion_caja_afiliaciones = {$session_terminal_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el satatus de afiliacion en la sesion de caja : {$this->link->error}" );
			return "Status de terminal actualizado exitsamente.";
		}

	//Terminales
		public function obtenerListaTerminales( $session_id, $user_id, $store_id ){
			$resp = "<select class=\"form-select\" id=\"terminal_combo_tmp\">
			<option value=\"0\">--Seleccionar--</option>";
			$sql = "SELECT
						tis.id_terminal_integracion AS teminal_id,
						tis.nombre_terminal AS terminal_name
					FROM ec_terminales_integracion_smartaccounts tis
					LEFT JOIN ec_terminales_sucursales_smartaccounts tss
					ON tss.id_terminal = tis.id_terminal_integracion
					/*LEFT JOIN ec_terminales_cajero_smartaccounts tcs 
					ON tis.id_terminal_integracion = tcs.id_terminal*/
					WHERE tis.id_terminal_integracion NOT IN( SELECT id_terminal FROM ec_sesion_caja_terminales WHERE id_sesion_caja = '{$session_id}' )
					/*AND tcs.id_cajero = '{$user_id}'*/
					AND tss.estado_suc = 1
					AND tss.id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar las terminales : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<option value=\"{$row['teminal_id']}\">{$row['terminal_name']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function obtenerListaTerminalesActuales( $session_id ){
			$resp = "<table class=\"table fs-5\">
				<thead>
					<tr>
						<th class=\"text-center\">Terminal</th>
						<th class=\"text-center\">Habilitada</th>
					</tr>
				<thead>
				<tbody>";
			$sql = "SELECT
						sct.id_sesion_caja_terminales AS terminal_session_id,
						tis.id_terminal_integracion AS terminal_id,
						tis.nombre_terminal AS terminal_name,
						sct.habilitado AS enabled
					FROM ec_sesion_caja_terminales sct
					LEFT JOIN ec_terminales_integracion_smartaccounts tis
					ON tis.id_terminal_integracion = sct.id_terminal
					WHERE sct.id_sesion_caja = {$session_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar afiliaciones de la sesion actual : {$this->link->error} {$sql}" );
			while( $row = $stm->fetch_assoc() ){
				$enabled = ( $row['enabled'] == 1 ? 'checked' : '');
				$resp .= "<tr>
					<td class=\"text-center\">{$row['terminal_name']}</td>
					<td class=\"text-center\"><input type=\"checkbox\" {$enabled} onclick=\"checkTerminalSesion( this, {$row['terminal_session_id']} );\"></td>
				</tr>";
			}
			$resp .= "</tbody>
			</table>";
			//die($resp);
			return $resp;
		}

		public function agregarTerminalSesion( $session_id, $user_id, $terminal_id, $es_error = 0 ){
			$sql = "INSERT INTO ec_sesion_caja_terminales ( id_sesion_caja, id_cajero, id_terminal, habilitado )
			VALUES ( '{$session_id}', '{$user_id}', '{$terminal_id}', 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al agregar terminal a la sesion de caja actual : {$this->link->error}" );
		//sincronizacion de terminales por sesion de caja
			//consulta el id insertado de la terminal por sesion de caja
				$sql = "SELECT MAX( id_sesion_caja_terminales ) AS last_id FROM ec_sesion_caja_terminales";
				$stm = $this->link->query( $sql ) or die( "Error al consultar el ultimo id insertado en ec_sesion_caja_terminales : {$sql} : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$id_sesion_caja_terminal = $row['last_id'];
			//envia a crear registro JSNO de sincronizacion
				$sql = "CALL SincronizacionSesionCajaTerminales(  'insert', {$id_sesion_caja_terminal} );";
				$stm = $this->link->query( $sql ) or die( "Error al ejecutar procedure para sincronizar terminal en sesion de caja : {$sql} : {$this->link->error}" );
			return 'ok';
		}

		public function checkTerminalSesion( $enabled, $session_terminal_id ){
			$sql = "UPDATE ec_sesion_caja_terminales SET habilitado = '{$enabled}' WHERE id_sesion_caja_terminales = {$session_terminal_id}";//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el status de terminal en la sesion de caja : {$this->link->error}" );
			return "Status de terminal actualizado exitsamente.";
		}

		public function getSaleData( $clave, $sale_folio, $user_id, $log_id = null ){
		//consulta si tiene devolucion pendiente
			$sql = "SELECT
						prd.id_sesion_caja_pedido_relacionado,
						p.folio_nv,
						p1.folio_nv AS folio_origen
					FROM ec_pedidos_relacion_devolucion prd
					LEFT JOIN ec_pedidos p
					ON p.id_pedido = prd.id_pedido_relacionado
					LEFT JOIN ec_pedidos p1
					ON p1.id_pedido = prd.id_pedido_original
					WHERE prd.id_pedido_original = {$clave}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si tiene devolucion pendiente : {$sql} : {$this->link->error}");
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				if( $row['id_sesion_caja_pedido_relacionado'] == 0 ){
					die( "<div class=\"text-center\">
							<h2 class=\"\">El ticket <b class=\"text-danger\">{$row['folio_origen']}</b> tiene una devolución pendiente, escanea el ticket <b class=\"text-success\">{$row['folio_nv']}</b> para continuar.</h2>
							<br><br>
							<button 
								type=\"\button\"
								class=\"btn btn-success\"
								onclick=\"location.reload();\"
							>Aceptar y recargar página
							</button>
						</div>" );//definir mensaje
				}
			}
		//consulta el id de sucursal de acceso 
			$sql = "SELECT id_sucursal AS system_type FROM sys_sucursales WHERE acceso = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el tipo de sistema : {$sql} : {$this->link->error}");
			$row = $stm->fetch_assoc();
			$TRANSACCIONES_POR_NOTIFICAR = array();
			if( $row["system_type"] > 0 && ( $sale_folio != '' && $sale_folio != null ) ){//si es diferente de linea
				$TRANSACCIONES_POR_NOTIFICAR['entra_en_condicion'] = true;
			//verifica si tiene transacciones pendientes
				$sql = "SELECT folio_unico AS unique_folio FROM vf_transacciones_netpay WHERE folio_venta = '{$sale_folio}' AND notificacion_vista = 0";
				$stm = $this->link->query( $sql );
			/*Logger*/
				if( $log_id != null ){
					$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consulta las transacciones pendientes ", $sql );
				}
				if( $this->link->error ){
					if( $log_id != null ){
						$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', $sale_folio, $sql, $this->link->error );
					}
					die( "Error al consultar las transacciones pendientes : {$this->link->error}" );
				}
				if( $stm->num_rows > 0){
					$TRANSACCIONES_POR_NOTIFICAR['transacciones_pendientes'] = array();
					$pending_transactions = array();
					while ( $row = $stm->fetch_assoc() ){
						$pending_transactions[] = $row;
						array_push( $TRANSACCIONES_POR_NOTIFICAR['transacciones_pendientes'], $row );
					}
					$sql = "SELECT value AS api_path FROM api_config WHERE name = 'path'";
					$stm = $this->link->query( $sql );// or die( "Error al consultar las transacciones pendientes : {$this->link->error}" );
				/*Logger*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Entra en transacciones pendientes y consulta path de api", $sql );
					}
					if( $this->link->error ){
						if( $log_id != null ){
							$steep_log_error = $this->Logger->insertErrorSteepRow( $steep_log_id, 'api_config', 'path', $sql, $this->link->error );
						}
						die( "Error al consultar path de api : {$this->link->error}" );
					}
					$row = $stm->fetch_assoc();
					$url = "{$row["api_path"]}/rest/netPay/consultar_transacciones_por_folio";
					$post_data = json_encode( array("transactions"=> $pending_transactions ) );
					//die( $post_data );
				/*Logger*/
					if( $log_id != null ){
						$steep_log_id = $this->Logger->insertLoggerSteepRow( $log_id, "Consume api para recuperar datos en {$url}", $post_data );
					}
				//consumir el API ( CURL )
					$results = json_decode( $this->sendPetition( $url, $post_data, $token = '' ), true );
					//var_dump( $results	 );die( "stop" );
					if( $results['status'] != null && $results['status'] != '' ){				
						$archivo_path = "../../../../../conexion_inicial.txt";
						$host = '';
						$carpeta_path = '';
						if(file_exists($archivo_path)){
							$file = fopen($archivo_path,"r");
							$line=fgets($file);
							fclose($file);
							$config=explode("<>",$line);
							$tmp=explode("~",$config[2]);
							$ruta_or=$tmp[0];
							$ruta_des=$tmp[1];
							$tmp_=explode("~",$config[0]);
							$host = base64_decode( $tmp_[0] );
							$carpeta_path = base64_decode( $tmp_[1] );
						}else{
							die("No hay archivo de configuración.");
						}
						$update_endpoint = "{$host}/{$carpeta_path}/rest/netPay/actualizar_datos_transacciones";
					//consulta token
						$user_token = $this->checkAccess( $user_id );
						$user_token = $user_token['token'];
						//die( $user_token );
						foreach ($results['transacciones'] as $key => $transaction) {
							$post_data = json_encode( $transaction );
							$update = $this->sendPetition( $update_endpoint, $post_data, $user_token );
						}
					}
				}
			}
	$CONSULTAS_SQL = array();
			//$clave=$_POST['valor'];
			$monto_saldo_a_favor = 0;
			$monto_saldo_tomado = 0;
			$id_venta_origen = 0;
		//consulta si tiene saldo a favor
			$sql = "SELECT
					SUM(saldo_a_favor) AS monto_saldo_a_favor,
					id_pedido_original AS id_venta_origen
				FROM ec_pedidos_relacion_devolucion
				WHERE id_pedido_relacionado = {$clave}
				AND id_sesion_caja_pedido_relacionado = 0";
	$CONSULTA_SALDO_FAVOR = $sql;
			$tmp = "";
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : {$this->link->error}" );
			if( $stm_1->num_rows > 0 ){
				$tmp = $stm_1->fetch_assoc();
				$monto_saldo_a_favor = $tmp['monto_saldo_a_favor'];
			}
	$CONSULTAS_SQL[] = array( "CONSULTA_SALDO_FAVOR"=>$CONSULTA_SALDO_FAVOR, "resultado"=>$tmp );
		//consulta si tiene pedido relacionado
			$sql = "SELECT
					id_pedido_original AS id_venta_origen
				FROM ec_pedidos_relacion_devolucion
				WHERE id_pedido_relacionado = {$clave}";
	$CONSULTA_VENTA_RELACIONADA = $sql;
	$CONSULTAS_SQL[] = array( "CONSULTA_VENTA_RELACIONADA"=>$CONSULTA_VENTA_RELACIONADA );
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : {$this->link->error}" );
			if( $stm_1->num_rows > 0 ){
				$tmp = $stm_1->fetch_assoc();
				$id_venta_origen = $tmp['id_venta_origen'];
			}
		//consulta si tiene saldo tomado
			$sql = "SELECT
					SUM(saldo_a_favor) AS monto_saldo_tomado,
					id_pedido_original AS id_venta_origen
				FROM ec_pedidos_relacion_devolucion
				WHERE id_pedido_original = {$clave}
				AND id_sesion_caja_pedido_relacionado = 0";
	$CONSULTA_SALDO_TOMADO = $sql;
	$CONSULTAS_SQL[] = array( "CONSULTA_SALDO_TOMADO"=>$CONSULTA_SALDO_TOMADO );
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : {$this->link->error}" );
			if( $stm_1->num_rows > 0 ){
				$tmp = $stm_1->fetch_assoc();
				$monto_saldo_tomado = $tmp['monto_saldo_tomado'];
			}

		//checamos los pagos pendientes de cobrar
			$sql="SELECT
					p.id_pedido AS id_venta,
					p.folio_nv AS folio_venta,
					IF( p.pagado = 0 AND pp.id_pedido_pago IS NULL, p.monto_pago_inicial, p.total ) AS pagos_pendientes,
					REPLACE( p.id_devoluciones, '~', ',' ) AS devoluciones_relacionadas,
					SUM( IF( pp.id_pedido_pago IS NULL , 0, pp.monto ) ) AS pagos_registrados,
					p.total AS total_nota
				FROM ec_pedidos p
				LEFT JOIN ec_pedido_pagos pp 
				ON p.id_pedido = pp.id_pedido
				WHERE p.id_pedido = {$clave}
				GROUP BY p.id_pedido";
	$CONSULTA_PAGOS_PENDIENTES_DE_COBRAR = $sql;
			$eje=$this->link->query($sql) or die("Error al consultar los datos del pedido.\n {$this->link->error}" );
			$r=$eje->fetch_assoc();

	$CONSULTAS_SQL[] = array( "CONSULTA_PAGOS_PENDIENTES_DE_COBRAR"=>$CONSULTA_PAGOS_PENDIENTES_DE_COBRAR, "RESULTADO"=>$r );
			$sql = "SELECT 
					ROUND( total_venta, 2 ) AS total_venta,
					ROUND( monto_venta_mas_ultima_devolucion, 2 ) AS monto_venta_mas_ultima_devolucion
				FROM ec_pedidos_referencia_devolucion
				WHERE id_pedido = {$clave}";//die( $sql );
			$reference_stm = $this->link->query( $sql ) or die( "Error al consultar la referencia de la venta y devolucion  : {$this->link->error}" );
			$reference_row = $reference_stm->fetch_assoc();
			$r['total_real'] = round( $r['total_nota'], 2 );
			$r['total_nota'] = $reference_row['monto_venta_mas_ultima_devolucion'];

	$CONSULTA_REFERENCIA_DEVOLUCION = $sql;
	$CONSULTAS_SQL[] = array( "CONSULTA_REFERENCIA_DEVOLUCION"=>$CONSULTA_REFERENCIA_DEVOLUCION );
		//checamos si hay devoluciones que dependan de este pedido y no esten pagadas
			$condicion_devoluciones = "IN('{$r['devoluciones_relacionadas']}')";
			$caso = 1;//no cobrada
			$tiene_devolucion = 0;
			if( $r['pagos_registrados'] == '' || $r['pagos_registrados'] == null ){
				$r['pagos_registrados'] = '0';
			}
			if( $r['pagos_registrados'] < $r['total_nota'] && $r['pagos_registrados'] > 0 ){//pagos < total_venta ( pagada parcialmente )
				$caso = 2;
			}else if( $r['pagos_registrados'] >= $r['total_nota'] ){//pagos >= total_venta ( pagada completamente )
				$caso = 3;
				if( $r['pagos_registrados'] > $r['total_nota'] ){
					$tiene_devolucion = 1;
				}
			}
		//verifica si tiene una devolucion relacionada y el status de esta
			$sql="SELECT 
						IF( d.id_devolucion IS NULL, 0, d.id_devolucion ) As id_devolucion,
						ROUND( SUM( IF( d.id_devolucion IS NULL, 0, d.monto_devolucion ) ), 2 ) AS monto_devolucion,
						ROUND( SUM( IF( dp.id_devolucion IS NULL ,0, dp.monto ) ), 2 ) AS pagos_devolucion,
						IF( d.id_devolucion IS NULL, '', d.status ) AS status
					FROM ec_devolucion d
					LEFT JOIN ec_devolucion_pagos dp
					ON dp.id_devolucion = d.id_devolucion 
					WHERE d.id_pedido = {$r['id_venta']} 
						AND d.id_cajero = 0
						AND d.id_sesion_caja = 0
					GROUP BY d.id_devolucion";
	$CONSULTA_VERIFICA_STATUS_DEVOLUCION = $sql;
	$CONSULTAS_SQL[] = array( "CONSULTA_VERIFICA_STATUS_DEVOLUCION"=>$CONSULTA_VERIFICA_STATUS_DEVOLUCION );
			$eje = $this->link->query($sql)or die("Error al consultar las devoluciones relacionadas a esta nota.\n{$this->link->error} : {$sql}" );
			if( $eje->num_rows > 0 ){
				$rd = $eje->fetch_assoc();
				if( $rd['status'] != 3 && $rd['status'] != '' ){
					die( "No se puede hacer un cobro sobre una nota con devolucion pendiente, finaliza la devolucion y vuelve a intentar. {$rd[1]}" );
				}
			}
	//verifica si hay una devolucion ligada al pedido sin cajero
				$sql = "SELECT 
							d.id_devolucion AS id_devolucion,
							d.folio AS folio_devolucion, 
							SUM( dp.monto ) AS monto_pagos_devolucion,
							d.observaciones,
							SUM( d.monto_devolucion ) AS monto_devolucion
						FROM ec_devolucion d 
						LEFT JOIN ec_devolucion_pagos dp
						ON d.id_devolucion = dp.id_devolucion
						WHERE d.id_pedido = '{$clave}'
						AND d.id_cajero = 0
						AND d.id_sesion_caja = 0
						GROUP BY d.id_pedido";
	$CONSULTA_DEVOLUCION_SIN_CAJERO = $sql;
	$CONSULTAS_SQL[] = array( "CONSULTA_DEVOLUCION_SIN_CAJERO"=>$CONSULTA_DEVOLUCION_SIN_CAJERO );
				$return_stm = $this->link->query( $sql ) or die( "Error al consultar si hay una devolucion pendiente : {$this->link->error}" );
				if( $return_stm->num_rows > 0 ){
					$return_row = $return_stm->fetch_assoc();
					if( $return_row['observaciones'] == 'Dinero regresado al cliente' 
						&& $return_row['monto_devolucion'] > $return_row['monto_pagos_devolucion'] ){
						$pending_ammount = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
					}
				}
	//verifica si hay una devolucion ligada al pedido sin cajero
			$sql = "SELECT 
						SUM( dp.monto ) AS monto_pagos_devolucion
					FROM ec_devolucion d 
					LEFT JOIN ec_devolucion_pagos dp
					ON d.id_devolucion = dp.id_devolucion
					WHERE d.id_pedido = '{$clave}'
					GROUP BY d.id_pedido";
	$CONSULTA_DEVOLUCION_RELACIONADA = $sql;
			$return_stm = $this->link->query( $sql ) or die( "Error al consultar pagos de devolucion : {$this->link->error}" );
			$return_row_2 = "";
			if( $return_stm->num_rows > 0 ){
				$return_row_2 = $return_stm->fetch_assoc();
				$return_row['monto_pagos_devolucion'] = $return_row_2['monto_pagos_devolucion'];
			}
	$CONSULTAS_SQL[] = array( "CONSULTA_DEVOLUCION_RELACIONADA"=>$CONSULTA_DEVOLUCION_RELACIONADA, "resultado"=>$return_row_2 );

			$return_row['monto_devolucion'] = ( $return_row['monto_devolucion'] == '' || $return_row['monto_devolucion'] == null ? 0 : $return_row['monto_devolucion'] );
			$return_row['monto_pagos_devolucion'] = ( $return_row['monto_pagos_devolucion'] == '' || $return_row['monto_pagos_devolucion'] == null ? 0 : $return_row['monto_pagos_devolucion'] );
			$r['pagos_pendientes'] = $r['total_nota'] - ( $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'] - $monto_saldo_tomado ) - $return_row['monto_devolucion'] - $monto_saldo_a_favor;
			if($rd[0]==''){
				$rd[0]=0;
			}
			$r['pagos_registrados'] = $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'];
			$FORMULA_PAGOS_COBRADOS = "{$r['pagos_registrados']} = {$r['pagos_registrados']} - {$return_row['monto_pagos_devolucion']}";
			$resp = json_encode( 
					array( 'id_venta'=>$r['id_venta'], 
						'transacciones_netpay_pendientes'=>$TRANSACCIONES_POR_NOTIFICAR,
						'folio_venta'=>$r['folio_venta'], 
						'total_venta'=>round( $r['total_nota'], 2 ),
						'pagos_cobrados'=>round( $r['pagos_registrados'], 2 ), 
						'FORMULA_PAGOS_COBRADOS'=>$FORMULA_PAGOS_COBRADOS,
						
						'id_devolucion'=>$return_row['id_devolucion'], 
						'monto_devolucion'=>round( $return_row['monto_devolucion'], 2 ), 
						'monto_pagos_devolucion'=>round( $return_row['monto_pagos_devolucion'], 2 ),
						'monto_saldo_a_favor'=>round( $monto_saldo_a_favor, 2 ),
						'pagos_pendientes'=>( ($r['pagos_pendientes'] >= -1 && $r['pagos_pendientes'] <= 1) ? '0' : $r['pagos_pendientes'] ), 
						'FORMULA_PAGOS_PENDIENTES'=>"(pagos_pendientes){$r['pagos_pendientes']} = (total_nota){$r['total_nota']} - ( (pagos_registrados){$r['pagos_registrados']} - (monto_pagos_devolucion){$return_row['monto_pagos_devolucion']} - (monto_saldo_tomado){$monto_saldo_tomado} ) - (monto_devolucion){$return_row['monto_devolucion']} - (monto_saldo_a_favor){$monto_saldo_a_favor}",
						
						'total_real'=>$r['total_real'],
						'id_venta_origen'=>$id_venta_origen,
						'monto_saldo_tomado'=>$monto_saldo_tomado,
						'CONSULTAS'=>$CONSULTAS_SQL
					)
				);
			die( "ok|{$resp}" );
		}
	}

?>