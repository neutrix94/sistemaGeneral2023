<?php
//coinsulta el id del almacen principal
	$sql = "SELECT 
				id_almacen AS warehouse_id 
			FROM ec_almacen 
			WHERE id_sucursal = {$user_sucursal} 
			AND es_almacen = 1";
	$stm = mysql_query( $sql ) or die( "Error al consultar el almacen principal de la sucursal " . mysql_error() );
	$row = mysql_fetch_assoc( $stm );
	$principal_warehouse_id = $row['warehouse_id'];
//consultamos los datos a nivel producto
	$sql="SELECT 
			/*0*/te.id_temporal_exhibicion,
			/*1*/p.orden_lista,
			/*2*/p.nombre,
			/**/ap.inventario,
			/*3*/(te.cantidad-te.piezas_recibidas)-te.piezas_agotadas,
			/*4*/0,
			/*5*/0,
			/*6*/p.id_productos,
			( SELECT IF( et.id_exclusion_transferencia IS NULL, '', 'icon-bookmark text-danger' ) FROM ec_exclusiones_transferencia et WHERE et.id_producto = p.id_productos )
		FROM ec_temporal_exhibicion te
		LEFT JOIN ec_productos p ON te.id_producto=p.id_productos
		LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
		AND sp.id_sucursal IN($user_sucursal)
		LEFT JOIN ec_almacen_producto ap
		ON ap.id_almacen = {$principal_warehouse_id}
		AND ap.id_producto = p.id_productos
		WHERE te.id_sucursal = {$user_sucursal}
		AND (te.cantidad-te.piezas_recibidas)-te.piezas_agotadas>0 
		AND te.es_valido=1
		ORDER BY p.orden_lista ASC";
	$eje=mysql_query($sql)or die("Error al consultar productos temporales en exhibición!!!\n\n".$sql."\n\n".mysql_error());
	$c=0;//declaramos contador en cero
	$counter = 0;
	while($r=mysql_fetch_row($eje)){
		$c++;//incrementamos el contador
		echo build_row( $r, $c, 'p' );
	//consulta el nivel proveedor producto
		$sql = "SELECT
					epp.id_temporal_exhibicion_proveedor_producto AS pp_exh_id,
					epp.id_producto AS product_id,
					CONCAT( pp.clave_proveedor , ' ( ', pp.id_proveedor_producto, ' )' ),
					ipp.inventario,
					epp.cantidad,
					0,
					epp.id_proveedor_producto AS product_provider_id
				FROM ec_temporal_exhibicion_proveedor_producto epp
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = epp.id_proveedor_producto
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_proveedor_producto = pp.id_proveedor_producto
				AND ipp.id_almacen IN ( {$principal_warehouse_id} )
				WHERE epp.id_temporal_exhibicion = {$r[0]}";
		$pp_stm = mysql_query( $sql ) or die( "Error al consultar el detalle de los proveedores producto : {$sql} " . mysql_error()  );
		while ( $pp_row = mysql_fetch_row( $pp_stm ) ) {
			$counter ++;
			$pp_row[3] = str_replace('.0000', '', $pp_row[3] );
			$pp_row[4] = str_replace('.0000', '', $pp_row[4] );
			echo build_row( $pp_row, $c, 'p_p', $counter );
		}
	}
	//echo '</table>';
//creamos variable oculta que contiene total de filtas
	echo '<input type="hidden" value="'.$c.'" id="total_filas">';
	function build_row( $row, $c, $type, $secondary_counter = null ){
		$color = "";
		if($c%2==0){
			$color='#E6E8AB';
		}else{
			$color='#BAD8E6';
		}
		$onclick = "";
		if( $type == 'p_p' ){
			$onclick = "onclick=\"editaCelda(5,{$secondary_counter});\"";
			//$onclick_2 = "onclick=\"editaCelda(8,{$secondary_counter});\"";
		}
		$resp = "<tr id=\"{$type}_fila_{$c}\" style=\"background : {$color};\" onclick=\"resalta_fila( {$c} );\" tabindex=\"{$c}\">
					<td style=\"display:none;\" id=\"{$type}_1_{$c}\" value=\"{$row[6]}\">{$row[0]}</td>
					<td style=\"\" id=\"\" class=\"{$row[8]} text-center\"></td>
					<td id=\"{$type}_2_{$c}\" width=\"10%\" style=\"padding:10px;\" align=\"right\">{$row[1]}</td>
					<td>{$row[2]}</td>
					<td id=\"{$type}_3_{$c}\" align=\"right\">{$row[3]}</td>
					<td id=\"{$type}_4_{$c}\" align=\"right\" >{$row[4]}</td>
					<td id=\"{$type}_5_{$c}\" align=\"right\" {$onclick}>{$row[5]}</td>
					<td style=\"display:none;\" id=\"{$type}_6_{$c}\" >{$row[6]}</td>
					<td style=\"display:none;\" id=\"{$type}_7_{$c}\" >{$row[3]}</td>
					<td style=\"\" id=\"{$type}_8_{$c}\" {$onclick_2}>0</td>";
//botones
		if( $type == 'p' ){
			$resp .= "<td>
						<button class=\"btn btn-success\" onclick=\"save_row_changes( '{$type}', {$secondary_counter} );\">
							<i class=\"icon-ok-circle\"></i>
						</button>
					</td>";
			$resp .= "<td class=\"text-center\">
						<button class=\"btn btn-danger\">
							<i class=\"icon-cancel-circled\"></i>
						</button>
					</td>";
			$resp .= "<td class=\"text-center\">
						<button class=\"btn btn-info\">
							<i class=\"icon-sticky-note-o\"></i>
						</button>
					</td>";
		}else{

			$resp .= "<td colspan=\"3\"></td>";
		}
		$resp .= '</tr>';
		return $resp;
	}
?>


