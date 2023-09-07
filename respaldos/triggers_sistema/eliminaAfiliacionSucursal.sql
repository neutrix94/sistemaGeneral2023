DROP TRIGGER IF EXISTS eliminaAfiliacionSucursal|
DELIMITER $$
CREATE TRIGGER eliminaAfiliacionSucursal
AFTER DELETE ON ec_afiliacion_sucursal
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
                '"table_name" : "ec_afiliacion_sucursal",',
                '"action_type" : "delete",',
                '"primary_key" : "id_afiliacion_sucursal",',
                '"primary_key_value" : "', old.id_afiliacion_sucursal, '"',
                '}'
            ),
            NOW(),
            'eliminaAfiliacionSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = old.id_sucursal;
    END IF;
END $$