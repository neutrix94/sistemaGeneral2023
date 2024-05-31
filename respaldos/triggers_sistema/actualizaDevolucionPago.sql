DROP TRIGGER IF EXISTS actualizaDevolucionPago|
DELIMITER $$
CREATE TRIGGER actualizaDevolucionPago
BEFORE UPDATE ON ec_devolucion_pagos
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE return_payment_store INTEGER;
    DECLARE teller_session_unique_folio VARCHAR( 30 );
    
    IF( new.sincronizar = 1 AND ( new.folio_unico IS NOT NULL AND old.folio_unico IS NOT NULL ) )
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
        SELECT id_sucursal INTO return_payment_store FROM ec_devolucion WHERE id_devolucion = new.id_devolucion;
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
            CONCAT( '{',
                '"table_name" : "ec_devolucion_pagos",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_tipo_pago" : "', new.id_tipo_pago, '",',
                '"monto" : "', new.monto, '",',
                '"referencia" : "', new.referencia, '",',
                '"es_externo" : "', new.es_externo, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora" : "', new.hora, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
                IF( new.id_sesion_caja != 0, 
                    CONCAT( '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",' ),
                    ''
                ),
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaDevolucionPago',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, return_payment_store, -1 );
    END IF;
    SET new.sincronizar=1;
END $$