DROP TRIGGER IF EXISTS insertaDetallePrecio|
DELIMITER $$
CREATE TRIGGER insertaDetallePrecio
AFTER INSERT ON ec_precios_detalle
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
                '"table_name" : "ec_precios_detalle",',
                '"action_type" : "insert",',
                '"primary_key" : "id_precio_detalle",',
                '"primary_key_value" : "', new.id_precio_detalle, '",',
                '"id_precio_detalle" : "', new.id_precio_detalle, '",',
                '"id_precio" : "', new.id_precio, '",',
                '"de_valor" : "', new.de_valor, '",',
                '"a_valor" : "', new.a_valor, '",',
                '"precio_venta" : "', new.precio_venta, '",',
                '"precio_etiqueta" : "', new.precio_etiqueta, '",',
                '"id_producto" : "', new.id_producto, '",',
                '"es_oferta" : "', new.es_oferta, '",',
                '"precio_anterior" : "', new.precio_anterior, '",',
                '"alta" : "', new.alta, '",',
                '"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaDetallePrecio',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$