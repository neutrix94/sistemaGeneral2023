<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteMuestra
* Path: /surte/Muestra
* Método: POST
* Descripción: Servicio para solicitar muestra
*/
$app->post('/surte/Muestra', function (Request $request, Response $response){
  //Init
  $db = new db();           //Instancia BD General
  $db = $db->conectDB();
  // $dbFact = new dbFact();   //Instancia a BD Fact
  // $dbFact = $dbFact->conectDB();
  $rs = new manageResponse();
  $vt = new tokenValidation();

  //Valida token
  $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
  if (empty($token) || strlen($token)<36 ) {
    //Define estructura de salida: Token requerido
    return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
  }else{
    //Consulta vigencia
    try{
      $resultadoToken = $vt->validaToken($token);
      if ($resultadoToken->rowCount()==0) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
      }
    }catch (PDOException $e) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
    }
  }

  //Recuperar parámetros de entrada
  $productos = $request->getParam('productos');
  $vendedor = $request->getParam('vendedor');
  $pedido = $request->getParam('pedido');
  $sucursal = $request->getParam('sucursal');
  //No maneja productos canelados, como lo hace el servicio de pedido
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($vendedor)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de vendedor para solicitar muestras', 400);
  }
  if (empty($pedido)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de pedido para solicitar muestras', 400);
  }
  if (empty($productos)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de productos para solicitar muestras', 400);
  }
  if (empty($sucursal)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de sucursal para solicitar muestras', 400);
  }
  //Validar elementos requerido para nodo productos
  if (count($productos)>0) {
    //Itera y valida productos
    $productRow=0;
    $idProductos = "'0'";
    foreach($productos as $producto) {
      if (empty($producto['id'])) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear producto(s)', 400);
      }
      $idProductos = $idProductos . ",'".$producto['id']."'";
      $productRow ++;
    }
  }
  
  //Ejecuta lógica para surtimiento
  try {
    //Consulta productos disponibles para surtimiento
    $resultado = 0;
    $productosSurtir=[];
    if($idProductos){
      $sqlConsultaProds="SELECT sp.id_producto, sp.surtir, p.orden_lista, sp.id_sucursal FROM sys_sucursales_producto sp
          inner join ec_productos p on p.id_productos = sp.id_producto
          where p.orden_lista in (".$idProductos.")
          and sp.id_sucursal='{$sucursal}'
          and surtir=1";
      //error_log('query:'.$sqlConsultaProds);
      foreach ($db->query($sqlConsultaProds) as $row) {
        $productosSurtir[]=$row['orden_lista'];
      }
    }
    //error_log('count:'.count($productosSurtir));
    //Inserta Surtido
    if(count($productosSurtir)>0){
      //Consulta solicitud existente; Pendiente o Proceso
      $solicitudActual=[];
      $productosSolicitados = [];
      $productosNoSolicitados = [];
      $productosNoHabilitados = [];
      $solicitudActual['id_surtimiento'] = '';
      $solicitudActual['lineas'] = [];
      $sqlConsultaSol="SELECT 
				        s.no_pedido,
                s.id id_surtimiento,
                sd.id id_detalle,
                sd.id_producto,
                sd.id_surtimiento,
                p.orden_lista,
                sd.cantidad_solicitada,
                sd.estado
            FROM ec_surtimiento_detalle sd
            LEFT JOIN ec_productos p ON p.id_productos = sd.id_producto
            INNER JOIN ec_surtimiento s ON s.id = sd.id_surtimiento
            WHERE  
                s.id_vendedor = '{$vendedor}'
                AND s.no_pedido ='{$pedido}'
                AND s.tipo ='1'
                AND sd.estado IN (1,2)
                AND s.estado IN (1,2);";
                
      foreach ($db->query($sqlConsultaSol) as $row) {
          $solicitudActual['id_surtimiento'] = $row['id_surtimiento'];
          $solicitudActual['lineas'][$row['orden_lista']] = [];
          $solicitudActual['lineas'][$row['orden_lista']]['id_detalle'] = $row['id_detalle'];
          $solicitudActual['lineas'][$row['orden_lista']]['cantidad_solicitada'] = $row['cantidad_solicitada'];
      }
      
      //Valida crear o actualizar
      if(empty($solicitudActual['id_surtimiento'])){
          // Inserta nueva muestra
          $idSurtido = gen_uuid();
          $sqlInsert = "INSERT INTO `ec_surtimiento` 
              (`id`, `no_pedido`, `tipo`, `estado`, `id_vendedor`, `prioridad`, `fecha_creacion`, `creado_por`, `fecha_modificacion`, `modificado_por`) 
              VALUES ('{$idSurtido}', '{$pedido}', '1', '1', '{$vendedor}', '3', now(), '{$vendedor}', now(), '{$vendedor}');";
          $db->exec($sqlInsert);
      }else{
          //toma muestra existente
          $idSurtido = $solicitudActual['id_surtimiento'];
      }
      
      //Itera lista de productos para insertar detalle
      $idSurtidor = '';
      foreach($productos as $producto) {
        if(in_array($producto['id'], $productosSurtir)){
          //error_log('proceso prod.'.$producto['id']);
          if(isset($solicitudActual['lineas'][$producto['id']])){
              //No se solicita producto por que no ya hay una muestra en proceso
              $productosNoSolicitados[] = $producto['id'];
          }else{
              $idDetalle = gen_uuid();
              $sqlInsert = "INSERT INTO `ec_surtimiento_detalle` 
                (`id`, `id_surtimiento`, `id_producto`, `cantidad_solicitada`, `estado`, `id_asignado`, `fecha_creacion`, `creado_por`, `fecha_modificacion`, `modificado_por`) 
                SELECT  '{$idDetalle}', '{$idSurtido}', p.id_productos, '{$producto['cantidad']}', '1', '{$idSurtidor}', now(), '{$vendedor}', now(), '{$vendedor}'  from ec_productos p where p.orden_lista='{$producto['id']}';";
              $db->exec($sqlInsert);
              //Se agrega a solicitud de muestra
              $productosSolicitados[] = $producto['id'];
              $resultado = 1;
          }
        }else {
          //No se solicita producto por que no está habilitado para surtimiento
          $productosNoHabilitados[] = $producto['id'];
          $productosNoSolicitados[] = $producto['id'];
        }
      }
      
      //Valida surtimiento en proceso
      if(count($productosSolicitados) == 0 && count($productosNoSolicitados) > 0 && ( count($productosNoSolicitados) > count($productosNoHabilitados) ) ){
         $resultado = 2;
      }
    }
    
    //No hay productos para surtir
    if($resultado == 0){
      //Regrsa resultado no hay productos por surtir
      $insertsProd['resultado']='Sin productos por surtir';
      $insertsProd['descripcion']='No hay productos habilitados para surtir';
    }
    
    //Solicitud generada
    if($resultado == 1){
      $insertsProd['resultado']='Solicitado';
      $insertsProd['descripcion']='Se ha solicitado la muestra de '. count($productosSolicitados) . ' producto(s)';
      $insertsProd['solicitado']= $productosSolicitados;
      $insertsProd['noSolicitado']= $productosNoSolicitados;
      $insertsProd['noHabilitado']= $productosNoHabilitados;
    }
    
    //Muestra en proceso
    if($resultado == 2){
      //Regrsa resultado no hay productos por surtir
      $insertsProd['resultado']='Muestra en proceso';
      $insertsProd['descripcion']='Ya existe una solicitud de muestra para los productos seleccionados. Espere a que se surta para poder solicitar otra muestra';
    
    }
  }catch (PDOException $e) {
    $insertsProd['resultado']='Error';
    $insertsProd['descripcion']= $e->getMessage();
  }


  //Limpia variables
  $db = null;
  //Regresa resultado
  return $rs->successMessage($request->getParsedBody(),$response, $insertsProd);

});

?>
