DROP TRIGGER IF EXISTS insertaRecepcionDetalle|
DELIMITER $$
CREATE TRIGGER insertaRecepcionDetalle
AFTER INSERT ON ec_oc_recepcion_detalle
FOR EACH ROW
BEGIN
	DECLARE id_prov INT(11);
	DECLARE id_orden INT(11);
	DECLARE mov_alm INT(11);
	DECLARE total_recibido FLOAT(12,2);
	DECLARE piezas_recibido INTEGER(11);

	SELECT oc_re.id_proveedor,oc_re.id_oc_recepcion INTO id_prov,id_orden
	FROM ec_oc_recepcion oc_re
	WHERE oc_re.id_oc_recepcion=new.id_oc_recepcion LIMIT 1;


	SELECT SUM(piezas_recibidas),SUM(monto) INTO piezas_recibido,total_recibido
	FROM ec_oc_recepcion_detalle WHERE id_oc_recepcion=new.id_oc_recepcion;
	UPDATE ec_oc_recepcion SET monto=total_recibido,piezas_recepcion=piezas_recibido WHERE id_oc_recepcion=new.id_oc_recepcion;


	UPDATE ec_oc_recepcion SET status=(IF((SELECT count(*) FROM ec_oc_recepcion_detalle
	WHERE id_oc_recepcion=new.id_oc_recepcion
	AND es_valido=1 AND precio_pieza='0.00')>0,1,2)) where id_oc_recepcion=new.id_oc_recepcion;

	IF(new.es_valido=1)
	THEN
	SELECT id_movimiento_almacen INTO mov_alm FROM ec_movimiento_almacen WHERE id_orden_compra=new.id_oc_recepcion;
	
	INSERT INTO ec_movimiento_detalle (id_movimiento_almacen_detalle, id_movimiento, id_producto, cantidad, cantidad_surtida,
	id_pedido_detalle, id_oc_detalle, id_proveedor_producto, id_equivalente, sincronizar )
	VALUES(
	null,
	mov_alm,
	new.id_producto,
	new.piezas_recibidas,
	new.piezas_recibidas,
	-1,
	new.id_oc_recepcion_detalle,
	new.id_proveedor_producto,
	'0',
	'1');
	END IF;

	UPDATE ec_productos SET precio_compra=IF(new.precio_pieza>0,new.precio_pieza,precio_compra),precio_venta_mayoreo=new.porcentaje_descuento
	WHERE id_productos=new.id_producto;
END $$