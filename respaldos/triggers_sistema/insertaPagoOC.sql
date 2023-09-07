DROP TRIGGER IF EXISTS insertaPagoOC|
DELIMITER $$
CREATE TRIGGER insertaPagoOC
AFTER INSERT ON ec_oc_pagos
FOR EACH ROW
BEGIN
	DECLARE monto_pagado INT(11);
	DECLARE monto_total INT(11);

	SELECT if(ocp.id_oc_pagos IS NULL,0,ROUND(SUM(ocp.monto))) INTO monto_pagado 
	FROM ec_oc_pagos ocp
	LEFT JOIN ec_oc_recepcion oc_re ON ocp.id_oc_recepcion=oc_re.id_oc_recepcion
	WHERE oc_re.id_oc_recepcion=new.id_oc_recepcion;

	SELECT SUM(monto_nota_proveedor-descuento) INTO monto_total FROM ec_oc_recepcion WHERE id_oc_recepcion=new.id_oc_recepcion;

	UPDATE ec_oc_recepcion SET status=IF(ROUND(monto_total)<=ROUND(monto_pagado),3,status) WHERE id_oc_recepcion=new.id_oc_recepcion;
END $$