<?php
	if( isset( $_GET['resolution_fl'] ) ){
		//include( '../../../../../config.inc.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$ProductResolution = new ProductResolution( $link, $_GET['reception_block_id'] );
		$action = $_GET['resolution_fl'];
		switch ( $action ) {
			case 'saveResolutionPrevious' :
				echo $ProductResolution->insertProductResolution( $_GET['case_1'], $_GET['case_2'], $user_id );
			break;
			
			case 'getResolutionForm' :
				echo $ProductResolution->getResolutionForm( $user_id );
			break;			

			case 'save_resolution_row' :
				echo $ProductResolution->save_resolution_row( $_GET['quantity'], $_GET['type'], $_GET['product_id'],
					$_GET['product_provider_id'], $_GET['resolution_id'], $_GET['movement_type'], 
					$_GET['transfer_block_resolution_id'], $_GET['product_resolution_id'], $user_id );
			break;

			default:
				die( 'Permission denied' );	
			break;
		}
	}

	class ProductResolution
	{
		private $link;
		private $reception_block_id;
		private $transfers;
		private $origin_store_id;
		private $destinity_store_id;
		private $warehouse_origin;
		private $warehouse_destinity;

		function __construct( $connection, $reception_block )
		{
			$this->link = $connection;
			$this->reception_block_id = $reception_block;
			$this->transfers = $this->getTransfersByBlock();
		}

		public function finishResolutionTransfers(){
		//die( 'here' );
			$this->link->autocommit( false );

			$sql = "SELECT
					GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids,
					t.id_sucursal_origen AS origin_store_id,
					t.id_sucursal_destino AS destinity_store_id,
					t.id_almacen_origen AS warehouse_origin,
					t.id_almacen_destino AS warehouse_destinity
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}
				AND t.id_tipo IN( 9, 12 )";
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar los ids transferencias Resolución : {$this->link->error}" );
			$transfer_rows = $stm->fetch_assoc();
			$transfers_ids = $transfer_rows['transfers_ids'];
			$sql = "UPDATE ec_transferencias SET id_estado = 2 WHERE id_transferencia IN( $transfers_ids )";
			$stm = $this->link->query( $sql ) or die( "error|Error actualizar transferencia a autorizada : {$this->link->error}" );

			$sql = "UPDATE ec_transferencias SET id_estado = 9 WHERE id_transferencia IN( $transfers_ids )";
			$stm = $this->link->query( $sql ) or die( "error|Error actualizar transferencia a terminada : {$this->link->error}" );
		//actualiza el bloque a recibido
			$sql = "UPDATE ec_bloques_transferencias_recepcion SET recibido = '1' WHERE id_bloque_transferencia_recepcion = {$this->reception_block_id}";
			$stm = $this->link->query( $sql ) or die( "error|Error actualizar bloque a recibido : {$this->link->error}" );
			
			$this->link->autocommit( true );

			return "ok|<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10\">
							<h5>La resolucion fue terminada exitosamente.</h5>
						</div>
						<div class=\"col-1\"></div>
						<div class=\"col-4\"></div>
						<div class=\"col-4 text-center\">
							<button
								type=\"button\"
								class=\"btn btn-success form-control\"
								onclick=\"location.reload();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
		}	

		public function getBlockTransferResolution( $type, $user ){
			$transfer_type = 9;
			if( $type == 12 || $type == 3 ){
				$transfer_type = 12;
			}
			$sql = "SELECT
					t.id_transferencia AS transfer_id
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}
				AND t.id_tipo = {$transfer_type}";
