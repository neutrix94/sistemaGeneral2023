DROP TRIGGER IF EXISTS insertaExclusion|
DELIMITER $$
CREATE TRIGGER insertaExclusion
AFTER INSERT ON ec_exclusiones_transferencia
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);	

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_exclusiones_transferencia',new.id_exclusion_transferencia,1,1,
    	CONCAT("INSERT INTO ec_exclusiones_transferencia SET ",
    			"id_exclusion_transferencia='",new.id_exclusion_transferencia,"',",
				"id_producto='",new.id_producto,"',",	
				"id_sucursal='",new.id_sucursal,"',",	
				"observaciones='",new.observaciones,"',",	
				"fecha='",new.fecha,"',",	
				"hora='",new.hora,"',",
				"sincronizar=0",
				"___UPDATE ec_exclusiones_transferencia SET sincronizar=0 WHERE id_exclusion_transferencia='",new.id_exclusion_transferencia,"'"	
		),
        1,0,CONCAT('Se excluyo el producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto),' de las trasnferencias'),now(),0,0,
        'id_exclusion_transferencia'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$