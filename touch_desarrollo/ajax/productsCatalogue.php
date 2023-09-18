<?php
	
	if( isset( $_GET['fl_db'] ) ){
		$action = $_GET['fl_db'];
//
		include( "../../conect.php" );
		include( "../../conexionMysqli.php" );
		$db = new dB( $link, $sucursal_id );
		switch ( $action ) {
		//obtener productos
			case 'getProductsCatalogue' :
				$price = null;
				if( isset( $_GET['price_list'] ) ){
					$price = $_GET['price_list'];
				}
				$sale_id = null;
				if( isset( $_GET['sale_id'] ) ){
					$sale_id = $_GET['sale_id'];
				}
				echo $db->getProductsCatalogue( $price, $sale_id );
			break;
			default:
				die( "Permission denied on '{$action}'!" );
			break;
		}
	}

	class dB
	{
		private $link;
		function __construct( $connection, $store_id )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
		}

		function getProductsCatalogue( $price_list = null, $sale_id = null ){
			$resp = array();
			$resp2 = array();
		//busca la lista de precios de la sucursal
			$price_id = null;
			if( $price_list == null ){
				$sql = "SELECT 
							id_precio AS price_id
						FROM sys_sucursales
						WHERE id_sucursal = {$this->store_id}";
				$stm = $this->link->query( $sql ) or die( "Error al buscar la lista de precios de la sucursal : {$link->error}" );
				$row = $stm->fetch_assoc();
				$price_id = $row['price_id'];
			}else{
				$price_id = $price_list;
			}
		//busca la lista de precios de la sucursal
			$sale_union = "";
			$sale_condition = "";
			$sale_field = "";
			$name_field = "CONCAT( '<><span style=\'color : gray ;\'>', GROUP_CONCAT(pp.clave_proveedor SEPARATOR ' ' ), '</span><>', p.nombre )";
			if( $sale_id != null ){
				$sale_union = "LEFT JOIN ec_pedidos_detalle pedd 
				ON p.id_productos = pedd.id_producto";
				$sale_condition = "AND pedd.id_pedido = {$sale_id}";
				$sale_field = ", pedd.id_pedido_detalle AS sale_detail_id";	
				$name_field = "p.nombre";
			}

			$sql = "SELECT
						p.id_productos AS product_id,
						p.orden_lista AS list_order,
						{$name_field} AS product_name,/*modificacion Oscar 2023 para poder buscar por modelo de proveedor*/
						GROUP_CONCAT( pp.codigo_barras_pieza_1 SEPARATOR ' __ ' ) AS codigo_barras_pieza_1, 
						GROUP_CONCAT( pp.codigo_barras_pieza_2 SEPARATOR ' __ ' ) AS codigo_barras_pieza_2,
						GROUP_CONCAT( pp.codigo_barras_pieza_3 SEPARATOR ' __ ' ) AS codigo_barras_pieza_3,
						GROUP_CONCAT( pp.codigo_barras_presentacion_cluces_1 SEPARATOR ' __ ' ) AS codigo_barras_presentacion_cluces_1,
						GROUP_CONCAT( pp.codigo_barras_presentacion_cluces_2 SEPARATOR ' __ ' ) AS codigo_barras_presentacion_cluces_2,
						GROUP_CONCAT( pp.codigo_barras_caja_1 SEPARATOR ' __ ' ) AS codigo_barras_caja_1,
						GROUP_CONCAT( pp.codigo_barras_caja_2 SEPARATOR ' __ ' ) AS codigo_barras_caja_2,
						/*GROUP_CONCAT(pp.clave_proveedor SEPARATOR ' __ ' ) AS clave_proveedor,*/
						IF(	pd.id_precio_detalle IS NULL,
							'<span __CLASS__>Sin Precio</span>',
							GROUP_CONCAT(	
								DISTINCT( CONCAT( '<span __CLASS__>', pd.de_valor, ' x $ ', ROUND( pd.precio_venta * pd.de_valor ), '</span>' )  ) ORDER BY pd.de_valor ASC 
								SEPARATOR ' l ' 
							)
						) AS product_prices
						{$sale_field}
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = p.id_productos
					LEFT JOIN sys_sucursales_producto sp
					ON sp.id_producto = p.id_productos
					AND sp.id_sucursal = '{$this->store_id}'
					LEFT JOIN ec_precios_detalle pd
					ON pd.id_producto = p.id_productos
					AND pd.id_precio = '{$price_id}'
					{$sale_union}
					WHERE p.id_productos > 0
					AND p.nombre NOT IN( 'Libre', 'ERROR ESTACIONALIDAD X2', 'ERROR ESTACIONALIDA X2', 'Error', 'Error ', 'Producto De Ajuste' )
					AND p.id_categoria !=1
					AND sp.id_sucursal = '{$this->store_id}'
					AND sp.estado_suc = '1'
					{$sale_condition}
					GROUP BY p.id_productos
					ORDER BY p.orden_lista";
			//die( $price_id . ' _ ' . $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row);
			}
			$sql="SELECT 
    			CONCAT('paquete',p.id_paquete ) AS pack_id_,
    			p.id_paquete AS pack_id,
    			p.nombre AS pack_name 
          	FROM ec_paquetes p 
          	LEFT JOIN sys_sucursales_paquete sp ON p.id_paquete=sp.id_paquete
          	LEFT JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
          	WHERE p.activo=1
          	AND sp.id_sucursal = {$this->store_id}
          	AND sp.estado_suc=1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp2, $row);
			}
			return "ok|" . json_encode( $resp ) . "|" . json_encode( $resp2 );
		}
	}
	
?>