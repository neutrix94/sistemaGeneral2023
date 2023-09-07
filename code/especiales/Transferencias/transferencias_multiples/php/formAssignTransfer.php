<?php
	include( '../../../../../config.ini.php' );
	include( '../../../../../conect.php' );
	include( '../../../../../conexionMysqli.php' );
	$transfer_id = $_GET['p_k'];

	if( isset( $_GET['fl'] ) && $_GET['fl'] == 'reassign' ){
//actualiza las tranferencias como pausadas durante la asignación
		$sql = "UPDATE ec_transferencias_surtimiento 
					SET id_status_asignacion = 3 
				WHERE id_transferencia = '{$transfer_id}'
				AND id_status_asignacion < 4";
		$stm = $link->query( $sql ) or die( "Error al poner surtimientos de transferencia en pausa : " . $link->error );
	}

	$sql = "SELECT
				t.id_transferencia AS transfer_id,
				t.folio,
				so.nombre AS origen,
				sd.nombre AS destino,
				COUNT( tp.id_transferencia ) AS parts,
				/*COUNT( tp.id_transferencia ) - */COUNT( tsd.id_surtimiento_detalle ) AS parts_assigned
			FROM ec_transferencias t
			LEFT JOIN ec_transferencia_productos tp 
			ON tp.id_transferencia = t.id_transferencia
			LEFT JOIN sys_sucursales so ON so.id_sucursal = t.id_sucursal_origen
			LEFT JOIN sys_sucursales sd ON sd.id_sucursal = t.id_sucursal_destino
			LEFT JOIN ec_transferencias_surtimiento_detalle tsd ON tsd.id_transferencia_producto = tp.id_transferencia_producto
			WHERE t.id_transferencia = '{$transfer_id}'
			/*AND tsd.id_transferencia_producto IS NULL*/
			GROUP BY t.id_transferencia";
	$stm = $link->query( $sql ) or die( "Error al consultar encabezado de la transferencia : " . $link->error );
	$r = $stm->fetch_assoc();

	function seekPeopleLoged( $link ){//$txt, $users,
		$sql= "SELECT
					u.id_usuario AS id,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
					IF( ts.id_transferencia_surtimiento IS NULL, 0, COUNT( ts.id_transferencia_surtimiento ) ) AS assigned_transfer
				FROM sys_users u
				LEFT JOIN ec_transferencias_surtimiento ts ON u.id_usuario = ts.id_usuario_asignado
				LEFT JOIN ec_registro_nomina rn ON u.id_usuario = rn.id_empleado
				WHERE u.id_sucursal = 1
				AND rn.fecha = DATE_FORMAT( NOW(), '%Y-%m-%d')
				AND rn.hora_salida = '00:00:00'
				GROUP BY u.id_usuario";
		$stm = $link->query( $sql ) or die( "Error al consultar usuarios logueados : " . $sql . $link->error );
		$resp = "<select id=\"combobox\" class=\"form-control\" onchange=\"alert();\">";
		if( $stm->num_rows <= 0 ){
			//return '<div>No se encontraron usuarios que coincidan con la búsqueda!</div>';
			$resp .= '<option value="">No hay usuarios con asistencia</option>';
		}else{
			$resp .= '<option value=""></option>';
		}
		while ( $r = $stm->fetch_assoc() ) {
			$resp .= "<option value=\"{$r['id']}\">{$r['name']} ({$r['assigned_transfer']})</option>";
		}
		$resp .= "</select>";
		return $resp;
	}
