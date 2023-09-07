DROP TRIGGER IF EXISTS insertaMovimientoAlmacenDetalle|
DELIMITER $$
CREATE TRIGGER insertaMovimientoAlmacenDetalle
AFTER INSERT ON ec_movimiento_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
  DECLARE id_almacen INTEGER(11);
  DECLARE tipo INTEGER(2);
  DECLARE id_tipo INTEGER(2);
  DECLARE es_resolucion INTEGER(1);
  DECLARE sucursal_id INT;
  DECLARE folio VARCHAR( 20 );
  DECLARE folio_unico_movimiento_almacen BIGINT( 20 );

  IF( new.insertado_por_sincronizacion = 0 )
  THEN 
    SELECT
      ma.id_almacen,
      tm.afecta,
      ma.id_tipo_movimiento,
      ma.id_sucursal,
      ma.folio_unico,
      IF(ma.id_transferencia!=-1,t.es_resolucion,0)
      INTO
      id_almacen,
      tipo,
      id_tipo,
      sucursal_id,
      folio_unico_movimiento_almacen,
      es_resolucion
    FROM ec_movimiento_almacen ma
    LEFT JOIN ec_tipos_movimiento tm 
    ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
    LEFT JOIN ec_transferencias t ON t.id_transferencia = ma.id_transferencia
    WHERE ma.id_movimiento_almacen=new.id_movimiento;
/*resetea las raciones*/
    IF(id_almacen=1 AND ( id_tipo=1 OR id_tipo=9 OR id_tipo=14 ) AND es_resolucion = 0 )
    THEN
      UPDATE sys_sucursales_producto SET
        stock_bajo=0,
        ajuste_realizado=0,
        racion_1=0,
        racion_2=0,
        racion_3=0
      WHERE id_producto=new.id_producto;

      DELETE FROM ec_exclusiones_transferencia WHERE id_producto=new.id_producto;
    END IF;

    UPDATE ec_almacen_producto ap
        SET ap.inventario = ap.inventario + ( ( new.cantidad * tipo ) )
    WHERE ap.id_producto = new.id_producto
    AND ap.id_almacen = id_almacen;  

    IF( new.id_proveedor_producto != '' AND new.id_proveedor_producto IS NOT NULL AND new.id_proveedor_producto != -1 )
    THEN
      INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle,
      id_proveedor_producto, cantidad, fecha_registro, id_sucursal, status_agrupacion, id_tipo_movimiento, id_almacen )
      VALUES( NULL, new.id_movimiento_almacen_detalle, new.id_proveedor_producto, new.cantidad, NOW(),
      sucursal_id, -1, id_tipo, id_almacen );
    END IF;
  END IF;
END $$