<?php
//consulta todos los almacenes
	$sql = "SELECT id_almacen, nombre FROM ec_almacen WHERE id_almacen > 0";
	$alm = $link->query( $sql )or die( "Error al consultar los almacenes : " . $link->error );
	$tabla_creada = 0;
	$diferencias_encontradas = array();
	while( $almacen = $alm->fetch_assoc() ){
/*Verificacion de inventario correcto por sucursal y almacen*/
		$sql = "SELECT 
	            ax.id_productos,
	            ax.nombre,
	            ax.inventario,
	            ax.nomAlmacen,
	            ax.id_almacen,
	            ax.InvCalculo
	        FROM(
	            SELECT
	                p.id_productos,
	                ROUND( ap.inventario, 4 ) AS inventario,
	                p.nombre,
	                alm.nombre as nomAlmacen,
	                ap.id_almacen,
	                ROUND( SUM( IF(
	                    ma.id_movimiento_almacen IS NULL,
	                    0.0000,
	                    ( md.cantidad * tm.afecta )
	                )
	            	), 4 ) AS InvCalculo
	            FROM ec_almacen_producto ap
	            LEFT JOIN ec_productos p 
	            ON p.id_productos = ap.id_producto
	            LEFT JOIN ec_almacen alm 
	            ON alm.id_almacen = ap.id_almacen
		        LEFT JOIN ec_movimiento_detalle md 
		        ON md.id_producto = p.id_productos
		        LEFT JOIN ec_movimiento_almacen ma 
		        ON ma.id_movimiento_almacen = md.id_movimiento
		        LEFT JOIN ec_tipos_movimiento tm 
		        ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
		        AND ma.id_almacen = {$almacen['id_almacen']}
	            WHERE ap.id_almacen = {$almacen['id_almacen']}
	            AND p.id_productos > 0
	            GROUP BY p.id_productos, alm.id_almacen
	        )ax
			WHERE ax.inventario <> ax.InvCalculo
			GROUP BY ax.id_productos, ax.id_almacen;";
				//die($sql);
		$diferencias = $link->query( $sql ) or die("Error al consultar las diferencias entre almacen producto (tabla de magento) y el inventario calculado : "
			. $link->error);

		$num_dif = $diferencias->num_rows;
	
	//si encuentra diferencias en los inventarios
		if( $num_dif > 0 ){
		//creación del encabezado de la tabla
			if( $tabla_creada == 0 ){
				$mensaje .= '<h2>Fueron encontradas las siguientes diferencias entre almacen - producto e inventario calculado</h2>';
				$encabezado = array('#','Id Producto', 'Almacén', 'Producto','Inventario (Almacén Producto)',
					'Inventario (Calculado)', 'Inventario (Tabla de Respaldo)');

				$mensaje .= "<br/>";
				$mensaje .= ( $es_navegador == 1 ? 
							$report->csv_header_generator( $encabezado ) : 
							$report->crea_tabla_log( $encabezado ) 
				);//crea encabezado de la tabla	
				$tabla_creada = 1;
			}
			while( $dif = $diferencias->fetch_assoc() ){
				array_push($diferencias_encontradas, $dif);//agrega el registro al arreglo global
			}
		}
	}//fin de iteración de almacenes
	
	$diferencias_calculadas = '';
	if( sizeof( $diferencias_encontradas ) > 0 ){
		foreach ($diferencias_encontradas as $key => $diferencia) {
			$diferencias_calculadas .= ( $es_navegador == 1 ? 
								$report->csv_row_generator( $diferencia ) : 
								$report->crea_fila_tabla_log( $diferencia ) 
			);
		}
	}else{		
		$mensaje .= '<h2>No Fueron encontradas diferencias entre almacen - producto e inventario calculado</h2>';
	}
	if( $es_navegador == 0){
		$mensaje = str_replace('|table_content|', $diferencias_calculadas, $mensaje);
	}else{
		$mensaje .= $diferencias_calculadas;
	}

	$mesaje .= '<h3>Diferencias por Proveedor - Producto</h3>';
