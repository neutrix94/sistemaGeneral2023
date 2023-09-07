<?php
    //Clase para buscar productos por código de barras
    //include("../../conectMin.php");
    include("../../conexionMysqli.php");
    
    header("Content-Type: text/plain;charset=utf-8");

    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    mysql_set_charset("utf8");

    /*header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header('content-type: application/json; charset=utf-8');*/

    //Recupera código
    extract($_GET);
    $codigo=trim($codigo);
    //$codigo ='518 PQ0 11205';
    //Prepara estructura de salida
    $response['code']='400';
    $response['status']='error';
    $response['description']='No se ha encontrado el código indicado';
    $products = [];
    $response['products'] = [];

    //Consulta código barras en BD
    $sqlQuery="select
        	pproducto.id_producto,
        	pproducto.codigo_barras_pieza_1,
        	pproducto.codigo_barras_pieza_2,
        	pproducto.codigo_barras_pieza_3,
        	pproducto.codigo_barras_presentacion_cluces_1,
        	pproducto.codigo_barras_presentacion_cluces_2,
        	pproducto.codigo_barras_caja_1,
        	pproducto.codigo_barras_caja_2,
          eproducto.orden_lista,
          eproducto.nombre,
          eproducto.precio_compra
        from
        	ec_proveedor_producto pproducto
            inner join ec_productos eproducto on pproducto.id_producto = eproducto.id_productos
        where
        	pproducto.codigo_barras_pieza_1 = '{$codigo}' or
        	pproducto.codigo_barras_pieza_2 = '{$codigo}' or
        	pproducto.codigo_barras_pieza_3 = '{$codigo}' or
        	pproducto.codigo_barras_presentacion_cluces_1 = '{$codigo}' or
        	pproducto.codigo_barras_presentacion_cluces_2 = '{$codigo}' or
        	pproducto.codigo_barras_caja_1 = '{$codigo}' or
        	pproducto.codigo_barras_caja_2 = '{$codigo}'
        limit 1;";

    //Ejecuta consulta
    try{
    //  $result=mysql_query($sqlQuery);
        $result=$link->query( $sqlQuery ) or die( "Error al consultar coincidencias : {$link->error}" );
      if (!$result) {
          $response['code']='400';
          $response['status']='error';
          $response['description']='Error en la ejecución de la consulta';
      }else{
          $existeCodigo = false;
         // while ($row = mysql_fetch_assoc($result)) {
          while ( $row = $result->fetch_assoc( ) ){//result
           
              $products['id']=$row['id_producto'];
              $products['orden_lista']=$row['orden_lista'];
              $products['nombre']=$row['nombre'];
              $products['precio_compra']=$row['precio_compra'];
              $response['products'][]=$products;
              $existeCodigo = true;
          }
          if($existeCodigo){
            $response['code']='200';
            $response['status']='success';
            $response['description']='Se ha(n) recuperado productos(s)';
          }
      }
  } catch (Exception $error) {
      $response['code']='500';
      $response['status']='error';
      $response['description']=	 $error->getMessage();
  }

    echo json_encode($response);
?>
