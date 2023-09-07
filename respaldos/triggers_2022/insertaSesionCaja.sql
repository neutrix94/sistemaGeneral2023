DROP TRIGGER IF EXISTS insertaSesionCaja|
DELIMITER $$
CREATE TRIGGER insertaSesionCaja
AFTER INSERT ON ec_sesion_caja
FOR EACH ROW
BEGIN
	DECLARE pref VARCHAR(10);
	DECLARE id_max INTEGER(11);
	DECLARE id_suc INT(11);	  
	DECLARE id_user_eq INT(11);
	DECLARE id_user_eq_2 INT(11);


    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

    SELECT id_equivalente INTO id_user_eq FROM sys_users WHERE id_usuario=new.id_cajero;
    SELECT id_equivalente INTO id_user_eq_2 FROM sys_users WHERE id_usuario=new.id_usuario_verifica;
	IF(new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_sesion_caja',new.id_sesion_caja,1,1,
        CONCAT("INSERT INTO ec_sesion_caja SET ",
                "id_cajero='",id_user_eq,"',",
                "id_sucursal='",new.id_sucursal,"',",          
                "folio='",new.folio,"',",     
                "fecha='",new.fecha,"',",     
                "hora_inicio='",new.hora_inicio,"',",     
                "hora_fin='",new.hora_fin,"',",     
                "total_monto_ventas='",new.total_monto_ventas,"',",     
                "total_monto_validacion='",new.total_monto_validacion,"',",     
                "verificado='",new.verificado,"',",    
                "id_usuario_verifica='",IF(id_user_eq_2 IS NULL, 0, id_user_eq_2),"',",
                "id_equivalente='",new.id_sesion_caja,"',",
                "observaciones='", new.observaciones ,"',",
                "caja_inicio='", new.caja_inicio ,"',",
                "caja_final='", new.caja_final ,"',",
                "sincronizar=0",
                "___UPDATE ec_sesion_caja SET sincronizar=0 WHERE id_equivalente='",new.id_sesion_caja,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        0,1,CONCAT('Se agregó la sesion de caja ',new.folio),now(),0,0,'id_sesion_caja'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
    END IF;
END $$