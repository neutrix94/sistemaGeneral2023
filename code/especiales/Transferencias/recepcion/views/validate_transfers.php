<?php
//include( 'resolution_view.php' );
?>
<div class="accordion group_card" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
      	<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        	<div class="row">
        			Pendientes de recibir
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
							<!--th>Ver</th>
							<th>Resuelto</th-->
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
        			Llegaron de más
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
							<!--th>Ver</th>
							<th>Resuelto</th-->
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
        			No corresponden ( Apartados )
      			<!--/div-->
      			<div class="col-2" style="text-align:right !important;">
      				<i class="icon-pin"><b style="font-size:7px;" id="transfer_dont_correspond_counter"></b></i><!--  -->
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
							<th>Se regresa</th>
							<!--th>Ver</th>
							<th>Resuelto</th-->
						</tr>
					</thead>
					<tbody id="transfer_dont_correspond">
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

  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingFour">
      	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false" aria-controls="panelsStayOpen-collapseFour">
        	<div class="row">
        			Productos que se devolverán
      			<div class="col-2" style="text-align:right !important;">
      				<i class="icon-pin"><b style="font-size:7px;" id="transfer_return_counter">0</b></i>
      			</div>
      		</div>
        </button>
    </h2>
    <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingFour">
    	<div class="accordion-body">		
	<!-- Productos de más que se regresan -->
			<div class="transfers_validation_container">
				<h5 class="title_sticky"></h5>
				<table class="table table-bordered table_80">
					<thead class="header_sticky" style="top : -10px;">
						<tr>
							<th>Producto</th>
							<th>Se regresa</th>
							<!--th>Ver</th>
							<th>Resuelto</th-->
						</tr>
					</thead>
					<tbody id="transfer_return">
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
<!-- -->

<!-- código únicos pendientes de recibir -->
<!--div class="group_card row">
	<div class="col-2"></div>
	<div class="col-8">
		<button
			type="button"
			class="btn btn-warning form-control"
			onclick="show_unic_codes_pending_to_recive();"
		>
			<i class="icon-tag" style="color:red;"><b>Ver códigos únicos pendientes de recibir</b></i>
		</button>
	</div>
</div-->
	<div class="" style="font-size : 70%; max-height: 450px; overflow-y : auto; ">
		<br>
	</div>
<hr>