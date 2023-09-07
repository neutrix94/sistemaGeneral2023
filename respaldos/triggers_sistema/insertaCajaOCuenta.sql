DROP TRIGGER IF EXISTS insertaCajaOCuenta|
DELIMITER $$
CREATE TRIGGER insertaCajaOCuenta
AFTER INSERT ON ec_caja_o_cuenta
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    
    INSERT INTO ec_caja_o_cuenta_sucursal 
    SELECT
        null,
        new.id_caja_cuenta,
        id_sucursal,
        0,
        '0000-00-00 00:00:00',
        1
    FROM sys_sucursales WHERE id_sucursal>0;

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
                '"table_name" : "ec_caja_o_cuenta",',
                '"action_type" : "insert",',
                '"primary_key" : "id_caja_cuenta",',
                '"primary_key_value" : "', new.id_caja_cuenta, '",',
                '"id_caja_cuenta" : "', new.id_caja_cuenta, '",',
                '"nombre" : "', new.nombre, '",',
                '"id_tipo_caja" : "', new.id_tipo_caja, '",',
                '"no_cuenta" : "', new.no_cuenta, '",',
                '"clave_interna" : "', new.clave_interna, '",',
                '"banco" : "', new.banco, '",',
                '"activo" : "', new.activo, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaCajaOCuenta',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$