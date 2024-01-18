DROP TRIGGER IF EXISTS insertaTipoRegimenFiscal|
DELIMITER $$
CREATE TRIGGER insertaTipoRegimenFiscal
AFTER INSERT ON vf_tipos_regimenes_fiscales
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    
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
                '"table_name" : "vf_tipos_regimenes_fiscales",',
                '"action_type" : "insert",',
                '"primary_key" : "id_tipo_regimen_fiscal",',
                '"primary_key_value" : "', new.id_tipo_regimen_fiscal, '",',
                '"id_tipo_regimen_fiscal" : "', new.id_tipo_regimen_fiscal, '",',
                '"clave_numerica" : "', new.clave_numerica, '",',
                '"nombre_tipo_regimen_fiscal" : "', new.nombre_tipo_regimen_fiscal, '",',
                '"habilitado" : "', new.habilitado, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaTipoRegimenFiscal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$