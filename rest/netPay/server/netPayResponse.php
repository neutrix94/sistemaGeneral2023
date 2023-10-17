<?php
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
//obtener el json en txt 
  $body = $request->getBody();
  $file = fopen("test.txt","w");
  fwrite($file,"{$body}");
  fclose($file);
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
  $message = $request->getParam( "message" );//18
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
  
  $transactionId_internal = $folioNumber;
  if( $traceability['petition_id'] != null && $traceability['petition_id'] != '' ){
    $transactionId_internal = $traceability['petition_id'];
  }

  //traceability
 // $traceability['']

//$file = fopen("archivo.txt", "w");
//inserta la respuesta de la transaccion
  $sql = "INSERT INTO vf_transacciones_netpay ( /*1*/id_transaccion_netpay, /*2*/affiliation,/*3*/applicationLabel,/*4*/arqc,/*5*/aid,/*6*/amount,
    /*7*/authCode,/*8*/bin,/*9*/bankName,/*10*/cardExpDate,/*11*/cardType,/*12*/cardTypeName,/*13*/cityName,/*14*/responseCode,/*15*/folioNumber,
    /*16*/hasPin,/*17*/hexSign,/*18*/isQps,/*19*/message,/*20*/isRePrint,/*21*/moduleCharge,/*22*/moduleLote,/*23*/customerName,/*24*/terminalId,
    /*25*/orderId,/*26*/preAuth,/*27*/preStatus,/*28*/promotion,/*29*/rePrintDate,/*30*/rePrintMark,/*31*/reprintModule,/*32*/cardNumber,
    /*33*/storeName,/*34*/streetName,/*35*/ticketDate,/*36*/tipAmount,/*37*/tipLessAmount,/*38*/transDate,/*39*/transType,/*40*/transactionCertificate,
    /*41*/transactionId, /*42*/id_sucursal, /*43*/id_cajero, /*44*/folio_venta )
      VALUES( /*1*/NULL, /*2*/'{$affiliation}',/*3*/'{$applicationLabel}',/*4*/'{$arqc}',/*5*/'{$aid}',/*6*/'{$amount}',
    /*7*/'{$authCode}',/*8*/'{$bin}',/*9*/'{$bankName}',/*10*/'{$cardExpDate}',/*11*/'{$cardType}',/*12*/'{$cardTypeName}',/*13*/'{$cityName}',
    /*14*/'{$responseCode}',/*15*/'{$folioNumber}',/*16*/'{$hasPin}',/*17*/'{$hexSign}',/*18*/'{$isQps}',/*19*/'{$message}',/*20*/'{$isRePrint}',
    /*21*/'{$moduleCharge}',/*22*/'{$moduleLote}',/*23*/'{$customerName}',/*24*/'{$terminalId}',/*25*/'{$orderId}',/*26*/'{$preAuth}',
    /*27*/'{$preStatus}',/*28*/'{$promotion}',/*29*/'{$rePrintDate}',/*30*/'{$rePrintMark}',/*31*/'{$reprintModule}',/*32*/'{$cardNumber}',
    /*33*/'{$storeName}',/*34*/'{$streetName}',/*35*/'{$ticketDate}',/*36*/'{$tipAmount}',/*37*/'{$tipLessAmount}',/*38*/'{$transDate}',
    /*39*/'{$transType}',/*40*/'{$transactionCertificate}',/*41*/'{$transactionId}',/*42*/'{$traceability['id_sucursal']}', 
    /*43*/'{$traceability['id_cajero']}', /*44*/'{$traceability['folio_venta']}' )";
  
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
            /*19*/message = '{$message}',
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
          WHERE id_transaccion_netpay = '{$transactionId_internal}'";//$folioNumber
  $stm = $link->query( $sql ) or die( "Error al actualizar el registro de transaccion : {$link->error}" );
  if( trim($message) == 'Transacción exitosa' ){
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
            WHERE t.numero_serie_terminal = '{$terminalId}'";

    }else{
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
    $stm = $link->query( $sql ) or die( "Error al recuperar datos para insertar el cobro del cajero {$link->error}" );
    $row = $stm->fetch_assoc();

//inserta el cobro del cajero si oel cobro fue exitoso
    $sql = "INSERT INTO ec_cajero_cobros( /*1*/id_cajero_cobro, /*2*/id_pedido, /*3*/id_cajero, /*4*/id_afiliacion, 
    /*5*/id_banco, /*6*/monto, /*7*/fecha, /*8*/hora, /*9*/observaciones, /*10*/sincronizar ) 
    VALUES ( /*1*/NULL, /*2*/'{$row['sale_id']}', /*3*/'{$traceability['id_cajero']}', /*4*/'{$row['affiliation_id']}', 
    /*5*/'{$row['bank_id']}', /*6*/'{$amount}', /*7*/NOW(), /*8*/NOW(), /*9*/'{$orderId}', /*10*/1 )";
//    error_log( $sql );
//actualiza el cajero de los cobros
    $stm = $link->query( $sql ) or die( "Error al insertar el cobro del cajero : {$link->error}" );
    $paymet_id = $link->insert_id;
//actualiza el id de cajero cobro en la transaccion
      $sql = "UPDATE vf_transacciones_netpay 
                SET id_cajero_cobro = '{$paymet_id}'
              WHERE id_transaccion_netpay = '{$folioNumber}'";
      $stm = $link->query( $sql ) or die( "Error al actualizar el cobro del cajero en la peticion : {$sql} {$link->error}" );
          
  //actualiza el id de cajero que cobro el pago*/
    if( $row['sale_id'] != null && $row['sale_id'] != '' ){
      $sql="UPDATE ec_pedidos 
              SET id_cajero = '{$traceability['id_cajero']}' 
              WHERE id_pedido = {$row['sale_id']}";
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
  $resp = array(
    "code"=>"00",
    "message"=>$message
  );
  return json_encode( $resp );
});
?>
