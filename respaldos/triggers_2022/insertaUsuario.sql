DROP TRIGGER IF EXISTS insertaUsuario|
DELIMITER $$
CREATE TRIGGER insertaUsuario
AFTER INSERT ON sys_users
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

    IF(new.sincronizar!=0 AND id_suc=-1)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_users',new.id_usuario,1,6,
        CONCAT("INSERT INTO sys_users SET ",
                "id_usuario='",new.id_usuario,"',"
                "nombre='",new.nombre,"',",
                IF(new.apellido_paterno is null,'',CONCAT("apellido_paterno='",new.apellido_paterno,"',")),
                IF(new.apellido_materno is null,'',CONCAT("apellido_materno='",new.apellido_materno,"',")),
                "login='",new.login,"',",
                IF(new.telefono is null,'',CONCAT("telefono='",new.telefono,"',")),
                "correo='",new.correo,"',",
                "contrasena='",new.contrasena,"',",
                IF(new.edad IS null,'',CONCAT("edad='",new.edad,"',")),
                IF(new.fecha_nacimiento IS null,'',CONCAT("fecha_nacimiento='",new.fecha_nacimiento,"',")),
                IF(new.puesto is null,'',CONCAT("puesto='",new.puesto,"',")),
                "administrador='",new.administrador,"',",
                "id_sucursal='",new.id_sucursal,"',",
                "autorizar_req='",new.autorizar_req,"',",
                "sincroniza='",new.sincroniza,"',",
                "recibe_correo='",new.recibe_correo,"',",
                "vende_mayoreo='",new.vende_mayoreo,"',",
                "pin_descuento='",new.pin_descuento,"',",
                "pago_dia='",new.pago_dia,"',",
                "minimo_horas='",new.minimo_horas,"',",
                "pago_hora='",new.pago_hora,"',",
                IF(new.sexo is null,'',CONCAT("sexo='",new.sexo,"',")),
                "tipo_perfil='",new.tipo_perfil,"',",
                "codigo_barras_usuario='",new.codigo_barras_usuario,"',",
                "fecha_alta='",new.fecha_alta,"',",
                "id_equivalente=",new.id_usuario,",",
                "sincronizar=0",
                "___UPDATE sys_users SET sincronizar=0 WHERE id_equivalente='",new.id_usuario,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        1,0,CONCAT('Se agregó el usuario ',new.nombre,' ',new.apellido_paterno,' ',new.apellido_materno),now(),0,0,'id_usuario'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$