<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: productos
* Path: /productos/nuevoFact
* Método: POST
* Descripción: Servicio para registrar nuev producto en BDs facturación
*/
$app->post('/productos/nuevoFact', function (Request $request, Response $response){
  //Init
  $db = new db();           //Instancia BD General
  $db = $db->conectDB();
  $dbFact = new dbFact();   //Instancia a BD Fact
  $dbFact = $dbFact->conectDB();
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

  //Validar elementos requerido para crear venta
  if (empty($productos)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear producto(s)', 400);
  }
  //Validar elementos requerido para nodo productos
  if (count($productos)>0) {
    //Itera y valida productos
    $productRow=0;
    $idProductos = "'0'";
    foreach($productos as $producto) {
      if (empty($producto['idProducto'])) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear producto(s)', 400);
      }
      $idProductos = $idProductos . ",'".$producto['idProducto']."'";
      $productRow ++;
    }
  }

  //Consulta BD para insertar
  try {
      //0.- Genera array para guardar BDs
      $bd_facturacion=[];
      //Recupera bases de datos
      $sqlBDFacturacion="SELECT id, nombre_bd FROM ec_bases_facturacion WHERE active=1";
      foreach ($db->query($sqlBDFacturacion) as $row) {
        $bd_facturacion[]=$row['nombre_bd'];
      }
      if (count($bd_facturacion)<=0) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', 'No existen bases de datos definidas para sistema de facturación: ec_bases_facturacion', 500);
      }
      //Recupera productos
      $productosConsulta = [];
      $sqlProductos="select
        	producto.id_productos,
        	producto.clave,
        	concat( COALESCE(codigo_sat.descripcion_cl,''), ' Modelo ' , COALESCE(producto.orden_lista,'')) as nombre,
        	0 as precio_venta,
        	0 as precio_compra,
        	producto.marca,
        	producto.min_existencia,
        	producto.imagen,
        	producto.observaciones,
        	producto.inventariado,
        	producto.genera_iva,
        	producto.genera_ieps,
        	producto.porc_iva,
        	producto.porc_ieps,
        	producto.desc_gral,
        	producto.nombre as nombre_etiqueta,
        	codigo_sat.codigo_sat orden_lista, -- producto.orden_lista,
        	producto.ubicacion_almacen,
        	producto.codigo_barras_1,
        	producto.codigo_barras_2,
        	producto.codigo_barras_3,
        	producto.codigo_barras_4,
        	producto.maximo_existencia,
        	1 as habilitado, -- producto.habilitado,
        	producto.omitir_alertas,
        	producto.existencia_media,
        	1 as id_tipo_facturacion,
        	if(tienda_linea.producto_solo_facturacion, tienda_linea.producto_solo_facturacion, 0) producto_solo_facturacion
        from ec_productos producto
        left join ec_admin_codigos_sat codigo_sat on codigo_sat.id_categoria = producto.id_categoria and codigo_sat.id_subcategoria=producto.id_subcategoria
        left join ec_producto_tienda_linea tienda_linea on tienda_linea.id_producto = producto.id_productos
        where producto.id_productos in ({$idProductos});";
      foreach ($db->query($sqlProductos) as $row) {
        $productosConsulta[$row['id_productos']]=$row;
      }
  } catch(PDOExecption $e) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
  }


  //Ejecuta inserts a BD cdelasluces
  try {
    //0.- Genera estructura para identificar registros insertados
    $inserts=[];
    //Itera bases de datos
    foreach ($bd_facturacion as $base) {
      $insertsBase=[];
      $insertsBase['base_datos']=$base;
      $insertsBase['detalle']=[];
      //Itera productos
      foreach($productos as $producto) {
        if (!empty($producto['idProducto'])) {
          //Establece estructura para guardar resultado de producto
          $insertsProd=[];
          $insertsProd['producto']='';
          $insertsProd['resultado']='';
          $insertsProd['descripcion']='';
          $insertsProd['producto']=$producto['idProducto'];
          //return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $insertProducto, 500);
          if (!empty($productosConsulta[$producto['idProducto']]['id_productos'])) {
            //Recupera datos y aplica insert
            $insertProducto="insert ignore into {$base}.ec_productos (id_productos,clave,nombre,precio_venta,precio_compra,marca,min_existencia,imagen,observaciones,inventariado,genera_iva,genera_ieps,porc_iva,porc_ieps,desc_gral,nombre_etiqueta,orden_lista,ubicacion_almacen,codigo_barras_1,codigo_barras_2,codigo_barras_3,codigo_barras_4,maximo_existencia,habilitado,omitir_alertas,existencia_media,id_tipo_facturacion,producto_solo_facturacion)
                  values (
                    '{$productosConsulta[$producto['idProducto']]['id_productos']}',
                    '{$productosConsulta[$producto['idProducto']]['clave']}',
                    '{$productosConsulta[$producto['idProducto']]['nombre']}',
                    '{$productosConsulta[$producto['idProducto']]['precio_venta']}',
                    '{$productosConsulta[$producto['idProducto']]['precio_compra']}',
                    '{$productosConsulta[$producto['idProducto']]['marca']}',
                    '{$productosConsulta[$producto['idProducto']]['min_existencia']}',
                    '{$productosConsulta[$producto['idProducto']]['imagen']}',
                    '{$productosConsulta[$producto['idProducto']]['observaciones']}',
                    '{$productosConsulta[$producto['idProducto']]['inventariado']}',
                    '{$productosConsulta[$producto['idProducto']]['genera_iva']}',
                    '{$productosConsulta[$producto['idProducto']]['genera_ieps']}',
                    '{$productosConsulta[$producto['idProducto']]['porc_iva']}',
                    '{$productosConsulta[$producto['idProducto']]['porc_ieps']}',
                    '{$productosConsulta[$producto['idProducto']]['desc_gral']}',
                    '{$productosConsulta[$producto['idProducto']]['nombre_etiqueta']}',
                    '{$productosConsulta[$producto['idProducto']]['orden_lista']}',
                    '{$productosConsulta[$producto['idProducto']]['ubicacion_almacen']}',
                    '{$productosConsulta[$producto['idProducto']]['codigo_barras_1']}',
                    '{$productosConsulta[$producto['idProducto']]['codigo_barras_2']}',
                    '{$productosConsulta[$producto['idProducto']]['codigo_barras_3']}',
                    '{$productosConsulta[$producto['idProducto']]['codigo_barras_4']}',
                    '{$productosConsulta[$producto['idProducto']]['maximo_existencia']}',
                    '{$productosConsulta[$producto['idProducto']]['habilitado']}',
                    '{$productosConsulta[$producto['idProducto']]['omitir_alertas']}',
                    '{$productosConsulta[$producto['idProducto']]['existencia_media']}',
                    3,
                    '{$productosConsulta[$producto['idProducto']]['producto_solo_facturacion']}'
                  );";
//'{$productosConsulta[$producto['idProducto']]['id_tipo_facturacion']}',
            $insertStmt = $dbFact->prepare($insertProducto);
            //Ejecuta insert
            try {
              $insertStmt->execute();
              //Recupera id_producto
              $idProducto = $dbFact->lastInsertId();
              //return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $idProducto, 500);
              //Valida resultado de producto existente
              if ($idProducto>0) {
                $insertsProd['resultado']='Insertado';
                $insertsProd['descripcion']='El producto ha sido insertado correctamente';
              }else{
                //Ejecuta update
                $updateProducto="update {$base}.ec_productos
                      set
                        clave = '{$productosConsulta[$producto['idProducto']]['clave']}',
                        nombre = '{$productosConsulta[$producto['idProducto']]['nombre']}',
                        marca = '{$productosConsulta[$producto['idProducto']]['marca']}',
                        min_existencia = '{$productosConsulta[$producto['idProducto']]['min_existencia']}',
                        imagen = '{$productosConsulta[$producto['idProducto']]['imagen']}',
                        observaciones = '{$productosConsulta[$producto['idProducto']]['observaciones']}',
                        inventariado = '{$productosConsulta[$producto['idProducto']]['inventariado']}',
                        genera_iva = '{$productosConsulta[$producto['idProducto']]['genera_iva']}',
                        genera_ieps = '{$productosConsulta[$producto['idProducto']]['genera_ieps']}',
                        porc_iva = '{$productosConsulta[$producto['idProducto']]['porc_iva']}',
                        porc_ieps = '{$productosConsulta[$producto['idProducto']]['porc_ieps']}',
                        desc_gral = '{$productosConsulta[$producto['idProducto']]['desc_gral']}',
                        nombre_etiqueta = '{$productosConsulta[$producto['idProducto']]['nombre_etiqueta']}',
                        orden_lista = '{$productosConsulta[$producto['idProducto']]['orden_lista']}',
                        ubicacion_almacen = '{$productosConsulta[$producto['idProducto']]['ubicacion_almacen']}',
                        codigo_barras_1 = '{$productosConsulta[$producto['idProducto']]['codigo_barras_1']}',
                        codigo_barras_2 = '{$productosConsulta[$producto['idProducto']]['codigo_barras_2']}',
                        codigo_barras_3 = '{$productosConsulta[$producto['idProducto']]['codigo_barras_3']}',
                        codigo_barras_4 = '{$productosConsulta[$producto['idProducto']]['codigo_barras_4']}',
                        maximo_existencia = '{$productosConsulta[$producto['idProducto']]['maximo_existencia']}',
                        omitir_alertas = '{$productosConsulta[$producto['idProducto']]['omitir_alertas']}',
                        existencia_media = '{$productosConsulta[$producto['idProducto']]['existencia_media']}',
                        producto_solo_facturacion = '{$productosConsulta[$producto['idProducto']]['producto_solo_facturacion']}',
                        id_tipo_facturacion = 3
                      where id_productos = '{$productosConsulta[$producto['idProducto']]['id_productos']}'
                      ;";
                $updateStmt = $dbFact->prepare($updateProducto);
                try {
                  $updateStmt->execute();
                  $idProducto = $productosConsulta[$producto['idProducto']]['id_productos'];
                  $insertsProd['resultado']='Actualizado';
                  $insertsProd['descripcion']='El producto ha sido actualizado correctamente';
                  //Recupera id_producto
                }catch (PDOException $e) {
                  $insertsProd['resultado']='Error';
                  $insertsProd['descripcion']= $e->getMessage();
                }
              }
              //Valida existencia de producto
              if ($idProducto>0) {
                //Prepara inser a ec_sucursal_producto: inventario
                $insertSucProducto ="insert into {$base}.ec_sucursal_producto (id_sucursal, id_producto, inventario)
                      select
                        s.id_sucursal id_sucursal,
                        '{$idProducto}' id_producto,
                        0 inventario
                      from {$base}.sys_sucursales s
                        left join {$base}.ec_sucursal_producto sp on sp.id_sucursal = s.id_sucursal and sp.id_producto='{$idProducto}'
                      where
                        sp.id_sucursal_producto is null
                        and s.activo=1;";
                $insertSPStmt = $dbFact->prepare($insertSucProducto);
                //Ejecuta insert
                try {
                  $insertSPStmt->execute();
                  $insertsProd['resultado']='Actualizado';
                  $insertsProd['descripcion']='El producto e inventario ha sido actualizado correctamente';
                }catch (PDOException $e) {
                  $insertsProd['resultado']='Error';
                  $insertsProd['descripcion']= $e->getMessage();
                }
              }
            }catch (PDOException $e) {
              $insertsProd['resultado']='Error';
              $insertsProd['descripcion']= $e->getMessage();
            }
          }else{
            $insertsProd['resultado']='Error';
            $insertsProd['descripcion']='No se ha encontrado el producto en la base de casa de las luces';
          }
          $insertsBase['detalle'][]=$insertsProd;
        }
        $productRow ++;
      }
      $inserts[]=$insertsBase;
    }
    //Limpia variables
    $db = null;
    //Regresa resultado
    return $rs->successMessage($request->getParsedBody(),$response, $inserts);

  } catch (PDOException $e) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Error_Insert', $e->getMessage(), 500);
  }
});

?>
