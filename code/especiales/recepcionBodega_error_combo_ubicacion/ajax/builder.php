<?php
	include( '../../../../config.ini.php' );
	include( '../../../../conectMin.php' );
	include( '../../../../conexionMysqli.php' );

	$builder = new Builder( $link );

	$action = $_POST['fl'];
	switch ( $action ) {
		case 'buildInvoiceList':
			//echo $_POST['series'];
			echo $builder->buildInvoiceList( $_POST['series'] );	
		break;

		case 'buildInvoiceListFinish':
			echo $builder->buildInvoiceListFinish( $_POST['series'], $link );
		break;
		
		default:
			# code...
			break;
	}

	class Builder{
		private $link;
		function __construct( $link )
		{
			$this->link = $link;	
		}

		public function buildInvoiceList( $series ){
			$resp = "";
			foreach ($series as $key => $serie) {
			//obtiene la cabecera de las series
				$sql = "SELECT 
							id_recepcion_bodega,
							folio_recepcion,
							numero_partidas,
							id_recepcion_bodega_status
						FROM ec_recepcion_bodega
						WHERE serie = '{$serie}'";
				$exc = $this->link->query( $sql ) or die( "Error al consultar cabecera de la recepción" . $this->link->error );
				while ( $r = $exc->fetch_row() ) {
					$resp .= $this->buildInvoiceListTable( $r, $serie );
				}
			}
			return $resp;
		}
		
		public function buildInvoiceListTable( $header, $serie ){
			$resp = '<table class="table table-striped table_list">';
				$resp .= '<thead>';
					$resp .= '<tr>';
						$resp .= '<th colspan="6" style="text-align : center;">' . $header[1] . '</th>';
					$resp .= '</tr>';
					$resp .= '<tr>';
						$resp .= '<th>Serie, partida</th>';
						$resp .= '<th>Producto</th>';
						$resp .= '<th>Modelo</th>';
						$resp .= '<th>Cajas recibidas</th>';
						$resp .= '<th>Piezas sueltas</th>';
						$resp .= '<th>Total</th>';
					$resp .= '</tr>';
				$resp .= '</thead>';
				$resp .= "<tbody id=\"serie_{$serie}\">";
		//genera el detalle de los registros
			$sql = "SELECT 
						rd.id_recepcion_bodega_detalle,
						CONCAT( rd.serie, ',', rd.numero_partida ),
						p.nombre,
						rd.modelo,
						rd.cajas_recibidas,
						rd.piezas_sueltas_recibidas,
						rd.piezas_sueltas_recibidas + ( rd.piezas_por_caja * rd.cajas_recibidas ) AS total
					FROM ec_recepcion_bodega_detalle rd
					LEFT JOIN ec_productos p ON p.id_productos = rd.id_producto
					WHERE rd.id_recepcion_bodega = '{$header[0]}'
					ORDER BY rd.numero_partida, p.id_productos";
					//die($sql);
			$exc = $this->link->query( $sql ) or die( "Error al consultar los detalles de la recepción" );
			while ( $r = $exc->fetch_row() ){
				$resp .= $this->buildInvoiceListRow( $r );
			}

				$resp .= '</tbody>';
			$resp .= '</table>';

			return $resp;
		}

		public function buildInvoiceListRow( $row ){
			$resp = "<tr onclick=\"editDetail({$row[0]})\">";
			foreach ($row as $key => $detail) {
				if( $key > 0 ){
					$resp .= "<td";
					$resp .= ">{$detail}</td>";
				}
			}
			$resp .= "</tr>";
			return $resp;
		}

		public function buildInvoiceListFinish( $series = null, $link ){
			$resp = '<table class="table table-striped table-bordered table_list">';
				$resp .= '<thead class="header_sticky_top-10">';
					$resp .= '<tr>';
						$resp .= '<th colspan="6" style="text-align : center;">' . $header[1] . '</th>';
					$resp .= '</tr>';
					$resp .= '<tr>';
						$resp .= '<th>Folio de remisión</th>';
						$resp .= '<th>Serie</th>';
						$resp .= '<th>Número de partidas</th>';
						$resp .= '<th>Número de partidas recibidas</th>';
						$resp .= '<th>Status</th>';
					$resp .= '</tr>';
				$resp .= '</thead>';
				$resp .= "<tbody id=\"tbody_finish\">";	
			if( $series != null ){
				foreach ( $series as $key => $serie ) {
					$sql = "SELECT
								rb.id_recepcion_bodega,
								rb.folio_recepcion,
								rb.serie,
								rb.numero_partidas,
								COUNT( rbd.id_recepcion_bodega_detalle ),
								rb.id_recepcion_bodega_status
							FROM ec_recepcion_bodega rb
							LEFT JOIN ec_recepcion_bodega_detalle rbd
							ON rbd.id_recepcion_bodega = rb.id_recepcion_bodega
							LEFT JOIN ec_recepcion_bodega_status rbs
							ON rb.id_recepcion_bodega_status = rbs.id_recepcion_bodega_status
							WHERE rb.serie = '{$serie}'";
							//echo $sql;
					$stm = $link->query( $sql ) or die( "Error al consultar recepcion para listado de finalización : " . $link->error );
					$r = $stm->fetch_row();
					$resp .= $this->getFinishDetail( $r, $key );
				}//fin de foreach
			}else{
				$sql = "SELECT
							rb.id_recepcion_bodega,
							rb.folio_recepcion,
							rb.serie,
							rb.numero_partidas,
							COUNT( rbd.id_recepcion_bodega_detalle ),
							rb.id_recepcion_bodega_status
						FROM ec_recepcion_bodega rb
						LEFT JOIN ec_recepcion_bodega_detalle rbd
						ON rbd.id_recepcion_bodega = rb.id_recepcion_bodega
						LEFT JOIN ec_recepcion_bodega_status rbs
						ON rb.id_recepcion_bodega_status = rbs.id_recepcion_bodega_status
						GROUP BY rb.id_recepcion_bodega";
					$stm = $link->query( $sql ) or die( "Error al consultar recepcion para listado de finalización (carga inicial): " . $link->error );
					$c = 0;
					while ( $r = $stm->fetch_row() ){
						$resp .= $this->getFinishDetail( $r, $c );
						$c++;
					}
			}
				$resp .= '</tbody>';
			$resp .= '</table>';
			if( $series != null ){
				$resp .= '<div>';
					$resp .= '<button'; 
						$resp .= ' type="button"';
						$resp .= ' class="btn btn-success form-control"';
						$resp .= ' onclick="change_invoices_status();"';
					$resp .= '>';
						$resp .= 'Finalizar';
					$resp .= '</button>';
				$resp .= '</div><br/><br/>';
			}
			return $resp;
		}
		public function getFinishDetail( $r, $counter ){
			$resp .= '<tr>';
				$resp .= '<td class="no_visible">' . $r[0] . '</td>';
				$resp .= '<td>' . $r[1] . '</td>';
				$resp .= '<td>' . $r[2] . '</td>';
				$resp .= '<td>' . $r[3] . '</td>';
				$resp .= '<td>' . $r[4] . '</td>';
				$resp .= '<td>' . $this->getInvoicesStatus( $r[5], $counter ) . '</td>'; 
			$resp .= '</tr>'; 
			return $resp;
		}
		public function getInvoicesStatus( $current_status = null, $counter ){
			$resp = '<select id="status_' . $counter . '" class="form-control" style="font-size: 10px;">';
				$resp .= '<option value="1"' . ( $current_status == 1 ? ' selected' : '' ) . '>Sin Recibir</option>';
				$resp .= '<option value="2"' . ( $current_status == 2 ? ' selected' : '' ) . '>Recibida Parcialmente</option>';
				$resp .= '<option value="3"' . ( $current_status == 3 ? ' selected' : '' ) . '>Finalizada</option>';
			$resp .= '</select>';
			return $resp;
		}

		/*public function getLocationForm( $is_source = 0 ){
			//echo  'ok';
			$resp = '';
			if( $is_source == 1 ){
				$location_status_onchange = "change_location( 'source' );";
				$specific_id = "_source";
				$hidden_form_id = "new_location_form_source";
				$save_type = "source";
			}else{
				$location_status_onchange = "change_location( 'seeker' );";
				$specific_id = "_seeker";
				$hidden_form_id = "new_location_form_seeker";
				$save_type = "seeker";
			}

			$resp .= "<div class=\"group_card\">";

			if( $is_source != 1 ){
				$resp .= "<div class=\"row\" style=\"padding:10px;\">			
					<div class=\"input-group\">
						<input 
							type=\"text\" 
							id=\"seeker_product_location\"
							class=\"form-control\"
							placeholder=\"Buscar Productos Recibidos\"
							onkeyup=\"seekProductsLocations( this );\"
						>

						<button 
							type=\"button\"
							class=\"input-group-text btn btn-primary\"
							id=\"product_seeker_location_form_btn\"
							onclick=\"\"
						>
							<i class=\"icon-search\"></i>
						</button>
						<button 
							type=\"button\"
							class=\"input-group-text btn btn-warning\"
							id=\"product_reset_location_form_btn\"
							onclick=\"cleanProductLocationForm();\"
						>
							<i class=\"icon-spin3\"></i>
						</button>
					</div>
					<div class=\"product_location_seeker_response\"></div>
				</div>";


				$resp .= "<div class=\"row\">
						<label 
							id=\"product_name_location_form{$specific_id}\"
							class=\"product_name_location_form\"
						></label>
						<input 
							type=\"hidden\" 
							id=\"product_id_location_form{$specific_id}\"
						>
					</div>

				<div class=\"row\">
					<div class=\"col-7\">
						<label>Inventario Recibido</label>
						<input 
							type=\"number\" 
							class=\"form-control\"
							id=\"product_inventory_recived\"
							disabled
						>
					</div>
					<div class=\"col-5\">
						<label>Piezas sin ubic.</label>
						<input 
							type=\"number\" 
							class=\"form-control\"
							id=\"product_inventory_no_ubicated\"
							disabled
						>
					</div>
				</div>";

			}

			$resp .= "<div class=\"row\">
					<div class=\"col-7\">
					<label for=\"location_status\">Estatus de Mercancía</label>
					<select 
						id=\"location_status{$specific_id}\" 
						class=\"form-control\"
						onchange=\"{$location_status_onchange}\"
						disabled
					>
						<option value=\"0\">-- Seleccionar --</option>
						<option value=\"1\">Sin acomodar</option>
						<option value=\"2\">Ubicación : </option>
						<option value=\"3\">Nueva ubicación</option>
					</select>
				</div>
				<div class=\"col-5\">
					<label for=\"product_location\">Ubic. Actual : </label>
					<input 
						type=\"text\" 
						id=\"product_location{$specific_id}\" 
						class=\"form-control\"
						readonly
					>
				</div>
			</div>
			<br>";

			$resp .= "<div
					id=\"{$hidden_form_id}\" 
					class=\"row new_location_form\">
				<div class=\"col-3\">
					<label for=\"\">Sección de</label>
					<input 
						type=\"text\"
						id=\"aisle{$specific_id}\"
						class=\"form-control\"
						onkeyup=\"\"
						placeholder=\"Letra\"
					>
				</div>

				<div class=\"col-3\">
					<label for=\"\"># de</label>
					<input 
						type=\"number\"
						id=\"location_number{$specific_id}\"
						class=\"form-control\"
						onkeyup=\"\"
						placeholder=\"#\"
					>
				</div>

				<div class=\"col-3\">
					<label for=\"\">Sección a</label>
					<input 
						type=\"text\"
						id=\"aisle{$specific_id}\"
						class=\"form-control\"
						onkeyup=\"\"
						placeholder=\"Letra\"
					>
				</div>

				<div class=\"col-3\">
					<label for=\"\"># a</label>
					<input 
						type=\"number\"
						id=\"location_number{$specific_id}\"
						class=\"form-control\"
						onkeyup=\"\"
						placeholder=\"#\"
					>
				</div>
			
				<div class=\"col-6\">
					<center><label for=\"\">fila / pasillo</label></center>
					<div class=\"row\">
						<div class=\"col-6\">
							<input 
								type=\"number\"
								id=\"aisle_from{$specific_id}\"
								class=\"form-control\"
								onkeyup=\"\"
								placeholder=\"del\"
							>
						</div>
						<div class=\"col-6\">
							<input 
								type=\"\"
								id=\"aisle_until{$specific_id}\"
								class=\"form-control\"
								onkeyup=\"\"
								placeholder=\"al\"
							>
						</div>
					</div>
					<br/>
				</div>
				<br/>
			<div>";

			if( $save_type == 'source' ){
				$resp .= "<button 
					type=\"button\" 
					class=\"btn btn-warning form-control\"
					onclick=\"make_new_location( '{$save_type}' );\"
				>
					Guardar Ubicación
				</button>";
			}

			$resp .= "</div>
			</div>";

			if( $save_type == 'seeker' ){
				$resp .= "<button 
				type=\"button\" 
				class=\"btn btn-success form-control\"
				onclick=\"saveNewLocation();\"
			>
				Guardar Ubicación
			</button>";
			}
			$resp .= "</div>";
			echo $resp;
			return $resp;

		}*/
	}
?>
