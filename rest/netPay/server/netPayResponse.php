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

  //traceability
 // $traceability['']

//$file = fopen("archivo.txt", "w");
//inserta la respuesta de la transaccion
  $sql = "INSERT INTO vf_transacciones_netpay ( /*1*/id_transaccion_netpay, /*2*/affiliation,/*3*/applicationLabel,/*4*/arqc,/*5*/aid,/*6*/amount,
    /*7*/authCode,/*8*/bin,/*9*/bankName,/*10*/cardExpDate,/*11*/cardType,/*12*/cardTypeName,/*13*/cityName,/*14*/responseCode,/*15*/folioNumber,
    /*16*/hasPin,/*17*/hexSign,/*18*/isQps,/*19*/message,/*20*/isRePrint,/*21*/moduleCharge,/*22*/moduleLote,/*23*/customerName,/*24*/terminalId,
    /*25*/orderId,/*26*/preAuth,/*27*/preStatus,/*28*/promotion,/*29*/rePrintDate,/*30*/rePrintMark,/*31*/reprintModule,/*32*/cardNumber,
    /*33*/storeName,/*34*/streetName,/*35*/ticketDate,/*36*/tipAmount,/*37*/tipLessAmount,/*38*/transDate,/*39*/transType,/*40*/transactionCertificate,
    /*41*/transactionId )
      VALUES( /*1*/NULL, /*2*/'{$affiliation}',/*3*/'{$applicationLabel}',/*4*/'{$arqc}',/*5*/'{$aid}',/*6*/'{$amount}',
    /*7*/'{$authCode}',/*8*/'{$bin}',/*9*/'{$bankName}',/*10*/'{$cardExpDate}',/*11*/'{$cardType}',/*12*/'{$cardTypeName}',/*13*/'{$cityName}',
    /*14*/'{$responseCode}',/*15*/'{$folioNumber}',/*16*/'{$hasPin}',/*17*/'{$hexSign}',/*18*/'{$isQps}',/*19*/'{$message}',/*20*/'{$isRePrint}',
    /*21*/'{$moduleCharge}',/*22*/'{$moduleLote}',/*23*/'{$customerName}',/*24*/'{$terminalId}',/*25*/'{$orderId}',/*26*/'{$preAuth}',
    /*27*/'{$preStatus}',/*28*/'{$promotion}',/*29*/'{$rePrintDate}',/*30*/'{$rePrintMark}',/*31*/'{$reprintModule}',/*32*/'{$cardNumber}',
    /*33*/'{$storeName}',/*34*/'{$streetName}',/*35*/'{$ticketDate}',/*36*/'{$tipAmount}',/*37*/'{$tipLessAmount}',/*38*/'{$transDate}',
    /*39*/'{$transType}',/*40*/'{$transactionCertificate}',/*41*/'{$transactionId}' )";
  
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
            /*41*/transactionId = '{$transactionId}'
          WHERE id_transaccion_netpay = '{$folioNumber}'";
  $stm = $link->query( $sql ) or die( "Error al actualizar el registro de transaccion : {$link->error}" );
//inserta el cobro del cajero
  
  $resp = array(
    "code"=>"00",
    "message"=>$message
  );
  return json_encode( $resp );
});
?>
