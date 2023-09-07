DROP TRIGGER IF EXISTS eliminaMovimientoAlmacenDetalle|
DELIMITER $$
CREATE TRIGGER eliminaMovimientoAlmacenDetalle
BEFORE DELETE ON ec_movimiento_detalle
FOR EACH ROW
BEGIN

  DECLARE id_almacen INT unsigned DEFAULT 0;    
  DECLARE tipo_afecta INT unsigned DEFAULT 1;

  SELECT
  ma.id_almacen,
  tm.afecta
  INTO
  id_almacen,tipo_afecta
  FROM ec_movimiento_almacen ma
  LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
  WHERE ma.id_movimiento_almacen=old.id_movimiento;



  UPDATE ec_almacen_producto pro
  SET pro.inventario = ( pro.inventario - ( old.cantidad * tm.afecta ) )
  WHERE alm.id_almacen = id_almacen 
  AND alm.id_producto = old.id_producto;


  IF( old.id_proveedor_producto != '' AND old.id_proveedor_producto IS NOT NULL AND old.id_proveedor_producto != -1 )
  THEN
  DELETE FROM ec_movimiento_detalle_proveedor_producto
  WHERE id_movimiento_almacen_detalle = old.id_movimiento_almacen_detalle
  AND id_proveedor_producto = old.id_proveedor_producto;
  END IF;
END $$