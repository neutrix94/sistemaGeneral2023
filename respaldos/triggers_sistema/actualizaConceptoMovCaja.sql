DROP TRIGGER IF EXISTS actualizaConceptoMovCaja|
DELIMITER $$
CREATE TRIGGER actualizaConceptoMovCaja
BEFORE UPDATE ON ec_concepto_movimiento
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
                '"table_name" : "ec_concepto_movimiento",',
                '"action_type" : "update",',
                '"primary_key" : "id_concepto_movimiento",',
                '"primary_key_value" : "', new.id_concepto_movimiento, '",',
                '"nombre" : "', new.nombre, '",',
                '"afecta" : "', new.afecta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaConceptoMovCaja',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar=1;
END $$