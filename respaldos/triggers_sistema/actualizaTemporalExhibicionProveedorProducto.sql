DROP TRIGGER IF EXISTS actualizaTemporalExhibicionProveedorProducto|
DELIMITER $$
CREATE TRIGGER actualizaTemporalExhibicionProveedorProducto
BEFORE UPDATE ON ec_temporal_exhibicion_proveedor_producto
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE row_id BIGINT;
    DECLARE exhibition_store_id INT( 11 );
    DECLARE exhibition_unique_folio VARCHAR(30);

	IF( new.sincronizar = 1 )
	THEN
	/*obtiene sucursal y prefijo*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    /*consulta el folio unico de cabecera exhibicion si es el caso*/ 
    	IF( new.id_temporal_exhibicion IS NOT NULL AND new.id_temporal_exhibicion != 0 )
    	THEN
    		SELECT
    			folio_unico,
    			id_sucursal
    		INTO
    			exhibition_unique_folio,
    			exhibition_store_id
    		FROM ec_temporal_exhibicion
    		WHERE id_temporal_exhibicion = new.id_temporal_exhibicion;
    	END IF; 
    /*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_temporal_exhibicion_proveedor_producto",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( exhibition_unique_folio != NULL,
					CONCAT( '"id_temporal_exhibicion" : "( SELECT id_temporal_exhibicion FROM ec_temporal_exhibicion WHERE folio_unico = \'', 
						exhibition_unique_folio, '\' LIMIT 1 )",' ),
					''
				),
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"cantidad" : "', new.cantidad, '",',
				'"piezas_exhibidas" : "', new.piezas_exhibidas, '",',
				'"piezas_ya_no_se_exhiben" : "', new.piezas_ya_no_se_exhiben, '",',
				'"piezas_muro" : "', new.piezas_muro, '",',
				'"notas_muro" : "', new.notas_muro, '",',
				'"piezas_colgar" : "', new.piezas_colgar, '",',
				'"notas_colgar" : "', new.notas_colgar, '",',
				'"piezas_adicional" : "', new.piezas_adicional, '",',
				'"notas_adicionales" : "', new.notas_adicionales, '",',
				'"resuelto" : "', new.resuelto, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTemporalExhibicionProveedorProducto',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, exhibition_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$