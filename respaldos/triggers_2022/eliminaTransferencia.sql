DROP TRIGGER IF EXISTS eliminaTransferencia|
DELIMITER $$
CREATE TRIGGER eliminaTransferencia
BEFORE DELETE ON ec_transferencias
FOR EACH ROW
BEGIN
	DELETE FROM ec_movimiento_almacen WHERE id_transferencia = old.id_transferencia;
END $$