<?php
//ok 2023/11/25
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_devoluciones
* Path: /inserta_devoluciones
* Método: GET
* Descripción: Insercion de devoluciones
*/

$app->post('/barre_y_envia_ventas_a_administracion_facturacion', function (Request $request, Response $response){
//libreria de conexion
	if ( ! include( '../../conexionMysqli.php' ) ){
    	die( 'No se incluyó libreria conexionMysqli.php' );
  	}
  	$link->set_charset("utf8mb4");
    if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
        die( "No se incluyó : SynchronizationManagmentLog.php" );
    }
    $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
//echo 'pasa_1';
  	$link->set_charset("utf8mb4");
    //consulta cabecera de venta
	$sql = "SELECT 
            p.id_pedido, 
            p.folio_pedido, 
            p.folio_nv, 
            p.folio_factura, 
            p.folio_cotizacion, 
            p.id_cliente, 
            p.id_estatus, 
            p.id_moneda, 
            p.fecha_alta, 
            p.fecha_factura, 
            p.id_razon_social, 
            p.subtotal, 
            p.iva, 
            p.ieps, 
            p.total, 
            p.dias_proximo, 
            p.pagado, 
            p.surtido, 
            p.enviado, 
            p.id_sucursal, 
            p.id_usuario, 
            p.fue_cot, 
            p.facturado, 
            p.id_tipo_envio, 
            p.descuento, 
            p.id_razon_factura, 
            p.folio_abono, 
            p.correo, 
            p.facebook, 
            p.modificado, 
            p.ultima_sincronizacion, 
            p.ultima_modificacion, 
            p.tipo_pedido, 
            p.id_status_agrupacion, 
            p.id_cajero, 
            p.id_devoluciones, 
            p.venta_validada, 
            p.folio_unico, 
            p.id_sesion_caja, 
            p.tipo_sistema, 
            p.monto_pago_inicial, 
            p.cobro_finalizado,
            p.id_status_facturacion 
    FROM ec_pedidos p
    LEFT JOIN ec_cajero_cobros cc
    ON p.id_pedido = cc.id_pedido
    WHERE p.id_status_facturacion <= 2
    AND ( cc.id_terminal > 0 || cc.id_afiliacion > 0 )
    GROUP BY p.id_pedido";
    $stm = $link->query( $sql );
    if( $link->error ){
        return json_encode( array( "status"=>400, "message"=>"Error al consultar las cabeceras de notas de venta pendientes de subir a administracion facturacion : {$sql} : {$link->error}" ) );
    }
    $sales_array = array();
    while( $sale_header = $stm->fetch_assoc() ){//echo "here";
        //consigue la razon social de la nota de venta
        $sale_header['id_razon_social'] = getSaleSocialReason( $sale_header['id_pedido'], $store_id, $link );
        //consulta detalle de la venta
        $sql = "SELECT 
                    id_pedido_detalle, 
                    id_pedido, 
                    id_producto, 
                    cantidad, 
                    precio, 
                    monto, 
                    iva, 
                    ieps, 
                    cantidad_surtida, 
                    descuento, 
                    modificado, 
                    es_externo, 
                    id_precio, 
                    folio_unico 
                FROM ec_pedidos_detalle 
                WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm_2 = $link->query( $sql );
        if( $link->error ){
            return json_encode( array( "status"=>400, "message"=>"Error al consultar el detalle de la nota de venta : {$sql} : {$link->error}" ) );
        }
        while( $detail_row = $stm_2->fetch_assoc() ){
            $sale_detail[] = $detail_row;
        }
    //consulta los cobros
        $sql = "SELECT 
                    id_cajero_cobro, 
                    id_sucursal, 
                    id_pedido, 
                    id_devolucion, 
                    id_cajero, 
                    id_sesion_caja, 
                    id_afiliacion, 
                    id_terminal, 
                    id_banco, 
                    id_tipo_pago, 
                    monto, 
                    fecha, 
                    hora, 
                    observaciones, 
                    cobro_cancelado,
                    folio_unico, 
                    sincronizar,
                    id_tipo_pago,
                    id_forma_pago 
                FROM ec_cajero_cobros 
                WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm_3 = $link->query( $sql );
        if( $link->error ){
            return json_encode( array( "status"=>400, "message"=>"Error al consultar cobros de la nota de venta : {$sql} : {$link->error}" ) );
        }
        while( $payments = $stm_3->fetch_assoc() ){
            $sale_payments[] = $payments;
        }
    //consulta los pagos
        $sql = "SELECT 
                    id_pedido_pago, 
                    id_pedido, 
                    id_cajero_cobro, 
                    id_tipo_pago, 
                    fecha, 
                    hora, 
                    monto, 
                    referencia, 
                    id_moneda, 
                    tipo_cambio, 
                    id_nota_credito, 
                    id_cxc, 
                    exportado, 
                    es_externo, 
                    id_cajero, 
                    folio_unico, 
                    sincronizar, 
                    id_sesion_caja, 
                    pago_cancelado 
                FROM ec_pedido_pagos
                WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm_4 = $link->query( $sql );
        if( $link->error ){
            return json_encode( array( "status"=>400, "message"=>"Error al consultar pagos de la nota de venta : {$sql} : {$link->error}" ) );
        }
        while( $payments_detail = $stm_4->fetch_assoc() ){
            $sale_payments_detail[] = $payments_detail;
        }
        $sql = "UPDATE ec_pedidos SET id_status_facturacion = 2, id_razon_social = {$sale_header['id_razon_social']} WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm_5 = $link->query( $sql );
        if( $link->error ){
            return json_encode( array( "status"=>400, "message"=>"Error al actualizar status de facturacion de la venta : {$sql} : {$link->error}" ) );
        }
        array_push( $sales_array, array( "venta"=>$sale_header, "venta_detalle"=>$sale_detail, "cobros"=>$sale_payments, "pagos"=>$sale_payments_detail  ) );
        //$post_data = json_encode( array( "venta"=>$sale_header, "venta_detalle"=>$sale_detail, "cobros"=>$sale_payments, "pagos"=>$sale_payments_detail  ) );
    }
//echo "pasa_2";
    $post_data = json_encode( array( "sales"=>$sales_array ) );
    //return $post_data;
//consulta el path de API Facturacion 
    $sql = "SELECT `value` AS api_path FROM api_config WHERE `name` = 'path_facturacion'";
    $stm = $link->query( $sql ) or die( "Error al consultar el path del API de Facturación : {$sql} : {$link->error}" );
    $row = $stm->fetch_assoc();
    $url = "{$row['api_path']}/rest/inserta_venta_facturacion";
//// echo $url;
    //envia peticion
    $petition = $SynchronizationManagmentLog->sendPetition( $url, $post_data, '' );
    //die( "respuesta : " . $petition );
    $response = json_decode( $petition );
    if( $response->status == 200 ){
        foreach ($response->exitosos as $key => $folio) {
            $sql = "UPDATE ec_pedidos SET id_status_facturacion = 3 WHERE folio_nv = '{$response->exitosos[$key]}'";//echo $sql;
            $stm = $link->query( $sql ) or die( "Error al actualizar status de facturacion de la venta : {$sql} : {$link->error}" );
        }
    }else{
        return json_encode( array( "status"=>400, "message"=>"Error al recibir respuesta", "message_detail"=>$petition) );
    }
    return json_encode( array( "status"=>200, "message"=>"Barrido exitoso." ) );
});


    function getSaleSocialReason( $sale_id, $store_id, $link ){
    //consulta si los pagos fueron en efectivo
        $sql = "SELECT DISTINCT( id_tipo_pago ) AS payment_type FROM ec_cajero_cobros WHERE id_pedido = {$sale_id}";
        $stm = $link->query( $sql ) or die( "Error al consultar los tipo de pagos : {$sql} : {$link->error}" );
        if( $stm->num_rows == 1 ){//una sola forma de pago
            $row = $stm->fetch_assoc();
            if( $row['payment_type'] == 1 ){//si solo fue pagada en efectivo
            //consulta el id de la razon social configurada en la sucursal
                $sql = "SELECT id_razon_social FROM sys_sucursales WHERE id_sucursal = {$store_id}";
                $stm = $link->query( $sql ) or die ( "Error al consultar la razon social configurada en la sucursal : {$sql} : {$link->error}" );
                $row = $stm->fetch_assoc();
                return $row['id_razon_social'];
            }
        }
    //consulta el pago mas alto y su razon social
        $sql = "SELECT 
                    cc.id_afiliacion,
                    cc.id_terminal
                FROM ec_cajero_cobros cc
                WHERE cc.id_pedido = {$sale_id}
                AND ( cc.id_afiliacion > 0 OR cc.id_terminal > 0 )
                ORDER BY cc.monto DESC
                LIMIT 1";
        $stm = $link->query( $sql ) or die( "Error al consultar pagos con tarjeta : {$sql} : {$link->error}" );
        $row = $stm->fetch_assoc();
        $sql = "";
        if( $row['id_afiliacion'] > 0 && $row['id_afiliacion'] != '' ){
            $sql = "SELECT 
                        cc.id_razon_social
                    FROM ec_afiliaciones a
                    LEFT JOIN ec_caja_o_cuenta cc
                    ON a.id_banco = cc.id_caja_cuenta
                    WHERE a.id_afiliacion = {$row['id_afiliacion']}";
            $stm = $link->query( $sql ) or die( "Error al consultar la razon social de la afiliación : {$sql} : {$link->error}" );
            $row = $stm->fetch_assoc();
            return $row['id_razon_social'];
        }else if( $row['id_terminal'] > 0 && $row['id_terminal'] != '' ){
            $sql = "SELECT 
                        cc.id_razon_social
                    FROM ec_terminales_integracion_smartaccounts t
                    LEFT JOIN ec_caja_o_cuenta cc
                    ON t.id_caja_cuenta = cc.id_caja_cuenta
                    WHERE t.id_terminal_integracion = {$row['id_terminal']}";
            $stm = $link->query( $sql ) or die( "Error al consultar la razon social de la afiliación : {$sql} : {$link->error}" );
            $row = $stm->fetch_assoc();
            return $row['id_razon_social'];
        }
    }