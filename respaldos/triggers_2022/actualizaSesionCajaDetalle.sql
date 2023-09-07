DROP TRIGGER IF EXISTS actualizaSesionCajaDetalle|
DELIMITER $$
CREATE TRIGGER actualizaSesionCajaDetalle
BEFORE UPDATE ON ec_sesion_caja_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);	  


    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    
    IF(new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_sesion_caja_detalle',new.id_sesion_caja_detalle,2,1,
        CONCAT("UPDATE ec_sesion_caja_detalle SET ",
                "id_corte_caja=(SELECT id_sesion_caja FROM ec_sesion_caja WHERE id_equivalente=",new.id_corte_caja," AND id_sucursal=",new.id_sucursal,"),",
                "id_afiliacion=",new.id_afiliacion,",",
                "id_banco='",new.id_banco,"',",
                "monto='",new.monto,"',",          
                "monto_validacion='",new.monto_validacion,"',",     
                "observaciones='",new.observaciones,"',",     
                "fecha_alta='",new.fecha_alta,"',",
                "id_equivalente=",new.id_sesion_caja_detalle,",",
                "id_sucursal=",new.id_sucursal,","
                "sincronizar=0 WHERE id_equivalente='",new.id_corte_caja,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        0,0,CONCAT('Se actualizo detalle de la sesion de caja '),now(),0,0,'id_sesion_caja_detalle'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
    END IF;
    SET new.sincronizar=1;

    IF(new.monto_validacion!=old.monto_validacion)
    THEN
        UPDATE ec_movimiento_banco SET monto=new.monto_validacion,id_caja=new.id_banco WHERE id_ingreso_corte_caja!=-1 AND id_ingreso_corte_caja=new.id_sesion_caja_detalle;
    END IF;
END $$