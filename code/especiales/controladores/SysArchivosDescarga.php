<?php
	if( isset( $_GET['fl_archivo_descarga'] ) ||  isset( $_POST['fl_archivo_descarga'] ) ){
		$action = ( isset( $_GET['fl_archivo_descarga'] ) ? $_GET['fl_archivo_descarga'] : $_POST['fl_archivo_descarga'] ); 
		include( '../../../conect.php' );
		include( '../../../conexionMysqli.php' );
		$SysArchivosDescarga = new SysArchivosDescarga( $link );
		switch ($action) {
			case 'sendSpecificFile' :
				$carpeta_path = ( isset( $_GET['carpeta'] ) ? $_GET['carpeta'] : $_POST['carpeta'] );
				$url = "http://localhost/{$carpeta_path}/rest/print/enviar_archivo_red_local";
				$id = ( isset( $_GET['file_id'] ) ? $_GET['file_id'] : $_POST['file_id'] );
				$id_modulo = ( isset( $_GET['module_id'] ) ? $_GET['module_id'] : $_POST['module_id'] );
				$post_data = json_encode( array( "id_archivo"=>"{$id}",
												"id_sucursal"=>"{$user_sucursal}",
												"id_usuario"=>"{$user_id}",
												"id_modulo_impresion"=>"{$id_modulo}"
											) 
										);
				//var_dump( $post_data );
				$enviar_archivo = $SysArchivosDescarga->sendPetition( $url, $post_data );
				if( $enviar_archivo != "ok" ){
					die( "Error al consumir el WebService en Red Local : {$enviar_archivo}|{$id}" );
				}//die( "here_2" );
				die( 'ok' );
				//die( "sendSpecificFile Case" );
			break;

			case 'resend_petiton_file_view' : 
				echo $SysArchivosDescarga->resend_petiton_file_view();
			break;

			case 'move_file_to_generic_folder' : 
				$file_id = ( isset( $_GET['file_id'] ) ? $_GET['file_id'] : $_POST['file_id'] );
				$path = ( isset( $_GET['path'] ) ? $_GET['path'] : $_POST['path'] );
				$id_modulo = ( isset( $_GET['module_id'] ) ? $_GET['module_id'] : $_POST['module_id'] );
				echo $SysArchivosDescarga->move_file_to_generic_folder( $file_id, $path, $id_modulo, $user_sucursal );
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

		function crea_registros_sincronizacion_archivo_por_red_local( $id_modulo, $tipo, $nombre_ticket, $ruta_origen, $ruta_salida, $store_id, $user_id, $carpeta_path, 
			$path = '../', $action_after = '' ){
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
			if( $conteo <= 0 || $row['endpoint_api_destino_local'] == '' ){
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
				$url = "http://localhost/{$carpeta_path}/rest/print/enviar_archivo_red_local";
				$enviar_archivo = $this->sendPetition( $url, $post_data );
				if( $enviar_archivo != "ok" ){
					die( $this->build_error_view( $id, $store_id, $id_modulo, $path, $action_after, $carpeta_path ) );
					//die( "Error al consumir el WebService en Red Local : {$enviar_archivo}|{$id}" );
				}//die( "here_2" );
				//die( "No hay APIS destino para este modulo. {$sql}" );
			}else{
				return 'ok';
			}
			
		}
		
		function build_error_view( $file_id, $store_id, $id_modulo, $path, $action_after, $carpeta_path ){
			$resp = "<div class=\"row\">
				<div class=\"col-12 text-center\" style=\"font-size : 200% !important;\"><br>
					<h2 class=\"text-danger\">Hubo un error al enviar el archivo por WebService, deseas volver a intentar?</h2>
				</div>
				<div class=\"col-6 text-end\"><br>
					<button 
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"reenviar_archivo_ws( path = '../', {$file_id}, {$id_modulo} );\"
					>
						Volver a intentar
					</button>
				</div>
				<div class=\"col-6 text-start\"><br>
					<button 
						type=\"button\"
						class=\"btn btn-warning\"
						onclick=\"enviar_archivo_carpeta_modulo( path = '../', {$file_id}, {$id_modulo} );\"
					>
						Enviar a carpeta de modulo
					</button>
				</div>
			</div>
			<script type=\"text/javascript\">
				function reenviar_archivo_ws( file_id ){
					var url = \"{$path}code/especiales/controladores/SysArchivosDescarga.php?fl_archivo_descarga=sendSpecificFile&file_id=\" + {$file_id} + \"&module_id={$id_modulo}\";
					url += \"&carpeta={$carpeta_path}\";
					//var resp = ajaxR( url );
					//alert( resp );
					$.ajax({
						type:'post',
						url: url,
						cache:false,
						success:function(dat){
							if( dat == 'ok' ){
								{$action_after}
							}else{
								alert( \"Error : \" + dat );
							}
							//alert( dat );
						}
					});
				}
				function enviar_archivo_carpeta_modulo( file_id ){
					var url = \"{$path}code/especiales/controladores/SysArchivosDescarga.php?fl_archivo_descarga=move_file_to_generic_folder&file_id=\" + {$file_id} + \"&module_id={$id_modulo}\";
					url += \"&carpeta={$carpeta_path}\";
					$.ajax({
						type:'post',
						url: url,
						cache:false,
						success:function(dat){
							if( dat == 'ok' ){
								{$action_after}
							}else{
								alert( \"Error : \" + dat );
							}
							//alert( dat );
						}
					});
				}
			</script>";
			return $resp;
		}

		function move_file_to_generic_folder( $file_id, $path, $id_modulo, $store_id ){
		//consulta los datos del archivo
			$sql = "SELECT 
						nombre_archivo,
						ruta_origen
					FROM sys_archivos_descarga
					WHERE id_archivo = {$file_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar informacion del archivo en move_file_to_generic_folder : {$this->link->error}" );
			$file_row = $stm->fetch_assoc();
			$origin_file_path = "../../..{$file_row['ruta_origen']}{$file_row['nombre_archivo']}";//ruta origen
		//consulta la ruta origen en relacion al modulo y sucursal
			$sql = "SELECT 
						CONCAT( c.path, '/', c.nombre_carpeta, '/' ) AS carpeta_generica
					FROM sys_modulos_impresion_sucursales mis
					LEFT JOIN sys_carpetas c
					ON mis.id_carpeta = c.id_carpeta
					WHERE mis.id_modulo_impresion = {$id_modulo}
					AND mis.id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la carpeta generica del módulo : {$sql} {$this->link->error}" );
			$destinity_row = $stm->fetch_assoc();

			$destinity_file_path = "../../../{$destinity_row['carpeta_generica']}{$file_row['nombre_archivo']}";
			
			$moved = false;
			if(is_file($origin_file_path)){
				$moved = rename($origin_file_path, $destinity_file_path);
			}
			if($moved){
				return 'ok';
			}else{
				die( "Error al mover el archivo de {$origin_file_path} a {$destinity_file_path}" );
			}
		}

		function resend_petiton_file_view( $file_id, $path ){

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