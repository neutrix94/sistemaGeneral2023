DROP TRIGGER IF EXISTS eliminaDetallePaquete|
DELIMITER $$
CREATE TRIGGER eliminaDetallePaquete
AFTER DELETE ON ec_paquete_detalle
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND old.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_paquete_detalle',old.id_paquete_detalle,3,1,
        CONCAT("DELETE FROM ec_paquete_detalle WHERE id_paquete_detalle='",old.id_paquete_detalle,"'"),
        0,0,CONCAT('Se eliminó el producto en el paquete ',old.id_producto),now(),0,0,'id_paquete_detalle'
        FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
    END IF;
END $$