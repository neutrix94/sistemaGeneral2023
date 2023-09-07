DROP TRIGGER IF EXISTS insertaNumLuces|
DELIMITER $$
CREATE TRIGGER insertaNumLuces
AFTER INSERT ON ec_numero_luces
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);
 
  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_numero_luces',new.id_numero_luces,1,6,
       CONCAT("INSERT INTO ec_numero_luces (id_numero_luces, nombre, id_categoria, id_magento, sincronizar) ",
               "VALUES ('",new.id_numero_luces,"', ",
               "'",new.nombre,"', ",
               "'",new.id_categoria,"', ",
               "'",new.id_magento,"', ",
               "0)",
               "___UPDATE ec_numero_luces SET sincronizar=0 WHERE id_numero_luces='",new.id_numero_luces,"'"
       ),
       1,0,CONCAT('Se agrego el numero de luces ',new.nombre),now(),0,0,'id_numero_luces'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
END $$