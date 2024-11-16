<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: verificacion_registros
* Path: /verificacion_registros
* Método: POST
* Descripción: Recupera y envia informacion de registros de petición que no obtubieron respuesta
*/
$app->get('/verificacion_registros', function (Request $request, Response $response){
    $data = array();
    $data['pending'] = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
      die( "No se incluyó : SynchronizationManagmentLog.php" );
    }
    $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
    $config = $SynchronizationManagmentLog->getSystemConfiguration( 'sys_sincronizacion_registros' );//consulta path del sistema central
    $path = trim ( $config['value'] );
    $system_store = $config['system_store'];
    $store_prefix = $config['store_prefix'];
    $initial_time = $config['process_initial_date_time'];
    $movements_limit = $config['rows_limit'];
    //var_dump( $config );return '';
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
    $post_data = json_encode($data, JSON_PRETTY_PRINT);
    return $post_data;
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/procesa_registros_pendientes", $post_data );//envia petición
    $result = json_decode( $result_1 );//decodifica respuesta
    return json_encode($result);
    var_dump( $data );
});

?>