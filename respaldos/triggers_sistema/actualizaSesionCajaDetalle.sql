DROP TRIGGER IF EXISTS actualizaSesionCajaDetalle|
DELIMITER $$
CREATE TRIGGER actualizaSesionCajaDetalle
BEFORE UPDATE ON ec_sesion_caja_detalle
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    IF(new.monto_validacion!=old.monto_validacion)
    THEN
        UPDATE ec_movimiento_banco 
            SET monto=new.monto_validacion,
            id_caja=new.id_banco 
        WHERE id_ingreso_corte_caja!=-1 
        AND id_ingreso_corte_caja=new.id_sesion_caja_detalle;
    END IF;

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
                '"table_name" : "ec_sesion_caja_detalle",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
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
            'actualizaSesionCajaDetalle',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$