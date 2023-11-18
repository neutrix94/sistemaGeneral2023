<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: productos
* Path: /productos/nuevoFact
* Método: POST
* Descripción: Servicio para registrar nuev producto en BDs facturación
*/
$app->post('/clientes/nuevoCliente', function (Request $request, Response $response){
//die( 'here' );
  include('../../conexionMysqli.php');
  $dbHost = "sistemageneralcasa.com";
  $dbUser = "wwsist_oscar23";
  $dbPassword = "wwsist_oscar23_23";
  $dbName = "wwsist_casa_luces_bazar"; 

  $linkFact = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
  if( $linkFact->connect_error ){
    die( "Error al conectar con la Base de Datos : " . $linkFact->connect_error);
  }
  $linkFact->set_charset("utf8mb4");
//die ( 'here5' );

  $costumers = $request->getParam('costumers');
//Validar elementos requerido para crear venta
  /*if (empty($costumer)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear cliente(s)', 400);
  }*/

//bases de datos destino de facturacion
  $bd_facturacion=[];
  //Recupera bases de datos
  $sql="SELECT id, nombre_bd FROM ec_bases_facturacion WHERE active=1";
  $stm = $link->query( $sql ) or die( "Error al consultar las bases de datos de facturacion : {$link->error}" );

  //die( 'here8' );
  while( $row = $stm->fetch_assoc() ) {
    $bd_facturacion[]=$row['nombre_bd'];
  }
  //itera bases de datos
    $linkFact->autocommit( false );
  foreach ($costumers as $key => $costumer) {
    // code...192.168.1.127/pruebas_etiquetas/rest/facturacion/envia_cliente
      foreach ($bd_facturacion as $key => $bd_destino) {
      //inserta cliente
        $sql = "INSERT INTO {$bd_destino}.ec_clientes ( id_cliente, nombre, telefono, telefono_2, movil, contacto, email, es_cliente, id_sucursal, 
          idTipoPersona, EntregaConsSitFiscal, regimenFiscal )
        VALUES ( NULL, '{$costumer['rfc']}', '{$costumer['detail']['telefono']}', '', '{$costumer['detail']['celular']}', '{$costumer['detail']['nombre']}', 
          '{$costumer['detail']['correo']}', 1, 1, '{$costumer['id_tipo_persona']}', '{$costumer['entrega_cedula_fiscal']}', '{$costumer['regimen_fiscal']}' )";
        $stm = $linkFact->query( $sql ) or die( "Error al insertar cliente en {$bd_destino} : {$linkFact->error}" );
        $costumer_id = $linkFact->insert_id;
      //inserta razon social
        $sql = "INSERT INTO {$bd_destino}.ec_clientes_razones_sociales ( id_cliente_rs, id_cliente, rfc, razon_social, calle, no_int, no_ext, colonia, del_municipio, cp, estado, pais ) 
        VALUES( NULL, '{$costumer_id}', '{$costumer['rfc']}', '{$costumer['razon_social']}', '{$costumer['calle']}', '{$costumer['no_int']}', '{$costumer['no_ext']}', 
          '{$costumer['colonia']}', '{$costumer['del_municipio']}', '{$costumer['cp']}', '{$costumer['estado']}', '{$costumer['pais']}' )";
        $stm = $linkFact->query( $sql ) or die( "Error al insertar razon social de cliente : {$linkFact->error}" );
      //inserta contactos
        $sql = "INSERT INTO {$bd_destino}.ec_clientes_contacto ( id_cliente_contacto, id_cliente_facturacion, nombre, telefono, 
          celular, correo, uso_cfdi, fecha_alta, fecha_ultima_actualizacion, folio_unico, sincronizar )
        VALUES( NULL, {$costumer_id}, '{$costumer['detail']['nombre']}', '{$costumer['detail']['telefono']}', '{$costumer['detail']['celular']}', 
          '{$costumer['detail']['correo']}', '{$costumer['detail']['uso_cfdi']}', NOW(), NOW(), '{$costumer['detail']['folio_unico']}', 1 )"; 
        $stm = $linkFact->query( $sql ) or die( "Error al insertar contactos de cliente : {$linkFact->error}" );
      }
  }
  $linkFact->autocommit( true );
  die( 'ok' );

});

?>
