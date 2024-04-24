<?php
	$sql = "";
?>
<div class="row">
	<div class="col-6">
		Rama Actual
		<select class="form-control">
			<option>-- Seleccionar --</option>
			<option value="Desarrollo">Desarrollo</option>
		</select>
	</div>
	<div class="col-6">
		Host BD
		<input type="text" class="form-control">
	</div>
	<div class="col-6">
		Usuario BD
		<input type="text" class="form-control">
	</div>
	<div class="col-6">
		Nombre BD
		<input type="text" class="form-control">
	</div>
	<div class="col-6">
		Password BD
		<input type="text" class="form-control">
	</div>
	<div class="col-6">
		Es servidor Principal
		<input type="checkbox" selected>
	</div>

	<div class="col-6 text-center">
		<button class="btn btn-success">
			<i class="icon-ok-circle">Aceptar</i>
		</button>
	</div>
	<div class="col-6 text-center">
		<button class="btn btn-danger" onclick="close_emergent();">
			<i class="icon-cancel-circled">Cancelar</i>
		</button>
	</div>
</div>