DROP TRIGGER IF EXISTS eliminaExclusion|
DELIMITER $$
CREATE TRIGGER eliminaExclusion
AFTER DELETE ON ec_exclusiones_transferencia
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
                '"table_name" : "ec_exclusiones_transferencia",',
                '"action_type" : "delete",',
                '"primary_key" : "id_exclusion_transferencia",',
                '"primary_key_value" : "', old.id_exclusion_transferencia, '"',
                '}'
            ),
            NOW(),
            'eliminaExclusion',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$