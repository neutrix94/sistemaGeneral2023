DROP TRIGGER IF EXISTS eliminaPaquete|
DELIMITER $$
CREATE TRIGGER eliminaPaquete
AFTER DELETE ON ec_paquetes
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquetes',old.id_paquete,3,1, 
                    CONCAT("DELETE FROM ec_paquetes WHERE id_paquete=",old.id_paquete)
                    ,0,0,CONCAT('Se eliminó el paquete ',old.nombre),now(),0,0,'id_paquete'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$