<?php
	include("../../../../conectMin.php");
	include("../../../../conexionMysqli.php");

	$fl=$_POST['flag'];
	$id_orden=$_POST['oc'];
/*Implementacion Oscar 23.07.2019 para modificar la ubicacion directo en la tabla de pedidos*/
	if($fl=='ubicacion'){
		$val=$_POST['valor'];
		$id_prod=$_POST['id'];
		$sql="UPDATE ec_productos SET ubicacion_almacen='$val',sincronizar=1 WHERE id_productos=$id_prod";
		$eje=mysql_query($sql)or die("Error al actualizar la ubicacion del almacen!!!\n".mysql_error()."\n".$sql);
		die('ok');
	}

	if($fl=='descuento'){
		$val=$_POST['valor'];
		$id_prod=$_POST['id'];
		$sql="UPDATE ec_productos SET precio_venta_mayoreo='$val',sincronizar=1 WHERE id_productos=$id_prod";
		$eje=mysql_query($sql)or die("Error al actualizar la ubicacion del almacen!!!\n".mysql_error()."\n".$sql);
		die('ok');
	}
/**/
/*implementación Oscar 11.02.2018 para buscar folios de notas de proveedor*/
//buscador de folios
	if($fl=='busca_folios'){
		$id_proveedor=$_POST['id_pro'];
		$type = $_POST['seeker_type'];
		if ( $type == 'remissions' ){
			$sql="SELECT 
					ocr.id_oc_recepcion,/*0*/
					ocr.folio_referencia_proveedor,/*1*/
					ocr.monto_nota_proveedor,/*2*/
					prov.nombre_comercial,/*3*/
					ocr.piezas_remision,/*4*/
					ocr.piezas_recepcion,/*5*/
					ocr.status,/*6*/
					ero.nombre/*7*/
				FROM ec_oc_recepcion ocr
				LEFT JOIN ec_proveedor prov 
				ON ocr.id_proveedor = prov.id_proveedor
				LEFT JOIN ec_estatus_recepcion_oc ero
				ON ero.id_estatus = ocr.status
				WHERE ocr.id_oc_recepcion > 0 AND prov.id_proveedor = '{$id_proveedor}' 
				AND (";
		}else if( $type == 'receptions' ){
			$sql="SELECT 
					rb.id_recepcion_bodega,/*0*/
					rb.folio_recepcion,/*1*/
					0,/*2*/
					prov.nombre_comercial,/*3*/
					rb.numero_partidas,/*4*/
					0,/*5*/
					rb.id_status_validacion,/*6*/
					svrb.nombre_status
				FROM ec_recepcion_bodega rb
				LEFT JOIN ec_proveedor prov 
				ON rb.id_proveedor = prov.id_proveedor
				LEFT JOIN ec_status_validacion_recepcion_bodega svrb
				ON svrb.id_status_validacion = rb.id_status_validacion
				WHERE rb.id_recepcion_bodega > 0 
				AND rb.id_proveedor = '{$id_proveedor}'
				/*AND rb.id_status_validacion < 3*/
				AND (";
		}
	//busqueda por coincidencias
		$clave=explode(" ",$_POST['txt']);
		for($i=0;$i<sizeof($clave);$i++){
			if($i>0){
				$sql.=" AND ";
			}
			$sql.= ($type == 'remissions' ? "ocr.folio_referencia_proveedor" : "rb.folio_recepcion" );
			$sql .= " LIKE '%".$clave[$i]."%'";
		}//fin de for i
		$sql.=")";//ciera el AND de la consulta
//die('ok|'.$sql);
		$eje=mysql_query($sql)or die("error|Error al consultar coincidencias de folio!!!<br>".$sql."<br>".mysql_error());

		echo 'ok|';
		if(mysql_num_rows($eje)<=0){
			die('sin coincidencias');
		}
		//echo '<table width="100%">';
		$c=0;
		while($r=mysql_fetch_row($eje)){
			$c++;
			echo '<div class="remission_option" tabindex="'.$c.'" onclick="carga_folio_recepcion('.$r[0].',\''.$r[1].'\','.$r[2].
				','.$r[4].','.$r[5].',' .$r[6].',\'' . $type . '\');">';
				echo $r[1].' - '.$r[3].' $'.$r[2].'(' . $r[7] . ')';//'<td width="100%" align="left">'.</td>
			echo '</div>';
		}	
		die('');//</table>
	}
/*fin de cambio Oscar 11.2.2018*/

//buscador de productos
	if($fl==1){
	//armamos la consulta
		$sql = "SELECT 
					p.id_productos,
					CONCAT(p.nombre, ' ( MODELO : ', pp.clave_proveedor, ' - ', 
						pp.presentacion_caja, ' )' )
				FROM ec_productos p
				LEFT JOIN ec_proveedor_producto pp ON p.id_productos = pp.id_producto
				WHERE pp.id_proveedor 
				IN( SELECT id_proveedor FROM ec_ordenes_compra WHERE id_orden_compra = '$id_orden')
				AND (";

/*		$sql="SELECT p.id_productos,p.nombre 
		FROM ec_productos p 
		LEFT JOIN ec_oc_detalle ocd ON p.id_productos=ocd.id_producto
		LEFT JOIN ec_ordenes_compra oc ON ocd.id_orden_compra=oc.id_orden_compra
		WHERE oc.id_orden_compra=$id_orden AND (";*/
	//precisamos la búsqueda
		$clave=explode(" ",$_POST['txt']);

		for($i=0;$i<sizeof($clave);$i++){
			if($clave[$i]!='' && $clave[$i]!=null){
				if($i>0){
					$sql.=" AND ";
				}
				$sql.="CONCAT(p.nombre, ' ( MODELO : ', pp.clave_proveedor, ' - ', 
						pp.presentacion_caja, ' )' ) LIKE '%".$clave[$i]."%'";
			}
		}//fin de for i
	//cerramos el parentesis de las condiciones
		$sql.=")";
		//ejecutamos consulta
		$eje=mysql_query($sql)or die("Error al buscar coincidencias!!\n\n".$sql."\n\n".mysql_error());
	//regresamos resultados
		echo 'ok|<table width="100%">';
		$tab=0;
		while($row=mysql_fetch_row($eje)){
			$tab++;
			echo '<tr tabindex="'.$tab.'" id="opc_'.$tab.'" class="opc_busc" onkeyup="valida_opc(event,'.$tab.');" onclick="valida_opc(\'click\','.$tab.');">';
				echo '<td style="display:none;" id="val_opc_'.$tab.'">'.$row[0].'</td>';
				echo '<td>'.$row[1].'</td>';
			echo '</tr>';	
		}	
		echo '</table>';
		echo '<input type="hidden" id="opc_totales" value="'.$tab.'">';
	}//fin de if $fl==1 (si es buscador)

//insertar recepción
	if($fl==2){
		$ref_prov = $_POST['ref'];
		$id_proveedor = $_POST['id_prov'];
		$id_recepcion = $_POST['id'];
		$monto_recepcion = $_POST['mt_nota'];
		$reception_id = $_POST['reference_reception'];
//consulta el año actual
		$sql = "SELECT DATE_FORMAT(NOW(), '%Y') AS current_year";
		$stm = $link->query( $sql ) or die( "Error al consular el año actual : {$link->error}" );
		$row = $stm->fetch_assoc();
		$current_year = $row['current_year'];
	/*echo "<div class=\"text-start\"><p>Entra en proceso Guardar remisión, datos que llegan : 
			<p>Referencia Proveedor : {$ref_prov}</p>
			<p>id Proveedor : {$id_proveedor}</p>
			<p>Monto Recepcion : {$monto_recepcion}</p>
			<p>Id de Remisión : {$reception_id}</p>
			</p>";*///die( $_POST['datos'] );
$link->autocommit( false );
//mysql_query("BEGIN");//marcamos el inicio de la transaccion

	//insertamos el detalle de la Recepción
		$dat=$_POST['datos'];
		$dato=explode("|", $dat);
	//verifica que exista un movimiento relacionado a la cabecera de la remisión
//echo "<p>verifica que exista un movimiento relacionado a la cabecera de la remisión</p>";
		$id_movimiento = 0;
		$sql = "SELECT 
					ma.id_movimiento_almacen AS movement_id
				FROM ec_movimiento_almacen ma
				WHERE ma.id_orden_compra = '{$id_recepcion}' ";
		//$stm = mysql_query( $sql ) or die( "Error al consultar el id de movimiento de almacen de la Remisión : " . mysql_error() );
		$stm = $link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen de la Remisión : {$link->error}" );
		if( $stm->num_rows <= 0 ){
			$sql = "SELECT 
						id_usuario AS user_id,
						CONCAT('RECEPCIÓN DE NOTA ', folio_referencia_proveedor) AS notations,
						id_oc_recepcion AS reception_id
					FROM ec_oc_recepcion
					WHERE id_oc_recepcion = '{$id_recepcion}'";
			$stm_2 = $link->query( $sql ) or die( "Error al consultar remisión para insertar movimiento de almacen : {$sql} : {$link->error}" ); 
			$detail_row = $stm_2->fetch_assoc();
		/*inserta cabecera de movimiento de almacen por procedure*/
			$sql = "CALL spMovimientoAlmacen_inserta ( {$detail_row['user_id']}, '{$detail_row['notations']}', 1, 1, 1, -1, {$detail_row['reception_id']}, -1, -1, 
						16, NULL )";
			$stm = $link->query( $sql ) or die( "Error al insertar movimiento de almacen de la Remisión por procedure : {$sql} : {$link->error}" );

			$sql = "SELECT LAST_INSERT_ID() AS last_id";
			$stm_3 = $link->query( $sql ) or die( "Error al consultar el id de movimiento de almacen insertado por procedure : {$sql} : {$link->error}" );
			$movement_row = $stm_3->fetch_assoc();
			$id_movimiento = $movement_row['last_id'];
		}else{
			$movement_row = $stm->fetch_assoc();
			$id_movimiento = $movement_row['movement_id'];
		}
		$orders = array();
		for($i=0;$i<sizeof($dato);$i++){
		if( $dato[$i] != '' ){//implementacion Oscar 2023 para corregir error al elimina fila y guardar
			$sql="";
			$d=explode("~",$dato[$i]);
/*implementacion Oscar 2023*/
			$sql_aux = "UPDATE ec_recepcion_bodega_detalle 
						SET cajas_recibidas = {$d[14]},
						piezas_sueltas_recibidas = {$d[15]}
					WHERE id_recepcion_bodega_detalle = {$d[7]}";
			$stm_aux = $link->query( $sql_aux ) or die( "Error al actualizar cajas / piezas recibidas previamente : {$link->error}" );
/*fin de cambio Oscar 2023*/
		//verificamos si el producto ya existe en la recepcion
			$sql="SELECT 
					id_oc_recepcion_detalle 
				FROM ec_oc_recepcion_detalle 
				WHERE id_oc_recepcion=$id_recepcion 
				AND id_recepcion_bodega_detalle = '{$d[7]}'
			/*id_producto=IF('$d[0]'='invalida','$d[1]','$d[0]')
			AND id_proveedor_producto = {$d['6']}*/";
			//echo $sql;
			$eje=$link->query( $sql ) or die( "Error al consultar si existe detalle de Recepción de Órden de Compra!!!\n\n{$sql} {$link->error}" );
			/*if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al insertar detalle de Recepción de Órden de Compra!!!\n\n".$sql."\n\n".$error);
			}*/
			$nvo=1;
			$id_recepcion_detalle = '';
			if( $eje->num_rows == 1 ){
		//echo 'num: '.mysql_num_rows($eje);
				$nvo=0;
				$r=$eje->fetch_row();
				$id_recepcion_detalle=$r[0];
			}

			//if($d[0]=='invalida'){
			//si es invalidar
				/*
DESHABILITADO POR OSCAR 2022
				if($nvo==0){
					$sql="UPDATE ec_oc_recepcion_detalle 
						SET piezas_recibidas=(piezas_recibidas+IF('$d[0]'='invalida',0,'$d[1]')),
						id_proveedor_producto = '{$d[6]}'
					/*,
							monto=(precio_pieza*piezas_recibidas)-((precio_pieza*piezas_recibidas)*porcentaje_descuento)*
						WHERE id_oc_recepcion_detalle=$id_recepcion_detalle";	
						//die($sql);
				}else*/
			$action = "";
			if($d[3]!=0||$d[1]!=0||$d[0]!='invalida'){
				$sql = "";
				if( $d[8] == 0 && $nvo==1 ){// && $nvo==1 modificacion oscar 2023 para que no se duplique el movimiento de almacen
					$sql="INSERT INTO ec_oc_recepcion_detalle 
							SET 
		 					id_oc_recepcion_detalle = null, 
		 					id_oc_recepcion = '{$id_recepcion}', 
		 					id_producto = IF( '{$d[0]}' = 'invalida', '{$d[1]}', '{$d[0]}' ), 
		 					id_proveedor_producto = IF( '{$d[0]}' = 'invalida', -1, '{$d[6]}' ),
							piezas_recibidas = IF( '{$d[0]}' = 'invalida', 0, '{$d[12]}' ), 
							presentacion_caja = IF( '{$d[0]}' = 'invalida', 0, '{$d[4]}' ), 
							precio_pieza = IF( '{$d[0]}' = 'invalida', 0, '{$d[2]}' ), 
							monto = IF( '{$d[0]}' = 'invalida', 0, '{$d[3]}' ), 
							es_valido = IF( '{$d[0]}' = 'invalida', 0, 1 ), 
							observaciones = IF( '{$d[0]}' = 'invalida', 'Se recibió en ceros', '' ),
							porcentaje_descuento = IF( '{$d[0]}' = 'invalida', 0, '{$d[5]}'),
							id_recepcion_bodega_detalle = '{$d[7]}'";
					$action = "insert";
				}else if( $d[8] == 1 || $nvo == 0 ){
					$sql="UPDATE ec_oc_recepcion_detalle 
							SET 
		 					id_oc_recepcion = '{$id_recepcion}', 
		 					id_producto = IF( '{$d[0]}' = 'invalida', '{$d[1]}', '{$d[0]}' ), 
		 					id_proveedor_producto = IF( '{$d[0]}' = 'invalida', -1, '{$d[6]}' ),
							piezas_recibidas = IF( '{$d[0]}' = 'invalida', 0, '{$d[12]}' ), 
							presentacion_caja = IF( '{$d[0]}' = 'invalida', 0, '{$d[4]}' ), 
							precio_pieza = IF( '{$d[0]}' = 'invalida', 0, '{$d[2]}' ), 
							monto = IF( '{$d[0]}' = 'invalida', 0, '{$d[3]}' ), 
							es_valido = IF( '{$d[0]}' = 'invalida', 0, 1 ), 
							observaciones = IF( '{$d[0]}' = 'invalida', 'Se recibió en ceros', '' ),
							porcentaje_descuento = IF( '{$d[0]}' = 'invalida', 0, '{$d[5]}')
						WHERE id_oc_recepcion_detalle = '{$id_recepcion_detalle}'";
					$action = "update";
				}
			}
//echo ( $sql. "<br>" );
			if($sql!=""){
			//ejecutamos la consulta que inserta el detalle
				$eje=$link->query($sql) or die("Error al insertar/ actualizar detalle de Recepción de Órden de Compra!!!\n\n{$sql} {$link->error}");
				if( $action == "insert" ){
					$sql = "SELECT LAST_INSERT_ID() AS last_id";
					$stm = $link->query( $sql ) or die( "Error al consultar el ultimo id insertado de detalle recepción : {$sql} : {$link->error}" );
					$reception_detail_id = $stm->fetch_assoc();
				//inserta el detalle de movimiento de almacen
					$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$id_movimiento}, {$d[0]}, {$d[12]}, {$d[12]}, -1, {$reception_detail_id['last_id']}, {$d[6]}, 16, NULL )";
					$stm_3 = $link->query( $sql ) or die( "Error al insertar detalle de movimiento de almacen con Procedure : {$sql} : {$link->error}" );
				}else if ( $action == "update" ){
					$sql = "SELECT id_movimiento_almacen_detalle AS movement_detail_id FROM ec_movimiento_detalle WHERE id_oc_detalle = {$id_recepcion_detalle}";
					$stm_2 = $link->query( $sql ) or die( "Error al consultar el id del detalle recepción : {$sql} : {$link->error}" );
					$row_detail = $stm->fetch_assoc();
				//actualiza el detalle de movimiento de almacen
					$sql = "CALL spMovimientoAlmacenDetalle_actualiza ( {$row_detail['movement_detail_id']}, {$d[12]} );";
					$stm_3 = $link->query( $sql ) or die( "Error al actualizar detalle de movimiento de almacen con Procedure : {$sql} : {$link->error}" );
				}
			//actualizamos lo recibido a la orden de compra
				$observaciones='se recibio en 0';
				if($d[0]=='invalida'){
/*echo "<p>Elimina detalle de orden de compra si entra en condición 'invalida'<br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea>";*/
					$sql="DELETE FROM ec_oc_detalle WHERE id_producto=$d[1] AND id_orden_compra=$id_orden";
					$eje=$link->query($sql) or die("Error al eliminar del detalle de Orden de Compra!!!\n\n {$link->error}");
					
					$d[0]=$d[1];
					$d[1]=0;
				}
			//consulta los piezas pendientes de recibir
				$sql = "SELECT 
							ocd.id_oc_detalle, 
							( ocd.cantidad - ocd.cantidad_surtido ),
							ocd.id_orden_compra
						FROM ec_oc_detalle ocd
						LEFT JOIN ec_ordenes_compra oc 
						ON oc.id_orden_compra = ocd.id_orden_compra
						WHERE oc.id_estatus_oc <= 3
						AND ocd.cantidad_surtido < ocd.cantidad
						AND ocd.id_producto = '{$d[0]}'
						AND ocd.id_proveedor_producto = '{$d[6]}'
						AND oc.id_proveedor = '{$id_proveedor}'
						AND oc.fecha LIKE '%{$current_year}%' 
						GROUP BY ocd.id_oc_detalle
						ORDER BY ocd.id_oc_detalle ASC";
				$exc = $link->query( $sql ) or die( "Error al consultar ordenes de compra por actualizar : {$link->error}" );
/*echo "<p>Consulta las piezas pendientes de recibir en pedido 
		en relación al producto : {$d[0]} y proveedor {$id_proveedor}<br>
		<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea>
	</p>";*/
				while ( $ocd = $exc->fetch_row() ) {
/*echo "<p>Si encontró el producto en pedido</p>";*/
					if( $d[11] > 0 ) {
						$aux_r = ( $d[11] >= $ocd[1] ? $ocd[1] : $d[11] );
						$sql="UPDATE ec_oc_detalle SET 
								cantidad_surtido = ( cantidad_surtido + $aux_r ) 
							WHERE id_producto = '{$d[0]}' AND id_oc_detalle = '{$ocd[0]}'";
//echo "{$sql}<br>";
	/*	echo "<p>Actualiza el detalle del pedido ( se le suman {$aux_r} a la cantidad recibida )<br>

	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
						$eje=$link->query($sql) or die("Error al actualizar piezas recibidas en la Orden de Compra!!!\n\n{$link->error}");
						/*if(!$eje){
							$error=mysql_error();
							mysql_query("ROLLBACK");//cancelamos transacción
						}*/
						$d[11] = ( $d[11] - $aux_r );
					//agrega el id de la orden de compra
						if( !in_array( $orders, $ocd[2] ) ){	
							$orders[] = $ocd[2];
						}
					}
			 	} 
/*echo "<br>";*/
				
		/*implementacion Oscar 16.08.2019*/
		//die($d[6]);
				if($d[0]!='invalida' && $d[6]!=''){
/*echo "<p>Entra en condición para NO invalidar</p>";*/
				//consultamos la clave de proveedor
					$sql="SELECT clave_proveedor FROM ec_proveedor_producto WHERE id_proveedor_producto=$d[6]";
					$eje=$link->query($sql) or die("Error al consultar el codigo de proveedor-producto!!!\n\n{$link->error}");
					//die($sql);
/*echo "<p>Consulta la clave de proveedor <br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
					/*if(!$eje){
						$error=mysql_error();
						mysql_query("ROLLBACK");//cancelamos transacción
					}*/
					$r_1=$eje->fetch_row();//die( 'here' );
			//introducimos el nuevo código de proveedor si no existe
				//corroboramos si esta clave ya existe en la tabla de productos; de lo contrario la insertamos
					$sql="SELECT COUNT(*) FROM ec_productos WHERE id_productos=$d[0] AND clave LIKE '%$r_1[0]%'";
/*echo "<p>Corroboramos si esta clave ya existe en la tabla de productos; de lo contrario la inserta<br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
					$eje=$link->query($sql) or die("Error al verificar el codigo de proveedor en la tabla de productos!!!\n\n {$link->error}");
					/*if(!$eje){
						$error=mysql_error();
						mysql_query("ROLLBACK");//cancelamos transacción
					}*/
					$r_2=$eje->fetch_row();
					if($r_2[0]==0){
					//actualizamos el codigo de proveedor producto en la tabla de productos
						$sql="UPDATE ec_productos SET clave=CONCAT(clave,',','$r_1[0]') WHERE id_productos=$d[0]";
/*echo "<p>Actualiza el codigo de proveedor producto {$r_1[0]} en la tabla de productos
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
						$eje = $link->query($sql) or die("Error al actualizar el codigo alfanumerico en la tabla de productos!!!\n\n {$link->error}");
						/*if(!$eje){
							$error=mysql_error();
							mysql_query("ROLLBACK");//cancelamos transacción
						}*/
					}
					//die($sql);
				//actualizamos el proveedor producto, producto
					$precio_caja=$d[2]*$d[4];
/*deshabilitado por Oscar 2023 para evitar error de sobreescritura proveedor producto*/
					$sql = "UPDATE ec_proveedor_producto pp 
								SET pp.precio_pieza=$d[2],
									pp.presentacion_caja=$d[4],
									pp.precio=$precio_caja,
									pp.fecha_ultima_compra = NOW()
						WHERE pp.id_proveedor_producto=$d[6]";
	/*implementacion Oscar 2023 para ver que esta mandando a actualizar*/
try{
	$file = fopen("log.txt", "a");
	fwrite($file, "\n\nlog del txt archivo ( recPedBD.php ( linea 407 aproximadamente ) ) : {$sql}\n\n" . PHP_EOL);
	fclose($file);
}catch( Exception $e ){
	die( "Error escribir el log del txt archivo ( recPedBD.php ( linea 407 aproximadamente ) ): " . mysql_error() );
}
	/*fin de cambio Oscar 2023*/

					$sql_ = "UPDATE ec_productos p
								SET p.precio_compra = IF( {$d[2]} > 0, {$d[2]}, p.precio_compra )
						WHERE p.id_productos IN( SELECT id_producto FROM ec_proveedor_producto WHERE id_proveedor_producto=$d[6] )";
/*fin de cambio Oscar 2023*/


/*echo "<p>Actualiza los datos del proveedor-producto y producto : 
	<p>precio_pieza : {$d[2]}</p>
	<p>presentacion_caja : {$d[4]}</p>
	<p>precio  ( caja ): {$precio_caja}</p>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea>
</p>";*/
					$eje_prov=$link->query($sql) or die("Error al actualizar los parametros de proveedor producto!!!\n\n {$link->error}");
					/*if(!$eje_prov){
						$error=mysql_error();
						mysql_query("ROLLBACK");//cancelamos transacción
					}*/

					$eje_prod_ = $link->query( $sql_ ) or die("Error al actualizar los parametros del producto!!!\n\n {$sql_} {$link->error}");
					/*if(!$eje_prod_ ){
						$error=mysql_error();
						mysql_query("ROLLBACK");//cancelamos transacción
					}*/
//echo "<p>actualizamos el proveedor producto, producto : {$sql}</p>";
				}//fin de si el registro es valido
		/*fin de cambio Oscar 16.08.2019*/
			}//fin de if la consulta no esta vacía
		//actualiza el registro de ec_status_validacion_recepcion_bodega
			$sql = "UPDATE ec_recepcion_bodega_detalle
						SET validado = '1',
						id_proveedor_producto = '{$d[6]}',
						cajas_recibidas = ( cajas_recibidas + {$d[10]} ),
						piezas_sueltas_recibidas = ( piezas_sueltas_recibidas + {$d[9]}  ),
						cajas_en_validacion = 0,
						piezas_sueltas_en_validacion = 0,
						total_piezas_en_validacion = 0
					WHERE id_recepcion_bodega_detalle = '{$d[7]}'";
//die( $sql );
					/*$sql_update = "UPDATE ec_recepcion_bodega_detalle 
							SET cajas_recibidas = ( cajas_recibidas + {$d[10]} ),
								piezas_sueltas_recibidas = ( piezas_sueltas_recibidas + {$d[9]}  ),
								cajas_en_validacion = 0,
								piezas_sueltas_en_validacion = 0,
								total_piezas_en_validacion = 0
						WHERE id_recepcion_bodega_detalle = {$d[7]}";	

				$stm_update	= mysql_query( $sql ) or die( "Error al actualizar detalle de recepcion de Mercancia : " . mysql_error() );*/
//die($sql);
/*echo "<p>actualiza el registro de ec_status_validacion_recepcion_bodega : 
			<p>id_proveedor_producto : {$d[6]}</p>
			<p>piezas_por_caja : {$d[4]}</p>
			<p>piezas_sueltas_recibidas : {$d[9]}</p>
			<p>cajas_recibidas : '$d[10]}</p>
			<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
			$stm = $link->query($sql) or die( "Error al actualizar el detalle a validado : {$link->error}");
			/*if( !$stm ){				
				mysql_query("ROLLBACK");//cancelamos transacción
			}*/
		}//fin de si tiene datos
		}//fin de for i

