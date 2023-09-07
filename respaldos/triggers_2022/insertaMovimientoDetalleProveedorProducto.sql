DROP TRIGGER IF EXISTS insertaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER insertaMovimientoDetalleProveedorProducto
BEFORE INSERT ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
	DECLARE final_inventory FLOAT;
	DECLARE folio VARCHAR( 20 );
	SET final_inventory = ( ( new.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = new.id_tipo_movimiento ) ) );
	UPDATE ec_inventario_proveedor_producto 
		SET inventario = ( inventario + final_inventory)
	WHERE id_proveedor_producto = new.id_proveedor_producto 
	AND id_almacen = new.id_almacen;

	/*IF( new.insertado_por_sincronizacion = '0' )
	THEN
		SELECT 
		    CONCAT( prefijo, '_MDPP_', 
		      (SELECT 
		        IF( MAX( id_movimiento_detalle_proveedor_producto ) <= 0 OR MAX( id_movimiento_detalle_proveedor_producto ) IS NULL, 
		        1,
		         MAX( id_movimiento_detalle_proveedor_producto ) + 1 ) 
		      FROM ec_movimiento_detalle_proveedor_producto 
		      ) 
		    )
		  INTO folio
		FROM sys_sucursales WHERE acceso = 1;

	  	SET new.folio_unico = IF( new.folio_unico = '' OR new.folio_unico IS NULL, folio , new.folio_unico );
	END IF;*/
END $$