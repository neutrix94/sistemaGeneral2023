<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_devoluciones
* Path: /inserta_devoluciones
* Método: GET
* Descripción: Insercion de devoluciones
*/

$app->post('/envia_cliente', function (Request $request, Response $response){
//variables
	$req = [];
	$req["rows"] = array(); 
	$result = "";
//librerias
	if ( ! include( '../../conexionMysqli.php' ) ){
    	die( 'No se incluyó libereria conexionMysqli.php' );
  	}
  	if ( ! include( 'utils/rowsSynchronization.php' ) ){
    	die( 'No se incluyó libereria de registros de sincronizacion' );
  	}
  	$rowsSynchronization = new rowsSynchronization( $link );
	if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
		die( "No se incluyó : SynchronizationManagmentLog.php" );
	}
  	$SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
//obtiene configuracion
	$config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
	//return json_encode( $config );
	$path = trim ( $config['value'] );
	$system_store = $config['system_store'];
	$store_prefix = $config['store_prefix'];
	$initial_time = $config['process_initial_date_time'];
	$costumers_limit = $config['rows_limit'];
	
	if( ! include( 'utils/facturacion.php' ) ){
		die( "No se incluyó : facturacion.php" );
	}//die( 'here' );
	$Bill = new Bill( $link, $system_store, $store_prefix );
//generacion de registros de sincronizacion
	$make_sinchronization_rows = $Bill->getTemporalCostumer();
//recupera los registros de sincronizacion
	$req["rows"] = $rowsSynchronization->getSynchronizationRows( $system_store, -1, $costumers_limit, 'sys_sincronizacion_registros_facturacion' );
	$req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION' );//inserta request
	//var_dump( $rows );
	$post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion//
	return $post_data;
	$result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/facturacion/inserta_cliente", $post_data );
	return $result_1;
	//return json_encode( $rows );
	
	//inserta en tabla de sincronizacion
	return 'ok';//json_encode( $request_data );
});

?>
