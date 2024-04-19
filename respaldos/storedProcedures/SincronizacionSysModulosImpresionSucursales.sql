DROP PROCEDURE IF EXISTS SincronizacionSysModulosImpresionSucursales|
DELIMITER $$
CREATE PROCEDURE SincronizacionSysModulosImpresionSucursales( IN TIPO_EVENTO VARCHAR(6), IN ID_REGISTRO INTEGER(1), IN ID_SUCURSAL_ORIGEN INTEGER(11) )
BEGIN
    DECLARE store_id INTEGER(11);
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		store_id,
		IF( store_id = -1, s.id_sucursal, -1 ),
		CONCAT(
			'{\n',
				'"action_type" : "', TIPO_EVENTO,'",\n',
				'"table_name" : "sys_modulos_impresion_sucursales",\n',
				'"id_sucursal" : "', mis.id_sucursal, '",\n',
				'"primary_key" : "id_modulo_impresion_sucursal",\n',
				'"primary_key_value" : "', mis.id_modulo_impresion_sucursal, '",\n',
                IF( TIPO_EVENTO = 'insert' OR TIPO_EVENTO = 'INSERT',
                    CONCAT( '"id_modulo_impresion_sucursal" : "', mis.id_modulo_impresion_sucursal, '",\n' ),
                    ''
                ),
                '"id_modulo_impresion" : "', mis.id_modulo_impresion, '",\n',
                '"id_carpeta" : "', mis.id_carpeta, '",\n',
				'"id_impresora_sucursal" : "', mis.id_impresora_sucursal, '",\n',
				'"extension_archivo" : "', mis.extension_archivo, '",\n',
				'"endpoint_api_destino" : "', mis.endpoint_api_destino, '",\n',
				'"endpoint_api_destino_local" : "', mis.endpoint_api_destino_local, '",\n',
				'"id_comando_impresion" : "', mis.id_comando_impresion, '",\n',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'SincronizacionSysModulosImpresionSucursales.sql',
		1
	FROM sys_modulos_impresion_sucursales mis
    LEFT JOIN sys_sucursales s 
    ON s.id_sucursal = mis.id_sucursal
    WHERE mis.id_modulo_impresion_sucursal = ID_REGISTRO;
END $$