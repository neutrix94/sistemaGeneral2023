DROP TRIGGER IF EXISTS actualizaSucProd|
DELIMITER $$
CREATE TRIGGER actualizaSucProd
BEFORE UPDATE ON sys_sucursales_producto
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    IF(new.id_sucursal!=old.id_sucursal OR new.id_producto!=old.id_producto
        OR new.minimo_surtir!=old.minimo_surtir OR new.estado_suc!=old.estado_suc
        OR new.ubicacion_almacen_sucursal!=old.ubicacion_almacen_sucursal OR new.es_externo!=old.es_externo)
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	   IF( new.sincronizar != 0 )
        THEN
            INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
            id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
            SELECT 
                NULL,
                store_id,
                id_sucursal,
                CONCAT('{',
                    '"table_name" : "sys_sucursales_producto",',
                    '"action_type" : "update",',
                    '"primary_key" : "id_sucursal",',
                    '"primary_key_value" : "', new.id_sucursal, '",',
                    '"secondary_key" : "id_producto",',
                    '"secondary_key_value" : "', new.id_producto, '",',
                    '"id_sucursal" : "', new.id_sucursal, '",',
                    '"id_producto" : "', new.id_producto, '",',
                    '"minimo_surtir" : "', new.minimo_surtir, '",',
                    '"estado_suc" : "', new.estado_suc, '",',
                    '"ubicacion_almacen_sucursal" : "', IF( new.ubicacion_almacen_sucursal IS NULL, '', new.ubicacion_almacen_sucursal ), '",',
                    '"ultima_modificacion" : "', new.ultima_modificacion, '",',
                    '"es_externo" : "', new.es_externo, '",',
                    '"stock_bajo" : "', new.stock_bajo, '",',
                    '"ajuste_realizado" : "', new.ajuste_realizado, '",',
                    '"racion_1" : "', new.racion_1, '",',
                    '"racion_2" : "', new.racion_2, '",',
                    '"racion_3" : "', new.racion_3, '",',
                    '"sincronizar" : "0"',
                    '}'
                ),
                NOW(),
                'actualizaSucProd',
                1
            FROM sys_sucursales 
            WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
        END IF;
    END IF;
    SET new.sincronizar=1;
END $$