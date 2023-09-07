DROP TRIGGER IF EXISTS eliminaSesionCajaDetalle|
DELIMITER $$
CREATE TRIGGER eliminaSesionCajaDetalle
AFTER DELETE ON ec_sesion_caja_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
		INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_sesion_caja_detalle',old.id_sesion_caja_detalle,3,1,
        CONCAT("DELETE FROM ec_sesion_caja_detalle WHERE id_equivalente='",old.id_sesion_caja_detalle,"' AND id_sucursal=",old.id_sucursal),
        0,0,CONCAT('Se elimino el detalle de caja '),now(),0,0,'id_tipo_banco_caja'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=old.id_sucursal,id_sucursal=-1);
END $$