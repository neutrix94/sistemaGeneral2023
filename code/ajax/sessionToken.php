<?php
	include( '../../conect.php' );
	include( '../../conexionMysqli.php' );

	if( isset( $_GET['session_flag'] ) || isset( $_POST['session_flag'] ) ) {
		$action = ( isset( $_GET['session_flag'] ) ? $_GET['session_flag'] : $_POST['session_flag'] );
		$deviceSession = new deviceSession( $link, $sucursal_id, $user_id );
		switch ( $action ) {
			case 'createToken':
				echo $deviceSession->createDeviceToken();
			break;
			case 'validateChanges':
				echo $deviceSession->validateDeviceChanges( $_GET['token'] );
			break;
				
			case 'getProductsCatalogue' :
				echo $deviceSession->getProductsCatalogue( $_GET['token'], $_GET['list_id'] );
			break;

			default:
				die( "Permission Denied on {$action}!" );
			break;
		}
	}

	/**
	* 
	*/
	class deviceSession
	{
		private $link;
		private $store_id;
		private $user_id;
		function __construct( $link, $store_id, $user_id )
		{
			$this->link = $link;
			$this->store_id = $store_id;
			$this->user_id = $user_id;
		}

		function validateDeviceChanges( $token ){
		//valida que el token del dispoitivivo este activo
			$sql = "SELECT 
						hay_cambios AS has_changed,
						finalizada AS has_finished
					FROM sys_sesiones_dispositivos
					WHERE token_unico = '{$token}'
					AND id_sucursal = '{$this->store_id}'
					AND id_usuario = {$this->user_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el status del token de dispositivo : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return 'invalid_token';
			}else{
				$row = $stm->fetch_assoc();
				if( $row['has_finished'] == 1 ){
					return 'invalid_token';
				}else if( $row['has_changed'] == 1 ){
					return 'ok|has_changed';
				}
				return 'ok';
			}
		}


		function createDeviceToken(){
			$ip_address = $_SERVER['REMOTE_ADDR'];
			
			$this->link->autocommit( false );
			$sql = "INSERT INTO sys_sesiones_dispositivos ( id_sesion_dispositivo, id_usuario, id_sucursal, token_unico, 
				fecha_alta, hay_cambios, finalizada, direccion_ip )
				VALUES( NULL, {$this->user_id}, {$this->store_id}, '', NOW(), '0', '0', '{$ip_address}' )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar el registro de sesion de recepcion : {$this->link->error} {$sql}" );
			$session_id = $this->link->insert_id;
		//generacion de token
			$sql = "SELECT 
						CONCAT( 'SESION_', 
							DATE_FORMAT( fecha_alta, '%Y%m%d' ), '_',
							DATE_FORMAT( fecha_alta, '%H%i%s' ), '_',
							id_usuario, '_',
							id_sesion_dispositivo
						) AS unic_token
					FROM sys_sesiones_dispositivos
					WHERE id_sesion_dispositivo = {$session_id}";
			$stm = $this->link->query( $sql ) or die( "Error al general el token de sesión del dispositivo : {$this->link->error}" );		
			$row = $stm->fetch_assoc();
			$unic_token = $row['unic_token'];
		//actualiza el token en la sesion
			$sql = "UPDATE sys_sesiones_dispositivos 
				SET token_unico = '{$unic_token}'
				WHERE id_sesion_dispositivo = {$session_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el token de la sesión del dispositivo : {$this->link->error}" );

			$this->link->autocommit( true );
			return "ok|{$unic_token}";
		}

		function getProductsCatalogue( $token, $price_list = null ){
			$resp = array();
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
			$sql = "SELECT
						p.id_productos AS product_id,
						p.orden_lista AS list_order,
						p.nombre AS product_name,
						GROUP_CONCAT( pp.codigo_barras_pieza_1 SEPARATOR ' __ ' ) AS codigo_barras_pieza_1, 
						GROUP_CONCAT( pp.codigo_barras_pieza_2 SEPARATOR ' __ ' ) AS codigo_barras_pieza_2,
						GROUP_CONCAT( pp.codigo_barras_pieza_3 SEPARATOR ' __ ' ) AS codigo_barras_pieza_3,
						GROUP_CONCAT( pp.codigo_barras_presentacion_cluces_1 SEPARATOR ' __ ' ) AS codigo_barras_presentacion_cluces_1,
						GROUP_CONCAT( pp.codigo_barras_presentacion_cluces_2 SEPARATOR ' __ ' ) AS codigo_barras_presentacion_cluces_2,
						GROUP_CONCAT( pp.codigo_barras_caja_1 SEPARATOR ' __ ' ) AS codigo_barras_caja_1,
						GROUP_CONCAT( pp.codigo_barras_caja_2 SEPARATOR ' __ ' ) AS codigo_barras_caja_2,
						IF(	pd.id_precio_detalle IS NULL,
							'<span __CLASS__>Sin Precio</span>',
							GROUP_CONCAT(	
								DISTINCT( CONCAT( '<span __CLASS__>', pd.de_valor, ' x $ ', ROUND( pd.precio_venta * pd.de_valor ), '</span>' )  ) ORDER BY pd.de_valor ASC 
								SEPARATOR ' l ' 
							)
						) AS product_prices
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = p.id_productos
					LEFT JOIN sys_sucursales_producto sp
					ON sp.id_producto = p.id_productos
					AND sp.id_sucursal = '{$this->store_id}'
					LEFT JOIN ec_precios_detalle pd
					ON pd.id_producto = p.id_productos
					AND pd.id_precio = '{$price_id}'
					WHERE p.id_productos > 0
					AND p.nombre NOT IN( 'Libre', 'ERROR ESTACIONALIDAD X2', 'ERROR ESTACIONALIDA X2', 'Error', 'Error ', 'Producto De Ajuste' )
					AND p.id_categoria !=1
					AND sp.id_sucursal = '{$this->store_id}'
					AND sp.estado_suc = '1'
					GROUP BY p.id_productos
					ORDER BY p.orden_lista";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row);
			}
		//actualiza la sesion a que no hay cambios
			$sql = "UPDATE sys_sesiones_dispositivos SET hay_cambios = '0' WHERE token_unico = '{$token}'";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar la sesion a que no hay cambios" );
			return "ok|" . json_encode( $resp );
		}
	}
?>