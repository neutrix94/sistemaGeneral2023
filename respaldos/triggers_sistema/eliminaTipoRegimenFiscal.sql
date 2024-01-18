DROP TRIGGER IF EXISTS eliminaTipoRegimenFiscal|
DELIMITER $$
CREATE TRIGGER eliminaTipoRegimenFiscal
AFTER DELETE ON vf_tipos_regimenes_fiscales
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    
    IF( store_id = -1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "vf_tipos_regimenes_fiscales",',
                '"action_type" : "delete",',
                '"primary_key" : "id_tipo_regimen_fiscal",',
                '"primary_key_value" : "', old.id_tipo_regimen_fiscal, '"',
                '}'
            ),
            NOW(),
            'eliminaTipoRegimenFiscal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$