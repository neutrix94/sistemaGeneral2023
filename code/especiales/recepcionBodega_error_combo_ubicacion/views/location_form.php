<?php
	if( $is_source == 1 ){
		$location_status_onchange = "change_location( 'source' );";
		$specific_id = "_source";
		$hidden_form_id = "new_location_form_source";
		$save_type = "source";
	}else{
		$location_status_onchange = "change_location( 'seeker' );";
		$specific_id = "_seeker";
		$hidden_form_id = "new_location_form_seeker";
		$save_type = "seeker";
	}
?>

	<div class="group_card">
<?php
	if( $is_source != 1 ){
?>
		
		<div class="row" style="padding:10px;">	

			<div class="input-group">
				<input 
					type="text" 
					id="seeker_product_location"
					class="form-control"
					placeholder="Buscar Productos Recibidos"
					onkeyup="seekProductsLocations( this );"
				>

				<button 
					type="button"
					class="input-group-text btn btn-primary"
					id="product_seeker_location_form_btn"
					onclick=""
				>
					<i class="icon-search"></i>
				</button>
				<button 
					type="button"
					class="input-group-text btn btn-warning"
					id="product_reset_location_form_btn"
					onclick="cleanProductLocationForm();"
				>
					<i class="icon-spin3"></i>
				</button>
			</div>
			<div class="product_location_seeker_response"></div>
		</div>


		<div class="row">
			<label 
				id="product_name_location_form<?php echo $specific_id; ?>"
				class="product_name_location_form"
			></label>
			<input 
				type="hidden" 
				id="product_id_location_form<?php echo $specific_id; ?>"
			>
			<input 
				type="hidden" 
				id="product_provider_id_location_form<?php echo $specific_id; ?>"
			>
		</div>

		<div class="row">
			<div class="col-7">
				<label>Inventario Recibido</label>
				<input 
					type="number" 
					class="form-control"
					id="product_inventory_recived"
					disabled
				>
			</div>
			<div class="col-5">
				<label>Piezas sin ubic.</label>
				<input 
					type="number" 
					class="form-control"
					id="product_inventory_no_ubicated"
					disabled
				>
			</div>
		</div>

<?php
	}
?>
		<div class="row">
			<input type="hidden" id="location_tmp_id">
			<div class="col-7">
				<label for="location_status">Estatus de Mercancía</label>
				<select 
					id="location_status<?php echo $specific_id; ?>" 
					class="form-control"
					onchange="<?php echo $location_status_onchange;?>"
					disabled
				>
					<option value="0">-- Seleccionar --</option>
					<option value="no_location">Sin acomodar</option>
					<option value="2">Ubicación : </option>
					<option value="new_location">Nueva ubicación</option>
				</select>
			</div>
			<div class="col-5">
				<label for="product_location">Ubic. Actual : </label>
				<input 
					type="text" 
					id="product_location<?php echo $specific_id; ?>" 
					class="form-control"
					readonly
				>
			</div>
		</div>
		<br>
		<div
			id="<?php echo $hidden_form_id;?>" 
			class="row new_location_form"
			style="display : flex !important; height : auto;"
		>
		<!-- ubicacion de -->
			<div class="col-12">
				<h3>Desde : </h3>
				<div class="row">
					<div class="col-3">
						<label>Piso <b id="flore_from">1</b></label>
					</div>
					<div class="col-3">
						<label>Ubicación</label>
					</div>
					<div class="col-3">
						<label>Pasillo</label>
					</div>
					<div class="col-3">
						<label>Altura</label>
					</div>
				</div>
			</div>

			<div class="col-1"></div>
			<div class="col-5">
				<div class="row">
				<!--label for="">Letra de</label-->
					<div class="col-6">
						<input 
							type="text"
							id="aisle<?php echo $specific_id; ?>_since"
							class="form-control"
							onkeyup=""
							placeholder="Letra"
						>
					</div>
					<div class="col-6">
						<!--label for=""># de</label-->
						<input 
							type="number"
							id="location_number<?php echo $specific_id; ?>_since"
							class="form-control"
							onkeyup=""
							placeholder="#"
						>
					</div>
				</div>
			</div>
		<!-- Pasillo de -->
			<div class="col-3">
				<!--center><label for="">fila / pasillo</label></center-->
					<input 
						type="number"
						id="aisle_from<?php echo $specific_id; ?>"
						class="form-control"
						onkeyup=""
						placeholder="del"
					>
			</div>
			<div class="col-3">
				<input 
					type="text"
					id="level_from<?php echo $specific_id; ?>"
					class="form-control"
					onkeyup=""
					placeholder="del"
				>
			</div>


		<!-- Ubicación hasta -->

		<!-- ubicacion de -->
			<div class="col-12">
				<h3>Hasta : </h3>
				<div class="row">
					<div class="col-3">
						<label>Piso <b id="flore_from">1</b></label>
					</div>
					<div class="col-3">
						<label>Ubicación</label>
					</div>
					<div class="col-3">
						<label>Pasillo</label>
					</div>
					<div class="col-3">
						<label>Altura</label>
					</div>
				</div>
			</div>
			<div class="col-1"></div>
			<div class="col-5">
				<div class="row">
					<div class="col-6">
						<input 
							type="text"
							id="aisle<?php echo $specific_id; ?>_to"
							class="form-control"
							onkeyup=""
							placeholder="Letra"
						>
					</div>




					<div class="col-6">
						<input 
							type="number"
							id="location_number<?php echo $specific_id; ?>_to"
							class="form-control"
							onkeyup=""
							placeholder="#"
						>
					</div>
				</div>
			</div>

			<div class="col-3">
				<input 
							type=""
							id="aisle_until<?php echo $specific_id; ?>"
							class="form-control"
							onkeyup=""
							placeholder="al"
						>
			</div>

			<div class="col-3">
				<input 
					type="text"
					id="level_to<?php echo $specific_id; ?>"
					class="form-control"
					onkeyup=""
					placeholder="al"
				>
			</div>

			<!--div class="col-6">
				<center><label for="">Nivel</label></center>
				<div class="row">
					
				</div>
				<br/>
			</div-->

						

			<br/>
			<div>
			<?php
				if( $save_type == 'source' ){//onclick="make_new_location( '<?php echo $save_type;' )"
			?>
				<button 
					type="button" 
					class="btn btn-warning form-control"
					onclick="saveNewLocation( '_source' );"
				>
					Guardar Ubicación
				</button>
			<?php
				}
			?>
			</div>
		</div>
		<?php
			if( $save_type == 'seeker' ){
		?>
			<button 
				type="button" 
				class="btn btn-success form-control"
				onclick="saveNewLocation( '_seeker' );"
			>
				Guardar Ubicación
			</button>
		<?php
			}
		?>
	</div>

