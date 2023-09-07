<?php
    $sql = "SELECT
                ax.orden_lista,
                ax.product_name AS producto,
                ax.product_provider_id AS id_proveedor_producto,
                ax.pieces_per_box AS piezas_por_caja,
                ax.box_barcode_1 AS codigo_caja_1,
                ax.pieces_box_barcode_cj1 AS piezas_codigo_caja_1,
                ax.box_barcode_2 AS codigo_caja_2,
                ax.pieces_box_barcode_cj2 AS piezas_codigo_caja_2
            FROM
            (
                SELECT
                    p.orden_lista,
                    p.nombre AS product_name,
                    pp.id_proveedor_producto AS product_provider_id,
                    pp.presentacion_caja AS pieces_per_box,
                   
                    pp.codigo_barras_caja_1 AS box_barcode_1,
                    
                    SUBSTRING_INDEX(
                         SUBSTRING_INDEX(pp.codigo_barras_caja_1, ' ', 2 ),
                     'CJ' , -1) AS pieces_box_barcode_cj1,
                    
                    pp.codigo_barras_caja_2 AS box_barcode_2,
                    
                    SUBSTRING_INDEX(
                         SUBSTRING_INDEX(pp.codigo_barras_caja_2, ' ', 2),
                     'CJ', -1 ) AS pieces_box_barcode_cj2
                    
                FROM ec_proveedor_producto pp
                LEFT JOIN ec_productos p
                ON p.id_productos = pp.id_producto
                WHERE pp.id_proveedor_producto > 0
                GROUP BY pp.id_proveedor_producto
            )ax
            WHERE ( ax.pieces_per_box != ax.pieces_box_barcode_cj1 AND ax.pieces_box_barcode_cj1 != '' )
            OR ( ax.pieces_per_box != ax.pieces_box_barcode_cj2 AND ax.pieces_box_barcode_cj2 != '' )
            GROUP BY ax.product_provider_id";
    $stm = $link->query( $sql ) or die( "Error al consultar diferncias en codigos de barras : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron diferencias en los codigos de barras de cajas</h2><br>";
    }else{
        $encabezado = array('#', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'Piezas por caja', 'Codigo caja 1', 'Piezas codigo_caja_1', 'Codigo caja 2', 'Piezas codigo caja 2' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron las siguientes diferencias entre los codigos de barras de cajas: </h2>' ) 
        );//crea encabezado de la tabla
        $contenido_tabla = '';
        while ( $row = $stm->fetch_assoc() ) {
            $fallas ++;//suma una falla al reporte que se envia por correo
        //crea una fila por cada registro encontrado con diferencias
            $contenido_tabla .= ( $es_navegador == 1 ? 
                                $report->csv_row_generator( $row ) : 
                                $report->crea_fila_tabla_log( $row ) );
        }
    //agrega el contenido a la tabla
        if ( $es_navegador == 0 ){
            $mensaje .= str_replace('|table_content|', $contenido_tabla, $mensaje);
        }else{
            $mensaje .= $contenido_tabla;
        }

    }


    $sql = "SELECT
                ax.orden_lista,
                ax.product_name AS producto,
                ax.product_provider_id AS id_proveedor_producto,
                ax.pieces_per_box AS piezas_por_caja,
                ax.box_barcode_1 AS codigo_caja_1,
                ax.pieces_box_barcode_cj1 AS piezas_codigo_caja_1,
                ax.box_barcode_2 AS codigo_caja_2,
                ax.pieces_box_barcode_cj2 AS piezas_codigo_caja_2
            FROM
            (
                SELECT
                    p.orden_lista,
                    p.nombre AS product_name,
                    pp.id_proveedor_producto AS product_provider_id,
                    pp.presentacion_caja AS pieces_per_box,
                   
                    pp.codigo_barras_caja_1 AS box_barcode_1,
                    
                    SUBSTRING_INDEX(
                         SUBSTRING_INDEX(pp.codigo_barras_caja_1, ' ', 2 ),
                     'CJ' , -1) AS pieces_box_barcode_cj1,
                    
                    pp.codigo_barras_caja_2 AS box_barcode_2,
                    
                    SUBSTRING_INDEX(
                         SUBSTRING_INDEX(pp.codigo_barras_caja_2, ' ', 2),
                     'CJ', -1 ) AS pieces_box_barcode_cj2
                    
                FROM ec_proveedor_producto pp
                LEFT JOIN ec_productos p
                ON p.id_productos = pp.id_producto
                WHERE pp.id_proveedor_producto > 0
                GROUP BY pp.id_proveedor_producto
            )ax
            WHERE ( ax.pieces_per_box != ax.pieces_box_barcode_cj1 AND ax.pieces_box_barcode_cj1 != '' )
            OR ( ax.pieces_per_box != ax.pieces_box_barcode_cj2 AND ax.pieces_box_barcode_cj2 != '' )
            GROUP BY ax.product_provider_id";
    $stm = $link->query( $sql ) or die( "Error al consultar diferncias en codigos de barras : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron diferencias en los codigos de barras de paquetes</h2><br>";
    }else{
        $encabezado = array('#', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'Piezas por caja', 'Codigo caja 1', 'Piezas codigo_caja_1', 'Codigo caja 2', 'Piezas codigo caja 2' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron las siguientes diferencias entre los codigos de barras de paquetes: </h2>' ) 
        );//crea encabezado de la tabla
        $contenido_tabla = '';
        while ( $row = $stm->fetch_assoc() ) {
            $fallas ++;//suma una falla al reporte que se envia por correo
        //crea una fila por cada registro encontrado con diferencias
            $contenido_tabla .= ( $es_navegador == 1 ? 
                                $report->csv_row_generator( $row ) : 
                                $report->crea_fila_tabla_log( $row ) );
        }
    //agrega el contenido a la tabla
        if ( $es_navegador == 0 ){
            $mensaje .= str_replace('|table_content|', $contenido_tabla, $mensaje);
        }else{
            $mensaje .= $contenido_tabla;
        }

    }
?>