<?php

//echo 'here';

	class reassignResolution
	{
		private $link;
		private $reception_block_id;
		function __construct( $connection, $reception_block ){
			$this->link = $connection;
			$this->reception_block_id = $reception_block;
		}

		public function getResoutionUniqueCodes(){
			$sql = "SELECT 

					FROM ec_";
			
		}	

		public function getResoutionProduct(){
			$resp = array();
			$sql = "SELECT 
						btr.id_bloque_transferencia_resolucion AS resolution_transfer_block_id,
						/*btr.id_bloque_transferencia_resolucion_detalle AS resolution_transfer_detail_block_id,*/
						btr.id_producto AS product_id,
						btr.id_proveedor_producto AS product_provider_id,
						btr.piezas_sobrantes AS excedent,
						btr.piezas_no_corresponden AS does_not_correspond,
						btr.piezas_se_quedan,
						btr.piezas_se_regresan,
						btr.piezas_faltaron
					FROM ec_bloques_transferencias_resolucion btr/*ec_bloques_transferencias_resolucion_detalle btr
					LEFT JOIN 
					ON btr.id_bloque_transferencia_resolucion = btrd.id_bloque_transferencia_resolucion*/
					WHERE btr.id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND ( btr.piezas_sobrantes != 0 OR btr.piezas_no_corresponden != 0 )";
//echo ( 'error|' . $sql );	
			$stm = $this->link->query( $sql ) or die( "Error al consultar los detalles del bloque : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
			return $resp;	
		}

		public function assignResolutionProductsToTransfer( $transfer_id ){
//echo "assignResolutionProductsToTransfer";
			$products = $this->getResoutionProduct();
			foreach ( $products as $key => $product ) {
				$assign_quantity = 0;
				$quantity_to_assign = 0;
				$type = "";
				if( $product['excedent'] != 0 ){
					$quantity_to_assign = $product['excedent'];
					$type = "excedent";
				}else if( $product['does_not_correspond'] != 0 ){
					$quantity_to_assign = $product['does_not_correspond'];
					$type = "does_not_correspond";
				}
			//consulta las piezas por asignar
				$sql = "SELECT 
							tp.id_transferencia_producto AS transfer_product_id,
							( tp.total_piezas_validacion - tp.total_piezas_recibidas ) AS pending_to_validate
						FROM ec_transferencia_productos tp
						WHERE tp.id_transferencia = {$transfer_id}
						AND tp.id_producto_or = {$product['product_id']}
						AND tp.id_proveedor_producto = {$product['product_provider_id']}";
//echo "<br><br>{$sql}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar los productos pendientes de validar : {$this->link->error}" );
//echo "<br><br>{$sql}";
				while ( $row = $stm->fetch_assoc() ){
					if( $row['pending_to_validate'] > $quantity_to_assign ){
						$assign_quantity = $quantity_to_assign;
					}
					if( $row['pending_to_validate'] < $quantity_to_assign ){
						$assign_quantity = $row['pending_to_validate'];
					}
					if( $row['pending_to_validate'] == $quantity_to_assign ){
						$assign_quantity = $quantity_to_assign;
					}
					if( $assign_quantity != 0 ){
						$quantity_to_assign -= $assign_quantity;
					//actualiza el detalle de transferencia
						$sql = "UPDATE ec_transferencia_productos 
									SET total_piezas_recibidas = ( total_piezas_recibidas + {$assign_quantity} ),
									cantidad_piezas_recibidas = ( cantidad_piezas_recibidas + {$assign_quantity} )
								WHERE id_transferencia_producto = {$row['transfer_product_id']}";
						$stm_upd = $this->link->query( $sql ) or die( "Error al actualizar {$this->link->error}" );
//echo "<br><br>{$sql}";
					//actualiza el detalle de la resolucion
						if( $quantity_to_assign > 0 ){
							$sql = "UPDATE ec_bloques_transferencias_resolucion SET ";
							$sql .= ( $type == "excedent" ? " piezas_sobrantes = ( piezas_sobrantes - {$assign_quantity} )" : "" );
							$sql .= ( $type == "does_not_correspond" ? " piezas_no_corresponden = ( piezas_no_corresponden - {$assign_quantity} )" : "" );
							$sql .= " WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
							$stm_upd = $this->link->query( $sql ) or die( "Error al actualizar {$this->link->error}" );
//echo "<br><br>{$sql}";
						}else{
							$sql = "DELETE FROM ec_bloques_transferencias_resolucion 
									WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
							$stm_del = $this->link->query( $sql ) or die( "Error al actualizar {$this->link->error}" );
//echo "<br><br>{$sql}";
						}
					/*actualiza la cabecera de resolucion
						$sql = "SELECT
									SUM( piezas_faltantes ),
									SUM( piezas_sobrantes ),
									SUM( piezas_no_corresponden )
								FROM ec_bloques_transferencias_resolucion_detalle
								WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
						$stm_final = $this->link->query( $sql )or die( "Error al consultar  las sumas en resolucion: {$this->link->error}" );
						$row_final = $stm_final->fetch_row();
						if( $row_final[0] == 0 && $row_final[1] == 0 && $row_final[2] == 0 ){
							$sql = "DELETE FROM ec_bloques_transferencias_resolucion 
									WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
							$stm_del = $this->link->query( $sql )or die( "Error al eliminar cabecera de resolucion: {$this->link->error}" );
echo "<br><br>{$sql}";
						}else{
							$sql = "UPDATE ec_bloques_transferencias_resolucion 
										SET piezas_faltantes = {$row_final[0]}, 
										piezas_sobrantes = {$row_final[1]}, 
										piezas_no_corresponden = {$row_final[2]}
									WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
							$stm_upd = $this->link->query( $sql )or die( "Error al actualizar cabecera de resolucion: {$this->link->error}" );
echo "<br><br>{$sql}";
						}*/

					//enlaza el codigo unico a la recepcion 
						$sql = "UPDATE ec_transferencia_codigos_unicos 
									SET insertado_por_resolucion = '0',
									id_bloque_transferencia_resolucion = NULL,
									id_bloque_transferencia_resolucion_detalle = NULL,
									id_bloque_transferencia_recepcion = {$this->reception_block_id}
								WHERE id_bloque_transferencia_resolucion = {$product['resolution_transfer_block_id']}";
						//AND id_bloque_transferencia_resolucion_detalle = {$product['resolution_transfer_detail_block_id']}
						$stm_upd = $this->link->query( $sql )or die( "Error al actualizar codigo unico de resolucion: {$this->link->error}" );
//echo "<br><br>{$sql}";

					}
				}

			}
			return true;
		} 	
	}
?>