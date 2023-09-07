DROP TRIGGER IF EXISTS actualizaCajaOCuenta|
DELIMITER $$
CREATE TRIGGER actualizaCajaOCuenta
BEFORE UPDATE ON ec_caja_o_cuenta
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
                '"table_name" : "ec_caja_o_cuenta",',
                '"action_type" : "update",',
                '"primary_key" : "id_caja_cuenta",',
                '"primary_key_value" : "', new.id_caja_cuenta, '",',
                '"nombre" : "', new.nombre, '",',
                '"id_tipo_caja" : "', new.id_tipo_caja, '",',
                '"no_cuenta" : "', new.no_cuenta, '",',
                '"clave_interna" : "', new.clave_interna, '",',
                '"banco" : "', new.banco, '",',
                '"activo" : "', new.activo, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaCajaOCuenta',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar=1;
END $$