DROP TRIGGER IF EXISTS actualizaPaquete|
DELIMITER $$
CREATE TRIGGER actualizaPaquete
BEFORE UPDATE ON ec_paquetes
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
   
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    
    IF( new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_paquetes",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"nombre" : "', new.nombre, '",',
                '"imagen" : "', IF( new.imagen IS NULL, '', new.imagen ), '",',
                '"descripcion" : "', new.descripcion, '",',
                '"activo" : "', new.activo, '",',
                '"trans_generada" : "', new.trans_generada, '",',
                '"id_sucursal_creacion" : "', new.id_sucursal_creacion, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaPaquete',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal_creacion, -1 );
    END IF;
    SET new.sincronizar = 1;
END $$