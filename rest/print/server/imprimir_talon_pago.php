<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Http\Request;
//use Slim\Http\Response;
//ini_set('max_execution_time', 1);
/*
* Endpoint: 
* Path: /imprimir_talon_pago
* Método: POST
* Descripción: API para impresion de talon de pago
*/
$app->post('/imprimir_talon_pago', function (Request $request, Response $response){
	if( ! include( '../../conexionMysqli.php' ) ){
        return json_encode( array( "sattus"=>400, "message"=>"No se pudo incluir la libreria de conexión." ) );
    }
    $id_venta = $request->getParam( "id_venta" );
    if( $id_venta == '' || $id_venta == null ){
        return json_encode( array( "sattus"=>400, "message"=>"Es necesario enviar id_venta com parametro." ) );
    }
//verifica si la venta realmente existe
    $sql = "SELECT folio_nv FROM ec_pedidos WHERE id_pedido = {$id_venta}";
    $stm = $link->query( $sql );
    if( $link->error ){
        return json_encode( array( "status"=>400, "message"=>"Error al consultar existencia de la venta : {$link->error}" ) );
    }
    if( $stm->num_rows <= 0 ){
        return json_encode( array( "status"=>400, "message"=>"La venta con el id '{$id_venta}' no fue encontrada, verifica y vuelva a intentar." ) );
    }
    if( ! include( "../../conect.php" ) ){
        return json_encode( array( "status"=>400, "message"=>"No se pudo importar la libreria conect." ) );
    }
    include( "../../touch_desarrollo/index.php?src=talon-pago&idp={$id_venta}" );
/*envia peticion para generacion de ticket
    $url = "http://localhost/desarrollo_cobros_e_impresion/touch_desarrollo/index.php?src=talon-pago&idp={$id_venta}";
    $response = file_get_contents($url);*/

});

?>
