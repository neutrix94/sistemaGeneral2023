<?php
	include( '../../../conexionMysqli.php' );
	$initialInventory = new inventoryMigration2022( $link );

	if ( isset( $_GET['fl'] ) || isset( $_POST['fl'] ) ) {
		$action = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
		switch ( $action ) {
			case 'reloadRows':
				//die( 'here');
				$category = ( isset($_GET['category']) && $_GET['category'] != 0 ? $_GET['category'] : null );
				$subcategory = ( isset($_GET['subcategory']) && $_GET['subcategory'] != 0 ? $_GET['subcategory'] : null );
				$subtype = ( isset($_GET['subtype']) && $_GET['subtype'] != 0 ? $_GET['subtype'] : null );
				$initial_date = ( isset($_GET['initial_date']) && $_GET['initial_date'] != '' ? $_GET['initial_date'] : null );
				$final_date = ( isset($_GET['final_date']) && $_GET['final_date'] != '' ? $_GET['final_date'] : null );
				echo $initialInventory->getProductProvider( $category , $subcategory , $subtype , $initial_date, $final_date );
			break;

			case 'getCombo':
				echo $initialInventory->getCombo( $_GET['type'], $_GET['key'], true );
			break;

			case 'saveData' :
				
				$inventory_charge_id = $_GET['inventory_charge_id'];
				$name = $_GET['name'];
				$product_provider_id = $_GET['product_provider_id'];
				$provider_clue = $_GET['provider_clue'];
				$count = $_GET['count'];
				$sources_with_count = $_GET['sources_with_count'];
				$initial_inventory = $_GET['initial_inventory'];
				$pending_order = $_GET['pending_order'];
				$sources = $_GET['sources'];
				$sources_last_date = $_GET['sources_last_date'];
				$pieces_total = $_GET['pieces_total'];

				echo $initialInventory->saveData( $inventory_charge_id, $name, $product_provider_id, $provider_clue, 
					$count, $sources_with_count, $initial_inventory, $pending_order, $sources, $sources_last_date, 
					$pieces_total );
			break;
			
			default:
				die( 'Permission denied!' );
			break;
		}
		return '';
	}

	class inventoryMigration2022{
		private $link;
		function __construct( $link ){
			$this->link = $link;
		}

		public function saveData( $inventory_charge_id, $name, $product_provider_id, $provider_clue, 
					$count, $sources_with_count, $initial_inventory, $pending_order, $sources, $sources_last_date, 
					$pieces_total ){
			$sql = ( $inventory_charge_id != 0 ? "UPDATE" : "INSERT");
			$sql .= " ec_carga_inventario_proveedor_producto ";
			$sql .= " SET ";
			$sql .= ( $inventory_charge_id == 0 ? "id_carga_inventario= NULL," : "" );
			$sql .= "/*2*/nombre='{$name}',
					/*3*/id_proveedor_producto='{$product_provider_id}',
					/*4*/modelo_proveedor='{$provider_clue}',
					/*5*/conteo='{$count}',
					/*6*/conteo_con_entradas='{$sources_with_count}',
					/*7*/inventario_inicial='{$initial_inventory}',
					/*8*/pedido_pendiente='{$pending_order}',
					/*9*/entradas='{$sources}',
					/*10*/fecha_entradas='{$sources_last_date}',
					/*11*/total_piezas='{$pieces_total}'";
			$sql .= ( $inventory_charge_id != 0 ? " WHERE id_carga_inventario = {$inventory_charge_id} " : "");
		//return $sql;
			$stm = $this->link->query( $sql ) or die( "Error al insertar / consultar ec_carga_inventario_proveedor_producto : {$this->link->error}" );
			return "ok|Guardado exitosamente." . ( $inventory_charge_id == 0 ? "|{$this->link->insert_id}" : "" );
		}

		public function getProductProvider( $category = null, $subcategory = null, $subtype = null, $date_from = null, $date_to = null ){
			$resp = "";
				//die( 'initial_date 1 : ' . $date_from ) ;

			$filter_category .= ( $category != null ? " AND p.id_categoria = {$category}" : "" );
			$filter_subcategory .= ( $subcategory != null ? " AND p.id_subcategoria = {$subcategory}" : "" );
			$filter_subtype .= ( $subtype != null ? " AND p.id_subtipo = {$subtype}" : "" );
			$sources_dates_filter = '';
			$orders_dates_filter = '';
			if( $date_from != null ){
				//die( 'final_date : ' . $date_from );
				//$sources_dates_filter = " AND mdpp.fecha_registro BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59'";
				//$orders_dates_filter = " AND oc.fecha BETWEEN '{$date_from}' AND '{$date_to}'";
				$sources_dates_filter = " AND ( mdpp.fecha_registro BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59')";
				$orders_dates_filter = " AND (oc.fecha BETWEEN '{$date_from}' AND '{$date_to}')";
			
			}
			$sql = "SELECT
						ax1.charge_inventory_id,
						ax1.product_id,
						ax1.product_provider_id,
						ax1.list_order,
						ax1.product_name,
						ax1.provider_clue,
						ax1.count,
						ax1.is_sources_counter,
						ax1.sources,
						ax1.max_date,
						ax1.initial_inventory,
						SUM( IF( ocd.id_oc_detalle IS NOT NULL {$orders_dates_filter}, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS pending_recive
					FROM(
							SELECT
								ax.charge_inventory_id,
								ax.product_id,
								ax.product_provider_id,
								ax.list_order,
								ax.product_name,
								ax.provider_clue,
								ax.count,
								ax.is_sources_counter,
								SUM( IF( mdpp.cantidad IS NOT NULL {$sources_dates_filter}, mdpp.cantidad, 0 ) ) AS sources,
								IF( mdpp.cantidad IS NULl, 'Sin entradas', MAX( mdpp.fecha_registro ) ) AS max_date,
								ax.initial_inventory
							FROM(
								SELECT 
								IF( cipp.id_carga_inventario IS NULL, 0, cipp.id_carga_inventario ) AS charge_inventory_id,
								pp.id_proveedor_producto AS product_provider_id,
								p.id_productos AS product_id,
								p.orden_lista AS list_order,
								p.nombre AS product_name,
								pp.clave_proveedor AS provider_clue,
								IF( cipp.id_carga_inventario IS NULL, '', cipp.conteo ) AS count,
								IF( cipp.id_carga_inventario IS NULL, 0, cipp.conteo_con_entradas ) AS is_sources_counter,
								IF( cipp.id_carga_inventario IS NULL, 0, cipp.inventario_inicial ) AS initial_inventory
							FROM ec_proveedor_producto pp
							LEFT JOIN ec_productos p
							ON pp.id_producto = p.id_productos
							LEFT JOIN ec_carga_inventario_proveedor_producto cipp
							ON cipp.id_proveedor_producto = pp.id_proveedor_producto
							WHERE p.id_productos > 0
							{$filter_category}
							{$filter_subcategory}
							{$filter_subtype}
						)ax
						LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
						ON mdpp.id_proveedor_producto = ax.product_provider_id
						AND mdpp.id_tipo_movimiento IN( 1 ) 
						{$sources_dates_filter}
						GROUP BY ax.product_provider_id
					)ax1
					LEFT JOIN ec_oc_detalle ocd
					ON ocd.id_proveedor_producto = ax1.product_provider_id
					LEFT JOIN ec_ordenes_compra oc ON oc.id_orden_compra = ocd.id_orden_compra
					{$orders_dates_filter}
					GROUP BY ax1.product_provider_id
					ORDER BY ax1.list_order ASC";
			//echo $sql;
			//die( 'final_date : ' . $sql . '<br>' . $sources_dates_filter );

			$stm = $this->link->query( $sql ) or die( "Error al consultar proveedores producto : {$this->link->error} {$sql}" );
			$product_id = "";
			$products_counter = 0;
			$counter = 0;
			while ( $row = $stm->fetch_assoc() ) {
				if( $product_id != $row['product_id'] ){
					$product_id = $row['product_id'];
					$products_counter ++;
				}
				$row['total'] = $row['pending_recive'] + $row['sources'] + $row['initial_inventory'];
				$background_color = ( $products_counter % 2 != 0 ? 'rgba( 0,0,0,.2 )' : 'white' );
				$checked = ( $row['is_sources_counter'] == 1 ? 'checked' : '' );
				$resp .= "<tr id=\"row_{$counter}\" tabindex=\"{$counter}\" value=\"{$row['charge_inventory_id']}\" style=\"background-color : {$background_color};\">
							<td id=\"0_{$counter}\">{$row['list_order']} {$row['product_name']}</td>
							<td id=\"1_{$counter}\">{$row['product_provider_id']}</td>
							<td id=\"2_{$counter}\">{$row['provider_clue']}</td>
							<td>
								<input type=\"text\" id=\"3_{$counter}\" 
									value=\"{$row['count']}\"
									class=\"form-control\" onkeyup=\"change_and_save( {$counter} );\">
							</td>
							<td class=\"text-center\">
								<input type=\"checkbox\" class=\"check\" id=\"4_{$counter}\" {$checked} onclick=\"change_and_save( {$counter} );\">
							</td>
							<td id=\"5_{$counter}\">{$row['initial_inventory']}</td>
							<td id=\"6_{$counter}\">{$row['pending_recive']}</td>
							<td id=\"7_{$counter}\">{$row['sources']}</td>
							<td id=\"8_{$counter}\">{$row['total']}</td>
						</tr>";
				$counter ++;
			}
			return $resp;
		}

		public function getLastYears( $range = 10 ){
			$sql = "SELECT DATE_FORMAT( NOW(), '%Y' ) AS current_year";
			$stm = $this->link->query( $sql ) or die( "Erorr al consultar el año actual : {$link->error}" );
			$row = $stm->fetch_assoc();
			$resp = "<select id=\"initial_year\" class=\"form-control\">";
			for ( $i = 0; $i <= $range ; $i++ ) {
				$resp .= "<option value=\"" . ($row['current_year'] - $i ) . "\">" . ($row['current_year'] - $i ) . "</option>"; 
			}
			$resp .= "</select>";
			return $resp;
		}

		public function getCombo( $type, $depend_value = null, $just_data = false ){
			$resp = "";
			$sql = "";
			$onchange = "";
			switch ( $type ) {
				case 'category':
					$sql = "SELECT id_categoria, nombre FROM ec_categoria";
					$onchange = "onchange=\"change_combo( this, '#subcategory_combo' );\"";
				break;
				case 'subcategory':
					$sql = "SELECT id_subcategoria, nombre FROM ec_subcategoria";
					$sql .= ( $depend_value != null ? " WHERE id_categoria = '{$depend_value}'" : "" );
					$onchange = "onchange=\"change_combo( this, '#subtype_combo' );\"";
				break;
				case 'subtype':
					$sql = "SELECT id_subtipos, nombre FROM  ec_subtipos";
					$sql .= ( $depend_value != null ? " WHERE id_tipo = '{$depend_value}'" : "" );
					$onchange = "";
				break;
				
				default:
					die( 'Permission denied' );
				break;
			}
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos del combo : {$link->error} {$sql}" );
			if( $just_data == false ){		
				$resp .= "<select id=\"{$type}_combo\" class=\"form-control\" {$onchange}>";
			}
			$resp .= "<option value=\"0\" >Ver todo</option>";
			while ( $row = $stm->fetch_row() ) {
				$resp .= "<option value=\"{$row[0]}\">{$row[1]}</option>";
			}
			if( $just_data == false ){
				$resp .= "</select>";
			}
			return $resp;
		}
	}
?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<title>Carga inicial inventario</title>
	
<style type="text/css">
	.header{
		background-color: #83B141;
		color: white;
		padding: 10px;
		width: 100%;
		position: relative;
	}
	.footer{
		background-color: #83B141;
		color: white;
		padding: 10px;
		width: 100%;
		position: absolute;
		bottom: 0;
		z-index:2;

	}
	.table_container{
		width: 99%;
		max-width: 99%;
		height: 400px;
		max-height: 400px;
		overflow: auto;
	}
	.header_sticky{
		position: sticky;
		top : 0;
		background: white;
		z-index: 1;
	}
	.check{
		transform: scale( 3 );
	}
	@media only screen and (max-width: 600px) {
		*{
			font-size: 90%;

		}
		.header{
			font-size: 70%;
		}
		.form-control{
			font-size: 70%;

		}
	.check{
		transform: scale( 2 );
	}

	}
</style>
</head>
<body>
	<div class="global_container" style="position : absolute; width : 100%; height : 100%; top : 0; left : 0; margin : 0;">
		<div class="row header">
			<div class="col-2">
				<label>Familia</label>
			<?php
				echo $initialInventory->getCombo( 'category', null );
			?>
			</div>
			<div class="col-2">
				<label>Tipo</label>
			<?php
				echo $initialInventory->getCombo( 'subcategory', null );
			?>
			</div>
			<div class="col-2">
				<label>Subtipo</label>
			<?php
				echo $initialInventory->getCombo( 'subtype', null );
			?>
			</div>
			<div class="col-2">
				<label>Fecha de : </label>
				<input type="date" id="date_from" class="form-control">
			</div>
			<div class="col-2">
				<label>Fecha a : </label>
				<input type="date" id="date_to" class="form-control">
			</div>
			<div class="col-2">
				<br>
				<button 
					class="btn btn-success form-control"
					onclick="reload_rows()"
				>
					<i class="icon-list-alt">Filtrar</i>
				</button>
			</div>
		</div>
	<!-- contenido -->
		<div class="row">
			<div class="col-2"></div>	
			<div id="save_response" class="col-10">

			</div>
		</div>
		<div class="table_container">
			<table class="table table-bordered">
				<thead class="header_sticky">
					<tr>
						<th>Producto</th>
						<th>Proveedor producto</th>
						<th>Clave Proveedor</th>
						<th>Conteo</th>
						<th>Conteo con entradas</th>
						<th>Inv. Inicial</th>
						<th>Pedido Pendiente</th>
						<th>Entradas</th>
						<th>Total piezas</th>
					</tr>
				</thead>
				<tbody id="inventory_rows">
				<?php
					echo $initialInventory->getProductProvider(null, null, null, null, null );
				?>
				</tbody>
			</table>
		</div>
	<!-- footer -->
	</div>
		<div class="footer">
			<div class="row">
				<div class="col-4"></div>
				<div class="col-4">
					<button class="btn btn-light form-control" onclick="if( confirm( '¿Salir sin guardar?' ) ){ window.location = '../../../index.php?';}">
						<i class="icon-home-outline">Ir al panel</i>
					</button>
				</div>
				<!--div class="col-3">
					<button class="btn btn-light">
						<i class="icon-floppy">Guardar</i>
					</button>
				</div-->
			</div>
		</div>
</body>
</html>

<script type="text/javascript">
	
	function change_and_save( counter ){
		var inventory_charge_id, name, product_provider_id, provider_clue, 
		count, sources_with_count, initial_inventory, pending_order,
		sources, sources_last_date, pieces_total;
		calculate( counter );
		setTimeout( function(){
			var url = "inventoryMigration2022.php?fl=saveData";
			url += "&inventory_charge_id=" + $( '#row_' + counter ).attr( 'value' ).trim();
			url += "&name=" + $( '#0_' + counter ).html().trim();
			url += "&product_provider_id=" + $( '#1_' + counter ).html().trim();
			url += "&provider_clue=" + $( '#2_' + counter ).html().trim();
			url += "&count=" + $( '#3_' + counter ).val().trim();
			url += "&sources_with_count=" + ($( '#4_' + counter ).prop( 'checked' ) ? 1 : 0);
			url += "&initial_inventory=" + $( '#5_' + counter ).html().trim();
			url += "&pending_order=" + $( "#6_" + counter ).html().trim();
			url += "&sources=" + $( "#7_" + counter ).html().trim();
			//url += "sources_last_date=" + $( "#8_" + counter ).html().trim();
			url += "&pieces_total=" + $( "#8_" + counter ).html().trim();
		//alert( url );
			var response = ajaxR( url ).split( '|' );
			if( response[0] != 'ok' ){
				alert( response );
				return false;
			}
			if( $( '#row_' + counter ).attr( 'value' ).trim() == 0 ){
				$( '#row_' + counter ).attr( 'value', response[2] );
			}
			$( '#save_response' ).html( response[1] );
			$( '#save_response' ).css( 'display' , 'block' );
			setTimeout( function(){
					$( '#save_response' ).css( 'display' , 'none' );
				}, 3000
			);
		//$
		}, 100);

	}

	/*function saveData(){
		var data_request = "";
		$( '#inventory_rows tr' ).each( function( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				switch( index2 ){
					case 1:
						data_request += $( this ).val();
					break;
					default:
						data_request += $( this ).html().trim();
					break;
				}
			});
		});
	}*/

	function calculate( counter ){
		var count, is_sources_counter, initial_inventory,
		pending_recive, sources, pieces_total;
		count = $( '#3_' + counter ).val();
		is_sources_counter = ( $( '#4_' + counter ).prop( 'checked' ) ? 1 : 0 );
		pending_recive = $( '#6_' + counter ).html().trim();
		sources = $( '#7_' + counter ).html().trim();
		if( is_sources_counter == 1 ){
			initial_inventory = count - sources;
		}else{
			initial_inventory = count;
		}
		$( '#5_' + counter ).html( initial_inventory );
		pieces_total = parseFloat( initial_inventory ) + parseFloat( pending_recive ) + parseFloat( sources );
//alert( pieces_total );
		pieces_total = ( '' + pieces_total ).split('.00').join('');
		$( '#8_' + counter ).html( pieces_total );
	}

	function change_combo( obj_origin, obj_depend ){
		var url = 'inventoryMigration2022.php?fl=getCombo&type=' + ( obj_depend.split( '_combo' ).join('') ).split('#').join('');

		if( $( obj_origin ).val() != 0 ){
			url += '&key=' + $( obj_origin ).val();
		}
		var response = ajaxR( url );
		//alert( response );
		$( obj_depend ).empty();
		$( obj_depend ).append( response );
		setTimeout( function(){	
				if( obj_depend == '#subcategory_combo' ){
					$( '#subcategory_combo' ).val( 0 );
					$( '#subtype_combo' ).val( 0 );
				}
			}, 100
		);

	}
	function reload_rows(){
		var url = 'inventoryMigration2022.php?fl=reloadRows';
		var category = '',subcategory = '', subtype = '', initial_date = '', final_date = '';
	//filtro familia
		category = $( '#category_combo' ).val();
		url += ( category != 0 ? "&category=" + category : '');
	//filtro subcategoria
		subcategory = $( '#subcategory_combo' ).val();
		url += ( subcategory != 0 ? "&subcategory=" + subcategory : '');
	//filtro de subtipo
		subtype = $( '#subtype_combo' ).val();
		url += ( subtype != 0 ? "&subtype=" + subtype : '');
	//filtro de fecha 
		initial_date = $( '#date_from' ).val();
		if( initial_date == '' ){
			alert( "La fecha inicial es obligatoria" );
			$( '#date_from' ).focus();
			return false;
		}
		url += ( initial_date != '' ? "&initial_date=" + initial_date : '');
	//filtro de fecha 
		final_date = $( '#date_to' ).val();
		if( final_date == '' ){
			alert( "La fecha final es obligatoria" );
			$( '#date_to' ).focus();
			return false;
		}
		url += ( final_date != '' ? "&final_date=" + final_date : '');
//alert( url );
		var response = ajaxR( url );
	//alert( response );
		$( '#inventory_rows' ).empty();
		$( '#inventory_rows' ).append( response );
	}	
//llamadas asincronas
	function ajaxR(url){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}

</script>