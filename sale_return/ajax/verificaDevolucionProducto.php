<?php
	if(!require('../../conect.php')){
		die('Sin conexion!!!');
	}
	extract($_GET);
	//product_provider
	//die($cant);
	//cant="+can+"&clave="+busqueda+"&id_pedido="+id_p
	/*$sql="SELECT pe.cantidad
			FROM ec_pedidos_detalle pe
			LEFT JOIN ec_productos p ON p.id_productos=pe.id_producto
			WHERE p.orden_lista='{$_GET['clave']}'
			AND pe.id_pedido='{$_GET['id_pedido']}'";*/
	$sql = "SELECT
                ax.product_id,
                ax.product_provider_id,
                ax.list_order,
                ax.product_name,
                ax.provider_clue,
                ax.validated_pieces,
				IF( dd.id_devolucion_detalle IS NULL, 
					0, 
					IF( ax.is_maquiled = 0,
						SUM(dd.cantidad),
						(SELECT
							ROUND( SUM( dd.cantidad ) / cantidad )
						FROM ec_productos_detalle
						WHERE id_producto = ax.product_id
						)
					) 
				) 
                AS returned
            FROM(
                SELECT
                  p.es_maquilado AS is_maquiled,
                  p.id_productos AS product_id,
                  pp.id_proveedor_producto AS product_provider_id,
                  p.orden_lista AS list_order,
                  p.nombre AS product_name,
                  pp.clave_proveedor AS provider_clue,
                  IF( p.es_maquilado = 0, 
                    SUM( pvu.piezas_validadas ),
                    (SELECT
                        ROUND( SUM( pvu.piezas_validadas ) / cantidad )
                      FROM ec_productos_detalle
                      WHERE id_producto = p.id_productos
                    )
                  ) AS validated_pieces,
				pd.id_pedido_detalle
                FROM ec_pedidos_validacion_usuarios pvu
                LEFT JOIN ec_pedidos_detalle pd
                ON pvu.id_pedido_detalle = pd.id_pedido_detalle
                LEFT JOIN ec_productos p 
                ON p.id_productos = pd.id_producto
                LEFT JOIN ec_proveedor_producto pp
                ON pp.id_proveedor_producto = pvu.id_proveedor_producto
                WHERE pd.id_pedido = {$_GET['id_pedido']}
                AND p.orden_lista = {$_GET['clave']}
                AND pvu.id_proveedor_producto = {$_GET['product_provider']}
                AND pd.id_pedido_detalle= {$_GET['id_pedido_detalle']}
                GROUP BY pd.id_pedido_detalle
			)ax
			LEFT JOIN ec_devolucion dev
			ON dev.id_pedido IN ( {$_GET['id_pedido']} )
			LEFT JOIN ec_devolucion_detalle dd
			ON dd.id_devolucion = dev.id_devolucion
			AND dd.id_proveedor_producto = ax.product_provider_id
			AND ax.product_provider_id = {$_GET['product_provider']}
			AND dd.id_pedido_detalle = {$_GET['id_pedido_detalle']}
			GROUP BY ax.id_pedido_detalle";
	//die( $sql );
	$eje=mysql_query($sql)or die("Error!!!\n\n".$sql."\n\n".mysql_error());
	$rw=mysql_fetch_row($eje);
	$en_pedido = $rw[5];// - $rw[6] comentado porque no se debe de restar la cantidad devuelta
	if( $cant > $en_pedido ){
		die('no');
	}else{
		echo 'ok';
	}
?>