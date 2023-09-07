<?php
	
	if( isset($_GET['pull_fl']) || isset($_POST['pull_fl']) ){
		$action = ( isset($_GET['pull_fl']) ? $_GET['pull_fl'] : $_POST['pull_fl'] );
		include( '../../../../../conexionMysqli.php' );
		$sP = new scriptPull( $link );
		switch ( $action ) {
			case 'branches_comparation':
				$origin_branch_name = ( isset($_GET['origin_branch_name']) ? $_GET['origin_branch_name'] : $_POST['origin_branch_name'] );
				$destinity_branch_name = ( isset($_GET['destinity_branch_name']) ? $_GET['destinity_branch_name'] : $_POST['destinity_branch_name'] );
				echo json_encode( $sP->get_branches_scripts_comparation( $origin_branch_name, $destinity_branch_name ) );
			break;

			case 'update_branch_scripts' :
				$origin_branch_name = ( isset($_GET['origin_branch_name']) ? $_GET['origin_branch_name'] : $_POST['origin_branch_name'] );
				$destinity_branch_name = ( isset($_GET['destinity_branch_name']) ? $_GET['destinity_branch_name'] : $_POST['destinity_branch_name'] );
				$scripts = ( isset($_GET['scripts']) ? $_GET['scripts'] : $_POST['scripts'] );
				echo json_encode( $sP->make_pull( $origin_branch_name, $destinity_branch_name, $scripts ) );
			break;

			/*case 'getScriptList' :
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
			*/
			default:
				die( "Permission denied ON '{$action}'!" );
			break;
		}
	}

	class scriptPull
	{
		
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function get_branch_combo( $element_id ){
			$branches = $this->getBranches();
			$resp = "<select id=\"{$element_id}\" class=\"form-control\">";
			$resp .= "<option value=\"0\">-- Seleccionar --</option>";
			foreach ($branches as $key => $branch ) {
				$resp .= "<option value=\"{$brach['branch_id']}\">{$branch['branch_name']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function getBranches( $branch_id = null ){
			$branches = array();
			$sql = "SELECT 
						id_rama AS branch_id, 
						nombre AS branch_name
					FROM versionador_ramas";
			$stm = $this->link->query( $sql ) or die( "Error al obtener ramas : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push( $branches, $row );
			}
			return $branches;
		}

		public function get_branches_scripts_comparation( $origin_branch_name, $destinity_branch_name, $scripts_ids = null ){
			$scripts = array();
			$condition = '';
			if( $scripts_ids != null ){
				$condition = "AND id_script_{$origin_branch_name} IN( {$scripts_ids} )";
			}
		//busca los folio unicos del origen
			$sql = "SELECT
						id_script_{$origin_branch_name} AS script_id,
						descripcion AS description,
						codigo AS code,
						rama_creacion AS creation_branch_id,
						folio_unico AS unique_folio,
						fecha_alta AS creation_date
					FROM versionador_scripts_{$origin_branch_name}
					WHERE folio_unico NOT IN( SELECT 
						folio_unico 
						FROM versionador_scripts_{$destinity_branch_name} 
					) {$condition}";
	//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al obtener comparacion entre ramas : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push( $scripts, $row );
			}
			return $scripts;
		}

		public function make_pull( $origin_branch_name, $destinity_branch_name, $scripts ){//inserta cada una de los scripts en el destino
			$rows = $this->get_branches_scripts_comparation( $origin_branch_name, $destinity_branch_name, $scripts );	
			$this->link->autocommit( false );
			foreach ($rows as $key => $row) {
				$row['code'] = str_replace("\'", "\\\'", $row['code'] );
				$row['code'] = str_replace("'", "\'", $row['code'] );
				$sql = "INSERT INTO versionador_scripts_{$destinity_branch_name} ( descripcion, codigo, rama_creacion, 
					folio_unico, fecha_alta ) VALUES ( '{$row['description']}', '{$row['code']}', 
					'{$row['creation_branch_id']}', '{$row['unique_folio']}', '{$row['creation_date']}')";
				$insert = $this->link->query( $sql ) or die( "Error al insertar script en rama : {$sql} {$this->link->error}" );			
			}
			$this->link->autocommit( true );
			return "ok|Scripts Insertados exitosamente de {$origin_branch_name} en {$destinity_branch_name}";
		}



		public function insert_pull( $scripts, $origin_branch_name, $destinity_branch_name ){
			
		}

	}
?>