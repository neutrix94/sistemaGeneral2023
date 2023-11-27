<?php
//ok 2023/11/25
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
  	$link->set_charset("utf8mb4");
  	if ( ! include( 'utils/rowsSynchronization.php' ) ){
    	die( 'No se incluyó libereria de registros de sincronizacion' );
  	}
  	$rowsSynchronization = new rowsSynchronization( $link );
	if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
		die( "No se incluyó : SynchronizationManagmentLog.php" );
	}
  	$SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
//obtiene configuracion
	$config = $SynchronizationManagmentLog->getSystemConfiguration( 'sys_sincronizacion_registros_facturacion' );
	//return json_encode( $config );
	$path = trim ( $config['value'] );
	$system_store = $config['system_store'];
	$store_prefix = $config['store_prefix'];
	$initial_time = $config['process_initial_date_time'];
	$costumers_limit = $config['rows_limit'];
	
	if( ! include( 'utils/facturacion.php' ) ){
		die( "No se incluyó : facturacion.php" );
	}
	//die( 'here' );
	$Bill = new Bill( $link, $system_store, $store_prefix );
//generacion de registros de sincronizacion
	$make_sinchronization_rows = $Bill->getTemporalCostumer();
	//var_dump( $make_sinchronization_rows );
	//die( 'here' );
//recupera los registros de sincronizacion
	$req["rows"] = $rowsSynchronization->getSynchronizationRows( $system_store, -1, $costumers_limit, 'sys_sincronizacion_registros_facturacion' );
	$req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION' );//inserta request
	//var_dump( $rows );
	$post_data = json_encode($req, JSON_UNESCAPED_UNICODE);//forma peticion//
//return $post_data;
	$result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/facturacion/inserta_cliente", $post_data );
    $result = json_decode( $result_1 );//decodifica respuesta
   	if( $result == null || $result == "" ){
   		echo "error : ";
   		var_dump($result_1);
   		die('<br><div class="text-center"><button class="btn btn-danger" onclick="close_emergent();">Cerrar</button></div>');
   	}
   //	var_dump( $result );
   	$rows_download = json_decode(  json_encode( $result->download, JSON_UNESCAPED_UNICODE ), true );
   	//var_dump( $rows_download );
   	$updated = $Bill->insertLocalCostumers( $rows_download );
   	if( $updated != "" ){
		$sql = "UPDATE sys_sincronizacion_registros_facturacion SET status_sincronizacion = 3 WHERE id_sincronizacion_registro IN( {$updated } )";
		//die( $sql );
	//$update_sinc_rows = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/facturacion/inserta_cliente", $post_data );
		$post_data = json_encode( array( "QUERY"=>$sql ), JSON_UNESCAPED_UNICODE );
		$result_1_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/", $post_data );
		if( $result_1_1 != '' && $result_1_1 != NULL ){
			die( "Error al actualizar peticion : {$result_1_1}" );
		}
	}
	if( $result->ok_rows != "" ){
		$sql = "UPDATE sys_sincronizacion_registros_facturacion SET status_sincronizacion = 3 WHERE id_sincronizacion_registro IN( {$result->ok_rows} )";
		$stm = $link->query( $sql ) or die( "Erorr al actualizar registros de sincronizacion en local : {$link->error}" );
	}
   	//var_dump( $example[0]['razon_social'] );
   	//die( "debug end" );	
   	$rows_inserted =  "";//array();
   	if( $result->download != '' && $result->download != null ){
		//die( 'herre' );
		//var_dump($result->download[0]->table_name);
		foreach ($result->download as $key => $costumer) {
			//var_dump( $costumer );die( $costumer->table_name );//hasta aqui me quede Oscar 2023/11/18
		//inserta los clientes localmente 
			//echo $costumer;
			if( $costumer->table_name == 'vf_clientes_razones_sociales' ){
				$insert_costumer = $Bill->insertCostumersLocal( $costumer );
				if( $insert_costumer == 'ok' ){
					$rows_inserted .= ( $rows_inserted == "" ? "" : "," );
					$rows_inserted .= $costumer->synchronization_row_id;
				}
			}else if( $costumer->table_name == 'vf_clientes_contacto' ){
				$insert_costumer_contact = $Bill->insertCostumerContactLocal( $costumer );
				if( $insert_costumer_contact == 'ok' ){
					$rows_inserted .= ( $rows_inserted == "" ? "" : "," );
					$rows_inserted .= $costumer->synchronization_row_id;
				}
			}
		}
		
	    //$rows_download = json_decode(json_encode($result->download), true);//json_encode($result->rows_download);
	    //return $rows_download;
	    //$log_download = json_decode(json_encode($result->log_download), true );
   }
   // var_dump( $result['download'] )
    //$rows_download = json_decode(json_encode($result->download), true);//json_encode($result->rows_download);
	//return $rows_download;
//return $result_1;

	//return json_encode( $rows );
	
	//inserta en tabla de sincronizacion
	return 'ok';//json_encode( $request_data );
});

?>
