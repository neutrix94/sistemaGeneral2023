<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: procesa_registros_pendientes
* Path: /procesa_registros_pendientes
* Método: POST
* Descripción: Procesa registros de petición que no obtuvieron respuesta
*/
$app->post('/procesa_registros_pendientes', function (Request $request, Response $response){
    $data = array();
    $data['pending'] = array();
    //die('here');
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
//recibe variables de peticion
    $pendientes = $request->getParam( 'pending' );
    foreach( $pendientes as $key=> $value){
    //consulta si existe la peticion en el servidor en linea
        echo $value['folio_unico'];// . "<br>"
    }
//consulta si hay registros pendientes de respuestas en linea
//consula la sucursal de acceso
    $sql = "SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1";
    $stm = $link->query( $sql ) or die( "Error al consultar sucursal de acceso : {$link->error}" );
    $row = $stm->fetch_assoc();
    $id_sucursal = $row['id_sucursal'];
//consulta los registros de sincronizacion que no tienen respuesta
    $sql = "SELECT
                folio_unico
            FROM sys_sincronizacion_peticion
            WHERE contenido_respuesta IS NULL
            AND id_sucursal_origen = {$id_sucursal}";
    $stm = $link->query( $sql ) or die( "Error al consultar si hay peticiones pendientes : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
        //echo 'here';
        array_push( $data['pending'], $row );
        //$data['pending'] = $row;
    }
//envia peticion al servidor en linea para comprobar 
    return json_encode($data);
    //return 'ok';
    //return json_encode( $pendientes );
//envia respuesta al servidor en linea para comprobar 
    //return json_encode($data);
});

?>