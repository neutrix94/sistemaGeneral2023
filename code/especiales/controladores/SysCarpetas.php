<?php
/**
 * 
 */
	class SysCarpetas
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
	//
		function getFoldersByPath( $store_path ){
			$resp = "";
			$sql = "SELECT id_carpeta, CONCAT( `path`, '/', nombre_carpeta ) AS folder, tipo_carpeta FROM sys_carpetas WHERE `path` = '{$store_path}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar carpetas en base al path : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$disabled = ( $row['tipo_carpeta'] == 'modulo' || $row['tipo_carpeta'] == 'carpeta_generica' ? 'disabled' : '' );
				$resp .= "<tr>
							<td>{$row['folder']}</td>
							<td class=\"text-center\">
								<button 
									class=\"btn btn-danger\"
									onclick=\"eliminar_carpeta( {$row['id_carpeta']})\"
									{$disabled}
								>
									X
								</button>
							</td>
						<tr>";
			}
			return $resp;
		}
	//creacion de folders
		function createFolder( $store_path, $folder_name ){
		//verifica que la sucursal sea linea
			$sql = "SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la sucursal de acceso : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['id_sucursal'] != -1 ){
				//die( "No se pudo crear la carpeta debido a que las carpetas solo pueden ser creadas desde linea!" );
			}
		//verifica que el path no exista
			$this->link->autocommit( false );
			$sql = "SELECT id_carpeta FROM sys_carpetas WHERE CONCAT( `path`, '/', `nombre_carpeta` ) = '{$store_path}/{$folder_name}'";
			$stm = $this->link->query( $sql ) or die( "Error al verificar si existe la carpeta : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				die( "La carpeta ya existe, verfica y vuelve a intentar con otro nombre de carpeta u otra ruta!" );
			}
		//inserta la carpeta
			$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
					VALUES ( NULL, 'subcarpeta', ( SELECT ax.id_sucursal FROM ( SELECT id_sucursal FROM sys_carpetas WHERE `path` = '{$store_path}' OR CONCAT( `path`, '/', `nombre_carpeta` ) = '{$store_path}' LIMIT 1 )ax ), 
					'{$store_path}', '{$folder_name}', NOW() )"; //die($sql);
			$this->link->query( $sql ) or die( "Error al insertar registro de nueva carpeta : {$this->link->error}" );
		//crea la carpeta
			mkdir( "../../../{$store_path}/{$folder_name}" , 0777);
			chmod( "../../../{$store_path}/{$folder_name}" , 0777 );
			$id_carpeta = $this->link->insert_id;
			$sincronizacion = $this->crearRegistrosSincronizacion( 'insert', 'sys_carpetas', $id_carpeta );
			if( $sincronizacion != 'ok' ){
				die( "Error : {$sincronizacion}" );
			}
			$this->link->autocommit( true );
			die( "Carpeta agregada exitosamente!" );
		}
	//obtener folders
		function getStorePathsFolders(){//$store = null, $folder_type = null, $folder_id = null
			$resp = array();
			$sql = "SELECT 
						CONCAT( `path`, '/', `nombre_carpeta` ) AS path_name
					FROM sys_carpetas
					WHERE tipo_carpeta = 'path_sucursal'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar capretas de sucursales : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return $resp;
		}
	//eliminar folder
		function deleteFolder(){

		}

		function obtenerModulos(){
			$resp = array();
		//consulta modulos
			$modules_sql = "SELECT DISTINCT( nombre_carpeta_modulo ) AS folder FROM sys_modulos_impresion";
			$modules_stm =$this->link->query( $modules_sql ) or die( "Error al consultar los modulos : {$this->link->error}" );
			while ( $modules_row = $modules_stm->fetch_assoc() ) {
				$resp[] = $modules_row;
			}
			return json_encode( $resp );
		}
	//creacion de registros de sincronizacion
		function crearRegistrosSincronizacion( $tipo, $tabla, $id_registro ){
			$json = "{
				    \"table_name\" : \"sys_carpetas\",
				    \"action_type\" : \"{$tipo}\",
				    \"primary_key\" : \"id_carpeta\",
				    \"primary_key_value\" : \"{$id_registro}\"";
			if( $tipo == 'insert' || $tipo == 'update' ){
			//consulta datos del registro
				$sql = "SELECT * FROM sys_carpetas WHERE id_carpeta = {$id_registro}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la carpeta para sincronizacion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$json .= ", 
					\"id_carpeta\" : \"{$row['id_carpeta']}\",
					\"tipo_carpeta\" : \"{$row['tipo_carpeta']}\",
					\"id_sucursal\" : \"{$row['id_sucursal']}\",
					\"`path`\" : \"{$row['path']}\",
					\"nombre_carpeta\" : \"{$row['nombre_carpeta']}\",
					\"fecha_creacion\" : \"{$row['fecha_creacion']}\"";
			}
			$json .= "}";
		//inserta el registro de sincronizacion
			$sql = "INSERT INTO sys_sincronizacion_registros( sucursal_de_cambio, id_sucursal_destino, datos_json, tipo, folio_unico_peticion, status_sincronizacion )
					SELECT
						-1, 
						id_sucursal, 
						'{$json}', 
						'SysCarpetas.php', 
						NULL,
						1
					FROM sys_sucursales
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de {$tabla} : {$this->link->error}" );
			return 'ok';
		}
		function eliminarCarpeta( $id ){
			$sql = "SELECT 
						CONCAT( `path`, '/', nombre_carpeta ) AS ruta_carpeta
					FROM sys_carpetas
					WHERE id_carpeta = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar ruta de la carpeta para eliminar : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$archivo_path = "../../../conexion_inicial.txt";
			if(file_exists($archivo_path)){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
			    $config=explode("<>",$line);
			    $tmp=explode("~",$config[2]);
			    $ruta_or=$tmp[0];
			    $ruta_des=$tmp[1];
			}else{
				die("No hay archivo de configuración!!!");
			}
			/*define('DIR_BASE', __DIR__);*/
			//die( $ruta_or );
		//elimina carpeta
			//if( rmdir( "{$ruta_or}{$row['ruta_carpeta']}" ) ){
			if( shell_exec( "sudo rm -R {$ruta_or}{$row['ruta_carpeta']}" ) ){
				$sql = "DELETE FROM sys_carpetas WHERE id_carpeta = {$id}";
				$stm = $this->link->query( $sql ) or die( "Error al eliminar la carpeta de la base de datos : {$this->link->error}" );
			}else{
				die( "Error al eliminar la carpeta del directorio : {$ruta_or}{$row['ruta_carpeta']}" );
			}
		}
	//creacion de todos los folders
		function setAllFolders(){
			$sql = "SELECT id_sucursal, REPLACE( nombre, ' ', '_' ) AS nombre FROM sys_sucursales WHERE id_sucursal > 0";
			$stm = $this->link->query($sql) or die("Eror al consultar sucursales : {$this->link->error}");
			while( $store = $stm->fetch_assoc() ){
				$store_folder = "../../../cache/{$store['nombre']}";
			//consulta modulos
				$modules_sql = "SELECT DISTINCT( nombre_carpeta_modulo ) AS folder FROM sys_modulos_impresion";
				$modules_stm =$this->link->query( $modules_sql ) or die( "Error al consultar los modulos : {$this->link->error}" );
				while ( $modules_row = $modules_stm->fetch_assoc() ) {
					$module_store_folder = "{$store_folder}/{$modules_row['folder']}";
					$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
							VALUES ( NULL, 'modulo', '{$store['id_sucursal']}', 'cache/{$store['nombre']}', '{$modules_row['folder']}', NOW() )";
					$stm_aux = $this->link->query( $sql ) or die( "Error al insertar carpetas de modulos de sucursales en base datos : {$this->link->error}" );
					if ( ! is_dir( $module_store_folder ) ){
						mkdir( $module_store_folder , 0777);
					}
				}
				//if( ! is_dir( $store_folder ) ){
				//inserta registro
					//$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
				//			VALUES ( NULL, 'path_sucursal', '{$store['id_sucursal']}', 'cache', '{$store['nombre']}', NOW() )";
				//	$stm_aux = $link->query( $sql ) or die( "Error al insertar carpetas de sucursales en base datos : {$link->error}" );
					//mkdir( $store_folder , 0777);
					//is_file("{$store_folder}/index.html");//proteccion de acceso a archivos
				//}
				//$store_folder_tickets = "{$store_folder}/ticket";

				//$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
				//			VALUES ( NULL, 'ticket_sucursal', '{$store['id_sucursal']}', 'cache/{$store['nombre']}', 'ticket', NOW() )";
				//$stm_aux = $link->query( $sql ) or die( "Error al insertar carpetas de ticket de sucursales en base datos : {$link->error}" );
				/*if( ! is_dir( $store_folder_tickets ) ){
					mkdir( $store_folder_tickets , 0777);
					//is_file("{$store_folder_tickets}/index.html");//proteccion de acceso a archivos
					//is_file('')//proteccion de acceso a archivos
				}*/
			}

			//die( 'here' );
		}	
	/*obtener folders
		function getFolders( $store = null, $folder_type = null, $folder_id = null ){
			$sql = "";
		}
	//obtener setFolder
		function insertFolder(){
		}
	//actualizar
		function updateFolder(){
		}
	//eliminar
		function deleteFolder(){

		}*/
	}
?>