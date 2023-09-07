DROP TRIGGER IF EXISTS actualizaDetallePrecio|
DELIMITER $$
CREATE TRIGGER actualizaDetallePrecio
BEFORE UPDATE ON ec_precios_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
IF(new.id_precio!=old.id_precio OR new.de_valor!=old.de_valor OR new.a_valor!=old.a_valor OR new.precio_venta!=old.precio_venta OR
 new.precio_etiqueta!=old.precio_etiqueta OR new.id_producto!=old.id_producto OR new.es_oferta!=old.es_oferta)
THEN
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios_detalle',new.id_precio_detalle,2,6,
        CONCAT("UPDATE ec_precios_detalle SET ",
                "id_precio='",new.id_precio,"',",
                "de_valor='",new.de_valor,"',",          
                "a_valor='",new.a_valor,"',",       
                "precio_venta='",new.precio_venta,"',",      
                "precio_etiqueta='",new.precio_etiqueta,"',",   
                "id_producto='",new.id_producto,"',",   
                "es_oferta='",new.es_oferta,"',", 
                "alta='",new.alta,"',",      
                "ultima_actualizacion='",new.ultima_actualizacion,"',",
                "sincronizar=0 WHERE id_precio_detalle='",new.id_precio_detalle,"'"
        ),
        0,0,CONCAT('Se actualizó precio del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto)),now(),0,0,'id_precio_detalle'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END IF;
    SET new.sincronizar=1;
END $$