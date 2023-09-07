DROP TRIGGER IF EXISTS insertaSubtipo|
DELIMITER $$
CREATE TRIGGER insertaSubtipo
AFTER INSERT ON ec_subtipos
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
        '"table_name" : "ec_subtipos",',
        '"action_type" : "insert",',
        '"primary_key" : "id_subtipos",',
        '"primary_key_value" : "', new.id_subtipos, '",',
        '"id_subtipos" : "', new.id_subtipos, '",',
        '"nombre" : "', new.nombre, '",',
        '"id_tipo" : "', new.id_tipo, '",',
        '"id_magento" : "', new.id_magento, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'insertaSubtipo',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$