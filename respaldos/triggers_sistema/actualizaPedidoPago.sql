DROP TRIGGER IF EXISTS actualizaPedidoPago|
DELIMITER $$
CREATE TRIGGER actualizaPedidoPago
BEFORE UPDATE ON ec_pedido_pagos
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE destinity_store_id INT(11);
    DECLARE sale_unique_folio VARCHAR(30);
    DECLARE teller_session_unique_folio VARCHAR( 30 );
    
    IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND new.sincronizar = 1 )
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
        SELECT folio_unico, id_sucursal INTO sale_unique_folio, destinity_store_id FROM ec_pedidos WHERE id_pedido = new.id_pedido;
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
                '"table_name" : "ec_pedido_pagos",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio, '\' LIMIT 1 )",',
                '"id_tipo_pago" : "', new.id_tipo_pago, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora" : "', new.hora, '",',
                '"monto" : "', new.monto, '",',
                '"referencia" : "', new.referencia, '",',
                '"id_moneda" : "', new.id_moneda, '",',
                '"tipo_cambio" : "', new.tipo_cambio, '",',
                '"id_nota_credito" : "', new.id_nota_credito, '",',
                '"id_cxc" : "', new.id_cxc, '",',
                '"exportado" : "', new.exportado, '",',
                '"es_externo" : "', new.es_externo, '",',
                '"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
                '"id_cajero" : "', new.id_cajero, '",',
                IF( new.id_sesion_caja != 0, 
                    CONCAT( '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",' ),
                    ''
                ),
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaPedidoPago',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$