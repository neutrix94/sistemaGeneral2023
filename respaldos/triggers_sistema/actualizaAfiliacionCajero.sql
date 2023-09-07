DROP TRIGGER IF EXISTS actualizaAfiliacionCajero|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacionCajero
BEFORE UPDATE ON ec_afiliaciones_cajero
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE equivalente_usuario INT(11);
    DECLARE suc_cajero INT(11);

    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN
        
        SELECT id_equivalente,id_sucursal INTO equivalente_usuario,suc_cajero FROM sys_users WHERE id_usuario=new.id_cajero;

        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_afiliaciones_cajero",',
                '"action_type" : "update",',
                '"primary_key" : "id_afiliacion_cajero",',
                '"primary_key_value" : "', new.id_afiliacion_cajero, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"id_afiliacion" : "', new.id_afiliacion, '",',
                '"no_afiliacion" : "', new.no_afiliacion, '",',
                '"activo" : "', new.activo, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"alta" : "', new.alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaAfiliacionCajero',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = suc_cajero;
    END IF;
    SET new.sincronizar=1;
END $$