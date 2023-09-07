<?php
	function getProvidersList( $link ){
		$resp .= '<label for="invoice_provider">Proveedor</label>';
		$resp .= '<div class="input-group">';
			$resp .= '<select class="form-control" id="invoice_provider">';
				$resp .= "<option value=\"0\">Seleccionar Proveedor</option>";
		
		$sql = "SELECT id_proveedor, nombre_comercial 
				FROM ec_proveedor 
				WHERE id_proveedor > 1";
		$exc = $link->query( $sql ) or die( "Error al consultar lista de proveedores : " . $link->error );
		while ( $r = $exc->fetch_row() ) {
			$resp .= "<option value=\"{$r[0]}\">{$r[1]}</option>"; 
		}
		$resp .= '</select>';
		$resp .= '<button class="btn btn-light" onclick="seeker_provider( 1 );">';
			$resp .= '<i class="icon-down-open"></i>';
		$resp .= '</button>';
		$resp .= '</div>';
		return $resp;
	}
?>

<div class="invoices_form">
	<div class="group_card">
		<div>
			<label for="invoice_provider">Proveedor</label>
			<div class="input-group">
				<!--select class="form-control" id="invoice_provider">
					<option value="0">Seleccionar Proveedor</option>
				</select-->
				<input type="text" 
					class="form-control" 
					id="invoice_provider_seeker"
					onkeyup="seeker_provider();" >
				<button class="btn btn-light" id="btn_show_all_providers" onclick="seeker_provider( 1 );">
					<i class="icon-down-open"></i>
				</button>
				<input type="hidden" id="invoice_provider" value="">
			</div>
			<div id="provider_seeker_response" class="group_card"></div>

			<?php 
				//echo getProvidersList( $link );
			?>

		</div>
		<div>
			<label>Número de Remisiones a recibir</label>
			<input 
				type="number" 
				class="form-control"
				id="invoices_initial_counter"
				placeholder="Numero de Remisiones a recibir"
			>
		</div>
		<div>
			<button
				type="button"
				class="btn btn-primary form-control"
				id="invoices_initial_config_confirm"
				onclick="setInitialConfig();"

			>
				Aceptar
			</button>
			<button
				type="button"
				class="btn btn-light form-control"
				id="invoices_initial_config_edit"
				onclick="editInitialConfig();"
			>
				Modificar
			</button>
		</div>
	<!--br /-->
	</div>

<!-- Formulario para agregar remisiones -->
	<div class="group_card">
		<div class="group_card">
			<label>Alta de Remisiones</label>
			<div class="row">
				<div class="col-6">
					<label>Folio</label><input 
						type="text" 
						class="form-control"
						placeholder="Folio de Remisión"
						id="invoice_folio"
						onblur="validateInvoiceNoExists( this );"
						disabled
					>
				</div>
				<!--br /-->

				<div class="col-3">
					<label># Part</label>
					<input 
						type="number" 
						class="form-control"
						placeholder="Número de Partidas"
						id="invoice_parts"
						disabled
					>
				</div>
				<div class="col-3">
					<button 
						type="button"
						class="btn btn-warning form-control"
						id="invoice_button_add"
						onclick="addInvoice();"
						disabled
						style="margin-top:15px; font-size:140%;"
					>
						<i class="icon-plus-circle"></i>
					</button>
				</div>
			</div>
		</div>

		<div class="group_card">
			<label >Remisiones recibidas parcialmente</label>
			<input 
				type="text" 
				class="form-control"
				placeholder="Buscar Remisiones recibidas parcialmente"
				id="invoices_seeker"
				onkeyup="seek_invoices( this );"
				disabled
			>
			<div class="search_results_invoice">
			</div>
		</div>
	</div>
</div>

<div class="current_invoices_list">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Serie</th>
				<th>Folio</th>
				<th># de Partidas</th>
				<th>X</th>
			</tr>
		</thead>
	<!-- listado de remisiones -->
		<tbody id="invoice_to_receive">
		</tbody>
	</table>

	<button 
		class="btn btn-success form-control"
		onclick="validate_first_part()"
	>
		Siguiente<i class="icon-right-big"></i>
	</button>
</div>
