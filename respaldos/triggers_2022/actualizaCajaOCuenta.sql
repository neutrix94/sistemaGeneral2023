DROP TRIGGER IF EXISTS actualizaCajaOCuenta|
DELIMITER $$
CREATE TRIGGER actualizaCajaOCuenta
BEFORE UPDATE ON ec_caja_o_cuenta
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
  
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_caja_o_cuenta',new.id_caja_cuenta,2,1,
         CONCAT("UPDATE ec_caja_o_cuenta SET ",
                "nombre='",new.nombre,"',",
                "id_tipo_caja='",new.id_tipo_caja,"',",          
                "no_cuenta='",new.no_cuenta,"',",     
                "clave_interna='",new.clave_interna,"',",
                "banco='",new.banco,"',",      
                "activo='",new.activo,"',", 
                "observaciones='",new.observaciones,"',",
                "fecha_alta='",new.fecha_alta,"',",  
                "sincronizar=0 WHERE id_caja_cuenta=",new.id_caja_cuenta
        ),
        0,0,CONCAT('Se actualizó la caja o cuenta ',new.id_caja_cuenta),now(),0,0,'id_caja_cuenta'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$