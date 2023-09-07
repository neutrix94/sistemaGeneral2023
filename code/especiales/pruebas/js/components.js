//tipo de prueba
	var type_test_component = `<div class="row text-center">
				<h4>Tipo de Prueba : </h4>
				<div class="col-3"></div>
				<div class="col-6">	
					<select 
						id="test_type_combo"
						class="form-control"
						onchange="change_screen_header();"
					>
						<option value="0"> -- Seleccionar -- </option>
						<option value="transfer">Transferencias</option>
						<option value="datetime">Fecha</option>
					</select>
					<br>
		<!-- Buscador de Transferencias -->
					<div id="transfer_seeker_container" class="no_visible">
						<div class="input-group">
							<input 
								type="search" 
								class="form-control"
								onkeyup="seekTransfer( this, event );"
								placeholder="Buscar Transferencia / Bloque Validacion / Bloque Recepcion"
							>
							<button
								class="btn btn-primary"
							>	
								<i class="icon-search"></i>
							</button>
						</div>
						<div class="transfer_seeker_response">

						</div>
					</div>
		<!-- -->
					<div id="date_time_tmp_container" class="no_visible">
						<input type="datetime-local" id="date_time_input_tmp" class="form-control">
					</div>
						<br>
						<button
							type="button"
							class="btn btn-success form-control"
							onclick="setTestType();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
				</div>	
			</div>`;
//
	var header_transfer_test_component = `<div class="row">
			<div class="col-4">
				Bloque Recepcion
				<input type="text" id="transfers_receptions_ids" class="form-control" disabled>
			</div>
			<div class="col-4">
				Bloque Validacion
				<textarea id="transfers_validations_ids" disabled></textarea>
			</div>
			<div class="col-4">
				Transferencias
				<textarea id="transfers_folios" disabled></textarea>
			</div>
		</div>`;
//
	var header_date_test_component = `<div class="row">
			<div class="col-4 text-end">
				Fecha desde : 
			</div>
			<div class="col-8">
				<input type="datetime-local" id="date_time_input" class="form-control">
			</div>
				
		</div>`;
//products_header

	var date_time_component = `<div class="row">
		<div class="col-3"></div>
		<div class="col-6">
			Fecha : 
			<input type="date" class="form-control" id="config_initial_date" value="__initial_date__">
			<br><br>
			Hora : 
			<input type="time" class="form-control" id="config_initial_time" value="__initial_time__">
			<br>
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="setInitialDateTime();"

			>
				<i class="icon-ok-circle">Aceptar</i>
			</button>
		</div>
	</div>`;

	var product_provider_note = `<div class="row">
		<div class="col-2"></div>
		<div class="col-8 text-center">
			<h5>Notas del proveedor producto modelo <b>__product_provider_clue__</b> :</h5>
			<textarea id="note_input"
				style="position : relative; width : 100%; height : 300px;"
			>__note__</textarea>
			<br>
			<button
				class="btn btn-success"
				onclick="set_product_provider_note( __product_provider_id__ );"
			>
				<i class="icon-ok-circle">Aceptar</i>
			</button>
		</div>
	</div>`;
