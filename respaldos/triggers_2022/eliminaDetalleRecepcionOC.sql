DROP TRIGGER IF EXISTS eliminaDetalleRecepcionOC|
DELIMITER $$
CREATE TRIGGER eliminaDetalleRecepcionOC
BEFORE DELETE ON ec_oc_recepcion_detalle
FOR EACH ROW
BEGIN
DECLARE total_recibido FLOAT(12,2);
DECLARE piezas_recibido INTEGER(11);
DECLARE id_movimiento_detalle BIGINT;
	

	SELECT id_movimiento_almacen_detalle INTO id_movimiento_detalle 
	FROM ec_movimiento_detalle
	WHERE id_oc_detalle = old.id_oc_recepcion=old.id_oc_recepcion_detalle;
	

	DELETE FROM ec_movimiento_detalle_proveedor_producto 
	WHERE id_movimiento_almacen_detalle = id_movimiento_detalle;

	DELETE FROM ec_movimiento_detalle 
	WHERE id_oc_detalle = old.id_oc_recepcion_detalle;

	SELECT SUM(piezas_recibidas),SUM(monto) INTO piezas_recibido,total_recibido
	FROM ec_oc_recepcion_detalle WHERE id_oc_recepcion=old.id_oc_recepcion;
	
	UPDATE ec_oc_recepcion SET monto=total_recibido,piezas_recepcion=piezas_recibido 
	WHERE id_oc_recepcion=old.id_oc_recepcion;
END $$