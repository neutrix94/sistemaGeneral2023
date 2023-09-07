DROP TRIGGER IF EXISTS actualizaSucursalPaquete|
DELIMITER $$
CREATE TRIGGER actualizaSucursalPaquete
BEFORE UPDATE ON sys_sucursales_paquete
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE pack_unique_folio VARCHAR(30);

    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

    IF( new.sincronizar = 1 )
    THEN
        SELECT folio_unico INTO pack_unique_folio FROM ec_paquetes WHERE id_paquete = new.id_paquete;
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "sys_sucursales_paquete",',
                '"action_type" : "update",',
                '"primary_key" : "id_paquete",',
                '"primary_key_value" : "( SELECT id_paquete FROM ec_paquetes WHERE folio_unico = \'', pack_unique_folio, '\' )",',
                '"secondary_key" : "id_sucursal",',
                '"secondary_key_value" : "', new.id_sucursal,'",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"id_paquete" : "', new.id_paquete, '",',
                '"estado_suc" : "', new.estado_suc, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaSucursalPaquete',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$