<?php
/*version Oscar sept 22*/
	if( isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
		include( '../../../../../config.ini.php' );
		include( '../../../../../conectMin.php' );//sesión
		include( '../../../../../conexionMysqli.php' );
		include( 'Transfer.php' );
		$action = (  isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
		$Blocks = new Blocks( $link, $user_id );
		switch ( $action ) {
			case 'getTransfers':
				$sucursal = ( isset($_GET['sucursal_id']) && $_GET['sucursal_id'] != '' ? $_GET['sucursal_id'] : null );
				$type = ( isset($_GET['type_block_id']) && $_GET['type_block_id'] != '' ? $_GET['type_block_id'] : 'validation' );

				echo $Blocks->getTransfers( $sucursal, $type );
			break;

			case 'infoBeforeRemove': 
				echo $Blocks->infoBeforeRemove( $_GET['transfer_id'], $_GET['validation_block_id'], $_GET['reception_block_id'] );
			break;

			case 'beforeRemoveTransfer' :
				$transfer_id = ( isset( $_GET['transfer_id'] ) ? $_GET['transfer_id'] : $_POST['transfer_id'] );
				$validation_block_id = ( isset( $_GET['validation_block_id'] ) ? $_GET['validation_block_id'] : null );
				$reception_block_id = ( isset( $_GET['reception_block_id'] ) ? $_GET['reception_block_id'] : null );
				echo $Blocks->beforeRemoveTransfer( $transfer_id, $validation_block_id, $reception_block_id );
			break;

			case 'resolve': 
				echo $Blocks->resolve( $_GET['transfer_id'], $_GET['action'], $_GET['quanity'], $_GET['product_provider_id'], $_GET['detail_id'] );
			break;

			case 'getValidationDetail':
				echo $Blocks->getValidationDetail( $_GET['transfer_product_id'], $_GET['type'] );
			break;

			case 'validateBarcode' :
				$permission_box = ( isset( $_GET['permission_box'] ) ? 1 : 0 );
				echo $Blocks->validateBarcode( $_GET['barcode'], $_GET['transfer_detail_id'], $permission_box, $user_id );/*, $excedent_permission = null, 
				$pieces_quantity = null, $permission_box = null, $unique_code = null, $was_find_by_name = 0 */
			break;

			case 'returnTransferProduct':
				echo $Blocks->returnTransferProduct( $_GET['transfer_product_id'], $_GET['ids_to_delete'], 
					$_GET['unique_codes'], $_GET['residue'] );
			break;

			case 'reasignTransferDetail':
				echo $Blocks->reasignTransferDetail( $_GET['transfer_product_id'], $_GET['current_transfer_block'], $user_id, $_GET['excedent_permission'] );
			break;

			case 'reasignTransferDetailExcedent' :
				$Transfer = new Transfer( $link, $user_id );
				echo $Transfer->reasignTransferDetailExcedent( $_GET['transfer_product_id'], $_GET['current_transfer_block'] );
			break;

			case 'unlock_block' :
				$validation_block_id = ( isset( $_GET['validation_block_id'] ) ? $_GET['validation_block_id'] : null );
				$reception_block_id = ( isset( $_GET['reception_block_id'] ) ? $_GET['reception_block_id'] : null );
				echo $Blocks->unlock_block( $validation_block_id, $reception_block_id );
			break;

			default:
				die( "Permission denied on '{$action}' !" );
			break;
		}
	}

	
	class Blocks
	{
		private $link;
		private $user_id;
		
		function __construct( $connection, $user = null )
		{
			$this->link = $connection;
			$this->user_id = $user;
		}

		public function infoBeforeRemove( $transfer_id, $validation_block_id, $reception_block_id = null ){
			$lock = $this->lock_block( $validation_block_id, $reception_block_id );
			$sql = "SELECT 
						t.folio,
						t.fecha AS date,
						s1.nombre AS origin_name,
						s2.nombre AS destinity_name,
						COUNT( tp.id_transferencia_producto ) AS validated_rows_counter
					FROM ec_transferencias t
					LEFT JOIN ec_transferencia_productos tp
					ON t.id_transferencia = tp.id_transferencia
					LEFT JOIN sys_sucursales s1
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2
					ON s2.id_sucursal = t.id_sucursal_destino
					WHERE t.id_transferencia = {$transfer_id}
					AND tp.total_piezas_validacion != 0";
			$stm = $this->link->query( $sql ) or die( "Error al consultar información previa de la transferencia : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return "<div class=\"row\">
						<div class=\"col-6\">
							<h5>Folio de Transferencia : <b style=\"color : green;\">{$row['folio']}</b></h5>
						</div>
						<div class=\"col-6\">
							<h5>Fecha : <b style=\"color : green;\">{$row['date']}</b></h5>
						</div>
						<div class=\"col-6\">
							<h5>Origen : <b style=\"color : green;\">{$row['origin_name']}</b></h5>
						</div>
						<div class=\"col-6\">
							<h5>Destino : <b style=\"color : green;\">{$row['destinity_name']}</b></h5>
						</div>
						<div class=\"col-6\">
							<h5># Productos por asignar : <b style=\"color : green;\">{$row['validated_rows_counter']}</b></h5>
						</div>
					</div>";
			//return "ok|" . json_encode( $row );
		}

		public function insertNoFoundProduct ( $transfer_validation_detail, $validation_block_id ){
				//consulta si hay mas transferencias del bloque para asignarle el detalle a otra transferencia
					$sql = "SELECT 
								t.id_transferencia AS transfer_id
							FROM ec_bloques_transferencias_validacion_detalle btvd
							LEFT JOIN ec_transferencias t 
							ON t.id_transferencia = btvd.id_transferencia
							WHERE btvd.id_bloque_transferencia_validacion = {$validation_block_id}
							AND btvd.id_transferencia NOT IN( {$transfer_validation_detail['transfer_id']} )
							ORDER BY btvd.id_bloque_transferencia_validacion_detalle DESC";
//echo  "<p>8 : consulta si hay mas transferencias del bloque para asignarle el detalle a otra transferencia</p> {$sql} <br><br><br>";

					$stm_3 = $this->link->query( $sql ) or die( "Error al consultar las transferencias del bloque : {$this->link->error}" );
					if( $stm_3->num_rows <= 0 ){
						return 'No hay transferencia del bloque para reasignar este producto, la única opción válida es REGRESAR.';
					}else{
						$row_assign = $stm_3->fetch_assoc();
					//inserta el detalle en la ultima transferencia del bloque
						$sql = "UPDATE ec_transferencia_productos 
									SET id_transferencia = {$row_assign['transfer_id']}
								WHERE id_transferencia_producto = {$transfer_validation_detail['transfer_product_id']}/*{$transfer_product_id}oscr 2023*/";
//echo  "<p>9 : inserta el detalle en la ultima transferencia del bloque</p> {$sql} <br><br><br>";


						$stm_5 = $this->link->query( $sql ) or die( "Error al reasignar el detalle de transferencia : {$sql}{$this->link->error}" );
						
					}
//echo "</div>";
		}

		public function updateTransferRow( $transfer_validation_detail, $transfer, $assign_quantity ){

		//inserta el registro de validación
			$sql = "INSERT INTO ec_transferencias_validacion_usuarios ( id_transferencia_validacion, id_transferencia_producto,
			id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_validadas, cantidad_paquetes_validados, cantidad_piezas_validadas, fecha_validacion, id_status )
			VALUES( NULL, '{$transfer['transfer_product_id']}', '{$this->user_id}', '{$transfer_validation_detail['product_id']}', '{$transfer_validation_detail['product_provider_id']}', 
				'{$transfer_validation_detail['box']}', '{$transfer_validation_detail['pack']}', '{$assign_quantity}', NOW(), 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el registro de validación : {$this->link->error}" );
			$validation_detail_id = $this->link->insert_id;

echo "<h5>Consulta el nuevo total de las piezas validadas en el detalle que se asignó</h5>";
			//consulta cuando quedó por validar
			$sql = "SELECT
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_cajas_validadas ) ) * pp.presentacion_caja ) AS boxes,
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_paquetes_validados ) ) * pp.piezas_presentacion_cluces ) AS packs,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_piezas_validadas ) ) AS pieces,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
							+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) 
							+ tvu.cantidad_piezas_validadas ) 
						) AS total_pieces
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					LEFT JOIN ec_transferencias_validacion_usuarios tvu
					ON tvu.id_transferencia_producto = tp.id_transferencia_producto
					WHERE tp.id_transferencia_producto = {$transfer['transfer_product_id']}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si hay validaciones pendientes : {$this->link->error} " );
			$row = $stm->fetch_assoc();
