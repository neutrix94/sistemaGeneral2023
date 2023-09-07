SELECT
    ax3.product_provider_id AS 'ID PROVEEDOR PRODUCTO',
    ax3.orden_lista AS 'ORDEN DE LISTA',
    ax3.nombre AS 'PRODUCTO',
    ax3.inventario_inicial AS 'INVENTARIO INICIAL',
    ax3.pedido AS 'PEDIDO',
    ax3.recibidoRecepcion AS 'CANTIDAD RECEPCION',
    ax3.reception_dates AS 'FECHAS DE RECEPCION',
    ax3.cantidad_validada AS 'CANTIDAD VALIDADA',
    ax3.fecha_validacion AS 'FECHA DE VALIDACION',
    ax3.datos_remision AS 'DATOS DE REMISION',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'PROYECCION INVENTARIO PIEZAS',
    ax3.piezas_por_paquete AS 'PIEZAS POR PAQUETE',
    ax3.piezas_por_caja AS 'PIEZAS POR CAJA',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) / ax3.piezas_por_caja AS 'PROYECCION INVENTARIO CAJAS',
    ax3.letra_ubicacion_desde AS 'LETRA DESDE',
    ax3.numero_ubicacion_desde AS 'NUMERO DESDE',
    ax3.pasillo_desde AS 'PASILLO DESDE',
    ax3.altura_desde AS 'ALTURA DESDE',
    ax3.letra_ubicacion_hasta AS 'LETRA HASTA',
    ax3.numero_ubicacion_hasta AS 'NUMERO HASTA',
    ax3.pasillo_hasta AS 'PASILLO HASTA',
    ax3.altura_hasta AS 'ALTURA HASTA'
