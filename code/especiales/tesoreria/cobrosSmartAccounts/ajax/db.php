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
		$Payments = new Payments( $link );
		$action = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
		switch ( $action ) {
			case 'sendPaymentPetition' :
				$apiUrl = $apiNetPay->getEndpoint( $terminal_id, 'endpoint_venta' );//"https://suite.netpay.com.mx/gateway/integration-service/transactions/sale";//http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale
				//die( 'here : ' . $apiUrl );
			//recibe variables
				$amount = ( isset( $_GET['amount'] ) ? $_GET['amount'] : $_POST['amount'] );
				$sale_folio = ( isset( $_GET['sale_folio'] ) ? $_GET['sale_folio'] : $_POST['sale_folio'] );
				$terminal_id = ( isset( $_GET['terminal_id'] ) ? $_GET['terminal_id'] : $_POST['terminal_id'] );
				$counter = ( isset( $_GET['counter'] ) ? $_GET['counter'] : $_POST['counter'] );
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
			//consume servicio de venta
				$req = $apiNetPay->salePetition( $apiUrl, $amount, $terminal_id, $user_id, 
					$sucursal_id, $sale_folio, $session_id );
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
				$session_id = ( isset( $_GET['session_id'] ) ? $_GET['session_id'] : $_POST['session_id'] );
				echo $Payments->insertCashPayment( $ammount, $sale_id, $user_id, $session_id );
			break;

			case 'getHistoricPayment' :
				$sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
				echo $Payments->getHistoricPayment( $sale_id );
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
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function getHistoricPayment( $sale_id ){
			$resp = "";
			$amount_payed = 0;
			$sql = "SELECT
						cc.id_cajero_cobro As payment_id,
						cc.monto AS amount,
						tp.nombre AS payment_type,
						CONCAT( cc.fecha, ' ', cc.hora ) AS datetime
					FROM ec_cajero_cobros cc
					LEFT JOIN ec_tipos_pago tp
					ON cc.id_tipo_pago = tp.id_tipo_pago
					WHERe cc.id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el historico de cobros : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$resp = "<table class=\"table table-bordered table-striped\">
					<thead>
						<tr>
							<th>Tipo Pago</th>
							<th>Monto</th>
							<th>Fecha / Hora</th>
						</tr>
					</thead>
					<tbody>";
				while( $row = $stm->fetch_assoc() ){
					$resp .= "<tr>
						<td>{$row['payment_type']}</td>
						<td>{$row['amount']}</td>
						<td>{$row['datetime']}</td>
					</tr>";
					$amount_payed += $row['amount'];
				}
				$resp .= "</tbody>
					<tfoot>
						<tr>
							<td colspan=\"3\" class=\"text-end\">Total pagado : $ {$amount_payed}</td>
						</tr>
					</tfoot>
					</table>";
			}
			return "ok|{$resp}";
		}

		public function insertCashPayment( $ammount, $sale_id, $user_id, $session_id ){
			$this->link->autocommit( false );
		//inserta el cobro del cajero en efectivo
			$sql = "INSERT INTO ec_cajero_cobros( id_cajero_cobro, id_pedido, id_cajero, id_afiliacion, id_banco, id_tipo_pago, 
				monto, fecha, hora, observaciones, sincronizar) 
			VALUES ( NULL, {$sale_id}, {$user_id}, -1, -1, 1, {$ammount}, NOW(), NOW(), '', 1)";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el cobro del cajero : {$this->link->error}" );
		//actualiza el pago
			$sql = "UPDATE ec_pedido_pagos 
						SET id_cajero = {$user_id}, 
						id_sesion_caja = {$session_id}
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

			$this->link->autocommit( true );
			return 'ok|';
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
					CONCAT( tis.nombre_terminal, ' - terminal : ', tis.numero_serie_terminal, ' - storeId :', rse.store_id_netpay ) AS afiliation_number
				FROM ec_terminales_integracion_smartaccounts tis
				LEFT JOIN ec_terminales_cajero_smartaccounts tcs
				ON tis.id_terminal_integracion = tcs.id_terminal
				LEFT JOIN ec_terminales_sucursales_smartaccounts tss
				ON tss.id_terminal = tcs.id_terminal
				LEFT JOIN vf_razones_sociales_emisores rse
				ON rse.id_razon_social = tss.id_razon_social
				WHERE tcs.id_cajero = '{$user_id}' 
				AND tcs.activo = 1
				AND tss.id_sucursal = {$store_id}";
			//$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
			$stm = $this->link->query( $sql ) or die( "Error al consultar las afiliaciones del cajero" );
			//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
			$tarjetas_cajero='';
			//$c=0;//Tarjeta {$c} : <br> <br>
			$resp .= "<tr>
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
								class=\"btn btn-danger no_visible\"
								onclick=\"cancelPayment( {$c}, {$r['afiliation_id']} );\"
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

	}

?>