//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar ids de transferencias : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				return $row['transfer_id'];
			}else{
				return $this->insertResolutionTransfer( $transfer_type, $user );
			}
		}

		public function insertResolutionTransfer( $transfer_type, $user ){
		//inserta transferencia
			$sql="INSERT INTO ec_transferencias SET 
				id_usuario = {$user},
				folio = '',
				fecha = NOW(),
				hora = NOW(),
				id_sucursal_origen = {$this->origin_store_id},
				id_sucursal_destino = {$this->destinity_store_id},
				observaciones = 'Transferencia por Resolución',
				id_razon_social_venta = -1,
				id_razon_social_compra = 1,
				facturable = 0,
				porc_ganancia = 0,
				id_almacen_origen = {$this->warehouse_origin},
				id_almacen_destino = {$this->warehouse_destinity},
				id_tipo = {$transfer_type},
				id_estado = 1,
				id_sucursal = {$this->destinity_store_id}, 
				titulo_transferencia = 'Resolución'";//inserta el bloque de validacion
			$this->link->query( $sql ) or die( "Error al insertar transferencia : {$sql} {$this->link->error}" );
			$transfer_id = $this->link->insert_id;
			$sql = "INSERT INTO ec_bloques_transferencias_validacion SET
					id_bloque_transferencia_validacion = NULL,
					fecha_alta = NOW(),
					validado = '0'";
			$insert_block = $this->link->query( $sql ) or die( "Error al insertar cabecera del bloque de validación : {$this->link->error}" );
			$block_id = $this->link->insert_id;
		//inserta detalle del bloque de validacion
			$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle SET 
					id_bloque_transferencia_validacion_detalle = NULL,
					id_bloque_transferencia_validacion = {$block_id},
					id_transferencia = {$transfer_id},
					fecha_alta = NOW(),
					invalidado = '0'";
			$insert_block_detail = $this->link->query( $sql ) or die( "Error al insertar detalle del bloque de validación : {$this->link->error}" );
		//inserta el detalle en el bloque de recepcion
			$sql = "INSERT INTO ec_bloques_transferencias_recepcion_detalle
						SET id_bloque_transferencia_recepcion_detalle = NULL,
						id_bloque_transferencia_recepcion = {$this->reception_block_id},
						id_bloque_transferencia_validacion = {$block_id}";
			$stm_ins = $this->link->query( $sql ) or die( "Error al insertar el detalle de escaneos de validacion : {$this->link->error}" );
		//genera el folio de la transferencia
			$sql_folio = "SELECT 
							CONCAT(s1.prefijo, s2.prefijo, ' ', t.id_transferencia ) AS folio
						FROM ec_transferencias t
						LEFT JOIN sys_sucursales s1
						ON s1.id_sucursal = t.id_sucursal_origen
						LEFT JOIN sys_sucursales s2
						ON s2.id_sucursal = t.id_sucursal_destino
						WHERE t.id_transferencia = '{$transfer_id}'";
			$stm_folio = $this->link->query( $sql_folio ) or die( "Error al consultar el folio : {$this->link->error}" );
			$row_folio = $stm_folio->fetch_assoc();
			$folio =  $row_folio['folio'];
		//actualiza el folio de la transferencia
			$sql = "UPDATE ec_transferencias SET folio = '{$folio}' WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio de la transferencia : {$this->link->error}" );
			return $transfer_id;
		}

		public function save_resolution_row( $quantity, $type, $product_id, $product_provider_id, $resolution_id, $movement_type, 
			$transfer_block_resolution_id, $product_resolution_id, $user ){

			//$this->link->autocommit( false );//pba oscar 2023

//$this->link->autocommit( false );

			$transfer_id = $this->getBlockTransferResolution( $_GET['type'], $user );
			$ommit_origin_movement = '0';
			$ommit_destinity_movement = '0';
			
			$resp = "ok|<div class=\"row\">
						<div class=\"col-2\"></div>
						<div class=\"col-10\">
							<h5>El producto fue resuelto exitosamente.</h5>
							<br>
							<button
								type=\"button\"
								class=\"btn btn-success\"
								onclick=\"close_emergent_2();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
							//1 - mov_origen,  2 - mov_dest , 3 - 2_movs, 4 - no_movs
			if( $movement_type == 1 ){
				$ommit_origin_movement = '0';
				$ommit_destinity_movement = '1';
				$quantity = $quantity * -1;
			}else if( $movement_type == 2 ){
				$ommit_origin_movement = '1';
				$ommit_destinity_movement = '0';
			}else if( $movement_type == 3 ){
				$ommit_origin_movement = '0';
				$ommit_destinity_movement = '0';
			}else if( $movement_type == 4 ){
				$ommit_origin_movement = '1';
				$ommit_destinity_movement = '1';
			}
//die( "omitir {$ommit_origin_movement} : {$ommit_destinity_movement}" );
		//inserta el detalle  de la  resolucion
			$sql = "INSERT INTO ec_transferencia_productos( /*1*/id_transferencia, /*2*/id_producto_or, 
				/*3*/id_presentacion, /*4*/cantidad_presentacion, /*5*/cantidad, /*6*/id_producto_de, 
				/*7*/referencia_resolucion, /*8*/cantidad_cajas, /*9*/cantidad_paquetes, 
				/*10*/cantidad_piezas, /*11*/id_proveedor_producto, /*12*/cantidad_cajas_surtidas,
				/*13*/cantidad_paquetes_surtidos, /*14*/cantidad_piezas_surtidas, 
				/*15*/total_piezas_surtimiento, /*16*/cantidad_cajas_validacion, 
				/*17*/cantidad_paquetes_validacion, /*18*/ cantidad_piezas_validacion, 
				/*19*/total_piezas_validacion, /*20*/agregado_en_surtimiento, /*21*/cantidad_piezas_recibidas, 
				/*22*/total_piezas_recibidas, /*23*/omite_movimiento_origen, /*24*/omite_movimiento_destino )
				SELECT
				/*1*/'{$transfer_id}',
				/*2*/'{$product_id}',
				/*3*/-1,
				/*4*/0,
				/*5*/{$quantity},
				/*6*/'{$product_id}',
				/*7*/{$quantity},
				/*8*/0,
				/*9*/0,
				/*10*/{$quantity},
				/*11*/'{$product_provider_id}',
				/*12*/0,
				/*13*/0,
				/*14*/'{$quantity}',
				/*15*/{$quantity},
				/*16*/'0',
				/*17*/'0',
				/*18*/'{$quantity}',
				/*19*/{$quantity},
				/*20*/'0',
				/*21*/{$quantity},
				/*22*/{$quantity},
				/*23*/'{$ommit_origin_movement}',
				/*24*/'{$ommit_destinity_movement}'";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo registro en la transferencia {$sql} " . $this->link->error );
			$new_detail_id  = $link->insert_id;

