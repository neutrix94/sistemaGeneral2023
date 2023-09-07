DROP TRIGGER IF EXISTS insertaListaPrecio|
DELIMITER $$
CREATE TRIGGER insertaListaPrecio
AFTER INSERT ON ec_precios
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
                '"table_name" : "ec_precios",',
                '"action_type" : "insert",',
                '"primary_key" : "id_precio",',
                '"primary_key_value" : "', new.id_precio, '",',
                '"id_precio" : "', new.id_precio, '",',
                '"fecha" : "', new.fecha, '",',
                '"nombre" : "', new.nombre, '",',
                '"id_usuario" : "', new.id_usuario, '",',
                '"id_equivalente" : "', IF( new.id_equivalente IS NULL, '', new.id_equivalente ), '",',
                '"es_externo" : "', new.es_externo, '",',
                '"ultima_modificacion" : "', new.ultima_modificacion, '",',
                '"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
                '"clave_precio" : "', IF( new.clave_precio IS NULL, '', new.clave_precio ), '",',
                '"sincronizar" : "0",',
                '"grupo_cliente_magento" : "', IF( new.grupo_cliente_magento IS NULL, '', new.grupo_cliente_magento ), '"',
                '}'
            ),
            NOW(),
            'insertaListaPrecio',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$