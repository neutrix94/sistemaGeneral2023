DROP TRIGGER IF EXISTS actualizaUsuario|
DELIMITER $$
CREATE TRIGGER actualizaUsuario
BEFORE UPDATE ON sys_users
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "sys_users",',
                '"action_type" : "update",',
                '"primary_key" : "id_usuario",',
                '"primary_key_value" : "', new.id_usuario, '",',
                '"nombre" : "', new.nombre, '",',
                '"apellido_paterno" : "', IF( new.apellido_paterno IS NULL, '', new.apellido_paterno ), '",',
                '"apellido_materno" : "', IF( new.apellido_materno IS NULL, '', new.apellido_materno ), '",',
                '"login" : "', new.login, '",',
                '"telefono" : "', IF( new.telefono IS NULL, '', new.telefono ), '",',
                '"correo" : "', new.correo, '",',
                '"contrasena" : "', new.contrasena, '",',
                '"edad" : "', IF( new.edad IS NULL, '', new.edad ), '",',
                '"fecha_nacimiento" : "', IF( new.fecha_nacimiento IS NULL, '', new.fecha_nacimiento ), '",',
                '"puesto" : "', IF( new.puesto IS NULL, '', new.puesto ), '",',
                '"administrador" : "', new.administrador, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"autorizar_req" : "', new.autorizar_req, '",',
                '"sincroniza" : "', new.sincroniza, '",',
                '"recibe_correo" : "', new.recibe_correo, '",',
                '"vende_mayoreo" : "', new.vende_mayoreo, '",',
                '"pin_descuento" : "', new.pin_descuento, '",',
                '"pago_dia" : "', new.pago_dia, '",',
                '"minimo_horas" : "', new.minimo_horas, '",',
                '"pago_hora" : "', new.pago_hora, '",',
                '"sexo" : "', IF( new.sexo IS NULL, '', new.sexo ), '",',
                '"tipo_perfil" : "', new.tipo_perfil, '",',
                '"codigo_barras_usuario" : "', new.codigo_barras_usuario, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"id_equivalente" : "', new.id_equivalente, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaUsuario',
            1
        FROM sys_sucursales 
        WHERE IF( store_id = -1, id_sucursal > 0, -1 );
    END IF;
    SET new.sincronizar=1;
END $$
