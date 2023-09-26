
<br />
<!-- Implementacion Oscar 2023/09/26 para el buscador de productos surtidos -->
<div class="row" style="margin : 10px;">
	<div class="input-group">
		<input type="text" class="form-control" id="list_seeker" onkeyup="list_seeker( this, event );">
		<button
			class="btn btn-warning"
			onclick="list_seeker( '#list_seeker', 'intro' );"
		>
			<i class="icon-search"></i>
		</button>
	</div>
</div>
<!-- Fin de cambio Oscar 2023/09/26 -->
<table class="table table-bordered table-striped" style="margin : 10px; width : calc( 100% - 20px ); font-size : 80%;">
	<thead class="header_sticky-0">
		<tr>
			<th class="text-center">Producto</th>
			<th class="text-center">Modelo</th>
			<th class="text-center">Cajas</th>
			<th class="text-center">Paq</th>
			<th class="text-center">Pzs</th>
			<th class="text-center">Tot Pzs</th>
<!-- implementacion Oscar 2023/09/26 En la pantalla de surtimiento, en el apartado de SURTIDO si puede tener una columna que tenga el numero de caja -->
			<th class="text-center">Marcar con</th>
<!-- fin de cambio Oscar 2023/09/26 -->
		</tr>
	</thead>
	<tbody id="list_assignmets_supplied">
	<?php
		//echo getAssignmentList( $user_id, $link );
	?> 
	</tbody>
</table>