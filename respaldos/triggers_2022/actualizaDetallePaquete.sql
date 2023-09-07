DROP TRIGGER IF EXISTS actualizaDetallePaquete|
DELIMITER $$
CREATE TRIGGER actualizaDetallePaquete
BEFORE UPDATE ON ec_paquete_detalle
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquete_detalle',new.id_paquete_detalle,2,1,
        CONCAT("UPDATE ec_paquete_detalle SET \n                    id_paquete='",new.id_paquete,"',",
                    "id_producto='",new.id_producto,"',",
                    "cantidad_producto='",new.cantidad_producto,"',",
                    "sincronizar=0 WHERE id_paquete_detalle='",new.id_paquete_detalle,"'"
        ),0,0,CONCAT('Se actualizó el producto en el paquete ',new.id_producto),now(),0,0,'id_paquete_detalle'
        FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
    END IF;
END $$