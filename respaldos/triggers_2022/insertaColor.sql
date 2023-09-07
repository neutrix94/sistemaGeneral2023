DROP TRIGGER IF EXISTS insertaColor|
DELIMITER $$
CREATE TRIGGER insertaColor
AFTER INSERT ON ec_colores
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);

  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_colores',new.id_colores,1,6,
       CONCAT("INSERT INTO ec_colores (id_colores, nombre, id_categoria, id_magento, sincronizar) VALUES(",
               "'",new.id_colores,"',",
               "'",new.nombre,"',",
               "'",new.id_categoria,"',",
               "'",new.id_magento,"',",
               "0)",
               "___UPDATE ec_colores SET sincronizar=0 WHERE id_colores='",new.id_colores,"'"
       ),
       1,0,CONCAT('Se agrego el color ',new.nombre),now(),0,0,'id_colores'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
END $$