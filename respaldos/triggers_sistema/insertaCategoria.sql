DROP TRIGGER IF EXISTS insertaCategoria|
DELIMITER $$
CREATE TRIGGER insertaCategoria
AFTER INSERT ON ec_categoria
FOR EACH ROW
BEGIN
  DECLARE store_id INTEGER;
  SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*inserta los factores de estacionalidad de la categoria Oscar 2024-08-29*/
  INSERT INTO ec_factores_estacionalidad_categorias ( id_categoria, id_tipo_factor, factor, sincronizar )
  SELECT
    new.id_categoria,
    id_factor,
    0.0,
    1
  FROM ec_factores_estacionalidad;
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
        '"action_type" : "insert",',
        '"primary_key" : "id_categoria",',
        '"primary_key_value" : "', new.id_categoria, '",',
        '"id_categoria" : "', new.id_categoria, '",',
        '"nombre" : "', new.nombre, '",',
        '"imagen" : "', new.imagen, '",',
        '"id_magento" : "', new.id_magento, '",',
        '"sincronizar" : "0"',
        '}'
      ),
      NOW(),
      'insertaCategoria',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$