<html>
<head>
	<title>Carga de ultimo precio de Compra CSV</title>
</head>
<body>
	<div></div>
</body>
</html>


<?php
	include( '../../conexionMysqli.php' );

	$Inventory = new Inventory( $link );
//	$Inventory->process_csv( 'carga_inventario01092022.csv' );
	$Inventory->process_csv( 'ultimos_precios.csv' );
	
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
			$this->link->autocommit( false );

		//inserta el detalle de movimiento de almacen
			foreach ( $detail_list as $key => $value) {
				//if( $key == 0 ){
				if( $value[4] == "" || $value[4] == null ){
					$value[4] = 'null';
				}
				$sql = "UPDATE ec_productos SET precio_compra = {$value[3]} WHERE id_productos = {$value[0]}";
						//$exc = $link->query( $sql ) or die ( "Error al insertar el detalle del movimiento de almacen 1 : {$link->error}" );	
				$stm_2 = $this->link->query( $sql ) or die( "Error al insertarel ultimo precio de compra : {$this->link->error} {$sql}" );
				echo "<br>Contador : {$key}<br>QUERY : {$sql}<br><br>";
				//}
			//	echo $sql . '<br>';
			}
			$this->link->autocommit( true );
			echo '<h3>Datos insertados correctamente</h3>';
		}

	}
?>