DROP TRIGGER IF EXISTS eliminaSucursal|
DELIMITER $$
CREATE TRIGGER eliminaSucursal
BEFORE DELETE ON sys_sucursales
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

	DELETE FROM sys_sucursales_producto WHERE sys_sucursales_producto.id_sucursal=old.id_sucursal;

    DELETE FROM ec_estacionalidad WHERE id_sucursal=old.id_sucursal;
   
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
                '"table_name" : "sys_sucursales",',
                '"action_type" : "delete",',
                '"primary_key" : "id_sucursal",',
                '"primary_key_value" : "', old.id_sucursal, '"',
                '}'
            ),
            NOW(),
            'eliminaSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$