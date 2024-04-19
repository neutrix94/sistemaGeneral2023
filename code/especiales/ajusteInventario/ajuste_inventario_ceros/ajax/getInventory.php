<?php
	if( ! include('../../../../../conexionMysqli.php')){
		die('No se encontró el archivo de conexion');
	}
	if( ! include('functions.php')){
		die('No se encontró el archivo de conexion');
	}
	$store_id = $_POST['store_id'];
	$warehouse_id = $_POST['warehouse_id'];
//	die( $warehouse_id );
	/*Modificacion Oscar 22.02.2018*/
	/*$c_a="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$sucursal AND es_almacen=1";
	$eje=mysql_query($c_a)or die("Error al consultar almacen primario!!!\n\n".$c_a."\n\n".mysql_error());
	$alm=mysql_fetch_row($eje);*/
	$sql="SELECT 
				/*0*/p.id_productos,
				/*1*/p.nombre,
				/*2*/FORMAT( IF(md.id_producto IS NULL,
						0,
						IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))), 
					2 ),
				/*3*/p.orden_lista,
				/*4*/IF($store_id=1,p.ubicacion_almacen,sp.ubicacion_almacen_sucursal),
				/*5*/(SELECT 
						IF( id_producto IS NULL, 0, id_producto ) 
					FROM ec_productos_detalle 
					WHERE id_producto_ordigen = p.id_productos LIMIT 1 )
			FROM ec_productos p /*ON i.id_producto=p.id_productos*/
			LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
			AND sp.id_sucursal IN( {$store_id} ) 
			/*AND sp.estado_suc=1*/
			LEFT JOIN ec_movimiento_detalle md 
			ON sp.id_producto=md.id_producto
			LEFT JOIN ec_movimiento_almacen ma 
			ON md.id_movimiento = ma.id_movimiento_almacen 
			AND ma.id_almacen = {$warehouse_id} 
			AND ma.id_sucursal = {$store_id}
			LEFT JOIN ec_tipos_movimiento tm
			ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE p.id_productos>2
			/*AND p.es_maquilado=0 AND sp.es_externo=0*/";
	$sql .= " GROUP BY p.id_productos ORDER BY p.orden_lista ASC";
	$cons=$link->query($sql);
//die($sql);
	if(!$cons){
		die("Error al consultar inventario...\n".$sql."\n\n" . $link->error );
	}
//declaramos contador
	$c=0;
	$global_counter = 0;
	$current_product = 0;
	while( $row=$cons->fetch_row() ){
		$c++;//incrementamos contador
		if ( $current_product != $row[0] ){
			$global_counter ++;
		}
		if( $global_counter % 2 == 0 ){
			$color='#FFFF99';
		}else{
			$color='#CCCCCC';
		}
		echo make_row( $row, $c, 'readonly' );
		$sql_prov_prod = "SELECT
			ax.id_productos,
			ax.nombre,
			ax.inventario,
			ax.orden_lista,
			IF($store_id = 1,
				CONCAT( ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde  ),
				ax.ubicacion_almacen_sucursal) AS location,
			ax.id_proveedor_producto,
			ax.is_maquiled
		FROM(
			SELECT 
				/*0*/p.id_productos,
				/*1*/CONCAT(p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' - ', pp.clave_proveedor, ' ( ', pp.presentacion_caja, ' pzas por caja )', ' <b>id_p_p : ', pp.id_proveedor_producto, '</b>' ) ) ) AS nombre,
				/*2*/FORMAT( SUM( IF( mdp.id_movimiento_detalle_proveedor_producto IS NULL, 0, ( mdp.cantidad * tm.afecta) ) ), 2) AS inventario,
				/*3*/p.orden_lista,
				/*4*/sp.ubicacion_almacen_sucursal,
				/*5*/pp.id_proveedor_producto,
				/*6*/(SELECT IF( id_producto IS NULL, 0, id_producto ) FROM ec_productos_detalle WHERE id_producto_ordigen = p.id_productos LIMIT 1) as is_maquiled
			FROM ec_productos p 
			LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
			AND sp.id_sucursal IN( $store_id ) /*AND sp.estado_suc=1*/
			LEFT JOIN ec_proveedor_producto pp ON pp.id_producto = p.id_productos
			LEFT JOIN ec_movimiento_detalle_proveedor_producto mdp 
			ON mdp.id_proveedor_producto = pp.id_proveedor_producto
			AND mdp.id_almacen = '{$warehouse_id}'
			LEFT JOIN ec_tipos_movimiento tm ON mdp.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE pp.id_producto = '{$row[0]}'
			GROUP BY pp.id_proveedor_producto
		) ax
		LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
		ON ppua.id_proveedor_producto = ax.id_proveedor_producto
		GROUP BY ax.id_proveedor_producto";
		//die( $sql_prov_prod );
		
		$prov_prod = $link->query( $sql_prov_prod ) or die( "Error al consultar detalle de inventario " . $link->error );
		while ( $row_prod_prod = $prov_prod->fetch_row() ) {
			echo make_row( $row_prod_prod, $c );
		}
		$current_product = $row[0];
	}