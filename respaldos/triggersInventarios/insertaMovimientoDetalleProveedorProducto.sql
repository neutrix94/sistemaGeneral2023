DROP TRIGGER IF EXISTS insertaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER insertaMovimientoDetalleProveedorProducto
BEFORE INSERT ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE final_inventory FLOAT;
	DECLARE folio VARCHAR( 20 );
	IF( new.insertado_por_sincronizacion = 0 )
	THEN
		SET final_inventory = ( ( new.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = new.id_tipo_movimiento ) ) );
		UPDATE ec_inventario_proveedor_producto 
			SET inventario = ( inventario + final_inventory )
		WHERE id_proveedor_producto = new.id_proveedor_producto 
		AND id_almacen = new.id_almacen;
	END IF;	
	SET new.insertado_por_sincronizacion = 0;
	SET new.sincronizar = 1;
END $$