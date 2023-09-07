<?php
  if( isset( $_GET['fl_sale_return']  ) ){
    include( '../confic.inc.php' );
    include( '../conectMin.php' );
    include( '../conexionMysqli.php' );
    $action = $_GET['fl_sale_return'];
    switch ( $action ) {
      case 'getValidatedProducts':
        echo $this->getValidatedProducts( $GET['ticket_id'] );
      break;
      
      default:
        die( "Permission denied on '{$action}'" );
      break;
    }

  }

  class SaleReturn
  {
    private $link;
    function __construct( $connection ){
      $this->link = $connection;
//  echo 'here';
    }

    public function getValidatedProducts( $ticket_id ){
      $resp = "";
      $sql = "SELECT
                ax.product_id,
                ax.product_provider_id,
                ax.list_order,
                ax.product_name,
                ax.provider_clue,
                ax.validated_pieces,
                 
                  IF( dd.id_devolucion_detalle IS NULL, 
                    0, 
                    IF( ax.is_maquiled = 0,
                      SUM(dd.cantidad),
                      (SELECT
                        ROUND( SUM( dd.cantidad ) / cantidad )
                      FROM ec_productos_detalle
                      WHERE id_producto = ax.product_id
                      )
                    ) 
                  ) 
                 AS returned
              FROM(
                SELECT
                  p.es_maquilado AS is_maquiled,
                  p.id_productos AS product_id,
                  pp.id_proveedor_producto AS product_provider_id,
                  p.orden_lista AS list_order,
                  p.nombre AS product_name,
                  pp.clave_proveedor AS provider_clue,
                  IF( p.es_maquilado = 0, 
                    SUM( pvu.piezas_validadas ),
                    (SELECT
                        ROUND( SUM( pvu.piezas_validadas ) / cantidad )
                      FROM ec_productos_detalle
                      WHERE id_producto = p.id_productos
                    )
                  ) AS validated_pieces
                FROM ec_pedidos_validacion_usuarios pvu
                LEFT JOIN ec_pedidos_detalle pd
                ON pvu.id_pedido_detalle = pd.id_pedido_detalle
                LEFT JOIN ec_productos p 
                ON p.id_productos = pd.id_producto
                LEFT JOIN ec_proveedor_producto pp
                ON pp.id_proveedor_producto = pvu.id_proveedor_producto
                WHERE pd.id_pedido = {$ticket_id}
                AND pvu.id_proveedor_producto IS NOT NULL
                GROUP BY pd.id_producto, pp.id_proveedor_producto
              )ax
              LEFT JOIN ec_devolucion dev
              ON dev.id_pedido IN ( {$ticket_id} )
              LEFT JOIN ec_devolucion_detalle dd
              ON dd.id_devolucion = dev.id_devolucion
              AND dd.id_proveedor_producto = ax.product_provider_id
              GROUP BY ax.product_id, ax.product_provider_id";
  //echo $sql ;
      $stm = $this->link->query( $sql ) or die( "Error al consultar los productos que fueron validados :{$sql} {$this->link->error}" );
      $resp = "<table class=\"table\">
                <thead>
                  <tr>
                    <th class=\"text-center\">Ord Lista</th>
                    <th class=\"text-center\">Producto</th>
                    <th class=\"text-center\">Clave Prov</th>
                    <th class=\"text-center\">Cantidad</th>
                    <th class=\"text-center\">Devuelto</th>
                  </tr>
                </thead>
                <tbody id=\"validated_list\">";
      $counter = 0;
      while( $row = $stm->fetch_assoc() ){
        $resp .= "<tr>
            <td id=\"validated_0_{$counter}\" class=\"no_visible\">{$row['product_id']}</td>
            <td id=\"validated_1_{$counter}\" class=\"no_visible\">{$row['product_provider_id']}</td>
            <td id=\"validated_2_{$counter}\" >{$row['list_order']}</td>
            <td id=\"validated_3_{$counter}\" >{$row['product_name']}</td>
            <td id=\"validated_4_{$counter}\" class=\"text-center\">{$row['provider_clue']}</td>
            <td id=\"validated_5_{$counter}\" class=\"text-center\">{$row['validated_pieces']}</td>
            <td id=\"validated_6_{$counter}\" class=\"text-center\">{$row['returned']}</td>
        <tr>";
        $counter ++;
      }
      $resp .= "</tbody></table>";
      return $resp;
    }
  }
?>