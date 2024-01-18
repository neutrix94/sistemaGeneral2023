DROP TRIGGER IF EXISTS actualizaRazonSocialEmisor|
DELIMITER $$
CREATE TRIGGER actualizaRazonSocialEmisor
BEFORE UPDATE ON vf_razones_sociales_emisores
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "vf_razones_sociales_emisores",',
                '"action_type" : "update",',
                '"primary_key" : "id_razon_social",',
                '"primary_key_value" : "', new.id_razon_social, '",',
                '"razon_social" : "', new.razon_social, '",',
                '"rfc" : "', new.rfc, '",',
                '"calle" : "', new.calle, '",',
                '"no_exterior" : "', new.no_exterior, '",',
                '"no_interior" : "', new.no_interior, '",',
                '"colonia" : "', new.colonia, '",',
                '"delegacion" : "', new.delegacion, '",',
                '"id_estado" : "', new.id_estado, '",',
                '"id_pais" : "', new.id_pais, '",',
                '"cp" : "', new.cp, '",',
                '"certificado" : "', new.certificado, '",',
                '"llave_fiel" : "', new.llave_fiel, '",',
                '"contrasena" : "', new.contrasena, '",',
                '"no_certificado" : "', new.no_certificado, '",',
                '"regimen_fiscal" : "', new.regimen_fiscal, '",',
                '"id_tipo_regimen_fiscal" : "', new.id_tipo_regimen_fiscal, '",',
                '"retencion_isr" : "', new.retencion_isr, '",',
                '"descripcion_nota" : "', new.descripcion_nota, '",',
                '"prefijo_folio" : "', new.prefijo_folio, '",',
                '"porcentaje_utilidad" : "', new.porcentaje_utilidad, '",',
                '"alerta_monto_maximo_compras" : "', new.alerta_monto_maximo_compras, '",',
                '"monto_maximo_compras" : "', new.monto_maximo_compras, '",',
                '"alerta_monto_maximo_ventas" : "', new.alerta_monto_maximo_ventas, '",',
                '"monto_maximo_ventas" : "', new.monto_maximo_ventas, '",',
                '"habilitado" : "', new.habilitado, '",',
                '"store_id_netpay" : "', new.store_id_netpay, '",',
                '"endpoint_api" : "', new.endpoint_api, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaRazonSocialEmisor',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$