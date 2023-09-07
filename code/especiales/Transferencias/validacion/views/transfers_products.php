<?php

?>
	<div>
		<!--div class="group_card"-->
			<div class="accordion group_card" id="accordionPanelsStayOpenExample">
			  <div class="accordion-item">
			    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
			      	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false" aria-controls="panelsStayOpen-collapseOne">
    					Transferencias
			  		</button>
			    </h2>
			    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne">
			    	<div class="accordion-body transfers">
			    	</div>
			    </div>
			  </div>
			</div>

			<div class="group_card" id="scanner_products_response"></div>
		<!--/div-->

		<div class="group_card">
			<div class="input-group">
		<!-- Botón de bloqueo -->
				<button 
					type="button" 
					id="barcode_seeker_lock_btn" 
					class="btn btn-danger" 
					onclick="lock_and_unlock_focus( this, '#barcode_seeker' )">
					<i class="icon-lock"></i>
				</button>
				<input 
					type="text"
					id="barcode_seeker"
					class="form-control"
					placeholder="Escanear código de barras"
					onkeyup="validateBarcode( this, event );"
				>
		<!-- Botón de búsqueda -->
				<button 
					type="button" 
					id="seek_by_name_btn" 
					class="btn btn-warning" 
					onclick="validateBarcode( '#barcode_seeker', 'enter' );">
					<i class="icon-search"></i>
				</button>
		<!-- Botón de cámara -->
				<button 
					type="button" 
					class="btn btn-info"
					id="camera_permission_btn" 
					onclick="confirm( 'Abrir cámara?' );"	
				>
					<i class="icon-instagram"></i>
				</button>
			</div>
			<div id="seeker_response"></div>
		</div>

		<h5>Últimos escaneos</h5>
		<div class="group_card last_validations_container">
			<table class="table table-bordered table-striped table-90">
				<thead class="last_validations_header_sticky">
					<tr>
						<th>Producto</th>
						<th>Piezas<br>Revisadas</th>
						<th>Transf.</th>
					</tr>
				</thead>
				<tbody id="last_validations">
				</tbody>
				<!--tfoot>
					<tr>
						<td colspan="2">
						</td>
					</tr>
				</tfoot-->
			</table>
		</div>
		<div class="group_card">
			<div>
				<div class="input-group">
					<input 
						type="text" 
						class="form-control"
						placeholder="Buscar productos validados"
						style="box-shadow: 1px 1px 5px #0dcaf0;"
						onkeyup="seek_recived_products();"
						id="recived_products_seeker"
					>
					<button
						onclick="clean_recived_form();"
						class="btn btn-danger"
						id="clean_recived_form_btn"
					>
						<i class="icon-cancel-alt-filled"></i>
					</button>
				</div>
				<div id="recived_products_seeker_response">
					
				</div>
			</div>
		</div>
	</div>
	<div class="row group_card">
		<div class="col-4"></div>
		<div class="col-4">
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="close_validation_session();"
			>
				<i>Finalizar Validacion</i>
			</button>
		</div>
	</div>

	<script type="text/javascript">
		//loadLastValidations();
	</script>