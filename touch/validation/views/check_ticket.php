<?php
	function getLastSales( $user_sucursal, $link ){
		$resp = '';
		$sql = "SELECT 
					id_pedido,
					folio_nv,
					total
				FROM ec_pedidos
				WHERE id_sucursal = '{$user_sucursal}'
				AND folio_nv != 'agrupacion'
				/*AND venta_validada = 0*/
				ORDER BY id_pedido DESC
				LIMIT 20";
		$stm = $link->query( $sql ) or die( "Error al consultar las notas de venta : " . $link->error );
		
		while( $r = $stm->fetch_row() ){
			$resp .= "<tr onclick=\"getTicketInfo( $r[0] );\">";
				$resp .= "<td>{$r[1]}</td>";
				$resp .= "<td>$ {$r[2]}</td>";
			$resp .= "</tr>";
		}
		return $resp;
	}

	function seekTicketByBarcode( $barcode ){
		$sql = "SELECT
					p.id_productos,
					pp.id_proveedor_producto,
					p.nombre,
					pp.clave_proveedor
				FROM ec_productos p
				LEFT JOIN ec_proveedor_producto pp 
				ON pp.id_producto = p.id_productos
				WHERE pp.codigo_barras_pieza_1 = '{$barcode}'
				OR pp.codigo_barras_pieza_2 = '{$barcode}'
				OR pp.codigo_barras_pieza_3 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
				OR pp.codigo_barras_caja_1 = '{$barcode}'
				OR pp.codigo_barras_caja_2 = '{$barcode}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el código de barras : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return 'El código de barras no fue encontrado, verifique y vuelva a intentar!';
		}else{
			$r = $stm->fetch_row();
			return 'ok|{$r[0]}~{$r[1]}~{$r[2]}~{$r[3]}';
		}
	}
?>
	<div>
		<div class="group_card">
			<label for="barcode_seeker">Escaner código de barras del Ticket</label>
			<div class="input-group">
				<input 
					type="text"
					id="barcode_seeker"
					class="form-control"
					placeholder="Escaner código de barras del Ticket"
					onkeyup="evitarCaracteresEspeciales( event );seekTicketBarcode( event, this, 'seekTicketBarcode' );"
				>
				<!-- 
					onblur="focus_again( this );" -->
				<button class="input-group-text btn btn-warning" 
					onclick="seekTicketBarcode( 'enter', '#barcode_seeker', 'seekTicketBarcode' );"><!-- alert_scann(); -->
					<i class="icon-barcode"></i>
				</button>
			</div>
		</div>

		<div class="group_card tickets_list no_visible">
			<table class="table table-striped table_80">
				<thead class="header_sticky header_sticky_validation">
					<tr>
						<th>Ticket</th>
						<th>Monto</th>
					</tr>
				</thead>
				<tbody>
					<?php
						echo getLastSales( $user_sucursal, $link );
					?>
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>

	</div>