/*actualiza deralles de transferencias
			$sql = "UPDATE ec_transferencia_productos SET resuelto = '1'
					WHERE id_transferencia_producto
					IN(
						SELECT
							ax.transfer_product_id
						FROM(
							SELECT 
								tp.id_transferencia_producto AS transfer_product_id
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_transferencias t
							ON tp.id_transferencia = t.id_transferencia
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON btvd.id_transferencia = t.id_transferencia
							LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
							ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
							WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}
							AND tp.resuelto = 0
							AND tp.id_proveedor_producto = {$product_provider_id}
							AND tp.total_piezas_validacion != tp.total_piezas_recibidas
							GROUP BY tp.id_transferencia_producto
						)ax
					)";
			$stm = $this->link->query( $sql ) or die( "error|Error al actualizar detalles de transferencias a resueltos : {$sql} " . $this->link->error );
*/
		//actualiza a resuelto el detalle de la resolucion
			if( $transfer_block_resolution_id != '' && $transfer_block_resolution_id != null ){	
				$sql = "UPDATE ec_bloques_transferencias_resolucion 
							SET resuelto = 1 
						WHERE id_bloque_transferencia_resolucion = {$transfer_block_resolution_id}";
		//die($sql);
				$stm = $this->link->query( $sql ) or die( "error|Error al actualizar registro de resolucion prov prod transferencia {$sql} " . $this->link->error );
			}

			if( $product_resolution_id != '' && $product_resolution_id != null ){	
				$sql = "UPDATE ec_productos_resoluciones_tmp 
							SET resuelto = '1' 
						WHERE id_producto_resolucion = {$product_resolution_id}";
				//die( $sql );
				$stm = $this->link->query( $sql ) or die( "error|Error al actualizar registro de resolucion producto transferencia {$sql} " . $this->link->error );
			}
		//verifica que no tenga pendientes de resolver ( sobrante / no corresponde )
			/*$sql = "SELECT
						id_producto_resolucion
					FROM ec_productos_resoluciones_tmp
					WHERE id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND resuelto = '0'";*/
			$sql = "SELECT
						id_bloque_transferencia_resolucion
					FROM ec_bloques_transferencias_resolucion
					WHERE id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND resuelto = 0";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar las resoluciones pendientes : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				return $resp . $stm->num_rows;
			}
		/*	$sql = "SELECT
						id_producto_resolucion
					FROM ec_productos_resoluciones_tmp
					WHERE id_bloque_transferencia_recepcion = {$this->reception_block_id}";
		die( $sql );
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar las resoluciones pendientes : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				return $resp . 2;
			}*/
		//verifica que no tenga pendiente ( faltante )
		/*	$sql = "SELECT
						id_transferencia_producto
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_transferencias t
					ON tp.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON btvd.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND tp.resuelto = 0
					AND tp.total_piezas_validacion != tp.total_piezas_recibidas
					GROUP BY tp.id_transferencia_producto";
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar las resoluciones pendientes ( faltante ) : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				return $resp;
			}*/
			return $this->finishResolutionTransfers();
			$this->link->autocommit( true );

		}

		public function getResolutionForm( $user, $numero = 1 ){
			$resp = "";
		//consulta si tiene el permiso para conntinuar con la resolucion
			$sql = "SELECT 
					perm.id_menu AS menu_id,
					IF( perm.ver = 1 OR perm.modificar = 1 OR perm.eliminar = 1 
						OR perm.nuevo = 1 OR perm.imprimir = 1 OR perm.generar = 1, 1, 0 ) AS permission
				FROM sys_permisos perm
				LEFT JOIN sys_users_perfiles up
				ON perm.id_perfil = up.id_perfil
				LEFT JOIN sys_users u 
				ON u.tipo_perfil = up.id_perfil
				WHERE perm.id_menu IN ( 260 )
				AND u.id_usuario = {$user}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar permiso especial del usuario : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resolution_special_permission = $row['permission'];

			$sql = "SELECT 
						prt.id_producto_resolucion AS product_resolution_id,
						prt.id_producto AS product_id,
						p.nombre AS product_name,
						prt.inventario AS inventory,
						prt.recibido AS received,
						prt.conteo_fisico AS fisic_counter,
						prt.conteo_excedente AS excedent_counter
					FROM ec_productos_resoluciones_tmp prt
					LEFT JOIN ec_productos p
					ON p.id_productos = prt.id_producto
					WHERE prt.id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND prt.resuelto = 0
					GROUP BY prt.id_producto";
	//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos que entraron en resolucion : {$this->link->error}" );
			
			$resp .= "<div class=\"text-end\">
						<button 
							class=\"btn btn-danger\"
							onclick=\"close_emergent();\"
						>
							<i>X</i>
						</button>
					</div>";
			$resp .= "<div class=\"accordion\" id=\"accordionExample\">";
			$counter = 0;
			while ( $row = $stm->fetch_assoc() ) {

				$row['received'] = str_replace( '.0000', '', $row['received'] );
				$row['inventory'] = str_replace( '.0000', '', $row['inventory'] );
				$row['fisic_counter'] = str_replace( '.0000', '', $row['fisic_counter'] );
				$row['excedent_counter'] = str_replace( '.0000', '', $row['excedent_counter'] );
				
				$resp .= '<div class="accordion-item">';
		    	$resp .= '<h2 class="accordion-header" id="heading_'.$numero .'_'. $counter .'">';
			    	$resp .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_'.$numero .'_'.$counter.'"'
			    	. ' aria-expanded="true" aria-controls="collapse_'.$numero .'_'. $counter .'" '
			    	. 'id="herramienta_'.$numero .'_' .  $counter  . '" class="opc_btn">';//onclick="carga_filtros('.$r[0].',\'busc_prod\');"
			        $resp .= "<div class=\"row\">
			        			<p style=\"color : red;\">{$row['product_name']}</p>
			        			<div class=\"col-3\">
			        				Inventario : <br>{$row['inventory']}
			        			</div>
			        			<div class=\"col-3\">
			        				Recibido : <br>{$row['received']}
			        			</div>
			        			<div class=\"col-3\">
			        				Conteo : <br>{$row['fisic_counter']}
			        			</div>
			        			<div class=\"col-3\">
			        				Excedente : <br>{$row['excedent_counter']}
			        			</div>
			        		</div>";
			      	$resp .= '</button>';
		    	$resp .= '</h2>';
		    	$resp .= '<div id="collapse_'.$numero .'_'. $counter .'" class="accordion-collapse collapse description" aria-labelledby="heading_'.$numero .'_' . $counter . '" data-bs-parent="#accordionExample">';
			    	$resp .= '<div class="accordion-body">';
			    	$resp .= $this->getProductProviderLevel( $row['product_id'], $row['product_resolution_id'], $resolution_special_permission );
			    	$resp .= '</div>';
		    	$resp .= '</div>';
			  	$resp .= '</div>';
				$counter  ++;
			}
		//$resp .= '<input type="hidden" id="contador_herramientas_' . $numero . '" value="' . $cont . '">';
			$resp .= '</div>';
			return $resp;
		}

		public function getProductProviderLevel( $product_id, $product_resolution_id, $resolution_special_permission ){
			$resp = "";
			/*$sql = "SELECT
						ax.product_provider_id,
						ax.product_id,
						ax.provider_clue,
						ax.validated,
						ax.received,
						( ax.validated - ax.received ) AS difference
					FROM(
						SELECT 
							tp.id_proveedor_producto AS product_provider_id,
							tp.id_producto_or AS product_id,
							pp.clave_proveedor AS provider_clue,
							SUM( tp.total_piezas_validacion ) AS validated,
							SUM( tp.total_piezas_recibidas ) AS received
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_productos p
						ON p.id_productos = tp.id_producto_or
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_proveedor_producto = tp.id_proveedor_producto
						WHERE tp.id_transferencia IN( {$this->transfers} )
						AND tp.id_producto_or = {$product_id}
						AND tp.resuelto = 0
						GROUP BY tp.id_proveedor_producto
					)ax
					WHERE ( ax.validated - ax.received ) != 0
					GROUP BY ax.product_provider_id";
			$stm = $this->link->query( $sql ) or die( "Error al consultar diferencias en recepciones : {$sql} {$this->link->error}" );
			
			$resp .= $this-> build_resolution_rows( $stm, $product_resolution_id, 1, $resolution_special_permission );
*/
			$sql = "SELECT
						btr.id_bloque_transferencia_resolucion AS transfer_block_resolution_id,
						btr.id_proveedor_producto AS product_provider_id,
						btr.id_producto AS product_id,
						pp.clave_proveedor AS provider_clue,
						0 AS validated,
						0 AS received,
						IF( btr.piezas_faltantes > 0,
							btr.piezas_faltantes,
							SUM( btr.piezas_sobrantes + btr.piezas_no_corresponden )
						) AS difference,
						IF( btr.piezas_faltantes > 0, 1 , 2 ) AS type
					FROM ec_bloques_transferencias_resolucion btr
					LEFT JOIN ec_productos p 
					ON p.id_productos = btr.id_producto
					LEFT JOIN  ec_proveedor_producto pp
					ON pp.id_proveedor_producto = btr.id_proveedor_producto
					/*LEFT JOIN ec_transferencia_productos tp
					ON tp.id_producto_or  = btr.id_producto
					AND tp.id_proveedor_producto =  btr.id_proveedor_producto
					AND tp.id_transferencia IN( $this->transfers )*/
					WHERE btr.id_bloque_transferencia_recepcion = {$this->reception_block_id}
					AND btr.id_producto = {$product_id}
					/*AND ( btr.piezas_no_corresponden > 0
					OR btr.piezas_sobrantes > 0 )*/
					AND btr.resuelto = 0
					GROUP BY btr.id_bloque_transferencia_resolucion
					/*AND tp.id_proveedor_producto IS NOT NULL*/";
//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar diferencias en recepciones : {$sql} {$this->link->error}" );
			$resp .= $this-> build_resolution_rows( $stm, $product_resolution_id, 2, $resolution_special_permission );
			return $resp;
		}

		public  function build_resolution_rows( $stm, $product_resolution_id, $type, $resolution_special_permission ){
			$resp = "";
			$disabled = ( $resolution_special_permission == 1 ? '' : 'disabled' );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<div class=\"row\">
						<table class=\"table table-bordered\">
							<thead>
								<tr>
									<th class=\"col-2 text-center\">
										Clave
									</th>
									<th class=\"col-2 text-center\">
										Validado
									</th>
									<th class=\"col-2 text-center\">
										Recibido
									</th>
									<th class=\"col-2 text-center\">
										Faltante
									</th>
									<th class=\"col-2 text-center\">
										Sobrante
									</th>
								</tr>
							</thead>
							<tbody>";
				if( $row['difference'] != null ){
					$row['difference'] = str_replace( '.0000', '', $row['difference'] );
					$row['validated'] = str_replace( '.0000', '', $row['validated'] );
					$row['received'] = str_replace( '.0000', '', $row['received'] );
					$missing = ( $row['difference'] > 0 ? $row['difference'] : 0 );
					$excedent = ( $row['difference'] < 0 ? ( $row['difference'] * -1 ) : 0 );
					//if( $type == 2 ){
					if( $row['type'] == 2 ){
						$missing = 0;
						$excedent = $row['difference'];
					}	
					$final_quantity = ( $missing != 0 ? $missing : $excedent );//<div class=\"row\">
					$resp .= "<tr>
								<td class=\"text-center\">
									{$row['provider_clue']}
								</td>
								<td class=\"text-end\">
									{$row['validated']}
								</td>
								<td class=\"text-end\">
									{$row['received']}
								</td>
								<td class=\"text-end\">
									{$missing}
								</td>
								<td class=\"text-end\">
									{$excedent}
								</td>
							</tr>
						</tbody>
					</table>
					</div>
					<br>
					<div class=\"row\">";
								/*
									
								<div class=\"col-2 text-end\">
									0
								</div>
								*/

						//movement types
							//1 - mov_origen,  2 - mov_dest , 3 - 2_movs, 4 - no_movs
							$movement_type = 3;
							if( $missing > 0 ){
								$movement_type = 2;
							}
							$class = ( $resolution_special_permission == 1 ? 'success' : 'light' );

							$resp .= "<div class=\"col-4\">
										<button 
											type=\"button\"
											class=\"btn btn-{$class} form-control\"
											onclick=\"saveResolutionPerProductProvider( {$row['product_id']}, {$row['product_provider_id']}, 
												{$final_quantity}, 9, {$movement_type}, '{$row['transfer_block_resolution_id']}', 
												'{$product_resolution_id}' );\"
											{$disabled}
										>
											<i class=\"\">Recibir <b>{$row['difference']}</b></i>
										</button>
									</div>";

						if( $excedent == 0 ){
							$class = ( $resolution_special_permission == 1 ? 'warning' : 'light' );
							$movement_type = 3;
							if( $missing > 0 ){
								$movement_type = 1;
							}
							$resp .= "<div class=\"col-4\">
										<button 
											type=\"button\"
											class=\"btn btn-{$class} form-control\"
											onclick=\"saveResolutionPerProductProvider( {$row['product_id']}, {$row['product_provider_id']}, 
												{$final_quantity}, 12, {$movement_type}, '{$row['transfer_block_resolution_id']}', 
												'{$product_resolution_id}' );\"
											{$disabled}
										>
											<i class=\"\">No Recibir <b>{$row['difference']}</b></i>
										</button>
									</div>";
						}
						if( $missing == 0 ){
							$class = ( $resolution_special_permission == 1 ? 'info' : 'light' );
							$movement_type = 3;
							if( $excedent > 0 ){
								$movement_type = 4;
							}
							$resp .= "<div class=\"col-4\">
										<button 
											type=\"button\"
											class=\"btn btn-info form-control\"
											onclick=\"saveResolutionPerProductProvider( {$row['product_id']}, {$row['product_provider_id']}, 
												{$final_quantity}, 12, {$movement_type}, '{$row['transfer_block_resolution_id']}', 
												'{$product_resolution_id}' );\"
											{$disabled}
										>
											<i class=\"\">Se Devuelven <b>{$row['difference']}</b></i>
										</button>
									</div>";
						}
						$resp .= "</div>";
				}
			}
			return $resp;		
		}

		public function insertProductResolution( $case_1, $case_2, $user ){
//return $case_1;
			$this->link->autocommit( false );
		//itera arreglos
			$case_1_array = explode( '|~|', $case_1 );
			foreach ($case_1_array as $key => $value) {
				if( $value != '' && $value != null ){
					$resolution = explode( '~', $value );
					$missing = '0';
					$excedent = '0';
					$doesnt_correspond = '0';
					if( $resolution[3] == '' || $resolution[3] == null ){
						$resolution[3] = '0';
					}
					$sql = "INSERT INTO ";
					if( $resolution[7] != '' ){
						$sql = "UPDATE ";
					}
					$sql .= "ec_productos_resoluciones_tmp SET 
								id_bloque_transferencia_recepcion = {$this->reception_block_id}, 
								id_usuario = {$user}, 
								id_producto = {$resolution[0]},
								conteo_fisico = {$resolution[1]},
								conteo_excedente = {$resolution[2]},
								inventario = {$resolution[3]},
								cantidad_faltante = '{$resolution[4]}', 
								cantidad_excedente = '{$excedent}', 
								cantidad_no_corresponde = '{$doesnt_correspond}',
								recibido = '{$resolution[4]}',
								resuelto = 0";//ec_productos_resoluciones_tmp
					$sql .= ( $resolution[7] != '' ? " WHERE id_producto_resolucion = {$resolution[7]}" : "" );
					
					$this->link->query( $sql ) or die( "Error al insertar resolución a nivel producto 1 : {$sql} {$this->link->error}" );
					$resolution_tmp_id = ( $resolution[7] != '' ? $resolution[7] : $this->link->insert_id );
					if( $resolution[5] != '' && $resolution[5] != null && $resolution[7] == '' ){//&& $resolution[7] == ''  agregado por Oscar 2023
						$transfer_products = str_replace('/', ',', $transfer_products ) ;// explode( '/', $resolution[5] );
						//foreach ($transfer_products as $key => $transfer_product_id ) {
					/*deshabilitado por Oscar 2023 por error de resolucion
							$sql = "UPDATE ec_transferencia_productos 
										SET id_producto_resolucion = {$resolution_tmp_id} 
									WHERE id_transferencia_producto = {$transfer_product_id}";
							$this->link->query( $sql ) or die( "Error al relacionar transferencia producto con resolucion tmp : {$sql} {$this->link->error}" );
//echo $sql;*/

					//implementacion Oscar 2023
						$sql = "INSERT INTO ec_bloques_transferencias_resolucion ( /*1*/id_bloque_transferencia_resolucion, 
							/*2*/id_bloque_transferencia_recepcion, /*3*/id_usuario, /*4*/id_producto, /*5*/id_proveedor_producto, 
							/*6*/piezas_faltantes, /*7*/piezas_sobrantes, /*8*/piezas_no_corresponden, /*9*/piezas_se_quedan, 
							/*10*/piezas_se_regresan, /*11*/piezas_faltaron, /*12*/conteo, /*13*/conteo_excedente, 
							/*14*/diferencia, /*15*/id_producto_resolucion, /*16*/resuelto )
						SELECT
							/*1*/NULL,
							/*2*/{$this->reception_block_id},
							/*3*/{$user},
							/*4*/tp.id_producto_or,
							/*5*/tp.id_proveedor_producto,
							/*6*/( SUM( tp.total_piezas_validacion ) - SUM( tp.total_piezas_recibidas ) ),
							/*7*/0,
							/*8*/0,
							/*9*/0,
							/*10*/0,
							/*11*/0,
							/*12*/{$resolution[1]},
							/*13*/{$resolution[2]},
							/*14*/0,
							/*15*/{$resolution_tmp_id},
							/*16*/0
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_transferencias t
						ON tp.id_transferencia = t.id_transferencia
						LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
						ON btvd.id_transferencia = t.id_transferencia
						LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
						ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
						WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}
						AND tp.id_producto_or = {$resolution[0]}
						AND tp.total_piezas_validacion != tp.total_piezas_recibidas
						GROUP BY tp.id_proveedor_producto";
//echo ( $sql );
						$stm = $this->link->query( $sql ) or die( "Error al insertar el detalle de resolucion en ec_bloques_transferencias_resolucion : {$sql} {$this->link->error}" );
						//}
					}
					if( $resolution[6] != '' && $resolution[6] != null ){
						$resolution_blocks = explode( '/', $resolution[6] );
						foreach ($resolution_blocks as $key => $resolution_block_id ) {
							$sql = "UPDATE ec_bloques_transferencias_resolucion 
										SET id_producto_resolucion = {$resolution_tmp_id} 
									WHERE id_bloque_transferencia_resolucion = {$resolution_block_id}";
							$this->link->query( $sql ) or die( "Error al reloacionar resolución detalle con resolucion tmp : {$sql} {$this->link->error}" );
//echo $sql;
						}
					}
				}
			}

