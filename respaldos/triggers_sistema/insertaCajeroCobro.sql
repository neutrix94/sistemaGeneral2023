DROP TRIGGER IF EXISTS insertaCajeroCobro|
DELIMITER $$
CREATE TRIGGER insertaCajeroCobro
BEFORE INSERT ON ec_cajero_cobros
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );
    DECLARE sale_unique_folio VARCHAR( 30 );
    DECLARE return_unique_folio VARCHAR( 30 );
    DECLARE teller_session_unique_folio VARCHAR( 30 );
/*obtiene el siguiente id*/
    SELECT 
        auto_increment into row_id
    FROM information_schema.tables
    WHERE table_name = 'ec_cajero_cobros'
    AND table_schema = database();
/*registros de sincronizacion*/
    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_COBRO_', row_id );/*, row_id*/
    /*id_venta*/
        IF( new.id_pedido > 0 )
        THEN
            SELECT
                folio_unico INTO sale_unique_folio
            FROM ec_pedidos
            WHERE id_pedido = new.id_pedido
        END IF;
        IF( new.id_devoluciones > 0 )
        THEN

        END IF;

        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_cajero_cobros",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"" : "', new., '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaCajeroCobro',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$