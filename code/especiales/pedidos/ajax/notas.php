<?php
	
	if( isset( $_GET['fl'] ) ){
		include( '../../../../config.ini.php' );
		include( '../../../../conectMin.php' );
		$action = $_GET['fl'];
		switch( $action ){
			case 'addProductNote' : 
				echo addProductNote( $_GET['product_id'], $_GET['category'], $_GET['category_value'], $_GET['text'], $user_id, $link );
			break;
			case 'getNotesByProduct' : 
				echo getNotesByProduct( $_GET['product_id'], $_GET['category_id'], $link );
			break; 

			case 'delete_product_note' :
				echo delete_product_note( $_GET['note_id'] );
			break;

			case 'getProyectionDetail' : 
				echo getProyectionDetail( $_GET['product_id'], $_GET['inital_inventory_date'], $_GET['date_from'], $_GET['date_to'], $link );
			break; 

			case 'updateNote' :
				echo updateNote( $_GET['is_new'], $_GET['txt'], $_GET['category_id'], $_GET['category_value_id'], $_GET['note_id'], $user_id, $_GET['product_id'], $link );
			break;

			case 'getProducNotesBefore':
				echo getProducNotesBefore ( $_GET['product_id'], $link );
			break;
			
			case 'copyHistoric':
				echo copyHistoric( $_GET['row_id'], $_GET['product_id'], $link );
			break;
		}
	}else{
	//muestra la vista
		echo getView( $id, $link );
	}

	function copyHistoric( $row_id, $product_id, $link ){
		$sql = "INSERT INTO ec_productos_notas
					SELECT 
						NULL,
						id_categoria_nota,
						id_valor_nota,
						id_producto,
						id_usuario,
						nota
					FROM ec_productos_notas_historico
					WHERE id_producto_nota = {$row_id}";
		//echo $sql;
		$stm = mysql_query( $sql ) or die( "Error al insertar la copia del registro : " . mysql_error() );

		return "Nota copiada exitosamente.";
	}

	function getProducNotesBefore ( $product_id, $link ){
		if( !$link ){
			include( '../../../../conecMin.php' );
		}
		$sql = "SELECT 
					CONCAT( '( ', orden_lista ,' ) ', nombre ) AS name,
					orden_lista AS list_order
				FROM ec_productos WHERE id_productos = {$product_id}";
		$stm = mysql_query( $sql ) or die( "Error al consultar el nombre del producto : " . mysql_error() );
		$row = mysql_fetch_assoc( $stm );
		$resp .= "<!DOCTYPE html>
					<link rel=\"stylesheet\" type=\"text/css\" href=\"../../../../css/bootstrap/css/bootstrap.min.css\"/>
					<link rel=\"stylesheet\" type=\"text/css\" href=\"../../../../css/icons/css/fontello.css\"/>
					<script type=\"text/javascript\" src=\"../../../../js/jquery-1.10.2.min.js\"></script>
					<br></br>
					<div class=\"row\">
						<div class=\"col-3 text-center\">
							<button class=\"btn btn-primary\" onclick=\"getOtherProduct( -1 );\">
								<i class=\"icon-left-big\"></i>
							</button>
						</div>
						<div class=\"col-6 text-center\">
							<h5>Histórico de notas del producto <b>{$row['name']}</b></h5>
						</div>
						<div class=\"col-3 text-center\">
							<button class=\"btn btn-primary\" onclick=\"getOtherProduct( 1 );\">
								<i class=\"icon-right-big\"></i>
							</button>
						</div>
					</div>";
		$resp .= "<table class=\"table table-bordered\" style=\"font-size : 80% !important;\">
					<thead style=\"font-size : 80%; background: rgba( 225, 0, 0, 0.8 );\">
						<tr>
							<th width=\"50px\" class=\"overflow_hidden\">Categoria</th>
							<th width=\"30px\" class=\"overflow_hidden\">Valor</th>
							<th width=\"50px\" class=\"overflow_hidden\">Nota</th>
							<th width=\"30px\" class=\"overflow_hidden\">Usuario</th>
							<th width=\"20px\" class=\"overflow_hidden\">Copiar</th>
						</tr>
					</thead>
					<tbody id=\"product_notes_lists\">";
			$resp .= getNotesByProduct( $product_id, -1, $link, 'is_historic' );
			$resp .= "</tbody>

			</html>";

			$resp .= "<script type=\"text/JavaScript\">
						var historic_id = '';
						function copy_historic_product_note( obj, row_id, product_id ){
							$( obj ).parent().parent().children().each( function ( index ) {
								if( index == 1 ){
									historic_id = $( this ).html().trim();
								}
							});
							$.ajax({
								type : 'get',
								cache : false,
								url : 'notas.php?fl=copyHistoric&row_id=' + historic_id + '&product_id=' + product_id,
								success : function ( dat ){
									alert( dat );
								}
							});
						}

						function getOtherProduct( type ){
							window.opener.getNexAndBeforeProduct( type );
						}

					   /* window.onbeforeunload = preguntarAntesDeSalir;
					    function preguntarAntesDeSalir()
					    {
							window.opener.hide_historic_notes();

					    }*/
					</script>";
		return $resp;
	}

	function updateNote( $is_new, $txt, $category_id, $category_value_id, $note_id, $user_id, $product_id,  $link ){
		if( $is_new == 1 ){
			$sql = "INSERT INTO ec_productos_notas ( id_producto_nota, id_categoria_nota, id_valor_nota, id_producto, id_usuario, nota )
				VALUES( NULL, {$category_id}, {$category_value_id}, {$product_id}, {$user_id}, '{$txt}'  )";
		}else{
			$sql = "UPDATE ec_productos_notas SET 
						nota = '{$txt}', 
						id_categoria_nota = {$category_id},
						id_valor_nota = {$category_value_id}
					WHERE id_producto_nota = {$note_id}";
		}
		//echo $sql;
	
		$stm = mysql_query( $sql ) or die( "Error al actualizar / insertar la nota del producto : {$sql} " . mysql_error() );
		return 'ok|La nota fue actualizada / insertada exitosamente!';
	}
	
	function delete_product_note( $note_id ){
		$sql = "DELETE FROM ec_productos_notas WHERE id_producto_nota = $note_id";
		$stm = mysql_query( $sql ) or die( "Error al eliminar la nota del producto : " . mysql_error() );
		return 'ok';
	}

	function addProductNote( $product_id, $category, $category_value, $text, $user_id, $link ){
		$sql = "INSERT INTO ec_productos_notas ( id_producto_nota, id_categoria_nota, id_valor_nota, id_producto, id_usuario, nota )
				VALUES( NULL, {$category}, {$category_value}, {$product_id}, {$user_id}, '{$text}'  )";
		$stm = mysql_query( $sql ) or die( "Error al insertar la nota del producto : " . mysql_error() );
		return 'ok|' . mysql_insert_id();
	}

	function getNotesByProduct( $product_id, $category_id, $link, $is_historic = null ){
		if( !$link ){
			include( '../../../../conecMin.php' );
		}
		$historic = "";
		if( $is_historic != null ){
			$historic = "_historico";
		}
		//echo 'here';
		$resp = "";
		$condition = "";
		if( $category_id != -1 ){
			$condition .= " AND pn.id_categoria_nota = {$category_id}";
		}
		if( $is_historic != null ){
			$condition = " AND pn.id_producto = {$product_id}";
		}else{
			$condition .= " AND pcn.id_categoria_nota > 0";
		}
		$sql = "SELECT 
					pn.id_producto_nota AS row_id,
					pcn.nombre_categoria_nota AS note_category_name, 
					pcn.id_categoria_nota AS note_category_id, 
					pvn.nombre_valor_nota AS note_value_name,
					pvn.id_valor_nota AS note_value_id,
					CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
					pn.nota AS note,
					pn.id_producto AS product_id
				FROM ec_productos_categorias_notas pcn
				LEFT JOIN ec_productos_notas{$historic} pn
				ON pcn.id_categoria_nota = pn.id_categoria_nota
				AND pn.id_producto = {$product_id}
				LEFT JOIN ec_productos_valores_notas pvn
				ON pn.id_valor_nota = pvn.id_valor_nota
				LEFT JOIN sys_users u 
				ON u.id_usuario = pn.id_usuario
				WHERE 1 {$condition}
				ORDER BY pcn.id_categoria_nota";
		//echo $sql;
		$stm = mysql_query( $sql ) or die( "Error al consultar las notas por producto : {$sql} " . mysql_error() );
		$counter = 0;
		while ( $row = mysql_fetch_assoc( $stm ) ) {//<td>{$row['row_id']}</td>
			$button = "delete_product_note( this, {$row['row_id']}, {$product_id} );";
			$button_text = "X";
			if( $is_historic ){
				$button = "copy_historic_product_note( this, {$row['row_id']}, {$product_id} );";
				$button_text = "Copiar";
			}
			$resp .= "<tr style=\"background-color : white;\">
						<td class=\"overflow_hidden\" id=\"product_note_-2_{$counter}\" style=\"display : none;\">{$product_id}</td>
						<td class=\"overflow_hidden\" id=\"product_note_-1_{$counter}\" style=\"display : none;\">{$row['row_id']}</td>
						<td width=\"50px\" class=\"overflow_hidden\"><select id=\"product_note_0_{$counter}\" onchange=\"updateProductNote( {$counter}, {$row['row_id']});\" class=\"box_shadow\">
							" . getComboNotesCategories( $link, $row['note_category_id'] ) . "</select></td>
						<td width=\"30px\" class=\"overflow_hidden\"><select id=\"product_note_1_{$counter}\" onchange=\"updateProductNote( {$counter}, {$row['row_id']});\" class=\"\">
							" . getValuesByCategory( $link, $row['note_category_id'], $row['note_value_id'] ) . "</select></td>
						<td width=\"50px\" class=\"overflow_hidden\" id=\"product_note_2_{$counter}\" onclick=\"editProductNote( {$counter}, {$row['row_id']} );\">{$row['note']}</td>
						<td width=\"30px\" class=\"overflow_hidden\" id=\"product_note_3_{$counter}\">{$row['user_name']}</td>
						<td width=\"20px\" class=\"overflow_hidden\" id=\"product_note_4_{$counter}\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"{$button}\"
							>
								<i class=\"btn btn-danger\">{$button_text}</i>
							</button>
						</td>
					</tr>";
			$counter ++;
		}
		return $resp;
	}

	function getValuesByCategory( $link, $category_id = null, $value_id = null ){
		$sql = "SELECT
					id_valor_nota AS note_value_id,
					nombre_valor_nota AS category_name_id
				FROM ec_productos_valores_notas";
		if( $category_id != null ){
			$sql .= " WHERE 1 AND id_categoria_nota = {$category_id}";
		}
		$sql .= " ORDER BY orden ASC";
		//$resp .= $sql;
//				WHERE id_categoria_nota = {$categoty_id}";
		$stm = mysql_query( $sql ) or die( "Error : {$sql} " . mysql_error() );
		while( $row = mysql_fetch_assoc( $stm ) ){
			$resp .= "<option value=\"{$row['note_value_id']}\" " . ( $value_id != null && $value_id == $row['note_value_id'] ? ' selected' : '' ) . ">{$row['category_name_id']}</option>";
		}
		return $resp;
	}


	function getUserName(){
		$sql = "SELECT
					CONCAT( nombre, ' ', apellido_paterno ) AS userName
				FROM sys_usuarios WHERE id_usuario = {$user_id}";
		$stm = mysql_query( $sql ) or die( "Error al consultar el nombre de usuario : {mysql_error()}" );
		$row = mysql_fetch_assoc( $stm );
		return $row['userName'];
	}

	function getComboNotesCategories( $link, $category_id = null ){
		if( !$link ){
			include( '../../../../conecMin.php' );
		}
		$resp = "";
		$sql = "SELECT
					id_categoria_nota AS note_category_id,
					nombre_categoria_nota AS note_category_name
				FROM ec_productos_categorias_notas
				WHERE 1";
		if( $category_id != null ){
			$sql .= " AND id_categoria_nota = {$category_id}";
		}
		
		$stm = mysql_query( $sql ) or die( "Error al consultar las categorias de notas de productos : {$sql}" . mysql_error() );
		while( $row = mysql_fetch_assoc( $stm ) ){
			$resp .= "<option value=\"{$row['note_category_id']}\">{$row['note_category_name']}</option>";
		}
		return $resp;
	}

	function getView( $product_id, $link ){
		$sql="SELECT observaciones FROM ec_productos WHERE id_productos=$product_id";
		$eje_nta=mysql_query($sql)or die("Error al consultar las notas de venta!!!".mysql_error());
		$nota=mysql_fetch_row($eje_nta);
		//$productNotes = new productNotes( $link );
		$resp = "<div>
					<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10\" style=\"font-size : 90% !important;\">
							<div class=\"row\">
								<div class=\"col-4\">	
									<span style=\"color:white;\">Tipo de nota</span>
								</div>
								<div class=\"col-3\">
									<textarea id=\"productNoteTextareaTmp\"></textarea>
									<select id=\"notes_categories_combo\" class=\"combo\" style=\"width : 100%; left : 50%;\" onchange=\"load_notes_by_type( this, {$product_id} );\">
									<option value=\"-1\">Ver todo</option>"
									. getComboNotesCategories( $link ) . 
									"</select>
								</div>

								<div class=\"col-3\">	
									<button 
										id=\"historic_btn\"
										type=\"button\" 
										class=\"btn btn-info\"
										onclick=\"show_historic_notes( {$product_id} )\"
									>
										<i>Historico</i>
									</button>
								</div>
								<div class=\"col-2\">
									<button 
										class=\"btn btn-success\" 
										style=\"border-radius : 50%;\"
										title=\"Da click para ver información de como generar el histórico.\"
										onclick=\"show_historic_info();\"
									>
										<i>?</i>
									</button>
								</div>
							</div>
							<table class=\"table table-bordered\" style=\"font-size : 80% !important;\">
								<thead style=\"font-size : 80%; background: rgba( 225, 0, 0, 0.8 );\">
									<tr>
										<th width=\"50px\" class=\"overflow_hidden\">Categoria</th>
										<th width=\"30px\" class=\"overflow_hidden\">Valor</th>
										<th width=\"50px\" class=\"overflow_hidden\">Nota</th>
										<th width=\"30px\" class=\"overflow_hidden\">Usuario</th>
										<th width=\"20px\" class=\"overflow_hidden\">X</th>
									</tr>
								</thead>
								<tbody id=\"product_notes_lists\">" .
								getNotesByProduct( $product_id, -1, $link )
								. "</tbody>
								<tfoot>
									<tr style=\"background-color : rgba( 0,225,0, .5 );\">
										<td width=\"50px\" id=\"note_type\"><select id=\"add_note_category\" class=\"form-control\">
										<option value=\"-1\">Seleccionar</option>" .  getComboNotesCategories( $link ) . "</select></td>
										<td width=\"30px\" id=\"note_catalogue_value\"><select id=\"id_type_category\">
										<option value=\"-1\">Seleccionar</option>" . getValuesByCategory( $link ) . "</select></td>
										<td width=\"50px\" id=\"note_text\">
											<textarea id=\"add_note_txt\" onclick=\"expand( '#add_note_txt', 1 );\" class=\"form-control\"></textarea>
										</td>
										<td width=\"30px\" id=\"user_note\">
										</td>
										<td width=\"20px\">
											<button type=\"button\" class=\"btn btn-success\" onclick=\"add_row_note( {$product_id} );\">
												<i class=\"icon-ok-circled\">+</i>
											</button>
										</td>
									</tr>
								</tfoot>
							</table>
							<br><br>
							<textarea id=\"campo_nota\" style=\"width:100%;height:100px;\" onkeyup=\"activa_edic_prec(event,0,'nota');\" placeholder=\"Notas Generales...\">{$nota[0]}</textarea>
							<br><button onclick=\"guarda_nota({$product_id});\" id=\"guardar_nota_prods\" style=\"padding:10px;display:none;\">Guardar</button>
						</div>
					</div>
				</div>
				<style>

					#productNoteTextareaTmp{
						position: absolute;
						width: 80%;
						left : 0;
						display : none;
					}
				</style>
				";
		return $resp;
	}

	function getProyectionDetail( $product_id, $date, $date_from, $date_to, $link, $is_per_product = null ){
		$sql = "SELECT
				    ax3.product_provider_id AS 'ID PROVEEDOR PRODUCTO',
				    ax3.nombre AS productModel,
				    ax3.inventario_inicial AS 'INVENTARIO INICIAL',
				    ax3.pedido AS 'PEDIDO',
				    ax3.recibidoRecepcion AS 'CANTIDAD RECEPCION',
				    ax3.cantidad_validada AS 'CANTIDAD VALIDADA',
				    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'proyection_in_pieces',
				    ax3.piezas_por_paquete AS 'pieces_per_pack',
				    ax3.piezas_por_caja AS 'pieces_per_box',
				    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) / ax3.piezas_por_caja AS 'proyection_in_boxes'
				FROM(
				    SELECT
				        ax2.product_provider_id,
				        ax2.nombre,
				        ax2.inventario_inicial,
				        SUM( IF( ocd.id_oc_detalle IS NOT NULL, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS 'pedido',
				        ax2.recibidoRecepcion,
				        ax2.cantidad_validada,
				        ax2.piezas_por_paquete,
				        ax2.piezas_por_caja
				    FROM
				    (
				        SELECT
				            ax1.product_provider_id,
				            ax1.nombre,
				            SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NOT NULL AND mdpp.fecha_registro <= '{$date} 23:59:59', ( tm.afecta * mdpp.cantidad ), 0 ) ) AS inventario_inicial,
				            ax1.recibidoRecepcion,
				            ax1.piezas_por_paquete,
				            ax1.piezas_por_caja,
				            ax1.cantidad_validada
				        FROM(
				            SELECT
				                ax.product_provider_id,
				                ax.nombre,
				                ax.recibidoRecepcion,
				                ax.piezas_por_paquete,
				                ax.piezas_por_caja,
				                SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada
				            FROM(
				                SELECT
				                    IF(pp.id_proveedor_producto IS NULL, 'No tiene', pp.id_proveedor_producto) AS 'product_provider_id',
				                    CONCAT( p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' MODELO : ', pp.clave_proveedor ) ) ) AS nombre,
				                    IF( rbd.id_recepcion_bodega_detalle IS NULL, 
				                       0,
				                       SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
				                    ) AS recibidoRecepcion,
				                    pp.piezas_presentacion_cluces AS 'piezas_por_paquete',
				                    pp.presentacion_caja AS 'piezas_por_caja'
				                FROM ec_productos p
				                LEFT JOIN ec_proveedor_producto pp
				                ON p.id_productos = pp.id_producto
				                LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				                ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				                LEFT JOIN ec_recepcion_bodega_detalle rbd
				                ON rbd.id_proveedor_producto = pp.id_proveedor_producto
				                AND rbd.validado IN( 0  )
				                LEFT JOIN ec_recepcion_bodega rb 
				                ON rb.id_recepcion_bodega = rbd.id_recepcion_bodega
				                AND ( rb.fecha_alta BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' )
				                AND rb.id_status_validacion IN( 1, 2  )
				                AND rb.id_recepcion_bodega_status IN(  2, 3  )
				                WHERE p.id_productos > 0
				                AND pp.id_proveedor_producto > 0
				                AND pp.id_producto IN( {$product_id} )
				                GROUP BY p.id_productos, pp.id_proveedor_producto  
				                ORDER BY `recibidoRecepcion`  DESC
				            )ax
				            LEFT JOIN ec_oc_recepcion_detalle ord
				            ON ax.product_provider_id = ord.id_proveedor_producto
				            LEFT JOIN ec_oc_recepcion ocr
				            ON ocr.id_oc_recepcion = ord.id_oc_recepcion
				            AND ( ocr.fecha_recepcion BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' )
				            GROUP BY ax.product_provider_id
				        )ax1
				        LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
				        ON mdpp.id_proveedor_producto = ax1.product_provider_id
				        AND mdpp.fecha_registro <= '{$date} 23:59:59'
				        LEFT JOIN ec_tipos_movimiento tm
				        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
				        GROUP BY ax1.product_provider_id
				    )ax2
				    LEFT JOIN ec_oc_detalle ocd
				    ON ocd.id_proveedor_producto = ax2.product_provider_id
				    LEFT JOIN ec_ordenes_compra oc
				    ON oc.id_orden_compra = ocd.id_orden_compra
				   /* AND ( oc.fecha BETWEEN '2022-08-20' AND '2022-08-20' )*/
				    GROUP BY ax2.product_provider_id
				)ax3
				GROUP BY ax3.product_provider_id";
		$stm = mysql_query( $sql ) or die( "Error al consultar los datos de la proyección : " . mysql_error() );
		$resp = "<table class=\"table table-bordered\" style=\"background-color : rgb(255, 248, 187);z-index:100; width : 100%;\">";
			$resp .= "<thead class=\"header_fixed\">
						<tr>
							<th width=\"50%\">Producto - modelo </th>
							<th width=\"12.5%\">Pzs x paq</th>
							<th width=\"12.5%\">Pzs x caja</th>
							<th width=\"12.5%\">Proy (pzs)</th>
							<th width=\"12.5%\">Proy (cjs)</th>
						</tr>
					</thead>
					<tbody>";
		$total = 0;
		while ( $row = mysql_fetch_assoc( $stm ) ) {
			$row['proyection_in_boxes'] = round( $row['proyection_in_boxes'], 2 );
			$resp .= "<tr>
						<td width=\"60%\" style=\"font-size : 80% !important;\">{$row['productModel']}</td>
						<td width=\"10%\">{$row['pieces_per_pack']}</td>
						<td width=\"10%\">{$row['pieces_per_box']}</td>
						<td width=\"10%\">{$row['proyection_in_pieces']}</td>
						<td width=\"10%\">{$row['proyection_in_boxes']}</td>
					</tr>";
			$total += $row['proyection_in_pieces'];
		}
		$resp .= "</tbody></table>";
		if( $is_per_product != null ){
			return $total;
		}
		return $resp;
	}

	/*function getProductProyectionByProduct( $product_id, $date, $date_from, $date_to, $link ){
		$sql = "SELECT fecha_inventario_inicial_actual AS initial_inventory FROM sys_configuracion_sistema WHERE id_configuracion_sistema = 1";
		$stm = mysql_query( $sql ) or die( "Error al consultar la configuracion de fecha de inventario inicial : " . mysql_error()  );
		$row = mysql_fetch_assoc( $stm );
		$inventory_initial_date = $row['initial_inventory'];
		$sql = "SELECT
					ax3.id_productos,
				    ax3.product_provider_id AS 'ID PROVEEDOR PRODUCTO',
				    ax3.nombre AS productModel,
				    ax3.inventario_inicial AS 'INVENTARIO INICIAL',
				    ax3.pedido AS 'PEDIDO',
				    ax3.recibidoRecepcion AS 'CANTIDAD RECEPCION',
				    ax3.cantidad_validada AS 'CANTIDAD VALIDADA',
				    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'proyection_in_pieces',
				    ax3.piezas_por_paquete AS 'pieces_per_pack',
				    ax3.piezas_por_caja AS 'pieces_per_box',
				    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) / ax3.piezas_por_caja AS 'proyection_in_boxes'
				FROM(
				    SELECT
				        ax2.id_productos,
				        ax2.product_provider_id,
				        ax2.nombre,
				        ax2.inventario_inicial,
				        SUM( IF( ocd.id_oc_detalle IS NOT NULL, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS 'pedido',
				        ax2.recibidoRecepcion,
				        ax2.cantidad_validada,
				        ax2.piezas_por_paquete,
				        ax2.piezas_por_caja
				    FROM
				    (
				        SELECT
				            ax1.id_productos,
				            ax1.product_provider_id,
				            ax1.nombre,
				            SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NOT NULL AND mdpp.fecha_registro <= '{$date} 23:59:59', ( tm.afecta * mdpp.cantidad ), 0 ) ) AS inventario_inicial,
				            ax1.recibidoRecepcion,
				            ax1.piezas_por_paquete,
				            ax1.piezas_por_caja,
				            ax1.cantidad_validada
				        FROM(
				            SELECT
				            	ax.id_productos,
				                ax.product_provider_id,
				                ax.nombre,
				                ax.recibidoRecepcion,
				                ax.piezas_por_paquete,
				                ax.piezas_por_caja,
				                SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada
				            FROM(
				                SELECT
				                    IF(pp.id_proveedor_producto IS NULL, 'No tiene', pp.id_proveedor_producto) AS 'product_provider_id',
				                    CONCAT( p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' MODELO : ', pp.clave_proveedor ) ) ) AS nombre,
				                    IF( rbd.id_recepcion_bodega_detalle IS NULL, 
				                       0,
				                       SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
				                    ) AS recibidoRecepcion,
				                    pp.piezas_presentacion_cluces AS 'piezas_por_paquete',
				                    pp.presentacion_caja AS 'piezas_por_caja',
				                    p.id_productos
				                FROM ec_productos p
				                LEFT JOIN ec_proveedor_producto pp
				                ON p.id_productos = pp.id_producto
				                LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				                ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				                LEFT JOIN ec_recepcion_bodega_detalle rbd
				                ON rbd.id_proveedor_producto = pp.id_proveedor_producto
				                AND rbd.validado IN( 0  )
				                LEFT JOIN ec_recepcion_bodega rb 
				                ON rb.id_recepcion_bodega = rbd.id_recepcion_bodega
				                AND ( rb.fecha_alta BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' )
				                AND rb.id_status_validacion IN( 1, 2  )
				                AND rb.id_recepcion_bodega_status IN(  2, 3  )
				                WHERE p.id_productos > 0
				                AND pp.id_proveedor_producto > 0
				                AND pp.id_producto IN( {$product_id} )
				                GROUP BY p.id_productos
				            )ax
				            LEFT JOIN ec_oc_recepcion_detalle ord
				            ON ax.product_provider_id = ord.id_proveedor_producto
				            LEFT JOIN ec_oc_recepcion ocr
				            ON ocr.id_oc_recepcion = ord.id_oc_recepcion
				            AND ( ocr.fecha_recepcion BETWEEN '{$date_from} 00:00:01' AND '{$date_to} 23:59:59' )
				            GROUP BY ax.id_productos
				        )ax1
				        LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
				        ON mdpp.id_proveedor_producto = ax1.product_provider_id
				        AND mdpp.fecha_registro <= '{$inventory_initial_date} 23:59:59'
				        LEFT JOIN ec_tipos_movimiento tm
				        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
				        GROUP BY ax1.id_productos
				    )ax2
				    LEFT JOIN ec_oc_detalle ocd
				    ON ocd.id_proveedor_producto = ax2.product_provider_id
				    LEFT JOIN ec_ordenes_compra oc
				    ON oc.id_orden_compra = ocd.id_orden_compra
				   /* AND ( oc.fecha BETWEEN '2022-08-20' AND '2022-08-20' )*
				    GROUP BY ax2.id_productos
				)ax3
				GROUP BY ax3.id_productos";
		$stm = mysql_query( $sql ) or die( "Error al consultar proyeccion del producto por año : " . mysql_error() );
		$row = mysql_fetch_assoc( $stm );
		return $row['proyection_in_pieces'];//'ok|' .
	}*/

?>

