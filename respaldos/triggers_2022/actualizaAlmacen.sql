DROP TRIGGER IF EXISTS actualizaAlmacen|
DELIMITER $$
CREATE TRIGGER actualizaAlmacen
BEFORE UPDATE ON ec_almacen
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
  
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_almacen',new.id_almacen,2,1,
        CONCAT("UPDATE ec_almacen SET ",
                "nombre='",new.nombre,"',",
                "es_almacen='",new.es_almacen,"',",          
                "prioridad='",new.prioridad,"',",     
                "id_sucursal='",new.id_sucursal,"',",
                "es_externo=",new.es_externo,",",      
                "ultima_sincronizacion='",new.ultima_sincronizacion,"',", 
                "ultima_actualizacion='",new.ultima_actualizacion,"',",  
                "sincronizar=0 WHERE id_almacen='",new.id_almacen,"'"
        ),
        0,0,CONCAT('Se actualizó el almacen ',new.nombre),now(),0,0,'id_almacen'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$