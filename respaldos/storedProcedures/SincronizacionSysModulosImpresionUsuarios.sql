DROP PROCEDURE IF EXISTS SincronizacionSysModulosImpresionUsuarios|
DELIMITER $$
CREATE PROCEDURE SincronizacionSysModulosImpresionUsuarios( IN TIPO_EVENTO VARCHAR(6), IN ID_REGISTRO INTEGER(1), IN ID_SUCURSAL_ORIGEN INTEGER(11) )
BEGIN
    DECLARE store_id INTEGER(11);
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		store_id,
		IF( store_id = -1, u.id_sucursal, -1 ),
		CONCAT(
			'{\n',
				'"action_type" : "', TIPO_EVENTO,'",\n',
				'"table_name" : "sys_modulos_impresion_usuarios",\n',
                IF( TIPO_EVENTO = 'insert' OR TIPO_EVENTO = 'INSERT',
					CONCAT( '"id_modulo_impresion_usuario" : "', miu.id_modulo_impresion_usuario, '",\n' ),
                    ''
                ),
				'"id_modulo_impresion" : "', miu.id_modulo_impresion, '",\n',
				'"id_usuario" : "', miu.id_usuario, '",\n',
				'"id_carpeta" : "', miu.id_carpeta, '",\n',
				'"id_impresora_sucursal" : "', miu.id_impresora_sucursal, '",\n',
				'"extension_archivo" : "', miu.extension_archivo, '",\n',
				'"endpoint_api_destino" : "', miu.endpoint_api_destino, '",\n',
				'"endpoint_api_destino_local" : "', miu.endpoint_api_destino_local, '",\n',
				'"id_comando_impresion" : "', miu.id_comando_impresion, '",\n',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'SincronizacionSysModulosImpresionUsuarios.sql',
		1
	FROM sys_modulos_impresion_usuarios miu
    LEFT JOIN sys_users u 
    ON u.id_usuario = miu.id_usuario
    WHERE miu.id_modulo_impresion_usuario = ID_REGISTRO;
END $$