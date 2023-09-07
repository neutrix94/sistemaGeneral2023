<?php	
	class ORM{
		private $link;
		private $database;
		function __construct(){
			$metadataDB = 'information_schema';
			$this->database = 'base_cdll_mis_pruebas';
			$this->link = mysqli_connect( 'localhost', 'root', '', $metadataDB );
			if( $this->link->connect_error ){
				die( "Error al conectar con la Base de Datos : " . $this->link->connect_error);
			}else{
				//echo "<p>Conectado a {$metadataDB}</p>";
			}
			$this->link->set_charset("utf8mb4");
		}

		public function getTables( $table_name ){
			$sql = "SELECT TABLE_NAME, TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = '{$this->database}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar tablas en {$this->database} : {$this->link->error}" );
			return $this->build_combo( $stm, $table_name );
		}

		public function build_combo( $stm, $table_name ){
			$resp = "<select id=\"tables_combo\" class=\"form-control\" onchange=\"getQueries( this );\">";
			while ( $r = $stm->fetch_row() ) {
				$resp .= "<option value=\"{$r[0]}\" " . ( trim($table_name) == trim($r[0]) ? " selected" : "" ) . ">{$r[1]}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function getTableMetadata( $table_name ){
			$sql = "SELECT COLUMN_NAME FROM COLUMNS WHERE TABLE_SCHEMA = '{$this->database}' AND TABLE_NAME = '$table_name'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar campos en {$table_name} : {$this->link->error}" );
			$stm2 = $this->link->query( $sql ) or die( "Error al consultar campos en {$table_name} : {$this->link->error}" );
			$stm3 = $this->link->query( $sql ) or die( "Error al consultar campos en {$table_name} : {$this->link->error}" );
			$resp = array( $this->buildQueryInsert( $stm, $table_name ), 
							$this->buildQueryUpdate( $stm2, $table_name ),
							$this->buildQuerySelect( $stm3, $table_name )
					);//
			return $resp;
		}

		public function buildQueryInsert( $stm, $table_name ){
			$resp ="INSERT INTO {$table_name} (\n\t";
			$fields = "";
			$fields_vars = "";
			$counter = 1;
			while ( $r = $stm->fetch_row() ){
				$fields .= ( $fields == "" ? "" : ",\n\t" );
				$fields .= "/*{$counter}*/{$r[0]}";
				$fields_vars .= ( $fields_vars == "" ? "" : ",\n\t" );
				$fields_vars .= "/*{$r[0]}*/'{\${$r[0]}}'";
				$counter ++;
			}
			$resp .= " {$fields} )\n VALUES ( \n\t{$fields_vars} )";
			return $resp;
		}

		public function buildQueryUpdate( $stm, $table_name ){
			$resp ="UPDATE {$table_name} SET\n\t";
			$fields = "";
			$counter = 1;
			while ( $r = $stm->fetch_row() ){
				$fields .= ( $fields == "" ? "" : ",\n\t" );
				$fields .= "/*{$counter}*/{$r[0]}='{\${$r[0]}}'";
				$counter ++;
			}
			$resp .= " {$fields} WHERE ";
			return $resp;
		}
		public function buildQuerySelect( $stm, $table_name ){
			$resp ="SELECT \n\t";
			$fields = "";
			$counter = 1;
			while ( $r = $stm->fetch_row() ){
				$fields .= ( $fields == "" ? "" : ",\n\t" );
				$fields .= "/*{$counter}*/{$r[0]} AS ''";
				$counter ++;
			}
			$resp .= "{$fields}\nFROM {$table_name}";
			$resp .= "\nWHERE ";
			return $resp;
		}
	}

	$gftorm = new ORM(  );//$link, $dbName
	//echo $gftorm->getTables();
	if( isset( $_GET['table'] ) ){
		$queries = $gftorm->getTableMetadata(  $_GET['table'] );
	}
?>
<!DOCTYPE html>
<head>
	<title>Generador de consultas MYSQL BY NEUTRIX</title>
	<link rel="stylesheet" type="text/css" href="../css/bootstrap/css/bootstrap.css">
</head>
<body>
	<div>
		<div class="row">
			<div class="col-2">Tabla : </div>
			<div class="col-8">
			<?php	
				echo $gftorm->getTables( ( isset( $_GET['table'] ) ? $_GET['table'] : '' ) ); 
			?>
			</div>
			<div class="col-2">
				<button type="button" class="btn btn-success form.control">
					Generar
				</button>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-4">
				<label class="btn-primary form-control text-center">INSERT</label>
				<textarea class="form-control" style="height : 600px;">
				<?php	
					echo $queries[0]; 
				?>
				</textarea>
			</div>
			<div class="col-4">
				<label class="btn-primary form-control text-center">UPDATE</label>
				<textarea class="form-control" style="height : 600px;">
				<?php	
					echo $queries[1]; 
				?>
				</textarea>
			</div>
			<div class="col-4">
				<label class="btn-primary form-control text-center">SELECT</label>
				<textarea class="form-control" style="height : 600px;">
				<?php	
					echo $queries[2]; 
				?>
				</textarea>
			</div>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	
	function getQueries( obj ){
		location.href = './queryGeneratorByNeutrix.php?table=' + obj.value;
	}
	
</script>