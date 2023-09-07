<?php

?>

<div class="product_invoices_form">
	<div>
		<div class="input-group">
			<input 
				type="text"
				class="form-control"
				id="product_seeker"
				placeholder="Escanear Pieza / Buscar producto"
				onkeyup="seek_product( event, this );"
			>
			
			<button 
				class="input-group-text btn btn-primary"
				id="product_seeker_btn"
				onclick="seek_product( 'enter', '#product_seeker' );"
			>
				<i class="icon-search"></i>
			</button>

			<button type="button" class="btn btn-info" onclick="sow_new_product_form();">
				<i class="icon-plus-circle"></i>
			</button>

			<button 
				class="input-group-text btn btn-warning"
				id="product_reset_btn"
				onclick="desactivate_product_form();clean_product_form();"
			>
				<i class="icon-spin3"></i>
			</button>

		</div>
		<div class="productResBusc"></div>
	</div>
	<div class="product_description">
		<p class="product_name"></p>
		<span class="product_model"></span>
		<input type="hidden" id="product_id" value="">
		<input type="hidden" id="product_provider" value="">
		<input type="hidden" id="reception_detail_id" value="">
		<input type="hidden" id="is_new_product_row" value="0">
		<input type="hidden" id="measure_tmp_id" value="0">
	</div>
	<br/>
	<div class="product_form">
		<div class="group_card">
			<div>
				<label for="product_model">Modelo</label>
				<div class="row">
					<div class="col-8">
						<input
							type="text"
							id="product_model"
							class="form-control"
							placeholder="Modelo del producto"
							disabled
						>
						<input 
							type="hidden"
							id="db_product_model"
						>
					</div>
					<div class="col-4">
						<input 
							type="checkbox" 
							id="product_model_null"
							disabled
						><label for="product_model_null">No tiene / Repetido</label>
						<input type="hidden" id="product_model_notes" value="">
					</div>
				</div>
			</div>

			<div>
				<label for="piece_barcode">Código de Barras Pieza</label>
				<div class="row">
					<div class="col-8">
						<input 
							type="text"
							id="piece_barcode"
							class="form-control"
							placeholder="Código de Barras Pieza"
							onblur="validateNoRepeatBarcode( this );show_measures_form( 'since_piece' );"
							disabled
						>
						<input 
							type="hidden"
							id="db_piece_barcode"
						>
						<input 
							type="hidden"
							id="db_piece_barcode_2"
						>
						<input 
							type="hidden"
							id="db_piece_barcode_3"
						>
					</div>
					<!--/div
					</div-->
					<div class="col-4">
						<input 
							type="checkbox" 
							id="piece_barcode_null"
							disabled
						><label for="piece_barcode_null">No tiene / Repetido</label>
						<input type="hidden" id="piece_barcode_notes" value="">
					</div>

					<div class="col-12" style="text-align : center;"><!-- style="position:absolute !important; left : 80%; top : 20%;" -->
						<button type="button" id="show_measures_form_btn" onclick="show_measures_form();" class="btn btn-warning" disabled>
							<i class="icon-wrench-outline">Medidas del modelo</i>
						</button>
					</div>

				</div>
			</div>
		</div>

		<div class="group_card gray">
			<div>
				<label for="pieces_per_pack">Piezas por paquete</label>
				<div class="row">
					<div class="col-8">
						<input 
							type="text"
							class="form-control"
							id="pieces_per_pack"
							placeholder="Piezas por paquete"
							disabled
						>
						<input 
							type="hidden"
							id="db_pieces_per_pack"
						>
					</div>
					<div class="col-4">
						<input 
							type="checkbox"
							id="pieces_per_pack_null"
							disabled
						><label for="pieces_per_pack_null">No tiene</label>
						<input type="hidden" id="pieces_per_pack_notes" value="">
					</div>
				</div>
			</div>

			<div>
				<label for="pack_barcode">Código de barras Paquete</label>
				<div class="row">
					<div class="col-8">
						<input 
							type="text"
							class="form-control"
							id="pack_barcode"
							placeholder="Código de barras Paquete"
							onblur="validateNoRepeatBarcode( this );"
							disabled
						>
						<input 
							type="hidden"
							id="db_pack_barcode"
						>
						<input 
							type="hidden"
							id="db_pack_barcode_2"
						>
					</div>
					<div class="col-4">
						<input 
							type="checkbox"
							id="pack_barcode_null"
							disabled
						><label for="pack_barcode_null">No tiene</label>
						<input type="hidden" id="pack_barcode_notes" value="">
					</div>
				</div>
			</div>
		</div>

		<div class="group_card">
			<div>
				<label for="pieces_per_box">Piezas por caja</label>
				<div class="row">
					<div class="col-8">
						<input 
							type="text"
							class="form-control"
							id="pieces_per_box"
							placeholder="Piezas por caja"
							disabled
						>
						<input 
							type="hidden"
							id="db_pieces_per_box"
						>
					</div>
					<div class="col-4">
						<input 
							type="checkbox"
							id="pieces_per_box_null"
							disabled
						><label for="pieces_per_box_null">No tiene</label>
						<input type="hidden" id="pieces_per_box_notes" value="">
					</div>
				</div>	
			</div>

			<div>
				<label for="box_barcode">Código de barras Caja</label>
				<div class="row">
					<div class="col-8">
						<input 
							type="text"
							class="form-control"
							id="box_barcode"
							placeholder="Código de barras Caja"
							onblur="validateNoRepeatBarcode( this );"
							disabled
						>
						<input 
							type="hidden"
							id="db_box_barcode"
							placeholder="Código de barras Caja"
						>
						<input 
							type="hidden"
							id="db_box_barcode_2"
						>
					</div>
					<div class="col-4">
						<input 
							type="checkbox"
							id="box_barcode_null"
							disabled
						><label for="box_barcode_null">No tiene</label>
						<input type="hidden" id="box_barcode_notes" value="">
					</div>
				</div>
			</div>
		</div>

		<div  class="group_card">
			<div>
				<label for="received_packs">Número de cajas recibidas</label>
				<input 
					type="number"
					class="form-control"
					id="received_packs"
					placeholder="Número de cajas recibidas"
					disabled
				>
			</div>

			<div>
				<label for="received_pieces">Número de piezas sueltas recibidas</label>
				<input 
					type="number"
					class="form-control"
					id="received_pieces"
					placeholder="Número de piezas sueltas recibidas"
					disabled
				>
			</div>

			<div>
				<div class="row">
					<div class="col-6">
						<label for="product_serie">Serie</label>
						<div class="product_serie">
						</div>
					</div>

					<div class="col-6">
						<label for="product_part_number">Número de Partida</label>
						<div class="input-group">
							<select
								class="form-control"
								id="product_part_number"
								disabled
							>
								<option value="0">-</option>
							</select>
							<button class="btn btn-warning input-group-text" 
									onclick="edit_parts_number();"
									id="btn_parts_edition"
									disabled
							>
								<i class="icon-pencil-neg"></i>
							</button>
						</div>
						<div class="number_part_aux">
							<input 
								type="number"
								class="form-control"
								id="product_part_number_aux"
								placeholder="Número de Partida"
							>
							<button class="btn btn-success"
								onclick="save_edition_parts();"
							>
								<i class="icon-ok-circle"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

			<?php
				$is_source = 1;
				include( 'location_form.php' );
				$is_source = 0;
			?>

		<br />
		<div>	
			<button 
				class="btn btn-success form-control"
				onclick="saveInvoiceDetail();"
			>Guardar</button>
		</div>	
	</div>
</div>