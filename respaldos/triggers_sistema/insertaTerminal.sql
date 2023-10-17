DROP TRIGGER IF EXISTS insertaTerminal|
DELIMITER $$
CREATE TRIGGER insertaTerminal
AFTER INSERT ON ec_terminales_integracion_smartaccounts
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

	INSERT INTO ec_terminales_sucursales_smartaccounts ( id_terminal_sucursal, id_terminal, id_sucursal, estado_suc, 
        id_razon_social, sincronizar )
    	SELECT 
    	null,
    	new.id_terminal_integracion,
    	id_sucursal,
    	0,
        -1,
    	1
	FROM sys_sucursales
	WHERE id_sucursal>0;

    /*SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
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
    END IF;*/
END $$