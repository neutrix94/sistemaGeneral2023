DROP TRIGGER IF EXISTS insertaAfiliacion|
DELIMITER $$
CREATE TRIGGER insertaAfiliacion
AFTER INSERT ON ec_afiliaciones
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

	INSERT INTO ec_afiliacion_sucursal
    	SELECT 
    	null,
    	new.id_afiliacion,
    	id_sucursal,
    	0,
    	1
	FROM sys_sucursales
	WHERE id_sucursal>0;

    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_afiliaciones",',
                '"action_type" : "insert",',
                '"primary_key" : "id_afiliacion",',
                '"primary_key_value" : "', new.id_afiliacion, '",',
                '"id_afiliacion" : "', new.id_afiliacion, '",',
                '"id_banco" : "', new.id_banco, '",',
                '"no_afiliacion" : "', new.no_afiliacion, '",',
                '"id_tipo_terminal" : "', new.id_tipo_terminal, '",',
                '"numero_serie_terminal" : "', new.numero_serie_terminal, '",',
                '"imprimir_ticket" : "', new.imprimir_ticket, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaAfiliacion',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$