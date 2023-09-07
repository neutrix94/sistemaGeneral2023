<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: facturas
* Path: /facturas/nueva
* Método: POST
* Descripción: Servicio para registrar nueva factura
*/
$app->post('/facturas/nueva', function (Request $request, Response $response){
    //Init
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    //return $rs->successMessage($request->getParsedBody(),$response, $request->getParams());

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
    $cliente = $request->getParam('cliente');
    $direccion = $request->getParam('direccion');
    $venta = $request->getParam('venta');
    $productos_input = $request->getParam('productos');
    $costo_envio = '';

    //Validar información de cliente para continuar
    if (empty($cliente) && empty($direccion) && empty($venta) && empty($productos_input)) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear una factura', 400);
    }
    $costo_envio = $venta['costo_envio'];
    $porcentaje_descuento = $venta['porcentaje_descuento'];

    //Validar que exista cliente.
    if (empty($cliente['rfc'])) {
      //Sale del flujo
      $inserts=[];
      $inserts['ec_clientes']=1;
      return $rs->successMessage($request->getParsedBody(),$response, $inserts);
    }
    //Validar elementos requerido para nodo clientes
    if (!empty($cliente['rfc'])) {
      if (empty($cliente['email']) || empty($cliente['razon_social']) ) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información del cliente para crear una factura', 400);
      }
    }

    //Validar elementos requerido para nodo ventas
    if (!empty($cliente['rfc'])) {
      if (empty($venta['total']) || empty($venta['fecha']) || empty($venta['hora']) || empty($venta['id_sucursal']) || empty($venta['forma_pago']) || empty($venta['metodo_pago']) || empty($venta['cfdi']) || $venta['costo_envio']=="" ) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de la venta para crear una factura', 400);
      }
    }


    //Instancia a BD Fact
    $dbFact = new dbFact();
    $dbFact = $dbFact->conectDB();
    //Recuperar valores para: cfdi
    $queryC = "select id from ec_cfdi where nombre='{$venta['cfdi']}';";
    $uso_cfdi = getOneQuery($dbFact, $queryC, 'id');
    $venta['cfdi'] = $uso_cfdi;

    //Validar elementos requerido para nodo productos
    if (count($productos_input)>0) {
      //Genera estructura de productos agrupable
      $productos = [];
      $producto_agrupado = [];
      //Recupera lista de precios mostrador
      $queryPM = "select a.value from api_config a where a.key='1' and name='lista_mostrador';";
      $precioMostrador = getOneQuery($db, $queryPM, 'value');
      //Itera y valida productos
      foreach($productos_input as $producto) {
        if (empty($producto['codigo_sat']) || empty($producto['id_producto']) || empty($producto['cantidad']) || empty($producto['precio']) ) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de productos para crear una factura', 400);

        }
        //Se evita uso de producto agrupable para facturación
        $producto['agrupable'] = false;
        if ($producto['agrupable']) {
            //Recupera productos agrupados
            $sqlProductosA="select
                	pd.id_producto_ordigen idProducto,
                  pd.cantidad cantidad,
                  p.precio_compra precio,
                  p.orden_lista codigoSat
                from ec_productos_detalle pd
                  inner join ec_productos p on p.id_productos = pd.id_producto_ordigen
                where pd.id_producto = '".$producto['id_producto'] ."'
            ;";
            foreach ($db->query($sqlProductosA) as $row) {
              $montoAgrupado = $row['precio'] * $row['cantidad'] * $producto['cantidad'];
              $precioUnitario = $row['precio'];
              $row['descuento']=10;
              $producto_agrupado = array(
                'codigo_sat' => $row['codigoSat'],
                'id_producto' => $row['idProducto'],
                'cantidad' => $row['cantidad'] * $producto['cantidad'],
                'precio' => ($row['descuento']<=0) ? $precioUnitario : $precioUnitario * ((100 - $row['descuento'])/100),
                'agrupable' => false
              );
              $productos[] = $producto_agrupado;
            }

        }else {
            //Aplica descuento general adicional
            $producto['precio'] = (!empty($porcentaje_descuento) && $porcentaje_descuento>0 ) ? $producto['precio']*((100-$porcentaje_descuento)/100) : $producto['precio'];
            $productos[] = $producto;
        }
      }
    }
    //Agrega costo de envío
    if ($costo_envio>0) {
        $queryCostoEnvio = "select a.value from api_config a where a.key='productos' and name='costo_envio';";
        $idProdCostoEnvio = getOneQuery($db, $queryCostoEnvio, 'value');
        $queryCS = "select orden_lista from ec_productos where id_productos='{$idProdCostoEnvio}';";
        $idCS = getOneQuery($dbFact, $queryCS, 'orden_lista');
        $productos[] = array(
          'codigo_sat' => $idCS,
          'id_producto' => $idProdCostoEnvio,
          'cantidad' => 1,
          'precio' => $costo_envio,
          'costo_envio' => true,
          'agrupable' => false
        );
    }
    //return print_r($productos,true);
    //Ejecuta instrucciones a BD facturacion
    try {

      $inserts=[];

      //0.- Genera consulta de Cliente
      $consultaCliente= "select id_cliente
        from ec_clientes
        where nombre='{$cliente['rfc']}'
        order by id_cliente asc
        limit 1
        ;";
      //return $consultaProducto;
      $resultadoCliente = $dbFact->query($consultaCliente);
      $resultadoClienteRow = $resultadoCliente->fetch();
      $idCliente = $resultadoClienteRow['id_cliente'];


      //Recupera variables de api_config
      $prefijo="";
      $consecutivo_venta = 0;
      $id_usuario = 0;
      $id_tipo_movimiento=0;
      $prefijo_mi="";
      $valida_inventario = 0;
      $forma_pago = '';
      $metodo_pago = '';
      $sqlAPIConfig="SELECT c.name, c.value FROM api_config c WHERE c.key='facturacion' and c.value is not null";
      foreach ($dbFact->query($sqlAPIConfig) as $row) {
        $id_usuario=($row['name'] == 'id_usuario') ? $row['value'] : $id_usuario;
        $id_tipo_movimiento=($row['name'] == 'id_tipo_movimiento') ? $row['value'] : $id_tipo_movimiento;
        $prefijo_mi=($row['name'] == 'prefijo_mi') ? $row['value'] : $prefijo_mi;
        $valida_inventario=($row['name'] == 'valida_inventario') ? intval($row['value']) : $valida_inventario;
        $forma_pago=($row['name'] == 'forma_pago') ? $row['value'] : $forma_pago;
        $metodo_pago=($row['name'] == 'metodo_pago') ? $row['value'] : $metodo_pago;
      }
      //Remplaza valores con ids de configuración
      $metodo_pago_texto = $venta['metodo_pago'];
      $forma_pago_texto = $venta['forma_pago'];
      $venta['forma_pago'] = $forma_pago;
      $venta['metodo_pago'] = $metodo_pago;
      //Consulta Prefijo y consecutivo
      $queryP = "select prefijo, consecutivo from api_tipo_facturacion where id='2';";
      $prefijo = getOneQuery($dbFact, $queryP, 'prefijo');
      $consecutivo_venta = intval(getOneQuery($dbFact, $queryP, 'consecutivo')) + 1;

      //Prerara insert a BD
      try {
        //1.- Insert ec_clientes
        if (empty($idCliente)) {
          $insertEcClientes = "
            INSERT INTO ec_clientes (nombre,telefono,email,es_cliente,id_sucursal)
            VALUES (:nombre,:telefono,:email,:es_cliente,:id_sucursal);
          ";
          $insertStmt = $dbFact->prepare($insertEcClientes);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "nombre"=>$cliente['rfc'],
            "telefono"=>$cliente['telefono'],
            "email"=>$cliente['email'],
            "es_cliente"=>'1',
            "id_sucursal"=>$venta['id_sucursal']
          ));

          //Recupera id_cliente
          $idCliente = $dbFact->lastInsertId();
        }else{
          //Consulta ec_clientes_razones_sociales
          $consultaClienteRS= "select id_cliente_rs, id_cliente
            from ec_clientes_razones_sociales
            where id_cliente='{$idCliente}'
            order by id_cliente_rs asc
            limit 1
            ;";
          //return $consultaProducto;
          $resultadoClienteRS = $dbFact->query($consultaClienteRS);
          $resultadoClienteRSRow = $resultadoClienteRS->fetch();
          $idClienteRS = $resultadoClienteRSRow['id_cliente_rs'];
        }
        $inserts['ec_clientes']=$idCliente;

        if (!empty($idCliente) && $idCliente !=0) {
          //2.- Insert ec_clientes_razones_sociales
          if (empty($idClienteRS)) {
            $insertEcClientesRS= "
              INSERT INTO ec_clientes_razones_sociales (id_cliente,rfc,razon_social,calle,no_int,no_ext,colonia,del_municipio,cp,localidad,estado,pais)
              VALUES (:id_cliente,:rfc,:razon_social,:calle,:no_int,:no_ext,:colonia,:del_municipio,:cp,:localidad,:estado,:pais);
            ";
            $insertStmt = $dbFact->prepare($insertEcClientesRS);
            //Ejecuta insert
            $insertStmt->execute(array(
              //Valores Magento
              "id_cliente"=>$idCliente,
              "rfc"=>$cliente['rfc'],
              "razon_social"=>$cliente['razon_social'],
              "calle"=>$direccion['calle'],
              "no_int"=>$direccion['no_int'],
              "no_ext"=>$direccion['no_ext'],
              "colonia"=>$direccion['colonia'],
              "del_municipio"=>$direccion['del_municipio'],
              "cp"=>$direccion['cp'],
              "localidad"=>$direccion['localidad'],
              "estado"=>$direccion['estado'],
              "pais"=>$direccion['pais']
            ));
            //Recupera id_cliente_rs
            $idClienteRS = $dbFact->lastInsertId();
          }
          $inserts['ec_clientes_razones_sociales']=$idClienteRS;
          //3.- Insert ec_ventas
          $insertEcVentas = "
            INSERT INTO ec_ventas (es_venta_linea,folio,numero_orden,fecha,hora,subtotal,total,id_usuario,id_sucursal,id_cliente,facturado,id_forma_pago,id_metodo_pago,cancelado,cfdi)
            VALUES (:es_venta_linea,:folio,id_venta,:fecha,:hora,:subtotal,:total,:id_usuario,:id_sucursal,:id_cliente,:facturado,:id_forma_pago,:id_metodo_pago,:cancelado,:cfdi);
          ";
          $insertStmt = $dbFact->prepare($insertEcVentas);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "es_venta_linea"=>1,
            "folio"=> $prefijo.$consecutivo_venta,
            "fecha"=>$venta['fecha'],
            "hora"=>$venta['hora'],
            "subtotal"=>$venta['total'],
            "total"=>$venta['total'],
            "id_usuario"=>$id_usuario,
            "id_sucursal"=>$venta['id_sucursal'],
            "id_cliente"=>$idCliente,
            "facturado"=>0,
            "id_forma_pago"=>$venta['forma_pago'],
            "id_metodo_pago"=>$venta['metodo_pago'],
            "cancelado"=>0,
            "cfdi"=>$venta['cfdi']
          ));
          //Recupera id_venta
          $idVenta = $dbFact->lastInsertId();
          $inserts['ec_ventas']=$idVenta;
          if ($idVenta) {
            //Actualiza numero de venta y folio
            $updateVenta = "update ec_ventas set numero_orden=id_venta where id_venta='{$idVenta}';";
            $resultadoUpdate = $dbFact->query($updateVenta);
            $updateFolio = "update api_tipo_facturacion a set a.consecutivo ='{$consecutivo_venta}' where a.id='2';";
            $resultadoUpdate = $dbFact->query($updateFolio);
          }
          //4.- Insert ec_movimiento_inventario
          $insertEcMovimientoI = "
            INSERT INTO ec_movimiento_inventario (id_tipo_movimiento,id_usuario,folio,fecha,hora,observaciones,id_sucursal,id_venta)
            VALUES (:id_tipo_movimiento,:id_usuario,:folio,date(now()),time(now()),:observaciones,:id_sucursal,:id_venta);
          ";
          $insertStmt = $dbFact->prepare($insertEcMovimientoI);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "id_tipo_movimiento"=>$id_tipo_movimiento,
            "id_usuario"=>$id_usuario,
            "folio"=>$prefijo_mi.$idVenta,
            "observaciones"=>"Movimiento realizado desde una venta en línea",
            "id_sucursal"=>$venta['id_sucursal'],
            "id_venta"=>$idVenta
          ));
          //Recupera id_venta
          $idMovimientoI = $dbFact->lastInsertId();
          $inserts['ec_movimiento_inventario']=$idMovimientoI;

          //4.1- Insert ec_venta_tienda_linea
          $insertEcVentaLinea = "
            INSERT INTO ec_venta_tienda_linea (id_venta,metodo_pago,forma_pago)
            VALUES (:id_venta,:metodo_pago,:forma_pago);
          ";
          $insertStmt = $dbFact->prepare($insertEcVentaLinea);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "id_venta"=>$idVenta,
            "metodo_pago"=>$metodo_pago_texto,
            "forma_pago"=>$forma_pago_texto
          ));
          //Recupera id_venta
          $idVentaLinea = $dbFact->lastInsertId();
          $inserts['ec_venta_tienda_linea']=$idVentaLinea;
          //return $rs->successMessage($request->getParsedBody(),$response, $inserts);
          //Itera productos recibidos
          $inserts['ec_detalle_venta']=[];
          $inserts['ec_detalle_movimiento']=[];
          $productosProcesados = "'0'";
          foreach($productos as $producto) {
            try {
              //Recupera producto
              $consultaProducto = "select
                	p.id_productos, p.orden_lista, p.precio_venta, sp.inventario
                from ec_productos p
                	inner join ec_sucursal_producto sp on sp.id_producto = p.id_productos and sp.id_sucursal='{$venta['id_sucursal']}'
                where p.orden_lista = '{$producto['codigo_sat']}'
                	and sp.inventario >='{$producto['cantidad']}'
                	and p.precio_venta <='{$producto['precio']}'
                	and p.id_productos not in ({$productosProcesados})
                  and p.id_tipo_facturacion='2'
                order by
                	p.precio_venta desc
                limit 1
                ;";
              //return $consultaProducto;
              $resultadoProd = $dbFact->query($consultaProducto);
              $resultadoProdRow = $resultadoProd->fetch();
              $idProducto = $resultadoProdRow['id_productos'];
              //Excluye validación para producto: Costo de Envío
              $idProducto = ($producto['costo_envio']) ? $producto['id_producto'] : $idProducto;
              $valida_inventario = ($producto['costo_envio']) ? false : $valida_inventario;
              //Valida obtención de producto
              if (empty($idProducto)) {
                //Regresa error
                rollBackF($inserts);
                return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', 'No se ha podido guardar el registro de facturación debido a que el producto con código sat: '.$producto['codigo_sat']. ' ,no cumple con los criterios de búsqueda de CL.', 500);
              }else {
                  //Consulta Inventario y compara CL vs Facturación
                  if ($valida_inventario) {
                      $queryICL = "select sum(inventario) as total from ec_almacen_producto where id_producto='{$idProducto}' and inventario>0;";
                      $queryIF = "select sum(inventario) as total from ec_sucursal_producto where id_producto='{$idProducto}' and inventario>0;";
                      $inventarioCL = getOneQuery($db, $queryICL, 'total');
                      $inventarioF = getOneQuery($dbFact, $queryIF, 'total');
                      //Valida inventario CL>F
                      if ($inventarioCL<$inventarioF) {
                          //Regresa error
                          rollBackF($inserts);
                          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', 'No se ha podido guardar el registro de facturación debido a que el producto con código sat: '.$producto['codigo_sat']. ' ,no cumple con los criterios de inventario de CL.', 500);
                      }
                  }
              }

              $productosProcesados = $productosProcesados . ",'{$idProducto}'";
              //5.- Inserta ec_detalle_venta
              //Prepara sentencia de insert
              $insertEcDetalleV = "
                INSERT INTO ec_detalle_venta (id_venta,id_producto,cantidad,precio,monto,codigo_sat)
                VALUES (:id_venta,:id_producto,:cantidad,:precio,:monto,:codigo_sat);
              ";
              $insertStmt = $dbFact->prepare($insertEcDetalleV);
              //Ejecuta insert
              $insertStmt->execute(array(
                //Valores Magento
                "id_venta"=>$idVenta,
                "id_producto"=>$idProducto,
                "cantidad"=>$producto['cantidad'],
                "precio"=>$producto['precio'],
                "monto"=> ($producto['cantidad']*$producto['precio']),
                "codigo_sat"=>$producto['codigo_sat']
              ));
              //Recupera id_pedido_detalle
              $idDetalleV = $dbFact->lastInsertId();
              $inserts['ec_detalle_venta'][]=$idDetalleV;

              //6.- Inserta ec_detalle_movimiento
              //Prepara sentencia de insert
              $insertEcDetalleM = "
                INSERT INTO ec_detalle_movimiento (id_movimiento_inventario,id_producto,cantidad)
                VALUES (:id_movimiento_inventario,:id_producto,:cantidad);
              ";
              $insertStmt = $dbFact->prepare($insertEcDetalleM);
              //Ejecuta insert
              $insertStmt->execute(array(
                //Valores Magento
                "id_movimiento_inventario"=>$idMovimientoI,
                "id_producto"=>$idProducto,
                "cantidad"=>$producto['cantidad']
              ));
              //Recupera id_pedido_detalle
              $idDetalleM = $dbFact->lastInsertId();
              $inserts['ec_detalle_movimiento'][]=$idDetalleM;

            } catch(PDOExecption $e) {
                rollBackF($inserts);
                return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
            }
          }
        }else {
          rollBackF($inserts);
          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', 'No se pudo insertar la venta de forma correcta, intente nuevamente.', 500);
        }
      } catch(PDOExecption $e) {
          rollBackF($inserts);
          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
      }
      //Limpia variables
      $db = null;
      $dbFact = null;
      //Regresa resultado
      //return json_encode($inserts);
      return $rs->successMessage($request->getParsedBody(),$response, $inserts);
    } catch (PDOException $e) {
      rollBackF($inserts);
      return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
    }
});

//Rollback
function rollBackF($inserts = null){
    if (!empty($inserts)) {
      //Valida registros por eliminar
      if ($inserts['ec_ventas']) {
        $dbFact = new dbFact();
        $dbFact = $dbFact->conectDB();
        $sqlDelete="delete from ec_ventas where id_venta='{$inserts['ec_ventas']}';";
        $resultDelete = $dbFact->query($sqlDelete);
      }
    }
}

//Consulta registro
function getOneQuery($db, $query, $columnReturn){
    $queryStatement= $query;
    $result = $db->query($queryStatement);
    $resultRow = $result->fetch();
    $value = $resultRow[$columnReturn];
    return $value;
}

?>
