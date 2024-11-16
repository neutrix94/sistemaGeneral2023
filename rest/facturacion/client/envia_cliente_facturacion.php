<?php
//ok 2023/11/25
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
/*Implementacion Oscar 2024-06-27*/
  $sql = "SELECT host_bd, usuario_bd, pass_bd, nombre_bd FROM ec_bases_facturacion LIMIT 1";
  $db_stm = $link->query( $sql ) or die( "Error al consultar los parametros de bases de datos de facturacion : {$sql} : {$link->error}" );
  $db_row = $db_stm->fetch_assoc();
  $dbHost = $db_row['host_bd'];//"sistemageneralcasa.com";
  $dbUser = $db_row['usuario_bd'];//"wwsist_oscar23";
  $dbPassword = $db_row['pass_bd'];//"wwsist_oscar23_23";
  $dbName = $db_row['nombre_bd'];//"wwsist_casa_luces_bazar"; */

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
  $sql="SELECT id, nombre_bd FROM ec_bases_facturacion WHERE active = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar las bases de datos de facturacion : {$link->error}" );

  //die( 'here8' );
  while( $row = $stm->fetch_assoc() ) {
    $bd_facturacion[]=$row['nombre_bd'];
  }
  //itera bases de datos
  foreach ($costumers as $key_1 => $costumer) {
  // $linkFact->autocommit( false );
    //var_dump( $costumer );
      foreach ($bd_facturacion as $key_2 => $bd_destino) {
        $client_exists = false;
      //verifica si el cliente existe en relacion al rfc
        $sql = "SELECT id_cliente FROM {$bd_destino}.ec_clientes WHERE id_cliente >= 10000 AND nombre = '{$costumer['rfc']}'";
        $stm_check_costumer = $linkFact->query( $sql ) or die( "Error al consultar si el cliente ya existe : {$linkFact->error}" );
        if( $stm_check_costumer->num_rows > 0 ){
          $client_exists = true;
          //$costumer['id_cliente_facturacion'] = $aux_row['id_cliente_facturacion'];

        }
        $sql = ( $client_exists == false ? "INSERT INTO" : "UPDATE" );
        $sql .= " {$bd_destino}.ec_clientes SET ";
        if( $client_exists == false ){
          $sql .= "id_cliente = '{$costumer['id_cliente_facturacion']}', ";
        }
      //cabecera de cliente
        $sql .= "nombre = '{$costumer['rfc']}', 
                telefono = '{$costumer['detail'][0]['telefono']}', 
                telefono_2 = '', 
                movil = '{$costumer['detail'][0]['celular']}', 
                contacto = '{$costumer['detail'][0]['nombre']}', 
                email = '{$costumer['detail'][0]['correo']}', 
                es_cliente = 1, 
                id_sucursal = 1, 
                idTipoPersona = {$costumer['id_tipo_persona']}, 
                EntregaConsSitFiscal = '{$costumer['entrega_cedula_fiscal']}', 
                regimenFiscal = '{$costumer['regimen_fiscal']}', 
                folio_unico = '{$costumer['folio_unico']}'";
        if( $client_exists == false ){
            $stm = $linkFact->query( $sql ) or die( "Error al insertar nuevo cliente de facturacion en {$bd_destino} : {$linkFact->error}" );
        }else{
          $sql .= " WHERE id_cliente = {$costumer['id_cliente_facturacion']}";
          $stm = $linkFact->query( $sql ) or die( "Error al actualiza cliente de facturacion en {$bd_destino} : {$linkFact->error}" );
        }
   //     echo "{$sql} ____________________ ";
      //razon social de cliente
        $sql = ( $client_exists == false ? "INSERT INTO" : "UPDATE" );
        $sql .= " {$bd_destino}.ec_clientes_razones_sociales SET ";
        if( $client_exists == false ){
          $sql .= "id_cliente_rs = '{$costumer['id_cliente_facturacion']}', ";
        }
        $sql .= "id_cliente = '{$costumer['id_cliente_facturacion']}', 
                rfc = '{$costumer['rfc']}', 
                razon_social = '{$costumer['razon_social']}', 
                calle = '{$costumer['calle']}', 
                no_int = '{$costumer['no_int']}', 
                no_ext = '{$costumer['no_ext']}', 
                colonia = '{$costumer['colonia']}', 
                del_municipio = '{$costumer['del_municipio']}', 
                cp = '{$costumer['cp']}', 
                estado = '{$costumer['estado']}', 
                pais = '{$costumer['pais']}'";
        if( $client_exists == false ){
            $stm = $linkFact->query( $sql ) or die( "Error al insertar nueva razon social cliente de facturacion en {$bd_destino} : {$sql} {$linkFact->error}" );
        }else{
          $sql .= " WHERE id_cliente_rs = {$costumer['id_cliente_facturacion']}";
          $stm = $linkFact->query( $sql ) or die( "Error al actualizar razon social de facturacion en {$bd_destino} : {$linkFact->error}" );
        }
       // echo "{$sql} ____________________ ";
      //procesa el detalle
          $contact_exists = false;
        foreach ( $costumer['detail'] as $key => $contact ) {
          $contact_exists = false;
        //consulta si el contacto existe
          $sql = "SELECT id_cliente_contacto FROM {$bd_destino}.ec_clientes_contacto WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
          $stm = $linkFact->query( $sql ) or die( "Error al consultar si existe el contacto en facturacion : {$linkFact->error}" );
          if( $stm->num_rows > 0 ){
            $contact_exists = true;
          }
          $sql = ( $contact_exists == false ? "INSERT INTO" : "UPDATE" );
          $costumer['detail'][$key]['id_cliente_facturacion'] = $costumer['id_cliente_facturacion'];
          $sql .= " {$bd_destino}.ec_clientes_contacto SET ";
          if( $contact_exists == false ){
            $sql .= "id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}, ";
          }
          $sql .= "id_cliente_facturacion = '{$costumer['detail'][$key]['id_cliente_facturacion']}',
                nombre = '{$costumer['detail'][$key]['nombre']}', 
                telefono = '{$costumer['detail'][$key]['telefono']}',
                celular = '{$costumer['detail'][$key]['celular']}', 
                correo = '{$costumer['detail'][$key]['correo']}', 
                uso_cfdi = ( SELECT id FROM ec_cfdi WHERE clave ='{$costumer['detail'][$key]['uso_cfdi']}' LIMIT 1 ), 
                fecha_ultima_actualizacion = NOW(), 
                sincronizar = '1',
                folio_unico = '{$costumer['detail'][$key]['folio_unico']}'";
          if( $contact_exists == false  ){
          //echo ( $sql );
              $stm = $linkFact->query( $sql ) or die( "Error al insertar el nuevo contacto : {$linkFact->error}" );
          }else{
          //echo ( $sql );
            $sql .= " WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
            $stm = $linkFact->query( $sql ) or die( "Error al actualizar el contacto : {$linkFact->error}" );
          }
   //     echo "{$sql} ____________________ ";
        }
      }
//  $linkFact->autocommit( true );
  }
  die( 'ok' );

});

?>
