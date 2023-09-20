<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

   // require __DIR__ . '/vendor/autoload.php';

    //$app = AppFactory::create();
//
    //$ruta_destino = "cache/ticket/tags/pieces/";
    $app->post('/descargar', function (Request $request, Response $response) {
        // Ruta del archivo que deseas enviar
        $ruta_origen = "http://sistemageneralcasa.com/produccion_linea_2023/cache/ticket/tags/pieces/";
        $nombre = "2023_09_18_10_51_18_650880060f853.txt";
        $archivo = "{$ruta_origen}{$nombre}";
      //  die( $archivo );
        // Comprueba si el archivo existe
        /*if (!file_exists($archivo)) {
            return $response->withStatus(404)->write('Archivo no encontrado');
        }*/

        // Configura la respuesta para enviar el archivo
        $response = $response->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="ejemplo.pdf"')
            ->withBody(new \Slim\Psr7\Stream(fopen($archivo, 'r')));
            echo 'here';
        return $response;
    });

   // $app->run();
?>