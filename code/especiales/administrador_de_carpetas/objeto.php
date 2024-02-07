<?
class Carpetas
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
	//
		function getFoldersByPath( $store_path ){
			$resp = "";
			$sql = "SELECT id_carpeta, CONCAT( `path`, '/', nombre_carpeta ) AS folder FROM sys_carpetas WHERE `path` = '{$store_path}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar carpetas en base al path : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
							<td>{$row['folder']}</td>
						<tr>";
			}
			return $resp;
		}
	//creacion de folders
		function createFolder( $store_path, $folder_name ){
//die( 'here : 1' );
		//verifica que el path no exista
			$sql = "SELECT id_carpeta FROM sys_carpetas WHERE CONCAT( `path`, '/', `nombre_carpeta` ) = '{$store_path}/{$folder_name}'";
			$stm = $this->link->query( $sql ) or die( "Error al verificar si existe la carpeta : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				die( "La carpeta ya existe, verfica y vuelve a intentar con otro nombre de carpeta u otra ruta!" );
			}
		//inserta la carpeta
			$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
					VALUES ( NULL, 'subcarpeta', ( SELECT ax.id_sucursal FROM ( SELECT id_sucursal FROM sys_carpetas WHERE `path` = '{$store_path}' LIMIT 1 )ax ), 
					'{$store_path}', '{$folder_name}', NOW() )"; 
			$this->link->query( $sql ) or die( "Error al insertar registro de nueva carpeta : {$this->link->error}" );
		//crea la carpeta
			mkdir( "../../../{$store_path}/{$folder_name}" , 0777);
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
	//creacion de todos los folders
		function setAllFolders(){
			$sql = "SELECT id_sucursal, nombre FROM sys_sucursales WHERE id_sucursal > 0";
			$stm = $link->query($sql) or die("Eror al consultar sucursales : {$link->error}");
			while( $store = $stm->fetch_assoc() ){
				$store_folder = "../../../cache/{$store['nombre']}";
				//if( ! is_dir( $store_folder ) ){
				//inserta registro
					//$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
				//			VALUES ( NULL, 'path_sucursal', '{$store['id_sucursal']}', 'cache', '{$store['nombre']}', NOW() )";
				//	$stm_aux = $link->query( $sql ) or die( "Error al insertar carpetas de sucursales en base datos : {$link->error}" );
					mkdir( $store_folder , 0777);
					//is_file("{$store_folder}/index.html");//proteccion de acceso a archivos
				//}
				$store_folder_tickets = "{$store_folder}/ticket";

				//$sql = "INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion )
				//			VALUES ( NULL, 'ticket_sucursal', '{$store['id_sucursal']}', 'cache/{$store['nombre']}', 'ticket', NOW() )";
				//$stm_aux = $link->query( $sql ) or die( "Error al insertar carpetas de ticket de sucursales en base datos : {$link->error}" );
				/*if( ! is_dir( $store_folder_tickets ) ){
					mkdir( $store_folder_tickets , 0777);
					//is_file("{$store_folder_tickets}/index.html");//proteccion de acceso a archivos
					//is_file('')//proteccion de acceso a archivos
				}*/
			}
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