DROP TRIGGER IF EXISTS insertaClienteFacturacion|
DELIMITER $$
CREATE TRIGGER insertaClienteFacturacion
AFTER INSERT ON vf_clientes_facturacion
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

	IF( store_id = -1 AND new.sincronizar != 0 )
	THEN
/*inserta el registro de sincronizacion para los sitemas locales*/
		INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "vf_clientes_facturacion",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"nombre" : "', new.nombre, '",',
				'"telefono" : "', new.telefono, '",',
				'"celular" : "', new.celular, '",',
				'"correo" : "', new.correo, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"fecha_ultima_actualizacion" : "', new.fecha_ultima_actualizacion, '",',
				'"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			0,
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
END $$