FROM(
    SELECT
        ax2.product_provider_id,
        ax2.orden_lista,
        ax2.nombre,
        ax2.inventario_inicial,
        SUM( IF( ocd.id_oc_detalle IS NULL, 0, ocd.cantidad - ocd.cantidad_surtido ) ) AS 'pedido',
        ax2.recibidoRecepcion,
        ax2.reception_dates,
        ax2.cantidad_validada,
        ax2.fecha_validacion,
        ax2.datos_remision,
        ax2.piezas_por_paquete,
        ax2.piezas_por_caja,
        ax2.letra_ubicacion_desde,
        ax2.numero_ubicacion_desde,
        ax2.pasillo_desde,
        ax2.altura_desde,
        ax2.letra_ubicacion_hasta,
        ax2.numero_ubicacion_hasta,
        ax2.pasillo_hasta,
        ax2.altura_hasta
    FROM
    (
        SELECT
            ax1.product_provider_id,
            ax1.orden_lista,
            ax1.nombre,
            SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL,0, ( tm.afecta * mdpp.cantidad ) ) ) AS inventario_inicial,
            ax1.recibidoRecepcion,
            ax1.reception_dates,
            ax1.piezas_por_paquete,
            ax1.piezas_por_caja,
            ax1.cantidad_validada,
            ax1.fecha_validacion,
            ax1.datos_remision,
            ax1.letra_ubicacion_desde,
            ax1.numero_ubicacion_desde,
            ax1.pasillo_desde,
            ax1.altura_desde,
            ax1.letra_ubicacion_hasta,
            ax1.numero_ubicacion_hasta,
            ax1.pasillo_hasta,
            ax1.altura_hasta
        FROM(
            SELECT
                ax.product_provider_id,
                ax.orden_lista,
                ax.nombre,
                ax.recibidoRecepcion,
                ax.reception_dates,
                ax.piezas_por_paquete,
                ax.piezas_por_caja,
                SUM( IF( ord.id_oc_recepcion_detalle IS NULL,0, piezas_recibidas ) ) AS cantidad_validada,
                GROUP_CONCAT( CONCAT( '( ', DATE_FORMAT( ocr.fecha_recepcion, '%d-%m-%Y' ), ' )' ) SEPARATOR ' * ' ) AS fecha_validacion,
                GROUP_CONCAT( CONCAT( '( ', DATE_FORMAT( ocr.fecha_remision, '%d-%m-%Y' ), ' * ', ocr.folio_referencia_proveedor, ' )') SEPARATOR ' - - - ' ) AS datos_remision,
                ax.letra_ubicacion_desde,
                ax.numero_ubicacion_desde,
                ax.pasillo_desde,
                ax.altura_desde,
                ax.letra_ubicacion_hasta,
                ax.numero_ubicacion_hasta,
                ax.pasillo_hasta,
                ax.altura_hasta
            FROM(
                SELECT
                    IF(pp.id_proveedor_producto IS NULL, 'No tiene', pp.id_proveedor_producto) AS 'product_provider_id',
                    p.orden_lista,
                    CONCAT( p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' MODELO : ', pp.clave_proveedor ) ) ) AS nombre,
                    IF( rbd.id_recepcion_bodega_detalle IS NULL, 
                       0,
                       SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
                    ) AS recibidoRecepcion,
                    GROUP_CONCAT( DATE_FORMAT( rb.fecha_alta, '%d-%m-%Y' ) SEPARATOR ' * ' ) AS reception_dates,
                    pp.piezas_presentacion_cluces AS 'piezas_por_paquete',
                    pp.presentacion_caja AS 'piezas_por_caja',
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.letra_ubicacion_desde ) AS letra_ubicacion_desde,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.numero_ubicacion_desde ) AS numero_ubicacion_desde,
                     IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.pasillo_desde ) AS pasillo_desde,
                     IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.altura_desde ) AS altura_desde,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.letra_ubicacion_hasta ) AS letra_ubicacion_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.numero_ubicacion_hasta ) AS numero_ubicacion_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.pasillo_hasta ) AS pasillo_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.altura_hasta ) AS altura_hasta
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
                WHERE p.id_productos > 0
                $_FAMILIA
                $_TIPO
                $_SUBTIPO
                AND pp.id_proveedor_producto > 0
                GROUP BY p.id_productos, pp.id_proveedor_producto  
                ORDER BY `recibidoRecepcion`  DESC
            )ax
            LEFT JOIN ec_oc_recepcion_detalle ord
            ON ax.product_provider_id = ord.id_proveedor_producto
            LEFT JOIN ec_oc_recepcion ocr
            ON ocr.id_oc_recepcion = ord.id_oc_recepcion
            AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' )
            GROUP BY ax.product_provider_id
        )ax1
        LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
        ON mdpp.id_proveedor_producto = ax1.product_provider_id
        AND mdpp.fecha_registro <= '$_FECHA_INICIAL 23:59:59'
        LEFT JOIN ec_tipos_movimiento tm
        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
        GROUP BY ax1.product_provider_id
    )ax2
    LEFT JOIN ec_oc_detalle ocd
    ON ocd.id_proveedor_producto = ax2.product_provider_id
    LEFT JOIN ec_ordenes_compra oc
    ON oc.id_orden_compra = ocd.id_orden_compra
    AND ( oc.fecha BETWEEN '$_FECHA_1' AND '$_FECHA_2' )
    GROUP BY ax2.product_provider_id
)ax3
GROUP BY ax3.product_provider_id
ORDER BY ax3.orden_lista


/*modificado por Oscar 22 Agosto*/

SELECT
    ax3.product_provider_id AS 'ID PROVEEDOR PRODUCTO',
    ax3.orden_lista AS 'ORDEN DE LISTA',
    ax3.nombre AS 'PRODUCTO',
    ax3.inventario_inicial AS 'INVENTARIO INICIAL',
    ax3.pedido AS 'PEDIDO',
    ax3.recibidoRecepcion AS 'CANTIDAD RECEPCION',
    ax3.reception_dates AS 'FECHAS DE RECEPCION',
    ax3.cantidad_validada AS 'CANTIDAD VALIDADA',
    ax3.fecha_validacion AS 'FECHA DE VALIDACION',
    ax3.datos_remision AS 'DATOS DE REMISION',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'PROYECCION INVENTARIO PIEZAS',
    ax3.piezas_por_paquete AS 'PIEZAS POR PAQUETE',
    ax3.piezas_por_caja AS 'PIEZAS POR CAJA',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) / ax3.piezas_por_caja AS 'PROYECCION INVENTARIO CAJAS',
    ax3.letra_ubicacion_desde AS 'LETRA DESDE',
    ax3.numero_ubicacion_desde AS 'NUMERO DESDE',
    ax3.pasillo_desde AS 'PASILLO DESDE',
    ax3.altura_desde AS 'ALTURA DESDE',
    ax3.letra_ubicacion_hasta AS 'LETRA HASTA',
    ax3.numero_ubicacion_hasta AS 'NUMERO HASTA',
    ax3.pasillo_hasta AS 'PASILLO HASTA',
    ax3.altura_hasta AS 'ALTURA HASTA'
