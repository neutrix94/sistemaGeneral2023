DROP TRIGGER IF EXISTS actualizaPedidoPago|
DELIMITER $$
CREATE TRIGGER actualizaPedidoPago
AFTER UPDATE ON ec_pedido_pagos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
    DECLARE id_pedido_equivalente INT(11);
    DECLARE id_suc_destino INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(new.referencia!=old.referencia)
    THEN
    SELECT id_equivalente,id_sucursal INTO id_pedido_equivalente,id_suc_destino FROM ec_pedidos WHERE id_pedido=new.id_pedido;
	
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_pedido_pagos',new.id_pedido_pago,2,1,
        CONCAT("UPDATE ec_pedido_pagos SET referencia='",new.referencia,"' WHERE id_equivalente='",new.id_pedido_pago,"' AND id_pedido='",id_pedido_equivalente,"'"),
        0,0,CONCAT('Se modificó referencia del pago'),now(),0,0,'id_pedido_pago'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=id_suc_destino,id_sucursal=-1) ORDER BY id_sucursal;
	END IF;
END $$