echo "<h5>Actualiza el nuevo total de las piezas validadas en el detalle de transferencia que se asignó</h5>";
		//actualiza la suma de los productos validados
			$sql = "UPDATE ec_transferencia_productos tp
						SET tp.cantidad_cajas_validacion = {$row['boxes']},
						tp.cantidad_paquetes_validacion = {$row['packs']},
						tp.cantidad_piezas_validacion = {$row['pieces']},
						tp.total_piezas_validacion = {$row['total_pieces']}
					WHERE tp.id_transferencia_producto = {$transfer['transfer_product_id']}";
			$stm = $this->link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia : {$this->link->error}" );
		}

		public function deleteTransferDetailRow( $transfer_detail_id ){
		//
		}

		public function assignmentCalculation( $transfer_validation_detail, $transfer_details, $validation_block_id, $excedent_permission = null ){// $excedent_permission = 1 
echo "<h5>Entra en el calculo de asignación de transferencias </h5>";
			$quantity = 0;
			if( $transfer_validation_detail['piece'] != 0 ){
				$quantity = $transfer_validation_detail['piece'];
			}else if( $transfer_validation_detail['pack'] != 0 ){
				$quantity = $transfer_validation_detail['pieces_per_pack'];
				$transfer_validation_detail['pack'] = 0;
			}else if( $transfer_validation_detail['box'] != 0 ){
				$quantity = $transfer_validation_detail['pieces_per_box'];
				$transfer_validation_detail['box'] = 0;
			}
			//$excedent = 0;
			/*$transfers_total = $transfer_details->num_rows;
			$transfers_counter = 1;
			$more_than_one_transfer = 0;*/
			$transfers_total = $transfer_details->num_rows;
echo "<p>Transferencias encontradas : <b>{$transfers_total}</b><p>";
			while( $transfer = $transfer_details->fetch_assoc() ){//&& $quantity > 0
echo "<p>Recorre transferencia <b style=\"color : green;\">{$transfer['folio']}</b></p>";
				$assign_quantity = 0;
				if( $quantity > 0 && $transfer['pending_to_validate'] != 0 ){
echo "<p style=\"color:green;\">Si entró a condición por asignar porque la cantidad por validar en la transferencia {$transfer['folio']} es diferente de cero ( {$transfer['pending_to_validate']} != 0 ) </p>";
					//piezas surtidas vs piezas_validadas		
					if( $transfer['pending_to_validate'] > $quantity ){
						$aux = ( $transfer['pending_to_validate'] == 0 ? '0' : $transfer['pending_to_validate'] );
echo "<p>La cantidad pendiente de validar es mayor a cantidad por asignar : {$aux} > {$quantity}</p>";
						$assign_quantity = $quantity;
					}
					if( $transfer['pending_to_validate'] == $quantity ){
						$aux = ( $transfer['pending_to_validate'] == 0 ? '0' : $transfer['pending_to_validate'] );
echo "<p>La cantidad pendiente de validar es igual a cantidad por asignar : {$aux} = {$quantity}</p>";
						$assign_quantity = $quantity;
					}
					if( $transfer['pending_to_validate'] < $quantity ){
						$aux = ( $transfer['pending_to_validate'] == 0 ? '0' : $transfer['pending_to_validate'] );
echo "<p>La cantidad pendiente de validar es menor a cantidad por asignar : {$aux} < {$quantity}</p>";
						$assign_quantity = $transfer['pending_to_validate'];
						/*if( $excedent_permission != null 
						&& $transfers_counter == $transfers_total ){
							$assign_quantity = 0;
							$excedent = $quantity;
						}*/
					}
					$aux = ( $assign_quantity == 0 ? '0' : $assign_quantity );
echo "<p>Cantidad asignada a la transferencia {$transfer['folio']} : <b style=\"color : green;\">{$aux}</b> </p>";
					if( $assign_quantity > 0 ){
echo "<h5>La cantidad asignada es mayor a cero y se harán los ajustes correspondientes en la base de datos </h5>";
						$this->updateTransferRow( $transfer_validation_detail, $transfer, $assign_quantity );
						$quantity -= $assign_quantity;
					}
echo "<b style=\"color : green;\">Cantidad no asignada : {$quantity}</b></br><br><br><br>";
				}else{
echo "<p style=\"color:red;\">No entro a condición por asignar porque la cantidad por validar en la transferencia {$transfer['folio']} es igual cero ( {$transfer['pending_to_validate']} = 0 ) </p>";
				}
				$transfers_counter ++;//incrementa contador de detalles de transferencias
				//$quantity -= $excedent;
			}

			/*if( $quantity > 0 && $excedent_permission != null ){
		//obtinen / inserta el id de transferencia excedente
				$excedent_transfer_id = $transfer->getTransferExcedentByBlock( $validation_block_id );
		//inserta el detalle en transferencia de escedente
				$transfer->InsertTransferDetail( $transfer_validation_detail, $quanity, $excedent_transfer_id );
			//resetea la cantidad a cero
				$quantity = 0;
			}*/


			if( $quantity > 0 ){
echo "<h5>Esta cantidad ( {$quantity} ) se aparta para transferencia</h5>";
				//inserta el registro de validación
				$sql = "UPDATE ec_transferencias_validacion_usuarios SET 
							cantidad_cajas_validadas = {$transfer_validation_detail['box']},
							cantidad_paquetes_validados = {$transfer_validation_detail['pack']},
							cantidad_piezas_validadas = {$quantity}
						WHERE id_transferencia_validacion = {$transfer_validation_detail['transfer_validation_id']}";
				
			}else{
echo "<h5>Entra en condición de eliminar el registro asignado</h5>";
				$sql = "DELETE FROM ec_transferencias_validacion_usuarios WHERE id_transferencia_validacion = {$transfer_validation_detail['transfer_validation_id']}";
			}
			$stm = $this->link->query( $sql ) or die( "error|Error al insertar el registro de validación : {$this->link->error}" );

echo "<h5>Consulta la nueva suma de los escaneos a nivel detalle de validacion de Usuarios que no fueron reasignados";
			//consulta cuando quedó por validar
			$sql = "SELECT
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_cajas_validadas ) ) * pp.presentacion_caja ) AS boxes,
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_paquetes_validados ) ) * pp.piezas_presentacion_cluces ) AS packs,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_piezas_validadas ) ) AS pieces,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
							+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) 
							+ tvu.cantidad_piezas_validadas ) 
						) AS total_pieces
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					LEFT JOIN ec_transferencias_validacion_usuarios tvu
					ON tvu.id_transferencia_producto = tp.id_transferencia_producto
					WHERE tp.id_transferencia_producto = {$transfer_validation_detail['transfer_product_id']}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si hay validaciones pendientes : {$this->link->error} " );
			$row = $stm->fetch_assoc();

