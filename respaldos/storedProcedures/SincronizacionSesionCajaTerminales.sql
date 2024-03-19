DROP PROCEDURE IF EXISTS SincronizacionSesionCajaTerminales|
DELIMITER $$
CREATE PROCEDURE SincronizacionSesionCajaTerminales( IN TIPO_EVENTO VARCHAR(6), IN ID_REGISTRO INTEGER(1) )
BEGIN
    DECLARE unique_folio VARCHAR(30);
    DECLARE store_prefix VARCHAR(30);
    DECLARE store_id  INTEGER(11);

    SELECT id_sucursal, prefijo INTO store_id, store_prefix FROM sys_sucursales WHERE acceso = 1;
    
    SET unique_folio = CONCAT( store_prefix, '_SCTER_', ID_REGISTRO );
	
    INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		store_id,
		IF( store_id = -1, s.id_sucursal, -1 ),
		CONCAT(
			'{\n',
				'"action_type" : "', TIPO_EVENTO,'",\n',
				'"table_name" : "ec_sesion_caja_terminales",\n',
                '"primary_key" : "folio_unico",', 
                '"primary_key_value" : "', unique_folio, '",', 
                IF( TIPO_EVENTO = 'insert' OR TIPO_EVENTO = 'INSERT',
				    CONCAT( '"folio_unico" : "', unique_folio, '",\n' ),
                    ''
                ),
				'"id_sesion_caja" : "',
                CONCAT( '( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', sc.folio_unico, '\' )",\n'),
				'"id_cajero" : "', sct.id_cajero, '",\n',
                '"id_terminal" : "', sct.id_terminal, '",\n',
                '"habilitado" : "', sct.habilitado, '",\n',
				'"insertada_por_error_en_cobro" : "', sct.insertada_por_error_en_cobro, '",\n',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'SincronizacionSesionCajaTerminales.sql',
		1
	FROM ec_sesion_caja_terminales sct
    LEFT JOIN ec_sesion_caja sc
    ON sc.id_sesion_caja = sct.id_sesion_caja
    LEFT JOIN sys_sucursales s 
    ON sc.id_sucursal = s.id_sucursal
    WHERE sct.id_sesion_caja_terminales = ID_REGISTRO;
END $$