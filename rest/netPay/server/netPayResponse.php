<?php
header('Content-Type: text/html; charset=utf-8');
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: netPayResponse
* Path: /netPayResponse
* Método: POST
* Descripción: Recupera respuesta de netPay
*/

$app->post('/', function (Request $request, Response $response){
  //die( 'here_1' );
  if( ! include( '../../conexionMysqli.php' ) ){
    die( "Error al incluir libreria de conexion!" );
  }
  if( ! include( '../../code/especiales/tesoreria/cobrosSmartAccounts/ajax/db.php' ) ){//oscar 2024-02-15
    die( "Error al incluir libreria de cobros!" );
  }
  //die( "here" );
//obtener el json en txt 
  /*$body = $request->getBody();
  $file = fopen("test.txt","w");
  fwrite($file,"{$body}");
  fclose($file);*/
  //try{
    $body = $request->getBody();
    //var_dump( $body );
    $file = fopen("archivo.txt", "w");
    fwrite($file,"{$body}");
    fclose($file);
  //}catch( Exception e ){
  //  die( "Error al escribir archivo txt : {$e}" );
  //}
//respuesta
  $response_message = "Transaccion exitosa!";
//recibe los parametros de netPay
  $affiliation = $request->getParam( "affiliation" );//1
  $applicationLabel = $request->getParam( "applicationLabel" );//2
  $arqc = $request->getParam( "arqc" );//3
  $aid = $request->getParam( "aid" );//4
  $amount = $request->getParam( "amount" );//5
  $authCode = $request->getParam( "authCode" );//6
  $bin = $request->getParam( "bin" );//7
  $bankName = $request->getParam( "bankName" );//8
  $cardExpDate = $request->getParam( "cardExpDate" );//9
  $cardType = $request->getParam( "cardType" );//10
  $cardTypeName = $request->getParam( "cardTypeName" );//11
  $cityName = $request->getParam( "cityName" );//12
  $responseCode = $request->getParam( "responseCode" );//13
  $folioNumber = $request->getParam( "folioNumber" );//14
  $hasPin = $request->getParam( "hasPin" );//15
  $hexSign = $request->getParam( "hexSign" );//16
  $isQps = $request->getParam( "isQps" );//17
  
  $message_ = $request->getParam( "message" );//utf8_encode($request->getParam( "message" ));//18
  $message_ = str_replace( 'á', 'a', $message_ );
  $message_ = str_replace( 'é', 'e', $message_ );
  $message_ = str_replace( 'í', 'i', $message_ );
  $message_ = str_replace( 'ó', 'o', $message_ );
  $message_ = str_replace( 'ú', 'u', $message_ );
  
  $isRePrint = $request->getParam( "isRePrint" );//19
  $moduleCharge = $request->getParam( "moduleCharge" );//20
  $moduleLote = $request->getParam( "moduleLote" );//21
  $customerName = $request->getParam( "customerName" );//22
  $terminalId = $request->getParam( "terminalId" );//23
  $orderId = $request->getParam( "orderId" );//24
  $preAuth = $request->getParam( "preAuth" );//25
  $preStatus = $request->getParam( "preStatus" );//26
  $promotion = $request->getParam( "promotion" );//27
  $rePrintDate = $request->getParam( "rePrintDate" );//28
  $rePrintMark = $request->getParam( "rePrintMark" );//29
  $reprintModule = $request->getParam( "reprintModule" );//30
  $cardNumber = $request->getParam( "cardNumber" );//31
  $storeName = $request->getParam( "storeName" );//32
  $streetName = $request->getParam( "streetName" );//33
  $ticketDate = $request->getParam( "ticketDate" );//34
  $tipAmount = $request->getParam( "tipAmount" );//35
  $tipLessAmount = $request->getParam( "tipLessAmount" );//36
  $transDate = $request->getParam( "transDate" );//37
  $transType = $request->getParam( "transType" );//38
  $transactionCertificate = $request->getParam( "transactionCertificate" );//39
  $transactionId = $request->getParam( "transactionId" );//40
  $traceability = $request->getParam( "traceability" );//41
  
  $transaction_unique_folio = $folioNumber;
  if( $traceability['folio_unico_transaccion'] != null && $traceability['folio_unico_transaccion'] != '' ){
    $transaction_unique_folio = $traceability['folio_unico_transaccion'];
  }
  //traceability
 // $traceability['']

//$file = fopen("archivo.txt", "w");
  $link->autocommit( false );
//actualiza la respuesta de la transaccion
  $sql = "UPDATE vf_transacciones_netpay SET 
            /*2*/affiliation = '{$affiliation}',
            /*3*/applicationLabel = '{$applicationLabel}',
            /*4*/arqc = '{$arqc}',
            /*5*/aid = '{$aid}',
            /*6*/amount = '{$amount}',
            /*7*/authCode = '{$authCode}',
            /*8*/bin = '{$bin}',
            /*9*/bankName = '{$bankName}',
            /*10*/cardExpDate = '{$cardExpDate}',
            /*11*/cardType = '{$cardType}',
            /*12*/cardTypeName = '{$cardTypeName}',
            /*13*/cityName = '{$cityName}',
            /*14*/responseCode = '{$responseCode}',
            /*15*/folioNumber = '{$folioNumber}',
            /*16*/hasPin = '{$hasPin}',
            /*17*/hexSign = '{$hexSign}',
            /*18*/isQps = '{$isQps}',
            /*19*/message = '{$message_}',
            /*20*/isRePrint = '{$isRePrint}',
            /*21*/moduleCharge = '{$moduleCharge}',
            /*22*/moduleLote = '{$moduleLote}',
            /*23*/customerName = '{$customerName}',
            /*24*/terminalId = '{$terminalId}',
            /*25*/orderId = '{$orderId}',
            /*26*/preAuth = '{$preAuth}',
            /*27*/preStatus = '{$preStatus}',
            /*28*/promotion = '{$promotion}',
            /*29*/rePrintDate = '{$rePrintDate}',
            /*30*/rePrintMark = '{$rePrintMark}',
            /*31*/reprintModule = '{$reprintModule}',
            /*32*/cardNumber = '{$cardNumber}',
            /*33*/storeName = '{$storeName}',
            /*34*/streetName = '{$streetName}',
            /*35*/ticketDate = '{$ticketDate}',
            /*36*/tipAmount = '{$tipAmount}',
            /*37*/tipLessAmount = '{$tipLessAmount}',
            /*38*/transDate = '{$transDate}',
            /*39*/transType = '{$transType}',
            /*40*/transactionCertificate = '{$transactionCertificate}',
            /*41*/transactionId = '{$transactionId}',
            /*42*/id_sucursal = '{$traceability['id_sucursal']}', 
            /*43*/id_cajero = '{$traceability['id_cajero']}', 
            /*44*/folio_venta = '{$traceability['folio_venta']}',
            /*44*/id_sesion_cajero = '{$traceability['id_sesion_cajero']}',
            /*45*/store_id_netpay = '{$traceability['store_id_netpay']}'
          WHERE folio_unico = '{$transaction_unique_folio}'";//$folioNumber
  $stm = $link->query( $sql ) or die( "Error al actualizar el registro de transaccion : {$link->error}" );
  if( trim($message_) == 'Transaccion exitosa' && $transType == 'A' ){
  //consulta los datos en relacion al numero de serie de la terminal
    $sql = "";
    if( isset( $traceability['smart_accounts'] ) && $traceability['smart_accounts'] == true ){
      
      $sql = "SELECT
              t.id_terminal_integracion AS affiliation_id,
              cc.id_caja_cuenta AS bank_id,
              (SELECT
              id_pedido FROM ec_pedidos
              WHERE folio_nv = '{$traceability['folio_venta']}'
              LIMIT 1
              ) AS sale_id
            FROM ec_terminales_integracion_smartaccounts t
            LEFT JOIN ec_caja_o_cuenta cc
            ON t.id_caja_cuenta = cc.id_caja_cuenta
            WHERE t.numero_serie_terminal = '{$terminalId}'
            AND t.store_id = '{$traceability['store_id_netpay']}'";

    }else{
    //  die( "here 1" );
      $sql = "SELECT 
                a.id_afiliacion AS affiliation_id,
                cc.id_caja_cuenta AS bank_id,
                (SELECT 
                  id_pedido FROM ec_pedidos 
                  WHERE folio_nv = '{$traceability['folio_venta']}' 
                  LIMIT 1
                ) AS sale_id
              FROM ec_afiliaciones a
              LEFT JOIN ec_caja_o_cuenta cc
              ON a.id_banco = cc.id_caja_cuenta
              WHERE a.numero_serie_terminal = '{$terminalId}'";
    }
  //  die( $sql );
    $stm = $link->query( $sql ) or die( "Error al recuperar datos para insertar el cobro del cajero {$link->error}" );
    $row = $stm->fetch_assoc();
    //consulta entre interno y externo
        $sql = "SELECT
                  ROUND( ax.internal/ax.total, 6 ) AS internal_porcent,
                  ROUND( ax.external/ax.total, 6 ) AS external_porcent
                FROM(
                  SELECT
                    SUM( pd.monto ) AS total,
                    SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
                    SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
                  FROM ec_pedidos_detalle pd
                  LEFT JOIN sys_sucursales_producto sp
                  ON pd.id_producto = sp.id_producto
                  AND sp.id_sucursal = {$traceability['id_sucursal']}
                  WHERE pd.id_pedido = {$row['sale_id']}
                )ax";
//die( $sql );
      $stm = $link->query( $sql ) or die( "Error al consultar porcentajes de pagos : {$sql} {$link->error}" );
  
//die( "here2" );
//die( "here 1.5" );
      $payment_row = $stm->fetch_assoc();//pagos de saldo a favor Oscar 2024-02-15
  
      $Payments = new Payments( $link, $traceability['id_sucursal'] );
      $Payments->insertPaymentsDepending( $amount, $row['sale_id'], $traceability['id_cajero'], $traceability['id_sesion_cajero'] );// $pago_por_saldo_a_favor
      if( $traceability['id_devolucion_relacionada'] != 0 && $traceability['id_devolucion_relacionada'] != '' && $traceability['id_devolucion_relacionada'] != null ){
        $Payments->reinsertaPagosPorDevolucionCaso2( $row['sale_id'], $traceability['id_cajero'], $traceability['id_sesion_cajero'], 'n/a', 0, 0 );
      }
      $internal_payment_id = '0';
      $external_payment_id = '0';
    //inserta pago interno    
      if( $payment_row['internal_porcent'] > 0 ){
        $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
        id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
        VALUES( {$row['sale_id']}, 7, NOW(), NOW(), ( {$amount}*{$payment_row['internal_porcent']} ), '', 1, 1, -1, -1, 0, 
          '{$traceability['id_cajero']}', '{$traceability['id_sesion_cajero']}' )";
        $stm = $link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$link->error}" );
       //die( $sql );
        $sql = "SELECT MAX( id_pedido_pago ) AS last_sale_payment_id FROM ec_pedido_pagos LIMIT 1";
        $aux_stm = $link->query( $sql ) or die( "Error al consultar el ultimo pago insertado (interno) : {$link->error}" );
        $aux_row = $aux_stm->fetch_assoc();
        $internal_payment_id = $aux_row['last_sale_payment_id'];
       //$internal_payment_id = $link->insert_id;
      }
    //inserta pago externo    
      if( $payment_row['external_porcent'] > 0 ){//aqui se modificó error de netPay ( solo externos ) Oscar 30-01-2024 desde development2024
        $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
        id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
        VALUES( {$row['sale_id']}, 7, NOW(), NOW(), ( {$amount}*{$payment_row['external_porcent']} ), '', 1, 1, -1, -1, 1, 
          '{$traceability['id_cajero']}', '{$traceability['id_sesion_cajero']}' )";
        $stm = $link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$sql} {$link->error}" );
        $sql = "SELECT MAX( id_pedido_pago ) AS last_sale_payment_id FROM ec_pedido_pagos LIMIT 1";
        $aux_stm = $link->query( $sql ) or die( "Error al consultar el ultimo pago insertado (externo) : {$link->error}" );
        $aux_row = $aux_stm->fetch_assoc();
        $external_payment_id = $aux_row['last_sale_payment_id'];
      }

  /*inserta el cobro del pedido
      $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
      id_nota_credito, id_cxc, es_externo )
      VALUES( {$row['sale_id']}, 7, NOW(), NOW(), {$amount}, '', 1, 1, -1, -1, 0 )";
      $stm = $link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$link->error}" );*/

//inserta el cobro del cajero si el cobro fue exitoso
    $sql = "INSERT INTO ec_cajero_cobros( /*1*/id_cajero_cobro, id_sucursal, /*2*/id_pedido, /*3*/id_cajero, /*4*/id_terminal, 
    /*5*/id_banco, /*6*/monto, /*7*/fecha, /*8*/hora, /*9*/observaciones, /*10*/sincronizar, /*11*/id_sesion_caja, /*12*/id_tipo_pago ) 
    VALUES ( /*1*/NULL, '{$traceability['id_sucursal']}', /*2*/'{$row['sale_id']}', /*3*/'{$traceability['id_cajero']}', /*4*/'{$row['affiliation_id']}', 
    /*5*/'{$row['bank_id']}', /*6*/'{$amount}', /*7*/NOW(), /*8*/NOW(), /*9*/'{$orderId}', /*10*/1, 
    /*11*/{$traceability['id_sesion_cajero']}, /*12*/7 )";
