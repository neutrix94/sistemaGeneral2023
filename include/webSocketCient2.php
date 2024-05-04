<?php
error_reporting( E_ALL );
require '../rest/netPay/utils/encriptacion_token.php';

require_once("../vendor/autoload.php");    
$loop = \React\EventLoop\Factory::create();

$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);
$Encrypt = new Encrypt();
$token = '7dff3c34-faee-11ea-a7be-3d014d7f956c';
$token = $Encrypt->encryptText( $token, '' );

$client = new \Devristo\Phpws\Client\WebSocket("wss://m9dksnfd-3003.usw3.devtunnels.ms/{$token}", $loop, $logger);

/*Este se puede habilitar para log ( creacion request )
$client->on("request", function($headers) use ($logger){
    $logger->notice("Request object created!");
});*/

/*notifica la conexion ( acuse de request )
$client->on("handshake", function() use ($logger) {
    $logger->notice("Handshake received!");
});*/


$client->on("connect", function($headers) use ($logger, $client){
    $logger->notice("Connected to WebSocket");
    $json = array(
        "affiliation"=>"7389108'",
        "applicationLabel"=>"VISA ELECTRON",
        "arqc"=>"4D4D31B2C554F440",
        "aid"=>"A0000000032010",
        "amount"=>"152.0",
        "authCode"=>"222222",
        "bankName"=>"BANCOPPEL",
        "bin"=>"416916",
        "cardExpDate"=>"08/23",
        "cardType"=>"D",
        "cardTypeName"=>"VISA",
        "cityNam"=> "OTHON P. BLANCO QUINTANA ROO",
        "responseCode"=>"00",
        "folioNumber"=> "24LNA_TNP_86",
        "hasPin"=> true,
        "hexSign"=> "",
        "isQps"=> 0,
        "isRePrint"=> false,
        "message"=> "Transacción exitosa",
        "moduleCharge"=> '626',
        "moduleLote"=> '10',
        "customerName"=> "",
        "terminalId"=> "1494113052",
        "orderId"=> "240418132851-1494113052",
        "preAuth"=>"0",
        "preStatus"=>"0",
        "promotion"=>"00",
        "rePrintDate"=>"1.3.10.2.p.p_20231005",
        "rePrintMark"=>"VISA",
        "reprintModule"=>"C",
        "cardNumber"=>"8217",
        "storeName"=>"EUROPIEL CHETUMAL",
        "streetName"=>"AV. INSURGENTES  KM 5.025",
        "ticketDate"=>"ABR. 18, 24 12:31:11 ",
        "tipAmount"=>"0.0",
        "tipLessAmount"=>"152.0",
        "traceability"=>array(
          "id_sesion_cajero"=> "3027",
          "smart_accounts"=> true,
          "id_cajero"=>"784",
          "folio_unico_transaccion"=>"24LNA_TNP_86",
          "folio_venta"=>"24CS60",
          "store_id_netpay"=>"455311",
          "id_sucursal"=>"4",
        ),
        "transDate"=>"2024-04-18 13:28:51.CDT",
        "transType"=>"A",
        "transactionCertificate"=>"8E2A893D6F14204E",
        "transactionId"=>"101B44F5-2506-7651-4707-697426F417B6",
    );
    
    //die( $json );
    $request = array( "type"=>"actual_transaction",
    "transaction"=>$json );
    $request = json_encode( $request );
    $client->send($request);
});

$client->on("message", function($message) use ($client, $logger){
    $logger->notice("Got message: ".$message->getData());
    $client->close();
});

$client->open();
$loop->run();
  /*
  
  
  $context = stream_context_create();
    stream_context_set_option($context, 'ssl', 'verify_peer', false);
    stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
    'Sec-WebSocket-Accept' => true
]]);//'timeout' => 60, ['context' => $context] 
  //echo $request;
  $client->send("cadena txt ejemplo");//$request

  echo $client->receive();
  
  $client->close();*/
?>