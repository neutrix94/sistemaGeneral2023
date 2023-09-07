<?php
	//echo 'here';
	if( isset( $_GET['development'] ) ){
		include( '../../../conexionMysqli.php' );
		$locationByBarcode = new locationByBarcode( $link );
		echo $locationByBarcode->compareLocation( '1556', '01540 CJ20 11343' );//1459 CJ40 11205 0001
		//var_dump($example);
	}

	class locationByBarcode
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;		
		}


		public function compareLocation( $product_provider_id, $barcode ){
			$resp = "";
			$location_origin = $this->getProductProviderLocation( $product_provider_id );
			if( sizeof( $location_origin ) <= 0 ){//$location_origin['response']
				return "No hay ubicación para el proveedor producto {$product_provider_id}";
			}
			//var_dump($location_origin);
			$location_barcode = $this->getProductProviderLocation( $barcode );
			if( sizeof( $location_barcode ) <= 0  ){// $location_barcode['response']
				return "No hay ubicación para el código de barras {$product_provider_id}";
			}
		//verifica letra desde
			if( $location_origin['location_letter_to'] > $location_barcode['location_letter_since'] ){
				$resp .= "<p>Retrocede a la letra {$location_barcode['location_letter_since']}</p>";
			}else if( $location_origin['location_letter_since'] < $location_barcode['location_letter_since'] ){
				$resp .= "Avanza a la letra {$location_barcode['location_letter_since']}";

			}else if( $location_origin['location_letter_since'] == $location_barcode['location_letter_since'] ){
				$resp .= "La letra es correcta, el numero es incorrecto : ";
				if( $location_origin['location_number_since'] > $location_barcode['location_number_since'] ){
					$aux = $location_origin['location_number_since'] - $location_barcode['location_number_since'];
					$resp .= "<p>Retrocede {$aux} ubicaciones</p>";
				}else if (  $location_origin['location_number_to'] < $location_barcode['location_number_to'] ) {
					$aux = $location_barcode['location_number_since'] - $location_origin['location_number_since'];
					$resp .= "<p>Avanza {$aux} ubicaciones</p>";
				}else if( $location_origin['location_number_to'] == $location_barcode['location_number_to'] ){
					$resp .= "<p>La ubicacion es correcta, el pasillo es incorrecto : </p>";
					if( $location_origin['until_since'] > $location_barcode['until_since'] ){
						$aux = $location_barcode['until_since'] - $location_origin['until_since'];
						$resp .= "<p>Retrocede {$aux} pasillos</p>";
					}else if( $location_origin['until_since'] < $location_barcode['until_since'] ){
						$aux = $location_barcode['until_since'] - $location_origin['until_since'];
						$resp .= "<p>Avanza {$aux} pasillos</p>";
					}else if( $location_origin['until_since'] == $location_barcode['until_since'] ){
						$resp .= "<p>El pasillo es correcto, la altura es la incorrecta</p>";
						if( $location_origin['level_since'] > $location_barcode['level_since'] ){
							//$aux = $location_barcode['until_since'] - $location_origin['until_since'];
							$resp .= "<p>El modelo esta mas abajo, en la altura {$$location_origin['level_since']}</p>";
						}else if( $location_origin['level_since'] < $location_barcode['level_since'] ){
							$resp .= "<p>El modelo esta mas arriba, en la altura {$$location_origin['level_since']}</p>";

						}else if( $location_origin['level_since'] == $location_barcode['level_since'] ){

						}
					}
				}
			}

			return $resp;


			/*echo '<br>';
			var_dump($location_barcode);*/
			
		}
		
		public function getProductProviderLocation( $key ){
			$response = array(  );//'response' => 'no_exists_location'

			$key = $this->remove_unique_code_from_barcode( $key );

			$sql = "SELECT 
						/*0*/ppua.id_proveedor_producto AS 'product_provider_id',
						/*1*/ppua.letra_ubicacion_desde AS 'location_letter_since',
						/*2*/ppua.numero_ubicacion_desde AS 'location_number_since',
						/*3*/ppua.letra_ubicacion_hasta AS 'location_letter_to',
						/*4*/ppua.numero_ubicacion_hasta AS 'location_number_to',
						/*5*/ppua.pasillo_desde AS 'until_since',
						/*6*/ppua.pasillo_hasta AS 'until_to',
						/*7*/ppua.altura_desde AS 'level_since',
						/*8*/ppua.altura_hasta AS 'level_to',
						/*9*/p.nombre
					FROM ec_proveedor_producto_ubicacion_almacen ppua
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = ppua.id_proveedor_producto
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					WHERE pp.codigo_barras_pieza_1 = '{$key}'
					OR pp.codigo_barras_pieza_2 = '{$key}'
					OR pp.codigo_barras_pieza_3 = '{$key}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$key}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$key}'
					OR pp.codigo_barras_caja_1 = '{$key}'
					OR pp.codigo_barras_caja_2 = '{$key}'
					OR pp.id_proveedor_producto = '{$key}'
					ORDER BY ppua.es_principal DESC";
			//echo '<br> <br>'. $sql. '<br> <br>';
			$stm = $this->link->query( $sql ) or die( "Error al consultar ubicación de acuerdo al código de barras : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$response = $stm->fetch_assoc();
			}
			//echo $sql;
			return $response;	
		}

		public function remove_unique_code_from_barcode( $barcode ){
			$response = '';
			$barcode_array = explode( ' ', $barcode );
			
			for( $i=0; $i <=2; $i++ ) {
				$response .= ( $response == '' ? '' : ' ' );
				$response .= $barcode_array[$i];
			}
			return $response;
		}
	}
?>