FROM(
    SELECT
        ax2.product_provider_id,
        ax2.orden_lista,
        ax2.nombre,
        ax2.inventario_inicial,
        SUM( IF( ocd.id_oc_detalle IS NOT NULL/* AND ( oc.fecha BETWEEN '$_FECHA_1' AND '$_FECHA_2' )*/, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS 'pedido',
        ax2.recibidoRecepcion,
        ax2.reception_dates,
        ax2.cantidad_validada,
        ax2.fecha_validacion,
        ax2.datos_remision,
        ax2.piezas_por_paquete,
        ax2.piezas_por_caja,
        ax2.letra_ubicacion_desde,
        ax2.numero_ubicacion_desde,
        ax2.pasillo_desde,
        ax2.altura_desde,
        ax2.letra_ubicacion_hasta,
        ax2.numero_ubicacion_hasta,
        ax2.pasillo_hasta,
        ax2.altura_hasta
    FROM
    (
        SELECT
            ax1.product_provider_id,
            ax1.orden_lista,
            ax1.nombre,
            SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NOT NULL AND mdpp.fecha_registro <= '$_FECHA_INICIAL 23:59:59', ( tm.afecta * mdpp.cantidad ), 0 ) ) AS inventario_inicial,
            ax1.recibidoRecepcion,
            ax1.reception_dates,
            ax1.piezas_por_paquete,
            ax1.piezas_por_caja,
            ax1.cantidad_validada,
            ax1.fecha_validacion,
            ax1.datos_remision,
            ax1.letra_ubicacion_desde,
            ax1.numero_ubicacion_desde,
            ax1.pasillo_desde,
            ax1.altura_desde,
            ax1.letra_ubicacion_hasta,
            ax1.numero_ubicacion_hasta,
            ax1.pasillo_hasta,
            ax1.altura_hasta
        FROM(
            SELECT
                ax.product_provider_id,
                ax.orden_lista,
                ax.nombre,
                ax.recibidoRecepcion,
                ax.reception_dates,
                ax.piezas_por_paquete,
                ax.piezas_por_caja,
                SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada,
                GROUP_CONCAT( CONCAT( '( ', DATE_FORMAT( ocr.fecha_recepcion, '%d-%m-%Y' ), ' )' ) SEPARATOR ' * ' ) AS fecha_validacion,
                GROUP_CONCAT( CONCAT( '( ', DATE_FORMAT( ocr.fecha_remision, '%d-%m-%Y' ), ' * ', ocr.folio_referencia_proveedor, ' )') SEPARATOR ' - - - ' ) AS datos_remision,
                ax.letra_ubicacion_desde,
                ax.numero_ubicacion_desde,
                ax.pasillo_desde,
                ax.altura_desde,
                ax.letra_ubicacion_hasta,
                ax.numero_ubicacion_hasta,
                ax.pasillo_hasta,
                ax.altura_hasta
            FROM(
                SELECT
                    IF(pp.id_proveedor_producto IS NULL, 'No tiene', pp.id_proveedor_producto) AS 'product_provider_id',
                    p.orden_lista,
                    CONCAT( p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' MODELO : ', pp.clave_proveedor ) ) ) AS nombre,
                    IF( rbd.id_recepcion_bodega_detalle IS NULL, 
                       0,
                       SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
                    ) AS recibidoRecepcion,
                    GROUP_CONCAT( DATE_FORMAT( rb.fecha_alta, '%d-%m-%Y' ) SEPARATOR ' * ' ) AS reception_dates,
                    pp.piezas_presentacion_cluces AS 'piezas_por_paquete',
                    pp.presentacion_caja AS 'piezas_por_caja',
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.letra_ubicacion_desde ) AS letra_ubicacion_desde,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.numero_ubicacion_desde ) AS numero_ubicacion_desde,
                     IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.pasillo_desde ) AS pasillo_desde,
                     IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.altura_desde ) AS altura_desde,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.letra_ubicacion_hasta ) AS letra_ubicacion_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.numero_ubicacion_hasta ) AS numero_ubicacion_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.pasillo_hasta ) AS pasillo_hasta,
                    IF( ppua.id_ubicacion_matriz IS NULL, '', ppua.altura_hasta ) AS altura_hasta
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
                WHERE p.id_productos > 0
                $_FAMILIA
                $_TIPO
                $_SUBTIPO
                AND pp.id_proveedor_producto > 0
                GROUP BY p.id_productos, pp.id_proveedor_producto  
                ORDER BY `recibidoRecepcion`  DESC
            )ax
            LEFT JOIN ec_oc_recepcion_detalle ord
            ON ax.product_provider_id = ord.id_proveedor_producto
            LEFT JOIN ec_oc_recepcion ocr
            ON ocr.id_oc_recepcion = ord.id_oc_recepcion
            AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' )
            GROUP BY ax.product_provider_id
        )ax1
        LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
        ON mdpp.id_proveedor_producto = ax1.product_provider_id
        AND mdpp.fecha_registro <= '$_FECHA_INICIAL 23:59:59'
        LEFT JOIN ec_tipos_movimiento tm
        ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
        GROUP BY ax1.product_provider_id
    )ax2
    LEFT JOIN ec_oc_detalle ocd
    ON ocd.id_proveedor_producto = ax2.product_provider_id
    LEFT JOIN ec_ordenes_compra oc
    ON oc.id_orden_compra = ocd.id_orden_compra
   /* AND ( oc.fecha BETWEEN '$_FECHA_1' AND '$_FECHA_2' )*/
    GROUP BY ax2.product_provider_id
)ax3
GROUP BY ax3.product_provider_id
ORDER BY ax3.orden_lista


