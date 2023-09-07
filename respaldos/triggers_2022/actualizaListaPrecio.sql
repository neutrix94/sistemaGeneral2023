DROP TRIGGER IF EXISTS actualizaListaPrecio|
DELIMITER $$
CREATE TRIGGER actualizaListaPrecio
BEFORE UPDATE ON ec_precios
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios',new.id_precio,2,6,
        CONCAT("UPDATE ec_precios SET ",
                "fecha='",new.fecha,"',",
                "nombre='",new.nombre,"',",            
                "id_usuario='",new.id_usuario,"',",        
                "id_equivalente='",IF(new.id_equivalente IS NULL,0,new.id_equivalente),"',",
                "es_externo='",new.es_externo,"',",                
                "ultima_modificacion='",new.ultima_modificacion,"',",       
                "ultima_actualizacion='",new.ultima_modificacion,"',", 
                "clave_precio='",new.clave_precio,"',",
                "sincronizar=0 WHERE id_precio='",new.id_precio,"'"
        ),
        0,0,CONCAT('Se actualizó lista de Precios ',new.nombre),now(),0,0,'id_precio'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$