DROP TRIGGER IF EXISTS actualizaColor|
DELIMITER $$
CREATE TRIGGER actualizaColor
BEFORE UPDATE ON ec_colores
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
 
   	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
   	THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_colores',new.id_colores,2,6,
       CONCAT("UPDATE ec_colores SET ",
               "nombre='",new.nombre,"', ",
               "id_categoria='",new.id_categoria,"', ",
               "id_magento='",new.id_magento,"', ",
               "sincronizar=0 WHERE id_colores='",new.id_colores,"'"
       ),
       0,0,CONCAT('Se actualizo el color ',old.nombre),now(),0,0,'id_colores'
       FROM sys_sucursales WHERE id_sucursal>0;
   	END IF;
   	SET new.sincronizar=1;
END $$