//msgecho " ( {$row['total_pieces']} )</h5>";
//msgecho "<h5>Actualiza el nuevo total de las piezas validadas en el detalle de transferencia que se asignó ( Detalle de Transferencia )  ( {$row['total_pieces']} ) </h5>";
		//actualiza la suma de los productos validados
			$sql = "UPDATE ec_transferencia_productos tp
						SET tp.cantidad_cajas_validacion = {$row['boxes']},
						tp.cantidad_paquetes_validacion = {$row['packs']},
						tp.cantidad_piezas_validacion = {$row['pieces']},
						tp.total_piezas_validacion = {$row['total_pieces']}
					WHERE tp.id_transferencia_producto = {$transfer_validation_detail['transfer_product_id']}";
			$stm = $this->link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia : {$this->link->error}" );
		}

		public function asignTransferDetail( $transfer_validation_detail, $validation_block_id, $excedent_permission = null ){
		//busca las transferencias del bloque que tengan el producto
			$sql = "SELECT
						ax.transfer_product_id,
						ax.pending_to_validate,
						ax.product_id,
						ax.product_provider_id,
						ax.folio
					FROM(
						SELECT 
							tp.id_transferencia_producto AS transfer_product_id,
							( tp.cantidad - tp.total_piezas_validacion ) AS pending_to_validate,
							tp.id_producto_or AS product_id,
							tp.id_proveedor_producto AS product_provider_id,
							t.folio
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_transferencias t
						ON tp.id_transferencia = t.id_transferencia
						LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
						ON btvd.id_transferencia = t.id_transferencia
						WHERE tp.id_proveedor_producto = {$transfer_validation_detail['product_provider_id']}
						AND btvd.id_bloque_transferencia_validacion = {$validation_block_id}
						AND btvd.id_transferencia NOT IN( {$transfer_validation_detail['transfer_id']} )
						GROUP BY tp.id_transferencia_producto
					)ax
					GROUP BY ax.transfer_product_id
					ORDER BY ax.transfer_product_id";/*ax.pending_to_validate*/

				//echo $sql;

			$stm = $this->link->query( $sql ) or die( "Error al consultar las transferencias del bloque que contienen al proveedor producto : {$this->link->error}" );
			$details_number = $stm->num_rows;
			if( $details_number > 0 ){
echo "<div style=\"background-color : rgba( 0,0,225,.2 );\">
//msg		<h5>Si encontró <b style=\"color:green;\">{$details_number}</b> transferencias que tienen al proveedor producto en el mismo bloque y va a calcular la asignación</h5>";
				$this->assignmentCalculation( $transfer_validation_detail, $stm, $validation_block_id, $excedent_permission );
			
			}else{
echo "<div style=\"background-color : rgba( 225,0,0,.3 );\">
//msg		<h5>No encontró transferencias que tienen al proveedor producto en el mismo bloque</h5>";
				$this->insertNoFoundProduct( $transfer_validation_detail, $validation_block_id );
			}
echo "</div>";
				
		}

		public function reasignTransferDetail( $transfer_product_id, $validation_block_id, $user, $excedent_permission = null ){
echo "<div class=\"group_card\" style=\"margin : 20px;\">";
		//busca los detalles de validacion relacionados a la transferencia
			$sql = "SELECT
						tvu.id_transferencia_validacion AS transfer_validation_id,
						tvu.cantidad_cajas_validadas AS box,
						pp.presentacion_caja AS pieces_per_box,
						tvu.cantidad_paquetes_validados AS pack,
						pp.piezas_presentacion_cluces AS pieces_per_pack,
						tvu.cantidad_piezas_validadas AS piece,
						tvu.id_proveedor_producto AS product_provider_id,
						tvu.id_producto AS product_id,
						tp.id_transferencia AS transfer_id,
						tp.id_transferencia_producto AS transfer_product_id,
						( ( tvu.cantidad_cajas_validadas * pp.presentacion_caja )
						+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces )
						+ tvu.cantidad_piezas_validadas ) AS total_validated_pieces,
						t.folio
					FROM ec_transferencias_validacion_usuarios tvu
					LEFT JOIN ec_transferencia_productos tp
					ON tvu.id_transferencia_producto = tp.id_transferencia_producto
					LEFT JOIN ec_transferencias t 
					ON t.id_transferencia = tp.id_transferencia
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tvu.id_proveedor_producto
					WHERE tvu.id_transferencia_producto = {$transfer_product_id}";
echo $sql;
//echo  "<p>1 : Consulta detalles de la validacion : </p>{$sql}<br><br><br>";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de transferencia producto por reasignar : {$this->link->error}" );	
			$details_counter = 0;
		//itera cada uno de los detalles y va asignando las piezas
			while( $transfer_validation_detail = $stm->fetch_assoc() ){
				$details_counter ++;
/*echo "<div class=\"group_card\" style=\"margin : 20px;\">
		<h3>Entra a la asignación del detalle de validación<b>{$details_counter}</b> ( {$transfer_validation_detail['folio']} - {$transfer_validation_detail['transfer_validation_id']} )</h3>
		<p class=\"text_middle\">Cajas por validar : <b>{$transfer_validation_detail['box']}</b></p>
		<p class=\"text_middle\">Piezas por caja : <b>{$transfer_validation_detail['pieces_per_box']}</b></p>
		<p class=\"text_middle\">Paquetes por validar : <b>{$transfer_validation_detail['pack']}</b></p>
		<p class=\"text_middle\">Piezas por paquete : <b>{$transfer_validation_detail['pieces_per_pack']}</b></p>
		<p class=\"text_middle\">Cantidad por reasignar : <b>{$transfer_validation_detail['total_validated_pieces']}</b></p><br>";*/
echo "<div class=\"group_card\" style=\"margin : 20px;\">
//msg		<h3>Entra a la asignación del detalle de validación - <b>{$details_counter}</b> ( {$transfer_validation_detail['folio']} - {$transfer_validation_detail['transfer_validation_id']} )</h3>
//msg		<p class=\"text_middle\">Cantidad por reasignar : <b>{$transfer_validation_detail['total_validated_pieces']} piezas </b></p><br>";
				$this->asignTransferDetail( $transfer_validation_detail, $validation_block_id, $excedent_permission );
echo "</div>";

			}//fin de while anidado
			//$this->link->autocommit( true );//commit
