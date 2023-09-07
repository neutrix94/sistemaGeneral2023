DROP TRIGGER IF EXISTS eliminaTraspasoCajas|
DELIMITER $$
CREATE TRIGGER eliminaTraspasoCajas
AFTER DELETE ON ec_traspasos_bancos
FOR EACH ROW
BEGIN
	DELETE FROM ec_movimiento_banco WHERE id_traspaso_banco=old.id_traspaso_banco;
	
END $$