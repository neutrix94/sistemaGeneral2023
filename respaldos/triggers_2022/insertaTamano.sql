DROP TRIGGER IF EXISTS insertaTamano|
DELIMITER $$
CREATE TRIGGER insertaTamano
AFTER INSERT ON ec_tamanos
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);

  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
   
       INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_tamanos',new.id_tamanos,1,6,
       CONCAT("INSERT INTO ec_tamanos (id_tamanos, nombre, id_categoria, id_magento, sincronizar) VALUES(",
               "'",new.id_tamanos,"', ",
               "'",new.nombre,"', ",
               "'",new.id_categoria,"', ",
               "'",new.id_magento,"', ",
               "0)",
               "___UPDATE ec_tamanos SET sincronizar=0 WHERE id_tamanos='",new.id_tamanos,"'"
       ),
       1,0,CONCAT('Se inserto el tamano ',new.nombre),now(),0,0,'id_tamanos'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
END $$