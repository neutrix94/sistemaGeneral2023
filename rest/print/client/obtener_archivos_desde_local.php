<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

   // require __DIR__ . '/vendor/autoload.php';

   // $app = AppFactory::create();

    $app->post('/obtener_archivos_desde_local', function (Request $request, Response $response) {
        //die( "here" );
        include( '../../conexionMysqli.php' );
    //consulta url de api linea
        $sql = "(SELECT `value` FROM api_config WHERE `name` = 'path')
                UNION
                (SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1)";
        //die( "{$sql}" );
        $stm = $link->query( $sql ) or die( "Error al consultar los parametros de api y sucursal : {$link->error}" );
        $row = $stm->fetch_assoc();
        $url = "{$row['value']}";
        $row = $stm->fetch_assoc();
        $store_id = $row['value'];
        $post_data = json_encode( array( "destinity_store_id"=>"1" ) );//{$store_id}
        //die( "here : {$url} , store_id : {$store_id}" );
    //consulta el api de linea
        $resp = "";
        $crl = curl_init( "{$url}/rest/print/get_print_files" );
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        //curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'token: ' . $token)
        );
        $resp = curl_exec($crl);//envia peticion
        curl_close($crl);
       // die( $resp );
        $files = json_decode( $resp, true );
        //var_dump($files['files']);
        if( sizeof($files['files']) > 0 ){
            include( './utils/Print.php' );
            $Print = new PrintApi( $link, '../../' );//instancia de la clase
            //$files = $request->getParam( "files" );
            $registros = array();
            $registros['ok_rows'] = '';
            $registros['error_rows'] = '';
            $resultado = '';
            foreach ($files['files'] as $key => $file) {
                //echo $key;
                //var_dump($file['file_id']);
                //echo $key . ' - ';
                //var_dump($file);
                $resultado = $Print->files_download( $file['file_origin'], $file['file_destinity'], $file['file_name'] );
               // echo "resultado : {$resultado}";
                if( $resultado == 'ok' ){
                    echo 'here';
                    $registros['ok_rows'] .= ( $registros['ok_rows'] == '' ? '' : ',' );
                    $registros['ok_rows'] .= $file['file_id'];
                }else{
                    $registros['error_rows'] .= ( $registros['error_rows'] == '' ? '' : ',' );
                    $registros['error_rows'] .= $file['file_id'];
                }
            }
        //envia peticion para actualizar registros exitosos
            if( $registros['ok_rows'] != '' ){
                $post_data = json_encode( $registros );//{$store_id}
            //die( "here : {$url} , store_id : {$store_id}" );
            //consulta el api de linea
                $resp = "";
                $crl = curl_init( "{$url}/rest/print/actualizar_status_archivos" );
                curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($crl, CURLINFO_HEADER_OUT, true);
                curl_setopt($crl, CURLOPT_POST, true);
                curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
                //curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
                curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                  'Content-Type: application/json',
                  'token: ' . $token)
                );
                $resp = curl_exec($crl);//envia peticion
                curl_close($crl);
                echo 'here';
                return $resp;
            }else{
                die( "No se pudieron insertar registros : {$resultado}" );
            }
        }else{
            return "{$resp}";die( 'here finish' );
        }
        return $files;
        $uploadedFiles = $request->getUploadedFiles();
        
        /* Verificar si se envió un archivo
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
        }*/
    });

   // $app->run();
?>