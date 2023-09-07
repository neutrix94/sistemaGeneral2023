DROP TRIGGER IF EXISTS actualizaAlmacen|
DELIMITER $$
CREATE TRIGGER actualizaAlmacen
BEFORE UPDATE ON ec_almacen
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
                '"table_name" : "ec_almacen",',
                '"action_type" : "update",',
                '"primary_key" : "id_almacen",',
                '"primary_key_value" : "', new.id_almacen, '",',
                '"nombre" : "', new.nombre, '",',
                '"es_almacen" : "', new.es_almacen, '",',
                '"prioridad" : "', new.prioridad, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"es_externo" : "', new.es_externo, '",',
                '"ultima_sincronizacion" : "', new.ultima_sincronizacion, '",',
                '"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaAlmacen',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar=1;
END $$