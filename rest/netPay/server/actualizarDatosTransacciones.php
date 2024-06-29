<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: actualizar_datos_transacciones
* Path: /actualizar_datos_transacciones
* Método: POST
* Descripción: Actualizar datos de transacciones de NetPay
*/

$app->post('/actualizar_datos_transacciones', function (Request $request, Response $response){
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    //$Encrypt = new $Encrypt();

    $body = $request->getBody();
    //var_dump( $body );
    $file = fopen("archivo_local.txt", "w");
    fwrite($file,"{$body}");
    fclose($file);
    $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
    //$token = $Encrypt->decryptText($token, 'CDLL2024');//desencripta token
    if (empty($token) || strlen($token)<36 ) {
    //Define estructura de salida: Token requerido
        return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
    }else{
      //Consulta vigencia
        try{
            $resultadoToken = $vt->verificaExistenciaToken($token);
        if ($resultadoToken->rowCount()==0) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
        }
        }catch (PDOException $e) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
        }
    }
    if( !include( '../../conexionMysqli.php' ) ){
        die( "No se pudo incluir el archivo de conexion!" );
    }
    if( !include( '../../code/especiales/tesoreria/cobrosSmartAccounts/ajax/db.php' ) ){
        die( "No se pudo incluir libreria de Pagos!" );
    }
    if( ! include( '../../code/especiales/tesoreria/cobrosSmartAccounts/ajax/Logger.php' ) ){/*Logger*/
      die( "Error al incluir libreria de Logs!" );
    }
	$Logger = null;
    $log_id = null;
    $steep_log_id = 0;
        $sql = "SELECT log_habilitado AS log_enabled FROM sys_configuraciones_logs WHERE id_configuracion_log = '2'";
        $stm = $link->query( $sql ) or die( "Error al consultar si el log de cobros esta habilitado : {$sql} : {$link->error}" );
        $log = $stm->fetch_assoc();
    if( $log['log_enabled'] == 1 ){
        $Logger = new Logger( $link );//instancia clase de log
    }
    //var_dump( $Logger ); //die( 'here' );
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
            $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Respuesta ( JSON ) que llega al servicio /actualizar_datos_transacciones : ", $body );
        }
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
              /*45*/store_id_netpay = '{$traceability['store_id_netpay']}',
              /*46*/notificacion_vista = '1'
            WHERE folio_unico = '{$transaction_unique_folio}'";//$folioNumber
    $stm = $link->query( $sql );//die( $sql );
    
/*Logger*/
    if( $log_id != null ){
    $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el registro de transaccion en servidor local", $sql );
    }
    if( $link->error ){
    if( $log_id != null ){
        $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', $traceability['folio_unico_transaccion'], $sql, $link->error );
    }
    return json_encode( array( "status"=>400, "message"=>"Error al actualizar datos de la transaccion en servidor local : {$link->error}" ) );
    //die( "Error al actualizar el registro de transaccion en Webhook : {$link->error}" );
    }
//inserta pago
    require_once( './utils/inserta_pago_con_tarjeta.php' );
    //die('here6');
    $link->autocommit( true );
	return json_encode( array( "status"=>200, "message"=>"Registro actualizado exitosamente." ) );
});
?>
