<?php
	if( isset( $_GET['fl_maquile'] ) ){
		include( '../../../conexionMysqli.php' );
		$action = $_GET['fl_maquile'];
		$maquile = new maquile( $link );
		switch ( $action ) {
			case 'getMaquileForm':
				$initial_quantity = 0;
				switch( $_GET['type'] ){ 
					case 'supply' :
						$sql = "SELECT 
									cantidad AS quantity
								FROM ec_transferencia_productos
								WHERE id_transferencia_producto = {$_GET['transfer_product_id']}";
						$stm = $link->query( $sql ) or die( "Error al consultar la cantidad pedida : {$link->error}" );
						$row = $stm->fetch_assoc();
						$initial_quantity = $row['quantity'];
					break;

					case 'validation' :
						$sql = "SELECT 
									cantidad AS quantity
								FROM ec_transferencia_productos
								WHERE id_transferencia_producto = {$_GET['transfer_product_id']}";
						$stm = $link->query( $sql ) or die( "Error al consultar la cantidad pedida : {$link->error}" );
						$initial_quantity = $row['quantity'];
					break;

					case 'reception' :
						$sql = "SELECT 
									cantidad  AS quantity
								FROM ec_transferencia_productos
								WHERE id_transferencia_producto = {$_GET['transfer_product_id']}";
						$stm = $link->query( $sql ) or die( "Error al consultar la cantidad pedida : {$link->error}" );
						$initial_quantity = $row['quantity'];
					break;
					case 'fast_transfer' :
						$sql = "SELECT 
									cantidad  AS quantity
								FROM ec_transferencia_productos
								WHERE id_transferencia_producto = {$_GET['transfer_product_id']}";
						$stm = $link->query( $sql ) or die( "Error al consultar la cantidad pedida : {$link->error}" );
						$initial_quantity = $row['quantity'];
					break;
				
				}
				$subtitle = ( isset( $_GET['subtitle'] ) ? $_GET['subtitle'] : 'Cantidad pedida: ' );
				$close_function = ( isset( $_GET['close_function'] ) ? $_GET['close_function'] : 'close_emergent();' );
				//$initial_quantity = ( isset( $_GET['initial_quantity'] ) ? $_GET['initial_quantity'] : 1 );
				echo $maquile->make_form( $_GET['product_id'], $_GET['quantity'], $_GET['function'], $initial_quantity, $subtitle, $close_function );
			break;
			
			default:

			break;
		}
	}
	class maquile
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function make_form( $product_id, $quantity = 0, $function_js, $initial_quantity, $subtitle = '', $close_function = '' ){
			$sql = "SELECT
						pd.id_producto AS product_origin_id,
						p1.nombre AS name_origin,
						pd.id_producto_ordigen AS product_destinity_id,
						p2.nombre AS name_destinity,
						pd.cantidad AS quantity,
						/*IF( pr1.nombre IS NULL, 'presentaciones completas', CONCAT(pr1.nombre, 's' ) ) AS presentation_name_origin,
						IF( pr2.nombre IS NULL, 'piezas', CONCAT(pr2.nombre, 's' ) ) AS presentation_name_destinity*/
						IF( pp1.unidad_medida_pieza IS NULL, 'presentaciones completas', CONCAT( pp1.unidad_medida_pieza, 'S' ) ) AS presentation_name_origin,
						IF( pp2.unidad_medida_pieza IS NULL, 'piezas', CONCAT( pp2.unidad_medida_pieza, 'S' ) ) AS presentation_name_destinity,
						pp1.clave_proveedor AS provider_clue,
						p1.orden_lista AS list_order
					FROM ec_productos_detalle pd
					LEFT JOIN ec_productos p1 ON p1.id_productos = pd.id_producto
					LEFT JOIN ec_productos p2 ON p2.id_productos = pd.id_producto_ordigen
					/*LEFT JOIN ec_productos_presentaciones pr1
					ON pr1.id_producto = p1.id_productos
					LEFT JOIN ec_productos_presentaciones pr2
					ON pr2.id_producto = p2.id_productos*/
					LEFT JOIN ec_proveedor_producto pp1 
					ON pp1.id_producto = p1.id_productos
					LEFT JOIN ec_proveedor_producto pp2 
					ON pp2.id_producto = p2.id_productos
					WHERE pd.id_producto = {$product_id} OR  pd.id_producto_ordigen = {$product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la maquila : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
/*
					function calculation_maquila_form(){
						var maquila_response = 0;
						var maquila_complete = 0;
						var maquila_pieces = 0;
						var maquila_equivalent = 0;
						maquila_complete = ( document.getElementById('maquila_complete').value <= 0 ? 0 : document.getElementById('maquila_complete').value );
						maquila_pieces = ( document.getElementById('maquila_pieces').value <= 0 ? 0 : document.getElementById('maquila_pieces').value );
						maquila_equivalent = ( document.getElementById('maquila_equivalent').value <= 0 ? 0 : document.getElementById('maquila_equivalent').value );
						var maquila_response = parseInt(maquila_complete) + ( maquila_equivalent * maquila_pieces );
						document.getElementById('maquila_decimal').value = maquila_response.toFixed( 2 );
					}
*/
			$resp = "<script type=\"text/JavaScript\">
					function calculation_maquila_form(){
						if( document.getElementById('maquila_complete').value % 1 != 0 ){
							alert( \"No se permiten decimales\" );
							var val = parseInt( document.getElementById('maquila_complete').value );
							$( '#maquila_complete' ).focus().val('').val(val);
						}
						if( document.getElementById('maquila_pieces').value % 1 != 0 ){
							alert( \"No se permiten decimales\" );
							var val = parseInt( document.getElementById('maquila_pieces').value );
							$( '#maquila_pieces' ).focus().val('').val(val);
						}
						var maquila_response = 0;
						var maquila_complete = 0;
						var maquila_pieces = 0;
						var maquila_equivalent = 0;
						maquila_complete = ( document.getElementById('maquila_complete').value <= 0 ? 0 : document.getElementById('maquila_complete').value );
						maquila_pieces = ( document.getElementById('maquila_pieces').value <= 0 ? 0 : document.getElementById('maquila_pieces').value );
						maquila_equivalent = ( document.getElementById('maquila_equivalent').value <= 0 ? 0 : document.getElementById('maquila_equivalent').value );
						var maquila_response = parseInt(maquila_complete) + ( maquila_equivalent * maquila_pieces );
						document.getElementById('maquila_decimal').value = maquila_response.toFixed( 2 );
					}
			</script>";
			if( $quantity != 0 ){
				$quantity_complete = floor( $quantity );/// $row['quantity']
				//$quantity_pieces = ( $quantity % ( 1 / $row['quantity'] ) ) * ( $row['quantity'] );
				$quantity_pieces = round(( $quantity - $quantity_complete ) / ( $row['quantity'] ));
			}

			if( $initial_quantity != 0 ){
				$quantity_complete_initial = floor( $initial_quantity );/// $row['quantity']
				//$quantity_pieces = ( $quantity % ( 1 / $row['quantity'] ) ) * ( $row['quantity'] );
				$quantity_pieces_initial = round(( $initial_quantity - $quantity_complete_initial ) / ( $row['quantity'] ));
			}
			$resp .= "<div class=\"row\" style=\"font-size : 150% ;\">";
				$resp .= "<div class=\"col-12\">";
					//$resp .= "<h2>Un(a) {$row['name_origin']} equivale a " . ( $row['quantity'] ) . " de {$row['name_destinity']}</h2>";
					$resp .= "<h2> ( <b>{$row['list_order']}</b> ) {$row['name_destinity']} - 
					<span style=\"color : red;\">{$row['provider_clue']}</span><br>";
						$resp .= ( $subtitle == "" ? " Cantidad pedida: " : "{$subtitle}" ) . "<br>";
						$resp .= " {$quantity_complete_initial} {$row['presentation_name_destinity']}<br>
								{$quantity_pieces_initial} {$row['presentation_name_origin']}
							</h2>";
					$resp .= "<input type=\"hidden\" value=\"" . ( $row['quantity'] ) . "\" id=\"maquila_equivalent\">";
				$resp .= "</div>";
				$resp .= "<div class=\"col-6\">
						<p>Ingresa {$row['presentation_name_destinity']} : </p>
						<div class=\"row\">	
							<div class=\"col-3\"></div>
							<div class=\"col-6\">
								<input type=\"number\" value=\"{$quantity_complete}\" id=\"maquila_complete\" onkeyup=\"calculation_maquila_form();\" class=\"form-control\"  style=\"text-align : right;\">
							</div>
						</div>
					</div>";
				$resp .= "<div class=\"col-6\">
						<p>Ingresa {$row['presentation_name_origin']} : </p>
						<div class=\"row\">	
							<div class=\"col-3\"></div>
							<div class=\"col-6\">
								<input type=\"number\" value=\"{$quantity_pieces}\" id=\"maquila_pieces\" onkeyup=\"calculation_maquila_form();\" class=\"form-control\" style=\"text-align : right;\">
							</div>
						</div>
					</div>";
				$resp .= "<div class=\"col-4\">
						<input type=\"hidden\" readonly id=\"maquila_decimal\" value=\"" . round( $quantity, 4 ) . "\">
					</div>";
				$resp .= "<div class=\"col-4\">
							<br><br>
							<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"{$function_js}\">Aceptar</button>
							<br><br>
							<button type=\"button\" class=\"btn btn-danger form-control\" onclick=\"{$close_function}\">Cancelar</button>
							<br><br>
					</div>";
			$resp .= "</div>";	

			return $resp;
		}
	}
?>