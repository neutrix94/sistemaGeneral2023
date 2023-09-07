DROP TRIGGER IF EXISTS eliminaCorreoDestinatario|
DELIMITER $$
CREATE TRIGGER eliminaCorreoDestinatario
AFTER DELETE ON ec_correo_destinatarios
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
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
                '"table_name" : "ec_correo_destinatarios",',
                '"action_type" : "delete",',
                '"primary_key" : "id_correo_destinatario",',
                '"primary_key_value" : "', old.id_correo_destinatario, '"',
                '}'
            ),
            NOW(),
            'eliminaCorreoDestinatario',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$