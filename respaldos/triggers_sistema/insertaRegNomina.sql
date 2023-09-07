DROP TRIGGER IF EXISTS insertaRegNomina|
DELIMITER $$
CREATE TRIGGER insertaRegNomina
BEFORE INSERT ON ec_registro_nomina
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
    WHERE table_name = 'ec_registro_nomina'
    AND table_schema = database(); 

    IF( new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_ASIST_', row_id );/*, row_id*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_registro_nomina",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_registro_nomina" : "', new.id_registro_nomina, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora_entrada" : "', new.hora_entrada, '",',
                '"hora_salida" : "', new.hora_salida, '",',
                '"id_empleado" : "', new.id_empleado, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"folio_unico" : "', new.folio_unico, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaRegNomina',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
END $$