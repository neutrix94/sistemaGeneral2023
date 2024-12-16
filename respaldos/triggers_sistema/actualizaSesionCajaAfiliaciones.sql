DROP TRIGGER IF EXISTS actualizaSesionCajaAfiliaciones|
DELIMITER $$
CREATE TRIGGER actualizaSesionCajaAfiliaciones
BEFORE UPDATE ON ec_sesion_caja_afiliaciones
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    IF( new.monto_validacion != old.monto_validacion )
    THEN
        UPDATE ec_movimiento_banco 
            SET monto=new.monto_validacion
        WHERE id_ingreso_corte_caja != -1 
        AND id_ingreso_corte_caja = new.id_sesion_caja_afiliaciones
        AND id_afiliacion = new.id_afiliacion
        AND id_terminal = -1;
    END IF;
END $$