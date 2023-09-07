DROP TRIGGER IF EXISTS eliminaMovimientoAlmacenDetalle|
DELIMITER $$
CREATE TRIGGER eliminaMovimientoAlmacenDetalle
BEFORE DELETE ON ec_movimiento_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
  DECLARE store_id INT;   
  DECLARE id_almacen INT;    
  DECLARE tipo_afecta INT; 
  DECLARE origin_store_id INT;

  SELECT
  ma.id_almacen,
  tm.afecta,
  ma.id_sucursal
  INTO
  id_almacen,
  tipo_afecta,
  origin_store_id
  FROM ec_movimiento_almacen ma
  LEFT JOIN ec_tipos_movimiento tm 
  ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
  WHERE ma.id_movimiento_almacen=old.id_movimiento;



  UPDATE ec_almacen_producto ap
  SET ap.inventario = ( ap.inventario - ( old.cantidad * tipo_afecta ) )
  WHERE ap.id_almacen = id_almacen 
  AND ap.id_producto = old.id_producto;


  IF( old.id_proveedor_producto != '' AND old.id_proveedor_producto IS NOT NULL AND old.id_proveedor_producto != -1 )
  THEN
  DELETE FROM ec_movimiento_detalle_proveedor_producto
  WHERE id_movimiento_almacen_detalle = old.id_movimiento_almacen_detalle
  AND id_proveedor_producto = old.id_proveedor_producto;
  END IF;
/*sincronizacion*/
  IF( old.folio_unico IS NOT NULL )
  THEN
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;

    INSERT INTO sys_sincronizacion_registros_movimientos_almacen ( id_sincronizacion_registro, sucursal_de_cambio,
      id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
      SELECT 
        NULL,
        store_id,
        id_sucursal,
        CONCAT('{',
          '"table_name" : "ec_movimiento_detalle",',
          '"action_type" : "delete",',
          '"primary_key" : "folio_unico",',
          '"primary_key_value" : "', old.folio_unico, '"',
          '}'
        ),
        NOW(),
        'eliminaMovimientoAlmacenDetalle',
        1
      FROM sys_sucursales 
      WHERE id_sucursal = IF( store_id = -1, origin_store_id, -1 );
  END IF;
/**/
END $$