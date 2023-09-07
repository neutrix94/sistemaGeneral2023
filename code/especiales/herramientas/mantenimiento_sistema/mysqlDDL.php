<?php
	//$mysqlDDL = new mysqlDDL( $link );
//reinsertar triggers de movimientos de almacen
	//$mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggersInventarios/' );
//eliminar triggers de movimientos de almacen
	//echo $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/eliminarTriggersInventarios/' );

	class mysqlDDL
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function alterInventoryTriggersSinceFiles( $path ){
			//echo $path;
			// Arreglo con todos los nombres de los archivos
			$files = array_diff(scandir($path), array('.', '..')); 
			$code = $_GET['codigo'];
			if( !$files ){
				echo "No hay archivos en : <b>{$path}</b>";
			}
		$this->link->autocommit( false );
			foreach($files as $file){
			    // Divides en dos el nombre de tu archivo utilizando el . 
			    $data = explode(".", $file);
			    // Nombre del archivo
			    $fileName      = $data[0];
			    // Extensión del archivo 
			    $fileExtension = $data[1];
			    //echo "<br>{$fileName}";
			    //echo " - {$fileExtension}";
			    if( $fileExtension == "sql" || $fileExtension == "SQL" ){
			    	echo $this->processFileAndExcecute( "{$path}{$file}" );
			    }
			}
			$this->link->autocommit( true );
			return 'ok';
		}

		public function processFileAndExcecute( $file ){
			$cadena_arreglo="";
			if (!file_exists( $file ) ){
				die( "no existe el archivo : {$file}" );
			}
			$fp = fopen( $file, "r")or die("Error al abrir archivo : {$file}");
			while (!feof($fp)){
			 	$linea = fgets($fp);
			 	$cadena_arreglo.=$linea;
			}
			fclose($fp);
	//echo $cadena_arreglo;
		//$cadena_arreglo=str_replace("DELIMITER $$", "", $cadena_arreglo);
			$code = explode("|", $cadena_arreglo);
			for( $i = 0; $i < sizeof( $code ); $i++ ){
		//		echo "Array: ".$arreglo_procedure[$i]."\n";
				$code[$i] = str_replace( "DELIMITER $$", "", $code[$i] );
				$code[$i] = str_replace ("$$", "", $code[$i] );
				$stm = $this->link->query( $code[$i] );
				if( !$stm ){
					die( "Error al ejecutar consulta DDL  en {$file}: {$this->link->error}" );
				}
			}
		}
	}
?>