DROP TRIGGER IF EXISTS actualizaTerminalCajero|
DELIMITER $$
CREATE TRIGGER actualizaTerminalCajero
BEFORE UPDATE ON ec_terminales_cajero_smartaccounts
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
                '"table_name" : "ec_terminales_cajero_smartaccounts",',
                '"action_type" : "update",',
                '"primary_key" : "id_terminal_cajero",',
                '"primary_key_value" : "', new.id_terminal_cajero, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"id_terminal" : "', new.id_terminal, '",',
                '"activo" : "', new.activo, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"alta" : "', new.alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaTerminalCajero',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$