SELECT
    ax4.id_productos AS 'ID PROVEEDOR PRODUCTO',
    ax4.orden_lista AS 'ORDEN DE LISTA',
    ax4.nombre AS 'PRODUCTO',
    ax4.proyeccion_piezas AS 'PROYECCION INVENTARIO PIEZAS',
    
    ax4.inventario_inicial AS 'INVENTARIO INICIAL',
    ax4.pedido AS 'PEDIDO',
    ax4.recibidoRecepcion AS 'CANTIDAD RECEPCION',
    ax4.cantidad_validada AS 'CANTIDAD VALIDADA',
    ax4.inventarioTotal AS 'INVENTARIO ACTUAL ( TODOS LOS ALMACENES )',
    SUM( IF( pd.id_pedido_detalle IS NOT NULL AND ( p.fecha_alta BETWEEN '$_FECHA_VENTAS_INIC 00:00:01' AND '$_FECHA_VENTAS_FIN 00:00:01' ), pd.cantidad, 0 ) ) AS 'VENTAS PERIODO ANTERIOR',
    ax4.inventario_inicial_otros_movs AS OTROS_MOVS,
    ax4.observaciones AS 'NOTAS GENERALES',
    (SELECT
        IF( pr_n.id_producto_nota IS NULL, 
            '',
            GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
        )
    FROM ec_productos_notas pr_n
    LEFT JOIN ec_productos_categorias_notas pcn
    ON pr_n.id_categoria_nota = pcn.id_categoria_nota
    LEFT JOIN ec_productos_valores_notas pvn
    ON pvn.id_valor_nota = pr_n.id_valor_nota
    WHERE id_producto = ax4.id_productos
    AND pr_n.id_categoria_nota = 3
    ) AS 'NOTAS_EXHIBICION_MURO',

    (SELECT
        IF( pr_n.id_producto_nota IS NULL, 
            '',
            GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
        )
    FROM ec_productos_notas pr_n
    LEFT JOIN ec_productos_categorias_notas pcn
    ON pr_n.id_categoria_nota = pcn.id_categoria_nota
    LEFT JOIN ec_productos_valores_notas pvn
    ON pvn.id_valor_nota = pr_n.id_valor_nota
    WHERE id_producto = ax4.id_productos
    AND pr_n.id_categoria_nota = 3
    ) AS 'NOTAS_EXHIBICION_COLGAR',

    (SELECT
        IF( pr_n.id_producto_nota IS NULL, 
            '',
            GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
        )
    FROM ec_productos_notas pr_n
    LEFT JOIN ec_productos_categorias_notas pcn
    ON pr_n.id_categoria_nota = pcn.id_categoria_nota
    LEFT JOIN ec_productos_valores_notas pvn
    ON pvn.id_valor_nota = pr_n.id_valor_nota
    WHERE id_producto = ax4.id_productos
    AND pr_n.id_categoria_nota = 3
    ) AS 'NOTAS_EXHIBICION_EXTERIOR'
FROM(
    SELECT
        ax3.id_productos,
        ax3.orden_lista,
        ax3.nombre,
        ax3.inventario_inicial,
        ax3.inventario_inicial_otros_movs,
        ax3.pedido,
        ax3.recibidoRecepcion,
        ax3.cantidad_validada,
        ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'proyeccion_piezas',
        ax3.observaciones,
        ax3.inventarioTotal
    FROM(
        SELECT
            ax2.id_productos,
            ax2.orden_lista,
            ax2.nombre,
            ax2.inventario_inicial,
            ax2.inventario_inicial_otros_movs,
            SUM( IF( ocd.id_oc_detalle IS NOT NULL, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS 'pedido',
            ax2.recibidoRecepcion,
            ax2.cantidad_validada,
            ax2.observaciones,
            ax2.inventarioTotal
        FROM
        (
            SELECT
                ax1.id_productos,
                ax1.orden_lista,
                ax1.nombre,
                SUM( IF( md.id_movimiento_almacen_detalle IS NOT NULL AND ma.fecha <= '$_FECHA_INICIAL', ( tm.afecta * md.cantidad ), 0 ) ) AS inventario_inicial,
                SUM( IF( md.id_movimiento_almacen_detalle IS NOT NULL AND ( ma.fecha BETWEEN '$_FECHA_1' AND '$_FECHA_2') AND ma.id_tipo_movimiento NOT IN(1), ( tm.afecta * md.cantidad ), 0 ) ) AS inventario_inicial_otros_movs,
                SUM( IF( md.id_movimiento_almacen_detalle IS NULL OR ma.id_movimiento_almacen IS NULL, 0, ( md.cantidad * tm.afecta ) ) ) AS inventarioTotal,
                ax1.recibidoRecepcion,
                ax1.cantidad_validada,
                ax1.observaciones
            FROM(
                SELECT
                    ax.id_productos,
                    ax.orden_lista,
                    ax.nombre,
                    ax.recibidoRecepcion,
                    SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada,
                    ax.observaciones
                FROM(
                    SELECT
                        p.id_productos,
                        p.orden_lista,
                        p.nombre AS nombre,
                        IF( rbd.id_recepcion_bodega_detalle IS NULL, 
                           0,
                           SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
                        ) AS recibidoRecepcion,
                        p.observaciones
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
                    AND ( rb.fecha_alta BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' )
                    AND rb.id_status_validacion IN( 1, 2  )
                    AND rb.id_recepcion_bodega_status IN(  2, 3  )
                    RIGHT JOIN ec_transferencia_productos tp
                    ON tp.id_producto_or
                    JOIN ec_transferencias t
                    ON tp.id_transferencia = t.id_transferencia
                    WHERE p.id_productos > 0
                    AND t.folio_transferencia = '$_transfer_folio'
                    AND p.nombre NOT IN( 'Libre' )
                    AND p.id_productos > 0
                    AND p.muestra_paleta = 0
                    AND p.es_maquilado = 0
                    GROUP BY p.id_productos
                )ax
                LEFT JOIN ec_oc_recepcion_detalle ord
                ON ax.id_productos = ord.id_producto
                LEFT JOIN ec_oc_recepcion ocr
                ON ocr.id_oc_recepcion = ord.id_oc_recepcion
                AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' )
                GROUP BY ax.id_productos
            )ax1
            LEFT JOIN ec_movimiento_detalle md
            ON md.id_producto = ax1.id_productos
            LEFT JOIN ec_movimiento_almacen ma 
            ON md.id_movimiento = ma.id_movimiento_almacen
            LEFT JOIN ec_tipos_movimiento tm
            ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
            GROUP BY ax1.id_productos
        )ax2
        LEFT JOIN ec_oc_detalle ocd
        ON ocd.id_producto = ax2.id_productos
        LEFT JOIN ec_ordenes_compra oc
        ON oc.id_orden_compra = ocd.id_orden_compra
        GROUP BY ax2.id_productos
    )ax3
    GROUP BY ax3.id_productos
    ORDER BY ax3.orden_lista
)ax4
LEFT JOIN ec_pedidos_detalle pd 
ON pd.id_producto = ax4.id_productos
LEFT JOIN ec_pedidos p 
ON p.id_pedido = pd.id_pedido
AND ( p.fecha_alta BETWEEN '$_FECHA_VENTAS_INIC 00:00:01' AND '$_FECHA_VENTAS_FIN 00:00:01' )
GROUP BY ax4.id_productos
ORDER BY ax4.orden_lista