<?php
	if( isset( $_POST['$user_id'] ) ){
		include( '../../../../../config.ini.php' );
		include( '../../../../../conectMin.php' );
		include( '../../../../../conexionMysqli.php' );
	}

/*	function getAssignmentList( $user_id, $link ){
		$resp = '';
		$sql = "SELECT 
					ts.id_transferencia_surtimiento,
					t.folio,
					ts.total_partidas,
					IF( t.id_tipo = 5, 'urgente', 'normal') AS tipo
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias t ON t.id_transferencia = ts.id_transferencia
				WHERE ts.id_usuario_asignado = '{$user_id}'
				AND ts.id_status_asignacion < 4";
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '<tr><td colspan="3" align="center">Sin Transferencias asignadas!</td></tr>';
		}
		while ( $r = $stm->fetch_row() ) {
			$resp .= build_list_row( $r );
		}
		return $resp;
	}*/
?>
<br />
<div class="">
	<div class="list_container">
		<div class="input-group">
			<input 
				type="text" 
				id="transfers_seeker"
				class="form-control" 
				onkeyup="seekTransferByBarcode( event );"
				placeholder="Escanear Transferencias por validar">
			<button 
				class="btn btn-warning"
				onclick="seekTransferByBarcode( 'intro' );"
			>
				<i class="icon-barcode"></i>
			</button>
		</div>
		<table class="table">
			<thead class="list_header_sticky" style="font-size: 100%;">
				<tr>	
					<th class="text-center"><i class="icon-barcode"></i></th>
					<th id="folio_order" class="text-center" 
						onclick="reload_transfers_list_view( this );" 
						order="">
						Folio
						<i class="icon-up-big"></i>
					</th>
					<th id="sucursal_origen" class="text-center" order="">
						<?php
							echo getStoresFilter( 'Orig', $sucursal_id, $link );
						?>
					</th>
					<th id="sucursal_destino" class="text-center">
						<?php
							echo getStoresFilter( 'Dest', '', $link );
						?>
					</th>
					<th id="status_id_order" class="text-center" onclick="reload_transfers_list_view( this );" order="">
						Status
						<i class="icon-up-big"></i>
					</th>
					<th id="order_block_id_order" class="text-center" onclick="reload_transfers_list_view( this );" order="">
						Bloque
						<i class="icon-up-big"></i>
					</th>
					<th class="text-center">Revisar</th>
				</tr>	
			</thead>
			<tbody id="transfers_list_content" style="font-size:100%;">
			<?php
				echo getTransfersListValidation( $link, $sucursal_id );
			?> 
			</tbody>
			<tfoot>
				<tr></tr>
			</tfoot>
		</table>
	</div>
	
	<br />
	
	<div class="row">

		<div class="col-2"></div>

		<div class="col-8">
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="set_transfers();"
			>
				Validar Transferencias
			</button>
		</div>

		<div class="col-2"></div>
	</div>
</div>