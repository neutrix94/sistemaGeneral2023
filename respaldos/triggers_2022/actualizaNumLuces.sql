DROP TRIGGER IF EXISTS actualizaNumLuces|
DELIMITER $$
CREATE TRIGGER actualizaNumLuces
BEFORE UPDATE ON ec_numero_luces
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
 
   	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
   	THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_numero_luces',new.id_numero_luces,2,6,
       CONCAT("UPDATE ec_numero_luces SET ",
               "nombre='",new.nombre,"',",
               "id_categoria='",new.id_categoria,"',",
               "id_magento='",new.id_magento,"',",
               "sincronizar=0 WHERE id_numero_luces='",new.id_numero_luces,"'"
       ),
       0,0,CONCAT('Se actualizo el numero de luces ',new.nombre),now(),0,0,'id_numero_luces'
       FROM sys_sucursales WHERE id_sucursal>0;
   	END IF;
   SET new.sincronizar=1;
END $$