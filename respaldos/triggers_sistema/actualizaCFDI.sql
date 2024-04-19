DROP TRIGGER IF EXISTS actualizaCFDI|
DELIMITER $$
CREATE TRIGGER actualizaCFDI
BEFORE UPDATE ON vf_cfdi
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    
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
                '"table_name" : "vf_cfdi",',
                '"action_type" : "update",',
                '"primary_key" : "id_cfdi",',
                '"primary_key_value" : "', new.id_cfdi, '",',
                '"clave" : "', new.clave, '",',
                '"nombre" : "', new.nombre, '",',
                '"orden" : "', new.orden, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaCFDI',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$