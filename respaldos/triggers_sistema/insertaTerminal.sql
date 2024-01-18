DROP TRIGGER IF EXISTS insertaTerminal|
DELIMITER $$
CREATE TRIGGER insertaTerminal
AFTER INSERT ON ec_terminales_integracion_smartaccounts
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
/*inserta las terminales por sucursal*/
	INSERT INTO ec_terminales_sucursales_smartaccounts ( id_terminal_sucursal, id_terminal, id_sucursal, estado_suc, 
        id_razon_social, sincronizar )
    	SELECT 
    	null,
    	new.id_terminal_integracion,
    	id_sucursal,
    	0,
        -1,
    	1
	FROM sys_sucursales
	WHERE id_sucursal>0;
/*registros de sincronizacion*/
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
                '"table_name" : "ec_terminales_integracion_smartaccounts",',
                '"action_type" : "insert",',
                '"primary_key" : "id_terminal_integracion",',
                '"primary_key_value" : "', new.id_terminal_integracion, '",',
                '"id_terminal_integracion" : "', new.id_terminal_integracion, '",',
                '"id_caja_cuenta" : "', new.id_caja_cuenta, '",',
                '"nombre_terminal" : "', new.nombre_terminal, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"id_tipo_terminal" : "', new.id_tipo_terminal, '",',
                '"numero_serie_terminal" : "', new.numero_serie_terminal, '",',
                '"store_id" : "', new.store_id, '",',
                '"imprimir_ticket" : "', new.imprimir_ticket, '",',
                '"fecha_alta" : "', new.fecha_alta, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaTerminal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$