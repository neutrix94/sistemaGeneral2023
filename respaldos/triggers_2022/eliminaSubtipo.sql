DROP TRIGGER IF EXISTS eliminaSubtipo|
DELIMITER $$
CREATE TRIGGER eliminaSubtipo
AFTER DELETE ON ec_subtipos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_subtipos',old.id_subtipos,3,6,
        CONCAT("DELETE FROM ec_subtipos WHERE id_subtipos='",old.id_subtipos,"'"),
        0,0,CONCAT('Se eliminó subtipo ',old.nombre),now(),0,0,'id_subtipos'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$