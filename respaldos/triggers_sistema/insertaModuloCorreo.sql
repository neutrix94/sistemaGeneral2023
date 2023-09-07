DROP TRIGGER IF EXISTS insertaModuloCorreo|
DELIMITER $$
CREATE TRIGGER insertaModuloCorreo
AFTER INSERT ON ec_modulos_correo
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
                '"table_name" : "ec_modulos_correo",',
                '"action_type" : "insert",',
                '"primary_key" : "id_modulo_correo",',
                '"primary_key_value" : "', new.id_modulo_correo, '",',
                '"id_modulo_correo" : "', new.id_modulo_correo, '",',
                '"tabla_modulo" : "', new.tabla_modulo, '",',
                '"nombre" : "', new.nombre, '",',
                '"activo" : "', new.activo, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaModuloCorreo',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$