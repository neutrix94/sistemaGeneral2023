DROP PROCEDURE IF EXISTS eliminaRegistrosMantenimiento|
DELIMITER $$
CREATE PROCEDURE eliminaRegistrosMantenimiento(IN fecha_eliminar VARCHAR(10))
	
BEGIN

	SELECT date_add(CURRENT_DATE(), INTERVAL (fecha_eliminar*-1) DAY) INTO fecha_eliminar;

/*Eliminamos movimientos_temporales*/
	DELETE FROM ec_movimiento_temporal WHERE fecha<=fecha_eliminar;
/*Eliminamos movimientos_temporales*/
	DELETE FROM ec_pedidos_back WHERE fecha_alta<=CONCAT(fecha_eliminar,' 23:59:59');
/*Eliminamos movimientos_temporales*/
	DELETE FROM ec_registro_nomina WHERE fecha<=fecha_eliminar;
/**/
	DELETE FROM ec_sincronizacion_registros WHERE fecha<=CONCAT(fecha_eliminar,' 23:59:59');
/**/
	DELETE FROM ec_temporal_exhibicion WHERE fecha_alta<=CONCAT(fecha_eliminar,' 23:59:59');
/**/
	DELETE t.* 
	FROM ec_transferencias t 
	LEFT JOIN ec_movimiento_almacen ma ON ma.id_transferencia=t.id_transferencia
	WHERE ma.id_movimiento_almacen IS NULL
	AND t.id_transferencia!=-1
	AND t.fecha<=fecha_eliminar;
/**
	DELETE FROM sys_archivos_descarga WHERE fecha<=fecha_eliminar;
*/
	TRUNCATE Log_almacen_producto;
	TRUNCATE ec_bloques_transferencias_validacion_detalle;
	TRUNCATE ec_bloques_transferencias_validacion;
	TRUNCATE ec_bloques_transferencias_resolucion_escaneos;
	TRUNCATE ec_bloques_transferencias_resolucion_detalle;
	TRUNCATE ec_bloques_transferencias_resolucion;
	TRUNCATE ec_bloques_transferencias_recepcion_detalle;
	TRUNCATE ec_bloques_transferencias_recepcion;

END $$