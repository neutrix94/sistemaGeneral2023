<?php
	if( isset( $_GET['fl'] ) ){
		include( '../../../../conexionMysqli.php' );
		if( $_GET['fl'] == 'seekTransfer' ){
			$transfer = array();
			$products = array();
			$txt = trim( $_GET['txt'] );
		//consulta si exste la transferencia
			$sql = "SELECT 
						id_transferencia AS transfer_id,
						id_sucursal_destino AS destinity_store_id
					FROM ec_transferencias
					WHERE folio = '{$txt}'
					LIMIT 1";
//die( "{$sql}" );
			$stm = $link->query( $sql ) or die( "Error al consultar folio de transferencia : {$link->error}" );
		//
			if( $stm->num_rows > 0 ){
			//die('here');
				$transfer = $stm->fetch_assoc();
				$sql = "SELECT
							tp.id_producto_or AS product_id,
							p.nombre AS product_name,
							tp.cantidad AS quantity
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_productos p
						ON p.id_productos = tp.id_producto_or
						WHERE tp.id_transferencia = '{$transfer['transfer_id']}'";
//die( "{$sql}" );
				$stm2 = $link->query( $sql ) or die( "Error al consultar productos de transferencia : {$link->error}" );
				while ( $row = $stm2->fetch_assoc() ) {
					$products[] = $row;
				}
				echo "ok|" . json_encode( $products ) . "|" . json_encode( $transfer );
				return '';
			}else{
				die( "La transferencia con el folio : '{$tx}' no fue encontrda, verifica y vuvlve a intentar!" );
			}

		}
	}

	$nombre="formato_ejemplo_importacion_etiquetas.csv";
//generamos descarga
	header('Content-Type: aplication/octect-stream');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Disposition: attachment; filename="'.$nombre.'"');
	echo "Id Producto,Nombre producto, Cantidad Etiquetas\n";
	echo "1821,Serie LED 50 Luces Blanca C/Transparente 3.5M,2\n";
	echo "1822,Serie LED 50 Luces Calida c/Verde 6.5M,1\n";
	die('');//<script>window.close();</script>
?>