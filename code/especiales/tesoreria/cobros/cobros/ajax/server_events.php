<?php
	include( '../../../../../../conexionMysqli.php' );
	header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");
	header("Connection: keep-alive");
	
	function getTransactionStatus( $transaction_id, $link ){
		$sql = "SELECT
					message
				FROM vf_transacciones_netpay
				WHERE id_transaccion_netpay = {$transaction_id}";
		$stm = $link->query( $sql );
		if( ! $stm ){
			    echo "data: Error al consultar status de la transaccion : {$link->error}\n\n";
			    // Flushea el búfer de salida para asegurarse de que el mensaje se envíe inmediatamente
			    ob_flush();
			    flush();
			return "Error al consultar status de la transaccion : {$link->error}";
		}else{
			//return $sql;
			$row = $stm->fetch_assoc();
			return $row['message'];
		}
	}

	// Establece un tiempo máximo de ejecución del script en infinito
	set_time_limit(0);

	// Puedes ajustar el intervalo de retransmisión como desees
	$retryInterval = 2000;
	$c = 0;

	while (true) {
	    // Genera un mensaje para enviar al cliente (puedes reemplazar esto con datos en tiempo real)
	  //  $message = "Mensaje en tiempo real :: {$_GET['fl']} ::" . date('Y-m-d H:i:s');

	    // Envia el mensaje al cliente
		if( $c >= 4 ){
			$message = getTransactionStatus( $_GET['transaction_id'], $link );
			if( $message != '' ){
			    echo "data: $message\n\n";
			    // Flushea el búfer de salida para asegurarse de que el mensaje se envíe inmediatamente
			    ob_flush();
			    flush();
			}
		}
		$c ++;
	    // Espera durante el intervalo antes de enviar otro mensaje
	    sleep($retryInterval / 1000);
	}
?>