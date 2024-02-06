<?php
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
		}
	}
?>