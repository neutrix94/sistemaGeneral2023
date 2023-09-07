DROP TRIGGER IF EXISTS `insertaMovimientosTransferencia`;
DELIMITER $$
CREATE TRIGGER insertaMovimientosTransferencia
AFTER UPDATE ON ec_transferencias
FOR EACH ROW
BEGIN
DECLARE idTransfer INT(11);
DECLARE estado INT(11);
DECLARE movAlmacen INT(11);
DECLARE sucActual INT(11);

SELECT id_sucursal INTO sucActual FROM sys_sucursales WHERE acceso=1;

SET idTransfer=old.id_transferencia;
SET estado=new.id_estado;

	IF(new.id_estado=3 AND new.id_estado!=old.id_estado AND old.id_sucursal_origen=sucActual)
	THEN
		INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
		SELECT 6,t.id_usuario, t.id_sucursal_origen, NOW(), NOW(), 'SALIDA DE TRANSFERENCIA', -1, -1, '', -1,t.id_transferencia, t.id_almacen_origen
		FROM ec_transferencias t where t.id_transferencia=idTransfer;
				
		SELECT MAX(id_movimiento_almacen) INTO movAlmacen FROM ec_movimiento_almacen;		
		
		UPDATE ec_transferencia_productos SET cantidad_salida=cantidad, cantidad_salida_pres=cantidad_presentacion WHERE id_transferencia=idTransfer;

		INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida, id_pedido_detalle, id_oc_detalle)
		SELECT movAlmacen,tP.id_producto_or,tP.cantidad,tP.cantidad,-1,-1
		FROM ec_transferencia_productos tP	
		WHERE tP.id_transferencia=idTransfer;
	END IF;	
	
	IF(new.id_estado=6 AND new.id_estado!=old.id_estado AND old.id_sucursal_destino=sucActual)
	THEN
		INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
		SELECT 5,t.id_usuario, t.id_sucursal_destino, NOW(), NOW(), 'ENTRADA DE TRANSFERENCIA', -1, -1, '', -1,t.id_transferencia, t.id_almacen_destino
		FROM ec_transferencias t where t.id_transferencia=idTransfer;
				
		SELECT MAX(id_movimiento_almacen) INTO movAlmacen FROM ec_movimiento_almacen;		

		INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida, id_pedido_detalle, id_oc_detalle)
		SELECT movAlmacen,tP.id_producto_or,tP.cantidad,tP.cantidad,-1,-1
		FROM ec_transferencia_productos tP	
		WHERE tP.id_transferencia=idTransfer;
	END IF;
END $$
DELIMITER ;