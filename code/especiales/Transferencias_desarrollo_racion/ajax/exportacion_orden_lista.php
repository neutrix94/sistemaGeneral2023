<?php
    include( '../../../../conexionMysqli.php' );//libreria de conexion
    $idTransfer = ( isset( $_POST['idTransfer'] ) ? $_POST['idTransfer'] : $_GET['idTransfer'] );
//consulta datos de transferencia
    $sql = "SELECT
                t.folio,
                t.id_sucursal_origen,
                t.id_sucursal_destino,
                t.id_almacen_origen,
                t.id_almacen_destino,
                REPLACE( t.titulo_transferencia , ',', '') AS titulo_transferencia,
                (SELECT DATE_FORMAT( NOW(), '%Y' ) ) AS current_year
            FROM ec_transferencias t
            WHERE t.id_transferencia = {$idTransfer}";
    $stm = $link->query( $sql ) or die( "Error al consultar datos de la transferencia : {$sql} : {$link->error}" );
    $row = $stm->fetch_assoc();
    $al_origen = $row['id_almacen_origen'];
    $al_destino = $row['id_almacen_destino'];
    $origen = $row['id_sucursal_origen'];
    $destino = $row['id_sucursal_destino'];
    $titulo = $row['titulo_transferencia'];
    $folio_transferencia = $row['folio'];
    $ano_actual = $row['current_year'];
    $file_name = str_replace( " ", "", $file_name );
    $file_name = "exportacion_{$row['folio']}.csv";
