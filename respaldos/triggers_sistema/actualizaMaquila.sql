DROP TRIGGER IF EXISTS actualizaMaquila|
DELIMITER $$
CREATE TRIGGER actualizaMaquila
BEFORE UPDATE ON ec_maquila
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    
    IF( new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_maquila",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"folio" : "', new.folio, '",',
                '"fecha" : "', new.fecha, '",',
                '"id_usuario" : "', new.id_usuario, '",',
                '"id_producto" : "', new.id_producto, '",',
                '"cantidad" : "', new.cantidad, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"activa" : "', new.activa, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaMaquila',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;
END $$