DROP TRIGGER IF EXISTS insertaTemporalExhibicion|
DELIMITER $$
CREATE TRIGGER insertaTemporalExhibicion
BEFORE INSERT ON ec_temporal_exhibicion
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE prefix VARCHAR(20);
    DECLARE row_id BIGINT;
    DECLARE sale_unique_folio VARCHAR(30);

	IF( new.sincronizar = 1 )
	THEN
	/*obtiene sucursal y prefijo*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
	/*obtiene el siguiente id*/
	    SELECT 
	    auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_temporal_exhibicion'
	    AND table_schema = database(); 
	/*genera folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_TEXH_', row_id );/*, row_id*/
    /*consulta el folio unico del pedido si es el caso*/ 
    	IF( new.id_pedido IS NOT NULL AND new.id_pedido != 0 )
    	THEN
    		SELECT
    			folio_unico
    		INTO
    			sale_unique_folio
    		FROM ec_pedidos
    		WHERE id_pedido = new.id_pedido;
    	END IF;   
    /*inserta registro de sincronizacion*/
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_temporal_exhibicion",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"cantidad" : "', new.cantidad, '",',
				'"piezas_exhibidas" : "', new.piezas_exhibidas, '",',
				'"piezas_ya_no_se_exhiben" : "', new.piezas_ya_no_se_exhiben, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"fecha_modificacion" : "', new.fecha_modificacion, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"es_valido" : "', new.es_valido, '",',
				IF( sale_unique_folio IS NOT NULL AND sale_unique_folio != '',
					CONCAT( '"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', 
						sale_unique_folio, '\' LIMIT 1 )",' ),
					''
				),
				'"es_nuevo" : "', new.es_nuevo, '",',
				'"folio_unico" : "',new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaTemporalExhibicion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$