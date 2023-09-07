DROP TRIGGER IF EXISTS insertaCabeceraRecOC|
DELIMITER $$
CREATE TRIGGER insertaCabeceraRecOC
AFTER INSERT ON ec_oc_recepcion
FOR EACH ROW
BEGIN
	INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones,
	id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen, status_agrupacion, id_equivalente, ultima_sincronizacion, ultima_actualizacion )
	VALUES( null, 1, new.id_usuario, 1, now(), now(), CONCAT('RECEPCIÓN DE NOTA ',new.folio_referencia_proveedor),
	-1, new.id_oc_recepcion, null, -1, -1, 1, -1, 0, '0000-00-00 00:00:00', now());
END $$