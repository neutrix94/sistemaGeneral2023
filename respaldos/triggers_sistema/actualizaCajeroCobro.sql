DROP TRIGGER IF EXISTS actualizaCajeroCobro|
DELIMITER $$
CREATE TRIGGER actualizaCajeroCobro
BEFORE UPDATE ON ec_cajero_cobros
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE destinity_store_id INT(11);
    /*DECLARE sale_unique_folio VARCHAR(30);*/
    DECLARE teller_session_unique_folio VARCHAR( 30 );
    
    IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND old.folio_unico != '' AND new.folio_unico != '' AND new.sincronizar = 1 )
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
        /*SELECT folio_unico, id_sucursal INTO sale_unique_folio, destinity_store_id FROM ec_cajero_cobros WHERE id_cajero_cobro = new.id_pedido;*/
        IF( new.id_sesion_caja != 0 )
        THEN
            SELECT 
                folio_unico INTO teller_session_unique_folio 
            FROM ec_sesion_caja 
            WHERE id_sesion_caja = new.id_sesion_caja;
        END IF;
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_cajero_cobros",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                IF( new.id_pedido > 0, 
                    CONCAT( '"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', ( SELECT folio_unico FROM ec_pedidos WHERE id_pedido = new.id_pedido ), '\' LIMIT 1 )",' ),
                    ''
                ),
                IF( new.id_devolucion > 0, 
                    CONCAT( '"id_devolucion" : "( SELECT id_devolucion FROM ec_devolucion WHERE folio_unico = \'', ( SELECT folio_unico FROM ec_devolucion WHERE id_devolucion = new.id_devolucion ), '\' LIMIT 1 )",' ),
                    ''
                ),
                '"id_cajero" : "', new.id_cajero, '",',
                '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",',
                '"id_afiliacion" : "', new.id_afiliacion, '",',
                '"id_terminal" : "', new.id_terminal, '",',
                '"id_banco" : "', new.id_banco, '",',
                '"id_tipo_pago" : "', new.id_tipo_pago, '",',
                '"monto" : "', new.monto, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora" : "', new.hora, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaCajeroCobro',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$