<?php
    header('Content-Type: text/html; charset=utf-8');

    use \Psr\Http\Message\ResponseInterface as Response;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    //use Slim\Factory\AppFactory;
    /*
    * Instancia accesos BD
    */
    require '../vendor/autoload.php';
    require '../src/config/db.php';         // DB Connect CL
    require 'utils/encriptacion_token.php';
    //require '../src/config/dbFact.php';     // DB Connect Fact
    /*
    * Instancia utilities
    */
    require '../v1/utils/manageResponse.php';
    require '../v1/utils/validaToken.php';

    $config = [
        'settings' => [
            'displayErrorDetails' => true, // Solo para desarrollo
            'determineRouteBeforeAppMiddleware' => true,
            'addContentLengthHeader' => false,
            'outputBuffering' => 'append',
            'charset' => 'UTF-8' // Asegúrate de configurar la codificación aquí
        ],
    ];
    $app = new \Slim\App( $config );
    //configuracion de CORS
    $app->add(function (Request $request, Response $response, $next) {
        $response = $next($request, $response);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*') // Permite solicitudes de cualquier origen
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS'); // Métodos HTTP permitidos
    });

    //webhook de netPay
    require 'server/netPayResponse.php';
    
    //endpoints de websockets
    require 'server/verificaTokenValido.php';//verifica validez de Token
    require 'server/insertarTransaccionNetpay.php';//insertar solicitudes de peticiones netPay
    require 'server/actualizarDatosTransacciones.php';//actualizar informacion de peticiones netPay
    require 'server/recuperarRespuestas.php';//recuperar respuestas que no han sido entregadas al usuario
    require 'server/actualizarStatusRespuesta.php';//obtener respuesta de servidor linea a local
    require 'server/recuperarRespuestaPorFolioUnico.php';//obtener respuesta por folio unico
    require 'server/tokenEncriptado.php';//obtener respuesta por folio unico

    //endpoints desde ventas
    require 'server/consultarTransaccionesPorFolio.php';

    $app->run();
