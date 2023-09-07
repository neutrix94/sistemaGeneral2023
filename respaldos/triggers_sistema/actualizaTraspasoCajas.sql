DROP TRIGGER IF EXISTS actualizaTraspasoCajas|
DELIMITER $$
CREATE TRIGGER actualizaTraspasoCajas
AFTER UPDATE ON ec_traspasos_bancos
FOR EACH ROW
BEGIN
	DECLARE folio_nv VARCHAR(20);
	DECLARE observacion_origen VARCHAR(50);
	DECLARE observacion_destino VARCHAR(50);
	DECLARE id_nvo INTEGER(11);

	SELECT CONCAT('TRASP',prefijo,id_nvo) INTO folio_nv FROM sys_sucursales WHERE id_sucursal=new.id_sucursal LIMIT 1;

	SELECT CONCAT('Salida por traspaso a caja ',nombre) INTO observacion_origen FROM ec_caja_o_cuenta WHERE id_caja_cuenta=new.id_banco_origen;

	SELECT CONCAT('Entrada por traspaso de caja ',nombre) INTO observacion_destino FROM ec_caja_o_cuenta WHERE id_caja_cuenta=new.id_banco_destino;

	UPDATE ec_movimiento_banco SET monto=new.monto,observaciones=observacion_origen,id_caja=new.id_banco_origen,id_usuario_modifica=new.id_usuario 
	WHERE id_traspaso_banco=new.id_traspaso_banco AND id_concepto=6;

	UPDATE ec_movimiento_banco SET monto=new.monto,observaciones=observacion_destino,id_caja=new.id_banco_destino,id_usuario_modifica=new.id_usuario 
	WHERE id_traspaso_banco=new.id_traspaso_banco AND id_concepto=5;
END $$