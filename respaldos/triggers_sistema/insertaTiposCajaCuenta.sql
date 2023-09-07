DROP TRIGGER IF EXISTS insertaTiposCajaCuenta|
DELIMITER $$
CREATE TRIGGER insertaTiposCajaCuenta
AFTER INSERT ON ec_tipo_banco_caja
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
                '"table_name" : "ec_tipo_banco_caja",',
                '"action_type" : "insert",',
                '"primary_key" : "id_tipo_banco_caja",',
                '"primary_key_value" : "', new.id_tipo_banco_caja, '",',
                '"id_tipo_banco_caja" : "', new.id_tipo_banco_caja, '",',
                '"nombre" : "', new.nombre, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaTiposCajaCuenta',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$