DROP TRIGGER IF EXISTS actualizaExclusion|
DELIMITER $$
CREATE TRIGGER actualizaExclusion
BEFORE UPDATE ON ec_exclusiones_transferencia
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_exclusiones_transferencia',new.id_exclusion_transferencia,2,1,
        CONCAT("UPDATE ec_exclusiones_transferencia SET ",
                "id_producto='",new.id_producto,"',",   
                "id_sucursal='",new.id_sucursal,"',",   
                "observaciones='",new.observaciones,"',",   
                "fecha='",new.fecha,"',",   
                "hora='",new.hora,"',",
                "sincronizar=0 WHERE id_exclusion_transferencia='",new.id_exclusion_transferencia,"'"    
        ),
        0,0,CONCAT('Se actualizó la exclusion del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto),' de las trasnferencias'),now(),0,0,
        'id_exclusion_transferencia'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$