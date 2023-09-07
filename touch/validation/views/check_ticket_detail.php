<?php
	/*function getProductReceived(){
		$resp = '';
		for ($i = 0; $i <= 10 ; $i++ ) {
			$resp .= "<tr>";
				$resp .= "<td>Serie LED 50 Luces Blanca C/Transparente 3.5M</td>";
				$resp .= "<td>10</td>";
			$resp .= "</tr>";
		}
		return $resp;
	}*/
?>
	<input type="hidden" id="temporal_sale_detail_id_to_validate">
	<div class="">

		<div class="group_card">
			<label for="product_barcode_seeker">Escaner código de barras del Producto</label>
			<div class="input-group">
				<input 
					type="text"
					id="product_barcode_seeker"
					class="form-control"
					placeholder="Escanea código de barras del Producto"
					onkeyup="seek_product( this, event, '#product_barcode_seeker_response' );"
				>
				<!--	onkeyup="seekTicketBarcode( event, this, 'seekProductBarcode' );" -->
				<button 
					class="input-group-text btn btn-warning"
					onclick="seek_product( '#product_barcode_seeker', 'intro', '#product_barcode_seeker_response' );"
				>
					<i class="icon-barcode"></i>
				</button>
			</div>
			<div id="product_barcode_seeker_response" class="seeker_response_div"></div>
		</div>

		<div class="group_card">
			<label for="product_barcode_seeker_pieces">
				Buscador con número de Piezas
			</label>
				<input type="checkbox" id="" onclick="setProductSeekerType( this );">
			<div class="input-group">
				<input 
					type="text"
					id="product_barcode_seeker_pieces"
					class="form-control"
					placeholder="Escanea código de barras del Producto"
					onkeyup="seek_product( this, event, '#product_barcode_seeker_pieces_response' );"
					disabled
				><!-- seekTicketBarcode( event, this, 'seekProductBarcode' ); -->
				<button class="input-group-text btn btn-warning no_visible"
					disabled
				>
					<i class="icon-barcode"></i>
				</button>
			</div>
			<div id="product_barcode_seeker_pieces_response" class="seeker_response_div"></div>
		</div>
		<div id="scanner_response">
		</div>
		<!--h5 style="color : red ;">Productos Pendientes de Revisar</h5-->
		<div class="group_card validation_detail_table_container group_danger">
			<h6 style="color : red;">Productos Pendientes</h6>
			<table class="table table-striped table_80">
				<thead class="header_sticky header_sticky_validation">
					<tr>
						<th>Producto</th>
						<th>Cantidad</th>
					</tr>
				</thead>
				<tbody id="pending_validation">
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>

		<!--h5 style="color : green ;">Productos Revisados</h5-->
		<div class="group_card validation_detail_table_container group_success">
			<h6 style="color : green;">Productos Validados</h6>
			<table class="table table-striped table_80">
				<thead class="header_sticky header_sticky_validation">
					<tr>
						<th>Producto</th>
						<th>Cantidad</th>
					</tr>
				</thead>
				<tbody id="validated">
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>
<hr>
<div class="row">

	<div class="col-5 text-center">
<?php
	if( $perfil_usuario != 18 && $perfil_usuario != 19 ){	
?>
		<button
			type="button"
			class="btn btn-warning"
			id="sale_edition_btn"
			onclick="getReturnPrevious();"
		>
			<i class="icon-pencil-neg">Editar nota de venta</i>
		</button>	
<?php
	}
?>
	</div>
	<div class="col-2"></div>
	<div class="col-5 text-center">
		<button
			type="button"
			class="btn btn-danger"
			id="sale_reset_btn"
		>
			<i class="icon-spin3">Resetear Validacion</i>
		</button>
	</div>
</div>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
		
	</div>