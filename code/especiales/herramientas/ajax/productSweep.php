	
<?php
	
/*	include( '../../../../conexionMysqli.php' );
	die( "Habilitar el código del Web Service!" );
	$productSweep = new productSweep( $link );
	$productSweep->getProducts( null, null, null );*/
	class productSweep
	{
		private $link;
		private $token;
		private $end_point;

		function __construct( $connection ){
			$this->link = $connection;
			if( ! $this->link ){
				die( "Error al conectar con la base de datos" );
			}
			//echo 'conectado!';
			$this->getWebServiceConfiguration();
		}

		public function getProducts( $family = null, $type = null, $subtype = null ){
			$sql = "SELECT 
						id_productos AS product_id,
						nombre AS name
					FROM ec_productos 
					WHERE id_productos > 0";
			$sql .= ( $family != null ? " AND id_categoria = '{$family}'" : "" );
			$sql .= ( $family != null ? " AND id_subcategoria = '{$type}'" : "" );
			$sql .= ( $family != null ? " AND id_subtipo = '{$subtype}'" : "" );
	//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				echo "<p>" . $this->invocateWebService( $row ) . "</p>";
			}
		}

		public function getWebServiceConfiguration(){
		//token
			$sql = "select token from api_token where expired_in > now() limit 1;";
			$stm = $this->link->query( $sql ) or die( "Error al consultar token : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( "No hay token Vigente!" );
			}
			$row = $stm->fetch_assoc();
			$this->token = $row['token'];
		//Recupera path de servicios
			$sql = "select a.value from api_config a where a.key='api' and a.name='path' limit 1;";
			$stm = $this->link->query( $sql ) or die( "Error al consultar token : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( "No hay token Vigente!" );
			}
			$row = $stm->fetch_assoc();
			$this->end_point = $row['value'];
		}

		public function invocateWebService( $product ){
		/*Prepar petición*/
			$data = array(
					'productos' => array(
						array(
						'idProducto' => $product['product_id']
						)
					)
			);
			$post_data = json_encode($data);
			//echo "{$this->end_point}/rest/v1/productos/nuevoFact";
			$crl = curl_init( "{$this->end_point}/rest/v1/productos/nuevoFact");
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'token: ' . $this->token)
			);
		// Ejecuta petición
			$result = curl_exec($crl);
		// Cierra curl sesión
			curl_close($crl);
			return "El producto <b>{$product['name']}</b> fue actualizado exitosamente en {$this->end_point}/rest/v1/productos/nuevoFact";
		}
	}


?>