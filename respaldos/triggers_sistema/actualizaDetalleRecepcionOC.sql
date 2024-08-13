DROP TRIGGER IF EXISTS actualizaDetalleRecepcionOC|
DELIMITER $$
CREATE TRIGGER actualizaDetalleRecepcionOC
BEFORE UPDATE ON ec_oc_recepcion_detalle
FOR EACH ROW
BEGIN
	DECLARE folio_referencia VARCHAR(25);
	DECLARE pendientes INT(11);
	DECLARE total FLOAT(12,2);
	DECLARE subtotal FLOAT(12,2);
	DECLARE total_recibido FLOAT(12,2);
	DECLARE piezas_recibido INTEGER(11);
	DECLARE total_con_redondeo FLOAT(12,2);
	DECLARE monto_recibido_seg_val FLOAT(12,2);
	DECLARE monto_notas_seg_val FLOAT(12,2);
	DECLARE piezas_recibidas_seg_val INTEGER(11);
	DECLARE piezas_nota_seg_val INTEGER(11);

	SELECT folio_referencia_proveedor INTO folio_referencia FROM ec_oc_recepcion WHERE id_oc_recepcion=new.id_oc_recepcion;
	IF(new.precio_pieza!=0)
	THEN
	SELECT COUNT(rd.id_oc_recepcion_detalle) INTO pendientes
	FROM ec_oc_recepcion_detalle rd
	LEFT JOIN ec_oc_recepcion rec ON rd.id_oc_recepcion=rec.id_oc_recepcion
	WHERE rec.id_oc_recepcion=new.id_oc_recepcion AND rd.precio_pieza=0 AND rd.id_oc_recepcion_detalle!=new.id_oc_recepcion_detalle;

	UPDATE ec_oc_recepcion SET status=IF(pendientes<1,2,status) WHERE id_oc_recepcion=new.id_oc_recepcion;
	END IF;

	IF(new.precio_pieza=0)
	THEN
	UPDATE ec_oc_recepcion SET status=1 WHERE id_oc_recepcion=new.id_oc_recepcion;
	END IF;

	

	SET new.monto=(new.precio_pieza*new.piezas_recibidas)-((new.precio_pieza*new.piezas_recibidas)*new.porcentaje_descuento);

/*
	IF(new.piezas_recibidas!=old.piezas_recibidas OR new.id_proveedor_producto!=old.id_proveedor_producto)
	THEN
		UPDATE ec_movimiento_detalle SET cantidad=new.piezas_recibidas,
			cantidad_surtida=new.piezas_recibidas,
			id_proveedor_producto = new.id_proveedor_producto,
			sincronizar = 1
		WHERE id_oc_detalle=new.id_oc_recepcion_detalle;
	END IF;
*/

	SELECT SUM(piezas_recibidas),SUM(monto) INTO piezas_recibido,total_recibido
	FROM ec_oc_recepcion_detalle WHERE id_oc_recepcion=new.id_oc_recepcion AND id_producto!=new.id_producto;

	SET piezas_recibido=(IF(piezas_recibido IS NULL,0,piezas_recibido)+new.piezas_recibidas);
	SET total_recibido=(IF(total_recibido IS NULL,0,total_recibido)+new.monto);

	UPDATE ec_oc_recepcion SET monto=total_recibido,piezas_recepcion=piezas_recibido WHERE id_oc_recepcion=new.id_oc_recepcion;


	SELECT ROUND(monto+monto_redondeo),ROUND(monto_nota_proveedor),piezas_remision,piezas_recepcion
	INTO monto_recibido_seg_val,monto_notas_seg_val,piezas_recibidas_seg_val,piezas_nota_seg_val
	FROM ec_oc_recepcion WHERE id_oc_recepcion=new.id_oc_recepcion;
	IF(monto_recibido_seg_val>0 AND monto_notas_seg_val>0 AND piezas_recibidas_seg_val>0 AND piezas_nota_seg_val>0
	AND monto_recibido_seg_val=monto_notas_seg_val AND piezas_recibidas_seg_val=piezas_nota_seg_val)
	THEN
	UPDATE ec_oc_recepcion SET status=2 WHERE id_oc_recepcion=new.id_oc_recepcion;
	ELSE
	UPDATE ec_oc_recepcion SET status=1 WHERE id_oc_recepcion=new.id_oc_recepcion;
	END IF;
END $$