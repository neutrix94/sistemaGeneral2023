DROP TRIGGER IF EXISTS actualizaPermiso|
DELIMITER $$
CREATE TRIGGER actualizaPermiso
BEFORE UPDATE ON sys_permisos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
IF(new.id_perfil!=old.id_perfil OR new.id_menu!=old.id_menu OR new.ver!=old.ver OR new.modificar!=old.modificar OR new.eliminar!=old.eliminar 
    OR new.nuevo!=old.nuevo OR new.imprimir!=old.imprimir OR new.generar!=old.generar)
THEN
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_permisos',new.id_permiso,2,6,
        CONCAT("UPDATE sys_permisos SET ",
                "id_perfil='",new.id_perfil,"',",
                "id_menu='",new.id_menu,"',",
                "ver='",new.ver,"',",
                "modificar='",new.modificar,"',",
                "eliminar='",new.eliminar,"',",
                "nuevo='",new.nuevo,"',",
                "imprimir='",new.imprimir,"',",
                "generar='",new.generar,"',",
                "sincronizar=0 WHERE id_perfil='",new.id_perfil,"' AND id_menu='",new.id_menu,"'"
        ),
        0,0,CONCAT('Actualización de permiso'),now(),0,0,'id_permiso'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END IF;
    SET new.sincronizar=1;
END $$