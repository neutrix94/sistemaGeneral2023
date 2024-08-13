<?php
//echo 'here';
	include( '../../../../../config.inc.php' );
	include( '../../../../../conectMin.php' );
	include( '../../../../../conexionMysqli.php' );
	$action = '';
	if( isset( $_POST['fl'] ) ){
		$action = $_POST['fl'];
	}else{
		$action = $_GET['fl'];
	}
	switch ( $action ) {
		case 'getProviderInvoices' :
	//obtiene los productos pendientes por proveedor
			$provider_id = $_POST['provider'];
			echo getProviderInvoices( $link, $provider_id );
		break;
		
		case 'saveRemission' :
			echo saveRemission( $_POST['provider_id'], $_POST['remision_invoice'], 
				$_POST['remission_amount'], $_POST['remission_pieces'],
				$_POST['remission_date'], $user_id, $link );
		break;

		case 'getRemission' :
			$invoice_id = $_POST['invoice'];
		break;

		case 'getRemissionForm' :
			echo getRemissionForm( $_POST['provider'], $link );
		break;

		case 'validateNoRepeatRemission':
			$invoice_reference = $_POST['invoice'];
			echo validateNoRepeatRemission( $invoice_reference, $link );
		break;

		case 'getReceptionDetail' : 
			$reception_id = $_POST['id'];
			$provider_id = $_POST['provider'];
			echo getReceptionDetail( $reception_id, $provider_id, $link );
		break;

		case 'getProductProviders' :
			$product_id = $_POST[ 'p_k' ];
			$provider_id = $_POST[ 'pp' ];
			$counter = $_POST['c'];
			echo getProductProviders( $product_id, null, $provider_id, $counter, $link );
		break;

		case 'finishReception' :
			echo finishReception( $_POST['id'], $link );
		break;

		case 'checkUserPassword':
			echo checkUserPassword( $_GET['pass'], $user_id, $link );
		break;

		case 'deleteOrderDetail': 
			echo deleteOrderDetail( $_POST['id'], $link );
		break;

		case 'seekProductByName' :
			echo seekProductByName( $_GET['txt'], $_GET['product_id'], $link );
		break;	

		case 'setNewProduct':
			echo setNewProduct( $_GET['tmp_product_id'], $_GET['product_id'], $_GET['reception_detail_id'], $link );
		break;

		case 'getProductProviderData':
			echo getProductProviderData( $_GET['product_provider_id'], $link );	
		break;
	/*implementacion Oscar 2023*/
		case 'updateRecepctionDetail' : 
			$table_name = ( isset( $_GET['table_name'] ) ? $_GET['table_name'] : $_POST['table_name'] );
			$field_name = ( isset( $_GET['field_name'] ) ? $_GET['field_name'] : $_POST['field_name'] );
			$primary_key = ( isset( $_GET['primary_key'] ) ? $_GET['primary_key'] : $_POST['primary_key'] );
			$primary_key_name = ( isset( $_GET['primary_key_name'] ) ? $_GET['primary_key_name'] : $_POST['primary_key_name'] );
			$new_value = ( isset( $_GET['new_value'] ) ? $_GET['new_value'] : $_POST['new_value'] );
			echo updateRecepctionDetail( $table_name, $field_name, $primary_key_name, $primary_key, $new_value, $link );
		break;

		case 'getPendingOrdersDetail' : 
			$provider_id = ( isset( $_GET['provider_id'] ) ? $_GET['provider_id'] : $_POST['provider_id'] );
			$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
			echo getPendingOrdersDetail( $provider_id, $product_id, $link );
		break;
	/**/

		default:
			die( "Permission Denied!" );
		break;
	}	

	function getPendingOrdersDetail( $provider_id, $product_id, $link ){
		$resp = "";
		//die( 'here' );
	//año actual
		$sql = "SELECT DATE_FORMAT( NOW(), '%Y' ) AS current_year";
		$stm = $link->query( $sql ) or die( "Error al consultar año actual : {$link->error}" );
		$row = $stm->fetch_assoc();
		$current_year = $row['current_year'];
	//ordenes de compra pendientes
		$sql = "SELECT 
					oc.folio,
					ocd.cantidad As quantity,
					ocd.cantidad_surtido AS received,
					( ocd.cantidad - ocd.cantidad_surtido ) AS pending
				FROM ec_oc_detalle ocd
				LEFT JOIN ec_ordenes_compra oc
				ON ocd.id_orden_compra = oc.id_orden_compra
				WHERE ocd.id_producto = {$product_id}
				AND oc.fecha LIKE '%{$current_year}%'
				AND oc.id_proveedor = {$provider_id}";
//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consular los detalles de ordenes de compra : {$link->error}" );
		$resp = "<table class=\"table\">
				<thead>
					<tr>
						<th>Folio</th>
						<th>Pedido</th>
						<th>Recibido</th>
						<th>Pendiente</th>
					</tr>
				</thead>
		";
		if( $stm->num_rows > 0 ){
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
							<td>{$row['folio']}</td>
							<td>{$row['quantity']}</td>
							<td>{$row['received']}</td>
							<td>{$row['pending']}</td>
						</tr>";
			}
		}else{
			$resp .= "<tr>
				<td colspan=\"4\" class=\"text-danger\">Sin pedidos</td>
			</tr>";
		}
		$resp .= "</table>
		<div class=\"row\">
			<div class=\"col-4\"></div>
			<div class=\"col-4\">
				<br><br>
				<button
					class=\"btn btn-success form-control\"
					onclick=\"close_emergent();\"
				>
					<i class=\"icon-ok-circle\">Aceptar</i>
				</button>
			</div>
		</div>";
		return $resp;
	}

	function updateRecepctionDetail( $table_name, $field_name, $primary_key_name, $primary_key, $new_value, $link ){
		$sql = "UPDATE {$table_name} 
					SET {$field_name} = '{$new_value}' 
				WHERE {$primary_key_name} = '{$primary_key}'";
		$stm = $link->query( $sql ) or die( "error|Error al actualizar registro : {$sql} {$link->error}" );
		return "ok";
	}	

	function getProductProviderData( $product_provider_id, $link ){
		$sql = "SELECT 
					presentacion_caja AS pieces_per_box,
					precio_pieza AS piece_price
				FROM ec_proveedor_producto
				WHERE id_proveedor_producto = {$product_provider_id}";
		$stm = $link->query( $sql ) or die("Error al consultar datos de proveedor_producto : {$link->error}"  );
		$row = $stm->fetch_assoc();
		return "ok|{$row['pieces_per_box']}|{$row['piece_price']}";
	}

	function setNewProduct( $tmp_product_id, $product_id, $reception_detail_id, $link ){
		$sql = "UPDATE ec_recepcion_bodega_detalle SET 
						id_producto_nuevo = NULL, 
						id_producto = {$product_id}
				WHERE id_recepcion_bodega = {$reception_detail_id}
				AND id_producto_nuevo = {$tmp_product_id}";
		
		$stm = $link->query( $sql ) or die("Error al actualizar el id de producto en el detalle de recepción : {$link->error} {$sql}"  );
		//return 'ok'.$sql;
		return 'ok';
	}
	function seekProductByName( $txt, $product_id, $link ){
		$resp = "";
		$sql = "SELECT
					id_productos AS product_id,
					nombre AS name
				FROM ec_productos
				WHERE 1 AND (";
		$array_txt = explode(' ', $txt );
		foreach ($array_txt as $key => $word) {
			$sql .= ( $key > 0 ? " AND" : "" );
			$sql .= " nombre LIKE '%{$word}%'";
		}
		$sql .= ")";
		$stm = $link->query( $sql ) or die("Error al consultar coincidencias de productos por nombre : {$link->error}" );
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= "<div class=\"group_card\" onclick=\"setNewProduct( {$product_id}, {$row['product_id']} );\">
						{$row['name']}
					</div>";
		}
		return $resp;
	}	

	function deleteOrderDetail( $order_detail_id, $link ){
		$resp = "ok|Registro eliminado exitosamente";
		$sql="DELETE FROM ec_oc_detalle WHERE id_oc_detalle = {$order_detail_id}";
		$stm = $link->query( $sql ) or die( "Error al eliminar el detalle de oren de compra : {$link->error}" );
		return $resp;
	}
	
	function getProviderInvoices( $link, $provider_id ){
		$resp = "";
		$sql="SELECT
			ax1.id_oc_detalle,
			ax1.id_producto,
			ax1.nombre,
			ax1.cantidad,
			ax1.recibido,
			0,
			0,
			pp.presentacion_caja,
			pp.precio_pieza,
			ax1.ubicacion_almacen,
			ax1.id_proveedor_producto,
			pp.id_proveedor,
			1, /*implementacion para que no se pinte de rojo*/ 
			'',
			999999 AS is_in_order
				FROM(
					SELECT 
						ocd.id_oc_detalle,
	 				 	ocd.id_producto,
					 	p.nombre,
					 	p.ubicacion_almacen,
					 	SUM( ocd.cantidad ) AS cantidad,
					 	SUM( ocd.cantidad_surtido ) AS recibido,
			 			ocd.id_proveedor_producto
			 		FROM ec_productos p
					LEFT JOIN ec_oc_detalle ocd ON p.id_productos=ocd.id_producto
				 	LEFT JOIN ec_ordenes_compra oc ON ocd.id_orden_compra=oc.id_orden_compra 
				 	WHERE oc.id_proveedor = '{$provider_id}' 
				 	AND oc.observaciones = ''
					GROUP BY ocd.id_producto
				 	ORDER BY ocd.id_oc_detalle ASC
				)ax1 
			LEFT JOIN ec_proveedor_producto pp ON ax1.id_producto=pp.id_producto
			LEFT JOIN ec_ordenes_compra oc2 ON pp.id_proveedor=oc2.id_proveedor 
			AND oc2.id_proveedor = '{$provider_id}'
			WHERE ax1.cantidad > ax1.recibido
			AND pp.id_proveedor_producto = ax1.id_proveedor_producto
			GROUP BY ax1.id_producto
			ORDER BY ax1.id_oc_detalle ASC";
			//return $provider_id;
		$exc = $link->query( $sql )or die("Error al consultar info del detalle de la órden de compra!!!\n\n" 
				. $sql ."\n\n". $link->error );
		$counter=0;
		while( $row = $exc->fetch_row() ){
			$counter++;//incrementamos el contador
			$resp .= build_row( $row, $counter, $provider_id, 'invoice', $link );
		}
		$resp .= '<input type="hidden" id="filas_totales" value="' . $counter . '">';
			//echo $resp;
		return $resp;
	}
	function build_row( $r, $c, $provider_id, $type, $link ){
		$resp = "";
		$background = "";
		$check_disabled = "";
		if( $r[12] <= 0 ){
			$background = 'rgba( 225, 0, 0, .5)';
		}
		if( $r[11] == '' ){
			$background = 'yellow';
		}
		if( $r[13] == 1 ){
			$background = 'green; color : white !important';
			$check_disabled = "disabled";
		}
		if( $r[20] == '' || $r[20] == null ){
			$r[20] = 0;
		}
		/*if( $c%2 == 0 ){
				$color="#E6E8AB";
			}else{
				$color="#BAD8E6";
			}*/
		$resp .= '<tr style="background:' . $background . ';" tabindex="'.$c.'" id="fila_'.$c.'">';
		//total en piezas
			$resp .= "<td id=\"-5_{$c}\" class=\"text-end\" field_name=\"total_en_piezas_remision\" 
				table_name=\"ec_recepcion_bodega_detalle\" primary_key_name=\"id_recepcion_bodega_detalle\" 
				primary_key=\"{$r[0]}\" onclick=\"editaCelda(-5,{$c});\">{$r[17]}</td>";
		//total cajas
			$resp .= "<td id=\"-4_{$c}\" class=\"text-end\" field_name=\"total_cajas_remision\" 
				table_name=\"ec_recepcion_bodega_detalle\" primary_key_name=\"id_recepcion_bodega_detalle\" 
				primary_key=\"{$r[0]}\" onclick=\"editaCelda(-4,{$c});\">{$r[15]}</td>";
		//total piezas sueltas
			$resp .= "<td id=\"-3_{$c}\" class=\"text-end\" field_name=\"total_piezas_sueltas_remision\" 
				table_name=\"ec_recepcion_bodega_detalle\" primary_key_name=\"id_recepcion_bodega_detalle\" 
				primary_key=\"{$r[0]}\" onclick=\"editaCelda(-3,{$c});\">{$r[16]}</td>";//total piezas

			$resp .= "<td id=\"-2_{$c}\" style=\"display:none;\">{$r[13]}</td>";
		//id del detalle de oc
			$resp .= "<td id=\"0_{$c}\" style=\"display:none;\">{$r[0]}</td>";
		//id del producto
			$resp .= "<td id=\"1_{$c}\" style=\"display:none;\">{$r[1]}</td>";
		//nombre del producto
			$resp .= '<td id="2_'.$c.'" class="product_description" style="padding:10px;" width="20%">';
			if( $r[14] == 999999 ){
				$resp .= $r[2];
			}else{
				$resp .= '<input type="text" class="form-control" onkeyup="seek_new_product( this, ' . $c . ' );" value="' . $r[2] . '">';
			}
			$resp .= '</td>';
		//id de proveedor_producto
			$resp .= '<td id="12_'.$c.'" width="7%">';
			if( $r[11] == null || $r[11] == '' ){
				$r[11] = $provider_id;
			}
			if( $type == null ){
				$resp .= getProductProviders( $r[1], $r[10], $r[11], $c, $link, $r[0], $check_disabled );
			}else{
				$resp .= getProductProviders( $r[1], $r[10], $r[11], $c, $link, null, $check_disabled );
			}
			$resp .= '</td>';
		//pedidos pendientes
			$resp .= '<td id="3_'.$c.'" align="center" title="Se pidieron '.$r[3].' piezas, se han recibido '.$r[4].' piezas">'.$r[20];//($r[3]-$r[4])
				$resp .= "<button
					class=\"btn btn-light\"
						onclick=\"getPendingOrdersDetail( {$r[1]} );\"
					>
					<i class=\"icon-eye\"></i>
					</button>";
			$resp .= '</td>';//title="Se han recibido '.$r[4].' piezas, faltan '.$r[3]-$r[4].' piezas por recibir"
		//piezas por caja
			$resp .= '<td id="4_'.$c.'" align="center" >'.$r[7].'</td>';//onclick="editaCelda(4,'.$c.');"
		
		//total piezas validadas
			$resp .= "<td id=\"4_1_{$c}\" align=\"center\" onclick=\"editaCeldaConPassword( '4_1',{$c}, false );\">{$r[21]}</td>";//title="Se han recibido '.$r[4].' piezas, faltan '.$r[3]-$r[4].' piezas por recibir"		
		//total piezas validadas
			$resp .= "<td id=\"4_2_{$c}\" align=\"center\" onclick=\"editaCeldaConPassword( '4_2',{$c}, false );\">{$r[22]}</td>";//title="Se han recibido '.$r[4].' piezas, faltan '.$r[3]-$r[4].' piezas por recibir"		
		//total piezas validadas
			$resp .= "<td id=\"4_3_{$c}\" align=\"center\">{$r[18]}</td>";//title="Se han recibido '.$r[4].' piezas, faltan '.$r[3]-$r[4].' piezas por recibir"		
		

		//validando cajas
			$resp .= '<td id="5_'.$c.'" align="center" onclick="editaCelda(5,'.$c.');">'.$r[5].'</td>';
		//validando piezas
			$resp .= '<td id="6_'.$c.'" align="center" onclick="editaCelda(6,'.$c.');">'.$r[6].'</td>';
		//total validando
			$resp .= '<td id="6_6_'.$c.'" align="center" onclick="editaCelda(6,'.$c.');">'.$r[19].'</td>';
		//precio pieza
			$resp .= '<td id="7_'.$c.'" align="center" onclick="editaCelda(7,'.$c.')">'.$r[8].'</td>';
		//porcentaje de descuento
			$resp .= '<td id="11_'.$c.'" align="center" onclick="editaCelda(11,'.$c.')">0</td>';
		//total piezas
			$resp .= '<td id="9_'.$c.'" align="center">' . ( $r[18] + $r[6] + ( $r[5] * $r[7] ) )  . '</td>';
		//monto
			$resp .= '<td id="8_'.$c.'" align="center" onclick="editaCelda(8,'.$c.')">' . ( $r[6] + ( $r[5] * $r[7] ) ) * ( $r[8] ) . '</td>';
		//	$r[10].'</td>';
		//quitar/cancelar
			$resp .= '<td class="delete_row_container" align="center">';//onclick="editaCelda(-1,'.$c.')"
			$resp .= '<input type="checkbox" id="10_'.$c.'" class="check">';
			//$resp .= '<img src="../../../../img/especiales/cierra.png" width="40" onclick="quitar_fila('.$c.');">';
				$resp .= '<i class="icon-cancel-alt-filled btn_del" onclick="quitar_fila('.$c.');"></i>';
			$resp .= '</td>';
				
			$resp .= '</td>';
		//	echo'<input type="checkbox" id="10_'.$c.'" class="check"><img src="../../../../img/especiales/cierra.png" width="40" onclick="quitar_fila('.$c.');"></td>';
		$resp .= '</tr>';
		return $resp;
	}
	
	function getProductProviders( $product_id, $product_provider_id, $provider_id, $counter, $link, $reception_detail_id = null, $check_disabled = null ){
	//verifica si el registro tiene pendiente un posible proveedor prodsucto por validar
		$color = "";
		if( $reception_detail_id != null ){
			$sql = "SELECT 
				id_recepcion_bodega_detalle 
			FROM ec_recepcion_bodega_detalle
			WHERE id_recepcion_bodega_detalle = {$reception_detail_id}
			AND id_proveedor_producto IS NULL
			AND omitir_p_p = 0";
			$stm = $link->query( $sql );
			if( $stm->num_rows > 0 ){
				$color = "style=\"color : red !important;\"";
			}
		}
		$resp = '<select id="13_'.$counter.'" class="form-control" onchange="changeProductProvider( this,'
			. $product_id . ', ' . $counter . ');" ' . $color . ' ' . $check_disabled .'>';
			
			$resp .= '<option value="0">Seleccionar</option>';

		$sql = "SELECT
					pp.id_proveedor_producto,
					CONCAT(pp.clave_proveedor, ' ( ', pp.presentacion_caja, ' piezas por caja )')
				FROM ec_proveedor_producto pp
				WHERE pp.id_producto = '{$product_id}'
				AND pp.id_proveedor = '{$provider_id}'";
		$exc = $link->query( $sql ) or die( "Error al consultar proveedores para este producto : " . $link->error );
		while ( $row = $exc->fetch_row() ) {
			$resp .= "<option value=\"{$row[0]}\"" . ( $product_provider_id == $row[0] ? ' selected' : '' ) . ">{$row[1]}</option>";
		}
		$resp .= '<option value="-1">Administrar proveedores</option>';
		$resp .= '</select>';
		return $resp;
	}

	function getRemissionForm( $provider_id, $link ){
	//include('../../conectMin.php');
		$resp = "";
	//armamos la lista de proveedores
		$exc = $link->query("SELECT id_proveedor,nombre_comercial FROM ec_proveedor WHERE id_proveedor = '{$provider_id}'") or die("Error al consultar los datos de proveedores!!!".mysql_error());
		$providers='<select id="remission_provider_id" class="form-control">';//<option value="-1">--SELECCIONAR--</option>';
		while( $r = $exc->fetch_row() ){
			$providers.='<option value="'.$r[0].'">'.$r[1].'</option>';
		}
		$providers.='</select>';
	//mandamos respuesta
		/*echo '<button style="padding:15px;position:absolute;top:12%;right:19%;color:white;background:red;"';
		echo ' onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">X</button>';*/
		$resp .= '<table class="table">';
			$resp .= '<tr><th colspan="2" font-size="30px">Alta de Remision<br><br></th></tr>';
		//seleccion de proveedor
			$resp .= '<tr>';
				$resp .= '<td>Proveedor</td>';
				$resp .= '<td>'.$providers.'</td>';
			$resp .= '</tr>';
		//folio del proveedor
			$resp .= '<tr>';
				$resp .= '<td>Igrese el folio de proveedor</td>';
				$resp .= '<td><input 
								type="text" 
								id="remission_invoice" 
								class="form-control"
								onblur="validateNoRepeatRemission();"
							></td>';
			$resp .= '</tr>';
		//monto
			$resp .= '<tr>';
				$resp .= '<td>Igrese el monto de la remision</td>';
				$resp .= '<td><input 
								type="number" 
								id="remision_amount" 
								class="form-control"
							></td>';
			$resp .= '</tr>';
		//piezas
			$resp .= '<tr>';
				$resp .= '<td>Igrese el total de piezas de la remision</td>';
				$resp .= '<td><input 
								type="number" 
								id="remision_pieces" 
								class="form-control"
							></td>';
			$resp .= '</tr>';
		//fecha_remision
			$resp .= '<tr>';
				$resp .= '<td>Igrese la fecha de remision</td>';
				$resp .= '<td><input 
								type="date" 
								id="remission_date" 
								class="form-control"
							></td>';
			$resp .= '</tr>';

			$resp .= '<tr>';
				$resp .= '<td colspan="2" align="center">
							<br>
							<button  
								onclick="save_remission();"  
								class="btn btn-success form-control"
							>Guardar</button></td>';
			$resp .= '</tr>';
			$resp .= '<br>';

		return $resp;
		//}
	}

	function validateNoRepeatRemission( $invoice_reference, $link ){
		$sql = "SELECT id_oc_recepcion FROM ec_oc_recepcion WHERE folio_referencia_proveedor = '{$invoice_reference}'";
		$exc = $link->query( $sql ) or die( "Error al consultar si el folio de la remisión existe : " . $link->error );
		$exists = $exc->num_rows;
		return ( $exists > 0 ? "El folio de recepción ya existe, Verifique y vuelva a intentar!" : "ok" );
	}

	function saveRemission( $provider_id, $remision_invoice, $remission_amount, $remission_pieces, $remission_date, $user_id, $link ){
		$arr=explode("~",$_GET['dats']);
		$link->autocommit( false );
		$sql="INSERT INTO ec_oc_recepcion ( id_proveedor, folio_referencia_proveedor, 
			monto_nota_proveedor, id_usuario, status, piezas_remision, fecha_remision )
			VALUES ( '{$provider_id}','{$remision_invoice}','{$remission_amount}',
				'{$user_id}', 1, '{$remission_pieces}', '{$remission_date}')";
		$exc = $link->query( $sql )or die("Error al insertar la remisión : \n" . $link->error );
