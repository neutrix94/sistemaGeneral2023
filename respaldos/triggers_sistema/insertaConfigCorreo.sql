DROP TRIGGER IF EXISTS insertaConfigCorreo|
DELIMITER $$
CREATE TRIGGER insertaConfigCorreo
AFTER INSERT ON ec_conf_correo
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
                '"table_name" : "ec_conf_correo",',
                '"action_type" : "insert",',
                '"primary_key" : "id_configuracion",',
                '"primary_key_value" : "', new.id_configuracion, '",',
                '"id_configuracion" : "', new.id_configuracion, '",',
                '"smtp_server" : "', new.smtp_server, '",',
                '"puerto" : "', new.puerto, '",',
                '"smtp_user" : "', new.smtp_user, '",',
                '"smtp_pass" : "', new.smtp_pass, '",',
                '"correo_envios" : "', new.correo_envios, '",',
                '"nombre_correo" : "', new.nombre_correo, '",',
                '"iva" : "', new.iva, '",',
                '"ieps" : "', new.ieps, '",',
                '"acceso_de" : "', new.acceso_de, '",',
                '"acceso_a" : "', new.acceso_a, '",',
                '"sincronizar" : "', new.sincronizar, '"',
                '}'
            ),
            NOW(),
            'insertaConfigCorreo',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$