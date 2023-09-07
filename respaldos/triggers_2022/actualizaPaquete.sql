DROP TRIGGER IF EXISTS actualizaPaquete|
DELIMITER $$
CREATE TRIGGER actualizaPaquete
BEFORE UPDATE ON ec_paquetes
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquetes',new.id_paquete,2,1,
        CONCAT("UPDATE ec_paquetes SET \n                    nombre='",new.nombre,"',",
                    IF(new.imagen IS NULL,"",CONCAT("imagen='",new.imagen,"',")),
                    "descripcion='",new.descripcion,"',",
                    "activo='",new.activo,"',",
                    "ultima_actualizacion='",new.ultima_actualizacion,"',",
                    "sincronizar=0 WHERE id_paquete='",new.id_paquete,"'"
        ),0,0,CONCAT('Se modificó el paquete ',new.nombre),now(),0,0,'id_paquete'
        FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
    END IF;
END $$