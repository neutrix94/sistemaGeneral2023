DROP TRIGGER IF EXISTS actualizaDetallePaquete|
DELIMITER $$
CREATE TRIGGER actualizaDetallePaquete
BEFORE UPDATE ON ec_paquete_detalle
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE pack_store_id INTEGER;
    DECLARE movement_unique_folio VARCHAR( 30 );
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    SELECT folio_unico, id_sucursal_creacion INTO movement_unique_folio, pack_store_id FROM ec_paquetes WHERE id_paquete = new.id_paquete;
    
    IF( new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_paquete_detalle",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico ,'",',
                '"secondary_key" : "id_producto",',
                '"secondary_key_value" : "', new.id_producto, '",',
                '"id_producto" : "', new.id_producto, '",',
                '"cantidad_producto" : "', new.cantidad_producto, '",',
                '"folio_unico" : "', new.folio_unico ,'",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaDetallePaquete',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, pack_store_id, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$