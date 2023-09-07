DROP TRIGGER IF EXISTS actualizaConfiguracionSistema|
DELIMITER $$
CREATE TRIGGER actualizaConfiguracionSistema
BEFORE UPDATE ON sys_configuracion_sistema
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
                '"table_name" : "sys_configuracion_sistema",',
                '"action_type" : "update",',
                '"primary_key" : "id_configuracion_sistema",',
                '"primary_key_value" : "', new.id_configuracion_sistema, '",',
                '"racionar_transferencias_productos" : "', new.racionar_transferencias_productos, '",',
                '"minimo_agrupar_ma_dia" : "', new.minimo_agrupar_ma_dia, '",',
                '"minimo_agrupar_ma_ano" : "', new.minimo_agrupar_ma_ano, '",',
                '"minimo_agrupar_ma_anteriores" : "', new.minimo_agrupar_ma_anteriores, '",',
                '"minimo_agrupar_vtas_dias" : "', new.minimo_agrupar_vtas_dias, '",',
                '"minimo_agrupar_vtas_ano" : "', new.minimo_agrupar_vtas_ano, '",',
                '"minimo_agrupar_vtas_anteriores" : "', new.minimo_agrupar_vtas_anteriores, '",',
                '"minimo_eliminar_reg_no_usados" : "', new.minimo_eliminar_reg_no_usados, '",',
                '"minimo_eliminar_reg_sin_inventario" : "', new.minimo_eliminar_reg_sin_inventario, '",',
                '"id_regla_transferencias" : "', new.id_regla_transferencias, '",',
                '"liberacion_bloque_recepcion_sucursal" : "', new.liberacion_bloque_recepcion_sucursal, '",',
                '"omitir_codigos_barras_unicos" : "', new.omitir_codigos_barras_unicos, '",',
                '"default_tipos_codigos_barras" : "', new.default_tipos_codigos_barras, '",',
                '"default_calcular_etiquetas_cb" : "', new.default_calcular_etiquetas_cb, '",',
                '"prefijo_codigos_unicos" : "', new.prefijo_codigos_unicos, '",',
                '"ultima_actualizacion_prefijo_codigos_unicos" : "', new.ultima_actualizacion_prefijo_codigos_unicos, '",',
                '"no_solicitar_medidas_recepcion" : "', new.no_solicitar_medidas_recepcion, '",',
                '"fecha_inventario_inicial_actual" : "', new.fecha_inventario_inicial_actual, '",',
                '"mostrar_marca_surtimiento" : "', new.mostrar_marca_surtimiento, '",',
                '"guardar_proveedores_producto" : "', new.guardar_proveedores_producto, '",',
                '"permite_escanear_con_camara" : "', new.permite_escanear_con_camara, '",',
                '"tipo_recepcion_transferencia" : "', new.tipo_recepcion_transferencia, '",',
                '"habilitar_transferencia_vaciar_almacen" : "', new.habilitar_transferencia_vaciar_almacen, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaConfiguracionSistema',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar=1;
END $$