DROP TRIGGER IF EXISTS actualizaSesionCaja|
DELIMITER $$
CREATE TRIGGER actualizaSesionCaja
BEFORE UPDATE ON ec_sesion_caja
FOR EACH ROW
BEGIN
	DECLARE store_id INT(11);	  
	DECLARE id_user_eq INT(11);
	DECLARE id_user_eq_2 INT(11);

	IF(new.verificado=1 AND  new.verificado!=old.verificado)
	THEN
		INSERT INTO ec_movimiento_banco
			SELECT
				null,
				id_banco,
				id_afiliacion,
				1,
				new.id_usuario_verifica,
				monto_validacion,
				'folio',
				now(),
				id_sesion_caja_detalle,
				-1,
				-1,
				observaciones,
				-1,
				0,
				1
			FROM ec_sesion_caja_detalle 
			WHERE id_corte_caja=new.id_sesion_caja;
	END IF;


    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

    IF( new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_sesion_caja",',
                '"action_type" : "update",',
                '"primary_key" : "folio_unico",',
                '"primary_key_value" : "', new.folio_unico, '",',
                '"id_cajero" : "', new.id_cajero, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"folio" : "', new.folio, '",',
                '"fecha" : "', new.fecha, '",',
                '"hora_inicio" : "', new.hora_inicio, '",',
                '"hora_fin" : "', new.hora_fin, '",',
                '"total_monto_ventas" : "', new.total_monto_ventas, '",',
                '"total_monto_validacion" : "', new.total_monto_validacion, '",',
                '"verificado" : "', new.verificado, '",',
                '"id_usuario_verifica" : "', new.id_usuario_verifica, '",',
                '"id_equivalente" : "', new.id_equivalente, '",',
                '"sincronizar" : "0",',
                '"observaciones" : "', new.observaciones, '",',
                '"caja_inicio" : "', new.caja_inicio, '",',
                '"caja_final" : "', new.caja_final, '",',
                '"folio_unico" : "', new.folio_unico, '"',
                '}'
            ),
            NOW(),
            'actualizaSesionCaja',
            1
        FROM sys_sucursales 
        WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
    END IF;
    SET new.sincronizar=1;

END $$