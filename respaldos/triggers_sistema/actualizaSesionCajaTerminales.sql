DROP TRIGGER IF EXISTS actualizaSesionCajaTerminales|
DELIMITER $$
CREATE TRIGGER actualizaSesionCajaTerminales
BEFORE UPDATE ON ec_sesion_caja_terminales
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    IF( new.monto_validacion != old.monto_validacion )
    THEN
        UPDATE ec_movimiento_banco 
            SET monto=new.monto_validacion
        WHERE id_ingreso_corte_caja != -1 
        AND id_ingreso_corte_caja = new.id_sesion_caja_terminales
        AND id_afiliacion = -1
        AND id_terminal = new.id_terminal;
    END IF;
END $$