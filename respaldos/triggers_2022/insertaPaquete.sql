DROP TRIGGER IF EXISTS insertaPaquete|
DELIMITER $$
CREATE TRIGGER insertaPaquete
AFTER INSERT ON ec_paquetes
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    INSERT INTO sys_sucursales_paquete ( id_sucursal_paquete, id_sucursal, id_paquete, estadO_suc, sincronizar )
    SELECT
        null,
        id_sucursal,
        new.id_paquete,
        IF( id_sucursal = new.id_sucursal_creacion, 1, 0 ),/*implementacion Oscar 2023 para habilitar el paquete solo en la sucursal donde se crea*/
        1
    FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquetes',new.id_paquete,1,1,
        CONCAT("INSERT INTO ec_paquetes SET \n                    id_paquete=",new.id_paquete,",",
                    "nombre='",new.nombre,"',",
                    IF(new.imagen IS NULL,"",CONCAT("imagen='",new.imagen,"',")),
                    "descripcion='",new.descripcion,"',",
                    "activo='",new.activo,"',",
                    "ultima_actualizacion='",new.ultima_actualizacion,"',",
                    "sincronizar=0",
                    "___UPDATE ec_paquetes SET sincronizar=0 WHERE id_paquete='",new.id_paquete,"'"
        ),1,0,CONCAT('Se agregó nuevo paquete ',new.nombre),now(),0,0,'id_paquete'
        FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
    END IF;
END $$