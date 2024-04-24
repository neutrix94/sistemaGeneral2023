DROP TRIGGER IF EXISTS insertaTipoBanco|
DELIMITER $$
CREATE TRIGGER insertaTipoBanco
AFTER INSERT ON ec_tipos_bancos
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
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
                '"table_name" : "ec_tipos_bancos",',
                '"action_type" : "insert",',
                '"primary_key" : "id_tipo_banco",',
                '"primary_key_value" : "', new.id_tipo_banco, '",',
                '"id_tipo_banco" : "', new.id_tipo_banco, '",',
                '"endpoint_token" : "', new.endpoint_token, '",',
                '"endpoint_refrescar_token" : "', new.endpoint_refrescar_token, '",',
                '"endpoint_venta" : "', new.endpoint_venta, '",',
                '"endpoint_reimpresion" : "', new.endpoint_reimpresion, '",',
                '"endpoint_cancelacion" : "', new.endpoint_cancelacion, '",',
                '"endpoint_reversado" : "', new.endpoint_reversado, '",',
                '"endpoint_adicional" : "', new.endpoint_adicional, '",',
                '"usuario_api" : "', new.usuario_api, '",',
                '"password_api" : "', new.password_api, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"fecha_actualizacion" : "', new.fecha_actualizacion, '",',
                '"habilitado" : "', new.habilitado, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaTipoBanco',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$