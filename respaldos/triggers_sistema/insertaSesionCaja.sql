DROP TRIGGER IF EXISTS insertaSesionCaja|
DELIMITER $$
CREATE TRIGGER insertaSesionCaja
BEFORE INSERT ON ec_sesion_caja
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
    WHERE table_name = 'ec_sesion_caja'
    AND table_schema = database(); 

    IF( new.sincronizar = 1 )
    THEN
        SET new.folio_unico = CONCAT( prefix, '_SC_', row_id );/*, row_id*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_sesion_caja",',
                '"action_type" : "insert",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"folio" : "', new.folio, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora_inicio" : "', new.hora_inicio, '",',
                '"hora_fin" : "', new.hora_fin, '",',
                '"total_monto_ventas" : "', new.total_monto_ventas, '",',
                '"total_monto_validacion" : "', new.total_monto_validacion, '",',
                '"verificado" : "', new.verificado, '",',
                '"id_usuario_verifica" : "', new.id_usuario_verifica, '",',
                '"id_equivalente" : "', new.id_equivalente, '",',
                '"sincronizar" : "0",',
                '"observaciones" : "', new.observaciones, '",',
                '"caja_inicio" : "', new.caja_inicio, '",',
                '"caja_final" : "', new.caja_final, '",',
                '"folio_unico" : "', new.folio_unico, '"',
                '}'
            ),
            NOW(),
            'insertaSesionCaja',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
END $$