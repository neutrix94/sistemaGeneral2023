<?php
	if( isset( $_GET['restoration_fl'] ) || isset( $_POST['restoration_fl'] ) ){
		include( '../../../../conexionMysqli.php' );
		$Rest = new Restoration( $link );
		$action = ( isset( $_GET['restoration_fl'] ) ? $_GET['restoration_fl'] : $_POST['restoration_fl'] );
		//die( $action );
		switch ( $action ) {
		//validacion de contrasena y generacion de registros de restauracion
			case 'validateUserPassword' :
				$login = ( isset( $_GET['usuario'] ) ? $_GET['usuario'] : $_POST['usuario'] );
				$password = ( isset( $_GET['clave'] ) ? $_GET['clave'] : $_POST['clave'] );
				$password = md5( $password );
				$store_id = ( isset( $_GET['suc'] ) ? $_GET['suc'] : $_POST['suc'] );
				echo $Rest->validateUserPassword( $login, $password, $store_id );
			break;

			case 'excecute_script' :
				$store_id = ( isset( $_GET['store_id'] ) ? $_GET['store_id'] : $_POST['store_id'] );
				$restoration_id = ( isset( $_GET['restoration_id'] ) ? $_GET['restoration_id'] : $_POST['restoration_id'] );
				$sql_instruction = ( isset( $_GET['sql_instruction'] ) ? $_GET['sql_instruction'] : $_POST['sql_instruction'] );
				echo $Rest->excecute_script( $store_id, $restoration_id, $sql_instruction );
			break;

			case 'delete_triggers':
				echo $Rest->delete_triggers();
			break;

			case 'insert_triggers':
				echo $Rest->insert_triggers();
			break;

/*Implementacion Oscar 2023 para insertar procedures despues de la restauracion 2023/09/19*/
			case 'insert_procedures':
				echo $Rest->insert_procedures();
			break;
/*fin de cambio Oscar 2023/09/19*/

			case 'set_apis_paths':
			//die( 'here' );
				$api_path = ( isset( $_GET['api_path'] ) ? $_GET['api_path'] : $_POST['api_path'] );
				$versioner_path = ( isset( $_GET['versioner_path'] ) ? $_GET['versioner_path'] : $_POST['versioner_path'] );
				echo $Rest->set_apis_paths( $api_path, $versioner_path );
			break;

			default :
				die( "Permission denied on {$action}" );
			break;
		}
	}

	/*include( '../../../../conexionMysqli.php' );
	$Rest = new Restoration( $link );
	$test = $Rest->getCurrentRestore();
	foreach ($test as $key => $value) {
		var_dump( $value );
		echo "<br><br>";
	}*/
	class Restoration {
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function set_apis_paths( $api_path, $versioner_path ){
			$sql = "UPDATE api_config SET value = '{$api_path}' WHERE name = 'path' AND `key` = 'api'";
			$stm = $this->link->query( $sql ) or die( "Error al resetear el path del api : {$this->link->error}" );
			$sql = "UPDATE versionador_configuracion SET url_api = '{$versioner_path}' WHERE 1";
			$stm = $this->link->query( $sql ) or die( "Error al resetear el path del versionador : {$this->link->error}" );
			die( 'ok' );
		}

		public function insert_triggers(){
			include( '../../herramientas/mantenimiento_sistema/mysqlDDL.php' );
			$mysqlDDL = new mysqlDDL( $this->link );
			$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggersInventarios/' );
			if( $enabled_triggers != 'ok' ){
				die( "Ocurrio un problema al reinsertar los triggers de inventario en la base de datos : {$enabled_triggers}" );
			}
			$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggers_sistema/' );
			if( $enabled_triggers != 'ok' ){
				die( "Ocurrio un problema al reinsertar los triggers del sistema en la base de datos : {$enabled_triggers}" );
			}
			$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggers_transferencias/' );
			if( $enabled_triggers != 'ok' ){
				die( "Ocurrio un problema al reinsertar los triggers de transferencias en la base de datos : {$enabled_triggers}" );
			}
			return 'ok|triggers insertados exitosamente!';

		}
/*Implementacion Oscar 2023 para insertar procedures despues de la restauracion 2023/09/19*/
		public function insert_procedures(){
			include( '../../herramientas/mantenimiento_sistema/mysqlDDL.php' );
			$mysqlDDL = new mysqlDDL( $this->link );
			$enabled_procedures = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/storedProcedures/' );
			if( $enabled_procedures != 'ok' ){
				die( "Ocurrio un problema al reinsertar los storedProcedures en la base de datos : {$enabled_procedures}" );
			}
			return 'ok|storedProcedures insertados exitosamente!';

		}
/*fin de cambio Oscar 2023/09/19*/
		public function delete_triggers(){
			$sql = "SHOW TRIGGERS";
			$stm = $this->link->query( $sql ) or die( "Error al listar triggers : {$this->link->error}" );
			//$link->autocommit( false );
			$c = 0;
			while ( $row = $stm->fetch_assoc() ) {
				$sql = "DROP TRIGGER IF EXISTS {$row['Trigger']}";
				$stm_del = $this->link->query( $sql ) or die( "Error al eliminar triggers : {$this->link->error}" );
				$c++;
			}
			return "ok|Fueron eliminados {$c} triggers";
		}

		public function excecute_script( $store_id, $restoration_id, $sql_instruction ){
		//recupera instruccion
			$restoration = $this->getCurrentRestore( $store_id, $restoration_id );
		//	var_dump( $restoration );
		//die( 'here' . $restoration[0]['sql_code'] );
			//echo $restoration[0]['sql_code'];
			if( $restoration[0]['is_in_local'] == 1 ){
				$excecute = $this->excecuteQuery( $restoration[0]['sql_code'] );
				if( $excecute != 'ok' ){
					return $excecute;
				}
			}
			if( $restoration[0]['is_in_line'] == 1 ){
		//consulta el path del api
				$api_path = $this->getApiConfig();
				/*if( $restoration_id == 18 ){
					die( $api_path . " _ " . $restoration[0]['sql_code'] );
				}*/
				$excecute = $this->send_petition( $api_path, $restoration[0]['sql_code'] );
				if( $excecute != 'ok' ){
					return $excecute;
				}
			}
			$this->update_current_restoration( $restoration_id );
			return 'ok';
		}

		public function update_current_restoration( $restoration_id, $system_type ){
			$sql = "UPDATE sys_restauracion_actual 
						SET realizado_local = 1,
						realizado_linea = 1 
					WHERE id_restauracion_actual = {$restoration_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar status de restauracion : {$this->link->error}" );
			return 'ok';
		}

	//verifica el password del usuario
		public function validateUserPassword( $login, $password, $store_id ){
		//verifica permisos y password del usuario
			$sql="SELECT 
					id_usuario 
				FROM sys_users 
				WHERE login = '{$login}' 
				AND contrasena ='{$password}' 
				AND ( tipo_perfil = 1 OR tipo_perfil = 5 )";
			$eje = $this->link->query( $sql ) or die("Error al verificar si el usuario tiene los permisos para restaurar o generar una nueva BD!!! {$sql} : {$link->error}");
			if( $eje->num_rows > 0 ){
			//genera los registros de restauracion
				return $this->setQueries( $store_id );
				//die('ok|');
			}else{
				die("error|El usuario y/o contraseña son Incorrectos o el usuario no tiene los permisos para restaurar la BD, verifique sus datos y vuelva a intentar!!!");
			}
		}
	//almacena los registros de la nueva restauracion
		public function setQueries( $store_id ){
		//verifica que no haya habido una restauracion anteriormente
			$sql = "SELECT id_restauracion_actual FROM sys_restauracion_actual";
			$stm = $this->link->query( $sql ) or die ( "Error al consultar si hubo una restauracion previa : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				//die( "warning|Ya hay registros previos de una restauracion, verifica y vuelve a intentar!" );
			}else{
				$sql = "INSERT INTO sys_restauracion_actual ( id_restauracion_actual, id_modulo_restauracion, 
					fecha_alta, fecha_inicio_local, fecha_fin_local )
					SELECT
						NULL,
						id_modulo_restauracion,
						NOW(),
						'',
						''
					FROM sys_restauracion_modulos";
				$stm = $this->link->query( $sql ) or die ( "Error al insertar registros de restauracion actual : {$this->link->error}" );
			}
			return "ok|" . json_encode( $this->getCurrentRestore( $store_id ) );
		}
//recupera los registros de la restauracion
		public function getCurrentRestore( $store_id, $current_restoration = null ){
		//recupera datos de la restauracion
			$sql = "SELECT
						fecha, 
						hora,
						( SELECT prefijo FROM sys_sucursales WHERE id_sucursal = {$store_id} ) AS prefijo
					FROM sys_respaldos
					ORDER BY id_respaldo DESC";
			$stm = $this->link->query( $sql ) or die ( "Error al consultar datos de restauracion actual : {$this->link->error}" );
			$initial_data = $stm->fetch_assoc();
			$fecha_rsp = "{$initial_data['fecha']} {$initial_data['hora']}";
			$resp = array();
			$sql = "SELECT
						ra.id_restauracion_actual AS current_restoration_id,
						rm.descripcion AS description,
						rm.instruccion_sql AS sql_code,
						rm.local AS is_in_local,
						rm.linea AS is_in_line,
						ra.fecha_inicio_local AS initial_date,
						ra.fecha_fin_local AS final_date,
						ra.realizado_local AS was_finished
					FROM sys_restauracion_modulos rm
					LEFT JOIN sys_restauracion_actual ra
					ON ra.id_modulo_restauracion = rm.id_modulo_restauracion";
			$sql .= ( $current_restoration == null ? "" : " WHERE ra.id_restauracion_actual = {$current_restoration}" );
			$sql .= " ORDER BY rm.orden ASC";
			$stm = $this->link->query( $sql ) or die ( "Error al consultar registro(s) de restauracion actual : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
			//remplazamientos de variables
				$row['sql_code'] = str_replace('{$id_suc}', $store_id, $row['sql_code'] );
				$row['sql_code'] = str_replace('{$store_prefix}', $initial_data['prefijo'], $row['sql_code'] );
				$row['sql_code'] = str_replace('{$fecha_rsp}', $fecha_rsp, $row['sql_code'] );
				$resp[] = $row;
			}
			return $resp;
		}

		function getApiConfig(){
			$sql = "SELECT 
	        	TRIM(value) AS path
	        FROM api_config WHERE name = 'path'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
			$config_row = $stm->fetch_assoc();
			$api_path = $config_row['path']."/rest/v1/restauracion";
			return $api_path;
		}
	//envia peticion
		function send_petition( $api_path, $sql ){
			$petition_data = array( "QUERY"=>$sql );
			$post_data = json_encode( $petition_data );
			$resp = "";
			$crl = curl_init( "{$api_path}" );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			//var_dump($resp);
			//$response = json_decode($resp);
			return $resp;
		}
	//ejecuta query
		function excecuteQuery( $sql ){
			$this->link->autocommit( false );
			$stm = $this->link->query( $sql ) or die( "Error al ejecutar consulta desde excecuteQuery : {$sql} : {$this->link->error}" );
			$this->link->autocommit( true );
			return 'ok';
		}
	}
?>