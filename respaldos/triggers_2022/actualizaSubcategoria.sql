DROP TRIGGER IF EXISTS actualizaSubcategoria|
DELIMITER $$
CREATE TRIGGER actualizaSubcategoria
BEFORE UPDATE ON ec_subcategoria
FOR EACH ROW
BEGIN
DECLARE id_suc INT(11);

   SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros 
    SELECT null,id_suc,id_sucursal,'ec_subcategoria',new.id_subcategoria,2,6,
       CONCAT("UPDATE ec_subcategoria SET ",
           "nombre='",new.nombre,"',",
           "id_categoria='",new.id_categoria,"',",
           "imagen='",new.imagen,"',",
           "surtir_presentacion='",new.surtir_presentacion,"',",
           "id_magento='",new.id_magento,"',"
           "sincronizar=0 WHERE id_subcategoria='",new.id_subcategoria,"'"  
       ),
       0,0,CONCAT('Se actualizó tipo ',new.nombre),now(),0,0,'id_subcategoria'
    FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
  SET new.sincronizar=1;
END $$