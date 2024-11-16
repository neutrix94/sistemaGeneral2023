<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: facturas
* Path: /facturas/nueva
* Método: POST
* Descripción: Servicio para registrar nueva factura
*/
$app->post('/facturaReceptor', function (Request $request, Response $response){
  //die( 'here' );
    //Init
    try {
      $validacion = [];
      $validacion['status']='';
      $validacion['result']='';
      
      $db = new db();
      $db = $db->conectDB();
      $rs = new manageResponse();
      $vt = new tokenValidation();
      
      //Valida token
      $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
      if (empty($token) || strlen($token)<36 ) {
        //Define estructura de salida: Token requerido
        return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
      }else{
        //Consulta vigencia
        try{
          $resultadoToken = $vt->validaToken($token);
          if ($resultadoToken->rowCount()==0) {
              return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
          }
        }catch (PDOException $e) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
        }
      }
      
      //Recuperar parámetros de entrada
      $rfc = htmlspecialchars($request->getParam('rfc'));
      $nombre = $request->getParam('nombre');//htmlspecialchars($request->getParam('nombre'));
    //  $nombre = str_replace( '"', '\\"', $nombre );

      $nombre = str_replace( '"', '\"', $nombre );
     // die("name : " . $nombre);
    // return json_encode( array( "result"=>"\"" . $nombre ) );
      $usoCFDI = $request->getParam('usoCFDI');
      $domicilioFiscal = $request->getParam('domicilioFiscal');
      $regimenFiscal = $request->getParam('regimenFiscal');
          
      //Validar información de cliente para continuar
      if (empty($rfc) || empty($nombre) || empty($usoCFDI) || empty($domicilioFiscal) || empty($regimenFiscal)) {
        $valorFaltante .= empty($rfc) ? ', RFC' : '';
        $valorFaltante .= empty($nombre) ? ', Nombre' : '';
        $valorFaltante .= empty($usoCFDI) ? ', Uso de CFDI' : '';
        $valorFaltante .= empty($domicilioFiscal) ? ', Domicilio Fiscal' : '';
        $valorFaltante .= empty($regimenFiscal) ? ', Regimen Fiscal' : '';
        $validacion['status']='400';
        $validacion['result']= 'Hace falta información del receptor'. $valorFaltante;
        return json_encode($validacion);
      }
      // URL del WSDL del servicio web Prueba
      $wsdlUrl = "https://cfdi33-pruebas.buzoncfdi.mx:1443/Timbrado.asmx?wsdl";

      // Configuración del cliente SOAP
      $options = array(
          'trace' => 1,          // Habilitar el seguimiento de mensajes SOAP para depuración
          'exceptions' => true   // Habilitar excepciones en caso de errores
      );

      //Forma XML de validación
      $fechaActual = date('Y-m-d\TH:i:s', strtotime('-2 hours'));
      $xmlTest = '<?xml version="1.0" encoding="UTF-8" ?>
      <cfdi:Comprobante
      	xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
      	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Fecha="'.$fechaActual.'"  Folio="B5867" Serie="Z" TipoDeComprobante="I" Exportacion="01" FormaPago="03" MetodoPago="PUE" LugarExpedicion="54870" SubTotal="100"  Total="116" Moneda="MXN">
      	<cfdi:Emisor Rfc="JES900109Q90" Nombre="JIMENEZ ESTRADA SALAS" RegimenFiscal="626"/>
      	<cfdi:Receptor Rfc="'.$rfc.'" Nombre="'.$nombre.'" UsoCFDI="'.$usoCFDI.'" DomicilioFiscalReceptor="'.$domicilioFiscal.'" RegimenFiscalReceptor="'.$regimenFiscal.'"></cfdi:Receptor>
      	<cfdi:Conceptos>
      		<cfdi:Concepto Cantidad="1" ClaveUnidad="H87" ClaveProdServ="39112011" Descripcion="Producto 1" ValorUnitario="100" Importe="100" ObjetoImp="02">
      			<cfdi:Impuestos>
      				<cfdi:Traslados>
      					<cfdi:Traslado Base="100" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="16" />
      				</cfdi:Traslados>
      			</cfdi:Impuestos>
      		</cfdi:Concepto>
      	</cfdi:Conceptos>
      	<cfdi:Impuestos TotalImpuestosTrasladados="16">
      		<cfdi:Traslados>
      			<cfdi:Traslado Base="100" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="16" />
      		</cfdi:Traslados>
      	</cfdi:Impuestos>
      </cfdi:Comprobante>';
      $base64Comprobante = base64_encode($xmlTest);
      
      // Crear el cliente SOAP
      $client = new SoapClient($wsdlUrl, $options);

      // Parámetros para el método
      $parametros = array(
          'usuarioIntegrador' => 'mvpNUXmQfK8=',
          'xmlComprobanteBase64' => $base64Comprobante,
          'idComprobante' => ''
      );
      
      $xmlTimbrado='';
      try {
          // Llamar al método del servicio web
          $resultado = $client->__soapCall('TimbraCFDI', array('parameters' => $parametros));
          $xmlTimbrado = isset($resultado->TimbraCFDIResult->anyType[3]) ? $resultado->TimbraCFDIResult->anyType[3] : '';
          $detalleError = isset($resultado->TimbraCFDIResult->anyType[8]) ? $resultado->TimbraCFDIResult->anyType[8] : '';
          // Imprimir el resultado
          //error_log('response: '.print_r($resultado,true));
      } catch (SoapFault $e) {
          // Error en servidor
          //echo "Error: " . $e->getMessage();
          error_log('Error: '.$e->getMessage());
          $validacion['status']='500';
          $validacion['result']= $e->getMessage();
      }
      if(empty($xmlTimbrado)){
          //Detalle de error
          $validacion['status']='400';
          $validacion['result']= json_decode($detalleError);
      }else{
          //Receptor correcto 
          $validacion['status']='200';
          $validacion['result']= 'Receptor válido';
      }
    } catch (\Exception $e) {
      error_log('Error: '.$e->getMessage());
      $validacion['status']='500';
      $validacion['result']= $e->getMessage();
      
    }
    
    //Respuesta de validación
    return json_encode($validacion);
});

?>
