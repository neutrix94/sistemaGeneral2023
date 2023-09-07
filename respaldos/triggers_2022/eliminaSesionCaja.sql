DROP TRIGGER IF EXISTS eliminaSesionCaja|
DELIMITER $$
CREATE TRIGGER eliminaSesionCaja
AFTER DELETE ON ec_sesion_caja
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	 	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_sesion_caja',old.id_sesion_caja,3,4,
        CONCAT("DELETE FROM ec_sesion_caja WHERE id_sesion_caja='",old.id_equivalente,"' AND id_sucursal='",old.id_sucursal,"'"),
        0,0,'Se eliminó sesion de caja ',now(),0,0,'id_sesion_caja'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=old.id_sucursal,id_sucursal=-1);
END $$