?>

	<div class="row" style="padding : 20px;">
		<div class="row">
			<div class="col-3">
				<label class="assign_label">Transferencia</label>
			</div>
			<div class="col-5">
				<input 
					type="text" 
					value="<?php echo $r['folio']; ?>" 
					class="form-control readonly text-center" 
					id="transfer_folio_input"
					readonly>
			</div>
			<div class="col-3 text-center">
				<button class="btn btn-warning" onclick="show_assignment_detail( <?php echo $r['transfer_id']; ?>, null );">
					<i class="icon-eye">Ver Resumen</i>
				</button>
			</div>
		</div>
		<br>
		<br>
		<br>
		<div class="row">
			<input type="hidden" id="transfer_id" value="<?php echo $r['transfer_id']; ?>">
			<div class="row">
				<div class="col-1"></div>
				<div class="col-2">
					<label class="assign_label">Partidas</label>
					<input 
						type="text" 
						id="transfer_parts"
						value="<?php echo $r['parts']; ?>" 
						class="form-control readonly text-right" 
						readonly
					>
				</div>	
				<div class="col-2">
					<label class="assign_label">Partidas Asign.</label>
					<input 
						type="text" 
						id="transfer_parts_assigned"
						value="<?php echo $r['parts_assigned']; ?>" 
						class="form-control readonly text-right" 
						readonly
					>
				</div>	
				<div class="col-2">
					<label class="assign_label">Partidas Pend.</label>
					<input 
						type="text" 
						id="slope_transfer_parts"
						value="<?php echo ($r['parts'] - $r['parts_assigned'] ); ?>" 
						class="form-control readonly text-right" 
						readonly
					>
				</div>	
				<div class="col-2">
					<label class="assign_label">Origen</label>
					<input type="text" value="<?php echo $r['origen']; ?>" class="form-control readonly" readonly>
				</div>
				<div class="col-2">
					<label class="assign_label">Destino</label>
					<input type="text" value="<?php echo $r['destino']; ?>" class="form-control readonly" readonly>
				</div>	
			</div>
		</div>
		<br>
		<hr>
		<br>
		<div class="row ">
			<div class="col-3">
				<!--label  class="assign_label">Número de Personas que surtirán la Transferencia</label-->
				<div class="input-group">
					<input 
						type="number"
						class="form-control"
						id="peopleNumber"
						placeholder="cantidad de personas por surtir"
					>
					<button
						type="button"
						class="btn btn-success btn_number_assign"
						onclick="setPeopleNumber();"
						title="Guardar"
					>
						<i class="icon-ok-circle"></i>
					</button>
					<button
						type="button"
						class="btn btn-warning btn_number_assign_edit"
						onclick="setPeopleNumber( 1 );"
						title="Guardar"
					>
						<i class="icon-pencil-neg"></i>
					</button>
				</div>
			</div>
			<div class="col-6">
				<div class="input-group">
					<input 
						type="text" 
						id="people_seeker"
						onkeyup="seek_people_loged( 1, )"
						class="form-control"
						placeholder="Buscar personas por asignar"
						disabled
					>
					<button
						type="button"
						class="btn btn-light"
						onclick="seek_people_loged( 1, true );"
						title="Guardar"
					>
						<i class="icon-down-open"></i>
					</button>
				</div>
				<div class="people_seeker_response"></div>
				<!--div class="ui-widget">
				 	<label>Personas por asignar: </label>
				 	<?php
				 		//echo seekPeopleLoged( $link );
				 	?>
				  	
				</div>
				<button id="toggle">Show underlying select</button-->
			</div>
			<div class="col-3">
				<input 
					type="number"
					class="form-control readonly"
					id="partsNumber"
					placeholder="partidas por persona"
					readonly
				>
			</div>

		</div>
		<hr>

		<div class="row"><br><br>
			<div class="col-2"></div>
			<div class="col-2"></div>
		</div>
		<div class="assignations">
			<table class="table table-striped">
				<thead>
					<tr><!-- no_visible -->
						<th class="<?php echo ( $_GET['fl'] == 'reassign' ? '' : '' ); ?>">Valido</th>
						<th>Persona</th>
						<th>Numero de Partidas</th>
						<th>Partidas Surtidas</th>
						<th>Detalle</th>
						<th>Liberar</th>
					</tr>
				</thead>
				<tbody class="assigned_users"></tbody>
				<tfoot>
					<!--tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr-->	
				</tfoot>
			</table>
		</div>	
	</div>

<?php
	if( isset( $_GET['fl'] ) && $_GET['fl'] == 'reassign' ){
//carga usuarios asignados a la transferencia			
		echo '<script type="text/javascript">getAssignedUsers( ' . $transfer_id . ' );</script>';	
	}
?>