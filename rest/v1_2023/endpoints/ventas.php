<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: ventas
* Path: /ventas/nueva
* Método: POST
* Descripción: Servicio para registrar nueva venta
*/
$app->post('/ventas/nueva', function (Request $request, Response $response){
    //Init
    $db = new db();
    $db = $db->conectDB();
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
    $folio = $request->getParam('folio');
    $subtotal = $request->getParam('subtotal');
    $descuento = $request->getParam('descuento');
    $porcentaje_descuento = $request->getParam('porcentaje_descuento');
    $total = $request->getParam('total');
    $productos_input = $request->getParam('productos');
    $sucursal = $request->getParam('sucursal');
    $costo_envio = $request->getParam('costo_envio');
    $grupo_cliente_magento = $request->getParam('grupo_cliente_magento');

    //Validar elementos requerido para crear venta
    if (empty($folio) || empty($subtotal) || empty($descuento) || empty($total) || empty($productos_input) || empty($sucursal) || empty($grupo_cliente_magento) /*|| empty($costo_envio)*/ ) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear una venta', 400);
    }
    //Validar elementos requerido para nodo productos
    if (count($productos_input)>0) {
      //Genera estructura de productos agrupable
      $productos = [];
      $producto_agrupado = [];
      //Recupera lista de precios mostrador
      $queryPM = "select a.value from api_config a where a.key='1' and name='lista_mostrador';";
      $precioMostrador = getOneQuery($db, $queryPM, 'value');
      //Itera y valida productos para guardado
      foreach($productos_input as $producto) {
        if (empty($producto['idProducto']) || empty($producto['cantidad']) || empty($producto['precio']) || empty($producto['monto']) || (empty($grupo_cliente_magento) && empty($producto['grupo_cliente_magento'])) ) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear una venta', 400);
        }
        //Recupera lista de precio para productos
        //NOT LOGGED IN = mostrador
        $grupo_cliente_magento = ($grupo_cliente_magento=='NOT LOGGED IN')? 'Mostrador':$grupo_cliente_magento;
        $producto['grupo_cliente_magento'] = empty($producto['grupo_cliente_magento']) ? $grupo_cliente_magento : $producto['grupo_cliente_magento'];
        $queryG = "select id_precio from ec_precios where grupo_cliente_magento='{$producto['grupo_cliente_magento']}';";
        $grupo_cliente = getOneQuery($db, $queryG, 'id_precio');
        if (empty($grupo_cliente)) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Erroneos', 'El valor especificado para Grupo cliente magento no es reconocido por CL', 400);
        }
        $producto['grupo_cliente_magento'] = $grupo_cliente;

        if ($producto['agrupable']) {
            //Recupera productos agrupados
            $sqlProductosA="select
              	pd.id_producto_ordigen idProducto,
              	pd.cantidad cantidad,
                pcd.precio_venta precio,
              	ifnull(tl.porcentaje_descuento_agrupado,0) descuento
              from ec_productos_detalle pd
              	inner join ec_productos p on p.id_productos = pd.id_producto_ordigen
                left join ec_producto_tienda_linea tl on tl.id_producto = pd.id_producto
                inner join ec_precios_detalle pcd on pcd.id_producto=p.id_productos
              where
              	pd.id_producto = '".$producto['idProducto'] ."'
                and pcd.id_precio = '".$producto['grupo_cliente_magento'] ."'
                and pd.cantidad > pcd.de_valor
                and pd.cantidad < pcd.a_valor
            ;";
            foreach ($db->query($sqlProductosA) as $row) {
              //Aplica descuento general adicional para producto agrupable
              $row['precio'] = (!empty($porcentaje_descuento) && $porcentaje_descuento>0 ) ? $row['precio']*((100-$porcentaje_descuento)/100) : $row['precio'];

              //Aplica descuento por producto agrupable
              $montoAgrupado = $row['precio'] * $row['cantidad'] * $producto['cantidad'];
              $precioUnitario = $row['precio'];

              //$row['descuento']=10;
              $producto_agrupado = array(
                'idProducto' => $row['idProducto'],
                'cantidad' => $row['cantidad'] * $producto['cantidad'],
                'precio' => ($row['descuento']>0 && $precioMostrador==$producto['grupo_cliente_magento']) ? $precioUnitario * ((100 - $row['descuento'])/100) : $precioUnitario,
                'monto' => ($row['descuento']>0 && $precioMostrador==$producto['grupo_cliente_magento']) ? $montoAgrupado * ((100 - $row['descuento'])/100) : $montoAgrupado,
                'grupo_cliente_magento' => $producto['grupo_cliente_magento'],
                'agrupable' => false
              );
              $productos[] = $producto_agrupado;
            }

        }else {
            //Aplica descuento general adicional
            $producto['precio'] = (!empty($porcentaje_descuento) && $porcentaje_descuento>0 ) ? $producto['precio']*((100-$porcentaje_descuento)/100) : $producto['precio'];
            $producto['monto'] = (!empty($porcentaje_descuento) && $porcentaje_descuento>0 ) ? $producto['precio']*$producto['cantidad'] : $producto['monto'];
            $productos[] = $producto;
        }
      }
      //Agrega costo de envío
      if ($costo_envio>0) {
          $queryCostoEnvio = "select a.value from api_config a where a.key='productos' and name='costo_envio';";
          $idProdCostoEnvio = getOneQuery($db, $queryCostoEnvio, 'value');
          $queryGCE = "select id_precio from ec_precios where grupo_cliente_magento='{$grupo_cliente_magento}';";
          $grupo_clienteCE = getOneQuery($db, $queryGCE, 'id_precio');

          $productos[] = array(
            'idProducto' => $idProdCostoEnvio,
            'cantidad' => 1,
            'precio' => $costo_envio,
            'monto' => $costo_envio,
            'grupo_cliente_magento' => $grupo_clienteCE,
            'agrupable' => false
          );
      }
    }
    //return print_r($productos,true);

    //Ejecuta inserts a BD cdelasluces
    try {
      //0.- Genera array para identificar registros insertados
      $inserts=[];

      //Recupera variables de api_config
      $sqlAPIConfig="SELECT c.name, c.value FROM api_config c WHERE c.key='{$sucursal}' and c.value is not null";
      foreach ($db->query($sqlAPIConfig) as $row) {
        $id_cliente= ($row['name'] == 'id_cliente') ? $row['value'] : $id_cliente;
        $id_estatus=($row['name'] == 'id_estatus') ? $row['value'] : $id_estatus;
        $id_moneda=($row['name'] == 'id_moneda') ? $row['value'] : $id_moneda;
        $id_direccion=($row['name'] == 'id_direccion') ? $row['value'] : $id_direccion;
        $id_razon_social=($row['name'] == 'id_razon_social') ? $row['value'] : $id_razon_social;
        $pagado=($row['name'] == 'pagado') ? $row['value'] : $pagado;
        $id_usuario=($row['name'] == 'id_usuario') ? $row['value'] : $id_usuario;
        $id_tipo_envio=($row['name'] == 'id_tipo_envio') ? $row['value'] : $id_tipo_envio;
        $id_cajero=($row['name'] == 'id_cajero') ? $row['value'] : $id_cajero;
        $prefijo_folio=($row['name'] == 'prefijo_folio') ? $row['value'] : $prefijo_folio;
      }
      //Prerara insert a BD
      try {
        //1.- Insert ec_pedidos
        $insertEcPedidos = "
          INSERT INTO ec_pedidos (folio_nv,subtotal,total,descuento,id_cliente,id_estatus,id_moneda,fecha_alta,id_direccion,id_razon_social,pagado,id_sucursal,id_usuario,id_tipo_envio,ultima_modificacion,id_cajero,ultima_sincronizacion)
          VALUES (:folio_nv,:subtotal,:total,:descuento,:id_cliente,:id_estatus,:id_moneda,now(),:id_direccion,:id_razon_social,:pagado,:id_sucursal,:id_usuario,:id_tipo_envio,now(),:id_cajero,now());
        ";
        $insertStmt = $db->prepare($insertEcPedidos);
        //Ejecuta insert
        $insertStmt->execute(array(
          //Valores Magento
          "folio_nv"=>$prefijo_folio.$folio,
          "subtotal"=>$subtotal,
          "total"=>$total,
          "descuento"=>$descuento,
          "id_sucursal"=>$sucursal,
          //Valores Constantes
          "id_cliente"=>$id_cliente,
          "id_estatus"=>$id_estatus,
          "id_moneda"=>$id_moneda,
          "id_direccion"=>$id_direccion,
          "id_razon_social"=>$id_razon_social,
          "pagado"=>$pagado,
          "id_usuario"=>$id_usuario,
          "id_tipo_envio"=>$id_tipo_envio,
          "id_cajero"=>$id_cajero
          /* -> Campos no requeridos
          "tipo_pedido"=>"0",
          "id_status_agrupacion"=>"-1",
          "id_devoluciones"=>"0",
          "iva"=>"0",
          "ieps"=>"0",
          "correo"=>"0",
          "facebook"=>"0",
          "surtido"=>"0",
          "enviado"=>"0",
          "id_equivalente"=>"0"*/
        ));
        //Recupera id_pedido
        $idPedido = $db->lastInsertId();
        $inserts['ec_pedidos']=$idPedido;
        if (!empty($idPedido) && $idPedido !=0) {
          //2.- Insert ec_pedido_pagos
          $insertEcPedidoPagos = "
            INSERT INTO ec_pedido_pagos (id_equivalente,id_pedido,id_tipo_pago,fecha,hora,monto,referencia,id_moneda,tipo_cambio,id_nota_credito,id_cxc,exportado,es_externo)
            VALUES (:id_equivalente,:id_pedido,:id_tipo_pago,date(now()),time(now()),:monto,:referencia,:id_moneda,:tipo_cambio,:id_nota_credito,:id_cxc,:exportado,:es_externo);
          ";
          $insertStmt = $db->prepare($insertEcPedidoPagos);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "id_pedido"=>$idPedido,
            "monto"=>$total,
            //Valores constantes
            "id_equivalente"=>"0",
            "id_tipo_pago"=>"1",
            "referencia"=>"0",
            "id_moneda"=>"1",
            "tipo_cambio"=>"-1",
            "id_nota_credito"=>"-1",
            "id_cxc"=>"-1",
            "exportado"=>"0",
            "es_externo"=>"0"
          ));
          //Recupera id_pedido
          $idPedidoPago = $db->lastInsertId();
          $inserts['ec_pedido_pagos']=$idPedidoPago;
          //3.- Insert ec_movimiento_almacen
          $insertEcMovimientoAlmacen = "
            INSERT INTO ec_movimiento_almacen (id_tipo_movimiento,id_usuario,id_sucursal,fecha,hora,observaciones,id_pedido,id_almacen,ultima_sincronizacion)
            VALUES (:id_tipo_movimiento,:id_usuario,:id_sucursal,date(now()),time(now()),:observaciones,:id_pedido,:id_almacen,now());
          ";
          $insertStmt = $db->prepare($insertEcMovimientoAlmacen);
          //Ejecuta insert
          $insertStmt->execute(array(
            //Valores Magento
            "id_pedido"=>$idPedido,
            //Valores constantes
            "id_tipo_movimiento"=>"2",
            "id_usuario"=>"1",
            "id_sucursal"=>"1",
            "observaciones"=>"Venta en línea Magento",
            "id_almacen"=>1,
          ));
          //Recupera id_pedido
          $idMovimientoAlmacen = $db->lastInsertId();
          $inserts['ec_movimiento_almacen']=$idMovimientoAlmacen;
          //Itera productos recibidos
          $inserts['ec_pedidos_detalle']=[];
          $inserts['ec_movimiento_detalle']=[];
          foreach($productos as $producto) {
            try {
              //4.- Inserta ec_pedidos_detalle
              //Prepara sentencia de insert
              $insertEcPedidosDetalle = "
                INSERT INTO ec_pedidos_detalle (id_pedido,id_producto,cantidad,precio,monto,cantidad_surtida,id_precio)
                VALUES (:id_pedido,:id_producto,:cantidad,:precio,:monto,:cantidad_surtida,:id_precio);
              ";
              $insertStmt = $db->prepare($insertEcPedidosDetalle);
              //Ejecuta insert
              $insertStmt->execute(array(
                //Valores Magento
                "id_pedido"=>$idPedido,
                "id_producto"=>$producto['idProducto'],
                "cantidad"=>$producto['cantidad'],
                "precio"=>$producto['precio'],
                "monto"=>$producto['monto'],
                "cantidad_surtida"=>$producto['cantidad'],
                "id_precio"=>$producto['grupo_cliente_magento']

              ));
              //Recupera id_pedido_detalle
              $idPedidoDetalle = $db->lastInsertId();
              $inserts['ec_pedidos_detalle'][]=$idPedidoDetalle;
              //5.- Inserta ec_movimiento_detalle
              //Prepara sentencia de insert
              $insertEcMovimientoDetalle = "
                INSERT INTO ec_movimiento_detalle (id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
                VALUES (:id_movimiento,:id_producto,:cantidad,:cantidad_surtida,:id_pedido_detalle,:id_oc_detalle);
              ";
              $insertStmt = $db->prepare($insertEcMovimientoDetalle);
              //Ejecuta insert
              $insertStmt->execute(array(
                //Valores Magento
                "id_movimiento"=>$idMovimientoAlmacen,
                "id_producto"=>$producto['idProducto'],
                "cantidad"=>$producto['cantidad'],
                "cantidad_surtida"=>$producto['cantidad'],
                "id_pedido_detalle"=>$idPedidoDetalle,
                "id_oc_detalle"=>"-1"
              ));
              //Recupera id_pedido_detalle
              $idMovimiento = $db->lastInsertId();
              $inserts['ec_movimiento_detalle'][]=$idMovimiento;

            } catch(PDOExecption $e) {
                rollBackV($inserts);
                return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
            }
          }
        }else {
          rollBackV($inserts);
          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', 'No se pudo insertar la venta de forma correcta, intente nuevamente.', 500);
        }
      } catch(PDOExecption $e) {
          rollBackV($inserts);
          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
      }
      //Limpia variables
      $db = null;
      //Regresa resultado
      //return json_encode($inserts);
      return $rs->successMessage($request->getParsedBody(),$response, $inserts);
    } catch (PDOException $e) {
      rollBackV($inserts);
      return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
    }
});

//Rollback
function rollBackV($inserts = null){
    if (!empty($inserts)) {
      //Valida registros por eliminar
      if ($inserts['ec_pedidos']) {
        $db = new db();
        $db = $db->conectDB();
        $sqlDelete="delete from ec_pedidos where id_pedido='{$inserts['ec_pedidos']}';";
        $resultDelete = $db->query($sqlDelete);
      }
    }
}

?>
