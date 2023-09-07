DROP TRIGGER IF EXISTS actualizaMovimientoCaja|
DELIMITER $$
CREATE TRIGGER actualizaMovimientoCaja
BEFORE UPDATE ON ec_movimiento_banco
FOR EACH ROW
BEGIN
	DECLARE cambios VARCHAR(150);
	SET cambios='';

	IF(new.id_caja!=old.id_caja)
	THEN
		SET cambios=CONCAT('Se cambio el movimiento de caja ',old.id_caja,' a ',new.id_caja);
	END IF;

	IF(new.id_concepto!=old.id_concepto)
	THEN
		SET cambios=CONCAT(cambios, ' Se cambio el concepto de ',old.id_concepto,' a ',new.id_concepto);
	END IF;

	IF(new.monto!=old.monto)
	THEN
		SET cambios=CONCAT(cambios, ' Se cambio el monto de ',old.monto,' a ',new.monto);
	END IF;

	IF(cambios!='')
	THEN 
		INSERT INTO ec_bitacora_movimiento_caja VALUES(null,new.id_movimiento_banco,new.id_usuario_modifica,old.monto,new.monto,now(),now(),1);
	END IF;
	SET new.id_usuario_modifica=-1;
END $$