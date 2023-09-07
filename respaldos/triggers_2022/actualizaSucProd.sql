DROP TRIGGER IF EXISTS actualizaSucProd|
DELIMITER $$
CREATE TRIGGER actualizaSucProd
BEFORE UPDATE ON sys_sucursales_producto
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
IF(new.id_sucursal!=old.id_sucursal OR new.id_producto!=old.id_producto
    OR new.minimo_surtir!=old.minimo_surtir OR new.estado_suc!=old.estado_suc
    OR new.ubicacion_almacen_sucursal!=old.ubicacion_almacen_sucursal OR new.es_externo!=old.es_externo)
THEN
	IF(new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_sucursales_producto',new.id,2,1,
        CONCAT("UPDATE sys_sucursales_producto SET ",
                "id_sucursal='",new.id_sucursal,"',",
                "id_producto='",new.id_producto,"',",           
                "minimo_surtir='",new.minimo_surtir,"',",
                "estado_suc=",new.estado_suc,",",
                IF(new.ubicacion_almacen_sucursal IS NOT NULL,CONCAT("ubicacion_almacen_sucursal='",new.ubicacion_almacen_sucursal,"',"),""),
                "ultima_modificacion='",new.ultima_modificacion,"',",
                "sincronizar=0,",
                "es_externo=",new.es_externo," WHERE id_producto='",new.id_producto,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        0,0,CONCAT('Se modificó la configuración del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto),' en la sucursal ',
            (SELECT nombre FROM sys_sucursales WHERE id_sucursal=new.id_sucursal)),now(),0,0,'id'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
    END IF;
END IF;
    SET new.sincronizar=1;
END $$