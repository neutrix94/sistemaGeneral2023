DROP TRIGGER IF EXISTS actualizaDevolucion|
DELIMITER $$
CREATE TRIGGER actualizaDevolucion
BEFORE UPDATE ON ec_devolucion
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    
    IF( new.sincronizar = 1 AND old.folio_unico IS NOT NULL 
        AND new.folio_unico IS NOT NULL )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_devolucion",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_usuario" : "', new.id_usuario, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                IF( new.fecha IS NOT NULL,
                    CONCAT( '"fecha" : "', new.fecha, '",' ),
                    ''
                ),
                IF( new.hora IS NOT NULL,
                    CONCAT( '"hora" : "', new.hora, '",' ),
                    ''
                ),
                IF( new.folio IS NOT NULL,
                    CONCAT( '"folio" : "', new.folio, '",' ),
                    ''
                ),
                '"es_externo" : "', new.es_externo, '",',
                '"status" : "', new.status, '",',
                IF( new.observaciones IS NOT NULL,
                    CONCAT( '"observaciones" : "', new.observaciones, '",' ),
                    ''
                ),
                '"tipo_sistema" : "', new.tipo_sistema, '",',
                '"id_status_agrupacion" : "', new.id_status_agrupacion, '",',
                IF( new.folio_unico IS NOT NULL,
                    CONCAT( '"folio_unico" : "', new.folio_unico, '",' ),
                    ''
                ),
                '"sincronizar" : "0"'
                '}'
            ),
            NOW(),
            'actualizaDevolucion',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;
END $$