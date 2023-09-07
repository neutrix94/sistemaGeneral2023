<?php
	include('../../../conexionMysqli.php');
	
	$sql = "SELECT id_sucursal, nombre FROM sys_sucursales WHERE id_sucursal > -2 ORDER BY nombre";
	$eje = $link->query( $sql ) or die("Error al consultar sucursales : { $link->error }");
	$sucursales = '';
	while ( $r = $eje->fetch_row() ) {
		$sucursales .= "<option value=\"{$r[0]}\">{$r[1]}</option>";
	}

	function build_accordeon( $tipo_lista = 'Consulta', $numero = 1, $link){
		$cont = 0;
		$resp = "<div class=\"accordion\" id=\"accordionExample\">";
		$sql = "SELECT 
					IF( s.id_sucursal =-1, 'MULTISUCURSAL', s.nombre ),
					GROUP_CONCAT(
						CONCAT( u.id_usuario, '~',u.id_sucursal, '~', u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno, ' (', u.login, ')')
						SEPARATOR '|' 
					)AS nombre,
					s.id_sucursal
			FROM sys_users u
			LEFT JOIN sys_sucursales s ON s.id_sucursal = u.id_sucursal
			WHERE s.id_sucursal != 0
			GROUP BY u.id_sucursal
			ORDER BY u.id_sucursal ASC";
			//return ($sql);
		$eje = $link -> query($sql)or die("Error al consultar las herramientas!!!<br>".mysql_error()."<br>".$sql);
		
		while($r = $eje->fetch_row()){
			$resp .= '<div class="accordion-item" id="suc_' . $r[2] . '">';
		    	$resp .= '<h2 class="accordion-header" id="heading_'.$numero .'_'.$cont.'">';
			    	$resp .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_'.$numero .'_'.$cont.'"'
			    	. ' aria-expanded="true" aria-controls="collapse_'.$numero .'_'.$cont.'"'
			    	. ' id="herramienta_'.$numero .'_' . $cont . '" class="opc_btn">';
			        $resp .= $r[0];
			      	$resp .= '</button>';
		    	$resp .= '</h2>';
		    	$resp .= '<div id="collapse_'.$numero .'_'.$cont.'" class="accordion-collapse collapse description" aria-labelledby="heading_'.$numero .'_' . $cont . '" data-bs-parent="#accordionExample">';
			    	$resp .= '<div class="accordion-body">';
			$users = explode('|', $r[1]);
			foreach ($users as $key => $user) {
				$tmp = explode('~', $user);
				if( $tmp[1] != '' && $tmp[1] != null  && $tmp[1] != 0 ){
					$resp .= '<input type="checkbox" id="user_' . $tmp[0] . '" sucursal="' . $tmp[1] . '">' 
						. '<span onclick="select_specific_user( ' . $tmp[0] . ', \'' . $tmp[2] . '\' )">' . $tmp[2] . '</span><br/>';
				}
			}
			    	//$resp .= $r[1];
			    	$resp .= '</div>';
		    	$resp .= '</div>';
		  	$resp .= '</div>';
			$cont ++;
		}
		//$resp.= '<input type="hidden" id="contador_herramientas_' . $numero . '" value="' . $cont . '">';
		$resp .= '</div>';
		return $resp;
	}
?>

<!DOCTYPE html>
<head>
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/utils.js"></script>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<title>Nómina</title>
</head>
<body>
	<div class="row global">
		<div class="col-sm-2">
			<input 
				type="checkbox"
				id="all_users" 
			/>
			<label for="all_users">Todos los Usuarios</label>
		</div>
		<div class="col-sm-3">
			<input 
				type="search" 
				class="form-control" 
				placeholder="Buscar Usuario..."
				onkeyup="user_search( this )"
				id="search"
				style="display:inline; width : 85%;"
			/> 
			<button 
				onclick="reset_search();"
				class="btn btn-light" 
				style="display:inline; width : 10%; padding : 0;">
				<img src="../../../img/especiales/reset.png" width="100%;">
			</button>
			<div class="users_results"></div>
		</div>
		<div class="col-sm-2">
			<select 
				type="search" 
				class="form-control"
				id="sucursal"
				onchange="change_sucursal(this);" 
			>
				<option value="0">Todas las Sucursales</option>
				<?php 
					echo $sucursales;
				?>
			</select>
		</div>
		<div class="col-sm-2">
			<input 
				type="date" 
				class="form-control"
				id="start_date" 
			/>
		</div>
		<div class="col-sm-2">
			<input 
				type="date" 
				class="form-control"
				id="end_date"
			/>
		</div>
		<div class="col-sm-1">
			<button 
				class="btn btn-success form-control"
				onclick="getData();"
				id="button_generate">
				Generar
			</button>
		</div>
	</div>
	<div class="row global">
		
		<div class="col-sm-2" id="users_list">
			<?php 
				echo build_accordeon( '',1, $link ); 
			?>
			<div class="row" style="display: block;position:absolute;bottom:20px;">
				<button 
					class="btn btn-light"
					type="button" 
					onclick="if( confirm('Salir de esta pantalla?') ){ location.href='../../../index.php?';}"
					style="padding : 0;"
			>		
					<img src="../../../img/img_casadelasluces/Logo.png" width="50px">
					<br />Ir al panel
				</button>
			</div>
		</div>
		
		<div class="col-sm-10 table_container">
			<table class="table" id="nomina_list">
				<thead>
					<tr>
						<th class="col">Empleado</th>
						<th class="col">Fecha</th>
						<th class="col">Hora entrada</th>
						<th class="col">Hora Salida</th>
						<th class="col">Horas Trabajadas</th>
						<th class="col" colspan="5">Acciones</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
				<tfoot style="position:absolute;top : 580px;right : 10%;">
					<tr>
						<td colspan="5" align="right">Total</td>
						<td> $ </td>
						<td> $ </td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class="row">
			<div class="col-sm-2"></div>
			<div class="col-sm-10">
				<table class="table">
					<tr>
						<td>Empleado : 
							<select 
								class="form-control" 
								id="employee"
							>
								<option value="0">-- Seleccionar --</option>	
							</select>
						</td>
						<td>
							Fecha : 
							<input 
								type="date" 
								class="form-control"
								id="new_date"
							>
						</td>
						<td>
							Hora Entrada : 
							<input 
								type="time"
								class="form-control"
								id="new_initial_date"
							>
						</td>
						<td>
							Hora Salida : 
							<input 
								type="time"
								class="form-control"
								id="new_final_date"
							>
						</td>
						<td style="text-align:center; vertical-align : bottom;">
							<button 
								type="button" 
								class="btn btn-primary form-control"
								onclick="add_row()"
							>
								Agregar
							</button>
						</td>
					</tr>
				</table>
			</div>
	</div>
	<input type="hidden" id="total_details_rows" value="0">
</body>
</html>