<?php
    //require './utils/encriptacion_token.php';
    if( ! require_once("../../vendor/autoload.php") ){
        die( "Sin libreria websockets" );
    }
    $loop = \React\EventLoop\Factory::create();

    $logger = new \Zend\Log\Logger();
    $writer = new Zend\Log\Writer\Stream("archivo");//php://output
    $logger->addWriter($writer);
    $Encrypt = new Encrypt();
    $token = '7dff3c34-faee-11ea-a7be-3d014d7f956c';
    $token = $Encrypt->encryptText( $token, '' );
//die( "ok" . $body );
//return json_encode($body);
    //$client = new \Devristo\Phpws\Client\WebSocket("wss://m9dksnfd-3003.usw3.devtunnels.ms/{$token}", $loop, $logger);
    //$client = new \Devristo\Phpws\Client\WebSocket("wss://websocketserver-sqk76fij5a-uc.a.run.app/{$token}", $loop, $logger);
    $client = new \Devristo\Phpws\Client\WebSocket("{$urlWebsocket}{$token}", $loop, $logger);
    $client->on("connect", function($headers) use ($logger, $client, $body){
//die( $body );
    //$logger->notice("Connected to WebSocket");
        $json = json_decode( $body, true );
        //die( $json );
        $request = array( "type"=>"actual_transaction",
        "transaction"=>$json );
        $request = json_encode( $request );
        //die( $request );
        $client->send($request);//$request
    });

    $client->on("message", function($message) use ($client){//, $logger
    // $logger->notice("Got message: ".$message->getData());
        $client->close();
    });

    $client->open();
    $loop->addTimer(10, function () use ($loop) {//para limitar el tiempo de ejecucion del websocket ( segundos )
    $loop->stop();
    });
    $loop->run();
?>