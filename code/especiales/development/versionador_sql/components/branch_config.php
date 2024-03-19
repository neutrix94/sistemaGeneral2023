<?php
	include( '../../../../../conexionMysqli.php' );
	$branch = "";
	if( isset( $_POST['branch_id'] ) || isset( $_GET['branch_id'] ) ){
		$branch_id = ( isset( $_POST['branch_id'] ) ? $_POST['branch_id'] : $_GET['branch_id'] );
		if( ! include( '../ajax/scriptVersioner.php' ) ){
			die( "No se incluyo libreria de Versionamiento MySQL" );
		}
		$sV = new scriptVersioner( $link );
		$branch = $sV->getBranch( $branch_id );
	}
	//die( 'ok' );
	echo "<div class=\"row\">
		<div class=\"col-2\"></div>
		<div class=\"col-8 row\">
			<h4 class=\"icon-flow-branch text-center\">{$branch['name']}</h4>
			<br>
			<br>
			<input type=\"hidden\" value=\"{$branch['branch_id']}\">
			<div class=\"col-4\">
				Nombre
			</div>
			<div class=\"col-8\">
				<input type=\"text\" class=\"form-control\" value=\"{$branch['name']}\">
			</div>

			<div class=\"col-4\">
				Orden
			</div>
			<div class=\"col-8\">
				<input type=\"text\" class=\"form-control\" value=\"{$branch['branch_order']}\">
			</div>

			<div class=\"col-4\">
				Observaciones
			</div>
			<div class=\"col-8\">
				<textarea class=\"form-control\">{$branch['branch_notations']}</textarea>
			</div>

			<div class=\"col-4\">
				URL de repositorio central
			</div>
			<div class=\"col-8\">
				<input type=\"text\" class=\"form-control\" value=\"{$branch['central_api_url']}\">
			</div>

			<div class=\"col-4\">
				Habilitado
			</div>
			<div class=\"col-8 text-center\">
				<input type=\"checkbox\">
			</div>

			<div class=\"col-4\">
				Fecha alta
			</div>
			<div class=\"col-8\">
				<input type=\"text\" class=\"form-control\" disabled>
			</div>

			<div class=\"col-4\">
				Fecha modificacion
			</div>
			<div class=\"col-8\">
				<input type=\"text\" class=\"form-control\" disabled>
			</div>
			<div class=\"row\">
				<div class=\"col-6 text-center\"><br><br>
					<button class=\"btn btn-success\">
						<i class=\"icon-ok-circle\">Guardar</i>
					</button>
				</div>
				<div class=\"col-6 text-center\"><br><br>
					<button class=\"btn btn-danger\" onclick=\"close_emergent();\">
						<i class=\"icon-cancel-circled\">Cancelar</i>
					</button><br><br>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<h5 class=\"icon-share-2 text-center\">Ingresa las urls de los servidores que deseas actualizar remotamente</h5>
			<div id=\"branch_webhooks\">
				<table class=\"table\">
					<thead class=\"\">
						<tr>
							<th width=\"40%\">Nombre / Descripcion </th>
							<th width=\"50%\">URL</th>
							<th width=\"10%\">Habilitado</th>
						</tr>
					</thead>
					<tbody class=\"\" id=\"\">
						<tr>
							<td>
								<input type=\"text\" class=\"form-control\">
							</td>
							<th>
								<input type=\"text\" class=\"form-control\">
							</th>
							<th class=\"text-center\">
								<label for=\"remote_branch_enabled_\" class=\"icon-toggle-on\"></label>
								<input type=\"checkbox\" id=\"remote_branch_enabled_\" class=\"hidden\" onclick=\"\">
							</th>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>";
?>