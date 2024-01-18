<?php
/*actualizado desde rama api_busqueda_archivos 2024-01-18*/
	class PrintApi
	{	
		private $link;
		private $global_path;
		private $configuration_file_path;
		function __construct( $connection, $path )
		{
			$this->link = $connection;
			$this->global_path = $path;
			$this->configuration_file_path = $this->getSystemPath();
			//echo $this->configuration_file_path;
		}

		public function getFilesPendingToDownload( $store_id ){
			$resp = array();
			$sql = "SELECT 
						id_archivo AS file_id,
						nombre_archivo AS file_name,
						ruta_origen AS origin_route,
						ruta_destino AS destinity_route
					FROM sys_archivos_descarga 
					WHERE descargado = 0 
					AND id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consulatar los archivo pendientes : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$resp[] = $row;
			}
			return $resp;
		}

		public function files_download($ruta_origen, $ruta_destino, $nombre) {
			$ruta_destino = "{$this->configuration_file_path}{$ruta_destino}";
			//die( $ruta_destino );
		    $ruta_origen .= $nombre;
		    // Definimos el directorio de descarga del archivo
		    if( ! is_dir( $ruta_destino ) ) {
		        if( ! mkdir( $ruta_destino, 0777, true) ) {
		            return "No se pudo crear la carpeta de destino: {$ruta_destino}";
		        }
		    }

		    $file = $ruta_destino . $nombre;

		    // Establecemos la conexión con la URL
		    $conn = file_get_contents($ruta_origen);

		    if ($conn === false) {
		        return "No se pudo conectar a la URL: " . $ruta_origen;
		    }

		    //echo "\nEmpezando descarga:\n";
		    //echo ">> URL: " . $ruta_origen . "\n";
		    //echo ">> Nombre: " . $nombre . "\n";
		    //echo ">> Tamaño: " . strlen($conn) . " bytes\n";

		    // Abrimos los streams
		    $in = fopen($ruta_origen, 'rb');
		    $out = fopen($file, 'wb');

		    if (!$in || !$out) {
		        return "Error al abrir los streams.";
		    }

		    // Leemos y escribimos el stream
		    while (!feof($in)) {
		        fwrite($out, fread($in, 8192));
		    }

		    // Cerramos los streams
		    fclose($in);
		    fclose($out);

		    return "ok";
		}

		public function getSystemPath(){
			$archivo_path = "{$this->global_path}conexion_inicial.txt";
			$path = array();
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
			return $ruta_or;
		}
	}
?>