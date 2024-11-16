DROP TRIGGER IF EXISTS insertaMovimientoAlmacen|
DELIMITER $$
CREATE TRIGGER insertaMovimientoAlmacen
BEFORE INSERT ON ec_movimiento_almacen
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE folio VARCHAR( 20 );
	IF( new.insertado_por_sincronizacion = '0' )
	THEN
		SET folio = "";
	END IF;
	SET new.insertado_por_sincronizacion = 0;
	SET new.sincronizar=1;
END $$