<html>
<head>
	<title>Carga de inventario por CSV</title>
</head>
<body>
	<div></div>
</body>
</html>


<?php
	include( '../../conexionMysqli.php' );

	$Inventory = new Inventory( $link );
//	$Inventory->process_csv( 'carga_inventario01092022.csv' );
	$Inventory->process_csv( 'ec_carga_inventario_proveedor_producto_15_09_22.csv' );
	
	class Inventory
	{
		private $link;

		function __construct( $connection ){
			$this->link = $connection;
			if( ! $this->link ){
				die( "No hay conexion con la base de datos." );
			}
		}

		public function process_csv( $file_name ){
			$detail_list = array();
			$file = fopen( $file_name, "r");
			$data = array();
			 
			while (!feof($file)) {
			        $data[] = fgetcsv($file,null,';');
			       // var_dump($data);
			}
		//	var_dump( $data );
			foreach ($data as $key => $value) {
				//echo 'here';
				if( $key > 0 && $value != '' ){//
					$val = explode( ',', $value[0] );
					//$product_provider_id = $this->getProductProvider( $val[0],  $val[2] );
					//echo "<br>Prod_prov : {$product_provider_id}<br>";
					array_push( $detail_list, array( $val[0], $val[1], $val[2], $val[3], $val[4] ) );//$product_provider_id
				}
			}
			fclose($file);
			echo $this->insertInventoryMovement( $detail_list );
		}

		public function insertInventoryMovement( $detail_list ){
			//inserta la cabecera del movimeinto de almacen
			$sql = "INSERT INTO ec_movimiento_almacen ( /*1*/id_movimiento_almacen, /*2*/id_tipo_movimiento, 
			/*3*/id_usuario, /*4*/id_sucursal, /*5*/fecha, /*6*/hora, /*7*/observaciones, /*8*/id_pedido,
			/*9*/id_orden_compra, /*10*/lote, /*11*/id_maquila, /*12*/id_transferencia, /*13*/id_almacen )
				VALUES( /*1*/NULL, /*2*/9, /*3*/1, /*4*/1, /*5*/NOW(), /*6*/NOW(), 
					/*7*/'SUMA DE INVENTARIO POR CSV (OSCAR 2022)', /*8*/-1, /*9*/-1, /*10*/NULL,
					/*11*/-1, /*12*/-1, /*13*/1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar la cabecera del movimiento de almacen : {$this->link->error} {$sql}" );
			echo "<br>$sql<br>";
			$header_id = $this->link->insert_id;

		//inserta el detalle de movimiento de almacen
			foreach ( $detail_list as $key => $value) {
				//if( $key == 0 ){
				if( $value[4] == "" || $value[4] == null ){
					$value[4] = 'null';
				}
				$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento,
							/*3*/id_producto, /*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle,/*7*/id_oc_detalle,
							/*8*/id_proveedor_producto ) VALUES ( /*1*/NULL, /*2*/{$header_id},/*3*/{$value[0]}, /*4*/{$value[3]}, 
							/*5*/{$value[3]}, /*6*/-1, /*7*/-1, /*8*/{$value[4]} )";
						//$exc = $link->query( $sql ) or die ( "Error al insertar el detalle del movimiento de almacen 1 : {$link->error}" );	
				$stm_2 = $this->link->query( $sql ) or die( "Error al insertar detalle del movimiento de almacen : {$this->link->error} {$sql}" );
				echo "<br>Contador : {$key}<br>QUERY : {$sql}<br><br>";
				//}
			//	echo $sql . '<br>';
			}
			echo '<h3>Datos insertados correctamente</h3>';
		}

		public function getProductProvider ( $product_id, $product_name ){
			//echo 'Producto ' . $product_id;
			$sql = "SELECT id_proveedor_producto AS product_provider_id FROM ec_proveedor_producto WHERE id_producto = {$product_id} LIMIT 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el id de proveedor producto :  {$sql} ; {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				echo "<br>{$product_name} no tiene proveedor_producto <br>";
			}
			$row = $stm->fetch_assoc();
			return $row['product_provider_id'];
		}

		/*public function getWithoutProductProvider(  ){

		}*/
	}
?>