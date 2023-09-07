DROP TRIGGER IF EXISTS insertaAlmacen|
DELIMITER $$
CREATE TRIGGER insertaAlmacen
AFTER INSERT ON ec_almacen
FOR EACH ROW
BEGIN
		DECLARE id_suc INT(11);
		 
	   	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
		IF(id_suc=-1 AND new.sincronizar=1)
		THEN
		    INSERT INTO ec_sincronizacion_registros 
		    ( id_registro_sincronizacion, sucursal_de_cambio, id_sucursal, tabla, id_registro, tipo, id_modulo_sincronizacion,
		    instruccion_sql, id_unico, regresa_id_equivalente, descripcion, fecha, id_equivalente, visto, campo_llave)
		    	SELECT null,id_suc,id_sucursal,'ec_almacen',new.id_almacen,1,1,
		       		CONCAT("INSERT INTO ec_almacen SET ",
		               "id_almacen='", new.id_almacen ,"',",
		               "nombre='", new.nombre ,"',",
		               "es_almacen='", new.es_almacen ,"',",          
		               "prioridad='",new.prioridad ,"',",    
		               "id_sucursal='", new.id_sucursal ,"',",
		               "es_externo=", new.es_externo ,",",      
		               "ultima_sincronizacion='", new.ultima_sincronizacion ,"',",
		               "ultima_actualizacion='", new.ultima_actualizacion ,"',",  
		               "sincronizar=0",
		               "___UPDATE ec_almacen SET sincronizar=0 WHERE id_almacen='", new.id_almacen ,"'"
		       		),
		       		1,0,CONCAT('Se agregó el almacen ', new.nombre ),now(),0,0,'id_almacen'
		        FROM sys_sucursales WHERE id_sucursal>0;
	    END IF;
	   
   		INSERT INTO ec_inventario_proveedor_producto 
   		( id_producto, id_proveedor_producto, id_sucursal, id_almacen, inventario, fecha_registro, ultima_actualizacion )
		SELECT 
			pp.id_producto,
			pp.id_proveedor_producto,
			new.id_sucursal,
			new.id_almacen,
			0,
			NOW(),
			'0000-00-00 00:00:00'
		FROM ec_proveedor_producto pp
		WHERE pp.id_proveedor_producto > 0;
	END $$