DROP TRIGGER IF EXISTS actualizaSubtipo|
DELIMITER $$
CREATE TRIGGER actualizaSubtipo
BEFORE UPDATE ON ec_subtipos
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);

  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
  THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_subtipos',new.id_subtipos,2,6,
       CONCAT("UPDATE ec_subtipos SET ",    
               "nombre='",new.nombre,"',",
               "id_tipo='",new.id_tipo,"',",
               "id_magento='",new.id_magento,"',",
               "sincronizar=0 WHERE id_subtipos='",new.id_subtipos,"'"
       ),
       0,0,CONCAT('Se actualizó subtipo ',new.nombre),now(),0,0,'id_subtipos'
       FROM sys_sucursales WHERE id_sucursal>0;
  END IF;
  SET new.sincronizar=1;
END $$