//consulta las familias de la transferencia en relacion al detalle de sus productos
    $sql = "SELECT
                GROUP_CONCAT( DISTINCT( c.id_categoria ) SEPARATOR ',' ) As familias
            FROM ec_transferencia_productos tp
            LEFT JOIN ec_productos p
            ON tp.id_producto_or = p.id_productos
            LEFT JOIN ec_categoria c
            ON c.id_categoria = p.id_categoria
            WHERE tp.id_transferencia = {$idTransfer}";
    $stm = $link->query( $sql ) or die( "Error al consultar familias de la transferencia : {$sql} : {$link->error}" );
    $row = $stm->fetch_assoc();
    $categorias = $row['familias'];
    $sql = "SELECT
                ax4.id_producto,
                ax4.orden_lista,
                ax4.nombre_producto,
                ( ax4.inventario_inicial + ax4.pedido + ax4.recibidoRecepcion + ax4.cantidad_validada ) AS proyeccion_piezas,
                ax4.inventarioTotal,
                ax4.piezas_recibidas,
                ax4.piezas_solicitadas,
                ax4.estacionalidad,
                ax4.nombre_familia,
                ax4.numero_consecutivo,
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
                WHERE id_producto = ax4.id_producto
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
                WHERE id_producto = ax4.id_producto
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
                WHERE id_producto = ax4.id_producto
                AND pr_n.id_categoria_nota = 3
                ) AS 'NOTAS_EXHIBICION_EXTERIOR',
                ax4.observaciones
            FROM(
                SELECT
                    ax3.id_producto,
                    ax3.orden_lista,
                    ax3.nombre_producto,
                    ax3.piezas_recibidas,
                    ax3.piezas_solicitadas,
                    ax3.numero_consecutivo,
                    ax3.observaciones,
                    ax3.nombre_familia,
                    ax3.estacionalidad,
                    ax3.recibidoRecepcion,
                    ax3.cantidad_validada,
                    ax3.inventario_inicial,
                    ax3.inventario_inicial_otros_movs,
                    ax3.inventarioTotal,     
                    SUM( IF( ocd.id_oc_detalle IS NOT NULL, ( ocd.cantidad - ocd.cantidad_surtido ), 0 ) ) AS pedido
                FROM(
                    SELECT
                        ax2.id_producto,
                        ax2.orden_lista,
                        ax2.nombre_producto,
                        ax2.piezas_recibidas,
                        ax2.piezas_solicitadas,
                        ax2.numero_consecutivo,
                        ax2.observaciones,
                        ax2.nombre_familia,
                        ax2.estacionalidad,
                        ax2.recibidoRecepcion,
                        ax2.cantidad_validada,
                        SUM( IF( md.id_movimiento_almacen_detalle IS NOT NULL AND ma.fecha <= '$_FECHA_INICIAL', ( tm.afecta * md.cantidad ), 0 ) ) AS inventario_inicial,
                        SUM( IF( md.id_movimiento_almacen_detalle IS NOT NULL AND ( ma.fecha BETWEEN '$_FECHA_1' AND '$_FECHA_2') AND ma.id_tipo_movimiento NOT IN(1), ( tm.afecta * md.cantidad ), 0 ) ) AS inventario_inicial_otros_movs,
                        SUM( IF( md.id_movimiento_almacen_detalle IS NULL OR ma.id_movimiento_almacen IS NULL, 0, ( md.cantidad * tm.afecta ) ) ) AS inventarioTotal                
                    FROM(
                        SELECT
                            ax1.id_producto,
                            ax1.orden_lista,
                            ax1.nombre_producto,
                            ax1.piezas_recibidas,
                            ax1.piezas_solicitadas,
                            ax1.numero_consecutivo,
                            ax1.observaciones,
                            ax1.nombre_familia,
                            ax1.estacionalidad,
                            ax1.recibidoRecepcion,
                            SUM( IF( ord.id_oc_recepcion_detalle IS NOT NULL AND ( ocr.fecha_recepcion BETWEEN '$_FECHA_1 00:00:01' AND '$_FECHA_2 23:59:59' ), ord.piezas_recibidas, 0 ) ) AS cantidad_validada            
                        FROM(
                            SELECT
                                ax.id_producto,
                                ax.orden_lista,
                                ax.nombre_producto,
                                ax.piezas_recibidas,
                                ax.piezas_solicitadas,
                                ax.numero_consecutivo,
                                ax.observaciones,
                                ax.nombre_familia,
                                ax.estacionalidad,
                                IF( rbd.id_recepcion_bodega_detalle IS NULL, 
                                    0,
                                    SUM( ax.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
                                ) AS recibidoRecepcion
                            FROM(
                                SELECT
                                    p.id_productos AS id_producto,
                                    p.orden_lista,
                                    p.nombre AS nombre_producto,
                                    pp.presentacion_caja,
                                    IF( tp.id_transferencia_producto IS NULL, 'n/a',tp.total_piezas_recibidas ) AS piezas_recibidas,
                                    IF( tp.id_transferencia_producto IS NULL, 'n/a',tp.cantidad ) AS piezas_solicitadas,
                                    IF( tp.id_transferencia_producto IS NULL, 'n/a', tp.numero_consecutivo ) AS numero_consecutivo,
                                    REPLACE( REPLACE( p.observaciones, '\n', '' ), ',', '' ) AS observaciones,
                                    c.nombre AS nombre_familia,
                                    ep.maximo AS estacionalidad
                                FROM ec_productos p
                                LEFT JOIN ec_proveedor_producto pp
                                ON p.id_productos = pp.id_producto
                                LEFT JOIN ec_transferencia_productos tp
                                ON tp.id_producto_or = p.id_productos
                                LEFT JOIN ec_transferencias t
                                ON tp.id_transferencia = t.id_transferencia
                                AND t.folio = '{$folio_transferencia}'
                                LEFT JOIN ec_categoria c
                                ON c.id_categoria = p.id_categoria
                                LEFT JOIN ec_estacionalidad_producto ep
                                ON ep.id_producto = p.id_productos
                                AND ep.id_estacionalidad IN( SELECT id_estacionalidad FROM sys_sucursales WHERE id_sucursal = {$destino} )
                                WHERE p.id_productos > 0
                                AND p.id_categoria IN( {$categorias})
                                AND p.nombre NOT IN( 'Libre' )
                                AND p.id_productos > 0
                                AND p.muestra_paleta = 0
                                AND p.es_maquilado = 0
                                GROUP BY p.id_productos
                            )ax
                            LEFT JOIN ec_recepcion_bodega_detalle rbd
                            ON rbd.id_producto = ax.id_producto
                            AND rbd.validado IN( 0  )
                            LEFT JOIN ec_recepcion_bodega rb 
                            ON rb.id_recepcion_bodega = rbd.id_recepcion_bodega
                            AND rb.fecha_alta LIKE '%{$ano_actual}%'
                            AND rb.id_status_validacion IN( 1, 2  )
                            AND rb.id_recepcion_bodega_status IN(  2, 3  )
                            GROUP BY ax.id_producto
                        )ax1
                        LEFT JOIN ec_oc_recepcion_detalle ord
                        ON ax1.id_producto = ord.id_producto
                        LEFT JOIN ec_oc_recepcion ocr
                        ON ocr.id_oc_recepcion = ord.id_oc_recepcion
                        AND ocr.fecha_recepcion LIKE '%{$ano_actual}%'
                        GROUP BY ax1.id_producto
                    )ax2
                    LEFT JOIN ec_movimiento_detalle md
                    ON md.id_producto = ax2.id_producto
                    LEFT JOIN ec_movimiento_almacen ma 
                    ON md.id_movimiento = ma.id_movimiento_almacen
                    LEFT JOIN ec_tipos_movimiento tm
                    ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
                    GROUP BY ax2.id_producto
                )ax3
                LEFT JOIN ec_oc_detalle ocd
                ON ocd.id_producto = ax3.id_producto
                LEFT JOIN ec_ordenes_compra oc
                ON oc.id_orden_compra = ocd.id_orden_compra
                GROUP BY ax3.id_producto
            )ax4
            GROUP BY ax4.id_producto
            ORDER BY ax4.orden_lista";
    $stm = $link->query( $sql ) or die( "Error al consultar detalle de transferencia : {$sql} : {$link->error}" );
    
    header('Content-Type: aplication/octect-stream');
    header('Content-Transfer-Encoding: Binary');
    header('Content-Disposition: attachment; filename="'.$file_name.'"');
    //echo(utf8_decode($info));
    echo "Id producto,Orden de lista,Nombre,Proyeccion de Inventario, Inv Origen,Recibido,Estacionalidad,Pedido,Familia,Numero caja,Observaciones exhibicion muro,Observaciones exhibicion colgar,Observaciones exhibicion exterior,Observaciones del producto";
    while( $row = $stm->fetch_assoc() ){
        echo "\n{$row['id_producto']},{$row['orden_lista']},{$row['nombre_producto']},{$row['proyeccion_piezas']},{$row['inventarioTotal']},";
        echo "{$row['piezas_recibidas']},{$row['estacionalidad']},{$row['piezas_solicitadas']},{$row['nombre_familia']},{$row['numero_consecutivo']},";
        echo "{$row['NOTAS_EXHIBICION_MURO']},{$row['NOTAS_EXHIBICION_COLGAR']},{$row['NOTAS_EXHIBICION_EXTERIOR']},{$row['observaciones']}";
    }
    die('');
?>