/**/
SELECT
    ax3.product_provider_id AS 'ID PROVEEDOR PRODUCTO',
    ax3.nombre AS 'PRODUCTO',
    ax3.inventario_inicial AS 'INVENTARIO INICIAL',
    ax3.pedido AS 'PEDIDO',
    ax3.recibidoRecepcion AS 'CANTIDAD RECEPCION',
    ax3.cantidad_validada AS 'CANTIDAD VALIDADA',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) AS 'PROYECCION INVENTARIO PIEZAS',
    ax3.piezas_por_paquete AS 'PIEZAS POR PAQUETE',
    ax3.piezas_por_caja AS 'PIEZAS POR CAJA',
    ( ax3.inventario_inicial + ax3.pedido + ax3.recibidoRecepcion + ax3.cantidad_validada ) / ax3.piezas_por_caja AS 'PROYECCION INVENTARIO CAJAS'
FROM(
    SELECT
        ax2.product_provider_id,
        ax2.nombre,
        ax2.inventario_inicial,
        SUM( IF( ocd.id_oc_detalle IS NOT NULL/* AND ( oc.fecha BETWEEN '2022-08-20' AND '2022-08-20' )*/, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS 'pedido',
        ax2.recibidoRecepcion,
        ax2.cantidad_validada,
        ax2.piezas_por_paquete,
        ax2.piezas_por_caja
    FROM
    (
        SELECT
            ax1.product_provider_id,
            ax1.nombre,
            SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NOT NULL AND mdpp.fecha_registro <= '2022-07-15 23:59:59', ( tm.afecta * mdpp.cantidad ), 0 ) ) AS inventario_inicial,
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
                SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '2022-08-20 00:00:01' AND '2022-08-20 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada
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
                AND ( rb.fecha_alta BETWEEN '2022-08-20 00:00:01' AND '2022-08-20 23:59:59' )
                AND rb.id_status_validacion IN( 1, 2  )
                AND rb.id_recepcion_bodega_status IN(  2, 3  )
                WHERE p.id_productos > 0
                AND pp.id_proveedor_producto > 0
                GROUP BY p.id_productos, pp.id_proveedor_producto  
                ORDER BY `recibidoRecepcion`  DESC
            )ax
            LEFT JOIN ec_oc_recepcion_detalle ord
            ON ax.product_provider_id = ord.id_proveedor_producto
            LEFT JOIN ec_oc_recepcion ocr
            ON ocr.id_oc_recepcion = ord.id_oc_recepcion
            AND ( ocr.fecha_recepcion BETWEEN '2022-08-20 00:00:01' AND '2022-08-20 23:59:59' )
            GROUP BY ax.product_provider_id
        )ax1
        LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
        ON mdpp.id_proveedor_producto = ax1.product_provider_id
        AND mdpp.fecha_registro <= '2022-07-15 23:59:59'
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
GROUP BY ax3.product_provider_id

