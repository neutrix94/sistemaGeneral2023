DROP TRIGGER IF EXISTS insertaProveedor|
DELIMITER $$
CREATE TRIGGER insertaProveedor
AFTER INSERT ON ec_proveedor
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
/*Consulta tipo de sistema*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*inserta registro de sincronizacion si es el caso*/
	IF( store_id = -1 AND new.sincronizar != 0 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_proveedor",',
				'"action_type" : "insert",',
				'"primary_key" : "id_proveedor",',
				'"primary_key_value" : "', new.id_proveedor, '",',
				'"id_proveedor" : "', new.id_proveedor, '",',
				'"nombre_comercial" : "', new.nombre_comercial, '",',
				IF( new.dias_credito IS NULL, '', CONCAT( '"dias_credito" : "', new.dias_credito, '",' ) ),
				IF( new.monto_credito IS NULL, '', CONCAT( '"monto_credito" : "', new.monto_credito, '",' ) ),
				IF( new.rfc IS NULL, '', CONCAT( '"rfc" : "', new.rfc, '",' ) ),
				IF( new.razon_social IS NULL, '', CONCAT( '"razon_social" : "', new.razon_social, '",' ) ),
				IF( new.calle IS NULL, '', CONCAT( '"calle" : "', new.calle, '",' ) ),
				IF( new.no_int IS NULL, '', CONCAT( '"no_int" : "', new.no_int, '",' ) ),
				IF( new.no_ext IS NULL, '', CONCAT( '"no_ext" : "', new.no_ext, '",' ) ),
				IF( new.colonia IS NULL, '', CONCAT( '"colonia" : "', new.colonia, '",' ) ),
				IF( new.del_municipio IS NULL, '', CONCAT( '"del_municipio" : "', new.del_municipio, '",' ) ),
				IF( new.cp IS NULL, '', CONCAT( '"cp" : "', new.cp, '",' ) ),
				'"id_pais" : "', new.id_pais, '",',
				'"id_estado" : "', new.id_estado, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"nombre" : "', new.nombre, '",',
				'"correo" : "', new.correo, '",',
				'"telefono" : "', new.telefono, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaProveedor',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
END $$