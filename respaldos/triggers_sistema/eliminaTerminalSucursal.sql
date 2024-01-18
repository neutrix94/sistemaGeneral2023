DROP TRIGGER IF EXISTS eliminaTerminalSucursal|
DELIMITER $$
CREATE TRIGGER eliminaTerminalSucursal
AFTER DELETE ON ec_terminales_sucursales_smartaccounts
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
                '"table_name" : "ec_terminales_sucursales_smartaccounts",',
                '"action_type" : "delete",',
                '"primary_key" : "id_terminal",',
                '"primary_key_value" : "', old.id_terminal, '"',
                '"secondary_key" : "id_sucursal",',
                '"secondary_key_value" : "', old.id_sucursal, '"'
                '}'
            ),
            NOW(),
            'eliminaTerminalSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$