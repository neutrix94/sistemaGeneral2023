DROP TRIGGER IF EXISTS insertaMaquila|
DELIMITER $$
CREATE TRIGGER insertaMaquila
BEFORE INSERT ON ec_maquila
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );

    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
/*obtiene el siguiente id*/
    SELECT 
        auto_increment into row_id
    FROM information_schema.tables
    WHERE table_name = 'ec_maquila'
    AND table_schema = database();

    IF( new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_MAQUILA_', row_id );/*, row_id*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_maquila",',
                '"action_type" : "insert",',
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
            'insertaMaquila',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
END $$