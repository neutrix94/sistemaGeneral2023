DROP TRIGGER IF EXISTS eliminaCaracteresEspeciales|
DELIMITER $$
CREATE TRIGGER eliminaCaracteresEspeciales
AFTER DELETE ON vf_caracteres_especiales
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    
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
                '"table_name" : "vf_caracteres_especiales",',
                '"action_type" : "delete",',
                '"primary_key" : "id_caracter_especial",',
                '"primary_key_value" : "', old.id_caracter_especial, '"',
                '}'
            ),
            NOW(),
            'eliminaCaracteresEspeciales',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$