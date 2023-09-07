DROP TRIGGER IF EXISTS insertaSubcategoria|
DELIMITER $$
CREATE TRIGGER insertaSubcategoria
AFTER INSERT ON ec_subcategoria
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);
  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_subcategoria',new.id_subcategoria,1,6,
       CONCAT("INSERT INTO ec_subcategoria (id_subcategoria, nombre, id_categoria, imagen, ",
           "surtir_presentacion, id_magento, sincronizar) VALUES (",
           "'",new.id_subcategoria,"', ",
           "'",new.nombre,"', ",
           "'",new.id_categoria,"', ",
           "'",new.imagen,"', ",
           "'",new.surtir_presentacion,"', ",
           "'",new.id_magento,"', ",
           "0)",          
           "___UPDATE ec_subcategoria SET sincronizar=0 WHERE id_subcategoria='",new.id_subcategoria,"'"  
       ),
       1,0,CONCAT('Se insertó nuevo tipo ',new.nombre),now(),0,0,'id_subcategoria'
       FROM sys_sucursales WHERE id_sucursal>0;
   END IF;
END $$