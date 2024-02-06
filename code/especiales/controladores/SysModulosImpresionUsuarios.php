<?php
/**
 * 
 */
	class SysModulosImpresionUsuarios
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
	//
		function obtener_ruta_modulo_usuario( $id_usuario, $modulo ){
		//consulta el nombre de la carpeta del modulo para el usuario especifico
			$sql = "SELECT 
						miu.id_modulo_impresion,
						miu.id_usuario,
						CONCAT( c.path, '/', c.nombre_carpeta ) AS nombre_carpeta_modulo,
						miu.endpoint_api_destino
					FROM sys_modulos_impresion_usuarios miu
					LEFT JOIN sys_carpetas c
					ON miu.id_carpeta = c.id_carpeta
					WHERE id_modulo_impresion = {$modulo}
					AND id_usuario = {$id_usuario}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre de la carpeta del modulo : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return 'no';
			}else{
				$ruta_especifica = $stm->fetch_assoc();
				return "{$ruta_especifica['nombre_carpeta_modulo']}";
			}
		}
	//creacion de registros de sincronizacion
		function crearRegistrosSincronizacionModuloImpresion( $tipo, $tabla, $id_registro ){
			$json = "{
				    \"table_name\" : \"sys_modulos_impresion\",
				    \"action_type\" : \"{$tipo}\",
				    \"primary_key\" : \"id_modulo_impresion\",
				    \"primary_key_value\" : \"{$id_registro}\"";
			if( $tipo == 'insert' || $tipo == 'update' ){
			//consulta datos del registro
				$sql = "SELECT * FROM sys_modulos_impresion WHERE id_modulo_impresion = {$id_registro}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la carpeta para sincronizacion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$json .= ", 
					\"id_modulo_impresion\" : \"{$row['id_modulo_impresion']}\",
					\"nombre_modulo\" : \"{$row['nombre_modulo']}\",
					\"nombre_carpeta_modulo\" : \"{$row['nombre_carpeta_modulo']}\",
					\"archivo\" : \"{$row['archivo']}\",
					\"sincronizar\" : \"{$row['sincronizar']}\"";
			}
			$json .= "}";
		//inserta el registro de sincronizacion
			$sql = "INSERT INTO sys_sincronizacion_registros( sucursal_de_cambio, id_sucursal_destino, datos_json, tipo, folio_unico_peticion, status_sincronizacion )
					SELECT
						-1, 
						id_sucursal, 
						'{$json}', 
						'SysModulosImpresionUsuarios.php', 
						NULL,
						1
					FROM sys_sucursales
					WHERE id_sucursal > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de {$tabla} : {$this->link->error}" );
			return 'ok';
		}
	}
?>