DROP TRIGGER IF EXISTS actualizaDetalleOC|
DELIMITER $$
CREATE TRIGGER actualizaDetalleOC
AFTER UPDATE ON ec_oc_detalle
FOR EACH ROW
BEGIN
	DECLARE pendientes INT(11);
	SELECT COUNT(id_oc_detalle) INTO pendientes FROM ec_oc_detalle WHERE id_orden_compra=new.id_orden_compra AND cantidad_surtido<cantidad;
	UPDATE ec_ordenes_compra SET id_estatus_oc=IF(pendientes<1,4,id_estatus_oc) WHERE id_orden_compra=new.id_orden_compra;
END $$