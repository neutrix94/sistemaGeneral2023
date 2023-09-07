DROP TRIGGER IF EXISTS eliminaTemporalExhibicionProveedorProducto|
DELIMITER $$
CREATE TRIGGER eliminaTemporalExhibicionProveedorProducto
AFTER DELETE ON ec_temporal_exhibicion_proveedor_producto
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE row_id BIGINT;
    DECLARE exhibition_store_id INT( 11 );
    DECLARE exhibition_unique_folio VARCHAR(30);
/*obtiene sucursal y prefijo*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*consulta el folio unico de cabecera exhibicion si es el caso*/ 
	IF( old.id_temporal_exhibicion IS NOT NULL AND old.id_temporal_exhibicion != 0 )
	THEN
		SELECT
			id_sucursal
		INTO
			exhibition_store_id
		FROM ec_temporal_exhibicion
		WHERE id_temporal_exhibicion = old.id_temporal_exhibicion;
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
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTemporalExhibicionProveedorProducto',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, exhibition_store_id, -1 );
END $$