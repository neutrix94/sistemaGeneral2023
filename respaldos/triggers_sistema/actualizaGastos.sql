DROP TRIGGER IF EXISTS actualizaGastos|
DELIMITER $$
CREATE TRIGGER actualizaGastos
BEFORE UPDATE ON ec_gastos
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE teller_session_unique_folio VARCHAR( 30 );
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( new.sincronizar = 1 )
    THEN
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
                '"table_name" : "ec_gastos",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_usuario" : "', new.id_usuario, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora" : "', new.hora, '",',
                '"id_concepto" : "', new.id_concepto, '",',
                '"monto" : "', new.monto, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                IF( new.id_sesion_caja != 0, 
                    CONCAT( '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",' ),
                    ''
                ),
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaGastos',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;
END $$