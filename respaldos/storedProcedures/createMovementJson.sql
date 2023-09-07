DROP PROCEDURE IF EXISTS createMovementJson| 
DELIMITER $$
CREATE PROCEDURE createMovementJson(  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	SET GLOBAL group_concat_max_len = 800000;
	INSERT INTO jsons ( json_id, json, table_name_ )
	SELECT
		NULL,
		CONCAT(
			'{ "id_movimiento_almacen" : "NULL",',
				'"id_tipo_movimiento" : "', ma.id_tipo_movimiento ,'",',
				'"id_usuario" : "', ma.id_usuario ,'",',
				'"id_sucursal" : "', ma.id_sucursal ,'",',
				'"fecha" : "', ma.fecha ,'",',
				'"hora" : "', ma.hora ,'",',
				'"observaciones" : "', ma.observaciones ,'",',
				'"id_pedido" : "', IF( ma.id_pedido IS NOT NULL, ma.id_pedido, 'NULL' ), '",',
				'"id_orden_compra" : "', ma.id_orden_compra ,'",',
				'"lote" : "',  IF( ma.lote IS NOT NULL, ma.lote, 'NULL' ), '",',
				'"id_maquila" : "', ma.id_maquila ,'",',
				'"id_transferencia" : "', ma.id_transferencia ,'",',
				'"id_almacen" : "', ma.id_almacen ,'",',
				'"status_agrupacion" : "', ma.status_agrupacion ,'",',
				'"id_equivalente" : "', ma.id_equivalente ,'",',
				'"ultima_sincronizacion" : "', ma.ultima_sincronizacion ,'",',
				'"ultima_actualizacion" : "', ma.ultima_actualizacion ,'"',
			IF( md.id_movimiento_almacen_detalle IS NOT NULL,
				CONCAT( ', "movimiento_detail" : [',
					GROUP_CONCAT(
						CONCAT( 
							'{ "id_movimiento_almacen_detalle" : "NULL",',
								'"id_tipo_movimiento" : "$_movement_id",',
								'"id_producto" : "', md.id_producto ,'",',
								'"cantidad" : "', md.cantidad ,'",',
								'"cantidad_surtida" : "', md.cantidad_surtida ,'",',
								'"id_pedido_detalle" : "', IF( md.id_pedido_detalle IS NOT NULL, md.id_pedido_detalle , 'NULL' ), '",',
								'"id_oc_detalle" : "', md.id_oc_detalle ,'",',
								'"id_proveedor_producto" : "', IF( md.id_proveedor_producto IS NOT NULL, md.id_proveedor_producto, 'NULL' ) ,'",',
								'"id_equivalente" : "', md.id_equivalente ,'",',
								'"sincronizar" : "', md.sincronizar ,'"',

							'}'
						)
						SEPARATOR ','
					),
					']'
				),
				''
			),
			'}'
		),
		'ec_movimiento_almacen'
	FROM ec_movimiento_almacen ma
	LEFT JOIN ec_movimiento_detalle md
	ON ma.id_movimiento_almacen = md.id_movimiento
	WHERE ma.id_movimiento_almacen = 649890
	GROUP BY ma.id_movimiento_almacen
	ORDER BY ma.id_movimiento_almacen DESC
	LIMIT 1;
END $$