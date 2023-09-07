DROP TRIGGER IF EXISTS insertaMovimientoCaja|
DELIMITER $$
CREATE TRIGGER insertaMovimientoCaja
BEFORE INSERT ON ec_movimiento_banco
FOR EACH ROW
BEGIN
	DECLARE id INTEGER(11);
	SELECT (IF(id_movimiento_banco IS NULL OR max(id_movimiento_banco)<0,0,max(id_movimiento_banco))+1) INTO id FROM ec_movimiento_banco;
	IF(id IS NULL OR id='')
	THEN
		SET id=1;
	END IF;
	SET new.folio=CONCAT(DATE_FORMAT(NOW(),'%Y%m%d'),id);
END $$