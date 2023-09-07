<?php
	//echo 'here';
?>

	<div class="group_card" id="<?php echo "location_form_container{$specific_id}";?>" 
		<?php echo( $is_source == 1 ? "style=\"display : ;\"": "" )?> >
		<input 
			type="hidden" 
			id="product_provider_id_location_form<?php echo $specific_id; ?>"
		>

		<div
			id="<?php echo $hidden_form_id;?>" 
			class="row new_location_form"
			style="display : flex !important; height : auto;"
		>
		<!-- ubicacion de -->
			<div class="col-12">
			<div class="text-center">
				<h5 style="color : green; font-size : 150%;">Ubicación principal ( Piso <b id="location_floor_from">0</b> )</h5>
			</div>
			<!--h3></h3-->
				<h6>Desde : </h6>
				<div class="row">
					<div class="col-2">
					</div>
					<div class="col-4">
						<label>Ubicación</label>
					</div>
					<div class="col-3 aisle_col">
						<label>Pasillo</label>
					</div>
					<div class="col-3 level_col">
						<label>Altura</label>
					</div>
				</div>
			</div>

			<div class="col-1"></div>
			<div class="col-5">
				<div class="row">
				<!--label for="">Letra de</label-->
					<div class="col-6 text-end" style="margin:0;padding:0;">
						<input 
							type="text"
							id="location_letter_from"
							class="form-control"
							placeholder="Letra"
							readonly
						>
					</div>
					<div class="col-6 text-start" style="margin:0;padding:0;">
						<!--label for=""># de</label-->
						<input 
							type="number"
							id="location_number_from"
							class="form-control"
							placeholder="#"
							readonly
						>
					</div>
				</div>
			</div>
		<!-- Pasillo de -->
			<div class="col-3 aisle_col">
				<!--center><label for="">fila / pasillo</label></center-->
					<input 
						type="number"
						id="location_aisle_from"
						class="form-control"
						placeholder="del"
							readonly
					>
			</div>
			<div class="col-3 level_col">
				<input 
					type="text"
					id="location_level_from"
					class="form-control"
					placeholder="del"
					readonly
				>
			</div>


		<!-- Ubicación hasta -->

		<!-- ubicacion de -->
			<div class="col-12">
				<h6>Hasta : </h6>
				<div class="row">
				</div>
			</div>
			<div class="col-1"></div>
			<div class="col-5">
				<div class="row">
					<div class="col-6 text-start" style="margin:0;padding:0;">
						<input 
							type="text"
							id="location_letter_to"
							class="form-control"
							placeholder="Letra"
							readonly
						>
					</div>
					<div class="col-6 text-start" style="margin:0;padding:0;">
						<input 
							type="number"
							id="location_number_to"
							class="form-control"
							placeholder="#"
							readonly
						>
					</div>
				</div>
			</div>

			<div class="col-3 aisle_col">
				<input 
							type="number"
							id="location_aisle_to"
							class="form-control"
							placeholder="al"
							readonly
						>
			</div>

			<div class="col-3 level_col">
				<input 
					type="text"
					id="location_level_to"
					class="form-control"
					placeholder="al"
							readonly
				>
			</div>
		</div>
	</div>
