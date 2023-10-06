<div class="row" style="padding : 10px;">
	<div class="row">
		<p class="informativo" align="left">
		<div class="input-group">
			<input type="text" class="form-control" id="buscador" placeholder="Escanea ticket( s )" onkeyup="busca(event);">
			<button 
				type="button"
				title="Buscar de nuevo" 
				onclick="busca( 'intro' );"
				class="btn btn-warning"
			>
				<i class="icon-barcode"></i>
			</button>
			<button 
				type="button"
				title="Buscar de nuevo" 
				onclick="link(2);"
				class="btn btn-danger"
			>
				<i class="icon-ccw-1"></i>
			</button>
		</div>
		<!--<img src="../../../../img/especiales/buscar.png" width="50px"></p>-->
		<div id="res_busc"></div>
	</div>
	<div class="row" style="margin-top : 20px;">
		<table class="table table-striped table-bordered">
			<thead class="bg-danger">
				<tr>
					<th class="text-center text-light">Folio</th>
					<th class="text-center text-light">Monto</th>
					<th class="text-center text-light">Quitar</th>
				</tr>
			</thead>
			<tbody id="tickets_list"></tbody>
		</table>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-6">
				<button
					class="btn btn-success form-control"
					onclick="setTickets();"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>
			</div>
		</div>
	</div>
	<!--div class="row">
		<p class="informativo" align="center">Monto:<br>
			<input type="text" id="monto_total" class="form-control" style="background:white;" disabled>
		</p>
	</div>
	<div class="row">
		<p class="informativo" align="center">Saldo a favor:<br>
			<input type="text" id="saldo_favor" class="form-control text-end" style="background:white;" disabled>
		</p>
	</div-->
</div>