<?php
/**
 * 
 */
	class SysImpresorasSucursales
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
	/*
		function obtener_ruta_modulo( $id_sucursal, $modulo ){
		//consulta el nombre de la sucursal
			$sql = "SELECT 
						REPLACE( nombre, ' ', '_' ) AS nombre
					FROM sys_sucursales
					WHERE id_sucursal = {$id_sucursal}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre de la sucursal : {$this->link->error}" );
			$sucursal = $stm->fetch_assoc();
		//consulta el nombre de la carpeta del modulo
			$sql = "SELECT 
						nombre_carpeta_modulo
					FROM sys_modulos_impresion
					WHERE id_modulo_impresion = {$modulo}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre de la carpeta del modulo : {$this->link->error}" );
			$ruta = $stm->fetch_assoc();
			return "{$sucursal['nombre']}/{$ruta['nombre_carpeta_modulo']}";
		}*/
	//creacion de registros de sincronizacion
		function crearRegistrosSincronizacionImpresorasSucursales( $tipo, $tabla, $id_registro ){
			$json = "{
				    \"table_name\" : \"sys_impresoras_sucursales\",
				    \"action_type\" : \"{$tipo}\",
				    \"primary_key\" : \"id_impresora_sucursal\",
				    \"primary_key_value\" : \"{$id_registro}\"";
			if( $tipo == 'insert' || $tipo == 'update' ){
			//consulta datos del registro
				$sql = "SELECT * FROM sys_impresoras_sucursales WHERE id_impresora_sucursal = {$id_registro}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la carpeta para sincronizacion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$json .= ", 
					\"id_impresora_sucursal\" : \"{$row['id_impresora_sucursal']}\",
					\"id_sucursal\" : \"{$row['id_sucursal']}\",
					\"nombre_impresora\" : \"{$row['nombre_impresora']}\",
					\"habilitada\" : \"{$row['habilitada']}\",
					\"sincronizar\" : \"{$row['sincronizar']}\"";
			}
			$json .= "}";
		//inserta el registro de sincronizacion
			$sql = "INSERT INTO sys_sincronizacion_registros( sucursal_de_cambio, id_sucursal_destino, datos_json, tipo, folio_unico_peticion, status_sincronizacion )
					SELECT
						-1, 
						id_sucursal, 
						'{$json}', 
						'SysImpresorasSucursales.php', 
						NULL,
						1
					FROM sys_sucursales
					WHERE id_sucursal = {$row['id_sucursal']}";//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de {$tabla} : {$this->link->error}" );
			return 'ok';
		}
	}
?>