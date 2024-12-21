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
/*Inserta movimeinto de efectivo*/
		INSERT INTO ec_movimiento_banco ( id_movimiento_banco, id_caja, id_afiliacion, id_terminal, id_concepto, id_usuario, monto, folio, fecha, id_ingreso_corte_caja, id_traspaso_banco, id_pago_recepcion_oc, 
        observaciones, id_usuario_modifica )
			SELECT
				null,
				id_banco,
				id_afiliacion,
                -1,
				1,
				new.id_usuario_verifica,
				monto_validacion,
				'folio',
				now(),
				id_sesion_caja_detalle,
				-1,
				-1,
				observaciones,
				-1
			FROM ec_sesion_caja_detalle 
			WHERE id_corte_caja=new.id_sesion_caja;
/*inserta movimientos de terminales INBURSA*/
		INSERT INTO ec_movimiento_banco ( id_movimiento_banco, id_caja, id_afiliacion, id_terminal, id_concepto, id_usuario, monto, folio, fecha, id_ingreso_corte_caja, id_traspaso_banco, id_pago_recepcion_oc, 
        observaciones, id_usuario_modifica )
			SELECT
				null,
				cc.id_caja_cuenta,
				sca.id_afiliacion,
                -1,
				1,
				new.id_usuario_verifica,
				sca.monto_validacion,
				'folio',
				now(),
				sca.id_sesion_caja,
				-1,
				-1,
				'CORTE DE INBURSA',
				-1
			FROM ec_sesion_caja_afiliaciones sca
            LEFT JOIN ec_afiliaciones a
            ON a.id_afiliacion = sca.id_afiliacion
            LEFT JOIN ec_caja_o_cuenta cc
            ON cc.id_caja_cuenta = a.id_banco
			WHERE sca.id_sesion_caja = new.id_sesion_caja;
/*inserta movimientos de terminales NETPAY*/
		INSERT INTO ec_movimiento_banco ( id_movimiento_banco, id_caja, id_afiliacion, id_terminal, id_concepto, id_usuario, monto, folio, fecha, id_ingreso_corte_caja, id_traspaso_banco, id_pago_recepcion_oc, 
        observaciones, id_usuario_modifica )
			SELECT
				null,
				cc.id_caja_cuenta,
                -1,
				sct.id_terminal,
				1,
				new.id_usuario_verifica,
				sct.monto_validacion,
				'folio',
				now(),
				sct.id_sesion_caja,
				-1,
				-1,
				'CORTE DE NETPAY',
				-1
			FROM ec_sesion_caja_terminales sct
            LEFT JOIN ec_terminales_integracion_smartaccounts ti
            ON sct.id_terminal = ti.id_terminal_integracion
            LEFT JOIN ec_caja_o_cuenta cc
            ON cc.id_caja_cuenta = ti.id_caja_cuenta
			WHERE sct.id_sesion_caja = new.id_sesion_caja;
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