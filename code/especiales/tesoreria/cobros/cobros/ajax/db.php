<?php
	include( '../../../../../../conexionMysqli.php' );
	$action = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
	switch ( $action ) {
		case 'sendPaymentPetition' :
			include( '../../../../netPay/apiNetPay.php' );
			$apiNetPay = new apiNetPay( $link );
			$apiUrl = "http://nubeqa.netpay.com.mx:3334/integration-service/transactions/sale";
			$amount = ( isset( $_GET['amount'] ) ? $_GET['amount'] : $_POST['amount'] );
			$req =  $apiNetPay->salePetition( $apiUrl, $amount );
			$resp = json_decode( $req );
			if( $resp->code == '00' && $resp->message == "Mensaje enviado exitosamente" ){
				include( '../vistas/formularioNetPay.php' );
			}
			return '';
		break;
			
		default :
			die( "Access denied on '{$action}'" );
		break;
	}


?>