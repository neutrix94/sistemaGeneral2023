DROP TRIGGER IF EXISTS eliminaColor|
DELIMITER $$
CREATE TRIGGER eliminaColor
AFTER DELETE ON ec_colores
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_colores',old.id_colores,3,6,
        CONCAT("DELETE FROM ec_colores WHERE id_colores='",old.id_colores,"'"),
        0,0,CONCAT('Se eliminó el color ',old.nombre),now(),0,0,'id_colores'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$