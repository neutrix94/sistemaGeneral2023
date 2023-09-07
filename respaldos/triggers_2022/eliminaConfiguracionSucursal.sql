DROP TRIGGER IF EXISTS eliminaConfiguracionSucursal|
DELIMITER $$
CREATE TRIGGER eliminaConfiguracionSucursal
AFTER DELETE ON ec_configuracion_sucursal
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_configuracion_sucursal',old.id_configuracion_sucursal,3,4,
    CONCAT("DELETE FROM ec_configuracion_sucursal WHERE id_configuracion_sucursal='",old.id_configuracion_sucursal),
    0,0,'Se eliminó configuracion de sucursal ',now(),0,0,'id_sesion_caja'
    FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=old.id_sucursal,id_sucursal=-1);
	
END $$