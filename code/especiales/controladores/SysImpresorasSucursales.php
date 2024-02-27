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
	//creacion de registros de sincronizacion
		function crearRegistrosSincronizacionImpresorasSucursales( $tipo, $tabla, $id_registro, $link = null ){
			$json = "{
				    \"table_name\" : \"sys_impresoras_sucursales\",
				    \"action_type\" : \"{$tipo}\",
				    \"primary_key\" : \"id_impresora_sucursal\",
				    \"primary_key_value\" : \"{$id_registro}\"";//die('here');
			if( $tipo == 'insert' || $tipo == 'update' ){
			//consulta datos del registro
				$sql = "SELECT * FROM sys_impresoras_sucursales WHERE id_impresora_sucursal = {$id_registro}";
				$stm = mysql_query( $sql ) or die( "Error al consultar datos de la carpeta para sincronizacion : " );//$this->link->query{$this->link->error}
				$row = mysql_fetch_assoc($stm);
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
			$stm = mysql_query( $sql ) or die( "Error al insertar registros de sincronizacion de {$tabla} : {$sql} : " . mysql_error() );
			return 'ok';
		}
	}
?>