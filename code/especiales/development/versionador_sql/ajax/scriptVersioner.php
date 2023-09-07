<?php
	if( isset($_GET['scriptVersioner_fl']) || isset($_POST['scriptVersioner_fl']) ){
		$action = ( isset($_GET['scriptVersioner_fl']) ? $_GET['scriptVersioner_fl'] : $_POST['scriptVersioner_fl'] );
		include( '../../../../../conexionMysqli.php' );
		$sV = new scriptVersioner( $link );
		switch ( $action ) {
			case 'setScript':
				$code = ( isset($_GET['code']) ? $_GET['code'] : $_POST['code'] );
				$comment = ( isset($_GET['comment']) ? $_GET['comment'] : $_POST['comment'] );
				$branch_id = ( isset($_GET['branch_id']) ? $_GET['branch_id'] : $_POST['branch_id'] );
				$branch_name = ( isset($_GET['branch_name']) ? $_GET['branch_name'] : $_POST['branch_name'] );
				$execute_script = ( isset($_GET['execute_script']) ? $_GET['execute_script'] : $_POST['execute_script'] );
				echo json_encode( $sV->setScript( $branch_id, $branch_name, $code, $comment, $execute_script ) );
			break;

			case 'getScriptList' :
				$branch_id = ( isset($_GET['branch_id']) ? $_GET['branch_id'] : $_POST['branch_id'] );
				$branch_name = ( isset($_GET['branch_name']) ? $_GET['branch_name'] : $_POST['branch_name'] );
				//$script_status = ( isset($_GET['script_status']) ? $_GET['script_status'] : $_POST['script_status'] );
				echo json_encode( $sV->getScriptList( $branch_id, $branch_name ) );//, $script_status
			//die( 'here' );
			break;

			case 'getScript' : 
				$script_id = ( isset($_GET['script_id']) ? $_GET['script_id'] : $_POST['script_id'] );
				$branch_name = ( isset($_GET['branch_name']) ? $_GET['branch_name'] : $_POST['branch_name'] );
				echo json_encode( $sV->getScript( $script_id, $branch_name ) );
			break;

			case 'updateScript' : 
				$code = ( isset($_GET['code']) ? $_GET['code'] : $_POST['code'] );
				$comment = ( isset($_GET['comment']) ? $_GET['comment'] : $_POST['comment'] );
				$script_id = ( isset($_GET['script_id']) ? $_GET['script_id'] : $_POST['script_id'] );
				$branch_name = ( isset($_GET['branch_name']) ? $_GET['branch_name'] : $_POST['branch_name'] );
				echo $sV->updateScript( $code, $comment, $script_id, $branch_name );
			break ;
			
			default:
				die( "Permission denied ON '{$action}'!" );
			break;
		}
	}

	class scriptVersioner
	{
		private $link;
		function __construct( $connection ) 
		{
			$this->link = $connection;
		}
/*configuracion de versionador*/
		public function getVersionerConfig(){
			$sql = "SELECT
						/*( SELECT TRIM(value) FROM api_config WHERE name = 'path' ) AS server_path,*/
						vc.id_versionador_configuracion AS versioner_configuration_id,
						vc.id_rama_versionador AS current_branch_id,
						vc.url_api AS server_path,
						vc.url_servidor_mysql AS mysql_server,
						vc.usuario_mysql AS mysql_user,
						vc.nombre_base_datos_mysql AS mysql_database,
						vc.contrasena_mysql AS mysql_password,
						vc.es_servidor AS is_server,
						LOWER( vr.nombre ) AS branch_name
					FROM versionador_configuracion vc
					LEFT JOIN versionador_ramas vr
					ON vr.id_rama = vc.id_rama_versionador";
			$stm = $this->link->query( $sql ) or die( "Error al consultar configuracion actual del versionador : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
		//consulta el ultimo script en origen
			$sql = "SELECT MAX( id_script_{$row['branch_name']} ) AS last_script_id FROM versionador_scripts_{$row['branch_name']}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el ultimo script : {$this->link->error}" );
			$aux = $stm->fetch_assoc();
			$row['last_script_id'] = $aux['last_script_id'];
			return $row;
		}

//Scripts
		public function setScript( $branch_id, $branch_name, $code, $comment, $execute_script ){
		//genera el folio unico
			$resp = "";
			$unique_folio = $this->setUniqueFolio( $branch_name );
			/*$code = str_replace( "'", "\\\"", $code );//"\'"
			$code = str_replace( "\\\\'", "\\\"", $code );//"\\'"*/
			$code = str_replace("\'", "\\\'", $code );
			$code = str_replace("'", "\'", $code );

			$sql = "INSERT INTO versionador_scripts_{$branch_name} ( codigo, descripcion, rama_creacion, folio_unico, fecha_alta )
			VALUES( '{$code}', '{$comment}', {$branch_id}, '{$unique_folio}', NOW() )";
			//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al insertar versionamiento de script : {$this->link->error} {$sql}" );
			$script_id = $this->link->insert_id;
		/*inserta detalle
			$sql = "INSERT INTO versionador_ramas_scripts ( id_rama_script, id_rama, id_script, id_status_script,
					resultado, fecha_alta )
					VALUES( NULL, {$branch_id}, {$script_id}, 1, '', NOW() )";*/
			//$stm = $this->link->query( $sql ) or die( "Error al insertar versionamiento de script a la rama : {$this->link->error} {$sql}" );
			//$script_id = $this->link->insert_id;
			if( $execute_script == 1 ){
				//die( "here" );
				$script = json_decode( json_encode( $this->getScript( $script_id, $branch_name ) ) );
				$script->branch_id = $branch_id;
				$aux = $this->executeScript( $branch_id, $branch_name, $script, 'FRONT' );
				$aux = explode( "|*|" , $aux );
				if( $aux[0] != 'ok' ){
					die( "Error al ejecutar el Script : {$aux[0]} {$aux[1]} {$aux[2]}" );
				}
				$resp = " y ejecutados";
			}
			return "Script(s) guardados {$resp} exitosamente!";
			return $this->getScript( $script_id, $branch_name );	
		}

		public function updateScript( $code, $comment, $script_id, $branch_name ){
			$code = str_replace("\'", "\\\'", $code );
			$code = str_replace("'", "\'", $code );
			$sql = "UPDATE versionador_scripts_{$branch_name} 
						SET codigo = '{$code}',
						descripcion = '{$comment}'
					WHERE id_script_{$branch_name} = {$script_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar Script {$branch_name} : {$this->link->error} {$sql} " );
			return "ok|Script Actualizado";
		}	

		public function getScriptList( $branch_id, $branch_name){//, $status 
			$resp = array();
			$sql = "SELECT 
						vs.id_script_{$branch_name} AS script_id, 
						vs.codigo AS code, 
						vs.descripcion AS description, 
						vs.rama_creacion AS creation_branch, 
						vs.folio_unico AS unique_folio, 
						vs.fecha_alta AS creation_date, 
						vs.fecha_ejecucion AS execution_date
					FROM versionador_scripts_{$branch_name} vs
					ORDER BY vs.id_script_{$branch_name} DESC";
			$stm = $this->link->query( $sql ) or die( "Error al consultar scripts : {$sql} {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}			
			return $resp;	
		}

		public function getScript( $script_id, $branch_name ){
			$sql = "SELECT 
						id_script_{$branch_name} AS script_id, 
						codigo AS code, 
						descripcion AS description, 
						rama_creacion AS creation_branch, 
						folio_unico AS unique_folio, 
						fecha_alta AS add_date, 
						fecha_ejecucion AS excecution_date
					FROM versionador_scripts_{$branch_name}
					WHERE id_script_{$branch_name} IN( {$script_id} )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar script de versionamiento : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
//api Scripts
		public function getBranchPendingScripts( $branch_name, $last_script_id ){
			$resp = array();
			$sql = "SELECT
						vs.id_script_{$branch_name} AS script_id,
						vs.codigo AS code,
						vs.descripcion AS description,
						vs.rama_creacion AS creation_branch,
						vs.folio_unico AS unique_folio,
						vs.fecha_alta AS add_date
					FROM versionador_scripts_{$branch_name} vs
					WHERE vs.id_script_{$branch_name} > {$last_script_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar scripts pendientes de descargar : 
				{$sql} {$this->link->error}" );
			
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
			return $resp;
		}
		public function updateDatabase( $pending_scripts, $branch_name, $branch_id ){
			$resp = array();
			$resp["updated"] = array();
			$resp["error"] = array();
			foreach ( $pending_scripts as $key => $script ) {
				/*var_dump( $script );
				die( '' );*/
				//$script->code = str_replace("\'", "\\\'", $script->code );
				$script->code = str_replace( "'", "\'", $script->code );
					//$script->code = str_replace("\\\\", "\\", $script->code );				
				$script->code = str_replace("\'", "\\\'", $script->code );

			$script->code = str_replace("\\\\", "\\", $script->code );
			$script->code = str_replace("\\\\'", "\\\\\\'", $script->code );
				//die( $script->code );
			//inserta el script 
				$sql = "INSERT INTO versionador_scripts_{$branch_name} ( id_script_{$branch_name}, codigo, descripcion, rama_creacion, 
					folio_unico, fecha_alta ) VALUES ( {$script->script_id}, '{$script->code}', '{$script->description}', 
				'{$script->creation_branch}', '{$script->unique_folio}', '{$script->add_date}' )";
				
				//$sql = str_replace( "'", "\'", $sql );
			//$sql = str_replace("\\\\", "\\", $sql );
			//$sql = str_replace("\\\\'", "\\\\\\'", $sql );
				//die( $sql );

				$stm = $this->link->query( $sql ) or die( "Error al insertar Script : {$sql} {$this->link->error}" );
				
			//inserta la rama en el script
				$sql = "INSERT INTO versionador_ramas_scripts (id_rama_script, id_rama, id_script, id_status_script, 
					resultado, fecha_alta ) VALUES ( NULL, {$branch_id}, '{$script->script_id}', 
					1, '', '{$script->add_date}' )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar Script en rama : {$sql} {$this->link->error}" );
				$update = explode('|*|', $this->executeScript( $branch_id, $branch_name, $script, 'API' ) );
				if( $update[0] == 'ok' ) {
					array_push( $resp["updated"], $update[1] );
				}else{
					array_push( $resp["error"], $update[1] );
				}
			}
			return $resp;
		}
		public function executeScript( $branch_id, $branch_name, $script, $type ){//$script
			//var_dump( $script );
			$resp = "ok|*|{$script->script_id}|*|Script(s) ejecutados exitosamente : ";
			//$script->code = str_replace( "\\", "", $script->code );
			if( $type == 'API' ){
				$script->code = str_replace("\'", "'", $script->code );
				$script->code = str_replace("\\\'", "\'", $script->code );
			}else if( $type == 'FRONT' ){

			}
			//$script->code = str_replace("'", "\'", $script->code );
			$sql = $script->code;//"UPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\r\nI8,A,001\r\n\r\n\r\nQ408,024\r\nq448\r\nrN\r\nS2\r\nD10\r\nZT\r\nJF\r\nO\r\nR112,0\r\nf100\r\nN\r\nB570,165,2,1,2,6,120,N,\"$_barcode\"\r\nA197,380,2,4,2,3,N,\"$_order_list\"\r\nA586,380,2,4,2,3,N,\"$_tag_type\"\r\nA581,310,2,4,1,1,N,\"$_product_name_1\"\r\nA581,288,2,4,1,1,N,\"$_product_name_2\"\r\nA591,255,2,4,1,1,N,\"$_tag_date\"\r\nA228,255,2,4,1,1,N,\"$_store\"\r\nA588,220,2,3,1,1,N,\"-\"\r\nA595,200,2,4,1,1,N,\"$_user_name\"\r\nA510,40,2,4,1,1,N,\"$_barcode\"\r\nP1\r\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 1;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\r\nI8,A,001\r\n\r\n\r\nQ408,024\r\nq448\r\nrN\r\nS2\r\nD10\r\nZT\r\nJF\r\nO\r\nR112,0\r\nf100\r\nN\r\nB570,289,2,1,2,6,120,N,\"$_barcode\"\r\nA588,340,2,3,1,1,N,\"-\"\r\nA591,378,2,4,1,1,N,\"$_tag_date\"\r\nA595,320,2,4,1,1,N,\"$_user_name\"\r\nA228,375,2,4,1,1,N,\"$_store\"\r\nA510,165,2,4,1,1,N,\"$_barcode\"\r\nA586,135,2,4,2,2,N,\"$_tag_type\"\r\nA581,70,2,4,1,1,N,\"$_product_name_1\"\r\nA581,40,2,4,1,1,N,\"$_product_name_2\"\r\nA197,135,2,4,2,2,N,\"$_order_list\"\r\nP1\r\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 2;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\r\nI8,A,001\r\n\r\n\r\nQ408,024\r\nq448\r\nrN\r\nS2\r\nD1\r\nZT\r\nJF\r\nO\r\nR112,0\r\nf100\r\nN\r\nB570,165,2,1,2,6,120,N,\"$_barcode\"\r\nA197,380,2,4,2,3,N,\"$_order_list\"\r\nA586,380,2,4,2,3,N,\"$_tag_type\"\r\nA581,310,2,4,1,1,N,\"$_product_name_1\"\r\nA581,288,2,4,1,1,N,\"$_product_name_2\"\r\nA591,255,2,4,1,1,N,\"$_tag_date\"\r\nA228,255,2,4,1,1,N,\"$_store\"\r\nA588,220,2,3,1,1,N,\"-\"\r\nA595,200,2,4,1,1,N,\"$_user_name\"\r\nA510,40,2,4,1,1,N,\"$_barcode\"\r\nP1\r\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 1;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\r\nI8,A,001\r\n\r\n\r\nQ408,024\r\nq448\r\nrN\r\nS2\r\nD1\r\nZT\r\nJF\r\nO\r\nR112,0\r\nf100\r\nN\r\nB570,289,2,1,2,6,120,N,\"$_barcode\"\r\nA588,340,2,3,1,1,N,\"-\"\r\nA591,378,2,4,1,1,N,\"$_tag_date\"\r\nA595,320,2,4,1,1,N,\"$_user_name\"\r\nA228,375,2,4,1,1,N,\"$_store\"\r\nA510,165,2,4,1,1,N,\"$_barcode\"\r\nA586,135,2,4,2,2,N,\"$_tag_type\"\r\nA581,70,2,4,1,1,N,\"$_product_name_1\"\r\nA581,40,2,4,1,1,N,\"$_product_name_2\"\r\nA197,135,2,4,2,2,N,\"$_order_list\"\r\nP1\r\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 2;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\nI8,A,001\n\n\nQ408,024\nq448\nrN\nS2\nD1\nZT\nJF\nO\nR112,0\nf100\nN\nB560,200,2,1,2,6,60,N,\"$_barcode\"\nA588,378,2,3,1,1,N,\"$_product_location\"\nA595,360,2,4,1,1,N,\"$_user_name\"\nA610,310,2,4,2,2,N,\"PRESENTACION\"\nA200,310,2,4,2,2,N,\"$_presentation\"\nA610,260,2,4,2,2,N,\"INCOMPLETA\"\nA128,360,2,4,1,1,N,\"$_store_prefix\"\nA600,125,2,4,2,1,N,\"$_tag_type\"\nA581,85,2,4,1,1,N,\"$_product_name_1\"\nA581,55,2,4,1,1,N,\"$_product_name_2\"\nA220,220,2,4,2,1,N,\"$_order_list\"\nA220,170,2,4,2,1,N,\"$_piece_unit\"\nP1\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 3;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = '\nI8,A,001\n\n\nQ94,024\nq370\nrN\nS2\nD1\nZT\nJF\nO\nR112,0\nf100\nN\nB390,140,2,1,2,6,100,N,\"$_barcode\"\nA440,20,1,4,1,2,N,\"$_order_list\"\nA400,190,2,1,1,1,N,\"$_product_name_1\"\nA400,160,2,1,1,1,N,\"$_product_name_2\"\nA350,30,2,3,1,1,N,\"$_barcode\"\nP$_prints_number\n' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 4;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = 'I8,A,001\n\n\nQ408,024\nq448\nrN\nS2\nD1\nZT\nJF\nO\nR112,0\nf100\nN\nB440,20,1,1,3,6,280,N,\"$_barcode\"\nA585,5,1,4,3,5,N,\"SELLO\"\nA505,40,1,4,3,5,N,\"ROTO\"\nA150,20,1,4,2,3,N,\"ESCANEAR\"\nA80,20,1,4,2,3,N,\"PAQUETES\"\nP1' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 5;\nUPDATE `sys_plantillas_etiquetas` SET `codigo_epl` = 'I8,A,001\r\n\r\n\r\nQ408,024\r\nq448\r\nrN\r\nS2\r\nD1\r\nZT\r\nJF\r\nO\r\nR112,0\r\nf100\r\nN\r\nB560,240,2,1,2,6,60,N,\"$_barcode\"\r\nA150,355,2,4,1,1,N,\"$_store_prefix\"\r\nA580,375,2,4,3,3,N,\"$_maquile_unit\"\r\nA580,310,2,4,3,3,N,\"INCOMPLETO\"\r\nA210,175,2,4,4,3,N,\"$_tag_type\"\r\nA581,85,2,4,1,1,N,\"$_product_name_1\"\r\nA581,55,2,4,1,1,N,\"$_product_name_2\"\r\nA220,220,2,4,2,1,N,\"$_order_list\"\r\nA440,170,2,4,2,3,N,\"$_piece_unit\"\r\nP1' WHERE `sys_plantillas_etiquetas`.`id_plantilla_etiquetas` = 6;";
		//$sql = str_replace("\\\\", "", $sql );
			//die( 'sql : ' . $sql );
			$this->link->autocommit( false );
			$stm = $this->link->multi_query( $sql );
			if( ! $stm ){
				if( $type == 'API' ){	
					$sql = str_replace("'", "\'", $sql );
				}
			//$sql = str_replace( "\'", "\\\'", $sql );
				$resp = "error|*|{$script->script_id}|*|Error al ejecutar Script(s) : {$sql} {$this->link->error}";
				//die( $resp );
				$mysql_error = str_replace( "'", "\'", $this->link->error );
				$this->reportScriptExecutionError( $branch_id, $script, "Error al ejecutar Script(s) :\n\n{$sql}\n\n{$mysql_error}" );
				$this->updateScriptStatus( $branch_id, $branch_name, $script, 3, "Error al ejecutar Script(s) : {$sql} {$mysql_error}" );//error
			}else{
				while( $this->link->more_results() && $this->link->next_result() ){
					//echo "here";
			        $resp .= "\n" . $this->link->affected_rows;
			    }
				$this->updateScriptStatus( $branch_id, $branch_name, $script, 2, "Script(s) ejecutados exitosamente" );
			}
			$this->link->autocommit( true );
			return $resp;
		}
		public function reportScriptExecutionError( $branch_id, $script, $error ){
			//$error = str_replace( "'", "\'", $error );
			//die( "Error : {$error}" );
			$sql = "INSERT INTO versionador_bitacora_errores_scripts ( id_rama_script_error, id_rama, id_rama_script,
				error, fecha_error ) VALUES ( NULL, {$branch_id}, {$script->script_id}, '{$error}', NOW() )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar error de ejecucion de Script : {$this->link->error} {$sql}" );
			return 'ok';
		}
		public function updateScriptStatus( $branch_id, $branch_name, $script, $status, $description, $branch_id ){
			//$description = str_replace( "'", "\'", $description );
			$sql = "UPDATE versionador_ramas_scripts vrs
					LEFT JOIN versionador_scripts_{$branch_name} vs
					ON vrs.id_script = vs.id_script_{$branch_name}
						SET vrs.id_status_script = '{$status}',
						vrs.resultado = '{$description}',
						vrs.fecha_ejecucion = NOW(),
						vs.fecha_ejecucion = NOW()
					WHERE vrs.id_script = {$script->script_id}
					AND vrs.id_rama = {$branch_id}";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al actualizar ejecucion de Script en rama : {$sql} {$this->link->error}" );
			return 'ok';
		}

		public function setUniqueFolio( $branch_name ){
			$sql = "SELECT CONCAT( '{$branch_name}_', IF( id_script_{$branch_name} IS NULL, 1, ( MAX( id_script_{$branch_name} ) + 1 ) ) ) AS folio FROM versionador_scripts_{$branch_name}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la generacion de folio unico de script : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['folio'];
		}
	/*ramas*/
		public function getBranch( $branch_id ){
			$sql = "SELECT
						id_rama AS branch_id,
						nombre AS name,
						orden AS branch_order,
						observaciones AS branch_notations,
						url_repositorio_central AS central_api_url,
						habilitado AS is_enabled,
						fecha_creacion,
						fecha_actualizacion
					FROM versionador_ramas
					WHERE id_rama = {$branch_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la rama : {$this->link->error} {$sql}" );
			$branch = $stm->fetch_assoc();
			return $branch;
		}
	}
?>