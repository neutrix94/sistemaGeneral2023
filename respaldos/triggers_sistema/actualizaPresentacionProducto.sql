DROP TRIGGER IF EXISTS actualizaPresentacionProducto|
DELIMITER $$
CREATE TRIGGER actualizaPresentacionProducto
BEFORE UPDATE ON ec_productos_presentaciones
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
                '"table_name" : "ec_productos_presentaciones",',
                '"action_type" : "update",',
                '"primary_key" : "id_producto_presentacion",',
                '"primary_key_value" : "', new.id_producto_presentacion, '",',
                '"id_producto" : "', new.id_producto, '",',
                '"nombre" : "', new.nombre, '",',
                '"cantidad" : "', new.cantidad, '",',
                '"unidad_medida" : "', new.unidad_medida, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaPresentacionProducto',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;

    SET new.sincronizar=1;
END $$