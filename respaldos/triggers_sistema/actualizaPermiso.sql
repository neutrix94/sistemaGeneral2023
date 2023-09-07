DROP TRIGGER IF EXISTS actualizaPermiso|
DELIMITER $$
CREATE TRIGGER actualizaPermiso
BEFORE UPDATE ON sys_permisos
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;

    IF(new.id_perfil!=old.id_perfil OR new.id_menu!=old.id_menu OR new.ver!=old.ver OR new.modificar!=old.modificar OR new.eliminar!=old.eliminar 
        OR new.nuevo!=old.nuevo OR new.imprimir!=old.imprimir OR new.generar!=old.generar)
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
        IF( store_id = -1 AND new.sincronizar = 1 )
        THEN
            INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
            id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
            SELECT 
                NULL,
                store_id,
                id_sucursal,
                CONCAT('{',
                    '"table_name" : "sys_permisos",',
                    '"action_type" : "update",',
                    '"primary_key" : "id_perfil",',
                    '"primary_key_value" : "', new.id_perfil, '",',
                    '"secondary_key" : "id_menu",',
                    '"secondary_key_value" : "', new.id_menu, '",',
                    '"id_menu" : "', new.id_menu, '",',
                    '"ver" : "', IF( new.ver IS NULL, '', new.ver ), '",',
                    '"modificar" : "', IF( new.modificar IS NULL, '', new.modificar ), '",',
                    '"eliminar" : "', IF( new.eliminar IS NULL, '', new.eliminar ), '",',
                    '"nuevo" : "', IF( new.nuevo IS NULL, '', new.nuevo ), '",',
                    '"imprimir" : "', IF( new.imprimir IS NULL, '', new.imprimir ), '",',
                    '"generar" : "', IF( new.generar IS NULL, '', new.generar ), '",',
                    '"sincronizar" : "0"',
                    '}'
                ),
                NOW(),
                'actualizaPermiso',
                1
            FROM sys_sucursales 
            WHERE id_sucursal >0;
        END IF;
    END IF;
    SET new.sincronizar=1;
END $$