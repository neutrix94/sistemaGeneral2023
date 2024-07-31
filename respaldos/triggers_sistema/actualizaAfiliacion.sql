DROP TRIGGER IF EXISTS actualizaAfiliacion|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacion
BEFORE UPDATE ON ec_afiliaciones
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
                '"table_name" : "ec_afiliaciones",',
                '"action_type" : "update",',
                '"primary_key" : "id_afiliacion",',
                '"primary_key_value" : "', new.id_afiliacion, '",',
                '"id_banco" : "', new.id_banco, '",',
                '"no_afiliacion" : "', new.no_afiliacion, '",',
                '"id_tipo_terminal" : "', new.id_tipo_terminal, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaAfiliacion',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar = 1;
END $$