/*Verificacion por proveedor producto*/
	/*Verificacion de inventario correcto por sucursal y almacen*/	
	$sql = "SELECT id_almacen, nombre FROM ec_almacen WHERE id_almacen > 0";
	$alm = $link->query( $sql )or die( "Error al consultar los almacenes : " . $link->error );
	$tabla_creada = 0;
	$diferencias_encontradas = array();
	while( $almacen = $alm->fetch_assoc() ){
		$sql = "SELECT 
		            ax.id_productos,
		            ax.id_proveedor_producto,
		            ax.nombre,
		            ax.inventario,
		            ax.nomAlmacen,
	                ax.id_almacen,
	                ax.InvCalculo
		        FROM(
		            SELECT
		                p.id_productos,
		            	pp.id_proveedor_producto,
		               	ROUND( ipp.inventario, 4 ) AS inventario,
		               	CONCAT( p.nombre, ' ( MODELO : ', IF( pp.clave_proveedor IS NULL, '', pp.clave_proveedor ) , ' ) ' ) AS nombre,		         
		                alm.nombre AS nomAlmacen,
		                alm.id_almacen,
			            ROUND(SUM(
			                IF(
			                    mdpp.id_movimiento_detalle_proveedor_producto IS NULL,
			                    0.0000,
			                    (mdpp.cantidad * tm.afecta)
			                )
			            ), 4 ) AS InvCalculo
		            FROM ec_inventario_proveedor_producto ipp
		            LEFT JOIN ec_proveedor_producto pp 
		            ON ipp.id_proveedor_producto = pp.id_proveedor_producto
		            LEFT JOIN ec_productos p 
		            ON p.id_productos = ipp.id_producto
		            LEFT JOIN ec_almacen alm 
		            ON alm.id_almacen = ipp.id_almacen
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
					ON mdpp.id_proveedor_producto = ipp.id_proveedor_producto
					AND mdpp.id_almacen = ipp.id_almacen
			        LEFT JOIN ec_tipos_movimiento tm 
			        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
		            WHERE p.id_productos > 0
		            AND ipp.id_almacen = '{$almacen['id_almacen']}'
			        AND mdpp.id_almacen = '{$almacen['id_almacen']}'
		            GROUP BY ipp.id_proveedor_producto, ipp.id_almacen
		        )ax
				WHERE ax.inventario != ax.InvCalculo
				GROUP BY ax.id_proveedor_producto, 
				ax.id_almacen";
				//die($sql);
		$diferencias = $link->query( $sql ) or die("Error al consultar las diferencias entre almacen producto (tabla de magento) y el inventario calculado : "
			. $link->error);

		$num_dif = $diferencias->num_rows;
	
	//si encuentra diferencias en los inventarios
		if( $num_dif > 0 ){
		//creación del encabezado de la tabla
			if( $tabla_creada == 0 ){
				$mensaje .= '<h2>Fueron encontradas las siguientes diferencias entre almacen - proveedor producto e inventario calculado</h2>';
				$encabezado = array('#','Id Producto', 'Id Proveedor Producto', 'Almacén', 'Producto','Inventario (Proveedor Producto)',
					'Inventario (Calculado)', 'Inventario (Tabla de Respaldo)');

				$mensaje .= "<br/>";
				$mensaje .= ( $es_navegador == 1 ? 
							$report->csv_header_generator( $encabezado ) : 
							$report->crea_tabla_log( $encabezado ) 
				);//crea encabezado de la tabla	
				$tabla_creada = 1;
			}
			while( $dif = $diferencias->fetch_assoc() ){
				array_push($diferencias_encontradas, $dif);//agrega el registro al arreglo global
			}
		}
	}//fin de foreach

	$diferencias_calculadas = '';
	if( sizeof( $diferencias_encontradas ) > 0 ){
		foreach ($diferencias_encontradas as $key => $diferencia) {
			$diferencias_calculadas .= ( $es_navegador == 1 ? 
								$report->csv_row_generator( $diferencia ) : 
								$report->crea_fila_tabla_log( $diferencia ) 
			);
		}
	}else{		
		$mensaje .= '<h2>No Fueron encontradas diferencias entre almacen - proveedor producto e inventario calculado</h2>';
	}
	if( $es_navegador == 0){
		$mensaje = str_replace('|table_content|', $diferencias_calculadas, $mensaje);
	}else{
		$mensaje .= $diferencias_calculadas;
	}

//comparacion entre inventario a nivel producto contra inventario a nivel proveedor producto
	$sql = "SELECT id_almacen, nombre FROM ec_almacen WHERE id_almacen > 0";
	$alm = $link->query( $sql )or die( "Error al consultar los almacenes : " . $link->error );
	$tabla_creada = 0;
	$diferencias_encontradas = array();
	while( $almacen = $alm->fetch_assoc() ){
		$sql = "SELECT				
					ax1.product_id,
				    ax1.list_order,
				    ax1.product_name,
				    IF( ax1.productInventory IS NULL, 0, ax1.productInventory ),
					IF( ax1.productProviderInventory IS NULL, 0, ax1.productProviderInventory )
				FROM(
					SELECT
						ax.product_id, 
						ax.list_order,
						ax.product_name,
						ax.productInventory,
						FORMAT( SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 
								0, 
								( mdpp.cantidad * tm.afecta ) 
							) 
						), 4 ) AS productProviderInventory
					FROM(
						SELECT
							p.id_productos AS product_id,
							p.orden_lista AS list_order,
							p.nombre AS product_name,
							FORMAT( SUM( IF( md.cantidad IS NULL, 0, ( md.cantidad * tm.afecta ) ) ), 4 ) AS productInventory
						FROM ec_productos p
						LEFT JOIN ec_movimiento_detalle md
						ON md.id_producto = p.id_productos
						LEFT JOIN ec_movimiento_almacen ma
						ON ma.id_movimiento_almacen = md.id_movimiento
			       		AND ma.id_almacen = '{$almacen['id_almacen']}'
						LEFT JOIN ec_tipos_movimiento tm
						ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
						LEFT JOIN ec_almacen alm 
						ON alm.id_almacen = ma.id_almacen
						WHERE p.id_productos > 0
						GROUP BY p.id_productos
					)ax
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = ax.product_id
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
			        ON mdpp.id_proveedor_producto = pp.id_proveedor_producto
			        LEFT JOIN ec_tipos_movimiento tm
			        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
			        AND mdpp.id_almacen = '{$almacen['id_almacen']}'
			        GROUP BY ax.product_id
			    )ax1
			WHERE IF( ax1.productInventory IS NULL, 0, ax1.productInventory ) != IF( ax1.productProviderInventory IS NULL, 0, ax1.productProviderInventory )
			GROUP BY ax1.product_id";
		$diferencias = $link->query( $sql ) or die("Error al consultar las diferencias entre almacen producto (tabla de magento) y el inventario calculado : "
			. $link->error);

		$num_dif = $diferencias->num_rows;
	}		
?>