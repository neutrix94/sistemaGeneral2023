<?php
	class Transfer
	{
		private $link;
		private $user_id;
		function __construct( $connection, $user_id )
		{
			$this->link = $connection;
			$this->user_id = $user_id;
		}// function insertNewProductValidation( $transfers, $product_id, $product_provider_id, $box, $pack, $piece, ){
		
		public function reasignTransferDetailExcedent( $transfer_product_id, $current_transfer_block ){
			$this->link->autocommit( false );
			$sql = "SELECT cantidad_piezas_validacion AS validated_pieces
					FROM ec_transferencia_productos 
					WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle que se va asignar : {$this->link->error}" );
			$quantity_row = $stm->fetch_assoc();
//echo 'pasa_1<br>';
		//verifica a que transferencia se le asignara el producto
			$transfer_excedent = explode('|', $this->getTransferExcedentByBlock( $current_transfer_block ));
			if( $transfer_excedent[0] != 'ok' ){
				die( "Algo salió mal al recuperar registro de transferencia adicional por modificación de bloques de validación : {$transfer_excedent[0]}" );
			}
//echo 'pasa_2<br>';
			$transfer_excedent_id = $transfer_excedent[1];
			$transfer_excedent_movement_id = $transfer_excedent[2];
//echo "<br>movs : {$transfer_excedent_id} {$transfer_excedent_movement_id}";
			$detail = explode('|', $this->insertTransferDetail( $transfer_excedent_id, $transfer_product_id, $quantity_row['validated_pieces'] ) );
			if( $detail[0] != 'ok' ){
				die( "Algo salió mal al insertar el detalle de transferencia en validación : {$detail[0]}" );
			}
//echo 'pasa_3<br>';
			$new_detail_id  = $detail[1];
		//cambia los escaneos de la transferencia
			$sql = "UPDATE ec_transferencias_validacion_usuarios 
						SET id_transferencia_producto = {$new_detail_id} 
					WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al reasignar los escaneos de validación de transferencias : {$this->link->error}");
			
//echo 'pasa_4<br>';
		//resetea las catidades validadas
			$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_cajas_validacion = 0, 
						cantidad_paquetes_validacion = 0,
						cantidad_piezas_validacion = 0,
						total_piezas_validacion = 0
					WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al resetear validación de transferencias : {$this->link->error}");

//echo 'pasa_5<br>';
		//inserta el movimiento de almacen
			$this->insertTransferMovementDetail( $transfer_excedent_movement_id, $new_detail_id );

//echo 'pasa_6<br>';
			$this->link->autocommit( true );
			return "ok|<div class=\"text-center\">
						<h5>Registro validado para enviar exitosamente.</h5><br><br>
						<button type=\"button\" class=\"btn btn-success\" onclick=\"close_emergent();\">
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>";
		}



		public function insertTransferMovementDetail( $transfer_excedent_movement_id, $new_transfer_detail_id ){
		//verifica si el registro del movimeinto de almacen ya existe
			$sql = "SELECT 
						md.id_movimiento_almacen_detalle AS movement_detail_id
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_transferencias t 
					ON tp.id_transferencia = t.id_transferencia
					LEFT JOIN ec_movimiento_almacen ma
					ON t.id_transferencia = ma.id_transferencia
					LEFT JOIN ec_movimiento_detalle md
					ON ma.id_movimiento_almacen = md.id_movimiento
					AND md.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia_producto = {$new_transfer_detail_id}
					LIMIT 1";
//echo $sql . '<br>';
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar si existe un detalle de movimiento de almacen para el registro adicional por mod bloq val : {$this->link->error}" );
//echo 'here_1<br>';
			//obtiene la cantidad validada
			$sql = "SELECT 
						tp.total_piezas_validacion AS validated_pieces,
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia_producto = {$new_transfer_detail_id}";
//echo $sql;
			$stm_quantity = $this->link->query( $sql ) or die( "Error al consultar la cantidad por insertar en el detalle de movimiento {$this->link->error}" );
			$validated_pieces = $stm_quantity->fetch_assoc();

//echo 'here_2<br>';

			$row = $stm_1->fetch_assoc();
			if( $row['movement_detail_id'] == null || $row['movement_detail_id'] == '' ){//$stm_1->num_rows <= 0
//echo 'here_2.1<br>';
				$sql = "INSERT INTO ec_movimiento_detalle SET
						id_movimiento_almacen_detalle = NULL,
						id_movimiento = {$transfer_excedent_movement_id},
						id_producto = {$validated_pieces['product_id']},
						cantidad = {$validated_pieces['validated_pieces']},
						cantidad_surtida = {$validated_pieces['validated_pieces']},
						id_pedido_detalle = -1,
						id_oc_detalle = -1,
						id_proveedor_producto = {$validated_pieces['product_provider_id']}";
				$stm2 = $this->link->query( $sql ) or die( "Error al consultar el id insertado : {$sql} {$this->link->error}" );
				$stm3 = $this->link->query( "SELECT LAST_INSERT_ID()" ) or die( "Error al consultar el id de detalle mov insertado : {$this->link->error}" );
				$row = $stm3->fetch_row();
//echo $sql . '<br>';
//echo 'here_2.1.1 pasa<br>';
				return "ok|{$row[0]}";
			}else{
//echo 'here_2.2<br>';
				$sql = "UPDATE ec_movimiento_detalle SET
						cantidad = {$validated_pieces['validated_pieces']},
						cantidad_surtida = {$validated_pieces['validated_pieces']}
					WHERE id_movimiento_almacen_detalle = {$row['movement_detail_id']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el detalle de movimiento de almacen : {$sql} {$this->link->error}" );
				
//echo $sql . '<br>';
//echo 'here_2.2.1 pasa<br>';
				return "ok|{$row['movement_detail_id']}";
			}
		}

		function insertTransferDetail( $transfer_excedent_id, $transfer_product_id, $quantity ){
		//verifica si ya existe un registro en el excedente con el mismo proveedor producto
			$sql = "SELECT 
						id_transferencia_producto AS transfer_product_id
					FROM ec_transferencia_productos
					WHERE id_transferencia = {$transfer_excedent_id}
					AND id_proveedor_producto 
					IN (	SELECT 
								ax.id_proveedor_producto
							FROM(	
								SELECT 
									id_proveedor_producto 
								FROM ec_transferencia_productos 
								WHERE id_transferencia_producto = {$transfer_product_id} 
								LIMIT 1
							)ax
					) ";
			$stm = $this->link->query( $sql )or die( "Error al consultar si ya existe el detalle en excedente de transferencia : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
			//inserta el detalle en transferencia producto
				$sql = "INSERT INTO ec_transferencia_productos( 
						/*1*/id_transferencia, /*2*/id_producto_or, 
						/*3*/id_presentacion, /*4*/cantidad_presentacion, 
						/*5*/cantidad, /*6*/id_producto_de, 
						/*7*/referencia_resolucion, /*8*/cantidad_cajas, 
						/*9*/cantidad_paquetes, /*10*/cantidad_piezas, 
						/*11*/id_proveedor_producto, /*12*/cantidad_cajas_surtidas,
						/*13*/cantidad_paquetes_surtidos, /*14*/cantidad_piezas_surtidas, 
						/*15*/total_piezas_surtimiento, /*16*/cantidad_cajas_validacion, 
						/*17*/ cantidad_paquetes_validacion, /*18*/ cantidad_piezas_validacion, 
						/*19*/total_piezas_validacion, /*20*/agregado_surtimiento_validacion )
					SELECT
					/*1*/'{$transfer_excedent_id}',
					/*2*/tp.id_producto_or,
					/*3*/-1,
					/*4*/tp.cantidad_piezas_validacion,
					/*5*/tp.cantidad_piezas_validacion,
					/*6*/tp.id_producto_de,
					/*7*/tp.cantidad_piezas_validacion,
					/*8*/tp.cantidad_cajas_validacion,
					/*9*/tp.cantidad_paquetes_validacion,
					/*10*/tp.cantidad_piezas_validacion,
					/*11*/tp.id_proveedor_producto,
					/*12*/tp.cantidad_cajas_surtidas,
					/*13*/tp.cantidad_paquetes_surtidos,
					/*14*/tp.cantidad_piezas_surtidas,
					/*15*/tp.total_piezas_surtimiento,
					/*16*/tp.cantidad_cajas_validacion,
					/*17*/tp.cantidad_paquetes_validacion,
					/*18*/tp.cantidad_piezas_validacion,
					/*19*/tp.total_piezas_validacion,
					/*20*/'1'
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo registro de detalle de transferencia excedente : {$sql} {$this->link->error}" );
				$new_detail_id  = $this->link->insert_id;
				return "ok|$new_detail_id";
			}else{
				$row = $stm->fetch_assoc();
				$sql = "UPDATE ec_transferencia_productos 
						SET cantidad_piezas_validacion = ( cantidad_piezas_validacion + {$quantity} ),
						total_piezas_validacion = ( total_piezas_validacion + {$quantity} )
						WHERE id_transferencia_producto = {$row['transfer_product_id']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar registro de detalle de transferencia excedente : {$sql} {$this->link->error}" );
				return "ok|{$row['transfer_product_id']}";
			}
		}

		public function getTransferExcedentByBlock( $validation_block_id ){
			$sql = "SELECT 
						t.id_transferencia AS transfer_id,
						ma.id_movimiento_almacen AS movementId
					FROM ec_transferencias t
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON t.id_transferencia = btvd.id_transferencia
					LEFT JOIN ec_movimiento_almacen ma
					ON ma.id_transferencia = t.id_transferencia
					WHERE t.id_tipo = 7 
					AND btvd.id_bloque_transferencia_validacion = {$validation_block_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el id de transferencia por ajuste de bloque : {$this->link->error}" );
			if( $stm->num_rows == 1 ){
				$row = $stm->fetch_assoc();
				return "ok|{$row['transfer_id']}|{$row['movementId']}";
			}else{
				return $this->InsertTransfer( $validation_block_id );
			}
		}

		public function InsertTransfer( $validation_block_id ){
			$sql = "INSERT INTO ec_transferencias (id_usuario,folio,fecha,hora,id_sucursal_origen,id_sucursal_destino,observaciones,
				id_razon_social_venta,id_razon_social_compra,facturable,porc_ganancia,id_almacen_origen,id_almacen_destino,id_tipo,
				id_estado,id_sucursal, titulo_transferencia)
				SELECT
					t.id_usuario,
					'',
					NOW(),
					NOW(),
					t.id_sucursal_origen,
					t.id_sucursal_destino,
					'TRANSFERENCIA POR AJUSTE DE BLOQUE ( GENERADA EN AUTOMÁTICO POR EL SISTEMA )',
					t.id_razon_social_venta,
					t.id_razon_social_compra,
					t.facturable,
					t.porc_ganancia,
					t.id_almacen_origen,
					t.id_almacen_destino,
					7,
					1,
					t.id_sucursal,
					''
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON t.id_transferencia = btvd.id_transferencia
				WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}
				LIMIT 1";

			$stm = $this->link->query( $sql ) or die ( "Error al insertar cabecera de transferencia : {$this->link->error}" );
			$new_id = $this->link->insert_id;

		//genera folio
			$sql_folio = "SELECT 
						CONCAT(s1.prefijo, s2.prefijo, ' ', t.id_transferencia ) AS folio
					FROM ec_transferencias t
					LEFT JOIN sys_sucursales s1
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2
					ON s2.id_sucursal = t.id_sucursal_destino
					WHERE t.id_transferencia = '{$new_id}'";
			$stm_folio = $this->link->query( $sql_folio ) or die( "Error al conslutar el folio de transferencia : {$this->link->error}" );
			$folio = $stm_folio->fetch_assoc();
		//actualiza folio
			$sql_folio = "UPDATE ec_transferencias SET folio = '{$folio['folio']}', id_estado = 2 WHERE id_transferencia = {$new_id}";
			$stm = $this->link->query( $sql_folio ) or die ( "Error al actualizar folio / activar trigger de transferencia : {$this->link->error}" );

		//actualiza folio
			$sql = "UPDATE ec_transferencias SET id_estado = 6 WHERE id_transferencia = {$new_id}";
			$stm = $this->link->query( $sql ) or die ( "Error al actualizar estatus de transferencia : {$this->link->error}" );

		//inserta transferencia en el bloque de validación
			$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle ( id_bloque_transferencia_validacion_detalle, 
						id_bloque_transferencia_validacion, id_transferencia, fecha_alta )
			VALUES ( NULL, {$validation_block_id}, {$new_id}, NOW() );";
			$stm = $this->link->query( $sql ) or die( "Error al insertar la transferencia en el bloque : {$this->link->error}" );
			$stm = $this->link->query( "SELECT id_movimiento_almacen FROM ec_movimiento_almacen WHERE id_transferencia = {$new_id}" ) or die( "Error al obtener el último id insertado : {$this->link->error}" );
			$row = $stm->fetch_row();
			return "ok|{$new_id}|{$row[0]}";
		}
		
	}
?>