DROP TRIGGER IF EXISTS insertaSubtipo|
DELIMITER $$
CREATE TRIGGER insertaSubtipo
AFTER INSERT ON ec_subtipos
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);

  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN

    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_subtipos',new.id_subtipos,1,6,
       CONCAT("INSERT INTO ec_subtipos (id_subtipos, nombre, id_tipo, id_magento, sincronizar) VALUES (",    
               "'",new.id_subtipos,"', ",
               "'",new.nombre,"', ",
               "'",new.id_tipo,"', ",
               "'",new.id_magento,"', ",
               "0)",
               "___UPDATE ec_subtipos SET sincronizar=0 WHERE id_subtipos='",new.id_subtipos,"'"
       ),
       1,0,CONCAT('Se inserto nuevo subtipo ',new.nombre),now(),0,0,'id_subtipos'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
END $$