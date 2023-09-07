<?php
	include( '../../../conexionMysqli.php' );

	function getQuery( $json ){
		$resp = "UPDATE {$json->table_name} SET ";
		if( $json->action_type == "insert" ){
			$resp = "INSERT INTO {$json->table_name} SET ";
		}
		foreach ($json as $key => $data) {
			if( $key != 'table_name' && $key != 'action_type' && $key != 'primary_key' 
				&& $key != 'primary_key_value' && $key != 'secondary_key' 
				&& $key != 'secondary_key_value' && $key != 'synchronization_row_id' ){
				$resp .= ( $resp == "" ? "" : ", " );
				$resp .= "{$key} = '{$data}'";
			}
		}
		
		if( $json->action_type == "update" ){
			$resp .= " WHERE {$json->primary_key} = '{$json->primary_key_value}'";
			if( $json->secondary_key != null && $json->secondary_key != '' ){
				$resp .= " AND {$json->secondary_key} = '{$json->secondary_key_value}'";
			}
		}

		$resp = str_replace( "'(", "(", $resp );
		$resp = str_replace( "' (", "(", $resp );
		$resp = str_replace( ")'", ")", $resp );
		$resp = str_replace( ") '", ")", $resp );
		$resp = str_replace( "SET ,", "SET ", $resp );

		return $resp;
	}
	$link->autocommit( false );
	/*echo "<br>Inicia<br>";
	$sql = "UPDATE `ec_movimiento_detalle_proveedor_producto` SET `folio_unico` = NULL WHERE `ec_movimiento_detalle_proveedor_producto`.`id_movimiento_detalle_proveedor_producto` = 416081";
	$link->query( $sql ) or die( "Error : {$sql}  {$link->error}" );
	sleep(120);
	echo "<br>Fin";*/
	$json = '{"table_name" : "ec_movimiento_detalle_proveedor_producto","action_type" : "update","primary_key" : "folio_unico","primary_key_value" : "22LINEA_MDPP_623270","id_proveedor_producto" : "1558","folio_unico_movimiento_detalle" : "","cantidad" : "0.4000","fecha_registro" : "2023-06-14 13:00:36","id_sucursal" : "1","status_agrupacion" : "-1","id_tipo_movimiento" : "13","id_almacen" : "1","id_pedido_validacion" : "-1","folio_unico" : "22LINEA_MDPP_623270","sincronizar" : "0","insertado_por_sincronizacion" : "0"}';
	$json_decoded = json_decode( $json );
	$sql = getQuery( $json_decoded );//$json_decoded->table_name;
	//$stm = $link->query( $sql ) or die( "Error al ejecutar la consulta 1 : {$link->error} <br> {$sql}" );
	echo "{$sql}<br></br>";

	$json = '{"table_name" : "ec_movimiento_detalle","action_type" : "insert","id_movimiento" : "( SELECT id_movimiento_almacen FROM ec_movimiento_almacen WHERE folio_unico = \'22LINEA_MA_1272388\' )","id_producto" : "2762","cantidad" : "0.5000","cantidad_surtida" : "0.5000","id_pedido_detalle" : "-1","id_proveedor_producto" : "401","folio_unico" : "22MAT_MD_3526467","insertado_por_sincronizacion" : "1","sincronizar" : "0"}';
	$json_decoded = json_decode( $json );
	$sql = getQuery( $json_decoded );//$json_decoded->table_name;
	//$stm = $link->query( $sql ) or die( "Error al ejecutar la consulta 2 : {$link->error} <br> {$sql}" );
	echo "{$sql}<br></br>";

	$json = '{"table_name" : "ec_almacen_producto","action_type" : "update","primary_key" : "id_producto","primary_key_value" : "2762","secondary_key" : "id_almacen","secondary_key_value" : "1","inventario" : "( inventario + -0.5000 )"}';
	$json_decoded = json_decode( $json );
	$sql = getQuery( $json_decoded );//$json_decoded->table_name;
	//$stm = $link->query( $sql ) or die( "Error al ejecutar la consulta 3 : {$link->error} <br> {$sql}" );
	echo "{$sql}<br></br>";

	$json = '{"table_name" : "ec_movimiento_detalle","action_type" : "insert","id_movimiento" : "( SELECT id_movimiento_almacen FROM ec_movimiento_almacen WHERE folio_unico = \'22LINEA_MA_1272388\' )","id_producto" : "2762","cantidad" : "0.5000","cantidad_surtida" : "0.5000","id_pedido_detalle" : "-1","id_proveedor_producto" : "401","folio_unico" : "22MAT_MD_3526467","insertado_por_sincronizacion" : "1","sincronizar" : "0"}';
	$json_decoded = json_decode( $json );
	$sql = getQuery( $json_decoded );//$json_decoded->table_name;
	//$stm = $link->query( $sql ) or die( "Error al ejecutar la consulta 4 : {$link->error} <br> {$sql}" );
	echo "{$sql}<br></br>";

	$json = '{"table_name" : "ec_movimiento_detalle_proveedor_producto","action_type" : "update","primary_key" : "folio_unico","primary_key_value" : "22LINEA_MDPP_623270","id_proveedor_producto" : "1558","folio_unico_movimiento_detalle" : "","cantidad" : "0.4000","fecha_registro" : "2023-06-14 13:00:36","id_sucursal" : "1","status_agrupacion" : "-1","id_tipo_movimiento" : "13","id_almacen" : "1","id_pedido_validacion" : "-1","folio_unico" : "22LINEA_MDPP_623270","sincronizar" : "0","insertado_por_sincronizacion" : "0"}';
	$json_decoded = json_decode( $json );
	$sql = getQuery( $json_decoded );//$json_decoded->table_name;
	//$stm = $link->query( $sql ) or die( "Error al ejecutar la consulta 5 : {$link->error} <br> {$sql}" );
	echo "{$sql}<br></br>";
$link->autocommit( true );
die( 'ok' );
?>