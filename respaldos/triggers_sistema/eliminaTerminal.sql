DROP TRIGGER IF EXISTS eliminaTerminal|
DELIMITER $$
CREATE TRIGGER eliminaTerminal
AFTER DELETE ON ec_terminales_integracion_smartaccounts
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
/*registros de sincronizacion*/
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_terminales_integracion_smartaccounts",',
                '"action_type" : "delete",',
                '"primary_key" : "id_terminal_integracion",',
                '"primary_key_value" : "', old.id_terminal_integracion, '"'
                '}'
            ),
            NOW(),
            'eliminaTerminal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$