DROP TRIGGER IF EXISTS insertaCFDI|
DELIMITER $$
CREATE TRIGGER insertaCFDI
BEFORE INSERT ON vf_cfdi
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );
    
    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
    /*obtiene el siguiente id*/
      SELECT 
        auto_increment into row_id
      FROM information_schema.tables
      WHERE table_name = 'vf_cfdi'
      AND table_schema = database();

    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN

        SET new.folio_unico = CONCAT( prefix, '_CFDI_', row_id );
        
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "vf_cfdi",',
                '"action_type" : "insert",',
                '"primary_key" : "id_cfdi",',
                '"primary_key_value" : "', new.id_cfdi, '",',
                '"id_cfdi" : "', new.id_cfdi, '",',
                '"clave" : "', new.clave, '",',
                '"nombre" : "', new.nombre, '",',
                '"orden" : "', new.orden, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaCFDI',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$