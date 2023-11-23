<?php
	if( isset( $_GET['bill_fl'] ) || isset( $_POST['bill_fl'] ) ){
		$action = ( isset( $_GET['bill_fl'] ) ? $_GET['bill_fl'] : $_POST['bill_fl'] );
		include( '../../../../conect.php' );
		include( '../../../../conexionMysqli.php' );
		$dB = new dB( $link );
		switch ( $action ) {
			case 'seek_costumer' :
			//die( 'here' );
				$rfc = strtoupper( isset( $_GET['rfc'] ) ? $_GET['rfc'] : $_POST['rfc'] );
				//die( $rfc );
				echo $dB->seek_costumer( $rfc );
			break;

			case 'seek_ticket' : 
				$ticket = strtoupper( isset( $_GET['ticket'] ) ? $_GET['ticket'] : $_POST['ticket'] );
				echo $dB->seek_ticket( $ticket );
			break;

			case 'get_payments' : 
				$tickets = strtoupper( isset( $_GET['tickets'] ) ? $_GET['tickets'] : $_POST['tickets'] );
				echo $dB->get_payments( $tickets );
			break;

			case 'update_payment_reference' : 
				$row_id = strtoupper( isset( $_GET['row_id'] ) ? $_GET['row_id'] : $_POST['row_id'] );
				$value = strtoupper( isset( $_GET['value'] ) ? $_GET['value'] : $_POST['value'] );
				echo $dB->update_payment_reference( $row_id, $value );
			break;

			case 'saveBills' :
				$cash = strtoupper( isset( $_GET['cash'] ) ? $_GET['cash'] : $_POST['cash'] );
				$card = strtoupper( isset( $_GET['card'] ) ? $_GET['card'] : $_POST['card'] );
				$tickets = strtoupper( isset( $_GET['tickets'] ) ? $_GET['tickets'] : $_POST['tickets'] );

				echo $dB->saveBills( $user_sucursal, $user_id, $cash, $card, $tickets );
			break;
			
			default:
				die( "Permission denied on '{$action}'!" );
			break;
		}
	}
	class dB
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function seek_costumer( $rfc ){
			$sql = "SELECT
						rfc
					FROM vf_clientes_razones_sociales_tmp
					WHERE rfc = '{$rfc}'";
//die("{$sql}");
			$stm = $this->link->query( $sql ) or die( "Error al buscar RFC de cliente : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				die( "ok|{$row['rfc']}" );
			}else{
				die( 'no' );
			}
		}

		public function seek_ticket( $ticket ){
			$sql = "SELECT
						folio_nv AS ticket,
						total AS total,
						facturado AS invoiced
					FROM ec_pedidos
					WHERE folio_nv = '{$ticket}'";
//die("{$sql}");
			$stm = $this->link->query( $sql ) or die( "Error al buscar ticket de venta : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				if( $row['invoiced'] == 1 ){
					die( "no|Esta nota de venta ya fue facturada!" );
				}
				die( "ok|{$row['ticket']}|{$row['total']}" );
			}else{
				die( 'no|La nota de venta no fue encontrada!' );
			}
		}

		public function get_payments( $tickets ){
			$resp = array();
			$sql = "SELECT
						cc.id_cajero_cobro AS paymet_detail_id,
						p.id_pedido AS sale_id,
						p.folio_nv AS sale_folio,
						cc.id_afiliacion AS afiliation_id,
						af.no_afiliacion AS afiliation_number,
						cc.monto AS amount,
						cc.fecha AS date,
						cc.hora AS hour,
						cc.observaciones AS reference
					FROM ec_cajero_cobros cc
					LEFT JOIN ec_pedidos p
					ON cc.id_pedido = p.id_pedido
					LEFT JOIN ec_afiliaciones af
					ON cc.id_afiliacion = af.id_afiliacion
					WHERE p.folio_nv IN( {$tickets} )"; //die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar pagos de nota(s) de venta(s) : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return "ok|" . json_encode( $resp );
		}

		public function update_payment_reference( $row_id, $value ){
			$sql = "UPDATE ec_cajero_cobros SET observaciones = '{$value}' WHERE id_cajero_cobro = {$row_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar referencia de pago : {$this->link->error}" );
			return 'ok';
		}

		public function saveBills( $store_id, $user_id, $cash, $card, $tickets ){
		//inserta el bloque de la factura
			$this->link->autocommit( false );
			$sql = "INSERT INTO vf_bloque_factura ( id_bloque_factura, id_sucursal, id_usuario, razon_social, 
				folio_unico, sincronizar ) VALUES( NULL, {$store_id}, {$user_id}, '', '', 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar bloque de facturacion : {$this->link->error}" );
			$block_id = $this->link->insert_id;
			$sales = array();
			if( $cash != "" ){
			//inserta facturas efectivo
				$cash_payment = explode( '|', $cash );
				foreach ($cash_payment as $key => $payment) {
					$payment_detail = explode( "~", $payment );
					$sql = "INSERT INTO vf_facturas( id_factura, id_bloque_factura, rfc_cliente, folio_venta, 
						monto_a_facturar, tipo_pago, referencia, folio_unico, sincronizar )
						VALUES( NULL, {$block_id}, '{$payment_detail[1]}', '{$folio}', '{$payment_detail[0]}', 1, '', '', 1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar factura ( efectivo ) : {$this->link->error}" );
				}
			}
			if( $card != "" ){
			//inserta facturas tarjeta
				$card_payment = explode( '|', $card );
				foreach ($card_payment as $key => $payment) {
					$payment_detail = explode( "~", $payment );
					//foreach ($payment_detail as $key => $detail) {
					$sql = "INSERT INTO vf_facturas( id_factura, id_bloque_factura, rfc_cliente, folio_venta, 
						monto_a_facturar, tipo_pago, referencia, folio_unico, sincronizar )
						VALUES( NULL, {$block_id}, '{$payment_detail[5]}', '{$payment_detail[0]}', '{$payment_detail[4]}',
						 7, '{$payment_detail[2]}', '', 1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar factura ( tajeta ) : {$this->link->error}" );
					//}
				}
			}
		//actualiza las notas de venta a facturadas
			$sql = "UPDATE ec_pedidos SET facturado = '1' WHERE folio_nv IN( {$tickets} )";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar ventas a facturadas : {$this->link->error}" );
			$this->link->autocommit( true );
			return 'ok';
		}
	}


?>