<?php
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	$condition = "";
	if( isset($_GET['fl_type']) || isset($_POST['fl_type']) ){
		$action = ( isset($_GET['fl_type']) ? $_GET['fl_type'] : $_POST['fl_type'] );
		switch ( $action ) {
			case 'priorityCount' :
				$condition = " ( p.id_categoria = 40 AND p.id_subcategoria = 151 ) OR p.codigo_barras_4 = 'Verificar'";
				/*$sql = "SELECT 
							id_almacen AS warehouse_id 
						FROM ec_almacen 
						WHERE id_sucursal = {$user_sucursal} 
						AND es_almacen = 1";
				$stm = $link->query( $sql ) or die( "Error al consultar el almacen principal : {$link->error}" );
				$row = $stm->fetch_assoc();*/
				$warehouse_id = $_GET['warehouse_id'];
				$sql = "SELECT
					p.id_productos AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					CONCAT( p.nombre,
						IF( p.codigo_barras_4 = 'Verificar', '<b class=\"icon-check-2\"></b>', '' )
					)AS product_name,
					p.orden_lista AS list_order,
					IF( pp.id_proveedor_producto IS NULL, -1, pp.id_proveedor_producto ) AS product_provider_id,
					IF( p.codigo_barras_4 = 'Verificar', 'icon-star', '' ),
					ap.inventario AS inventory
				FROM ec_productos p
				LEFT JOIN ec_almacen_producto ap
				ON ap.id_producto = p.id_productos
				AND id_almacen IN( {$warehouse_id} )
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_producto = p.id_productos
				WHERE ( {$condition} )
				AND pp.id_proveedor_producto IS NOT NULL
				GROUP BY pp.id_producto";
			break;
			case 'withoutPriority' :
				$condition = "p.id_categoria = 40 AND p.id_subcategoria = 152";
				$sql = "SELECT
					p.id_productos AS product_id,
					CONCAT( p.nombre,
						IF( p.codigo_barras_4 = 'Verificar', '<b class=\"icon-check-2\"></b>', '' )
					)AS product_name,
					p.orden_lista AS list_order,
					IF( pp.id_proveedor_producto IS NULL, -1, pp.id_proveedor_producto ) AS product_provider_id,
					IF( p.codigo_barras_4 = 'Verificar', 'icon-star', '' )
				FROM ec_productos p
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_producto = p.id_productos
				WHERE ( {$condition} )
				AND pp.id_proveedor_producto IS NOT NULL
				GROUP BY pp.id_producto";
				//die( $sql );
			break;
		}
	}
	if( $sql == '' || $sql == null || ! isset( $sql ) ){
		die( "No se formó la consulta!" );
	}
	$stm = $link->query( $sql ) or die( "Error al consultar consumibles : {$link->error}" );
if( $action == 'withoutPriority' ){
?>
<h4>Pide los siguientes consumibles si los necesitas, si no da click en el check "No"</h4>
<table class="table table-bordered table-striped">
	<thead class="bg-danger" style="position : sticky; top : 0;">
		<tr>
			<th class="text-center col-7">Producto</th>
			<th class="text-center col-4">Pedir</th>
			<th class="text-center col-1">No</th>
		</tr>
	</thead>
	<tbody id="consumablesList">
<?php
	$counter = 0;
	while ( $row = $stm->fetch_assoc() ) {
		echo "<tr value=\"{$row['product_provider_id']}\">
			<td>{$row['product_name']}</td>
			<td>
				<input type=\"number\" class=\"form-control\" id=\"consumables_0_{$counter}\">
			</td>
			<td class=\"text-center\">
				<input type=\"checkbox\" value=\"{$row['product_id']}\" id=\"consumables_1_{$counter}\">
			</td>
		</tr>";
		$counter ++;
	}
}else{
?>
	<script type="text/javascript">
		function calculate_inventory_difference( counter ){
			var virtual_inventory = parseInt( $( '#consumables_0_' + counter ).val() );
			var physical_inventory = parseInt( $( '#consumables_1_' + counter ).val() );
			var difference = parseInt( virtual_inventory - physical_inventory );
			$( '#consumables_2_' + counter ).val( difference );
		}
	</script>

	<table class="table table-bordered table-striped">
	<thead class="bg-danger" style="position : sticky; top : 0;">
		<tr>
			<th class="text-center col-6">Producto</th>
			<th class="text-center col-2">Inventario Virtual</th>
			<th class="text-center col-2">Inventario Fisico</th>
			<th class="text-center col-2">Diferencia</th>
		</tr>
	</thead>
	<tbody id="consumablesList">
<?php
	$counter = 0;
	while ( $row = $stm->fetch_assoc() ) {
		echo "<tr value=\"{$row['product_provider_id']}\">
			<td>{$row['product_name']}</td>
			<td>
				<input 
					type=\"number\" 
					value=\"{$row['inventory']}\" 
					class=\"form-control text-end\" 
					id=\"consumables_0_{$counter}\"
					disabled
				>
			</td>
			<td class=\"text-center\" id=\"consumables_3_{$counter}\" value=\"{$row['product_id']}\">
				<input type=\"number\" 
					class=\"form-control text-end\" id=\"consumables_1_{$counter}\"
					onkeyup=\"calculate_inventory_difference( {$counter} );\"
				>
			</td>
			<td id=\"consumables_4_{$counter}\" value=\"{$row['product_provider_id']}\">
				<input type=\"number\" 
					class=\"form-control text-end\" 
					id=\"consumables_2_{$counter}\"
					disabled
				>
			</td>
		</tr>";
		$counter ++;
	}
}
?>
	</tbody>
</table>