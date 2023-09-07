DROP TRIGGER IF EXISTS insertaTamano|
DELIMITER $$
CREATE TRIGGER insertaTamano
AFTER INSERT ON ec_tamanos
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
        '"table_name" : "ec_tamanos",',
        '"action_type" : "insert",',
        '"primary_key" : "id_tamanos",',
        '"primary_key_value" : "', new.id_tamanos, '",',
        '"id_tamanos" : "', new.id_tamanos, '",',
        '"nombre" : "', new.nombre, '",',
        '"id_categoria" : "', new.id_categoria, '",',
        '"id_magento" : "', new.id_magento, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'insertaTamano',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$