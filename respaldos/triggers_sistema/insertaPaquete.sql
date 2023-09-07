DROP TRIGGER IF EXISTS insertaPaquete|
DELIMITER $$
CREATE TRIGGER insertaPaquete
BEFORE INSERT ON ec_paquetes
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );
    SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
/*obtiene el siguiente id*/
    SELECT 
        auto_increment into row_id
    FROM information_schema.tables
    WHERE table_name = 'ec_paquetes'
    AND table_schema = database();
    INSERT INTO sys_sucursales_paquete ( id_sucursal_paquete, id_sucursal, id_paquete, estado_suc, sincronizar )
    SELECT
        null,
        id_sucursal,
        row_id,
        IF( id_sucursal = new.id_sucursal_creacion, 1, 0 ),/*implementacion Oscar 2023 para habilitar el paquete solo en la sucursal donde se crea*/
        1
    FROM sys_sucursales WHERE id_sucursal>0 ORDER BY id_sucursal;
   
    IF( new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_PQT_', row_id );/*, row_id*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_paquetes",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"nombre" : "', new.nombre, '",',
                '"imagen" : "', IF( new.imagen IS NULL, '', new.imagen ), '",',
                '"descripcion" : "', new.descripcion, '",',
                '"activo" : "', new.activo, '",',
                '"trans_generada" : "', new.trans_generada, '",',
                '"id_sucursal_creacion" : "', new.id_sucursal_creacion, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaPaquete',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal_creacion, -1 );
    END IF;
END $$