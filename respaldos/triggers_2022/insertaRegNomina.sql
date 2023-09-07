DROP TRIGGER IF EXISTS insertaRegNomina|
DELIMITER $$
CREATE TRIGGER insertaRegNomina
AFTER INSERT ON ec_registro_nomina
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
    DECLARE id_user_eq INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

    SELECT id_equivalente INTO id_user_eq FROM sys_users WHERE id_usuario=new.id_empleado;
	IF(new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_registro_nomina',new.id_registro_nomina,1,4,
        CONCAT("INSERT INTO ec_registro_nomina SET ",
                "id_registro_nomina=null,",
                "fecha='",new.fecha,"',", 
                "hora_entrada='",new.hora_entrada,"',",  
                "hora_salida='",new.hora_salida,"',",   
                "id_empleado='",id_user_eq,"',",   
                "id_sucursal='",new.id_sucursal,"',",
                "fecha_alta='",new.fecha_alta,"',",
                "id_equivalente='",new.id_registro_nomina,"',",
                "sincronizar=0",
                "___UPDATE ec_registro_nomina SET sincronizar=0 WHERE id_equivalente='",new.id_registro_nomina,"' AND id_sucursal='",new.id_sucursal,"'"
            ),
        0,1,'Se insertó registro de nómina',now(),0,0,'id_registro_nomina'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
    END IF;
END $$