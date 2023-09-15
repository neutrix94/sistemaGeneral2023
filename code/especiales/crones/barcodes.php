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

/*implementacion Oscar 2023*/
    $sql = "SELECT
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) SEPARATOR '<br>' ) AS product_provider_id,
                pp.codigo_barras_pieza_1 AS piece_barcode_1,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_pieza_1 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_pieza_1
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra pieza 1 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra pieza 1 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB pieza 1', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra pieza 1 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) SEPARATOR '<br>' ) AS product_provider_id,
                pp.codigo_barras_pieza_2 AS piece_barcode_2,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_pieza_2 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_pieza_2
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra pieza 2 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra pieza 2 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB pieza 2', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra pieza 2 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) SEPARATOR '<br>' ) AS product_provider_id,
                pp.codigo_barras_pieza_3 AS piece_barcode_3,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_pieza_3 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_pieza_3
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra pieza 3 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra pieza 3 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB pieza 3', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra pieza 3 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) SEPARATOR '<br>' ) AS product_provider_id,
                pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_presentacion_cluces_1 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_presentacion_cluces_1
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra paquete 1 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra paquete 1 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB paquete 1', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra paquete 1 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) ) AS product_provider_id,
                pp.codigo_barras_presentacion_cluces_2 AS pack_barcode_2,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_presentacion_cluces_2 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_presentacion_cluces_2
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra paquete 2 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra paquete 2 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB paquete 2', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra paquete 2 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) ) AS product_provider_id,
                pp.codigo_barras_caja_1 AS box_barcode_1,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_caja_1 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_caja_1
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra caja 1 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra caja 1 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB caja 1', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra caja 1 repetidos : </h2>' ) 
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
                GROUP_CONCAT( DISTINCT( p.id_productos ) SEPARATOR '<br>' )AS product_id,
                GROUP_CONCAT( DISTINCT( p.orden_lista ) SEPARATOR '<br>' ) AS list_order,
                GROUP_CONCAT( DISTINCT( p.nombre ) SEPARATOR '<br>' ) AS products_names,
                GROUP_CONCAT( DISTINCT( pp.id_proveedor_producto ) ) AS product_provider_id,
                pp.codigo_barras_caja_2 AS box_barcode_2,
                COUNT( pp.id_proveedor_producto ) AS repeat_counter
            FROM ec_proveedor_producto pp
            LEFT JOIN ec_productos p
            ON pp.id_producto = p.id_productos
            WHERE pp.codigo_barras_caja_2 != ''
            AND p.id_productos > 0
            GROUP BY pp.codigo_barras_caja_2
            HAVING COUNT( pp.id_proveedor_producto ) > 1";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra caja 2 repetidos : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra caja 2 repetidos</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB caja 2', 'Veces repetido' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra caja 2 repetidos : </h2>' ) 
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
                ax.product_id,
                ax.list_order,
                ax.products_name,
                ax.product_provider_id_1,
                ax.piece_barcode_1,
                ax.product_provider_id_2,
                ax.pack_barcode_1,
                ax.product_provider_id_3,
                ax.box_barcode_1
            FROM(
                SELECT
                    p.id_productos AS product_id,
                    p.orden_lista AS list_order,
                    p.nombre AS products_name,

                    pp.id_proveedor_producto AS product_provider_id_1,
                    IF( pp.codigo_barras_pieza_1 = '',
                        1,
                        INSTR( pp.codigo_barras_pieza_1 , LPAD( pp.id_proveedor_producto, 5, '0' ) ) 
                    )AS piece_barcode_1_exists,
                    pp.codigo_barras_pieza_1 AS piece_barcode_1,

                    pp.id_proveedor_producto AS product_provider_id_2,
                    IF( pp.codigo_barras_presentacion_cluces_1 = '',
                        1,
                        INSTR( pp.codigo_barras_presentacion_cluces_1, LPAD( pp.id_proveedor_producto, 5, '0' ) )  
                    ) AS piece_pack_1_exists,
                    pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1,

                    pp.id_proveedor_producto AS product_provider_id_3,
                    IF( pp.codigo_barras_caja_1 = '',
                        1,
                        INSTR( pp.codigo_barras_caja_1 , LPAD( pp.id_proveedor_producto, 5, '0' ) )
                    ) AS box_barcode_1_exists,
                    pp.codigo_barras_caja_1 AS box_barcode_1
                FROM ec_proveedor_producto pp
                LEFT JOIN ec_productos p
                ON pp.id_producto = p.id_productos
                WHERE p.id_productos > 0
            )ax
            WHERE ax.piece_barcode_1_exists <= 0 
            OR ax.piece_pack_1_exists <= 0 
            OR ax.box_barcode_1_exists <= 0";
    $stm = $link->query( $sql ) or die( "Error al consultar codigos de barra que no contienen el id proveedor producto : {$link->error}" );
    if( $stm->num_rows <= 0 ){
        $mensaje .= "<br><h2 style=\"color : green;\">No se encontraron codigos de barra que no contienen proveedor producto</h2><br>";
    }else{
        $encabezado = array('#', 'Id Producto', 'Orden de lista', 'Producto', 'Id proveedor producto', 
            'CB pieza 1', 'Id proveedor producto', 'CB paquete 1', 'Id proveedor producto', 'CB caja 1' );
        $mensaje .= ( $es_navegador == 1 ? 
                    $report->csv_header_generator( $encabezado ) : 
                    $report->crea_tabla_log( $encabezado, '<h2 style="color : red;">Se localizaron los siguientes codigos de barra que no contienen el proveedor producto : </h2>' ) 
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
/*fin de cambio Oscar 2023*/
?>