//actualiza el estatus de validación de recepcion de mercancía
		/*$sql = "UPDATE ec_recepcion_bodega SET id_status_validacion = 2 WHERE folio_recepcion = '{$remision_invoice}'";
		$exc = $link->query( $sql ) or die( "Error al actualizar el estatus adminsitrativo de la recepción : {$link->error}" );*/
		$link->autocommit( true );
		return 'Remision registrada exitosamente!!!';
	}

	function getReceptionDetail( $reception_id, $provider_id, $link ){

		$sql = "SELECT DATE_FORMAT( NOW(), '%Y' ) AS current_year";
		$stm = $link->query( $sql ) or die( "Error al consultar año actual : {$link->error}" );
		$row = $stm->fetch_assoc();
		$current_year = $row['current_year'];

		$resp = "";
		$sql="SELECT
				/*0*/ax1.id_recepcion_bodega_detalle,
				/*1*/IF(ax1.id_producto IS NULL, ax1.order_new, ax1.id_producto ) AS id_producto,
				/*2*/ax1.nombre,
				/*3*/ax1.cantidad,
				/*4*/ax1.recibido,
				/*5*/ax1.cajas_en_validacion,
				/*6*/ax1.piezas_sueltas_en_validacion,
				/*7*/ax1.piezas_por_caja,
				/*8*/IF(pp.precio_pieza IS NULL, 0, pp.precio_pieza) as precio_pieza,
				/*9*/ax1.ubicacion_almacen,
				/*10*/ax1.id_proveedor_producto,
				/*11*/pp.id_proveedor,
				/*12*/(SELECT 
						SUM( IF( ocd.id_oc_detalle IS NULL , 0, ocd.cantidad ) )
							FROM ec_oc_detalle ocd
							LEFT JOIN ec_ordenes_compra oc 
							ON ocd.id_orden_compra = oc.id_orden_compra
							WHERE ocd.id_producto = ax1.id_producto
							/*AND oc.id_estatus_oc IN( 1, 2, 3 )*/
							AND oc.fecha LIKE '%{$current_year}%'
							AND oc.id_proveedor = pp.id_proveedor
							AND ocd.id_proveedor_producto = pp.id_proveedor_producto
						) AS is_in_order,
				/*13*/ax1.validado, 
		 		/*14*/ax1.order_new,
				/*15*/ax1.total_cajas_remision,
				/*16*/ax1.total_piezas_sueltas_remision,
				/*17*/ax1.total_en_piezas_remision,
				/*18*/ax1.total_piezas_validadas,
				/*19*/ax1.total_piezas_validando,
				/*20*/(SELECT 
						SUM( IF( ocd.id_oc_detalle IS NULL , 0, ( ocd.cantidad - ocd.cantidad_surtido ) ) )
							FROM ec_oc_detalle ocd
							LEFT JOIN ec_ordenes_compra oc 
							ON ocd.id_orden_compra = oc.id_orden_compra
							WHERE ocd.id_producto = ax1.id_producto
							AND oc.fecha LIKE '%{$current_year}%'
							AND oc.id_proveedor = pp.id_proveedor
							AND ocd.id_proveedor_producto = pp.id_proveedor_producto
						GROUP BY ocd.id_proveedor_producto
					) AS pending_to_recive,
				/*21*/ax1.cajas_recibidas,
				/*22*/ax1.piezas_sueltas_recibidas,
				/*23*/ax1.id_proveedor_producto
			FROM(
				SELECT 
					rd.id_recepcion_bodega_detalle,
 				 	rd.id_producto,
				 	IF(p.nombre IS NULL, 
				 		(SELECT 
							nombre 
				 		FROM ec_productos_nuevos_temporal
				 		WHERE id_producto_nuevo = rd.id_producto_nuevo),
				 		p.nombre ) AS nombre,
				 	p.ubicacion_almacen,
				 	rd.cajas_en_validacion,
				 	rd.piezas_sueltas_en_validacion,
				 	( rd.piezas_sueltas_recibidas + ( rd.piezas_por_caja * rd.cajas_recibidas ) ) AS cantidad,
				 	( rd.piezas_sueltas_recibidas + ( rd.piezas_por_caja * rd.cajas_recibidas ) ) AS recibido,
		 			rd.id_proveedor_producto,
		 			rd.piezas_por_caja,
		 			rd.numero_partida,
		 			rd.validado,
		 			IF( rd.id_producto IS NULL, rd.id_producto_nuevo, 999999 ) AS order_new,
		 			rd.total_cajas_remision,
		 			rd.total_piezas_sueltas_remision,
		 			rd.total_en_piezas_remision,
		 			( rd.piezas_por_caja * rd.cajas_recibidas ) + rd.piezas_sueltas_recibidas AS total_piezas_validadas,
		 			( rd.piezas_por_caja * rd.cajas_en_validacion ) + rd.piezas_sueltas_en_validacion AS total_piezas_validando,
		 			rd.cajas_recibidas,
		 			rd.piezas_sueltas_recibidas
		 		FROM ec_recepcion_bodega_detalle rd
				LEFT JOIN ec_productos p 
				ON p.id_productos = rd.id_producto
				/*LEFT JOIN ec_oc_recepcion_detalle*/
				WHERE rd.id_recepcion_bodega = '{$reception_id}'
				GROUP BY rd.id_recepcion_bodega_detalle
			 	ORDER BY rd.numero_partida ASC
			)ax1 
			LEFT JOIN ec_proveedor_producto pp ON ax1.id_producto=pp.id_producto
			AND pp.id_proveedor_producto = ax1.id_proveedor_producto
			WHERE 1 
			GROUP BY ax1.id_recepcion_bodega_detalle
			ORDER BY ax1.order_new, ax1.numero_partida ASC";
//die( $sql );
		$exc = $link->query( $sql )or die("Error al consultar info del detalle de la órden de compra!!!\n\n" 
				. $sql ."\n\n". $link->error );
		$counter=0;
		while( $row = $exc->fetch_row() ){
			if( $row[23] != null && $row[23] != '' ){
				$sql = "SELECT 
							SUM( IF( ocd.id_oc_detalle IS NULL , 0, ( ocd.cantidad - ocd.cantidad_surtido ) ) )
								FROM ec_oc_detalle ocd
								LEFT JOIN ec_ordenes_compra oc 
								ON ocd.id_orden_compra = oc.id_orden_compra
								WHERE ocd.id_producto = {$row[1]}
								AND oc.fecha LIKE '%{$current_year}%'
								AND oc.id_proveedor = {$provider_id}
								AND ocd.id_proveedor_producto = {$row[23]}
							GROUP BY ocd.id_producto";
				$stm_aux = $link->query( $sql ) or die( "Error al consultar lo pendiente de recibir : {$sql} {$link->error}" );
				$row_aux = $stm_aux->fetch_row();
			}
			$row[20] = $row_aux[0];
			$counter++;//incrementa el contador
			$resp .= build_row( $row, $counter, $provider_id, null, $link );
		}
		$resp .= '<input type="hidden" id="filas_totales" value="' . $counter . '">';
		return $resp;
	}

	function finishReception( $id, $link ){
		$sql = "UPDATE ec_recepcion_bodega
					SET serie = IF( id_status_validacion IN( 3 ), serie,  CONCAT( serie, '_', id_recepcion_bodega ) ), 
						id_status_validacion = 3,
					id_recepcion_bodega_status = 3
				WHERE id_recepcion_bodega = '{$id}'";
		//die($sql);
		$link->query( $sql ) or die( "Error al actualizar a terminada la recepcion de bodega : " . $link->error );
		return "ok|La recepción fue finalizada exitosamente!";
	}

	function checkUserPassword( $pass, $user, $link ){
		$sql = "SELECT 
					id_usuario 
				FROM sys_users 
				WHERE id_usuario = '{$user}'
				AND contrasena = md5( '{$pass}' )";
		$stm = $link->query( $sql ) or die( "Error al consultar contraseña de usario : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return "Contraseña incorrecta";
		}else{
			return "ok";
		}
	}


?>