<?php
	if( isset( $_POST['$user_id'] ) ){
		include( '../../../../../config.ini.php' );
		include( '../../../../../conectMin.php' );
		include( '../../../../../conexionMysqli.php' );
	}

	function getAssignmentList( $user_id, $link ){
		$resp = '';
		$sql = "SELECT 
					/*0*/ts.id_transferencia_surtimiento,
					/*1*/t.folio,
					/*2*/ts.total_partidas,
					/*3*/IF( t.id_tipo = 5, 'urgente', 'normal') AS tipo,
					/*4*/t.id_transferencia,
					/*5*/s.nombre,
					/*6*/IF( t.id_tipo = 1, 'URGENTE', 'NORMAL' )
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias t 
				ON t.id_transferencia = ts.id_transferencia
				LEFT JOIN sys_sucursales s
				ON s.id_sucursal = t.id_sucursal_destino
				WHERE ts.id_usuario_asignado = '{$user_id}'
				AND ts.id_status_asignacion < 4";
		//die( 'here' . $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '<tr><td colspan="5" align="center">Sin Transferencias asignadas!</td></tr>';
		}
		$counter = 0;
		while ( $r = $stm->fetch_row() ) {
			$resp .= build_list_row( $r, $counter );
			$counter ++;
		}
		return $resp;
	}

	function build_list_row( $row, $counter ) {
		$resp = "<tr id=\"list_assignmet_{$counter}\"";
			$resp .= " priority=\"{$row[6]}\"";
		$resp .= ">";
		$resp .= "<td align=\"center\">{$row[4]}</td>
			<td align=\"center\">{$row[5]}</td>
			<td align=\"center\">{$row[2]}</td>
			<td align=\"center\">
				<button 
					class\"btn btn-warning\"
					onclick=\"pack_off( {$row[0]}, {$row[4]}, null, '{$row[5]}', '{$row[6]}' );\"
				>
					<i class=\"icon-right-big\"></i>
				</button>
			</td>
		</tr>";
		return $resp;
	}
?>
<div class="row">
	<div class="col-3" style="text-align : right;">
		<label>Ver : </label>
	</div>
	<div class="col-8">
		<select class="form-control" onchange="filter_list( this );">
			<option value="-1">Todas</option>
			<option value="5">Normal</option>
			<option value="1">Urgente</option>
		</select>
	</div>
</div>
<table class="table table-striped" style="margin : 10px; width : calc( 100% - 20px ); font-size : 80%;">
	<thead>
		<tr>
			<th>Transferencia</th>
			<th>Destino</th>
			<th>Partidas</th>
			<th>Surtir</th>
		</tr>
	</thead>
	<tbody class="list_assignmets">
	<?php
		echo getAssignmentList( $user_id, $link );
	?> 
	</tbody>
</table>