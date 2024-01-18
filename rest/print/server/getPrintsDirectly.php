<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

   // require __DIR__ . '/vendor/autoload.php';

   // $app = AppFactory::create();

    $app->post('/subir-archivo', function (Request $request, Response $response) {
        

        $uploadedFiles = $request->getUploadedFiles();
        
        // Verificar si se envió un archivo
        if (isset($uploadedFiles['archivo']) && $uploadedFiles['archivo']->getError() === UPLOAD_ERR_OK) {
            $archivo = $uploadedFiles['archivo'];
            
            // Directorio de destino para guardar el archivo
            $directorioDestino = __DIR__ . '/archivos_subidos/';
            
            // Mover el archivo al directorio de destino con un nuevo nombre
            $nombreArchivo = uniqid() . '_' . $archivo->getClientFilename();
            $archivo->moveTo($directorioDestino . $nombreArchivo);
            
            // Respondemos con un mensaje de éxito
            return $response->withStatus(200)->write('Archivo subido con éxito');
        } else {
            // No se envió un archivo válido
            return $response->withStatus(400)->write('No se ha enviado un archivo válido');
        }
    });

   // $app->run();
?>