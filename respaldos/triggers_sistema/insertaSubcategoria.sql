DROP TRIGGER IF EXISTS insertaSubcategoria|
DELIMITER $$
CREATE TRIGGER insertaSubcategoria
AFTER INSERT ON ec_subcategoria
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
        '"table_name" : "ec_subcategoria",',
        '"action_type" : "insert",',
        '"primary_key" : "id_subcategoria",',
        '"primary_key_value" : "', new.id_subcategoria, '",',
        '"id_subcategoria" : "', new.id_subcategoria, '",',
        '"nombre" : "', new.nombre, '",',
        '"id_categoria" : "', new.id_categoria, '",',
        '"imagen" : "', new.imagen, '",',
        '"surtir_presentacion" : "', new.surtir_presentacion, '",',
        '"id_magento" : "', new.id_magento, '",',
        '"tiene_ubicacion_prinicipal" : "', new.tiene_ubicacion_prinicipal, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'insertaSubcategoria',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$