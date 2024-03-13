<?php
	if( isset( $_GET['fl_archivo_descarga'] ) ||  isset( $_POST['fl_archivo_descarga'] ) ){
		$action = ( isset( $_GET['fl_archivo_descarga'] ) ? $_GET['fl_archivo_descarga'] : $_POST['fl_archivo_descarga'] ); 
		include( '../../../conect.php' );
		include( '../../../conexionMysqli.php' );
		$SysArchivosDescarga = new SysArchivosDescarga( $link );
		switch ($action) {
			case 'sendSpecificFile' :
				$url = "http://localhost/desarrollo_cobros_e_impresion/rest/print/enviar_archivo_red_local";
				$id = ( isset( $_GET['file_id'] ) ? $_GET['file_id'] : $_POST['file_id'] );
				$id_modulo = ( isset( $_GET['module_id'] ) ? $_GET['module_id'] : $_POST['module_id'] );
				$post_data = json_encode( array( "id_archivo"=>"{$id}",
												"id_sucursal"=>"{$user_sucursal}",
												"id_usuario"=>"{$user_id}",
												"id_modulo_impresion"=>"{$id_modulo}"
											) 
										);
										var_dump( $post_data );
				$enviar_archivo = $SysArchivosDescarga->sendPetition( $url, $post_data );
				if( $enviar_archivo != "ok" ){
					die( "Error al consumir el WebService en Red Local : {$enviar_archivo}|{$id}" );
				}//die( "here_2" );
				die( "sendSpecificFile Case" );
			break;
			
			default:
				die( "Permission denied on '{$action}'!" );
			break;
		}
	}
	/**
	 * 
	 */
	class SysArchivosDescarga
	{	
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		function crea_registros_sincronizacion_archivo( $tipo, $nombre_archivo, $ruta_origen, $ruta_salida, $sucursal_destino, $id_usuario ){
			$sql = "SELECT 
				dominio_sucursal AS store_dns
			FROM ec_configuracion_sucursal
			WHERE id_sucursal = ( SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1 LIMIT 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el dominio de la sucursal destino" );
			$row = $stm->fetch_assoc( $stm );
			$ruta_or = $row['store_dns'];
			$origen = "{$ruta_origen}/{$ruta_salida}/";
			$origen = str_replace('//', '/', $origen);
			$sql = "INSERT INTO sys_archivos_descarga SET 
					id_archivo = null,
					tipo_archivo = '{$tipo}',
					nombre_archivo = '{$nombre_archivo}',
					ruta_origen = '{$origen}',
					ruta_destino = '{$ruta_salida}/',
					id_sucursal = '$sucursal_destino',
					id_usuario = '$id_usuario',
					observaciones = 'insertado desde SysArchivosDescarga.php'";
			$inserta_reg_arch = $this->link->query( $sql )or die( "Error al guardar el registro de sincronización del ticket de reimpresión : {$this->link->error} : {$sql}" );
			return $this->link->insert_id;
		}

		function crea_registros_sincronizacion_archivo_por_red_local( $id_modulo, $tipo, $nombre_ticket, $ruta_origen, $ruta_salida, $store_id, $user_id ){
			
		//consulta si tiene endpoint especifico local por usuario
			$url_base = "";
			$sql = "SELECT
						miu.endpoint_api_destino_local
					FROM sys_modulos_impresion_usuarios miu
					WHERE miu.id_modulo_impresion = {$id_modulo}
					AND miu.id_usuario = {$user_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el endpoint por usuario : {$sql}" );
			$conteo = $stm->num_rows;
			$row = $stm->fetch_assoc();
			if( $conteo <= 0 || $row['endpoint_api_destino_local'] = '' ){
		//consulta si tiene endpoint especifico local por sucursal
				$sql = "SELECT
					mis.endpoint_api_destino_local
				FROM sys_modulos_impresion_sucursales mis
				WHERE mis.id_modulo_impresion = {$id_modulo}
				AND mis.id_sucursal = {$store_id}";//die($sql);
				$stm = $this->link->query( $sql ) or die( "Error al consultar el endpoint por sucursal : {$sql}" );
				$row = $stm->fetch_assoc();
				$url_base = $row['endpoint_api_destino_local'];
			}else{
				$url_base = $row['endpoint_api_destino_local'];
			}
			if( $url_base != "" || $url_base != null ){
			//crea el registro de sincronizacion
				$id = $this->crea_registros_sincronizacion_archivo( $tipo, $nombre_ticket, '', $ruta_salida, $store_id, $user_id );
			// 
				$post_data = json_encode( array( "id_archivo"=>"{$id}",
												"id_sucursal"=>"{$store_id}",
												"id_usuario"=>"{$user_id}",
												"id_modulo_impresion"=>"{$id_modulo}"
											) 
										);
										//die( $post_data );
				$url = "http://localhost/desarrollo_cobros_e_impresion/rest/print/enviar_archivo_red_local";
				$enviar_archivo = $this->sendPetition( $url, $post_data );
				if( $enviar_archivo != "ok" ){
					die( "Error al consumir el WebService en Red Local : {$enviar_archivo}|{$id}" );
				}//die( "here_2" );
				//die( "No hay APIS destino para este modulo. {$sql}" );
			}else{
				return 'ok';
			}
			
		}

		function sendPetition( $url, $json ){
			
			$resp = "";
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $json);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			return $resp;
			
		}
	}
?>