echo "</div>";
			return "ok|El producto fue reasignado exitosamente.";
		}
 
		public function returnTransferProduct( $transfer_product_id, $ids_to_delete, $unique_codes, $residue = 0 ){
			$this->link->autocommit( false );
		//si tiene residuo
			if( $residue > 0 ){
				$sql = "SELECT";
			}
		//elimina los detalles de la validacion
			if( $ids_to_delete != '' ){
				$sql = "DELETE FROM ec_transferencias_validacion_usuarios WHERE id_transferencia_validacion IN( {$ids_to_delete} )";
				$this->link->query( $sql ) or die( "Error al eliminar los registros de validacion de usuarios : {$sql} {$this->link->error}" );
			}
		//elimina los codigos únicos
			$array_uniques_codes = explode(',', $unique_codes );
			$uniques_codes = '';
			foreach ($array_uniques_codes as $key => $unique) {
				$uniques_codes .= ( $uniques_codes != '' ? ',' : '' );
				$uniques_codes .= "'{$unique}'";
			}
				$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE codigo_unico IN( {$uniques_codes} )";
				$this->link->query( $sql ) or die( "Error al eliminar los registros de códigos únicos en validacion : {$sql} {$this->link->error}" );
		//consulta cuando quedó por validar
			$sql = "SELECT
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_cajas_validadas ) ) * pp.presentacion_caja ) AS boxes,
						( SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_paquetes_validados ) ) * pp.piezas_presentacion_cluces ) AS packs,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, tvu.cantidad_piezas_validadas ) ) AS pieces,
						SUM( IF( tvu.id_transferencia_validacion IS NULL,0, ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
							+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) 
							+ tvu.cantidad_piezas_validadas ) 
						) AS total_pieces
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					LEFT JOIN ec_transferencias_validacion_usuarios tvu
					ON tvu.id_transferencia_producto = tp.id_transferencia_producto
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si hay validaciones pendientes : {$this->link->error} " );
			$row = $stm->fetch_assoc();
		//actualiza la suma de los productos validados
			$sql = "UPDATE ec_transferencia_productos tp
						SET tp.cantidad_cajas_validacion = {$row['boxes']},
						tp.cantidad_paquetes_validacion = {$row['packs']},
						tp.cantidad_piezas_validacion = {$row['pieces']},
						tp.total_piezas_validacion = {$row['total_pieces']}
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
			$this->link->query( $sql ) or die( "Error al resetear la validacion de : {$this->link->error} {$sql}" );
			
			$this->link->autocommit( true );
			return 'ok';
		}

		public function validateBarcode( $barcode, $transfer_detail_id, $permission_box = 0, $user ){
			//if( $permission_box == null ){
				$sql = "SELECT 
							id_codigo_validacion
						FROM ec_codigos_validacion_cajas
						WHERE codigo_barras = '{$barcode}'";
				$stm = $this->link->query( $sql ) or die( "error|Error al consultar si es código de validación de caja : {$this->link->error}" );
				if( $stm->num_rows == 1 ){
					$resp = 'is_box_code|';
					$resp .= '<div>';
						$resp .= '<div class="row">';
							$resp .= '<div class="col-2"></div>';
							$resp .= '<div class="col-8">';
								$resp .= '<label for="tmp_sell_barcode">El código de barras del sello es válido, para continuar escaneé el código de barras de la caja : </label>';
								$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control" onkeyup="validateBarcode( ' . $transfer_detail_id . ', this, event, null, null, 1 );"><br>';
								$resp .= '<button type="button" class="btn btn-success form-control"';
								$resp .= ' onclick="validateBarcode( ' . $transfer_detail_id . ', \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
									$resp .= '<i class="icon-ok-circle">Aceptar</i>';
								$resp .= '</button><br><br>';
								$resp .= '<button type="button" class="btn btn-danger form-control"';
								$resp .= ' onclick="close_emergent_3();">';
									$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
								$resp .= '</button>';
							$resp .= '</div>';
						$resp .= '</div>';
					$resp .= '</div>';
					return $resp;
				}
			//}
	//verifica si el código de barras existe
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.id_producto AS product_id
				FROM ec_proveedor_producto pp
				WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
				OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
				OR pp.codigo_barras_caja_2 = '{$barcode}')";
		$stm1 = $this->link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : {$this->link->error}" );
		
		if( $stm1->num_rows <= 0 ){
			$resp = "info|<div class=\"row\">
						<div class=\"col-2\"></div>
						<div class=\"col-10\">
							<h4>El código de barras no fue encontrado en ningún producto</h4>
							<div class=\"row\">
								<div class=\"col-3\"></div>
								<div class=\"col-6\">
									<button
										type=\"button\"
										class=\"btn btn-warning\"
										onclick=\"close_emergent_3();\"
									>
										<i class=\"icon-ok-circle\">Aceptar</i>
									</button>
								</div>
							</div>
						</div>
					</div>";
			return $resp;
		}
		//$first_data = $stm1->fetch_assoc();

	//verifica que el proveedor producto exista
		$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
					OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 ) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					tp.cantidad_cajas,
					tp.cantidad_paquetes,
					tp.cantidad_piezas,
					tp.cantidad,
					SUM( IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							( tru.cantidad_cajas_recibidas * pp.presentacion_caja ) 
						) 
					) AS validated_boxes,
					pp.presentacion_caja AS box_pieces_quantity,
					SUM(IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							( tru.cantidad_paquetes_recibidos * pp.piezas_presentacion_cluces ) 
						) 
					) AS validated_packs,
					SUM(IF( tru.id_transferencia_recepcion IS NULL, 
							0, 
							tru.cantidad_piezas_recibidas 
						) 
					) AS validated_pieces
				/*FROM ec_transferencias_validacion_usuarios tvu
				ON tp.id_transferencia_producto = tvu.id_transferencia_producto*/
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_transferencias_recepcion_usuarios tru 
				ON tp.id_transferencia_producto = tru.id_transferencia_producto
				WHERE tp.id_transferencia_producto IN( {$transfer_detail_id} )
				AND ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
				GROUP BY tp.id_transferencia_producto";
		
		$stm2 = $this->link->query( $sql ) or die( "error|Error al buscar el producto por código de barras :  {$this->link->error}" );
			
	//verifica si el producto existe en la transferencia
		if( $stm2->num_rows <= 0 ){
			//$inform = $stm3->fetch_assoc();
			//$resp = 'exception|<br/><h3 class="inform_error">El producto no pertenece a esta(s) Transferencia(s).<br />Este producto tiene que ser devuelto a Matriz</h3>';	
			$resp = 'message_info|<br/><h3 class="inform_error">El producto no es el correcto, verifique y vuelva a intentar<br />';
				//$resp .= '<b class="red">Aparte este producto, NO ACOMODAR!</b></h3>'; 
			$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= '<button class="btn btn-warning form-control" onclick="close_emergent_3( )">';
					$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				$resp .= '</button>';
			$resp .= "</div></div><br/><br/>";
			return $resp;
		}

		$row = $stm2->fetch_assoc();
		if( $row['piece'] == 1 && $pieces_quantity == null
			&& $excedent_permission == null && $permission_box == '' ){
			$resp = 'pieces_form|<div class="row">';
					$resp .= '<div><h5>Ingrese el número de Piezas : </h5></div>';
					$resp .= '<div class="col-2"></div>';
					$resp .= '<div class="col-8">';
						$resp .= '<input type="number" class="form-control" id="pieces_quantity_emergent">';
						$resp .= '<button type="button" class="btn btn-success form-control"';
						$resp .= ' onclick="setPiecesQuantity();">';
							$resp .= 'Aceptar';
						$resp .= '</button>';
						$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent_3();">';
							$resp .= '<i class="icon-ok-circle">Cancelar</i>';
						$resp .= '</button>';
					$resp .= '</div>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}
		if( $permission_box == 0 && $row['box'] == 1 && $row['box_pieces_quantity'] > 1 ){
		//return "message_info|1 : {$permission_box} - {$row['box']}";
			$resp = 'message_info|<div class="row">';
				$resp .= '<div class="col-2"></div>';
				$resp .= '<div class="col-8"><h5>Para escanear la caja primero escaneé el sello de caja, si este esta roto escaneé los paquetes </h5>';
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="close_emergent_3( );document.getElementById( \'product_seeker\' ).select();">';
						$resp .= 'Aceptar';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}else if( $permission_box != null && $row['box'] != 1 ){
		//return "message_info|2 : {$permission_box} - {$row['box']}";
				$resp = 'is_not_a_box_code|';
				$resp .= '<div>';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-2"></div>';
						$resp .= '<div class="col-8">';
							$resp .= '<label for="tmp_sell_barcode">El código de barras no pertenece a una caja, para continuar escaneé el código de barras de la caja : </label>';
							$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control"><br>';
							$resp .= '<button type="button" class="btn btn-success form-control"';
							$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
								$resp .= '<i class="icon-ok-circle">Aceptar</i>';
							$resp .= '</button><br>';
							$resp .= '<button type="button" class="btn btn-danger form-control"';
							$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
								$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
							$resp .= '</button>';
						$resp .= '</div>';
					$resp .= '</div>';
				$resp .= '</div>';
				return $resp;
		}

		if( $pieces_quantity != null ){
			$row['piece'] = $pieces_quantity;
		}
		if( $row['box'] == 1 ){
			return 'box';
		}
		return 'ok';
		//return insertProductReception( $row, $user, $transfers, $excedent_permission, $was_find_by_name, $barcode, $unique_code, $link );
	}

		public function getValidationDetail( $transfer_product_id, $type ){
			$resp = "";
			$sql = "SELECT
						tvu.id_transferencia_validacion AS transfer_validation_id,
						tvu.cantidad_cajas_validadas AS validated_boxes,
						pp.presentacion_caja AS pieces_per_box,
						tvu.cantidad_paquetes_validados AS validated_packs,
						pp.piezas_presentacion_cluces AS pieces_per_pack,
						tvu.cantidad_piezas_validadas AS validated_pieces,
						pp.codigo_barras_pieza_1 AS piece_barcode_1,
						pp.codigo_barras_pieza_2 AS piece_barcode_2,
						pp.codigo_barras_pieza_3 AS piece_barcode_3,
						pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1,
						pp.codigo_barras_presentacion_cluces_2 AS pack_barcode_2,
						pp.codigo_barras_caja_1 AS box_barcode_1,
						pp.codigo_barras_caja_2 AS box_barcode_2
					FROM ec_transferencias_validacion_usuarios tvu
					LEFT JOIN ec_transferencia_productos tp
					ON tp.id_transferencia_producto = tvu.id_transferencia_producto
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tvu.id_proveedor_producto
					WHERE tp.id_transferencia_producto IN( {$transfer_product_id} )
					ORDER BY tvu.cantidad_cajas_validadas, tvu.cantidad_paquetes_validados DESC";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle por devolver : {$this->link->error} {$sql}" );
			//echo $sql;
			$validated_boxes = 0;
			$validated_packs = 0;
			$validated_pieces = 0;
			$validated_total = 0;
			$pieces_per_box = 0;
			$pieces_per_pack = 0;
			$pieces_by_id = '';
			$packs_by_id = '';
			$boxes_by_id = '';
			while( $row = $stm->fetch_assoc() ){
				$validated_boxes += $row['validated_boxes'];
				$validated_packs += $row['validated_packs'];
				$validated_pieces += $row['validated_pieces'];
				$pieces_per_box = $row['pieces_per_box'];
				$pieces_per_pack = $row['pieces_per_pack'];
				$validated_total += ( $row['validated_boxes'] * $row['pieces_per_box'] ) + ( $row['validated_packs'] * $row['pieces_per_pack'] ) + $row['validated_pieces'];
				if( $row['validated_boxes'] == 1 ){
					$boxes_by_id .= ( $boxes_by_id != '' ? ',' . $row['transfer_validation_id'] : $row['transfer_validation_id'] );
				}else if( $row['validated_packs'] == 1 ){
					$packs_by_id .= ( $packs_by_id != '' ? ',' . $row['transfer_validation_id'] : $row['transfer_validation_id'] );
				}else if( $row['validated_pieces'] > 0 ){
					$pieces_by_id .= ( $pieces_by_id != '' ? ',' . $row['transfer_validation_id'] : $row['transfer_validation_id'] );
				}
			}
			$resp .= "<div class=\"row\">
						<div class=\"col-2\"></div>
						<div class=\"col-8\">
							<input 
								type=\"text\" 
								id=\"product_seeker\" 
								class=\"form-control\"
								placeholder=\"Escanear códigos de barras\"
								onkeyup=\"validateBarcode( {$transfer_product_id}, this, event );\"
								style=\"margin-top : 10px;\"
							>
						</div>
						<div class=\"col-12\">
							<table class=\"table table-bordered\">
								<thead>
									<tr style=\"box-shadow : 1px 1px 10px rgba( 0,0,0,.5 ); background-color : rgba( 225,0,0,.8 ); color : white;\">
										<th>Cantidad</th>
										<th>Descripción</th>
										<th>Regresado</th>
										<th>Por regresar</th>
									</tr>
								</thead>
								<tbody>";
			//cajas
				$resp .= "<tr id=\"boxes_return\" style=\"box-shadow : 1px 1px 10px rgba( 0,0,0,.5 );\">
						<td class=\"text-center\" id=\"boxes_return_quantity\">{$validated_boxes}</td>
						<td class=\"\">Caja con {$pieces_per_box} piezas</td>
						<td class=\"text-center\" id=\"boxes_returned_quantity\">0</td>
						<td class=\"text-center\" id=\"boxes_to_return\">{$validated_boxes}</td>
						<td class=\"no_visible\" id=\"return_pieces_per_box\">{$pieces_per_box}</td>
						<td class=\"no_visible\" id=\"boxes_ids\">{$boxes_by_id}</td>
					</tr>
					<tr>
						<td colspan=\"1\" align=\"right\" style=\"vertical-align: middle;\">
							Códigos Únicos : 
						</td>
						<td colspan=\"3\">
							<textarea id=\"boxes_unique_codes\" style=\"width : 100%;\" readonly></textarea>
						</td>
					</tr>";
			
			//paquetes
				$resp .= "<tr id=\"packs_return\" style=\"box-shadow : 1px 1px 10px rgba( 0,0,0,.5 );\">
						<td class=\"text-center\" id=\"packs_return_quantity\">{$validated_packs}</td>
						<td>Paquete con {$pieces_per_pack} piezas</td>
						<td class=\"text-center\" id=\"packs_returned_quantity\">0</td>
						<td class=\"text-center\" id=\"packs_to_return\">{$validated_packs}</td>
						<td class=\"no_visible\" id=\"return_pieces_per_pack\">{$pieces_per_pack}</td>
						<td class=\"no_visible\" id=\"packs_ids\">{$packs_by_id}</td>
					</tr>
					<tr>
						<td colspan=\"1\" align=\"right\" style=\"vertical-align: middle;\">
							Códigos Únicos : 
						</td>
						<td colspan=\"3\">
							<textarea id=\"packs_unique_codes\" style=\"width : 100%;\" readonly></textarea>
						</td>
					</tr>";

			//piezas
				$resp .= "<tr id=\"pieces_return\" style=\"box-shadow : 1px 1px 10px rgba( 0,0,0,.5 );\">
						<td class=\"text-center\" id=\"pieces_return_quantity\">{$validated_pieces}</td>
						<td>Piezas</td>
						<td class=\"text-center\" id=\"pieces_returned_quantity\">0</td>
						<td class=\"text-center\" id=\"pieces_to_return\">{$validated_pieces}</td>
						<td class=\"no_visible\" id=\"pieces_ids\">{$pieces_by_id}</td>
					</tr>";
			//}
			$resp .= "	</tbody>
						<tfoot>
							<tr style=\"background-color : silver;\">
								<td colspan=\"2\" align=\"right\";> Totales</td>
								<td id=\"total_pieces_returned\" class=\"text-center\">0</td>
								<td id=\"total_pieces_to_return\" class=\"text-center\">{$validated_total}</td>
								<td id=\"total_pieces_to_return_origin\" class=\"no_visible\">{$validated_total}</td>
							</tr>
						</tfoot>
					</table>
					</div>
					<br><br>
					<div class=\"row\">
						<div class=\"col-2\"></div>
						<div class=\"col-3\">
							<button 
								type=\"button\" 
								onclick=\"saveProductTransferReturn( '{$transfer_product_id}', '{$type}' );\"
								class=\"btn btn-success form-control\"
							>
								<i class=\"\">Guardar y salir</i>
							</button>
						</div>
						<div class=\"col-2\"></div>
						<div class=\"col-3\">
							<button 
								type=\"button\" 
								onclick=\"close_emergent_2();\"
								class=\"btn btn-danger form-control\"
							>
								<i class=\"\">Cancelar y salir</i>
							</button>
						</div>
					</div>
				</div>";
			return $resp;
		}

		public function getSucursales( $sucursal = null ){
			$resp = "";
			$sql = "SELECT
						s.id_sucursal AS sucursal_id,
						s.nombre AS sucursal_name
					FROM sys_sucursales s 
					WHERE s.id_sucursal > 0";
			$sql .= ( $sucursal != null && $sucursal > 1 ? " AND s.id_sucursal = {$sucursal}" : "" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar las sucursales : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {//selected
				$resp .= "<option value=\"{$row['sucursal_id']}\"" . ( $row['sucursal_id'] == $sucursal ? " selected" : "" ) . ">{$row['sucursal_name']}</option>";
			}
			return $resp;
		}

		public function getTransfers( $sucursal_id = null, $type = 'validation' ){
			if( $type == 'validation' ){
//echo 'one';
				$sql = "SELECT 
						t.id_transferencia AS transfer_id,
						t.folio,
						IF( btvd.id_bloque_transferencia_validacion_detalle IS NULL, 
							'S/B', 
							btvd.id_bloque_transferencia_validacion_detalle 
						) AS validation_block_detail_id,
						IF( btv.id_bloque_transferencia_validacion IS NULL,
							'S/B',
							btv.id_bloque_transferencia_validacion
						) AS validation_block_id,
						IF( btrd.id_bloque_transferencia_recepcion IS NULL, 
							'S/B', 
							btrd.id_bloque_transferencia_recepcion 
						) AS block_of_blocks_detail_id,
						s1.nombre AS sucursal_origin,
						s2.nombre AS sucursal_destinity,
						tt.nombre AS transfer_type
					FROM ec_bloques_transferencias_validacion_detalle btvd 
					LEFT JOIN ec_transferencias t
					ON btvd.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion btv
					ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btv.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion btr
					ON btr.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
					LEFT JOIN sys_sucursales s1
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2
					ON s2.id_sucursal = t.id_sucursal_destino
					LEFT JOIN ec_tipos_transferencias tt
					ON tt.id_tipo_transferencia = t.id_tipo
					WHERE t.id_transferencia > 0
					AND ( t.id_estado BETWEEN 3 AND 6 )
					AND btvd.id_transferencia IS NOT NULL"; 
					$sql .= ( $sucursal_id != null ? " AND t.id_sucursal_destino = {$sucursal_id}" : "" );
					$sql .= " GROUP BY t.id_transferencia";
			}else{
//echo 'two';
			//consulta de bloques
				$sql = "SELECT 
						GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfer_id,
						GROUP_CONCAT( t.folio SEPARATOR '<br>') AS folio,
						IF( btvd.id_bloque_transferencia_validacion_detalle IS NULL, 
							'S/B', 
							btvd.id_bloque_transferencia_validacion_detalle 
						) AS validation_block_detail_id,
						IF( btv.id_bloque_transferencia_validacion IS NULL,
							'S/B',
							btv.id_bloque_transferencia_validacion
						) AS validation_block_id,
						IF( btrd.id_bloque_transferencia_recepcion IS NULL, 
							'S/B', 
							btrd.id_bloque_transferencia_recepcion 
						) AS block_of_blocks_detail_id,
						s1.nombre AS sucursal_origin,
						s2.nombre AS sucursal_destinity,
						btr.id_bloque_transferencia_recepcion AS reception_block_id
					FROM ec_bloques_transferencias_validacion_detalle btvd 
					LEFT JOIN ec_transferencias t
					ON btvd.id_transferencia = t.id_transferencia
					LEFT JOIN ec_bloques_transferencias_validacion btv
					ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
					ON btv.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
					LEFT JOIN ec_bloques_transferencias_recepcion btr
					ON btr.id_bloque_transferencia_recepcion = btrd.id_bloque_transferencia_recepcion
					LEFT JOIN sys_sucursales s1
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2
					ON s2.id_sucursal = t.id_sucursal_destino
					WHERE btvd.id_transferencia > 0
					AND t.id_estado = 8
					AND btr.id_bloque_transferencia_recepcion IS NOT NULL";
					$sql .= ( $sucursal_id != null ? " AND t.id_sucursal_destino = {$sucursal_id}" : "" );
					$sql .= " GROUP BY btv.id_bloque_transferencia_validacion";
			}
			//echo "<textarea>$sql</textarea>";
			$stm = $this->link->query( $sql ) or die( "Error al consultar transferencias : {$this->link->error}" );
			$resp = "";
			$counter = 0;
			$tabindex = 0;
			$reception_block_counter = "";
			$reception_block = "";
			$background_color = "";
			$colors = array( 'red', 'black' );
			$color = "";
			while ( $row = $stm->fetch_assoc() ) {
				if( $type == 'validation' ){
					if( $reception_block != $row['validation_block_id']  ){
						$reception_block = $row['validation_block_id'];
						$reception_block_counter ++;
						if( $reception_block_counter % 2 == 0 ){
							$background_color = "#f0c49b";
						}else{
							$background_color = "rgba( 0, 0, 0, .3 )";
						}
					}
					/*if(  ){

					}*/
				}else{
					if( $reception_block != $row['reception_block_id']  ){
						$reception_block = $row['reception_block_id'];
						$reception_block_counter ++;
						if( $reception_block_counter % 2 == 0 ){
							$background_color = "#f0c49b";
						}else{
							$background_color = "rgba( 0, 0, 0, .3 )";
						}
					}
				}
				$tabindex ++;
				$resp .= $this->buildTransferRow( $row, $counter, $tabindex, $type, $background_color, $color );
				$counter ++;
			}
			return $resp;
		}
	
		public function buildTransferRow( $row, $counter, $tabindex, $type, $background_color, $color ){//{$row['validation_block_detail_id']}
//echo $type;
			$row['reception_block_id'] = ( $row['reception_block_id'] == null || $row['reception_block_id'] == '' ? 'null' : $row['reception_block_id'] );
			$resp = "<tr style=\"background-color : {$background_color}; color : {$color};\">
						<td id=\"transfer_row_1_{$counter}\" class=\"text-center\">{$row['validation_block_id']}</td>
						<td id=\"transfer_row_2_{$counter}\" class=\"text-center" . ( $type == 'validation' ? ' no_visible' : '' ) . "\">{$row['block_of_blocks_detail_id']}</td>
						<td id=\"transfer_row_3_{$counter}\">{$row['folio']}</td>
						<td>{$row['transfer_type']}</td>
						<td id=\"transfer_row_4_{$counter}\" class=\"no_visible\">{$row['sucursal_origin']}</td>
						<td id=\"transfer_row_5_{$counter}\" class=\"no_visible\">{$row['sucursal_destinity']}</td>
						<td id=\"transfer_row_6_{$counter}\" class=\"text-center\">
							<button 
								id=\"btn_del_{$counter}\" tabindex=\"{$tabindex}\"
								type=\"button\" 
								onclick=\"before_remove_transfer( '{$row['transfer_id']}', '{$row['validation_block_id']}', {$row['reception_block_id']} );\"
								class=\"btn btn-danger\">
								<i>X</i>
							</button>
						</td>
					</tr>";
					//echo $resp;
			return $resp;
		}

		public function lock_block( $validation_block_id = null, $reception_block_id = null ){
			if( $validation_block_id != null ){
				$sql = "UPDATE ec_bloques_transferencias_validacion 
							SET bloqueado = '1' 
						WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al bloquear bloque de validación transferencia: {$this->link->error}" );
				
				$sql = "UPDATE ec_sesiones_dispositivos_validacion_transferencias 
							SET bloqueada = '1' 
						WHERE id_bloque_validacion = {$validation_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al bloquear sesiones de validación transferencia: {$this->link->error}" );
			}

			if( $reception_block_id != null ){
				$sql = "UPDATE ec_bloques_transferencias_recepcion 
						SET bloqueado = '1' 
					WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al bloquear bloque de recepción transferencia: {$this->link->error}" );
				
				$sql = "UPDATE ec_sesiones_dispositivos_recepcion_transferencias 
							SET bloqueada = '1' 
						WHERE id_bloque_recepcion = {$reception_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al bloquear sesiones de recepción transferencia: {$this->link->error}" );
			}
			return 'ok';
		}

		public function unlock_block( $validation_block_id, $reception_block_id = null ){
			if( $validation_block_id != null ){
				$sql = "UPDATE ec_bloques_transferencias_validacion 
							SET bloqueado = '0' 
						WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al desbloquear bloque de validación transferencia: {$this->link->error}" );
				
			}

			if( $reception_block_id != null ){
				$sql = "UPDATE ec_bloques_transferencias_recepcion 
						SET bloqueado = '0' 
					WHERE id_bloque_transferencia_recepcion = {$reception_block_id}";
				$stm = $this->link->query( $sql ) or die( "Error al desbloquear bloque de recepción transferencia: {$this->link->error}" );
			}
			return 'ok';
		}

		public function beforeRemoveTransfer( $transfer_id, $validation_block_id = null, $reception_block_id = null ){
		//	echo "rec : {$reception_block_id}";
			$type_action = '';
			if( $reception_block_id == null ){
				$type_action = 'validation';
				$sql = "SELECT
						tp.id_transferencia_producto AS transfer_product_id,
						CONCAT( p.nombre, ' <b>MODELO : ', pp.clave_proveedor, 
								' ( Caja con ' , pp.presentacion_caja , 'pzas)</b>' 
						) AS product_name,
						tp.cantidad_cajas_validacion AS validated_boxes,
						tp.cantidad_paquetes_validacion AS validated_packs,
						tp.total_piezas_validacion AS total_validated_pieces,
						pp.id_proveedor_producto AS product_provider_id
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON tp.id_producto_or = p.id_productos
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia = {$transfer_id}
					AND tp.total_piezas_validacion > 0";
			}else{
				$type_action = 'reception';
				$sql = "SELECT
							GROUP_CONCAT( tru.id_transferencia_recepcion SEPARATOR ',' ) AS reception_details_ids,
							GROUP_CONCAT( tp.id_transferencia_producto SEPARATOR ',' ) AS transfer_product_id,
							CONCAT( p.nombre, ' <b>MODELO : ', pp.clave_proveedor, 
									' ( Caja con ' , pp.presentacion_caja , 'pzas)</b>' 
							) AS product_name,
							SUM( tp.cantidad_cajas_recibidas ) AS validated_boxes,
							SUM( tp.cantidad_paquetes_recibidos ) AS validated_packs,
							SUM( tp.total_piezas_recibidas ) AS total_validated_pieces,
							pp.id_proveedor_producto AS product_provider_id
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_productos p
						ON tp.id_producto_or = p.id_productos
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_proveedor_producto = tp.id_proveedor_producto
						LEFT JOIN ec_transferencias_recepcion_usuarios tru
						ON tru.id_transferencia_producto = tp.id_transferencia_producto
						WHERE tp.id_transferencia IN( {$transfer_id} )
						AND tp.total_piezas_recibidas > 0
						AND tru.id_transferencia_recepcion IS NOT NULL
						GROUP BY tru.id_producto";
				//echo $sql;
			}
				//	echo 'reception_block_id : ' . $reception_block_id;

			$validation_block_id = ($validation_block_id == null ? 'null' : $validation_block_id); 
			$reception_block_id = ($reception_block_id == null ? 'null' : $reception_block_id);
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de como fue validada / recibida la transferencia : {$sql} {$this->link->error}" );
			
		//asigna en automático
			while( $row = $stm->fetch_assoc() ){
				$this->reasignTransferDetail( $row['transfer_product_id'], $validation_block_id, 1 );
			}

			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de como fue validada / recibida la transferencia : {$sql} {$this->link->error}" );
			//echo 'here';
			if( $stm->num_rows <= 0 && ( $reception_block_id == 'null' || $reception_block_id == '' ) ){
				$sql = "DELETE FROM ec_bloques_transferencias_validacion_detalle WHERE id_transferencia = {$transfer_id}";
		//	echo 'here';				
				$this->unlock_block( $validation_block_id, $reception_block_id );
				$stm = $this->link->query( $sql ) or die( "Error al eliminar la transferencia del bloque de validacion : {$this->link->error}" );
			//verifica si le quedan detalles al bloque
				$sql = "SELECT";

				$this->unlock_block( $validation_block_id, $reception_block_id );
				return 'ok';
			}else if( $stm->num_rows <= 0 && $reception_block_id != 'null' && $reception_block_id != '' ){
				$sql = "DELETE FROM ec_bloques_transferencias_recepcion_detalle WHERE id_bloque_transferencia_validacion = {$validation_block_id}";
		//	echo 'here_2';
				$stm = $this->link->query( $sql ) or die( "Error al eliminar la transferencia del bloque de recepcion : {$this->link->error}" );
				
				$this->unlock_block( $validation_block_id, $reception_block_id );
				return 'ok';
			}

			$resp  = "";
			$resp .= "<table class=\"table table-bordered table-striped\">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Cajas Validadas</th>
								<th>Paquetes validados</th>
								<th>Total piezas validadas</th>
								<th>Regresar</th>
								<th>Asignar a transferencias del bloque</th>
							</tr>
						</thead>
						<tbody id=\"\">";
			while ( $row = $stm->fetch_assoc() ) {//<td>{$row['transfer_validation_id']}</td>
				$resp .= "<tr>
							<td>{$row['product_name']} - {$row['reception_details_ids']}</td>
							<td class=\"text-center\">{$row['validated_boxes']}</td>
							<td class=\"text-center\">{$row['validated_packs']}</td>
							<td class=\"text-center\">{$row['total_validated_pieces']}</td>
							<td class=\"text-center\">
								<button 
									type=\"button\"
									class=\"btn btn-warning\"
									onclick=\"resolve_detail( -1, '{$row['transfer_product_id']}', '{$type_action}' );\"
								>
									<i class=\"icon-loop\"><br>Regresar</i>
								</button>
							</td>
							<td class=\"text-center\">
								<button 
									type=\"button\"
									class=\"btn btn-success\"
									onclick=\"resolve_detail( 1, '{$row['transfer_product_id']}', '{$type_action}' );\"
								>
									<i class=\"icon-paper-plane-empty\"><br>Enviar</i>
								</button>
							</td>
						</tr>";
			}
			$resp .= "</tbody></table><br>";
			$resp .= "<div class=\"row\">
						<div class=\"col-3\"></div>
						<div class=\"col-6\">	
							<button
								class=\"btn btn-danger form-control\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circled-1\">Cancelar y cerrar</i>
							</button>
						</div>
					</div><br>";
			$this->unlock_block( $validation_block_id, $reception_block_id );
			return $resp;
		}

		public function resolve( $transfer_id, $action, $quanity, $product_provider_id, $detail_id ){
			$this->link->autocommit( false );
			switch ( $action ) {
				case '-1':/*quitar*/
				//consulta todos los ids de los productos relacionados
					$sql = "SELECT 
								tvu.id_transferencia_validacion AS validation_detail_id,
								tp.id_transferencia_producto AS transfer_product_id,
								tvu.cantidad_cajas_validadas AS validated_boxes,
								tvu.cantidad_paquetes_validados AS validated_packs,
								tvu.cantidad_piezas_validadas AS validated_pieces,
								( ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) +
								  ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) +
								  tvu.cantidad_piezas_validadas
								) AS validated_pieces_total
							FROM ec_transferencias_validacion_usuarios tvu
							LEFT JOIN ec_transferencia_productos tp
							ON tp.id_transferencia_producto = tvu.id_transferencia_producto
							LEFT JOIN ec_proveedor_producto pp
							ON pp.id_proveedor_producto = tp.id_proveedor_producto
							WHERE tp.id_proveedor_producto = {$product_provider_id}
							AND tp.id_transferencia = {$transfer_id}";
					//die( "{$sql}" );
					$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle por eliminar : {$this->link->error}" );
					while ( $row = $stm->fetch_assoc() ) {//quita piezas recibidas del detalle de validacion
						$sql = "UPDATE ec_transferencia_productos SET 
									cantidad_cajas_validacion = ( cantidad_cajas_validacion - {$row['validated_boxes']} ),
									cantidad_paquetes_validacion = ( cantidad_paquetes_validacion - {$row['validated_packs']} ),
									cantidad_piezas_validacion = ( cantidad_piezas_validacion - {$row['validated_pieces']} ),
									total_piezas_validacion = ( total_piezas_validacion - {$row['validated_pieces_total']} )
								WHERE id_transferencia_producto = {$row['transfer_product_id']}";
						$stm_2 = $this->link->query( $sql ) or die( "Error al actualizar validacion de transferencia producto : {$this->link->error}" );

					//elimina el detalle de validacion
						$sql = "DELETE FROM ec_transferencias_validacion_usuarios
								WHERE id_transferencia_validacion = {$row['validation_detail_id']}";
						$stm_2 = $this->link->query( $sql ) or die( "Error al eliminar los detalles de validacion : {$this->link->error}" );
						//echo $sql;
					}			
					$this->link->autocommit( true );
					return 'ok';	
				break;
				case '1':/*asignar*/
			//
					$this->link->autocommit( true );
				break;
			}
			$this->link->autocommit( true );
		}
	}
?>