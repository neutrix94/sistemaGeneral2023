DROP TRIGGER IF EXISTS actualizaTerminalSucursal|
DELIMITER $$
CREATE TRIGGER actualizaTerminalSucursal
BEFORE UPDATE ON ec_terminales_sucursales_smartaccounts
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
/*registros de sincronizacion*/
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
                '"table_name" : "ec_terminales_sucursales_smartaccounts",',
                '"action_type" : "update",',
                '"primary_key" : "id_terminal",',
                '"primary_key_value" : "', new.id_terminal, '",',
                '"secondary_key" : "id_sucursal",',
                '"secondary_key_value" : "', new.id_sucursal, '",',
                '"estado_suc" : "', new.estado_suc, '",',
                '"id_razon_social" : "', new.id_razon_social, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaTerminalSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$