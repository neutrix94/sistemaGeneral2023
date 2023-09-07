DROP TRIGGER IF EXISTS eliminaSubtipo|
DELIMITER $$
CREATE TRIGGER eliminaSubtipo
AFTER DELETE ON ec_subtipos
FOR EACH ROW
BEGIN
  DECLARE store_id INTEGER;
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
        '"table_name" : "ec_subtipos",',
        '"action_type" : "delete",',
        '"primary_key" : "id_subtipos",',
        '"primary_key_value" : "', old.id_subtipos, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'eliminaSubtipo',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$