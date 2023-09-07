DROP TRIGGER IF EXISTS actualizaReferenciaResolucion|
DELIMITER $$
CREATE TRIGGER actualizaReferenciaResolucion
BEFORE UPDATE ON ec_transferencia_productos
FOR EACH ROW
BEGIN
	DECLARE idTransfer int(11);
    DECLARE estado int(1);
    SET idTransfer=old.id_transferencia;
    SELECT id_estado INTO estado FROM ec_transferencias WHERE id_transferencia=idTransfer;
    IF(estado=1 AND old.cantidad!=new.cantidad)
    THEN
    	SET new.referencia_resolucion=new.cantidad;
    END IF;
END $$