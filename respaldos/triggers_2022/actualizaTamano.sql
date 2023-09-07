DROP TRIGGER IF EXISTS actualizaTamano|
DELIMITER $$
CREATE TRIGGER actualizaTamano
BEFORE UPDATE ON ec_tamanos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

   	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
   	THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_tamanos',new.id_tamanos,2,6,
       	CONCAT("UPDATE ec_tamanos SET ",
               "nombre='",new.nombre,"', ",
               "id_categoria='",new.id_categoria,"', ",
               "id_magento='",new.id_magento,"', ",
               "sincronizar=0 WHERE id_tamanos='",new.id_tamanos,"'"
       ),
       0,0,CONCAT('Se actualizo el tamano ',new.nombre),now(),0,0,'id_tamanos'
       FROM sys_sucursales WHERE id_sucursal>0;
   	END IF;
   	SET new.sincronizar=1;
END $$