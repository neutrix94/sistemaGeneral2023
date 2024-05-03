<?php
    include( '../../../conexionMysqli.php' );
//consulta datos de movimientos de almacen
    $sql = "SELECT * FROM ec_movimiento_almacen WHERE id_movimiento_almacen IN(  )";
    $stm = $link->query( $sql )or die( "Error al consultar los datos de movimientos de almacen : {$link->error}" );
$link->autocommit( false );
    while( $ma = $stm->fetch_assoc() ){
    //inserta datos de movimientos de almacen
        $sql = "INSERT INTO ec_movimiento_almacen ( id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, 
        id_transferencia, id_almacen, status_agrupacion, insertado_por_sincronizacion, id_pantalla, sincronizar ) VALUES ( 13, {$ma['id_usuario']}, {$ma['id_sucursal']}, 
        NOW(), NOW(), CONCAT( 'Movimiento para anular movimiento de almacen ' , '{$ma['folio_unico']}' ), {$ma['id_pedido']}, {$ma['id_orden_compra']}, '{$ma['lote']}', {$ma['id_maquila']}, 
        {$ma['id_transferencia']}, {$ma['id_almacen']}, {$ma['status_agrupacion']}, -1, 1 )";
        $stm_1 = $link->query( $sql ) or die( "Error al insertar la cabecera de movimiento de almacen : {$link->error}" );
        $sql = "SELECT MAX( id_movimiento_almacen ) As id FROM ec_movimiento_almacen";
        $max_id_stm = $link->query( $sql ) or die( "Err 1 : {$link->error}" );
        $row_id = $max_id_stm->fetch_assoc();
        $ma['nuevo_id'] = $row_id['id'];
    //consulta el detalle
        $sql = "SELECT * FROM ec_movimiento_detalle WHERE id_movimiento = {$ma['id_movimiento_almacen']}";
        $stm_2 = $link->query( $sql ) or die( "Error al consultar detalle de movimiento de almacen : {$link->error}" );
        while( $md = $stm_2->fetch_assoc() ){
        //inserta detalle
            $sql = "INSERT INTO ec_movimiento_detalle ( id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle,
            id_oc_detalle, id_proveedor_producto, id_equivalente, sincronizar ) VALUES ( {$md['nuevo_id']}, {$md['id_producto']}, {$md['cantidad']}, 
            {$md['cantidad_surtida']}, {$md['id_pedido_detalle']}, {$md['id_oc_detalle']}, {$md['id_proveedor_producto']}, {$md['id_equivalente']}, 1 )";
            $stm_3 = $link->query( $sql ) or die( "Error al insertar la cabecera de movimiento de almacen : {$link->error}" );
            $sql = "SELECT MAX( id_movimiento_almacen_detalle ) AS id FROM ec_movimiento_detalle";
            $max_id_stm = $link->query( $sql ) or die( "Err 1 : {$link->error}" );
            $row_id = $max_id_stm->fetch_assoc();
            $md['nuevo_id'] = $row_id['id'];
        //consulta detalle de movimiento detalle proveedor producto
            $sql = "SELECT * FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_almacen_detalle = {$md['id_movimiento_almacen_detalle']}";
            $stm_4 = $link->query( $sql ) or die( "Error al consultar detalle proveedor producto de movimiento de almacen : {$link->error}" );
            while ( $mdpp = $stm_4->fetch_assoc() ){
            //inserta detalle de movimiento detalle proveedor producto
               $sql = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_almacen_detalle, id_proveedor_producto, cantidad, fecha_registro, 
               id_sucursal, status_agrupacion, id_tipo_movimiento, id_almacen, id_pedido_validacion, sincronizar ) VALUES ( {$md['nuevo_id']}, {$mdpp['id_proveedor_producto']}, 
               {$mdpp['cantidad']}, {$mdpp['fecha_registro']}, {$mdpp['id_sucursal']}, {$mdpp['status_agrupacion']}, {$mdpp['id_tipo_movimiento']}, {$mdpp['id_almacen']}, {$mdpp['id_pedido_validacion']}, 1 )";
                $stm_5 = $link->query( $sql ) or die( "Error al insertar detalle proveedor producto de movimiento de almacen : {$link->error}" ); 
            }
        }
    }
$link->autocommit( true );
    die( "Movimientos de almacen anulados exitosamente." );
?>