DROP TRIGGER IF EXISTS actualizaAfiliacionSucursal|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacionSucursal
BEFORE UPDATE ON ec_afiliacion_sucursal
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
                '"table_name" : "ec_afiliacion_sucursal",',
                '"action_type" : "update",',
                '"primary_key" : "id_afiliacion_sucursal",',
                '"primary_key_value" : "', new.id_afiliacion_sucursal, '",',
                '"id_afiliacion" : "', new.id_afiliacion, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"estado_suc" : "', new.estado_suc, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaAfiliacionSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = new.id_sucursal;
    END IF;
    
    SET new.sincronizar=1;
END $$