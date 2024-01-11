DROP TRIGGER IF EXISTS insertaCaracteresEspeciales|
DELIMITER $$
CREATE TRIGGER insertaCaracteresEspeciales
AFTER INSERT ON vf_caracteres_especiales
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
                '"table_name" : "vf_caracteres_especiales",',
                '"action_type" : "insert",',
                '"primary_key" : "id_caracter_especial",',
                '"primary_key_value" : "', new.id_caracter_especial, '",',
                '"id_caracter_especial" : "', new.id_caracter_especial, '",',
                '"caracter" : "', new.caracter, '",',
                '"nombre_caracter" : "', new.nombre_caracter, '",',
                '"codigo_reemplazo" : "', new.codigo_reemplazo, '",',
                '"habilitado" : "', new.habilitado, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaCaracteresEspeciales',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$