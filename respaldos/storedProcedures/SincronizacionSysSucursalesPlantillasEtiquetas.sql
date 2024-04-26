DROP PROCEDURE IF EXISTS SincronizacionSysSucursalesPlantillasEtiquetas|
DELIMITER $$
CREATE PROCEDURE SincronizacionSysSucursalesPlantillasEtiquetas( IN TIPO_EVENTO VARCHAR(6), IN ID_REGISTRO INTEGER(1), IN ID_SUCURSAL_ORIGEN INTEGER(11) )
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
				'"table_name" : "sys_sucursales_plantillas_etiquetas",\n',
				'"id_sucursal" : "', spe.id_sucursal, '",\n',
				'"primary_key" : "id_sucursal_plantilla_etiqueta",\n',
				'"primary_key_value" : "', spe.id_sucursal_plantilla_etiqueta, '",\n',
                IF( TIPO_EVENTO = 'insert' OR TIPO_EVENTO = 'INSERT',
                    CONCAT( '"id_sucursal_plantilla_etiqueta" : "', spe.id_sucursal_plantilla_etiqueta, '",\n' ),
                    ''
                ),
                '"id_plantilla" : "', spe.id_plantilla, '",\n',
				'"tipo_codigo_plantilla" : "', spe.tipo_codigo_plantilla, '",\n',
				'"ruta_destino" : "', spe.ruta_destino, '",\n',
				'"habilitado" : "', spe.habilitado, '",\n',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'SincronizacionSysSucursalesPlantillasEtiquetas.sql',
		1
	FROM sys_sucursales_plantillas_etiquetas spe
    LEFT JOIN sys_sucursales s 
    ON s.id_sucursal = spe.id_sucursal
    WHERE spe.id_sucursal_plantilla_etiqueta = ID_REGISTRO;
END $$