DROP TRIGGER IF EXISTS insertaCategoria|
DELIMITER $$
CREATE TRIGGER insertaCategoria
AFTER INSERT ON ec_categoria
FOR EACH ROW
BEGIN
DECLARE id_suc INT(11);
 
   SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT NULL,id_suc,id_sucursal,'ec_categoria',new.id_categoria,1,6,
       CONCAT("INSERT INTO ec_categoria (id_categoria, nombre, imagen, id_magento, sincronizar) VALUES (",
           "'",new.id_categoria,"',",
           "'",new.nombre,"',",            
           "'",new.imagen,"',",
           "'",new.id_magento,"',", 
           "0)",
           "___UPDATE ec_categoria SET sincronizar=0 WHERE id_categoria='",new.id_categoria,"'"
       ),
       1,0,CONCAT('Se agrego familia ',new.nombre),now(),0,0,'id_categoria'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
END $$