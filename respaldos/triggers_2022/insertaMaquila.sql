DROP TRIGGER IF EXISTS insertaMaquila|
DELIMITER $$
CREATE TRIGGER insertaMaquila
AFTER INSERT ON ec_maquila
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_maquila',new.id_maquila,1,1,
        CONCAT("INSERT INTO ec_maquila SET ",
                "id_maquila=null,",
                "folio='",new.folio,"',",
                "fecha='",new.fecha,"',",         
                "id_usuario='",new.id_usuario,"',",
                "id_producto='",new.id_producto,"',",
                "cantidad='",new.cantidad,"',",
                "id_sucursal='",new.id_sucursal,"',",
                "activa='",new.activa,"',",
                "id_equivalente=",new.id_maquila,",",
                "sincronizar=0",
                "___UPDATE ec_maquila SET sincronizar=0 WHERE id_equivalente='",new.id_maquila,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        0,1,CONCAT('Se agregó maquila ',new.folio),now(),0,0,'id_maquila'
        FROM sys_sucursales WHERE id_sucursal=new.id_sucursal;
    END IF;
    IF(id_suc>0 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,-1,'ec_maquila',new.id_maquila,1,1,CONCAT('Se agregó maquila ',new.folio),now(),0,0);
    END IF;
END $$