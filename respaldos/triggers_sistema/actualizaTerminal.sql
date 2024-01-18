DROP TRIGGER IF EXISTS actualizaTerminal|
DELIMITER $$
CREATE TRIGGER actualizaTerminal
BEFORE UPDATE ON ec_terminales_integracion_smartaccounts
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
                '"table_name" : "ec_terminales_integracion_smartaccounts",',
                '"action_type" : "update",',
                '"primary_key" : "id_terminal_integracion",',
                '"primary_key_value" : "', new.id_terminal_integracion, '",',
                '"id_caja_cuenta" : "', new.id_caja_cuenta, '",',
                '"nombre_terminal" : "', new.nombre_terminal, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"id_tipo_terminal" : "', new.id_tipo_terminal, '",',
                '"numero_serie_terminal" : "', new.numero_serie_terminal, '",',
                '"store_id" : "', new.store_id, '",',
                '"imprimir_ticket" : "', new.imprimir_ticket, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaTerminal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$