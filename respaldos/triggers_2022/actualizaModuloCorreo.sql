DROP TRIGGER IF EXISTS actualizaModuloCorreo|
DELIMITER $$
CREATE TRIGGER actualizaModuloCorreo
BEFORE UPDATE ON ec_modulos_correo
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_modulos_correo',new.id_modulo_correo,2,6,
        CONCAT("UPDATE ec_modulos_correo SET ",
            "tabla_modulo='",new.tabla_modulo,"',",            
            "nombre='",new.nombre,"',",         
            "activo='",new.activo,"',",
            "sincronizar=0 WHERE id_modulo_correo='",new.id_modulo_correo,"'"
        ),
        0,0,CONCAT('Se actualizó módulo de envío de correos ',new.nombre),now(),0,0,'id_modulo_correo'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$