DROP TRIGGER IF EXISTS insertaConfiguracionSucursal|
DELIMITER $$
CREATE TRIGGER insertaConfiguracionSucursal
AFTER INSERT ON ec_configuracion_sucursal
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    IF( store_id = -1 AND new.sincronizar = 1 )
    THEN
        INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
        id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
        SELECT 
            NULL,
            store_id,
            id_sucursal,
            CONCAT('{',
                '"table_name" : "ec_configuracion_sucursal",',
                '"action_type" : "insert",',
                '"primary_key" : "id_configuracion_sucursal",',
                '"primary_key_value" : "', new.id_configuracion_sucursal, '",',
                '"id_configuracion_sucursal" : "', new.id_configuracion_sucursal, '",',
                '"id_sucursal" : "', new.id_sucursal, '",',
                '"no_tickets_resolucion" : "', new.no_tickets_resolucion, '",',
                '"imprime_ubicacion_pdf_transf" : "', new.imprime_ubicacion_pdf_transf, '",',
                '"no_tickets_abono" : "', new.no_tickets_abono, '",',
                '"sucursal_impresion_local" : "', new.sucursal_impresion_local, '",',
                '"solicitar_asistencia_iniciar_sesion" : "', new.solicitar_asistencia_iniciar_sesion, '",',
                '"multicajero" : "', new.multicajero, '",',
                '"pide_password_asistencia_login" : "', new.pide_password_asistencia_login, '",',
                '"permite_abrir_caja_linea" : "', new.permite_abrir_caja_linea, '",',
                '"permite_ventas_linea" : "', new.permite_ventas_linea, '",',
                '"mostrar_descuento_ticket" : "', new.mostrar_descuento_ticket, '",',
                '"solicitar_password_inventario_insuficiente" : "', new.solicitar_password_inventario_insuficiente, '",',
                '"pedir_password_devolucion" : "', new.pedir_password_devolucion, '",',
                '"ofrecer_productos" : "', new.ofrecer_productos, '",',
                '"imprimir_validaciones_pendientes" : "', new.imprimir_validaciones_pendientes, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaConfiguracionSucursal',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$