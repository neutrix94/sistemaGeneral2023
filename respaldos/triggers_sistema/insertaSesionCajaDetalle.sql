DROP TRIGGER IF EXISTS insertaSesionCajaDetalle|
DELIMITER $$
CREATE TRIGGER insertaSesionCajaDetalle
BEFORE INSERT ON ec_sesion_caja_detalle
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );
    DECLARE session_unique_code VARCHAR( 30 );
    
    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
    SELECT folio_unico INTO session_unique_code FROM ec_sesion_caja WHERE id_sesion_caja = new.id_corte_caja;
/*obtiene el siguiente id*/
    SELECT 
    auto_increment into row_id
    FROM information_schema.tables
    WHERE table_name = 'ec_sesion_caja_detalle'
    AND table_schema = database();
    IF( new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_SCD_', row_id );/*, row_id*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_sesion_caja_detalle",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_corte_caja" : "(SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico=\'', session_unique_code , '\')",',
                '"id_afiliacion" : "', new.id_afiliacion, '",',
                '"id_banco" : "', new.id_banco, '",',
                '"monto" : "', new.monto, '",',
                '"monto_validacion" : "', new.monto_validacion, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaSesionCajaDetalle',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
END $$