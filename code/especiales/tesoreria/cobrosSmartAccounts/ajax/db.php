<?php
	if( isset( $_GET['fl'] ) || isset( $_POST['fl'] ) ){
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
	//verifica si esta habilitada la funcion de SmartAccounts
		$sql = "SELECT 
					habilitar_smartaccounts_netpay AS is_smart_accounts
				FROM sys_sucursales s
				WHERE id_sucursal = {$sucursal_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar si esta habilitado SmartAccounts : {$link->error}" );
		$row = $stm->fetch_assoc();
		$is_smart_accounts = $row['is_smart_accounts'];
		//if( $row['is_smart_accounts'] == 0 ){
		//	include( '../../../netPay/apiNetPaySinSmartAccount.php' );//sin smartaccounts
		//}else{
			include( '../../../netPay/apiNetPay.php' );
		//}
	//
		$apiNetPay = new apiNetPay( $link, $sucursal_id );
		$Payments = new Payments( $link, $user_sucursal );
		$action = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
		switch ( $action ) {
			case 'sendPaymentPetition' :
				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_venta' );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/sale";//http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale
				//die( 'here : ' . $apiUrl );
			//recibe variables
				$amount = ( isset( $_GET['amount'] ) ? $_GET['amount'] : $_POST['amount'] );
				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );
				
		
				$validation = $Payments->validate_payment_is_not_bigger( $sale_folio, $amount );
				$terminal_id = ( isset( $_GET['terminal_id'] ) ? $_GET['terminal_id'] : $_POST['terminal_id'] );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : $_POST['counter'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
			//consume servicio de venta
				$req = $apiNetPay->salePetition( $apiUrl, $amount, $terminal_id, $user_id, 
					$sucursal_id, $sale_folio, $session_id );
	//die("here");
				$resp = json_decode( $req );
				if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
					$transaction_id = $resp->petition_id;
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
			break;

			case 'getTerminals' :
				$user_id = ( isset( $_GET['user_id'] ) ? $_GET['user_id'] : $_POST['user_id'] );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : $_POST['counter'] );
				echo $Payments->getTerminals( $user_id, $counter );
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
				if( isset( $_GET['pago_por_saldo_a_favor'] ) || isset( $_POST['pago_por_saldo_a_favor'] ) ){
					$pago_por_saldo_a_favor = ( isset( $_GET['pago_por_saldo_a_favor'] ) ? $_GET['pago_por_saldo_a_favor'] : $_POST['pago_por_saldo_a_favor'] );
				}
				if( isset( $_GET['ammount_permission'] ) || isset( $_POST['ammount_permission'] ) ){
					$ammount_permission = ( isset( $_GET['ammount_permission'] ) ? $_GET['ammount_permission'] : $_POST['ammount_permission'] );
				}
				if( isset( $_GET['id_venta_origen'] ) || isset( $_POST['id_venta_origen'] ) ){
					$id_venta_origen = ( isset( $_GET['id_venta_origen'] ) ? $_GET['id_venta_origen'] : $_POST['id_venta_origen'] );
				}
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				//if ( $ammount_permission == 0 ) {
					$validation = $Payments->validate_payment_is_not_bigger( $sale_id, $ammount );
				//}
				echo $Payments->insertCashPayment( $ammount, $sale_id, $user_id, $session_id, $pago_por_saldo_a_favor, $id_venta_origen );
			break;

			case 'getHistoricPayment' :
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				echo $Payments->getHistoricPayment( $sale_id );
			break;

			case 'seekTerminalByQr' :
				$qr_txt = ( isset( $_GET['qr_txt'] ) ? $_GET['qr_txt'] : $_POST['qr_txt'] );
				echo $Payments->seekTerminalByQr( $qr_txt, $sucursal_id );

			break;

			case 'setPaymentWhithouthIntegration' :
				$afiliation_id = ( isset( $_GET['afiliation_id'] ) ? $_GET['afiliation_id'] : $_POST['afiliation_id'] );
				$ammount = ( isset( $_GET['ammount'] ) ? $_GET['ammount'] : $_POST['ammount'] );
				$authorization_number = ( isset( $_GET['authorization_number'] ) ? $_GET['authorization_number'] : $_POST['authorization_number'] );
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				$validation = $Payments->validate_payment_is_not_bigger( $sale_id, $ammount );
				echo $Payments->setPaymentWhithouthIntegration( $afiliation_id, $ammount, $authorization_number, $sale_id, $session_id, $user_id );
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
				echo $Payments->delete_payment_saved( $payment_id );
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
		function __construct( $connection, $store_id )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
			
		}

		public function validate_payment_is_not_bigger( $sale_id, $ammount ){
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
			$stm = $this->link->query( $sql ) or die( "Error al consultar pagos para comprobacion : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){	//busqueda por folio
			
				$sql = "SELECT
						ROUND( p.total ) AS sale_total,
						ROUND( SUM( pp.monto ) ) AS payments_total,
						p.pagado AS was_payed
					FROM ec_pedidos p
					LEFT JOIN ec_pedido_pagos pp
					ON pp.id_pedido = p.id_pedido
					WHERE p.folio_nv = '{$sale_id}'
					GROUP BY p.id_pedido";//
				$stm = $this->link->query( $sql ) or die( "Error al consultar pagos para comprobacion : {$this->link->error}" );
			}
			if( $stm->num_rows == 0 ){
				die( "error|La nota de venta {$sale_id} no fue encontrada." );
			}

			$row = $stm->fetch_assoc();
			$sale_total = round( $row['sale_total'] );
			$payments_total = round( $row['payments_total'] );
			$rest = round( $row['sale_total'] - $row['payments_total'] );
			$tmp_total = round( $payments_total + $ammount );
			if( $sale_total < $tmp_total ){
				die( "<div class=\"row\" style=\"padding:15px;\">
						<h2 class=\"text-center text-danger\">El pago no puede ser mayor al total de la venta!</h2>
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
			/*
				<div class=\"row text-center\">
						<div class=\"col-3 text-primary\">
							Total : {$sale_total}
						</div>
						<div class=\"col-3 text-success\">
							Total Pagado : {$payments_total}
						</div>
						<div class=\"col-3 text-danger\">
							Restante : {$rest}
						</div>
						<div class=\"col-3 text-warning\">
							Monto Pago : {$ammount}
						</div>
			*/
			return 'ok';
		}

		public function validatePayments( $sale_id ){
			$saldo_favor = 0;
			$sql = "SELECT
						ROUND( SUM( monto_devolucion_interna ) ) AS a_favor
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
			$stm = $this->link->query( $sql ) or die( "Error al consultar los totales para validar : {$sql} : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['was_payed'] == 1 && round( $row['sale_total'] ) > round( $row['payments_total'] ) ){
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

		public function setPaymentWhithouthIntegration( $afiliation_id, $ammount, $authorization_number, $sale_id, $session_id, $user_id ){	
			$this->link->autocommit( false );
		//inserta el cobro del cajero en efectivo
			$sql_cc = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, {$afiliation_id}, -1, 7, {$ammount}, NOW(), NOW(), '{$authorization_number}', 1)";
			$stm_cc = $this->link->query( $sql_cc ) or die( "Error al insertar el cobro del cajero en setPaymentWhithouthIntegration: {$this->link->error}" );
			$id_cajero_cobro = $this->link->insert_id;//die( 'here' );
		//consulta entre interno y externo
		    $sql = "SELECT
						ROUND( ax.internal/ax.total, 2 ) AS internal_porcent,
						ROUND( ax.external/ax.total, 2 ) AS external_porcent
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
			$this->link->autocommit( true );
			return "ok|Pago registrado exitosamente!";
		}

		public function seekTerminalByQr( $qr_txt, $sucursal_id ){
			$sql = "SELECT
						a.id_afiliacion AS afiliation_id,
						a.no_afiliacion AS afiliation_number
					FROM ec_afiliaciones a
					LEFT JOIN ec_afiliacion_sucursal afs
					ON a.id_afiliacion = afs.id_afiliacion
					WHERE a.no_afiliacion = '{$qr_txt}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la afil;iacion  : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( "La terminal '{$qr_txt}' no fue encontrada!" );
			}else{
				$row = $stm->fetch_assoc();
				return "ok|" . json_encode( $row );
			}
		}	

		public function getHistoricPayment( $sale_id ){
			$resp = "";
			$amount_payed = 0;
			$sql = "SELECT
						cc.id_cajero_cobro AS payment_id,
						cc.monto AS amount,
						tp.nombre AS payment_type,
						CONCAT( cc.fecha, ' ', cc.hora ) AS datetime,
						cc.id_terminal AS terminal_id,
						cc.observaciones
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
					$button = "<button
								type=\"button\"
								class=\"btn btn-danger\"
								onclick=\"delete_payment_saved( {$row['payment_id']}, {$sale_id} );\"
								style=\"padding : 0px !important;\"
							>
								<i class=\"icon-cancel-circle\"></i>
							</button>";
					if( $row['amount'] < 0 ){
						$row['payment_type'] = "Devuelto al cliente";
						$color = "class=\"text-danger\"";
					}
					if( $row['terminal_id'] > 0 ){
						$sql_tmp = "SELECT id_transaccion_netpay FROM vf_transacciones_netpay WHERE orderId = '{$row['observaciones']}'";
						$stm_aux = $this->link->query( $sql_tmp ) or die( "Error al conultar id de transaccion : {$this->link->error}" );
						$aux_row = $stm_aux->fetch_assoc();
						$button = "<button
								type=\"button\"
								class=\"btn btn-warning\"
								style=\"padding : 0px !important;\"
								onclick=\"rePrintByOrderId( '{$aux_row['id_transaccion_netpay']}' );\"
							>
								<i class=\"icon-print-3\"></i>
							</button>";
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

		public function insertCashPayment( $ammount, $sale_id, $user_id, $session_id, $pago_por_saldo_a_favor = 0, $id_venta_origen = 0 ){
//die( "Monto : {$ammount} - pago_por_saldo_a_favor : $pago_por_saldo_a_favor - Id_venta Origen : {$id_venta_origen}" );
			$this->link->autocommit( false );
			//caso 0 : el cliente tiene saldo a favor, y su nueva nota es menor a su saldo a afavor
			if( $ammount < 0 && $pago_por_saldo_a_favor > 0 ){//die( 'caso 1' );
			//insertar pago
				$this->insertPayment( $pago_por_saldo_a_favor, $sale_id, $user_id, $session_id );
				$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id, ( $pago_por_saldo_a_favor * -1 )  );
				$this->insertReturnPayment( $ammount, $sale_id, $user_id, $session_id, $id_venta_origen );

			}else if( $ammount > 0 ){//die( "caso 2 : cobrar al cliente con dev o sin dev" );
				$ammount = $this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
				$this->insertPayment( $ammount, $sale_id, $user_id, $session_id );
			}else if( $ammount < 0 ){//die( "caso 3 : devolver efectivo al cliente cuando no se agregan productos" );
				$this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
			//insertar pago
				$this->insertReturnPayment( $ammount, $sale_id, $user_id, $session_id );
			}else if( $ammount == 0 ){//die( "caso 4 : no se devuleve dinero al cliente ni se cobra pero se inserta pago" );
				$ammount = $this->insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id );
				if( $ammount != 0 ){
					$this->insertPayment( $ammount, $sale_id, $user_id, $session_id );
				}
			}
			//$stm = $this->link->query( $sql ) or die( "Error al consultar la suma de los pagos : {$this->link->error}" );
		$this->link->autocommit( true );
			return 'ok|';
		}

		public function insertPaymentsDepending( $ammount, $sale_id, $user_id, $session_id, $saldo_especial = 0 ){
//echo "<br>INSERTPAYMENTDEPENDING<br>";
			$total_devolver_cajero = 0;
			$devolucion_interna = 0;
			$devolucion_externa = 0;
			$sql = "SELECT
						id_pedido_relacion_devolucion,
						id_pedido_original,
						monto_pedido_original,
						id_sesion_caja_pedido_orginal, 
						id_devolucion_interna,
						monto_devolucion_interna,
						id_devolucion_externa,
						monto_devolucion_externa,
						id_pedido_relacionado,
						monto_pedido_relacionado,
						id_sesion_caja_pedido_relacionado
					FROM ec_pedidos_relacion_devolucion
					WHERE id_pedido_relacionado = {$sale_id}
					AND id_sesion_caja_pedido_relacionado = 0";
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : {$this->link->error}" );
//echo $sql . "<br><br>";
			if( $stm_1->num_rows == 1 ){
				$row = $stm_1->fetch_assoc();
		//verifica que el pedido no sea un apartado
				$sql = "SELECT 
							p.pagado AS was_payed, 
							p.total AS sale_total, 
							SUM( pp.monto ) AS payments_amount
						FROM ec_pedidos p
						LEFT JOIN ec_pedido_pagos pp
						ON p.id_pedido = pp.id_pedido 
						WHERE p.id_pedido = {$row['id_pedido_relacionado']}";
				$stm_2 = $this->link->query($sql) or die( "Error al consultar si la venta esta pagada : {$this->link->error}" );
//echo $sql . "<br><br>";
				$row_2 = $stm_2->fetch_assoc();
			//inserta el pago de la devolucion interna
				if( $row['monto_devolucion_interna'] > 0 ){
					$sql = "INSERT INTO ec_devolucion_pagos( id_devolucion_pago, id_devolucion, id_tipo_pago, monto, es_externo, fecha, hora,
					id_cajero, id_sesion_caja ) VALUES( NULL, {$row['id_devolucion_interna']}, 1, {$row['monto_devolucion_interna']}, 0, 
					NOW(), NOW(), {$user_id}, {$session_id} )";
					$stm_3 = $this->link->query($sql) or die( "Error al insertar pago de devolucion interna : {$this->link->error}" );
//echo $sql . "<br><br>";
					$devolucion_interna = $this->link->insert_id;
					$total_devolver_cajero += $row['monto_devolucion_interna'];
				}
			//inserta el pago de la devolucion externa
				if( $row['monto_devolucion_externa'] > 0 ){
					$sql = "INSERT INTO ec_devolucion_pagos( id_devolucion_pago, id_devolucion, id_tipo_pago, monto, es_externo, fecha, hora,
					id_cajero, id_sesion_caja ) VALUES( NULL, {$row['id_devolucion_externa']}, 1, {$row['monto_devolucion_externa']}, 1, 
					NOW(), NOW(), {$user_id}, {$session_id} )";
					$stm_4 = $this->link->query($sql) or die( "Error al insertar pago de devolucion externa : {$this->link->error}" );
//echo $sql . "<br><br>";
					$devolucion_externa = $this->link->insert_id;
					$total_devolver_cajero += $row['monto_devolucion_externa'];
				}
				$total_devolver_cajero = round( $total_devolver_cajero ) * -1;
/*inserta el pago por devolucion en el cajero*/
			//inserta el cobro del cajero en efectivo por devolucion
				if( $saldo_especial == 0 ){
					$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
						monto, fecha, hora, observaciones, sincronizar) 
					VALUES ( NULL, {$this->store_id}, {$row['id_pedido_original']}, {$user_id}, {$session_id}, -1, -1, 1, {$total_devolver_cajero}, NOW(), NOW(), '', 1)";
				}else{
					$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
						monto, fecha, hora, observaciones, sincronizar) 
					VALUES ( NULL, {$this->store_id}, {$row['id_pedido_original']}, {$user_id}, {$session_id}, -1, -1, 1, {$saldo_especial}, NOW(), NOW(), '', 1)";
					
				}
				$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del cajero en insertPaymentsDepending : {$this->link->error}" );
				$id_cajero_cobro = $this->link->insert_id;
				
				$sql = "UPDATE ec_devolucion_pagos 
							SET id_cajero_cobro = {$id_cajero_cobro} 
						WHERE id_devolucion_pago IN( {$devolucion_interna}, {$devolucion_externa} )";
				$stm_update = $this->link->query( $sql ) or die( "Error al actualizar las devoluciones : {$this->link->error}" );
/**/			
//echo $sql . "<br><br>";
				$sql = "UPDATE ec_pedidos_relacion_devolucion 
					SET id_sesion_caja_pedido_relacionado = {$session_id} 
				WHERE id_pedido_relacionado = {$row['id_pedido_relacionado']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar ec_pedidos_relacion_devolucion de pagos : {$sql} {$this->link->error}" );
//echo $sql . "<br><br>";
			//modifica el monto para hacer cuadrar el pago por devolucion
				return $row_2['sale_total'];
			}else{
				return $ammount;
			}
		}

		public function insertPayment( $ammount, $sale_id, $user_id, $session_id, $type = 1 ){//$type = 1 ( efectivo )
			//die( 'insertPayment' );
//echo "<br>INSERTPAYMENT<br>";
//echo $sql . "<br><br>";
		//inserta el cobro del cajero en efectivo
			$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_sucursal, id_pedido, id_cajero, id_sesion_caja, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar ) 
			VALUES ( NULL, {$this->store_id}, {$sale_id}, {$user_id}, {$session_id}, -1, -1, {$type}, {$ammount}, NOW(), NOW(), '', 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del cajero en insertPayment : {$this->link->error}" );
			$id_cajero_cobro = $this->link->insert_id;

		//consulta entre interno y externo
		    $sql = "SELECT
		              ROUND( ax.internal/ax.total, 2 ) AS internal_porcent,
		              ROUND( ax.external/ax.total, 2 ) AS external_porcent
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

		public function insertReturnPayment( $ammount, $sale_id, $user_id, $session_id, $id_venta_origen = 0 ){
			$devolucion_interna = 0;
			$devolucion_externa = 0;
			//die( 'insertReturnPayment' );
//echo "<br>INSERTRETURNPAYMENT<br>";
		//dev interna / externa
			$sql = "(SELECT 
						id_devolucion
		               FROM ec_devolucion dev
		            WHERE id_pedido = {$sale_id}
		            AND es_externo = 0)
		            UNION
		            (SELECT 
						id_devolucion
		               FROM ec_devolucion dev
		            WHERE id_pedido = {$sale_id}
		            AND es_externo = 1)";
			$stm = $this->link->query( $sql ) or die( "Error al consultar devolucion interna/externa : {$this->link->error}" );
//echo $sql . "<br><br>";
			$row = $stm->fetch_row();
			$id_dev_interna = $row[0];
			$id_dev_externa = $row[1];
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
		    $datos_1[0] = round( $ammount * ( $datos_1[0] / $datos_1[2] ) )*-1;
		    $datos_1[1] = round( $ammount * ( $datos_1[1] / $datos_1[2] ) )*-1;
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
		        VALUES(null,$id_dev_interna,1,$datos_1[1],'$datos_1[0]',0,now(),now(), {$user_id}, {$session_id} )";//modificacion Oscar 2023/10/12 {$id_cajero}, {$id_sesion_caja}
		        $eje = $this->link->query( $sql ) or die( "Error al insertar el pago de la devolución interna\n{$sql}\n{$this->link->error}" );
		        $devolucion_interna = $this->link->insert_id;
//echo $sql . "<br><br>";
		   	//actualiza la sesion de la cabecera de devoluciones
				$sql = "UPDATE ec_devolucion SET id_cajero = {$user_id}, id_sesion_caja = {$session_id} 
				WHERE id_devolucion = {$id_dev_interna}";
		    	$eje = $this->link->query($sql) or die( "Error al actualizar cajero de la devolución interna\n{$sql}\n{$this->link->error}" );	
//echo $sql . "<br><br>";   		
		    }   
		    if( $id_venta_origen != 0 ){//si tiene saldo a favor de una venta origen
		    	$sale_id = 	$id_venta_origen;
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
						WHERE id_transaccion_netpay = {$transaction_id}";
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

		public function getTerminals( $user_id, $c = 0, $store_id = 1 ){
			$resp = "";
			$sql="SELECT 
					tis.id_terminal_integracion AS afiliation_id,
					CONCAT( tis.nombre_terminal, ' - terminal : ', tis.numero_serie_terminal, ' - storeId :', tis.store_id ) AS afiliation_number
				FROM ec_terminales_integracion_smartaccounts tis
				LEFT JOIN ec_terminales_cajero_smartaccounts tcs
				ON tis.id_terminal_integracion = tcs.id_terminal
				LEFT JOIN ec_terminales_sucursales_smartaccounts tss
				ON tss.id_terminal = tcs.id_terminal
				LEFT JOIN vf_razones_sociales_emisores rse
				ON rse.id_razon_social = tss.id_razon_social
				WHERE tcs.id_cajero = '{$user_id}' 
				AND tcs.activo = 1
				AND tss.estado_suc = 1
				AND tss.id_sucursal = {$store_id}";
			//$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
			$stm = $this->link->query( $sql ) or die( "Error al consultar las terminales del cajero : {$this->link->error}" );
			//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
			$tarjetas_cajero='';
			//$c=0;//Tarjeta {$c} : <br> <br>
			$resp .= "<tr id=\"card_payment_row_{$c}\">
				<td class=\"col-5\">
					<select id=\"tarjeta_{$c}\" class=\"form-select\">";
			while($r = $stm->fetch_assoc() ){
					$resp .= "<option value=\"{$r['afiliation_id']}\">{$r['afiliation_number']}</option>";
			}
			$resp .= "</select>
					</td>
					<td class=\"col-6\">
						<div class=\"input-group\">
							<input type=\"text\" class=\"form-control text-end\" id=\"t{$c}\" value=\"\" 
							onkeydown=\"prevenir(event);\" onkeyup=\"valida_tca(this,event,1,'.$c.');\">
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
					IF(p.ver=1 OR p.modificar=1,1,0)
				FROM sys_permisos p
				LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
				LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
				WHERE p.id_menu=200
				AND u.id_usuario={$user_id}";
			//die($sql);
			//$eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
			$stm = $this->link->query( $sql ) or die("Error al consultar el permiso de cajero : {$this->link->error}");
			//$es_cajero=mysql_fetch_row($eje);
			$es_cajero = $stm->fetch_row();
			if($es_cajero[0] == 0 ){
				die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
			}
		//validamos que haya una sesion de caja iniciada con este cajero; de lo contrario avisamos que no hay sesión de caja y no dejamos acceder a esta pantalla
			$sql="SELECT 
					count(id_sesion_caja) 
				FROM ec_sesion_caja 
				WHERE id_cajero=$user_id
				AND hora_fin='00:00:00' 
				AND fecha=current_date()";
		//	die($sql);
			//$eje=mysql_query($sql)or die("Error al verificar si ya existe una sesion de caja para este cajero!!!\n".mysql_error());
			$stm = $this->link->query( $sql ) or die( '<script>alert("Es necesario abrir caja antes de cobrar!!!");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>' );
			$r=$stm->fetch_row();
			if($r[0]!=1){
				die('<script>alert("Es necesario abrir caja antes de cobrar!!!");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>');
			}
		}

		public function getBoxesMoney( $store_id ){	
			$resp = "";
			$sql="SELECT bc.id_caja_cuenta,bc.nombre 
				FROM ec_caja_o_cuenta bc
				LEFT JOIN ec_caja_o_cuenta_sucursal bcs ON bc.id_caja_cuenta=bcs.id_caja_o_cuenta 
				WHERE bcs.estado_suc=1
				AND bcs.id_sucursal = '{$store_id}'";
			//$eje=mysql_query( $sql )or die("Error al listar los bancos o cajas!!!<br>".mysql_error());
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
						SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ) AS payments_amount
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

		public function delete_payment_saved( $payment_id ){
			$sql = "DELETE FROM ec_pedido_pagos WHERE id_cajero_cobro = {$payment_id}";
			$tm = $this->link->query( $sql ) or die( "Error al eliminar pago : {$this->link->error}" );
			$sql = "DELETE FROM ec_cajero_cobros WHERE id_cajero_cobro = {$payment_id}";
			$tm = $this->link->query( $sql ) or die( "Error al eliminar el cobro del cajero : {$this->link->error}" );
			die( 'ok' );
		}

	}

?>