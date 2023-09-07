DROP TRIGGER IF EXISTS actualizaCategoria|
DELIMITER $$/**/
CREATE TRIGGER actualizaCategoria/**/
BEFORE UPDATE ON ec_categoria
FOR EACH ROW
BEGIN/**/
  DECLARE store_id INTEGER;/**/
  
  SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;/**/
  IF( store_id = -1 AND new.sincronizar = 1 )
  THEN
    INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
    id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
    SELECT 
      NULL,
      store_id,
      id_sucursal,
      CONCAT('{',
        '"table_name" : "ec_categoria",',
        '"action_type" : "update",',
        '"primary_key" : "id_categoria",',
        '"primary_key_value" : "', new.id_categoria, '",',
        '"nombre" : "', new.nombre, '",',
        '"imagen" : "', new.imagen, '",',
        '"id_magento" : "', new.id_magento, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'actualizaCategoria',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;/**/
  SET new.sincronizar = 1;/**/
END $$/**/