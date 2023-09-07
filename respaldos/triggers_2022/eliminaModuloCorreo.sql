DROP TRIGGER IF EXISTS eliminaModuloCorreo|
DELIMITER $$
CREATE TRIGGER eliminaModuloCorreo
AFTER DELETE ON ec_modulos_correo
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_modulos_correo',old.id_modulo_correo,3,6,
        CONCAT("DELETE FROM ec_modulos_correo WHERE id_modulo_correo='",old.id_modulo_correo,"'"),
        0,0,CONCAT('Se eliminó módulo de envío de correos ',old.nombre),now(),0,0,'id_modulo_correo'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$