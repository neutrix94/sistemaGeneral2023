DROP TRIGGER IF EXISTS insertaTraspasoCajas|
DELIMITER $$
CREATE TRIGGER insertaTraspasoCajas
BEFORE INSERT ON ec_traspasos_bancos
FOR EACH ROW
BEGIN
	DECLARE folio_nv VARCHAR(20);
	DECLARE observacion_origen VARCHAR(50);
	DECLARE observacion_destino VARCHAR(50);
	DECLARE id_nvo INTEGER(11);

	SELECT IF(id_traspaso_banco IS null OR max(id_traspaso_banco)<0,0,max(id_traspaso_banco))+1 INTO id_nvo FROM ec_traspasos_bancos LIMIT 1;
	IF(id_nvo='' OR id_nvo IS NULL)
	THEN
		SET id_nvo=1;
	END IF;	

	SELECT CONCAT('TRASP',prefijo,id_nvo) INTO folio_nv FROM sys_sucursales WHERE id_sucursal=new.id_sucursal LIMIT 1;

	SELECT CONCAT('Salida por traspaso a caja ',nombre) INTO observacion_origen FROM ec_caja_o_cuenta WHERE id_caja_cuenta=new.id_banco_origen;

	SELECT CONCAT('Entrada por traspaso de caja ',nombre) INTO observacion_destino FROM ec_caja_o_cuenta WHERE id_caja_cuenta=new.id_banco_destino;

	SET new.folio=folio_nv;	

	INSERT INTO ec_movimiento_banco VALUES( null, new.id_banco_origen, -1, 6, new.id_usuario, new.monto, '', now(), -1, id_nvo,
		 -1, observacion_origen, -1, 0, 1);

	INSERT INTO ec_movimiento_banco VALUES( null, new.id_banco_destino, -1, 5, new.id_usuario, new.monto, '', now(), -1, id_nvo,
		 -1, observacion_destino, -1, 0, 1);
END $$