DROP TRIGGER IF EXISTS actualizaConfiguracionSistema|
DELIMITER $$
CREATE TRIGGER actualizaConfiguracionSistema
BEFORE UPDATE ON sys_configuracion_sistema
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar=1)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_configuracion_sistema',new.id_configuracion_sistema,2,1,
        CONCAT("UPDATE sys_configuracion_sistema SET ",
                "racionar_transferencias_productos='",new.racionar_transferencias_productos,"',",
                "minimo_agrupar_ma_dia='",new.minimo_agrupar_ma_dia,"',",
                "minimo_agrupar_ma_ano='",new.minimo_agrupar_ma_ano,"',",
                "minimo_agrupar_ma_anteriores='",new.minimo_agrupar_ma_anteriores,"',",
                "minimo_agrupar_vtas_dias='",new.minimo_agrupar_vtas_dias,"',",
                "minimo_agrupar_vtas_ano='",new.minimo_agrupar_vtas_ano,"',",
                "minimo_agrupar_vtas_anteriores='",new.minimo_agrupar_vtas_anteriores,"',",
                "minimo_eliminar_reg_no_usados='",new.minimo_eliminar_reg_no_usados,"',",
                "sincronizar=0 WHERE id_configuracion_sistema='",new.id_configuracion_sistema,"'"
        ),
        1,0,CONCAT('Se modifico la configuracion del sistema'),now(),0,0,'id_configuracion_sistema'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$