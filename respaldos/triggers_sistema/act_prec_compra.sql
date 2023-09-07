DROP TRIGGER IF EXISTS act_prec_compra|
DELIMITER $$
CREATE TRIGGER act_prec_compra
AFTER INSERT ON ec_oc_detalle
FOR EACH ROW
BEGIN
	UPDATE ec_productos SET precio_compra=new.precio WHERE id_productos=new.id_producto;
END $$