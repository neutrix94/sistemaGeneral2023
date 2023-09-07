<?php
//include( 'resolution_view.php' );
?>
<div class="accordion group_card" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
      	<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        	<div class="row">
        			Productos que NO llegaron
      			<div class="col-2" style="text-align:left !important;">
      				<i class="icon-pin"><b style="font-size:7px;" id="transfer_difference_counter">0</b></i>
      			</div>
      		</div>
  		</button>
    </h2>
    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
    	<div class="accordion-body">
	<!-- Faltante -->
			<div class="transfers_validation_container">
				<h5 class="title_sticky"></h5>
				<table class="table table-bordered table_80">
					<thead class="header_sticky" style="top : -10px;">
						<tr>
							<th>Producto</th>
							<th>Faltante</th>
						</tr>
					</thead>
					<tbody id="transfer_difference">
						<?php
							//echo receptionResumen( 1, $link );
						?>
					</tbody>
					<tfoot></tfoot>
				</table>
			</div>
    	</div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
        	<div class="row">
        			Productos que llegaron de MÁS
      			<div class="col-2" style="text-align:left !important;">
      				<i class="icon-pin"><b style="font-size:7px;" id="transfer_excedent_counter">0</b></i>
      			</div>
      		</div>
      	</button>
    </h2>
    <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
    	<div class="accordion-body">
	<!-- Productos de más que se quedan en sucursal -->
			<div class="transfers_validation_container">
				<table class="table table-bordered table_80">
					<thead class="header_sticky" style="top : -10px;">
						<tr>
							<th>Producto</th>
							<th>Excedente</th>
						</tr>
					</thead>
					<tbody id="transfer_excedent">
						<?php
						//	echo receptionResumen( 2, $link );
						?>
					</tbody>
					<tfoot></tfoot>
				</table>
			</div>
    	</div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingThree">
      	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
        	<div class="row">
        		<!--div class="col-10"-->
        		Productos que llegaron bien
      			<!--/div-->
      			<div class="col-2" style="text-align:right !important;">
      				<i class="icon-pin"><b style="font-size:7px;" id="products_ok_list_counter">0</b></i><!--  -->
      			</div>
      		</div>
        </button>
    </h2>
    <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
    	<div class="accordion-body">		
	<!-- Productos de más que se regresan -->
			<div class="transfers_validation_container">
				<h5 class="title_sticky"></h5>
				<table class="table table-bordered table_80">
					<thead class="header_sticky" style="top : -10px;">
						<tr>
							<th>Producto</th>
							<th>Cantidad</th>
						</tr>
					</thead>
					<tbody id="products_ok_list">
						<?php
							//echo receptionResumen( 3, $link );
						?>
					</tbody>
					<tfoot></tfoot>
				</table>
			</div>
    	</div>
    </div>
  </div>
  <div class="row">
  	<div class="col-12"><label for="type_resumen" class="form-control text-center">Filtrar por : </label></div>
  	<div class="col-12">
	  	<select id="type_resumen" class="combo" onchange="change_filter_type( this );">
	  		<option value="0">Ver todos</option>
	  		<option value="1">No Resueltos</option>
	  		<option value="2">Ya Resueltos</option>
	  	</select>
	</div>
  </div>
</div>
<br>
<br>
<div class="row">
	<div class="col-3"></div>
	<div class="col-6">
		<button
			type="button"
			class="btn btn-primary form-control"
			id="got_to_count_since_info_view"
			onclick="show_view( this, '.finish_transfers_since_button');"
		>
			<i class="icon-up-hand">Ir al conteo</i>
		</button>
	</div>
</div>
	<div class="" style="font-size : 70%; max-height: 450px; overflow-y : auto; ">
		<br>
	</div>
<hr>