//die( 'here : ' . $case_2 );
			//$case_2_array = explode( '|~|', $case_2 );

			/*foreach ($case_2_array as $key => $value) {
				$resolution = explode( '~', $value );
				if( $resolution[0] != ''  ){
					$missing = '0';
					$excedent = '0';
					$doesnt_correspond = '0';
					$sql = "INSERT INTO ec_productos_resoluciones_tmp SET 
								id_producto_resolucion = NULL, 
								id_bloque_transferencia_recepcion = {$this->reception_block_id}, 
								id_usuario = {$user}, 
								id_producto = {$resolution[0]},
								conteo_fisico = {$resolution[1]},
								conteo_excedente = {$resolution[2]},
								inventario = {$resolution[3]},
								cantidad_faltante = '{$missing}', 
								cantidad_excedente = '{$resolution[4]}', 
								cantidad_no_corresponde = '{$doesnt_correspond}',
								recibido = '{$resolution[4]}', 
								resuelto = '0'";
//die( $sql );
					$this->link->query( $sql ) or die( "Error al insertar resolución a nivel producto 2 : {$sql} {$this->link->error}" );
				}
			}*/
		//commit
			$this->link->autocommit( true );
			return "<h5 style=\"color : green; font-size : 200%;\">Resolucion Guardada exitosamente</h5>
					<br><br>
					<div class=\"center\">
						<button 
							type=\"button\"
							onclick=\"location.reload();\"
							class=\"btn btn-success\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>";
		}

		public function getTransfersByBlock(){
			$sql = "SELECT
					GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids,
					t.id_sucursal_origen AS origin_store_id,
					t.id_sucursal_destino AS destinity_store_id,
					t.id_almacen_origen AS warehouse_origin,
					t.id_almacen_destino AS warehouse_destinity
				FROM ec_bloques_transferencias_recepcion_detalle btrd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btrd.id_bloque_transferencia_recepcion = {$this->reception_block_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar ids de transferencias : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$this->origin_store_id = $row['origin_store_id'];
			$this->destinity_store_id = $row['destinity_store_id'];
			$this->warehouse_origin = $row['warehouse_origin'];
			$this->warehouse_destinity = $row['warehouse_destinity'];
			return $row['transfers_ids'];
		}
	}
?>