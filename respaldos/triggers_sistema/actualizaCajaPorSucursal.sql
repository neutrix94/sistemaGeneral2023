DROP TRIGGER IF EXISTS actualizaCajaPorSucursal|
DELIMITER $$
CREATE TRIGGER actualizaCajaPorSucursal
BEFORE UPDATE ON ec_caja_o_cuenta_sucursal
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

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
                '"table_name" : "ec_caja_o_cuenta_sucursal",',
                '"action_type" : "update",',
                '"primary_key" : "id_caja_o_cuenta_sucursal",',
                '"primary_key_value" : "', new.id_caja_o_cuenta_sucursal, '",',
                '"id_caja_o_cuenta" : "', new.id_caja_o_cuenta, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"estado_suc" : "', new.estado_suc, '",',
                '"ultima_modificacion" : "', new.ultima_modificacion, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaCajaPorSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;
END $$