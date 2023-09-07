DROP TRIGGER IF EXISTS insertaDetallePaquete|
DELIMITER $$
CREATE TRIGGER insertaDetallePaquete
AFTER INSERT ON ec_paquete_detalle
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquete_detalle',new.id_paquete_detalle,1,1,
        CONCAT("INSERT INTO ec_paquete_detalle SET \n                    id_paquete_detalle=",new.id_paquete_detalle,",",
                    "id_paquete='",new.id_paquete,"',",
                    "id_producto='",new.id_producto,"',",
                    "cantidad_producto='",new.cantidad_producto,"',",
                    "sincronizar=0",
                    "___UPDATE ec_paquete_detalle SET sincronizar=0 WHERE id_paquete_detalle='",new.id_paquete_detalle,"'"
        ),1,0,CONCAT('Se agregó producto a paquete ',new.id_producto),now(),0,0,'id_paquete_detalle'
        FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
    END IF;
END $$