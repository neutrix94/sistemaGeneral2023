DROP TRIGGER IF EXISTS actualizaTemporalExhibicion|
DELIMITER $$
CREATE TRIGGER actualizaTemporalExhibicion
BEFORE UPDATE ON ec_temporal_exhibicion
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE sale_unique_folio VARCHAR(30);

	IF( new.sincronizar = 1 )
	THEN
	/*obtiene sucursal y prefijo*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
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
				'"action_type" : "update",',
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
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTemporalExhibicion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$