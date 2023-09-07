<?php
	if( isset( $_GET['fl_special'] ) ){
		include( '../../../conexionMysqli.php' );
		$action = $_GET['fl_special'];
		$maquile = new maquila( $link );
		switch ( $action ) {
			case 'getMaquileForm':
				echo $maquile->make_form( $_GET['product_id'], 0, $_GET['function'] );
			break;
			
			default:

			break;
		}
	}
	class maquila
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function make_form( $product_id, $quantity = 0, $function_js ){
			$sql = "SELECT
						p1.nombre AS product_name,
						pp1.clave_proveedor AS provider_clue,
						p1.orden_lista AS list_order
					FROM ec_productos p
					LEFT JOIN ec_productos p1 
					ON p1.id_productos = p.id_productos
					LEFT JOIN ec_proveedor_producto pp1 
					ON pp1.id_producto = p1.id_productos
					WHERE p.id_productos = {$product_id} ";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la maquila : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $quantity != 0 ){
				$quantity_complete = floor( $quantity );/// $row['quantity']
				//$quantity_pieces = ( $quantity % ( 1 / $row['quantity'] ) ) * ( $row['quantity'] );
				$quantity_pieces = round(( $quantity - $quantity_complete ) / ( $row['quantity'] ));
			}
			$resp .= "<div class=\"row\" style=\"font-size : 150% ;\">";
				$resp .= "<div class=\"col-12\">";
					/*$resp .= "<h2>Un(a) {$row['name_origin']} equivale a " . ( $row['quantity'] ) . " de {$row['name_destinity']}</h2>";
						de {$row['name_destinity']}
						de {$row['name_origin']}
					*/
					$resp .= "<h2> ( <b>{$row['list_order']}</b> ) {$row['product_name']} - 
					<span style=\"color : red;\">{$row['provider_clue']}</span><br>
							</h2>"; 
					$resp .= "<input type=\"hidden\" value=\"" . ( $row['quantity'] ) . "\" id=\"maquila_equivalent\">";
				$resp .= "</div>";
				$resp .= "<div class=\"col-12\">
						<p style=\"color : red; font-size : 90%; text-align : justify;\">Este producto no requiere escaneo, solo necesitas <b>CONTAR BIEN</b> el numero de piezas e ingresarlo en la siguiente 
						entrada de texto, por favor se preciso en el conteo ya que no hay otra validación</p>
						<p>Ingresa el numero de piezas : </p>
						<div class=\"row\">	
							<div class=\"col-3\"></div>
							<div class=\"col-6\">
								<input type=\"number\" value=\"{$quantity_complete}\" 
									id=\"special_tmp_input\" 
									class=\"form-control\"  
									style=\"text-align : right;\">
							</div>
						</div>
					</div>";
				$resp .= "<div class=\"col-4\">
					</div>";
				$resp .= "<div class=\"col-4\">
							<br>
							<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"{$function_js}\">Aceptar</button>
							<br><br>
					</div>";
			$resp .= "</div>";	

			return $resp;
		}
	}
?>