//    error_log( $sql );
    $stm = $link->query( $sql ) or die( "Error al insertar el cobro del cajero : {$link->error}" );
    $paymet_id = $link->insert_id;
//actualiza el id de sesion de caja del pedido 
    $sql = "UPDATE ec_pedidos SET id_cajero = {$traceability['id_cajero']}, id_sesion_caja = {$traceability['id_sesion_cajero']} WHERE id_pedido = {$row['sale_id']}";
    $stm_pedido = $link->query( $sql ) or die( "Error al actualizar ids de cajero y sesion de caja desde Webhook : {$link->error}" );
//actualiza el cajero de los cobros
//actualiza el id de cajero cobro en la transaccion
      $sql = "UPDATE vf_transacciones_netpay 
                SET id_cajero_cobro = '{$paymet_id}'
              WHERE id_transaccion_netpay = '{$folioNumber}'";
      $stm = $link->query( $sql ) or die( "Error al actualizar el cobro del cajero en la peticion : {$sql} {$link->error}" );
          
  //actualiza en la venta el id de cajero que cobro el pago*/
    if( $row['sale_id'] != null && $row['sale_id'] != '' ){
      $sql="UPDATE ec_pedidos 
              SET id_cajero = '{$traceability['id_cajero']}' 
              WHERE id_pedido = {$row['sale_id']}";
      $stm = $link->query( $sql ) or die( "Error al actualizar el pedido para este cajero : {$sql} {$link->error}" );

  //actualiza en el pago el id de cajero que cobro el pago Oscar 2023-01-10*/
    //if( $row['sale_id'] != null && $row['sale_id'] != '' ){
      $sql="UPDATE ec_pedido_pagos 
              SET id_cajero_cobro = '{$paymet_id}' 
              WHERE id_pedido_pago IN( {$internal_payment_id}, {$external_payment_id} )";
      $stm = $link->query( $sql ) or die( "Error al actualizar el pedido para este cajero : {$sql} {$link->error}" );

    //actualiza el id de cajero que cobro el pago*/
      $sql="UPDATE ec_pedido_pagos 
              SET id_cajero = '{$traceability['id_cajero']}',
              fecha = now(),
              hora = now() 
              WHERE id_pedido = {$row['sale_id']}
              AND id_cajero=0";
      $stm = $link->query( $sql ) or die( "Error al actualizar el pago para este cajero : {$sql} {$link->error}" );
    }
/*$fp = fopen('data.txt', 'w');
fwrite($fp, $sql );
fclose($fp);*/
  }
  $link->autocommit( true );
  $resp = array(
    "code"=>"00",
    "message"=>$message_
  );
  return json_encode( $resp );
  //die('');
});
?>
