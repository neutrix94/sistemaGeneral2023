<?php
/*actualizado desde rama api_busqueda_archivos 2024-01-18*/
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

    $app->post('/obtener_archivos_desde_local', function (Request $request, Response $response) {
        include( '../../conexionMysqli.php' );
    //consulta url de api linea
        $sql = "(SELECT `value` FROM api_config WHERE `name` = 'path')
                UNION
                (SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1)";
        $stm = $link->query( $sql ) or die( "Error al consultar los parametros de api y sucursal : {$link->error}" );
        $row = $stm->fetch_assoc();
        $url = "{$row['value']}";
        $row = $stm->fetch_assoc();
        $store_id = $row['value'];
        $post_data = json_encode( array( "destinity_store_id"=>"{$store_id}" ) );
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
        $files = json_decode( $resp, true );
        if( sizeof($files['files']) > 0 ){
            include( './utils/Print.php' );
            $Print = new PrintApi( $link, '../../' );//instancia de la clase
            $registros = array();
            $registros['ok_rows'] = '';
            $registros['error_rows'] = '';
            $resultado = '';
            foreach ($files['files'] as $key => $file) {
                $resultado = $Print->files_download( $file['file_origin'], $file['file_destinity'], $file['file_name'] );
                if( $resultado == 'ok' ){
                    $registros['ok_rows'] .= ( $registros['ok_rows'] == '' ? '' : ',' );
                    $registros['ok_rows'] .= $file['file_id'];
                }else{
                    $registros['error_rows'] .= ( $registros['error_rows'] == '' ? '' : ',' );
                    $registros['error_rows'] .= $file['file_id'];
                }
            }
        //envia peticion para actualizar registros exitosos
            if( $registros['ok_rows'] != '' ){
                $post_data = json_encode( $registros );
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
                return $resp;
            }else{
                die( "No se pudieron descargar archivos : {$resultado}" );
            }
        }else{
            return "{$resp}";
        }
    });
?>