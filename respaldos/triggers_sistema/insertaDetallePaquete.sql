DROP TRIGGER IF EXISTS insertaDetallePaquete|
DELIMITER $$
CREATE TRIGGER insertaDetallePaquete
BEFORE INSERT ON ec_paquete_detalle
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    DECLARE prefix VARCHAR(20);
    DECLARE pack_store_id INTEGER;
    DECLARE pack_unique_folio VARCHAR( 30 );
    DECLARE row_id INT( 11 );
    
    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
    
    SELECT 
        folio_unico, id_sucursal_creacion 
        INTO pack_unique_folio, pack_store_id 
    FROM ec_paquetes WHERE id_paquete = new.id_paquete;


    IF( new.sincronizar = 1 )
    THEN
    /*obtiene el siguiente id de ec_paquete_detalle*/
        SELECT 
            auto_increment into row_id
        FROM information_schema.tables
        WHERE table_name = 'ec_paquete_detalle'
        AND table_schema = database();
        
        SET new.folio_unico = CONCAT( prefix, '_PQTDET_', row_id );/*, row_id*/

        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_paquete_detalle",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico ,'",',
                '"id_paquete" : "( SELECT id_paquete FROM ec_paquetes WHERE folio_unico = \'', pack_unique_folio ,'\' )",',
                '"id_producto" : "', new.id_producto, '",',
                '"cantidad_producto" : "', new.cantidad_producto, '",',
                '"folio_unico" : "', new.folio_unico ,'",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaDetallePaquete',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, pack_store_id, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$