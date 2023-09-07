DROP TRIGGER IF EXISTS insertaDetallePrecio|
DELIMITER $$
CREATE TRIGGER insertaDetallePrecio
AFTER INSERT ON ec_precios_detalle
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios_detalle',new.id_precio_detalle,1,6,
        CONCAT("INSERT INTO ec_precios_detalle SET ",
                "id_precio_detalle='",new.id_precio_detalle,"',",
                "id_precio='",new.id_precio,"',",
                "de_valor='",new.de_valor,"',",          
                "a_valor='",new.a_valor,"',",       
                "precio_venta='",new.precio_venta,"',",      
                "precio_etiqueta='",new.precio_etiqueta,"',",   
                "id_producto='",new.id_producto,"',",   
                "es_oferta='",new.es_oferta,"',", 
                "alta='",new.alta,"',",      
                "ultima_actualizacion='",new.ultima_actualizacion,"',",
                "sincronizar=0",
                "___UPDATE ec_precios_detalle SET sincronizar=0 WHERE id_precio_detalle='",new.id_precio_detalle,"'"
        ),
        1,0,CONCAT('Se agregó un precio para el producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto)),now(),0,0,'id_precio_detalle'
        FROM sys_sucursales WHERE id_sucursal>0 ;
    END IF;
END $$