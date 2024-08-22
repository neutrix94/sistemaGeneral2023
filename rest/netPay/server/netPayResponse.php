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
  set_time_limit( 20 );
  //die( 'here_1' );
  if( ! include( '../../conexionMysqli.php' ) ){
    die( "Error al incluir libreria de conexion!" );
  }
  if( ! include( '../../code/especiales/tesoreria/cobrosSmartAccounts/ajax/db.php' ) ){//oscar 2024-02-15
    die( "Error al incluir libreria de cobros!" );
  }
  if( ! include( '../../code/especiales/tesoreria/cobrosSmartAccounts/ajax/Logger.php' ) ){/*Logger*/
    die( "Error al incluir libreria de Logs!" );
  }
	$Logger = null;
  $log_id = null;
  $steep_log_id = 0;

  $body = $request->getBody();
  if( $body == '' || $body == null ){//cuando es el test de enpoints de terminal
      $resp = array(
        "code"=>"00",
        "message"=>"Peticion para configurar terminal"
      );
      return json_encode( $resp );
  }

	$sql = "SELECT log_habilitado AS log_enabled FROM sys_configuraciones_logs WHERE id_configuracion_log = '2'";
	$stm = $link->query( $sql ) or die( "Error al consultar si el log de cobros esta habilitado : {$sql} : {$link->error}" );
	$log = $stm->fetch_assoc();
  if( $log['log_enabled'] == 1 ){
    $Logger = new Logger( $link );//instancia clase de log
  }
//obtener el json en txt 
  /*$body = $request->getBody();
  $file = fopen("test.txt","w");
  fwrite($file,"{$body}");
  fclose($file);*/
  //try{
    $body = $request->getBody();
    //var_dump( $body );
    try{
      $filePath = getenv('PATH_NETPAY_TRANSACTION_FILE') ?: '';
      $file = fopen("{$filePath}archivo.txt", "w");
      fwrite($file,"{$body}");
      fclose($file);
    }catch( Exception $e ){
      die( "Error en escritura de arcchivo.txt : {$e}" );
    }
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
/*Logger*/
  if( $Logger != null ){
    $log = $Logger->insertLoggerRow( $traceability['folio_unico_transaccion'], $traceability['id_cajero'], 'vf_transacciones_netpay', -1, $traceability['id_sucursal'] );
    $log_id = $log['id_log'];
    if( $log_id != null ){
      $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Respuesta ( JSON ) que llega a NetPay : ", $body );
    }
  }
  //traceability
 // $traceability['']
//consulta el tipo de sistema en relacion al campo de acceso
  /*$sql_store = "SELECT id_sucursal AS store_id FROM sys_sucursales WHERE acceso = 1";
  $stm_store = $link->query( $sql_store ) or die( "Error al consultar el tipo de sistema  : {$link->error} : {$sql}" );
  $store_row = $stm_store->fetch_assoc();
  $system_type = $store_row['store_id'];*/
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
  $stm = $link->query( $sql );
/*Logger*/
  if( $log_id != null ){
    $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el registro de transaccion en Webhook", $sql );
  }
  if( $link->error ){
    if( $log_id != null ){
      $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', $traceability['folio_unico_transaccion'], $sql, $link->error );
    }
    die( "Error al actualizar el registro de transaccion en Webhook : {$link->error}" );
  }
  
  /*if( $traceability['tipo_sistema'] == -1 ){//peticion desde linea
    require_once( './utils/inserta_pago_con_tarjeta.php' );//inserta pago
    $link->autocommit( true );
  }else{//peticion desde local
    $link->autocommit( true );
    require_once( './utils/conexion_con_websocket.php' );//consume websocket
  }*/

  if( isset( $traceability['tipo_sistema'] ) ){
    if( $traceability['tipo_sistema'] == -1 ){//peticion desde linea
    /*Logger*/
      if( $log_id != null ){
        $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Entra en proceso de peticion desde linea", "" );
      }
      require_once( './utils/inserta_pago_con_tarjeta.php' );//inserta pago
      $link->autocommit( true );
      $urlWebsocket = 'wss://websocketserverlocal-sqk76fij5a-uc.a.run.app/';
    }else{//peticion desde local
    /*Logger*/
      if( $log_id != null ){
        $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Entra en proceso de peticion desde local", "" );
      }
      $link->autocommit( true );
      $urlWebsocket = 'wss://websocketserver-sqk76fij5a-uc.a.run.app/';
    }
    require_once( './utils/conexion_con_websocket.php' );//consume websocket
  }else{
    return json_encode( array( "code"=>"00", "message"=>"falta el parametro tracebility->tipo_sistema" ) );
  }

  ob_start();
  $resp = array(
    "code"=>"00",
    "message"=>$message_
  );
  ob_flush();
  return json_encode( $resp );
  //die('');
});
?>
