DROP TRIGGER IF EXISTS eliminaExclusion|
DELIMITER $$
CREATE TRIGGER eliminaExclusion
AFTER DELETE ON ec_exclusiones_transferencia
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);


    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	
    IF(id_suc=-1)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_exclusiones_transferencia',old.id_exclusion_transferencia,3,1,
    	CONCAT("DELETE FROM ec_exclusiones_transferencia WHERE id_exclusion_transferencia='",old.id_exclusion_transferencia,"'"),
        0,0,CONCAT('Se eliminó la exclusion del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=old.id_producto),' de las trasnferencias'),now(),0,0,
        'id_exclusion_transferencia'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$