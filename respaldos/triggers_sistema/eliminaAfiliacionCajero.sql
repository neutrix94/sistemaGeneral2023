DROP TRIGGER IF EXISTS eliminaAfiliacionCajero|
DELIMITER $$
CREATE TRIGGER eliminaAfiliacionCajero
AFTER DELETE ON ec_afiliaciones_cajero
FOR EACH ROW
BEGIN
	DECLARE store_id INT(11);
    DECLARE suc_cajero INT(11);
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 )
    THEN

        SELECT id_sucursal INTO suc_cajero FROM sys_users WHERE id_usuario=old.id_cajero;
    
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_afiliaciones_cajero",',
                '"action_type" : "delete",',
                '"primary_key" : "id_afiliacion_cajero",',
                '"primary_key_value" : "', old.id_afiliacion_cajero, '"'
                '}'
            ),
            NOW(),
            'eliminaAfiliacionCajero',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = suc_cajero;
    END IF;
END $$