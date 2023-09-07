DROP TRIGGER IF EXISTS eliminaPedidoDetalle|
DELIMITER $$
CREATE TRIGGER eliminaPedidoDetalle
AFTER DELETE ON ec_pedidos_detalle
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	DECLARE sale_store_id INTEGER;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	SELECT id_sucursal INTO sale_store_id FROM ec_pedidos WHERE id_pedido = old.id_pedido;
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_pedidos_detalle",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaPedidoDetalle',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, sale_store_id, -1 );
END $$