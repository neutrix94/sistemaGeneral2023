DROP TRIGGER IF EXISTS insertaListaPrecio|
DELIMITER $$
CREATE TRIGGER insertaListaPrecio
AFTER INSERT ON ec_precios
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios',new.id_precio,1,6,
        CONCAT("INSERT INTO ec_precios SET ",
                "id_precio='",new.id_precio,"',",
                "fecha='",new.fecha,"',",
                "nombre='",new.nombre,"',",            
                "id_usuario='",new.id_usuario,"',",        
                "id_equivalente='",IF(new.id_equivalente IS NULL,0,new.id_equivalente),"',",
                "es_externo='",new.es_externo,"',",                
                "ultima_modificacion='",new.ultima_modificacion,"',",       
                "ultima_actualizacion='",new.ultima_modificacion,"',",      
                "clave_precio='",new.clave_precio,"',",
                "sincronizar=0",
                "___UPDATE ec_precios SET sincronizar=0 WHERE id_precio='",new.id_precio,"'"
        ),
        1,0,CONCAT('Se agregó lista de Precios ',new.nombre),now(),0,0,'id_precio'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$