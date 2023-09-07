DROP TRIGGER IF EXISTS actualizaEstacionalidad|
DELIMITER $$
CREATE TRIGGER actualizaEstacionalidad
BEFORE UPDATE ON ec_estacionalidad
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
                '"table_name" : "ec_estacionalidad",',
                '"action_type" : "update",',
                '"primary_key" : "id_estacionalidad",',
                '"primary_key_value" : "', new.id_estacionalidad, '",',
                '"nombre" : "', new.nombre, '",',
                '"id_periodo" : "', new.id_periodo, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"es_alta" : "', new.es_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaEstacionalidad',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;
END $$