DROP TRIGGER IF EXISTS actualizaCategoria|
DELIMITER $$
CREATE TRIGGER actualizaCategoria
BEFORE UPDATE ON ec_categoria
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);
 
   SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_categoria',new.id_categoria,2,6,
       CONCAT("UPDATE ec_categoria SET ",
           "nombre='",new.nombre,"',",            
           "imagen='",new.imagen,"',",            
           "id_magento='",new.id_magento,"',",
           "sincronizar=0 WHERE id_categoria='",new.id_categoria,"'"
       ),
       0,0,CONCAT('Se actualiza la familia ',new.nombre),now(),0,0,'id_categoria'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
  SET new.sincronizar=1;
END $$