//actualizamos las piezas recibidas
		$sql="UPDATE ec_oc_recepcion 
				SET piezas_recepcion=(SELECT SUM( IF(id_oc_recepcion_detalle IS NULL,0,piezas_recibidas) )
				FROM ec_oc_recepcion_detalle WHERE id_oc_recepcion=$id_recepcion ) 
			WHERE id_oc_recepcion=$id_recepcion";
/*echo "<p>Actualiza las piezas recibidas<br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
		$eje=$link->query($sql) or die("Error al actualizar las piezas recibidas en la remisión!!!\n {$link->error}");
		
		/*if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transaccion

		}*/
//inserta la relacion entre la orden de compra y la recepcion
		foreach ($orders as $key => $id_orden) {
			$sql="INSERT INTO ec_relaciones_oc_recepcion VALUES(null,$id_orden,$id_recepcion,now())";
/*echo "<p>inserta la relacion entre la orden de compra y la recepcion<br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
			$eje=$link->query($sql) or die("Error al insertar la relación entre la recepcion y la orden de compra!!!\n {$link->error}");
			/*if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transaccion
			}*/

	//actualiza el status de la orden de compra
			$sql="UPDATE ec_ordenes_compra 
				SET id_estatus_oc=IF( 
							(SELECT SUM(cantidad)-SUM(cantidad_surtido) FROM ec_oc_detalle WHERE id_orden_compra=$id_orden)=0
							OR
							(SELECT SUM(cantidad)-SUM(cantidad_surtido) FROM ec_oc_detalle WHERE id_orden_compra=$id_orden) IS NULL,
							4,
							3
				)
				WHERE id_orden_compra = '{$id_orden}'";
/*echo "<p>actualiza el status de la orden de compra : <br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
			$eje=$link->query($sql)or die("Error al actualizar el status de orden de compra!!!\n {$link->error}");
			/*if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transaccion
			}*/

		}
	//actualiza el status administrativo de la recepcion
		$sql = "UPDATE ec_recepcion_bodega 
					SET id_status_validacion = 2
				WHERE id_recepcion_bodega = '{$reception_id}'";
		$exc = $link->query( $sql )or die( "Error al actualizar el status de la recepción de Bodega : {$link->error}" );
/*echo "<p>actualiza el status administrativo de la recepcion a 2<br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p>";*/
	/*actualiza el status administrativo de la recepcion Deshabilitado por Oscar 2023
		$sql = "UPDATE ec_series_recepciones_bodega SET recepcion_actual = 0 WHERE recepcion_actual = '{$reception_id}'";
		$exc = $link->query( $sql )or die( "Error al liberar serie de recepción de Bodega :  {$link->error}" );*/
/*echo "<p>actualiza el status administrativo de la recepcion : <br>
	<textarea style=\"max-width:100%; width : 50%;\">{$sql}</textarea></p></div>";*/
$link->autocommit( true );
//mysql_query("COMMIT");//autorizamos transacción
		die('ok|<button type="button" class="btn btn-success" onclick="close_emergent();"><i class="icon-ok-circle">Aceptar</i></button>');
	}//fin de if $fl==2 (Recibir pedido)
?>